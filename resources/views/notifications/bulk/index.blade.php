@extends('layouts.modern')

@section('title', 'Campañas Masivas de Consumos')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header pt-4 pb-3 border-0" style="background-color: transparent;">
                    <h4 class="fw-bold mb-1"><i class="bi bi-broadcast text-primary"></i> Emisión de Accesos Públicos</h4>
                    <p class="small mb-0" style="opacity: 0.75;">Dispara correos electrónicos con el enlace de Visor Público
                        (y QR) para todos los medidores de un grupo que posean un <strong>email de contacto</strong>
                        asignado.</p>
                </div>

                <div class="card-body bg-light rounded-bottom">

                    <!-- Estado de Reputación y Advertencia de Seguridad -->
                    <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid {{ ($reputation['status'] ?? '') === 'bloqueada' ? '#dc3545' : ((($reputation['status'] ?? '') === 'en_riesgo') ? '#ffc107' : '#0d6efd') }} !important;">
                        <div class="card-body p-3 p-md-4 bg-white rounded-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                                        <i class="bi bi-shield-check fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">Seguridad SMTP y Reputación de Entregabilidad</h6>
                                        <small class="text-muted">Motor de despacho certificado mediante Amazon SES</small>
                                    </div>
                                </div>
                                <div class="mt-2 mt-md-0">
                                    <span class="badge {{ $reputation['badge_class'] ?? 'bg-success' }} px-3 py-2">
                                        <i class="bi bi-activity me-1"></i> {{ $reputation['status_label'] ?? 'Reputación Óptima' }}
                                    </span>
                                </div>
                            </div>

                            <div class="row g-2 mt-1 small text-muted">
                                <div class="col-md-4">
                                    <i class="bi bi-send-check text-primary me-1"></i> Enviados últimos 30 días: <strong>{{ $reputation['total_sent_30d'] ?? 0 }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <i class="bi bi-exclamation-triangle text-warning me-1"></i> Rebotes registrados: <strong>{{ $reputation['bounces_30d'] ?? 0 }}</strong> ({{ $reputation['bounce_rate'] ?? 0 }}%)
                                </div>
                                <div class="col-md-4">
                                    <i class="bi bi-slash-circle text-danger me-1"></i> Quejas / Spam: <strong>{{ $reputation['complaints_30d'] ?? 0 }}</strong>
                                </div>
                            </div>

                            <div class="alert alert-warning border-0 bg-warning-subtle text-dark mt-3 mb-0 p-3 rounded-3" style="font-size: 0.88rem;">
                                <div class="d-flex">
                                    <i class="bi bi-exclamation-octagon-fill fs-5 text-warning me-2 flex-shrink-0"></i>
                                    <div>
                                        <strong>IMPORTANTE: El campo de correo electrónico es sensible y auditado en tiempo real.</strong>
                                        <ul class="mb-0 ps-3 mt-1">
                                            <li><strong>Prohibido usar correos falsos o inventados:</strong> Direcciones como <code>test.com</code>, <code>ejemplo.com</code> o correos mal escritos generan rebotes directos que dañan la reputación del servidor.</li>
                                            <li><strong>Protección automática por umbrales:</strong> Si una lista de destinatarios genera más del 5% de rebotes o reportes de spam, el sistema <strong>pausará automáticamente el despacho masivo de tu cuenta</strong> para auditoría.</li>
                                            <li><strong>Envíos dosificados y Cooldown:</strong> Para garantizar un 99% de entrada a bandeja principal (inbox), los correos se envían de forma escalonada con un período de enfriamiento de 24 horas por grupo.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            @if(app()->environment('local'))
                                <div class="alert alert-info border-0 mt-2 mb-0 py-2 small">
                                    <i class="bi bi-laptop me-1"></i> <strong>Modo Desarrollo Local:</strong> Las campañas envían un máximo de 2 correos hacia <code>scastellano10@gmail.com</code> como prueba de simulación segura.
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($groups->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inboxes text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="fw-bold">No tienes grupos creados</h5>
                            <p class="text-muted">Las campañas masivas requieren al menos un grupo con sensores para poder despachar notificaciones.</p>
                            <a href="/groups" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-circle me-1"></i> Crear mi primer Grupo
                            </a>
                        </div>
                    @elseif($groups->sum('sensors_count') == 0)
                        <div class="text-center py-5">
                            <i class="bi bi-diagram-3 text-warning mb-3" style="font-size: 3rem;"></i>
                            <h5 class="fw-bold">Grupos vacíos</h5>
                            <p class="text-muted">Tienes grupos, pero ninguno contiene sensores. Agrega sensores a tus grupos para iniciar una campaña.</p>
                            <a href="/sensors" class="btn btn-warning mt-2 fw-bold text-dark">
                                <i class="bi bi-node-plus-fill me-1"></i> Ir a Sensores
                            </a>
                        </div>
                    @else
                        <form id="campaignForm" class="mt-4 p-3 bg-white border rounded shadow-sm">
                            <div class="mb-4">
                                <label class="form-label fw-bold">1. Seleccionar Destino (Grupo de Lotes / Sensores)</label>
                                <select class="form-select form-select-lg" id="group_id" required>
                                    <option value="">Selecciona el grupo o barrio a notificar...</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ $group->sensors_count == 0 ? 'disabled' : '' }}>
                                            🌐 {{ $group->name }} ({{ $group->sensors_count }} sensores)
                                            {{ $group->sensors_count == 0 ? '- Vacío' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text mt-2"><i class="bi bi-arrow-return-right"></i> Todos los propietarios de
                                    este grupo recibirán su enlace de acceso privado directo al celular.</div>
                            </div>

                            <!-- Panel de Exportación Manual -->
                            <div id="manualExportPanel" class="mb-4 p-3 bg-light border border-secondary rounded shadow-sm"
                                style="display: none;">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-printer"></i> ¿No tienes correos
                                    electrónicos?</h6>
                                <p class="small text-muted mb-2">Puedes descargar la lista entera de medidores de este grupo con
                                    sus respectivos enlaces QRs y entregarlos en papel a la vieja escuela.</p>
                                <a id="btnExportCSV" href="#" target="_blank" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-file-earmark-excel"></i> Descargar listado manual (Excel / CSV)
                                </a>
                                <button type="button" id="btnPrintQRs" class="btn btn-sm btn-outline-primary ms-2"
                                    style="display: none;" onclick="printGroupQRs()">
                                    <i class="bi bi-qr-code-scan"></i> Imprimir Grilla de Códigos QR
                                </button>
                            </div>

                            <div class="mb-4" id="emailFieldSelectorContainer" style="display: none;">
                                <label class="form-label fw-bold"><i class="bi bi-envelope text-primary"></i> 2. Mapear Campo de
                                    Correo Electrónico</label>
                                <select class="form-select border-primary" id="email_field" required disabled>
                                    <option value="">Cargando campos de la plantilla...</option>
                                </select>
                                <div class="form-text" style="opacity: 0.85;"><i class="bi bi-info-circle text-primary"></i>
                                    Selecciona cuál de tus campos personalizados de la plantilla aloja el correo electrónico de
                                    los residentes. Los medidores con este campo vacío serán ignorados.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">3. Mensaje Adjunto (Opcional)</label>
                                <textarea class="form-control" id="message_body" rows="3"
                                    placeholder="Ej: Estimado propietario, adjunto a la presente le hacemos llegar el botón de acceso total hacia las mediciones auditadas de su lote para este semestre."></textarea>
                                <div class="form-text">Si dejas este campo en blanco, sólo se enviará la grilla genérica de
                                    bienvenida con el link.</div>
                            </div>

                            <div class="mb-4 p-3 bg-light rounded border">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="only_new" value="1">
                                    <label class="form-check-label fw-bold text-dark" for="only_new">
                                        <i class="bi bi-person-check-fill text-success me-1"></i> Notificar únicamente a medidores nuevos (ahorro de envíos)
                                    </label>
                                    <div class="form-text small text-muted">
                                        Si marcas esta casilla, se omitirán los sensores que ya recibieron su enlace/QR previamente, enviando el correo solo a nuevas incorporaciones de este grupo.
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="button" id="btnDispatch" class="btn btn-primary btn-lg fw-bold">
                                    <i class="bi bi-send-check-fill"></i> Iniciar Despacho Masivo por E-Mail
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Javascript QR Generator CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
        $('#group_id').change(function () {
            const groupId = $(this).val();
            const container = $('#emailFieldSelectorContainer');
            const select = $('#email_field');
            const exportPanel = $('#manualExportPanel');

            if (!groupId) {
                container.slideUp();
                exportPanel.slideUp();
                $('#btnPrintQRs').hide();
                return;
            }

            container.slideDown();
            exportPanel.slideDown();
            $('#btnExportCSV').attr('href', `/campaigns/bulk/export-links/${groupId}`);
            $('#btnPrintQRs').show();
            select.prop('disabled', true).html('<option value="">Cargando campos...</option>');

            $.ajax({
                url: `/campaigns/bulk/schema/${groupId}`,
                type: 'GET',
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') },
                success: function (res) {
                    if (res.success && res.fields.length > 0) {
                        let options = '<option value="">-- Selecciona el campo de e-mail --</option>';
                        res.fields.forEach(f => {
                            options += `<option value="${f.nombre}">${f.nombre} (${f.tipo})</option>`;
                        });
                        select.html(options).prop('disabled', false);
                    } else {
                        select.html('<option value="">Este grupo no tiene campos en su plantilla</option>');
                    }
                },
                error: function () {
                    select.html('<option value="">Error al cargar plantilla</option>');
                }
            });
        });

        $('#btnDispatch').click(function () {
            const groupId = $('#group_id').val();
            const emailField = $('#email_field').val();
            const msg = $('#message_body').val();
            const onlyNew = $('#only_new').is(':checked') ? 1 : 0;

            if (!groupId) {
                showLocalAlert('Atención: Debes seleccionar un grupo de lotes como destino principal.', 'warning');
                return;
            }

            if (!emailField) {
                showLocalAlert('Atención: Debes indicarnos cuál de tus campos de plantilla contiene el correo de los propietarios.', 'warning');
                return;
            }

            let btn = $(this);
            let originalText = btn.html();

            const confirmMsg = "⚠️ ¿Confirmar inicio de campaña masiva?\n\n" +
                "• Los correos deben ser reales y válidos. MedFlow audita los rebotes mediante Amazon SES.\n" +
                "• Si la lista genera más de un 5% de rebotes o quejas de spam, la emisión se pausará automáticamente.\n" +
                "• Los correos se enviarán de forma escalonada para proteger la entrega a bandeja principal.\n\n" +
                "¿Deseas comenzar el encolado ahora?";

            if (confirm(confirmMsg)) {
                btn.addClass('disabled').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Encolando y validando...');

                $.ajax({
                    url: '{{ route("campaigns.bulk.dispatch") }}',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        group_id: groupId,
                        email_field: emailField,
                        message_body: msg,
                        only_new: onlyNew
                    },
                    success: function (res) {
                        btn.removeClass('disabled').html(originalText);
                        if (res.success) {
                            showLocalAlert(res.message, 'success');
                            $('#message_body').val(''); // Limpiar si desean relanzar
                        } else {
                            showLocalAlert(res.message, 'danger');
                        }
                    },
                    error: function (xhr) {
                        btn.removeClass('disabled').html(originalText);
                        const errorMsg = xhr.responseJSON?.message || 'Error grave al lanzar la campaña.';
                        showLocalAlert(errorMsg, 'danger');
                    }
                });
            }
        });

        // Simple alert function fallback (as modern layout normally uses SWAL or a general function)
        function showLocalAlert(msg, type) {
            if (typeof window.showAlert === 'function') {
                window.showAlert(msg, type); // Usa la de Medflow si existe (generalmente global en modern scripts)
            } else {
                alert(msg);
            }
        }

        // ==========================================
        // QR Code Generator & Print Engine
        // ==========================================
        function printGroupQRs() {
            const groupId = $('#group_id').val();
            if (!groupId) return;

            let btn = $('#btnPrintQRs');
            let originalHtml = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Preparando grilla...');

            // Fetch JSON Data from Backend Exporter using Content Negotiation
            $.ajax({
                url: `/campaigns/bulk/export-links/${groupId}`,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                },
                success: function (response) {
                    btn.prop('disabled', false).html(originalHtml);

                    if (response.success && response.data.length > 0) {
                        generatePrintWindow(response.data);
                    } else {
                        showLocalAlert('No hay medidores disponibles para este grupo.', 'warning');
                    }
                },
                error: function (xhr) {
                    btn.prop('disabled', false).html(originalHtml);
                    showLocalAlert('Hubo un error al intentar recolectar los enlaces públicos.', 'danger');
                }
            });
        }

        function generatePrintWindow(sensorsData) {
            // Create a specialized popup for Printing
            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                showLocalAlert('Permite los Pop-ups del navegador para imprimir.', 'warning');
                return;
            }

            let htmlBase = `
                        <html>
                        <head>
                            <title>Grilla de QRs - Medflow</title>
                            <style>
                                body { font-family: 'Inter', sans-serif; margin: 0; padding: 20px; color: #000; background: #fff; }
                                .grid-container { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
                                .qr-card { 
                                    border: 1px dashed #666; 
                                    padding: 15px; 
                                    text-align: center; 
                                    width: 200px;
                                    page-break-inside: avoid; /* Essential parameter so cards don't split half-way across pages */
                                    display: flex; flex-direction: column; align-items: center; justify-content: center;
                                }
                                .qr-title { font-size: 15px; font-weight: bold; margin-bottom: 5px; }
                                .qr-subtitle { font-size: 11px; margin-bottom: 12px; color: #555; }
                                .qr-image { width: 150px; height: 150px; }

                                @media print {
                                    body { margin: 0; padding: 0; }
                                    .no-print { display: none !important; }
                                }

                                /* A4 Paper setup for browsers */
                                @page { size: A4 portrait; margin: 1.5cm; }
                            </style>
                            <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>
                        </head>
                        <body>
                            <div style="text-align: center; margin-bottom: 25px;" class="no-print">
                                <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; background: #0d6efd; color: white; border: none; border-radius: 5px; cursor: pointer;">
                                    🖨️ Desplegar Propiedades de Impresión
                                </button>
                                <p style="margin-top: 10px; font-size: 13px; color: #666;">Nota: Una vez impresa, asegúrate de recortar los QRs por las líneas punteadas.</p>
                            </div>

                            <div class="grid-container" id="qrContainer"></div>

                            <script>
                                window.onload = function() {
                                    const data = ${JSON.stringify(sensorsData)};
                                    const container = document.getElementById('qrContainer');

                                    data.forEach((sensor, index) => {
                                        // Prepare Card Wrapper
                                        const card = document.createElement('div');
                                        card.className = 'qr-card';

                                        const title = document.createElement('div');
                                        title.className = 'qr-title';
                                        title.innerText = sensor.name;

                                        const sub = document.createElement('div');
                                        sub.className = 'qr-subtitle';
                                        sub.innerText = 'Medflow ID: #' + sensor.id;

                                        const qrBox = document.createElement('div');
                                        qrBox.className = 'qr-image';
                                        qrBox.id = 'qr_' + index;

                                        card.appendChild(title);
                                        card.appendChild(sub);
                                        card.appendChild(qrBox);
                                        container.appendChild(card);

                                        // Generate QR matrix
                                        new QRCode(document.getElementById('qr_' + index), {
                                            text: sensor.url,
                                            width: 150,
                                            height: 150,
                                            colorDark : "#000000",
                                            colorLight : "#ffffff",
                                            correctLevel : QRCode.CorrectLevel.M
                                        });
                                    });

                                    window.print();
                                };
                            <\/script>
                        </body>
                        </html>
                    `;

            printWindow.document.open();
            printWindow.document.write(htmlBase);
            printWindow.document.close();
        }
    </script>
@endpush