<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedFlow | Ecosistema Único de Medición y Facturación</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Google Fonts (Outfit & Inter) -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary: #0f172a;
            --accent: #2563eb;
            --accent-glow: rgba(37, 99, 235, 0.4);
            --surface: #ffffff;
            --background: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-main);
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
        }

        .navbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 800;
            color: var(--primary);
            font-size: 1.5rem;
        }

        .navbar-brand i {
            color: var(--accent);
        }

        /* Hero Section */
        .hero {
            position: relative;
            padding: 80px 0 160px;
            background: linear-gradient(180deg, #e0e7ff 0%, #f8fafc 100%);
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -10%;
            width: 120%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(37, 99, 235, 0.08) 0%, transparent 70%);
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 850px;
            margin: 0 auto;
        }

        .tag-badge {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--accent);
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .hero h1 {
            font-size: 4.8rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1.5px;
            color: var(--primary);
            margin-bottom: 24px;
        }

        .hero h1 span {
            background: linear-gradient(135deg, #2563eb, #3b82f6, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 40px;
            padding: 0 40px;
        }

        .btn-modern {
            padding: 14px 32px;
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary-modern {
            background: var(--accent);
            color: white;
            box-shadow: 0 10px 25px var(--accent-glow);
        }

        .btn-primary-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px var(--accent-glow);
            color: white;
        }

        /* Mockup Placeholder Central */
        .mockup-container {
            margin-top: -100px;
            position: relative;
            z-index: 10;
        }

        .mockup-image-box {
            background: white;
            border-radius: 24px;
            padding: 10px;
            box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transform: perspective(1200px) rotateX(4deg);
            transition: transform 0.5s ease;
            position: relative;
            overflow: hidden;
            background-color: #f1f5f9;
            height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: var(--text-muted);
        }

        .mockup-image-box:hover {
            transform: perspective(1200px) rotateX(0deg) translateY(-10px);
        }

        .mockup-image-box img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
            z-index: 5;
            opacity: 0;
            /* Desaparece el gris si la foto real no existe */
            transition: opacity 0.3s ease;
        }

        /* Features Section */
        .features {
            padding: 120px 0;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 80px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .section-title h2 {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -1px;
            margin-bottom: 20px;
        }

        .section-title p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .feature-card {
            border: none;
            background: #f8fafc;
            border-radius: 24px;
            padding: 40px;
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .feature-card:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04);
            transform: translateY(-5px);
            background: white;
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 24px;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--primary);
        }

        .feature-card p {
            color: var(--text-muted);
            line-height: -6;
        }

        /* Placeholder for mini-features */
        .mini-photo-placeholder {
            width: 100%;
            height: 220px;
            background: #e2e8f0;
            border-radius: 16px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
            padding: 20px;
            position: relative;
            box-shadow: inset 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .mini-photo-placeholder img {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            border-radius: 16px;
            z-index: 2;
        }

        /* Banner Final */
        .cta-banner {
            background: var(--primary);
            padding: 100px 0;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .cta-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 80% 20%, rgba(37, 99, 235, 0.4) 0%, transparent 60%);
        }

        .cta-banner h2 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 24px;
            position: relative;
            z-index: 2;
        }

        .cta-banner p {
            font-size: 1.2rem;
            color: #94a3b8;
            margin-bottom: 40px;
            position: relative;
            z-index: 2;
        }

        @media (max-width: 991px) {
            .hero h1 {
                font-size: 3.5rem;
            }

            .mockup-image-box {
                height: 400px;
            }
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.8rem;
            }

            .mockup-image-box {
                height: 250px;
            }

            .hero {
                padding: 60px 0 100px;
            }

            .hero p {
                padding: 0 10px;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR PARA EL FLYER -->
    <nav class="navbar navbar-expand-lg py-3 sticky-top">
        <div class="container d-flex justify-content-between">
            <a class="navbar-brand" href="#">
                <i class="bi bi-activity"></i> MedFlow
            </a>
            <a href="/register" class="btn-modern btn-primary-modern"
                style="padding: 10px 24px; font-size: 1rem;">Acceso al Sistema</a>
        </div>
    </nav>

    <!-- HERO SECTION PROMOCIONAL -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <span class="tag-badge"><i class="bi bi-broadcast me-1"></i> Software As A Service Industrial</span>
                <h1>Auditoría y control de <span>Mediciones</span> en tiempo real.</h1>
                <p>MedFlow revoluciona cómo recolectas datos físicos. Una sola plataforma en la nube para registrar,
                    auditar y prorratear consumo de Agua, Luz, Gas o parámetros industriales, usando tu smartphone como
                    colector inteligente.</p>
            </div>
        </div>
    </section>

    <!-- BIG IMAGE PLACEHOLDER: DASHBOARD -->
    <section class="container mockup-container">
        <!-- 💡 INSTRUCCIÓN PARA TI: Sube una foto bella llamada 'flyer_hero.png' en public/img/ -->
        <div class="mockup-image-box">
            <i class="bi bi-image" style="font-size: 3rem; color: #94a3b8; margin-bottom: 10px;"></i>
            <h5 class="m-0" style="color: #64748b;">[ Inserta: flyer_hero.png ]</h5>
            <small style="color: #94a3b8;">Sugerencia: Foto extendida del Panel Principal / Dashboard</small>
            <img src="{{ asset('img/flyer_hero.png') }}" onload="this.style.opacity='1'"
                onerror="this.style.opacity='0'" alt="Dashboard Principal">
        </div>
    </section>

    <!-- CARACTERÍSTICAS CORE (MÓDULOS) -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Diseñado para la precisión</h2>
                <p>Nuestra arquitectura entrelaza a los directivos con los operarios de calle en un flujo de información
                    perfectamente auditado y protegido financieramente.</p>
            </div>

            <div class="row g-4">
                <!-- Tarjeta 1: La App Móvil -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <!-- 💡 FOTO A INSERTAR 1 -->
                        <div class="mini-photo-placeholder">
                            <div><i class="bi bi-phone mb-2 fs-3"></i><br><b>flyer_app.png</b><br><small>Sugerencia:
                                    Captura de la App tomando foto a un contador</small></div>
                            <img src="{{ asset('img/flyer_app.png') }}" onload="this.style.opacity='1'"
                                onerror="this.style.opacity='0'" alt="App Campo">
                        </div>
                        <div class="feature-icon"><i class="bi bi-phone"></i></div>
                        <h3>Recolección Segura</h3>
                        <p style="color: #64748b;">Evidencia anti-fraude obligatoria. Al medir en terreno, la App exige
                            una fotografía real del medidor y calcula en vivo si los números tipearos tienen sentido.
                        </p>
                    </div>
                </div>

                <!-- Tarjeta 2: Cálculo Matemático -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <!-- 💡 FOTO A INSERTAR 2 -->
                        <div class="mini-photo-placeholder">
                            <div><i class="bi bi-calculator mb-2 fs-3"></i><br><b>flyer_calculo.png</b><br><small>Sugerencia:
                                    Captura de la tabla de consumo con montos</small></div>
                            <img src="{{ asset('img/flyer_calculo.png') }}" onload="this.style.opacity='1'"
                                onerror="this.style.opacity='0'" alt="Calculo">
                        </div>
                        <div class="feature-icon"><i class="bi bi-calculator"></i></div>
                        <h3>Motor de Prorrateo</h3>
                        <p style="color: #64748b;">Detectamos consumos negativos, alertamos de fugas estructurales, y
                            prorrateamos automáticamente los cortes contables sin perder meses por recambios de
                            medidores.</p>
                    </div>
                </div>

                <!-- Tarjeta 3: Transparencia y Correos -->
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <!-- 💡 FOTO A INSERTAR 3 -->
                        <div class="mini-photo-placeholder">
                            <div><i class="bi bi-envelope-paper mb-2 fs-3"></i><br><b>flyer_email.png</b><br><small>Sugerencia:
                                    Captura de un Visor Público o PDF recibido en el celular</small></div>
                            <img src="{{ asset('img/flyer_email.png') }}" onload="this.style.opacity='1'"
                                onerror="this.style.opacity='0'" alt="Transparencia">
                        </div>
                        <div class="feature-icon"><i class="bi bi-broadcast"></i></div>
                        <h3>Distribución Masiva</h3>
                        <p style="color: #64748b;">Despacha campañas de correo automáticas (Cron Jobs) con los visores
                            públicos (PDF) para que tus clientes paguen visualizando la foto del contador de su puerta.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BANNER INFERIOR -->
    <section class="cta-banner">
        <div class="container">
            <h2>Comienza a medir con inteligencia.</h2>
            <p>Implementa tecnología que tus equipos y tus clientes adorarán.</p>
            <a href="/register" class="btn-modern"
                style="background: white; color: var(--primary); padding: 18px 45px; font-size: 1.25rem;">
                Integrar mi Empresa Hoy <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </section>

</body>

</html>