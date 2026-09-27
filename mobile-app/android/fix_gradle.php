<?php
$buildGradle = 'k:\desarrollo\medflow\mobile-app\android\app\build.gradle.kts';
$content = file_get_contents($buildGradle);

// Fix the }plugins syntax error
$content = str_replace('}plugins {', "}\n\nplugins {", $content);

file_put_contents($buildGradle, $content);
echo "gradle formatting fixed\n";
