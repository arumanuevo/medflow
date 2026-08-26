<?php
$html = file_get_contents('resources/views/consumptions/index.blade.php');
if (preg_match('/<div class="modal fade" id="analyzeSensorModal".*?<\/div>\s*<\/div>\s*<\/div>/s', $html, $matches)) {
    $modalHTML = $matches[0];
    preg_match_all('/<div[ >]/', $modalHTML, $open);
    preg_match_all('/<\/div>/', $modalHTML, $close);
    echo "Open: " . count($open[0]) . " Close: " . count($close[0]) . "\n";
}
