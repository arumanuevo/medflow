<?php
$c = file_get_contents('resources/views/consumptions/index.blade.php');

// 1. Add shareReportModal before <script src="https://cdn.jsdelivr.net/npm/chart.js">
$modal = <<<HTML
    <!-- Modal para Enviar Informe -->
    <div class="modal fade" id="shareReportModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title m-0"><i class="bi bi-envelope"></i> Enviar Informe</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Selecciona un campo del grupo o ingresa el correo manualmente:</p>
                    <div class="mb-3">
                        <select id="shareEmailSelect" class="form-select form-select-sm mb-2 d-none">
                            <option value="">-- Ingresar Manualmente --</option>
                        </select>
                        <input type="email" id="shareEmailInput" class="form-control form-control-sm" placeholder="ejemplo@correo.com">
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success btn-sm px-3" id="btnConfirmShare">Enviar <i class="bi bi-send-fill ms-1"></i></button>
                </div>
            </div>
        </div>
    </div>
HTML;
$c = preg_replace('/(    <div class="modal fade" id="globalRadarModal")/s', $modal . "$1", $c);

// 2. Change analyzedSensorsList.push to include metadata
$targetPush = "analyzedSensorsList.push({
                            id: c.sensor.id,
                            name: c.sensor.name || 'Sensor desconocido'
                        });";
$replacementPush = "analyzedSensorsList.push({
                            id: c.sensor.id,
                            name: c.sensor.name || 'Sensor desconocido',
                            metadata: c.sensor.metadata || {}
                        });";
$c = str_replace($targetPush, $replacementPush, $c);

// 3. Replace `#btnShareAnalysis` click event with modal logic
$targetScript = <<<JS
        $('#btnShareAnalysis').click(function() {
            const sensorId = $('#analyzeSensorId').val();
            const sensorName = $('#analyzeSensorName').text();
            if (!sensorId) return;

            const email = prompt('Ingrese el correo electrónico para enviar el reporte de ' + sensorName + ':');
            if (email) {
                $.ajax({
                    url: '/api/sensors/' + sensorId + '/share',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    data: { email: email },
                    success: function (res) {
                        if(res.success) {
                            alert('Informe enviado correctamente a ' + email);
                        } else {
                            alert('Error: ' + res.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error de conexión al enviar informe.');
                    }
                });
            }
        });
JS;

$replacementScript = <<<JS
        $('#btnShareAnalysis').click(function() {
            const sensorId = $('#analyzeSensorId').val();
            if (!sensorId) return;

            const sensorObj = analyzedSensorsList.find(s => s.id == sensorId);
            const select = $('#shareEmailSelect');
            const input = $('#shareEmailInput');
            
            select.empty();
            select.append('<option value="">-- Ingresar Manualmente --</option>');
            let hasEmails = false;

            if (sensorObj && sensorObj.metadata) {
                for (const [key, value] of Object.entries(sensorObj.metadata)) {
                    if (value && typeof value === 'string' && value.includes('@')) {
                        select.append(`<option value="\${value}">\${key}: \${value}</option>`);
                        hasEmails = true;
                    }
                }
            }

            if (hasEmails) {
                select.removeClass('d-none');
                input.val(select.val());
            } else {
                select.addClass('d-none');
                input.val('');
            }
            
            select.off('change').on('change', function() {
                if($(this).val()) {
                    input.val($(this).val());
                } else {
                    input.val('');
                }
            });

            $('#shareReportModal').modal('show');
        });

        $('#btnConfirmShare').click(function() {
            const sensorId = $('#analyzeSensorId').val();
            const email = $('#shareEmailInput').val();
            
            if (!email) {
                alert('Debe ingresar un correo válido.');
                return;
            }

            const btn = $(this);
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Enviando...');

            $.ajax({
                url: '/api/sensors/' + sensorId + '/share',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                data: { email: email },
                success: function (res) {
                    btn.prop('disabled', false).html(originalText);
                    if(res.success || res.message) {
                        alert('Informe enviado correctamente a ' + email);
                        $('#shareReportModal').modal('hide');
                    } else {
                        alert('Error: ' + (res.message || 'Error desconocido'));
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalText);
                    alert('Error de conexión al enviar informe.');
                }
            });
        });
JS;

$c = str_replace($targetScript, $replacementScript, $c);

// Support unix ends
$c = str_replace(str_replace("\r\n", "\n", $targetScript), $replacementScript, $c);

file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo "Integrated Custom Email Modal\n";
