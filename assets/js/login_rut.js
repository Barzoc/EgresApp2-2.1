// login_rut.js
// Valida RUT en la pantalla de login y consulta nombre asociado via validar.php

$(function () {
  // Crear el contenedor de información si no existe
  if (!$('.info-egresado-container').length) {
    $('.login-container').append('<div class="info-egresado-container"></div>');
  }

  function mostrarMensaje(text, tipo = 'danger') {
    let $err = $('#rut-error');
    $err.removeClass('text-success text-danger');
    $err.addClass(tipo === 'success' ? 'text-success' : 'text-danger');
    $err.text(text).show();
  }

  function doValidarRut() {
    const rut = $('#rut_login').val().trim();
    if (!rut) {
      $('.info-egresado-container').removeClass('visible').empty();
      mostrarMensaje('Ingrese un RUT para validar');
      return;
    }
    $('#rut-error').hide();
    $.ajax({
      url: 'validar.php',
      method: 'POST',
      dataType: 'json',
      data: { rut: rut },
      success: function (res) {
        if (!res.success) {
          mostrarMensaje(res.message || 'Error desconocido');
          $('#nombre_rut').text('');
          return;
        }
        if (!res.valid) {
          mostrarMensaje('RUT inválido');
          $('#nombre_rut').text('');
          return;
        }
        // RUT válido
        if (res.nombre) {
          const tituloPrincipal = res.titulo_obtenido || (res.titulos && res.titulos.length > 0 ? res.titulos[0].nombre : (res.titulo || ''));

          const $container = $('.info-egresado-container');
          $container.empty();

          // Función para formatear fecha a dd/mm/yyyy
          const formatDate = (dateStr) => {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length === 3) {
              return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            return dateStr;
          };

          const parts = [];
          parts.push('<div class="info-egresado">');
          parts.push(`<h5 class="mb-3"><strong>${res.nombre}</strong></h5>`);
          parts.push('<div class="info-section">');

          if (tituloPrincipal) {
            parts.push(`<p class="mb-1"><strong>RUT:</strong> ${res.rut || $('#rut_login').val()}</p>`);
            parts.push(`<p class="mb-1"><strong>Título:</strong> ${tituloPrincipal}</p>`);
            if (res.fechaTitulo) parts.push(`<p class="mb-1"><strong>Fecha de Egreso:</strong> ${formatDate(res.fechaTitulo)}</p>`);
            if (res.numeroRegistro) parts.push(`<p class="mb-1"><strong>N° Registro:</strong> ${res.numeroRegistro}</p>`);
            parts.push('<div class="mt-3"><button id="btn_generar_cert" class="btn btn-sm btn-success"><i class="fas fa-file-pdf"></i> Generar Certificado</button></div>');
          }

          parts.push('</div></div>');
          $container.html(parts.join('')).addClass('visible');
          $('#rut-error').hide();
          // Enlazar handler al botón de generar certificado si existe
          if ($('#btn_generar_cert').length) {
            const tituloObj = (res.titulos && res.titulos.length > 0) ? res.titulos[0] : null;
            const genData = {
              nombre: res.nombre || '',
              titulo: res.titulo_obtenido || (tituloObj ? tituloObj.nombre : (res.titulo || '')),
              fechaTitulo: tituloObj ? tituloObj.fecha : (res.fechaTitulo || res.fechaTituloPrincipal || ''),
              numeroRegistro: res.numeroRegistro || ''
            };

            $('#btn_generar_cert').on('click', function () {
              if (window.generarCertificado) {
                window.generarCertificado(JSON.stringify(genData), $('#rut_login').val().trim());
              } else {
                console.warn('generarCertificado no está definido en el scope global');
              }
            });
          }
        } else {
          $('.info-egresado-container').removeClass('visible').empty();
          mostrarMensaje('RUT válido pero no se encontró registro', 'danger');
        }
      },
      error: function (xhr, status, err) {
        mostrarMensaje('Error al conectar con el servidor');
        $('.info-egresado-container').removeClass('visible').empty();
      }
    });
  }

  // Click en el botón Validar
  $('#validate_rut_btn').on('click', function () {
    doValidarRut();
  });

  // Permitir Enter en el input para disparar la validación
  $('#rut_login').on('keypress', function (e) {
    if (e.which === 13) { // Enter
      e.preventDefault();
      doValidarRut();
    }
  });
});
