@extends('layouts.modern')

@section('title', 'Respaldos y Retención')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="fw-bold mb-2">Respaldos de Evidencias <span
                        class="badge bg-warning text-dark fs-6 ms-2">Anti-Colapso</span></h3>
                <p class="text-muted">Descarga todas las fotos históricas de tus sensores antes de que caduquen al cumplir 1
                    año. El proceso se realiza directamente en tu navegador para máxima velocidad.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-cloud-arrow-down text-primary me-2"></i> Generar Respaldo
                        </h5>
                        <form id="backupForm">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label text-muted fw-bold">1. Selecciona un Lote/Grupo</label>
                                <select class="form-select" id="groupSelect" name="group_id" required>
                                    <option value="" disabled selected>Elige el grupo...</option>
                                    @foreach($groups as $g)
                                        <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->sensors_count }} sensores)</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="btnStartBackup">
                                <i class="bi bi-search me-1"></i> Buscar Evidencias
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Políticas -->
                <div class="alert alert-info border-0 shadow-sm rounded-4 p-4 text-sm">
                    <h6 class="fw-bold"><i class="bi bi-info-circle me-1"></i> Política de Retención</h6>
                    <p class="mb-1">Toda evidencia (foto) que alcance los <strong>365 días</strong> de antigüedad será
                        borrada automáticamente del servidor central.</p>
                    <p class="mb-0">Los datos matemáticos de la medición (metros cúbicos, etc.) <strong>no se
                            borrarán</strong>, pero perderán su foto auditora. Genera resguardos periódicos.</p>
                </div>
            </div>

            <div class="col-md-7">
                <!-- Consola de proceso JS -->
                <div class="card shadow-sm border-0 rounded-4" id="processConsole"
                    style="display: none; min-height: 380px;">
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Estado del Empaquetado</h5>

                        <div id="statusMessage" class="text-primary fw-bold mb-3">Preparando entorno local...</div>

                        <div class="progress mb-4" style="height: 25px;">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                                aria-valuemax="100">0%</div>
                        </div>

                        <div class="console-box bg-dark text-light p-3 rounded"
                            style="font-family: monospace; font-size: 0.85rem; height: 180px; overflow-y: auto;"
                            id="logBox">
                            > Listo para descargar...
                        </div>

                        <div class="mt-auto pt-3 text-end">
                            <button class="btn btn-success d-none" id="btnSaveZip"><i class="bi bi-download me-1"></i>
                                Guardar ZIP en mi PC</button>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-4 bg-light d-flex align-items-center justify-content-center"
                    id="emptyState" style="min-height: 380px;">
                    <div class="text-center text-muted">
                        <i class="bi bi-archive fs-1 mb-2"></i>
                        <p>Selecciona un grupo a la izquierda para comenzar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script>
        $(document).ready(function () {
            let globalZip = null;
            let zipName = 'backup.zip';

            function log(msg, error = false) {
                const box = $('#logBox');
                const color = error ? 'text-danger' : 'text-success';
                box.append(`<br><span class="${color}">> ${msg}</span>`);
                box.scrollTop(box[0].scrollHeight);
            }

            $('#backupForm').on('submit', function (e) {
                e.preventDefault();

                const groupId = $('#groupSelect').val();
                if (!groupId) return;

                const groupName = $('#groupSelect option:selected').text().split('(')[0].trim();
                zipName = 'MedFlow_Evidencias_' + groupName.replace(/ /g, '_') + '.zip';

                $('#emptyState').hide();
                $('#processConsole').show();
                $('#progressBar').css('width', '0%').text('0%').removeClass('bg-danger').addClass('bg-success');
                $('#btnSaveZip').addClass('d-none');
                $('#logBox').html('> Inicializando búsqueda de archivos para: ' + groupName);
                $('#statusMessage').text('Buscando evidencias en el servidor...');

                $('#btnStartBackup').prop('disabled', true);

                $.ajax({
                    url: "{{ route('api.backups.fetch') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        group_id: groupId
                    },
                    success: function (res) {
                        if (res.success) {
                            if (res.count === 0) {
                                $('#statusMessage').text('No se encontraron fotos disponibles.');
                                log('0 imágenes encontradas.');
                                $('#btnStartBackup').prop('disabled', false);
                                return;
                            }

                            $('#statusMessage').text(`Encontradas ${res.count} fotos. Iniciando descarga local...`);
                            log(`API respondió con ${res.count} URLs estáticas.`);
                            startClientSideZip(res.files);
                        } else {
                            log('Error en servidor', true);
                            $('#btnStartBackup').prop('disabled', false);
                        }
                    },
                    error: function () {
                        log('Error de conexión', true);
                        $('#btnStartBackup').prop('disabled', false);
                    }
                });
            });

            async function startClientSideZip(files) {
                const zip = new JSZip();
                globalZip = zip;

                let downloaded = 0;
                const total = files.length;

                // Función auxiliar para descargar blob
                const fetchBlob = async (url) => {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error("HTTP " + response.status);
                    return await response.blob();
                };

                // Descarga iterativa para no colapsar la RAM del navegador
                for (let i = 0; i < total; i++) {
                    const file = files[i];
                    try {
                        const blob = await fetchBlob(file.url);
                        zip.file(file.filename, blob);

                        downloaded++;
                        const percentage = Math.round((downloaded / total) * 100);
                        $('#progressBar').css('width', percentage + '%').text(percentage + '%');
                        $('#statusMessage').text(`Descargando foto ${downloaded} de ${total}...`);
                        log(`[${downloaded}/${total}] Descargado: ${file.filename}`);
                    } catch (err) {
                        log(`Error al decargar ${file.filename}: ${err.message}`, true);
                    }
                }

                $('#statusMessage').text(`¡Empaquetado Listo! Se obtuvieron ${downloaded} fotos.`);
                log(`Proceso completado. Listo para guardar ZIP.`);
                $('#btnSaveZip').removeClass('d-none');
                $('#btnStartBackup').prop('disabled', false);
            }

            $('#btnSaveZip').click(function () {
                if (!globalZip) return;
                $('#statusMessage').text('Generando archivo final...');

                globalZip.generateAsync({ type: "blob" }).then(function (content) {
                    saveAs(content, zipName);
                    $('#statusMessage').text('¡Descarga iniciada exitosamente!');
                    log('Archivo ZIP guardado en disco duro.');
                });
            });
        });
    </script>
@endpush