<?php
$file = 'k:\desarrollo\medflow\resources\views\flyer.blade.php';
$content = file_get_contents($file);

// Replace masonry-grid rule with new formatted rule
$content = preg_replace(
    '/\.masonry-grid\s*\{[^\}]+\}/i',
    ".masonry-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; max-width: 1200px; margin: 0 auto; }",
    $content
);

// We need to also wrap the media queries since repeat(3, 1fr) is not responsive by itself
// Actually we can just add media queries right after the `.masonry-grid {...}`
$content = preg_replace(
    '/\.masonry-grid\s*\{[^\}]+\}/i',
    "$0 
    @media (max-width: 991px) { .masonry-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) { .masonry-grid { grid-template-columns: 1fr; } }",
    $content
);


file_put_contents($file, $content);
echo "Masonry grid CSS regex fixed!\n";
