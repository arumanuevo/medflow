<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MedFlow - Gestión de Suministros Inteligente</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-bg: #090b14;
            --primary-color: #2F66EE;
            --accent-color: #F8B803;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--primary-bg);
            color: #f8f9fa;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Hero */
        .hero {
            position: relative;
            padding: 8rem 0 6rem;
            background: radial-gradient(circle at center, rgba(47, 102, 238, 0.15) 0%, rgba(9, 11, 20, 1) 100%);
            text-align: center;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(90deg, #fff, #aab1c0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
            letter-spacing: -1px;
        }

        /* Nav */
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #fff !important;
            letter-spacing: -0.5px;
        }

        .nav-btn {
            background-blur: 10px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: #fff;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background: #fff;
            color: #000;
        }

        /* Cards */
        .pricing-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2.5rem;
            transition: transform 0.3s ease, border-color 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .pricing-card.premium {
            border-color: var(--primary-color);
            background: linear-gradient(180deg, rgba(47, 102, 238, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            box-shadow: 0 0 40px rgba(47, 102, 238, 0.15);
        }

        .pricing-card.premium::before {
            content: 'Más Popular';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .price {
            font-size: 3rem;
            font-weight: 800;
            margin: 1.5rem 0;
            color: #fff;
        }

        .price span {
            font-size: 1rem;
            font-weight: 400;
            color: #8f9bb3;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
            flex-grow: 1;
        }

        .feature-list li {
            margin-bottom: 1rem;
            color: #cbd5e1;
            display: flex;
            align-items: center;
        }

        .feature-list li i {
            color: #10b981;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .feature-list li.missing i {
            color: #475569;
        }

        .feature-list li.missing {
            color: #64748b;
        }

        /* Action Buttons */
        .btn-gradient {
            background: linear-gradient(90deg, #2F66EE, #4f80fa);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 1rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-gradient:hover {
            box-shadow: 0 10px 20px rgba(47, 102, 238, 0.3);
            transform: scale(1.02);
            color: white;
        }

        .btn-outline-glass {
            background: transparent;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 1rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-outline-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Legal Section */
        .legal-section {
            background: #040509;
            border-top: 1px solid var(--glass-border);
            padding: 4rem 0;
            font-size: 0.85rem;
            color: #64748b;
        }

        .badge-advantage {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg pt-4 pb-0">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <i class="bi bi-droplet-fill text-primary me-2"></i> MedFlow
            </a>
            @if (Route::has('login'))
                <div class="d-flex ms-auto gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-decoration-none nav-btn">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-decoration-none nav-btn"
                            style="background: transparent;">Ingresar</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-decoration-none nav-btn">Regístrate</a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero container">
        <h1>Control Total para tus<br>Redes de Suministro</h1>
        <p class="lead text-secondary mb-5 mx-auto" style="max-width: 600px;">
            Plataforma integral para consorcios, administradores de edificios y empresas. Centralizá mediciones, enrutá
            recorridos y sincronizá a tus inspectores en tiempo real.
        </p>
    </section>

    <!-- Pricing -->
    <section class="container mb-5 pb-5">
        <div class="row g-4 justify-content-center">

            <!-- Plan Free -->
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card">
                    <h4>Plan Inicial</h4>
                    <p class="text-muted">La solución ideal para pequeños administradores.</p>
                    <div class="price">$0 <span>/ para siempre</span></div>

                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle-fill"></i> Panel de Administración Base</li>
                        <li><i class="bi bi-check-circle-fill"></i> Hasta 200 sensores limitados</li>
                        <li><i class="bi bi-check-circle-fill"></i> Lector QR para medidores</li>
                        <li class="missing"><i class="bi bi-x-circle-fill"></i> Multi-usuario e Inspectores</li>
                        <li class="missing"><i class="bi bi-x-circle-fill"></i> Enrutamiento Masivo</li>
                    </ul>

                    <a href="{{ route('register') }}"
                        class="btn-outline-glass text-center text-decoration-none">Comenzar Gratis</a>
                </div>
            </div>

            <!-- Plan Premium -->
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card premium">
                    <span class="badge-advantage"><i class="bi bi-star-fill me-1"></i> ¡1 Mes de Prueba Gratuito!</span>
                    <h4>Inspector Premium</h4>
                    <p class="text-muted">Diseñado para PyMEs y Barrios Privados de alto flujo.</p>
                    <div class="price">$25 <span>/ usd mensuales</span></div>

                    <!-- Autocancelable advantage -->
                    <div class="alert mt-2 mb-3 py-2 px-3 d-flex align-items-center"
                        style="background: rgba(248,184,3,0.1); border: 1px solid rgba(248,184,3,0.2); border-radius: 12px;">
                        <i class="bi bi-info-circle-fill text-warning me-2"></i>
                        <small class="text-warning"><strong>Autocancelable:</strong> Contratas 30 días, no hay débitos
                            sorpresa y se cancela sola al vencer. *Pronto modalidad a débito más económica.</small>
                    </div>

                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle-fill"></i> Sistema Multi-Roles (Inspectores Invitados)</li>
                        <li><i class="bi bi-check-circle-fill"></i> Sensores sin límite de registros</li>
                        <li><i class="bi bi-check-circle-fill"></i> Restricción In-App de Acceso a Rutas (Modal Assign)
                        </li>
                        <li><i class="bi bi-check-circle-fill"></i> Exportación CSV Masiva</li>
                        <li><i class="bi bi-check-circle-fill"></i> Soporte Prioritario</li>
                    </ul>

                    <a href="{{ route('register') }}" class="btn-gradient text-center text-decoration-none">Obtener
                        Premium</a>
                </div>
            </div>

            <!-- Ad Hoc -->
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card" style="border-color: rgba(248,184,3,0.3);">
                    <h4>Corporativo & Obras</h4>
                    <p class="text-muted">Arquitectura y despliegue llave en mano.</p>
                    <div class="price">Ad-Hoc <span>/ a medida</span></div>

                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle-fill" style="color:var(--accent-color)"></i> Software a Medida
                            sobre el motor Base</li>
                        <li><i class="bi bi-check-circle-fill" style="color:var(--accent-color)"></i> Administración
                            tercerizada de Infraestructura</li>
                        <li><i class="bi bi-check-circle-fill" style="color:var(--accent-color)"></i> Delegación total
                            de Inspectores</li>
                        <li><i class="bi bi-check-circle-fill" style="color:var(--accent-color)"></i> Concurrencia de
                            Características Personalizadas</li>
                        <li><i class="bi bi-check-circle-fill" style="color:var(--accent-color)"></i> SLA 24/7 Dedicado
                        </li>
                    </ul>

                    <a href="mailto:ventas@medflow.com"
                        class="btn-outline-glass text-center text-decoration-none">Contactar Ventas</a>
                </div>
            </div>

        </div>
    </section>

    <!-- Legal Section -->
    <footer class="legal-section">
        <div class="container text-center">
            <h5 class="text-white mb-3"><i class="bi bi-shield-check"></i> Privacidad y Condiciones Legales Estatales
            </h5>
            <p style="max-width: 800px; margin: 0 auto; line-height: 1.8;">
                <strong>Exención de Responsabilidad y Privacidad (SaaS):</strong> El software "MedFlow" y sus
                desarrolladores proporcionan la plataforma exclusivamente como una herramienta informática (SaaS)
                destinada a facilitar la recolección y logística de medidores. Los administradores, usuarios y empresas
                registradas son los únicos dueños, creadores y responsables absolutos de los datos cargados en el
                sistema.
                <br><br>
                <strong>Protección Criptográfica de la Identidad:</strong> MedFlow <b>no recopila, no alquila, ni
                    comercia</b> con direcciones de correo electrónico, fotos subidas, métricas ni metadatos de los
                clientes finales bajo ninguna circunstancia. El software no efectúa ningún uso no autorizado ni venta de
                información a terceros. Al utilizar esta herramienta o registrarse, el usuario exime formalmente a los
                desarrolladores del software por los daños directos e indirectos causados por el mal uso de la
                plataforma por parte de los operadores inspectores corporativos.
            </p>
            <div class="mt-4 pt-4 border-top" style="border-color: var(--glass-border) !important;">
                <p>&copy; {{ date('Y') }} MedFlow Systems. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

</body>

</html>