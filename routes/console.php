<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
// Corre el trabajador de cola 1 sola vez por minuto durante hasta agotar los trabajos
// Ideal para hospederÃ­as compartidas (cPanel/Wiroos) sin supervisorD.
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/queue-work.log'));

// Tarea diaria de eliminación de fotos históricas (Día 365) y Alertas (Día 358)
Schedule::command('app:clean-expired-photos')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/clean-photos.log'));
