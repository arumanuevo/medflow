<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedFlow | Ecosistema Total de Medición, GIS y Facturación</title>

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

        /* GIS Showcase Section */
        .gis-section {
            padding: 120px 0;
            background: linear-gradient(180deg, #0f172a 0%, #020617 100%);
            color: #f8fafc;
            position: relative;
            overflow: hidden;
        }

        .gis-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(56, 189, 248, 0.14) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }

        .gis-section .section-header h2 {
            color: #ffffff;
        }

        .gis-section .section-header p {
            color: #cbd5e1;
        }

        .gis-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            margin-bottom: 20px;
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(52, 211, 153, 0.35);
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .gis-card {
            background: rgba(30, 41, 59, 0.75);
            backdrop-filter: blur(12px);
            border-radius: 22px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: all 0.4s ease;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.3);
        }

        .gis-card:hover {
            transform: translateY(-10px);
            border-color: #38bdf8;
            box-shadow: 0 25px 55px rgba(14, 165, 233, 0.25);
        }

        .gis-img-wrapper {
            width: 100%;
            height: 260px;
            background: #090d16;
            overflow: hidden;
            position: relative;
            border-bottom: 1px solid rgba(148, 163, 184, 0.15);
        }

        .gis-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.5s ease;
        }

        .gis-card:hover .gis-img-wrapper img {
            transform: scale(1.06);
        }

        .gis-step-pill {
            position: absolute;
            top: 14px;
            left: 14px;
            background: rgba(15, 23, 42, 0.85);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.4);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 50px;
            backdrop-filter: blur(6px);
        }

        .gis-card-body {
            padding: 28px;
            flex-grow: 1;
        }

        .gis-card-body h3 {
            color: #ffffff;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .gis-card-body p {
            color: #cbd5e1;
            font-size: 0.96rem;
            line-height: 1.6;
            margin-bottom: 0;
        }

        .gis-highlight-strip {
            margin-top: 48px;
            background: rgba(30, 41, 59, 0.65);
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: 20px;
            padding: 28px 32px;
        }

        .gis-pill-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #e2e8f0;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .gis-pill-item i {
            font-size: 1.35rem;
            color: #38bdf8;
        }

        /* Carousel Section */
        .carousel-section {
            padding: 120px 0;
            background: var(--surface);
            overflow: hidden;
        }

        .carousel-container {
            max-width: 850px;
            margin: 0 auto;
            border: 4px solid #f8fafc;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            background: #0f172a;
        }

        .carousel-item img {
            height: 450px !important;
            max-height: 450px;
            object-fit: contain;
            object-position: center;
            width: 100%;
            padding: 15px;
            background: #0f172a;
        }

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
        .masonry-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        @media (max-width: 991px) {
            .masonry-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .masonry-grid {
                grid-template-columns: 1fr;
            }
        }

        .masonry-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.05);
            border: 2px solid #cbd5e1;
            transition: 0.3s;
        }

        .masonry-item:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 25px 30px -5px rgba(0, 0, 0, 0.15);
            border-color: var(--accent);
            z-index: 2;
        }

        .masonry-item img {
            width: 100%;
            height: auto;
            display: block;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Esta línea eliminó la raya azul 3px */
        .masonry-caption {
            padding: 30px 25px 25px 25px;
        }

        /* Añade el espaciado pedido */
        .masonry-caption h4 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .masonry-caption p {
            font-size: 0.9rem;
            color: var(--text-body);
            margin-bottom: 0;
            line-height: 1.4;
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

            .carousel-item img {
                height: 400px !important;
            }
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

            .carousel-item img {
                height: 350px !important;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/"><i class="bi bi-droplet-half"></i> MedFlow</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse text-end" id="navMenu">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#modulos">Módulos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#gis">Mapa GIS</a></li>
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
                <div class="col-lg-9 mx-auto text-center">
                    <div class="badge-software"><i class="bi bi-lightning-charge-fill me-1"></i> Plataforma SaaS
                        Industrial &amp; GIS</div>
                    <h1>Mide, Geolocaliza y Factura con <span>Precisión Absoluta</span></h1>
                    <p class="mx-auto">MedFlow es el ecosistema definitivo para consorcios, barrios privados, plantas y redes de distribución. Gestiona
                        tus sensores en mapas interactivos GIS, programa tu calendario de tomas con tolerancias, captura mediciones auditadas con zoom fotográfico y dispara facturaciones
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
                            <img src="{{ asset('calendario.PNG') }}"
                                onerror="const alts=['{{ asset('flyer/calendario.PNG') }}','{{ asset('calendario.png') }}','{{ asset('flyer/calendario.png') }}']; const i=parseInt(this.dataset.fb||0); if(i<alts.length){this.dataset.fb=i+1;this.src=alts[i];}"
                                alt="Logística y Calendario de Tomas">
                        </div>
                        <div class="bento-content">
                            <h3><i class="bi bi-calendar2-check-fill text-primary me-2"></i> Logística y Calendario de Tomas</h3>
                            <p>Segmenta edificios, barrios o alas industriales en Grupos Logísticos, agenda días fijos de medición con ventanas de pre-aviso y días de tolerancia, e importa redes
                                enteras desde Excel aplicando plantillas de Agua, Electricidad o Gas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="bento-card">
                        <div class="bento-img-container">
                            <img src="{{ asset('flyer/tomar_mediciones.PNG') }}" alt="Captura de Medición">
                        </div>
                        <div class="bento-content">
                            <h3><i class="bi bi-camera-fill text-primary me-2"></i> Recolección Auditada con Zoom HD</h3>
                            <p>El inspector utiliza su móvil como terminal de ingreso reportando lecturas con fotografías obligatorias del contador, y el administrador cuenta con inspector de zoom interactivo para auditar cada dígito al milímetro.</p>
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
                            <p>Algoritmos de cálculo automático que procesan las lecturas transformándolas en consumos y
                                liquidaciones a cobrar, detectando estancamientos, prorrateando áreas comunes
                                y alertando desfases.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NUEVA SECCIÓN: ANÁLISIS DE INFORMACIÓN GEOGRÁFICA (GIS) -->
    <section class="gis-section" id="gis">
        <div class="container position-relative" style="z-index: 2;">
            <div class="section-header">
                <div class="gis-badge">
                    <i class="bi bi-geo-alt-fill"></i> Exclusivo Plan Premium · Nuevo Módulo GIS
                </div>
                <h2>Análisis de Información Geográfica (GIS)</h2>
                <p>Transforma tus coordenadas en un centro de comando territorial. Visualiza de un pantallazo qué medidores faltan medir, cuáles están vencidos y dónde se concentran los picos de consumo o posibles fugas.</p>
            </div>

            <div class="row g-4">
                <!-- Captura 1: mapa1.PNG -->
                <div class="col-lg-4 col-md-6">
                    <div class="gis-card">
                        <div class="gis-img-wrapper">
                            <span class="gis-step-pill"><i class="bi bi-pin-map-fill me-1"></i> Georreferenciación &amp; Tomas</span>
                            <img src="{{ asset('mapa1.PNG') }}"
                                onerror="const alts=['{{ asset('mapa1.png') }}','{{ asset('flyer/mapa1.PNG') }}','{{ asset('flyer/mapa1.png') }}']; const i=parseInt(this.dataset.fb||0); if(i<alts.length){this.dataset.fb=i+1;this.src=alts[i];}"
                                alt="Mapa Interactivo de Sensores - Estado de Tomas">
                        </div>
                        <div class="gis-card-body">
                            <h3><i class="bi bi-compass text-info me-2"></i> Ubicación Ágil y Semáforo Operativo</h3>
                            <p>Posiciona cada medidor sobre vista Calles o Satélite con un clic, arrastrando el marcador (Drag &amp; Drop) o pegando coordenadas de Google Maps. Los pines cambian de color en vivo según el calendario: medidos al día, en ventana, en tolerancia o atrasados.</p>
                        </div>
                    </div>
                </div>

                <!-- Captura 2: mapa2.PNG -->
                <div class="col-lg-4 col-md-6">
                    <div class="gis-card">
                        <div class="gis-img-wrapper">
                            <span class="gis-step-pill"><i class="bi bi-fire me-1"></i> Radar Territorial de Anomalías</span>
                            <img src="{{ asset('mapa2.PNG') }}"
                                onerror="const alts=['{{ asset('mapa2.png') }}','{{ asset('flyer/mapa2.PNG') }}','{{ asset('flyer/mapa2.png') }}']; const i=parseInt(this.dataset.fb||0); if(i<alts.length){this.dataset.fb=i+1;this.src=alts[i];}"
                                alt="Mapa GIS - Detección de Picos de Consumo">
                        </div>
                        <div class="gis-card-body">
                            <h3><i class="bi bi-activity text-danger me-2"></i> Mapa de Picos de Consumo y Fugas</h3>
                            <p>Activa el modo <strong>Picos y Consumos</strong> para que el motor contraste el ritmo diario actual contra el promedio histórico de cada lote. Los medidores con saltos mayores al +50% emiten un halo rojo pulsante y los estancados (consumo 0 prolongado) se destacan en morado.</p>
                        </div>
                    </div>
                </div>

                <!-- Captura 3: mapa3.PNG -->
                <div class="col-lg-4 col-md-12">
                    <div class="gis-card">
                        <div class="gis-img-wrapper">
                            <span class="gis-step-pill"><i class="bi bi-graph-up-arrow me-1"></i> Telemetría Directa en Mapa</span>
                            <img src="{{ asset('mapa3.PNG') }}"
                                onerror="const alts=['{{ asset('mapa3.png') }}','{{ asset('flyer/mapa3.PNG') }}','{{ asset('flyer/mapa3.png') }}']; const i=parseInt(this.dataset.fb||0); if(i<alts.length){this.dataset.fb=i+1;this.src=alts[i];}"
                                alt="Mapa GIS - Filtros en 1 Clic y Curva de Telemetría">
                        </div>
                        <div class="gis-card-body">
                            <h3><i class="bi bi-sliders text-success me-2"></i> Filtros en 1 Clic y Curva Histórica</h3>
                            <p>Utiliza las 7 tarjetas superiores para aislar instantáneamente sensores pendientes, vencidos o con anomalías. Haz clic en cualquier pin para inspeccionar su curva histórica de telemetría en una ventana emergente o iniciar su medición de inmediato.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de capacidades GIS -->
            <div class="gis-highlight-strip">
                <div class="row g-3 text-start">
                    <div class="col-lg-3 col-md-6">
                        <div class="gis-pill-item">
                            <i class="bi bi-layers-half"></i>
                            <span>Capas Calles (OSM) y Satélite Esri HD</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="gis-pill-item">
                            <i class="bi bi-funnel-fill"></i>
                            <span>7 Tarjetas de Diagnóstico y Filtro Rápido</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="gis-pill-item">
                            <i class="bi bi-exclamation-diamond-fill"></i>
                            <span>Alerta de Picos (+50%) y Medidores Trabados</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="gis-pill-item">
                            <i class="bi bi-bar-chart-line-fill"></i>
                            <span>Gráfico de Telemetría Integrado en cada Pin</span>
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
                <p>Navega a través de un Panel limpio, documentado, con Mapas GIS, Calendario de Tolerancias, Códigos QR y herramientas de Backups para tu máxima
                    tranquilidad.</p>
            </div>

            <div class="masonry-grid">
                <div class="masonry-item">
                    <img src="{{ asset('flyer/grilla_qr.PNG') }}" alt="Tokens QR">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-qr-code-scan text-primary me-2"></i> Tokens de Visor (QR)</h4>
                        <p>Brinda a cada usuario un Token o Código QR único para que puedan escanearlo y consultar la
                            evolución de sus propios consumos desde su celular en todo momento, sin contraseñas.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset('flyer/plantillas.PNG') }}" alt="Plantillas">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-braces text-primary me-2"></i> Plantillas Dinámicas</h4>
                        <p>Crea esquemas variables de toma (Agua, Electricidad, Gas) y obliga a tus inspectores a
                            relevar exactamente los campos que necesites.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset('flyer/backups.PNG') }}" alt="Seguridad">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-shield-check text-primary me-2"></i> Backups Cifrados</h4>
                        <p>Descarga e importa copias de seguridad estáticas de toda la información de tus medidores con
                            un flujo protegido.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset('flyer/sensores.PNG') }}" alt="Inventario">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-router text-primary me-2"></i> Inventario Táctico y Calendario</h4>
                        <p>Tu flota en terreno unificada con semáforo de vencimientos y tolerancias. Busca, filtra y monitoriza qué inspectores están atendiendo
                            qué líneas.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset('flyer/detalle_consumo.PNG') }}" alt="Liquidador">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-receipt-cutoff text-primary me-2"></i> Detalles de Liquidación con Zoom HD</h4>
                        <p>Transparencia fotográfica con lupa interactiva de alta precisión por cada medidor para respaldar los cálculos financieros ante
                            reclamos.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset('flyer/centro_ayuda.PNG') }}" alt="Base de Conocimiento">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-robot text-primary me-2"></i> Soporte C-Level (IA)</h4>
                        <p>Los suscriptores Premium desbloquean a <b>Flowy</b>, un agente de Inteligencia Artificial integrado 24/7 con lectura en tiempo real de tu agenda y cobertura GIS.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION FINAL -->
    <section class="cta-section">
        <div class="container">
            <h2>Listo para la revolución métrica.</h2>
            <p>Empieza a operar. Agrega tus sensores, geolocalízalos en el mapa y observa los datos fluir con
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
