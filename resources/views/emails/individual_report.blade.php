<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Informe de Consumos Avanzado</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px;">
    <div
        style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <!-- Encabezado -->
        <div style="background-color: #0056b3; color: #ffffff; padding: 20px; text-align: center;">
            <h2 style="margin: 0; font-size: 24px;">Informe de Consumos</h2>
            <p style="margin: 5px 0 0 0; opacity: 0.8;">Plataforma Inteligente MedFlow</p>
        </div>

        <!-- Cuerpo -->
        <div style="padding: 30px;">
            <p style="font-size: 16px; font-weight: bold; margin-bottom: 20px;">Estimado cliente,</p>

            <div style="font-size: 15px; line-height: 1.6; color: #555;">
                <p>La administración ha emitido un informe de consumo detallado con respecto al punto de medición:
                    <strong>{{ $sensor->name }}</strong> (ID: {{ $sensor->identifier }}).
                </p>

                @if(isset($financialText) && $financialText)
                    <div style="background-color: #e8f5e9; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;">
                        <p style="margin: 0; font-size: 16px; color: #155724;">
                            <strong>Liquidación Proyectada:</strong> {{ $financialText }}
                        </p>
                    </div>
                @endif

                <p>Puedes acceder a la auditoría analítica interactiva y visualizar tu nivel de varianza tocando el
                    siguiente botón seguro:</p>
            </div>

            <div style="text-align: center; margin: 35px 0;">
                <a href="{{ $url }}"
                    style="background-color: #28a745; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block;">
                    Abrir Informe Avanzado
                </a>
            </div>

            <p style="font-size: 14px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                Si el botón superior no funciona, copia y pega este enlace en tu navegador:<br>
                <a href="{{ $url }}" style="color: #0056b3; word-break: break-all;">{{ $url }}</a>
            </p>
        </div>
    </div>
</body>

</html>