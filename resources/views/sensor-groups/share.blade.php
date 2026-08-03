@extends('layouts.modern')

@section('title', 'Compartir Grupo de Sensores - MeasureFlow')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-people btn-icon"></i> Compartir Grupo de Sensores</h4>
                    <a href="{{ route('sensor-groups.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left btn-icon"></i> Volver a Grupos
                    </a>
                </div>
                <div class="card-body">
                    <input type="hidden" id="groupId" value="">

                    <div class="mb-4">
                        <h5>Grupo: <span id="groupName" class="text-primary"></span></h5>
                        <p id="groupDescription"></p>
                    </div>

                    <!-- Pestañas para usuarios registrados e invitaciones pendientes -->
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="registeredUsersTab" data-bs-toggle="tab" data-bs-target="#registeredUsers" type="button" role="tab">
                                <i class="bi bi-person-check btn-icon"></i> Usuarios Registrados
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pendingInvitationsTab" data-bs-toggle="tab" data-bs-target="#pendingInvitations" type="button" role="tab">
                                <i class="bi bi-envelope btn-icon"></i> Invitaciones Pendientes
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <!-- Pestaña de usuarios registrados -->
                        <div class="tab-pane fade show active" id="registeredUsers" role="tabpanel">
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h5><i class="bi bi-person-plus btn-icon"></i> Invitar Usuario Registrado</h5>
                                        </div>
                                        <div class="card-body">
                                            <form id="shareForm">
                                                <div class="mb-3">
                                                    <label for="userEmail" class="form-label">Correo Electrónico del Usuario <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" id="userEmail" required placeholder="Ej: inspector@empresa.com">
                                                    <div class="form-text">El usuario debe estar registrado en el sistema.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="userRole" class="form-label">Rol <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="userRole" required>
                                                        <option value="inspector" selected>Inspector (puede tomar mediciones)</option>
                                                        <option value="viewer">Visualizador (solo puede ver datos)</option>
                                                    </select>
                                                </div>
                                                <button type="button" class="btn btn-primary" id="shareGroup">
                                                    <i class="bi bi-person-plus btn-icon"></i> Compartir Acceso
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5><i class="bi bi-people btn-icon"></i> Usuarios con Acceso</h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="sharedUsersList">
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle"></i> No hay usuarios con acceso compartido a este grupo.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pestaña de invitaciones pendientes -->
                        <div class="tab-pane fade" id="pendingInvitations" role="tabpanel">
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h5><i class="bi bi-envelope-plus btn-icon"></i> Invitar por Correo</h5>
                                        </div>
                                        <div class="card-body">
                                            <form id="inviteForm">
                                                <div class="mb-3">
                                                    <label for="inviteEmail" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" id="inviteEmail" required placeholder="Ej: nuevo.inspector@empresa.com">
                                                    <div class="form-text">El usuario recibirá una invitación para registrarse y acceder al grupo.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="inviteRole" class="form-label">Rol <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="inviteRole" required>
                                                        <option value="inspector" selected>Inspector (puede tomar mediciones)</option>
                                                        <option value="viewer">Visualizador (solo puede ver datos)</option>
                                                    </select>
                                                </div>
                                                <button type="button" class="btn btn-primary" id="sendInvitation">
                                                    <i class="bi bi-envelope-paper btn-icon"></i> Enviar Invitación
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5><i class="bi bi-envelope-open btn-icon"></i> Invitaciones Pendientes</h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="pendingInvitationsList">
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle"></i> No hay invitaciones pendientes para este grupo.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación de acceso -->
<div class="modal fade" id="removeAccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación de Acceso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar el acceso de este usuario al grupo?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> El usuario ya no podrá acceder a los sensores de este grupo.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveAccess">Eliminar Acceso</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación de invitación -->
<div class="modal fade" id="removeInvitationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación de Invitación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar esta invitación pendiente?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> El usuario no podrá usar esta invitación para registrarse.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveInvitation">Eliminar Invitación</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentGroupId = null;
let currentAccessId = null;
let currentInvitationId = null;
const removeAccessModal = new bootstrap.Modal(document.getElementById('removeAccessModal'));
const removeInvitationModal = new bootstrap.Modal(document.getElementById('removeInvitationModal'));

