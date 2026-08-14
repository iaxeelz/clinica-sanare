<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clínica Sanare</title>

    <!-- AdminLTE CSS (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- ============================================ -->
    <!-- FLATPICKR CSS -->
    <!-- ============================================ -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">

    <!-- ============================================ -->
    <!-- STACK PARA ESTILOS DE VISTAS -->
    <!-- ============================================ -->
    @stack('styles')

    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        .main-sidebar {
            background-color: #1a2935 !important;
        }

        .brand-link {
            background-color: #1A5276 !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            padding: 15px 10px !important;
            min-height: 70px;
        }

        /* Logo completo (sidebar expandido) */
        .brand-link .brand-image {
            max-height: 55px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        /* Logo recortado (sidebar contraído) - oculto por defecto */
        .brand-link .brand-image-mini {
            display: none;
            max-height: 40px;
            width: auto;
            object-fit: contain;
        }

        /* Ocultar el texto del brand */
        .brand-link .brand-text {
            display: none !important;
        }

        /* ============================================
           CORRECCIÓN: Mostrar/ocultar logos
        ============================================ */
        .sidebar-mini .brand-link .brand-image {
            display: none !important;
        }

        .sidebar-mini .brand-link .brand-image-mini {
            display: block !important;
        }

        .sidebar-mini:not(.sidebar-collapse) .brand-link .brand-image {
            display: block !important;
        }

        .sidebar-mini:not(.sidebar-collapse) .brand-link .brand-image-mini {
            display: none !important;
        }

        .main-header .navbar {
            background-color: #1A5276 !important;
        }

        .main-header .navbar .nav-link {
            color: #fff !important;
        }

        .nav-sidebar .nav-link.active {
            background-color: #2ECC71 !important;
            color: #fff !important;
        }

        .content-header h1 {
            color: #1A5276;
        }

        /* Ocultar el user-panel (nombre de usuario en el sidebar) */
        .user-panel {
            display: none !important;
        }

        .sidebar {
            padding-top: 0 !important;
        }

        .brand-link .brand-image,
        .brand-link .brand-image-mini {
            transition: all 0.3s ease;
        }

        /* ============================================
           RESPONSIVE - MÓVIL Y TABLET
        ============================================ */

        /* Tablets y dispositivos medianos */
        @media (max-width: 991.98px) {

            /* Ajustar el sidebar en tablet */
            .main-sidebar {
                width: 250px !important;
            }

            .content-wrapper,
            .main-footer {
                margin-left: 0 !important;
            }

            /* Logo más pequeño en tablet */
            .brand-link .brand-image {
                max-height: 45px;
            }

            .brand-link .brand-image-mini {
                max-height: 35px;
            }

            /* Ajustar el navbar */
            .main-header .navbar {
                padding: 0 10px;
            }

            .main-header .navbar .nav-link {
                font-size: 14px;
            }

            /* Ocultar texto "Inicio" en móvil */
            .main-header .navbar .d-none.d-sm-inline-block {
                display: none !important;
            }
        }

        /* Móviles */
        @media (max-width: 767.98px) {

            /* Sidebar oculto por defecto en móvil */
            .main-sidebar {
                transform: translateX(-250px);
                transition: transform 0.3s ease-in-out;
                width: 250px !important;
            }

            .sidebar-open .main-sidebar {
                transform: translateX(0);
            }

            .content-wrapper,
            .main-footer {
                margin-left: 0 !important;
            }

            /* Ajustar el navbar en móvil */
            .main-header .navbar {
                padding: 0 8px;
                min-height: 50px;
            }

            .main-header .navbar .nav-link {
                font-size: 13px;
                padding: 8px 10px;
            }

            /* Logo más pequeño en móvil */
            .brand-link {
                min-height: 60px !important;
                padding: 10px 8px !important;
            }

            .brand-link .brand-image {
                max-height: 38px;
            }

            .brand-link .brand-image-mini {
                max-height: 30px;
            }

            /* Ajustar contenido */
            .content-header h1 {
                font-size: 20px !important;
            }

            .content-header .container-fluid {
                padding: 10px 12px !important;
            }

            .content .container-fluid {
                padding: 0 12px !important;
            }

            /* Reloj más pequeño en móvil */
            #reloj {
                font-size: 12px !important;
            }

            /* Botón de menú más grande para móvil */
            [data-widget="pushmenu"] {
                padding: 10px 12px !important;
                font-size: 18px;
            }

            /* Ajustar el footer */
            .main-footer {
                font-size: 12px;
                padding: 10px 15px !important;
                text-align: center;
            }
        }

        /* Móviles muy pequeños */
        @media (max-width: 480px) {
            .main-header .navbar {
                min-height: 45px;
            }

            .main-header .navbar .nav-link {
                font-size: 12px;
                padding: 6px 8px;
            }

            .brand-link .brand-image {
                max-height: 32px;
            }

            .brand-link .brand-image-mini {
                max-height: 26px;
            }

            .brand-link {
                min-height: 50px !important;
                padding: 8px 5px !important;
            }

            .content-header h1 {
                font-size: 18px !important;
            }

            #reloj {
                font-size: 10px !important;
            }

            #reloj i {
                font-size: 12px;
            }

            [data-widget="pushmenu"] {
                padding: 8px 10px !important;
                font-size: 16px;
            }

            .main-footer {
                font-size: 10px;
                padding: 8px 10px !important;
            }
        }

        /* Ajustes para landscape en móvil */
        @media (max-height: 500px) and (orientation: landscape) {
            .brand-link {
                min-height: 45px !important;
                padding: 5px 8px !important;
            }

            .brand-link .brand-image {
                max-height: 30px;
            }

            .brand-link .brand-image-mini {
                max-height: 25px;
            }

            .main-header .navbar {
                min-height: 40px;
            }

            .main-header .navbar .nav-link {
                padding: 4px 8px;
                font-size: 12px;
            }
        }

        /* Overlay para móvil cuando el sidebar está abierto */
        .sidebar-open .sidebar-overlay {
            display: block !important;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1038;
        }

        /* ============================================
           ESTILOS PARA FLATPICKR
        ============================================ */
        .flatpickr-day.disabled {
            opacity: 0.3 !important;
        }
        .flatpickr-day.available {
            background: #2ECC71 !important;
            color: white !important;
            border-color: #2ECC71 !important;
        }
        .flatpickr-day.available:hover {
            background: #27ae60 !important;
            border-color: #27ae60 !important;
        }

        /* ============================================
           ESTILOS PARA NOTIFICACIONES
        ============================================ */
        .notification-item {
            cursor: pointer;
            transition: background 0.2s;
        }
        .notification-item:hover {
            background: #f8f9fa;
        }
        .notification-item .media-body {
            padding: 5px 0;
        }
        .dropdown-header-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }
        #notificationList {
            max-height: 350px;
            overflow-y: auto;
        }
        #notificationList::-webkit-scrollbar {
            width: 5px;
        }
        #notificationList::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }
        #notificationList::-webkit-scrollbar-thumb:hover {
            background: #aaa;
        }
        #notificationMenu .dropdown-footer {
            border-top: 1px solid #e9ecef;
            padding: 10px 0;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('dashboard') }}" class="nav-link">Inicio</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-none d-sm-block">
                    <span class="nav-link" id="reloj">
                        <i class="far fa-clock"></i> <span id="fecha-hora"></span>
                    </span>
                </li>
                <!-- ============================================ -->
                <!-- NOTIFICACIONES CON CAMPANITA (ACTUALIZADO) -->
                <!-- ============================================ -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" id="notificationDropdown">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge" id="notif-count" style="display: none;">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notificationMenu" style="min-width: 350px; max-height: 450px; overflow-y: auto;">
                        <div class="dropdown-header">
                            <strong>Notificaciones</strong>
                            <button class="btn btn-sm btn-link float-right" id="markAllReadBtn">Marcar todas</button>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div id="notificationList">
                            <div class="text-center py-3 text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Cargando...
                            </div>
                        </div>
                        <div class="dropdown-footer text-center">
                            <a href="{{ route('notifications.index') }}" class="text-muted">Ver todas las notificaciones</a>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fas fa-user"></i> <span
                            class="d-none d-md-inline">{{ Auth::user()->name ?? 'Usuario' }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('dashboard') }}" class="brand-link">
                <img src="{{ asset('images/logo-sanare-completo.png') }}" alt="Logo Sanare" class="brand-image">
                <img src="{{ asset('images/logo-sanare-recortado.png') }}" alt="Logo Sanare Mini"
                    class="brand-image-mini">
                <span class="brand-text font-weight-light">Sanare</span>
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    @include('vendor.adminlte.partials.sidebar')
                </nav>
            </div>
        </aside>

        <!-- Content -->
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>@yield('page-title', 'Dashboard')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                                <li class="breadcrumb-item active">@yield('page-title', 'Dashboard')</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong>Clínica Sanare &copy; {{ date('Y') }}</strong>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ============================================ -->
    <!-- FLATPICKR JS -->
    <!-- ============================================ -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        function actualizarReloj() {
            const ahora = new Date();
            document.getElementById('fecha-hora').textContent = ahora.toLocaleString('es-PE');
        }
        actualizarReloj();
        setInterval(actualizarReloj, 1000);

        // Manejar los logos al cargar y al colapsar
        $(document).ready(function () {
            function toggleLogos() {
                if ($('body').hasClass('sidebar-collapse')) {
                    $('.brand-image').hide();
                    $('.brand-image-mini').show();
                } else {
                    $('.brand-image').show();
                    $('.brand-image-mini').hide();
                }
            }

            toggleLogos();

            $(document).on('click', '[data-widget="pushmenu"]', function () {
                setTimeout(toggleLogos, 150);
            });

            // Cerrar sidebar al hacer clic en overlay en móvil
            $(document).on('click', '.sidebar-overlay', function () {
                $('body').removeClass('sidebar-open');
            });
        });

        // ============================================
        // NOTIFICACIONES - JAVASCRIPT
        // ============================================
        function loadNotifications() {
            fetch('/notifications/unread')
                .then(response => response.json())
                .then(data => {
                    const count = data.count;
                    const list = document.getElementById('notificationList');
                    const badge = document.getElementById('notif-count');

                    // Actualizar badge
                    badge.textContent = count;
                    badge.style.display = count > 0 ? 'inline' : 'none';

                    if (count === 0) {
                        list.innerHTML = `
                            <div class="text-center py-3 text-muted">
                                <i class="fas fa-check-circle"></i> No hay notificaciones nuevas
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    data.notifications.slice(0, 10).forEach(notif => {
                        html += `
                            <div class="dropdown-item notification-item" data-id="${notif.id}">
                                <div class="media">
                                    <div class="media-left mr-2">
                                        <span class="badge badge-${notif.color} p-2">
                                            <i class="fas ${notif.icon}"></i>
                                        </span>
                                    </div>
                                    <div class="media-body">
                                        <h6 class="dropdown-header-title">${notif.title}</h6>
                                        <p class="text-sm text-muted mb-0">${notif.message}</p>
                                        <small class="text-muted">${notif.time_ago}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    if (data.notifications.length > 10) {
                        html += `
                            <div class="text-center py-2">
                                <small class="text-muted">Y ${data.notifications.length - 10} notificaciones más</small>
                            </div>
                        `;
                    }

                    list.innerHTML = html;

                    // Click en notificación
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const id = this.dataset.id;
                            fetch(`/notifications/${id}/read`, { method: 'POST' })
                                .then(() => {
                                    loadNotifications();
                                    // Redirigir si tiene link
                                    const link = this.querySelector('a');
                                    if (link) window.location.href = link.href;
                                });
                        });
                    });
                })
                .catch(error => {
                    console.error('Error cargando notificaciones:', error);
                });
        }

        // Cargar notificaciones al inicio
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();

            // Recargar cada 30 segundos
            setInterval(loadNotifications, 30000);

            // Marcar todas como leídas
            document.getElementById('markAllReadBtn')?.addEventListener('click', function(e) {
                e.preventDefault();
                fetch('/notifications/read-all', { method: 'POST' })
                    .then(() => loadNotifications());
            });
        });
    </script>

    <!-- ============================================ -->
    <!-- STACK PARA SCRIPTS DE VISTAS -->
    <!-- ============================================ -->
    @stack('scripts')

</body>

</html>