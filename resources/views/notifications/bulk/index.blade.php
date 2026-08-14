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

                    <form id="campaignForm" class="mt-4 p-3 bg-white border rounded shadow-sm">
                        <div class="mb-4">
                            <label class="form-label fw-bold">1. Seleccionar Destino (Grupo de Lotes / Sensores)</label>
                            <select class="form-select form-select-lg" id="group_id" required>
                                <option value="">Selecciona el grupo o barrio a notificar...</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">🌐 {{ $group->name }} ({{ $group->sensors_count }}
                                        medidores)</option>
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
                        </div>

                        <div class="mb-4" id="emailFieldSelectorContainer" style="display: none;">
                            <label class="form-label fw-bold"><i class="bi bi-envelope text-primary"></i> 2. Mapear Campo de
                                Correo Electrónico</label>
                            <select class="form-select border-primary" id="email_field" required disabled>
                                <option value="">Cargando campos de la plantilla...</option>
                            </select>
                            <div class="form-text" style="opacity: 0.85;"><i class="bi bi-info-circle text-primary"></i>
                                Selecciona cuál de tus
                                campos personalizados de la plantilla aloja el correo electrónico de los residentes. Los
                                medidores con este campo vacío serán ignorados.</div>
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

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#group_id').change(function () {
            const groupId = $(this).val();
            const container = $('#emailFieldSelectorContainer');
            const select = $('#email_field');
            const exportPanel = $('#manualExportPanel');

            if (!groupId) {
                container.slideUp();
                exportPanel.slideUp();
                return;
            }

            container.slideDown();
            exportPanel.slideDown();
            $('#btnExportCSV').attr('href', `/campaigns/bulk/export-links/${groupId}`);
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
    </script>
@endpush