// Obtener el ID del grupo de la URL
const urlParams = new URLSearchParams(window.location.search);
currentGroupId = urlParams.get('group_id') || window.location.pathname.split('/').pop();

$(document).ready(async function() {
    if (currentGroupId) {
        await Promise.all([
            loadGroupData(currentGroupId),
            loadSharedUsers(currentGroupId),
            loadPendingInvitations(currentGroupId)
        ]);
    }

    // Configurar eventos
    $('#shareGroup').click(shareGroupAccess);
    $('#sendInvitation').click(sendInvitation);
    $('#confirmRemoveAccess').click(removeAccess);
    $('#confirmRemoveInvitation').click(removeInvitation);
});

async function loadGroupData(groupId) {
    try {
        const response = await $.ajax({
            url: `/api/sensor-groups/${groupId}`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            }
        });

        if (!response.success) {
            showAlert(response.message || 'Error al cargar el grupo', 'danger');
            return;
        }

        const group = response.data;
        $('#groupId').val(group.id);
        $('#groupName').text(group.name);
        $('#groupDescription').text(group.description || 'Sin descripción');
    } catch (error) {
        showAlert('Error al cargar el grupo: ' + error.message, 'danger');
    }
}

async function loadSharedUsers(groupId) {
    try {
        const response = await $.ajax({
            url: `/api/sensor-groups/${groupId}/shared-access`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            }
        });

        if (!response.success) {
            showAlert(response.message || 'Error al cargar usuarios compartidos', 'danger');
            return;
        }

        const sharedUsers = response.data;
        const container = $('#sharedUsersList');

        if (!sharedUsers || sharedUsers.length === 0) {
            container.html(`
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay usuarios con acceso compartido a este grupo.
                </div>
            `);
            return;
        }

        container.empty();
        sharedUsers.forEach(access => {
            const user = access.user;
            const roleClass = `role-${access.role}`;
            const roleBadge = `<span class="role-badge ${roleClass}">${access.role}</span>`;

            const userRow = $(`
                <div class="user-row d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${user.name}</strong> (${user.email})
                        ${roleBadge}
                    </div>
                    <button class="btn btn-sm btn-danger remove-access-btn"
                            data-access-id="${access.id}"
                            title="Eliminar acceso">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `);
            container.append(userRow);
        });

        // Configurar eventos para los botones de eliminar
        $('.remove-access-btn').click(function() {
            currentAccessId = $(this).data('access-id');
            removeAccessModal.show();
        });
    } catch (error) {
        showAlert('Error al cargar usuarios compartidos: ' + error.message, 'danger');
    }
}

async function loadPendingInvitations(groupId) {
    try {
        const response = await $.ajax({
            url: `/api/sensor-groups/${groupId}/invitations`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            }
        });

        if (!response.success) {
            showAlert(response.message || 'Error al cargar invitaciones pendientes', 'danger');
            return;
        }

        const invitations = response.data;
        const container = $('#pendingInvitationsList');

        if (!invitations || invitations.length === 0) {
            container.html(`
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay invitaciones pendientes para este grupo.
                </div>
            `);
            return;
        }

        container.empty();
        invitations.forEach(invitation => {
            const statusClass = invitation.used ? 'status-used' : 'status-pending';
            const statusText = invitation.used ? 'Usada' : 'Pendiente';
            const statusBadge = `<span class="status-badge ${statusClass}">${statusText}</span>`;

            const invitationRow = $(`
                <div class="invitation-row d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${invitation.email}</strong>
                        <span class="role-badge role-${invitation.role}">${invitation.role}</span>
                        ${statusBadge}
                    </div>
                    ${!invitation.used ? `
                    <button class="btn btn-sm btn-danger remove-invitation-btn"
                            data-invitation-id="${invitation.id}"
                            title="Eliminar invitación">
                        <i class="bi bi-trash"></i>
                    </button>
                    ` : ''}
                </div>
            `);
            container.append(invitationRow);
        });

        // Configurar eventos para los botones de eliminar
        $('.remove-invitation-btn').click(function() {
            currentInvitationId = $(this).data('invitation-id');
            removeInvitationModal.show();
        });
    } catch (error) {
        showAlert('Error al cargar invitaciones pendientes: ' + error.message, 'danger');
    }
}

