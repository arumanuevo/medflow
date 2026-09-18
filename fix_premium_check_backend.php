<?php
$controllerPath = 'k:\desarrollo\medflow\app\Http\Controllers\AIChatController.php';
$contentC = file_get_contents($controllerPath);

$pattern = '/\/\/ Proteccion Backend.*?return response\(\)->json\(\[\'success\' => false.*?\} \}\n/s';

$replacementC = <<<EOT
        // Proteccion Backend contra abuso (Solo Premium)
        \$service = new \App\Services\Subscription\SubscriptionService(auth()->user());
        \$userPlan = \$service->getPlan()->getPlanKey();
        \$isAdmin = method_exists(auth()->user(), 'hasRole') ? (auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin')) : false;

        if (strtolower(\$userPlan) !== 'premium' && strtolower(auth()->user()->subscription_plan ?? '') !== 'premium' && !\$isAdmin) {
             return response()->json(['success' => false, 'answer' => 'Función Premium. Debes subir de plan en tu perfil para usar la Inteligencia Artificial.']);
        }
EOT;

$oldControllerC = <<<EOT
        // Proteccion Backend contra abuso (Solo Premium)
        if (auth()->user()->subscription_plan !== 'premium' && !auth()->user()->hasRole('admin') && !auth()->user()->hasRole('superadmin')) {
             return response()->json(['success' => false, 'answer' => 'Función Premium. Debes subir de plan en tu perfil para usar la Inteligencia Artificial.']);
        }
EOT;

$contentC = str_replace($oldControllerC, $replacementC, $contentC);
file_put_contents($controllerPath, $contentC);
echo "Backend Subscription validation restored!\n";
