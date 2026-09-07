<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedFlow | Ecosistema Total de Medición y Facturación</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary: #020617;
            --secondary: #0f172a;
            --accent: #3b82f6;
            --accent-glow: rgba(59, 130, 246, 0.5);
            --gradient: linear-gradient(135deg, #2563eb, #0ea5e9, #38bdf8);
            --surface: #ffffff;
            --bg-light: #f8fafc;
            --text-heading: #0f172a;
            --text-body: #475569;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-body);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Outfit', sans-serif;
            color: var(--text-heading);
            letter-spacing: -0.03em;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            color: var(--primary);
        }

        .navbar-brand i {
            color: var(--accent);
        }

        .nav-link {
            font-weight: 600;
            color: var(--text-body);
            margin: 0 10px;
        }

        .nav-link:hover {
            color: var(--accent);
        }

        .btn-modern {
            padding: 12px 28px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-primary-modern {
            background: var(--gradient);
            color: white;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }

        .btn-primary-modern:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.4);
            color: white;
        }

        /* Hero */
        .hero {
            padding: 140px 0 180px;
            position: relative;
            background: #ffffff;
            overflow: hidden;
        }

        .hero-bg-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape-1 {
            position: absolute;
            top: -20%;
            right: -10%;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, transparent 70%);
        }

        .shape-2 {
            position: absolute;
            bottom: -10%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%);
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .badge-software {
            display: inline-block;
            padding: 8px 16px;
            margin-bottom: 24px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--accent);
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 1px;
            border: 1px solid rgba(37, 99, 235, 0.2);
        }

        .hero h1 {
            font-size: 5.5rem;
            font-weight: 900;
            line-height: 1.05;
            margin-bottom: 24px;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }

        .hero h1 span {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.35rem;
            color: var(--text-body);
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 90%;
        }

        /* Floating Dashboard Mockup */
        .hero-mockup-wrapper {
            position: relative;
            z-index: 10;
            margin-top: -120px;
            perspective: 1000px;
            padding: 0 20px;
        }

        .hero-mockup {
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.05);
            transform: rotateX(5deg) scale(0.98);
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            background: white;
            overflow: hidden;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero-mockup:hover {
            transform: rotateX(0deg) scale(1) translateY(-10px);
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.05);
        }

        .hero-mockup img {
            width: 100%;
            display: block;
            border-radius: 12px;
        }

        .mockup-header {
            height: 30px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            padding: 0 15px;
            gap: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .mockup-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .dot-r {
            background: #ef4444;
        }

        .dot-y {
            background: #eab308;
        }

        .dot-g {
            background: #22c55e;
        }

        /* Core Features Grid */
        .features-section {
            padding: 120px 0;
            background: var(--bg-light);
        }

        .section-header {
            text-align: center;
            margin-bottom: 80px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .section-header h2 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .section-header p {
            font-size: 1.25rem;
            color: var(--text-body);
        }

        .bento-card {
            background: white;
            border-radius: 24px;
            padding: 2px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            transition: all 0.4s ease;
            height: 100%;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
        }

        .bento-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        .bento-img-container {
            width: 100%;
            height: 280px;
            overflow: hidden;
            border-radius: 22px 22px 0 0;
            background: #f8fafc;
            display: flex;
            align-items: top;
            justify-content: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .bento-img-container img {
            width: 100%;
            height: auto;
            object-fit: cover;
            object-position: top;
            transition: transform 0.5s ease;
        }

        .bento-card:hover .bento-img-container img {
            transform: scale(1.05);
        }

        .bento-content {
            padding: 30px;
            flex-grow: 1;
        }

        .bento-content h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .bento-content p {
            color: var(--text-body);
            margin-bottom: 0;
            line-height: 1.6;
        }

        /* Carousel Section */
        .carousel-section {
            padding: 120px 0;
            background: var(--surface);
            overflow: hidden;
        }

        .carousel-container { max-width: 850px; margin: 0 auto; border: 4px solid #f8fafc; border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,0.15); overflow: hidden; background: #0f172a; }

        .carousel-item img { height: 450px !important; max-height: 450px; object-fit: contain; object-position: center; width: 100%; padding: 15px; background: #0f172a; }

        .carousel-caption-custom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
            padding: 100px 40px 40px;
            color: white;
            text-align: left;
        }

        .carousel-caption-custom h3 {
            color: white;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .carousel-caption-custom p {
            font-size: 1.2rem;
            margin-bottom: 0;
            opacity: 0.9;
        }

        /* Grid Masonry */
        .masonry-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; max-width: 1200px; margin: 0 auto; } 
    @media (max-width: 991px) { .masonry-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) { .masonry-grid { grid-template-columns: 1fr; } }

        .masonry-item {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
        }

        .masonry-item:hover {
            transform: scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }

        .masonry-item img {
            width: 100%;
            height: auto;
            display: block;
            border-bottom: 3px solid var(--accent);
        }

        /* CTA Action */
        .cta-section {
            padding: 120px 0;
            background: var(--primary);
            text-align: center;
            color: white;
            position: relative;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0.15;
            background-image: radial-gradient(white 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .cta-section h2 {
            color: white;
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        .cta-section p {
            font-size: 1.4rem;
            color: #cbd5e1;
            margin-bottom: 50px;
            position: relative;
            z-index: 2;
        }

        @media (max-width: 991px) {
            .hero h1 {
                font-size: 4rem;
            }

            .hero-mockup-wrapper {
                margin-top: -60px;
            }

            .carousel-item img { height: 400px !important; }
        }

        @media (max-width: 768px) {
            .hero {
                padding: 100px 0 120px;
            }

            .hero h1 {
                font-size: 3.2rem;
            }

            .hero p {
                font-size: 1.2rem;
            }

            .section-header h2 {
                font-size: 2.8rem;
            }

            .carousel-item img { height: 350px !important; }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-droplet-half"></i> MedFlow</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-toggle="collapse"
                data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse text-end" id="navMenu">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#modulos">Módulos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#analiticas">Analíticas</a></li>
                    <li class="nav-item"><a class="nav-link" href="#plataforma">La Plataforma</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="/login" class="btn-modern btn-primary-modern">
                            Ir al Sistema <i class="bi bi-box-arrow-in-right"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-bg-shapes">
            <div class="shape-1"></div>
            <div class="shape-2"></div>
        </div>
        <div class="container hero-content">
            <div class="row align-items-center mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="badge-software"><i class="bi bi-lightning-charge-fill me-1"></i> Plataforma SaaS
                        Industrial</div>
                    <h1>Mide, Audita y Factura con <span>Precisión Absoluta</span></h1>
                    <p>MedFlow es el ecosistema definitivo para consorcios, plantas y redes de distribución. Gestiona
                        tus sensores en campo, captura mediciones auditadas mediante fotografía y dispara facturaciones
                        automáticas con inteligencia de tasa diaria.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN MOCKUP -->
    <div class="hero-mockup-wrapper">
        <div class="hero-mockup">
            <div class="mockup-header">
                <div class="mockup-dot dot-r"></div>
                <div class="mockup-dot dot-y"></div>
                <div class="mockup-dot dot-g"></div>
            </div>
            <!-- IMAGEN PRINCIPAL GLOBAL -->
            <img src="{{ asset('flyer/dashboard.PNG') }}" alt="Dashboard MedFlow" loading="lazy">
        </div>
    </div>

    <!-- MAIN MODULES (BENTO GRID) -->
    <section class="features-section" id="modulos">
        <div class="container">
            <div class="section-header">
                <h2>El Motor Operativo</h2>
                <p>Una arquitectura diseñada para eliminar el fraude operativo y acelerar tu ciclo de cobro de
                    suministros, totalmente parametrizable a tus necesidades.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="bento-card">
                        <div class="bento-img-container">
                            <img src="{{ asset('flyer/grupos_sensores.PNG') }}" alt="Organización de Sensores">
                        </div>
                        <div class="bento-content">
                            <h3><i class="bi bi-diagram-3-fill text-primary me-2"></i> Logística de Estructuras</h3>
                            <p>Segmenta edificios, barrios o alas industriales en Grupos Logísticos. Importa redes
                                enteras con nuestro gestor Excel y aplica plantillas técnicas según tu tipo de fluido
                                (Agua, Energía, Presión).</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="bento-card">
                        <div class="bento-img-container">
                            <img src="{{ asset('flyer/tomar_mediciones.PNG') }}" alt="Captura de Medición">
                        </div>
                        <div class="bento-content">
                            <h3><i class="bi bi-camera-fill text-primary me-2"></i> Recolección de Campo Auditada</h3>
                            <p>El inspector utiliza su móvil como terminal de ingreso, reportando anomalías
                                geolocalizadas con fotografías obligatorias del contador como respaldo incuestionable de
                                su métrica.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="bento-card">
                        <div class="bento-img-container">
                            <img src="{{ asset('flyer/consumos.PNG') }}" alt="Liquidación Financiera">
                        </div>
                        <div class="bento-content">
                            <h3><i class="bi bi-cash-coin text-primary me-2"></i> Facturación y Prorrateo</h3>
                            <p>Algoritmos de cálculo automático que procesan las lecturas transformándolas en metros
                                cúbicos y liquidaciones a cobrar, detectando estancamientos, prorrateando áreas comunes
                                y alertando desfases.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SHOWCASE CAROUSEL -->
    <section class="carousel-section" id="analiticas">
        <div class="container">
            <div class="section-header mb-5">
                <h2>Auditorías Avanzadas al Máximo</h2>
                <p>Una suite de herramientas potentes que cuidan tu rentabilidad sin esfuerzo manual.</p>
            </div>

            <div class="carousel-container">
                <div id="medflowCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#medflowCarousel" data-bs-slide-to="0"
                            class="active"></button>
                        <button type="button" data-bs-target="#medflowCarousel" data-bs-slide-to="1"></button>
                        <button type="button" data-bs-target="#medflowCarousel" data-bs-slide-to="2"></button>
                        <button type="button" data-bs-target="#medflowCarousel" data-bs-slide-to="3"></button>
                    </div>
                    <div class="carousel-inner">
                        <div class="carousel-item active" data-bs-interval="4000">
                            <img src="{{ asset('flyer/analisis_avanzado_mediciones.PNG') }}" class="d-block w-100"
                                alt="Análisis Avanzado">
                            <div class="carousel-caption-custom">
                                <h3>Radar de Tasa Diaria</h3>
                                <p>Algoritmos predictivos que detectan aceleraciones anómalas en el consumo de lote de
                                    tus clientes y dibujan saltos visuales antes de que se transformen en pérdida.</p>
                            </div>
                        </div>
                        <div class="carousel-item" data-bs-interval="4000">
                            <img src="{{ asset('flyer/mediciones_negativas.PNG') }}" class="d-block w-100"
                                alt="Mediciones Negativas">
                            <div class="carousel-caption-custom">
                                <h3>Control de Anomalías Estructurales</h3>
                                <p>Captura bloqueos de contadores o recambios no autorizados automáticamente
                                    identificando retrocesos negativos (Rollbacks) en la física del volumen inyectado.
                                </p>
                            </div>
                        </div>
                        <div class="carousel-item" data-bs-interval="4000">
                            <img src="{{ asset('flyer/campanas_publicas.PNG') }}" class="d-block w-100"
                                alt="Campañas Públicas">
                            <div class="carousel-caption-custom">
                                <h3>Transparencia Pública</h3>
                                <p>Dispara campañas de email automáticas hacia tus inquilinos con el visor PDF adjunto
                                    de sus consumos inter-fechas, probando con fotos de lectura el 100% de la
                                    facturación.</p>
                            </div>
                        </div>
                        <div class="carousel-item" data-bs-interval="4000">
                            <img src="{{ asset('flyer/invitar_inspector.PNG') }}" class="d-block w-100"
                                alt="Gestión RH">
                            <div class="carousel-caption-custom">
                                <h3>Gestión Descentralizada de RRHH</h3>
                                <p>Invita Inspectores al terreno restringiendo su visión únicamente a un bloque de
                                    edificios o áreas, auditando cada movimiento en su recorrido de toma.</p>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#medflowCarousel"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"
                            style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#medflowCarousel"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"
                            style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- GALERIA MASONRY -->
    <section class="py-5 bg-white" id="plataforma">
        <div class="container py-5">
            <div class="section-header">
                <h2>Explora el Entorno</h2>
                <p>Navega a través de un Panel limpio, documentado, con Códigos QR y herramientas Backups para tu máxima
                    tranquilidad.</p>
            </div>

            <div class="masonry-grid">
                <div class="masonry-item">
                    <img src="{{ asset('flyer/grilla_qr.PNG') }}" alt="Tokens QR">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-qr-code-scan text-primary me-2"></i> Tokens de Visor (QR)</h4>
                        <p>Brinda a cada usuario un Token o Código QR único para que puedan escanearlo y consultar la evolución de sus propios consumos desde su celular en todo momento, sin contraseñas.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset('flyer/plantillas.PNG') }}" alt="Plantillas">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-braces text-primary me-2"></i> Plantillas Dinámicas</h4>
                        <p>Crea esquemas variables de toma (Agua, Energía, Presión) y obliga a tus inspectores a relevar exactly los campos que necesites.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset('flyer/backups.PNG') }}" alt="Seguridad">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-shield-check text-primary me-2"></i> Backups Cifrados</h4>
                        <p>Descarga e importa copias de seguridad estáticas de toda la información de tus medidores con un flujo protegido.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset('flyer/sensores.PNG') }}" alt="Inventario">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-router text-primary me-2"></i> Inventario Táctico</h4>
                        <p>Tu flota en terreno unificada. Busca, filtra y monitoriza qué inspectores están atendiendo qué líneas.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset('flyer/detalle_consumo.PNG') }}" alt="Liquidador">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-receipt-cutoff text-primary me-2"></i> Detalles de Liquidación</h4>
                        <p>Transparencia fotográfica por cada medidor para respaldar los cálculos financieros ante reclamos.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset('flyer/centro_ayuda.PNG') }}" alt="Base de Conocimiento">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-info-square text-primary me-2"></i> Centro de Ayuda</h4>
                        <p>Manuales y vías de contacto de soporte listas para asistir a cualquier nivel gerencial u operario.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION FINAL -->
    <section class="cta-section">
        <div class="container">
            <h2>Listo para la revolución métrica.</h2>
            <p>Empieza a operar. Agrega tus sensores, configura a tus clientes y observa los datos fluir con
                rentabilidad segura.</p>
            <a href="/login" class="btn-modern text-primary bg-white shadow-lg fw-bold"
                style="padding: 16px 40px; font-size: 1.25rem;">
                Ingresar al Panel de Control <i class="bi bi-rocket-takeoff ms-2"></i>
            </a>
        </div>
    </section>

    <!-- Footer Simple -->
    <footer
        style="background: var(--primary); padding: 40px 0; border-top: 1px solid rgba(255,255,255,0.1); color: #94a3b8; text-align: center;">
        <div class="container">
            <h4 class="text-white mb-3" style="font-family: 'Outfit'; font-weight: 700;"><i
                    class="bi bi-droplet-half text-primary"></i> MedFlow Systems</h4>
            <p class="mb-0">© {{ date('Y') }} ArumaSoft Solutions. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            var nav = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                nav.style.background = 'rgba(255, 255, 255, 0.95)';
                nav.style.boxShadow = '0 5px 20px rgba(0,0,0,0.05)';
            } else {
                nav.style.background = 'rgba(255, 255, 255, 0.85)';
                nav.style.boxShadow = 'none';
            }
        });
    </script>
</body>

</html>