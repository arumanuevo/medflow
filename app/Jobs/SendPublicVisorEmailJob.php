<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\Sensor;
use App\Mail\PublicVisorNotification;

class SendPublicVisorEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $sensor;
    public $messageBody;
    public $destEmail;

    /**
     * Create a new job instance.
     */
    public function __construct(Sensor $sensor, $messageBody = null, $destEmail = null)
    {
        $this->sensor = $sensor;
        $this->messageBody = $messageBody;
        $this->destEmail = $destEmail;
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

        // REDUNDANCIA EXTRA (Anti-Leak) 
        // Si estamos en entorno local, garantizo que cualquier email en memoria sea borrado por mi casilla segura.
        // Esto protege la aplicación incluso si otro dev lanza la Job de forma insegura desde tinker.
        if (app()->environment('local') && $this->destEmail !== 'scastellano10@gmail.com') {
            $this->destEmail = 'scastellano10@gmail.com';
        }

        // 4. Despachar email
        Mail::to($this->destEmail)->send(new PublicVisorNotification($this->sensor, $this->messageBody));
    }
}
