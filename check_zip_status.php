<?php
// check_zip_status.php
header('Content-Type: text/plain');

echo "=== DIAGNÓSTICO DE ZIPARCHIVE ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "SAPI: " . php_sapi_name() . "\n\n";

echo "[1] VERIFICACIÓN DE CLASE\n";
if (class_exists('ZipArchive')) {
    echo "✅ Clase 'ZipArchive' ESTÁ DISPONIBLE.\n";
} else {
    echo "❌ Clase 'ZipArchive' NO ENCONTRADA.\n";
}

echo "\n[2] ARCHIVOS DE CONFIGURACIÓN (INI)\n";
echo "Loaded Configuration File: " . php_ini_loaded_file() . "\n";
echo "Scan this dir for additional .ini files: " . php_ini_scanned_files() . "\n";

echo "\n[3] EXTENSIONES CARGADAS\n";
$exts = get_loaded_extensions();
if (in_array('zip', $exts)) {
    echo "✅ Extensión 'zip' aparece en la lista de extensiones cargadas.\n";
} else {
    echo "❌ Extensión 'zip' NO está en la lista de extensiones cargadas.\n";
}

echo "\n[4] DIRECTORIO DE EXTENSIONES\n";
$extDir = ini_get('extension_dir');
echo "extension_dir = " . $extDir . "\n";

if (is_dir($extDir)) {
    $dllPath = $extDir . DIRECTORY_SEPARATOR . 'php_zip.dll';
    if (file_exists($dllPath)) {
        echo "✅ Archivo 'php_zip.dll' encontrado en: $dllPath\n";
    } else {
        echo "❌ Archivo 'php_zip.dll' NO encontrado en: $extDir\n";
        echo "   Contenido del directorio:\n";
        $files = scandir($extDir);
        foreach ($files as $f) {
            if (strpos($f, 'zip') !== false) echo "   - $f\n";
        }
    }
} else {
    echo "❌ El directorio de extensiones no existe o es inaccesible.\n";
}

echo "\n[5] CONTENIDO DE PHP.INI (Búsqueda de 'zip')\n";
$iniFile = php_ini_loaded_file();
if ($iniFile && file_exists($iniFile)) {
    $lines = file($iniFile);
    foreach ($lines as $n => $line) {
        if (stripos($line, 'zip') !== false) {
            echo "Línea " . ($n+1) . ": " . trim($line) . "\n";
        }
    }
} else {
    echo "No se pudo leer el archivo php.ini.\n";
}

echo "\n[6] INTENTO DE CREACIÓN\n";
try {
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        echo "✅ Instancia de ZipArchive creada correctamente.\n";
    }
} catch (Throwable $e) {
    echo "❌ Error al instanciar: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
