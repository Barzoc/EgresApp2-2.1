<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}
$titulo_pag = 'Escáner QR (Próximamente)';
include_once './layouts/header.php';
include_once './layouts/nav.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?php echo $titulo_pag; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="./inicio.php">Inicio</a></li>
                        <li class="breadcrumb-item active"><?php echo $titulo_pag; ?></li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <section class="content">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Escáner QR (Próximamente)</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5 class="mb-1"><i class="fas fa-info-circle mr-2"></i>Funcionalidad en desarrollo</h5>
                    <p class="mb-0">El lector de códigos QR se encuentra desactivado temporalmente mientras trabajamos en una implementación mejorada. Estará disponible en una próxima actualización.</p>
                </div>
                <p class="text-muted mb-0">Si necesitas registrar egresados, utiliza las opciones de importación o carga manual desde el menú principal.</p>
            </div>
        </div>
    </section>
</div>

<?php
include_once 'layouts/footer.php';
?>

<!-- Scripts del escáner deshabilitados temporalmente -->
