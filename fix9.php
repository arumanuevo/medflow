<?php
$c = file_get_contents('resources/views/consumptions/index.blade.php');

$targetFooter = '<div class="modal-footer p-2">
                <button type="button" class="btn btn-outline-success btn-sm me-auto" id="btnShareAnalysis"><i class="bi bi-share"></i> Compartir Informe</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>';
$replacementFooter = '<div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>';
$c = str_replace($targetFooter, $replacementFooter, $c);

// Add the button right after the canvas chart container
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

file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo "Button moved to body\n";
