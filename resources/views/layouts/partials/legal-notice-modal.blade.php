{{-- Modal y Widget de Aviso Legal y Delimitación de Responsabilidad (SaaS Argentina) --}}
<div class="modal fade" id="medflowLegalNoticeModal" tabindex="-1" aria-labelledby="medflowLegalNoticeLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header border-0 text-white p-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-bottom: 3px solid #0284c7 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(2, 132, 199, 0.25); border: 1px solid rgba(56, 189, 248, 0.5);">
                        <i class="bi bi-shield-check text-info fs-4"></i>
                    </div>
                    <div>
                        <span class="badge bg-primary-subtle text-info border border-info border-opacity-25 rounded-pill px-2 py-1 mb-1" style="font-size: 0.72rem;">
                            República Argentina &bull; Régimen SaaS
                        </span>
                        <h5 class="modal-title fw-bold text-white mb-0" id="medflowLegalNoticeLabel">
                            Aviso Legal, Alcance Técnico y Régimen de Responsabilidad
                        </h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="minimizeLegalNotice()" aria-label="Minimizar" title="Minimizar aviso"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4" style="background-color: #f8fafc; font-size: 0.88rem; color: #334155; max-height: 65vh; overflow-y: auto;">
                
                <div class="p-3 bg-white rounded-3 border mb-3 shadow-sm">
                    <p class="mb-0 text-dark fw-semibold" style="line-height: 1.5;">
                        Estimado Administrador: Para garantizar la máxima transparencia y blindaje operativo en el uso de <strong>MedFlow</strong> (aplicable a Barrios Privados, Clubes de Campo, Consorcios, Cooperativas y Municipios), le recordamos el marco legal regulatorio que rige el uso de esta plataforma:
                    </p>
                </div>

                <div class="row g-3">
                    <!-- Pilar 1 -->
                    <div class="col-md-6">
                        <div class="p-3 bg-white rounded-3 border h-100 shadow-sm border-start border-primary border-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-cpu text-primary fs-5"></i>
                                <span class="fw-bold text-dark">1. Naturaleza Instrumental</span>
                            </div>
                            <p class="small text-muted mb-0">
                                MedFlow es una herramienta puramente informática de digitalización fotográfica y cálculo aritmético entre lecturas. <strong>No es prestadora de servicios públicos ni realiza certificaciones de metrología legal física (Ley 19.511)</strong> sobre caudalímetros o medidores.
                            </p>
                        </div>
                    </div>

                    <!-- Pilar 2 -->
                    <div class="col-md-6">
                        <div class="p-3 bg-white rounded-3 border h-100 shadow-sm border-start border-warning border-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-building-check text-warning-emphasis fs-5"></i>
                                <span class="fw-bold text-dark">2. Responsabilidad de la Entidad</span>
                            </div>
                            <p class="small text-muted mb-0">
                                La entidad administradora es la <strong>única y exclusiva responsable</strong> de la veracidad y calidad de las lecturas ingresadas, de auditar anomalías y de las liquidaciones de cobro o expensas emitidas a sus vecinos, socios o contribuyentes.
                            </p>
                        </div>
                    </div>

                    <!-- Pilar 3 -->
                    <div class="col-md-6">
                        <div class="p-3 bg-white rounded-3 border h-100 shadow-sm border-start border-danger border-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-shield-x text-danger fs-5"></i>
                                <span class="fw-bold text-dark">3. Indemnidad ante Reclamos</span>
                            </div>
                            <p class="small text-muted mb-0">
                                La entidad se compromete a <strong>mantener plenamente indemne a MedFlow</strong> y a sus autores ante cualquier litigio judicial, reclamo de consorcistas/vecinos o denuncias ante Defensa del Consumidor (Ley 24.240) o entes reguladores derivados de facturaciones o mediciones.
                            </p>
                        </div>
                    </div>

                    <!-- Pilar 4 -->
                    <div class="col-md-6">
                        <div class="p-3 bg-white rounded-3 border h-100 shadow-sm border-start border-success border-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-lock text-success fs-5"></i>
                                <span class="fw-bold text-dark">4. Datos Personales (Ley 25.326)</span>
                            </div>
                            <p class="small text-muted mb-0">
                                La entidad reviste el rol de <strong>Responsable de la Base de Datos</strong> y MedFlow el de mero Encargado de Tratamiento. MedFlow <strong>no vende, no cede ni comercializa</strong> información ni fotografías con terceros bajo ninguna circunstancia.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contralor, Adulteración y Error Humano -->
                <div class="mt-3 p-3 bg-white rounded-3 border shadow-sm border-start border-info border-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-eye text-info fs-5"></i>
                        <span class="fw-bold text-dark">5. Órgano de Contralor Neutral, Prohibición de Trucaje y Error Humano</span>
                    </div>
                    <p class="small text-muted mb-0">
                        MedFlow opera como un <strong>instrumento objetivo de ordenamiento y fiscalización bilateral</strong> (permite al Administrador auditar a sus inspectores y al Vecino contrastar la foto real de su medidor vía Visor Público QR). <strong>Queda terminantemente prohibido falsear o trucar fotos, fechas o mediciones</strong> (Arts. 172 y 292 Cód. Penal); cualquier manipulación deliberada o error humano de tipeo es exclusiva responsabilidad civil y penal del operador que la ejecute.
                    </p>
                </div>

                <div class="mt-3 p-2 px-3 bg-light rounded border text-muted small text-center" style="font-size: 0.76rem;">
                    <i class="bi bi-info-circle me-1"></i> Obligación de resguardo contable: Se recomienda al Administrador descargar y archivar periódicamente los reportes en Excel de las mediciones para sus libros y balances (Arts. 320 CCCN).
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-0 p-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <a href="{{ route('legal.terms') }}" target="_blank" class="btn btn-link text-decoration-none text-primary p-0 small fw-semibold">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Ver Marco Legal Completo y Artículos
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" onclick="minimizeLegalNotice()">
                        <i class="bi bi-dash-circle me-1"></i> Minimizar aviso
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-4 rounded-pill fw-semibold" onclick="acceptLegalNotice()">
                        <i class="bi bi-check-circle me-1"></i> Comprendido y Aceptar Alcance
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pastilla Flotante Minimizada (Visible de forma no intrusiva) -->
<div id="medflowLegalFloatBadge" class="position-fixed" style="bottom: 18px; left: 18px; z-index: 1045;">
    <button type="button" onclick="openLegalNoticeModal()" class="btn btn-sm btn-white bg-white text-secondary shadow-sm rounded-pill border d-inline-flex align-items-center gap-2 px-3 py-1" style="font-size: 0.76rem; border-color: #cbd5e1 !important; transition: all 0.2s ease;">
        <i class="bi bi-shield-check text-primary"></i>
        <span class="d-none d-md-inline fw-semibold text-dark">Aviso Legal & Responsabilidad</span>
        <span class="badge bg-light text-muted border px-1" style="font-size: 0.65rem;">SaaS</span>
    </button>
