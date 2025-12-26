<?php
/**
 * ActualizarBDCentral.php
 * 
 * Actualiza la base de datos CENTRAL con los cambios realizados localmente
 * Sincroniza: LOCAL → CENTRAL
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=======================================================\n";
echo "  ACTUALIZACION DE BASE DE DATOS CENTRAL\n";
echo "  Sincronizacion: LOCAL -> CENTRAL\n";
echo "=======================================================\n\n";

// Configuración de conexión CENTRAL
$central_host = '26.234.93.144';
$central_port = 3306;
$central_user = 'remoto';
$central_pass = 'Sistemas2025!';
$central_db = 'gestion_egresados';

// Configuración de conexión LOCAL
$local_host = 'localhost';
$local_user = 'root';
$local_pass = '';
$local_db = 'gestion_egresados';

echo "[1/5] Conectando a base de datos LOCAL...\n";

try {
    $pdoLocal = new PDO(
        "mysql:host=$local_host;dbname=$local_db;charset=utf8mb4",
        $local_user,
        $local_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "  [OK] Conectado a BD LOCAL\n\n";
} catch (PDOException $e) {
    die("  [X] Error conectando a BD LOCAL: " . $e->getMessage() . "\n");
}

echo "[2/5] Conectando a base de datos CENTRAL...\n";

try {
    $pdoCentral = new PDO(
        "mysql:host=$central_host;port=$central_port;dbname=$central_db;charset=utf8mb4",
        $central_user,
        $central_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5
        ]
    );
    echo "  [OK] Conectado a BD CENTRAL ($central_host)\n\n";
} catch (PDOException $e) {
    die("  [X] Error conectando a BD CENTRAL: " . $e->getMessage() . "\n\n" .
        "Posibles causas:\n" .
        "  - Servidor central no accesible\n" .
        "  - Firewall bloqueando puerto 3306\n" .
        "  - Usuario remoto no configurado\n" .
        "  - VPN no activa\n\n");
}

echo "[3/5] Obteniendo datos LOCALES...\n";

// Obtener todos los registros locales que tienen ID < 1000000 (datos centrales)
$sqlLocal = "SELECT identificacion, nombreCompleto, expediente_pdf, 
             expediente_drive_id, expediente_drive_link, tituloObtenido,
             fechaEntregaCertificado
             FROM egresado 
             WHERE identificacion < 1000000
             ORDER BY identificacion";

$stmtLocal = $pdoLocal->prepare($sqlLocal);
$stmtLocal->execute();
$datosLocales = $stmtLocal->fetchAll();

echo "  Total de registros locales (centrales): " . count($datosLocales) . "\n\n";

if (count($datosLocales) === 0) {
    die("  [X] No hay datos para sincronizar\n");
}

echo "[4/5] Actualizando base de datos CENTRAL...\n\n";

$actualizados = 0;
$errores = 0;
$sinCambios = 0;
$detalles = [];

foreach ($datosLocales as $reg) {
    $id = $reg['identificacion'];
    $nombre = $reg['nombrecompleto'] ?? $reg['nombreCompleto'];
    
    echo "ID: $id | $nombre\n";
    
    try {
        // Verificar si existe en central
        $sqlCheck = "SELECT expediente_pdf, expediente_drive_id, expediente_drive_link 
                     FROM egresado WHERE identificacion = :id";
        $stmtCheck = $pdoCentral->prepare($sqlCheck);
        $stmtCheck->execute([':id' => $id]);
        $datoCentral = $stmtCheck->fetch();
        
        if (!$datoCentral) {
            echo "  [!] No existe en central, omitiendo\n";
            continue;
        }
        
        // Comparar datos
        $cambios = false;
        $camposActualizar = [];
        
        if (($reg['expediente_pdf'] ?? '') !== ($datoCentral['expediente_pdf'] ?? '')) {
            $cambios = true;
            $camposActualizar[] = "expediente_pdf";
        }
        
        if (($reg['expediente_drive_id'] ?? '') !== ($datoCentral['expediente_drive_id'] ?? '')) {
            $cambios = true;
            $camposActualizar[] = "expediente_drive_id";
        }
        
        if (($reg['expediente_drive_link'] ?? '') !== ($datoCentral['expediente_drive_link'] ?? '')) {
            $cambios = true;
            $camposActualizar[] = "expediente_drive_link";
        }
        
        if (!$cambios) {
            echo "  [=] Sin cambios\n";
            $sinCambios++;
            continue;
        }
        
        // Actualizar en central
        $sqlUpdate = "UPDATE egresado SET 
                      expediente_pdf = :pdf,
                      expediente_drive_id = :drive_id,
                      expediente_drive_link = :drive_link
                      WHERE identificacion = :id";
        
        $stmtUpdate = $pdoCentral->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':pdf' => $reg['expediente_pdf'] ?? '',
            ':drive_id' => $reg['expediente_drive_id'] ?? '',
            ':drive_link' => $reg['expediente_drive_link'] ?? '',
            ':id' => $id
        ]);
        
        echo "  [OK] Actualizado: " . implode(', ', $camposActualizar) . "\n";
        $actualizados++;
        
        $detalles[] = [
            'id' => $id,
            'nombre' => $nombre,
            'campos' => $camposActualizar
        ];
        
    } catch (PDOException $e) {
        echo "  [X] Error: " . $e->getMessage() . "\n";
        $errores++;
    }
    
    echo "\n";
}

echo "[5/5] Sincronizando tablas auxiliares...\n\n";

// Sincronizar tabla titulo
try {
    echo "Tabla 'titulo':\n";
    $sqlTitulo = "SELECT * FROM titulo";
    $stmtTitulo = $pdoLocal->prepare($sqlTitulo);
    $stmtTitulo->execute();
    $titulos = $stmtTitulo->fetchAll();
    
    $titulosSyncCount = 0;
    foreach ($titulos as $titulo) {
        $sqlReplace = "REPLACE INTO titulo (id_titulo, descripcion) VALUES (:id, :desc)";
        $stmtReplace = $pdoCentral->prepare($sqlReplace);
        $stmtReplace->execute([
            ':id' => $titulo['id_titulo'],
            ':desc' => $titulo['descripcion']
        ]);
        $titulosSyncCount++;
    }
    
    echo "  [OK] $titulosSyncCount registros sincronizados\n\n";
} catch (PDOException $e) {
    echo "  [X] Error sincronizando titulos: " . $e->getMessage() . "\n\n";
}

// Sincronizar configuracion_certificado
try {
    echo "Tabla 'configuracion_certificado':\n";
    $sqlConfig = "SELECT * FROM configuracion_certificado LIMIT 1";
    $stmtConfig = $pdoLocal->prepare($sqlConfig);
    $stmtConfig->execute();
    $config = $stmtConfig->fetch();
    
    if ($config) {
        $sqlReplace = "REPLACE INTO configuracion_certificado 
                       (id, nombre_institucion, logo_path, firmante_nombre, firmante_cargo, 
                        firmante_firma_path, template_html) 
                       VALUES (:id, :nombre, :logo, :firma_nombre, :firma_cargo, :firma_path, :template)";
        $stmtReplace = $pdoCentral->prepare($sqlReplace);
        $stmtReplace->execute([
            ':id' => $config['id'],
            ':nombre' => $config['nombre_institucion'] ?? '',
            ':logo' => $config['logo_path'] ?? '',
            ':firma_nombre' => $config['firmante_nombre'] ?? '',
            ':firma_cargo' => $config['firmante_cargo'] ?? '',
            ':firma_path' => $config['firmante_firma_path'] ?? '',
            ':template' => $config['template_html'] ?? ''
        ]);
        
        echo "  [OK] Configuracion sincronizada\n\n";
    }
} catch (PDOException $e) {
    echo "  [X] Error sincronizando configuracion: " . $e->getMessage() . "\n\n";
}

echo "=======================================================\n";
echo "                 RESUMEN\n";
echo "=======================================================\n\n";
echo "Registros analizados:     " . count($datosLocales) . "\n";
echo "Registros actualizados:   $actualizados\n";
echo "Sin cambios:              $sinCambios\n";
echo "Errores:                  $errores\n\n";

if (count($detalles) > 0) {
    echo "Detalle de registros actualizados:\n\n";
    foreach ($detalles as $detalle) {
        echo sprintf("  ID %-6s | %-40s | %s\n", 
            $detalle['id'], 
            substr($detalle['nombre'], 0, 40),
            implode(', ', $detalle['campos'])
        );
    }
    echo "\n";
}

if ($errores === 0 && $actualizados > 0) {
    echo "Estado: SINCRONIZACION EXITOSA\n";
    echo "\nLa base de datos CENTRAL ha sido actualizada con los cambios locales.\n";
} elseif ($actualizados === 0 && $sinCambios > 0) {
    echo "Estado: BASES DE DATOS YA SINCRONIZADAS\n";
    echo "\nNo se requirieron cambios. Ambas bases de datos estan identicas.\n";
} else {
    echo "Estado: COMPLETADO CON ERRORES\n";
    echo "\nRevisar los errores mostrados arriba.\n";
}

echo "\n=======================================================\n";
