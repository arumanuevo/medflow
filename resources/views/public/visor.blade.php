<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor Público de Consumo - {{ $sensor->name }}</title>
    
    <!-- Bootstrap CSS (from Medflow admin) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
        }
        .public-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .header-bg {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 2.5rem 1rem 3rem;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .sensor-card {
            margin-top: -2.5rem;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            background-color: #f8f9fa;
        }
        .anomaly-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>

    <div class="header-bg text-center mb-0">
        <h4 class="fw-bold mb-1"><i class="bi bi-droplet-half"></i> Medflow</h4>
        <p class="text-white-50 small mb-0">Portal Público de Mediciones</p>
    </div>

    <div class="container public-container px-3 pb-5">
        <!-- Tarjeta Principal del Sensor -->
        <div class="card sensor-card mb-4">
            <div class="card-body p-4 text-center">
                <h2 class="fw-bold text-dark mb-1">{{ $sensor->name }}</h2>
                <div class="text-muted small mb-3">ID del Medidor: <code>{{ $sensor->identifier ?? 'N/A' }}</code></div>
                
                @if(isset($hasData) && !$hasData)
                    <div class="alert alert-warning mb-0 border-0 shadow-sm">
                        <i class="bi bi-exclamation-triangle"></i> Todavía no hay registros de consumo para este medidor. Vuelve a consultar más adelante.
                    </div>
                @else
                    <div class="row g-2 mt-4 text-start">
                        <div class="col-12 mb-2">
                            <div class="stat-card p-3 border text-center" style="background-color: #f8f9fa;">
                                <h6 class="text-muted small mb-1">Monto a Facturar (Total)</h6>
                                <h1 class="fw-bold text-dark mb-0">{{ number_format($finalBilledTotal, 2) }} <span class="fs-5 text-muted">{{ $unit }}</span></h1>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card p-3 h-100 border text-center">
                                <h6 class="text-muted small mb-1">Consumo Lote</h6>
                                <h4 class="fw-bold text-primary mb-0">{{ number_format($totalDelta, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card p-3 h-100 border text-center">
                                <h6 class="text-muted small mb-1">Espacios Comunes</h6>
                                <h4 class="fw-bold text-success mb-0">+{{ number_format($communityContribution, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-2 small text-muted text-center mt-2">
                        Período auditado: últimos {{ $daysBetween }} días ({{ count($chartData) }} lecturas)
                    </div>
                @endif
            </div>
        </div>

        @if(!isset($hasData) || $hasData)
            <!-- Gráfico Lineal -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0">Evolución del Consumo</h5>
                </div>
                <div class="card-body p-3">
                    <div style="position: relative; height: 230px; width: 100%;">
                        <canvas id="evolutionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Anomalías Visuales -->
            @if($anomaliesCount > 0)
                <div class="card border-danger shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-danger text-white border-0 pt-3 pb-3 px-4 rounded-top-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Puntos Atípicos Detectados</h5>
                        <p class="small mb-0 opacity-75">Se detectaron {{ $anomaliesCount }} consumos inusuales.</p>
                    </div>
                    <div class="card-body p-3 bg-light rounded-bottom-4">
                        <div class="row g-3">
                            @foreach($chartData as $cd)
                                @if($cd['anomaly'])
                                    <div class="col-12">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="badge bg-danger anomaly-badge">Anomalía</span>
                                                    <span class="text-muted small">{{ $cd['date'] }}</span>
                                                </div>
                                                <h4 class="text-danger fw-bold mb-3">{{ $cd['value'] }} <span class="fs-6 text-muted">{{ $unit }}</span></h4>
                                                
                                                @if($cd['photo'])
                                                    <img src="{{ $cd['photo'] }}" style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px;" alt="Evidencia">
                                                @else
                                                    <div class="p-3 text-center bg-light rounded text-muted small border">
                                                        <i class="bi bi-camera-video-off fs-4 d-block mb-1"></i>
                                                        Sin evidencia fotográfica.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @if(!isset($hasData) || $hasData)
            <!-- Historial Completo -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 rounded-top-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history"></i> Historial de Lecturas</h5>
                    <p class="small text-muted mb-0">Listado cronológico de todos tus consumos auditados.</p>
                </div>
                <div class="card-body p-0 rounded-bottom-4">
                    <div class="list-group list-group-flush rounded-bottom-4">
                        @foreach(array_reverse($chartData) as $cd)
                            <div class="list-group-item p-3 {{ $cd['anomaly'] ? 'bg-danger bg-opacity-10' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold fs-5 {{ $cd['anomaly'] ? 'text-danger' : 'text-primary' }}">{{ $cd['value'] }} <small class="FS-6 text-muted">{{ $unit }}</small></span>
                                    <span class="text-muted small">{{ $cd['date'] }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    @if($cd['anomaly'])
                                        <span class="badge bg-danger">Anomalía Detectada</span>
                                    @else
                                        <span class="badge bg-light text-secondary border"><i class="bi bi-check-circle text-success"></i> Lectura Normal</span>
                                    @endif
                                    
                                    @if($cd['photo'])
                                        <a href="{{ $cd['photo'] }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-image"></i> Ver Foto
                                        </a>
                                    @else
                                        <span class="small text-muted fst-italic">Sin foto</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="text-center mt-5 mb-4">
            <p class="small text-muted mb-1">Elaborado y certificado por auditores a través de <strong>Medflow Analytics</strong>.</p>
            <p class="small text-muted opacity-50">&copy; {{ date('Y') }} Todos los derechos reservados.</p>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.css"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @if(!isset($hasData) || $hasData)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($chartData);
            const unit = "{{ $unit }}";
            
            const labels = chartData.map(c => c.date.split(' ')[0]); // Solo fecha por espacio en mobile
            const dataValues = chartData.map(c => c.value);
            const pointColors = chartData.map(c => c.anomaly ? 'rgba(220, 53, 69, 1)' : 'rgba(13, 110, 253, 1)');
            const pointRadius = chartData.map(c => c.anomaly ? 6 : 3);

            const ctx = document.getElementById('evolutionChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Lectura (' + unit + ')',
                        data: dataValues,
                        borderColor: 'rgba(13, 110, 253, 0.7)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        pointBackgroundColor: pointColors,
                        pointBorderColor: pointColors,
                        pointRadius: pointRadius,
                        pointHoverRadius: 8,
                        tension: 0.3, 
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y;
                                    }
                                    const pointRaw = chartData[context.dataIndex];
                                    if (pointRaw.anomaly) {
                                        label += ' ⚠️ [PUNTO ATÍPICO]';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { maxTicksLimit: 5, font: { size: 10 } }
                        },
                        y: {
                            beginAtZero: false,
                            grid: { 
                                borderDash: [2, 4],
                                color: 'rgba(0,0,0,0.05)'
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endif
</body>
</html>
