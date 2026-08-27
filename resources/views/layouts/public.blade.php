<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MedFlow')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @stack('styles')

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #d9e2f0 50%, #c5d3e8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .public-container {
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
        }

        .public-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .public-header .logo {
            font-size: 2rem;
            font-weight: 800;
            color: #1a202c;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .public-header .logo i {
            color: #0d6efd;
            font-size: 2.2rem;
        }

        .public-header .logo span {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .public-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease;
        }

        .public-card:hover {
            transform: translateY(-4px);
        }

        .login-content {
            width: 100%;
        }

        .login-icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #e8f0fe, #d4e4ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-icon-wrapper i {
            font-size: 2.5rem;
            color: #0d6efd;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            width: 100%;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1);
        }

        /* ✅ Password wrapper - Estilo mejorado */
        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper .form-control {
            padding-right: 3.5rem;
            width: 100%;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #a0aec0;
            padding: 0.25rem 0.5rem;
            cursor: pointer;
            transition: color 0.3s ease;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #0d6efd;
        }

        .password-toggle:focus {
            outline: none;
        }

        .password-toggle i {
            pointer-events: none;
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0a5fd9);
            border: none;
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.35);
            color: #fff;
        }

        .btn-outline-danger {
            border: 2px solid #ea4335;
            color: #ea4335;
            background: transparent;
        }

        .btn-outline-danger:hover {
            background: #ea4335;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(234, 67, 53, 0.2);
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .form-check-label {
            font-size: 0.9rem;
            color: #4a5568;
        }

        .public-footer {
            text-align: center;
            margin-top: 2rem;
            color: #718096;
            font-size: 0.8rem;
        }

        .public-footer a {
            color: #718096;
            text-decoration: none;
        }

        .public-footer a:hover {
            color: #0d6efd;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #a0aec0;
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider::before {
            margin-right: 1rem;
        }

        .divider::after {
            margin-left: 1rem;
        }

        .alert {
            border-radius: 12px;
            border: none;
            font-size: 0.9rem;
            padding: 0.75rem 1rem;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fff5f5, #fed7d7);
            color: #9b2c2c;
        }

        .alert-success {
            background: linear-gradient(135deg, #f0fff4, #e6fffa);
            color: #22543d;
        }

        .invalid-feedback {
            font-size: 0.8rem;
            margin-top: 0.3rem;
            color: #dc3545;
            display: block;
        }

        .text-muted {
            color: #718096 !important;
        }

        @media (max-width: 576px) {
            .public-card {
                padding: 1.5rem;
            }

            .public-header .logo {
                font-size: 1.6rem;
            }

            .login-icon-wrapper {
                width: 60px;
                height: 60px;
            }

            .login-icon-wrapper i {
                font-size: 2rem !important;
            }

            .public-container {
                max-width: 100%;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .public-card {
            animation: fadeInUp 0.6s ease forwards;
        }
    </style>
</head>
<body>
    <div class="public-container">
        <div class="public-header">
            <a href="{{ route('landing') }}" class="logo">
                <i class="bi bi-activity"></i>
                <span>MedFlow</span>
            </a>
        </div>

        <div class="public-card">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>

        <div class="public-footer">
            &copy; {{ date('Y') }} <a href="{{ route('landing') }}">MedFlow</a> - 
            Gestión Inteligente de Sensores.
            <span class="mx-2">·</span>
            <a href="#">Términos</a>
            <span class="mx-1">·</span>
            <a href="#">Privacidad</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>