<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SubscriptionHelperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Cargar el helper manualmente
        require_once app_path('Helpers/SubscriptionHelper.php');
    }

    public function register()
    {
        //
    }
}