<?php
$c = file_get_contents('resources/views/landing.blade.php');
$i = strpos($c, 'Desarrollo UI');
if ($i !== false) {
    echo substr($c, $i - 800, 1500);
}
