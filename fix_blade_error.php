<?php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

// Target the end of the Flowy script and insert @endif safely
$search = "</script>\n</body>";
$replace = "</script>\n@endif\n</body>";

if (strpos($content, "@endif") === false || substr_count($content, "@endif") < substr_count($content, "@if")) {
    $content = str_replace($search, $replace, $content);
    // If it uses \r\n
    $searchRN = "</script>\r\n</body>";
    $replaceRN = "</script>\r\n@endif\r\n</body>";
    if (strpos($content, $replace) === false) {
        $content = str_replace($searchRN, $replaceRN, $content);
    }
}

file_put_contents($layoutPath, $content);
echo "Blade syntax @endif restored!\n";
