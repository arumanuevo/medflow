<?php
$file = 'k:\desarrollo\medflow\resources\views\flyer.blade.php';
$content = file_get_contents($file);

// Replace main carousel img CSS
$content = preg_replace(
    '/\.carousel-item img\s*\{\s*height\s*:\s*[^\}]+\}/i',
    ".carousel-item img { height: 450px !important; max-height: 450px; object-fit: contain; object-position: center; width: 100%; padding: 15px; background: #0f172a; }",
    $content,
    1
); // Only replace the first occurrence (the main one)

// Replace media query 1
$content = preg_replace('/\.carousel-item img\s*\{\s*height:\s*50vh;\s*\}/', '.carousel-item img { height: 400px !important; }', $content);

// Replace media query 2
$content = preg_replace('/\.carousel-item img\s*\{\s*height:\s*40vh;\s*\}/', '.carousel-item img { height: 350px !important; }', $content);

// Shrink container
$content = preg_replace(
    '/\.carousel-container\s*\{[^\}]+\}/i',
    ".carousel-container { max-width: 850px; margin: 0 auto; border: 4px solid #f8fafc; border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,0.15); overflow: hidden; background: #0f172a; }",
    $content
);

file_put_contents($file, $content);
echo "Regex replacement succeeded!\n";
