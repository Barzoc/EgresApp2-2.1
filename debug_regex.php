<?php
function findLocalFileByNameDebug(string $name, string $dir)
{
    echo "Searching for: '$name'\n";
    echo "Directory: '$dir'\n";

    $name = trim($name);
    
    // Create a fuzzy regex pattern from the name
    // 1. Normalize accents
    $map = [
        'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U',
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
        // Treat Ñ as a wildcard separator because it's often handled inconsistently
        'Ñ' => '.*', 'ñ' => '.*'
    ];
    $cleanName = strtr($name, $map);
    echo "Clean Name (Step 1): '$cleanName'\n";
    
    // 2. Keep only alphanumeric and wildcards
    $cleanName = preg_replace('/[^A-Za-z0-9\.\*]+/', '.*', $cleanName);
    echo "Clean Name (Step 2): '$cleanName'\n";
    
    // 3. Build regex: Start with anything, match the name parts, end with .pdf
    $regex = '/.*' . $cleanName . '.*\.pdf$/i';
    echo "Regex: $regex\n";

    try {
        $directory = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $info) {
            if ($info->isFile()) {
                $filename = $info->getFilename();
                // echo "Checking: '$filename' ... ";
                if (preg_match($regex, $filename)) {
                    echo "MATCH FOUND: '$filename'\n";
                    return;
                } else {
                    // echo "No match\n";
                }
            }
        }
    } catch (Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "No match found in directory.\n";
}

$dir = __DIR__ . '/assets/expedientes/expedientes_subidos';
$name = "ADRIAN VÍCTOR ANDRÉS YAÑEZ ROJAS";

findLocalFileByNameDebug($name, $dir);
