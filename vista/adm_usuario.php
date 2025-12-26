<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}
$titulo_pag = 'Usuarios';
include_once './layouts/header.php';
include_once './layouts/nav.php';
?>

<div class="content-wrapper">
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
        </div>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card card-secondary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Gestión de usuarios</h3>
                        <button id="btnNuevoUsuario" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus mr-1"></i>Nuevo usuario
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Desde aquí puedes crear, editar, eliminar usuarios del sistema y restablecer contraseñas.</p>
                        <div class="table-responsive">
                            <table id="tablaUsuarios" class="table table-bordered table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Fecha de creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal crear/editar usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" role="dialog" aria-labelledby="usuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="usuarioModalLabel">Nuevo usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formUsuario">
                <div class="modal-body">
                    <input type="hidden" id="usuario_id">
                    <div class="form-group">
                        <label for="usuario_nombre">Nombre completo</label>
                        <input type="text" class="form-control" id="usuario_nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="usuario_email">Correo electrónico</label>
                        <input type="email" class="form-control" id="usuario_email" required>
                    </div>
                    <div id="grupo-password" class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="usuario_pass">Contraseña</label>
                            <input type="password" class="form-control" id="usuario_pass">
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label for="usuario_pass_confirm">Confirmar contraseña</label>
                            <input type="password" class="form-control" id="usuario_pass_confirm">
                        </div>
                    </div>
                    <small class="text-muted" id="passwordHelp">La contraseña solo es obligatoria al crear usuarios.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal cambiar contraseña -->
<div class="modal fade" id="modalPassword" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordModalLabel">Cambiar contraseña</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formPassword">
                <div class="modal-body">
                    <input type="hidden" id="password_usuario_id">
                    <p class="text-muted">Ingresa primero la contraseña actual del usuario y luego la nueva contraseña.</p>
                    <div class="form-group">
                        <label for="password_actual">Contraseña actual</label>
                        <input type="password" class="form-control" id="password_actual" required>
                    </div>
                    <div class="form-group">
                        <label for="password_nueva">Nueva contraseña</label>
                        <input type="password" class="form-control" id="password_nueva" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmar">Confirmar contraseña</label>
                        <input type="password" class="form-control" id="password_confirmar" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once './layouts/footer.php';
?>
<script src="../assets/js/usuario.js?v=<?php echo time(); ?>"></script>
