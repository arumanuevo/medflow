<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Comprobante de Suscripción</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 20px;">
    <div
        style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <!-- Encabezado corporativo -->
        <div style="background-color: #2F55D4; padding: 20px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0; font-size: 24px; font-weight: bold;">MedFlow</h2>
            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Departamento de Facturación</p>
        </div>

        <!-- Cuerpo -->
        <div style="padding: 30px;">
            <p style="font-size: 16px; font-weight: bold; margin-bottom: 20px;">Hola, {{ $name }}</p>

            <div style="font-size: 15px; line-height: 1.6; color: #555;">
                <p>Adjunto a este correo encontrarás el comprobante de tu suscripción en formato PDF.</p>
                <p>Agradecemos tu confianza en MedFlow para el control profesional de mediciones.</p>
            </div>

            <p style="font-size: 14px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                Si tienes alguna consulta sobre este comprobante, puedes responder directamente a este correo.
            </p>
        </div>

        <!-- Pie -->
        <div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #aaa;">
            &copy; {{ date('Y') }} MedFlow - Todos los derechos reservados.
        </div>
    </div>
</body>

</html>