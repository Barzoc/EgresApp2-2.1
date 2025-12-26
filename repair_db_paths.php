<?php
require_once __DIR__ . '/modelo/Conexion.php';

function findFileRecursive($dir)
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

try {
    $db = new Conexion();
    $pdo = $db->pdo;
    $baseDir = realpath(__DIR__ . '/assets/expedientes/expedientes_subidos');

    echo "Indexing files in $baseDir...\n";
    $allFiles = findFileRecursive($baseDir);
    $fileMap = []; // filename -> relative_path

    foreach ($allFiles as $path) {
        $filename = basename($path);
        // Calculate relative path
        $relPath = substr($path, strlen($baseDir) + 1);
        $relPath = str_replace('\\', '/', $relPath);
        $fileMap[$filename] = $relPath;
    }

    echo "Found " . count($fileMap) . " files.\n";

    $stmt = $pdo->query("SELECT identificacion, nombrecompleto, expediente_pdf FROM egresado");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $row) {
        $currentPdf = $row['expediente_pdf'];
        if (empty($currentPdf))
            continue;

        // Try to find a match
        $match = null;

        // 1. Exact match in our map (if currentPdf is just filename)
        if (isset($fileMap[$currentPdf])) {
            $match = $fileMap[$currentPdf];
        }
        // 2. If currentPdf is a path (or corrupted path), try to match by basename
        else {
            // Extract what looks like a filename from the end
            // e.g. "tecnico-en-administracionFILE.pdf" -> we might not be able to parse it easily
            // But we can check if any file in our map *ends* with the suffix of currentPdf?
            // Or better: Search for the user's name in the file list!

            // Let's try to match by user name first, as it's more reliable for these corrupted cases
            $slugName = str_replace(' ', '_', $row['nombrecompleto']); // Rough slug
            // This is hard because filenames have specific formats.

            // Let's try to see if the currentPdf (which might be corrupted) contains the filename of any file we found
            foreach ($fileMap as $fname => $relPath) {
                // If the corrupted string contains the filename (e.g. "folderFILE.pdf" contains "FILE.pdf")
                // But "FILE.pdf" might be "MANUEL_ALEJANDRO...pdf"
                if (strpos($currentPdf, $fname) !== false) {
                    $match = $relPath;
                    break;
                }

                // Or if the filename contains parts of the user's name
                // (This is risky, might match wrong file)
            }

            // If still no match, try to fuzzy match the filename against the corrupted string
            if (!$match) {
                // Try to extract the filename part from the corrupted string
                // e.g. "tecnico-en-administracionMANUEL_...pdf"
                // We can look for the pattern [A-Z_]+\d+\.pdf
                if (preg_match('/([A-Z_]+_+\d+\.pdf)$/i', $currentPdf, $m)) {
                    $extractedName = $m[1];
                    if (isset($fileMap[$extractedName])) {
                        $match = $fileMap[$extractedName];
                    }
                }
            }
        }

        if ($match && $match !== $currentPdf) {
            echo "ID: " . $row['identificacion'] . "\n";
            echo "Current: " . $currentPdf . "\n";
            echo "New:     " . $match . "\n";

            $update = $pdo->prepare("UPDATE egresado SET expediente_pdf = :pdf WHERE identificacion = :id");
            $update->execute([':pdf' => $match, ':id' => $row['identificacion']]);
            echo "Updated.\n----------------\n";
        }
    }

    echo "Done repairing paths.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
