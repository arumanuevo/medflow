<?php
$c = file_get_contents('resources/views/superadmin/users.blade.php');

$targetBtn = 'Historial Facturas Manuales</a>';
$replacementBtn = 'Historial Facturas Manuales</a>
                <button type="button" class="btn btn-outline-success ms-2" data-bs-toggle="modal" data-bs-target="#editPricesModal">
                    <i class="bi bi-currency-dollar"></i> Configurar Precios
                </button>';
$c = str_replace($targetBtn, $replacementBtn, $c);

$modal = <<<EOF

    <!-- Modal para Configurar Precios -->
    <div class="modal fade" id="editPricesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('superadmin.prices.save') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-tags-fill"></i> Configurar Valores de Planes (ARS)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle"></i> Actualiza aquí los montos (en Pesos Argentinos) de forma dinámica para ajustar la matriz de costos del sistema.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Plan Básico (ARS)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price_basico" class="form-control" step="0.01" value="{{ $prices['basico'] ?? 10000.00 }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Plan Premium (ARS)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price_premium" class="form-control" step="0.01" value="{{ $prices['premium'] ?? 25000.00 }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar Precios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
EOF;

// Since there is a closing @endsection at the end
$c = preg_replace('/@endsection\s*$/', $modal, $c);

file_put_contents('resources/views/superadmin/users.blade.php', $c);
echo "Injected Superadmin UI\n";
