$(document).ready(function () {
    var funcion;
    var edit = false;

    const expedienteFieldSelectors = {
        rut: '#rut_extraido',
        nombre: '#nombre_extraido',
        fecha_egreso: '#fecha_egreso_extraido',
        numero_certificado: '#numero_certificado_extraido',
        titulo: '#titulo_extraido'
    };

    const storageControllerUrl = '../controlador/ExpedienteStorageController.php';
    const driveBrowserControllerUrl = '../controlador/DriveBrowserController.php';
    const configControllerUrl = '../controlador/ConfiguracionCertificadoController.php';

    const buildLocalExpedienteUrl = (relativePath = '') => {
        const clean = (relativePath || '').replace(/^\\+/g, '').replace(/^\/+/, '');
        return clean ? `../assets/expedientes/${clean}` : '';
    };

    const updateImportedPdfPreview = (relativePath = '') => {
        const $banner = $('#imported_pdf_preview');
        const $link = $('#imported_pdf_preview_link');
        if (!$banner.length || !$link.length) return;
        const url = buildLocalExpedienteUrl(relativePath);
        if (url) {
            $link.attr('href', url);
            $banner.show();
        } else {
            $banner.hide();
            $link.attr('href', '#');
        }
    };

    const openUrlInNewTab = (url) => {
        if (!url) {
            if (window.Swal) Swal.fire('Información', 'No se encontró un enlace disponible.', 'info');
            return;
        }
        window.open(url, '_blank');
    };

    const extractDriveId = (value = '') => {
        const trimmed = (value || '').trim();
        if (!trimmed) return '';
        const idMatch = trimmed.match(/[-\w]{25,}/);
        return idMatch ? idMatch[0] : '';
    };

    const showImportResultMessage = (type = 'info', html = '') => {
        const $box = $('#import_result_message');
        if (!$box.length) return;
        $box.removeClass('alert-success alert-warning alert-danger alert-info');
        const map = { success: 'alert-success', warning: 'alert-warning', error: 'alert-danger', info: 'alert-info' };
        $box.addClass(map[type] || 'alert-info').html(html).toggle(!!html);
    };

    const updateImportSourceBadges = (fuentesOverride = null) => {
        const $localBadge = $('#fuente_local_badge');
        const $driveBadge = $('#fuente_drive_badge');
        const $localDetail = $('#fuente_local_detalle');
        const $driveDetail = $('#fuente_drive_detalle');

        const applyBadge = ($badge, type, text) => {
            if (!$badge.length) return;
            $badge.removeClass('badge-secondary badge-success badge-warning badge-danger');
            $badge.addClass(`badge-${type}`).text(text);
        };

        const localSource = fuentesOverride?.local;
        if (localSource) {
            if (localSource.disponible) {
                applyBadge($localBadge, 'success', 'Disponible');
                $localDetail.text(`Archivo: ${localSource.archivo || 'sin nombre'} (origen ${localSource.origen || 'desconocido'})`);
            } else {
                applyBadge($localBadge, 'warning', 'No disponible');
                $localDetail.text('No se encontró copia local.');
            }
        } else {
            const hasFile = ($('#import_file')[0]?.files?.length ?? 0) > 0;
            applyBadge($localBadge, hasFile ? 'success' : 'secondary', hasFile ? 'Seleccionado' : 'Sin seleccionar');
            $localDetail.text(hasFile ? $('#import_file')[0].files[0].name : 'Adjunta un archivo PDF.');
        }

        const driveSource = fuentesOverride?.drive;
        if (driveSource) {
            if (driveSource.disponible) {
                applyBadge($driveBadge, 'success', 'Disponible');
                $driveDetail.text(`ID: ${driveSource.drive_id || '—'} (${driveSource.origen === 'drive_existente' ? 'desde Drive' : 'subido ahora'})`);
            } else {
                applyBadge($driveBadge, 'warning', 'No disponible');
                $driveDetail.text('No hay respaldo en Drive.');
            }
        } else {
            const driveInput = $('#import_drive_input').val().trim();
            const driveId = extractDriveId(driveInput);
            applyBadge($driveBadge, driveId ? 'success' : 'secondary', driveId ? 'Seleccionado' : 'Sin seleccionar');
            $driveDetail.text(driveId ? `ID detectado: ${driveId}` : 'Pega el ID o enlace del archivo.');
        }
    };

    const renderDriveFolderResults = ({ loading = false, mensaje = '', archivos = [], folderId = '' } = {}) => {
        const $container = $('#drive_folder_results');
        if (!$container.length) return;

        if (!loading && !mensaje && (!archivos || archivos.length === 0)) {
            mensaje = 'No se encontraron archivos en la carpeta especificada.';
        }

        $container.show();

        if (loading) {
            $container.html('<div class="p-3 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>Listando archivos de Google Drive...</div>');
            return;
        }

        if (mensaje) {
            $container.html(`<div class="p-3 text-muted">${mensaje}</div>`);
            return;
        }

        const header = folderId
            ? `<div class="p-2 border-bottom small text-muted">Carpeta: ${folderId}</div>`
            : '';

        const rows = archivos.map(file => {
            const tipoBadge = file.isFolder
                ? '<span class="badge badge-secondary mr-2">CARPETA</span>'
                : '<span class="badge badge-primary mr-2">ARCHIVO</span>';
            const fecha = file.modifiedTime
                ? new Date(file.modifiedTime).toLocaleString('es-CL')
                : 'Sin fecha';
            const localPath = file.local_path ? String(file.local_path).replace(/^[/\\]+/, '') : '';
            const localLink = localPath
                ? `<a href="${buildLocalExpedienteUrl(localPath)}" target="_blank" class="small">Ver copia local</a>`
                : '';
            const boton = file.descargable
                ? `<button type="button" class="btn btn-sm btn-primary js-drive-select-file" data-file-id="${file.id}" data-file-name="${file.name}" ${localPath ? `data-local-path="${localPath}"` : ''}><i class="fas fa-download mr-1"></i>Usar</button>`
                : '<span class="text-muted small">No descargable</span>';

            const status = file.respaldo_local
                ? `<span class="badge badge-success badge-status">Respaldado local</span>${localLink ? `<div class="text-right">${localLink}</div>` : ''}`
                : '<span class="badge badge-warning badge-status">Sin copia local</span>';

            return `
                <div class="drive-item">
                    <div class="flex-grow-1">
                        <div class="drive-name">${tipoBadge}${file.name || 'Sin nombre'}</div>
                        <div class="drive-meta">${fecha}</div>
                    </div>
                    <div class="drive-actions">
                        ${status}
                        ${boton}
                    </div>
                </div>
            `;
        }).join('');

        $container.html(`
            ${header}
            ${rows}
        `);
    };

    const updateFirmanteStatus = (state = 'loading', message = 'Cargando datos...') => {
        const $estado = $('#firmante_config_estado');
        if (!$estado.length) return;
        const classMap = {
            loading: 'badge-primary',
            ok: 'badge-success',
            warn: 'badge-warning',
            error: 'badge-danger'
        };
        $estado.removeClass('badge-primary badge-success badge-warning badge-danger');
        $estado.addClass(classMap[state] || 'badge-secondary').text(message);
    };

    const updateFirmanteSummary = (nombre = '', cargo = '') => {
        $('#firmante_resumen_nombre').text(nombre || '—');
        $('#firmante_resumen_cargo').text(cargo || '—');
    };

    const applyFirmanteDefault = (nombre = '', cargo = '') => {
        $('#firmante_default_nombre').val(nombre);
        $('#firmante_default_cargo').val(cargo);
        updateFirmanteSummary(nombre, cargo);
    };

    const fetchFirmanteConfig = () => {
        if (!$('#form-config-firmante').length) return;
        updateFirmanteStatus('loading', 'Cargando datos...');
        $.getJSON(configControllerUrl, { accion: 'obtener' })
            .done(res => {
                if (res && res.success && res.data) {
                    applyFirmanteDefault(res.data.nombre || '', res.data.cargo || '');
                    updateFirmanteStatus('ok', 'Valores cargados');
                } else {
                    updateFirmanteStatus('warn', 'Sin datos configurados');
                    updateFirmanteSummary();
                }
            })
            .fail(() => {
                updateFirmanteStatus('error', 'Error al cargar');
                updateFirmanteSummary();
            });
    };

    const openFirmanteModal = ({ rut, nombre }) => {
        $('#firmante_modal_rut').val(rut || '');
        $('#firmante_modal_alumno').text(nombre || '—');

        // Reset to Titular by default
        $('#option_titular').prop('checked', true);
        $('#option_titular_label').addClass('active');
        $('#option_suplente_label').removeClass('active');

        const defaultNombre = $('#firmante_default_nombre').val() || '';
        const defaultCargo = $('#firmante_default_cargo').val() || '';

        // Set values for Titular
        $('#firmante_modal_nombre').val(defaultNombre);
        $('#firmante_modal_cargo').val(defaultCargo);

        // Update Info Text
        $('#info_titular_nombre').text(defaultNombre);
        $('#info_titular_cargo').text(defaultCargo);

        // UI State: Titular
        $('#firmante_inputs_wrapper').hide();
        $('#firmante_titular_info').show();

        $('#firmanteSeleccionModal').modal('show');
    };

    // Toggle logic for Titular/Suplente
    $('input[name="tipo_firmante"]').change(function () {
        const tipo = $(this).val();
        if (tipo === 'titular') {
            const defaultNombre = $('#firmante_default_nombre').val() || '';
            const defaultCargo = $('#firmante_default_cargo').val() || '';

            $('#firmante_modal_nombre').val(defaultNombre);
            $('#firmante_modal_cargo').val(defaultCargo);

            $('#info_titular_nombre').text(defaultNombre);
            $('#info_titular_cargo').text(defaultCargo);

            $('#firmante_inputs_wrapper').hide();
            $('#firmante_titular_info').show();
        } else {
            $('#firmante_modal_nombre').val('');
            $('#firmante_modal_cargo').val('');
            $('#label_firmante_nombre').text('Nombre del Suplente');
            $('#label_firmante_cargo').text('Cargo Suplente');

            $('#firmante_inputs_wrapper').show();
            $('#firmante_titular_info').hide();
        }
    });

    const applyImportedDataToCreateForm = (datos = {}, meta = {}) => {
        if (!$('#form-crear').length) return;
        $('#nombreCompleto').val(datos.nombre || datos.nombreCompleto || '');
        $('#correoPrincipal').val(datos.correo || '');
        $('#carnet').val(datos.rut || '');
        $('#sexo').val(datos.sexo || '');
        $('#titulo').val(datos.titulo || datos.titulo_catalogo || '');
        $('#numeroCertificado').val(datos.numero_certificado || datos.numeroCertificado || '');
        const fecha = datos.fecha_entrega || datos.fecha_egreso || datos.fechaGrado || '';
        $('#fechaGrado').val(fecha ? fecha.substring(0, 10) : '');
        $('#tit_ven').text('Crear Egresado Manual (datos importados)');
        $('#form-crear').data('imported-id', meta.id_expediente || meta.egresadoId || meta.egresado_id || '');
        updateImportedPdfPreview(meta.archivo || meta.local_path || '');

        // Cerrar modal de importación primero
        $('#importarExpedienteModal').modal('hide');

        // Esperar a que se cierre completamente antes de abrir el modal de crear
        setTimeout(() => {
            if (!$('#crear').hasClass('show')) {
                $('#crear').modal('show');
            }
        }, 300);
    };

    const fetchResumenRespaldo = () => {
        $.post('../controlador/DashboardController.php', { funcion: 'obtenerResumenRespaldo' }, function (res) {
            const data = typeof res === 'string' ? JSON.parse(res) : res;
            $('#respaldo_total').text(data.total_egresados ?? 0);
            $('#respaldo_local').text(data.con_local ?? 0);
            $('#respaldo_drive').text(data.con_drive ?? 0);

            const faltantesLocal = data.sin_local ?? 0;
            const faltantesDrive = data.sin_drive ?? 0;
            $('#respaldo_local_detalle').text(faltantesLocal > 0 ? `${faltantesLocal} sin copia local` : 'Todos tienen copia local.');
            $('#respaldo_drive_detalle').text(faltantesDrive > 0 ? `${faltantesDrive} sin respaldo en Drive` : 'Todos tienen respaldo en Drive.');

            const driveTotal = data.drive_total_archivos ?? 0;
            const pendientesBD = data.drive_pendientes_bd ?? 0;
            const pendientesLocal = data.drive_pendientes_local ?? 0;

            $('#respaldo_total_drive_diff').text(driveTotal > data.total_egresados
                ? `${driveTotal - (data.total_egresados || 0)} pendientes por importar desde Drive.`
                : 'No hay expedientes adicionales en Drive.');

            $('#respaldo_local_drive_gap').text(pendientesLocal > 0
                ? `${pendientesLocal} expedientes en Drive aún sin copia local.`
                : 'Todas las copias de Drive tienen respaldo local.');

            $('#respaldo_drive_pendientes_bd').text(pendientesBD > 0
                ? `${pendientesBD} archivos en Drive aún sin registrar en BD.`
                : 'Todos los archivos de Drive están asociados a la BD.');

            renderDriveInventory(data.drive_inventario);
        }, 'json');
    };

    const renderDriveInventory = (inventario = {}) => {
        const panel = $('#drive-inventario-panel');
        if (!inventario || inventario.habilitado === false || (inventario.total_archivos ?? 0) === 0) {
            panel.hide();
            if (inventario && inventario.mensaje) {
                $('#drive_inventario_resumen').text(inventario.mensaje);
            }
            return;
        }

        panel.show();
        $('#drive_inventario_timestamp').text(inventario.timestamp ? `Actualizado: ${new Date(inventario.timestamp).toLocaleString('es-CL')}` : '');
        $('#drive_inventario_resumen').text(`Google Drive contiene ${inventario.total_archivos ?? 0} archivos de expedientes.`);

        const contenedor = $('#drive_inventario_carpetas');
        contenedor.empty();

        (inventario.carpetas || []).forEach(carpeta => {
            const html = `
                <div class="fuente-chip">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong>${carpeta.nombre || 'Carpeta'}</strong>
                        <span class="badge badge-info">${carpeta.total_archivos ?? 0}</span>
                    </div>
                    <small class="text-muted">Archivos disponibles</small>
                </div>
            `;
            contenedor.append(html);
        });
    };

    const buildStorageHtml = (id, status) => {
        const parts = [];
        const warnings = status.warnings || [];

        parts.push('<div class="text-left">');

        parts.push('<div class="mb-3">');
        parts.push('<h6 class="font-weight-bold">Respaldo local</h6>');
        if (status.local_exists && status.local_url) {
            parts.push('<p class="text-success mb-2"><i class="fa fa-check-circle mr-1"></i> Disponible</p>');
            parts.push(`<button type="button" class="btn btn-sm btn-primary js-open-expediente" data-url="${status.local_url}">Abrir copia local</button>`);
        } else {
            parts.push('<p class="text-danger mb-2"><i class="fa fa-exclamation-triangle mr-1"></i> No se encontró el archivo local.</p>');
            if (status.drive_exists) {
                parts.push(`<button type="button" class="btn btn-sm btn-outline-primary js-restaurar-local" data-id="${id}">Restaurar desde Drive</button>`);
            } else {
                parts.push('<small class="text-muted">Sube nuevamente el expediente para recrear la copia local.</small>');
            }
        }
        parts.push('</div>');

        parts.push('<div class="mb-1">');
        parts.push('<h6 class="font-weight-bold">Respaldo en Google Drive</h6>');
        if (status.drive_exists && status.drive_url) {
            parts.push('<p class="text-success mb-2"><i class="fa fa-check-circle mr-1"></i> Disponible</p>');
            parts.push(`<button type="button" class="btn btn-sm btn-success js-open-expediente" data-url="${status.drive_url}">Abrir en Google Drive</button>`);
        } else {
            parts.push('<p class="text-warning mb-2"><i class="fa fa-exclamation-triangle mr-1"></i> No hay respaldo en Drive.</p>');
            if (status.local_exists) {
                parts.push(`<button type="button" class="btn btn-sm btn-outline-success js-subir-drive" data-id="${id}">Subir copia local a Drive</button>`);
            } else {
                parts.push('<small class="text-muted">Carga un expediente para generar un nuevo respaldo en Drive.</small>');
            }
        }
        parts.push('</div>');

        if (warnings.length) {
            parts.push('<div class="alert alert-warning mt-3">');
            warnings.forEach(msg => {
                parts.push(`<div class="small mb-1">${msg}</div>`);
            });
            parts.push('</div>');
        }

        parts.push('</div>');
        return parts.join('');
    };

    const showStorageVerification = (id) => {
        if (!id) {
            if (window.Swal) Swal.fire('Información', 'No se pudo determinar el expediente.', 'info');
            return;
        }

        Swal.fire({
            title: 'Verificando respaldos...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.post(storageControllerUrl, { accion: 'verificar', id }, function (res) {
            Swal.close();
            if (!res || !res.success) {
                Swal.fire('Error', res?.mensaje || 'No se pudo verificar el expediente.', 'error');
                return;
            }

            const html = buildStorageHtml(id, res);
            Swal.fire({
                title: 'Respaldos del expediente',
                html,
                width: '600px',
                showConfirmButton: false,
                showCloseButton: true
            });
        }, 'json').fail(function () {
            Swal.close();
            Swal.fire('Error', 'No se pudo verificar el expediente.', 'error');
        });
    };

    const triggerStorageAction = (accion, id) => {
        if (!id) {
            if (window.Swal) Swal.fire('Información', 'No se pudo determinar el expediente.', 'info');
            return;
        }

        Swal.fire({
            title: 'Procesando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.post(storageControllerUrl, { accion, id }, function (res) {
            Swal.close();
            if (!res || !res.success) {
                Swal.fire('Error', res?.mensaje || 'No se pudo completar la acción.', 'error');
                return;
            }

            Swal.fire('Listo', res.mensaje || 'Acción completada correctamente', 'success').then(() => {
                if (typeof tabla !== 'undefined') {
                    tabla.ajax.reload(null, false);
                }
                showStorageVerification(id);
            });
        }, 'json').fail(function () {
            Swal.close();
            Swal.fire('Error', 'No se pudo completar la acción.', 'error');
        });
    };

    const setEditButtonEnabled = (enabled) => {
        $('#btn-habilitar-edicion').prop('disabled', !enabled);
    };

    const setExpedienteReadonly = (readonly) => {
        Object.values(expedienteFieldSelectors).forEach(selector => {
            $(selector).prop('readonly', readonly);
        });
    };

    const evaluateExpedienteFields = () => {
        const missingKeys = [];
        Object.entries(expedienteFieldSelectors).forEach(([key, selector]) => {
            const value = ($(selector).val() || '').trim();
            if (!value) {
                missingKeys.push(key);
            }
        });

        const needsManual = missingKeys.length > 0;
        setExpedienteReadonly(!needsManual);

        if (needsManual) {
            $('#noReconocido').show();
            $('#btnManual').show();
            $('#btn-guardar-expediente').text('Subir y Procesar');
            $('#form-expediente').data('modo', 'process');
        } else {
            $('#noReconocido').hide();
            $('#btnManual').hide();
            $('#btn-guardar-expediente').text('Guardar');
            $('#form-expediente').data('modo', 'save');
        }

        return needsManual;
    };

    const enableManualEditing = () => {
        setExpedienteReadonly(false);
        $('#noReconocido').hide();
        $('#btnManual').hide();
        $('#btn-guardar-expediente').text('Guardar');
        $('#form-expediente').data('modo', 'save');
    };

    setEditButtonEnabled(false);

    // Cargar egresados y catálogos al iniciar
    cargar_egresados();
    cargar_titulos();
    cargar_titulos_form();
    fetchResumenRespaldo();

    const pdfExportColumns = [1, 4, 5, 6, 8, 9, 10];

    // Inicializar la tabla DataTable con configuraciones personalizadas
    var tabla = $('#tabla').DataTable({
        dom: 'Bfrtip',
        "order": [[0, "desc"]],

        "lengthMenu": [[5, 10, 20, 25, 50, -1], [5, 10, 20, 25, 50, "Todos"]],
        "iDisplayLength": 10,
        "responsive": true,
        "autoWidth": false,
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "info": "Registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "sProcessing": "Procesando..."
        },
        "ajax": {
            "url": "../controlador/EgresadoController.php",
            "method": 'POST',
            "data": { funcion: 'listar' },
            "dataSrc": ""
        },
        "columns": [
            { "data": "identificacion", "title": "Identificación", "visible": false },
            { "data": "nombreCompleto", "title": "Nombre Completo" },
            { "data": "dirResidencia", "title": "Dir Residencia", "visible": false },
            { "data": "telResidencia", "title": "Tel Residencia", "visible": false },
            { "data": "correoPrincipal", "title": "Correo Principal" },
            { "data": "carnet", "title": "Carnet" },
            { "data": "sexo", "title": "Sexo" },
            { "data": "fallecido", "title": "Fallecido", "visible": false },
            {
                "data": null,
                "title": "Título",
                "render": function (data, type, row) {
                    if (type === 'display' || type === 'filter') {
                        return row.titulo_catalogo || row.titulo || '';
                    }
                    return row.titulo_catalogo || row.titulo || '';
                }
            },
            { "data": "numeroCertificado", "title": "N° Certificado" },
            {
                "data": null,
                "title": "Año Egresado",
                "render": function (data, type, row) {
                    const formatYMD = value => {
                        if (!value) return null;
                        const parts = value.split('-');
                        if (parts.length === 3) {
                            const [year, month, day] = parts;
                            return `${day}/${month}/${year}`;
                        }
                        return null;
                    };

                    const fechaGrado = formatYMD(row.fechaGrado);
                    if (fechaGrado) {
                        return fechaGrado;
                    }

                    const fechaEntrega = formatYMD(row.fechaEntregaCertificado);
                    if (fechaEntrega) {
                        return fechaEntrega;
                    }

                    return 'Sin fecha';
                }
            },
            {
                "data": null,
                "title": "Acciones",
                "orderable": false,
                "render": function (data, type, row) {
                    const viewBtn = `<button class='ver-expediente btn bg-teal btn-sm' title='Respaldos del expediente' data-id='${row.identificacion || ''}'><i class='fas fa-file-pdf'></i></button>`;

                    return "<div class='btn-group'>"
                        + viewBtn
                        + `<button class='generar-cert btn btn-sm btn-success' title='Generar certificado' data-carnet='${row.carnet || ''}' data-nombre='${row.nombreCompleto || ''}' data-numero='${row.numeroCertificado || ''}' data-titulo='${row.titulo_catalogo || row.titulo || ''}' data-fecha='${row.fechaGrado || row.fechaEntregaCertificado || ''}'><i class='fas fa-certificate'></i></button>`
                        + "<button type='button' class='editar btn btn-sm btn-primary' title='Editar'><i class='fas fa-pencil-alt'></i></button>"
                        + "<button class='eliminar btn btn-sm btn-danger' title='Eliminar'><i class='fas fa-trash'></i></button>"
                        + "</div>";
                }
            }
        ],
        "columnDefs": [
            {
                "targets": [0, 2, 3, 7],
                "visible": false,
                "searchable": false,
                "className": 'noVis'
            },
            {
                "className": "text-center",
                "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                "visible": true,
                "searchable": true
            }
        ],
        buttons: [
            {
                extend: 'copy',
                text: 'Copiar',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'csv',
                text: 'CSV',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                title: 'Listado de Egresados',
                exportOptions: {
                    columns: [1, 4, 5, 6, 9, 10, 11]
                },
                customize: function (xlsx) {
                    const sheet = xlsx.xl.worksheets['sheet1.xml'];
                    const $sheet = $(sheet);
                    const styles = xlsx.xl['styles.xml'];
                    const $styles = $(styles);

                    const fonts = $styles.find('fonts');
                    let fontCount = parseInt(fonts.attr('count'), 10);
                    const titleFontId = fontCount;
                    fonts.append('<font><sz val="18"/><color theme="1"/><name val="Calibri"/></font>');
                    fontCount++;
                    const headerFontId = fontCount;
                    fonts.append('<font><sz val="14"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>');
                    fontCount++;
                    fonts.attr('count', fontCount);

                    const fills = $styles.find('fills');
                    let fillCount = parseInt(fills.attr('count'), 10);
                    const titleFillId = fillCount;
                    fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FF5B82B8"/><bgColor indexed="64"/></patternFill></fill>');
                    fillCount++;
                    const headerFillId = fillCount;
                    fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FF1F497D"/><bgColor indexed="64"/></patternFill></fill>');
                    fillCount++;
                    const rowAltFillId = fillCount;
                    fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FFEFF4FB"/><bgColor indexed="64"/></patternFill></fill>');
                    fillCount++;
                    fills.attr('count', fillCount);

                    const cellXfs = $styles.find('cellXfs');
                    let styleCount = parseInt(cellXfs.attr('count'), 10);
                    const titleStyleId = styleCount;
                    cellXfs.append(`<xf numFmtId="0" fontId="${titleFontId}" fillId="${titleFillId}" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>`);
                    styleCount++;
                    const headerStyleId = styleCount;
                    cellXfs.append(`<xf numFmtId="0" fontId="${headerFontId}" fillId="${headerFillId}" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>`);
                    styleCount++;
                    const rowAltStyleId = styleCount;
                    cellXfs.append(`<xf numFmtId="0" fontId="0" fillId="${rowAltFillId}" borderId="0" xfId="0" applyFill="1"/>`);
                    styleCount++;
                    cellXfs.attr('count', styleCount);

                    const getColumnLetter = (index) => {
                        let letter = '';
                        let temp = index;
                        while (temp > 0) {
                            const modulo = (temp - 1) % 26;
                            letter = String.fromCharCode(65 + modulo) + letter;
                            temp = Math.floor((temp - modulo) / 26);
                        }
                        return letter;
                    };

                    const shiftRowNumbers = () => {
                        const rows = $sheet.find('sheetData row');
                        rows.each(function () {
                            const r = parseInt($(this).attr('r'), 10);
                            const newR = r + 1;
                            $(this).attr('r', newR);
                            $(this).find('c').each(function () {
                                const cellRef = $(this).attr('r');
                                const col = cellRef.replace(/[0-9]/g, '');
                                const row = parseInt(cellRef.replace(/[^0-9]/g, ''), 10);
                                $(this).attr('r', `${col}${row + 1}`);
                            });
                        });
                    };

                    shiftRowNumbers();

                    const columnCount = $('col', $sheet).length || 7;
                    const lastCol = getColumnLetter(columnCount);

                    const sheetData = $sheet.find('sheetData');
                    const columns = Array.from({ length: columnCount }, (_, i) => getColumnLetter(i + 1));
                    const titleCells = columns.map((col, idx) => {
                        if (idx === 0) {
                            return `<c t="inlineStr" s="${titleStyleId}" r="${col}1"><is><t>Egresados - EgresApp2</t></is></c>`;
                        }
                        return `<c s="${titleStyleId}" r="${col}1"/>`;
                    }).join('');
                    const titleRow = $(`<row r="1">${titleCells}</row>`);
                    sheetData.prepend(titleRow);

                    const headerRow = sheetData.find('row[r="2"]');
                    headerRow.find('c').each(function () {
                        $(this).attr('s', headerStyleId);
                    });

                    sheetData.find('row').each(function () {
                        const r = parseInt($(this).attr('r'), 10);
                        if (r >= 3 && r % 2 === 1) {
                            $(this).find('c').each(function () {
                                $(this).attr('s', rowAltStyleId);
                            });
                        }
                    });

                    // Anchuras amigables
                    const widths = [28, 30, 18, 12, 30, 32, 16];
                    $('col', $sheet).each(function (i) {
                        if (widths[i]) {
                            $(this).attr('width', widths[i]);
                            $(this).attr('customWidth', 1);
                        }
                    });

                    // Aplicar autofiltro
                    const rowCount = $('row', $sheet).length;
                    $sheet.find('autoFilter').remove();
                    sheetData.after(`<autoFilter ref="A2:${lastCol}${rowCount}"/>`);
                }
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                title: 'Listado de Egresados',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                margin: [20, 20, 20, 20],
                exportOptions: {
                    columns: pdfExportColumns
                },
                customize: function (doc) {
                    doc.defaultStyle.fontSize = 9;
                    doc.defaultStyle.margin = [0, 1, 0, 1];

                    doc.styles.tableHeader = {
                        fillColor: '#1F4E79',
                        color: '#FFFFFF',
                        fontSize: 11,
                        bold: true,
                        alignment: 'center'
                    };

                    doc.styles.tableBodyEven = { fillColor: '#EFF4FB' };
                    doc.styles.tableBodyOdd = { fillColor: '#FFFFFF' };

                    doc.content.splice(0, 1);
                    doc.content.unshift(
                        {
                            text: 'Egresados - EgresApp2',
                            style: 'title',
                            alignment: 'center',
                            margin: [0, 0, 0, 2]
                        },
                        {
                            text: 'Reporte generado el ' + new Date().toLocaleDateString('es-CL', {
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric'
                            }),
                            style: 'subtitle',
                            alignment: 'center',
                            margin: [0, 0, 0, 6]
                        }
                    );

                    doc.styles.title = {
                        fontSize: 16,
                        bold: true,
                        color: '#1F4E79'
                    };
                    doc.styles.subtitle = {
                        fontSize: 9,
                        italics: true,
                        color: '#5B5B5B'
                    };

                    const tableNode = doc.content.find(item => item.table);
                    if (!tableNode || !tableNode.table) {
                        return;
                    }

                    const body = tableNode.table.body;
                    body.forEach(function (row, rowIndex) {
                        if (rowIndex === 0) return;
                        row.forEach(function (cell, cellIndex) {
                            cell.margin = [3, 2, 3, 2];
                            if (cellIndex === 2 || cellIndex === 3 || cellIndex === 5) {
                                cell.alignment = 'center';
                            } else {
                                cell.alignment = 'left';
                            }
                        });
                    });

                    const dt = $('#tabla').DataTable();
                    const columnWidthsPx = pdfExportColumns.map(idx => {
                        const headerCell = $(dt.column(idx).header());
                        const width = headerCell.length ? headerCell.outerWidth() : null;
                        return width && width > 0 ? width : 80;
                    });
                    const totalWidthPx = columnWidthsPx.reduce((sum, val) => sum + val, 0) || 1;
                    const pageWidth = (doc.pageSize && doc.pageSize.width)
                        ? doc.pageSize.width
                        : (doc.pageOrientation === 'landscape' ? 841.89 : 595.28);
                    const marginLeft = Array.isArray(doc.pageMargins) ? doc.pageMargins[0] : 20;
                    const marginRight = Array.isArray(doc.pageMargins) ? doc.pageMargins[2] : 20;
                    const availableWidth = Math.max(pageWidth - marginLeft - marginRight, 400);
                    const scaleFactor = availableWidth / totalWidthPx;
                    const tableWidths = columnWidthsPx.map(width => parseFloat((width * scaleFactor).toFixed(2)));
                    tableNode.table.widths = tableWidths.length ? tableWidths : ['*'];
                    tableNode.layout = {
                        paddingLeft: function () { return 3; },
                        paddingRight: function () { return 3; },
                        paddingTop: function () { return 2; },
                        paddingBottom: function () { return 2; },
                        hLineWidth: function () { return 0.3; },
                        vLineWidth: function () { return 0.3; },
                        hLineColor: function () { return '#B0C4DE'; },
                        vLineColor: function () { return '#B0C4DE'; }
                    };
                }
            },
            {
                extend: 'print',
                text: 'Imprimir',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'colvis',
                columns: ':not(.noVis)'
            }
        ]
    });

    tabla.buttons().container().appendTo($('.col-md-6:eq(0)', tabla.table().container()));

    // Gestionar respaldos del expediente
    $(document).on('click', '.ver-expediente', function () {
        const id = $(this).data('id');
        showStorageVerification(id);
    });

    $(document).on('click', '.js-open-expediente', function () {
        const url = $(this).data('url');
        openUrlInNewTab(url);
    });

    $(document).on('click', '.js-restaurar-local', function () {
        const id = $(this).data('id');
        triggerStorageAction('restaurar_local', id);
    });

    $(document).on('click', '.js-subir-drive', function () {
        const id = $(this).data('id');
        triggerStorageAction('subir_drive', id);
    });

    // Enviar el formulario para subir y procesar el expediente (PDF)
    $('#form-expediente').submit(e => {
        const formMode = $('#form-expediente').data('modo') || 'process';
        const onlySave = formMode === 'save';

        if (onlySave) {
            e.preventDefault();

            const payload = {
                id_expediente: $('#id_expediente').val(),
                rut: $('#rut_extraido').val(),
                nombre: $('#nombre_extraido').val(),
                fecha_egreso: $('#fecha_egreso_extraido').val(),
                numero_certificado: $('#numero_certificado_extraido').val(),
                titulo: $('#titulo_extraido').val(),
                fecha_entrega: $('#fecha_entrega_extraido').val() || $('#fecha_egreso_extraido').val(),
                correo: $('#correo_extraido').val(),
                sexo: $('#sexo_extraido').val(),
                gestion: $('#gestion_extraido').val()
            };

            Swal.fire({
                title: 'Guardando...',
                text: 'Actualizando datos del expediente',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.post('../controlador/GuardarExpedienteManualController.php', payload, function (res) {
                Swal.close();
                if (!res || !res.success) {
                    Swal.fire('Error', res?.mensaje || 'No se pudieron guardar los cambios.', 'error');
                    return;
                } else {
                    Swal.fire('Guardado', res.mensaje || 'Datos actualizados correctamente', 'success');
                    if (typeof tabla !== 'undefined') {
                        tabla.ajax.reload(null, false);
                    }
                    fetchResumenRespaldo();
                    fetchResumenRespaldo();
                }
            }, 'json').fail(function () {
                Swal.close();
                Swal.fire('Error', 'No se pudieron guardar los cambios.', 'error');
            });

            return;
        }

        e.preventDefault();

        // Mostrar loader o spinner
        Swal.fire({
            title: 'Procesando...',
            text: 'Subiendo y analizando el expediente',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        let formData = new FormData($('#form-expediente')[0]);

        $.ajax({
            url: '../controlador/ProcesarExpedienteController.php',
            type: 'POST',
            data: formData,
            cache: false,
            processData: false,
            contentType: false
        }).done(function (response) {
            try {
                // Convertir a JSON solo si es una cadena
                const json = typeof response === 'string' ? JSON.parse(response) : response;
                Swal.close();

                // Mostrar información de depuración en consola
                console.log('Respuesta completa:', json);

                if (json.success) {
                    if (json.estado === 'pending') {
                        $('.datos-extraidos').hide();
                        $('#updateexpediente').hide();
                        $('#noupdateexpediente').hide();

                        if (json.queue_id) {
                            $('#form-expediente').data('queue-id', json.queue_id);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Expediente procesado',
                            text: json.mensaje || 'Datos extraídos correctamente.',
                            timer: 3000,
                            showConfirmButton: false
                        });

                        return;
                    }

                    if (json.debug) {
                        console.log('Texto extraído:', json.debug.texto_extraido);
                        console.log('Longitud del texto:', json.debug.texto_largo);
                        console.log('Datos crudos:', json.debug.datos_crudos);
                    }

                    const datos = json.datos || {};
                    // Mostrar datos extraídos
                    if (!$('#cambiarExpediente').hasClass('show')) {
                        $('#cambiarExpediente').modal('show');
                    }

                    $('.datos-extraidos').show();
                    $('#rut_extraido').val(datos.rut || '');
                    $('#nombre_extraido').val(datos.nombre || '');
                    $('#fecha_egreso_extraido').val(datos.fecha_egreso || '');
                    $('#numero_certificado_extraido').val(datos.numero_certificado || '');
                    $('#titulo_extraido').val(datos.titulo || '');
                    $('#id_expediente').val(json.egresado_id || '');
                    setEditButtonEnabled(true);

                    evaluateExpedienteFields();

                    // Mostrar mensaje de éxito
                    $('#updateexpediente').hide('slow').show(1000).text('Expediente subido y datos extraídos correctamente');

                    // Actualizar link para ver el PDF
                    const localUrl = json.archivo ? '../assets/expedientes/' + json.archivo : null;
                    if (localUrl) {
                        $('#link_expediente_local').attr('href', localUrl).show();
                    } else {
                        $('#link_expediente_local').hide();
                    }

                    if (json.drive_link) {
                        $('#link_expediente_drive').attr('href', json.drive_link).show();
                    } else {
                        $('#link_expediente_drive').hide();
                    }

                    if (typeof tabla !== 'undefined') {
                        tabla.ajax.reload(null, false);
                    }
                    fetchResumenRespaldo();
                } else {
                    throw new Error(json.mensaje);
                }
            } catch (err) {
                Swal.close();
                console.error('Error al procesar expediente:', err);
                $('#noupdateexpediente').hide('slow').show(1000).text('Los datos no fueron ingresados.');

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message || 'Los datos no fueron ingresados.'
                });
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            Swal.close();
            console.error('Error en la petición AJAX:', textStatus, errorThrown);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Los datos no fueron ingresados.'
            });
        });
    });

    // Habilitar edición manual si no se reconocen datos
    $(document).on('click', '#btnManual, #btn-habilitar-edicion', function () {
        enableManualEditing();
    });

    // Re-evaluar cuando se muestra el modal
    $(document).on('shown.bs.modal', '#cambiarExpediente', function () {
        evaluateExpedienteFields();
    });

    // Botón para generar certificado desde la vista admin
    $(document).on('click', '#btn_generar_cert_admin', function () {
        const carnet = $('#form-expediente').data('carnet');
        if (!carnet) {
            if (window.Swal) Swal.fire('Error', 'No se encontró el carnet del egresado', 'error');
            return;
        }
        // Llamar al endpoint de autoconsulta para obtener los datos necesarios
        $.post('../controlador/AutoconsultaController.php', { rut: carnet }, function (res) {
            try {
                // res ya viene como JSON desde el endpoint
                if (!res || !res.success) {
                    if (window.Swal) Swal.fire('Error', res.message || 'No fue posible obtener datos del egresado', 'error');
                    return;
                }
                // Pasar los datos al generador global
                const jsonData = JSON.stringify(res);
                // cerrar modal y mostrar loader en generarCertificado
                $('#cambiarExpediente').modal('hide');
                if (typeof window.generarCertificado === 'function') {
                    window.generarCertificado(jsonData, carnet);
                } else {
                    if (window.Swal) Swal.fire('Info', 'La generación está disponible desde la herramienta de autoconsulta.', 'info');
                }
            } catch (err) {
                console.error('Error al procesar datos de autoconsulta', err);
                if (window.Swal) Swal.fire('Error', 'Respuesta inválida del servidor', 'error');
            }
        }, 'json').fail(function () {
            if (window.Swal) Swal.fire('Error', 'Error al conectar con el servidor de autoconsulta', 'error');
        });
    });

    // Evento para mostrar el formulario de creación de egresado
    $(document).on('click', '.btn-crear', (e) => {
        $('#form-crear').trigger('reset');
        $('#tit_ven').html('Crear Egresado Manual');
        // Reiniciar identificacion oculta
        $('#identificacion_hidden').val('');
        $('#form-crear').data('imported-id', '');
        edit = false;
        $('#titulo').prop('disabled', false);
        $('#numeroCertificado').val('');
        updateImportedPdfPreview(null);
    });

    $(document).on('click', '.btn-importar-expediente', () => {
        $('#form-importar-expediente')[0].reset();
        $('#import_result_message').hide();
        updateImportSourceBadges();
        $('#import_local_existing_path').val('');

        // Cargar carpetas de Google Drive en el dropdown
        loadDriveFolders();

        renderDriveFolderResults({ mensaje: 'Selecciona una carpeta o ingresa un ID para listar expedientes.' });
        $('#importarExpedienteModal').modal('show');
    });

    $('#import_file').on('change', () => updateImportSourceBadges());
    $('#import_drive_input').on('input', () => updateImportSourceBadges());

    // Función para cargar carpetas de Google Drive en el dropdown
    function loadDriveFolders() {
        $.get('../controlador/GetDriveFoldersController.php', function (response) {
            if (!response || !response.success) {
                console.error('No se pudieron cargar las carpetas de Drive');
                $('#drive_folder_selector').html('<option value="">-- Error al cargar carpetas --</option>');
                return;
            }

            const $selector = $('#drive_folder_selector');
            $selector.empty();
            $selector.append('<option value="">-- Seleccionar Carpeta --</option>');

            response.carpetas.forEach(carpeta => {
                $selector.append(`<option value="${carpeta.folder_id}">${carpeta.name}</option>`);
            });
        }, 'json').fail(() => {
            console.error('Error al conectar con el servidor para obtener carpetas');
            $('#drive_folder_selector').html('<option value="">-- Error de conexión --</option>');
        });
    }

    // Evento cuando se selecciona una carpeta del dropdown
    $('#drive_folder_selector').on('change', function () {
        const folderId = $(this).val();
        if (folderId) {
            $('#import_drive_folder_id').val(folderId);
            $('#btn_drive_listar').click(); // Auto-listar archivos de la carpeta seleccionada
        }
    });

    $('#btn_drive_listar').on('click', () => {
        const rawValue = $('#import_drive_folder_id').val().trim();
        const folderId = extractDriveId(rawValue) || rawValue;
        if (!folderId) {
            renderDriveFolderResults({ mensaje: 'Debes ingresar el ID de una carpeta para listar sus archivos.' });
            return;
        }

        renderDriveFolderResults({ loading: true });

        $.post(driveBrowserControllerUrl, { accion: 'listar_archivos', folder_id: folderId }, function (res) {
            if (!res || !res.success) {
                renderDriveFolderResults({ mensaje: res?.mensaje || 'No se pudo listar la carpeta.' });
                return;
            }
            renderDriveFolderResults({ archivos: res.archivos || [], folderId });
        }, 'json').fail(() => {
            renderDriveFolderResults({ mensaje: 'No se pudo conectar con Google Drive.' });
        });
    });

    $(document).on('click', '.js-drive-select-file', function () {
        const fileId = $(this).data('file-id');
        const fileName = $(this).data('file-name');
        const localPath = $(this).data('localPath') || '';
        if (!fileId) {
            return;
        }
        $('#import_drive_input').val(fileId);
        $('#import_local_existing_path').val(localPath);
        showImportResultMessage('info', `Expediente seleccionado: <strong>${fileName || fileId}</strong>`);
        updateImportSourceBadges();

        $('#drive_folder_results .list-group-item').removeClass('active');
        $(this).closest('.list-group-item').addClass('active');
    });

    $('#form-config-firmante').on('submit', function (e) {
        e.preventDefault();
        const nombre = $('#firmante_default_nombre').val().trim();
        const cargo = $('#firmante_default_cargo').val().trim();
        if (!nombre || !cargo) {
            Swal.fire('Campos incompletos', 'Debes ingresar nombre y cargo del firmante titular.', 'warning');
            return;
        }

        const $button = $('#firmante_config_guardar');
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Guardando...');

        $.post(configControllerUrl, { accion: 'guardar', nombre, cargo }, function (res) {
            if (res && res.success) {
                Swal.fire('Listo', res.message || 'Firmante actualizado correctamente.', 'success');
                updateFirmanteStatus('ok', 'Último cambio guardado');
                updateFirmanteSummary(nombre, cargo);
                $('#firmanteConfigModal').modal('hide');
            } else {
                Swal.fire('Error', res?.message || 'No se pudo guardar la configuración.', 'error');
                updateFirmanteStatus('error', 'No se pudo guardar');
            }
        }, 'json')
            .fail(() => {
                Swal.fire('Error', 'No se pudo comunicar con el servidor.', 'error');
                updateFirmanteStatus('error', 'No se pudo guardar');
            })
            .always(() => {
                $button.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Guardar titular');
            });
    });

    $('#firmante_config_open').on('click', () => {
        $('#firmanteConfigModal').modal('show');
    });

    $(document).on('click', '.generar-cert', function () {
        const boton = $(this);
        const rut = boton.data('carnet');
        const nombre = boton.data('nombre');
        if (!rut) {
            Swal.fire('Información incompleta', 'El egresado no tiene carnet asociado.', 'warning');
            return;
        }
        $('#firmanteSeleccionModal').data('trigger-button', boton[0]);
        openFirmanteModal({ rut, nombre });
    });

    $('#form-firmante-seleccion').on('submit', function (e) {
        e.preventDefault();
        const rut = $('#firmante_modal_rut').val();
        const tipo = $('input[name="tipo_firmante"]:checked').val();
        const firmanteNombre = $('#firmante_modal_nombre').val().trim();
        const firmanteCargo = $('#firmante_modal_cargo').val().trim();
        if (tipo === 'suplente') {
            if (!rut || !firmanteNombre || !firmanteCargo) {
                Swal.fire('Campos incompletos', 'Completa los datos del firmante suplente.', 'warning');
                return;
            }
        } else {
            // Titular: validation only for RUT
            if (!rut) {
                Swal.fire('Error', 'No se ha identificado al egresado.', 'error');
                return;
            }
        }

        const $btn = $('#firmante_modal_submit');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Generando...');

        $.ajax({
            url: '../controlador/GenerarCertificadoWord.php',
            type: 'POST',
            dataType: 'json',
            data: {
                rut,
                firmante_nombre: firmanteNombre,
                firmante_cargo: firmanteCargo
            }
        }).done((response) => {
            if (!response || !response.success || !response.url) {
                const mensaje = (response && response.message) || 'No se pudo generar el certificado.';
                Swal.fire('Atención', mensaje, 'warning');
                return;
            }
            $('#firmanteSeleccionModal').modal('hide');
            Swal.fire('Éxito', response.message || 'Certificado generado correctamente.', 'success');
            window.open(response.url, '_blank');
        }).fail((xhr) => {
            let mensaje = 'No se pudo generar el certificado.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            Swal.fire('Error', mensaje, 'error');
        }).always(() => {
            $btn.prop('disabled', false).html('<i class="fas fa-file-signature mr-1"></i>Generar certificado');
        });
    });

    $('#form-importar-expediente').on('submit', function (e) {
        e.preventDefault();
        const fileInput = $('#import_file')[0];
        const file = fileInput?.files?.[0];
        const driveValue = $('#import_drive_input').val().trim();
        const driveId = extractDriveId(driveValue);

        if (!file && !driveId) {
            showImportResultMessage('warning', 'Selecciona un PDF local o ingresa un enlace/ID de Google Drive.');
            return;
        }

        const formData = new FormData();
        formData.append('import_context', 'crear_manual');
        if (file) {
            formData.append('file', file);
        }
        if (driveId) {
            formData.append('drive_file_id', driveId);
            if (driveValue.startsWith('http')) {
                formData.append('drive_file_link', driveValue);
            }
        }

        Swal.fire({
            title: 'Procesando expediente...',
            text: 'Estamos extrayendo los datos del documento.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '../controlador/ProcesarExpedienteController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false
        }).done((response) => {
            Swal.close();
            const res = typeof response === 'string' ? JSON.parse(response) : response;
            if (!res || !res.success) {
                throw new Error(res?.mensaje || 'No se pudo procesar el expediente.');
            }

            const resumen = [];
            resumen.push('<strong>Expediente procesado correctamente.</strong>');
            if (res.datos?.nombre || res.datos?.rut) {
                resumen.push(`Nombre: <u>${res.datos.nombre || '—'}</u>`);
                resumen.push(`RUT/Carnet: <u>${res.datos.rut || '—'}</u>`);
            }
            const fuentes = res.fuentes || {};
            if (fuentes.local) {
                resumen.push(fuentes.local.disponible ? 'Copia local disponible.' : 'Falta copia local.');
            }
            if (fuentes.drive) {
                resumen.push(fuentes.drive.disponible ? 'Respaldo en Google Drive disponible.' : 'No hay respaldo en Google Drive.');
            }

            showImportResultMessage('success', resumen.join('<br>'));
            updateImportSourceBadges(res.fuentes || null);
            applyImportedDataToCreateForm(res.datos || {}, {
                archivo: res.archivo || '',
                id_expediente: res.egresado_id || null
            });
        }).fail((jqXHR) => {
            Swal.close();
            let message = 'No se pudo procesar el expediente.';
            if (jqXHR.responseJSON && jqXHR.responseJSON.mensaje) {
                message = jqXHR.responseJSON.mensaje;
            } else if (jqXHR.responseText) {
                try {
                    const parsed = JSON.parse(jqXHR.responseText);
                    message = parsed.mensaje || message;
                } catch (e) {
                    message = jqXHR.responseText;
                }
            }
            showImportResultMessage('error', message);
        });
    });

    // Evento para abrir modal de Subir Expediente desde el header (global)
    $(document).on('click', '.btn-subir-expediente', (e) => {
        // Resetear formulario y UI del modal
        $('#form-expediente').trigger('reset');
        $('.datos-extraidos').hide();
        $('#updateexpediente').hide();
        $('#noupdateexpediente').hide();
        $('#id_expediente').val('');
        $('#nombre_expediente').text('');
        $('#link_expediente_local, #link_expediente_drive').hide();
        $('#form-expediente').data('modo', 'process');
        setEditButtonEnabled(false);
        $('#cambiarExpediente').modal('show');
    });

    // Controlador del modal de edición independiente
    const editModalSelectors = {
        rut: '#edit_rut',
        nombre: '#edit_nombre',
        correo: '#edit_correo',
        sexo: '#edit_sexo',
        fecha_egreso: '#edit_fecha_egreso',
        numero_certificado: '#edit_numero_certificado',
        titulo: '#edit_titulo',
        fecha_entrega: '#edit_fecha_entrega'
    };

    const setEditModalReadonly = (readonly) => {
        Object.entries(editModalSelectors).forEach(([key, selector]) => {
            const $element = $(selector);
            if ($element.is('select')) {
                $element.prop('disabled', readonly);
            } else {
                $element.prop('readonly', readonly);
            }
        });
    };

    const openEditModal = (data) => {
        $('#form-editar-egresado')[0].reset();
        $('#edit_id_expediente').val(data.identificacion || '');
        $('#edit_nombre_expediente').text(data.nombreCompleto || '');
        const editLocalUrl = data.expediente_pdf ? '../assets/expedientes/' + data.expediente_pdf : null;
        if (editLocalUrl) {
            $('#edit_link_expediente_local').attr('href', editLocalUrl).show();
        } else {
            $('#edit_link_expediente_local').hide();
        }

        if (data.expediente_drive_link) {
            $('#edit_link_expediente_drive').attr('href', data.expediente_drive_link).show();
        } else {
            $('#edit_link_expediente_drive').hide();
        }

        $('#edit_rut').val(data.carnet || '');
        $('#edit_nombre').val(data.nombreCompleto || '');
        $('#edit_correo').val(data.correoPrincipal || '');
        $('#edit_sexo').val(data.sexo || '');
        $('#edit_fecha_egreso').val(data.fechaEntregaCertificado || data.fechaGrado || '');
        $('#edit_numero_certificado').val(data.numeroCertificado || '');
        $('#edit_titulo').val(data.titulo || '');
        $('#edit_fecha_entrega').val(data.fechaEntregaCertificado || '');

        setEditModalReadonly(true);
        $('#btn-editar-egresado-campos').prop('disabled', false).text('Editar');
        $('#btn-guardar-egresado-modal').prop('disabled', false);
        $('#editExpedienteAlert').hide();
        $('#editarExpedienteModal').modal('show');
    };

    $(document).on('click', '.editar', function () {
        let data;
        if (tabla.row(this).child.isShown()) {
            data = tabla.row(this).data();
        } else {
            data = tabla.row($(this).parents('tr')).data();
        }

        if (!data) {
            return;
        }

        openEditModal(data);
    });

    $('#btn-editar-egresado-campos').on('click', function () {
        setEditModalReadonly(false);
        $(this).prop('disabled', true).text('Editando');
    });

    $('#editarExpedienteModal').on('hidden.bs.modal', function () {
        setEditModalReadonly(true);
        $('#btn-editar-egresado-campos').prop('disabled', false).text('Editar');
    });

    $('#form-editar-egresado').on('submit', function (e) {
        e.preventDefault();
        const payload = {
            id_expediente: $('#edit_id_expediente').val(),
            rut: $('#edit_rut').val(),
            nombre: $('#edit_nombre').val(),
            correo: $('#edit_correo').val(),
            sexo: $('#edit_sexo').val(),
            fecha_egreso: $('#edit_fecha_egreso').val(),
            numero_certificado: $('#edit_numero_certificado').val(),
            titulo: $('#edit_titulo').val(),
            fecha_entrega: $('#edit_fecha_entrega').val()
        };

        Swal.fire({
            title: 'Guardando...',
            text: 'Actualizando datos del expediente',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.post('../controlador/GuardarExpedienteManualController.php', payload, function (res) {
            Swal.close();
            if (!res || !res.success) {
                Swal.fire('Error', res?.mensaje || 'No se pudieron guardar los cambios.', 'error');
                return;
            }

            Swal.fire('Guardado', res.mensaje || 'Datos actualizados correctamente', 'success');
            $('#editarExpedienteModal').modal('hide');
            if (typeof tabla !== 'undefined') {
                tabla.ajax.reload(null, false);
            }
            fetchResumenRespaldo();
        }, 'json').fail(function () {
            Swal.close();
            Swal.fire('Error', 'No se pudieron guardar los cambios.', 'error');
        });
    });

    // Función para buscar un egresado por identificación
    function buscar(dato) {
        funcion = 'buscar';
        $.post('../controlador/EgresadoController.php', { dato, funcion }, (response) => {
            const respuesta = JSON.parse(response);
            $('#identificacion_hidden').val(respuesta.identificacion);
            $('#nombreCompleto').val(respuesta.nombreCompleto);
            $('#dirResidencia').val(respuesta.dirResidencia);
            $('#telResidencia').val(respuesta.telResidencia);
            $('#telAlternativo').val(respuesta.telAlternativo);
            $('#correoPrincipal').val(respuesta.correoPrincipal);
            $('#correoSecundario').val(respuesta.correoSecundario);
            $('#carnet').val(respuesta.carnet);
            $('#sexo').val(respuesta.sexo);
            $('#fallecido').val(respuesta.fallecido);
            $('#titulo').val(respuesta.titulo || '');

            $('#nombre_avatar').html(respuesta.nombreCompleto);
            $('#id_avatar').val(respuesta.identificacion);
            $('#avataractual').attr('src', '../assets/img/prod/' + respuesta.avatar);
            // Mostrar enlace al expediente si existe
            if (respuesta.expediente_pdf) {
                $('#link_expediente_local').attr('href', '../assets/expedientes/' + respuesta.expediente_pdf).show();
            } else {
                $('#link_expediente_local').hide();
            }

            if (respuesta.expediente_drive_link) {
                $('#link_expediente_drive').attr('href', respuesta.expediente_drive_link).show();
            } else {
                $('#link_expediente_drive').hide();
            }
        });
    }

    // Enviar el formulario para crear o editar un egresado
    const submitImportedManualForm = (importedId) => {
        const payload = {
            id_expediente: importedId,
            nombre: $('#nombreCompleto').val(),
            rut: $('#carnet').val(),
            correo: $('#correoPrincipal').val(),
            sexo: $('#sexo').val(),
            titulo: $('#titulo').val(),
            fecha_egreso: $('#fechaGrado').val(),
            numero_certificado: $('#numeroCertificado').val()
        };

        Swal.fire({
            title: 'Guardando...',
            text: 'Actualizando datos importados',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.post('../controlador/GuardarExpedienteManualController.php', payload, function (res) {
            Swal.close();
            if (!res || !res.success) {
                Swal.fire('Error', res?.mensaje || 'No se pudieron guardar los cambios.', 'error');
                return;
            }

            Swal.fire('Guardado', res.mensaje || 'Datos actualizados correctamente', 'success');
            $('#crear').modal('hide');
            $('#form-crear').data('imported-id', '');
            if (typeof tabla !== 'undefined') {
                tabla.ajax.reload(null, false);
            }
            fetchResumenRespaldo();
        }, 'json').fail(function () {
            Swal.close();
            Swal.fire('Error', 'No se pudieron guardar los cambios.', 'error');
        });
    };

    $('#form-crear').submit(e => {
        const importedId = $('#form-crear').data('imported-id');
        if (importedId) {
            e.preventDefault();
            submitImportedManualForm(importedId);
            return;
        }
        let nombreCompleto = $('#nombreCompleto').val();
        let dirResidencia = $('#dirResidencia').val() || 'Sin dirección';
        let telResidencia = $('#telResidencia').val() || 'Sin teléfono';
        let telAlternativo = $('#telAlternativo').val() || 'Sin teléfono alternativo';
        let correoPrincipal = $('#correoPrincipal').val();
        let correoSecundario = $('#correoSecundario').val() || 'sin@correo.com';
        let carnet = $('#carnet').val();
        let sexo = $('#sexo').val();
        let fallecido = $('#fallecido').val() || 'No';
        let titulo = $('#titulo').val();
        let fechaGrado = $('#fechaGrado').val();
        let avatar = 'default.png';

        if (edit == true)
            funcion = 'editar';
        else
            funcion = 'crear';

        // No enviar identificacion al crear
        let data = { nombreCompleto, dirResidencia, telResidencia, telAlternativo, correoPrincipal, correoSecundario, carnet, sexo, fallecido, avatar, funcion };
        if (edit == true) {
            data.identificacion = $('#identificacion_hidden').val();
        }
        // Agregar título y fecha solo si se están creando
        if (edit == false && titulo && fechaGrado) {
            data.titulo = titulo;
            data.fechaGrado = fechaGrado;
        }

        $.post('../controlador/EgresadoController.php', data, (response) => {
            response = response.trim();
            if (response == 'add') {
                Swal.fire({
                    title: 'Egresado creado!',
                    text: 'El egresado ha sido creado exitosamente.',
                    icon: 'success'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else if (response == 'noadd') {
                Swal.fire({
                    title: 'Error!',
                    text: 'El egresado ya existe.',
                    icon: 'error'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else if (response == 'update') {
                Swal.fire({
                    title: 'Egresado actualizado!',
                    text: 'El egresado ha sido actualizado exitosamente.',
                    icon: 'success'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'No se pudo realizar la operación.',
                    icon: 'error'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            }
            $('#crear').modal('hide');
            tabla.ajax.reload(null, false);
            fetchResumenRespaldo();
        });
        e.preventDefault();
    });

    // Evento para agregar un título a un egresado
    $(document).on('click', '.titulo', function () {
        let data;
        if (tabla.row(this).child.isShown())
            data = tabla.row(this).data();
        else
            data = tabla.row($(this).parents("tr")).data();

        $('#egresado').val(data.identificacion);

        // Cargar títulos disponibles
        $.post('../controlador/AgregarTituloEgresadoController.php', { funcion: 'seleccionarTitulos' }, (response) => {
            let titulos = JSON.parse(response);
            $('#titulo').empty();
            titulos.forEach(titulo => {
                $('#titulo').append(`<option value="${titulo.id}">${titulo.nombre}</option>`);
            });
        });

        // Cargar título y fecha de graduación del egresado
        $.post('../controlador/AgregarTituloEgresadoController.php', { funcion: 'obtenerTituloEgresado', identificacion: data.identificacion }, (response) => {
            let tituloEgresado = JSON.parse(response);
            if (tituloEgresado) {
                $('#titulo').val(tituloEgresado.id);

                // Convertir la fecha de dd/mm/yyyy a yyyy-mm-dd
                let fechaArray = tituloEgresado.fechagrado.split('/');
                let fechaGrado = `${fechaArray[2]}-${fechaArray[1]}-${fechaArray[0]}`;

                $('#fechaGrado').val(fechaGrado);
            } else {
                $('#titulo').val('');
                $('#fechaGrado').val('');
            }
        });

        $('#modalTituloEgresado').modal('show');
    });

    // Enviar el formulario para agregar un título a un egresado
    $('#formTituloEgresado').submit(e => {
        e.preventDefault();
        let egresado = $('#egresado').val();
        let titulo = $('#titulo').val();
        let fechaGrado = $('#fechaGrado').val();

        funcion = 'agregarTitulo';
        $.post('../controlador/AgregarTituloEgresadoController.php', { egresado, titulo, fechaGrado, funcion }, (response) => {
            if (response.trim() === 'add') {
                Swal.fire({
                    icon: 'success',
                    title: 'Título agregado',
                    text: 'El título se ha agregado correctamente',
                });
                $('#modalTituloEgresado').modal('hide');
                tabla.ajax.reload(null, false);
            } else if (response.trim() === 'existe') {
                Swal.fire({
                    icon: 'error',
                    title: 'Título ya asignado',
                    text: 'Este título ya ha sido asignado a este egresado',
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo agregar el título',
                });
            }
        });
    });

    // Evento para eliminar un egresado
    $(document).on('click', '.eliminar', function () {
        if (tabla.row(this).child.isShown()) {
            var data = tabla.row(this).data();
        } else {
            var data = tabla.row($(this).parents("tr")).data();
        }
        const id = data.identificacion;
        const nombre = data.nombreCompleto;
        buscar(id);
        funcion = 'eliminar';

        Swal.fire({
            title: 'Desea eliminar ' + nombre + '?',
            text: "Esto no se podrá revertir!",
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, eliminar!'
        }).then((result) => {
            if (result.value) {
                $.post('../controlador/EgresadoController.php', { id, funcion }, (response) => {
                    response = response.trim();
                    if (response == 'eliminado') {
                        Swal.fire(
                            'Eliminado!',
                            nombre + ' fue eliminado.',
                            'success'
                        );
                    }
                    else {
                        Swal.fire(
                            'No se pudo eliminar!',
                            nombre + ' está utilizado',
                            'error'
                        );
                    }
                    tabla.ajax.reload(null, false);
                });
            }
        });
    });

    // Cargar las opciones de egresados en el formulario de observaciones
    function cargar_egresados() {
        funcion = 'obtener_egresados';
        $.post('../controlador/ObservacionController.php', { funcion }, (response) => {
            const registros = JSON.parse(response);
            let template = '';
            registros.forEach(registro => {
                template += `<option value="${registro.identificacion}">${registro.nombreCompleto}</option>`;
            });
            $('#egresado').html(template);
        });
    }

    // Cargar las opciones de títulos en el formulario de agregar título
    function cargar_titulos() {
        funcion = 'seleccionar';
        $.post('../controlador/AgregarTituloController.php', { funcion }, (response) => {
            const registros = JSON.parse(response);
            let template = '';
            registros.forEach(registro => {
                template += `<option value="${registro.id}">${registro.nombre}</option>`;
            });
            $('#titulo').html(template);
        });
    }

    // Cargar las opciones de gestión en el formulario de crear/editar egresado
    function cargar_gestiones() {
    }

    // Cargar las opciones de títulos en el formulario de crear egresado
    function cargar_titulos_form() {
        funcion = 'seleccionar';
        $.post('../controlador/AgregarTituloController.php', { funcion }, (response) => {
            const registros = JSON.parse(response);
            let template = '';
            registros.forEach(registro => {
                template += `<option value="${registro.id}">${registro.nombre}</option>`;
            });
            $('#titulo').html(template);
        });
    }
    cargar_titulos_form();
    fetchFirmanteConfig();
    fetchResumenRespaldo();
});
