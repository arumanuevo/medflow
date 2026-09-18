<?php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$contentL = file_get_contents($layoutPath);

$oldBladePhp = <<<EOT
@php
\$flowyIsPremium = false;
if(auth()->check()){
    \$flowyService = app(\App\Services\Subscription\SubscriptionService::class, ['user' => auth()->user()]);
    \$flowyIsPremium = (\$flowyService->getPlan()->getPlanKey() === 'premium');
}
@endphp
EOT;

$newBladePhp = <<<EOT
@php
\$flowyIsPremium = false;
try {
    if(auth()->check()){
        \$flowyService = new \App\Services\Subscription\SubscriptionService(auth()->user());
        \$flowyIsPremium = (\$flowyService->getPlan()->getPlanKey() === 'premium');
    }
} catch(\Exception \$e) {
    \$flowyIsPremium = false;
}
@endphp
EOT;

$contentL = str_replace($oldBladePhp, $newBladePhp, $contentL);
file_put_contents($layoutPath, $contentL);


$controllerPath = 'k:\desarrollo\medflow\app\Http\Controllers\AIChatController.php';
$contentC = file_get_contents($controllerPath);

$oldControllerPhp = <<<EOT
        // Proteccion Backend contra abuso (Solo Premium)
        \$service = app(\App\Services\Subscription\SubscriptionService::class, ['user' => auth()->user()]);
        if (\$service->getPlan()->getPlanKey() !== 'premium') {
            return response()->json(['success' => false, 'answer' => 'Función Premium. Debes subir de plan en tu perfil para usar la Inteligencia Artificial.']);
        }
EOT;

$newControllerPhp = <<<EOT
        // Proteccion Backend contra abuso (Solo Premium)
        \$service = new \App\Services\Subscription\SubscriptionService(auth()->user());
        if (\$service->getPlan()->getPlanKey() !== 'premium') {
            return response()->json(['success' => false, 'answer' => 'Función Premium. Debes subir de plan en tu perfil para usar la Inteligencia Artificial.']);
        }
EOT;

$contentC = str_replace($oldControllerPhp, $newControllerPhp, $contentC);
file_put_contents($controllerPath, $contentC);

echo "Instantiations fixed to standard new Object()\n";
