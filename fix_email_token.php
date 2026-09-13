<?php
$emailPath = 'k:\desarrollo\medflow\resources\views\emails\mobile_access.blade.php';
$content = file_get_contents($emailPath);

// Rediseñando el body otra vez: Quitando el deeplink y poniendo el Token explicito
$pattern = '/<p style="color:#e2e8f0; font-weight: 600; margin-bottom: 10px;">Paso 2: Conectar la Cuenta<\/p>.*?<\/div>\s*<\/div>\s*<div class="warning">/s';

$replacement = <<<EOT
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
EOT;

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($emailPath, $content);

echo "Token restored and DeepLink removed!\n";
