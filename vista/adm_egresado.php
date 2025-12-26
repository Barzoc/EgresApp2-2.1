<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}
$titulo_pag = 'Egresados';
include_once './layouts/header.php';
include_once './layouts/nav.php';
?>

<style>
    .expediente-links a {
        font-size: 0.95rem;
        font-weight: 600;
        color: #0d6efd;
    }

    .expediente-links a+a {
        margin-top: 0.1rem;
    }

    .expediente-links a:hover {
        text-decoration: underline;
    }

    .fuentes-resumen {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .fuente-chip {
        flex: 1 1 160px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.75rem;
        border: 1px solid #e1e4e8;
    }

    .fuente-chip span.badge {
        font-size: 0.85rem;
    }

    #drive_folder_results {
        border: 1px solid #dee2e6;
        border-radius: 12px;
        background: #fdfdfd;
    }

    .drive-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f0f2f5;
    }

    .drive-item:last-child {
        border-bottom: none;
    }

    .drive-item .drive-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #1f2d3d;
        word-break: break-word;
    }

    .drive-item .drive-meta {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .drive-item .drive-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.35rem;
    }

    .drive-item .badge-status {
        font-size: 0.7rem;
        letter-spacing: 0.02em;
    }
</style>

<!------------------------------------------------------>
<!--   Ventana Modal para CREAR Y EDITAR              -->
<!------------------------------------------------------>
<div class="modal fade" id="crear" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span id="tit_ven">Crear Egresado</span> </h5>
                <button data-dismiss="modal" arial-label="close" class="close">
                    <span arial-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success text-center" id="add" style='display:none;'>
                    <i class="fa fa-check-circle m-1"> Operación realizada correctamente</i>
                </div>
                <div class="alert alert-danger text-center" id="noadd" style='display:none;'>
                    <i class="fa fa-times-circle m-1"> El egresado ya existe</i>
                </div>
                <div class="alert alert-info d-flex justify-content-between align-items-center"
                    id="imported_pdf_preview" style="display:none;">
                    <span><i class="fa fa-file-pdf mr-1"></i>Se importó un expediente PDF para revisión.</span>
                    <a id="imported_pdf_preview_link" href="#" target="_blank" class="font-weight-bold">Abrir PDF</a>
                </div>
                <form id="form-crear">
                    <input type="hidden" id="identificacion_hidden" name="identificacion">
                    <div class="form-group">
                        <label for="nombreCompleto">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombreCompleto" name="nombreCompleto">
                    </div>
                    <div class="form-group" style="display:none;">
                        <label for="telResidencia">Tel Residencia</label>
                        <input type="text" class="form-control" id="telResidencia" name="telResidencia">
                    </div>
                    <div class="form-group" style="display:none;">
                        <label for="telAlternativo">Tel Alternativo</label>
                        <input type="text" class="form-control" id="telAlternativo" name="telAlternativo">
                    </div>
                    <div class="form-group" style="display:none;">
                        <label for="correoSecundario">Correo Secundario</label>
                        <input type="email" class="form-control" id="correoSecundario" name="correoSecundario">
                    </div>
                    <div class="form-group">
                        <label for="correoPrincipal">Correo Principal</label>
                        <input type="email" class="form-control" id="correoPrincipal" name="correoPrincipal">
                    </div>
                    <div class="form-group">
                        <label for="carnet">Carnet</label>
                        <input type="text" class="form-control" id="carnet" name="carnet">
                    </div>
                    <div class="form-group">
                        <label for="sexo">Sexo</label>
                        <select class="form-control" id="sexo" name="sexo">
                            <option value="">Seleccione...</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo"
                            placeholder="Título del egresado">
                    </div>
                    <div class="form-group">
                        <label for="numeroCertificado">Número de Certificado</label>
                        <input type="text" class="form-control" id="numeroCertificado" name="numeroCertificado"
                            placeholder="N° certificado">
                    </div>
                    <div class="form-group">
                        <label for="fechaGrado">Fecha de Graduación</label>
                        <input type="text" class="form-control" id="fechaGrado" name="fechaGrado"
                            placeholder="Ej: 25/06/2010">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn bg-gradient-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-------------------------------------------------->
<!-- FIN Ventana Modal para el crear              -->
<!-------------------------------------------------->

