<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MedFlow')</title>

    <!-- Bootstrap 5 (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @stack('styles')

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }
        .landing-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1rem 0;
        }
        .btn-google {
            background: #fff;
            border: 1px solid #ddd;
            color: #333;
            transition: all 0.2s;
        }
        .btn-google:hover {
            background: #f1f1f1;
            border-color: #bbb;
        }
        .card-shadow {
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <!-- Navbar simple -->
    <nav class="navbar navbar-expand-lg landing-header">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('landing') }}">
                <i class="bi bi-water"></i> MedFlow
            </a>
            <div class="ms-auto">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary me-2">Iniciar sesión</a>
                    <a href="{{ route('landing') }}#registro" class="btn btn-primary">Registrarse</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-4 mt-5">
        <div class="container text-center text-muted">
            <small>&copy; {{ date('Y') }} MedFlow. Todos los derechos reservados.</small>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>