<?php
/**
 * PASO 1: Generar enlace de autorización
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Google\Client as GoogleClient;
use Google\Service\Drive;

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

$authUrl = $client->createAuthUrl();

echo "========================================\n";
echo "   PASO 1: GENERAR ENLACE\n";
echo "========================================\n\n";
echo "Enlace de autorización:\n\n";
echo "$authUrl\n\n";
echo "========================================\n";
echo "INSTRUCCIONES:\n";
echo "========================================\n";
echo "1. Copia todo el enlace de arriba\n";
echo "2. Pégalo en tu navegador\n";
echo "3. Inicia sesión con Google\n";
echo "4. Autoriza la aplicación EGRESAPP2\n";
echo "5. Te redirigirá a http://localhost/?code=...\n";
echo "6. Copia TODA esa URL completa\n";
echo "7. Pásame la URL completa por chat\n";
echo "========================================\n\n";
