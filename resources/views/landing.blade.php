<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MedFlow - Gestión Inteligente de Sensores</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- ✅ reCAPTCHA v2 (con checkbox visible) -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <style>
        /* ===== ESTILOS GLOBALES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #d9e2f0 50%, #c5d3e8 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .landing-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }
        
        /* ===== NAVBAR ===== */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .navbar-custom .brand-text {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .navbar-custom .brand-text i {
            -webkit-text-fill-color: #0d6efd;
            font-size: 1.8rem;
        }
        
        /* ===== HERO SECTION ===== */
        .hero-section {
            padding: 1rem 0 2rem;
        }
        
        .hero-title {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }
        
        .hero-title .highlight {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            color: #4a5568;
            line-height: 1.7;
            margin-bottom: 2rem;
            max-width: 500px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0;
            font-size: 1rem;
            color: #2d3748;
        }
        
        .feature-item .icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #e8f0fe, #d4e4ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #0d6efd;
            font-size: 1rem;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #0d6efd, #0a5fd9);
            border: none;
            padding: 0.8rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            color: #fff;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.35);
            color: #fff;
        }
        
        .btn-outline-custom {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid #e2e8f0;
            padding: 0.8rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            color: #2d3748;
            transition: all 0.3s ease;
        }
        
        .btn-outline-custom:hover {
            background: #fff;
            border-color: #0d6efd;
            color: #0d6efd;
            transform: translateY(-2px);
        }
        
        /* ===== CARD DE REGISTRO ===== */
        .card-register {
            border: none;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: transform 0.3s ease;
        }
        
        .card-register:hover {
            transform: translateY(-4px);
        }
        
        .card-register .card-body {
            padding: 2.5rem;
        }
        
        .card-register .card-title {
            font-weight: 700;
            color: #1a202c;
        }
        
        .card-register .card-title i {
            color: #0d6efd;
        }
        
        /* ===== FORMULARIO ===== */
        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }
        
        .form-label {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
        }
        
        /* ===== SUBSCRIPTION OPTIONS ===== */
        .subscription-option {
            padding: 0.9rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
            height: 100%;
        }
        
        .subscription-option:hover {
            border-color: #0d6efd;
            background: #f8faff;
            transform: translateY(-2px);
        }
        
        .subscription-option.active {
            border-color: #0d6efd;
            background: linear-gradient(135deg, #f0f7ff, #e8f0fe);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.12);
        }
        
        .subscription-option input[type="radio"] {
            display: none;
        }
        
        .subscription-option .icon {
            font-size: 1.8rem;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }
        
        .subscription-option .title {
            font-weight: 700;
            color: #1a202c;
            font-size: 1rem;
        }
        
        .subscription-option .subtitle {
            font-size: 0.8rem;
            color: #718096;
        }
        
        .subscription-option .badge-option {
            background: #0d6efd;
            color: #fff;
            font-size: 0.6rem;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            margin-left: 0.5rem;
        }
        
        /* ===== BOTONES ===== */
        .btn-google {
            background: #fff;
            border: 2px solid #e2e8f0;
            color: #2d3748;
            padding: 0.75rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-google:hover {
            background: #f8fafc;
            border-color: #cbd5e0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }
        
        .btn-google i {
            color: #ea4335;
        }
        
        /* ===== PLANES ===== */
        .plan-card {
            border: none;
            border-radius: 20px;
            transition: all 0.3s ease;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .plan-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }
        
        .plan-card.popular {
            border: 2px solid #0d6efd;
            background: linear-gradient(135deg, #f8faff, #f0f7ff);
        }
        
        .plan-badge {
            position: absolute;
            top: -12px;
            right: 20px;
            background: linear-gradient(135deg, #0d6efd, #0a5fd9);
            padding: 0.3rem 1.2rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        
        .plan-card .price {
            font-size: 2.2rem;
            font-weight: 800;
            color: #0d6efd;
        }
        
        .plan-card .price small {
            font-size: 0.9rem;
            font-weight: 400;
            color: #718096;
        }
        
        .plan-feature {
            padding: 0.4rem 0;
            color: #2d3748;
        }
        
        .plan-feature i {
            margin-right: 0.5rem;
        }
        
        .plan-feature .bi-check {
            color: #38a169;
        }
        
        .plan-feature .bi-x {
            color: #e53e3e;
        }
        
        /* ===== FOOTER ===== */
        .footer {
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            padding-top: 2rem;
            margin-top: 3rem;
            color: #4a5568;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .card-register .card-body {
                padding: 1.8rem;
            }
            
            .hero-section {
                text-align: center;
            }
            
            .hero-subtitle {
                margin: 0 auto 1.5rem;
            }
            
            .feature-item {
                justify-content: center;
            }
            
            .hero-buttons {
                justify-content: center;
            }
        }
        
        @media (max-width: 576px) {
            .landing-container {
                padding: 1rem;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .navbar-custom .brand-text {
                font-size: 1.4rem;
            }
            
            .card-register .card-body {
                padding: 1.2rem;
            }
            
            .subscription-option .icon {
                font-size: 1.4rem;
            }
        }
        
        /* ===== ALERTAS ===== */
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #f0fff4, #e6fffa);
            color: #22543d;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fff5f5, #fed7d7);
            color: #9b2c2c;
        }
        
        /* ===== RECAPTCHA ===== */
        .recaptcha-container {
            display: flex;
            justify-content: center;
            margin: 1rem 0;
        }
        
        /* ===== ANIMACIONES ===== */
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
        
        .animate-fade-in {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .animate-delay-1 {
            animation-delay: 0.1s;
            opacity: 0;
        }
        
        .animate-delay-2 {
            animation-delay: 0.2s;
            opacity: 0;
        }
        
        .animate-delay-3 {
            animation-delay: 0.3s;
            opacity: 0;
        }
    </style>
</head>
<body>
    <div class="landing-container">
        
        <!-- ========================================= -->
        <!-- NAVBAR -->
        <!-- ========================================= -->
        <nav class="navbar-custom animate-fade-in">
            <div class="d-flex justify-content-between align-items-center">
                <a class="navbar-brand brand-text" href="#">
                    <i class="bi bi-droplet me-2"></i>MedFlow
                </a>
                <div class="d-flex gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
                    </a>
                </div>
            </div>
        </nav>
        
        <!-- ========================================= -->
        <!-- HERO + REGISTRO -->
        <!-- ========================================= -->
        <div class="row align-items-center hero-section">
            
            <!-- Columna Izquierda: Información -->
            <div class="col-lg-6 animate-fade-in animate-delay-1">
                <h1 class="hero-title">
                    Gestión de Sensores<br>
                    <span class="highlight">Inteligente y Simple</span>
                </h1>
                <p class="hero-subtitle">
                    La plataforma completa para monitorear sensores en obras civiles, 
                    industrias y hogares. Toma mediciones, genera reportes y colabora con tu equipo.
                </p>
                
                <div class="feature-item">
                    <span class="icon"><i class="bi bi-check-lg"></i></span>
                    <span>Monitoreo en tiempo real</span>
                </div>
                <div class="feature-item">
                    <span class="icon"><i class="bi bi-check-lg"></i></span>
                    <span>Importación masiva desde Excel/CSV</span>
                </div>
                <div class="feature-item">
                    <span class="icon"><i class="bi bi-check-lg"></i></span>
                    <span>Reportes y análisis de consumos</span>
                </div>
                <div class="feature-item">
                    <span class="icon"><i class="bi bi-check-lg"></i></span>
                    <span>Colaboración con roles y permisos</span>
                </div>
                
                <div class="hero-buttons">
                    <a href="#registro" class="btn btn-primary-custom">
                        <i class="bi bi-person-plus me-2"></i> Comienza ahora
                    </a>
                    <a href="#planes" class="btn btn-outline-custom">
                        <i class="bi bi-grid me-2"></i> Ver planes
                    </a>
                </div>
            </div>
            
            <!-- Columna Derecha: Formulario de Registro -->
            <div class="col-lg-6 animate-fade-in animate-delay-2">
                <div class="card card-register" id="registro">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">
                            <i class="bi bi-person-plus me-2"></i>Regístrate gratis
                        </h3>
                        
                        <!-- Contenedor de alertas -->
                        <div id="alertContainer"></div>
                        
                        <!-- Formulario -->
                        <form id="registerForm" novalidate>
                            @csrf
                            
                            <!-- Nombre -->
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="bi bi-person me-1"></i> Nombre completo
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       placeholder="Ej: Juan Pérez" required>
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-1"></i> Correo electrónico
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="ejemplo@correo.com" required>
                            </div>
                            
                            <!-- Contraseña -->
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-1"></i> Contraseña
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Mínimo 8 caracteres" required minlength="8">
                            </div>
                            
                            <!-- Confirmar contraseña -->
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <i class="bi bi-shield-lock me-1"></i> Confirmar contraseña
                                </label>
                                <input type="password" class="form-control" id="password_confirmation" 
                                       name="password_confirmation" placeholder="Repite tu contraseña" required>
                            </div>
                            
                            <!-- Tipo de cuenta -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-tag me-1"></i> Tipo de cuenta
                                </label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="subscription-option w-100 active" id="label_domiciliario">
                                            <input type="radio" name="subscription_type" value="domiciliario" checked>
                                            <div class="d-flex align-items-center">
                                                <span class="icon">🏠</span>
                                                <div>
                                                    <div class="title">Domiciliario</div>
                                                    <div class="subtitle">Hasta 10 sensores</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-6">
                                        <label class="subscription-option w-100" id="label_corporativo">
                                            <input type="radio" name="subscription_type" value="corporativo">
                                            <div class="d-flex align-items-center">
                                                <span class="icon">🏢</span>
                                                <div>
                                                    <div class="title">Corporativo</div>
                                                    <div class="subtitle">Sensores ilimitados</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="mt-2" id="subscriptionDescription">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i> 
                                        <span id="planDescription">Ideal para monitoreo de hogares o pequeños negocios con hasta 10 sensores.</span>
                                    </small>
                                </div>
                            </div>
                            
                            <!-- ✅ RECAPTCHA V2 VISIBLE -->
                            <div class="mb-3">
                                <div class="recaptcha-container">
                                    <div class="g-recaptcha" data-sitekey="{{ config('captcha.sitekey') }}"></div>
                                </div>
                                <div id="recaptchaError" class="text-danger small d-none mt-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i> 
                                    Por favor, completa el captcha.
                                </div>
                            </div>
                            
                            <!-- Botón de registro -->
                            <button type="submit" class="btn btn-primary-custom w-100 mt-3" id="registerBtn">
                                <i class="bi bi-person-plus me-2"></i> Crear cuenta
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <!-- Registro con Google -->
                        <div class="text-center">
                            <p class="text-muted mb-3">O regístrate con:</p>
                            <a href="{{ route('auth.google') }}" class="btn btn-google w-100">
                                <i class="bi bi-google me-2"></i> Continuar con Google
                            </a>
                        </div>
                        
                        <p class="mt-3 text-center small text-muted">
                            ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="fw-bold text-primary">Inicia sesión</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========================================= -->
        <!-- PLANES Y PRECIOS -->
        <!-- ========================================= -->
        <div id="planes" class="row mt-5 pt-4 animate-fade-in animate-delay-3">
            <div class="col-12 text-center mb-4">
                <h2 class="fw-bold">Planes y precios</h2>
                <p class="text-muted">Elige el plan que mejor se adapte a tus necesidades.</p>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card plan-card text-center p-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">🏠 Domiciliario</h5>
                        <p class="card-text text-muted small">Hasta 10 sensores, mediciones básicas, reportes mensuales.</p>
                        <div class="price">Gratis</div>
                        <hr>
                        <div class="text-start">
                            <div class="plan-feature"><i class="bi bi-check"></i> 10 sensores</div>
                            <div class="plan-feature"><i class="bi bi-check"></i> Mediciones básicas</div>
                            <div class="plan-feature"><i class="bi bi-check"></i> Reportes mensuales</div>
                            <div class="plan-feature"><i class="bi bi-x"></i> Colaboración en equipo</div>
                            <div class="plan-feature"><i class="bi bi-x"></i> API RESTful</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card plan-card popular text-center p-4 position-relative">
                    <span class="badge plan-badge">⭐ Popular</span>
                    <div class="card-body">
                        <h5 class="card-title fw-bold">🏢 Corporativo</h5>
                        <p class="card-text text-muted small">Sensores ilimitados, mediciones avanzadas, API, colaboración.</p>
                        <div class="price">Consultar</div>
                        <hr>
                        <div class="text-start">
                            <div class="plan-feature"><i class="bi bi-check"></i> Sensores ilimitados</div>
                            <div class="plan-feature"><i class="bi bi-check"></i> Mediciones avanzadas</div>
                            <div class="plan-feature"><i class="bi bi-check"></i> Reportes personalizados</div>
                            <div class="plan-feature"><i class="bi bi-check"></i> Colaboración en equipo</div>
                            <div class="plan-feature"><i class="bi bi-check"></i> API RESTful</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card plan-card text-center p-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">⭐ Empresarial</h5>
                        <p class="card-text text-muted small">Soporte 24/7, integración personalizada, seguridad avanzada.</p>
                        <div class="price">Consultar</div>
                        <hr>
                        <div class="text-start">
                            <div class="plan-feature"><i class="bi bi-check"></i> Soporte 24/7</div>
                            <div class="plan-feature"><i class="bi bi-check"></i> Integración personalizada</div>
                            <div class="plan-feature"><i class="bi bi-check"></i> Seguridad avanzada</div>
                            <div class="plan-feature"><i class="bi bi-check"></i> SLA garantizado</div>
                            <div class="plan-feature"><i class="bi bi-check"></i> Despliegue dedicado</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========================================= -->
        <!-- FOOTER -->
        <!-- ========================================= -->
        <footer class="footer text-center">
            <div class="row">
                <div class="col-12">
                    <p class="mb-1">
                        <i class="bi bi-droplet text-primary me-1"></i>
                        <strong>MedFlow</strong> - Gestión Inteligente de Sensores
                    </p>
                    <p class="small text-muted">
                        &copy; {{ date('Y') }} MedFlow. Todos los derechos reservados.
                        <span class="mx-2">|</span>
                        <a href="#" class="text-muted text-decoration-none">Términos</a>
                        <span class="mx-1">·</span>
                        <a href="#" class="text-muted text-decoration-none">Privacidad</a>
                    </p>
                </div>
            </div>
        </footer>
        
    </div>
    
    <!-- ========================================= -->
    <!-- SCRIPTS -->
    <!-- ========================================= -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    $(document).ready(function() {
        
        // =============================================
        // SELECCIÓN DE TIPO DE CUENTA
        // =============================================
        $('.subscription-option').click(function() {
            // Remover active de todas
            $('.subscription-option').removeClass('active');
            // Activar la seleccionada
            $(this).addClass('active');
            $(this).find('input[type="radio"]').prop('checked', true);
            
            // Actualizar descripción
            const type = $(this).find('input[type="radio"]').val();
            const descriptions = {
                'domiciliario': 'Ideal para monitoreo de hogares o pequeños negocios con hasta 10 sensores.',
                'corporativo': 'Para empresas, obras civiles e industrias con múltiples equipos y sensores ilimitados.'
            };
            $('#planDescription').text(descriptions[type] || '');
        });
        
        // =============================================
        // ENVÍO DEL FORMULARIO
        // =============================================
        $('#registerForm').submit(function(e) {
            e.preventDefault();
            
            // ✅ Validar captcha
            const captchaResponse = grecaptcha.getResponse();
            if (!captchaResponse) {
                $('#recaptchaError').removeClass('d-none');
                return;
            }
            $('#recaptchaError').addClass('d-none');
            
            // Validar campos básicos
            const name = $('#name').val().trim();
            const email = $('#email').val().trim();
            const password = $('#password').val();
            const passwordConf = $('#password_confirmation').val();
            
            if (!name || !email || !password || !passwordConf) {
                showAlert('Todos los campos son obligatorios.', 'danger');
                return;
            }
            
            if (password.length < 8) {
                showAlert('La contraseña debe tener al menos 8 caracteres.', 'danger');
                return;
            }
            
            if (password !== passwordConf) {
                showAlert('Las contraseñas no coinciden.', 'danger');
                return;
            }
            
            // Preparar datos para enviar
            const formData = {
                name: name,
                email: email,
                password: password,
                password_confirmation: passwordConf,
                subscription_type: $('input[name="subscription_type"]:checked').val(),
                'g-recaptcha-response': captchaResponse
            };
            
            console.log('📤 Enviando registro:', formData);
            
            // Deshabilitar botón
            $('#registerBtn').prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Registrando...
            `);
            
            $.ajax({
                url: '/api/auth/register',
                type: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify(formData),
                success: function(response) {
    console.log('✅ Registro exitoso:', response);
    
    if (response.success) {
        // ✅ Guardar token (por si acaso)
        if (response.data.token) {
            localStorage.setItem('token', response.data.token);
        }
        
        // ✅ Mostrar mensaje de éxito
        showAlert('¡Registro exitoso! Redirigiendo...', 'success');
        
        // ✅ Redirigir a la página de éxito con los datos en la URL
        setTimeout(function() {
            const userData = response.data.user;
            const redirectUrl = '/registro-exitoso?' + $.param({
                name: userData.name,
                email: userData.email,
                subscription_type: userData.subscription_type
            });
            window.location.href = redirectUrl;
        }, 1500);
    } else {
        showAlert(response.message || 'Error al registrar', 'danger');
        grecaptcha.reset();
    }
},
                error: function(xhr) {
                    console.error('❌ Error:', xhr);
                    
                    const errors = xhr.responseJSON?.errors;
                    let message = xhr.responseJSON?.message || 'Error al registrar';
                    
                    if (errors) {
                        message = Object.values(errors).flat().join('<br>');
                    }
                    
                    showAlert(message, 'danger');
                    grecaptcha.reset();
                },
                complete: function() {
                    $('#registerBtn').prop('disabled', false).html(`
                        <i class="bi bi-person-plus me-2"></i> Crear cuenta
                    `);
                }
            });
        });
        
        // =============================================
        // MOSTRAR ALERTAS
        // =============================================
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('#alertContainer').append(alertHtml);
            
            // Auto-eliminar después de 5 segundos
            setTimeout(() => {
                $('#alertContainer .alert').first().fadeOut(500, function() {
                    $(this).remove();
                });
            }, 5000);
        }
        
    });
    </script>
    
</body>
</html>