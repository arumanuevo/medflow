<!DOCTYPE html>
<html>

<head>
    <title>Aviso de Pago - Emisión de Factura AFIP</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h2 style="color: #4CAF50;">¡Nuevo Pago Acreditado en MedFlow!</h2>

        <p>Hola Administrador,</p>
        <p>Un cliente acaba de realizar su pago de suscripción y es necesario emitirle la factura correspondiente
            (AFIP).</p>

        <div style="background-color: #f9f9f9; padding: 15px; margin: 20px 0; border-left: 4px solid #4CAF50;">
            <h3 style="margin-top: 0;">Datos del Cliente</h3>
            <p><strong>Nombre/Razón Social:</strong> {{ $user->name }}</p>
            <p><strong>Email de Registro:</strong> {{ $user->email }}</p>
            <p><strong>Email a enviar Factura:</strong> {{ $user->email_facturacion ?? $user->email }}</p>
            <p><strong>CUIT:</strong> {{ $user->cuit ?? 'No cargado' }}</p>
            <p><strong>Condición IVA:</strong> {{ $user->condicion_iva ?? 'No cargada' }}</p>
            <p><strong>Condición de Venta:</strong> {{ $user->condicion_venta ?? 'No indicada' }}</p>
            <p><strong>Concepto / Descripción a Facturar:</strong>
                {{ $user->descripcion_servicio ?? ('Suscripción MedFlow - Plan ' . strtoupper($subscription->plan)) }}
            </p>

            <h3 style="margin-bottom: 0;">Datos del Pago</h3>
            <p><strong>Monto:</strong> ${{ number_format($subscription->amount, 2, ',', '.') }}
                {{ $subscription->currency }}
            </p>
            <p><strong>Plan:</strong> {{ strtoupper($subscription->plan) }}</p>
            <p><strong>Ticket / Referencia de Pago:</strong> {{ $subscription->payment_id ?? 'N/A' }}</p>
        </div>

        <p>Por favor, ingresá al portal de AFIP y emití la factura correspondiente utilizando estos datos.</p>

        <br>
        <p>Saludos,<br><strong>Equipo Automático MedFlow</strong></p>
    </div>
</body>

</html>