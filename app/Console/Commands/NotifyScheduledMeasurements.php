<?php

namespace App\Console\Commands;

use App\Mail\ScheduledMeasurementReminder;
use App\Models\User;
use App\Services\MeasurementScheduleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyScheduledMeasurements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-scheduled-measurements {--user= : ID opcional de usuario específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evalúa el Calendario de Tomas y Tolerancias y envía recordatorios por email a los usuarios con mediciones en ventana, en tolerancia o vencidas';

    /**
     * Execute the console command.
     */
    public function handle(MeasurementScheduleService $scheduleService): int
    {
        $userId = $this->option('user');
        $query = User::query()->whereNotNull('email');

        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();
        $sentCount = 0;

        foreach ($users as $user) {
            try {
                $alerts = $scheduleService->getActiveAlerts($user, $user->id);

                if (($alerts['total_actionable'] ?? 0) > 0) {
                    Mail::to($user->email)->send(new ScheduledMeasurementReminder($user, $alerts));
                    $sentCount++;
                    $this->info("Recordatorio enviado a {$user->email} ({$alerts['total_actionable']} avisos activos).");
                }
            } catch (\Throwable $e) {
                Log::error('Error enviando recordatorio de calendario de tomas a ' . $user->email . ': ' . $e->getMessage());
                $this->error("Error con {$user->email}: " . $e->getMessage());
            }
        }

        $this->info("Proceso finalizado. Total de correos enviados: {$sentCount}");
        return Command::SUCCESS;
    }
}
