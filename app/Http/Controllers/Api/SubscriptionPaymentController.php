<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;

class SubscriptionPaymentController extends Controller
{
    private $accessToken;

    public function __construct()
    {
        // ✅ Configurar el ACCESS_TOKEN de Mercado Pago
        $this->accessToken = config('mercadopago.access_token');

        if ($this->accessToken) {
            try {
                MercadoPagoConfig::setAccessToken($this->accessToken);
            } catch (\Exception $e) {
                Log::error('Error al configurar Mercado Pago: ' . $e->getMessage());
            }
        } else {
            Log::warning('MERCADO_PAGO_ACCESS_TOKEN no configurado en .env');
        }
    }

    /**
     * Crear una preferencia de pago para una suscripción
     */
    public function createPreference(Request $request)
    {
        $user = $request->user();

        Log::info('🔵 Iniciando creación de preferencia', [
            'user_id' => $user->id,
            'email' => $user->email,
            'request_data' => $request->all()
        ]);

        if (!$this->accessToken) {
            Log::error('❌ Access token no configurado');
            return response()->json([
                'success' => false,
                'message' => 'Mercado Pago no está configurado correctamente. Contacta al administrador.'
            ], 500);
        }

        $validator = Validator::make($request->all(), [
            'plan' => 'required|in:free,basico,premium',
        ]);

        if ($validator->fails()) {
            Log::error('❌ Error de validación', ['errors' => $validator->errors()->toArray()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $plan = $request->plan;
        $planConfig = config('mercadopago.plans.' . $plan);

        if (!$planConfig) {
            Log::error('❌ Plan no encontrado', ['plan' => $plan]);
            return response()->json([
                'success' => false,
                'message' => 'Plan no encontrado'
            ], 404);
        }

        Log::info('📋 Datos del plan', [
            'plan' => $plan,
            'config' => $planConfig
        ]);

        // Verificar si el usuario ya tiene una suscripción activa
        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($activeSubscription) {
            Log::info('⚠️ Usuario ya tiene suscripción activa', [
                'user_id' => $user->id,
                'subscription_id' => $activeSubscription->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes una suscripción activa',
                'data' => [
                    'subscription' => $activeSubscription
                ]
            ], 400);
        }

        try {
            Log::info('🔧 Creando cliente de preferencia');
            $client = new PreferenceClient();

            // ✅ Generar URLs dinámicas
            $baseUrl = config('app.url');
            $successUrl = $baseUrl . '/subscription/success/' . $plan;
            $failureUrl = $baseUrl . '/subscription/failure/' . $plan;
            $pendingUrl = $baseUrl . '/subscription/pending/' . $plan;
            $notificationUrl = $baseUrl . '/api/subscription/webhook';

            $preferenceData = [
                "items" => [
                    [
                        "title" => $planConfig['name'] . ' - MedFlow',
                        "description" => $planConfig['description'],
                        "quantity" => 1,
                        "unit_price" => (float) $planConfig['price'] / 100,
                        "currency_id" => $planConfig['currency']
                    ]
                ],
                "payer" => [
                    "email" => $user->email,
                    "name" => $user->name,
                ],
                "back_urls" => [
                    "success" => $successUrl,
                    "failure" => $failureUrl,
                    "pending" => $pendingUrl
                ],
                "auto_return" => "approved",
                "external_reference" => $user->id . '_' . $plan . '_' . time(),
                "notification_url" => $notificationUrl,
                "statement_descriptor" => "MedFlow",
            ];

            Log::info('📤 Enviando datos a Mercado Pago', [
                'data' => $preferenceData
            ]);

            $preference = $client->create($preferenceData);

            Log::info('✅ Preferencia creada con éxito', [
                'preference_id' => $preference->id,
                'init_point' => $preference->init_point ?? 'N/A'
            ]);

            // Guardar la preferencia en la base de datos
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan' => $plan,
                'status' => 'pending',
                'preference_id' => $preference->id,
                'amount' => (float) $planConfig['price'] / 100,
                'currency' => $planConfig['currency'],
            ]);

            session(['preference_id' => $preference->id]);

            Log::info('✅ Suscripción creada en BD', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'preference_id' => $preference->id,
                    'subscription_id' => $subscription->id,
                    'public_key' => config('mercadopago.public_key'),
                ]
            ]);

        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            $response = $e->getApiResponse();
            Log::error('❌ Error API de Mercado Pago', [
                'message' => $e->getMessage(),
                'status' => $response ? $response->getStatusCode() : 'N/A',
                'response' => $response ? $response->getContent() : 'N/A'
            ]);

            $errorMessage = 'Error en la API de Mercado Pago: ';
            if ($response && $response->getContent()) {
                $content = json_decode($response->getContent(), true);
                if (isset($content['message'])) {
                    $errorMessage .= $content['message'];
                } else {
                    $errorMessage .= $e->getMessage();
                }
            } else {
                $errorMessage .= $e->getMessage();
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);

        } catch (\Exception $e) {
            Log::error('❌ Error al crear preferencia', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la preferencia de pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manejar el éxito del pago (redirección)
     */
    public function handleSuccess(Request $request, $plan)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/login')->with('error', 'Debes iniciar sesión para verificar tu suscripción.');
        }

        $subscription = Subscription::where('user_id', $user->id)
            ->where('plan', $plan)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$subscription) {
            return redirect('/dashboard')->with('error', 'No se encontró la suscripción pendiente.');
        }

