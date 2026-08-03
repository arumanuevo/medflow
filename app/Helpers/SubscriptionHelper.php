<?php
// app/Helpers/SubscriptionHelper.php

namespace App\Helpers;

use App\Services\Subscription\SubscriptionGate;

if (!function_exists('canAccess')) {
    /**
     * Verificar si el usuario autenticado puede acceder a una funcionalidad
     * 
     * @param string $gate
     * @return bool
     */
    function canAccess(string $gate): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        $gateService = new SubscriptionGate($user);
        return $gateService->allows($gate);
    }
}

if (!function_exists('gateMessage')) {
    /**
     * Obtener mensaje para una puerta
     */
    function gateMessage(string $gate): string
    {
        $messages = config('subscription_gates.messages', []);
        return $messages[$gate] ?? $messages['default'] ?? 'Funcionalidad no disponible para tu plan.';
    }
}