<?php
/**
 * PASO 2: Procesar código de autorización
 * Uso: php step2_process_code.php "URL_COMPLETA"
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Google\Client as GoogleClient;
use Google\Service\Drive;

if ($argc < 2) {
    echo "❌ ERROR: Falta la URL\n\n";
    echo "Uso: php step2_process_code.php \"URL_COMPLETA\"\n";
    echo "Ejemplo: php step2_process_code.php \"http://localhost/?code=4/0AZX...\"\n";
    exit(1);
}

$url = $argv[1];

// Extraer código de la URL
if (strpos($url, 'code=') === false) {
    echo "❌ ERROR: La URL no contiene un código de autorización\n";
    echo "Asegúrate de copiar la URL completa: http://localhost/?code=...\n";
    exit(1);
}

parse_str(parse_url($url, PHP_URL_QUERY), $params);
$authCode = $params['code'] ?? '';

if (empty($authCode)) {
    echo "❌ ERROR: No se pudo extraer el código\n";
    exit(1);
}

echo "========================================\n";
echo "   PASO 2: PROCESAR CÓDIGO\n";
echo "========================================\n\n";
echo "✅ Código extraído correctamente\n";
echo "🔄 Obteniendo token de acceso...\n\n";

$client = new GoogleClient();
$client->setApplicationName('EGRESAPP2');
$client->setScopes([Drive::DRIVE]);
$client->setAuthConfig(__DIR__ . '/../config/client_secret.json');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');
$client->setRedirectUri('http://localhost');

// Fix SSL error for Laragon
$httpClient = new \GuzzleHttp\Client(['verify' => false]);
$client->setHttpClient($httpClient);

try {
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
    
    if (array_key_exists('error', $accessToken)) {
        throw new Exception('Error: ' . $accessToken['error']);
    }
    
    $tokenPath = __DIR__ . '/../config/token.json';
    file_put_contents($tokenPath, json_encode($accessToken, JSON_PRETTY_PRINT));
    
    echo "========================================\n";
    echo "✅ ¡TOKEN GENERADO EXITOSAMENTE!\n";
    echo "========================================\n\n";
    echo "📁 Archivo guardado: $tokenPath\n\n";
    echo "Contenido del token:\n";
    echo json_encode($accessToken, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "========================================\n";
    echo "VERIFICAR:\n";
    echo "========================================\n";
    echo "Ejecuta: php ../verify_token_status.php\n\n";
    
} catch (Exception $e) {
    echo "========================================\n";
    echo "❌ ERROR AL GENERAR TOKEN\n";
    echo "========================================\n";
    echo "Mensaje: " . $e->getMessage() . "\n\n";
    exit(1);
}