<!-------------------------------------------------->
<!--   Ventana Modal para subir Expediente (PDF)  -->
<!-------------------------------------------------->
<div class="modal fade" id="cambiarExpediente" tabindex="-1" role="dialog" aria-labelledby="expedienteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expedienteModalLabel">Subir Expediente (PDF)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <b id="nombre_expediente"></b>
                    <div class="expediente-links d-flex flex-column align-items-center mt-2">
                        <a id="link_expediente_local" href="#" target="_blank" style="display:none;">Ver copia local</a>
                        <a id="link_expediente_drive" href="#" target="_blank" style="display:none;">Ver en Google
                            Drive</a>
                    </div>
                </div>
                <div class="alert alert-success text-center" id="updateexpediente" style='display:none;'>
                    <i class="fa fa-check-circle m-1"> Expediente subido y datos extraídos correctamente</i>
                </div>
                <div class="alert alert-danger text-center" id="noupdateexpediente" style='display:none;'>
                    <i class="fa fa-times-circle m-1"> Error al procesar el expediente</i>
                </div>
                <form id="form-expediente" enctype="multipart/form-data">
                    <div class="form-group" id="expediente-upload-group">
                        <label for="file">Seleccionar Expediente (PDF)</label>
                        <input type="file" name="file" accept="application/pdf" class="form-control-file" required>
                    </div>


                    <div class="datos-extraidos" style="display:none;">
                        <hr>
                        <h5>Datos Extraídos del PDF</h5>
                        <div class="form-group">
                            <label for="rut_extraido">RUT</label>
                            <input type="text" class="form-control" id="rut_extraido" name="rut_extraido" readonly>
                        </div>
                        <div class="form-group">
                            <label for="nombre_extraido">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre_extraido" name="nombre_extraido"
                                readonly>
                        </div>
                        <div class="form-group">
                            <label for="fecha_egreso_extraido">Fecha de Egresado</label>
                            <input type="text" class="form-control" id="fecha_egreso_extraido"
                                name="fecha_egreso_extraido" readonly>
                        </div>
                        <div class="form-group">
                            <label for="numero_certificado_extraido">Número de Certificado</label>
                            <input type="text" class="form-control" id="numero_certificado_extraido"
                                name="numero_certificado_extraido" readonly>
                        </div>
                        <div class="form-group">
                            <label for="titulo_extraido">Título Obtenido</label>
                            <input type="text" class="form-control" id="titulo_extraido" name="titulo_extraido"
                                readonly>
                        </div>
                        <div class="alert alert-warning text-center" id="noReconocido" style="display:none;">
                            <i class="fa fa-exclamation-triangle m-1"></i> No se reconocen datos, ¿desea agregarlos
                            manualmente?
                            <br>
                            <button type="button" class="btn btn-sm btn-warning mt-2" id="btnManual">Agregar
                                manualmente</button>
                        </div>
                    </div>

                    <input type="hidden" name="funcion" id="funcion_expediente" value="subir_expediente">
                    <input type="hidden" name="id_expediente" id="id_expediente">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-outline-primary" id="btn-habilitar-edicion">Editar</button>
                        <button type="submit" class="btn btn-primary" id="btn-guardar-expediente">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-------------------------------------------------->
<!-- FIN Ventana Modal para Expediente  -->
<!-------------------------------------------------->

<!-------------------------------------------------->
<!--   Ventana Modal para importar egresado desde expediente -->
<!-------------------------------------------------->
<div class="modal fade" id="importarExpedienteModal" tabindex="-1" role="dialog"
    aria-labelledby="importarExpedienteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importarExpedienteLabel">Importar desde Expediente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Selecciona al menos una fuente. Puedes subir un PDF local o indicar un
                    archivo existente en Google Drive.</p>
                <!-- Chips de selección eliminados por solicitud del usuario -->

                <form id="form-importar-expediente" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="import_file">Archivo PDF local (opcional)</label>
                        <input type="file" class="form-control-file" id="import_file" accept="application/pdf">
                    </div>
                    <div class="text-center text-muted my-2">— o —</div>
                    <input type="hidden" id="import_drive_input">
                    <input type="hidden" id="import_local_existing_path">

                    <!-- Selector de carpetas predefinidas -->
                    <div class="form-group">
                        <label for="drive_folder_selector">
                            <i class="fab fa-google-drive mr-1"></i>Carpeta de Google Drive
                        </label>
                        <select class="form-control" id="drive_folder_selector">
                            <option value="">-- Cargando carpetas... --</option>
                        </select>
                        <small class="form-text text-muted">
                            Selecciona una carpeta predefinida (Administración, Contabilidad, etc.)
                        </small>
                    </div>

                    <!-- Input manual como alternativa -->
                    <div class="form-group">
                        <label for="import_drive_folder_id">
                            <i class="fas fa-link mr-1"></i>O ingresa ID/Enlace manualmente
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="import_drive_folder_id"
                                placeholder="ID de la carpeta en Google Drive">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="btn_drive_listar">Listar
                                    expedientes</button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Pega el ID o enlace de la carpeta (por ejemplo, la carrera)
                            para ver los archivos disponibles.</small>
                    </div>
                    <div class="drive-folder-results mb-3" id="drive_folder_results"
                        style="display:none; max-height: 260px; overflow:auto;">
                        <div class="p-2 text-muted small">Ingresa un ID de carpeta y presiona "Listar expedientes".
                        </div>
                    </div>
                    <div class="alert alert-light border" id="import_result_message" style="display:none;"></div>
                    <div class="modal-footer px-0">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" id="btn-importar-expediente-submit">Procesar e
                            Importar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-------------------------------------------------->
