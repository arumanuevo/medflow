<?php
$c = file_get_contents('temp_index.blade.php');

// 1. Fix global container double div
$c = preg_replace('/<\/div>\s*<\/div>\s*<!-- Modal para detalles del/s', "</div>\n\n    <!-- Modal para detalles del", $c);

// 2. Fix analyzeSensorModal double div
$c = preg_replace('/<\/div>\s*<\/div>\s*<\/div>\s*<!-- Modal para Radar Global/s', "</div>\n    </div>\n\n    <!-- Modal para Radar Global", $c);

// 3. Fix filter layout - replacing the entire filterControls block to be safe
$filterOld = <<<HTML
                        <div class="row mb-3 align-items-end" id="filterControls">
                            <div class="col-md-3">
                                <label for="sensorFilter" class="form-label mb-1">Sensor</label>
                                <select class="form-select form-select-sm" id="sensorFilter">
                                    <option value="" selected>Todos los sensores</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="identifierFilter" class="form-label mb-1">Identificador</label>
                                <input type="text" class="form-control form-control-sm" id="identifierFilter"
                                    placeholder="Ej: SN-123">
                            </div>
                            <div class="col-md-2">
                                <label for="startDate" class="form-label mb-1">Fecha desde</label>
                                <input type="date" class="form-control form-control-sm" id="startDate">
                            </div>
                            <div class="col-md-2">
                                <label for="endDate" class="form-label mb-1">Fecha hasta</label>
                                <input type="date" class="form-control form-control-sm" id="endDate">
                            </div>
                            <div class="col-md-2" style="min-width: 15rem;">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2 w-100">
                                    <button type="button" class="btn btn-outline-success" id="btnFilterCommunity"
                                        style="white-space: nowrap;">
                                        <i class="bi bi-tree-fill"></i> Áreas Comunes
                                    </button>
                                    <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                                        <i class="bi bi-funnel"></i> Filtrar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="clearFiltersBtn">
                                        <i class="bi bi-x-lg"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
HTML;
$filterNew = <<<HTML
                        <div class="row mb-3 align-items-end" id="filterControls">
                            <div class="col-md-2">
                                <label for="sensorFilter" class="form-label mb-1">Sensor</label>
                                <select class="form-select form-select-sm" id="sensorFilter">
                                    <option value="" selected>Todos los sensores</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="identifierFilter" class="form-label mb-1">Identificador</label>
                                <input type="text" class="form-control form-control-sm" id="identifierFilter"
                                    placeholder="Ej: SN-123">
                            </div>
                            <div class="col-md-2">
                                <label for="startDate" class="form-label mb-1">Fecha desde</label>
                                <input type="date" class="form-control form-control-sm" id="startDate">
                            </div>
                            <div class="col-md-2">
                                <label for="endDate" class="form-label mb-1">Fecha hasta</label>
                                <input type="date" class="form-control form-control-sm" id="endDate">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2 w-100">
                                    <button type="button" class="btn btn-outline-success btn-sm" id="btnFilterCommunity"
                                        style="white-space: nowrap;">
                                        <i class="bi bi-tree-fill"></i> Áreas Comunes
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" id="applyFiltersBtn">
                                        <i class="bi bi-funnel"></i> Filtrar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFiltersBtn">
                                        <i class="bi bi-x-lg"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
HTML;
$c = str_replace($filterOld, $filterNew, $c);

// 4. Shrink Anomaly and Stagnation titles
$c = preg_replace('/label for="analyzeThreshold" class="form-label mb-1 text-danger"/', 'label for="analyzeThreshold" class="form-label mb-1 text-danger small"', $c);
$c = preg_replace('/label for="analyzeStagnation" class="form-label mb-1 text-secondary"/', 'label for="analyzeStagnation" class="form-label mb-1 text-secondary small"', $c);

// 5. Check if btnShareAnalysis JS is duplicated or incorrectly formatted
// Find all instances
$scriptMatchCount = preg_match_all("/\\$\\('#btnShareAnalysis'\\)\\.click/", $c);
if ($scriptMatchCount > 1) {
    // Clean them all out
    $c = preg_replace("/\\$\\('#btnShareAnalysis'\\)\\.click\\([\\s\\S]*?\\}\\);/s", '', $c);
}
// Add it cleanly once at the end if not exists
if (strpos($c, "$('#btnShareAnalysis').click") === false) {
    $script = <<<JS

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
    $c = str_replace('</script>' . "\n" . '@endpush', $script . "\n    </script>\n@endpush", $c);
}

// 6. Ensure action buttons are compact
$c = str_replace("btn-sm btn-info viewConsumptionBtn", "btn-sm py-0 px-2 viewConsumptionBtn", $c);
$c = str_replace("btn-sm btn-primary ms-1 analyzeSensorBtn", "btn-sm py-0 px-2 ms-1 analyzeSensorBtn", $c);

file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo "File generated.\n";
