<?php
$file = 'k:\desarrollo\medflow\resources\views\measurements\create.blade.php';
$content = file_get_contents($file);

$search = <<<EOD
                    <!-- Fecha de medición -->
EOD;

$replace = <<<EOD
                    <!-- Opciones de Reseteo (Cambio de Medidor) -->
                    <div class="mb-4 bg-light p-3 rounded border">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="is_reset" name="data[is_reset]" value="true" onchange="toggleResetOptions()">
                            <label class="form-check-label fw-bold text-danger" for="is_reset">
                                <i class="bi bi-arrow-repeat me-1"></i> ¿Se reemplazó este medidor físico por uno nuevo?
                            </label>
                        </div>
                        
                        <div id="resetOptionsContainer" class="d-none mt-3 p-3 bg-white rounded border border-danger-subtle">
                            <p class="small text-muted mb-3"><i class="bi bi-info-circle text-primary"></i> Detectamos un cambio de medidor. Elige cómo quieres registrar este corte periódico para no afectar la facturación y consumos finales.</p>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="data[reset_type]" id="reset_simple" value="simple" checked onchange="toggleExactClosure(false)">
                                <label class="form-check-label fw-bold" for="reset_simple">
                                    Opción 1: Reseteo Simple (Corte de mes)
                                </label>
                                <div class="small text-muted">Ignora matemáticamente el consumo de este periodo transitorio. Las lecturas futuras a partir de hoy comenzarán basándose en la nueva lectura de este medidor. Recomendado si cobras importes fijos.</div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="data[reset_type]" id="reset_exact" value="exact" onchange="toggleExactClosure(true)">
                                <label class="form-check-label fw-bold" for="reset_exact">
                                    Opción 2: Cierre Exacto (Combinación matemática)
                                </label>
                                <div class="small text-muted">Combina el consumo no facturado del medidor viejo (antes de retirarlo) y el consumo inicial gastado en el medidor nuevo para generar un consumo total de período exacto, sin perder dinero.</div>
                            </div>

                            <!-- Campos para Cierre Exacto -->
                            <div id="exactClosureFields" class="d-none p-3 bg-light rounded mt-2 border">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">Lectura final (Medidor retirado)</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm" id="old_meter_final" name="data[old_meter_final]" placeholder="Ej: 8550">
                                        <small class="text-muted" style="font-size: 0.75rem;">Último número visto en el aparato viejo antes de desconectarlo.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">Lectura inicial (Medidor nuevo)</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm" id="new_meter_start" name="data[new_meter_start]" placeholder="Ej: 0">
                                        <small class="text-muted" style="font-size: 0.75rem;">El número inicial en el que arrancó el aparato nuevo al instalarlo.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        function toggleResetOptions() {
                            const isChecked = document.getElementById('is_reset').checked;
                            const container = document.getElementById('resetOptionsContainer');
                            if (isChecked) {
                                container.classList.remove('d-none');
                                // Evitar las validaciones JS visuales para que no bloquee con "X Válido"
                                const statusBadge = document.getElementById('consumptionStatus');
                                if(statusBadge) {
                                    statusBadge.textContent = '🔄 Medidor Cambiado';
                                    statusBadge.className = 'badge bg-warning text-dark';
                                }
                            } else {
                                container.classList.add('d-none');
                            }
                        }
                        
                        function toggleExactClosure(show) {
                            const fields = document.getElementById('exactClosureFields');
                            if (show) {
                                fields.classList.remove('d-none');
                                document.getElementById('old_meter_final').setAttribute('required', 'true');
                            } else {
                                fields.classList.add('d-none');
                                document.getElementById('old_meter_final').removeAttribute('required');
                            }
                        }
                    </script>

                    <!-- Fecha de medición -->
EOD;

if (strpos($content, "Opciones de Reseteo") === false) {
    file_put_contents($file, str_replace($search, $replace, $content));
    echo "Done view patch";
} else {
    echo "View already patched";
}
