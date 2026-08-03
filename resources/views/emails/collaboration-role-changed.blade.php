<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rol actualizado</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #0d6efd, #0a5fd9); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px 20px; }
        .content p { line-height: 1.6; color: #333; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; border-top: 1px solid #eee; }
        .role-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-weight: 600; }
        .role-old { background: #f8d7da; color: #721c24; }
        .role-new { background: #d4edda; color: #155724; }
        .btn { display: inline-block; background: #0d6efd; color: white; padding: 10px 25px; text-decoration: none; border-radius: 6px; }
        .btn:hover { background: #0b5ed7; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔄 Rol actualizado</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $collaborator->user->name }}</strong>,</p>
            <p>
                <strong>{{ $owner->name }}</strong> ha actualizado tu rol en el espacio de trabajo <strong>{{ $workspaceName }}</strong>.
            </p>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0;">
                <p style="margin: 0; font-size: 14px;">Tu rol ha cambiado de</p>
                <p style="margin: 10px 0;">
                    <span class="role-badge role-old">{{ ucfirst($oldRole) }}</span>
                    <span style="font-size: 24px; margin: 0 15px;">→</span>
                    <span class="role-badge role-new">{{ ucfirst($newRole) }}</span>
                </p>
            </div>
            <div style="background: #f0f0f0; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0; font-size: 13px; color: #495057;">
                    <strong>Nuevos permisos según tu nuevo rol:</strong>
                </p>
                <ul style="font-size: 13px; color: #495057;">
                    @if($newRole === 'admin')
                        <li>✅ Control total del espacio</li>
                        <li>✅ Gestionar sensores y mediciones</li>
                        <li>✅ Gestionar colaboradores</li>
                    @elseif($newRole === 'inspector')
                        <li>✅ Tomar mediciones</li>
                        <li>✅ Ver sensores y mediciones</li>
                        <li>❌ Gestionar colaboradores</li>
                    @else
                        <li>✅ Ver sensores y mediciones</li>
                        <li>❌ Tomar mediciones</li>
                        <li>❌ Gestionar colaboradores</li>
                    @endif
                </ul>
            </div>
            <div style="text-align: center; margin: 20px 0;">
                <a href="{{ url('/dashboard') }}" class="btn">Ir al Dashboard</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>