<!-- FIN Modal importar egresado -->
<!-------------------------------------------------->

<!-------------------------------------------------->
<!-- Modal para seleccionar firmante antes de generar -->
<!-------------------------------------------------->
<div class="modal fade" id="firmanteSeleccionModal" tabindex="-1" role="dialog" aria-labelledby="firmanteSeleccionLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="firmanteSeleccionLabel">Seleccionar firmante del certificado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-firmante-seleccion">
                <div class="modal-body">
                    <input type="hidden" id="firmante_modal_rut">
                    <p class="mb-3">
                        <strong>Egresado:</strong> <span id="firmante_modal_alumno" class="text-primary">—</span>
                    </p>
                    <div class="btn-group btn-group-toggle w-100 mb-3" data-toggle="buttons">
                        <label class="btn btn-outline-primary active" id="option_titular_label">
                            <input type="radio" name="tipo_firmante" id="option_titular" value="titular"
                                autocomplete="off" checked> Titular
                        </label>
                        <label class="btn btn-outline-primary" id="option_suplente_label">
                            <input type="radio" name="tipo_firmante" id="option_suplente" value="suplente"
                                autocomplete="off"> Suplente
                        </label>
                    </div>

                    <div id="firmante_titular_info" class="alert alert-info text-center mb-3">
                        <p class="mb-0">Firmará: <strong id="info_titular_nombre"></strong></p>
                        <small id="info_titular_cargo"></small>
                    </div>

                    <div id="firmante_inputs_wrapper" style="display:none;">
                        <div class="form-group">
                            <label for="firmante_modal_nombre" id="label_firmante_nombre">Nombre del firmante</label>
                            <input type="text" class="form-control" id="firmante_modal_nombre"
                                placeholder="Ej: María Pérez">
                        </div>
                        <div class="form-group">
                            <label for="firmante_modal_cargo" id="label_firmante_cargo">Cargo</label>
                            <input type="text" class="form-control" id="firmante_modal_cargo" placeholder="Ej: Rectora">
                        </div>
                    </div>
                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-user-check mr-1"></i>Esta información se imprimirá en el certificado PDF.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="firmante_modal_submit">
                        <i class="fas fa-file-signature mr-1"></i>Generar certificado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-------------------------------------------------->
<!-- FIN Modal firmante -->
<!-------------------------------------------------->

<!-------------------------------------------------->
<!-- Modal configuración de firmante titular          -->
<!-------------------------------------------------->
<div class="modal fade" id="firmanteConfigModal" tabindex="-1" role="dialog" aria-labelledby="firmanteConfigLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="firmanteConfigLabel"><i class="fas fa-user-edit mr-2"></i>Configurar titular
                    de certificados</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-config-firmante">
                <div class="modal-body">
                    <p class="text-muted small">Ingresa los datos que se usarán por defecto al generar certificados.
                        Siempre podrás cambiarlos para un egresado específico.</p>
                    <div class="form-group">
                        <label for="firmante_default_nombre">Nombre del firmante</label>
                        <input type="text" class="form-control" id="firmante_default_nombre"
                            placeholder="Ej: MARÍA PÉREZ" required>
                    </div>
                    <div class="form-group">
                        <label for="firmante_default_cargo">Cargo</label>
                        <input type="text" class="form-control" id="firmante_default_cargo" placeholder="Ej: Rectora"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="firmante_config_guardar"><i
                            class="fas fa-save mr-1"></i>Guardar titular</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-------------------------------------------------->
<!-- FIN Modal configuración firmante -->
<!-------------------------------------------------->

