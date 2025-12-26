<?php
/**
 * Clase de Conexión Dual con Auto-Sincronización
 * 
 * Comportamiento:
 * 1. Al instanciar, intenta conectarse al SERVIDOR_CENTRAL
 * 2. Si conecta, ejecuta sincronización automática (Central -> Local)
 * 3. Siempre trabaja con la base de datos LOCAL
 * 4. Registra logs de sincronización
 */

class ConexionDual extends PDO {
    private $motor = 'mysql';
    private $charset = 'utf8';
    
    // Configuración SERVIDOR CENTRAL
    private $central_host = '26.234.93.144'; // IP Radmin VPN
    private $central_user = 'remoto';
    private $central_pass = 'Sistemas2025!';
    private $central_db = 'gestion_egresados';
    private $central_port = '3306';
    
    // Configuración LOCAL
    private $local_host = 'localhost';
    private $local_user = 'root';
    private $local_pass = '';
    private $local_db = 'gestion_egresados';
    private $local_port = '3306';
    
    private $atributos = [
        PDO::ATTR_CASE => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_EMPTY_STRING,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    ];
    
    public $pdo = null;
    private $modo_actual = 'LOCAL';
    private $ultima_sincronizacion = null;
    private $log_file = 'logs/sincronizacion.log';
    
