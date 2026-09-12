<?php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

// Target the button HTML
$oldBtn = '<button class="btn bg-white rounded-circle shadow-lg d-flex align-items-center justify-content-center p-0" 
        id="btnFlowyAI" 
        style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border-radius: 50% !important; overflow: hidden; border: 2px solid #0d6efd;">';

$newBtn = '<button class="btn rounded-circle shadow-lg d-flex align-items-center justify-content-center p-0" 
        id="btnFlowyAI" 
        style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border-radius: 50% !important; overflow: hidden; border: 1.5px solid rgba(13, 110, 253, 0.5); background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); transition: all 0.3s ease;">';

// Also add a cool hover effect just for bonus UX
$hoverStyle = "
<style>
#btnFlowyAI:hover {
    background: rgba(255, 255, 255, 0.95) !important;
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(13, 110, 253, 0.2) !important;
}
</style>
";

// Inject the style right before the button
if (strpos($content, '#btnFlowyAI:hover') === false) {
    $content = str_replace($oldBtn, $hoverStyle . $newBtn, $content);
} else {
    $content = str_replace($oldBtn, $newBtn, $content);
}

file_put_contents($layoutPath, $content);
echo "Glassmorphism activated!\n";
