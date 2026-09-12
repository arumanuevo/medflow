<?php

$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

// Replace Floating Button
$oldButton = <<<EOT
<button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
        id="btnFlowyAI" 
        style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border-radius: 50% !important;">
    <i class="bi bi-robot fs-3 text-white"></i>
</button>
EOT;

$newButton = <<<EOT
<button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center p-0" 
        id="btnFlowyAI" 
        style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border-radius: 50% !important; overflow: hidden; border: 2px solid white;">
    <img src="{{ asset('img/flowy-ai.png') }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Flowy AI">
</button>
EOT;

$content = str_replace($oldButton, $newButton, $content);

// Replace Chat Header
$oldHeader = <<<EOT
<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-robot me-2"></i> Flowy (Beta IA)</h6>
            <button type="button" class="btn-close btn-close-white" id="closeFlowyChat" style="font-size: 0.8rem;"></button>
        </div>
EOT;

$newHeader = <<<EOT
<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-3">
            <h6 class="mb-0 fw-bold d-flex align-items-center">
                <img src="{{ asset('img/flowy-ai.png') }}" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; border: 1px solid white;" class="me-2 bg-white" alt="AI"> 
                Flowy (Soporte IA)
            </h6>
            <button type="button" class="btn-close btn-close-white" id="closeFlowyChat" style="font-size: 0.8rem;"></button>
        </div>
EOT;

$content = str_replace($oldHeader, $newHeader, $content);

file_put_contents($layoutPath, $content);
echo "Image replacements implemented!\n";
