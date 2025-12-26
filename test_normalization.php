<?php
require_once __DIR__ . '/lib/DriveFolderMapper.php';

$input = "Técnico De Nivel Medio En Importacién Y Exportacion";
$aliases = [
    'TECNICO EN IMPORTACION Y EXPORTACION',
    'TÉCNICO EN IMPORTACIÓN Y EXPORTACIÓN',
];

echo "Input: '$input'\n";

// Access private method normalizeKey via reflection
$reflector = new ReflectionClass('DriveFolderMapper');
$method = $reflector->getMethod('normalizeKey');
$method->setAccessible(true);

$normalizedInput = $method->invoke(null, $input);
echo "Normalized Input: '$normalizedInput'\n";

foreach ($aliases as $alias) {
    $normalizedAlias = $method->invoke(null, $alias);
    echo "Alias: '$alias' -> Normalized: '$normalizedAlias'\n";

    if ($normalizedInput === $normalizedAlias) {
        echo "MATCH FOUND!\n";
    }
}

$resolved = DriveFolderMapper::resolveByTitle($input);
print_r($resolved);
