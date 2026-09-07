<?php
$file = 'k:\desarrollo\medflow\resources\views\consumptions\index.blade.php';
$content = file_get_contents($file);

$content = preg_replace(
    '/const params = \{\s*page:\s*currentPage,\s*per_page:\s*15\s*\};/',
    "const params = {\n                page: currentPage,\n                per_page: 15,\n                sort_by: currentSortBy,\n                sort_dir: currentSortDir\n            };",
    $content
);

file_put_contents($file, $content);
echo "Params updated!\n";
