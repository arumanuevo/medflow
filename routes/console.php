<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
// Corre el trabajador de cola 1 sola vez por minuto durante hasta agotar los trabajos
// Ideal para hospederías compartidas (cPanel/Wiroos) sin supervisorD.
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/queue-work.log'));
