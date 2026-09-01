<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Measurement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\PhotoExpirationWarning;
use Illuminate\Support\Facades\Log;

class CleanExpiredPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-expired-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes photos older than 365 days and sends warnings for photos at 358 days old.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Iniciando limpieza de fotos caducadas...");

        // 1. Borrar fotos con más de 365 días
        $expirationDate = Carbon::now()->subDays(365);

        $expiredMeasurements = Measurement::where('measured_at', '<=', $expirationDate)
            ->whereNotNull('data->foto')
            ->where('data->foto', '!=', 'Sin Foto')
            ->where('data->foto', '!=', 'Expirada')
            ->get();

        $deletedCount = 0;
        foreach ($expiredMeasurements as $measurement) {
            $data = $measurement->data;
            if (isset($data['foto']) && $data['foto'] !== 'Sin Foto' && $data['foto'] !== 'Expirada') {
                $filePath = public_path(ltrim($data['foto'], '/'));
                if (file_exists($filePath) && is_file($filePath)) {
                    @unlink($filePath);
                }
                $data['foto'] = 'Expirada';
                $measurement->data = $data;
                $measurement->save();
                $deletedCount++;
            }
        }

        Log::info("Limpieza de fotos: {$deletedCount} imágenes borradas físicamente.");
        $this->info("{$deletedCount} fotos expiradas borradas.");

        // 2. Alertar sobre fotos a 7 días de expirar (358 - 364)
        $warningStartD = Carbon::now()->subDays(364)->startOfDay();
        $warningEndD = Carbon::now()->subDays(358)->endOfDay();

        $warningMeasurements = Measurement::with('sensor.group.user')
            ->whereBetween('measured_at', [$warningStartD, $warningEndD])
            ->whereNotNull('data->foto')
            ->where('data->foto', '!=', 'Sin Foto')
            ->where('data->foto', '!=', 'Expirada')
            ->get();

        // Agrupar por usuario
        $usersToWarn = [];
        foreach ($warningMeasurements as $m) {
            $user = $m->sensor->group->user ?? null;
            if ($user) {
                if (!isset($usersToWarn[$user->id])) {
                    $usersToWarn[$user->id] = [
                        'user' => $user,
                        'count' => 0
                    ];
                }
                $usersToWarn[$user->id]['count']++;
            }
        }

        foreach ($usersToWarn as $userId => $info) {
            $u = $info['user'];
            $count = $info['count'];

            // Enviar correo (Encola)
            Mail::to($u->email)->queue(new PhotoExpirationWarning($u, $count));
            Log::info("Alerta enviada a {$u->email} por {$count} fotos por expirar.");
        }

        $this->info("Alertas enviadas a " . count($usersToWarn) . " administradores.");
    }
}
