<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Aviso de Retención de Evidencias</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fc; padding: 20px;">
    <div
        style="max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">

        <h2 style="color: #4e73df; margin-top: 0;">MedFlow | Vencimiento de Archivos</h2>

        <p>Hola <strong>{{ $user->name }}</strong>,</p>

        <p>Te escribimos para avisarte que por políticas de optimización de espacio y velocidad del servidor, las
            evidencias fotográficas de tus lecturas caducan automáticamente al cumplir 1 año (365 días).</p>

        <div
            style="background-color: #fff3cd; color: #856404; padding: 15px; border-left: 4px solid #ffeeba; margin: 20px 0; border-radius: 4px;">
            <strong>Aviso Importante:</strong> El algoritmo detectó que tienes <strong>{{ $count }} fotos</strong> que
            cumplirán 1 año en los próximos 7 días, y serán eliminadas físicamente del disco.
        </div>

        <p><em>No te preocupes:</em> Los datos numéricos e historiales de la medición no se borrarán y seguirán
            facturando en tus gráficos normales. Solo desaparecerá la imagen de prueba y quedará marcada como
            "Expirada".</p>

        <hr style="border: none; border-top: 1px solid #eaeaea; margin: 30px 0;">

        <p style="font-size: 13px; color: #666;">Si necesitas guardar estar evidencias por temas legales o de auditoría,
            ingresa a la plataforma MedFlow, dirígete a la sección de Backups y descarga el paquete ZIP que construirá
            tu navegador usando tu disco local, sin estresar al servidor.</p>

        <p style="font-size: 12px; color: #999; margin-top: 30px;">Equipo de Sistemas de MedFlow.</p>
    </div>
</body>

</html>