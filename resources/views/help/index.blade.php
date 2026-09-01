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
                        placeholder="Buscar por palabra clave (ej: Plantillas, Colaboradores)...">
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
                                    data-content="El tablero principal te permite visualizar el estado global de tu organización. Desde el menú lateral izquierdo puedes acceder a todas tus propiedades, crear plantillas métricas, invitar colaboradores y extraer un resumen estadístico de las temperaturas registradas."
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
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Consumos y Resúmenes"
                                    data-content="Mediante el apartado 'Consumos' podrás visualizar líneas y gráficos radiales que reflejan la temperatura de tus máquinas o espacios durante los últimos meses. Además, podrás exportar todo en un enlace público o PDF vía Email."
                                    data-steps="Dirígete a &lt;strong&gt;Dashboard&lt;/strong&gt; o a la vista de detalle de cualquier &lt;strong&gt;Sensor&lt;/strong&gt; particular para observar sus gráficas.">
                                    <i class="bi bi-hdd-network me-1 text-success"></i> Entender los gráficos de Consumo</a>
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
                                    <i class="bi bi-calculator me-1 text-info"></i> ¿Cómo calcula el consumo al cambiarlo?</a>
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
                                    data-content="<strong>1. Crear Plantilla:</strong> Decide qué vas a medir (ej: Agua, Luz).<br><br><strong>2. Crear Grupo de Sensores:</strong> Agrupa todos los lotes de tu barrio o planta bajo la plantilla creada.<br><br><strong>3. Dar de Alta Sensores:</strong> Agrega cada Lote (con su nombre y correo en la metadata).<br><br><strong>4. Medir y Repetir:</strong> Los inspectores enviarán lecturas mensuales desde la calle vía foto o texto con su celular.<br><br><strong>5. Campaña Pública:</strong> Terminado el mes, emites una campaña que envía Links por E-mail automáticamente a cada propietario con las estadísticas monetizadas."
                                    data-steps="Sigue el flujo de botones principales: &lt;br&gt;&lt;strong&gt;1. Plantillas > 2. Grupos > 3. Sensores > 4. Mediciones.&lt;/strong&gt;">
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
                                    data-content="La suscripción Premium desbloquea el potencial total del sistema: Importación masiva de sensores, gestión y creación de plantillas métricas, incorporación de colaboradores e inspectores, exportación avanzada de datos y panel de analíticas exclusivas."
                                    data-steps="Visualizarás menús desbloqueados (ej. &lt;strong&gt;Analíticas, Importaciones&lt;/strong&gt;) de forma automática según la membresía.">
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

            <!-- Módulo 7: App Móvil e Inspectores -->
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
                                    data-content="Los colaboradores con el rol de 'Inspector' visualizan una interfaz simplificada al entrar. El celular solo les mostrará la vista de 'Tomar Mediciones' para asegurar un enfoque exclusivo en el trabajo de campo. No pueden alterar sensores ni ver información contable."
                                    data-steps="El inspector invitado iniciará sesión y verá su menú delimitado automáticamente a &lt;strong&gt;Tomar Mediciones&lt;/strong&gt;.">
                                    <i class="bi bi-phone me-1 text-dark"></i> Interfaz y operación de campo</a>
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
                const query = this.value.toLowerCase().trim();
                let countVisible = 0;

                helpCards.forEach(card => {
                    const text = card.textContent.toLowerCase();

                    // Mostramos u ocultamos las tarjetas según match
                    if (text.includes(query)) {
                        card.style.display = 'block';
                        countVisible++;

                        // Además, resaltamos levemente los items individuales internos si los usamos.
                        const lis = card.querySelectorAll('li');
                        lis.forEach(li => {
                            li.style.display = li.textContent.toLowerCase().includes(query) || query === '' ? 'block' : 'none';
                        });
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