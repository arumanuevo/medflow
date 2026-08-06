<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MedFlow')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Modern Sidebar CSS -->
    <link rel="stylesheet" href="{{ asset('css/modern-sidebar.css') }}">
    <!-- Shared Styles CSS -->
    <link rel="stylesheet" href="{{ asset('css/shared-styles.css') }}">
    <!-- Table Styles CSS (fuerza consistencia en todas las tablas) -->
    <link rel="stylesheet" href="{{ asset('css/table-styles.css') }}">

    @stack('styles')

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        /* Estilos para páginas públicas (sin sidebar) */
        .public-page .modern-sidebar {
            display: none !important;
        }
        .public-page .modern-main {
            margin-left: 0 !important;
        }
        .public-page .modern-header {
            display: none !important;
        }
        .public-page .modern-content {
            padding: 0;
            background: transparent;
        }

        /* Toggle de sidebar en móvil */
        .sidebar-toggle-btn {
            background: transparent;
            border: none;
            color: #6c757d;
            font-size: 1.5rem;
            padding: 0.25rem 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .sidebar-toggle-btn:hover {
            color: #0d6efd;
        }
        @media (min-width: 768px) {
            .sidebar-toggle-btn {
                display: none;
            }
        }

        /* Ajustes para el header */
        .modern-header .header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .modern-header .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <div class="modern-app {{ auth()->check() ? '' : 'public-page' }}">
        
        {{-- ✅ SIDEBAR --}}
        @auth
            @include('layouts.partials.modern-sidebar')
            {{-- ✅ OVERLAY MÓVIL --}}
            <div class="sidebar-overlay" id="sidebarOverlay"></div>
        @endauth

        {{-- ✅ MAIN CONTENT --}}
        <div class="modern-main" id="modernMain">
            
            {{-- ✅ HEADER --}}
            @auth
                @include('layouts.partials.modern-header')
            @endauth

            {{-- ✅ CONTENIDO --}}
            <div class="modern-content">
                @yield('content')
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Scripts del sidebar moderno --}}
    @include('layouts.partials.modern-scripts')
    
    @stack('scripts')
</body>
</html>
