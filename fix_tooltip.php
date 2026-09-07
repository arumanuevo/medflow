<?php
$files = [
    'k:\desarrollo\medflow\resources\views\sensors\index.blade.php',
    'k:\desarrollo\medflow\resources\views\measurements\select-sensor.blade.php',
    'k:\desarrollo\medflow\resources\views\measurements\inspector-select-sensor.blade.php'
];

$tooltip = 'Has alcanzado el límite máximo de licencias de sensores de tu plan (Premium base: 20). Para habilitar este y más sensores físicos, ingresa a tu Perfil > Facturación y Licencias.';

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);

        $content = str_replace('Límite de Sensores superado en el Plan Actual', $tooltip, $content);
        $content = str_replace('L├¡mite de Sensores superado en el Plan Actual', $tooltip, $content);

        $content = str_replace('Límite del Plan Superado', $tooltip, $content);
        $content = str_replace('L├¡mite del Plan Superado', $tooltip, $content);

        file_put_contents($file, $content);
    }
}
echo "Tooltips updated!\n";
