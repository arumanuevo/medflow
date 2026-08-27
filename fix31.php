<?php
$c = file_get_contents('resources/views/landing.blade.php');
$c = preg_replace('/(Delegaci.*?n completa de inspectores y rutas<\/div>\s*<\/div>)/is', "$1\n                          <a href=\"#contacto\" class=\"btn btn-dark w-100 mt-4 rounded-pill fw-bold shadow-sm\"><i class=\"bi bi-headset me-2\"></i>Contactar Asesor</a>", $c);
file_put_contents('resources/views/landing.blade.php', $c);
echo "Injected Button via Regex\n";
