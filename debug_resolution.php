<?php
require_once __DIR__ . '/lib/DriveFolderMapper.php';

$titulos_prueba = [
    'Técnico De Nivel Medio En Administración',
    'TECNICO DE NIVEL MEDIO EN ADMINISTRACION',
    'Tecnico en Administracion',
    'Administracion',
    'Sin Titulo',
    'TECNICO EN ENFERMERIA'
];

echo "=== Simulacion de Resolucion de Carpetas ===\n";

foreach ($titulos_prueba as $titulo) {
    echo "Titulo: ['$titulo']\n";
    $res = DriveFolderMapper::resolveByTitle($titulo);
    
    if (empty($res['drive_folder_id'])) {
        echo "  -> Resultado: NO ENCONTRADO (Null)\n";
    } else {
        echo "  -> Resultado: ID " . $res['drive_folder_id'] . " (Alias: " . ($res['aliases'][0] ?? '?') . ")\n";
    }
    echo "----------------------------------------\n";
}
