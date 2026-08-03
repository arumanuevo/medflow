<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso revocado</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px 20px; }
        .content p { line-height: 1.6; color: #333; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>❌ Acceso revocado</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $collaborator->user->name }}</strong>,</p>
            <p>
                <strong>{{ $owner->name }}</strong> ha <strong>revocado</strong> tu acceso al espacio de trabajo <strong>{{ $workspaceName }}</strong>.
            </p>
            <p>Esto significa que:</p>
            <ul>
                <li>Ya no tienes acceso a los sensores de este espacio</li>
                <li>No puedes ver ni tomar mediciones</li>
                <li>Los datos de este espacio ya no están disponibles para ti</li>
            </ul>
            <p style="font-size: 13px; color: #6c757d;">
                Si crees que esto es un error, contacta al propietario del espacio.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>