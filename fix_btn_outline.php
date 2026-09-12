<?php

// 1. Change SVG color to Blue (#0d6efd)
$svgPath = 'k:\desarrollo\medflow\public\img\flowy-ai.svg';
$svgContent = file_get_contents($svgPath);
$svgContent = str_replace('fill:#ffffff', 'fill:#0d6efd', $svgContent);
file_put_contents($svgPath, $svgContent);

// 2. Change Button styling to have no blue fill, just an outer border and white background (so it blocks text below it)
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

// Target the exact floating button definition
$oldBtn = '<button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center p-0" 
        id="btnFlowyAI" 
        style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border-radius: 50% !important; overflow: hidden; border: 2px solid white;">';

$newBtn = '<button class="btn bg-white rounded-circle shadow-lg d-flex align-items-center justify-content-center p-0" 
        id="btnFlowyAI" 
        style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border-radius: 50% !important; overflow: hidden; border: 2px solid #0d6efd;">';

$content = str_replace($oldBtn, $newBtn, $content);

file_put_contents($layoutPath, $content);

echo "Button made transparent (white) with blue border, and SVG re-colored to blue!\n";
