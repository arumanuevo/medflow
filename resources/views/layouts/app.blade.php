<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'MedFlow')</title>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    @stack('styles')

    <!-- Estilos personalizados para el layout -->
    <style>
        /* Asegurar que el wrapper ocupe toda la altura de la pantalla */
        html, body {
            height: 100%;
        }

        /* Asegurar que el wrapper use flexbox */
        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Asegurar que el content-wrapper ocupe el espacio disponible */
        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Asegurar que el contenido principal ocupe el espacio disponible */
        .content {
            flex: 1;
        }

        /* Asegurar que el footer siempre quede al final */
        .main-footer {
            flex-shrink: 0;
            position: relative;
            width: 100%;
        }

        /* Ajustar el container-fluid dentro del content */
        .content .container-fluid {
            padding-bottom: 1.5rem;
        }

        /* ✅ Estilo para páginas públicas (sin sidebar) */
        .public-page .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .public-page .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-left: 0 !important;
        }

        .public-page .main-header {
            display: none !important;
        }

        .public-page .main-sidebar {
            display: none !important;
        }

        .public-page .content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        .public-page .main-footer {
            display: none !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed {{ auth()->check() ? '' : 'public-page' }}">
    <div class="wrapper">
        <!-- ✅ Navbar - SOLO si el usuario está autenticado -->
        @auth
            @include('layouts.partials.navbar')
        @endauth

        <!-- ✅ Sidebar - SOLO si el usuario está autenticado -->
        @auth
            @include('layouts.partials.sidebar')
        @endauth

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header - SOLO si el usuario está autenticado -->
            @auth
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('title', 'MedFlow')</h1>
                        </div>
                    </div>
                </div>
            </div>
            @endauth

            <!-- Main Content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        <!-- Footer - SOLO si el usuario está autenticado -->
        @auth
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Versión</b> 1.0.0
            </div>
            <strong>Copyright &copy; 2026 <a href="#">MedFlow</a>.</strong> Todos los derechos reservados.
        </footer>
        @endauth
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App - SOLO si el usuario está autenticado -->
    @auth
    <script src="https://cdn.jsdelivr.net/npm/adminlte@3.2/dist/js/adminlte.min.js"></script>
    @endauth

    <!-- ✅ Script para guardar el token en localStorage (desde cookie o sesión) -->
    @auth
    <script>
        // ✅ Intentar obtener token de la sesión (inyectado desde PHP)
        @if(session()->has('sanctum_token'))
            localStorage.setItem('token', '{{ session('sanctum_token') }}');
        @else
            // ✅ Fallback: leer de la cookie
            const cookieToken = document.cookie.split('; ').find(row => row.startsWith('sanctum_token='));
            if (cookieToken) {
                const token = cookieToken.split('=')[1];
                localStorage.setItem('token', token);
            }
        @endif

        // ✅ Log para depuración
        console.log('🔑 Token en localStorage:', localStorage.getItem('token') ? '✅ Presente' : '❌ No encontrado');
    </script>
    @endauth

    <!-- Scripts personalizados (interceptor AJAX y helpers) -->
    @include('partials.scripts')
    @stack('scripts')
</body>
</html>