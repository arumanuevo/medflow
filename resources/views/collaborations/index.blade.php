@extends('layouts.modern')

@section('title', 'Colaboraciones - MedFlow')

@push('styles')
    <style>
        .collaboration-card {
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .collaboration-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .status-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 500;
            line-height: 1.4;
        }

        .status-badge i {
            font-size: 0.65rem;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-active {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-paused {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .role-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.5rem;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background-color: #e9ecef;
            color: #495057;
            font-weight: 500;
            line-height: 1.4;
        }

        .role-badge i {
            font-size: 0.65rem;
        }

        .role-badge.owner {
            background-color: #cfe2ff;
            color: #084298;
        }

        .role-badge.admin {
            background-color: #f8d7da;
            color: #721c24;
        }

        .role-badge.inspector {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .role-badge.consumidor {
            background-color: #d4edda;
            color: #155724;
        }

        .invitation-card {
            border-left: 4px solid #ffc107;
            background-color: #fffef5;
        }

        .collaboration-active {
            border-left: 4px solid #28a745;
            background-color: #f0fff4;
        }

        .collaboration-paused {
            border-left: 4px solid #dc3545;
            background-color: #fff5f5;
        }

        .badge-group {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            align-items: center;
            margin-top: 4px;
        }

        .badge-group .status-badge,
        .badge-group .role-badge {
            font-size: 0.6rem;
            padding: 0.15rem 0.5rem;
        }

        .badge-group .status-badge i,
        .badge-group .role-badge i {
            font-size: 0.6rem;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 8px;
        }

        .action-buttons .btn {
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4><i class="bi bi-people"></i> Colaboraciones</h4>
                        <button class="btn btn-light" id="openInviteModal">
                            <i class="bi bi-person-plus"></i> Invitar Usuario
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="alertContainer"></div>

                        <!-- ============================================= -->
                        <!-- INVITACIONES PENDIENTES QUE RECIBÍ -->
                        <!-- ============================================= -->
                        @if($pendingInvitations->count() > 0)
                            <div class="mb-4">
                                <h5><i class="bi bi-envelope"></i> Invitaciones Pendientes para mí</h5>
                                <p class="text-muted small">Estas son invitaciones que otros usuarios te han enviado.</p>
                                <div class="row">
                                    @foreach($pendingInvitations as $invitation)
                                        <div class="col-md-6 mb-3">
                                            <div class="card invitation-card collaboration-card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <i class="bi bi-building"></i>
                                                                <strong>Espacio de:
                                                                    {{ $invitation->workspace->name ?? $invitation->workspace->email }}</strong>
                                                            </h6>
                                                            <small class="text-muted">
                                                                Invitado por:
                                                                <strong>{{ $invitation->inviter->name ?? 'Desconocido' }}</strong>
                                                            </small>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="bi bi-clock"></i> Expira:
                                                                {{ $invitation->expires_at ? $invitation->expires_at->format('d/m/Y') : 'N/A' }}
                                                            </small>
                                                            <br>
                                                            <div class="badge-group">
                                                                <span class="role-badge {{ $invitation->role }}">
                                                                    <i class="bi bi-person-badge"></i>
                                                                    {{ ucfirst($invitation->role) }}
                                                                </span>
                                                                <span class="status-badge status-pending">
                                                                    <i class="bi bi-hourglass-split"></i> Pendiente
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-1">
                                                            <button class="btn btn-sm btn-success accept-invitation-btn"
                                                                data-token="{{ $invitation->token }}" title="Aceptar invitación">
                                                                <i class="bi bi-check-circle"></i> Aceptar
                                                            </button>
                                                            <button class="btn btn-sm btn-danger reject-invitation-btn"
                                                                data-token="{{ $invitation->token }}" title="Rechazar invitación">
                                                                <i class="bi bi-x-circle"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="bi bi-info-circle"></i>
                                                            Al aceptar, podrás ver los sensores de
                                                            <strong>{{ $invitation->workspace->name ?? 'este espacio' }}</strong>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- ============================================= -->
                        <!-- MIS COLABORADORES (PROPIETARIO) -->
                        <!-- ============================================= -->
                        <h5><i class="bi bi-people-fill"></i> Mis Colaboradores</h5>
                        <p class="text-muted small">Personas que tienen acceso a tu espacio de trabajo.</p>

                        @if($collaborators->count() > 0)
                            <div class="row">
                                @foreach($collaborators as $collaborator)
                                    @php
                                        $cardClass = 'collaboration-active';
                                        if ($collaborator->status === 'pending') {
                                            $cardClass = 'invitation-card';
                                        } elseif ($collaborator->is_paused) {
                                            $cardClass = 'collaboration-paused';
                                        }
                                    @endphp
                                    <div class="col-md-6 mb-3">
                                        <div class="card collaboration-card {{ $cardClass }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <i class="bi bi-person-circle"></i>
                                                            <strong>{{ $collaborator->user->name ?? 'Usuario' }}</strong>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <i class="bi bi-envelope"></i>
                                                            {{ $collaborator->user->email ?? 'Sin email' }}
                                                        </small>
                                                        <br>
                                                        <div class="badge-group">
                                                            <span class="role-badge {{ $collaborator->role }}">
                                                                <i class="bi bi-person-badge"></i>
                                                                {{ ucfirst($collaborator->role) }}
                                                            </span>

                                                            @if($collaborator->status === 'pending')
                                                                <span class="status-badge status-pending">
                                                                    <i class="bi bi-hourglass-split"></i> Pendiente
                                                                </span>
                                                            @elseif($collaborator->is_paused)
                                                                <span class="status-badge status-paused">
                                                                    <i class="bi bi-pause-circle"></i> ⏸️ Pausado
                                                                </span>
                                                            @else
                                                                <span class="status-badge status-active">
                                                                    <i class="bi bi-check-circle-fill"></i> ✅ Activo
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @if($collaborator->status === 'active' && $collaborator->last_active_at)
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="bi bi-clock"></i>
                                                                Última actividad:
                                                                {{ $collaborator->last_active_at->format('d/m/Y H:i') }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- ⭐ ACCIONES DE GESTIÓN --}}
                                                <div class="action-buttons">
                                                    @if($collaborator->status === 'active')
                                                        @if($collaborator->is_paused)
                                                            <button class="btn btn-sm btn-success unpause-collaborator-btn"
                                                                data-id="{{ $collaborator->id }}"
                                                                data-name="{{ $collaborator->user->name ?? 'Usuario' }}"
                                                                title="Reanudar acceso">
                                                                <i class="bi bi-play-circle"></i> Reanudar
                                                            </button>
                                                        @else
                                                            <button class="btn btn-sm btn-warning pause-collaborator-btn"
                                                                data-id="{{ $collaborator->id }}"
                                                                data-name="{{ $collaborator->user->name ?? 'Usuario' }}"
                                                                title="Pausar acceso temporalmente">
                                                                <i class="bi bi-pause-circle"></i> Pausar
                                                            </button>
                                                        @endif
                                                    @endif

                                                    {{-- Cambiar Rol --}}
                                                    @if($collaborator->status === 'active' && !$collaborator->is_paused)
                                                        <div class="dropdown d-inline">
                                                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                                                data-bs-toggle="dropdown">
                                                                <i class="bi bi-person-badge"></i> {{ ucfirst($collaborator->role) }}
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item change-role-btn" href="#"
                                                                        data-id="{{ $collaborator->id }}"
                                                                        data-role="inspector">Inspector</a></li>
                                                                <li><a class="dropdown-item change-role-btn" href="#"
                                                                        data-id="{{ $collaborator->id }}"
                                                                        data-role="admin">Administrador</a></li>
                                                            </ul>
                                                        </div>
                                                    @endif

                                                    {{-- ✅ Ruteo (Fase 38) --}}
                                                    @if($collaborator->status === 'active' && !$collaborator->is_paused)
                                                        <button class="btn btn-sm btn-info assign-routes-btn text-white"
                                                            data-id="{{ $collaborator->id }}" title="Filtrar Sensores Permitidos">
                                                            <i class="bi bi-geo-alt"></i> Asignar Rutas
                                                        </button>
                                                    @endif

                                                    {{-- Eliminar --}}
                                                    <button class="btn btn-sm btn-danger remove-collaborator-btn"
                                                        data-id="{{ $collaborator->id }}"
                                                        data-name="{{ $collaborator->user->name ?? 'este usuario' }}"
                                                        title="Eliminar colaborador">
                                                        <i class="bi bi-trash"></i> Eliminar
                                                    </button>
                                                </div>

                                                @if($collaborator->is_paused)
                                                    <div class="mt-2">
                                                        <small class="text-danger">
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                            Este colaborador tiene el acceso pausado. No puede tomar mediciones hasta
                                                            que lo reanudes.
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-people" style="font-size: 2rem;"></i>
                                <p class="mt-2">No tienes colaboradores aún.</p>
                                <p class="small">Invita a otros usuarios para compartir tu espacio de trabajo.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para invitar usuario -->
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus"></i> Invitar Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="inviteForm">
                        <div class="mb-3">
                            <label for="inviteEmail" class="form-label">Correo electrónico *</label>
                            <input type="email" class="form-control" id="inviteEmail" placeholder="ejemplo@correo.com"
                                required>
                            <small class="text-muted">Si el usuario no está registrado, se creará una cuenta
                                automáticamente.</small>
                        </div>
                        <div class="mb-3">
                            <label for="inviteRole" class="form-label">Rol *</label>
                            <select class="form-select" id="inviteRole" required>
                                <option value="inspector">Inspector - Puede tomar mediciones</option>
                                <option value="admin">Administrador - Control total</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="inviteMessage" class="form-label">Mensaje personalizado</label>
                            <textarea class="form-control" id="inviteMessage" rows="2"
                                placeholder="Escribe un mensaje para el usuario invitado..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="sendInviteBtn">
                        <i class="bi bi-send"></i> Enviar Invitación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="confirmRemoveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar a <strong id="removeCollaboratorName"></strong>?</p>
                    <p class="text-muted small">El usuario perderá acceso a tu espacio de trabajo.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmRemoveBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Asignación de Sensores (Fase 38) -->
    <div class="modal fade" id="assignSensorRoutesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-geo-alt"></i> Asignar Rutas a <span id="assignRoutesCollaboratorName">Usuario</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="restrictAccessSwitch">
                        <label class="form-check-label" for="restrictAccessSwitch">
                            <strong>Restringir acceso a una cuadrilla específica</strong>
                        </label>
                        <small class="d-block text-muted">Si está apagado, el inspector tiene acceso liberado para procesar
                            todos los medidores de tu red.</small>

                        <div class="route-tools-container d-none mt-2 pb-2 border-bottom">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-7">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                        <input type="text" id="routeSensorSearch" class="form-control"
                                            placeholder="Buscar por nombre, lote o grupo...">
                                    </div>
                                </div>
                                <div class="col-md-5 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                        id="selectAllFilteredRoutes"><i class="bi bi-check-all"></i> Todos</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllRoutes"><i
                                            class="bi bi-eraser"></i> Limpiar</button>
                                </div>
                            </div>
                        </div>

                        <div id="sensorsListContainer" class="d-none mt-2"
                            style="max-height: 50vh; overflow-x: hidden; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 6px; padding: 8px;">
                            <!-- Los sensores se rinden vía AJAX -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="saveRoutesBtn">Guardar Asignación</button>
                    </div>
                </div>
            </div>
        </div>

@endsection

    @push('scripts')
        <script>
            $(document).ready(function () {
                let currentCollaboratorId = null;

                // =============================================
                // FUNCIÓN PARA OBTENER EL TOKEN
                // =============================================
                function getToken() {
                    return localStorage.getItem('token') || localStorage.getItem('sanctum_token') || '';
                }

                // =============================================
                // ABRIR MODAL DE INVITACIÓN
                // =============================================
                $('#openInviteModal').click(function () {
                    $('#inviteModal').modal('show');
                });

                // =============================================
                // ENVIAR INVITACIÓN
                // =============================================
                $('#sendInviteBtn').click(function () {
                    const email = $('#inviteEmail').val();
                    const role = $('#inviteRole').val();
                    const message = $('#inviteMessage').val();

                    if (!email || !role) {
                        showAlert('Por favor, completa todos los campos obligatorios.', 'danger');
                        return;
                    }

                    const token = getToken();

                    if (!token) {
                        showAlert('No se encontró token de autenticación.', 'danger');
                        return;
                    }

                    $(this).prop('disabled', true).html(`
                                            <span class="spinner-border spinner-border-sm" role="status"></span> Enviando...
                                        `);

                    $.ajax({
                        url: '/api/collaborations/invite',
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: JSON.stringify({
                            email: email,
                            role: role,
                            message: message
                        }),
                        success: function (response) {
                            if (response.success) {
                                $('#inviteModal').modal('hide');
                                showAlert(response.message, 'success');
                                setTimeout(() => location.reload(), 2000);
                            } else {
                                showAlert(response.message || 'Error al enviar invitación', 'danger');
                            }
                        },
                        error: function (xhr) {
                            let errorMsg = 'Error al enviar invitación';
                            if (xhr.status === 401) {
                                errorMsg = 'Sesión expirada. Por favor, recarga la página.';
                            } else if (xhr.responseJSON?.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            showAlert(errorMsg, 'danger');
                        },
                        complete: function () {
                            $('#sendInviteBtn').prop('disabled', false).html(`
                                                    <i class="bi bi-send"></i> Enviar Invitación
                                                `);
                        }
                    });
                });

                // =============================================
                // ACEPTAR INVITACIÓN
                // =============================================
                $('.accept-invitation-btn').click(function () {
                    const token = $(this).data('token');
                    const btn = $(this);
                    const authToken = getToken();

                    if (!authToken) {
                        showAlert('No se encontró token de autenticación.', 'danger');
                        return;
                    }

                    if (!confirm('¿Aceptar esta invitación?')) return;

                    btn.prop('disabled', true).html(`
                                            <span class="spinner-border spinner-border-sm" role="status"></span>
                                        `);

                    $.ajax({
                        url: `/api/collaborations/accept/${token}`,
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + authToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                showAlert(response.message, 'success');
                                setTimeout(() => location.reload(), 2000);
                            } else {
                                showAlert(response.message || 'Error al aceptar invitación', 'danger');
                            }
                        },
                        error: function (xhr) {
                            if (xhr.status === 401) {
                                showAlert('Sesión expirada. Por favor, recarga la página.', 'danger');
                            } else {
                                showAlert(xhr.responseJSON?.message || 'Error al aceptar invitación', 'danger');
                            }
                        },
                        complete: function () {
                            btn.prop('disabled', false).html(`<i class="bi bi-check-circle"></i>`);
                        }
                    });
                });

                // =============================================
                // RECHAZAR INVITACIÓN
                // =============================================
                $('.reject-invitation-btn').click(function () {
                    const token = $(this).data('token');
                    const btn = $(this);
                    const authToken = getToken();

                    if (!authToken) {
                        showAlert('No se encontró token de autenticación.', 'danger');
                        return;
                    }

                    if (!confirm('¿Rechazar esta invitación?')) return;

                    btn.prop('disabled', true).html(`
                                            <span class="spinner-border spinner-border-sm" role="status"></span>
                                        `);

                    $.ajax({
                        url: `/api/collaborations/reject/${token}`,
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + authToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                showAlert(response.message, 'success');
                                setTimeout(() => location.reload(), 2000);
                            } else {
                                showAlert(response.message || 'Error al rechazar invitación', 'danger');
                            }
                        },
                        error: function (xhr) {
                            if (xhr.status === 401) {
                                showAlert('Sesión expirada. Por favor, recarga la página.', 'danger');
                            } else {
                                showAlert(xhr.responseJSON?.message || 'Error al rechazar invitación', 'danger');
                            }
                        },
                        complete: function () {
                            btn.prop('disabled', false).html(`<i class="bi bi-x-circle"></i>`);
                        }
                    });
                });

                // =============================================
                // PAUSAR COLABORADOR
                // =============================================
                $(document).on('click', '.pause-collaborator-btn', function () {
                    const id = $(this).data('id');
                    const name = $(this).data('name');

                    if (!confirm(`¿Pausar el acceso de "${name}"? Podrás reanudarlo después.`)) return;

                    $.ajax({
                        url: `/api/collaborations/${id}/pause`,
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + getToken(),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        success: function (response) {
                            if (response.success) {
                                showAlert(response.message, 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showAlert(response.message || 'Error al pausar colaborador', 'danger');
                            }
                        },
                        error: function (xhr) {
                            showAlert(xhr.responseJSON?.message || 'Error al pausar colaborador', 'danger');
                        }
                    });
                });

                // =============================================
                // REANUDAR COLABORADOR
                // =============================================
                $(document).on('click', '.unpause-collaborator-btn', function () {
                    const id = $(this).data('id');
                    const name = $(this).data('name');

                    if (!confirm(`¿Reanudar el acceso de "${name}"?`)) return;

                    $.ajax({
                        url: `/api/collaborations/${id}/unpause`,
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + getToken(),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        success: function (response) {
                            if (response.success) {
                                showAlert(response.message, 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showAlert(response.message || 'Error al reanudar colaborador', 'danger');
                            }
                        },
                        error: function (xhr) {
                            showAlert(xhr.responseJSON?.message || 'Error al reanudar colaborador', 'danger');
                        }
                    });
                });

                // =============================================
                // CAMBIAR ROL
                // =============================================
                $(document).on('click', '.change-role-btn', function (e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    const role = $(this).data('role');
                    const roleNames = {
                        'inspector': 'Inspector (puede tomar mediciones)',
                        'consumidor': 'Consumidor (solo ver)',
                        'admin': 'Administrador (control total)'
                    };

                    if (!confirm(`¿Cambiar el rol a "${roleNames[role]}"?`)) return;

                    $.ajax({
                        url: `/api/collaborations/${id}/role`,
                        type: 'PUT',
                        headers: {
                            'Authorization': 'Bearer ' + getToken(),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        data: JSON.stringify({ role: role }),
                        success: function (response) {
                            if (response.success) {
                                showAlert(response.message, 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showAlert(response.message || 'Error al cambiar rol', 'danger');
                            }
                        },
                        error: function (xhr) {
                            showAlert(xhr.responseJSON?.message || 'Error al cambiar rol', 'danger');
                        }
                    });
                });

                // =============================================
                // ELIMINAR COLABORADOR
                // =============================================
                $('.remove-collaborator-btn').click(function () {
                    const id = $(this).data('id');
                    const name = $(this).data('name');
                    const authToken = getToken();

                    if (!authToken) {
                        showAlert('No se encontró token de autenticación.', 'danger');
                        return;
                    }

                    currentCollaboratorId = id;
                    $('#removeCollaboratorName').text(name);
                    $('#confirmRemoveModal').modal('show');
                });

                $('#confirmRemoveBtn').click(function () {
                    if (!currentCollaboratorId) return;

                    const authToken = getToken();

                    $(this).prop('disabled', true).html(`
                                            <span class="spinner-border spinner-border-sm" role="status"></span> Eliminando...
                                        `);

                    $.ajax({
                        url: `/api/collaborations/${currentCollaboratorId}`,
                        type: 'DELETE',
                        headers: {
                            'Authorization': 'Bearer ' + authToken,
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                $('#confirmRemoveModal').modal('hide');
                                showAlert(response.message, 'success');
                                setTimeout(() => location.reload(), 2000);
                            } else {
                                showAlert(response.message || 'Error al eliminar colaborador', 'danger');
                            }
                        },
                        error: function (xhr) {
                            if (xhr.status === 401) {
                                showAlert('Sesión expirada. Por favor, recarga la página.', 'danger');
                            } else {
                                showAlert(xhr.responseJSON?.message || 'Error al eliminar colaborador', 'danger');
                            }
                        },
                        complete: function () {
                            $('#confirmRemoveBtn').prop('disabled', false).html('Eliminar');
                        }
                    });
                });

                // =============================================
                // ALERTAS
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
                    }, 5000);
                }

                // Resetear formulario al cerrar modal
                $('#inviteModal').on('hidden.bs.modal', function () {
                    $('#inviteForm')[0].reset();
                    $('#inviteMessage').val('');
                });

                // =============================================
                // GESTIÓN DE RUTAS / ASIGNACIÓN DE SENSORES (FASE 38)
                // =============================================
                $('.assign-routes-btn').click(function () {
                    currentCollaboratorId = $(this).data('id');
                    const authToken = getToken();

                    if (!authToken) {
                        showAlert('No se encontró token de autenticación.', 'danger');
                        return;
                    }

                    // Bloquear UI del botón mientras carga
                    $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                    $.ajax({
                        url: `/api/collaborations/${currentCollaboratorId}/sensors`,
                        type: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + authToken,
                            'Accept': 'application/json'
                        },
                        success: function (response) {
                            if (response.success) {
                                $('#assignRoutesCollaboratorName').text(response.data.collaborator_name);
                                $('#restrictAccessSwitch').prop('checked', response.data.has_restricted_access);

                                const container = $('#sensorsListContainer');
                                container.empty();

                                if (response.data.available_sensors.length === 0) {
                                    container.html('<p class="text-muted mb-0">No hay sensores disponibles en el espacio.</p>');
                                } else {
                                    response.data.available_sensors.forEach(sensor => {
                                        const isChecked = response.data.assigned_sensor_ids.includes(sensor.id) ? 'checked' : '';
                                        const searchText = `${sensor.nombre.toLowerCase()} ${sensor.identificador ? sensor.identificador.toLowerCase() : ''} ${sensor.grupo.toLowerCase()}`;

                                        container.append(`
                                                        <div class="sensor-route-item d-flex align-items-center" data-search="${searchText}" style="margin-bottom: 2px; border-bottom: 1px solid #f8f9fa; padding: 4px 6px; border-radius: 4px;">
                                                            <div class="form-check m-0 p-0 d-flex align-items-center w-100">
                                                                <input class="form-check-input route-sensor-checkbox m-0 flex-shrink-0" type="checkbox" value="${sensor.id}" id="sensorcheck${sensor.id}" ${isChecked} style="width: 1.1em; height: 1.1em;">
                                                                <label class="form-check-label d-flex justify-content-between align-items-center w-100 ms-2" for="sensorcheck${sensor.id}" style="cursor: pointer; font-size: 0.75rem; line-height: 1.2;">
                                                                    <span class="text-truncate" style="max-width: 60%;"><strong>${sensor.nombre}</strong> <span class="text-muted ms-1">${sensor.identificador || ''}</span></span>
                                                                    <span class="badge bg-secondary flex-shrink-0" style="font-size: 0.65rem;">${sensor.grupo}</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    `);
                                    });
                                }

                                if (response.data.has_restricted_access) {
                                    container.removeClass('d-none');
                                    $('.route-tools-container').removeClass('d-none');
                                } else {
                                    container.addClass('d-none');
                                    $('.route-tools-container').addClass('d-none');
                                }

                                // Reset search field when modal opens
                                $('#routeSensorSearch').val('').trigger('input');
                                $('#assignSensorRoutesModal').modal('show');
                            }
                        },
                        error: function (xhr) {
                            showAlert(xhr.responseJSON?.message || 'Error al obtener sensores', 'danger');
                        },
                        complete: () => {
                            $(this).prop('disabled', false).html('<i class="bi bi-geo-alt"></i> Asignar Rutas');
                        }
                    });
                });

                $('#restrictAccessSwitch').change(function () {
                    if ($(this).is(':checked')) {
                        $('#sensorsListContainer').removeClass('d-none');
                        $('.route-tools-container').removeClass('d-none');
                    } else {
                        $('#sensorsListContainer').addClass('d-none');
                        $('.route-tools-container').addClass('d-none');
                    }
                });

                // Filtro en tiempo real
                $('#routeSensorSearch').on('input', function () {
                    const term = $(this).val().toLowerCase().trim();
                    if (term === '') {
                        $('.sensor-route-item').removeClass('d-none');
                        return;
                    }

                    $('.sensor-route-item').each(function () {
                        const searchData = $(this).data('search');
                        if (searchData.includes(term)) {
                            $(this).removeClass('d-none');
                        } else {
                            $(this).addClass('d-none');
                        }
                    });
                });

                // Seleccionar todos los Visibles
                $('#selectAllFilteredRoutes').click(function () {
                    $('.sensor-route-item:not(.d-none) .route-sensor-checkbox').prop('checked', true);
                });

                // Limpiar toda la tabla
                $('#deselectAllRoutes').click(function () {
                    $('.route-sensor-checkbox').prop('checked', false);
                });

                $('#saveRoutesBtn').click(function () {
                    const authToken = getToken();
                    $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

                    const hasRestrictedAccess = $('#restrictAccessSwitch').is(':checked');
                    const assignedIds = [];

                    if (hasRestrictedAccess) {
                        $('.route-sensor-checkbox:checked').each(function () {
                            assignedIds.push($(this).val());
                        });
                    }

                    $.ajax({
                        url: `/api/collaborations/${currentCollaboratorId}/sensors`,
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + authToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: JSON.stringify({
                            has_restricted_access: hasRestrictedAccess,
                            assigned_ids: assignedIds
                        }),
                        success: function (response) {
                            if (response.success) {
                                $('#assignSensorRoutesModal').modal('hide');
                                showAlert(response.message, 'success');
                            }
                        },
                        error: function (xhr) {
                            showAlert(xhr.responseJSON?.message || 'Error al guardar asignación de sensores.', 'danger');
                        },
                        complete: function () {
                            $('#saveRoutesBtn').prop('disabled', false).html('Guardar Asignación');
                        }
                    });
                });
            });
        </script>
    @endpush