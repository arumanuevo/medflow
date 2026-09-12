<?php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

// Change the SVG Header tag to show the SVG brilliantly against the blue header
$oldHeaderImg = '<img src="{{ asset(\'img/flowy-ai.svg\') }}" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; border: 1px solid white;" class="me-2 bg-white" alt="AI">';
$newHeaderImg = '<img src="{{ asset(\'img/flowy-ai.svg\') }}" style="width: 28px; height: 28px; object-fit: contain;" class="me-2" alt="AI">';

$content = str_replace($oldHeaderImg, $newHeaderImg, $content);

// Also let's adjust the floating button just in case object-fit cover is hiding the edges of the SVG
$oldBtnImg = '<img src="{{ asset(\'img/flowy-ai.svg\') }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Flowy AI">';
$newBtnImg = '<img src="{{ asset(\'img/flowy-ai.svg\') }}" style="width: 60%; height: 60%; object-fit: contain;" alt="Flowy AI">';

$content = str_replace($oldBtnImg, $newBtnImg, $content);

file_put_contents($layoutPath, $content);
echo "Adjusted SVG CSS styling!\n";
