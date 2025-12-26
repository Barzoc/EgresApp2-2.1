<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}
include_once './layouts/header.php';
include_once './layouts/nav.php';
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Inicio</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">

                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-primary card-outline">
                        <div class="card-body">
                            <h5 class="card-title"><b>Visualizar gráficos</b></h5>
                            <br>
                            <p class="card-text">
                                Visualiza gráficos a partir de los estudiantes ingresados en el sistema.
                            </p>

                            <a href="./adm_dashboard.php" class="card-link">Gráficas</a>
                        </div>
                    </div>

                    <div class="card card-info">
                        <div class="card-body">
                            <h5 class="card-title"><b>Sincronización con Servidor Central</b></h5>
                            <p class="card-text">
                                Actualiza los datos desde el servidor central manualmente.
                            </p>
                            <button id="btn-sincronizar-inicio" class="btn btn-info">
                                <i class="fas fa-sync-alt"></i> Sincronizar BD Central
                            </button>
                            <small id="sync-status" class="d-block mt-2 text-muted"></small>
                        </div>
                    </div><!-- /.card -->
                </div>

                <!-- /.col-md-6 -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-0">Tabla de egresados</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Gestione los datos de los estudiantes egresados.</p>
                            <a href="./adm_egresado.php" class="btn btn-primary">Lista de egresados</a>
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
include_once './layouts/footer.php';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    const btnSincronizar = $('#btn-sincronizar-inicio');
    const syncStatus = $('#sync-status');
    
    // Obtener estado inicial
    function obtenerEstadoSync() {
        $.ajax({
            url: '../controlador/SincronizarController.php',
            type: 'POST',
            data: { accion: 'estado' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (response.modo === 'SINCRONIZADO') {
                        btnSincronizar.removeClass('btn-info').addClass('btn-success');
                        syncStatus.html('<i class="fas fa-check-circle text-success"></i> Última sincronización: ' + response.ultima_sincronizacion);
                    } else {
                        btnSincronizar.removeClass('btn-success').addClass('btn-warning');
                        syncStatus.html('<i class="fas fa-exclamation-triangle text-warning"></i> Modo local - Servidor central no accesible');
                    }
                }
            }
        });
    }
    
    // Función para sincronizar
    function sincronizarBD() {
        btnSincronizar.prop('disabled', true);
        btnSincronizar.html('<i class="fas fa-spinner fa-spin"></i> Sincronizando...');
        syncStatus.html('<i class="fas fa-clock text-info"></i> Conectando al servidor central...');
        
        $.ajax({
            url: '../controlador/SincronizarController.php',
            type: 'POST',
            data: { accion: 'sincronizar' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Sincronización Exitosa!',
                        html: `
                            <p><strong>Datos actualizados desde el servidor central</strong></p>
                            <p class="text-muted"><i class="far fa-clock"></i> ${response.ultima_sincronizacion}</p>
                        `,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    
                    btnSincronizar.removeClass('btn-warning btn-info').addClass('btn-success');
                    syncStatus.html('<i class="fas fa-check-circle text-success"></i> Última sincronización: ' + response.ultima_sincronizacion);
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Servidor Central No Accesible',
                        text: response.mensaje,
                        footer: 'El sistema continuará trabajando con los datos locales.',
                        confirmButtonText: 'Entendido'
                    });
                    
                    btnSincronizar.removeClass('btn-success btn-info').addClass('btn-warning');
                    syncStatus.html('<i class="fas fa-exclamation-triangle text-warning"></i> Modo local - Servidor central no accesible');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo completar la sincronización.',
                    confirmButtonText: 'Cerrar'
                });
                
                syncStatus.html('<i class="fas fa-times-circle text-danger"></i> Error en la sincronización');
            },
            complete: function() {
                btnSincronizar.prop('disabled', false);
                btnSincronizar.html('<i class="fas fa-sync-alt"></i> Sincronizar BD Central');
            }
        });
    }
    
    // Event handler
    btnSincronizar.on('click', function() {
        sincronizarBD();
    });
    
    // Cargar estado inicial
    obtenerEstadoSync();
});
</script>