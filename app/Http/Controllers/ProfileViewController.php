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

        $billingHistory = $user->subscriptions()->orderBy('created_at', 'desc')->get();

        return view('profile.index', compact(
            'user',
            'showDowngradeAlert',
            'previousPlan',
            'downgradeMessage',
            'billingHistory'
        ));
    }

    /**
     * Actualiza la información fiscal del usuario
     */
    public function updateBilling(Request $request)
    {
        $request->validate([
            'cuit' => 'required|string|max:20',
            'condicion_iva' => 'required|string|max:50',
            'condicion_venta' => 'required|string|max:50',
            'email_facturacion' => 'required|string|max:255',
            'descripcion_servicio' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $user->cuit = $request->cuit;
        $user->condicion_iva = $request->condicion_iva;
        $user->condicion_venta = $request->condicion_venta;
        $user->email_facturacion = $request->email_facturacion;
        $user->descripcion_servicio = $request->descripcion_servicio;
        $user->save();

        return back()->with('success', 'Datos fiscales actualizados correctamente.');
    }

    /**
     * Descarga un comprobante interno provisorio en PDF
     */
    public function downloadReceipt($id)
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('profile.receipt_pdf', compact('user', 'subscription'));

        return $pdf->download('Comprobante_Pago_MedFlow_' . $subscription->id . '.pdf');
    }
}
