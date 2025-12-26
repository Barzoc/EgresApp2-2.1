<?php
/**
 * SincronizarDesdeCentral.php
 * 
 * Sincroniza datos desde el SERVIDOR CENTRAL hacia la base de datos LOCAL
 * Dirección: CENTRAL -> LOCAL
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=======================================================\n";
echo "  SINCRONIZACION: SERVIDOR CENTRAL -> LOCAL\n";
echo "=======================================================\n\n";

// Configuración
$central_host = '26.234.93.144';
$central_port = 3306;
$central_user = 'remoto';
$central_pass = 'Sistemas2025!';
$central_db = 'gestion_egresados';

$local_host = 'localhost';
$local_user = 'root';
$local_pass = '';
$local_db = 'gestion_egresados';

echo "[1/4] Conectando a SERVIDOR CENTRAL...\n";

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
    echo "  [OK] Conectado al SERVIDOR CENTRAL ($central_host)\n\n";
} catch (PDOException $e) {
    die("  [X] Error conectando al SERVIDOR CENTRAL: " . $e->getMessage() . "\n\n" .
        "El servidor central no está accesible.\n");
}

echo "[2/4] Conectando a BASE DE DATOS LOCAL...\n";

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

echo "[3/4] Sincronizando datos...\n\n";

// Tabla: egresado
echo "Sincronizando tabla 'egresado'...\n";

$sqlEgresado = "SELECT * FROM egresado WHERE identificacion < 1000000";
$stmtEgresado = $pdoCentral->prepare($sqlEgresado);
$stmtEgresado->execute();
$egresados = $stmtEgresado->fetchAll();

echo "  Registros en central: " . count($egresados) . "\n";

$sqlReplace = "REPLACE INTO egresado (
    identificacion, nombreCompleto, carnet, tituloObtenido, 
    fechaEntregaCertificado, expediente_pdf, expediente_drive_id, 
    expediente_drive_link
) VALUES (
    :identificacion, :nombreCompleto, :carnet, :tituloObtenido,
    :fechaEntregaCertificado, :expediente_pdf, :expediente_drive_id,
    :expediente_drive_link
)";

$stmtReplace = $pdoLocal->prepare($sqlReplace);

$sincronizados = 0;
foreach ($egresados as $egresado) {
    $stmtReplace->execute([
        ':identificacion' => $egresado['identificacion'],
        ':nombreCompleto' => $egresado['nombrecompleto'] ?? $egresado['nombreCompleto'],
        ':carnet' => $egresado['carnet'] ?? '',
        ':tituloObtenido' => $egresado['tituloobtenido'] ?? $egresado['tituloObtenido'] ?? '',
        ':fechaEntregaCertificado' => $egresado['fechaentregacertificado'] ?? $egresado['fechaEntregaCertificado'] ?? null,
        ':expediente_pdf' => $egresado['expediente_pdf'] ?? '',
        ':expediente_drive_id' => $egresado['expediente_drive_id'] ?? '',
        ':expediente_drive_link' => $egresado['expediente_drive_link'] ?? ''
    ]);
    $sincronizados++;
}

echo "  [OK] $sincronizados registros sincronizados\n\n";

// Tabla: titulo
echo "Sincronizando tabla 'titulo'...\n";

$sqlTitulo = "SELECT * FROM titulo";
$stmtTitulo = $pdoCentral->prepare($sqlTitulo);
$stmtTitulo->execute();
$titulos = $stmtTitulo->fetchAll();

$sqlReplaceTitulo = "REPLACE INTO titulo (id_titulo, descripcion) VALUES (:id, :desc)";
$stmtReplaceTitulo = $pdoLocal->prepare($sqlReplaceTitulo);

$titulosSinc = 0;
foreach ($titulos as $titulo) {
    $stmtReplaceTitulo->execute([
        ':id' => $titulo['id_titulo'],
        ':desc' => $titulo['descripcion']
    ]);
    $titulosSinc++;
}

echo "  [OK] $titulosSinc registros sincronizados\n\n";

// Tabla: configuracion_certificado
echo "Sincronizando tabla 'configuracion_certificado'...\n";

$sqlConfig = "SELECT * FROM configuracion_certificado LIMIT 1";
$stmtConfig = $pdoCentral->prepare($sqlConfig);
$stmtConfig->execute();
$config = $stmtConfig->fetch();

if ($config) {
    $sqlReplaceConfig = "REPLACE INTO configuracion_certificado 
        (id, nombre_institucion, logo_path, firmante_nombre, 
         firmante_cargo, firmante_firma_path, template_html)
        VALUES (:id, :nombre, :logo, :firma_nombre, :firma_cargo, :firma_path, :template)";
    
    $stmtReplaceConfig = $pdoLocal->prepare($sqlReplaceConfig);
    $stmtReplaceConfig->execute([
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

echo "[4/4] Verificando sincronizacion...\n\n";

// Verificar conteo local
$sqlCount = "SELECT COUNT(*) as total FROM egresado WHERE identificacion < 1000000";
$stmtCount = $pdoLocal->prepare($sqlCount);
$stmtCount->execute();
$localCount = $stmtCount->fetch();

echo "Registros en BD LOCAL: " . $localCount['total'] . "\n";
echo "Registros sincronizados: $sincronizados\n\n";

if ($localCount['total'] == $sincronizados) {
    echo "[OK] Sincronizacion verificada correctamente\n";
} else {
    echo "[!!] Advertencia: Los conteos no coinciden\n";
}

echo "\n=======================================================\n";
echo "                 SINCRONIZACION COMPLETADA\n";
echo "=======================================================\n\n";

echo "Resumen:\n";
echo "  Tabla 'egresado':                $sincronizados registros\n";
echo "  Tabla 'titulo':                  $titulosSinc registros\n";
echo "  Tabla 'configuracion_certificado': Actualizada\n\n";

echo "La base de datos LOCAL ahora tiene los datos mas recientes\n";
echo "del SERVIDOR CENTRAL.\n\n";

echo "Fecha de sincronizacion: " . date('Y-m-d H:i:s') . "\n";
echo "=======================================================\n";
