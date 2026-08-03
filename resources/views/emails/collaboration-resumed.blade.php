<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso reanudado</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px 20px; }
        .content p { line-height: 1.6; color: #333; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; border-top: 1px solid #eee; }
        .btn { display: inline-block; background: #28a745; color: white; padding: 10px 25px; text-decoration: none; border-radius: 6px; }
        .btn:hover { background: #1e7e34; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>▶️ Acceso reanudado</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $collaborator->user->name }}</strong>,</p>
            <p>
                <strong>{{ $owner->name }}</strong> ha <strong>reanudado</strong> tu acceso al espacio de trabajo <strong>{{ $workspaceName }}</strong>.
            </p>
            <p>Ahora puedes nuevamente:</p>
            <ul>
                <li>Ver los sensores de este espacio</li>
                <li>Tomar mediciones</li>
                <li>Acceder a los datos del espacio</li>
            </ul>
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