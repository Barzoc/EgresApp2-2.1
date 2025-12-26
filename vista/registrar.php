<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="../assets/img/imagenes/icon_white.png">
    <meta name="description" content="Regístrese para acceder al sistema de gestión de egresados y manejar la información de exalumnos.">
    <meta property="og:title" content="Registro - EgresApp2">
    <meta property="og:description" content="Únete al sistema EgresApp2 y comienza a administrar los datos relevantes de exalumnos.">
    <meta property="og:image" content="../assets/img/imagenes/icon.png">
    <meta property="og:type" content="website">
    <title>Registro - EgresApp2</title>
    <link rel="stylesheet" href="../assets/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/chatbot.css">
</head>

<body class="hold-transition register-page">
    <div class="register-box">
        <div class="register-logo" style="display: flex; flex-direction: column; align-items: center;">
            <img src="../assets/img/imagenes/icon.png" alt="Icono del Sistema de Gestión de Egresados" style="width: 100px; height: auto;">
            <a href="#"><b>Registro</b> EgresApp2</a>
        </div>

        <div class="card">
            <div class="card-body register-card-body">
                <p class="login-box-msg">Ingrese sus datos</p>

                <form id="registration-form" method="post">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre completo" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" name="email" id="email" placeholder="Correo electrónico" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="contrasena" id="contrasena" placeholder="Contraseña" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="confirmar_contrasena" id="confirmar_contrasena" placeholder="Repetir contraseña" required>
                        <div class="input-group-append">
                            <div the="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" id="boton_registrar" class="btn btn-primary btn-block">Registrar</button>
                        </div>
                    </div>
                </form>

                <!-- Alertas de notificación -->
                <div class="alert alert-success mt-2" style="display: none;" id="success-alert">
                    Usuario creado con éxito.
                </div>
                <div class="alert alert-danger mt-2" style="display: none;" id="error-alert">
                    El usuario no se ha podido crear, el correo es inválido o las contraseñas no coinciden.
                </div>

                <br>
                <a href="../index.html" class="text-center">Ya tengo un usuario</a>
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

    <!-- Scripts necesarios para AdminLTE -->
    <script src="../assets/plugins/jquery/jquery.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/adminlte.min.js"></script>
    <script src="../assets/js/registrar.js"></script>
    <script>
        window.CHATBOT_ENDPOINT = '../controlador/ChatbotController.php';
    </script>
    <script src="../assets/js/chatbot.js"></script>
</body>

</html>
