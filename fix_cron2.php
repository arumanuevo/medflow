<?php
$file = 'k:\desarrollo\medflow\routes\console.php';
$content = file_get_contents($file);

// Add descriptive names to the closures so withoutOverlapping can generate a mutex hash
$content = str_replace("Schedule::call(function () { \Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]); })->everyMinute()->withoutOverlapping();", "Schedule::call(function () { \Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]); })->name('queue-worker-sync')->everyMinute()->withoutOverlapping();", $content);

$content = str_replace("Schedule::call(function () { \Illuminate\Support\Facades\Artisan::call('app:clean-expired-photos'); })->dailyAt('02:00')->withoutOverlapping();", "Schedule::call(function () { \Illuminate\Support\Facades\Artisan::call('app:clean-expired-photos'); })->name('app-cleaner-sync')->dailyAt('02:00')->withoutOverlapping();", $content);

file_put_contents($file, $content);
echo "Cron names fixed!\n";
