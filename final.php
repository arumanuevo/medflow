<?php
$c = file_get_contents('temp_index.blade.php');
$startPagination = strpos($c, 'function renderPagination(meta)');

// Find the very first </script> tag after renderPagination
$endScript = strpos($c, '</script>', $startPagination);

// But wait, there is a } that closes renderPagination.
// Let's just find the end of `renderPagination` which is at the end of the file basically.
// I will just slice the file.
$goodCode = substr($c, 0, $startPagination);

$paginationCode = <<<JS
        // Renderizar paginación
        function renderPagination(meta) {
            if (!meta || !meta.last_page) {
                $('#pagination').html('');
                $('#paginationInfo').html('');
                return;
            }

            const from = meta.from || 0;
            const to = meta.to || 0;
            const total = meta.total || 0;
            $('#paginationInfo').html(`Mostrando \${from} a \${to} de \${total} consumos`);

            let paginationHtml = '';

            window.changePage = function (page) {
                if (typeof currentPage !== 'undefined') {
                    currentPage = page;
                } else {
                    window.currentPage = page;
                }
                loadConsumptions();
            };

            if (meta.current_page > 1) {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(\${meta.current_page - 1})" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>`;
            } else {
                paginationHtml += `<li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">&laquo;</span>
                </li>`;
            }

            const maxPages = 5;
            let startPage = Math.max(1, meta.current_page - Math.floor(maxPages / 2));
            let endPage = Math.min(meta.last_page, startPage + maxPages - 1);

            if (endPage - startPage + 1 < maxPages) {
                startPage = Math.max(1, endPage - maxPages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                if (i === meta.current_page) {
                    paginationHtml += `<li class="page-item active"><span class="page-link">\${i}</span></li>`;
                } else {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(\${i})">\${i}</a></li>`;
                }
            }

            if (meta.current_page < meta.last_page) {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(\${meta.current_page + 1})" aria-label="Siguiente">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>`;
            } else {
                paginationHtml += `<li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">&raquo;</span>
                </li>`;
            }

            $('#pagination').html(paginationHtml);
        }

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
    </script>
@endpush
JS;

$c = $goodCode . $paginationCode;

// Fix global double div at end of container
$c = preg_replace('/<\/div>\s*<\/div>\s*<!-- Modal para detalles del/s', "</div>\n\n    <!-- Modal para detalles del", $c);

// Fix extra div after analyzeSensorModal
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

// Shrink Anomaly and Stagnation titles
$c = preg_replace('/label for="analyzeThreshold" class="form-label mb-1 text-danger"/', 'label for="analyzeThreshold" class="form-label mb-1 text-danger small"', $c);
$c = preg_replace('/label for="analyzeStagnation" class="form-label mb-1 text-secondary"/', 'label for="analyzeStagnation" class="form-label mb-1 text-secondary small"', $c);

// Add the Share button cleanly
$c = preg_replace('/<button type="button" class="btn btn-outline-success btn-sm me-auto" id="btnShareAnalysis">.*?<\/button>/s', '', $c);
$target = '<div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>';
$replacement = '<div class="modal-footer p-2">
                <button type="button" class="btn btn-outline-success btn-sm me-auto" id="btnShareAnalysis"><i class="bi bi-share"></i> Compartir Informe</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>';
$c = str_replace($target, $replacement, $c);

$c = str_replace("btn-sm btn-info viewConsumptionBtn", "btn-sm py-0 px-2 viewConsumptionBtn", $c);
$c = str_replace("btn-sm btn-primary ms-1 analyzeSensorBtn", "btn-sm py-0 px-2 ms-1 analyzeSensorBtn", $c);
$c = str_replace("$('#resDaysBetween').text(d.days_between);", "$('#resDaysBetween').text(parseFloat(d.days_between).toFixed(0));", $c);
$c = str_replace("parseFloat(d.daily_average).toFixed(4)", "parseFloat(d.daily_average).toFixed(2)", $c);

file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo "Final generation complete\n";
