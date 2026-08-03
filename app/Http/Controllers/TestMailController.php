<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestMailController extends Controller
{
    public function sendTestEmail()
    {
        try {
            $to = 'scastellano10@gmail.com';
            $subject = '🧪 Correo de prueba desde MedFlow';
            
            $html = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Correo de prueba</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        padding: 20px;
                    }
                    .container {
                        max-width: 600px;
                        margin: 0 auto;
                        background: white;
                        padding: 30px;
                        border-radius: 8px;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    }
                    .header {
                        background: linear-gradient(135deg, #0d6efd, #0a5fd9);
                        color: white;
                        padding: 20px;
                        text-align: center;
                        border-radius: 8px 8px 0 0;
                        margin: -30px -30px 20px -30px;
                    }
                    .header h1 {
                        margin: 0;
                        font-size: 24px;
                    }
                    .success {
                        color: #155724;
                        background-color: #d4edda;
                        border: 1px solid #c3e6cb;
                        padding: 15px;
                        border-radius: 6px;
                    }
                    .info {
                        margin-top: 20px;
                        padding: 15px;
                        background: #f8f9fa;
                        border-radius: 6px;
                        font-size: 14px;
                        color: #6c757d;
                    }
                    .footer {
                        text-align: center;
                        margin-top: 20px;
                        padding-top: 20px;
                        border-top: 1px solid #eee;
                        font-size: 12px;
                        color: #888;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>📧 MedFlow</h1>
                        <p style="margin: 5px 0 0; opacity: 0.9;">Correo de prueba</p>
                    </div>
                    
                    <h2 style="color: #1a202c;">¡Hola! 👋</h2>
                    
                    <p>Este es un correo de prueba desde la aplicación <strong>MedFlow</strong>.</p>
                    
                    <div class="success">
                        <strong>✅ ¡El sistema de correo está funcionando correctamente!</strong>
                    </div>
                    
                    <div class="info">
                        <p><strong>📋 Información:</strong></p>
                        <ul style="margin: 5px 0; padding-left: 20px;">
                            <li><strong>Fecha y hora:</strong> ' . now()->format('d/m/Y H:i:s') . '</li>
                            <li><strong>Entorno:</strong> ' . app()->environment() . '</li>
                            <li><strong>Remitente:</strong> ' . config('mail.from.address') . '</li>
                            <li><strong>Destinatario:</strong> ' . $to . '</li>
                        </ul>
                    </div>
                    
                    <p style="margin-top: 20px; color: #4a5568;">
                        Si estás viendo este correo, significa que la configuración SMTP está funcionando correctamente.
                    </p>
                    
                    <div class="footer">
                        &copy; ' . date('Y') . ' MedFlow - Gestión de Sensores<br>
                        <small>Este es un correo automático de prueba.</small>
                    </div>
                </div>
            </body>
            </html>
            ';
            
            // ✅ Enviar el correo
            Mail::send([], [], function ($message) use ($to, $subject, $html) {
                $message->to($to)
                        ->subject($subject)
                        ->html($html);
            });
            
            Log::info('📧 Correo de prueba enviado a: ' . $to);
            
            return back()->with('success', '✅ Correo de prueba enviado a ' . $to . '! Revisa tu bandeja de entrada y spam.');
            
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar correo de prueba: ' . $e->getMessage());
            
            return back()->with('error', '❌ Error al enviar correo: ' . $e->getMessage());
        }
    }
}