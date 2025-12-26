<?php
require_once __DIR__ . '/modelo/Egresado.php';
$e = new Egresado();
$id = $argv[1] ?? '';
if ($id === '') {
    fwrite(STDERR, "Usage: php debug_storage.php <id>\n");
    exit(1);
}
$result = $e->ObtenerExpedienteStorage($id);
var_dump($result);
