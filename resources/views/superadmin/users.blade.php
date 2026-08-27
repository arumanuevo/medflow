@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark"><i class="bi bi-shield-lock text-danger me-2"></i> Panel SuperAdmin</h2>
            <div>
                <a href="{{ route('superadmin.invoices') }}" class="btn btn-outline-primary"><i class="bi bi-receipt"></i>
                    Historial Facturas Manuales</a>
                <button type="button" class="btn btn-outline-success ms-2" data-toggle="modal" data-target="#editPricesModal">
                    <i class="bi bi-currency-dollar"></i> Configurar Precios
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Gestión de Usuarios</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Plan Actual</th>
                                <th>Registro</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $u)
                                <tr>
                                    <td>{{ $u->id }}</td>
                                    <td>{{ $u->name }} <br><small
                                            class="text-muted">{{ $u->roles->pluck('name')->join(', ') }}</small></td>
                                    <td>{{ $u->email }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $u->subscription_plan == 'premium' ? 'bg-warning' : ($u->subscription_plan == 'free' ? 'bg-secondary' : 'bg-primary') }}">
                                            {{ strtoupper($u->subscription_plan ?: 'N/A') }}
                                        </span>
                                    </td>
                                    <td>{{ $u->created_at->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <!-- Cambiar Plan -->
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-toggle="modal"
                                            data-target="#planModal{{ $u->id }}" title="Cambiar Plan">
                                            <i class="bi bi-stars"></i>
                                        </button>
                                        <!-- Generar Pago -->
                                        <a href="{{ url('/api/subscription/create-preference?plan=premium&type=mensual&email=' . $u->email) }}"
                                            target="_blank" class="btn btn-sm btn-outline-success mb-1"
                                            title="Generar Checkout MP para Bás/Prem. Usa Link Wiroos">
                                            <i class="bi bi-cash"></i>
                                        </a>
                                        <!-- Enviar Factura/Recibo (NUEVO MODAL FACTURADOR) -->
                                        <button type="button" class="btn btn-sm btn-outline-info mb-1" data-toggle="modal"
                                            data-target="#facturaModal{{ $u->id }}"
                                            title="Emitir Factura Manual y Adjuntar PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </button>
                                        <!-- Enviar Mensaje Institucional -->
                                        <button type="button" class="btn btn-sm btn-outline-secondary mb-1"
                                            data-toggle="modal" data-target="#messageModal{{ $u->id }}"
                                            title="Escribir Mensaje Oficial">
                                            <i class="bi bi-envelope"></i>
                                        </button>
                                        <!-- Eliminar -->
                                        <form action="{{ route('superadmin.users.delete', $u->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('¿Seguro de eliminar este usuario para siempre? Todos sus lotes y sensores desapareceran.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger mb-1" {{ $u->email ==
                                                'scastellanoadmin@gmail.com' ? 'disabled' : '' }} title="Eliminar para
                                                siempre"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ZONA DE MODALES (FUERA DE LA TABLA PARA EVITAR BUGS HTML) -->
    @foreach($users as $u)
        <!-- Modal Facturación Manual -->
        <div class="modal fade" id="facturaModal{{ $u->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('superadmin.users.invoice', $u->id) }}" method="POST" enctype="multipart/form-data"
                    class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Emitir Factura a {{ $u->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Monto ($)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" placeholder="15000.00"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado Inicial</label>
                                <select name="status" class="form-select">
                                    <option value="pendiente">Pendiente de Pago</option>
                                    <option value="pagada">Confirmada / Pagada</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Concepto / Descripción</label>
                            <input type="text" name="description" class="form-control"
                                placeholder="Servicio MedFlow - Mes Septiembre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adjuntar Factura PDF (Opcional)</label>
                            <input type="file" name="invoice_file" class="form-control" accept="application/pdf">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="send_email" value="1"
                                id="sendEmail{{ $u->id }}" checked>
                            <label class="form-check-label" for="sendEmail{{ $u->id }}">
                                Enviar aviso y factura por correo a
                                {{ !empty($u->email_facturacion) ? $u->email_facturacion : $u->email }}
                                ahora mismo.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i>
                            Generar Factura</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Plan -->
        <div class="modal fade" id="planModal{{ $u->id }}" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <form action="{{ route('superadmin.users.plan', $u->id) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Cambiar Plan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body text-start">
                        <label>Forzar plan de <b>{{ $u->name }}</b></label>
                        <select name="subscription_plan" class="form-select mt-2">
                            <option value="free" {{ $u->subscription_plan == 'free' ? 'selected' : '' }}>
                                Free</option>
                            <option value="basico" {{ $u->subscription_plan == 'basico' ? 'selected' : '' }}>Básico</option>
                            <option value="premium" {{ $u->subscription_plan == 'premium' ? 'selected' : '' }}>Premium</option>
                            <option value="enterprise" {{ $u->subscription_plan == 'enterprise' ? 'selected' : '' }}>Enterprise
                            </option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Mensaje -->
        <div class="modal fade" id="messageModal{{ $u->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('superadmin.users.message', $u->id) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Escribir Mensaje Oficial a {{ $u->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label">Asunto / Título</label>
                            <input type="text" name="subject" class="form-control" placeholder="Aviso de MedFlow..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cuerpo del Mensaje</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Estimado cliente..."
                                required></textarea>
                        </div>
                        <div class="text-muted small">Este mensaje será enviado a {{ $u->email }} bajo
                            la plantilla institucional de MedFlow.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-send"></i>
                            Enviar Correo</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <!-- Modal para Configurar Precios -->
    <div class="modal fade" id="editPricesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('superadmin.prices.save') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-tags-fill"></i> Configurar Valores de Planes (ARS)</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar Precios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection