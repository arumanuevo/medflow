<?php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

// Change the floating button SVG sizing
$oldBtnImg = '<img src="{{ asset(\'img/flowy-ai.svg\') }}" style="width: 60%; height: 60%; object-fit: contain;" alt="Flowy AI">';
$newBtnImg = '<img src="{{ asset(\'img/flowy-ai.svg\') }}" style="width: 85%; height: 85%; object-fit: contain;" alt="Flowy AI">';

$content = str_replace($oldBtnImg, $newBtnImg, $content);

file_put_contents($layoutPath, $content);
echo "Image resized to 85%!\n";
