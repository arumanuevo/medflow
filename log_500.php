<?php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

$pattern = '/@php\s*\$flowyIsPremium = false;.*?@endphp/s';

$replacement = <<<EOT
@php
\$flowyIsPremium = false;
try {
    if(auth()->check()){
        // Fallback for getting plan name safe
        \$plan = auth()->user()->subscription_plan ?? 'free';
        
        // Safer role check: don't call hasRole strictly if not available, or wrap it
        \$isAdmin = false;
        if(method_exists(auth()->user(), 'hasRole')){
            \$isAdmin = auth()->user()->hasRole('admin');
        }
        
        if(\$plan === 'premium' || \$isAdmin){
             \$flowyIsPremium = true;
        }
    }
} catch(\\Throwable \$e) {
    \Illuminate\Support\Facades\Log::error("Flowy Layout Crash: " . \$e->getMessage());
    \$flowyIsPremium = false;
}
@endphp
EOT;

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($layoutPath, $content);
echo "Try catch implemented!\n";
