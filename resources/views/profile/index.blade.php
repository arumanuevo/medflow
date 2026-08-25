@extends('layouts.modern')

@section('title', 'Mi Perfil - MedFlow')

@push('styles')
    <style>
        /* Estilos existentes */
        /* Estilo para el boton close blanco en modal de peligro */
        .btn-close-white {
            filter: brightness(0) invert(1);
        }

        /*  Estilos para la sección de suscripción */
        #subscriptionStatus .btn-outline-primary:hover {
            background: #0d6efd;
            color: #fff;
        }

        #subscriptionStatus .btn-warning {
            color: #000;
            border-color: #ffc107;
        }

        #subscriptionStatus .btn-warning:hover {
            background: #e0a800;
            border-color: #d39e00;
        }

        /*  Estilos para el contador regresivo */
        .countdown-timer {
            font-size: 1.2rem;
            font-weight: 600;
            color: #0d6efd;
            background: #e9ecef;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            display: inline-block;
        }

        .countdown-timer.expiring {
            color: #dc3545;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        /*  Badge de estado */
        .status-badge {
            font-size: 0.85rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.expired {
            background: #f8d7da;
            color: #721c24;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        /*  Botones de depuracin */
        .debug-btn {
            border: 2px dashed #6c757d;
            background: #f8f9fa;
            transition: all 0.3s;
        }

        .debug-btn:hover {
            background: #e9ecef;
            border-color: #0d6efd;
        }

        .debug-section {
            background: #f8f9fa;
            border: 2px dashed #6c757d;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .debug-section .badge {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Estilos para el modal de confirmación */
        #confirmDeleteAllDataModal .modal-header {
            border-bottom: none;
        }

        #confirmDeleteAllDataModal .modal-footer {
            border-top: none;
        }

        #confirmDeleteAllDataModal .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        /*  Estilos para la sección de suscripción mejorada */
        #subscriptionStatus .card {
            border-radius: 10px;
            overflow: hidden;
        }

        #subscriptionStatus .card-header {
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
        }

        #subscriptionStatus .card-body {
            padding: 0.8rem 1rem;
        }

        #subscriptionStatus .card-body .text-muted {
            font-size: 0.75rem;
        }

        #subscriptionStatus .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.6rem;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-user-circle"></i> Mi Perfil</h4>
                        <span class="badge bg-light text-dark" id="userRole">
                            <i class="fas fa-id-badge"></i> Cargando...
                        </span>
                    </div>
                    <div class="card-body">
                        <!-- Alertas -->
                        <div id="alertContainer"></div>

                        <!-- Formulario Modernizado -->
                        <form id="profileForm">
                            @csrf
                            @method('PUT')

                            <input type="hidden" id="hasGoogleId" value="{{ $user->google_id ? 'true' : 'false' }}">

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3"
                                        style="font-size: 0.75rem; letter-spacing: 1px;">Identidad Personal</h6>

                                    <div class="mb-3">
                                        <label for="name" class="form-label small fw-semibold text-muted mb-1">Nombre
                                            completo</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white text-muted border-end-0"><i
                                                    class="bi bi-person"></i></span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="name"
                                                name="name" autocomplete="name" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label small fw-semibold text-muted mb-1">Correo
                                            electrónico</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white text-muted border-end-0"><i
                                                    class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control border-start-0 ps-0" id="email"
                                                name="email" autocomplete="email" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3"
                                        style="font-size: 0.75rem; letter-spacing: 1px;">Seguridad</h6>

                                    {{-- Google Info --}}
                                    <div id="googleInfo" class="mb-3 d-none">
                                        <div
                                            class="alert alert-info border-info-subtle shadow-sm d-flex align-items-center">
                                            <i class="bi bi-google fs-4 me-3 text-info"></i>
                                            <div>
                                                <strong>Cuenta enlazada a Google</strong><br>
                                                <small>Gestionas tu seguridad directamente desde Google.</small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Password Fields --}}
                                    <div id="passwordFields" class="{{ $user->google_id ? 'd-none' : '' }}">
                                        <div class="mb-3">
                                            <label for="password" class="form-label small fw-semibold text-muted mb-1">Nueva
                                                contraseña</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text bg-white text-muted border-end-0"><i
                                                        class="bi bi-key"></i></span>
                                                <input type="password" class="form-control border-start-0 ps-0"
                                                    id="password" name="password" placeholder="Mínimo 8 caracteres">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password_confirmation"
                                                class="form-label small fw-semibold text-muted mb-1">Confirmar
                                                contraseña</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text bg-white text-muted border-end-0"><i
                                                        class="bi bi-shield-lock"></i></span>
                                                <input type="password" class="form-control border-start-0 ps-0"
                                                    id="password_confirmation" name="password_confirmation">
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Dejar en blanco
                                                si no deseas cambiarla.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                                <button type="button" class="btn btn-light border-secondary-subtle px-4 shadow-sm"
                                    id="refreshStatsBtn">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Sincronizar
                                </button>
                                <button type="submit" class="btn btn-primary px-4 shadow-sm fw-semibold" id="saveProfileBtn"
                                    style="border-radius: 8px;">
                                    <i class="bi bi-save2 me-1"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>

                        <hr>
                        <div class="mt-3" id="userStats">
                            <h6>Estadísticas de la cuenta</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 id="totalSensors">-</h5>
                                            <small>Sensores</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 id="totalMeasurements">-</h5>
                                            <small>Mediciones</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 id="totalGroups">-</h5>
                                            <small>Grupos</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 mb-2">
                            <h6 class="text-uppercase text-muted fw-bold mb-3"
                                style="font-size: 0.75rem; letter-spacing: 1px;">Detalles de Acceso Crítico</h6>
                            <div class="row g-3">
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <small class="text-muted d-block mb-1">Identificador nico</small>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-hash text-primary"></i>
                                            <strong class="fs-6" id="userId">-</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <small class="text-muted d-block mb-1">Licencia Base</small>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-shield-check text-success"></i>
                                            <strong class="fs-6" id="userPlan">-</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <small class="text-muted d-block mb-1">Adhesión al Sistema</small>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-calendar-plus text-info"></i>
                                            <strong style="font-size: 0.85rem;" id="userCreatedAt">-</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <small class="text-muted d-block mb-1">última Edición</small>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-clock-history text-warning"></i>
                                            <strong style="font-size: 0.85rem;" id="userUpdatedAt">-</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- SECCIN DE SUSCRIPCIN --}}
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><i class="bi bi-credit-card"></i> Suscripción</h6>
                                <span class="badge bg-secondary" id="subscriptionEnv">
                                    <i class="bi bi-tag"></i> {{ app()->environment() }}
                                </span>
                            </div>

                            <div id="subscriptionStatus">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                    <span>Cargando estado de suscripción...</span>
                                </div>
                            </div>
                        </div>

                        {{-- SECCIN DE INFORMACIN DE PLANES --}}
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Alcances de los Planes</h6>
                            </div>
                            <div class="row g-2">
                                <!-- Plan Free -->
                                <div class="col-md-4">
                                    <div class="card h-100 border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="bi bi-gift"></i> Plan Free</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small>1 grupo máximo</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small>1 sensor máximo</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                                    <small>Sin colaboradores</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                                    <small>Sin importacin masiva</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                                    <small>Mediciones limitadas</small>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- Plan Básico -->
                                <div class="col-md-4">
                                    <div class="card h-100 border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="bi bi-gem"></i> Plan Básico</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small>2 grupos máximo</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small>2 sensores máximo</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small>1 colaborador</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                                    <small>Sin importacin masiva</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small>Mediciones ilimitadas</small>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- Plan Premium -->
                                <div class="col-md-4">
                                    <div class="card h-100 border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i class="bi bi-stars"></i> Plan Premium</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small>Grupos ilimitados</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small>Sensores ilimitados</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small>Colaboradores ilimitados</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small>Importacin masiva</small>
                                                </li>
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small>Todas las funciones</small>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        {{-- SECCIN DE FACTURACIN --}}
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-receipt"></i> Facturación y Comprobantes</h6>
                            </div>

                            <!-- Datos Impositivos -->
                            <div class="alert bg-light border">
                                <form action="{{ route('profile.update-billing') }}" method="POST">
                                    @csrf
                                    <h6 class="fs-6 mb-3">Tus Datos Fiscales (Para Factura AFIP)</h6>
                                    <div class="row g-2 align-items-end mb-2">
                                        <div class="col-md-4">
                                            <label class="form-label small">CUIT / CUIL</label>
                                            <input type="text" name="cuit" class="form-control"
                                                placeholder="Ej: 30-12345678-9" value="{{ $user->cuit ?? '' }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Condición IVA</label>
                                            <select name="condicion_iva" class="form-select" required>
                                                <option value="" {{ empty($user->condicion_iva) ? 'selected' : '' }}>
                                                    Seleccionar...</option>
                                                <option value="IVA Responsable Inscripto" {{ ($user->condicion_iva ?? '') == 'IVA Responsable Inscripto' ? 'selected' : '' }}>IVA Responsable
                                                    Inscripto</option>
                                                <option value="IVA Sujeto Exento" {{ ($user->condicion_iva ?? '') == 'IVA Sujeto Exento' ? 'selected' : '' }}>IVA Sujeto Exento</option>
                                                <option value="Consumidor Final" {{ ($user->condicion_iva ?? '') == 'Consumidor Final' ? 'selected' : '' }}>Consumidor Final</option>
                                                <option value="Responsable Monotributo" {{ ($user->condicion_iva ?? '') == 'Responsable Monotributo' ? 'selected' : '' }}>Responsable
                                                    Monotributo</option>
                                                <option value="Sujeto No Categorizado" {{ ($user->condicion_iva ?? '') == 'Sujeto No Categorizado' ? 'selected' : '' }}>Sujeto No
                                                    Categorizado</option>
                                                <option value="Proveedor del Exterior" {{ ($user->condicion_iva ?? '') == 'Proveedor del Exterior' ? 'selected' : '' }}>Proveedor del
                                                    Exterior</option>
                                                <option value="Cliente del Exterior" {{ ($user->condicion_iva ?? '') == 'Cliente del Exterior' ? 'selected' : '' }}>Cliente del Exterior
                                                </option>
                                                <option value="IVA Liberado - Ley N 19.640" {{ ($user->condicion_iva ?? '') == 'IVA Liberado - Ley N 19.640' ? 'selected' : '' }}>IVA Liberado -
                                                    Ley N 19.640</option>
                                                <option value="Monotributista Social" {{ ($user->condicion_iva ?? '') == 'Monotributista Social' ? 'selected' : '' }}>Monotributista Social
                                                </option>
                                                <option value="IVA No Alcanzado" {{ ($user->condicion_iva ?? '') == 'IVA No Alcanzado' ? 'selected' : '' }}>IVA No Alcanzado</option>
                                                <option value="Monotributista Trabajador Independiente Promovido" {{ ($user->condicion_iva ?? '') == 'Monotributista Trabajador Independiente Promovido' ? 'selected' : '' }}>Monotributista Trabajador Independiente
                                                    Promovido</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Email de Envio de Factura</label>
                                            <input type="text" name="email_facturacion" class="form-control"
                                                placeholder="Ej: admin@empresa.com, pagos@empresa.com"
                                                value="{{ $user->email_facturacion ?? $user->email }}" required>
                                        </div>
                                    </div>
                                    <div class="row g-2 align-items-end mb-2">
                                        <div class="col-md-4">
                                            <label class="form-label small">Condición de Venta</label>
                                            <select name="condicion_venta" class="form-select" required>
                                                <option value="" {{ empty($user->condicion_venta) ? 'selected' : '' }}>
                                                    Seleccionar...</option>
                                                <option value="Contado" {{ ($user->condicion_venta ?? '') == 'Contado' ? 'selected' : '' }}>Contado</option>
                                                <option value="Tarjeta de Crédito" {{ ($user->condicion_venta ?? '') == 'Tarjeta de Crédito' ? 'selected' : '' }}>Tarjeta de Crédito
                                                </option>
                                                <option value="Tarjeta de Débito" {{ ($user->condicion_venta ?? '') == 'Tarjeta de Débito' ? 'selected' : '' }}>Tarjeta de Débito</option>
                                                <option value="Cuenta Corriente" {{ ($user->condicion_venta ?? '') == 'Cuenta Corriente' ? 'selected' : '' }}>Cuenta Corriente</option>
                                                <option value="Cheque" {{ ($user->condicion_venta ?? '') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                                <option value="Ticket" {{ ($user->condicion_venta ?? '') == 'Ticket' ? 'selected' : '' }}>Ticket</option>
                                                <option value="Otra" {{ ($user->condicion_venta ?? '') == 'Otra' ? 'selected' : '' }}>Otra</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Descripción para Factura (Opcional)</label>
                                            <input type="text" name="descripcion_servicio" class="form-control"
                                                placeholder="Ej: Suscripción Medflow - Barrio Las Lomas"
                                                value="{{ $user->descripcion_servicio ?? '' }}">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save"></i>
                                                Guardar</button>
                                        </div>
                                    </div>
                                    <small class="text-muted mt-2 d-block"><i class="bi bi-info-circle"></i> Completá estos
                                        datos si requerís la emisión de tu Factura oficial.</small>
                                </form>
                            </div>

                            <!-- Historial de Pagos -->
                            <h6 class="fs-6 mt-4 mb-3">Historial de Pagos</h6>
                            @if(isset($billingHistory) && $billingHistory->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle">
                                        <thead class="table-light text-center small text-uppercase">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Plan</th>
                                                <th>Ref. de Pago</th>
                                                <th>Monto</th>
                                                <th>Comprobante</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center text-sm">
                                            @foreach($billingHistory as $receipt)
                                                <tr>
                                                    <td>{{ $receipt->created_at->format('d/m/Y') }}</td>
                                                    <td class="text-uppercase fw-bold">{{ $receipt->plan }}</td>
                                                    <td class="text-muted">
                                                        <small>#{{ $receipt->payment_id ?? $receipt->id }}</small>
                                                    </td>
                                                    <td>${{ number_format($receipt->amount, 2, ',', '.') }} {{ $receipt->currency }}
                                                    </td>
                                                    <td>
                                                        <a target="_blank"
                                                            href="{{ route('profile.download-receipt', $receipt->id) }}"
                                                            class="btn btn-sm btn-outline-secondary"
                                                            title="Descargar Comprobante Provisorio">
                                                            <i class="bi bi-file-earmark-pdf"></i> Recibo
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-secondary text-center">
                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                    An no hay registros de pagos en tu historial.
                                </div>
                            @endif
                        </div>

                        {{-- SECCIN DE ELIMINACIN DE DATOS --}}
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-danger">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    Eliminar todos mis datos
                                </h6>
                            </div>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>TODOS</strong> tus datos: sensores, mediciones, fotos asociadas, grupos,
                                colaboraciones, suscripciones y configuraciones.
                                <strong>No podrás recuperar esta información.</strong>
                            </div>
                            <button type="button" class="btn btn-danger w-100" id="deleteAllDataBtn">
                                <i class="bi bi-trash-fill me-2"></i>
                                Eliminar todos mis datos
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar todos los datos -->
    <div class="modal fade" id="confirmDeleteAllDataModal" tabindex="-1" aria-labelledby="confirmDeleteAllDataModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmDeleteAllDataModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        ?Estás seguro de que deseas eliminar TODOS tus datos?
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Advertencia: Esta acción eliminar TODOS tus datos:</strong> sensores, mediciones, fotos
                        asociadas, grupos, colaboraciones, suscripciones y configuraciones. No podrás recuperar esta
                        información.
                    </div>
                    <p>Se eliminarán:</p>
                    <ul>
                        <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todos tus sensores</li>
                        <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todas tus mediciones</li>
                        <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todas las fotos asociadas a mediciones
                        </li>
                        <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todos tus grupos de sensores</li>
                        <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Todas tus colaboraciones y accesos
                            compartidos</li>
                        <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Tus suscripciones</li>
                        <li><i class="bi bi-check-circle-fill text-danger me-2"></i> Tus configuraciones personales</li>
                    </ul>
                    <p>Tu cuenta ser anonimizada (nombre y email cambiados).</p>
                    <div class="form-group">
                        <label for="confirmationText" class="form-label">Para confirmar, escribe <strong>"ELIMINAR
                                TODO"</strong>:</label>
                        <input type="text" class="form-control" id="confirmationText" placeholder="ELIMINAR TODO">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteAllData">
                        <i class="bi bi-trash-fill me-1"></i> S, eliminar todo
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>


        /**
         * Subir de plan (Free  Básico, Free  Premium, Básico  Premium)
         */
        function upgradePlan(targetPlan) {
            const planNames = {
                'basico': 'Básico ($10 ARS)',
                'premium': 'Premium ($25 ARS)'
            };

            const planIcons = {
                'basico': '',
                'premium': ''
            };

            if (!confirm(`?Deseas cambiar al plan ${planIcons[targetPlan]} ${planNames[targetPlan]}?`)) {
                return;
            }

            @if(app()->environment('local'))
                debugActivateSubscription(targetPlan);
            @else
                window.location.href = `/suscripcion/${targetPlan}/pagar`;
            @endif
                  }

        /**
         * Bajar de plan (Premium  Básico)
         */
        function downgradePlan(targetPlan) {
            const planNames = {
                'basico': 'Básico ($10 ARS)',
                'free': 'Free (Gratuito)'
            };

            const planIcons = {
                'basico': '',
                'free': ''
            };

            if (!confirm(`?Deseas bajar al plan ${planIcons[targetPlan]} ${planNames[targetPlan]}?`)) {
                return;
            }

            @if(app()->environment('local'))
                debugActivateSubscription(targetPlan);
            @else
                showAlert(' La bajada de plan se aplicar al finalizar el período actual.', 'warning');
            @endif
                  }

        /**
         * Cancelar suscripción
         */
        function cancelSubscription() {
            if (!confirm('?Estás seguro de que deseas cancelar tu suscripción? Perders los beneficios al final del período actual.')) {
                return;
            }

            @if(app()->environment('local'))
                debugExpireSubscription();
            @else
                $.ajax({
                    url: '/api/subscription/cancel',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    success: function (response) {
                        if (response.success) {
                            showAlert(' Suscripción cancelada correctamente.', 'success');
                            loadSubscriptionStatus();
                        } else {
                            showAlert(' ' + (response.message || 'Error al cancelar'), 'danger');
                        }
                    },
                    error: function (xhr) {
                        showAlert(' Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                    }
                });
            @endif
                  }

        // =============================================
        //  FUNCIONES DE DEPURACIN (SOLO LOCAL)
        // =============================================

        @if(app()->environment('local'))
            function debugActivateSubscription(plan) {
                const planNames = {
                    'free': 'Plan Free',
                    'basico': 'Plan Básico',
                    'premium': 'Plan Premium'
                };

                const planIcons = {
                    'free': '',
                    'basico': '',
                    'premium': ''
                };

                //  Cambiar duración: 30 das para planes de prueba (43200 minutos)
                const duration = plan === 'free' ? 9999 : 43200; // 30 das = 43200 minutos
                const durationText = plan === 'free' ? 'tiempo indefinido' : '30 das';

                showAlert(
                    ` Activando ${planNames[plan]} por ${durationText}...`,
                    'info'
                );

                $.ajax({
                    url: '/api/subscription/debug/activate',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    data: JSON.stringify({
                        plan: plan,
                        duration_minutes: duration
                    }),
                    success: function (response) {
                        if (response.success) {
                            showAlert(
                                ` ${planIcons[plan]} ${planNames[plan]} activado correctamente${plan === 'free' ? ' (permanente)' : ' por 30 das'}.`,
                                'success'
                            );

                            //  Si se activa Free, limpiar el plan anterior
                            if (plan === 'free') {
                                localStorage.removeItem('previous_plan');
                            } else {
                                localStorage.setItem('previous_plan', plan);
                            }

                            //  REFRESCAR TODO
                            loadSubscriptionStatus();
                            loadStats();
                            loadProfile();
                            updateAccountInfo();

                            if (typeof refreshSubscriptionStatus === 'function') {
                                refreshSubscriptionStatus();
                            }
                        } else {
                            showAlert(' ' + (response.message || 'Error al activar'), 'danger');
                        }
                    },
                    error: function (xhr) {
                        showAlert(
                            ' Error: ' + (xhr.responseJSON?.message || xhr.statusText),
                            'danger'
                        );
                    }
                });
            }

            function debugExpireSubscription() {
                showAlert(' Forzando expiración de la suscripción...', 'warning');

                $.ajax({
                    url: '/api/subscription/debug/expire',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        if (response.success) {
                            showAlert(' Suscripción expirada correctamente', 'success');

                            //  RECARGAR TODO
                            loadSubscriptionStatus();
                            loadStats();
                            loadProfile();
                            updateAccountInfo();

                            if (typeof refreshSubscriptionStatus === 'function') {
                                refreshSubscriptionStatus();
                            }
                        } else {
                            showAlert(' ' + (response.message || 'Error al expirar'), 'danger');
                        }
                    },
                    error: function (xhr) {
                        showAlert(
                            ' Error: ' + (xhr.responseJSON?.message || xhr.statusText),
                            'danger'
                        );
                    }
                });
            }

            function debugClearSubscriptions() {
                if (!confirm(' ?Estás seguro de que quieres eliminar TODO el historial de suscripciones?')) {
                    return;
                }

                showAlert(' Limpiando historial de suscripciones...', 'warning');

                $.ajax({
                    url: '/api/subscription/debug/clear',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        if (response.success) {
                            showAlert(' Historial limpiado correctamente', 'success');
                            loadSubscriptionStatus();
                            loadStats();
                            loadProfile();
                        } else {
                            showAlert(' ' + (response.message || 'Error al limpiar'), 'danger');
                        }
                    },
                    error: function (xhr) {
                        showAlert(
                            ' Error: ' + (xhr.responseJSON?.message || xhr.statusText),
                            'danger'
                        );
                    }
                });
            }

            window.debugRenewSubscription = function () {
                $.ajax({
                    url: '/api/subscription/plan/status',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        if (response.success && response.data.has_active_subscription) {
                            const currentPlan = response.data.subscription.plan;
                            debugActivateSubscription(currentPlan);
                        } else {
                            showAlert(' No hay suscripción activa para renovar', 'warning');
                        }
                    },
                    error: function () {
                        showAlert(' Error al obtener el plan actual', 'danger');
                    }
                });
            };
        @endif

            // =============================================
            //  FUNCIONES DE ALERTAS Y UTILIDADES
            // =============================================

            function showAlert(message, type) {
                const alertHtml = `
                                                                                                            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                                                                                                                ${message}
                                                                                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                                                                            </div>
                                                                                                        `;
                $('#alertContainer').append(alertHtml);

                setTimeout(() => {
                    $('#alertContainer .alert').first().fadeOut(500, function () {
                        $(this).remove();
                    });
                }, 8000);
            }

        // =============================================
        //  RENDERIZAR ESTADO DE SUSCRIPCIN
        // =============================================

        function renderSubscriptionStatus(data) {
            console.log(' Renderizando estado de suscripción:', data);

            let html = '';

            // Limpiar intervalo anterior si existe
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }

            // =============================================
            // OBTENER ESTADO REAL
            // =============================================
            const hasActive = data.has_active_subscription;
            const sub = data.subscription;
            const planKey = data.plan.key;
            const planName = data.plan.name;
            const isPremium = planKey === 'premium';
            const isBasico = planKey === 'basico';
            const isFree = planKey === 'free';
            const isExpired = sub && sub.status === 'expired';
            const isPending = sub && sub.status === 'pending';

            console.log(' Estado:', { hasActive, planKey, isExpired, isPending });

            // =============================================
            // CASO 1: SUSCRIPCIN ACTIVA
            // =============================================
            if (hasActive) {
                let statusText = '';
                let statusClass = '';
                let statusIcon = '';
                let showCancel = false;
                let showUpgradeBasico = false;
                let showUpgradePremium = false;
                let showDowngrade = false;
                let countdownHtml = '';
                let expiresAtDate = null;

                if (isPremium) {
                    statusText = ' Premium Activo';
                    statusClass = 'success';
                    statusIcon = 'bi-star-fill';
                    showCancel = true;
                    showDowngrade = true;
                } else if (isBasico) {
                    statusText = ' Básico Activo';
                    statusClass = 'primary';
                    statusIcon = 'bi-credit-card';
                    showCancel = true;
                    showUpgradePremium = true;
                } else if (isFree) {
                    statusText = ' Free Activo';
                    statusClass = 'info';
                    statusIcon = 'bi-gift';
                    showUpgradeBasico = true;
                    showUpgradePremium = true;
                }

                //  CONTADOR REGRESIVO - CUANDO TERMINA LLAMA A debugExpireSubscription() (igual que el botón Cancelar)
                //  CONTADOR REGRESIVO CON FORMATO DE DAS, HORAS Y MINUTOS
                if (sub && sub.expires_at) {
                    expiresAtDate = new Date(sub.expires_at);
                    const now = new Date();
                    const diffMs = expiresAtDate - now;

                    if (diffMs > 0) {
                        // Calcular das, horas, minutos
                        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                        const diffHours = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

                        let timeStr = '';
                        if (diffDays > 0) {
                            timeStr = `${diffDays}d ${diffHours}h ${diffMinutes}m`;
                        } else if (diffHours > 0) {
                            timeStr = `${diffHours}h ${diffMinutes}m`;
                        } else {
                            timeStr = `${diffMinutes}m`;
                        }

                        const isExpiring = diffDays === 0 && diffHours === 0 && diffMinutes < 5;

                        countdownHtml = `
                                                                                                                        <div class="mt-1">
                                                                                                                            <span class="countdown-timer ${isExpiring ? 'expiring' : ''}" id="countdownDisplay">
                                                                                                                                 ${timeStr}
                                                                                                                            </span>
                                                                                                                            <small class="text-muted ms-2">tiempo restante</small>
                                                                                                                        </div>
                                                                                                                    `;

                        //  INICIAR CONTADOR CON VERIFICACIN DE EXPIRACIN (actualiza cada minuto)
                        countdownInterval = setInterval(function () {
                            const now2 = new Date();
                            const diffMs2 = expiresAtDate - now2;

                            if (diffMs2 <= 0) {
                                clearInterval(countdownInterval);

                                showAlert(' Tu suscripción ha expirado. Volviendo al plan Free.', 'warning');

                                setTimeout(function () {
                                    debugExpireSubscription();
                                }, 500);
                                return;
                            }

                            // Recalcular das, horas, minutos
                            const diffDays2 = Math.floor(diffMs2 / (1000 * 60 * 60 * 24));
                            const diffHours2 = Math.floor((diffMs2 % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const diffMinutes2 = Math.floor((diffMs2 % (1000 * 60 * 60)) / (1000 * 60));

                            let timeStr2 = '';
                            if (diffDays2 > 0) {
                                timeStr2 = `${diffDays2}d ${diffHours2}h ${diffMinutes2}m`;
                            } else if (diffHours2 > 0) {
                                timeStr2 = `${diffHours2}h ${diffMinutes2}m`;
                            } else {
                                timeStr2 = `${diffMinutes2}m`;
                            }

                            const display = $('#countdownDisplay');
                            if (display.length) {
                                display.text(` ${timeStr2}`);
                                if (diffDays2 === 0 && diffHours2 === 0 && diffMinutes2 < 5) {
                                    display.addClass('expiring');
                                } else {
                                    display.removeClass('expiring');
                                }
                            }
                        }, 60000); //  Actualizar cada minuto (60000 ms) en lugar de cada segundo
                    } else {
                        //  Si ya expir, ejecutar inmediatamente
                        setTimeout(function () {
                            debugExpireSubscription();
                        }, 500);
                    }
                }

                const expiresDate = sub && sub.expires_at ? new Date(sub.expires_at).toLocaleDateString('es-ES') : 'No definida';

                html = `
                                                                                                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                                                                                                    <!-- Sleek Header -->
                                                                                                    <div class="p-3 bg-${statusClass} bg-gradient text-white d-flex justify-content-between align-items-center">
                                                                                                        <div class="d-flex align-items-center gap-2">
                                                                                                            <i class="bi ${statusIcon} fs-5"></i>
                                                                                                            <h5 class="mb-0 fw-semibold">${statusText}</h5>
                                                                                                        </div>
                                                                                                        <span class="badge bg-white text-${statusClass} px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.85rem;">
                                                                                                            <i class="bi bi-patch-check-fill me-1"></i> Plan ${planName}
                                                                                                        </span>
                                                                                                    </div>

                                                                                                    <div class="card-body p-4 bg-light">
                                                                                                        <div class="row g-4 align-items-center">

                                                                                                            <!-- Left Column: Current Status & Money -->
                                                                                                            <div class="col-lg-5 col-md-6 border-end-md">
                                                                                                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">Resumen Financiero</h6>

                                                                                                                <div class="d-flex flex-column gap-3">
                                                                                                                    <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm">
                                                                                                                        <div class="d-flex flex-column">
                                                                                                                            <span class="text-muted small">Costo del Ciclo Base</span>
                                                                                                                            <strong class="fs-6 text-dark">${planName === 'Premium' ? '$25.000 ARS' : (planName === 'Básico' ? '$10.000 ARS' : 'Sin Costo')}</strong>
                                                                                                                        </div>
                                                                                                                        <i class="bi bi-credit-card-2-front text-${statusClass} fs-3 opacity-50"></i>
                                                                                                                    </div>

                                                                                                                    ${planName === 'Premium' && data.limits?.sensors?.max > 20 ? `
                                                                                                                        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm border-start border-4 border-success">
                                                                                                                            <div class="d-flex flex-column">
                                                                                                                                <span class="text-muted small">Packs Extras x${(data.limits.sensors.max - 20) / 10}</span>
                                                                                                                                <strong class="fs-6 text-success">+$${((data.limits.sensors.max - 20) / 10) * 10000} ARS</strong>
                                                                                                                            </div>
                                                                                                                            <i class="bi bi-cart-plus text-success fs-3 opacity-50"></i>
                                                                                                                        </div>
                                                                                                                    ` : ''}

                                                                                                                    <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm border-start border-4 border-${statusClass}">
                                                                                                                        <div class="d-flex flex-column">
                                                                                                                            <span class="text-muted small">Renovación / Vencimiento</span>
                                                                                                                            <strong class="fs-6 text-dark">${expiresDate}</strong>
                                                                                                                        </div>
                                                                                                                        <i class="bi bi-calendar-check text-${statusClass} fs-3 opacity-50"></i>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>

                                                                                                            <!-- Right Column: Capacity Telemetry -->
                                                                                                            <div class="col-lg-7 col-md-6 ps-lg-4">
                                                                                                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">Telemetría de Capacidad</h6>

                                                                                                                ${data.limits?.sensors ? `
                                                                                                                    <div class="mb-4 bg-white p-3 rounded shadow-sm border-start border-3 border-${data.limits.sensors.used >= data.limits.sensors.max ? 'danger' : 'primary'}">
                                                                                                                        <div class="d-flex justify-content-between mb-2">
                                                                                                                            <span class="small fw-semibold text-dark">Sensores Físicos (Licencias)</span>
                                                                                                                            <span class="small fw-bold badge bg-${data.limits.sensors.used >= data.limits.sensors.max ? 'danger' : 'primary'} px-2 py-1">
                                                                                                                                ${data.limits.sensors.used} / ${data.limits.sensors.max}
                                                                                                                            </span>
                                                                                                                        </div>
                                                                                                                        <div class="progress" style="height: 10px; border-radius: 6px; background-color: #e9ecef;">
                                                                                                                            <div class="progress-bar ${data.limits.sensors.used >= data.limits.sensors.max ? 'bg-danger' : 'bg-primary'}" 
                                                                                                                                 role="progressbar" 
                                                                                                                                 style="width: ${(data.limits.sensors.used / data.limits.sensors.max) * 100}%" 
                                                                                                                                 aria-valuenow="${data.limits.sensors.used}" 
                                                                                                                                 aria-valuemin="0" 
                                                                                                                                 aria-valuemax="${data.limits.sensors.max}">
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                        <div class="text-end mt-2">
                                                                                                                            <small class="text-muted" style="font-size: 0.7rem;">
                                                                                                                                Quedan <span class="fw-bold ${data.limits.sensors.remaining === 0 ? 'text-danger' : 'text-success'}">${data.limits.sensors.remaining}</span> celdas libres
                                                                                                                            </small>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                ` : ''}

                                                                                                                ${data.limits?.groups ? `
                                                                                                                    <div class="mb-2 bg-white p-3 rounded shadow-sm border-start border-3 border-${data.limits.groups.used >= data.limits.groups.max ? 'danger' : 'info'}">
                                                                                                                        <div class="d-flex justify-content-between mb-2">
                                                                                                                            <span class="small fw-semibold text-dark">Lotes Lógicos (Grupos)</span>
                                                                                                                            <span class="small fw-bold badge bg-${data.limits.groups.used >= data.limits.groups.max ? 'danger' : 'info'} text-white px-2 py-1">
                                                                                                                                ${data.limits.groups.used} / ${data.limits.groups.max || ''}
                                                                                                                            </span>
                                                                                                                        </div>
                                                                                                                        ${data.limits.groups.max ? `
                                                                                                                            <div class="progress" style="height: 10px; border-radius: 6px; background-color: #e9ecef;">
                                                                                                                                <div class="progress-bar ${data.limits.groups.used >= data.limits.groups.max ? 'bg-danger' : 'bg-info'}" 
                                                                                                                                     role="progressbar" 
                                                                                                                                     style="width: ${(data.limits.groups.used / data.limits.groups.max) * 100}%" 
                                                                                                                                     aria-valuenow="${data.limits.groups.used}" 
                                                                                                                                     aria-valuemin="0" 
                                                                                                                                     aria-valuemax="${data.limits.groups.max}">
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        ` : `
                                                                                                                            <div class="progress" style="height: 10px; border-radius: 6px; background-color: #e9ecef;">
                                                                                                                                <div class="progress-bar bg-info progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%;"></div>
                                                                                                                            </div>
                                                                                                                        `}
                                                                                                                    </div>
                                                                                                                ` : ''}
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>

                                                                                                    <!-- Actions Footer -->
                                                                                                    <div class="card-footer bg-white p-3 border-top-0 d-flex flex-wrap justify-content-between align-items-center gap-4">

                                                                                                        <!-- Left: Extra Packs Cart or Countdown -->
                                                                                                        <div class="flex-grow-1" style="min-width: 250px;">
                                                                                                            ${planName === 'Premium' ? `
                                                                                                                <div class="input-group shadow-sm">
                                                                                                                    <span class="input-group-text bg-light border-end-0" style="padding-right: 8px;"><i class="bi bi-box-seam text-success"></i></span>
                                                                                                                    <select class="form-select border-start-0 ps-0 text-secondary" id="extraPacksSelect" style="font-size: 0.85rem; cursor: pointer;">
                                                                                                                        <option value="">Añadir Paquetes de Sensores Extra...</option>
                                                                                                                        <option value="1">+10 Pack (+$10,000 ARS)</option>
                                                                                                                        <option value="2">+20 Pack (+$20,000 ARS)</option>
                                                                                                                        <option value="3">+30 Pack (+$30,000 ARS)</option>
                                                                                                                        <option value="4">+40 Pack (+$40,000 ARS)</option>
                                                                                                                        <option value="5">+50 Pack (+$50,000 ARS)</option>
                                                                                                                    </select>
                                                                                                                    <button class="btn btn-success fw-bold px-3 d-flex align-items-center gap-1" onclick="buyExtraPacks()">
                                                                                                                        <i class="bi bi-cart"></i> Comprar
                                                                                                                    </button>
                                                                                                                </div>
                                                                                                            ` : (countdownHtml ? `<div class="bg-light px-3 py-2 rounded shadow-sm d-inline-block border">${countdownHtml}</div>` : '')}
                                                                                                        </div>

                                                                                                        <!-- Right: Lifecycle Controls -->
                                                                                                        <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                                                                                                            ${planName === 'Premium' && countdownHtml ? `
                                                                                                                <div class="me-3 bg-light px-3 py-1 rounded shadow-sm border border-light-subtle d-flex align-items-center">
                                                                                                                    ${countdownHtml}
                                                                                                                </div>
                                                                                                            ` : ''}

                                                                                                            ${showUpgradeBasico ? `
                                                                                                                <button class="btn btn-outline-primary rounded-pill px-4 shadow-sm transition-all text-nowrap" onclick="upgradePlan('basico')">
                                                                                                                    <i class="bi bi-credit-card me-1"></i> Renovar Básico
                                                                                                                </button>
                                                                                                            ` : ''}
                                                                                                            ${showUpgradePremium ? `
                                                                                                                <button class="btn btn-warning rounded-pill px-4 shadow-sm fw-bold text-dark transition-all text-nowrap" onclick="upgradePlan('premium')">
                                                                                                                    <i class="bi bi-star-fill me-1 text-dark"></i> Escalar a Premium
                                                                                                                </button>
                                                                                                            ` : ''}
                                                                                                            ${showDowngrade ? `
                                                                                                                <button class="btn btn-outline-secondary rounded-pill px-3 transition-all text-nowrap" onclick="downgradePlan('basico')">
                                                                                                                    <i class="bi bi-arrow-down-circle me-1"></i> Descender Básico
                                                                                                                </button>
                                                                                                            ` : ''}
                                                                                                            ${showCancel ? `
                                                                                                                <button class="btn btn-light text-danger rounded-pill px-3 transition-all shadow-sm border border-danger-subtle text-nowrap" onclick="cancelSubscription()">
                                                                                                                    <i class="bi bi-x-circle me-1"></i> Desactivar
                                                                                                                </button>
                                                                                                            ` : ''}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            `;

                // =============================================
                // CASO 2: PAGO PENDIENTE
                // =============================================
            } else if (isPending) {
                html = `
                                                                                                                <div class="card border-warning">
                                                                                                                    <div class="card-header bg-warning text-dark">
                                                                                                                        <i class="bi bi-hourglass-split me-2"></i>
                                                                                                                        <strong>Pago pendiente de confirmación</strong>
                                                                                                                    </div>
                                                                                                                    <div class="card-body">
                                                                                                                        <p class="mb-0 text-muted">
                                                                                                                            Tu pago est siendo procesado. Esto puede tomar unos minutos.
                                                                                                                            <br>
                                                                                                                            <small>Si el problema persiste, contacta con soporte.</small>
                                                                                                                        </p>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            `;

                // =============================================
                // CASO 3: SUSCRIPCIN EXPIRADA
                // =============================================
            } else if (isExpired) {
                html = `
                                                                                                                <div class="card border-danger">
                                                                                                                    <div class="card-header bg-danger text-white">
                                                                                                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                                                                                                        <strong>Suscripción expirada</strong>
                                                                                                                    </div>
                                                                                                                    <div class="card-body">
                                                                                                                        <div class="row align-items-center">
                                                                                                                            <div class="col-md-7">
                                                                                                                                <p class="mb-0">
                                                                                                                                    Tu suscripción <strong>${planName}</strong> ha expirado.
                                                                                                                                    <br>
                                                                                                                                    <small class="text-muted">Renueva para seguir disfrutando de los beneficios.</small>
                                                                                                                                </p>
                                                                                                                            </div>
                                                                                                                            <div class="col-md-5 mt-2 mt-md-0">
                                                                                                                                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                                                                                                                    <button class="btn btn-primary btn-sm" onclick="upgradePlan('basico')">
                                                                                                                                        <i class="bi bi-credit-card me-1"></i> Plan Básico ($10 ARS)
                                                                                                                                    </button>
                                                                                                                                    <button class="btn btn-warning btn-sm" onclick="upgradePlan('premium')">
                                                                                                                                        <i class="bi bi-star me-1"></i> Plan Premium ($25 ARS)
                                                                                                                                    </button>
                                                                                                                                    @if(app()->environment('local'))
                                                                                                                                        <button class="btn btn-secondary btn-sm" onclick="debugActivateSubscription('free')">
                                                                                                                                            <i class="bi bi-gift me-1"></i> Emular Free
                                                                                                                                        </button>
                                                                                                                                    @endif
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            `;

                // =============================================
                // CASO 4: SIN SUSCRIPCIN ACTIVA
                // =============================================
            } else {
                html = `
                                                                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">

                                                                        <!-- Header Free -->
                                                                        <div class="card-header border-0 text-white p-3 d-flex justify-content-between align-items-center" 
                                                                             style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                                                                            <div class="d-flex align-items-center gap-2">
                                                                                <i class="bi bi-gift-fill fs-4"></i>
                                                                                <div class="d-flex flex-column">
                                                                                    <h5 class="mb-0 fw-bold">Plan Free (Licencia Gratuita)</h5>
                                                                                    <span class="opacity-75" style="font-size: 0.8rem;">Estás usando funcionalidades limitadas bsicas</span>
                                                                                </div>
                                                                            </div>
                                                                            <span class="badge bg-white text-secondary rounded-pill px-3 py-2 shadow-sm fw-bold">
                                                                                Sin Costo Mensual
                                                                            </span>
                                                                        </div>

                                                                        <!-- Panel de Datos -->
                                                                        <div class="card-body bg-light p-0">
                                                                            <div class="row g-0">

                                                                                <!-- Columna Izquierda: Información Financiera (Vaca en Free) & CTA -->
                                                                                <div class="col-lg-5 col-md-6 border-end border-light-subtle bg-white h-100 p-4">
                                                                                    <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">Estado de Licencia</h6>

                                                                                    <div class="d-flex flex-column gap-3 mb-4">
                                                                                        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm border-start border-4 border-secondary">
                                                                                            <div class="d-flex flex-column">
                                                                                                <span class="text-muted small">Costo del Ciclo Base</span>
                                                                                                <strong class="fs-6 text-dark">$0 ARS</strong>
                                                                                            </div>
                                                                                            <i class="bi bi-wallet2 text-secondary fs-3 opacity-50"></i>
                                                                                        </div>
                                                                                    </div>

                                                                                    <p class="text-muted small mb-0">Esta licencia restringe el acceso masivo a colaboracin y limita el registro de sensores. Realiza un Upgrade a Premium o Básico para liberar tu entorno.</p>
                                                                                </div>

                                                                                <!-- Columna Derecha: Telemetry -->
                                                                                <div class="col-lg-7 col-md-6 bg-white h-100 p-4">
                                                                                    <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">Telemetría de Capacidad</h6>

                                                                                    ${data.limits?.sensors ? `
                                                                                        <div class="mb-4 bg-white p-3 rounded shadow-sm border-start border-3 border-${data.limits.sensors.used >= data.limits.sensors.max ? 'danger' : 'secondary'}">
                                                                                            <div class="d-flex justify-content-between mb-2">
                                                                                                <span class="small fw-semibold text-dark">Sensores Físicos (Licencias)</span>
                                                                                                <span class="small fw-bold badge bg-${data.limits.sensors.used >= data.limits.sensors.max ? 'danger' : 'secondary'} px-2 py-1">
                                                                                                    ${data.limits.sensors.used} / ${data.limits.sensors.max}
                                                                                                </span>
                                                                                            </div>
                                                                                            <div class="progress" style="height: 10px; border-radius: 6px; background-color: #e9ecef;">
                                                                                                <div class="progress-bar ${data.limits.sensors.used >= data.limits.sensors.max ? 'bg-danger' : 'bg-secondary'}" 
                                                                                                     role="progressbar" 
                                                                                                     style="width: ${(data.limits.sensors.used / data.limits.sensors.max) * 100}%">
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="text-end mt-2">
                                                                                                <small class="text-muted" style="font-size: 0.7rem;">Quedan <span class="fw-bold ${data.limits.sensors.remaining === 0 ? 'text-danger' : 'text-success'}">${data.limits.sensors.remaining}</span> celdas libres</small>
                                                                                            </div>
                                                                                        </div>
                                                                                    ` : ''}

                                                                                    ${data.limits?.groups ? `
                                                                                        <div class="mb-2 bg-white p-3 rounded shadow-sm border-start border-3 border-${data.limits.groups.used >= data.limits.groups.max ? 'danger' : 'secondary'}">
                                                                                            <div class="d-flex justify-content-between mb-2">
                                                                                                <span class="small fw-semibold text-dark">Lotes Lógicos (Grupos)</span>
                                                                                                <span class="small fw-bold badge bg-${data.limits.groups.used >= data.limits.groups.max ? 'danger' : 'secondary'} px-2 py-1">
                                                                                                    ${data.limits.groups.used} / ${data.limits.groups.max}
                                                                                                </span>
                                                                                            </div>
                                                                                            <div class="progress" style="height: 10px; border-radius: 6px; background-color: #e9ecef;">
                                                                                                <div class="progress-bar ${data.limits.groups.used >= data.limits.groups.max ? 'bg-danger' : 'bg-secondary'}" 
                                                                                                     role="progressbar" 
                                                                                                     style="width: ${(data.limits.groups.used / data.limits.groups.max) * 100}%">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    ` : ''}
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Footer Upgrades -->
                                                                        <div class="card-footer bg-light p-3 border-top d-flex gap-2 justify-content-center flex-wrap">
                                                                            <button class="btn btn-primary rounded-pill px-4 shadow-sm transition-all" onclick="upgradePlan('basico')">
                                                                                <i class="bi bi-credit-card me-1"></i> Subir a Básico ($10 ARS)
                                                                            </button>
                                                                            <button class="btn btn-warning rounded-pill px-4 shadow-sm fw-bold text-dark transition-all" onclick="upgradePlan('premium')">
                                                                                <i class="bi bi-star-fill me-1 text-dark"></i> Escalar a Premium ($25 ARS)
                                                                            </button>
                                                                            @if(app()->environment('local'))
                                                                                <button class="btn btn-outline-secondary rounded-pill px-3 transition-all" onclick="debugActivateSubscription('free')">
                                                                                    <i class="bi bi-bug me-1"></i> Restablecer Free
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                `;
            }

            $('#subscriptionStatus').html(html);

            //  ACTUALIZAR BADGE DEL HEADER
            updateHeaderBadge(data);
        }

        //  FUNCIN PARA ACTUALIZAR EL BADGE DEL HEADER
        function updateHeaderBadge(data) {
            const planKey = data.plan.key;
            const hasActive = data.has_active_subscription;
            const planName = data.plan.name;
            const badge = document.querySelector('.subscription-badge');
            if (!badge) return;

            let icon = 'bi-hourglass-split';
            let className = 'free';
            let label = 'Gratuito';
            let dotClass = 'expired';

            //  Si est activo, mostrar segn el plan
            if (hasActive) {
                if (planKey === 'premium') {
                    icon = 'bi-star-fill';
                    className = 'premium';
                    label = 'Premium';
                    dotClass = 'active';
                } else if (planKey === 'basico') {
                    icon = 'bi-credit-card';
                    className = 'basico';
                    label = 'Básico';
                    dotClass = 'active';
                } else {
                    icon = 'bi-gift';
                    className = 'free';
                    label = 'Free';
                    dotClass = 'active';
                }
            } else {
                //  Si NO est activo, mostrar segn el plan del usuario
                if (planKey === 'free') {
                    icon = 'bi-gift';
                    className = 'free';
                    label = 'Free';
                    dotClass = 'expired'; // Mantener el punto rojo para indicar que no hay suscripción activa
                } else if (planKey === 'basico' || planKey === 'premium') {
                    // Si tiene un plan pago pero no est activo (expirado)
                    icon = 'bi-exclamation-triangle';
                    className = 'expired';
                    label = planKey === 'premium' ? 'Premium (Expirado)' : 'Básico (Expirado)';
                    dotClass = 'expired';
                } else {
                    icon = 'bi-exclamation-triangle';
                    className = 'expired';
                    label = 'Sin suscripción';
                    dotClass = 'expired';
                }
            }

            badge.className = `subscription-badge ${className}`;
            badge.innerHTML = `
                                                                                                            <span class="badge-dot ${dotClass}"></span>
                                                                                                            <i class="bi ${icon}"></i>
                                                                                                            ${label}
                                                                                                        `;
        }

        function renderSubscriptionError() {
            $('#subscriptionStatus').html(`
                                                                                                            <div class="alert alert-danger">
                                                                                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                                                                                <strong>Error al cargar el estado de la suscripción.</strong>
                                                                                                                <br>
                                                                                                                <small class="text-muted">Intenta recargar la página. Si el problema persiste, contacta con soporte.</small>
                                                                                                                <br>
                                                                                                                <button class="btn btn-sm btn-outline-danger mt-2" onclick="loadSubscriptionStatus()">
                                                                                                                    <i class="bi bi-arrow-repeat me-1"></i> Reintentar
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        `);
        }

        // =============================================
        //  FUNCIONES DE CARGA DE DATOS
        // =============================================

        function loadSubscriptionStatus() {
            const token = localStorage.getItem('token');
            if (!token) return;

            //  Obtener plan anterior del input oculto
            const previousPlanInput = document.getElementById('previousPlanValue');
            if (previousPlanInput && previousPlanInput.value) {
                localStorage.setItem('previous_plan', previousPlanInput.value);
            }

            $.ajax({
                url: '/api/subscription/plan/status',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                cache: false,
                success: function (response) {
                    if (response.success) {
                        //  Si hay un plan anterior en localStorage, pasarlo a los datos
                        const previousPlan = localStorage.getItem('previous_plan');
                        if (previousPlan) {
                            response.data.previous_plan = previousPlan;
                        }

                        renderSubscriptionStatus(response.data);
                        updateAccountInfo();
                        updateHeaderBadge(response.data);

                        //  Actualizar badge de downgrade
                        if (typeof updateDowngradeBadge === 'function') {
                            updateDowngradeBadge(response.data);
                        }
                    } else {
                        renderSubscriptionError();
                    }
                },
                error: function (xhr) {
                    console.error('Error al cargar estado de suscripción:', xhr);
                    renderSubscriptionError();
                }
            });
        }

        function loadStats() {
            $('#totalSensors').text('...');
            $('#totalMeasurements').text('...');
            $('#totalGroups').text('...');

            $.ajax({
                url: '/api/profile/stats',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                success: function (response) {
                    if (response.success) {
                        const stats = response.data;
                        $('#totalSensors').text(stats.total_sensors);
                        $('#totalMeasurements').text(stats.total_measurements);
                        $('#totalGroups').text(stats.total_groups);
                    }
                },
                error: function (xhr) {
                    console.error('Error al cargar estadísticas:', xhr);
                }
            });
        }

        function loadProfile() {
            $.ajax({
                url: '/api/profile',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                cache: false,
                success: function (response) {
                    if (response.success) {
                        const user = response.data.user;
                        const subscription = response.data.subscription;

                        $('#name').val(user.name || '');
                        $('#email').val(user.email || '');
                        $('#userId').text(user.id || '-');

                        //  Mostrar el plan REAL (si es free, mostrar "Free")
                        let planDisplay = 'Free';
                        if (subscription && subscription.plan) {
                            const planKey = subscription.plan.key || subscription.plan;
                            if (planKey === 'premium') planDisplay = 'Premium';
                            else if (planKey === 'basico') planDisplay = 'Básico';
                            else if (planKey === 'free') planDisplay = 'Free';
                        } else {
                            // Si no hay suscripción, usar el plan del usuario
                            const userPlan = user.subscription_plan || 'free';
                            planDisplay = userPlan === 'basico' ? 'Básico' :
                                userPlan === 'premium' ? 'Premium' :
                                    userPlan === 'free' ? 'Free' : 'Free';
                        }
                        $('#userPlan').text(planDisplay);

                        $('#userCreatedAt').text(user.created_at ? new Date(user.created_at).toLocaleString('es-ES') : '-');
                        $('#userUpdatedAt').text(user.updated_at ? new Date(user.updated_at).toLocaleString('es-ES') : '-');

                        let rolesText = 'Sin roles';
                        if (user.roles) {
                            if (Array.isArray(user.roles)) {
                                rolesText = user.roles.join(', ');
                            } else if (typeof user.roles === 'string') {
                                rolesText = user.roles;
                            } else if (typeof user.roles === 'object') {
                                rolesText = Object.values(user.roles).join(', ');
                            }
                        }
                        $('#userRole').html(`<i class="fas fa-id-badge"></i> ${rolesText}`);

                        if (user.google_id) {
                            $('#passwordFields').addClass('d-none');
                            $('#googleInfo').removeClass('d-none');
                            $('#hasGoogleId').val('true');
                        } else {
                            $('#passwordFields').removeClass('d-none');
                            $('#googleInfo').addClass('d-none');
                            $('#hasGoogleId').val('false');
                        }
                    } else {
                        showAlert(response.message || 'Error al cargar el perfil', 'danger');
                    }
                },
                error: function (xhr) {
                    showAlert('Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                }
            });
        }

        function updateAccountInfo() {
            const token = localStorage.getItem('token');
            if (!token) return;

            $.ajax({
                url: '/api/profile',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                cache: false, //  Evitar cach
                success: function (response) {
                    if (response.success && response.data) {
                        const user = response.data.user;
                        const subscription = response.data.subscription;

                        $('#userId').text(user.id || '-');
                        $('#userCreatedAt').text(user.created_at ? new Date(user.created_at).toLocaleDateString('es-ES') : '-');
                        $('#userUpdatedAt').text(user.updated_at ? new Date(user.updated_at).toLocaleDateString('es-ES') : '-');

                        //  Mostrar el plan REAL desde la suscripción
                        let planDisplay = 'Free';
                        if (subscription && subscription.plan) {
                            const planKey = subscription.plan.key || subscription.plan;
                            if (planKey === 'premium') planDisplay = 'Premium';
                            else if (planKey === 'basico') planDisplay = 'Básico';
                            else if (planKey === 'free') planDisplay = 'Free';
                        } else {
                            // Si no hay suscripción, usar el plan del usuario
                            planDisplay = user.subscription_plan === 'basico' ? 'Básico' :
                                user.subscription_plan === 'premium' ? 'Premium' :
                                    user.subscription_plan === 'free' ? 'Free' : 'Free';
                        }
                        $('#userPlan').text(planDisplay);
                    }
                },
                error: function (xhr) {
                    console.error('Error al cargar información de la cuenta:', xhr.status, xhr.statusText);
                }
            });
        }

        function saveProfile(e) {
            e.preventDefault();

            const formData = {
                name: $('#name').val(),
                email: $('#email').val(),
            };

            const passwordField = $('#password');
            const passwordConfField = $('#password_confirmation');

            if (passwordField.length > 0 && !passwordField.closest('#passwordFields').hasClass('d-none')) {
                const password = passwordField.val();
                const passwordConf = passwordConfField.val();

                if ((password && !passwordConf) || (!password && passwordConf)) {
                    showAlert('Debes completar ambos campos de contraseña o dejarlos vacos.', 'danger');
                    return;
                }

                if (password && passwordConf) {
                    if (password !== passwordConf) {
                        showAlert('Las contraseñas no coinciden.', 'danger');
                        return;
                    }

                    if (password.length < 8) {
                        showAlert('La contraseña debe tener al menos 8 caracteres.', 'danger');
                        return;
                    }

                    formData.password = password;
                    formData.password_confirmation = passwordConf;
                }
            }

            $.ajax({
                url: '/api/profile',
                type: 'PUT',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify(formData),
                beforeSend: function () {
                    $('#saveProfileBtn').prop('disabled', true).html(`
                                                                                                                    <span class="spinner-border spinner-border-sm" role="status"></span> Guardando...
                                                                                                                `);
                },
                success: function (response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        loadProfile();
                        loadStats();
                        loadSubscriptionStatus();
                        if (passwordField.length > 0) {
                            passwordField.val('');
                            passwordConfField.val('');
                        }
                    } else {
                        showAlert(response.message || 'Error al guardar', 'danger');
                    }
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors;
                    let message = xhr.responseJSON?.message || 'Error al guardar';
                    if (errors) {
                        message = Object.values(errors).flat().join('<br>');
                    }
                    showAlert(message, 'danger');
                },
                complete: function () {
                    $('#saveProfileBtn').prop('disabled', false).html(`
                                                                                                                    <i class="fas fa-save"></i> Guardar cambios
                                                                                                                `);
                }
            });
        }

        function deleteAllUserData(token) {
            $.ajax({
                url: '/api/profile/delete-all-data',
                type: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    confirm_token: token
                }),
                success: function (response) {
                    if (response.success) {
                        $('#confirmDeleteAllDataModal').modal('hide');
                        showAlert(response.message, 'success');

                        setTimeout(function () {
                            window.location.href = '/login';
                        }, 3000);
                    } else {
                        showAlert(response.message || 'Error al eliminar datos', 'danger');
                    }
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                    showAlert('Error: ' + errorMessage, 'danger');
                },
                complete: function () {
                    $('#confirmDeleteAllData').prop('disabled', false).html(`
                                                                                                                    <i class="bi bi-trash-fill me-1"></i> S, eliminar todo
                                                                                                                `);
                }
            });
        }

        // ==========================================
        // Comprar Packs Extras de Sensores
        // ==========================================
        window.buyExtraPacks = function () {
            const packs = $('#extraPacksSelect').val();

            if (!packs) {
                // Implementacin de Snackbar flotante en vez del alerta global
                let snackHtml = `
                                                                                                        <div class="toast align-items-center text-white bg-warning border-0 position-fixed top-0 start-50 translate-middle-x mt-4" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 9999;">
                                                                                                          <div class="d-flex">
                                                                                                            <div class="toast-body">
                                                                                                              <i class="bi bi-exclamation-circle me-2"></i> Por favor, selecciona cuntos packs deseas comprar primero.
                                                                                                            </div>
                                                                                                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                                                                                          </div>
                                                                                                        </div>
                                                                                                    `;
                $('body').append(snackHtml);
                let toastEl = $('.toast').last();
                let toast = new bootstrap.Toast(toastEl, { delay: 3500 });
                toast.show();

                toastEl.on('hidden.bs.toast', function () {
                    $(this).remove();
                });
                return;
            }

            if (confirm(`Estás por comprar una expansin de +${packs * 10} sensores por $${packs * 10000} ARS hasta fin de mes. ?Estás seguro?`)) {
                showAlert(' Generando preferencia de pago...', 'info');

                $.ajax({
                    url: '/api/subscription/buy-packs',
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        packs: packs
                    },
                    success: function (response) {
                        if (response.success && response.data.preference_id) {

                            //  BYPASS PARA ENTORNO LOCAL
                            if (response.data.is_local) {
                                showAlert(' [DEV] Packs acreditados localmente en Sandbox. Recargando perfil...', 'success');
                                setTimeout(() => window.location.reload(), 1500);
                                return;
                            }

                            showAlert(' Redirigiendo a Mercado Pago...', 'success');
                            // Inicializar Checkout Pro
                            const devEnv = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
                            const mp = new MercadoPago(response.data.public_key, {
                                locale: 'es-AR'
                            });

                            if (devEnv) {
                                showAlert(' Modo Desarrollo: Simulando redirección. Por favor aprueba en sandbox mercadopago.', 'warning');
                            }

                            mp.checkout({
                                preference: {
                                    id: response.data.preference_id
                                },
                                autoOpen: true,
                            });
                        } else {
                            showAlert('Error: ' + (response.message || 'Respuesta invlida al general link de pago'), 'danger');
                        }
                    },
                    error: function (xhr) {
                        let errorMsg = 'Error procesando solicitud.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showAlert(' ' + errorMsg, 'danger');
                    }
                });
            }
        };

        // =============================================
        //  DOCUMENT READY - INICIALIZACIN
        // =============================================
        $(document).ready(function () {
            let countdownInterval = null;
            let subscriptionCheckInterval = null;

            // Exponer countdownInterval globalmente para renderSubscriptionStatus
            window.countdownInterval = countdownInterval;

            // Cargar datos del perfil
            loadProfile();
            loadStats();
            loadSubscriptionStatus();

            // Configuración de intervalos
            subscriptionCheckInterval = setInterval(function () {
                loadSubscriptionStatus();
            }, 10000);

            // Eventos del formulario
            $('#profileForm').submit(saveProfile);
            $('#refreshStatsBtn').click(function () {
                loadStats();
                loadSubscriptionStatus();
            });

            // Eventos de depuracin (solo local)
            @if(app()->environment('local'))
                $('#debugActivateFree').click(function () {
                    debugActivateSubscription('free');
                });

                $('#debugActivateBasico').click(function () {
                    debugActivateSubscription('basico');
                });

                $('#debugActivatePremium').click(function () {
                    debugActivateSubscription('premium');
                });

                $('#debugExpireNow').click(function () {
                    debugExpireSubscription();
                });

                $('#debugClearSubscriptions').click(function () {
                    debugClearSubscriptions();
                });

                $('#debugCheckStatus').click(function () {
                    loadSubscriptionStatus();
                    showAlert(' Estado actualizado', 'info');
                });
            @endif

            // Funcionalidad para eliminar todos los datos
            $('#deleteAllDataBtn').click(function () {
                $('#confirmDeleteAllDataModal').modal('show');
                $('#confirmationText').val('');
            });

            $('#confirmDeleteAllData').click(function () {
                const confirmationText = $('#confirmationText').val().trim();

                if (confirmationText !== 'ELIMINAR TODO') {
                    showAlert('Debes escribir exactamente "ELIMINAR TODO" para confirmar.', 'danger');
                    return;
                }

                $.ajax({
                    url: '/api/profile/delete-all-data/confirmation-token',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    beforeSend: function () {
                        $('#confirmDeleteAllData').prop('disabled', true).html(`
                                                                                                                        <span class="spinner-border spinner-border-sm" role="status"></span> Procesando...
                                                                                                                    `);
                    },
                    success: function (response) {
                        if (response.success) {
                            deleteAllUserData(response.token);
                        } else {
                            showAlert(response.message || 'Error al generar token', 'danger');
                            $('#confirmDeleteAllData').prop('disabled', false).html(`
                                                                                                                            <i class="bi bi-trash-fill me-1"></i> S, eliminar todo
                                                                                                                        `);
                        }
                    },
                    error: function (xhr) {
                        showAlert('Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                        $('#confirmDeleteAllData').prop('disabled', false).html(`
                                                                                                                        <i class="bi bi-trash-fill me-1"></i> S, eliminar todo
                                                                                                                    `);
                    }
                });
            });

            // Actualizar información de la cuenta
            updateAccountInfo();

            // Escuchar eventos
            $(document).on('workspaceChanged subscriptionUpdated', function () {
                updateAccountInfo();
            });

            // Actualizar cada 30 segundos
            setInterval(updateAccountInfo, 30000);

            // Función para actualizar solo el plan
            window.updatePlanInfo = function () {
                updateAccountInfo();
            };
        });
    </script>
@endpush

