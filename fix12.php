<?php
$c = file_get_contents('resources/views/consumptions/index.blade.php');

// Regex inject button after canvas
$c = preg_replace('/<canvas id="evolutionChart"><\/canvas>\s*<\/div>\s*<\/div>/', '<canvas id="evolutionChart"></canvas>
                            </div>
                        </div>
                        <div class="text-center mt-3 mb-2" id="shareBtnContainer">
                            <button type="button" class="btn btn-success px-4" id="btnShareAnalysis">
                                <i class="bi bi-envelope-check"></i> Enviar Resumen Avanzado al Usuario
                            </button>
                        </div>', $c);

// Fix the DIV balance.
// In the current output wait, I noticed:
// </div>
// </div>
// </div>
// </div>
// <!-- Modal para Radar Global
// Wait! It has FOUR closing divs before Radar modal?
// Let's strip the extra div I added in fix11.php since the problem was the `row` balance.
// To perfectly balance `analyzeSensorModal`:
/*
    <div class="modal fade" id="analyzeSensorModal" tabindex="-1" aria-labelledby="analyzeSensorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered compact-modal">
            <div class="modal-content">
                ... 
            </div>
        </div>
    </div>
*/
$target = '<div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
        </div>
    </div>';
$replacement = '<div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>';

// Is there a missing row or div? `modal-dialog` should only require 3 divs to close.
$c = preg_replace('/<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<!-- Modal para Radar Global/s', "</div>\n    </div>\n</div>\n\n<!-- Modal para Radar Global", $c);

// Are there missing divs around the filter container?
// I had:
// <div class="container mt-4">
//   <div class="row">
//     <div class="col-md-12">
//       <div class="card shadow-sm border-0">
//         <div class="card-body">
// So that's 5 divs.
$c = preg_replace('/<\/div>\s*<\/div>\s*<\/div>\s*<!-- Modal para detalles del/s', "</div>\n</div>\n</div>\n</div>\n</div>\n\n<!-- Modal para detalles del", $c);


file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo "Final attempt\n";