</div>

<script>
    (function() {
        const STORAGE_KEY = 'medflow_legal_accepted_v1';
        const SESSION_MINIMIZED_KEY = 'medflow_legal_minimized_session';

        function checkLegalNotice() {
            try {
                const acceptedData = localStorage.getItem(STORAGE_KEY);
                const wasMinimizedThisSession = sessionStorage.getItem(SESSION_MINIMIZED_KEY);

                // Si nunca lo aceptó y no lo minimizó en esta sesión, mostramos el modal automáticamente tras un breve delay
                if (!acceptedData && !wasMinimizedThisSession) {
                    setTimeout(() => {
                        const modalEl = document.getElementById('medflowLegalNoticeModal');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        }
                    }, 1200);
                }
            } catch (e) {
                console.warn('Legal notice check skipped:', e);
            }
        }

        window.openLegalNoticeModal = function() {
            const modalEl = document.getElementById('medflowLegalNoticeModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        };

        window.minimizeLegalNotice = function() {
            const modalEl = document.getElementById('medflowLegalNoticeModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
            try {
                sessionStorage.setItem(SESSION_MINIMIZED_KEY, '1');
            } catch (e) {}
        };

        window.acceptLegalNotice = function() {
            try {
                const payload = {
                    accepted: true,
                    timestamp: new Date().toISOString(),
                    version: '1.0-AR'
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
            } catch (e) {}

            const modalEl = document.getElementById('medflowLegalNoticeModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        };

        document.addEventListener('DOMContentLoaded', checkLegalNotice);
    })();
</script>
