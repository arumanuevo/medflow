<?php
$fileProfile = 'k:\desarrollo\medflow\resources\views\profile\index.blade.php';
$contentProfile = file_get_contents($fileProfile);

// Añadir clarificación de Próxima Factura
$searchHeader = '<h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">Resumen Financiero</h6>';
$replaceHeader = '<h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Resumen Financiero</h6>
                        <p class="text-muted mb-3" style="font-size: 0.8rem;"><i class="bi bi-info-circle me-1"></i> Costo proyectado para su próxima factura a vencer.</p>';

$contentProfile = str_replace($searchHeader, $replaceHeader, $contentProfile);

file_put_contents($fileProfile, $contentProfile);
echo "Added financial projection clarification.\n";
