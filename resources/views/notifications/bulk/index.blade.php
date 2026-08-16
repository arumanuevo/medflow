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

                    <div class="alert alert-info border-info d-flex align-items-center">
                        <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                        <div>
                            <strong>Manejo Automático de Envio:</strong> El sistema utilizará colas (Queues) para proteger
                            los envíos masivos. Puede procesar miles de correos de forma automática.
                            @if(app()->environment('local'))
                                <br><strong class="text-danger mt-1 d-block"><i class="bi bi-shield-lock-fill"></i> MODO
                                    DESARROLLO (DEV TRAP):</strong> Se encuentra en local. El sistema validará todo, pero
                                ignorará las direcciones y <strong>SÓLO enviará 1 correo</strong> a
                                <code>scastellano10@gmail.com</code> para protección anti-spam de prueba.
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
                                <textarea class="form-control" id="message_body" rows="4"
                                    placeholder="Ej: Estimado propietario, adjunto a la presente le hacemos llegar el botón de acceso total hacia las mediciones auditadas de su lote para este semestre."></textarea>
                                <div class="form-text">Si dejas este campo en blanco, sólo se enviará la grilla genérica de
                                    bienvenida con el link.</div>
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

            if (confirm("¿Confirmar emisión? Se comenzará el despachador de emails masivo para este barrio/grupo utilizando el campo " + emailField + ".")) {
                btn.addClass('disabled').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Encolando...');

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
                        message_body: msg
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
                        showLocalAlert(xhr.responseJSON?.message || 'Error grave al lanzar la campaña.', 'danger');
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