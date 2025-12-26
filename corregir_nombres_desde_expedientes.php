<?php
// corregir_nombres_desde_expedientes.php
require_once 'modelo/Conexion.php';
require_once 'modelo/Egresado.php';
require_once 'lib/PDFProcessor.php';

set_time_limit(300); // 5 minutos

echo "<h1>Corrección de Nombres desde Expedientes</h1>";
echo "<p>Este script extraerá los nombres correctos desde los expedientes PDF y actualizará la base de datos.</p>";

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    // 1. Obtener todos los egresados
    $stmt = $pdo->query("SELECT identificacion, nombreCompleto, tituloObtenido, expediente_pdf FROM egresado WHERE expediente_pdf IS NOT NULL AND expediente_pdf != ''");
    $egresados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Egresados encontrados: " . count($egresados) . "</h2>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #092483; color: white;'>";
    echo "<th>ID</th><th>Nombre Actual (BD)</th><th>Nombre Extraído (PDF)</th><th>Título Actual</th><th>Título Extraído</th><th>Estado</th>";
    echo "</tr>";

    $actualizados = 0;
    $errores = 0;

    foreach ($egresados as $egresado) {
        $id = $egresado['identificacion'];
        $nombreActual = $egresado['nombrecompleto'] ?? '';
        $tituloActual = $egresado['tituloobtenido'] ?? '';
        $rutaExpediente = $egresado['expediente_pdf'] ?? '';

        // Construir ruta completa del expediente
        $expedientePath = __DIR__ . '/assets/expedientes/' . $rutaExpediente;

        if (!file_exists($expedientePath)) {
            echo "<tr style='background: #ffe6e6;'>";
            echo "<td>$id</td>";
            echo "<td>$nombreActual</td>";
            echo "<td colspan='3'>Expediente no encontrado: $rutaExpediente</td>";
            echo "<td>❌ Error</td>";
            echo "</tr>";
            $errores++;
            continue;
        }

        // Extraer datos del PDF
        try {
            $extracted = PDFProcessor::extractStructuredData($expedientePath);
            $nombreExtraido = $extracted['fields']['nombre_completo'] ?? '';
            $tituloExtraido = $extracted['fields']['titulo'] ?? '';

            // Verificar si hay diferencias (caracteres corruptos vs correctos)
            $nombreNecesitaActualizar = !empty($nombreExtraido) && $nombreExtraido !== $nombreActual && strpos($nombreActual, '?') !== false;
            $tituloNecesitaActualizar = !empty($tituloExtraido) && $tituloExtraido !== $tituloActual && strpos($tituloActual, '?') !== false;

            if ($nombreNecesitaActualizar || $tituloNecesitaActualizar) {
                // Actualizar base de datos
                $updates = [];
                $params = [':id' => $id];

                if ($nombreNecesitaActualizar) {
                    $updates[] = "nombreCompleto = :nombre";
                    $params[':nombre'] = $nombreExtraido;
                }

                if ($tituloNecesitaActualizar) {
                    $updates[] = "tituloObtenido = :titulo";
                    $params[':titulo'] = $tituloExtraido;
                }

                if (!empty($updates)) {
                    $sql = "UPDATE egresado SET " . implode(', ', $updates) . " WHERE identificacion = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);

                    echo "<tr style='background: #e6ffe6;'>";
                    echo "<td>$id</td>";
                    echo "<td>" . htmlspecialchars($nombreActual) . "</td>";
                    echo "<td><b>" . htmlspecialchars($nombreExtraido) . "</b></td>";
                    echo "<td>" . htmlspecialchars($tituloActual) . "</td>";
                    echo "<td><b>" . htmlspecialchars($tituloExtraido) . "</b></td>";
                    echo "<td>✅ Actualizado</td>";
                    echo "</tr>";
                    $actualizados++;
                }
            } else {
                echo "<tr>";
                echo "<td>$id</td>";
                echo "<td>" . htmlspecialchars($nombreActual) . "</td>";
                echo "<td>" . (empty($nombreExtraido) ? '<i>No extraído</i>' : htmlspecialchars($nombreExtraido)) . "</td>";
                echo "<td>" . htmlspecialchars($tituloActual) . "</td>";
                echo "<td>" . (empty($tituloExtraido) ? '<i>No extraído</i>' : htmlspecialchars($tituloExtraido)) . "</td>";
                echo "<td>⚪ Sin cambios</td>";
                echo "</tr>";
            }

        } catch (Exception $e) {
            echo "<tr style='background: #ffe6e6;'>";
            echo "<td>$id</td>";
            echo "<td>$nombreActual</td>";
            echo "<td colspan='3'>Error al procesar PDF: " . htmlspecialchars($e->getMessage()) . "</td>";
            echo "<td>❌ Error</td>";
            echo "</tr>";
            $errores++;
        }

        // Flush output para ver progreso en tiempo real
        ob_flush();
        flush();
    }

    echo "</table>";

    echo "<h2>Resumen</h2>";
    echo "<ul>";
    echo "<li><b>Total procesados:</b> " . count($egresados) . "</li>";
    echo "<li><b style='color:green'>Actualizados:</b> $actualizados</li>";
    echo "<li><b style='color:red'>Errores:</b> $errores</li>";
    echo "<li><b>Sin cambios:</b> " . (count($egresados) - $actualizados - $errores) . "</li>";
    echo "</ul>";

    if ($actualizados > 0) {
        echo "<p style='background: #e6ffe6; padding: 10px; border: 2px solid green;'>";
        echo "<b>✅ Proceso completado.</b> Se actualizaron $actualizados registros con los nombres y títulos correctos desde los expedientes PDF.";
        echo "</p>";
    }

} catch (Exception $e) {
    echo "<b style='color:red'>Error fatal: " . $e->getMessage() . "</b>";
}
?>