<!-------------------------------------------------->
<!--   Ventana Modal para editar egresado (sin PDF)  -->
<!-------------------------------------------------->
<div class="modal fade" id="editarExpedienteModal" tabindex="-1" role="dialog" aria-labelledby="editarExpedienteLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarExpedienteLabel">Editar egresado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <b id="edit_nombre_expediente"></b>
                    <div class="expediente-links d-flex flex-column align-items-center mt-2">
                        <a id="edit_link_expediente_local" href="#" target="_blank" style="display:none;">Ver copia
                            local</a>
                        <a id="edit_link_expediente_drive" href="#" target="_blank" style="display:none;">Ver en Google
                            Drive</a>
                    </div>
                </div>
                <form id="form-editar-egresado">
                    <input type="hidden" id="edit_id_expediente" name="id_expediente">
                    <div class="form-group">
                        <label for="edit_rut">RUT</label>
                        <input type="text" class="form-control" id="edit_rut" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_nombre">Nombre Completo</label>
                        <input type="text" class="form-control" id="edit_nombre" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_correo">Correo Principal</label>
                        <input type="email" class="form-control" id="edit_correo" placeholder="Opcional" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_sexo">Sexo</label>
                        <select class="form-control" id="edit_sexo" disabled>
                            <option value="">Seleccione...</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_fecha_egreso">Fecha de Egresado</label>
                        <input type="text" class="form-control" id="edit_fecha_egreso" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_numero_certificado">Número de Certificado</label>
                        <input type="text" class="form-control" id="edit_numero_certificado" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_titulo">Título Obtenido</label>
                        <input type="text" class="form-control" id="edit_titulo" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_fecha_entrega">Fecha de Entrega de Certificado</label>
                        <input type="text" class="form-control" id="edit_fecha_entrega" placeholder="Ej: 2010-06-08"
                            readonly>
                    </div>
                    <div class="alert alert-warning text-center" id="editExpedienteAlert" style="display:none;">
                        <i class="fa fa-exclamation-triangle m-1"></i> No se reconocen datos, ¿desea agregarlos
                        manualmente?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-outline-primary"
                            id="btn-editar-egresado-campos">Editar</button>
                        <button type="submit" class="btn btn-primary" id="btn-guardar-egresado-modal">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-------------------------------------------------->
<!-------- Modal para agregar Titulo Egresado ------>
<!-------------------------------------------------->
<div class="modal fade" id="modalTituloEgresado" tabindex="-1" role="dialog" aria-labelledby="tituloEgresadoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloEgresadoModalLabel">Agregar Título Egresado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="existe" class="alert alert-warning" role="alert" style="display:none;">
                    <strong>El título ya existe para este egresado.</strong>
                </div>
                <form id="formTituloEgresado">
                    <div class="form-group">
                        <label for="egresado">Egresado</label>
                        <select class="form-control" id="egresado" name="egresado"></select>
                    </div>
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <select class="form-control" id="titulo" name="titulo"></select>
                    </div>
                    <div class="form-group">
                        <label for="fechaGrado">Fecha de Graduación</label>
                        <input type="date" class="form-control" id="fechaGrado" name="fechaGrado">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-------------------------------------------------->
