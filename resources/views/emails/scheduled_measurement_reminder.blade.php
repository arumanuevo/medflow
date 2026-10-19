<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recordatorio de Calendario de Tomas - MedFlow</title>
</head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 24px; color: #212529;">
    <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08);">
        <div style="background: linear-gradient(135deg, #0d6efd, #0a58ca); color: #ffffff; padding: 24px;">
            <h2 style="margin: 0 0 6px 0; font-size: 20px;">📅 Agenda de Tomas Programadas - MedFlow</h2>
            <p style="margin: 0; opacity: 0.9; font-size: 14px;">Hola {{ $user->name }}, tienes mediciones programadas que requieren tu atención.</p>
        </div>

        <div style="padding: 24px;">
            <p style="margin-top: 0; font-size: 14px; color: #495057;">
                A continuación te mostramos el estado actual de tus grupos y sensores según las fechas meta y días de tolerancia configurados:
            </p>

            @foreach(($alerts['items'] ?? []) as $item)
                @php
                    $borderColor = match($item['status']) {
                        'overdue' => '#dc3545',
                        'in_tolerance' => '#fd7e14',
                        'in_window' => '#ffc107',
                        default => '#0d6efd'
                    };
                    $bgColor = match($item['status']) {
                        'overdue' => '#fff5f5',
                        'in_tolerance' => '#fff8f0',
                        'in_window' => '#fffdf2',
                        default => '#f8f9fa'
                    };
                @endphp
                <div style="border-left: 4px solid {{ $borderColor }}; background: {{ $bgColor }}; padding: 14px 16px; border-radius: 8px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <strong style="font-size: 15px; color: #212529;">{{ $item['group_name'] }}</strong>
                        <span style="font-size: 12px; font-weight: 700; color: {{ $borderColor }}; text-transform: uppercase;">
                            {{ $item['label'] }}
                        </span>
                    </div>
                    <div style="font-size: 13px; color: #495057; margin-bottom: 4px;">
                        <strong>Sensores pendientes:</strong> {{ $item['pending_count'] }} de {{ $item['total_count'] }}
                        &nbsp;|&nbsp;
                        <strong>Fecha Meta:</strong> {{ \Carbon\Carbon::parse($item['target_date'])->format('d/m/Y') }}
                    </div>
                    <div style="font-size: 12px; color: #6c757d;">
                        {{ $item['message'] }} (Ventana: {{ \Carbon\Carbon::parse($item['window_start'])->format('d/m') }} al {{ \Carbon\Carbon::parse($item['grace_end'])->format('d/m/Y') }})
                    </div>
                </div>
            @endforeach

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/calendario-mediciones') }}"
                   style="display: inline-block; background-color: #0d6efd; color: #ffffff; text-decoration: none; font-weight: 600; padding: 12px 24px; border-radius: 8px; font-size: 14px;">
                    Abrir Calendario de Tomas en MedFlow
                </a>
            </div>
        </div>

        <div style="background: #f8f9fa; padding: 16px 24px; text-align: center; font-size: 12px; color: #6c757d; border-top: 1px solid #e9ecef;">
            Este es un aviso automático del Calendario de Programación de Tomas y Tolerancias de MedFlow.
        </div>
    </div>
</body>
</html>
