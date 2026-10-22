<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Subscription\SubscriptionGate;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // ✅ Registrar directiva de Blade para canAccess
        \Blade::if('canAccess', function (string $gate) {
            $user = auth()->user();
            if (!$user) {
                return false;
            }
            $gateService = new SubscriptionGate($user);
            return $gateService->allows($gate);
        });

        // ✅ Registrar directiva de Blade para gateMessage
        \Blade::directive('gateMessage', function (string $gate) {
            return "<?php echo \App\Helpers\SubscriptionHelper::gateMessage($gate); ?>";
        });

        // 🛡️ Filtro global anti-rebote: cancela cualquier envío saliente a emails suprimidos
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, function ($event) {
            try {
                $toAddresses = $event->message->getTo();
                if (!empty($toAddresses)) {
                    foreach ($toAddresses as $address) {
                        $email = is_object($address) ? $address->getAddress() : (string)$address;
                        if (\App\Services\EmailReputationService::isSuppressed($email)) {
                            \Illuminate\Support\Facades\Log::warning("MessageSending: Envío cancelado preventivamente hacia [{$email}] por estar en lista de supresión.");
                            return false; // Retornar false cancela el envío en Symfony Mailer
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silencioso para no interrumpir el flujo en caso de excepción
            }
        });
    }
}