<!-------- Modal para agregar Titulo Egresado ------>
<!-------------------------------------------------->


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h1 class="d-inline-block mr-2"><?php echo $titulo_pag; ?></h1>
                        <button class="btn-crear btn bg-gradient-primary btn-sm m-1" data-toggle="modal"
                            data-target="#crear">Crear Egresado Manual</button>
                        <button class="btn-importar-expediente btn btn-info btn-sm m-1" type="button">Importar desde
                            Expediente</button>
                        <button class="btn-subir-expediente btn btn-secondary btn-sm m-1" type="button">Subir
                            Expediente</button>
                    </div>

                    <!-- Widget Total Egresados (Movido aquí) -->
                    <div class="text-center mx-3 my-2 my-lg-0">
                        <div class="card card-outline card-primary mb-0 shadow-sm" style="min-width: 140px;">
                            <div class="card-body p-2">
                                <small class="text-muted text-uppercase font-weight-bold"
                                    style="font-size: 0.7rem;">Total Egresados</small>
                                <h4 class="mb-0 font-weight-bold text-primary" id="respaldo_total">0</h4>
                            </div>
                        </div>
                    </div>

                    <div>
                        <ol class="breadcrumb float-sm-right m-0">
                            <li class="breadcrumb-item"><a href="./inicio.php">Inicio</a></li>
                            <li class="breadcrumb-item active"><?php echo $titulo_pag; ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!------------------ Main content ------------------------------>
    <!-- ----------------------------------------------------------->
    <!------------------ Main content ------------------------------>
    <section class="content">
        <!-- Panel de respaldo eliminado y movido al header -->

        <div class="row mt-3" id="firmante-config-section">
            <div class="col-lg-5">
                <div class="card card-outline card-warning h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><i class="fas fa-user-pen mr-2"></i>Firmante titular</h3>
                        <span class="badge badge-pill badge-primary" id="firmante_config_estado">Cargando
                            datos...</span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div class="mb-3">
                                <small class="text-muted text-uppercase">Nombre</small>
                                <h5 class="mb-0" id="firmante_resumen_nombre">—</h5>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted text-uppercase">Cargo</small>
                                <h5 class="mb-0" id="firmante_resumen_cargo">—</h5>
                            </div>
                        </div>
                        <p class="text-muted small mb-3">Estos datos se usan como valores iniciales al generar
                            certificados.</p>
                        <button type="button" class="btn btn-sm btn-warning" id="firmante_config_open"><i
                                class="fas fa-edit mr-1"></i>Actualizar titular</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 mt-3 mt-lg-0">
                <div class="card card-outline card-light">
                    <div class="card-body">
                        <h5 class="text-muted"><i class="fas fa-info-circle mr-1"></i>¿Cómo funciona?</h5>
                        <ul class="small pl-3 mb-0">
                            <li>Al generar un certificado se abrirá un cuadro para confirmar o reemplazar al firmante.
                            </li>
                            <li>Si la autoridad titular no está disponible, ingresa temporalmente el nombre y cargo de
                                quien firmará.</li>
                            <li>Este panel solo actualiza el valor por defecto; no modifica certificados anteriores.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Sección de Cita Motivacional -->
                <div class="card card-outline card-info mt-3">
                    <div class="card-body d-flex align-items-center justify-content-between py-2">
                        <div
                            style="font-style: italic; color: #0277bd; border-left: 4px solid #17a2b8; padding-left: 15px;">
                            <i class="fas fa-quote-left mr-2" style="font-size: 1rem; opacity: 0.5;"></i>
                            <span id="quote-text" style="font-size: 0.95rem;">Cargando cita inspiradora...</span>
                            <i class="fas fa-quote-right ml-2" style="font-size: 1rem; opacity: 0.5;"></i>
                        </div>
                        <button class="btn btn-tool" id="refresh-quote" title="Nueva cita">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <script>
            // Función para obtener cita motivacional
            async function fetchMotivationalQuote() {
                try {
                    const response = await fetch('https://api.quotable.io/random?tags=inspirational|success|education|wisdom&maxLength=120');
                    const data = await response.json();

                    const quoteText = `${data.content} — ${data.author}`;
                    document.getElementById('quote-text').textContent = quoteText;
                } catch (error) {
                    // Fallback a citas locales si falla la API
                    const localQuotes = [
                        '"La educación es el arma más poderosa que puedes usar para cambiar el mundo." — Nelson Mandela',
                        '"El éxito es la suma de pequeños esfuerzos repetidos día tras día." — Robert Collier',
                        '"La única forma de hacer un gran trabajo es amar lo que haces." — Steve Jobs',
                        '"El futuro pertenece a quienes creen en la belleza de sus sueños." — Eleanor Roosevelt',
                        '"No cuentes los días, haz que los días cuenten." — Muhammad Ali',
                        '"La perseverancia es la clave del éxito." — Anónimo',
                        '"Nunca es tarde para ser lo que podrías haber sido." — George Eliot'
                    ];
                    const randomQuote = localQuotes[Math.floor(Math.random() * localQuotes.length)];
                    document.getElementById('quote-text').textContent = randomQuote;
                }
            }

            // Cargar cita al iniciar
            document.addEventListener('DOMContentLoaded', fetchMotivationalQuote);

            // Botón para refrescar cita
            document.getElementById('refresh-quote')?.addEventListener('click', (e) => {
                e.preventDefault();
                fetchMotivationalQuote();
            });
        </script>

        <div class="row mt-3">
            <div class="col-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Egresados</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="tabla" class="table table-bordered table-striped table-hover dataTable dtr-inline">
                            <thead>
                                <tr>
                                    <th>Identificación</th>
                                    <th>Nombre Completo</th>
                                    <th>Dir Residencia</th>
                                    <th>Tel Residencia</th>
                                    <th>Correo Principal</th>
                                    <th>Carnet</th>
                                    <th>Sexo</th>
                                    <th>Fallecido</th>
                                    <th>Título</th>
                                    <th>N° Certificado</th>
                                    <th>Año Egresado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php
include_once 'layouts/footer.php';
?>

<?php // Bust cache: añadir versión por timestamp para forzar recarga del JS en el navegador ?>
<script src="../assets/js/egresado.js?v=<?php echo time(); ?>"></script>