<?php
require_once __DIR__ . '/Conexion.php';

class ConfiguracionCertificado
{
    private $pdo;
    private array $fallbackFirmante;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->pdo = $conexion->pdo;
        $this->ensureTable();
        $this->fallbackFirmante = $this->resolveFallbackFirmante();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS configuracion_certificado (
            clave VARCHAR(100) PRIMARY KEY,
            valor TEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $this->pdo->exec($sql);
    }

    public function obtener(string $clave, $default = null)
    {
        $stmt = $this->pdo->prepare('SELECT valor FROM configuracion_certificado WHERE clave = ? LIMIT 1');
        $stmt->execute([$clave]);
        $valor = $stmt->fetchColumn();
        return $valor !== false ? $valor : $default;
    }

    public function guardar(string $clave, string $valor): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO configuracion_certificado (clave, valor) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)');
        return $stmt->execute([$clave, $valor]);
    }

    public function obtenerTodo(): array
    {
        $stmt = $this->pdo->query('SELECT clave, valor FROM configuracion_certificado');
        $datos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $datos[$row['clave']] = $row['valor'];
        }
        return $datos;
    }

    public function obtenerFirmante(): array
    {
        $nombre = trim((string) $this->obtener('firmante_nombre', ''));
        $cargo = trim((string) $this->obtener('firmante_cargo', ''));

        if ($nombre === '') {
            $nombre = $this->fallbackFirmante['nombre'];
        }
        if ($cargo === '') {
            $cargo = $this->fallbackFirmante['cargo'];
        }

        return ['nombre' => $nombre, 'cargo' => $cargo];
    }

    public function guardarFirmante(string $nombre, string $cargo): bool
    {
        $nombre = trim($nombre);
        $cargo = trim($cargo);
        $okNombre = $this->guardar('firmante_nombre', $nombre);
        $okCargo = $this->guardar('firmante_cargo', $cargo);
        return $okNombre && $okCargo;
    }

    private function resolveFallbackFirmante(): array
    {
        $configPath = __DIR__ . '/../config/certificado.php';
        $config = is_file($configPath) ? require $configPath : [];

        $fallbackNombre = trim($config['firmante_nombre'] ?? $config['rector'] ?? 'RECTOR(A) DEL ESTABLECIMIENTO');
        $fallbackCargo = trim($config['firmante_cargo'] ?? 'RECTOR(A)');

        if ($fallbackNombre === '') {
            $fallbackNombre = 'RECTOR(A) DEL ESTABLECIMIENTO';
        }
        if ($fallbackCargo === '') {
            $fallbackCargo = 'RECTOR(A)';
        }

        return [
            'nombre' => $fallbackNombre,
            'cargo' => $fallbackCargo,
        ];
    }
}
