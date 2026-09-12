<?php

$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

// This matches my previous string which was exactly: asset('img/flowy-ai.png')
$content = str_replace("asset('img/flowy-ai.png')", "asset('img/flowy-ai.svg')", $content);

file_put_contents($layoutPath, $content);
echo "SVG replacement completed!\n";
