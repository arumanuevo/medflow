<?php
$content = file_get_contents('k:\desarrollo\medflow\app\Http\Controllers\Api\MeasurementController.php');

$search = <<<EOD
        // Caso 2: Es la ÚLTIMA medición (fecha posterior a todas)
        if (\$position === 'last' && \$previousMeasurement) {
            \$previousValue = \$previousMeasurement->data[\$mainField] ?? 0;

            // El valor debe ser MAYOR al anterior
            if (\$currentValue <= \$previousValue) {
EOD;

$replace = <<<EOD
        // ✅ VERIFICAR SI HAY RESETEO (CAMBIO DE MEDIDOR)
        \$isReset = isset(\$data['is_reset']) && filter_var(\$data['is_reset'], FILTER_VALIDATE_BOOLEAN);

        // Caso 2: Es la ÚLTIMA medición (fecha posterior a todas)
        if (\$position === 'last' && \$previousMeasurement) {
            \$previousValue = \$previousMeasurement->data[\$mainField] ?? 0;

            // El valor debe ser MAYOR al anterior, EXCEPTO si es un reseteo
            if (\$currentValue <= \$previousValue && !\$isReset) {
EOD;

if (strpos($content, trim(explode("\n", $search)[1])) !== false) {
    if (strpos($content, "\$isReset") === false) {
        $content = str_replace($search, $replace, $content);
        file_put_contents('k:\desarrollo\medflow\app\Http\Controllers\Api\MeasurementController.php', $content);
        echo "Patched MeasurementController\n";
    } else {
        echo "MeasurementController already patched\n";
    }
} else {
    echo "Could not find target in MeasurementController\n";
}



$content2 = file_get_contents('k:\desarrollo\medflow\app\Http\Controllers\Api\ConsumptionController.php');

$search2 = <<<EOD
            // Validar que el valor final sea mayor al inicial
            if ((float) \$endValRaw <= (float) \$startValRaw) {
                continue;
            }

            \$startValue = (float) \$startValRaw;
            \$endValue = (float) \$endValRaw;
            \$consumptionValue = round(\$endValue - \$startValue, 2);
EOD;

$replace2 = <<<EOD
            // ✅ VERIFICAR SI LA MEDICIÓN FINAL ES UN REEMPLAZO DE MEDIDOR
            \$isReset = isset(\$end->data['is_reset']) && filter_var(\$end->data['is_reset'], FILTER_VALIDATE_BOOLEAN);
            \$resetType = \$end->data['reset_type'] ?? 'simple';
            
            \$startValue = (float) \$startValRaw;
            \$endValue = (float) \$endValRaw;
            
            // Validar que el valor final sea mayor al inicial, EXCEPTO si es un reseteo
            if (\$endValue <= \$startValue && !\$isReset) {
                continue;
            }

            if (\$isReset) {
                if (\$resetType === 'exact') {
                    // Opción 2: Cierre exacto
                    \$oldMeterFinal = (float) (\$end->data['old_meter_final'] ?? \$startValue);
                    \$newMeterStart = (float) (\$end->data['new_meter_start'] ?? 0);
                    
                    // Asegurar que no sea negativo si escriben mal
                    \$oldConsumption = max(0, \$oldMeterFinal - \$startValue);
                    \$newConsumption = max(0, \$endValue - \$newMeterStart);
                    
                    \$consumptionValue = round(\$oldConsumption + \$newConsumption, 2);
                } else {
                    // Opción 1: Reseteo simple (ignorar consumo de este periodo)
                    // Configura consumo a 0 o bien podrías continuar sin generarlo.
                    // Generalmente, para no perder los días transcurridos, se marca como 0.
                    \$consumptionValue = 0;
                }
            } else {
                \$consumptionValue = round(\$endValue - \$startValue, 2);
            }
EOD;

if (strpos($content2, "if ((float) \$endValRaw <= (float) \$startValRaw) {") !== false) {
    if (strpos($content2, "is_reset") === false) {
        $content2 = str_replace($search2, $replace2, $content2);
        file_put_contents('k:\desarrollo\medflow\app\Http\Controllers\Api\ConsumptionController.php', $content2);
        echo "Patched ConsumptionController\n";
    } else {
        echo "ConsumptionController already patched\n";
    }
} else {
    echo "Could not find target in ConsumptionController\n";
}