    public function __construct() {
        // Asegurar que existe el directorio de logs
        $log_dir = dirname(__DIR__ . '/' . $this->log_file);
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0777, true);
        }
        
        // Intentar sincronización automática
        $this->sincronizarDesdeServidor();
        
        // SIEMPRE conectar a la base de datos LOCAL para trabajar
        $this->conectarLocal();
    }
    
    /**
     * Intenta sincronizar datos desde el servidor central
     */
    private function sincronizarDesdeServidor() {
        try {
            $this->log("Iniciando sincronización automática...");
            
            // Intentar conectar al servidor central
            $pdo_central = $this->intentarConexionCentral();
            
            if ($pdo_central) {
                $this->log("✓ Conexión exitosa al SERVIDOR CENTRAL");
                $this->modo_actual = 'SINCRONIZADO';
                
                // Ejecutar sincronización
                $this->copiarDatosCentralALocal($pdo_central);
                
                $this->ultima_sincronizacion = date('Y-m-d H:i:s');
                $this->log("✓ Sincronización completada: " . $this->ultima_sincronizacion);
                
                // Cerrar conexión central
                $pdo_central = null;
            } else {
                $this->log("⚠ No se pudo conectar al servidor central. Trabajando solo con datos locales.");
                $this->modo_actual = 'LOCAL_SOLAMENTE';
            }
        } catch (Exception $e) {
            $this->log("✗ Error en sincronización: " . $e->getMessage());
            $this->modo_actual = 'LOCAL_SOLAMENTE';
        }
    }
    
    /**
     * Intenta conectar al servidor central
     * @return PDO|null
     */
    private function intentarConexionCentral() {
        try {
            $dns = "$this->motor:host=$this->central_host;port=$this->central_port;dbname=$this->central_db;charset=$this->charset";
            
            // Timeout de 3 segundos para no detener la carga de la plataforma
            $opciones = array_merge($this->atributos, [
                PDO::ATTR_TIMEOUT => 3,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $pdo = new PDO($dns, $this->central_user, $this->central_pass, $opciones);
            return $pdo;
        } catch (Exception $e) {
            $this->log("No se pudo conectar al central: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Conecta a la base de datos LOCAL
     */
    private function conectarLocal() {
        try {
            $dns = "$this->motor:host=$this->local_host;port=$this->local_port;dbname=$this->local_db;charset=$this->charset";
            $this->pdo = new PDO($dns, $this->local_user, $this->local_pass, $this->atributos);
            $this->log("✓ Conectado a base de datos LOCAL");
        } catch (Exception $e) {
            $this->log("✗ ERROR CRÍTICO: No se pudo conectar a BD local: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Copia datos desde el servidor central a la base local
     * @param PDO $pdo_central Conexión al servidor central
     */
    private function copiarDatosCentralALocal($pdo_central) {
        try {
            // Conectar temporalmente a la BD local para sincronización
            $dns_local = "$this->motor:host=$this->local_host;port=$this->local_port;dbname=$this->local_db;charset=$this->charset";
            $pdo_local = new PDO($dns_local, $this->local_user, $this->local_pass, $this->atributos);
            
            // 1. Sincronizar tabla EGRESADO (solo registros del central: ID < 1000000)
            $this->log("Sincronizando tabla egresado...");
            $sql_central = "SELECT * FROM egresado WHERE identificacion < 1000000";
            $stmt = $pdo_central->prepare($sql_central);
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $count = 0;
            foreach ($registros as $reg) {
                // Usar REPLACE INTO para actualizar o insertar
                $campos = array_keys($reg);
                $placeholders = array_map(function($c) { return ":$c"; }, $campos);
                
                $sql_insert = "REPLACE INTO egresado (" . implode(', ', $campos) . ") 
                               VALUES (" . implode(', ', $placeholders) . ")";
                
                $stmt_insert = $pdo_local->prepare($sql_insert);
                foreach ($reg as $campo => $valor) {
                    $stmt_insert->bindValue(":$campo", $valor);
                }
                $stmt_insert->execute();
                $count++;
            }
            $this->log("  → {$count} registros sincronizados en egresado");
            
            // 2. Sincronizar tabla TITULO
            $this->log("Sincronizando tabla titulo...");
            $sql_titulos = "SELECT * FROM titulo";
            $stmt = $pdo_central->prepare($sql_titulos);
            $stmt->execute();
            $titulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $count = 0;
            foreach ($titulos as $titulo) {
                $sql_insert = "REPLACE INTO titulo (id, nombre) VALUES (:id, :nombre)";
                $stmt_insert = $pdo_local->prepare($sql_insert);
                $stmt_insert->execute([
                    ':id' => $titulo['id'],
                    ':nombre' => $titulo['nombre']
                ]);
                $count++;
            }
            $this->log("  → {$count} registros sincronizados en titulo");
            
            // 3. Sincronizar tabla CONFIGURACION_CERTIFICADO
            $this->log("Sincronizando configuración de certificados...");
            $sql_config = "SELECT * FROM configuracion_certificado LIMIT 1";
            $stmt = $pdo_central->prepare($sql_config);
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($config) {
                $campos = array_keys($config);
                $placeholders = array_map(function($c) { return ":$c"; }, $campos);
                
                $sql_insert = "REPLACE INTO configuracion_certificado (" . implode(', ', $campos) . ") 
                               VALUES (" . implode(', ', $placeholders) . ")";
                
                $stmt_insert = $pdo_local->prepare($sql_insert);
                foreach ($config as $campo => $valor) {
                    $stmt_insert->bindValue(":$campo", $valor);
                }
                $stmt_insert->execute();
                $this->log("  → Configuración sincronizada");
            }
            
            $pdo_local = null;
            $this->log("✓ Sincronización de datos completada exitosamente");
            
        } catch (Exception $e) {
            $this->log("✗ Error copiando datos: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Registra mensajes en el log
     * @param string $mensaje
     */
    private function log($mensaje) {
        $timestamp = date('Y-m-d H:i:s');
        $linea = "[$timestamp] $mensaje\n";
        
        // Escribir en archivo
        @file_put_contents(__DIR__ . '/../' . $this->log_file, $linea, FILE_APPEND);
        
        // También escribir en error_log de PHP para debugging
        error_log("ConexionDual: $mensaje");
    }
    
    /**
     * Obtiene el modo de conexión actual
     * @return string 'SINCRONIZADO' | 'LOCAL_SOLAMENTE'
     */
    public function getModoConexion() {
        return $this->modo_actual;
    }
    
    /**
     * Obtiene la fecha/hora de la última sincronización
     * @return string|null
     */
    public function getUltimaSincronizacion() {
        return $this->ultima_sincronizacion;
    }
    
    /**
     * Lee el contenido del log de sincronización
     * @param int $lineas Número de últimas líneas a leer
     * @return array
     */
    public function leerLog($lineas = 50) {
        $log_path = __DIR__ . '/../' . $this->log_file;
        if (!file_exists($log_path)) {
            return [];
        }
        
        $contenido = file($log_path);
        return array_slice($contenido, -$lineas);
    }
}
