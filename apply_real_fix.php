<?php
$emailPath = 'k:\desarrollo\medflow\resources\views\emails\mobile_access.blade.php';
$content = file_get_contents($emailPath);

$pattern = '/<div class="body">.*?<div class="footer">/s';

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

                <p style="color:#e2e8f0; font-weight: 600; margin-bottom: 10px;">Paso 2: Sincronizar (Token de Acceso)</p>
                <p>
                    Abre la aplicación que acabas de instalar. En la pantalla principal, se te solicitará un código para ingresar. <strong>Copia el siguiente Token y pégalo allí</strong> para habilitar tu usuario y descargar las rutas asignadas.
                </p>

                @php
                    \$queryStr = parse_url(\$deepLink, PHP_URL_QUERY) ?? '';
                    parse_str(\$queryStr, \$params);
                    \$syncToken = \$params['token'] ?? 'TOKEN INVALIDO';
                @endphp

                <div style="background:#0f1117; border:2px dashed #0ea5e9; border-radius:10px; padding:18px 20px; font-family: 'Courier New', monospace; font-size:15px; color:#38bdf8; word-break:break-all; text-align: center; font-weight: bold; margin: 25px 0;">
                    {{ \$syncToken }}
                </div>

                <div class="warning">
                    <p>⚠️ Este enlace y token son personales e intransferibles. El acceso puede ser revocado por el administrador en cualquier momento.</p>
                </div>
            </div>

            <div class="footer">
EOT;

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($emailPath, $content);
echo "BLADE FILE UPDATED SUCCESFULLY\n";
