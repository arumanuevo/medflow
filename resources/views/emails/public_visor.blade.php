<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte de Consumos</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; color: #333;">

    <div
        style="max-width: 600px; margin: 0 auto; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">

        <h2 style="color: #0d6efd; margin-top: 0;">Medflow Analytics</h2>

        <p style="font-size: 16px;">Hola,</p>

        <p style="font-size: 16px;">Se ha generado una nueva actualización en tus consumos de
            <strong>{{ $sensor->name }}</strong> (ID: {{ $sensor->identifier ?? 'N/A' }}).
        </p>

        @if(!empty($messageBody))
            <div
                style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #0d6efd; margin: 20px 0; font-style: italic;">
                {{ $messageBody }}
            </div>
        @endif

        <p style="font-size: 16px;">Puedes visualizar todo el detalle histórico, estadísticas, y fotos de verificación
            ingresando a tu visor privado. <strong>No necesitas contraseña.</strong></p>

        <div style="text-align: center; margin: 35px 0;">
            <a href="{{ $publicUrl }}"
                style="background-color: #0d6efd; color: white; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-weight: bold; font-size: 16px; display: inline-block;">
                Ver Mis Consumos
            </a>
        </div>

        <p style="font-size: 14px; color: #777; text-align: center;">
            También puedes escanear este código QR desde tu celular:
            <br><br>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($publicUrl) }}"
                alt="QR Code" style="border: 1px solid #ddd; padding: 10px; border-radius: 10px;">
        </p>

        <div style="margin-top: 35px; text-align: left; font-size: 14px; color: #555;">
            <p>Atentamente,<br>
                <strong>El Equipo de Medflow Analytics</strong>
            </p>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">

        <div style="font-size: 12px; color: #888; text-align: center; line-height: 1.5;">
            <p style="margin-bottom: 8px;">
                Este correo fue emitido por la administración de tu grupo a través de <strong>Medflow</strong> para fines informativos y de transparencia métrica.
            </p>
            @if(!empty($unsubscribeUrl))
                <p style="margin-top: 6px;">
                    ¿Ya no resides en este lote o no deseas recibir notificaciones por correo? 
                    <a href="{{ $unsubscribeUrl }}" style="color: #6c757d; text-decoration: underline;">Desuscribirme de este medidor</a>.
                </p>
            @endif
        </div>
    </div>

</body>

</html>
