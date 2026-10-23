<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marco Legal, Términos de Servicio y Delimitación de Responsabilidad | MedFlow</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNiAxNiI+PHBhdGggZmlsbD0iIzBkNmVmZCIgZD0iTTE0LjUgM2EuNS41IDAgMCAxIC41LjV2OWEuNS41IDAgMCAxLS41LjVoLTEzYS41LjUgMCAwIDEtLjUtLjV2LTlhLjUuNSAwIDAgMSAuNS0uNWgxM3ptLTEzLTFBMS41IDEuNSAwIDAgMCAwIDMuNXY5QTEuNSAxLjUgMCAwIDAgMS41IDE0aDEzYTEuNSAxLjUgMCAwIDAgMS41LTEuNXYtOUExLjUgMS41IDAgMCAwIDE0LjUgMmgtMTN6Ii8+PHBhdGggZmlsbD0iIzBkNmVmZCIgZD0iTTMgOC41YS41LjUgMCAwIDEgLjUtLjVoMS44OWwxLjI1NC0yLjUwOGEuNS41IDAgMCAxIC45MS0uMDE2bDEuMjk0IDIuOTgzLjg0NC0xLjI2NmEuNS41IDAgMCAxIC44NDMuMDE4bDEuMTA5IDEuNzg5SDEyLjVhLjUuNSAwIDAgMSAwIDFIMTFhLjUuNSAwIDAgMS0uNDI0LS4yMzZsLS42ODktMS4xMS0uODQ5IDEuMjcyYS41LjUgMCAwIDEtLjg0NC0uMDE3bC0xLjMtMy0xLjI0NiAyLjQ5MkEuNS41IDAgMCAxIDUuMiA5SDMuNWEuNS41IDAgMCAxLS41LS41eiIvPjwvc3ZnPg==">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.7;
        }
        .legal-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 3rem 0 2.5rem;
            border-bottom: 3px solid #0284c7;
        }
        .legal-badge-pill {
            background: rgba(2, 132, 199, 0.2);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.4);
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .legal-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        .legal-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f1f5f9;
        }
        .legal-section-title i {
            color: #0284c7;
        }
        .highlight-box {
            background: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 1.25rem;
            border-radius: 0 8px 8px 0;
            margin: 1.25rem 0;
            font-size: 0.95rem;
        }
        .danger-box {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 1.25rem;
            border-radius: 0 8px 8px 0;
            margin: 1.25rem 0;
            font-size: 0.95rem;
        }
        .success-box {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 1.25rem;
            border-radius: 0 8px 8px 0;
            margin: 1.25rem 0;
            font-size: 0.95rem;
        }
        .toc-list a {
            color: #475569;
            text-decoration: none;
            display: block;
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .toc-list a:hover {
            background: #f1f5f9;
            color: #0284c7;
            padding-left: 0.9rem;
        }
        @media print {
            .no-print { display: none !important; }
            .legal-card { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="legal-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="/" class="text-white text-decoration-none d-flex align-items-center gap-2">
                    <span class="fs-3 fw-bold text-primary"><i class="bi bi-activity text-info me-1"></i>Med<span class="text-white">Flow</span></span>
                </a>
                <div class="d-flex gap-2 no-print">
                    @auth
                        <a href="/dashboard" class="btn btn-outline-light btn-sm rounded-pill px-3">
                            <i class="bi bi-speedometer2 me-1"></i> Ir al Panel
                        </a>
                    @else
                        <a href="/login" class="btn btn-outline-light btn-sm rounded-pill px-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                        </a>
                    @endauth
                    <button onclick="window.print()" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="bi bi-printer me-1"></i> Imprimir / Guardar PDF
                    </button>
                </div>
            </div>
            <div class="legal-badge-pill mb-2">
                <i class="bi bi-shield-check"></i> Términos de Servicio y Delimitación de Responsabilidad Operativa (SaaS)
            </div>
            <h1 class="h2 fw-bold mb-2">Marco Legal y Régimen de Responsabilidad de MedFlow</h1>
            <p class="text-slate-300 mb-0" style="color: #cbd5e1; max-width: 850px;">
                Instrumento regulatorio de alcance técnico, deslinde metrológico, régimen de datos personales (Ley 25.326) 
                y cláusulas de indemnidad aplicables a Barrios Privados, Clubes de Campo, Consorcios, Cooperativas, Municipios y Empresas en la República Argentina.
            </p>
        </div>
    </header>

    <main class="container py-5">
        <div class="row">
            <!-- Tabla de Contenidos (Sticky lateral) -->
            <div class="col-lg-3 d-none d-lg-block no-print">
                <div class="sticky-top" style="top: 2rem;">
                    <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-white">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-list-nested me-2 text-primary"></i>Índice Temático</h6>
                        <nav class="toc-list">
                            <a href="#art1">1. Naturaleza Instrumental</a>
                            <a href="#art2">2. Deslinde de Metrología Legal</a>
                            <a href="#art3">3. Responsabilidad de la Entidad</a>
                            <a href="#art4">4. Indemnidad ante Reclamos</a>
                            <a href="#art5">5. Datos Personales (Ley 25.326)</a>
                            <a href="#art6">6. Backups y Libros Contables</a>
                            <a href="#art7">7. Inspectores y Delegación</a>
                            <a href="#art8">8. Límite de Responsabilidad</a>
                            <a href="#art9">9. Contralor, Adulteración y Error Humano</a>
                            <a href="#art10">10. Jurisdicción y Ley Aplicable</a>
                        </nav>
                        <div class="border-top pt-3 mt-3 text-center">
                            <span class="badge bg-light text-muted border py-2 px-3 w-100" style="font-size: 0.72rem;">
                                <i class="bi bi-geo-alt me-1"></i> República Argentina
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="col-lg-9">
                
                <!-- Resumen Ejecutivo -->
                <div class="alert alert-primary border-0 rounded-4 p-4 mb-4 shadow-sm" style="background: linear-gradient(135deg, #e0f2fe, #f0f9ff); border-left: 5px solid #0284c7 !important;">
                    <div class="d-flex align-items-start gap-3">
                        <div class="fs-2 text-primary"><i class="bi bi-shield-shaded"></i></div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Aviso Regulatorio Fundamental</h5>
                            <p class="mb-0 text-muted" style="font-size: 0.92rem;">
                                MedFlow es una <strong>herramienta tecnológica de computación y digitalización SaaS</strong>. 
                                La entidad contratante (Barrio Privado, Municipio, Cooperativa o Consorcio) es la <strong>única y exclusiva responsable</strong> ante sus vecinos, socios o usuarios finales de las mediciones, liquidaciones, cobros y calibración física de los medidores, manteniendo a MedFlow y sus creadores <strong>totalmente indemnes</strong> de cualquier controversia o reclamo judicial/administrativo.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="legal-card" id="art1">
                    <h2 class="legal-section-title">
                        <i class="bi bi-cpu"></i> 1. Calificación Jurídica y Naturaleza Instrumental del Servicio
                    </h2>
                    <p>
                        El software <strong>MedFlow</strong> (en adelante, la "Plataforma" o el "Servicio") es provisto como una solución tecnológica bajo la modalidad de <em>Software as a Service</em> (SaaS). Su finalidad única y estricta es suministrar una <strong>interfaz instrumental informática</strong> para:
                    </p>
                    <ul>
                        <li>El registro y almacenamiento digital de fotografías de tomas de medidores capturadas en campo.</li>
                        <li>El cálculo aritmético automatizado de diferencias de lecturas entre registros sucesivos (consumo diferencial).</li>
                        <li>La organización geoespacial y secuencial de rutas de inspección.</li>
                    </ul>
                    <div class="highlight-box">
                        <strong>Declaración de Autonomía Instrumental:</strong> MedFlow opera de manera estrictamente pasiva respecto al origen de la información. La Plataforma <strong>no genera mediciones por sí misma</strong>, no audita fácticas instalaciones domiciliarias ni determina unilateralmente tarifas o políticas de cobro.
                    </div>
                </div>

                <div class="legal-card" id="art2">
                    <h2 class="legal-section-title">
                        <i class="bi bi-rulers"></i> 2. Deslinde Expreso de Metrología Legal y Servicios Públicos
                    </h2>
                    <p>
                        Para todos los efectos legales en el territorio de la República Argentina y demás jurisdicciones:
                    </p>
                    <ol>
                        <li>
                            <strong>No somos empresa distribuidora de servicios públicos:</strong> MedFlow y sus desarrolladores no revisten la calidad de concesionarios, distribuidores, entes de saneamiento ni prestadores de energía, gas o agua potable domiciliaria.
                        </li>
                        <li>
                            <strong>Inexistencia de Certificación Metrológica (Ley Nacional N° 19.511):</strong> MedFlow <strong>NO certifica ni homologa</strong> la calibración, el aforo, la precisión técnica ni la legalidad metrológica de los instrumentos físicos de medición (caudalímetros, medidores electromagnéticos, contadores de pulsos o medidores mecánicos instalados en el terreno).
                        </li>
                        <li>
                            <strong>Averías, Fugas y Descalibraciones Físicas:</strong> Cualquier discrepancia derivada de medidores descalibrados, trabados, con marcha inversa, fugas internas en domicilios particulares, pérdidas en red o desfasajes por sedimentación es de resorte exclusivo entre el titular de la instalación y la Administración del predio.
                        </li>
                    </ol>
                </div>

                <div class="legal-card" id="art3">
                    <h2 class="legal-section-title">
                        <i class="bi bi-building"></i> 3. Responsabilidad Exclusiva de la Entidad Administradora
                    </h2>
                    <p>
                        La persona humana o jurídica que contrate, cree un espacio de trabajo (Workspace) o administre registros en MedFlow (sea bajo la figura de <strong>Barrio Cerrado / Club de Campo</strong> conforme arts. 2073 y concordantes del Código Civil y Comercial de la Nación, <strong>Consorcio de Propiedad Horizontal</strong> - art. 2037 CCCN, <strong>Cooperativa de Provisión</strong> - Ley 20.337, o <strong>Municipio</strong>):
                    </p>
                    <ul>
                        <li>
                            <strong>Carga y Veracidad del Dato:</strong> Es la <strong>única y exclusiva responsable</strong> de la veracidad, exactitud, integridad, revisión y auditoría de los datos numéricos, fotos de respaldo, identificadores de lotes o medidores y fechas introducidas manualmente o por carga masiva.
                        </li>
                        <li>
                            <strong>Supervisión de Auditoría:</strong> El software cuenta con alertas de "Consumo Negativo" y "Fechas Desfasadas". La decisión de corregir, aceptar, anular o imputar una lectura a la liquidación final de expensas o tasas es un acto discrecional y privativo del Administrador.
                        </li>
                        <li>
                            <strong>Relación Contractual con los Vecinos/Abonados:</strong> El vínculo jurídico, contractual, arancelario y de facturación subsiste pura y exclusivamente entre el Administrador y los propietarios/vecinos/socios.
                        </li>
                    </ul>
                </div>

                <div class="legal-card" id="art4">
                    <h2 class="legal-section-title">
                        <i class="bi bi-shield-x"></i> 4. Cláusula de Indemnidad Total y Exención de Responsabilidad (Hold Harmless)
                    </h2>
                    <div class="danger-box">
                        <strong>PACTO DE INDEMNIDAD EXPRESA:</strong> El Administrador y la entidad usuaria se obligan formalmente a <strong>mantener plenamente indemne</strong> a MedFlow, sus desarrolladores, directores, apoderados y personal técnico ante cualquier reclamo, intimación prejudicial, mediación obligatoria, demanda civil, comercial o penal, o procedimiento administrativo incoado por vecinos, copropietarios, inquilinos, socios, usuarios finales, o terceros.
                    </div>
                    <p>
                        Esta inmunidad comprende, sin limitación:
                    </p>
                    <ul>
                        <li>Reclamos por cobros presuntamente indebidos, cálculos de expensas, sobreconsumos o facturación de servicios en barrios cerrados o cooperativas.</li>
                        <li>Denuncias tramitadas ante la <strong>Dirección Nacional de Defensa del Consumidor (Ley 24.240)</strong>, Tribunales Arbitrales de Consumo, Juzgados de Paz o entes reguladores provinciales o nacionales (ADA, ENRE, ENARGAS, ERAS, etc.).</li>
                        <li>Cuestionamientos técnicos sobre la validez probatoria de las fotos tomadas por inspectores de campo.</li>
                        <li>Demandas por daños y perjuicios atribuidos a interrupciones técnicas o fallas de conectividad durante la toma de datos.</li>
                    </ul>
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                        En caso de que MedFlow fuera citado judicial o extrajudicialmente por una controversia nacida de los datos de un Administrador, este último deberá asumir de inmediato la totalidad de las costas, honorarios letrados y daños emergentes.
                    </p>
                </div>

                <div class="legal-card" id="art5">
                    <h2 class="legal-section-title">
                        <i class="bi bi-lock"></i> 5. Régimen de Protección de Datos Personales (Ley 25.326 - Argentina)
                    </h2>
                    <p>
                        En estricto cumplimiento de la <strong>Ley de Protección de Datos Personales N° 25.326</strong>, su Decreto Reglamentario 1558/2001 y las disposiciones de la Agencia de Acceso a la Información Pública (AAIP):
                    </p>
                    <div class="row g-3 my-2">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-person-badge me-1 text-primary"></i> Rol de la Entidad Administradora</h6>
                                <p class="small text-muted mb-0">
                                    Reviste el carácter de <strong>"Responsable del Tratamiento y de la Base de Datos"</strong> (Art. 2 Ley 25.326). Declara contar con la autorización legítima y/o base contractual con sus administrados para procesar sus tomas y ubicaciones de medidores.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-gear-wide-connected me-1 text-primary"></i> Rol de MedFlow</h6>
                                <p class="small text-muted mb-0">
                                    Reviste el carácter de <strong>"Encargado del Tratamiento"</strong> (Art. 25 Ley 25.326). Procesa los datos por cuenta y orden técnica del Responsable, sin adquirir derecho de propiedad sobre ellos.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="success-box">
                        <strong>Garantía de No Comercialización:</strong> MedFlow <strong>NO vende, NO alquila, NO cede, NO comparte ni comercializa</strong> con terceros bases de datos de medidores, correos de vecinos, fotos ni registros históricos bajo ninguna modalidad.
                    </div>
                </div>

                <div class="legal-card" id="art6">
                    <h2 class="legal-section-title">
                        <i class="bi bi-cloud-arrow-down"></i> 6. Deber de Respaldo Contable y Conservación Documental
                    </h2>
                    <p>
                        De conformidad con el Código Civil y Comercial de la Nación (arts. 320 y subsiguientes sobre la obligación de llevar y conservar documentación respaldatoria de consorcios y personas jurídicas):
                    </p>
                    <ul>
                        <li>
                            MedFlow facilita mecanismos de <strong>exportación masiva en formatos estándar (Excel, CSV e informes de vinculación fotográfica)</strong> para que el Administrador pueda descargar sus datos.
                        </li>
                        <li>
                            Es <strong>obligación legal e indelegable del Administrador</strong> generar copias de seguridad locales y archivar las evidencias necesarias para sus asambleas anuales, balances contables y rendición de cuentas (art. 2067 CCCN).
                        </li>
                        <li>
                            MedFlow no asume el rol de escribanía digital ni de archivo perenne perpetuo fuera de los períodos de suscripción activa y límites del plan contratado.
                        </li>
                    </ul>
                </div>

                <div class="legal-card" id="art7">
                    <h2 class="legal-section-title">
                        <i class="bi bi-people"></i> 7. Inspectores, Colaboradores y Usuarios Invitados
                    </h2>
                    <p>
                        La creación de cuentas para inspectores, lectores de campo o colaboradores delegados en un Workspace es facultad del Administrador. La entidad asume la supervisión estricta de sus operadores, respondiendo por el buen o mal uso que estos hagan de las credenciales de acceso, dispositivos móviles y captura de imágenes.
                    </p>
                </div>

                <div class="legal-card" id="art8">
                    <h2 class="legal-section-title">
                        <i class="bi bi-slash-circle"></i> 8. Límite Pecuniario de Responsabilidad (Arts. 1720 y 1743 CCCN)
                    </h2>
                    <p>
                        En la máxima medida autorizada por la legislación aplicable:
                    </p>
                    <p>
                        Bajo ninguna circunstancia MedFlow ni sus autores responderán frente al Administrador por lucro cesante, pérdidas indirectas, reclamos de asambleas de copropietarios o daños punitivos. La responsabilidad patrimonial máxima acumulada de MedFlow frente al usuario contratante se limitará formalmente al <strong>monto total abonado en concepto de suscripción por el cliente en el último mes de servicio inmediatamente anterior</strong> al hecho que motiva la reclamación.
                    </p>
                </div>

                <div class="legal-card" id="art9">
                    <h2 class="legal-section-title">
                        <i class="bi bi-shield-check"></i> 9. Función de Contralor Neutral, Prohibición de Adulteración Dolosa y Régimen del Error Humano
                    </h2>
                    
                    <h5 class="fw-bold text-dark mt-3">9.1. MedFlow como Instrumento de Contralor y Transparencia Bilateral</h5>
                    <p>
                        La plataforma ha sido concebida con un propósito de <strong>ordenamiento, transparencia y contralor técnico objetivo</strong> para todas las partes intervinientes:
                    </p>
                    <ul>
                        <li>
                            <strong>Para la Entidad Administradora:</strong> Permite supervisar y fiscalizar en tiempo real el desempeño de sus inspectores en campo, corroborando que las tomas cuenten con respaldo fotográfico real, fecha y hora de captura, evitando la invención de lecturas desde el escritorio.
                        </li>
                        <li>
                            <strong>Para los Vecinos, Consorcistas y Abonados:</strong> El sistema provee un canal de transparencia mediante el <strong>Visor Público de Mediciones (Token / QR)</strong>, permitiendo que cada usuario final audite por sí mismo la fotografía física del visor de su medidor y la compare contra la liquidación aplicada en su expensa o factura.
                        </li>
                        <li>
                            <strong>Neutralidad Algorítmica Absoluta:</strong> MedFlow no favorece ni altera cálculos en beneficio de ninguna de las partes. El cómputo responde invariablemente a la fórmula matemática objetiva de consumo diferencial entre lecturas sucesivas.
                        </li>
                    </ul>

                    <h5 class="fw-bold text-dark mt-4">9.2. Prohibición Terminante de Falsificación, Trucaje o Manipulación Dolosa</h5>
                    <div class="danger-box">
                        <strong>PROHIBICIÓN PENAL DE FALSEAMIENTO:</strong> Queda terminantemente prohibido a cualquier administrador, operador o inspector introducir datos deliberadamente falsos, trucar o editar fotografías, utilizar fotos apócrifas o de medidores ajenos, o alterar retroactivamente fechas con el propósito de perjudicar a vecinos, generar sobrefacturaciones o disimular desvíos.
                    </div>
                    <p>
                        Dichas conductas configuran delitos de acción pública tipificados en los <strong>Arts. 172, 173 y 292 del Código Penal de la Nación Argentina</strong> (Defraudación, Administración Fraudulenta y Falsificación de Documentos). 
                        El Administrador o la persona física que ejecute la maniobra es el <strong>único y exclusivo responsable penal, civil y disciplinario</strong>. MedFlow queda totalmente desvinculada de tales actos, no pudiendo imputársele complicidad, negligencia ni defecto de software.
                    </p>
                    <p class="small text-muted">
                        <strong>Trazabilidad y Colaboración Judicial:</strong> MedFlow registra metadatos inmutables de auditoría (direcciones IP de conexión, usuario autenticado, timestamps inalterables de creación <code>created_at</code> y modificación <code>updated_at</code>). Ante requerimiento fundado de autoridades judiciales, fiscalías o juzgados en el marco de una investigación penal por fraude, MedFlow aportará la información técnica forense disponible para individualizar al autor de la adulteración.
                    </p>

                    <h5 class="fw-bold text-dark mt-4">9.3. Régimen del Error Humano Involuntario</h5>
                    <p>
                        Se reconoce que la tarea de lectura en terreno está expuesta a la falibilidad humana natural (confusión involuntaria de dígitos bajo reflejos solares, tipeo numérico erróneo, desgaste del visor mecánico del medidor o inversión de lotes contiguos).
                    </p>
                    <ul>
                        <li>
                            MedFlow proporciona <strong>herramientas de fiscalización preventiva</strong> (alertas automáticas de Consumo Negativo, detección de Fechas Desfasadas, Zoom HD de contraste fotográfico y picos de consumo en el Mapa GIS).
                        </li>
                        <li>
                            <strong>Deber de Diligencia Previa:</strong> Estas alertas actúan como asistentes de apoyo y <strong>no eximen a la Administración de su deber de revisar y auditar las lecturas</strong> antes de emitir cobros a los vecinos.
                        </li>
                        <li>
                            Si un operador comete un error de tipeo y la administración no lo subsana en su bandeja de auditoría antes de liquidar, dicho error no es imputable al software ni da derecho a reclamación alguna contra MedFlow.
                        </li>
                    </ul>
                </div>

                <div class="legal-card" id="art10">
                    <h2 class="legal-section-title">
                        <i class="bi bi-bank"></i> 10. Legislación Aplicable y Jurisdicción Exclusiva
                    </h2>
                    <p>
                        El presente acuerdo y cualquier relación contractual entre el usuario y MedFlow se regirá e interpretará íntegramente de acuerdo con las leyes de la <strong>República Argentina</strong>.
                    </p>
                    <p>
                        Para dirimir cualquier controversia que pudiera surgir, las partes se someten a la jurisdicción exclusiva de los <strong>Tribunales Ordinarios en lo Comercial de la sede legal de los titulares de la plataforma</strong>, renunciando de manera expresa a cualquier otro fuero o jurisdicción que pudiera corresponderles por sus domicilios presentes o futuros, incluidos los fueros federales o de consumo en relaciones B2B entre administradores y el software.
                    </p>
                </div>

                <!-- Footer del documento -->
                <div class="card border-0 bg-light rounded-4 p-4 text-center text-muted" style="font-size: 0.85rem;">
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span class="fw-bold text-dark">Protocolo Legal SaaS - MedFlow Argentina</span>
                    </div>
                    <p class="mb-1">
                        Última actualización y entrada en vigencia: <strong>Octubre 2026</strong>.
                    </p>
                    <p class="mb-0">
                        Al utilizar la plataforma, registrar una cuenta o acceder al panel operativo, usted manifiesta haber leído, comprendido y aceptado en su totalidad las presentes condiciones legales.
                    </p>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer Simple -->
    <footer class="bg-white border-top py-4 text-center text-muted" style="font-size: 0.85rem;">
        <div class="container">
            <p class="mb-1">&copy; {{ date('Y') }} MedFlow - Plataforma de Gestión y Medición Inteligente de Sensores.</p>
            <p class="mb-0 text-muted">Todos los derechos reservados. Sujeto a legislación de la República Argentina.</p>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
