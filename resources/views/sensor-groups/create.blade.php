@extends('layouts.modern')

@section('title', 'Crear Grupo de Sensores - MeasureFlow')

@section('content')
<!-- Incluir el archivo CSS externo -->
<link rel="stylesheet" href="{{ asset('css/sensor-groups-create-styles.css') }}">

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-folder btn-icon"></i> Crear Grupo de Sensores</h4>
                </div>
                <div class="card-body">
                    <!-- ✅ Mensaje de información mejorado -->
                    @if(session('info'))
                        <div class="alert alert-{{ session('alert_type', 'warning') }} alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-{{ session('alert_type') === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill' }} fs-3 me-3"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading">{{ session('alert_title', 'Información') }}</h5>
                                    <p class="mb-0">{{ session('info') }}</p>
                                    <hr>
                                    <p class="mb-0 small text-muted">
                                        <i class="bi bi-lightbulb"></i> 
                                        <strong>Consejo:</strong> Un grupo de sensores te permite organizar tus sensores por proyectos, 
                                        ubicaciones o tipos de medición. Por ejemplo: "Sensores Planta Norte", "Mediciones de Agua", etc.
                                    </p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Mensaje de éxito (inicialmente oculto) -->
                    <div id="successAlert" class="alert alert-success d-none" role="alert">
                        <i class="bi bi-check-circle"></i> Grupo de sensores creado correctamente.
                    </div>

                    <form id="sensorGroupForm">
                        <div class="mb-3">
                            <label for="groupName" class="form-label">Nombre del Grupo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="groupName" required>
                            <div class="form-text">Ej: "Sensores Planta Norte", "Mediciones de Agua", "Proyecto X"</div>
                        </div>

                        <div class="mb-3">
                            <label for="groupDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="groupDescription" rows="2" placeholder="Describe el propósito de este grupo..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="templateId" class="form-label">Plantilla <span class="text-danger">*</span></label>
                            <select class="form-select" id="templateId" required>
                                <option value="" selected disabled>Selecciona una plantilla...</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}">
                                        {{ $template->name }} ({{ $template->type }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">La plantilla define los campos que tendrán las mediciones de este grupo.</div>
                            <div class="template-info mt-2 d-none" id="templateInfo">
                                <strong>Información de la plantilla:</strong>
                                <p id="templateDescriptionText"></p>
                                <div class="field-preview">
                                    <strong>Campos:</strong>
                                    <ul id="templateFieldsList"></ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('sensor-groups.index') }}" class="btn btn-secondary me-2">
                                <i class="bi bi-arrow-left btn-icon"></i> Cancelar
                            </a>
                            <button type="button" class="btn btn-primary" id="saveSensorGroup">
                                <i class="bi bi-check-circle btn-icon"></i> Crear Grupo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Cargar información de la plantilla al seleccionar
    $('#templateId').change(function() {
        const templateId = $(this).val();
        if (!templateId) {
            $('#templateInfo').addClass('d-none');
            return;
        }

        // Buscar la plantilla seleccionada
        const template = @json($templates->toArray());
        const selectedTemplate = template.find(t => t.id == templateId);

        if (selectedTemplate) {
            $('#templateDescriptionText').text(selectedTemplate.description || 'Sin descripción');
            const fieldsList = $('#templateFieldsList');
            fieldsList.empty();

            selectedTemplate.schema.campos.forEach(campo => {
                let fieldInfo = `
                    <li>
                        <strong>${campo.nombre}</strong> (${campo.tipo})
                `;
                if (campo.unidad) {
                    fieldInfo += ` - Unidad: ${campo.unidad}`;
                }
                fieldInfo += campo.requerido ? ' <span class="text-danger">(Requerido)</span>' : ' (Opcional)';
                fieldInfo += `</li>`;
                fieldsList.append(fieldInfo);
            });

            $('#templateInfo').removeClass('d-none');
        }
    });

    // Guardar el grupo
    $('#saveSensorGroup').click(async function() {
        const name = $('#groupName').val();
        const description = $('#groupDescription').val();
        const templateId = $('#templateId').val();

        if (!name || !templateId) {
            showAlert('Los campos obligatorios deben completarse', 'danger');
            return;
        }

        try {
            const response = await $.ajax({
                url: '/api/sensor-groups',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    name: name,
                    description: description,
                    template_id: templateId
                })
            });

            if (response.success) {
                // Mostrar mensaje de éxito
                $('#successAlert').removeClass('d-none');
                // Redirigir a sensor-groups después de 2 segundos
                setTimeout(function() {
                    window.location.href = '/sensor-groups';
                }, 2000);
            } else {
                showAlert(response.message || 'Error al crear el grupo', 'danger');
            }
        } catch (error) {
            showAlert('Error al crear el grupo: ' + (error.responseJSON?.message || error.message), 'danger');
        }
    });
});

// Función para mostrar alertas
function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    $('.card-body').prepend(alertHtml);
}
</script>
@endpush