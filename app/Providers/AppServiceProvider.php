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
    }
}