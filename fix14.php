<?php
$c = file_get_contents('routes/api.php');

if (strpos($c, '/sensors/{sensor}/share') === false) {
    if (strpos($c, 'Route::apiResource(\'/sensors\', SensorController::class)') !== false) {
        $c = str_replace('Route::apiResource(\'/sensors\', SensorController::class);', "Route::post('/sensors/{sensor}/share', [App\Http\Controllers\Api\SensorController::class, 'shareReport']);\n    Route::apiResource('/sensors', App\Http\Controllers\Api\SensorController::class);", $c);
    } else {
        // Just append to the end of auth:sanctum group
        $c = preg_replace('/(Route::delete\(\'\/sensor-groups\/\{group\}\/shared-access\/\{access\}\'.*?;)/s', "$1\n    Route::post('/sensors/{sensor}/share', [App\Http\Controllers\Api\SensorController::class, 'shareReport']);", $c);
    }
}
file_put_contents('routes/api.php', $c);
echo "Route patched\n";
