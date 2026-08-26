@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark"><i class="bi bi-receipt text-primary me-2"></i> Facturación Paralela (SuperAdmin)
            </h2>
            <div>
                <a href="{{ route('superadmin.users') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>
                    Volver a Usuarios</a>
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
                <h5 class="mb-0">Historial de Facturas Emitidas Manualmente</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nº Doc</th>
                                <th>Emisión</th>
                                <th>Cliente</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                                <tr>
                                    <td class="fw-bold">#{{ str_pad($inv->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $inv->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $inv->user->name ?? 'Usuario Eliminado' }}<br>
                                        <small class="text-muted">{{ $inv->user->email ?? '' }}</small>
                                    </td>
                                    <td>{{ $inv->description }}</td>
                                    <td class="fw-bold text-success">${{ number_format($inv->amount, 2, ',', '.') }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $inv->status == 'pagada' ? 'bg-success' : ($inv->status == 'pendiente' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ strtoupper($inv->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <!-- Cambiar Estado -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#statusModal{{ $inv->id }}" title="Cambiar Estado">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        <!-- Descargar PDF si existe -->
                                        @if($inv->file_path)
                                            <a href="{{ asset($inv->file_path) }}" target="_blank"
                                                class="btn btn-sm btn-outline-info" title="Ver Factura PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        @endif
                                        <!-- Enviar Email -->
                                        <form action="{{ route('superadmin.invoices.resend', $inv->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('¿Reenviar esta factura al correo del cliente?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success"
                                                title="Reenviar Aviso al Cliente"><i class="bi bi-envelope"></i></button>
                                        </form>
                                        <!-- Eliminar -->
                                        <form action="{{ route('superadmin.invoices.delete', $inv->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('¿Seguro de eliminar este registro de factura? El PDF no se borrará del disco local.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                title="Borrar Registro"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Estado -->
                                <div class="modal fade" id="statusModal{{ $inv->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-sm modal-dialog-centered">
                                        <form action="{{ route('superadmin.invoices.status', $inv->id) }}" method="POST"
                                            class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Cambiar Estado</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <select name="status" class="form-select mt-2">
                                                    <option value="pendiente" {{ $inv->status == 'pendiente' ? 'selected' : '' }}>
                                                        Pendiente de Pago</option>
                                                    <option value="pagada" {{ $inv->status == 'pagada' ? 'selected' : '' }}>Pagada
                                                    </option>
                                                    <option value="anulada" {{ $inv->status == 'anulada' ? 'selected' : '' }}>
                                                        Anulada</option>
                                                </select>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary btn-sm">Actualizar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No hay facturas manuales registradas en
                                        el sistema.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection