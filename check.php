<?php
$c = file_get_contents('resources/views/collaborations/index.blade.php');
preg_match_all('/<script>(.*?)<\/script>/s', $c, $m);
$script = $m[1][0];
file_put_contents('test.js', $script);
$output = shell_exec('node -c test.js 2>&1');
echo $output;
