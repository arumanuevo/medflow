<?php
$emailPath = 'k:\desarrollo\medflow\resources\views\emails\mobile_access.blade.php';
$content = file_get_contents($emailPath);

// Rediseñando el body completo del email
$pattern = '/<div class="body">.*<div class="footer">/s';

$replacement = <<<EOT
<div class="body">
                <div class="sender-info">
                    <p>Invitado por: <strong>{{ \$senderName }}</strong></p>
                </div>

                @if(\$groupName !== '')
                    <span class="scope-pill">📍 Ruta Asignada: Grupo {{ \$groupName }}</span>
                @else
                    @if(\$sensorLimit > 0)
                        <span class="scope-pill">🔒 Acceso limitado a {{ \$sensorLimit }} sensores</span>
                    @else
                        <span class="scope-pill">🔓 Acceso completo a todos los sensores</span>
                    @endif
                @endif

                <p style="color:#e2e8f0; font-weight: 600; margin-bottom: 10px;">Paso 1: Instala la Herramienta</p>
                <p>
                    Si aún no tienes la aplicación en tu celular, pulsa para descargarla. Si tu sistema bloquea temporalmente la instalación por seguridad, marca <strong>"Instalar de todas formas"</strong>.
                </p>
                <div style="margin-bottom: 30px;">
                    <a href="{{ url('/inspector/descargar-app') }}"
                        style="display:inline-block; background-color:#1e293b; color:#38bdf8; text-decoration:none; padding:10px 16px; border-radius:6px; font-weight:600; font-size:13px; border:1px solid #334155;">
                        ⬇️ Descargar e Instalar (APK)
                    </a>
                </div>

                <p style="color:#e2e8f0; font-weight: 600; margin-bottom: 10px;">Paso 2: Conectar la Cuenta</p>
                <p>
                    Una vez que tengas la App instalada, pulsa el siguiente botón desde tu teléfono para vincular automáticamente tu perfil y empezar a trabajar (incluso sin internet).
                </p>

                <div class="cta-wrapper" style="margin-top: 15px;">
                    <a href="{{ \$deepLink }}" class="cta-btn">
                        📱 Vincular mi Dispositivo
                    </a>
                </div>

                <div class="warning">
                    <p>⚠️ Este enlace es personal e intransferible. El acceso puede ser revocado por el administrador en cualquier momento.</p>
                </div>
            </div>

            <div class="footer">
EOT;

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($emailPath, $content);
echo "Email template fixed and modernized!\n";
