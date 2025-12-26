<?php
$id = $argv[1] ?? '';
if ($id === '') {
    fwrite(STDERR, "Usage: php debug_verifica_cli.php <id>\n");
    exit(1);
}
$_POST = ['accion' => 'verificar', 'id' => $id];
include __DIR__ . '/controlador/ExpedienteStorageController.php';
