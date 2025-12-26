<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Google\Client as GoogleClient;
use Google\Service\Drive;

$client = new GoogleClient();
$client->setApplicationName('Prototipo QR Sync');
$client->setScopes([Drive::DRIVE]);
$client->setAuthConfig(__DIR__ . '/../config/client_secret.json');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');
// Usar urn:ietf:wg:oauth:2.0:oob para obtener el código directamente en la página
$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');

$tokenPath = __DIR__ . '/../config/token.json';

if (file_exists($tokenPath)) {
    echo "Ya existe un token. Si quieres re-autorizar, borra el archivo:\n";
    echo "$tokenPath\n";
    exit(0);
}

$authUrl = $client->createAuthUrl();
echo "Abre este enlace en tu navegador:\n\n";
echo "$authUrl\n\n";
echo "Después de autorizar, Google te mostrará un código en la página.\n";
echo "Copia ese código y pégalo aquí: ";

$authCode = trim(fgets(STDIN));

try {
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
    
    if (array_key_exists('error', $accessToken)) {
        throw new Exception('Error: ' . $accessToken['error']);
    }
    
    file_put_contents($tokenPath, json_encode($accessToken));
    echo "\n¡Autorización exitosa! El token se guardó en $tokenPath\n";
    echo "Ahora puedes ejecutar: php scripts/sync_drive.php\n";
    
} catch (Exception $e) {
    echo "\nError al obtener el token: " . $e->getMessage() . "\n";
    exit(1);
}
