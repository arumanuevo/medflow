@extends('layouts.modern')

@section('title', 'Importar Sensores Masivamente - MedFlow')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/bulk-sensors-import-styles.css') }}">

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4><i class="bi bi-file-earmark-excel btn-icon"></i> Importar Sensores Masivamente</h4>
                        <a href="{{ route('sensor-groups.index') }}" class="btn btn-light">
                            <i class="bi bi-arrow-left btn-icon"></i> Volver a Grupos
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Alertas -->
                        <div id="alertContainer"></div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Formatos de archivo soportados:</strong>
                            <ul class="mb-0 mt-1">
                                <li><strong>Excel (.xlsx):</strong> Compatible con todas las versiones</li>
                                <li><strong>CSV (.csv):</strong> Soporta diferentes codificaciones (UTF-8, ISO-8859-1,
                                    Windows-1252)</li>
                                <li>Delimitadores soportados: <strong>; (punto y coma)</strong> y <strong>, (coma)</strong>
                                </li>
                                <li class="text-muted">Si tienes problemas con caracteres especiales, guarda el archivo como
                                    UTF-8</li>
                            </ul>
                        </div>
                        <!-- Paso 1: Seleccionar Grupo y Archivo -->
                        <div id="step1" class="step">
                            <h5><i class="bi bi-upload btn-icon"></i> Paso 1: Seleccionar Grupo y Subir Archivo</h5>
                            <p class="text-muted">Selecciona el grupo de sensores y sube un archivo <strong>.xlsx</strong> o
                                <strong>.csv</strong> con los datos.</p>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle btn-icon"></i>
                                <strong>Instrucciones:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>El archivo debe contener una columna con el <strong>nombre del sensor</strong>.</li>
                                    <li>El archivo debe contener una columna con el <strong>identificador</strong> (único).
                                    </li>
                                    <li>La <strong>descripción</strong> es opcional.</li>
                                    <li>Puedes agregar <strong>campos adicionales</strong> (Lote, Apellido, Ubicación, etc.)
                                        en el paso 2.</li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <label for="groupSelect" class="form-label">Grupo Destino <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="groupSelect" required>
                                    @if(isset($groups) && $groups->isEmpty())
                                        <option value="" selected disabled>No tienes grupos disponibles (Crea uno primero)
                                        </option>
                                    @else
                                        <option value="" selected disabled>Selecciona un grupo destino...</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" {{ $group->id == ($selectedGroupId ?? null) ? 'selected' : '' }}>
                                                {{ $group->name }} ({{ $group->template->name ?? 'Sin plantilla' }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="form-text">Selecciona el grupo donde se crearán los sensores</div>
                            </div>

                            <div id="groupInfo" class="alert alert-secondary d-none">
                                <i class="bi bi-info-circle"></i>
                                <span id="groupInfoText"></span>
                            </div>

                            <!-- Información de la plantilla del grupo (solo informativa) -->
                            <div id="templateInfo" class="alert alert-light d-none">
                                <h6><i class="bi bi-file-text"></i> Plantilla del grupo: <span
                                        id="templateNameDisplay"></span></h6>
                                <p class="mb-0"><strong>Campos de medición de este grupo:</strong></p>
                                <ul id="templateFieldsList" class="mb-0">
                                    <li class="text-muted"><i class="bi bi-info-circle"></i> Estos campos se usan al tomar
                                        mediciones, no al importar sensores.</li>
                                </ul>
                            </div>

                            <div class="drop-zone" id="dropZone">
                                <i class="bi bi-file-earmark-excel"></i>
                                <p><strong>Arrastra y suelta el archivo aquí</strong> o haz clic para seleccionarlo</p>
                                <p class="text-muted small">(Solo archivos .xlsx o .csv)</p>
                                <input type="file" id="fileInput" accept=".xlsx,.csv" style="display: none;">
                            </div>

                            <div class="d-flex gap-2 mt-3 flex-wrap">
                                <button class="btn btn-secondary" id="selectFileBtn">
                                    <i class="bi bi-folder-open btn-icon"></i> Seleccionar Archivo
                                </button>
                                <a href="/api/sensor-groups/download-template" class="btn btn-info">
                                    <i class="bi bi-download btn-icon"></i> Descargar Plantilla (.csv)
                                </a>
                            </div>
                        </div>
                        <!-- Paso 2: Mapear Campos -->
                        <div id="step2" class="step d-none">
                            <hr>
                            <h5><i class="bi bi-arrow-left-right btn-icon"></i> Paso 2: Mapear Campos</h5>
                            <p class="text-muted">Asocia los campos de tu archivo con los campos del sistema.</p>

                            <!-- ✅ ADVERTENCIA DEL CAMPO CLAVE (SIEMPRE VISIBLE) -->
                            <div class="alert alert-warning border-2 border-warning mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-key-fill fs-2 text-warning me-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading mb-1">🔑 Identificador Único</h6>
                                        <p class="mb-1 small">
                                            El campo <strong>"Identificador"</strong> es como la matrícula, el número de
                                            serie o el DNI irrepetible del sensor.
                                            <strong>No puede haber dos identificadores iguales en tu grupo.</strong> Si tu
                                            archivo de Excel tiene dos filas distintas
                                            intentando usar el mismo Identificador, la segunda fila dará error en la
                                            importación o destruirá los datos de la primera.
                                        </p>
                                        <ul class="small mb-0">
                                            <li>
                                                <span class="text-success">✅</span>
                                                Si el <strong>Identificador NO existe</strong> → Se <strong>CREA</strong> un
                                                nuevo sensor
                                            </li>
                                            <li>
                                                <span class="text-warning">🔄</span>
                                                Si el <strong>Identificador YA existe</strong> → Se
                                                <strong>SOBRESCRIBE</strong> el sensor existente
                                            </li>
                                        </ul>
                                        <p class="small mt-1 mb-0 text-muted">
                                            <i class="bi bi-exclamation-triangle text-warning"></i>
                                            <strong>Importante:</strong> Asegúrate de mapear correctamente esta columna.
                                            Un mapeo incorrecto puede sobrescribir sensores existentes.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Campos básicos -->
                            <div id="fieldMappingContainer" class="field-mapping">
                                <!-- Los campos se generan dinámicamente -->
                            </div>

                            <!-- ✅ Campos Extras (Opcional) -->
                            <div id="extraFieldsContainer" class="mt-4">
                                <!-- Los campos extras se generan dinámicamente -->
                            </div>
                        </div>

                        <!-- Paso 3: Preview y Confirmación -->
                        <div id="step3" class="step d-none">
                            <hr>
                            <h5><i class="bi bi-eye btn-icon"></i> Paso 3: Preview y Confirmación</h5>
                            <p class="text-muted">Revisa los datos antes de importar.</p>

                            <!-- ✅ INFORMACIÓN DE SOBRESCRITURA -->
                            <div class="alert alert-warning mb-3">
                                <h6><i class="bi bi-exclamation-triangle-fill"></i> Reglas de Importación</h6>
                                <ul class="mb-0 small">
                                    <li>
                                        <strong>Identificador único:</strong> El campo <strong>"Identificador"</strong> se
                                        usa como clave única.
                                    </li>
                                    <li>
                                        <strong>Sobrescritura:</strong> Si un sensor con el mismo
                                        <strong>Identificador</strong> ya existe en el grupo:
                                        <ul>
                                            <li>✅ Se actualizarán: <strong>Nombre</strong> y <strong>Descripción</strong>
                                            </li>
                                            <li>✅ Se actualizarán: <strong>Campos Adicionales</strong> (Lote, Apellido,
                                                etc.)</li>
                                            <li>❌ <strong>No se modificarán:</strong> Mediciones históricas, Fecha de
                                                creación, Estado de marcado</li>
                                        </ul>
                                    </li>
                                    <li>
                                        <strong>Creación:</strong> Si el <strong>Identificador</strong> no existe, se creará
                                        un nuevo sensor.
                                    </li>
                                </ul>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="previewTable">
                                    <thead id="previewTableHead">
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Identificador</th>
                                            <th>Descripción</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewTableBody">
                                        <!-- Datos generados dinámicamente -->
                                    </tbody>
                                </table>
                            </div>

                            <div id="importSummary" class="import-summary d-none">
                                <h5><i class="bi bi-info-circle btn-icon"></i> Resumen de Importación</h5>
                                <ul>
                                    <li><strong>Total de registros en el archivo:</strong> <span id="totalRecords">0</span>
                                    </li>
                                    <li><strong>Registros válidos (en preview):</strong> <span id="validRecords">0</span>
                                    </li>
                                    <li><strong>Registros con errores (en preview):</strong> <span
                                            id="errorRecords">0</span></li>
                                </ul>
                            </div>

                            <div id="importErrors" class="error-container d-none">
                                <h5><i class="bi bi-exclamation-triangle btn-icon"></i> Errores Encontrados en el Preview
                                </h5>
                                <ul id="errorList"></ul>
                            </div>
                        </div>

                        <!-- Botones de navegación -->
                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-secondary" id="prevStepBtn" style="display: none;">
                                <i class="bi bi-arrow-left btn-icon"></i> Anterior
                            </button>
                            <button class="btn btn-primary" id="nextStepBtn" style="display: none;">
                                <i class="bi bi-arrow-right btn-icon"></i> Siguiente
                            </button>
                            <button class="btn btn-success" id="importBtn" style="display: none;">
                                <i class="bi bi-check-circle btn-icon"></i> Importar Sensores
                            </button>
                            <button class="btn btn-danger" id="cancelBtn">
                                <i class="bi bi-x-circle btn-icon"></i> Cancelar
                            </button>
                        </div>

                        <!-- Barra de progreso -->
                        <div id="progressContainer" class="progress-container d-none">
                            <h5><i class="bi bi-hourglass-split btn-icon"></i> Importando...</h5>
                            <div class="progress">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                    role="progressbar" style="width: 0%"></div>
                            </div>
                            <p id="progressText" class="mt-2">Preparando importación...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar cancelación -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="cancelModalLabel">
                        <i class="bi bi-exclamation-triangle btn-icon"></i> Confirmar Cancelación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas cancelar la importación? Todos los datos no guardados se perderán.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle btn-icon"></i> No, Continuar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmCancel">
                        <i class="bi bi-check-circle btn-icon"></i> Sí, Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para mostrar errores detallados -->
    <div class="modal fade" id="errorDetailsModal" tabindex="-1" aria-labelledby="errorDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorDetailsModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i> Detalles de Errores de Importación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorDetailsContent">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-success" id="downloadErrorReportBtn">
                        <i class="bi bi-download"></i> Descargar Informe
                    </button>
                    <button type="button" class="btn btn-primary" id="continueToSensorsBtn">
                        <i class="bi bi-arrow-right"></i> Ir a Sensores
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de confirmación de importación -->
    <div class="modal fade" id="confirmImportModal" tabindex="-1" aria-labelledby="confirmImportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="confirmImportModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i> Confirmar Importación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="confirmImportContent">
                        <!-- Contenido dinámico -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="confirmImportBtn">
                        <i class="bi bi-check-circle"></i> Confirmar Importación
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de límite de cuota y Upsell -->
    <div class="modal fade" id="quotaUpsellModal" tabindex="-1" aria-labelledby="quotaUpsellModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 overflow-hidden shadow-lg">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title" id="quotaUpsellModalLabel">
                        <i class="bi bi-rocket-takeoff-fill me-2"></i> Límite de Sensores Excedido
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-4">
                        <i class="bi bi-database-fill-exclamation text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mb-3">¡Necesitas más espacio!</h4>
                    <p class="text-muted mb-2" id="quotaUpsellMessage"></p>
                    <p class="mb-4">Para importar todo el archivo en bloque, te proponemos adquirir los paquetes necesarios ahora mismo:</p>
                    
                    <div class="card bg-light border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="text-primary mb-3">Expansión Solicitada Automática</h5>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Sensores faltantes:</span>
                                <strong id="upsellMissingSensors">0</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Packs a adquirir (+10 c/u):</span>
                                <strong id="upsellNeededPacks">0 packs</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total a pagar ahora:</span>
                                <strong class="fs-4 text-success">$<span id="upsellCost">0</span> ARS</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar importación</button>
                    <button type="button" class="btn btn-primary px-4 shadow-sm" id="btnBuyAndImport">
                        <i class="bi bi-cart3 me-2"></i> Comprar Packs e Importar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        // =============================================
        // CONFIGURACIÓN GLOBAL
        // =============================================
        let excelHeaders = [];
        let sampleData = [];
        let fieldMapping = {};
        let extraFieldMapping = {};
        let currentStep = 1;
        let selectedGroupId = null;
        let file = null;
        let sensorsInGroup = [];
        let templateFields = [];
        let extraFields = [];

        // =============================================
        // INICIALIZACIÓN
        // =============================================
        $(document).ready(function () {
            // Eventos
            $('#selectFileBtn').click(() => $('#fileInput').click());
            $('#fileInput').change(handleFileSelect);
            $('#dropZone').click(() => $('#fileInput').click());

            // Drag and drop
            $('#dropZone').on('dragover', function (e) {
                e.preventDefault();
                $(this).addClass('active');
            });

            $('#dropZone').on('dragleave', function (e) {
                e.preventDefault();
                $(this).removeClass('active');
            });

            $('#dropZone').on('drop', function (e) {
                e.preventDefault();
                $(this).removeClass('active');
                if (e.originalEvent.dataTransfer.files.length) {
                    $('#fileInput').prop('files', e.originalEvent.dataTransfer.files);
                    handleFileSelect();
                }
            });

            // Botones de navegación
            $('#nextStepBtn').click(nextStep);
            $('#prevStepBtn').click(prevStep);
            $('#importBtn').click(importSensors);
            $('#cancelBtn').click(() => $('#cancelModal').modal('show'));
            $('#confirmCancel').click(() => window.location.href = '{{ route("sensor-groups.index") }}');

            // Botón para agregar campo extra
            $('#addExtraFieldBtn').click(addExtraField);

            // ✅ Evento del botón confirmar importación
            $('#confirmImportBtn').click(function () {
                $('#confirmImportModal').modal('hide');
                executeImport();
            });

            // ✅ Evento Automático de Upsell
            $('#btnBuyAndImport').click(function () {
                const btn = $(this);
                const packs = btn.data('packs');
                
                if (!packs) return;
                
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando pago...');
                
                $.ajax({
                    url: '/api/subscription/buy-packs',
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { packs: packs },
                    success: function(response) {
                        if (response.success && response.data.preference_id) {
                            $('#quotaUpsellModal').modal('hide');
                            
                            // ✅ BYPASS PARA ENTORNO LOCAL
                            if (response.data.is_local) {
                                showAlert('✅ [DEV] Packs acreditados localmente en Sandbox. Recargando cuotas...', 'success');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                                return;
                            }
                            
                            showAlert('✅ Redirigiendo a Mercado Pago para confirmar tu compra...', 'success');
                            
                            const mp = new MercadoPago('APP_USR-79f9f592-1234-1234-1234-fcba80287103', { locale: 'es-AR' });
                            mp.checkout({
                                preference: { id: response.data.preference_id },
                                autoOpen: true
                            });
                        } else {
                            btn.prop('disabled', false).html('<i class="bi bi-cart3 me-2"></i> Comprar Packs e Importar');
                            showAlert('Error al generar la preferencia de pago', 'danger');
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="bi bi-cart3 me-2"></i> Comprar Packs e Importar');
                        showAlert('Error de conexión con el procesador de pagos', 'danger');
                    }
                });
            });

            // =============================================
            // FUNCIÓN DE IMPORTACIÓN - VERSIÓN COMPLETA
            // =============================================

            function executeImport() {
                const totalReal = window.totalRows || 0;

                // ✅ Verificar que el archivo existe
                if (!file) {
                    showAlert('No hay archivo para importar', 'danger');
                    return;
                }

                // ✅ Verificar el tamaño del archivo (máximo 10MB)
                const maxSize = 10 * 1024 * 1024; // 10MB en bytes
                if (file.size > maxSize) {
                    showAlert('El archivo es demasiado grande. El tamaño máximo permitido es 10MB.', 'danger');
                    return;
                }

                // ✅ Verificar la extensión del archivo
                const fileExtension = file.name.split('.').pop().toLowerCase();
                const validExtensions = ['xlsx', 'csv'];
                if (!validExtensions.includes(fileExtension)) {
                    showAlert(`Extensión de archivo no válida: '${fileExtension}'. Solo se permiten archivos .xlsx o .csv.`, 'danger');
                    return;
                }

                // ✅ Mostrar progreso
                $('#progressContainer').removeClass('d-none');
                $('#importBtn').prop('disabled', true);
                $('#progressBar').css('width', '0%');
                $('#progressText').text('Preparando importación...');

                // ✅ Convertir extraFieldMapping a array de objetos
                const extraFieldsArray = [];
                for (const [fieldName, columnIndex] of Object.entries(extraFieldMapping)) {
                    if (columnIndex !== '' && columnIndex !== null && columnIndex !== undefined) {
                        extraFieldsArray.push({
                            name: fieldName,
                            column: parseInt(columnIndex)
                        });
                    }
                }

                // ✅ Verificar que los campos obligatorios estén mapeados
                const requiredFields = ['name', 'identifier'];
                let missingRequired = [];
                requiredFields.forEach(field => {
                    if (!fieldMapping[field] && fieldMapping[field] !== 0) {
                        missingRequired.push(field);
                    }
                });

                if (missingRequired.length > 0) {
                    showAlert(`Faltan campos obligatorios por mapear: ${missingRequired.join(', ')}`, 'danger');
                    $('#progressContainer').addClass('d-none');
                    $('#importBtn').prop('disabled', false);
                    return;
                }

                // ✅ Log para depuración
                console.log('📤 Enviando archivo:', {
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    extension: fileExtension,
                    group_id: selectedGroupId,
                    field_mapping: fieldMapping,
                    extra_fields: extraFieldsArray,
                    total_rows: totalReal
                });

                // ✅ Crear FormData
                const formData = new FormData();
                formData.append('file', file, file.name);
                formData.append('group_id', selectedGroupId);
                formData.append('field_mapping', JSON.stringify(fieldMapping));
                formData.append('extra_fields', JSON.stringify(extraFieldsArray));

                // ✅ Mostrar progreso inicial
                $('#progressText').text(`Preparando importación de ${totalReal} registros...`);
                $('#progressBar').css('width', '10%');

                // ✅ Enviar solicitud
                $.ajax({
                    url: '/api/sensor-groups/bulk-import',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 60000, // 60 segundos de timeout
                    xhr: function () {
                        const xhr = new window.XMLHttpRequest();
                        // ✅ Monitorear progreso de subida
                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                const percentComplete = Math.round((evt.loaded / evt.total) * 50);
                                $('#progressBar').css('width', (10 + percentComplete) + '%');
                                $('#progressText').text(`Subiendo archivo... ${percentComplete}%`);
                            }
                        }, false);
                        return xhr;
                    },
                    beforeSend: function () {
                        $('#progressText').text('Enviando archivo al servidor...');
                        $('#progressBar').css('width', '20%');
                    },
                    success: function (response) {
                        console.log('✅ Respuesta de importación:', response);

                        if (response.success) {
                            // ✅ Progreso completado
                            $('#progressBar').css('width', '100%');
                            $('#progressText').text('✅ Importación completada');

                            let message = '';
                            let hasErrors = response.data.error_count > 0;

                            if (response.data.created_count > 0) {
                                message += `✅ ${response.data.created_count} sensores nuevos creados. `;
                            }
                            if (response.data.updated_count > 0) {
                                message += `🔄 ${response.data.updated_count} sensores actualizados. `;
                            }
                            if (response.data.error_count > 0) {
                                message += `❌ ${response.data.error_count} con errores.`;
                            }

                            // ✅ Si hay errores, mostrar modal con detalles
                            if (hasErrors && response.data.errors && response.data.errors.length > 0) {
                                showErrorDetailsModal(response.data.errors);
                                showAlert(message + ' Revisa los detalles en el modal.', 'warning');
                                $('#importBtn').prop('disabled', false);
                                $('#progressContainer').addClass('d-none');
                            } else {
                                // ✅ Éxito completo sin errores
                                showAlert(message, 'success');

                                // ✅ Redirigir a la lista de sensores después de 3 segundos
                                setTimeout(() => {
                                    window.location.href = '{{ route("sensors.index") }}';
                                }, 3000);
                            }
                        } else {
                            // ❌ Error en la respuesta
                            $('#progressContainer').addClass('d-none');
                            $('#importBtn').prop('disabled', false);
                            showAlert(response.message || 'Error al importar sensores', 'danger');
                        }
                    },
                    error: function (xhr) {
                        console.error('❌ Error en la importación:', xhr);

                        $('#progressContainer').addClass('d-none');
                        $('#importBtn').prop('disabled', false);

                        // ✅ Manejar errores específicos
                        let errorMessage = 'Error al importar sensores';

                        if (xhr.status === 0) {
                            errorMessage = 'Error de conexión. Verifica tu conexión a internet e intenta nuevamente.';
                        } else if (xhr.status === 413) {
                            errorMessage = 'El archivo es demasiado grande. El tamaño máximo permitido es 10MB.';
                        } else if (xhr.status === 403) {
                            errorMessage = 'No tienes permiso para importar sensores en este grupo.';
                        } else if (xhr.status === 404) {
                            errorMessage = 'El grupo seleccionado no existe.';
                        } else if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            if (xhr.responseJSON.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                errorMessage += '<br><ul>';
                                errors.forEach(err => {
                                    errorMessage += `<li>${err}</li>`;
                                });
                                errorMessage += '</ul>';
                            }
                        } else if (xhr.statusText) {
                            errorMessage += ': ' + xhr.statusText;
                        }

                        // ✅ Mostrar error en un modal si es detallado
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            showErrorDetailsModal([{
                                row: 'N/A',
                                error: errorMessage,
                                data: xhr.responseJSON
                            }]);
                        } else {
                            showAlert(errorMessage, 'danger');
                        }
                    }
                });
            }
            // Cambio de grupo
            $('#groupSelect').change(function () {
                selectedGroupId = $(this).val();
                if (selectedGroupId) {
                    loadTemplateFields(selectedGroupId);
                    loadSensorsByGroup(selectedGroupId);
                } else {
                    $('#groupInfo').addClass('d-none');
                    $('#templateInfo').addClass('d-none');
                }
            });

            // Si hay un grupo preseleccionado, cargar sus datos
            if ($('#groupSelect').val()) {
                selectedGroupId = $('#groupSelect').val();
                loadTemplateFields(selectedGroupId);
                loadSensorsByGroup(selectedGroupId);
            }
        });

        // =============================================
        // FUNCIÓN PARA MOSTRAR ERRORES DETALLADOS
        // =============================================

        function showErrorDetailsModal(errors) {
            // ✅ Crear el modal si no existe
            if ($('#errorDetailsModal').length === 0) {
                const modalHtml = `
                    <div class="modal fade" id="errorDetailsModal" tabindex="-1" aria-labelledby="errorDetailsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="errorDetailsModalLabel">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Detalles de Errores de Importación
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="errorDetailsContent">
                                    <!-- Contenido dinámico -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle"></i> Cerrar
                                    </button>
                                    <button type="button" class="btn btn-success" id="downloadErrorReportBtn">
                                        <i class="bi bi-download"></i> Descargar Informe
                                    </button>
                                    <button type="button" class="btn btn-primary" id="continueToSensorsBtn">
                                        <i class="bi bi-arrow-right"></i> Ir a Sensores
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('body').append(modalHtml);
            }

            // ✅ Llenar el modal con los errores
            const content = $('#errorDetailsContent');
            let html = `
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    <strong>Total de errores:</strong> ${errors.length}
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th># Fila</th>
                                <th>Error</th>
                                <th>Datos</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            errors.forEach((error, index) => {
                const rowData = error.data ? JSON.stringify(error.data) : 'N/A';
                html += `
                    <tr>
                        <td><span class="badge bg-danger">${error.row || 'N/A'}</span></td>
                        <td><strong class="text-danger">${error.error || 'Error desconocido'}</strong></td>
                        <td><small class="text-muted">${rowData}</small></td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-lightbulb"></i>
                    <strong>Consejo:</strong> Revisa los datos de las filas con error y corrige el archivo antes de volver a importar.
                </div>
            `;

            content.html(html);

            // ✅ Evento para descargar informe
            $('#downloadErrorReportBtn').off('click').on('click', function() {
                downloadErrorReport(errors);
            });

            // ✅ Evento para ir a sensores
            $('#continueToSensorsBtn').off('click').on('click', function() {
                $('#errorDetailsModal').modal('hide');
                window.location.href = '{{ route("sensors.index") }}';
            });

            // ✅ Mostrar el modal
            $('#errorDetailsModal').modal('show');
        }

    // =============================================
    // FUNCIÓN PARA DESCARGAR INFORME DE ERRORES
    // =============================================

    function downloadErrorReport(errors) {
        // ✅ Crear contenido CSV
        let csvContent = "Fila,Error,Datos\n";

        errors.forEach(error => {
            const rowData = error.data ? JSON.stringify(error.data).replace(/,/g, ';') : 'N/A';
            csvContent += `${error.row || 'N/A'},"${error.error || 'Error desconocido'}","${rowData}"\n`;
        });

        // ✅ Agregar resumen al final
        csvContent += `\n\nRESUMEN DE IMPORTACIÓN\n`;
        csvContent += `Total de errores,${errors.length}\n`;
        csvContent += `Fecha,${new Date().toLocaleString('es-ES')}\n`;

        // ✅ Crear y descargar archivo
        const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `errores_importacion_${new Date().getTime()}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    // =============================================
    // FUNCIÓN PARA MOSTRAR ALERTAS
    // =============================================

    function showAlert(message, type) {
        // En lugar de una alerta inline, generamos un Toast flotante moderno
        if ($('#toast-container-global').length === 0) {
            $('body').append('<div id="toast-container-global" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;"></div>');
        }

        const icon = type === 'success' ? 'check-circle' : (type === 'danger' ? 'x-circle' : 'exclamation-circle');
        const bgColor = type === 'danger' ? 'bg-danger' : (type === 'success' ? 'bg-success' : 'bg-warning');
        
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgColor} border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2" style="font-weight: 500;">
                        <i class="bi bi-${icon} fs-5"></i>
                        <span>${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        $('#toast-container-global').append(toastHtml);
        
        // Inicializar y mostrar el Toast usando Bootstrap directamente
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 6000 });
        toast.show();
        
        // Limpiar el DOM después de que se oculte
        toastElement.addEventListener('hidden.bs.toast', function () {
            $(this).remove();
        });
    }

    // =============================================
    // FUNCIONES DE CARGA DE DATOS
    // =============================================

    /**
     * Cargar campos de la plantilla del grupo (solo informativo)
     */
    function loadTemplateFields(groupId) {
        const token = localStorage.getItem('token');

        $.ajax({
            url: `/api/sensor-groups/${groupId}/template-fields`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    templateFields = response.data.fields;

                    // ✅ ENLAZAR CAMPOS ESTATICOS DEL SENSOR PARA EL MAPEO DEL CSV
                    const contextSensorFields = templateFields.filter(f => !f.is_base && f.contexto === 'sensor');
                    extraFields = contextSensorFields.map(f => ({ name: f.name, label: f.label || f.name }));

                    $('#templateInfo').removeClass('d-none');
                    $('#templateNameDisplay').text(response.data.template_name || 'Sin nombre');

                    const fieldsList = $('#templateFieldsList');
                    fieldsList.empty();

                    const measurementFields = templateFields.filter(f => !f.is_base);
                    if (measurementFields.length > 0) {
                        measurementFields.forEach(field => {
                            const unit = field.unit ? ` (${field.unit})` : '';
                            const required = field.required ? ' *' : '';
                            fieldsList.append(`<li class="text-muted small">• ${field.label || field.name}${unit}${required}</li>`);
                        });
                    } else {
                        fieldsList.append('<li class="text-muted"><i class="bi bi-info-circle"></i> Sin campos de medición definidos.</li>');
                    }
                }
            },
            error: function(xhr) {
                console.error('Error al cargar campos de la plantilla:', xhr);
            }
        });
    }

    /**
     * Cargar sensores del grupo (solo informativo)
     */
    function loadSensorsByGroup(groupId) {
        const token = localStorage.getItem('token');

        $.ajax({
            url: `/api/measurements/bulk-import/groups/${groupId}/sensors`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    sensorsInGroup = response.data;
                    const count = sensorsInGroup.length;

                    $('#groupInfo').removeClass('d-none');
                    if (count === 0) {
                        $('#groupInfoText').html(`
                            <i class="bi bi-info-circle"></i> 
                            Este grupo no tiene sensores aún. La importación creará nuevos sensores.
                        `);
                    } else {
                        $('#groupInfoText').html(
                            `Sensores disponibles en este grupo: <strong>${count}</strong> ` +
                            `(${sensorsInGroup.map(s => s.name).join(', ')})`
                        );
                    }
                }
            },
            error: function(xhr) {
                console.error('Error al cargar sensores del grupo:', xhr);
            }
        });
    }
    // =============================================
    // MANEJO DE ARCHIVOS
    // =============================================

    /**
     * Manejar selección de archivo
     */
    function handleFileSelect() {
        const fileInput = $('#fileInput')[0];
        if (fileInput.files.length === 0) return;

        file = fileInput.files[0];
        const fileName = file.name.toLowerCase();

        if (!fileName.endsWith('.xlsx') && !fileName.endsWith('.csv')) {
            showAlert('Por favor, selecciona un archivo .xlsx o .csv', 'danger');
            return;
        }

        selectedGroupId = $('#groupSelect').val();
        if (!selectedGroupId) {
            showAlert('Por favor, selecciona un grupo destino', 'danger');
            return;
        }

        // ✅ LIMPIAR DATOS ANTERIORES
        window.allExcelData = [];
        window.totalRows = 0;
        excelHeaders = [];
        sampleData = [];
        fieldMapping = {};
        extraFieldMapping = {};

        // ✅ Limpiar contenedores visuales
        $('#fieldMappingContainer').empty();
        $('#extraFieldsList').empty();
        $('#previewTableBody').empty();
        $('#previewTableHead').empty();

        loadTemplateFields(selectedGroupId);

        showLoading();

        const formData = new FormData();
        formData.append('file', file);

        $.ajax({
            url: '/api/sensor-groups/analyze-file',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoading();
                if (response.success) {
                    excelHeaders = response.data.headers;
                    sampleData = response.data.sample_data;

                    // ✅ Guardar TODOS los datos del archivo
                    window.allExcelData = response.data.all_data || [];
                    window.totalRows = response.data.total_rows || 0;

                    console.log('📊 Total de filas en el archivo:', window.totalRows);
                    console.log('📊 Preview de filas:', window.allExcelData.slice(0, 5));

                    generateFieldMapping();
                    renderExtraFields();
                    showStep(2);
                } else {
                    showAlert(response.message || 'Error al analizar el archivo', 'danger');
                }
            },
            error: function(xhr) {
                hideLoading();
                
                // ✅ Interceptar Error de Cuota con Oportunidad de Upsell
                if (xhr.status === 403 && xhr.responseJSON?.error_type === 'quota_exceeded' && xhr.responseJSON?.upsell_data) {
                    const upsell = xhr.responseJSON.upsell_data;
                    $('#quotaUpsellMessage').text(xhr.responseJSON.message);
                    $('#upsellMissingSensors').text(upsell.missing_sensors);
                    $('#upsellNeededPacks').text(upsell.needed_packs + ' packs');
                    $('#upsellCost').text(upsell.formatted_cost);
                    
                    $('#btnBuyAndImport').data('packs', upsell.needed_packs);
                    $('#quotaUpsellModal').modal('show');
                    return;
                }
                
                const errorMessage = xhr.responseJSON?.message || xhr.statusText;
                showAlert('Error al analizar el archivo: ' + errorMessage, 'danger');
            }
        });
    }
    // =============================================
    // GENERACIÓN DE MAPEO DE CAMPOS - PASO 2
    // =============================================

    function generateFieldMapping() {
        const container = $('#fieldMappingContainer');
        container.empty();

        // ✅ INFORMACIÓN DEL CAMPO CLAVE (IDENTIFICADOR)
        const keyFieldHtml = `
            <div class="alert alert-danger mb-3">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="bi bi-key-fill fs-3 me-3"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading mb-1">⚠️ Campo Clave: Identificador</h6>
                        <p class="mb-1 small">
                            El campo <strong>"Identificador"</strong> es la <strong>clave única</strong> que se usa para:
                        </p>
                        <ul class="small mb-0">
                            <li>
                                <span class="text-success">✅</span> 
                                <strong>Crear</strong> nuevos sensores si el identificador NO existe
                            </li>
                            <li>
                                <span class="text-warning">🔄</span> 
                                <strong>Sobrescribir</strong> sensores existentes si el identificador YA existe
                            </li>
                        </ul>
                        <p class="small mt-1 mb-0 text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Asegúrate de mapear correctamente esta columna para evitar sobrescrituras no deseadas.
                        </p>
                    </div>
                </div>
            </div>
        `;
        container.append(keyFieldHtml);

        // ✅ Información de la importación
        const infoHtml = `
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle"></i>
                <strong>Importación de Sensores:</strong>
                Solo necesitas mapear los campos básicos del sensor.
                <br>
                <small>Los campos de medición se definen en la plantilla del grupo y se usan al tomar mediciones.</small>
            </div>
        `;
        container.append(infoHtml);

        // ✅ Campos fijos para SENSORES
        const sensorFields = [
            { 
                name: 'name', 
                label: 'Nombre del Sensor', 
                required: true,
                description: 'Nombre descriptivo del sensor'
            },
            { 
                name: 'identifier', 
                label: 'Identificador * CLAVE ÚNICA', 
                required: true,
                description: 'Código único que identifica al sensor (se usa para sobrescritura)',
                isKey: true
            },
            { 
                name: 'description', 
                label: 'Descripción', 
                required: false,
                description: 'Información adicional opcional del sensor'
            }
        ];

        // ✅ Generar campos
        sensorFields.forEach(field => {
            const isKey = field.isKey || false;
            const isRequired = field.required || false;
            const label = field.label;
            const description = field.description || '';

            const row = $(`
                <div class="row mb-2">
                    <div class="col-md-5">
                        <label class="form-label">
                            ${label}
                            ${isRequired ? '<span class="text-danger">*</span>' : ''}
                            ${isKey ? '<span class="badge bg-warning text-dark ms-1">🔑 CLAVE</span>' : ''}
                            ${description ? `<small class="text-muted d-block">${description}</small>` : ''}
                        </label>
                    </div>
                    <div class="col-md-5">
                        <select class="form-select field-mapping-select" data-field="${field.name}" data-required="${isRequired}" data-is-key="${isKey}">
                            <option value="" selected>No mapear</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-center">
                        ${isRequired ? '<span class="text-danger">*</span>' : ''}
                        ${isKey ? '<i class="bi bi-key text-warning" title="Campo clave"></i>' : ''}
                    </div>
                </div>
            `);

            const select = row.find('.field-mapping-select');
            excelHeaders.forEach((header, index) => {
                const displayHeader = header ? header.trim() : `Columna ${index + 1}`;
                select.append(`<option value="${index}">${displayHeader}</option>`);
            });

            container.append(row);
        });

        $('#nextStepBtn').show();
        $('#prevStepBtn').show();
    }

    // =============================================
    // CAMPOS EXTRAS
    // =============================================

    function renderExtraFields() {
        const container = $('#extraFieldsContainer');
        container.empty();

        // ✅ Si no hay encabezados del archivo, mostrar mensaje
        if (excelHeaders.length === 0) {
            container.html(`
                <div class="text-muted small text-center py-2">
                    <i class="bi bi-info-circle"></i> 
                    Sube un archivo primero para ver las columnas disponibles.
                </div>
            `);
            return;
        }

        // ✅ Mostrar sección de campos extras
        const html = `
            <div class="card mt-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6><i class="bi bi-list-check"></i> Atributos Fijos (Según la Plantilla)</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addExtraFieldBtn">
                        <i class="bi bi-plus-circle"></i> Forzar Campo Manual
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        <i class="bi bi-info-circle"></i> 
                        Estos son los Atributos Fijos de la plantilla asociada al Grupo de Sensores. Selecciona columnas del Excel para mapearlos.
                    </p>
                    <div id="extraFieldsList">
                        <div class="text-muted small text-center py-2" id="noExtraFieldsMsg">
                            <i class="bi bi-check-circle text-success"></i> 
                            La plantilla de este grupo de sensores no requiere ningún atributo fijo. Si querés agregar uno manualmente, hacé clic en "Forzar Campo Manual".
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.append(html);

        // ✅ Evento para el botón de agregar campo (DELEGACIÓN DE EVENTOS)
        $('#addExtraFieldBtn').off('click').on('click', function() {
            addExtraField();
        });

        // ✅ Si hay campos extras predefinidos, mostrarlos
        if (extraFields && extraFields.length > 0) {
            extraFields.forEach(field => {
                addExtraFieldRow(field.name, field.label, false);
            });
        }
    }


    function addExtraField() {
        // ✅ Verificar que haya encabezados disponibles
        if (excelHeaders.length === 0) {
            showAlert('Primero debes subir un archivo para ver las columnas disponibles.', 'warning');
            return;
        }

        const fieldName = prompt('Nombre del campo (ej: lote, apellido, ubicacion):');
        if (!fieldName || fieldName.trim() === '') return;

        const fieldLabel = prompt('Etiqueta del campo (ej: Lote, Apellido, Ubicación):', fieldName.charAt(0).toUpperCase() + fieldName.slice(1));
        if (!fieldLabel || fieldLabel.trim() === '') return;

        // ✅ Verificar si ya existe
        if ($(`.extra-field[data-field="${fieldName.trim()}"]`).length > 0) {
            showAlert(`El campo "${fieldLabel}" ya existe`, 'warning');
            return;
        }

        addExtraFieldRow(fieldName.trim(), fieldLabel.trim(), true);
    }

    function addExtraFieldRow(fieldName, fieldLabel, isCustom = false) {
        const container = $('#extraFieldsList');

        // ✅ Si hay un mensaje de "no hay campos", eliminarlo
        container.find('#noExtraFieldsMsg').remove();

        const row = $(`
            <div class="row mb-2 extra-field align-items-center" data-field="${fieldName}">
                <div class="col-md-4">
                    <label class="form-label small fw-bold mb-0">
                        ${fieldLabel}
                        ${isCustom ? '<span class="badge bg-warning text-dark ms-1">Personalizado</span>' : ''}
                    </label>
                </div>
                <div class="col-md-6">
                    <select class="form-select form-select-sm extra-field-select" data-field="${fieldName}">
                        <option value="" selected>No mapear</option>
                    </select>
                </div>
                <div class="col-md-2 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-extra-field" title="Eliminar campo">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `);

        // ✅ Llenar el select con los encabezados del archivo
        const select = row.find('.extra-field-select');
        excelHeaders.forEach((header, index) => {
            const displayHeader = header ? header.trim() : `Columna ${index + 1}`;
            select.append(`<option value="${index}">${displayHeader}</option>`);
        });

        // ✅ Evento para eliminar campo
        row.find('.remove-extra-field').off('click').on('click', function() {
            if (confirm(`¿Eliminar el campo "${fieldLabel}"?`)) {
                row.remove();
                // ✅ Si no quedan campos, mostrar mensaje
                if (container.children().length === 0) {
                    container.html(`
                        <div class="text-muted small text-center py-2" id="noExtraFieldsMsg">
                            <i class="bi bi-info-circle"></i> 
                            No hay campos adicionales configurados. Haz clic en "Agregar Campo" para crear uno.
                        </div>
                    `);
                }
            }
        });

        container.append(row);
    }

    /**
     * Obtener el mapeo de campos extras (para usar en la importación)
     */
    function getExtraFieldMapping() {
        const mapping = {};
        $('.extra-field-select').each(function() {
            const fieldName = $(this).data('field');
            const columnIndex = $(this).val();
            if (columnIndex !== '' && columnIndex !== null) {
                mapping[fieldName] = parseInt(columnIndex);
            }
        });
        return mapping;
    }

    // =============================================
    // NAVEGACIÓN DE PASOS
    // =============================================

    function nextStep() {
        if (currentStep === 1) {
            if (!file) {
                showAlert('Por favor, selecciona un archivo primero', 'danger');
                return;
            }
            if (!selectedGroupId) {
                showAlert('Por favor, selecciona un grupo destino', 'danger');
                return;
            }
        } else if (currentStep === 2) {
            // ✅ Validar que los campos obligatorios estén mapeados
            let allRequiredMapped = true;
            let missingFields = [];
            let keyFieldMapped = false;

            $('.field-mapping-select').each(function() {
                const isRequired = $(this).data('required');
                const isKey = $(this).data('is-key');
                const fieldName = $(this).data('field');
                const hasValue = $(this).val() !== '';

                if (isRequired && !hasValue) {
                    allRequiredMapped = false;
                    $(this).addClass('is-invalid');
                    missingFields.push(fieldName);
                } else {
                    $(this).removeClass('is-invalid');
                }

                if (isKey && hasValue) {
                    keyFieldMapped = true;
                }
            });

            if (!allRequiredMapped) {
                showAlert('Por favor, mapea todos los campos obligatorios (marcados con *): ' + missingFields.join(', '), 'danger');
                return;
            }

            // ✅ Verificar que el campo clave (identificador) esté mapeado
            if (!keyFieldMapped) {
                showAlert(
                    '⚠️ El campo <strong>"Identificador"</strong> (clave única) es obligatorio. ' +
                    'Este campo se usa para identificar sensores y evitar duplicados.', 
                    'danger'
                );
                return;
            }

            // Guardar el mapeo de campos básicos
            fieldMapping = {};
            $('.field-mapping-select').each(function() {
                const fieldName = $(this).data('field');
                const columnIndex = $(this).val();
                if (columnIndex !== '') {
                    fieldMapping[fieldName] = parseInt(columnIndex);
                }
            });

            // ✅ Guardar el mapeo de campos extras usando la función
            extraFieldMapping = getExtraFieldMapping();

            // ✅ Mostrar resumen del mapeo para depuración
            console.log('📋 Mapeo de campos básicos:', fieldMapping);
            console.log('📋 Mapeo de campos extras:', extraFieldMapping);

            generatePreview();
        }

        showStep(currentStep + 1);
    }

    function prevStep() {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    }

    function showStep(step) {
        currentStep = step;
        $('.step').addClass('d-none');
        $(`#step${step}`).removeClass('d-none');
        $('#prevStepBtn').toggle(step > 1);
        $('#nextStepBtn').toggle(step > 0 && step < 3);
        $('#importBtn').toggle(step === 3);
    }

    // =============================================
    // PREVIEW - VERSIÓN CORREGIDA
    // =============================================

    function generatePreview() {
        const container = $('#previewTableBody');
        const head = $('#previewTableHead');
        container.empty();

        let validRecords = 0;
        let errorRecords = 0;
        const errors = [];

        // ✅ Obtener TODOS los datos del archivo
        const allRows = window.allExcelData || [];
        const totalRecords = window.totalRows || 0;

        // ✅ Para el preview, mostrar solo las primeras 5 filas (excluyendo encabezados)
        const previewRows = allRows.slice(1, 6); // Saltar encabezados, tomar 5 filas

        // ✅ Obtener campos base y extras
        const baseFields = ['name', 'identifier', 'description'];
        const extraFieldNames = Object.keys(extraFieldMapping);

        // ✅ Construir encabezados dinámicos
        let headers = ['#', 'Nombre *', 'Identificador *', 'Descripción'];

        extraFieldNames.forEach(fieldName => {
            const field = extraFields.find(f => f.name === fieldName);
            headers.push(field ? field.label : fieldName);
        });

        headers.push('Estado');

        // ✅ Generar encabezado de la tabla
        let headerHtml = '<tr>';
        headers.forEach(h => {
            headerHtml += `<th>${h}</th>`;
        });
        headerHtml += '</tr>';
        head.html(headerHtml);

        // ✅ Si no hay datos, mostrar mensaje
        if (totalRecords === 0 || previewRows.length === 0) {
            container.html(`<tr><td colspan="${headers.length}" class="text-center">No hay datos para mostrar</td></tr>`);
            $('#totalRecords').text('0');
            $('#validRecords').text('0');
            $('#errorRecords').text('0');
            $('#importSummary').removeClass('d-none');

            const previewInfo = `
                <div class="alert alert-warning mt-2">
                    <i class="bi bi-exclamation-triangle"></i>
                    No se encontraron datos en el archivo. Verifica que el archivo tenga contenido.
                </div>
            `;
            updatePreviewInfo(previewInfo);
            return;
        }

        // ✅ Procesar SOLO las filas del preview (primeras 5)
        previewRows.forEach((row, index) => {
            if (!row || row.length === 0) return;

            // ✅ Obtener valores base
            const name = getMappedValue(row, 'name');
            const identifier = getMappedValue(row, 'identifier');
            const description = getMappedValue(row, 'description');

            // ✅ Obtener valores de campos extras
            const extraValues = {};
            extraFieldNames.forEach(fieldName => {
                extraValues[fieldName] = getMappedValue(row, fieldName) || 'N/A';
            });

            let isValid = true;
            const recordErrors = [];

            // ✅ Validar campos obligatorios
            if (!name || name === '') {
                isValid = false;
                recordErrors.push('El campo "Nombre" es obligatorio');
            }

            if (!identifier || identifier === '') {
                isValid = false;
                recordErrors.push('El campo "Identificador" es obligatorio');
            }

            // ✅ Validar campos extras que sean requeridos
            extraFieldNames.forEach(fieldName => {
                const field = extraFields.find(f => f.name === fieldName);
                if (field && field.required) {
                    const value = extraValues[fieldName];
                    if (!value || value === '' || value === 'N/A') {
                        isValid = false;
                        recordErrors.push(`El campo "${field.label || fieldName}" es obligatorio`);
                    }
                }
            });

            if (isValid) {
                validRecords++;
            } else {
                errorRecords++;
                errors.push({
                    index: index + 1,
                    errors: recordErrors
                });
            }

            // ✅ Generar fila de la tabla
            let rowHtml = `<tr class="${isValid ? '' : 'table-danger'}">`;
            rowHtml += `<td>${index + 1}</td>`;
            rowHtml += `<td>${name || 'N/A'}</td>`;
            rowHtml += `<td>${identifier || 'N/A'}</td>`;
            rowHtml += `<td>${description || 'N/A'}</td>`;

            extraFieldNames.forEach(fieldName => {
                rowHtml += `<td>${extraValues[fieldName]}</td>`;
            });

            const statusBadge = isValid ?
                '<span class="badge bg-success">Válido</span>' :
                '<span class="badge bg-danger">Error</span>';
            rowHtml += `<td>${statusBadge}</td>`;
            rowHtml += '</tr>';

            container.append(rowHtml);
        });

        // ✅ MOSTRAR EL TOTAL REAL DE REGISTROS
        $('#totalRecords').text(totalRecords);
        $('#validRecords').text(validRecords);
        $('#errorRecords').text(errorRecords);

        // ✅ Mensaje informativo sobre el preview
        const previewInfo = `
            <div class="alert alert-info mt-2">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-info-circle-fill fs-4 me-2"></i>
                    </div>
                    <div>
                        <strong>Vista previa:</strong> Mostrando <strong>${previewRows.length}</strong> de <strong>${totalRecords}</strong> registros.
                        ${totalRecords > previewRows.length ? 'El resto de los registros se procesarán durante la importación.' : ''}
                        ${totalRecords === 0 ? 'El archivo no contiene datos.' : ''}
                    </div>
                </div>
            </div>
        `;
        updatePreviewInfo(previewInfo);

        if (errorRecords > 0) {
            $('#importErrors').removeClass('d-none');
            renderErrors(errors);
        } else {
            $('#importErrors').addClass('d-none');
        }

        $('#importSummary').removeClass('d-none');
    }

    // ✅ Función auxiliar para actualizar el mensaje de preview
    function updatePreviewInfo(html) {
        if ($('#previewInfo').length === 0) {
            $('#importSummary').after(`<div id="previewInfo">${html}</div>`);
        } else {
            $('#previewInfo').html(html);
        }
    }

    // =============================================
    // FUNCIONES AUXILIARES
    // =============================================

    function getMappedValue(row, fieldName) {
        // ✅ Primero buscar en campos básicos
        if (fieldMapping[fieldName] !== undefined && fieldMapping[fieldName] !== null) {
            const columnIndex = fieldMapping[fieldName];
            if (columnIndex !== undefined && columnIndex >= 0 && columnIndex < row.length) {
                const value = row[columnIndex];
                if (value !== undefined && value !== null) {
                    if (typeof value === 'number') return value.toString();
                    if (typeof value === 'string') return value.trim() || null;
                    return String(value);
                }
            }
        }

        // ✅ Buscar en campos extras
        if (extraFieldMapping[fieldName] !== undefined && extraFieldMapping[fieldName] !== null) {
            const columnIndex = extraFieldMapping[fieldName];
            if (columnIndex !== undefined && columnIndex >= 0 && columnIndex < row.length) {
                const value = row[columnIndex];
                if (value !== undefined && value !== null) {
                    if (typeof value === 'number') return value.toString();
                    if (typeof value === 'string') return value.trim() || null;
                    return String(value);
                }
            }
        }

        return null;
    }

    function renderErrors(errors) {
        const container = $('#errorList');
        container.empty();

        errors.forEach(error => {
            container.append(`
                <li>
                    <strong>Fila ${error.index}:</strong>
                    <ul>
                        ${error.errors.map(e => `<li>${e}</li>`).join('')}
                    </ul>
                </li>
            `);
        });
    }

    function showLoading() {
        $('#dropZone').html(`
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Analizando archivo...</p>
            </div>
        `);
    }

    function hideLoading() {
        $('#dropZone').html(`
            <i class="bi bi-file-earmark-excel"></i>
            <p><strong>Arrastra y suelta el archivo aquí</strong> o haz clic para seleccionarlo</p>
            <p class="text-muted small">(Solo archivos .xlsx o .csv)</p>
            <input type="file" id="fileInput" accept=".xlsx,.csv" style="display: none;">
        `);
    }



    // =============================================
    // IMPORTACIÓN - VERSIÓN CORREGIDA
    // =============================================

    function importSensors() {
        if (!file || !fieldMapping || !selectedGroupId) {
            showAlert('No hay datos para importar', 'danger');
            return;
        }

        const totalReal = window.totalRows || 0;
        const errorRecords = parseInt($('#errorRecords').text());

        if (totalReal === 0) {
            showAlert('El archivo no contiene datos para importar', 'warning');
            return;
        }

        // ✅ MOSTRAR MODAL DE CONFIRMACIÓN
        showConfirmModal(totalReal, errorRecords);
    }

    function showConfirmModal(totalReal, errorRecords) {
        const confirmContent = $('#confirmImportContent');

        // ✅ Obtener cantidad de sensores existentes en el grupo
        $.ajax({
            url: `/api/sensor-groups/${selectedGroupId}`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    const existingSensors = response.data.sensors_count || 0;
                    const extraFieldsList = Object.keys(extraFieldMapping);

                    let html = `
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Resumen de la importación</h6>
                            <ul class="mb-0">
                                <li><strong>Registros a procesar:</strong> ${totalReal}</li>
                                <li><strong>Sensores existentes en el grupo:</strong> ${existingSensors}</li>
                                <li class="mt-2">
                                    <strong>¿Qué va a pasar?</strong>
                                    <ul class="mb-0">
                                        <li>🔄 Los sensores con <strong>identificador existente</strong> se <strong>actualizarán</strong></li>
                                        <li>🆕 Los sensores con <strong>identificador nuevo</strong> se <strong>crearán</strong></li>
                                        <li>📊 <strong>Total final aproximado:</strong> ${existingSensors} + (identificadores nuevos)</li>
                                    </ul>
                                </li>
                                <li class="mt-2"><small class="text-muted">
                                    <i class="bi bi-info-circle"></i> 
                                    <strong>Nota:</strong> El número final de sensores dependerá de cuántos identificadores sean nuevos.
                                    Como máximo, el total será <strong>${existingSensors + totalReal}</strong> (si todos los registros fueran nuevos).
                                    Los sensores existentes se actualizan, no se duplican.
                                </small></li>
                            </ul>
                        </div>
                    `;

                    // ✅ Reglas de sobrescritura
                    html += `
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-exclamation-triangle"></i> Reglas de Sobrescritura</h6>
                            <p class="small mb-0">
                                <strong>El campo "Identificador" se usa como clave única.</strong>
                            </p>
                            <ul class="small mb-0 mt-1">
                                <li>
                                    <span class="text-success">✅</span> 
                                    <strong>Se actualizarán:</strong> Nombre, Descripción y Campos Adicionales
                                </li>
                                <li>
                                    <span class="text-danger">❌</span> 
                                    <strong>NO se modificarán:</strong> Mediciones históricas, Fecha de creación, Estado de marcado
                                </li>
                                <li>
                                    <span class="text-info">ℹ️</span> 
                                    Si el Identificador <strong>NO existe</strong>, se creará un nuevo sensor.
                                </li>
                                <li>
                                    <span class="text-info">ℹ️</span> 
                                    <strong>Campos que no vienen en el archivo:</strong> Mantienen su valor anterior.
                                </li>
                            </ul>
                        </div>
                    `;

                    // ✅ Campos que se actualizarán
                    html += `
                        <div class="alert alert-secondary">
                            <h6><i class="bi bi-tags"></i> Campos que se actualizarán</h6>
                            <ul class="small mb-0">
                                <li><strong>Campos base:</strong> Nombre, Descripción</li>
                                ${extraFieldsList.length > 0 ? `<li><strong>Campos adicionales:</strong> ${extraFieldsList.join(', ')}</li>` : '<li><strong>Campos adicionales:</strong> Ninguno</li>'}
                            </ul>
                            <p class="small mt-1 mb-0 text-muted">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Nota:</strong> Los campos que no vienen en el archivo <strong>mantienen</strong> su valor anterior.
                            </p>
                        </div>
                    `;

                    // ✅ Mostrar advertencia si hay errores
                    if (errorRecords > 0) {
                        html += `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Advertencia:</strong> Hay ${errorRecords} registros con errores en el preview.
                                Estos registros <strong>NO</strong> serán importados.
                            </div>
                        `;
                    }

                    html += `
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            <strong>¿Estás seguro de que deseas continuar?</strong>
                            <p class="small mb-0 mt-1">Esta acción procesará ${totalReal} registros del archivo.</p>
                        </div>
                    `;

                    confirmContent.html(html);
                    $('#confirmImportModal').modal('show');
                }
            },
            error: function(xhr) {
                // ✅ Fallback: mostrar modal genérico
                let html = `
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Resumen de la importación</h6>
                        <ul class="mb-0">
                            <li><strong>Registros a procesar:</strong> ${totalReal}</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle"></i> Reglas de Sobrescritura</h6>
                        <ul class="small mb-0">
                            <li>✅ <strong>Se actualizarán:</strong> Nombre, Descripción y Campos Adicionales</li>
                            <li>❌ <strong>NO se modificarán:</strong> Mediciones históricas, Fecha de creación</li>
                            <li>ℹ️ Si el Identificador <strong>NO existe</strong>, se creará un nuevo sensor.</li>
                            <li>ℹ️ Campos que no vienen en el archivo: Mantienen su valor anterior.</li>
                        </ul>
                    </div>
                `;

                if (errorRecords > 0) {
                    html += `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Advertencia:</strong> Hay ${errorRecords} registros con errores que NO serán importados.
                        </div>
                    `;
                }

                confirmContent.html(html);
                $('#confirmImportModal').modal('show');
            }
        });
    }

    function renderConfirmModalContent(totalReal, errorRecords, existingSensors) {
        const confirmContent = $('#confirmImportContent');

        // ✅ Obtener campos extras mapeados
        const extraFieldsList = Object.keys(extraFieldMapping);

        let html = `
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle"></i> Resumen de la importación</h6>
                <ul class="mb-0">
                    <li><strong>Registros a procesar:</strong> ${totalReal}</li>
                    <li><strong>Sensores existentes en el grupo:</strong> ${existingSensors}</li>
                    <li class="mt-2">
                        <strong>¿Qué va a pasar?</strong>
                        <ul class="mb-0">
                            <li>🔄 Los sensores con <strong>identificador existente</strong> se <strong>actualizarán</strong></li>
                            <li>🆕 Los sensores con <strong>identificador nuevo</strong> se <strong>crearán</strong></li>
                            <li>📊 <strong>Total final aproximado:</strong> ${existingSensors} + (identificadores nuevos)</li>
                        </ul>
                    </li>
                    <li class="mt-2"><small class="text-muted">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Nota:</strong> El número final de sensores dependerá de cuántos identificadores sean nuevos.
                        Como máximo, el total será <strong>${existingSensors + totalReal}</strong> (si todos los registros fueran nuevos).
                        Los sensores existentes se actualizan, no se duplican.
                    </small></li>
                </ul>
            </div>
        `;

        // ✅ Reglas de sobrescritura
        html += `
            <div class="alert alert-warning">
                <h6><i class="bi bi-exclamation-triangle"></i> Reglas de Sobrescritura</h6>
                <p class="small mb-0">
                    <strong>El campo "Identificador" se usa como clave única.</strong>
                </p>
                <ul class="small mb-0 mt-1">
                    <li>
                        <span class="text-success">✅</span> 
                        <strong>Se actualizarán:</strong> Nombre, Descripción y Campos Adicionales
                    </li>
                    <li>
                        <span class="text-danger">❌</span> 
                        <strong>NO se modificarán:</strong> Mediciones históricas, Fecha de creación, Estado de marcado
                    </li>
                    <li>
                        <span class="text-info">ℹ️</span> 
                        Si el Identificador <strong>NO existe</strong>, se creará un nuevo sensor.
                    </li>
                </ul>
            </div>
        `;

        // ✅ Campos que se actualizarán
        html += `
            <div class="alert alert-secondary">
                <h6><i class="bi bi-tags"></i> Campos que se actualizarán</h6>
                <ul class="small mb-0">
                    <li><strong>Campos base:</strong> Nombre, Descripción</li>
                    ${extraFieldsList.length > 0 ? `<li><strong>Campos adicionales:</strong> ${extraFieldsList.join(', ')}</li>` : '<li><strong>Campos adicionales:</strong> Ninguno</li>'}
                </ul>
            </div>
        `;

        // ✅ Advertencia de errores
        if (errorRecords > 0) {
            html += `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Advertencia:</strong> Hay ${errorRecords} registros con errores en el preview.
                    Estos registros <strong>NO</strong> serán importados.
                </div>
            `;
        }

        // ✅ Confirmación final
        html += `
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                <strong>¿Estás seguro de que deseas continuar?</strong>
                <p class="small mb-0 mt-1">Esta acción procesará ${totalReal} registros del archivo.</p>
            </div>
        `;

        confirmContent.html(html);
    }
    // ✅ Función para mostrar modal genérico (fallback)
    function showGenericConfirmModal(totalReal, errorRecords) {
        const confirmContent = $('#confirmImportContent');
        let html = `
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle"></i> Resumen de la importación</h6>
                <ul class="mb-0">
                    <li><strong>Registros a procesar:</strong> ${totalReal}</li>
                </ul>
            </div>

            <div class="alert alert-warning">
                <h6><i class="bi bi-exclamation-triangle"></i> Reglas de Sobrescritura</h6>
                <ul class="small mb-0">
                    <li>✅ <strong>Se actualizarán:</strong> Nombre, Descripción y Campos Adicionales</li>
                    <li>❌ <strong>NO se modificarán:</strong> Mediciones históricas, Fecha de creación</li>
                    <li>ℹ️ Si el Identificador <strong>NO existe</strong>, se creará un nuevo sensor.</li>
                </ul>
            </div>
        `;

        if (errorRecords > 0) {
            html += `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Advertencia:</strong> Hay ${errorRecords} registros con errores que NO serán importados.
                </div>
            `;
        }

        confirmContent.html(html);
        $('#confirmImportModal').modal('show');
    }
    </script>
@endpush