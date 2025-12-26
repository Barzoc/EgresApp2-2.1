<?php
// Enable error reporting to capture everything
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Simulate the environment
$_POST['funcion'] = 'obtenerResumenRespaldo';

// Capture output
ob_start();
try {
    // We need to be in the controller directory context
    chdir(__DIR__ . '/controlador');
    include 'DashboardController.php';
} catch (Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage();
}
$output = ob_get_clean();

echo "--- START DEBUG OUTPUT ---\n";
echo $output;
echo "\n--- END DEBUG OUTPUT ---\n";
