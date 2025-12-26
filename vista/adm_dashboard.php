<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}
include_once './layouts/header.php';
include_once './layouts/nav.php';
$titulo_pag = 'Estadísticas';
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
                    <button id="btn-sincronizar-central" class="btn btn-info float-right" title="Sincronizar con servidor central">
                        <i class="fas fa-sync-alt"></i> Sincronizar BD Central
                    </button>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="./inicio.php">Inicio</a></li>
                        <li class="breadcrumb-item active"><?php echo $titulo_pag; ?></li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!------------------ Main content ------------------------------>
    <!-- ----------------------------------------------------------->
    <!------------------ Main content ------------------------------>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Gráfico de Distribución por Género -->
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Distribución por Género</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="genderChart"
                                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            <div id="genderSummary" class="mt-3 text-center" style="font-size: 16px;">
                                <!-- Summary will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Gráfico de Cantidad de Egresados por Título -->
                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Cantidad de Egresados por Título</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="titleChart"
                                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Gráfico de Cantidad de Egresados por Año de Graduación -->
                <div class="col-md-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Cantidad de Egresados por Año de Graduación</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="graduacionChart"
                                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            <div id="graduacionSummary" class="mt-3" style="font-size: 14px;">
                                <!-- Summary will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col  -->

                <!-- Gráfico de Cantidad de Egresados por Mes -->
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Cantidad de Egresados por Mes</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="mesChart"
                                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            <div id="mesSummary" class="mt-3 text-center" style="font-size: 14px;">
                                <!-- Summary will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid-->
    </section>
    <!-- /.content -->

</div>
<!-- /.content-wrapper -->
<?php
include_once 'layouts/footer.php';
?>
<script>
// Script de sincronización manual
$(document).ready(function() {
    const btnSincronizar = $('#btn-sincronizar-central');
    
    // Función para sincronizar
    function sincronizarBDCentral() {
        btnSincronizar.prop('disabled', true);
        btnSincronizar.html('<i class="fas fa-spinner fa-spin"></i> Sincronizando...');
        
        $.ajax({
            url: '../controlador/SincronizarController.php',
            type: 'POST',
            data: { accion: 'sincronizar' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sincronización Exitosa',
                        html: `
                            <p><strong>Datos actualizados desde el servidor central</strong></p>
                            <p class="text-muted">Última sincronización: ${response.ultima_sincronizacion}</p>
                        `,
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        // Recargar gráficos con datos actualizados
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Modo Local',
                        text: response.mensaje,
                        confirmButtonText: 'Entendido'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo completar la sincronización. Revisa la conexión al servidor central.',
                    confirmButtonText: 'Cerrar'
                });
            },
            complete: function() {
                btnSincronizar.prop('disabled', false);
                btnSincronizar.html('<i class="fas fa-sync-alt"></i> Sincronizar BD Central');
            }
        });
    }
    
    // Event handler para el botón
    btnSincronizar.on('click', function() {
        sincronizarBDCentral();
    });
    
    // Mostrar estado inicial en tooltip
    $.ajax({
        url: '../controlador/SincronizarController.php',
        type: 'POST',
        data: { accion: 'estado' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const modo = response.modo === 'SINCRONIZADO' ? 
                    '<span class="text-success">✓ Sincronizado</span>' : 
                    '<span class="text-warning">⚠ Solo local</span>';
                
                btnSincronizar.attr('title', 
                    `Estado: ${response.modo}\nÚltima sync: ${response.ultima_sincronizacion || 'Nunca'}`
                );
                
                // Pequeño badge de estado
                if (response.modo === 'SINCRONIZADO') {
                    btnSincronizar.removeClass('btn-info').addClass('btn-success');
                }
            }
        }
    });
});
</script>
<script src="../assets/js/dashboard.js"></script>