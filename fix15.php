<?php
$c = file_get_contents('routes/api.php');
if (strpos($c, '/sensors/{sensor}/share') === false) {
    if (strpos($c, "Route::apiResource('/sensors', SensorController::class)") !== false) {
        $c = str_replace("Route::apiResource('/sensors', SensorController::class);", "Route::post('/sensors/{sensor}/share', [SensorController::class, 'shareReport']);\n    Route::apiResource('/sensors', SensorController::class);", $c);
    } else {
        $c .= "\nRoute::middleware('auth:sanctum')->group(function () {\n    Route::post('/sensors/{sensor}/share', [App\Http\Controllers\Api\SensorController::class, 'shareReport'])->name('api.sensors.share');\n});\n";
    }
}
file_put_contents('routes/api.php', $c);
echo "Route injected\n";
