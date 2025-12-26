<?php
/**
 * Test directo de procesamiento de expediente desde Google Drive
 */

// Simular request POST
$_POST = [
    'import_context' => 'crear_manual',
    'drive_file_id' => '1P3BxHa30y0uqI4H7JJbQjQJKKJ7KJWU8', // ID de un archivo en Google Drive
];

// Cargar controller
require_once __DIR__ . '/controlador/ProcesarExpedienteController.php';
?>