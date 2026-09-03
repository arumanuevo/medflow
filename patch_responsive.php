<?php
$file = 'k:\desarrollo\medflow\resources\views\landing.blade.php';
$content = file_get_contents($file);

// 1. Navbar Login button fix
$oldNav = '<a href="{{ route(\'login\') }}" class="btn btn-outline-primary rounded-pill px-4">';
$newNav = '<a href="{{ route(\'login\') }}" class="btn btn-outline-primary rounded-pill px-3 px-md-4 py-1 py-md-2" style="font-size: clamp(0.85rem, 3vw, 1rem);">';
$content = str_replace($oldNav, $newNav, $content);

// 2. Add bottom margin to left column or hero-buttons to prevent touching Registration Card
$oldLeftCol = '<div class="col-lg-6 animate-fade-in animate-delay-1">';
$newLeftCol = '<div class="col-lg-6 animate-fade-in animate-delay-1 mb-5 mb-lg-0">';
$content = str_replace($oldLeftCol, $newLeftCol, $content);

file_put_contents($file, $content);
echo "Responsive fixes applied!\n";