        $paymentId = $request->query('payment_id');

        if ($paymentId && $this->accessToken) {
            try {
                $client = new PaymentClient();
                $payment = $client->get($paymentId);

                if ($payment->status === 'approved') {
                    $subscription->markAsPaid($paymentId);

                    $user->subscription_type = $plan === 'premium' ? 'corporativo' : 'domiciliario';
                    $user->subscription_plan = $plan;
                    // Al renovar el plan completo, preserva la cantidad de packs, pero si el monto del request indicó menos packs, podría limpiarse, pero como es renovación general los asume.
                    $user->save();

                    if ($plan === 'premium') {
                        $user->assignRole('inspector');
                    }

                    Log::info('Pago verificado exitosamente', [
                        'user_id' => $user->id,
                        'payment_id' => $paymentId,
                        'plan' => $plan
                    ]);

                    return redirect('/dashboard')->with('success', '¡Suscripción activada correctamente! Plan: ' . ucfirst($plan));
                }
            } catch (\Exception $e) {
                Log::error('Error al verificar pago: ' . $e->getMessage());
            }
        }

        return redirect('/dashboard')->with('info', 'Tu pago está siendo procesado. Recibirás una confirmación en breve.');
    }

    /**
     * Manejar el fallo del pago
     */
    public function handleFailure(Request $request, $plan)
    {
        $user = auth()->user();

        if ($user) {
            $subscription = Subscription::where('user_id', $user->id)
                ->where('plan', $plan)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($subscription) {
                $subscription->status = 'cancelled';
                $subscription->save();
            }
        }

        return redirect('/dashboard')->with('error', 'El pago no se pudo completar. Intenta nuevamente.');
    }

    /**
     * Manejar el pago pendiente
     */
    public function handlePending(Request $request, $plan)
    {
        return redirect('/dashboard')->with('info', 'Tu pago está pendiente de confirmación.');
    }

    /**
     * Webhook para notificaciones de Mercado Pago
     */
    public function handleWebhook(Request $request)
    {
        Log::info('Webhook de Mercado Pago recibido', $request->all());

        try {
            $data = $request->all();

            if (!isset($data['action']) || $data['action'] != 'payment.updated') {
                return response()->json(['status' => 'ignored']);
            }

            $paymentId = $data['data']['id'] ?? null;

            if (!$paymentId) {
                return response()->json(['status' => 'error', 'message' => 'Payment ID not found'], 400);
            }

            if (!$this->accessToken) {
                Log::error('Access token no configurado para webhook');
                return response()->json(['status' => 'error', 'message' => 'Access token not configured'], 500);
            }

            MercadoPagoConfig::setAccessToken($this->accessToken);

            $client = new PaymentClient();
            $payment = $client->get($paymentId);

            $externalReference = $payment->external_reference ?? '';
            $parts = explode('_', $externalReference);
            $userId = $parts[0] ?? null;
            $plan = $parts[1] ?? 'basico';

            if (!$userId) {
                return response()->json(['status' => 'error', 'message' => 'User ID not found'], 400);
            }

            if ($payment->status === 'approved') {
                $subscription = Subscription::where('user_id', $userId)
                    ->where('plan', $plan)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();

                if (!$subscription) {
                    $subscription = Subscription::create([
                        'user_id' => $userId,
                        'plan' => $plan,
                        'status' => 'active',
                        'payment_id' => $paymentId,
                        'amount' => $payment->transaction_amount ?? 0,
                        'currency' => $payment->currency_id ?? 'ARS',
                        'paid_at' => now(),
                        'expires_at' => now()->addMinutes(3), // 💡 Modificado a 3 min para QA
                    ]);
                } else {
                    $subscription->markAsPaid($paymentId);
                }

                $user = User::find($userId);
                if ($user) {
                    $user->subscription_type = $plan === 'premium' ? 'corporativo' : 'domiciliario';
                    $user->subscription_plan = $plan;
                    $user->save();

                    if ($plan === 'premium') {
                        $user->assignRole('inspector');
                    }
                }

                Log::info('Pago confirmado por webhook', [
                    'user_id' => $userId,
                    'payment_id' => $paymentId,
                    'plan' => $plan
                ]);

                return response()->json(['status' => 'success']);
            }

            return response()->json(['status' => 'received']);

        } catch (\Exception $e) {
            Log::error('Error en webhook: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener el estado de la suscripción del usuario
     */
    public function getStatus(Request $request)
    {
        try {
            $user = $request->user();

            $subscription = Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->latest()
                ->first();

            $pending = Subscription::where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'has_active_subscription' => $subscription !== null,
                    'has_pending_payment' => $pending !== null,
                    'subscription' => $subscription,
                    'pending_payment' => $pending,
                    'plans' => config('mercadopago.plans')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estado de suscripción: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el estado de la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Comprar Packs Extras separados (Upgrade Mid-Cycle)
     */
    public function buyExtraPacks(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'packs' => 'required|integer|min:1|max:100', // Ampliado a 100 para testing de importaciones masivas
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $packs = (int) $request->packs;

        // ✅ BYPASS PARA ENTORNO DE DESARROLLO (LOCAL)
        if (app()->environment('local')) {
            $user->additional_sensor_packs += $packs;
            $user->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'preference_id' => 'sandbox_debug_' . time(),
                    'is_local' => true
                ]
            ]);
        }

        if (!$this->accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Mercado Pago no está configurado.'
            ], 500);
        }

        $pricePerPack = 10000; // 10.000 ARS por Pack de 10 sensores

        try {
            $client = new PreferenceClient();
            $baseUrl = config('app.url');

            // Creamos un plan ficticio "packs"
            $successUrl = $baseUrl . '/subscription/success_packs?packs=' . $packs;
            $failureUrl = $baseUrl . '/subscription/failure/premium';
            $pendingUrl = $baseUrl . '/subscription/pending/premium';

            $preferenceData = [
                "items" => [
                    [
                        "title" => "Pack Extra de Sensores x" . ($packs * 10) . ' - MedFlow',
                        "description" => "Expansión de capacidad para " . ($packs * 10) . " sensores adicionales.",
                        "quantity" => 1,
                        "unit_price" => (float) ($packs * $pricePerPack),
                        "currency_id" => "ARS"
                    ]
                ],
                "payer" => [
                    "email" => $user->email,
                    "name" => $user->name,
                ],
                "back_urls" => [
                    "success" => $successUrl,
                    "failure" => $failureUrl,
                    "pending" => $pendingUrl
                ],
                "auto_return" => "approved",
                "external_reference" => $user->id . '_packs_' . time(),
                "statement_descriptor" => "MedFlow Extra",
            ];

            $preference = $client->create($preferenceData);

            return response()->json([
                'success' => true,
                'data' => [
                    'preference_id' => $preference->id,
                    'public_key' => config('mercadopago.public_key'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la preferencia de packs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handlers for success packs
     */
    public function handleSuccessPacks(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/login')->with('error', 'Debes iniciar sesión.');
        }

        $paymentId = $request->query('payment_id');
        $packs = (int) $request->query('packs', 0);

        if ($paymentId && $packs > 0) {
            try {
                $client = new PaymentClient();
                $payment = $client->get($paymentId);

                if ($payment->status === 'approved') {
                    $user->additional_sensor_packs += $packs;
                    $user->save();

                    return redirect('/profile')->with('success', "¡Excelente! Has expandido tu límite exitosamente instalando {$packs} paquete(s) de expansión.");
                }
            } catch (\Exception $e) {
                Log::error('Error validando pago de packs: ' . $e->getMessage());
            }
        }

        return redirect('/profile')->with('error', 'Hubo un problema verificando el pago de tu pack.');
    }

    // =============================================
    // ✅ MÉTODOS DE DEPURACIÓN (SOLO LOCAL)
    // =============================================

    /**
     * Activar suscripción de prueba (emulación sin Mercado Pago)
     */
    public function debugActivate(Request $request)
    {
        // ✅ Solo permitir en entorno local
        if (!app()->environment('local')) {
            return response()->json([
                'success' => false,
                'message' => 'Esta acción solo está disponible en entorno de desarrollo.'
            ], 403);
        }

        try {
            $user = $request->user();
            $plan = $request->input('plan', 'basico');
            $durationMinutes = $request->input('duration_minutes', 43200); // ✅ 30 días para QA/Producción

            // Validar plan
            if (!in_array($plan, ['free', 'basico', 'premium'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plan inválido. Debe ser "free", "basico" o "premium".'
                ], 422);
            }

            // ✅ Cancelar suscripciones activas anteriores
            Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            // ✅ Crear nueva suscripción de prueba
            $expiresAt = ($plan === 'free') ? null : now()->addMinutes($durationMinutes);

            $amount = 0.00;
            if ($plan === 'premium') {
                $amount = 25.00;
            } elseif ($plan === 'basico') {
                $amount = 10.00;
            }

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan' => $plan,
                'status' => 'active',
                'amount' => $amount,
                'currency' => 'ARS',
                'paid_at' => now(),
                'expires_at' => $expiresAt,
                'payment_id' => 'debug_' . uniqid(),
                'preference_id' => 'debug_' . uniqid(),
            ]);

            // ✅ Actualizar el usuario
            $user->subscription_type = $plan === 'premium' ? 'corporativo' : 'domiciliario';
            $user->subscription_plan = $plan;
            $user->save();

            // ✅ Asignar rol según plan
            if ($plan === 'premium') {
                $user->assignRole('inspector');
            } else {
                $user->assignRole('consumidor');
            }

            // ✅ Guardar plan anterior en sesión para detección de downgrade
            if ($plan !== 'free') {
                session(['previous_plan' => $plan]);
            }

            Log::info('✅ Suscripción de prueba activada', [
                'user_id' => $user->id,
                'plan' => $plan,
                'expires_at' => $subscription->expires_at,
                'duration_minutes' => $durationMinutes
            ]);

            return response()->json([
                'success' => true,
                'message' => ($plan === 'free')
                    ? "Suscripción Free activada (permanente)."
                    : "Suscripción {$plan} activada por 30 días para pruebas.",
                'data' => [
                    'subscription' => $subscription,
                    'expires_at' => $subscription->expires_at ? $subscription->expires_at->toDateTimeString() : null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error en debugActivate: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al activar suscripción de prueba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forzar expiración de la suscripción (debug)
     */
    public function debugExpire(Request $request)
    {
        // ✅ Solo permitir en entorno local
        if (!app()->environment('local')) {
            return response()->json([
                'success' => false,
                'message' => 'Esta acción solo está disponible en entorno de desarrollo.'
            ], 403);
        }

        try {
            $user = $request->user();

            $subscriptions = Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->get();

            $subId = null;
            if ($subscriptions->isNotEmpty()) {
                // ✅ 1. Marcar TODAS las suscripciones como expiradas
                foreach ($subscriptions as $subscription) {
                    $subscription->status = 'expired';
                    $subscription->expires_at = now()->subSecond();
                    $subscription->save();
                    $subId = $subscription->id;
                }
            }

            // ✅ 2. ACTUALIZAR USUARIO A 'free' (MISMA LÓGICA QUE checkAndUpdate)
            $user->subscription_type = 'domiciliario';
            $user->subscription_plan = 'free';
            $user->save();
            $user->syncRoles(['consumidor']);

            // ✅ 3. PAUSAR ACCESOS A COLABORADORES SI EXISTEN
            \App\Models\WorkspaceCollaborator::where('workspace_id', $user->id)
                ->where('status', 'active')
                ->update(['is_paused' => true]);

            Log::info('⏰ Suscripción expirada o reseteada por depuración', [
                'user_id' => $user->id,
                'subscription_id' => $subId,
                'new_plan' => 'free'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Suscripción expirada correctamente. Usuario cambiado a Free.',
                'data' => [
                    'subscription_id' => $subId,
                    'expired_at' => now()->toDateTimeString(),
                    'new_plan' => 'free'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error en debugExpire: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al expirar suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar todo el historial de suscripciones del usuario (debug)
     */
    public function debugClear(Request $request)
    {
        // ✅ Solo permitir en entorno local
        if (!app()->environment('local')) {
            return response()->json([
                'success' => false,
                'message' => 'Esta acción solo está disponible en entorno de desarrollo.'
            ], 403);
        }

        try {
            $user = $request->user();

            $count = Subscription::where('user_id', $user->id)->count();

            Subscription::where('user_id', $user->id)->delete();

            // ✅ Resetear el usuario a estado por defecto
            $user->subscription_type = 'domiciliario';
            $user->subscription_plan = 'free';
            $user->save();
            $user->syncRoles(['consumidor']);

            // ✅ Pausar accesos iterativos
            \App\Models\WorkspaceCollaborator::where('workspace_id', $user->id)
                ->where('status', 'active')
                ->update(['is_paused' => true]);

            Log::info('🧹 Historial de suscripciones limpiado', [
                'user_id' => $user->id,
                'deleted_count' => $count
            ]);

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$count} suscripciones del historial.",
                'data' => [
                    'deleted_count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error en debugClear: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Panel de depuración (vista)
     */
    public function debug(Request $request)
    {
        // ✅ Solo permitir en entorno local
        if (!app()->environment('local')) {
            abort(404);
        }

        $user = $request->user();

        $currentSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $allSubscriptions = Subscription::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $userStatus = [
            'subscription_type' => $user->subscription_type,
            'subscription_plan' => $user->subscription_plan,
            'roles' => $user->getRoleNames()->toArray(),
            'has_active_subscription' => $currentSubscription && $currentSubscription->isActive(),
        ];

        return view('dev.subscription-debug', compact(
            'currentSubscription',
            'allSubscriptions',
            'userStatus'
        ));
    }

    /**
     * Verificar si la suscripción ha expirado y actualizar
     */
    public function checkExpiration(Request $request)
    {
        $user = $request->user();
        $service = new \App\Services\Subscription\SubscriptionService($user);
        $expired = $service->checkAndUpdateExpiredSubscription();

        return response()->json([
            'success' => true,
            'expired' => $expired,
            'plan' => $service->getFullStatus()
        ]);
    }
}