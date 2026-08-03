<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación a colaborar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #0d6efd, #0a5fd9);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px 20px;
        }
        .content p {
            line-height: 1.6;
            color: #333;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #0d6efd, #0a5fd9);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .btn:hover {
            background: #0b5ed7;
        }
        .btn-secondary {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #888;
            font-size: 12px;
            border-top: 1px solid #eee;
        }
        .message-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #0d6efd;
            margin: 15px 0;
        }
        .warning {
            color: #856404;
            background-color: #fff3cd;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 14px;
        }
        .company-name {
            color: #0d6efd;
            font-weight: bold;
        }
        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 20px 0;
        }
        .badge {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .new-user-box {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .new-user-box p {
            margin: 0;
            color: #155724;
        }
        .new-user-box ol {
            margin: 5px 0 0;
            padding-left: 20px;
            color: #155724;
            font-size: 14px;
        }
        .new-user-box ol li {
            margin-bottom: 4px;
        }
        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
            margin: 20px 0;
        }
        .btn-container .btn {
            width: 100%;
            text-align: center;
        }
        @media (min-width: 480px) {
            .btn-container {
                flex-direction: row;
                justify-content: center;
            }
            .btn-container .btn {
                width: auto;
                min-width: 200px;
            }
        }
        .steps-box {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px 20px;
            margin: 15px 0;
            border-left: 4px solid #0d6efd;
        }
        .steps-box ol {
            margin: 8px 0 0;
            padding-left: 20px;
        }
        .steps-box ol li {
            margin-bottom: 6px;
            font-size: 14px;
            line-height: 1.5;
        }
        .highlight {
            font-weight: 600;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 <span class="company-name" style="color:white;">{{ $appName }}</span></h1>
            <p style="margin: 5px 0 0; opacity: 0.9;">Invitación a colaborar</p>
        </div>

        <div class="content">
            <p style="font-size: 18px; color: #1a202c;">
                Hola <strong>{{ $invitedUser->name }}</strong> 👋
            </p>

            <p>
                <strong>{{ $inviter->name }}</strong> te ha invitado a colaborar en su espacio de trabajo en <strong>{{ $appName }}</strong>.
                Ahora podrás acceder a sus sensores y mediciones.
            </p>

            @if(isset($isNewUser) && $isNewUser)
                <div class="new-user-box">
                    <p>
                        <strong>🆕 ¡Cuenta creada automáticamente!</strong><br>
                        Se ha creado una cuenta para ti con este correo electrónico.
                        <strong>No necesitas registrarte</strong>, solo debes establecer tu contraseña.
                    </p>
                </div>

                <div class="steps-box">
                    <p style="margin: 0; font-weight: 600;">📋 Pasos para aceptar la invitación:</p>
                    <ol>
                        <li><span class="highlight">Establece tu contraseña</span> usando el botón <strong>"🔑 Establecer Contraseña"</strong> que está más abajo.</li>
                        <li>Luego <span class="highlight">inicia sesión</span> con tu correo electrónico y la contraseña que creaste.</li>
                        <li>Finalmente, <span class="highlight">acepta la invitación</span> haciendo clic en <strong>"✅ Aceptar Invitación"</strong>.</li>
                    </ol>
                </div>

                <div style="text-align: center; margin: 20px 0;">
                    <a href="{{ url('/establecer-contraseña/' . base64_encode($invitedUser->email)) }}" class="btn" style="font-size: 16px; padding: 14px 40px; background: linear-gradient(135deg, #28a745, #1e7e34);">
                        🔑 Establecer Contraseña
                    </a>
                </div>
            @endif

            @if(isset($personalMessage) && $personalMessage)
                <div class="message-box">
                    <strong>📝 Mensaje de {{ $inviter->name }}:</strong>
                    <p style="margin: 5px 0 0; font-style: italic;">"{{ $personalMessage }}"</p>
                </div>
            @endif

            <div class="btn-container">
                <a href="{{ $acceptUrl }}" class="btn" style="font-size: 16px; padding: 14px 40px;">
                    ✅ Aceptar Invitación
                </a>
                <a href="{{ url('/login') }}" class="btn-secondary" style="font-size: 16px; padding: 14px 40px;">
                    🔐 Iniciar Sesión
                </a>
            </div>

            <div class="warning" style="font-size: 13px;">
                ⚠️ Esta invitación expirará en <strong>7 días</strong>.
            </div>

            <div class="divider"></div>

            <p style="font-size: 13px; color: #718096;">
                <strong>¿Qué es {{ $appName }}?</strong><br>
                Es una plataforma para la gestión de sensores y mediciones en obras civiles, industrias y hogares.
            </p>

            <p style="font-size: 13px; color: #718096; margin-top: 10px;">
                Si no esperabas esta invitación, puedes <strong>ignorar este correo</strong>.
            </p>

            <div style="margin-top: 20px; padding: 12px; background-color: #f8f9fa; border-radius: 6px; font-size: 12px; color: #6c757d; text-align: center;">
                <span class="badge" style="background-color: #6c757d;">Aviso</span>
                Este es un mensaje automático del sistema {{ $appName }}.
                Por favor, no respondas a este correo.
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0;">
                &copy; {{ date('Y') }} {{ $appName }}. Todos los derechos reservados.
            </p>
            <p style="margin: 5px 0 0; font-size: 11px; color: #aaa;">
                <a href="{{ url('/') }}" style="color: #0d6efd; text-decoration: none;">{{ url('/') }}</a>
            </p>
        </div>
    </div>
</body>
</html>