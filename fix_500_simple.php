<?php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

// Find the PHP block
$pattern = '/@php\s*\$flowyIsPremium = false;\s*try \{.*?\} catch\(\\\\Exception \$e\) \{\s*\$flowyIsPremium = false;\s*\}\s*@endphp/s';

$replacement = <<<EOT
@php
\$flowyIsPremium = false;
if(auth()->check()){
    // Check directly using the User model attribute for maximum safety and zero dependencies
    \$flowyIsPremium = (auth()->user()->subscription_plan === 'premium' || auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'));
}
@endphp
EOT;

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($layoutPath, $content);

// Now Controller
$controllerPath = 'k:\desarrollo\medflow\app\Http\Controllers\AIChatController.php';
$contentC = file_get_contents($controllerPath);

$patternC = '/\/\/ Proteccion Backend.*?return response\(\)->json\(\[\'success\' => false.*?\} \}\n/s';

$replacementC = <<<EOT
        // Proteccion Backend contra abuso (Solo Premium)
        if (auth()->user()->subscription_plan !== 'premium' && !auth()->user()->hasRole('admin') && !auth()->user()->hasRole('superadmin')) {
             return response()->json(['success' => false, 'answer' => 'Función Premium. Debes subir de plan en tu perfil para usar la Inteligencia Artificial.']);
        }
EOT;

// I'll just use str_replace since regex multiline with single quotes can misfire
$oldControllerC = <<<EOT
        // Proteccion Backend contra abuso (Solo Premium)
        \$service = new \App\Services\Subscription\SubscriptionService(auth()->user());
        if (\$service->getPlan()->getPlanKey() !== 'premium') {
            return response()->json(['success' => false, 'answer' => 'Función Premium. Debes subir de plan en tu perfil para usar la Inteligencia Artificial.']);
        }
EOT;

$contentC = str_replace($oldControllerC, $replacementC, $contentC);
file_put_contents($controllerPath, $contentC);

echo "Simpler checks applied!\n";
