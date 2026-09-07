<?php
$file = 'k:\desarrollo\medflow\resources\views\flyer.blade.php';
$content = file_get_contents($file);

// Replace padding for masonry-caption
$content = preg_replace(
    '/\.masonry-caption\s*\{\s*padding:\s*[^\};]+\s*;\s*\}/i',
    ".masonry-caption { padding: 26px 20px 20px 20px; }",
    $content
);

file_put_contents($file, $content);
echo "Padding tweaked!\n";
