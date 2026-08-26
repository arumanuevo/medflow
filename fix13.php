<?php
$c = file_get_contents('resources/views/consumptions/index.blade.php');

// We are going to replace everything between `<!-- Modal para Análisis Avanzado de Rango -->` and `<!-- Modal para Radar Global de Anomalías -->`

$start = strpos($c, '<!-- Modal para Análisis Avanzado de Rango -->');
$end = strpos($c, '<!-- Modal para Radar Global de Anomalías -->');
if ($start === false || $end === false) {
    die("Could not find delimiters\n");
}

$before = substr($c, 0, $start);
$after = substr($c, $end);

$modal = <<<HTML
<!-- Modal para Análisis Avanzado de Rango -->
    <div class="modal fade" id="analyzeSensorModal" tabindex="-1" aria-labelledby="analyzeSensorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered compact-modal">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="analyzeSensorModalLabel"><i class="bi bi-bar-chart-fill"></i> Análisis Avanzado: <span id="analyzeSensorName"></span></h5>
                    <div class="d-flex align-items-center ms-auto">
                        <button type="button" class="btn btn-sm btn-outline-light me-1" id="btnPrevAnalyzeSensor" title="Sensor Anterior">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light me-3" id="btnNextAnalyzeSensor" title="Sensor Siguiente">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <!-- Loading Fechas Base -->
                    <div id="metaLoadingStatus" class="text-center text-muted small mb-2 d-none">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Sincronizando fechas disponibles...
                    </div>

                    <div id="metaAvailableDates" class="alert alert-info py-1 px-3 mb-3 d-none">
                        <strong>Fechas disponibles:</strong> entre <span id="metaFirstDate"></span> y <span id="metaLastDate"></span>
                    </div>

                    <!-- Configuración del rango -->
                    <div class="row align-items-end mb-3 bg-light p-2 rounded mx-0 border">
                        <div class="col-md-3">
                            <label for="analyzeStartDate" class="form-label mb-1">Inicio</label>
                            <input type="date" class="form-control form-control-sm" id="analyzeStartDate">
                        </div>
                        <div class="col-md-3">
                            <label for="analyzeEndDate" class="form-label mb-1">Corte</label>
                            <input type="date" class="form-control form-control-sm" id="analyzeEndDate">
                        </div>
                        <div class="col-md-2">
                            <label for="analyzeThreshold" class="form-label mb-1 text-danger small" title="Salto en la tasa diaria"><i class="bi bi-exclamation-triangle"></i> Anomalía</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-danger fw-bold" id="analyzeThreshold" value="50" min="1" step="5">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="analyzeStagnation" class="form-label mb-1 text-secondary small" title="Alerta si delta es 0 luego de X días"><i class="bi bi-hourglass-top"></i> Estancamiento</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-secondary fw-bold" id="analyzeStagnation" value="15" min="1" step="1">
                                <span class="input-group-text">Días</span>
                            </div>
                        </div>
                        <div class="col-md-1 d-grid px-1">
                            <button class="btn btn-primary btn-sm" id="btnCalculateRange" title="Calcular Rango">
                                <i class="bi bi-play-fill"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Explicación Sensibilidad Inteligente -->
                    <div class="alert alert-secondary py-2 px-3 mb-3 pointer-events-none">
                        <strong class="text-danger small"><i class="bi bi-shield-exclamation"></i> ¿Qué es la sensibilidad inteligente?</strong><br>
                        <small class="text-muted" style="font-size: 0.8rem;">
                            El sistema analiza matemáticamente la <b>tasa diaria de consumo</b> en lugar del salto
                            bruto. Si detecta que la aceleración de consumo entre mediciones supera el <strong class="text-danger"><span id="infoThreshold">50</span>%</strong>, o si detecta estancamiento por
                            más de <strong class="text-secondary"><span id="infoStagnation">15</span> días</strong>, dibujará
                            ese punto con una alerta ⚠️ roja. Esto previene distorsiones causadas por tiempos de inspección irregulares.
                        </small>
                    </div>

                    <input type="hidden" id="analyzeSensorId" value="">

                    <!-- Resultados del Dashboard Analítico -->
                    <div id="analyzeResults" class="d-none">
                        <div class="row g-2 mb-3">
                            <div class="col-md-12 mb-1">
                                <div class="card border-dark shadow-sm" style="background-color: #f8f9fa;">
                                    <div class="card-body text-center py-2">
                                        <h6 class="text-muted mb-0"><i class="bi bi-cash-coin"></i> Facturación Total Estimada</h6>
                                        <h3 class="text-dark my-1 fw-bold" id="resFinalBilledTotal">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 border-info shadow-sm">
                                    <div class="card-body text-center p-2">
                                        <h6 class="text-muted mb-1" style="font-size: 0.8rem;"><i class="bi bi-speedometer2"></i> Consumo Lote</h6>
                                        <h4 class="text-info my-1 fw-bold" id="resTotalDelta">0</h4>
                                        <p class="mb-0 text-muted" style="font-size: 0.65rem;"><span id="resMeasurementsCount" class="fw-bold"></span> lect.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 border-success shadow-sm" style="background-color: #f0fdf4;">
                                    <div class="card-body text-center p-2">
                                        <h6 class="text-success mb-1" style="font-size: 0.8rem;"><i class="bi bi-tree-fill"></i> Cargos Comunes</h6>
                                        <h4 class="text-success my-1 fw-bold" id="resCommunityContribution">0</h4>
                                        <p class="mb-0 text-muted" style="font-size: 0.65rem;">+ Prorrateado</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 border-secondary shadow-sm">
                                    <div class="card-body text-center p-2">
                                        <h6 class="text-muted mb-1" style="font-size: 0.8rem;"><i class="bi bi-calendar3"></i> Prom. Diario</h6>
                                        <h4 class="text-secondary my-1 fw-bold" id="resDailyAvg">0</h4>
                                        <p class="mb-0 text-muted" style="font-size: 0.65rem;"><span id="resDaysBetween"></span> días</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico Chart.js -->
                        <div class="card shadow-sm border-0 mb-2">
                            <div class="card-body p-2" style="position: relative; height: 230px; width: 100%;">
                                <canvas id="evolutionChart"></canvas>
                            </div>
                        </div>

                        <div class="text-center mt-3 mb-2" id="shareBtnContainer">
                            <button type="button" class="btn btn-success px-4" id="btnShareAnalysis">
                                <i class="bi bi-envelope-check"></i> Enviar Resumen Avanzado al Usuario
                            </button>
                        </div>
                    </div>

                    <!-- Panel de Inspección de Anomalías (Solo visible si hay anomalías en el rango) -->
                    <div id="anomaliesInspectionContainer" class="d-none mt-4 mb-3 border rounded p-3 bg-light">
                        <h6 class="text-danger fw-bold border-bottom pb-2 mb-3">
                            <i class="bi bi-camera-fill"></i> Auditoría Visual de Anomalías
                        </h6>
                        <p class="small text-muted mb-3">
                            Se requiere verificación manual visual sobre los siguientes puntos atípicos generados. Compara si el registro se corresponde con la foto de prueba adjunta:
                        </p>
                        <div class="row g-3" id="anomaliesInspectionGrid">
                            <!-- Cards dinámicas inyectadas por JS -->
                        </div>
                    </div>

                    <div class="alert alert-secondary mb-0 pointer-events-none">
                        <strong><i class="bi bi-info-circle"></i> Resumen del período:</strong>
                        El cálculo abarca desde la lectura inicial <strong id="resStartLog"></strong> (<span id="resStartVal"></span>)
                        hasta el corte de <strong id="resEndLog"></strong> (<span id="resEndVal"></span>).
                    </div>
                </div>

                <!-- Loading / Estacionario state -->
                <div id="analyzeLoading" class="text-center d-none py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted mb-0">Calculando analíticas y dibujando trazados...</p>
                </div>
                
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    
HTML;

$c = $before . $modal . $after;
file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo "Perfectly balanced modal injected\n";
