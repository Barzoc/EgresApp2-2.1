<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <span class="nav-link font-weight-bold" style="color: #333; font-size: 1.1rem;">
                        Bienvenido al portal de gestión de egresados
                    </span>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <!-- Right navbar links -->
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="./inicio.php" class="brand-link">
                <img src="../assets/img/imagenes/icon_white.png" alt="Logo del Aplicativo"
                    class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">EgresApp2</span>
            </a>
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item">
                            <a href="./adm_escaner.php" class="nav-link">
                                <i class="nav-icon fas fa-qrcode"></i>
                                <p>Escáner QR</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="./adm_egresado.php" class="nav-link">
                                <i class="nav-icon fas fa-table"></i>
                                <p>Añadir Egresados</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="./adm_agregarTitulo.php" class="nav-link">
                                <i class="nav-icon fas fa-graduation-cap"></i>
                                <p>Añadir Titulos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="./adm_dashboard.php" class="nav-link">
                                <i class="nav-icon fas fa-book-reader"></i>
                                <p>Estadísticas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="./adm_usuario.php" class="nav-link">
                                <i class="nav-icon fas fa-user-plus"></i>
                                <p>Crear Usuario</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../controlador/LogoutController.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Salir</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->

                <!-- Institution Branding (Logo + Motto + Mascots) -->
                <div class="text-center mt-4">
                    <img src="../assets/img/sidebar_mascotas.png" alt="Liceo Bicentenario Domingo Santa María"
                        style="width: 100%; height: auto;">
                </div>
            </div>
            <!-- /.sidebar -->
        </aside>