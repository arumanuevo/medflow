<?php
$files = [
    'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php',
    'k:\desarrollo\medflow\resources\views\profile\index.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);

        // Use simpler replacements
        $content = str_replace("const previousPlan = localStorage.getItem('previous_plan')", "// const previousPlan = null", $content);
        $content = preg_replace("/data\.previous_plan = previousPlan;/", "// data override removed", $content);
        $content = preg_replace("/response\.data\.previous_plan = previousPlan;/", "// override removed", $content);
        $content = str_replace("localStorage.setItem('previous_plan', plan);", "// localStorage mock removed", $content);
        $content = str_replace("localStorage.removeItem('previous_plan');", "// localStorage mock removed", $content);
        $content = str_replace("localStorage.setItem('previous_plan', previousPlanInput.value);", "// localStorage mock removed", $content);

        file_put_contents($file, $content);
    }
}
echo "LocalStorage references completely destroyed!\n";
