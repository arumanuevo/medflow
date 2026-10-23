@extends('layouts.modern')

@section('content')
    <div class="container-fluid py-4">

        <!-- Encabezado del Centro de Ayuda -->
        <div class="row mb-5 justify-content-center text-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-3"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i> Centro de Ayuda
                    MedFlow</h2>
                <p class="text-muted fs-5">Encuentra respuestas rápidas y aprende a sacar el máximo provecho de tu gestión
                    térmica y estructural.</p>

                <div class="input-group mt-4 shadow-sm" style="max-width: 600px; margin: 0 auto;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="helpSearch" class="form-control border-start-0 py-3"
                        placeholder="Buscar por palabra clave (ej: Mapa GIS, Coordenadas, Calendario, Tolerancias, Plantillas)...">
                </div>
            </div>
        </div>

        <!-- Grilla Mampostería (Masonry) para Módulos -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="helpTopics">

            <!-- Módulo 1: Primeros Pasos -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-rocket-takeoff fs-5"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Primeros Pasos</h5>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="¿Cómo navego el tablero?"
                                    data-content="El tablero principal te permite visualizar el estado global de tu organización. Desde el menú lateral izquierdo puedes acceder a todas tus propiedades, crear plantillas métricas, programar tu Calendario de Tomas y Tolerancias, invitar colaboradores y extraer un resumen estadístico de las lecturas registradas."
                                    data-steps="Ve al menú lateral izquierdo y haz clic en &lt;strong&gt;Dashboard&lt;/strong&gt;.">
                                    <i class="bi bi-play-circle me-1 text-primary"></i> ¿Cómo navego el dashboard?</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Configuración inicial"
                                    data-content="Recomendamos empezar creando una 'Plantilla'. ¿Por qué? Porque la plantilla dicta reglas universales (ej: el costo fijo, multiplicador de moneda o límites). Así evitas configurar mecánicamente la misma regla cientos de veces para cada sensor de un barrio o planta."
                                    data-steps="Navega a &lt;strong&gt;Plantillas&lt;/strong&gt; desde el menú lateral, crea una y luego ve a &lt;strong&gt;Grupos&lt;/strong&gt;.">
                                    <i class="bi bi-play-circle me-1 text-primary"></i> Configuración inicial
                                    recomendada</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Mis Límites y Suscripción"
                                    data-content="Los planes Freemium tienen límites de cantidad de sensores. Ve a 'Planes y Facturación' para revisar los cupos de uso que tienes activos. Todo se sincroniza en tiempo real."
                                    data-steps="Haz clic en &lt;strong&gt;Facturación&lt;/strong&gt; o &lt;strong&gt;Suscripción&lt;/strong&gt; en el menú lateral o desplegable debajo de tu usuario.">
                                    <i class="bi bi-play-circle me-1 text-primary"></i> Revisar mi plan actual</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Módulo 2: Sensores y Mediciones -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-broadcast fs-5"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Sensores y Medición</h5>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Crear un Sensor"
                                    data-content="Dirígete al menú de Sensores. Cada dispositivo requiere obligatoriamente pertenecer a un Grupo. Es de suma importancia que la Nomenclatura del sensor (el nombre o código de serie) sea legible y preciso en los campos de metadatos, ya que esta información convive en la base de datos y será la única forma de identificar físicamente el medidor correcto en el campo."
                                    data-steps="Ve a &lt;strong&gt;Sensores > Nuevo Sensor&lt;/strong&gt; y selecciona a qué grupo pertenece en el formulario.">
                                    <i class="bi bi-hdd-network me-1 text-success"></i> ¿Cómo cargo un nuevo sensor?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Carga Masiva"
                                    data-content="Si decides tomar las mediciones de docenas de sensores a la vez en lugar de uno por uno, la ruta 'Mediciones Masivas' te permite generar una lista tipo Check-List Excel. Marcando rápidamente los valores y adjuntando las fotos al unísono."
                                    data-steps="Accede a &lt;strong&gt;Mediciones > Carga Masiva&lt;/strong&gt; para ver la lista de los indicadores.">
                                    <i class="bi bi-hdd-network me-1 text-success"></i> La medición masiva en ruta</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Módulo Unificado: Mediciones y Consumos"
                                    data-content="Las pantallas de Mediciones y Consumos ahora están integradas en una única vista inteligente y ultra-rápida. En ella puedes consultar la lectura física del medidor, el consumo diferencial en m³ o kWh, el intervalo de días entre tomas, el promedio diario y el costo monetario calculado en base a la tarifa del grupo. Además, incluye botones para el Radar Global de Anomalías, Análisis Avanzado con gráficos por sensor, y Exportación completa a Excel (.xlsx) respetando todos los filtros que tengas aplicados."
                                    data-steps="Ingresa a &lt;strong&gt;Mediciones y Consumos&lt;/strong&gt; desde el menú lateral. Utiliza los filtros de grupo, fecha o estado, y haz clic en &lt;strong&gt;Exportar a Excel&lt;/strong&gt; o en el icono de gráfico de cualquier sensor.">
                                    <i class="bi bi-graph-up-arrow me-1 text-primary"></i> Vista Unificada de Mediciones y Consumos</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Panel de Filtros Avanzados, Búsqueda de Lotes y Cruces AND/OR"
                                    data-content="El Panel Unificado de Mediciones y Consumos te permite filtrar por Sensor, Identificador, Grupo, Plantilla, Estado de Auditoría, Fotografía, Consumo/Costo y Fechas, además de cualquier atributo dinámico (como Lote o Manzana).&lt;br&gt;&lt;br&gt;&lt;strong&gt;¿Cómo buscar múltiples lotes (ej: 100 y 144)?&lt;/strong&gt;&lt;br&gt;En bases de datos, cada medidor tiene un solo lote. Para traer mediciones de los lotes 100 y 144 simultáneamente, debes usar el operador &lt;strong&gt;Coincide con alguno (Lista / OR)&lt;/strong&gt; y escribir &lt;code&gt;100, 144&lt;/code&gt;. Si usaras el operador &lt;em&gt;AND (todas las palabras)&lt;/em&gt;, el sistema buscaría un medidor cuyo lote sea 100 y 144 a la vez en la misma fila (lo cual no existe y da 0 resultados).&lt;br&gt;&lt;br&gt;Además, la &lt;strong&gt;Interpretación Semántica&lt;/strong&gt; en vivo te explica en lenguaje natural qué registros se están buscando a medida que seleccionas tus filtros."
                                    data-steps="En &lt;strong&gt;Mediciones y Consumos&lt;/strong&gt;, selecciona el campo personalizado (ej: Lote), el operador &lt;strong&gt;Coincide con alguno (Lista)&lt;/strong&gt;, escribe los valores separados por coma (ej: 100, 144) y pulsa &lt;strong&gt;Filtrar Resultados&lt;/strong&gt;.">
                                    <i class="bi bi-funnel-fill me-1 text-primary"></i> Filtrado avanzado de Lotes y lógica AND / OR</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Carga Masiva de Fotos, Retención e Informe de Diagnóstico"
                                    data-content="En el listado de 'Mediciones y Consumos' dispones del botón 'Carga Masiva Fotos'. Esta herramienta te permite arrastrar un archivo comprimido .ZIP (recomendado para miles de fotos) o un lote de imágenes .JPG / .PNG. El sistema ejecuta una verificación estricta: solo se guardan fotos que coincidan con mediciones registradas en el último año (máximo 365 días), descartando fotos huérfanas o vencidas para resguardar el almacenamiento de tu servidor.<br><br><strong>Informe Descargable de Supervisión (.CSV / Excel):</strong><br>Una vez finalizado el procesamiento, dispones del botón <strong>Descargar Informe Detallado</strong>. Este informe (abrible en Excel con codificación UTF-8) detalla foto por foto: archivo, estado (Vinculada, Rechazada por > 1 año, Sin coincidencia en BD o Formato inválido), diagnóstico del problema, medidor y fecha de toma. Además, en pantalla puedes filtrar el detalle en vivo por 'Vinculadas' o 'Con Problemas' con su buscador instantáneo."
                                    data-steps="Haz clic en &lt;strong&gt;Carga Masiva Fotos&lt;/strong&gt; en Mediciones, sube tu archivo .ZIP o imágenes y presiona &lt;strong&gt;Iniciar Carga y Validación&lt;/strong&gt;. Al finalizar, pulsa &lt;strong&gt;Descargar Informe Detallado (CSV / Excel)&lt;/strong&gt; o &lt;strong&gt;Ver Detalle y Supervisión&lt;/strong&gt;.">
                                    <i class="bi bi-cloud-arrow-up me-1 text-info"></i> Asistente de Carga Masiva de Fotos e Informe de Supervisión</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Sobreescritura de Sensores sin Consumo de Cuota"
                                    data-content="Al reimportar un lote de sensores en un grupo existente (por ejemplo, para actualizar campos personalizados o direcciones), el sistema detecta las coincidencias por el Identificador Único. Los sensores coincidentes se actualizan y sobreescriben en el lugar, garantizando que NO se consuman cupos adicionales de tu suscripción."
                                    data-steps="Ve a &lt;strong&gt;Grupos > Importar Sensores&lt;/strong&gt; y sube tu Excel. El sistema verificará los identificadores preexistentes y solo contabilizará para la cuota los sensores genuinamente nuevos.">
                                    <i class="bi bi-arrow-repeat me-1 text-success"></i> Sobreescritura de Sensores y Cuotas</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Auditoría de Consumos Negativos y Corrección Histórica"
                                    data-content="Si detectas consumos negativos causados por errores de tipeo o por medidores reemplazados sin precargar el reseteo previo, puedes resolverlos directamente desde el panel de Auditoría. Al hacer clic en la tarjeta de 'Consumos Negativos', verás el detalle cronológico de cada caso. Dispones del botón 'Ver Foto' para contrastar los números reales del aparato, 'Cambio Medidor' para normalizarlo en 1 clic como nuevo contador base, o 'Corregir' para modificar el valor o fecha. Al corregir, el sistema te muestra el rango seguro entre la lectura anterior y la posterior, y el interruptor 'Forzar corrección histórica' te permite omitir el bloqueo secuencial si la medición siguiente también requiriese ajustes."
                                    data-steps="Pasos: &lt;br&gt;1. Ve a &lt;strong&gt;Mediciones y Consumos&lt;/strong&gt; y haz clic en &lt;strong&gt;Auditoría&lt;/strong&gt;.&lt;br&gt;2. Presiona sobre la tarjeta roja de &lt;strong&gt;Consumos Negativos&lt;/strong&gt;.&lt;br&gt;3. Revisa la foto del medidor y elige &lt;strong&gt;Cambio Medidor&lt;/strong&gt; (si el aparato era nuevo) o &lt;strong&gt;Corregir&lt;/strong&gt; (si fue un error de carga).">
                                    <i class="bi bi-shield-check me-1 text-danger"></i> Auditoría de Consumos Negativos y Fotos</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Tomar Mediciones Manuales"
                                    data-content="Al registrar lecturas individuales desde 'Tomar Mediciones', podrás subir valores. ¿Por qué es vital exigir o adjuntar la foto del medidor? La fotografía funciona como 'seguro anti-reclamos' y como auditoría de calidad. Si un inspector ingresa un número defectuoso equivocado (ej: 1000 en vez de 100), el administrador siempre tendrá la chance de corregir consultando la imagen fuente resguardada en el registro."
                                    data-steps="Ingresa a &lt;strong&gt;Tomar Mediciones (Menú Izquierdo)&lt;/strong&gt; y elige el sensor o lote a registrar.">
                                    <i class="bi bi-hdd-network me-1 text-success"></i> ¿Cómo tomar mediciones?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Reemplazo de un Medidor Roto"
                                    data-content="Al registrar una nueva medición desde 'Tomar Mediciones', si el equipo físico fue reemplazado, activa el botón '¿Se reemplazó este medidor físico por uno nuevo?'. Aparecerán dos opciones para eludir el error de 'Consumo Negativo'. Opción 1 (Reseteo Simple): Ignora el consumo de ese periodo y en su lugar inicia una tarifa cero a partir del nuevo aparato. Opción 2 (Cierre Exacto): Te pedirá la última lectura antes de desconectar el equipo viejo y el arranque del nuevo equipo, sumando los gastos para que factures con precisión absoluta el cruce sin alterar los gráficos históricos ni regalar el consumo mensual."
                                    data-steps="Disponible al tomar una nueva lectura manual desde &lt;strong&gt;Tomar Mediciones (Paso a Paso)&lt;/strong&gt; marcando la casilla desplegable.">
                                    <i class="bi bi-hdd-network me-1 text-success"></i> ¿Qué hago si debo cambiar un
                                    medidor?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Reemplazo y la App Móvil (Flutter)"
                                    data-content="Debido a que la App Móvil para inspectores tiene validaciones estrictas y bloquea el guardado si el operario ingresa un número MENOR a la &lt;strong&gt;última medición&lt;/strong&gt; conocida, es vital que EL ADMINISTRADOR ingrese de forma anticipada una &lt;strong&gt;medición con reseteo (Web)&lt;/strong&gt; el día del reemplazo de hardware. Al hacer esto (ej: dejando la cuenta en cero), cuando el inspector presione &lt;strong&gt;Sincronizar&lt;/strong&gt; en su teléfono, bajará ese 'cero' como la última marca registrada. Así, cuando llegue al sitio y el medidor nuevo marque '15', la app validará que 15 es mayor a 0 y lo dejará trabajar sin errores."
                                    data-steps="Pasos (Admin): &lt;br&gt;1. Sabiendo que el aparato del lote se cambió, ve a la Web y marca un Registro Manual en '0' usando la opción &lt;strong&gt;Reseteo de Medidor&lt;/strong&gt;.&lt;br&gt;2. Envía la ruta al Inspector al día siguiente (La App descargará el nuevo cero como 'última lectura').&lt;br&gt;3. El inspector mide en campo de manera normal y el celular le aprueba el dato.">
                                    <i class="bi bi-phone me-1 text-primary"></i> Cambios de Sensor desde la App Móvil</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Matemáticas del Cambio de Medidor (Consumos)"
                                    data-content="¿Tienes dudas de cómo el sistema elude un consumo negativo? &lt;strong&gt;¡No te preocupes!&lt;/strong&gt; El servidor NUNCA intentará restar la lectura nueva del inspector contra la vieja que se rompió (ej: 15 vs 8500). Gracias a que tú (Administrador) has precargado un registro de 'Reseteo' en el sistema, éste actuará como un &lt;strong&gt;Muro Cortafuegos&lt;/strong&gt;. El algoritmo dividirá la línea de tiempo en dos: Un periodo que cierra la etapa vieja cobrando el valor exacto adeudado de los 8500, y otro periodo fresco donde el nuevo medidor arranca limpiamente cruzando su novedad contra tu Cero (Restando 15 - 0 = 15). Esto resguarda todo tu historial intacto."
                                    data-steps="Este proceso es automatizado por nuestro Backend matemático. Tu única responsabilidad es asegurarte de ingresar correctamente las reglas de 'Reseteo' en la Web.">
                                    <i class="bi bi-calculator me-1 text-info"></i> ¿Cómo calcula el consumo al
                                    cambiarlo?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Vencimiento de Fotos y Respaldos"
                                    data-content="Por políticas de velocidad y optimización del servidor, &lt;strong&gt;las evidencias fotográficas caducan automáticamente a los 365 días (1 año)&lt;/strong&gt; de haber sido subidas. El sistema te informará por email una semana antes. Ten la tranquilidad de que &lt;strong&gt;NUNCA se borrarán los consumos ni las estadísticas (metros cúbicos, gráficas, etc)&lt;/strong&gt;, únicamente se borrará la imagen física para liberar espacio de disco. Puedes entrar al menú de 'Backups/Respaldos' para descargar un ZIP con todas tus fotos con máxima velocidad gracias a nuestro motor 'Anti-Colapso' procesado desde tu navegador local."
                                    data-steps="Pasos: &lt;br&gt;1. Dirigete a la sección de Backups de Evidencias.&lt;br&gt;2. Selecciona un grupo.&lt;br&gt;3. El navegador empaquetará todo sin sobrecargar la red y arrojará un archivo .zip a tu computadora.">
                                    <i class="bi bi-archive me-1 text-warning"></i> Política de Retención y Backups ZIP</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Módulo 3: Colaboradores -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-people fs-5"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Colaboradores</h5>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Invitar Equipo"
                                    data-content="Desde la pestaña de colaboradores, puedes invitar inspectores de tu empresa. Llegará un correo formal otorgándoles acceso al Workspace activo. Recuerda que no todos pueden modificar estructuras; los operarios (inspectores) sólo cargarán valores."
                                    data-steps="En el menú lateral, selecciona &lt;strong&gt;Colaboradores > Invitar&lt;/strong&gt; y elige el rol (ej: Inspector).">
                                    <i class="bi bi-person-fill-add me-1 text-info"></i> Invitar a Operarios
                                    (Inspectores)</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="¿Qué es el aislamiento espacial?"
                                    data-content="El Workspace aísla la información totalmente. ¿Por qué se implementó esta segregación tan estricta? Para la privacidad integral de los datos. Un inspector puede trabajar recolectando lecturas para 10 empresas distintas con la misma cuenta; este diseño fuerza a que cada base de sensores y cobros quede blindada, asegurando que un usuario nunca cruce accidentalmente clientes."
                                    data-steps="El colaborador invitado deberá hacer clic en el nombre de su entorno en la esquina superior derecha para &lt;strong&gt;cambiar la vista de datos&lt;/strong&gt;.">
                                    <i class="bi bi-person-fill-add me-1 text-info"></i> Compartir accesos con Clientes</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Módulo 4: Finanzas y Áreas Comunes -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-wallet2 fs-5"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Finanzas y Áreas Comunes</h5>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="La Configuración Contable"
                                    data-content="Al editar un Grupo de Sensores, puedes habilitar la 'Configuración Contable'. Esto te permite asignar una moneda, un cargo fijo y un multiplicador de precio por unidad (Ej: $1500 por cada m3 de agua). Se reflejará como costo en tus tablas de consumo y en enlaces públicos si así lo habilitas."
                                    data-steps="Ve a &lt;strong&gt;Grupos de Sensores > Editar Grupo&lt;/strong&gt; y marca la opción &lt;strong&gt;Habilitar Monetización&lt;/strong&gt;.">
                                    <i class="bi bi-play-circle me-1 text-warning"></i> ¿Cómo funciona la Monetización?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Sensores de Área Común"
                                    data-content="Si administras un consorcio puedes marcar sensores como 'Áreas Comunes'. ¿Por qué se ofrece esta funcionalidad? Porque automatiza las finanzas evitando excel paralelos. El gasto en recursos públicos (ej: riego del parque) se abstrae y prorratea, dividiendo el costo total equitativamente e inyectándolo a cada boleto de los medidores privados del mismo grupo vecinal de manera transparente."
                                    data-steps="Al Crear o Editar un Sensor, activa la opción &lt;strong&gt;Es Área Común / Aplica a Prorrateo&lt;/strong&gt;.">
                                    <i class="bi bi-play-circle me-1 text-warning"></i> Prorrateo de gastos comunes</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Módulo 5: Secuencia de Operación -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-compass fs-5"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Secuencia de Operación</h5>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="El flujo de trabajo (Paso a Paso)"
                                    data-content="<strong>1. Crear Plantilla:</strong> Decide qué vas a medir (ej: Agua, Luz).<br><br><strong>2. Crear Grupo de Sensores:</strong> Agrupa todos los lotes de tu barrio o planta bajo la plantilla creada.<br><br><strong>3. Dar de Alta Sensores:</strong> Agrega cada Lote (con su nombre y correo en los datos adicionales).<br><br><strong>4. Programar en Calendario de Tomas:</strong> Define qué día del mes (ej: el día 15) o con qué frecuencia debes medir cada grupo o sensor, y configura sus días de pre-aviso y tolerancia.<br><br><strong>5. Medir y Repetir:</strong> Los inspectores enviarán lecturas desde la calle vía foto o texto con su celular o desde el Calendario.<br><br><strong>6. Campaña Pública:</strong> Terminado el ciclo, emites una campaña que envía Links por E-mail automáticamente a cada propietario con las estadísticas monetizadas."
                                    data-steps="Sigue el flujo de botones principales: &lt;br&gt;&lt;strong&gt;1. Plantillas > 2. Grupos > 3. Sensores > 4. Calendario de Tomas > 5. Mediciones.&lt;/strong&gt;">
                                    <i class="bi bi-pin-map me-1 text-danger"></i> Entender el circuito lógico del
                                    sistema</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Módulo 6: Planes y Suscripciones -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-secondary-subtle text-secondary rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-star-fill fs-5"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Planes y Suscripción</h5>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Limitaciones del Plan Free"
                                    data-content="El plan gratuito te permite acceder a las funcionalidades base como ver, crear y administrar medidores, grupos y mediciones individuales. Existe un límite de capacidad mensual para los sensores y funciones limitadas respecto a trabajo en equipo."
                                    data-steps="Puedes llevar un control de tus usos actuales accediendo a &lt;strong&gt;Planes y Facturación&lt;/strong&gt;.">
                                    <i class="bi bi-info-circle me-1 text-secondary"></i> ¿Qué incluye el Plan Free?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Beneficios de ser Premium"
                                    data-content="La suscripción Premium desbloquea el potencial total del sistema: Mapa Interactivo de Sensores (GIS) con diagnóstico geográfico de picos de consumo y estado de tomas, importación masiva de sensores, gestión y creación de plantillas métricas, incorporación de colaboradores e inspectores, exportación avanzada de datos y panel de analíticas exclusivas."
                                    data-steps="Visualizarás menús desbloqueados (ej. &lt;strong&gt;Mapa de Sensores, Analíticas, Importaciones&lt;/strong&gt;) de forma automática según la membresía.">
                                    <i class="bi bi-info-circle me-1 text-secondary"></i> Funcionalidades Premium</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Administrar mi Suscripción"
                                    data-content="Para cambiar tu plan, ver métodos de pago y ciclos de facturación, dirígete al menú de 'Planes y Facturación' en la navegación. Los cobros son procesados de forma segura e inmediata."
                                    data-steps="Visita &lt;strong&gt;Planes y Facturación > Cambiar Plan&lt;/strong&gt; para migrar tu nivel de suscripción.">
                                    <i class="bi bi-info-circle me-1 text-secondary"></i> ¿Cómo mejorar mi plan o pagar?</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Módulo 7: Migración e Importación Masiva (Excel) -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4" style="border-left: 4px solid #6f42c1 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-purple-subtle rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px; background: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                                <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Importación y Migraciones</h5>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="La Filosofía de Empate (Migrando a MedFlow)"
                                    data-content="Al transicionar desde sistemas arcaicos o archivos Excel aislados de tu empresa, el paso más delicado es la <strong>normalización</strong>. MedFlow no exige que 'destruyas' tu orden de columnas viejas; su sistema de importación inteligente te permitirá cargar tu <code>.xlsx</code> o <code>.csv</code> y solicitará que <strong>empates o enlaces (mapees)</strong> tus columnas antiguas con los campos requeridos por MedFlow (Ej: 'Nro de Medidor' se unirá a 'Identificador Físico')."
                                    data-steps="Disponible dentro del menú <strong>Sensores > Importar Sensores</strong>.">
                                    <i class="bi bi-diagram-3-fill me-1" style="color: #6f42c1;"></i> ¿Cómo empato mi
                                    sistema viejo?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Migración de Sensores Físicos (Base)"
                                    data-content="Al subir tu archivo Excel de Sensores, asegúrate de contar al menos con dos columnas madre: <strong>Un Identificador Único Universal (ID Físico/Serial del aparato)</strong> y <strong>El Nombre o Dirección</strong> del lote. Si tu excel antiguo tenía columnas adicionales como 'Nombre de Inquilino', 'Deuda Previa' o 'Piso', ¡no las descartes! MedFlow las convertirá automáticamente en <strong>Metadatos (Campos Extra)</strong> que quedarán inyectados permanentemente en el ecosistema, dándole contexto al inspector en campo."
                                    data-steps="1. Limpia tu Excel de celdas vacías en la cabecera.<br>2. Súbelo a <strong>Importar Sensores</strong>.<br>3. Mapea la columna serial y decide qué columnas extra guardar como Metadata.">
                                    <i class="bi bi-hdd-network-fill me-1" style="color: #6f42c1;"></i> Construyendo la base
                                    de aparatos</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Sobrescritura y Actualización Masiva (Sin costo de cuota)"
                                    data-content="¿Necesitas actualizar nombres, descripciones o añadir nuevas columnas a medidores que ya tienes cargados? Puedes volver a subir tu archivo Excel/CSV sobre el mismo Grupo. MedFlow detectará automáticamente los <strong>Identificadores</strong> ya existentes y <strong>sobrescribirá/actualizará</strong> su información sin duplicarlos.<br><br><strong>Consumo de Cupo en tu Plan:</strong> Las actualizaciones consumen <code>0 cupo</code> de suscripción. Únicamente se descuenta cupo de tu plan si el archivo incluye identificadores nuevos que no existían previamente en ese grupo."
                                    data-steps="1. Ve a <strong>Sensores > Importar Sensores</strong> y selecciona tu Grupo.<br>2. Sube la planilla con los mismos Identificadores y las nuevas columnas.<br>3. Mapea los campos; los registros coincidentes se actualizarán sin consumir cuota adicional.">
                                    <i class="bi bi-arrow-repeat me-1" style="color: #6f42c1;"></i> Sobrescritura y actualización sin costo</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Migración del Histórico de Consumos"
                                    data-content="Si deseas que los gráficos de MedFlow reaccionen al instante mostrando toda tu historia de consumos del último año, necesitas inyectar un histórico. Para que el motor asocie cada lectura pasada a su respectivo sensor, nuestro importador buscará <strong>únicamente el Identificador Físico</strong>. Tu Excel de Historial debe tener tres columnas excluyentes: <code>1) Identificador del Medidor</code>, <code>2) Fecha de la Medición (ideal en formato Año-Mes-Día YYYY-MM-DD)</code> y <code>3) El Valor numérico capturado</code>."
                                    data-steps="Deberás realizar esto <strong>DESPUÉS</strong> de haber importado los sensores primero. MedFlow rastreará todos los Identificadores coincidiendo y anidará tus historias fotográficas o datos numéricos sin colapsar.">
                                    <i class="bi bi-clock-history me-1" style="color: #6f42c1;"></i> Inyectar mediciones del
                                    pasado</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Resolución de Conflictos de Fechas (Excel)"
                                    data-content="El error de migración más grave sucede cuando Excel transforma ocultamente tus fechas en 'números de serie'. <strong>Antes de exportar y subir tu Excel</strong>: Selecciona la columna de fechas de tus mediciones históricas, presiona click derecho -> Formato de Celdas, y asegúrate que esté seteada explícitamente en formato de Texto (AÑO-MES-DIA) para evitar que hojas de cálculo antiguas rompan la línea de tiempo temporal (Time-series) del Analizador de Tasa Diaria del servidor."
                                    data-steps="Aplica esta revisión en tu Excel nativo previo a entrar en <strong>Importar Mediciones Masivas</strong>.">
                                    <i class="bi bi-exclamation-triangle-fill me-1" style="color: #6f42c1;"></i> Regla vital
                                    contra fallos de tiempos</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Fotos en la Importación Masiva (Referencias y Archivos)"
                                    data-content="Al importar mediciones desde Excel, el archivo sólo registra el <strong>nombre o referencia de la foto</strong> (ej: <code>100_17-06-2022</code> o <code>100_17-06-2022.jpg</code>). El sistema le añade automáticamente <code>.jpg</code> si no tiene extensión y lo vincula a <code>/measurements/</code>.<br><br><strong>¿Cómo subir las fotos físicas?</strong><br>• <strong>Desde la App:</strong> En la tabla de Mediciones, pulsa <strong>Ver / Reemplazar</strong> o <strong>Subir</strong> en cada fila.<br>• <strong>Por Servidor:</strong> Si tienes cientos de fotos, puedes copiarlas masivamente a la carpeta <code>public/measurements/</code> en tu servidor manteniendo los mismos nombres de archivo.<br>• <strong>Sin Foto:</strong> Si dejas la celda vacía o 'Sin Foto', la medición se guarda lista con el botón para adjuntar la foto cuando gustes."
                                    data-steps="Configura la columna Foto en tu Excel e importa normalmente desde <strong>Mediciones > Importar Mediciones</strong>.">
                                    <i class="bi bi-camera me-1" style="color: #6f42c1;"></i> Fotos en la importación masiva</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Módulo 8: App Móvil e Inspectores -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-phone-fill fs-5"></i>
                            </div>
                            <h5 class="fw-bold mb-0">App Móvil e Inspectores</h5>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Instalar MedFlow en el Celular (PWA)"
                                    data-content="MedFlow puede usarse como una app nativa. Para instalarla, abre la web en Safari (iOS) o Chrome (Android) desde tu celular. Luego, en las herramientas del navegador, selecciona <strong>'Añadir a la pantalla de inicio'</strong> (o 'App Install'). Aparecerá un ícono directo junto a tus otras aplicaciones para operar sin distracciones."
                                    data-steps="Abre el menú de tu navegador móvil y ubica la opción &lt;strong&gt;Añadir a inicio&lt;/strong&gt; o &lt;strong&gt;Instalar App&lt;/strong&gt;.">
                                    <i class="bi bi-phone me-1 text-dark"></i> ¿Cómo instalar la App en un celular?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Operar la App como Inspector"
                                    data-content="Los colaboradores con el rol de 'Inspector' visualizan una interfaz simplificada al entrar. El celular solo les mostrará la vista de 'Tomar Mediciones' y el 'Calendario de Tomas' para asegurar un enfoque exclusivo en el trabajo de campo. No pueden alterar sensores ni ver información contable."
                                    data-steps="El inspector invitado iniciará sesión y verá su menú delimitado automáticamente a &lt;strong&gt;Tomar Mediciones&lt;/strong&gt; y &lt;strong&gt;Calendario de Tomas&lt;/strong&gt;.">
                                    <i class="bi bi-phone me-1 text-dark"></i> Interfaz y operación de campo</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Módulo 9: Calendario de Tomas y Tolerancias -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4" style="border-left: 4px solid #0d6efd !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-calendar2-check-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Calendario de Tomas y Tolerancias</h5>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle mt-1" style="font-size: 0.68rem;">Nuevo</span>
                            </div>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="¿Cómo programar fechas de medición en el Calendario?"
                                    data-content="El <strong>Calendario de Tomas</strong> te permite agendar qué día se deben tomar las mediciones de un grupo o de sensores particulares. Puedes elegir entre tres modalidades:<br><br><strong>1. Día fijo de cada mes:</strong> Ideal si siempre mides el mismo día (por ejemplo, todos los <strong>días 15</strong> de cada mes).<br><strong>2. Fecha específica única:</strong> Si quieres agendar una inspección puntual en una fecha exacta del calendario.<br><strong>3. Intervalo continuo en días:</strong> Si prefieres medir cada cierta cantidad de días corridos (ej: cada 30 días desde la última lectura)."
                                    data-steps="Ve al menú lateral izquierdo y haz clic en &lt;strong&gt;Calendario de Tomas&lt;/strong&gt;, luego pulsa el botón &lt;strong&gt;Programar Toma / Tolerancias&lt;/strong&gt; (o haz clic sobre cualquier día del calendario).">
                                    <i class="bi bi-calendar-plus me-1 text-primary"></i> ¿Cómo programar tomas (ej: día 15)?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Días de Pre-aviso y Días de Tolerancia"
                                    data-content="Para que nunca se te pase una fecha de lectura, cada programación te permite definir dos márgenes en días:<br><br><strong>• Días de Pre-aviso (Ventana previa):</strong> Cantidad de días <em>antes</em> de la fecha programada en que el sistema abre la ventana de medición y comienza a avisarte (por ejemplo, 3 días antes del día 15, es decir, desde el día 12).<br><strong>• Días de Tolerancia (Plazo de gracia posterior):</strong> Cantidad de días <em>después</em> de la fecha programada en que aún estás a tiempo de registrar la lectura sin que se marque como vencida (por ejemplo, 3 días de tolerancia después del día 15, hasta el día 18 inclusive)."
                                    data-steps="Configúralos dentro de &lt;strong&gt;Calendario de Tomas > Programar Toma / Tolerancias&lt;/strong&gt; o al editar un &lt;strong&gt;Grupo de Sensores&lt;/strong&gt;.">
                                    <i class="bi bi-sliders me-1 text-primary"></i> ¿Qué son el Pre-aviso y la Tolerancia?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Programación por Grupo vs. Excepción por Sensor"
                                    data-content="No necesitas configurar mil medidores uno por uno:<br><br><strong>• Regla por Grupo (Herencia):</strong> Al programar un <strong>Grupo de Sensores</strong>, todos sus medidores heredan automáticamente la fecha de toma y las tolerancias.<br><br><strong>• Excepción por Sensor Particular:</strong> Si dentro de un grupo tienes uno o varios sensores especiales que deben medirse un día distinto o con otra tolerancia, puedes crearles una regla individual sin afectar al resto del grupo. En cualquier momento puedes quitar la excepción para que vuelvan a heredar la regla del grupo."
                                    data-steps="En el modal &lt;strong&gt;Programar Toma / Tolerancias&lt;/strong&gt; elige la pestaña &lt;strong&gt;Por Grupo Completo&lt;/strong&gt; o &lt;strong&gt;Por Sensor(es) Particular(es)&lt;/strong&gt;. También puedes hacerlo desde &lt;strong&gt;Sensores > Editar Sensor&lt;/strong&gt;.">
                                    <i class="bi bi-diagram-2 me-1 text-primary"></i> Programar por Grupo o por Sensor individual</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Semáforo de Tolerancias y Avisos Automáticos"
                                    data-content="El sistema evalúa automáticamente si cada sensor ya recibió su lectura dentro de la ventana actual y te muestra un semáforo de 5 colores:<br><br>🟢 <strong>Completado:</strong> Ya se tomó la medición de este ciclo.<br>🔵 <strong>Programado:</strong> Aún falta para que inicie la ventana de pre-aviso.<br>🟡 <strong>En Ventana / Hoy Toca:</strong> Ya estás en los días de pre-aviso o en el día exacto de toma.<br>🟠 <strong>En Tolerancia:</strong> Pasó la fecha meta, pero estás dentro de los días de tolerancia permitidos.<br>🔴 <strong>Fuera de Tolerancia (Vencido):</strong> Se superaron los días de tolerancia sin haber registrado la lectura.<br><br>Verás estos avisos en la <strong>Campana de la barra superior</strong>, en el panel del <strong>Dashboard</strong> y en el <strong>Calendario de Tomas</strong>."
                                    data-steps="Revisa el ícono de &lt;strong&gt;Calendario / Campana&lt;/strong&gt; arriba a la derecha desde cualquier pantalla o entra al &lt;strong&gt;Dashboard&lt;/strong&gt;.">
                                    <i class="bi bi-traffic-light me-1 text-primary"></i> Entender el Semáforo y las Alertas</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Iniciar Toma de Pendientes desde el Calendario"
                                    data-content="Cuando llega el día de medición (o estás en ventana/tolerancia), al hacer clic sobre el evento en el <strong>Calendario de Tomas</strong> verás qué sensores ya fueron medidos y cuáles siguen pendientes. Con un solo clic en el botón <strong>Iniciar Toma de Pendientes</strong>, el sistema abrirá directamente la ruta de carga masiva para ese grupo."
                                    data-steps="Entra a &lt;strong&gt;Calendario de Tomas&lt;/strong&gt;, haz clic sobre el grupo agendado en el día correspondiente y presiona &lt;strong&gt;Iniciar Toma de Pendientes&lt;/strong&gt;.">
                                    <i class="bi bi-lightning-charge me-1 text-primary"></i> Medir pendientes en 1 clic desde la agenda</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Módulo 10: Mapa Interactivo de Sensores (GIS) y Telemetría -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4" style="border-left: 4px solid #198754 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-geo-alt-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Mapa Interactivo de Sensores (GIS)</h5>
                                <span class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 0.68rem;">Premium · Nuevo</span>
                            </div>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="¿Cómo asignar o mover la coordenada de un sensor en el mapa?"
                                    data-content="El módulo <strong>Mapa de Sensores</strong> te permite georreferenciar cada medidor de forma visual y sin salir de la pantalla, ofreciendo tres métodos:<br><br><strong>1. Clic directo en el mapa (Ubicar / Mover):</strong> Pulsa el botón <strong>Ubicar</strong> (o <strong>Mover</strong> si ya tenía pin) en la tarjeta del sensor dentro de la lista lateral y haz clic sobre el punto exacto del mapa (puedes alternar entre vista <strong>Calles</strong> y <strong>Satélite</strong> o buscar direcciones por nombre).<br><strong>2. Arrastrar y Soltar (Drag & Drop):</strong> Si el sensor ya tiene un pin en el mapa, simplemente mantén presionado el marcador con el mouse y arrástralo a su nueva posición; se guarda automáticamente al soltarlo.<br><strong>3. Carga Manual o Pegado Rápido desde Google Maps:</strong> Haz clic en el botón <strong>Manual</strong> de la tarjeta y pega directamente el par de coordenadas copiado de Google Maps (ej: <code>-34.6037, -58.3816</code>) para que el sistema separe latitud y longitud por ti."
                                    data-steps="Ve al menú lateral izquierdo, entra en &lt;strong&gt;Mapa de Sensores&lt;/strong&gt;, selecciona un sensor de la lista derecha y pulsa &lt;strong&gt;Ubicar&lt;/strong&gt;, &lt;strong&gt;Mover&lt;/strong&gt; o &lt;strong&gt;Manual&lt;/strong&gt;.">
                                    <i class="bi bi-pin-map-fill me-1 text-success"></i> ¿Cómo ubicar o mover sensores en el mapa?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Modos de Coloración GIS: Estado de Tomas, Picos de Consumo y Servicio"
                                    data-content="En la barra superior del mapa encontrarás el selector <strong>Pintar pines por</strong>, que transforma el mapa en un tablero de control territorial:<br><br><strong>• Estado de Tomas:</strong> Pinta cada marcador según el semáforo de tu calendario: 🟢 <strong>Medido al día</strong>, 🟡 <strong>En ventana / Hoy toca</strong>, 🟠 <strong>En tolerancia</strong>, 🔴 <strong>Atrasado (Vencido)</strong> o 🔵 <strong>Programado</strong>.<br><br><strong>• Picos y Consumos:</strong> Analiza la tasa diaria de consumo de cada medidor respecto a su promedio histórico y pinta con un <strong>halo rojo pulsante</strong> los sensores con 🔥 <strong>Pico de consumo (+50% o más sobre su media)</strong>, en morado 🟣 los <strong>Estancados (consumo 0 por más de 15 días, posible medidor trabado)</strong> y en verde 🟢 los de consumo normal.<br><br><strong>• Tipo de Servicio:</strong> Colorea los sensores según su suministro (Agua, Electricidad, Gas o General)."
                                    data-steps="Dentro de &lt;strong&gt;Mapa de Sensores&lt;/strong&gt;, usa los botones superiores &lt;strong&gt;Estado de Tomas&lt;/strong&gt;, &lt;strong&gt;Picos y Consumos&lt;/strong&gt; o &lt;strong&gt;Tipo de Servicio&lt;/strong&gt;.">
                                    <i class="bi bi-palette-fill me-1 text-success"></i> Colorear mapa por Vencimientos o Picos de Consumo</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Tarjetas de Diagnóstico Rápido (Filtros GIS en 1 clic)"
                                    data-content="Debajo del encabezado del mapa tienes <strong>7 tarjetas de resumen en vivo</strong>: <em>Total Sensores, Medidos (Al día), Faltan Medir, Atrasados / Vencidos, Picos de Consumo, Estancados (0 cons.) y Sin Coordenada</em>.<br><br>Al hacer clic en cualquiera de estas tarjetas, el sistema filtra simultáneamente los pines del mapa y el listado lateral para mostrarte únicamente los medidores con esa condición (por ejemplo, ver de un pantallazo en qué calles o lotes están los medidores atrasados o los que registraron un pico inusual)."
                                    data-steps="Haz clic sobre cualquier tarjeta superior (ej: &lt;strong&gt;Atrasados / Vencidos&lt;/strong&gt; o &lt;strong&gt;Picos de Consumo&lt;/strong&gt;) en &lt;strong&gt;Mapa de Sensores&lt;/strong&gt; para aislar esos medidores.">
                                    <i class="bi bi-funnel-fill me-1 text-success"></i> Filtrar en 1 clic faltantes, atrasados o anomalías</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Telemetría Rápida y Acciones desde el Pin del Mapa"
                                    data-content="Al hacer clic sobre cualquier pin del mapa se despliega su ficha resumida con:<br><br><strong>• Estado operativo y de consumo:</strong> Última lectura, fecha, variación porcentual contra el promedio diario histórico y alerta si presenta pico o estancamiento.<br><strong>• Botón Telemetría (Gráfico en vivo):</strong> Abre una ventana emergente con la curva histórica de consumos y lecturas de las últimas tomas sin abandonar el mapa.<br><strong>• Botón Medir:</strong> Te lleva en un clic a registrar la lectura de ese sensor en particular.<br><strong>• Botón Ficha:</strong> Abre la página de detalle completo e historial del sensor."
                                    data-steps="Haz clic en cualquier marcador del &lt;strong&gt;Mapa de Sensores&lt;/strong&gt; y presiona &lt;strong&gt;Telemetría&lt;/strong&gt;, &lt;strong&gt;Medir&lt;/strong&gt; o &lt;strong&gt;Ficha&lt;/strong&gt;.">
                                    <i class="bi bi-graph-up-arrow me-1 text-success"></i> Ver curva de telemetría y medir desde el mapa</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Módulo 11: Marco Legal, Responsabilidad del Administrador e Indemnidad (SaaS Argentina) -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4" style="border-left: 4px solid #0284c7 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-shield-check fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Marco Legal & Responsabilidad</h5>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle mt-1" style="font-size: 0.68rem;">SaaS · Argentina</span>
                            </div>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="¿Cuál es la responsabilidad del Administrador sobre los datos y cobros a vecinos?"
                                    data-content="La entidad administradora (Barrio Privado / Club de Campo bajo CCCN art. 2073, Consorcio de Propiedad Horizontal art. 2037, Cooperativa Ley 20.337 o Municipio) es la <strong>única, exclusiva e indelegable responsable</strong> civil, administrativa y contable por los datos cargados en MedFlow, por las fotos tomadas por sus inspectores y por las liquidaciones de cobro, tasas o expensas emitidas a sus respectivos vecinos o socios.<br><br>MedFlow y sus desarrolladores quedan expresamente eximidos y mantenidos <strong>totalmente indemnes</strong> de cualquier reclamo judicial o controversia vecinal."
                                    data-steps="Consulta el &lt;a href='/terminos-legales' target='_blank' class='fw-bold text-primary'&gt;Marco Legal y Cláusulas de Indemnidad&lt;/a&gt;.">
                                    <i class="bi bi-building-check me-1 text-primary"></i> Responsabilidad de la Entidad y Reclamos Vecinales</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="¿MedFlow es una empresa de servicios públicos o certifica medidores?"
                                    data-content="<strong>No.</strong> MedFlow es exclusivamente una herramienta de software instrumental (SaaS) para digitalización fotográfica y cálculo aritmético entre lecturas sucesivas.<br><br>MedFlow <strong>NO es empresa distribuidora de servicios públicos ni realiza certificaciones de metrología legal (Ley 19.511)</strong> de medidores mecánicos o caudalímetros. El estado de calibración física y mantenimiento de las redes es responsabilidad de la entidad administradora."
                                    data-steps="Detallado en el Artículo 2 del &lt;a href='/terminos-legales' target='_blank' class='fw-bold text-primary'&gt;Marco Legal&lt;/a&gt;.">
                                    <i class="bi bi-rulers me-1 text-primary"></i> Deslinde de Metrología y Servicios Públicos</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Protección de Datos Personales (Ley 25.326): ¿Quién es el Responsable?"
                                    data-content="Bajo la Ley de Protección de Datos Personales N° 25.326 de la República Argentina:<br><br>• <strong>La Entidad Administradora</strong> es el <em>Responsable del Tratamiento y de la Base de Datos</em> (Art. 2), teniendo la relación legal directa con los vecinos.<br>• <strong>MedFlow</strong> actúa estrictamente como <em>Encargado del Tratamiento</em> (Art. 25), procesando datos en la nube por cuenta y orden técnica del Administrador.<br><br>MedFlow <strong>no vende, no alquila ni comercializa datos</strong> bajo ninguna modalidad."
                                    data-steps="Revisa el Artículo 5 del documento &lt;a href='/terminos-legales' target='_blank'&gt;Términos Legales&lt;/a&gt;.">
                                    <i class="bi bi-lock me-1 text-primary"></i> Régimen de Datos Personales (Ley 25.326)</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Deber de Respaldo Contable: ¿Cómo descargar mis libros de mediciones?"
                                    data-content="De acuerdo con los arts. 320 y concordantes del Código Civil y Comercial de la Nación, es deber legal de la administración llevar y conservar documentación respaldatoria.<br><br>MedFlow provee botones para <strong>Exportar a Excel</strong> las mediciones con todas sus columnas de auditoría y fechas. Recomendamos a toda administración descargar periódicamente estos archivos para sus balances y asambleas anuales."
                                    data-steps="Desde &lt;strong&gt;Mediciones y Consumos&lt;/strong&gt; haz clic en el botón superior &lt;strong&gt;Exportar a Excel&lt;/strong&gt;.">
                                    <i class="bi bi-file-earmark-spreadsheet me-1 text-primary"></i> Resguardos Contables y Exportación Excel</a>
                            </li>
                            <li class="mb-2">
                                <a href="{{ route('legal.terms') }}" target="_blank" class="text-primary fw-semibold text-decoration-none">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver Marco Legal Completo y Artículos &raquo;
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <!-- Contenedor Visual de Contenido (Modal Genérico) -->
        <div class="modal fade" id="helpModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom bg-light">
                        <h5 class="modal-title fw-bold" id="helpModalTitle">Título de Ayuda</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-dark" style="font-size: 1.05rem; line-height: 1.6;">
                        <div id="helpModalContent">El contenido cargará aquí...</div>

                        <div id="helpModalStepsContainer" class="mt-4 pt-3 border-top text-muted small"
                            style="display: none;">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-signpost-split text-primary me-1"></i> Dónde
                                encontrarlo:</h6>
                            <div id="helpModalSteps" class="p-3 bg-light rounded-3 d-inline-block w-100"
                                style="border: 1px dashed #dee2e6;"></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Entendido</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Cargar Modal Dinámico
            const helpItems = document.querySelectorAll('.help-item');
            const modalTitle = document.getElementById('helpModalTitle');
            const modalContent = document.getElementById('helpModalContent');
            const modalSteps = document.getElementById('helpModalSteps');
            const modalStepsContainer = document.getElementById('helpModalStepsContainer');

            helpItems.forEach(item => {
                item.addEventListener('click', function () {
                    modalTitle.textContent = this.getAttribute('data-title');
                    modalContent.innerHTML = this.getAttribute('data-content');

                    const steps = this.getAttribute('data-steps');
                    if (steps) {
                        modalSteps.innerHTML = steps;
                        modalStepsContainer.style.display = 'block';
                    } else {
                        modalStepsContainer.style.display = 'none';
                    }
                });
            });

            // Buscador Dinámico
            const searchInput = document.getElementById('helpSearch');
            const helpCards = document.querySelectorAll('.help-card');

            searchInput.addEventListener('input', function () {
                // Removemos acentos para la búsqueda (Ej: importacion -> importación)
                const normalize = str => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
                const query = normalize(this.value.trim());

                helpCards.forEach(card => {
                    const cardText = normalize(card.textContent);
                    const headerText = normalize(card.querySelector('h5') ? card.querySelector('h5')
                        .textContent : '');

                    // Mostramos u ocultamos las tarjetas según match
                    if (cardText.includes(query)) {
                        card.style.display = 'block';

                        // Si el título del módulo coincide con la búsqueda, mostramos todos los items.
                        // Si no, filtramos individualmente los items para dejar solo los que coinciden.
                        const headerMatch = headerText.includes(query);
                        const lis = card.querySelectorAll('li');

                        let hasVisibleLi = false;
                        lis.forEach(li => {
                            const liText = normalize(li.textContent);
                            if (headerMatch || query === '' || liText.includes(query)) {
                                li.style.display = 'block';
                                hasVisibleLi = true;
                            } else {
                                li.style.display = 'none';
                            }
                        });

                        // Si el header NO hizo match, y resulta que todos los LI se ocultaron 
                        // (porque el match fue de alguna palabra oculta que no esta en los lis),
                        // entonces ocultamos la tarjeta para no mostrar una vacía.
                        if (!headerMatch && !hasVisibleLi && query !== '') {
                            card.style.display = 'none';
                        }

                    } else {
                        card.style.display = 'none';
                    }
                });
            });

        });
    </script>

    <style>
        .help-item:hover {
            color: #0d6efd !important;
            text-decoration: underline !important;
        }
    </style>
@endsection
