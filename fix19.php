<?php
$c = file_get_contents('routes/api.php');
$c = preg_replace('/checkExpiration\']\);.*/s', "checkExpiration']);", $c);
$c .= "\nRoute::middleware('auth:sanctum')->group(function () {\n    Route::post('/sensors/{sensor}/share', [\App\Http\Controllers\Api\SensorController::class, 'shareReport'])->name('api.sensors.share');\n});\n";
file_put_contents('routes/api.php', $c);
echo "Fixed api.php\n";
