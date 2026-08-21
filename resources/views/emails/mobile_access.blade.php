<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Inspector Móvil - MedFlow</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #0f1117;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px 15px;
        }

        .card {
            background: #1a1d2e;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #2a2d3e;
        }

        .header {
            background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
            padding: 36px 32px;
            text-align: center;
        }

        .header img {
            width: 48px;
            margin-bottom: 12px;
        }

        .header h1 {
            color: #fff;
            font-size: 22px;
            margin: 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.8);
            margin: 8px 0 0;
            font-size: 14px;
        }

        .body {
            padding: 36px 32px;
        }

        .sender-info {
            background: #0f1117;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 24px;
            border-left: 4px solid #0ea5e9;
        }

        .sender-info p {
            color: #94a3b8;
            margin: 0;
            font-size: 14px;
        }

        .sender-info strong {
            color: #f1f5f9;
        }

        .scope-pill {
            display: inline-block;
            background: #1e3a5f;
            color: #60a5fa;
            border-radius: 999px;
            padding: 5px 14px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
            border: 1px solid #2563eb30;
        }

        .body p {
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.7;
            margin: 0 0 28px;
        }

        .cta-wrapper {
            text-align: center;
            margin: 32px 0;
        }

        .cta-btn {
            display: inline-block;
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
            color: #fff !important;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .warning {
            background: #1e1a0e;
            border: 1px solid #854d0e40;
            border-radius: 10px;
            padding: 14px 18px;
            margin-top: 28px;
        }

        .warning p {
            color: #ca8a04;
            font-size: 13px;
            margin: 0;
        }

        .footer {
            text-align: center;
            padding: 20px 32px 32px;
        }

        .footer p {
            color: #4b5563;
            font-size: 12px;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>💧 MedFlow Inspector</h1>
                <p>Invitación de acceso al sistema de mediciones</p>
            </div>

            <div class="body">
                <div class="sender-info">
                    <p>Invitado por: <strong>{{ $senderName }}</strong></p>
                </div>

                @if($groupName !== '')
                    <span class="scope-pill">📍 Ruta Asignada: Grupo {{ $groupName }}</span>
                @else
                    <span class="scope-pill">📍 Ruta Asignada: Acceso completo a la planta</span>
                @endif

                <p>
                    Tocá el botón de abajo desde tu teléfono celular para sincronizar la aplicación
                    <strong style="color:#f1f5f9">MedFlow Inspector</strong> con tu área de trabajo.
                    Una vez sincronizado, podrás tomar mediciones en campo <strong style="color:#f1f5f9">sin necesidad
                        de Internet</strong>.
                </p>

                <div class="cta-wrapper">
                    <a href="{{ $deepLink }}" class="cta-btn">
                        📱 Sincronizar mi Dispositivo
                    </a>
                </div>

                @php
                    $queryStr = parse_url($deepLink, PHP_URL_QUERY) ?? '';
                    parse_str($queryStr, $params);
                    $syncToken = $params['token'] ?? null;
                    $workspaceId = $params['workspace'] ?? null;
                    $sensorLimitParam = $params['limit'] ?? 0;
                @endphp

                @if($syncToken)
                    <div class="manual-sync">
                        <p style="color:#94a3b8;font-size:13px;margin:0 0 10px;font-weight:600;">¿No se abre la app? Copiá
                            este token y pegalo manualmente en MedFlow Inspector:</p>
                        <div
                            style="background:#0f1117;border:1px dashed #334155;border-radius:8px;padding:12px 14px;font-family:monospace;font-size:12px;color:#60a5fa;word-break:break-all;letter-spacing:0.3px;">
                            {{ $syncToken }}</div>
                        @if($workspaceId)
                            <p style="color:#475569;font-size:11px;margin:8px 0 0;">Workspace: <strong
                                    style="color:#64748b">{{ $workspaceId }}</strong> @if($sensorLimitParam > 0)· Límite de
                                    sensores: <strong style="color:#64748b">{{ $sensorLimitParam }}</strong>@endif</p>
                        @endif
                    </div>
                @endif

                <div class="warning">
                    <p>⚠️ Este enlace es personal e intransferible. No lo compartas con terceros. El acceso puede ser
                        revocado por el administrador en cualquier momento.</p>
                </div>
            </div>

            <div class="footer">
                <p>MedFlow &mdash; Sistema de Inspección y Mediciones &bull; {{ date('Y') }}</p>
            </div>
        </div>
    </div>
</body>

</html>