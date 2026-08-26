<?php
$c = file_get_contents('resources/views/consumptions/index.blade.php');

$targetChart = '<canvas id="evolutionChart"></canvas>
                            </div>
                        </div>';
$replacementChart = '<canvas id="evolutionChart"></canvas>
                            </div>
                        </div>
                        <div class="text-center mt-3 mb-2" id="shareBtnContainer">
                            <button type="button" class="btn btn-success px-4" id="btnShareAnalysis">
                                <i class="bi bi-envelope-check"></i> Enviar Resumen Avanzado al Usuario
                            </button>
                        </div>';
$c = str_replace($targetChart, $replacementChart, $c);

// Also fix extra div missing?
// If the button Cerrar was outside the right limits, it means the modal content wasn't properly closed, or closed too early.
// In pristine 80b8524:
/*
                <div id="analyzeLoading" class="text-center d-none py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted mb-0">Calculando analíticas y dibujando trazados...</p>
                </div>
            </div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
*/
file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo "Injected button\n";
