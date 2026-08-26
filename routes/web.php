<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SensorViewController;
use App\Http\Controllers\TemplateViewController;
use App\Http\Controllers\SensorGroupViewController;
use App\Http\Controllers\MeasurementViewController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SensorExtraFieldController;
use App\Http\Controllers\Api\CollaborationController;
use App\Http\Controllers\CollaborationViewController;
use App\Http\Controllers\Api\Auth\GoogleController as ApiGoogleController;
use App\Http\Controllers\TestMailController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\Api\SubscriptionPaymentController;

// =============================================
// RUTAS PÚBLICAS (sin autenticación)
// =============================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register'])->middleware('guest');

// Visor Público de Consumos
Route::get('/visor/{token}', [\App\Http\Controllers\PublicViewerController::class, 'show'])->name('public.visor');
// Landing pública
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/registro', [LandingController::class, 'register'])->name('landing.register');

// Google Auth
Route::get('/auth/google', [ApiGoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [ApiGoogleController::class, 'handleGoogleCallback']);

// Ruta de registro exitoso - Página estática
Route::get('/registro-exitoso', function () {
    $name = request()->get('name', 'Usuario');
    $email = request()->get('email', '');
    $subscriptionType = request()->get('subscription_type', 'domiciliario');

    $user = (object) [
        'name' => $name,
        'email' => $email,
        'subscription_type' => $subscriptionType,
    ];

    return view('auth.register-success', compact('user'));
})->name('register.success');

// =============================================
// RUTAS PROTEGIDAS CON MIDDLEWARE 'auth'
// =============================================
Route::middleware(['auth'])->group(function () {

    // Superadmin Panel (Protegido via middleware auth y clase dedicada)
    Route::prefix('superadmin')->middleware([\App\Http\Middleware\SuperAdminMiddleware::class])->group(function () {
        Route::get('/users', [\App\Http\Controllers\SuperAdminController::class, 'index'])->name('superadmin.users');
        Route::post('/users/{user}/plan', [\App\Http\Controllers\SuperAdminController::class, 'updatePlan'])->name('superadmin.users.plan');

        // Facturación paralela
        Route::post('/users/{user}/invoice', [\App\Http\Controllers\SuperAdminController::class, 'generateInvoice'])->name('superadmin.users.invoice');
        Route::get('/invoices', [\App\Http\Controllers\SuperAdminController::class, 'invoicesIndex'])->name('superadmin.invoices');
        Route::post('/invoices/{invoice}/status', [\App\Http\Controllers\SuperAdminController::class, 'changeInvoiceStatus'])->name('superadmin.invoices.status');
        Route::post('/invoices/{invoice}/resend', [\App\Http\Controllers\SuperAdminController::class, 'resendInvoice'])->name('superadmin.invoices.resend');
        Route::delete('/invoices/{invoice}', [\App\Http\Controllers\SuperAdminController::class, 'deleteInvoice'])->name('superadmin.invoices.delete');

        Route::post('/users/{user}/send-message', [\App\Http\Controllers\SuperAdminController::class, 'sendCustomMessage'])->name('superadmin.users.message');
        Route::delete('/users/{user}', [\App\Http\Controllers\SuperAdminController::class, 'deleteUser'])->name('superadmin.users.delete');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/update-settings', [DashboardController::class, 'updateSettings'])
        ->name('dashboard.update-settings');

    // =============================================
    // SENSORES (vistas)
    // =============================================
    Route::get('/sensors', [SensorViewController::class, 'index'])->name('sensors.index');
    Route::get('/sensors/create', [SensorViewController::class, 'create'])->name('sensors.create');
    Route::get('/sensors/{sensor}/edit', [SensorViewController::class, 'edit'])->name('sensors.edit');
    Route::get('/sensors/{sensor}', [SensorViewController::class, 'show'])->name('sensors.show');
    Route::get('/sensors/extra-fields', [SensorExtraFieldController::class, 'index'])->name('sensors.extra-fields');

    // =============================================
    // MEDICIONES (vistas)
    // =============================================
    Route::get('/mediciones', [MeasurementViewController::class, 'index'])->name('measurements.index');
    Route::get('/mediciones/select-sensor', [MeasurementViewController::class, 'selectSensor'])->name('measurements.select-sensor');

    // ✅ NUEVA RUTA: Vista específica para inspector colaborador
    Route::get('/mediciones/inspector', [MeasurementViewController::class, 'inspectorSelectSensor'])->name('measurements.inspector');

    Route::get('/mediciones/create/{sensor}', [MeasurementViewController::class, 'create'])->name('measurements.create');
    Route::get('/mediciones/edit/{measurement}', [MeasurementViewController::class, 'edit'])->name('measurements.edit');
    Route::get('/mediciones/bulk-import', [MeasurementViewController::class, 'showBulkImportForm'])
        ->name('measurements.bulk-import');
    Route::get('/mediciones/bulk-create/{sensor}', [MeasurementViewController::class, 'bulkCreate'])
        ->name('measurements.bulk-create')
        ->where('sensor', '[0-9]+');

    // =============================================
    // NUEVO FLUJO DE MEDICIONES MASIVAS (Bulk Measurement Flow)
    // =============================================
    Route::get('/bulk-measurements/select', [\App\Http\Controllers\BulkMeasurementFlowController::class, 'selectSensors'])
        ->name('bulk-measurements.select')
        ->middleware('auth');

    Route::post('/bulk-measurements/start', [\App\Http\Controllers\BulkMeasurementFlowController::class, 'startBulkMeasurement'])
        ->name('bulk-measurements.start')
        ->middleware('auth');

    Route::get('/bulk-measurements/create/{sensor}', [\App\Http\Controllers\BulkMeasurementFlowController::class, 'create'])
        ->name('bulk-measurements.create')
        ->where('sensor', '[0-9]+')
        ->middleware('auth');

    Route::post('/bulk-measurements/store', [\App\Http\Controllers\BulkMeasurementFlowController::class, 'store'])
        ->name('bulk-measurements.store')
        ->middleware('auth');

    Route::get('/bulk-measurements/next/{sensor}', [\App\Http\Controllers\BulkMeasurementFlowController::class, 'nextSensor'])
        ->name('bulk-measurements.next')
        ->where('sensor', '[0-9]+')
        ->middleware('auth');

    Route::get('/bulk-measurements/previous/{sensor}', [\App\Http\Controllers\BulkMeasurementFlowController::class, 'previousSensor'])
        ->name('bulk-measurements.previous')
        ->where('sensor', '[0-9]+')
        ->middleware('auth');

    Route::get('/bulk-measurements/cancel', [\App\Http\Controllers\BulkMeasurementFlowController::class, 'cancelBulkMeasurement'])
        ->name('bulk-measurements.cancel')
        ->middleware('auth');

    // =============================================
    // GRUPOS DE SENSORES (vistas)
    // =============================================
    Route::get('/sensor-groups', [SensorGroupViewController::class, 'index'])->name('sensor-groups.index');
    Route::get('/sensor-groups/create', [SensorGroupViewController::class, 'create'])->name('sensor-groups.create');

    // ✅ IMPORTACIÓN DE SENSORES - SOLO PREMIUM
    Route::get('/sensor-groups/bulk-import', [SensorGroupViewController::class, 'showBulkImportForm'])
        ->name('sensor-groups.bulk-import')
        ->middleware('subscription.gate:import_sensors');

    Route::get('/sensor-groups/{group}/edit', [SensorGroupViewController::class, 'edit'])
        ->name('sensor-groups.edit')
        ->where('group', '[0-9]+');
    Route::get('/sensor-groups/{group}', [SensorGroupViewController::class, 'show'])
        ->name('sensor-groups.show')
        ->where('group', '[0-9]+');
    Route::delete('/sensor-groups/{group}', [SensorGroupViewController::class, 'destroy'])
        ->name('sensor-groups.destroy')
        ->where('group', '[0-9]+');

    // =============================================
    // CAMPAÑAS DE NOTIFICACIÓN MASIVA (Fase 20)
    // =============================================
    Route::get('/campaigns/bulk', [\App\Http\Controllers\BulkNotificationController::class, 'index'])
        ->name('campaigns.bulk.index');
    Route::post('/campaigns/bulk/dispatch', [\App\Http\Controllers\BulkNotificationController::class, 'dispatchCampaign'])
        ->name('campaigns.bulk.dispatch');
    Route::get('/campaigns/bulk/schema/{id}', [\App\Http\Controllers\BulkNotificationController::class, 'getGroupSchema'])
        ->name('campaigns.bulk.schema')
        ->where('id', '[0-9]+');
    Route::get('/campaigns/bulk/export-links/{id}', [\App\Http\Controllers\BulkNotificationController::class, 'exportLinks'])
        ->name('campaigns.bulk.export')
        ->where('id', '[0-9]+');

    // Route temporarily moved to bottom

    // =============================================
    // PLANTILLAS (vistas)
    // =============================================
    Route::get('/templates', [TemplateViewController::class, 'index'])->name('templates.index');

    // ✅ CREAR PLANTILLA - SOLO PREMIUM
    Route::get('/templates/create', [TemplateViewController::class, 'create'])
        ->name('templates.create')
        ->middleware('subscription.gate:create_template');

    Route::get('/templates/{template}/edit', [TemplateViewController::class, 'edit'])
        ->name('templates.edit')
        ->where('template', '[0-9]+');

    // =============================================
    // CONSUMOS (vistas)
    // =============================================
    Route::get('/consumptions', fn() => view('consumptions.index'))->name('consumptions.index');
    // =============================================
    // PERFIL
    // =============================================

    Route::get('/profile', [\App\Http\Controllers\ProfileViewController::class, 'index'])->name('profile.index');
    Route::post('/profile/billing', [\App\Http\Controllers\ProfileViewController::class, 'updateBilling'])->name('profile.update-billing');
    Route::get('/profile/receipt/{id}', [\App\Http\Controllers\ProfileViewController::class, 'downloadReceipt'])->name('profile.download-receipt');

    // =============================================
    // COLABORACIONES
    // =============================================
    Route::get('/collaborations', [CollaborationViewController::class, 'index'])->name('collaborations.index');

    // =============================================
    // TEST EMAIL
    // =============================================
    Route::get('/test-email', [TestMailController::class, 'sendTestEmail'])->name('test.email');

    Route::get('/mediciones/inspector/create/{sensor}', [MeasurementViewController::class, 'inspectorCreate'])
        ->name('measurements.inspector.create')
        ->where('sensor', '[0-9]+');
});

// =============================================
// SUSCRIPCIONES Y PAGOS
// =============================================
Route::middleware(['auth'])->group(function () {
    // Vista de pago
    Route::get('/suscripcion/{plan}/pagar', function ($plan) {
        $user = auth()->user();

        // Verificar que el plan existe
        $planData = config('mercadopago.plans.' . $plan);
        if (!$planData) {
            return redirect('/dashboard')->with('error', 'Plan no encontrado.');
        }

        // Verificar si ya tiene suscripción activa
        $activeSubscription = \App\Models\Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($activeSubscription) {
            return redirect('/dashboard')->with('info', 'Ya tienes una suscripción activa.');
        }

        return view('subscriptions.payment', [
            'plan' => $plan,
            'planData' => $planData,
            'preferenceId' => session('preference_id')
        ]);
    })->name('subscription.payment');

    // Crear preferencia (API)
    Route::post('/api/subscription/create-preference', [SubscriptionPaymentController::class, 'createPreference'])
        ->name('subscription.create.preference');

    // Comprar Packs Extras (API)
    Route::post('/api/subscription/buy-packs', [SubscriptionPaymentController::class, 'buyExtraPacks'])
        ->name('subscription.buy.packs');

    // Webhook (público)
    Route::post('/api/subscription/webhook', [SubscriptionPaymentController::class, 'handleWebhook'])
        ->name('subscription.payment.webhook');

    // Callbacks de pago Packs Extra (públicos)
    Route::get('/subscription/success_packs', [SubscriptionPaymentController::class, 'handleSuccessPacks'])
        ->name('subscription.payment.success_packs');

    // Callbacks de pago (públicos)
    Route::get('/subscription/success/{plan}', [SubscriptionPaymentController::class, 'handleSuccess'])
        ->name('subscription.payment.success');
    Route::get('/subscription/failure/{plan}', [SubscriptionPaymentController::class, 'handleFailure'])
        ->name('subscription.payment.failure');
    Route::get('/subscription/pending/{plan}', [SubscriptionPaymentController::class, 'handlePending'])
        ->name('subscription.payment.pending');

    // Estado de suscripción (API)
    Route::get('/api/subscription/status', [SubscriptionPaymentController::class, 'getStatus'])
        ->name('subscription.status');

    Route::get('/profile', [App\Http\Controllers\ProfileViewController::class, 'index'])
        ->name('profile.index')
        ->middleware('auth');
});

// =============================================
// RUTAS DE INVITACIÓN (con autenticación)
// =============================================
Route::get('/invitacion/aceptar/{token}', function ($token) {
    return view('collaborations.accept', compact('token'));
})->name('collaborations.accept')->middleware('auth');

// =============================================
// RUTAS DE ESTABLECER CONTRASEÑA
// =============================================
Route::get('/establecer-contraseña/{token}', [SetPasswordController::class, 'showForm'])->name('password.set.form');
Route::post('/establecer-contraseña', [SetPasswordController::class, 'setPassword'])->name('password.set');

//Route::get('/mediciones', [MeasurementViewController::class, 'index'])->name('measurements.index');
// Ruta temporal para prueba (FUERA del grupo auth) - PUEDES ELIMINARLA AHORA
// Route::get('/test-sensors', fn() => view('sensors.index'))->name('test.sensors');