<?php
$file = 'k:\desarrollo\medflow\resources\views\landing.blade.php';
$content = file_get_contents($file);

$basicPriceStr = 'style="font-size: 1.8rem;">$10<small>/mes</small>';
$basicReplace = 'style="font-size: 1.8rem;">${{ number_format(config(\'mercadopago.plans.basico.price\', 10000) / 100, 0) }}<small>/{{ config(\'mercadopago.plans.basico.currency\', \'ARS\') }} mes</small>';

$premiumPriceStr = 'style="font-size: 1.8rem;">$25<small>/usd mes</small>';
$premiumReplace = 'style="font-size: 1.8rem;">${{ number_format(config(\'mercadopago.plans.premium.price\', 25000) / 100, 0) }}<small>/{{ config(\'mercadopago.plans.premium.currency\', \'ARS\') }} mes</small>';

$content = str_replace($basicPriceStr, $basicReplace, $content);
$content = str_replace($premiumPriceStr, $premiumReplace, $content);

file_put_contents($file, $content);
echo "Prices linked dynamically!\n";
