<?php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

// The exact string we wrote in the last step
$oldBtnMatch = '<button class="btn rounded-circle shadow-lg d-flex align-items-center justify-content-center p-0" 
        id="btnFlowyAI" 
        style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border-radius: 50% !important; overflow: hidden; border: 1.5px solid rgba(13, 110, 253, 0.5); background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); transition: all 0.3s ease;">';

// The new string with updated glassmorphism properties (shadow and heavy transparency)
$newBtn = '<button class="btn rounded-circle d-flex align-items-center justify-content-center p-0" 
        id="btnFlowyAI" 
        style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border-radius: 50% !important; overflow: hidden; border: 1px solid rgba(13, 110, 253, 0.4); background: rgba(255, 255, 255, 0.35); box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.25) !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); transition: all 0.3s ease;">';

$content = str_replace($oldBtnMatch, $newBtn, $content);
file_put_contents($layoutPath, $content);
echo "Glassmorphism shadow + higher transparency applied!\n";
