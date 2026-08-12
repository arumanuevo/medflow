@extends('layouts.modern')

@section('title', 'Mis Consumos - MeasureFlow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/consumptions-styles.css') }}">
@endpush

@section('content')

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="bi bi-graph-up-arrow"></i> Mis Consumos</h4>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" id="calculateConsumption">
                                <i class="bi bi-calculator btn-icon"></i> Recalcular Consumos
                            </button>
                            <button class="btn btn-info" id="exportConsumptions">
                                <i class="bi bi-file-earmark-spreadsheet btn-icon"></i> Exportar a Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4" id="filterControls">
                            <div class="col-md-3">
                                <label for="sensorFilter" class="form-label">Sensor</label>
                                <select class="form-select" id="sensorFilter">
                                    <option value="" selected>Todos los sensores</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="startDate" class="form-label">Fecha desde</label>
                                <input type="date" class="form-control" id="startDate">
                            </div>
                            <div class="col-md-3">
                                <label for="endDate" class="form-label">Fecha hasta</label>
                                <input type="date" class="form-control" id="endDate">
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-secondary flex-grow-1" id="applyFilters">
                                        <i class="bi bi-funnel"></i> Aplicar Filtros
                                    </button>
                                    <button class="btn btn-outline-secondary flex-grow-1" id="resetFilters">
                                        <i class="bi bi-arrow-clockwise"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>

                        </table>
                    </div>

                    <!-- Paginación (opcional, si se implementa) -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="paginationInfo"></div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination" id="pagination">
                                <!-- Paginación se generará dinámicamente -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Modal para detalles del consumo -->
    <div class="modal fade" id="consumptionDetailsModal" tabindex="-1" aria-labelledby="consumptionDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="consumptionDetailsModalLabel">Detalles del Consumo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="consumptionDetailsContent">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let currentPage = 1;

            // Cargar sensores del usuario para el filtro
            loadSensors();

            // Cargar consumos al inicio
            loadConsumptions();

            // Eventos
            $('#calculateConsumption').click(calculateAllConsumptions);
            $('#exportConsumptions').click(exportConsumptions);
            $('#applyFilters').click(function () {
                currentPage = 1;
                loadConsumptions();
            });
            $('#resetFilters').click(resetFilters);
            $('#sensorFilter, #startDate, #endDate').change(function () {
                currentPage = 1;
                loadConsumptions();
            });
        });

        // Cargar sensores del usuario
        function loadSensors() {
            $.ajax({
                url: '/api/sensors',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                success: function (response) {
                    if (response.success) {
                        const sensorFilter = $('#sensorFilter');
                        sensorFilter.empty();
                        sensorFilter.append('<option value="" selected>Todos los sensores</option>');

                        response.data.forEach(function (sensor) {
                            sensorFilter.append('<option value="' + sensor.id + '">' + sensor.name + ' (' + sensor.identifier + ')</option>');
                        });
                    }
                },
                error: function (xhr) {
                    showAlert('Error al cargar sensores: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                }
            });
        }

        // Cargar consumos
        function loadConsumptions() {
            const sensorId = $('#sensorFilter').val();
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();

            const params = {
                page: currentPage,
                per_page: 15
            };
            if (sensorId) params.sensor_id = sensorId;
            if (startDate) params.start_date = startDate;
            if (endDate) params.end_date = endDate;

            $.ajax({
                url: '/api/consumptions',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                data: params,
                beforeSend: function () {
                    $('#consumptionsTable').html(
                        '<tr>' +
                        '   <td colspan="9" class="text-center">' +
                        '       <div class="spinner-border text-primary" role="status">' +
                        '           <span class="visually-hidden">Cargando...</span>' +
                        '       </div> Cargando consumos...' +
                        '   </td>' +
                        '</tr>'
                    );
                },
                success: function (response) {
                    if (response.success) {
                        renderConsumptions(response.data);
                        // Si la respuesta incluye paginación con meta, renderizarla
                        if (response.meta) {
                            renderPagination(response.meta);
                        }
                    } else {
                        showAlert(response.message || 'Error al cargar consumos', 'danger');
                    }
                },
                error: function (xhr) {
                    showAlert('Error al cargar consumos: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                }
            });
        }

        // Renderizar consumos en la tabla
        function renderConsumptions(consumptions) {
            const tableBody = $('#consumptionsTable');
            tableBody.empty();

            if (!consumptions || consumptions.length === 0) {
                tableBody.append(
                    '<tr>' +
                    '   <td colspan="9" class="text-center">' +
                    '       <div class="empty-state">' +
                    '           <i class="bi bi-graph-up-arrow" style="font-size: 3rem;"></i>' +
                    '           <h4 class="mt-2">No hay consumos registrados</h4>' +
                    '           <p class="text-muted">Toma mediciones en tus sensores para generar consumos.</p>' +
                    '       </div>' +
                    '   </td>' +
                    '</tr>'
                );
                return;
            }

            consumptions.forEach(function (consumption) {
                // Asegurar que el sensor y grupo existan
                const sensorName = (consumption.sensor && consumption.sensor.name) ? consumption.sensor.name : 'Sensor desconocido';
                const sensorIdentifier = (consumption.sensor && consumption.sensor.identifier) ? consumption.sensor.identifier : 'N/A';
                const groupName = (consumption.sensor && consumption.sensor.group && consumption.sensor.group.name) ? consumption.sensor.group.name : 'Sin grupo';

                const startDate = new Date(consumption.period_start).toLocaleString('es-ES');
                const endDate = new Date(consumption.period_end).toLocaleString('es-ES');
                const period = startDate + ' → ' + endDate;

                // Redondear días a 2 decimales
                const daysBetween = consumption.days_between ? parseFloat(consumption.days_between).toFixed(2) : 0;
                const dailyAverage = consumption.daily_average ? parseFloat(consumption.daily_average).toFixed(2) : 0;

                const row = '<tr>' +
                    '   <td>' + consumption.id + '</td>' +
                    '   <td>' + sensorName + ' (' + sensorIdentifier + ')</td>' +
                    '   <td>' + groupName + '</td>' +
                    '   <td>' + consumption.value + '</td>' +
                    '   <td>' + consumption.unit + '</td>' +
                    '   <td>' + period + '</td>' +
                    '   <td>' + daysBetween + '</td>' +
                    '   <td>' + dailyAverage + '</td>' +
                    '   <td>' +
                    '       <button class="btn btn-sm btn-info viewConsumptionBtn" data-consumption-id="' + consumption.id + '" title="Ver detalles">' +
                    '           <i class="bi bi-eye"></i>' +
                    '       </button>' +
                    '   </td>' +
                    '</tr>';

                tableBody.append(row);
            });

            // Asignar eventos a los botones de ver detalles
            $('.viewConsumptionBtn').click(function () {
                const consumptionId = $(this).data('consumption-id');
                viewConsumptionDetails(consumptionId);
            });
        }

        // Ver detalles de un consumo
        function viewConsumptionDetails(consumptionId) {
            $.ajax({
                url: '/api/consumptions/' + consumptionId,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                beforeSend: function () {
                    $('#consumptionDetailsContent').html(
                        '<div class="text-center">' +
                        '   <div class="spinner-border text-primary" role="status">' +
                        '       <span class="visually-hidden">Cargando...</span>' +
                        '   </div>' +
                        '   <p>Cargando detalles del consumo...</p>' +
                        '</div>'
                    );
                    $('#consumptionDetailsModal').modal('show');
                },
                success: function (response) {
                    if (response.success) {
                        renderConsumptionDetails(response.data);
                    } else {
                        $('#consumptionDetailsContent').html(
                            '<div class="alert alert-danger">' +
                            '   <i class="bi bi-exclamation-triangle"></i> ' + (response.message || 'Error al cargar detalles') +
                            '</div>'
                        );
                    }
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                    $('#consumptionDetailsContent').html(
                        '<div class="alert alert-danger">' +
                        '   <i class="bi bi-exclamation-triangle"></i> Error: ' + errorMessage +
                        '</div>'
                    );
                }
            });
        }

        // Renderizar detalles del consumo en el modal
        function renderConsumptionDetails(consumption) {
            const startDate = new Date(consumption.period_start).toLocaleString('es-ES');
            const endDate = new Date(consumption.period_end).toLocaleString('es-ES');
            const dailyAverage = consumption.daily_average || 0;

            let html = '<div class="row">' +
                '   <div class="col-md-6">' +
                '       <div class="card mb-3">' +
                '           <div class="card-header bg-light">' +
                '               <h5><i class="bi bi-info-circle"></i> Información General</h5>' +
                '           </div>' +
                '           <div class="card-body">' +
                '               <dl class="row">' +
                '                   <dt class="col-sm-4">ID:</dt>' +
                '                   <dd class="col-sm-8">' + consumption.id + '</dd>' +
                '                   <dt class="col-sm-4">Sensor:</dt>' +
                '                   <dd class="col-sm-8">' + (consumption.sensor?.name || 'N/A') + ' (' + (consumption.sensor?.identifier || 'N/A') + ')</dd>' +
                '                   <dt class="col-sm-4">Grupo:</dt>' +
                '                   <dd class="col-sm-8">' + (consumption.sensor?.group?.name || 'N/A') + '</dd>' +
                '                   <dt class="col-sm-4">Unidad:</dt>' +
                '                   <dd class="col-sm-8">' + consumption.unit + '</dd>' +
                '               </dl>' +
                '           </div>' +
                '       </div>' +
                '   </div>' +
                '   <div class="col-md-6">' +
                '       <div class="card mb-3">' +
                '           <div class="card-header bg-light">' +
                '               <h5><i class="bi bi-graph-up"></i> Datos del Consumo</h5>' +
                '           </div>' +
                '           <div class="card-body">' +
                '               <dl class="row">' +
                '                   <dt class="col-sm-4">Valor:</dt>' +
                '                   <dd class="col-sm-8">' + consumption.value + ' ' + consumption.unit + '</dd>' +
                '                   <dt class="col-sm-4">Días:</dt>' +
                '                   <dd class="col-sm-8">' + consumption.days_between + '</dd>' +
                '                   <dt class="col-sm-4">Promedio Diario:</dt>' +
                '                   <dd class="col-sm-8">' + dailyAverage + ' ' + consumption.unit + '/día</dd>' +
                '               </dl>' +
                '           </div>' +
                '       </div>' +
                '   </div>' +
                '</div>' +
                '<div class="row">' +
                '   <div class="col-md-12">' +
                '       <div class="card">' +
                '           <div class="card-header bg-light">' +
                '               <h5><i class="bi bi-calendar-range"></i> Período</h5>' +
                '           </div>' +
                '           <div class="card-body">' +
                '               <dl class="row">' +
                '                   <dt class="col-sm-2">Inicio:</dt>' +
                '                   <dd class="col-sm-4">' + startDate + '</dd>' +
                '                   <dt class="col-sm-2">Fin:</dt>' +
                '                   <dd class="col-sm-4">' + endDate + '</dd>' +
                '               </dl>' +
                '           </div>' +
                '       </div>' +
                '   </div>' +
                '</div>';

            // Función para obtener el valor de una medición (soporta diferentes campos)
            function getMeasurementValue(measurement) {
                if (!measurement || !measurement.data) return 'N/A';

                // Intentar con campos comunes
                const fields = ['valor', 'consumo_m3', 'consumo', 'value', 'medicion'];
                for (const field of fields) {
                    if (measurement.data[field] !== undefined) {
                        return measurement.data[field];
                    }
                }

                // Si no se encuentra ningún campo conocido, devolver el primer valor numérico
                for (const [key, value] of Object.entries(measurement.data)) {
                    if (typeof value === 'number') {
                        return value;
                    }
                }

                return 'N/A';
            }

            // Agregar información de las mediciones si están disponibles
            if (consumption.start_measurement) {
                const startValue = getMeasurementValue(consumption.start_measurement) || 'N/A';
                const startMeasurementDate = new Date(consumption.start_measurement.measured_at).toLocaleString('es-ES');

                html += '<div class="row mt-3">' +
                    '   <div class="col-md-6">' +
                    '       <div class="card">' +
                    '           <div class="card-header bg-light">' +
                    '               <h5><i class="bi bi-arrow-down-left"></i> Medición Inicial</h5>' +
                    '           </div>' +
                    '           <div class="card-body">' +
                    '               <dl class="row">' +
                    '                   <dt class="col-sm-4">ID:</dt>' +
                    '                   <dd class="col-sm-8">' + consumption.start_measurement.id + '</dd>' +
                    '                   <dt class="col-sm-4">Valor:</dt>' +
                    '                   <dd class="col-sm-8">' + startValue + ' ' + consumption.unit + '</dd>' +
                    '                   <dt class="col-sm-4">Fecha:</dt>' +
                    '                   <dd class="col-sm-8">' + startMeasurementDate + '</dd>' +
                    '               </dl>' +
                    '           </div>' +
                    '       </div>' +
                    '   </div>';

                if (consumption.end_measurement) {
                    const endValue = getMeasurementValue(consumption.end_measurement) || 'N/A';
                    const endMeasurementDate = new Date(consumption.end_measurement.measured_at).toLocaleString('es-ES');

                    html += '   <div class="col-md-6">' +
                        '       <div class="card">' +
                        '           <div class="card-header bg-light">' +
                        '               <h5><i class="bi bi-arrow-up-right"></i> Medición Final</h5>' +
                        '           </div>' +
                        '           <div class="card-body">' +
                        '               <dl class="row">' +
                        '                   <dt class="col-sm-4">ID:</dt>' +
                        '                   <dd class="col-sm-8">' + consumption.end_measurement.id + '</dd>' +
                        '                   <dt class="col-sm-4">Valor:</dt>' +
                        '                   <dd class="col-sm-8">' + endValue + ' ' + consumption.unit + '</dd>' +
                        '                   <dt class="col-sm-4">Fecha:</dt>' +
                        '                   <dd class="col-sm-8">' + endMeasurementDate + '</dd>' +
                        '               </dl>' +
                        '           </div>' +
                        '       </div>' +
                        '   </div>' +
                        '</div>';
                }
            }

            $('#consumptionDetailsContent').html(html);
        }

        // Calcular consumos para todos los sensores
        function calculateAllConsumptions() {
            if (!confirm('¿Estás seguro de que deseas recalcular todos los consumos? Esto puede tardar unos segundos.')) {
                return;
            }

            $.ajax({
                url: '/api/consumptions/calculate-all',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                beforeSend: function () {
                    $('#calculateConsumption').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Recalculando...'
                    );
                },
                success: function (response) {
                    if (response.success) {
                        showAlert(response.message || 'Consumos recalculados correctamente', 'success');
                        loadConsumptions();
                    } else {
                        showAlert(response.message || 'Error al recalcular consumos', 'danger');
                    }
                },
                error: function (xhr) {
                    showAlert('Error al recalcular consumos: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                },
                complete: function () {
                    $('#calculateConsumption').prop('disabled', false).html(
                        '<i class="bi bi-calculator btn-icon"></i> Recalcular Consumos'
                    );
                }
            });
        }

        // Exportar consumos a Excel
        function exportConsumptions() {
            const sensorId = $('#sensorFilter').val();
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();

            const params = {};
            if (sensorId) params.sensor_id = sensorId;
            if (startDate) params.start_date = startDate;
            if (endDate) params.end_date = endDate;

            const url = '/api/consumptions/export?' + new URLSearchParams(params).toString();
            window.open(url, '_blank');
        }

        // Limpiar filtros
        function resetFilters() {
            $('#sensorFilter').val('');
            $('#startDate').val('');
            $('#endDate').val('');
            currentPage = 1;
            loadConsumptions();
        }

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
            $('#paginationInfo').html(`Mostrando ${from} a ${to} de ${total} consumos`);

            let paginationHtml = '';

            // Función global para que sea accesible desde html inline si lo hay
            window.changePage = function (page) {
                // Obtenemos la referencia a través de scope o usamos la variable principal si existe
                if (typeof currentPage !== 'undefined') {
                    currentPage = page;
                } else {
                    // Intentar inyectar en el query param o recargar script local
                    window.currentPage = page;
                }
                loadConsumptions();
            };

            if (meta.current_page > 1) {
                paginationHtml += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(${meta.current_page - 1})" aria-label="Anterior">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>`;
            } else {
                paginationHtml += `
            <li class="page-item disabled">
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
                    paginationHtml += `
                <li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
                } else {
                    paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(${i})">${i}</a>
                </li>`;
                }
            }

            if (meta.current_page < meta.last_page) {
                paginationHtml += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(${meta.current_page + 1})" aria-label="Siguiente">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>`;
            } else {
                paginationHtml += `
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true">&raquo;</span>
            </li>`;
            }

            $('#pagination').html(paginationHtml);
        }

        // Mostrar alerta
        function showAlert(message, type) {
            const alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>';
            $('.card-body').prepend(alertHtml);
        }
    </script>
@endpush