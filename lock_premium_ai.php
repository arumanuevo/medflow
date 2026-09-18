<?php

// 1. Proteger el Backend en el AIChatController
$controllerPath = 'k:\desarrollo\medflow\app\Http\Controllers\AIChatController.php';
$contentC = file_get_contents($controllerPath);

$oldAskFunc = <<<EOT
    public function ask(Request \$request)
    {
        \$request->validate(['message' => 'required|string']);
        \$userMessage = \$request->input('message');
EOT;

$newAskFunc = <<<EOT
    public function ask(Request \$request)
    {
        // Proteccion Backend contra abuso (Solo Premium)
        \$service = app(\App\Services\Subscription\SubscriptionService::class, ['user' => auth()->user()]);
        if (\$service->getPlan()->getPlanKey() !== 'premium') {
            return response()->json(['success' => false, 'answer' => 'Función Premium. Debes subir de plan en tu perfil para usar la Inteligencia Artificial.']);
        }

        \$request->validate(['message' => 'required|string']);
        \$userMessage = \$request->input('message');
EOT;

$contentC = str_replace($oldAskFunc, $newAskFunc, $contentC);
file_put_contents($controllerPath, $contentC);

// 2. Proteger el Frontend (Ocultar el botón y el panel) en modern.blade.php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$contentL = file_get_contents($layoutPath);

// Find where Flowy starts
$flowyStart = "<!-- Botón Flotante Flowy AI -->";

$flowyStartReplacement = <<<EOT
@php
\$flowyIsPremium = false;
if(auth()->check()){
    \$flowyService = app(\App\Services\Subscription\SubscriptionService::class, ['user' => auth()->user()]);
    \$flowyIsPremium = (\$flowyService->getPlan()->getPlanKey() === 'premium');
}
@endphp

@if(\$flowyIsPremium)
<!-- Botón Flotante Flowy AI -->
EOT;

$contentL = str_replace($flowyStart, $flowyStartReplacement, $contentL);


// Find where Flowy ends (The end of the JS block)
$flowyEnd = <<<EOT
      const style = document.createElement('style');
      style.innerHTML = `@keyframes heartbeat { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } } .heartbeat-anim { display: inline-block; animation: heartbeat 1.5s infinite; }`;
      document.head.appendChild(style);
  });
  </script>
EOT;

$flowyEndReplacement = <<<EOT
      const style = document.createElement('style');
      style.innerHTML = `@keyframes heartbeat { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } } .heartbeat-anim { display: inline-block; animation: heartbeat 1.5s infinite; }`;
      document.head.appendChild(style);
  });
  </script>
@endif
EOT;

$contentL = str_replace($flowyEnd, $flowyEndReplacement, $contentL);
file_put_contents($layoutPath, $contentL);

echo "Premium Protection active!\n";
