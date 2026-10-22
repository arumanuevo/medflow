<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Sensor;
use App\Mail\PublicVisorNotification;
use App\Services\EmailReputationService;

class SendPublicVisorEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $sensor;
    public $messageBody;
    public $destEmail;
    public $workspaceId;

    /**
     * Tries y timeout del Job
     */
    public $tries = 2;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(Sensor $sensor, $messageBody = null, $destEmail = null, $workspaceId = null)
    {
        $this->sensor = $sensor;
        $this->messageBody = $messageBody;
        $this->destEmail = $destEmail;
        $this->workspaceId = $workspaceId ?: ($sensor ? $sensor->user_id : null);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Verificar si el sensor tiene Token y generarlo en hilo de cola si faltara
        if (empty($this->sensor->public_token)) {
            $this->sensor->public_token = \Illuminate\Support\Str::random(32);
            $this->sensor->save();
        }

        // Si destEmail es nulo, este Job no debió haberse despachado pero retornamos seguro.
        if (empty($this->destEmail)) {
            return;
        }

        // 2. PROTECCIÓN REPUTACIÓN: Verificar lista negra interna de supresión
        if (EmailReputationService::isSuppressed($this->destEmail)) {
            Log::info("SendPublicVisorEmailJob: Email [{$this->destEmail}] omitido por estar en lista de supresión.");
            return;
        }

        // 3. REDUNDANCIA EXTRA (Anti-Leak para desarrollo local)
        if (app()->environment('local') && $this->destEmail !== 'scastellano10@gmail.com') {
            $this->destEmail = 'scastellano10@gmail.com';
        }

        // 4. Despachar email
        try {
            Mail::to($this->destEmail)->send(new PublicVisorNotification($this->sensor, $this->messageBody, $this->destEmail));
        } catch (\Throwable $e) {
            Log::error("SendPublicVisorEmailJob: Fallo al enviar a [{$this->destEmail}]: " . $e->getMessage());

            // Si el error de SMTP indica que la dirección no existe o fue rechazada permanentemente
            if (stripos($e->getMessage(), '550') !== false || stripos($e->getMessage(), 'Recipient address rejected') !== false) {
                EmailReputationService::addSuppression(
                    $this->destEmail,
                    'bounce',
                    'HardBounceImmediate',
                    $this->workspaceId,
                    'smtp_rejection',
                    ['error' => $e->getMessage()]
                );
            }
        }
    }
}
