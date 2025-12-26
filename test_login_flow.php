<?php
// test_login_flow.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Test Login Flow</h1>";

// Mock $_POST and $_SESSION
$_POST['email'] = 'admin@test.com';
$_POST['contrasena'] = '12345678';
$_SESSION = [];

try {
    echo "Including Ingresar.php...<br>";
    require_once 'modelo/Ingresar.php';

    echo "Instantiating Ingresar...<br>";
    $ingreso = new Ingresar();

    echo "Calling Validar...<br>";
    $ingreso->Validar($_POST['email'], $_POST['contrasena']);

    echo "<br>Finished.<br>";

    if (file_exists('debug_login.log')) {
        echo "<h3>Log Content:</h3>";
        echo nl2br(file_get_contents('debug_login.log'));
    } else {
        echo "<b>Log file not created!</b>";
    }

} catch (Throwable $e) {
    echo "<b style='color:red'>FATAL ERROR: " . $e->getMessage() . "</b><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>