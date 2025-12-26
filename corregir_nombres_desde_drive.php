<?php
// corregir_nombres_desde_drive.php
require_once 'modelo/Conexion.php';
require_once 'modelo/Egresado.php';
require_once 'lib/PDFProcessor.php';

set_time_limit(600); // 10 minutos

echo "<h1>Corrección de Nombres desde Google Drive</h1>";
echo "<p>Este script descargará los expedientes desde Google Drive, extraerá los nombres correctos y actualizará la base de datos.</p>";

try {
    $db = new Conexion();
    $pdo = $db->pdo;

    // 1. Obtener todos los egresados con expedientes en Drive
    $stmt = $pdo->query("SELECT identificacion, nombreCompleto, tituloObtenido, expediente_drive_id, expediente_drive_link FROM egresado WHERE expediente_drive_id IS NOT NULL AND expediente_drive_id != ''");
    $egresados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Egresados encontrados: " . count($egresados) . "</h2>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #092483; color: white;'>";
    echo "<th>ID</th><th>Nombre Actual (BD)</th><th>Nombre Extraído (PDF)</th><th>Título Actual</th><th>Título Extraído</th><th>Estado</th>";
    echo "</tr>";

    $actualizados = 0;
    $errores = 0;
    $tmpDir = sys_get_temp_dir();

    foreach ($egresados as $egresado) {
        $id = $egresado['identificacion'];
        $nombreActual = $egresado['nombrecompleto'] ?? '';
        $tituloActual = $egresado['tituloobtenido'] ?? '';
        $driveId = $egresado['expediente_drive_id'] ?? '';
        $driveLink = $egresado['expediente_drive_link'] ?? '';

        if (empty($driveId)) {
            echo "<tr style='background: #ffe6e6;'>";
            echo "<td>$id</td>";
            echo "<td>$nombreActual</td>";
            echo "<td colspan='3'>Sin ID de Drive</td>";
            echo "<td>⚠️ Omitido</td>";
            echo "</tr>";
            continue;
        }

        // Construir URL de descarga directa de Google Drive
        $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $driveId;
        $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . "expediente_" . $driveId . ".pdf";

        // Descargar PDF desde Drive
        try {
            echo "<tr>";
            echo "<td>$id</td>";
            echo "<td>" . htmlspecialchars($nombreActual) . "</td>";

            // Intentar descargar
            $context = stream_context_create([
                'http' => [
                    'timeout' => 30,
                    'user_agent' => 'Mozilla/5.0'
                ]
            ]);

            $pdfContent = @file_get_contents($downloadUrl, false, $context);

            if ($pdfContent === false || empty($pdfContent)) {
                echo "<td colspan='3'>Error al descargar desde Drive</td>";
                echo "<td>❌ Error</td>";
                echo "</tr>";
                $errores++;
                continue;
            }

            // Guardar temporalmente
            file_put_contents($tmpFile, $pdfContent);

            // Extraer datos del PDF
            try {
                $extracted = PDFProcessor::extractStructuredData($tmpFile);
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

                        echo "<td><b style='color:green'>" . htmlspecialchars($nombreExtraido) . "</b></td>";
                        echo "<td>" . htmlspecialchars($tituloActual) . "</td>";
                        echo "<td><b style='color:green'>" . htmlspecialchars($tituloExtraido) . "</b></td>";
                        echo "<td>✅ Actualizado</td>";
                        $actualizados++;
                    }
                } else {
                    echo "<td>" . (empty($nombreExtraido) ? '<i>No extraído</i>' : htmlspecialchars($nombreExtraido)) . "</td>";
                    echo "<td>" . htmlspecialchars($tituloActual) . "</td>";
                    echo "<td>" . (empty($tituloExtraido) ? '<i>No extraído</i>' : htmlspecialchars($tituloExtraido)) . "</td>";
                    echo "<td>⚪ Sin cambios</td>";
                }

                // Limpiar archivo temporal
                @unlink($tmpFile);

            } catch (Exception $e) {
                echo "<td colspan='3'>Error al procesar PDF: " . htmlspecialchars($e->getMessage()) . "</td>";
                echo "<td>❌ Error</td>";
                $errores++;
                @unlink($tmpFile);
            }

            echo "</tr>";

        } catch (Exception $e) {
            echo "<td colspan='3'>Error: " . htmlspecialchars($e->getMessage()) . "</td>";
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
        echo "<b>✅ Proceso completado.</b> Se actualizaron $actualizados registros con los nombres y títulos correctos desde Google Drive.";
        echo "</p>";
    }

} catch (Exception $e) {
    echo "<b style='color:red'>Error fatal: " . $e->getMessage() . "</b>";
}
?>