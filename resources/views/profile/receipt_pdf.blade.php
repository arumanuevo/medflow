<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago #{{ $subscription->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            color: #0056b3;
        }

        .header p {
            margin: 5px 0 0 0;
            color: #777;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .details th,
        .details td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .details th {
            background-color: #f9f9f9;
            width: 40%;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            margin-top: 50px;
        }

        .disclaimer {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>MedFlow</h1>
        <p>Recibo de Pago Provisorio</p>
    </div>

    <table class="details">
        <tr>
            <th>Nro. de Recibo Interno:</th>
            <td>#{{ str_pad($subscription->id, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <th>Fecha de Emisión:</th>
            <td>{{ $subscription->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <th>Cliente:</th>
            <td>{{ $user->name }} ({{ $user->email }})</td>
        </tr>
        <tr>
            <th>CUIT:</th>
            <td>{{ $user->cuit ?? 'No declarado' }}</td>
        </tr>
        <tr>
            <th>Condición frente al IVA:</th>
            <td>{{ $user->condicion_iva ?? 'No declarada' }}</td>
        </tr>
        <tr>
            <th>Condición de Venta:</th>
            <td>{{ $user->condicion_venta ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Concepto / Descripción:</th>
            <td>{{ $user->descripcion_servicio ?? ('Suscripción MedFlow - Plan ' . strtoupper($subscription->plan)) }}
            </td>
        </tr>
        <tr>
            <th>Estado:</th>
            <td style="color: green; font-weight: bold;">PAGADO</td>
        </tr>
        <tr>
            <th>Referencia / ID de Pago:</th>
            <td>{{ $subscription->payment_id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Monto Total:</th>
            <td style="font-size: 18px; font-weight: bold;">${{ number_format($subscription->amount, 2, ',', '.') }}
                {{ $subscription->currency }}
            </td>
        </tr>
    </table>

    <div class="disclaimer">
        ESTE DOCUMENTO ES UN COMPROBANTE DE PAGO INTERNO NO VÁLIDO COMO FACTURA (DOCUMENTO NO FISCAL).<br><br>
        La Factura Electrónica (AFIP) será generada y enviada a su dirección de correo electrónico a la brevedad,
        utilizando los datos fiscales declarados en su perfil de usuario.
    </div>

    <div class="footer">
        Documento generado automáticamente por la Plataforma MedFlow el
        {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}<br>
        Para consultas administrativas, por favor contáctese a administracion@medflow.com
    </div>
</body>

</html>