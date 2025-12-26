$(document).ready(function () {
    const apiUrl = '../controlador/UsuarioController.php';
    const $modalUsuario = $('#modalUsuario');
    const $formUsuario = $('#formUsuario');
    const $modalPassword = $('#modalPassword');
    const $formPassword = $('#formPassword');
    const $passwordGroup = $('#grupo-password');
    const $passwordHelp = $('#passwordHelp');

    const showAlert = (opts) => {
        if (window.Swal) {
            Swal.fire(opts);
        } else {
            alert(opts.text || opts.title || 'Operación realizada');
        }
    };

    const callApi = (funcion, data = {}) => {
        return $.ajax({
            url: apiUrl,
            type: 'POST',
            data: { funcion, ...data },
            dataType: 'json'
        });
    };

    const tabla = $('#tablaUsuarios').DataTable({
        ajax: {
            url: apiUrl,
            type: 'POST',
            data: { funcion: 'listar' },
            dataSrc: (json) => Array.isArray(json) ? json : []
        },
        columns: [
            { data: 'nombre', defaultContent: '' },
            { data: 'email', defaultContent: '' },
            {
                data: 'created_at',
                render: (value) => {
                    if (!value) return '—';
                    const date = new Date(value);
                    if (Number.isNaN(date.getTime())) return value;
                    return date.toLocaleDateString('es-CL', { year: 'numeric', month: '2-digit', day: '2-digit' });
                }
            },
            {
                data: null,
                orderable: false,
                render: () => {
                    return `
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-info btn-editar" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-warning btn-password" title="Cambiar contraseña"><i class="fas fa-key"></i></button>
                            <button class="btn btn-danger btn-eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </div>`;
                }
            }
        ],
        language: {
            url: '../assets/plugins/datatables/i18n/es-ES.json'
        },
        responsive: true,
        autoWidth: false
    });

    const resetUsuarioForm = () => {
        $formUsuario[0].reset();
        $('#usuario_id').val('');
        $passwordGroup.removeClass('d-none');
        $passwordHelp.text('La contraseña solo es obligatoria al crear usuarios.');
    };

    $('#btnNuevoUsuario').on('click', () => {
        resetUsuarioForm();
        $modalUsuario.find('.modal-title').text('Nuevo usuario');
        $modalUsuario.modal('show');
    });

    $formUsuario.on('submit', function (e) {
        e.preventDefault();
        const id = $('#usuario_id').val();
        const nombre = $('#usuario_nombre').val().trim();
        const email = $('#usuario_email').val().trim();
        const pass = $('#usuario_pass').val();
        const pass2 = $('#usuario_pass_confirm').val();

        if (!nombre || !email) {
            showAlert({ icon: 'warning', title: 'Completa los campos obligatorios.' });
            return;
        }

        if (!id) {
            if (!pass || !pass2) {
                showAlert({ icon: 'warning', title: 'Ingresa y confirma la contraseña.' });
                return;
            }
            if (pass !== pass2) {
                showAlert({ icon: 'warning', title: 'Las contraseñas no coinciden.' });
                return;
            }
        } else if (pass || pass2) {
            showAlert({ icon: 'info', title: 'Para cambiar la contraseña usa la opción "Cambiar contraseña".' });
            return;
        }

        const funcion = id ? 'actualizar' : 'crear';
        const payload = id
            ? { id, nombre, email }
            : { nombre, email, contrasena: pass };

        callApi(funcion, payload)
            .done((resp) => {
                if (resp.status === 'duplicado') {
                    showAlert({ icon: 'warning', title: 'El correo ya está registrado.' });
                    return;
                }
                if (resp.status !== 'ok') {
                    showAlert({ icon: 'error', title: 'Ocurrió un problema.', text: resp.mensaje || '' });
                    return;
                }
                $modalUsuario.modal('hide');
                tabla.ajax.reload(null, false);
                showAlert({ icon: 'success', title: 'Usuario guardado.' });
            })
            .fail(() => {
                showAlert({ icon: 'error', title: 'No se pudo guardar el usuario.' });
            });
    });

    $('#tablaUsuarios tbody').on('click', '.btn-editar', function () {
        const data = tabla.row($(this).closest('tr')).data();
        if (!data) return;
        resetUsuarioForm();
        $('#usuario_id').val(data.id);
        $('#usuario_nombre').val(data.nombre || '');
        $('#usuario_email').val(data.email || '');
        $passwordGroup.addClass('d-none');
        $passwordHelp.text('La contraseña se mantiene. Usa "Cambiar contraseña" si deseas actualizarla.');
        $modalUsuario.find('.modal-title').text('Editar usuario');
        $modalUsuario.modal('show');
    });

    $('#tablaUsuarios tbody').on('click', '.btn-eliminar', function () {
        const data = tabla.row($(this).closest('tr')).data();
        if (!data) return;
        const confirmar = () => callApi('eliminar', { id: data.id })
            .done((resp) => {
                if (resp.status === 'ok') {
                    tabla.ajax.reload(null, false);
                    showAlert({ icon: 'success', title: 'Usuario eliminado.' });
                } else {
                    showAlert({ icon: 'error', title: 'No se pudo eliminar.' });
                }
            })
            .fail(() => showAlert({ icon: 'error', title: 'Error al eliminar.' }));

        if (window.Swal) {
            Swal.fire({
                title: `Eliminar a ${data.nombre}?`,
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    confirmar();
                }
            });
        } else if (confirm('¿Eliminar este usuario?')) {
            confirmar();
        }
    });

    $('#tablaUsuarios tbody').on('click', '.btn-password', function () {
        const data = tabla.row($(this).closest('tr')).data();
        if (!data) return;
        $formPassword[0].reset();
        $('#password_usuario_id').val(data.id);
        $modalPassword.modal('show');
    });

    $formPassword.on('submit', function (e) {
        e.preventDefault();
        const id = $('#password_usuario_id').val();
        const actual = $('#password_actual').val();
        const pass1 = $('#password_nueva').val();
        const pass2 = $('#password_confirmar').val();

        if (!id || !actual || !pass1 || !pass2) {
            showAlert({ icon: 'warning', title: 'Completa todos los campos.' });
            return;
        }
        if (pass1 !== pass2) {
            showAlert({ icon: 'warning', title: 'Las contraseñas no coinciden.' });
            return;
        }

        callApi('cambiar_password', { id, contrasena: pass1, contrasena_actual: actual })
            .done((resp) => {
                if (resp.status === 'ok') {
                    $modalPassword.modal('hide');
                    showAlert({ icon: 'success', title: 'Contraseña actualizada.' });
                } else {
                    showAlert({ icon: 'error', title: 'No se pudo actualizar la contraseña.' });
                }
            })
            .fail(() => showAlert({ icon: 'error', title: 'Error al cambiar la contraseña.' }));
    });
});
