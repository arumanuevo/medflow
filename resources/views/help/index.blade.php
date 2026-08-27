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
                                    data-content="El tablero principal te permite visualizar el estado global de tu organización. Desde el menú lateral izquierdo puedes acceder a todas tus propiedades, crear plantillas métricas, invitar colaboradores y extraer un resumen estadístico de las temperaturas registradas.">
                                    <i class="bi bi-play-circle me-1 text-primary"></i> ¿Cómo navego el dashboard?</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Configuración inicial"
                                    data-content="Recomendamos empezar creando una 'Plantilla'. Una Plantilla dicta los campos estáticos o límites de termostato que van a compartir muchos Sensores a la vez. Luego de crear tu plantilla, puedes crear tus 'Grupos de Sensores'.">
                                    <i class="bi bi-play-circle me-1 text-primary"></i> Configuración inicial
                                    recomendada</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Mis Límites y Suscripción"
                                    data-content="Los planes Freemium tienen límites de cantidad de sensores. Ve a 'Planes y Facturación' para revisar los cupos de uso que tienes activos. Todo se sincroniza en tiempo real.">
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
                                    data-content="Para crear sensores, dirígete al menú de Sensores. Cada dispositivo requiere obligatoriamente pertenecer a un Grupo, ya que heredará las configuraciones base de dicho bloque.">
                                    <i class="bi bi-hdd-network me-1 text-success"></i> ¿Cómo cargo un nuevo sensor?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Carga Masiva"
                                    data-content="Si decides tomar las mediciones de docenas de sensores a la vez en lugar de uno por uno, la ruta 'Mediciones Masivas' te permite generar una lista tipo Check-List Excel. Marcando rápidamente los valores y adjuntando las fotos al unísono.">
                                    <i class="bi bi-hdd-network me-1 text-success"></i> La medición masiva en ruta</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal" data-title="Consumos y Resúmenes"
                                    data-content="Mediante el apartado 'Consumos' podrás visualizar líneas y gráficos radiales que reflejan la temperatura de tus máquinas o espacios durante los últimos meses. Además, podrás exportar todo en un enlace público o PDF vía Email.">
                                    <i class="bi bi-hdd-network me-1 text-success"></i> Entender los gráficos de Consumo</a>
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
                                    data-content="Desde la pestaña de colaboradores, puedes invitar inspectores de tu empresa. Llegará un correo formal otorgándoles acceso al Workspace activo. Recuerda que no todos pueden modificar estructuras; los operarios (inspectores) sólo cargarán valores.">
                                    <i class="bi bi-person-fill-add me-1 text-info"></i> Invitar a Operarios
                                    (Inspectores)</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="¿Qué es el aislamiento espacial?"
                                    data-content="El Workspace (Espacio de Trabajo) aísla visualmente toda la información. Cuando un usuario acepta tu invitación, al loguearse verá un switch arriba a la derecha para pasar de su Workspace Personal al Workspace de tu Empresa.">
                                    <i class="bi bi-person-fill-add me-1 text-info"></i> Compartir accesos con Clientes</a>
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
                        <div class="mt-4 pt-3 border-top text-center text-muted small">
                            <i class="bi bi-image" style="font-size: 1.5rem; display:block; margin-bottom: 5px;"></i>
                            [Zona para insertar imagen o video tutorial ilustrativo en el futuro]
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

            helpItems.forEach(item => {
                item.addEventListener('click', function () {
                    modalTitle.textContent = this.getAttribute('data-title');
                    modalContent.innerHTML = this.getAttribute('data-content');
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