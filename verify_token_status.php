<?php
date_default_timezone_set('America/Santiago');

echo "========================================\n";
echo "   VERIFICACIÓN TOKEN GOOGLE DRIVE\n";
echo "========================================\n\n";

$tokenPath = 'config/token.json';
if (!file_exists($tokenPath)) {
    echo "❌ ERROR: No existe el archivo config/token.json\n";
    echo "\n💡 SOLUCIÓN: Ejecuta RENOVAR_TOKEN_DRIVE.bat\n";
    exit(1);
}

$json = json_decode(file_get_contents($tokenPath), true);
if (!$json) {
    echo "❌ ERROR: El archivo token.json no es un JSON válido.\n";
    echo "\n💡 SOLUCIÓN: Elimina el archivo y ejecuta RENOVAR_TOKEN_DRIVE.bat\n";
    exit(1);
}

$created = $json['created'] ?? 0;
$expiresIn = $json['expires_in'] ?? 0;
$expiresAt = $created + $expiresIn;
$now = time();

echo "📁 Archivo: $tokenPath\n";
echo "📅 Creado:  " . date('Y-m-d H:i:s', $created) . "\n";
echo "⏰ Expira:  " . date('Y-m-d H:i:s', $expiresAt) . "\n";
echo "🕐 Ahora:   " . date('Y-m-d H:i:s', $now) . "\n";
echo "----------------------------------------\n";

// Verificar Access Token
if ($expiresAt > $now) {
    $remaining = $expiresAt - $now;
    $minutes = floor($remaining / 60);
    echo "✅ Access Token: ACTIVO (Vence en $minutes minutos)\n";
} else {
    $elapsed = $now - $expiresAt;
    $minutes = floor($elapsed / 60);
    echo "⚠️  Access Token: EXPIRADO (Venció hace $minutes minutos)\n";
    echo "ℹ️  El sistema intentará usar el Refresh Token automáticamente.\n";
}

// Verificar Refresh Token
echo "\n";
if (!isset($json['refresh_token'])) {
    echo "❌ Refresh Token: FALTA\n";
    echo "\n⚠️  PROBLEMA CRÍTICO: No hay Refresh Token.\n";
    echo "💡 SOLUCIÓN: Ejecuta RENOVAR_TOKEN_DRIVE.bat\n";
} else {
    echo "✅ Refresh Token: PRESENTE\n";
    
    if (isset($json['refresh_token_expires_in'])) {
        // Token de prueba/desarrollo (expira en 7 días)
        $refreshExpiresAt = $created + $json['refresh_token_expires_in'];
        $remainingSeconds = $refreshExpiresAt - $now;
        $remainingDays = floor($remainingSeconds / 86400);
        $remainingHours = floor(($remainingSeconds % 86400) / 3600);
        
        echo "⏰ Refresh Expira: " . date('Y-m-d H:i:s', $refreshExpiresAt) . "\n";
        
        if ($refreshExpiresAt > $now) {
            if ($remainingDays > 1) {
                echo "✅ ESTADO: VÁLIDO (Quedan $remainingDays días)\n";
            } else if ($remainingHours > 0) {
                echo "⚠️  ADVERTENCIA: Quedan solo $remainingHours horas\n";
                echo "💡 Renueva pronto ejecutando RENOVAR_TOKEN_DRIVE.bat\n";
            } else {
                echo "⚠️  ALERTA: Quedan menos de 1 hora\n";
                echo "💡 URGENTE: Ejecuta RENOVAR_TOKEN_DRIVE.bat AHORA\n";
            }
        } else {
            echo "❌ ESTADO: EXPIRADO - ¡ALERTA CRÍTICA!\n";
            echo "\n⚠️  El token de prueba expiró después de 7 días.\n";
            echo "💡 SOLUCIÓN: Ejecuta RENOVAR_TOKEN_DRIVE.bat\n";
        }
        
        echo "\nℹ️  NOTA: Estás usando un token de prueba/desarrollo.\n";
        echo "   Para producción, considera publicar la app en Google Cloud.\n";
    } else {
        echo "✅ ESTADO: PERMANENTE (Token de producción)\n";
        echo "   El Refresh Token no expira.\n";
    }
}

echo "\n========================================\n";
echo "RESUMEN\n";
echo "========================================\n";

$hasRefreshToken = isset($json['refresh_token']);
$isRefreshValid = true;
if (isset($json['refresh_token_expires_in'])) {
    $refreshExpiresAt = $created + $json['refresh_token_expires_in'];
    $isRefreshValid = $refreshExpiresAt > $now;
}

if ($hasRefreshToken && $isRefreshValid) {
    echo "✅ Google Drive está funcionando correctamente.\n";
    echo "   Puedes subir y sincronizar expedientes.\n";
} else {
    echo "❌ Google Drive NO está funcionando.\n";
    echo "\n🔧 SOLUCIÓN:\n";
    echo "   1. Ejecuta: RENOVAR_TOKEN_DRIVE.bat\n";
    echo "   2. Sigue las instrucciones en pantalla\n";
    echo "   3. Autoriza con tu cuenta de Google\n";
}

echo "\n";

