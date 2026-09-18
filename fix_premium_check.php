<?php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

$pattern = '/@php\s*\$flowyIsPremium = false;\s*try \{.*?\} catch\(\\\\Throwable \$e\) \{.*?\}\s*@endphp/s';

$replacement = <<<EOT
@php
\$flowyIsPremium = false;
try {
    if(auth()->check()){
        // 1. Evaluar subscripcion real activa en Base de Datos
        \$service = new \App\Services\Subscription\SubscriptionService(auth()->user());
        \$userPlan = \$service->getPlan()->getPlanKey(); // Devuelve 'free', 'basico' o 'premium'
        
        // 2. Revisar si tiene beneficios ejecutivos
        \$isAdmin = false;
        if(method_exists(auth()->user(), 'hasRole')){
            \$isAdmin = auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin');
        }
        
        // 3. Activar Flowy
        if(strtolower(\$userPlan) === 'premium' || strtolower(auth()->user()->subscription_plan ?? '') === 'premium' || \$isAdmin){
             \$flowyIsPremium = true;
        }
    }
} catch(\\Throwable \$e) {
    \Illuminate\Support\Facades\Log::error("Flowy Layout Crash: " . \$e->getMessage());
    // Sistema anticolapso de emergencia
    if (strtolower(auth()->user()->subscription_plan ?? '') === 'premium') {
        \$flowyIsPremium = true;
    }
}
@endphp
EOT;

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($layoutPath, $content);
echo "Robust Subscription validation added!\n";
