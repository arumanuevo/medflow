<?php
$b = file_get_contents('app/Services/Subscription/Plans/BasicoPlan.php');
$b = preg_replace('/public function getPrice\(\): float\s*\{\s*return [0-9.]+;\s*\}/', "public function getPrice(): float\n    {\n        \$prices = @json_decode(file_get_contents(storage_path('app/pricing.json')), true) ?: ['basico' => 10000.00, 'premium' => 25000.00];\n        return isset(\$prices['basico']) ? (float)\$prices['basico'] : 10000.00;\n    }", $b);
file_put_contents('app/Services/Subscription/Plans/BasicoPlan.php', $b);

$p = file_get_contents('app/Services/Subscription/Plans/PremiumPlan.php');
$p = preg_replace('/public function getPrice\(\): float\s*\{\s*return [0-9.]+;\s*\}/', "public function getPrice(): float\n    {\n        \$prices = @json_decode(file_get_contents(storage_path('app/pricing.json')), true) ?: ['basico' => 10000.00, 'premium' => 25000.00];\n        return isset(\$prices['premium']) ? (float)\$prices['premium'] : 25000.00;\n    }", $p);
file_put_contents('app/Services/Subscription/Plans/PremiumPlan.php', $p);

echo "Updated plans\n";
