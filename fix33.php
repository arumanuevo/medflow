<?php
$c = file_get_contents('resources/views/landing.blade.php');
$c = preg_replace('/<a href="#contacto" class="btn btn-dark w-100 mt-4 rounded-pill fw-bold shadow-sm"><i class="bi bi-headset me-2"><\/i>Contactar Asesor<\/a>/', '', $c);
file_put_contents('resources/views/landing.blade.php', $c);
echo "Removed Button\n";
