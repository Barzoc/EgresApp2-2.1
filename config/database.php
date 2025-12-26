<?php
/**
 * Configuración de Base de Datos Centralizada - EGRESAPP2
 * 
 * MODO RECOMENDADO: 'auto'
 * - Intenta conectar al servidor central (Internet)
 * - Si falla, usa base de datos local automáticamente
 * - Permite trabajar offline sin problemas
 * 
 * ========================================
 * INSTRUCCIONES PARA CONFIGURAR
 * ========================================
 * 
 * EN EL PC SERVIDOR (donde está la BD central):
 * 1. Ejecutar: CONFIGURAR_SERVIDOR_CENTRAL.bat
 * 2. Anotar la IP pública o configurar DynDNS
 * 3. Ejecutar: db/setup_central_server.sql en MySQL
 * 
 * EN CADA PC CLIENTE:
 * 1. Copiar este archivo a config/database.php
 * 2. Cambiar 'CAMBIAR_POR_TU_DOMINIO' por tu dominio DynDNS o IP públicaStep Id: 52
 * 3. Cambiar 'CAMBIAR_CONTRASEÑA' por la contraseña real
 * 4. Ejecutar: php test_database_connection.php
 */

return [
    // ========================================
    // MODO DE OPERACIÓN
    // ========================================
    // 'auto'    → Intenta central, si falla usa local (RECOMENDADO)
    // 'central' → Solo servidor central (requiere Internet siempre)
    // 'local'   → Solo base de datos local (no sincroniza)
    
    'mode' => 'auto',
    
    // ========================================
    // CONFIGURACIÓN SERVIDOR CENTRAL
    // ========================================
    
    'central' => [
        // Opción 1: Usar dominio DynDNS (RECOMENDADO)
        'host' => 'CAMBIAR_POR_TU_DOMINIO.ddns.net',
        
        // Opción 2: Usar IP pública directa
        // 'host' => '200.123.45.67',
        
        // Opción 3: Si usas VPN, IP local del servidor
        // 'host' => '192.168.1.100',
        
        'port' => 3306,
        'database' => 'gestion_egresados',
        'username' => 'egresapp_remote',
        'password' => 'CAMBIAR_CONTRASEÑA',
        'charset' => 'utf8mb4',
        'timeout' => 10, // Más tiempo para conexiones por Internet
        
        // SSL/TLS (opcional pero recomendado para seguridad)
        'ssl' => [
            'enabled' => false, // Cambiar a true si configuraste SSL
            'ca_cert' => __DIR__ . '/ssl/ca-cert.pem',
            'verify_cert' => false, // true en producción
        ],
    ],
    
    // ========================================
    // CONFIGURACIÓN LOCAL (FALLBACK)
    // ========================================
    
    'local' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'gestion_egresados',
        'username' => 'root',
        'password' => '', // Laragon usa contraseña vacía por defecto
        'charset' => 'utf8mb4',
        'timeout' => 2,
    ],
    
    // ========================================
    // OPCIONES AVANZADAS
    // ========================================
    
    'options' => [
        // Número de intentos antes de fallar
        'retry_attempts' => 2,
        
        // Segundos entre reintentos
        'retry_delay' => 2,
        
        // Activar logging (útil para debugging)
        'enable_logging' => true,
        
        // Archivo de log
        'log_file' => __DIR__ . '/../logs/database.log',
        
        // Notificar cuando cambia el modo (central ↔ local)
        'notify_mode_change' => true,
    ],
];
