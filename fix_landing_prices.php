<?php
$file = 'k:\desarrollo\medflow\resources\views\landing.blade.php';
$content = file_get_contents($file);

// Ajustar los valores por defecto a $10000 y $25000 para ser consistentes con todo el backend
$content = preg_replace(
    '/\["basico" => \d+, "premium" => \d+\]/',
    '["basico" => 10000, "premium" => 25000]',
    $content
);

// Añadir la aclaración de Packs Extras en el plan Premium de la Landing
$premiumTextSearch = 'Base de 20 Sensores (Packs Extra)';
$premiumTextReplace = 'Base de 20 Sensores (<b class="text-primary">+ Packs Extra</b>)';
$content = str_replace($premiumTextSearch, $premiumTextReplace, $content);

// Arreglar si hay otras instancias confusas de Packs Extra
$extraPacksSearch = '<div class="plan-feature"><i class="bi bi-check text-primary"></i> Base de 20 Sensores (Packs Extra)</div>';
$extraPacksReplace = '<div class="plan-feature"><i class="bi bi-check text-primary"></i> Base de 20 Sensores <br><span class="text-muted small ms-4">Autoscala con Packs de 10 extras</span></div>';
$content = str_replace($extraPacksSearch, $extraPacksReplace, $content);

file_put_contents($file, $content);
echo "Landing Page pricing and text logic fixed!\n";
