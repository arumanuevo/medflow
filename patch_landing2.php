<?php
$file = 'k:\desarrollo\medflow\resources\views\landing.blade.php';
$content = file_get_contents($file);

$basicPriceStr = '${{ number_format(config(\'mercadopago.plans.basico.price\', 10000) / 100, 0) }}<small>/{{ config(\'mercadopago.plans.basico.currency\', \'ARS\') }} mes</small>';
$basicReplace = '@php $sysPrices = @json_decode(file_get_contents(storage_path("app/pricing.json")), true) ?: ["basico" => 10, "premium" => 25]; @endphp ${{ number_format($sysPrices["basico"], 0) }}<small>/ARS mes</small>';

$premiumPriceStr = '${{ number_format(config(\'mercadopago.plans.premium.price\', 25000) / 100, 0) }}<small>/{{ config(\'mercadopago.plans.premium.currency\', \'ARS\') }} mes</small>';
$premiumReplace = '${{ number_format($sysPrices["premium"], 0) }}<small>/ARS mes</small>';

$content = str_replace($basicPriceStr, $basicReplace, $content);
$content = str_replace($premiumPriceStr, $premiumReplace, $content);

file_put_contents($file, $content);
echo "Prices fixed to JSON!\n";
