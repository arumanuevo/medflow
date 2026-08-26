@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark"><i class="bi bi-shield-lock text-danger me-2"></i> Panel SuperAdmin</h2>
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
                                            {{ strtoupper($u->subscription_plan) }}
                                        </span>
                                    </td>
                                    <td>{{ $u->sensors_count }}</td>
                                    <td>{{ $u->created_at->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <!-- Cambiar Plan -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#planModal{{ $u->id }}">
                                            <i class="bi bi-stars"></i> Plan
                                        </button>
                                        <!-- Generar Pago -->
                                        <a href="{{ url('/api/subscription/create-preference?plan=premium&type=mensual&email=' . $u->email) }}"
                                            target="_blank" class="btn btn-sm btn-outline-success"
                                            title="Generar Checkout MP para Bás/Prem. Usa Link Wiroos">
                                            <i class="bi bi-cash"></i> Pago
                                        </a>
                                        <!-- Eliminar -->
                                        <form action="{{ route('superadmin.users.delete', $u->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('¿Seguro de eliminar este usuario para siempre? Todos sus lotes y sensores desapareceran.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" {{ $u->email ==
                                                'scastellanoadmin@gmail.com' ? 'disabled' : '' }}><i
                                                    class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal -->
                                <div class="modal fade" id="planModal{{ $u->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-sm modal-dialog-centered">
                                        <form action="{{ route('superadmin.users.plan', $u->id) }}" method="POST"
                                            class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Cambiar Plan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <label>Forzar plan de <b>{{ $u->name }}</b></label>
                                                <select name="subscription_plan" class="form-select mt-2">
                                                    <option value="free" {{ $u->subscription_plan == 'free' ? 'selected' : '' }}>
                                                        Free</option>
                                                    <option value="basico" {{ $u->subscription_plan == 'basico' ? 'selected' : '' }}>Básico</option>
                                                    <option value="premium" {{ $u->subscription_plan == 'premium' ? 'selected' : '' }}>Premium</option>
                                                    <option value="enterprise" {{ $u->subscription_plan == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                                                </select>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary btn-sm">Guardar Cambio</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection