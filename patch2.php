<?php
$c = file_get_contents('routes/api.php');
$target = "Route::post('/consumptions/calculate-range', [App\Http\Controllers\Api\ConsumptionController::class, 'calculateRange']);";
$replacement = <<<PHP
Route::post('/consumptions/calculate-range', [App\Http\Controllers\Api\ConsumptionController::class, 'calculateRange']);

        Route::post('/sensors/{sensor}/share', [App\Http\Controllers\Api\SensorController::class, 'shareReport']);
PHP;
if (strpos($c, 'shareReport') === false) {
    $c = str_replace($target, $replacement, $c);
    file_put_contents('routes/api.php', $c);
    echo "Added route to api.php\n";
}
