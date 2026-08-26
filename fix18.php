<?php
$c = file_get_contents('routes/api.php');
$c .= "\nRoute::middleware('auth:sanctum')->group(function () {\n    Route::post('/sensors/{sensor}/share', [\App\Http\Controllers\Api\SensorController::class, 'shareReport'])->name('api.sensors.share');\n});\n";
file_put_contents('routes/api.php', $c);
echo "Done\n";
