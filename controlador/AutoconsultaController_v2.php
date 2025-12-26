<?php
require_once __DIR__ . '/../modelo/Conexion.php';
require_once __DIR__ . '/../modelo/Utils.php';

class AutoconsultaController {
    private $db;

    public function __construct() {
        $this->db = new Conexion();
    }

    public function consultarCertificado($rut) {
        if (empty($rut)) {
            return [
                'success' => false,
                'message' => 'RUT no proporcionado'
            ];
        }

        // Validar formato del RUT
        if (!Utils::validarRut($rut)) {
            return [
                'success' => false,
                'valid' => false,
                'message' => 'RUT inválido'
            ];
        }

        try {
            $clean = preg_replace('/[^0-9kK]/', '', $rut);
            
            $sql = "SELECT 
                    e.identificacion,
                    e.nombreCompleto,
                    e.carnet,
                    e.correoPrincipal as correo,
                    e.tituloObtenido,
                    e.fechaEntregaCertificado,
                    e.numeroCertificado
                FROM egresado e
                WHERE REPLACE(REPLACE(UPPER(e.carnet),'.',''),'-','') = ?
                LIMIT 1";

            $stmt = $this->db->pdo->prepare($sql);
            $stmt->execute([$clean]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $certificado_url = null;
                if (!empty($result['rutaCertificado'])) {
                    $certificado_url = './certificados/' . $result['rutaCertificado'];
                }

                // Determinar título/fecha/número priorizando los valores OCR almacenados en egresado
                $tituloPrincipal = $result['tituloObtenido'] ?? '';
                $fechaGrado = $result['fechaEntregaCertificado'] ?? null;
                $numeroCertificado = $result['numeroCertificado'] ?? null;

                return [
                    'success' => true,
                    'valid' => true,
                    'nombre' => $result['nombreCompleto'] ?? '',
                    'correo' => $result['correo'] ?? '',
                    'titulo' => $tituloPrincipal,
                    'fechaTitulo' => $fechaGrado,
                    'numeroRegistro' => $numeroCertificado,
                    'certificado_url' => $certificado_url
                ];
            }

            return [
                'success' => false,
                'valid' => true,
                'message' => 'No se encontraron registros para el RUT proporcionado'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'valid' => true,
                'message' => 'RUT válido, pero error al consultar base de datos: ' . $e->getMessage()
            ];
        }
    }
}

// Si se llama directamente como endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $rut = $_POST['rut'] ?? '';
    $controller = new AutoconsultaController();
    $response = $controller->consultarCertificado($rut);
    
    echo json_encode($response);
    exit;
}