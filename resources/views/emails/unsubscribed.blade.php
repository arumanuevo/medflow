<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscripción Cancelada | MedFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .card-box {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            padding: 35px;
            text-align: center;
        }
        .icon-circle {
            width: 65px;
            height: 65px;
            background: #dcfce7;
            color: #16a34a;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="card-box">
        <div class="icon-circle">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h3 class="fw-bold mb-2">Preferencia Actualizada</h3>
        <p class="text-muted mb-4">
            @if(!empty($email))
                La dirección <strong>{{ $email }}</strong> ha sido removida de la lista de notificaciones por correo de MedFlow.
            @else
                Tu correo ha sido removido exitosamente de la lista de notificaciones.
            @endif
        </p>
        <div class="p-3 bg-light rounded text-start small text-muted mb-4">
            <i class="bi bi-info-circle me-1 text-primary"></i>
            Podrás seguir consultando tus mediciones y fotos cuando lo desees accediendo directamente mediante tu código QR físico o solicitando el enlace a tu administración.
        </div>
        <p class="small text-muted mb-0">MedFlow Analytics &bull; Gestión Inteligente de Sensores</p>
    </div>
</body>
</html>
