<?php

$controllerPath = 'k:\desarrollo\medflow\app\Http\Controllers\AIChatController.php';
$content = file_get_contents($controllerPath);

$oldLogic = <<<EOT
            \$brainPath = storage_path('app/ai/medflow_brain.md');
            \$systemContent = file_exists(\$brainPath) 
                             ? file_get_contents(\$brainPath) 
                             : 'Eres Flowy, asistente automatizado. Debes responder brevemente.';
EOT;

$newLogic = <<<EOT
            // Buscar en carpeta storage o en la raiz directamente
            \$brainPathStorage = storage_path('app/ai/medflow_brain.md');
            \$brainPathRoot = base_path('medflow_brain.md');
            
            if (file_exists(\$brainPathStorage)) {
                 \$systemContent = file_get_contents(\$brainPathStorage);
            } else if (file_exists(\$brainPathRoot)) {
                 \$systemContent = file_get_contents(\$brainPathRoot);
            } else {
                 \$systemContent = 'Eres Flowy, asistente automatizado. Debes responder brevemente. Pero dile al usuario que hubo un error: No encontraste el archivo medflow_brain.md en el servidor.';
            }
EOT;

$content = str_replace($oldLogic, $newLogic, $content);
file_put_contents($controllerPath, $content);
echo "Controller updated to check root dir and warn if missing!\n";
