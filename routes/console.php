<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
// Corre el trabajador de cola 1 sola vez por minuto durante hasta agotar los trabajos
// Ideal para hospederÃ­as compartidas (cPanel/Wiroos) sin supervisorD.
Schedule::call(function () { \Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]); })->everyMinute()->withoutOverlapping();

// Tarea diaria de eliminación de fotos históricas (Día 365) y Alertas (Día 358)
Schedule::call(function () { \Illuminate\Support\Facades\Artisan::call('app:clean-expired-photos'); })->dailyAt('02:00')->withoutOverlapping();
