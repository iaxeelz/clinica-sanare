<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Clínica Sanare') }} - @yield('title')</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AdminLTE 3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- OverlayScrollbars -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@1.13.1/css/OverlayScrollbars.min.css">

    <!-- Estilos personalizados de Sanare -->
    <style>
        :root {
            --sanare-primary: #1A5276;
            --sanare-secondary: #2ECC71;
            --sanare-accent: #F39C12;
        }

        body {
            font-family: 'Nunito', sans-serif;
        }

        .main-header .navbar {
            background-color: var(--sanare-primary) !important;
        }

        .main-header .navbar .nav-link {
            color: #fff !important;
        }

        .main-sidebar {
            background-color: #1a2935 !important;
        }

        .brand-link {
            background-color: var(--sanare-primary) !important;
            border-bottom: 2px solid var(--sanare-secondary);
        }

        .brand-text {
            color: #fff !important;
        }

        .small-box .icon {
            font-size: 45px;
        }

        .small-box .inner h3 {
            font-size: 38px;
        }

        .nav-sidebar .nav-link.active {
            background-color: var(--sanare-secondary) !important;
            color: #fff !important;
        }

        .nav-sidebar .nav-link:hover {
            background-color: rgba(46, 204, 113, 0.1);
        }

        .user-panel .info a {
            color: #fff !important;
        }

        .dropdown-menu .dropdown-item {
            color: #333;
        }

        .btn-primary {
            background-color: var(--sanare-primary) !important;
            border-color: var(--sanare-primary) !important;
        }

        .btn-primary:hover {
            background-color: #0d3a52 !important;
            border-color: #0d3a52 !important;
        }

        .btn-success {
            background-color: var(--sanare-secondary) !important;
            border-color: var(--sanare-secondary) !important;
        }

        .badge-warning {
            background-color: var(--sanare-accent) !important;
        }

        .content-header h1 {
            color: var(--sanare-primary);
        }
    </style>

    @stack('css')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars text-white"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('dashboard') }}" class="nav-link text-white">Inicio</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Reloj -->
                <li class="nav-item d-none d-sm-inline-block">
                    <span class="nav-link text-white" id="reloj">
                        <i class="far fa-clock"></i>
                        <span id="fecha-hora"></span>
                    </span>
                </li>

                <!-- Notificaciones -->
                <li class="nav-item dropdown">
                    <a class="nav-link text-white" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge" id="notificaciones-count">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notificaciones-dropdown">
                        <span class="dropdown-header">Notificaciones</span>
                        <div class="dropdown-divider"></div>
                        <div id="notificaciones-lista">
                            <a href="#" class="dropdown-item text-center text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Cargando...
                            </a>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">Ver todas</a>
                    </div>
                </li>

                <!-- Usuario -->
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle text-white" data-toggle="dropdown">
                        <img src="https://www.gravatar.com/avatar/{{ md5(Auth::user()->email ?? 'default') }}?d=mp&s=40"
                            class="user-image img-circle elevation-2" alt="User Image">
                        <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'Usuario' }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <li class="user-header bg-primary">
                            <img src="https://www.gravatar.com/avatar/{{ md5(Auth::user()->email ?? 'default') }}?d=mp&s=90"
                                class="img-circle elevation-2" alt="User Image">
                            <p>
                                {{ Auth::user()->name ?? 'Usuario' }}
                                <small>{{ Auth::user()->email ?? '' }}</small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <a href="#" class="btn btn-default btn-flat">Perfil</a>
                            <a href="{{ route('logout') }}" class="btn btn-default btn-flat float-right"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Cerrar Sesión
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="brand-link">
                <span class="brand-text font-weight-light"><i class="fas fa-heartbeat mr-2"></i>Clínica Sanare</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://www.gravatar.com/avatar/{{ md5(Auth::user()->email ?? 'default') }}?d=mp&s=40"
                            class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{ Auth::user()->name ?? 'Usuario' }}</a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    @include('vendor.adminlte.partials.sidebar')
                </nav>
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>@yield('page-title', 'Dashboard')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>
        <!-- /.content-wrapper -->

        <!-- Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Versión</b> 1.0.0
            </div>
            <strong>Copyright &copy; {{ date('Y') }} Clínica Sanare.</strong> Todos los derechos reservados.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE 3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <!-- OverlayScrollbars -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@1.13.1/js/OverlayScrollbars.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Reloj en tiempo real
        function actualizarReloj() {
            const ahora = new Date();
            const opciones = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                weekday: 'long',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'America/Lima'
            };
            document.getElementById('fecha-hora').textContent = ahora.toLocaleString('es-PE', opciones);
        }
        actualizarReloj();
        setInterval(actualizarReloj, 1000);

        // Sistema de notificaciones
        function cargarNotificaciones() {
            fetch('/notificaciones')
                .then(response => response.json())
                .then(data => {
                    const lista = document.getElementById('notificaciones-lista');
                    const count = document.getElementById('notificaciones-count');

                    if (data.length === 0) {
                        lista.innerHTML = `
                            <a href="#" class="dropdown-item text-center text-muted">
                                <i class="fas fa-check-circle"></i> No hay notificaciones
                            </a>
                        `;
                        count.textContent = '0';
                    } else {
                        let html = '';
                        data.slice(0, 5).forEach(notif => {
                            html += `
                                <a href="${notif.link || '#'}" class="dropdown-item">
                                    <i class="${notif.icon || 'fas fa-circle'} mr-2 ${notif.color || 'text-info'}"></i>
                                    ${notif.message}
                                    <span class="float-right text-muted text-sm">${notif.time || 'Ahora'}</span>
                                </a>
                                <div class="dropdown-divider"></div>
                            `;
                        });
                        lista.innerHTML = html;
                        count.textContent = data.length;
                    }
                })
                .catch(error => {
                    console.error('Error cargando notificaciones:', error);
                });
        }

        cargarNotificaciones();
        setInterval(cargarNotificaciones, 30000);
    </script>

    @stack('js')
</body>

</html>