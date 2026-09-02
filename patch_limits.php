<?php
// Patching landing.blade.php
$file = 'k:\desarrollo\medflow\resources\views\landing.blade.php';
$content = file_get_contents($file);
$content = str_replace('Sensores sin Límite (Ilimitados)', 'Base de 20 Sensores (Packs Extra)', $content);
file_put_contents($file, $content);

// Patching profile/index.blade.php
$file = 'k:\desarrollo\medflow\resources\views\profile\index.blade.php';
$content = file_get_contents($file);
$content = str_replace('<small>Sensores ilimitados</small>', '<small>Base de 20 Sensores (+Packs)</small>', $content);
file_put_contents($file, $content);

// Checking flyer.blade.php if there's any reference to unlimited
$file = 'k:\desarrollo\medflow\resources\views\flyer.blade.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    if (strpos($content, 'ilimitado') !== false) {
        $content = str_replace('ilimitado', 'ampliable', $content);
        file_put_contents($file, $content);
    }
}

echo "Limits patched in text descriptions!\n";
