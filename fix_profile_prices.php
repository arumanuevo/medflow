<?php
$file = 'k:\desarrollo\medflow\resources\views\profile\index.blade.php';
$content = file_get_contents($file);

// Include pricing logic at the top of the script
$scriptTagPos = strpos($content, '<script>');
if ($scriptTagPos !== false) {
    $phpBlock = "
    @php
        \$sysPrices = @json_decode(file_get_contents(storage_path('app/pricing.json')), true) ?: ['basico' => 10000, 'premium' => 25000];
        \$priceBasico = \$sysPrices['basico'];
        \$pricePremium = \$sysPrices['premium'];
    @endphp
    ";

    // Check if not already added to avoid duplication
    if (strpos($content, '$sysPrices = @json_decode') === false) {
        $content = substr_replace($content, $phpBlock . '<script>' . "\n        const PRICE_BASICO = {{ \$priceBasico }};\n        const PRICE_PREMIUM = {{ \$pricePremium }};", $scriptTagPos, strlen('<script>'));
    }
}

// 1. Reemplazar Base
$searchBase = '${planName === \'Premium\' ? \'$25.000 ARS\' : (planName === \'Básico\' ? \'$10.000 ARS\' : \'Sin Costo\')}';
$replaceBase = '${planName === \'Premium\' ? \'$\' + PRICE_PREMIUM.toLocaleString(\'es-AR\') + \' ARS\' : (planName === \'Básico\' ? \'$\' + PRICE_BASICO.toLocaleString(\'es-AR\') + \' ARS\' : \'Sin Costo\')}';
$content = str_replace($searchBase, $replaceBase, $content);
// Manejar variacion de caracteres raros (encoding)
$searchBase2 = '${planName === \'Premium\' ? \'$25.000 ARS\' : (planName === \'BÃ¡sico\' ? \'$10.000 ARS\' : \'Sin Costo\')}';
$replaceBase2 = '${planName === \'Premium\' ? \'$\' + PRICE_PREMIUM.toLocaleString(\'es-AR\') + \' ARS\' : (planName === \'BÃ¡sico\' ? \'$\' + PRICE_BASICO.toLocaleString(\'es-AR\') + \' ARS\' : \'Sin Costo\')}';
$content = str_replace($searchBase2, $replaceBase2, $content);

// 2. Reemplazar calculo de packs extra
$searchPacksCalculo = '+$${((data.limits.sensors.max - 20) / 10) * 10000} ARS';
$replacePacksCalculo = '+$${(((data.limits.sensors.max - 20) / 10) * PRICE_BASICO).toLocaleString(\'es-AR\')} ARS';
$content = str_replace($searchPacksCalculo, $replacePacksCalculo, $content);

// 3. Reemplazar opciones del select
$searchPacksOption1 = '+10 Pack (+$10,000 ARS)';
$replacePacksOption1 = '+10 Pack (+$${(PRICE_BASICO * 1).toLocaleString(\'es-AR\')} ARS)';
$content = str_replace($searchPacksOption1, $replacePacksOption1, $content);

$searchPacksOption2 = '+20 Pack (+$20,000 ARS)';
$replacePacksOption2 = '+20 Pack (+$${(PRICE_BASICO * 2).toLocaleString(\'es-AR\')} ARS)';
$content = str_replace($searchPacksOption2, $replacePacksOption2, $content);

$searchPacksOption3 = '+30 Pack (+$30,000 ARS)';
$replacePacksOption3 = '+30 Pack (+$${(PRICE_BASICO * 3).toLocaleString(\'es-AR\')} ARS)';
$content = str_replace($searchPacksOption3, $replacePacksOption3, $content);

$searchPacksOption4 = '+40 Pack (+$40,000 ARS)';
$replacePacksOption4 = '+40 Pack (+$${(PRICE_BASICO * 4).toLocaleString(\'es-AR\')} ARS)';
$content = str_replace($searchPacksOption4, $replacePacksOption4, $content);

$searchPacksOption5 = '+50 Pack (+$50,000 ARS)';
$replacePacksOption5 = '+50 Pack (+$${(PRICE_BASICO * 5).toLocaleString(\'es-AR\')} ARS)';
$content = str_replace($searchPacksOption5, $replacePacksOption5, $content);

file_put_contents($file, $content);
echo "Profile dynamic pricing implemented!\n";
