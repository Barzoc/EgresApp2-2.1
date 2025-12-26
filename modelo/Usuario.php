<?php
include_once 'Conexion.php';

class Usuario
{
    private $acceso;

    public function __construct()
    {
        $db = new Conexion();
        $this->acceso = $db->pdo;
        $this->asegurarColumnas();
    }

    private function asegurarColumnas()
    {
        try {
            $stmt = $this->acceso->query("SHOW COLUMNS FROM usuario LIKE 'created_at'");
            if (!$stmt->fetch()) {
                $this->acceso->exec("ALTER TABLE usuario ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
            }
        } catch (Throwable $e) {
            // En entornos sin permisos para ALTER se ignora y la columna seguirá sin mostrarse
        }
    }

    public function listar()
    {
        $sql = "SELECT id, nombre, email, created_at FROM usuario ORDER BY id DESC";
        $query = $this->acceso->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $email, $contrasena)
    {
        $sql = "SELECT id FROM usuario WHERE email = :email LIMIT 1";
        $query = $this->acceso->prepare($sql);
        $query->execute([':email' => $email]);

        if ($query->fetch()) {
            return ['status' => 'duplicado'];
        }

        $hashed = password_hash($contrasena, PASSWORD_DEFAULT);
        $insert = "INSERT INTO usuario (nombre, email, contraseña) VALUES (:nombre, :email, :pass)";
        $stmt = $this->acceso->prepare($insert);
        $ok = $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':pass' => $hashed,
        ]);

        return ['status' => $ok ? 'ok' : 'error'];
    }

    public function actualizar($id, $nombre, $email)
    {
        $sql = "SELECT id FROM usuario WHERE email = :email AND id <> :id LIMIT 1";
        $query = $this->acceso->prepare($sql);
        $query->execute([
            ':email' => $email,
            ':id' => $id,
        ]);
        if ($query->fetch()) {
            return ['status' => 'duplicado'];
        }

        $update = "UPDATE usuario SET nombre = :nombre, email = :email WHERE id = :id";
        $stmt = $this->acceso->prepare($update);
        $ok = $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':id' => $id,
        ]);
        return ['status' => $ok ? 'ok' : 'error'];
    }

    public function eliminar($id)
    {
        $stmt = $this->acceso->prepare('DELETE FROM usuario WHERE id = :id');
        $ok = $stmt->execute([':id' => $id]);
        return ['status' => $ok ? 'ok' : 'error'];
    }

    public function verificarContrasena($id, $contrasenaActual)
    {
        $stmt = $this->acceso->prepare('SELECT contraseña FROM usuario WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        return password_verify($contrasenaActual, $row['contraseña']);
    }

    public function actualizarContrasena($id, $contrasena)
    {
        $hashed = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $this->acceso->prepare('UPDATE usuario SET contraseña = :pass WHERE id = :id');
        $ok = $stmt->execute([
            ':pass' => $hashed,
            ':id' => $id,
        ]);
        return ['status' => $ok ? 'ok' : 'error'];
    }
}
