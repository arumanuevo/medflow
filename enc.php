<?php
$c = file_get_contents('resources/views/consumptions/index.blade.php');
if (substr($c, 0, 2) === "\xFF\xFE") {
    $c = mb_convert_encoding(substr($c, 2), 'UTF-8', 'UTF-16LE');
    file_put_contents('resources/views/consumptions/index.blade.php', $c);
    echo "Encoding fixed!\n";
} else {
    echo "No UTF-16LE BOM found.\n";
}
