<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$sensor = App\Models\Sensor::with('group.template')->first();
echo json_encode($sensor, JSON_PRETTY_PRINT);
