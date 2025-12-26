// autoconsulta.js
$(function () {
    function mostrarError(mensaje) {
        $('#rut_consulta_error').text(mensaje).show();
        $('#resultado_consulta').hide();
    }

    function limpiarError() {
        $('#rut_consulta_error').hide();
    }

    function consultarCertificado() {
        const rut = $('#rut_consulta').val().trim();
        if (!rut) {
            mostrarError('Ingrese un RUT para consultar');
            return;
        }

        limpiarError();
        $.ajax({
            url: 'controlador/AutoconsultaController.php',
            method: 'POST',
            dataType: 'json',
            data: { rut: rut },
            success: function (res) {
                if (!res.success) {
                    mostrarError(res.message || 'Error en la consulta');
                    return;
                }

                // Mostrar la información básica del alumno
                let html = `
                    <div class="info-alumno">
                        <h5 class="mb-3">Información del Egresado</h5>
                        <p><strong>Nombre:</strong> ${res.nombre || ''}</p>
                        <p><strong>RUT:</strong> ${rut}</p>
                        <p><strong>Título obtenido:</strong> ${res.titulo || ''}</p>
                        ${res.fechaTitulo ? `<p><strong>Fecha de titulación:</strong> ${formatFechaLong(res.fechaTitulo, false)}</p>` : ''}
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="generarCertificado('${JSON.stringify(res).replace(/'/g, "\\'")}', '${rut}')">
                            <i class="fas fa-file-pdf"></i> Generar Certificado de Título
                        </button>
                    </div>`;

                $('#resultado_consulta').html(html).show();
            },
            error: function () {
                mostrarError('Error al conectar con el servidor');
            }
        });
    }

    // Click en el botón Validar
    $('#btn_consultar').on('click', function () {
        consultarCertificado();
    });

    function solicitarCertificadoDesdeServidor(rut) {
        if (!rut) {
            return Promise.reject(new Error('RUT no especificado.'));
        }

        return $.ajax({
            url: 'controlador/GenerarCertificadoPDF.php',
            method: 'POST',
            dataType: 'json',
            data: { rut: rut }
        });
    }

    // Formatea fechas: acepta Date o YYYY-MM-DD. Si upperMonth true convierte mes a MAYÚSCULAS
    function formatFechaLong(input, upperMonth) {
        try {
            const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            let d;
            if (input instanceof Date) {
                d = input;
            } else if (/^\d{4}-\d{2}-\d{2}$/.test(input)) {
                const parts = input.split('-');
                d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            } else {
                // intentar parseo general
                d = new Date(input);
                if (isNaN(d)) return input;
            }
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}-${month}-${year}`;
        } catch (e) {
            return input;
        }
    }

    // Permitir Enter en el input
    $('#rut_consulta').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            consultarCertificado();
        }
    });

    // Exponer una función global que use la lógica existente para generar/subir el certificado
    // Recibe jsonData (string) y rut (string)
    window.generarCertificado = function (jsonData, rut) {
        let data;
        try {
            data = JSON.parse(jsonData);
        } catch (e) {
            console.error('Error parseando datos para generar certificado:', e);
            if (window.Swal) Swal.fire('Error', 'Datos inválidos para generar certificado', 'error');
            return;
        }

        if (window.Swal) {
            Swal.fire({
                title: 'Generando certificado...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        solicitarCertificadoDesdeServidor(rut)
            .done(function (res) {
                if (window.Swal) Swal.close();
                if (res && res.success && res.url) {
                    const newWindow = window.open(res.url, '_blank');
                    if (!newWindow && window.Swal) {
                        Swal.fire('Listo', 'Certificado generado. Revisa la descarga bloqueada por el navegador.', 'success');
                    }

                    // Mostrar opción de enviar por correo si tiene email
                    if (res.has_email && res.email_address) {
                        setTimeout(() => {
                            Swal.fire({
                                title: 'Enviar por correo',
                                html: `
                                    <p>¿Desea enviar el certificado a <strong>${res.email_address}</strong>?</p>
                                    <div class="alert alert-warning mt-3" style="font-size: 0.9em;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Importante:</strong> El certificado debe estar firmado y timbrado 
                                        por el Director(a) del establecimiento antes de enviarlo para que tenga validez legal.
                                    </div>
                                `,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Sí, enviar',
                                cancelButtonText: 'No',
                                confirmButtonColor: '#28a745',
                                cancelButtonColor: '#6c757d'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    enviarCertificadoPorCorreo(res.rut, res.path, data.nombre || 'Egresado', res.email_address);
                                }
                            });
                        }, 500);
                    }
                } else {
                    if (window.Swal) Swal.fire('Error', (res && res.message) ? res.message : 'No se pudo generar el certificado', 'error');
                }
            })
            .fail(function () {
                if (window.Swal) Swal.close();
                if (window.Swal) Swal.fire('Error', 'No se pudo generar el certificado. Intente nuevamente.', 'error');
            });
    };

    // Función para enviar certificado por correo
    function enviarCertificadoPorCorreo(rut, pdfPath, nombre, email) {
        if (window.Swal) {
            Swal.fire({
                title: 'Enviando correo...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        $.ajax({
            url: 'controlador/EnviarCertificadoEmail.php',
            method: 'POST',
            dataType: 'json',
            data: {
                rut: rut,
                pdf_path: pdfPath,
                nombre: nombre,
                email: email
            },
            success: function (res) {
                if (window.Swal) Swal.close();
                if (res && res.success) {
                    Swal.fire('Enviado', res.message || 'Certificado enviado exitosamente', 'success');
                } else {
                    Swal.fire('Error', res.message || 'No se pudo enviar el correo', 'error');
                }
            },
            error: function () {
                if (window.Swal) Swal.close();
                Swal.fire('Error', 'No se pudo enviar el correo. Intente nuevamente.', 'error');
            }
        });
    }
});