async function shareGroupAccess() {
    const groupId = $('#groupId').val();
    const email = $('#userEmail').val();
    const role = $('#userRole').val();

    if (!email || !role) {
        showAlert('Los campos obligatorios deben completarse', 'danger');
        return;
    }

    try {
        // Buscar el usuario por email
        const userResponse = await $.ajax({
            url: `/api/users?email=${email}`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            }
        });

        if (!userResponse.success || !userResponse.data) {
            showAlert('No se encontró un usuario con ese correo electrónico', 'danger');
            return;
        }

        const userId = userResponse.data.id;

        // Compartir acceso
        const shareResponse = await $.ajax({
            url: `/api/sensor-groups/${groupId}/shared-access`,
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                user_id: userId,
                role: role
            })
        });

        if (shareResponse.success) {
            showAlert('Acceso compartido correctamente', 'success');
            $('#userEmail').val('');
            loadSharedUsers(groupId);
        } else {
            showAlert(shareResponse.message || 'Error al compartir acceso', 'danger');
        }
    } catch (error) {
        showAlert('Error al compartir acceso: ' + error.message, 'danger');
    }
}

async function sendInvitation() {
    const groupId = $('#groupId').val();
    const email = $('#inviteEmail').val();
    const role = $('#inviteRole').val();

    if (!email || !role) {
        showAlert('Los campos obligatorios deben completarse', 'danger');
        return;
    }

    try {
        const response = await $.ajax({
            url: `/api/sensor-groups/${groupId}/invitations`,
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                email: email,
                role: role
            })
        });

        if (response.success) {
            showAlert('Invitación enviada correctamente. El usuario podrá registrarse y acceder al grupo.', 'success');
            $('#inviteEmail').val('');
            loadPendingInvitations(groupId);
        } else {
            showAlert(response.message || 'Error al enviar invitación', 'danger');
        }
    } catch (error) {
        showAlert('Error al enviar invitación: ' + error.message, 'danger');
    }
}

async function removeAccess() {
    if (!currentGroupId || !currentAccessId) return;

    try {
        const response = await $.ajax({
            url: `/api/sensor-groups/${currentGroupId}/shared-access/${currentAccessId}`,
            type: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            }
        });

        removeAccessModal.hide();

        if (response.success) {
            showAlert('Acceso eliminado correctamente', 'success');
            loadSharedUsers(currentGroupId);
        } else {
            showAlert(response.message || 'Error al eliminar acceso', 'danger');
        }
    } catch (error) {
        removeAccessModal.hide();
        showAlert('Error al eliminar acceso: ' + error.message, 'danger');
    }
}

async function removeInvitation() {
    if (!currentGroupId || !currentInvitationId) return;

    try {
        const response = await $.ajax({
            url: `/api/sensor-groups/${currentGroupId}/invitations/${currentInvitationId}`,
            type: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            }
        });

        removeInvitationModal.hide();

        if (response.success) {
            showAlert('Invitación eliminada correctamente', 'success');
            loadPendingInvitations(currentGroupId);
        } else {
            showAlert(response.message || 'Error al eliminar invitación', 'danger');
        }
    } catch (error) {
        removeInvitationModal.hide();
        showAlert('Error al eliminar invitación: ' + error.message, 'danger');
    }
}
</script>
@endpush