<?php
$files = [
    'k:\desarrollo\medflow\resources\views\profile\index.blade.php',
    'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);

        $content = preg_replace('/\/\/ const previousPlan = null;\s*if \(previousPlan\) \{\s*response\.\/\/ data override removed\s*\}/s', '', $content);
        $content = preg_replace('/\/\/ const previousPlan = null;\s*if \(\!data\.previous_plan\) \{\s*\/\/ data override removed\s*\}/s', '', $content);

        file_put_contents($file, $content);
    }
}
echo "JS Syntax fixed!\n";
