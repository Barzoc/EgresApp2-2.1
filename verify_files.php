<?php
require_once __DIR__ . '/modelo/Conexion.php';

function findFile($name, $dir)
{
    if (empty($name))
        return null;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->getFilename() === $name) {
            return $file->getPathname();
        }
    }
    return null;
}

try {
    $db = new Conexion();
    $pdo = $db->pdo;
    $baseDir = __DIR__ . '/assets/expedientes/expedientes_subidos';

    $stmt = $pdo->query("SELECT identificacion, nombrecompleto, expediente_pdf FROM egresado ORDER BY identificacion ASC LIMIT 3");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $row) {
        echo "ID: " . $row['identificacion'] . "\n";
        echo "DB PDF: " . $row['expediente_pdf'] . "\n";

        $path = $baseDir . '/' . $row['expediente_pdf'];
        if (file_exists($path)) {
            echo "Status: FOUND at " . $path . "\n";
        } else {
            echo "Status: NOT FOUND at " . $path . "\n";
            // Try to find it
            $filename = basename($row['expediente_pdf']);
            // If the DB path was corrupted (missing slash), the basename might be wrong too if it was 'folderfile.pdf'
            // But let's try to find by just the name part if we can extract it, or search by user name

            echo "Searching recursively...\n";
            // Try to guess filename from DB value (assuming it might be folderfile.pdf)
            // But better to just search for *anything* matching the user name or parts of the DB value

            // Let's just list what we find for this user
            // ...
        }
        echo "--------------------------------\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
