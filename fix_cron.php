<?php
$file = 'k:\desarrollo\medflow\routes\console.php';
$content = file_get_contents($file);

$content = preg_replace('/Schedule::command\(\'queue:work --stop-when-empty\'\)[^;]+;/s', "Schedule::call(function () { \Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]); })->everyMinute()->withoutOverlapping();", $content);

$content = preg_replace('/Schedule::command\(\'app:clean-expired-photos\'\)[^;]+;/s', "Schedule::call(function () { \Illuminate\Support\Facades\Artisan::call('app:clean-expired-photos'); })->dailyAt('02:00')->withoutOverlapping();", $content);

file_put_contents($file, $content);
echo "Cron architecture updated to bypass proc_open!\n";
