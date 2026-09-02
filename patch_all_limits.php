<?php
$views = [
    'k:\desarrollo\medflow\resources\views\landing.blade.php',
    'k:\desarrollo\medflow\resources\views\profile\index.blade.php'
];

foreach ($views as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);

        // Fix Basic limits text
        $content = str_replace('2 Sensores por Grupo', '10 Sensores en Total', $content);
        $content = str_replace('2 sensores máximo', '10 sensores máximo', $content);

        file_put_contents($file, $content);
    }
}
echo "All texts synchronized with backend classes!\n";
