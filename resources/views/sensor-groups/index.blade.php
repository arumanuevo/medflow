@extends('layouts.modern')

@section('title', 'Grupos de Sensores')

@push('styles')
    <style>
        /* ===== ESTILOS ESPECÍFICOS PARA GRUPOS DE SENSORES ===== */

        /* Estilo para las tarjetas de grupos */
        .group-card {
            border-radius: 8px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .group-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
        }

        .group-card .card-body {
            padding: 1.25rem;
        }

        .group-card .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .sensor-count {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .sensor-count i {
            margin-right: 5px;
        }

        .template-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .template-type-agua {
            background-color: #0dcaf0;
            color: #fff;
        }

        .template-type-gas {
            background-color: #ffc107;
            color: #212529;
        }

        .template-type-electricidad {
            background-color: #fd7e14;
            color: #fff;
        }

        .template-type-personalizado {
            background-color: #6c757d;
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .empty-state h4 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .modal-content {
            border-radius: 8px;
        }

        .modal-header {
            border-radius: 8px 8px 0 0;
            padding: 1rem 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-radius: 0 0 8px 8px;
        }

        .btn-custom {
            margin: 0 0.15rem;
        }

        .alert {
            border-radius: 6px;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: none;
        }
    </style>
@endpush

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0 text-white"><i class="bi bi-folder btn-icon"></i> Grupos de Sensores</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('measurements.select-sensor') }}" class="btn btn-success">
                    <i class="bi bi-rulers"></i> Tomar Medición
                </a>
                <a href="{{ route('sensor-groups.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Crear Grupo
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- ✅ Contenedor para el contenido dinámico de JavaScript -->
            <div id="groupsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando grupos...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar eliminación de grupo -->
    <div class="modal fade" id="deleteGroupModal" tabindex="-1" aria-labelledby="deleteGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="deleteGroupModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este grupo? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteGroup">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let allGroups = [];
        let currentGroupId = null;
        const modal = new bootstrap.Modal(document.getElementById('deleteGroupModal'));

        $(document).ready(function () {
            loadGroups();
            $('#confirmDeleteGroup').click(deleteGroup);
        });

        async function loadGroups() {
            const token = localStorage.getItem('token');

            if (!token) {
                $('#groupsContent').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        No se encontró token de autenticación. Por favor, <a href="{{ route('login') }}">inicia sesión</a>.
                    </div>
                `);
                return;
            }

            try {
                const response = await $.ajax({
                    url: '/api/sensor-groups',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });

                if (!response.success) {
                    showAlert(response.message || 'Error al cargar grupos de sensores', 'danger');
                    return;
                }

                allGroups = response.data;
                renderGroups(response.data);
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error al cargar los grupos: ' + (error.responseJSON?.message || error.message), 'danger');
                $('#groupsContent').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Error al cargar los grupos. Intenta nuevamente.
                    </div>
                `);
            }
        }

        function renderGroups(groups) {
            const content = $('#groupsContent');
            let html = '';

            if (!Array.isArray(groups) || groups.length === 0) {
                html = `
                    <div class="empty-state">
                        <i class="bi bi-folder"></i>
                        <h4>No tienes grupos de sensores</h4>
                        <p>Crea tu primer grupo para organizar tus sensores.</p>
                        <a href="{{ route('sensor-groups.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Crear Primer Grupo
                        </a>
                    </div>
                `;
            } else {
                html = `<div class="row">`;

                groups.forEach(group => {
                    let templateBadge = '';
                    if (group.template) {
                        const typeClass = `template-type-${group.template.type}`;
                        templateBadge = `<span class="template-badge ${typeClass}">${group.template.name}</span>`;
                    }

                    html += `
                        <div class="col-md-6 col-xl-4 mb-3">
                            <div class="card group-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">${group.name}</h5>
                                        ${templateBadge}
                                    </div>
                                    <p class="card-text text-muted small">${group.description || 'Sin descripción'}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="sensor-count">
                                                <i class="bi bi-sensors"></i> ${group.sensors_count || group.sensors?.length || 0} sensores
                                            </span>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <a href="/sensor-groups/${group.id}" class="btn btn-sm btn-info btn-custom text-white" title="Ver grupo">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('sensors.create') }}?group_id=${group.id}" class="btn btn-sm btn-primary btn-custom" title="Agregar sensor">
                                                <i class="bi bi-plus-circle"></i>
                                            </a>
                                            <a href="/sensor-groups/${group.id}/edit" class="btn btn-sm btn-warning btn-custom" title="Editar grupo">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="/sensor-groups/${group.id}/share" class="btn btn-sm btn-info btn-custom" title="Compartir grupo">
                                                <i class="bi bi-people"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger btn-custom deleteGroupBtn"
                                                    data-group-id="${group.id}"
                                                    title="Eliminar grupo">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `</div>`;
            }

            content.html(html);

            // Configurar eventos para los botones de eliminar
            $('.deleteGroupBtn').click(function () {
                currentGroupId = $(this).data('group-id');
                modal.show();
            });
        }

        async function deleteGroup() {
            if (!currentGroupId) return;

            const token = localStorage.getItem('token');

            // ✅ Log para depuración
            console.log('🗑️ Eliminando grupo:', {
                groupId: currentGroupId,
                token: token ? 'Token presente' : 'Token NO presente',
                url: `/api/sensor-groups/${currentGroupId}`
            });

            if (!token) {
                showAlert('No se encontró token de autenticación. Por favor, recarga la página.', 'danger');
                modal.hide();
                return;
            }

            try {
                const response = await $.ajax({
                    url: `/api/sensor-groups/${currentGroupId}`,
                    type: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                console.log('✅ Respuesta del servidor:', response);

                modal.hide();

                if (response.success) {
                    showAlert('Grupo eliminado correctamente', 'success');
                    // Recargar la lista sin recargar toda la página
                    loadGroups();
                } else {
                    showAlert(response.message || 'Error al eliminar el grupo', 'danger');
                }
            } catch (error) {
                console.error('❌ Error al eliminar:', error);
                modal.hide();
                const errorMessage = error.responseJSON?.message || error.message || 'Error desconocido';
                showAlert('Error al eliminar el grupo: ' + errorMessage, 'danger');
            }
        }

        // Función para mostrar alertas
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            // Remover alertas anteriores
            $('.alert').remove();
            // Agregar nueva alerta al inicio del card-body
            $('.card-body').prepend(alertHtml);

            // Auto-eliminar después de 5 segundos
            setTimeout(() => {
                $('.alert').fadeOut(500, function () {
                    $(this).remove();
                });
            }, 5000);
        }
    </script>
@endpush