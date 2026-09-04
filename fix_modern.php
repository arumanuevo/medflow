<?php
$file = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($file);
$content = preg_replace('/\/\/ const previousPlan = null \|\| null;\s*if \(previousPlan\) \{\s*\/\/ data override removed\s*\}/s', '', $content);
file_put_contents($file, $content);
echo "modern.blade fixed\n";
