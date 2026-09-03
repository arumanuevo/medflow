<?php
$files = [
    'k:\desarrollo\medflow\resources\views\measurements\edit.blade.php',
    'k:\desarrollo\medflow\resources\views\measurements\create.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);

        $content = str_replace("window.location.href = '/measurements'", "window.location.href = '/mediciones'", $content);
        $content = str_replace('window.location.href = "/measurements"', "window.location.href = '/mediciones'", $content);

        file_put_contents($file, $content);
    }
}
echo "Redirects patched to /mediciones!\n";
