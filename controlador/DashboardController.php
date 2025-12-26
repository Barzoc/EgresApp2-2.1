<?php
include_once '../modelo/Egresado.php';
include_once '../lib/GoogleDriveClient.php';
error_reporting(0);

$egresado = new Egresado();

$funcion = $_POST['funcion'] ?? '';

switch ($funcion) {
    case 'obtenerDatosGenero':
        $datos = $egresado->obtenerDatosGenero();
        echo json_encode($datos);
        break;
    
    case 'obtenerDatosTitulo':
        $datos = $egresado->obtenerDatosTitulo();
        echo json_encode($datos);
        break;
    
    case 'obtenerDatosGestion':
        $datos = $egresado->obtenerDatosGestion();
        echo json_encode($datos);
        break;
    
    case 'obtenerDatosFallecidos':
        $datos = $egresado->obtenerDatosFallecidos();
        echo json_encode($datos);
        break;
    
    case 'obtenerDatosGraduacion':
        $datos = $egresado->obtenerDatosGraduacion();
        echo json_encode($datos);
        break;
    
    case 'obtenerDatosMes':
        $datos = $egresado->obtenerDatosMes();
        echo json_encode($datos);
        break;
    
    case 'obtenerResumenRespaldo':
        $datos = $egresado->obtenerResumenRespaldo();

        $datos['drive_total_archivos'] = 0;
        $datos['drive_pendientes_bd'] = 0;
        $datos['drive_pendientes_local'] = 0;
        $datos['drive_inventario'] = [
            'habilitado' => false,
            'total_archivos' => 0,
            'carpetas' => [],
            'mensaje' => null,
            'timestamp' => null,
        ];

        try {
            $driveClient = new GoogleDriveClient();
            if ($driveClient->isEnabled()) {
                $summary = $driveClient->getFolderSummary();
                $datos['drive_inventario'] = $summary;
                $datos['drive_total_archivos'] = $summary['total_archivos'] ?? 0;
                $datos['drive_pendientes_bd'] = max(($datos['drive_total_archivos'] ?? 0) - ($datos['con_drive'] ?? 0), 0);
                $datos['drive_pendientes_local'] = max(($datos['drive_total_archivos'] ?? 0) - ($datos['con_local'] ?? 0), 0);
            } else {
                $datos['drive_inventario']['mensaje'] = 'Google Drive no está habilitado.';
            }
        } catch (Throwable $e) {
            $datos['drive_inventario']['mensaje'] = 'Error al consultar Drive: ' . $e->getMessage();
        }

        echo json_encode($datos);
        break;
}
?>
