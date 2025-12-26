<?php
/**
 * Script de Depuración de Almacenamiento Local (Storage)
 * Verifica si el sistema puede "ver" los archivos físicos de los expedientes.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>📂 Debugger de Almacenamiento (Storage)</h1>";

// 1. Verificar Rutas Base
echo "<h2>1. Resolución de Rutas</h2>";

$baseDir = __DIR__;
$assetsDir = $baseDir . '/assets/expedientes/expedientes_subidos';
$realAssetsDir = realpath($assetsDir);

echo "<pre>";
echo "CRITICAL: __DIR__ actual: " . $baseDir . "\n";
echo "Assets Path esperado:    " . $assetsDir . "\n";
echo "Assets Path resuelto:    " . ($realAssetsDir ?: '❌ FALSE (No existe o sin permisos)') . "\n";
echo "</pre>";

if ($realAssetsDir === false) {
    echo "<div style='color:red;'>❌ ERROR CRÍTICO: La carpeta de expedientes no se encuentra o no es accesible.</div>";
    echo "<p>Verifica que hayas copiado la carpeta <code>assets</code> completa al otro PC.</p>";
    // Intentar crearla para ver si es permisos
    echo "Intentando crear directorio... ";
    if (@mkdir($assetsDir, 0755, true)) {
        echo "✅ Éxito creando directorio (estaba ausente).<br>";
    } else {
        $lastError = error_get_last();
        echo "❌ Falló al crear: " . ($lastError['message'] ?? 'Error desconocido') . "<br>";
    }
} else {
    echo "<div style='color:green;'>✅ Carpeta de expedientes accesible.</div>";
    
    // 2. Listar Archivos
    echo "<h2>2. Listado de Archivos (Primeros 10)</h2>";
    $files = scandir($realAssetsDir);
    $files = array_diff($files, ['.', '..']);
    
    echo "<p>Total archivos/carpetas en raíz: " . count($files) . "</p>";
    echo "<ul style='background:#f0f0f0; padding:10px;'>";
    $count = 0;
    foreach ($files as $f) {
        if ($count++ > 10) break;
        $path = $realAssetsDir . DIRECTORY_SEPARATOR . $f;
        $isDir = is_dir($path) ? '[DIR]' : '[FILE]';
        $size = is_file($path) ? round(filesize($path)/1024, 2) . ' KB' : '-';
        $perm = substr(sprintf('%o', fileperms($path)), -4);
        
        echo "<li><strong>$isDir $f</strong> (Permisos: $perm, Tamaño: $size)</li>";
        
        // Si es directorio, listar contenido
        if (is_dir($path)) {
            $subfiles = scandir($path);
            $subfiles = array_diff($subfiles, ['.', '..']);
            echo "<ul>";
            $subcount = 0;
            foreach ($subfiles as $sf) {
                if ($subcount++ > 3) { echo "<li>...</li>"; break; }
                echo "<li>$sf</li>";
            }
            if (empty($subfiles)) echo "<li>(vacío)</li>";
            echo "</ul>";
        }
    }
    echo "</ul>";
    
    if (count($files) === 0) {
        echo "<div style='color:orange;'>⚠️ La carpeta existe pero está VACÍA. ¿Copiaste los archivos de expedientes?</div>";
    }
}

// 3. Prueba de Acceso Web
echo "<h2>3. Prueba de Acceso Web</h2>";
// Intentar acceder a un archivo de prueba via URL relativa
echo "<p>Intenta acceder manualmente a un archivo PDF si aparece en la lista anterior usando esta URL base:</p>";
echo "<code>http://localhost/EGRESAPP2/assets/expedientes/expedientes_subidos/[NOMBRE_ARCHIVO]</code>";

echo "<h2>4. Verificación de Permisos de Escritura</h2>";
$testFile = $assetsDir . '/test_write_' . uniqid() . '.txt';
$canWrite = @file_put_contents($testFile, 'test');
if ($canWrite) {
    echo "<div style='color:green;'>✅ Permisos de ESCRITURA correctos.</div>";
    unlink($testFile);
} else {
    echo "<div style='color:red;'>❌ NO HAY PERMISOS DE ESCRITURA en la carpeta de expedientes.</div>";
}
