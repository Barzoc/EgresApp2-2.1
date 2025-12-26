
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon para la pestaña del navegador -->
    <link rel="icon" type="image/webp" href="../assets/img/imagenes/icon_white.png">
    <!-- Metadatos para la descripción y previsualización de la página -->
    <meta name="description" content="Página de recuperación de contraseña del sistema CRUD para la gestión de egresados.">
    <meta property="og:title" content="Recuperación de Contraseña - EgresApp2">
    <meta property="og:description" content="Solicite un link de recuperación de contraseña para restablecer su acceso al sistema de gestión de egresados.">
    <meta property="og:image" content="../assets/img/imagenes/icon.png">
    <meta property="og:type" content="website">
    <title>Recuperación de Contraseña - EgresApp2</title>
    <link rel="stylesheet" href="../assets/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/chatbot.css">
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo" style="display: flex; flex-direction: column; align-items: center;">
            <img src="../assets/img/imagenes/icon.png" alt="Icono del Sistema de Gestión de Egresados" style="width: 100px; height: auto;">
            <a href="#"><b>Recuperar</b> Contraseña</a>
        </div>
        <!-- Tarjeta para recuperación de contraseña -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Ingresa tu correo electrónico para recuperar tu contraseña</p>
                <!-- Formulario para solicitud de recuperación -->
                <form action="/path_to_recovery" method="post" id="recovery-form">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Email" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Solicitar nueva contraseña</button>
                        </div>
                    </div>
                </form>
                <!-- Alerta de notificación -->
                <div class="alert alert-success mt-3" style="display: none;" id="alert-recovery">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Link Enviado!</h5>
                    Se ha enviado un link de recuperación de contraseña a tu correo electrónico.
                </div>
            </div>
        </div>
    </div>
    <!-- Chatbot assistant widget -->
    <div id="chatbot-widget" class="chatbot">
        <button id="chatbot-toggle" class="chatbot-toggle" title="Asistente virtual">
            <img src="../assets/img/imagenes/chatbot_mascota.png.png" alt="Asistente" class="chatbot-toggle-face">
            <div class="chatbot-toggle-text">
                <span>¿Te ayudo?</span>
                <small>EgresApp Assistant</small>
            </div>
        </button>
        <div id="chatbot-panel" class="chatbot-panel hidden">
            <header>
                <div>
                    <h6 class="mb-1">EgresApp Assistant</h6>
                    <small>IA local para ayudarte</small>
                </div>
                <button id="chatbot-close" class="btn btn-sm btn-light"><i class="fas fa-times"></i></button>
            </header>
            <div class="chatbot-hero">
                <img src="../assets/img/imagenes/chatbot_mascota.png.png" alt="Mascota EgresApp">
            </div>
            <div id="chatbot-messages" class="chatbot-messages">
                <div class="chatbot-empty-state">Inicia la conversación o elige una acción rápida.</div>
            </div>
            <div id="chatbot-quick" class="chatbot-quick-actions"></div>
            <div class="chatbot-input">
                <textarea id="chatbot-text" placeholder="Escribe tu pregunta..."></textarea>
                <button id="chatbot-send"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <!-- Scripts necesarios para AdminLTE y Bootstrap -->
    <script src="../assets/plugins/jquery/jquery.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/adminlte.min.js"></script>
    <script>
        window.CHATBOT_ENDPOINT = '../controlador/ChatbotController.php';
    </script>
    <script src="../assets/js/chatbot.js"></script>
    <script>
        // Script para simular el envío y mostrar la alerta
        $('#recovery-form').on('submit', function(e) {
            e.preventDefault();
            // Aquí se agregaría el código para manejar la lógica de recuperación de contraseña
            // ...

            // Mostrar la alerta
            $('#alert-recovery').show();

            // Esperar 2 segundos y luego redirigir al login
            setTimeout(function() {
                window.location.href = '../index.html'; // Asegúrate de cambiar esto por la ruta correcta del login
            }, 2000);
        });
    </script>
</body>
</html>
