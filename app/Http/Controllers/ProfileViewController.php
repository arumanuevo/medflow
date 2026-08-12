<?php
// app/Http/Controllers/ProfileViewController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileViewController extends Controller
{
    /**
     * Mostrar la página de perfil del usuario
     */
    public function index()
    {
        $user = Auth::user();

        // ✅ Variables para la alerta de downgrade
        $showDowngradeAlert = false;
        $previousPlan = null;
        $downgradeMessage = '';

        // ✅ Verificar si el usuario tiene una suscripción activa
        $activeSubscription = $user->getActiveSubscription();

        // ✅ CASO 1 & 2: Solo aplican si NO hay una suscripción activa
        if (!$activeSubscription) {
            // CASO 1: No tiene suscripción activa pero su plan en BD es pago
            if ($user->subscription_plan === 'basico' || $user->subscription_plan === 'premium') {
                $showDowngradeAlert = true;
                $previousPlan = $user->subscription_plan === 'premium' ? 'Premium' : 'Básico';
                $downgradeMessage = "⚠️ Tu suscripción {$previousPlan} ha expirado o fue cancelada. Has perdido acceso a funcionalidades premium.";
            } else {
                // CASO 2: La suscripción expiró y el usuario está como free
                $lastSubscription = Subscription::where('user_id', $user->id)
                    ->where('status', 'expired')
                    ->whereIn('plan', ['basico', 'premium'])
                    ->latest()
                    ->first();

                if ($lastSubscription) {
                    $showDowngradeAlert = true;
                    $previousPlan = $lastSubscription->plan === 'premium' ? 'Premium' : 'Básico';
                    $downgradeMessage = "⚠️ Tu suscripción {$previousPlan} ha expirado. Renueva para recuperar tus beneficios.";
                }
            }
        }

        // ✅ CASO 3: Tiene rol 'inspector' pero está en plan Free (solo para usuarios que fueron Premium)
        if ($user->hasRole('inspector') && $user->subscription_plan === 'free') {
            $showDowngradeAlert = true;
            $previousPlan = 'Premium';
            $downgradeMessage = "⚠️ Tu suscripción Premium ha expirado. Has perdido acceso a funcionalidades premium.";
        }

        return view('profile.index', compact(
            'user',
            'showDowngradeAlert',
            'previousPlan',
            'downgradeMessage'
        ));
    }
}
