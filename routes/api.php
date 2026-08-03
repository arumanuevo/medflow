<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConsumptionController;
use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\MeasurementController;
use App\Http\Controllers\Api\SensorGroupController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\SensorGroupSharedAccessController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PendingInvitationController;
use App\Http\Controllers\Api\BulkMeasurementController;
use App\Http\Controllers\Api\HelpContentController;
use App\Http\Controllers\Api\BulkSensorImportController;
use App\Http\Controllers\Api\BulkMeasurementImportController;
use App\Http\Controllers\Api\Auth\GoogleController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\SensorExtraFieldController;
use App\Http\Controllers\Api\CollaborationController;
use App\Http\Controllers\Api\AccessTokenController;
use App\Http\Controllers\Api\SubscriptionPaymentController;
use App\Http\Controllers\Api\SubscriptionPlanController;

// =====================================================
// RUTAS PÚBLICAS (sin autenticación)
// =====================================================
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'registerConsumer']);

// ✅ Ruta pública para campos extras (solo para pruebas)
Route::get('/sensor-groups/extra-fields', [BulkSensorImportController::class, 'getExtraFields'])
    ->name('api.sensor-groups.extra-fields');

// =====================================================
// RUTAS PROTEGIDAS CON SANCTUM
// =====================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // =====================================================
    // AUTENTICACIÓN
    // =====================================================
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/users/{user}/assign-roles', [AuthController::class, 'assignRoles']);

    // =====================================================
    // CAMPOS EXTRAS DE SENSORES
    // =====================================================
    Route::prefix('sensors/extra-fields')->group(function () {
        Route::get('/', [SensorExtraFieldController::class, 'getFields'])->name('api.sensors.extra-fields.get');
        Route::delete('/', [SensorExtraFieldController::class, 'deleteField'])->name('api.sensors.extra-fields.delete');
        Route::put('/rename', [SensorExtraFieldController::class, 'renameField'])->name('api.sensors.extra-fields.rename');
    });

    // ⚠️ Las rutas con parámetros DEBEN ir DESPUÉS
    Route::get('/sensors/{sensor}', [SensorController::class, 'show'])->name('api.sensors.show');
    Route::put('/sensors/{sensor}', [SensorController::class, 'update'])->name('api.sensors.update');
    Route::delete('/sensors/{sensor}', [SensorController::class, 'destroy'])->name('api.sensors.destroy');

    // =====================================================
    // SENSORES
    // =====================================================
    Route::apiResource('/sensors', SensorController::class)
        ->except(['create', 'edit'])
        ->parameter('sensor', 'sensor');

    Route::get('/sensors/with-remaining-days', [SensorController::class, 'getSensorsWithRemainingDays'])
        ->name('api.sensors.with-remaining-days');

    Route::put('/sensors/{sensor}/next-measurement-days', [SensorController::class, 'setNextMeasurementDays'])
        ->name('api.sensors.next-measurement-days')
        ->where('sensor', '[0-9]+');

    Route::delete('/sensors/{sensor}', [SensorController::class, 'destroy'])
        ->name('api.sensors.destroy')
        ->where('sensor', '[0-9]+');

    Route::post('/sensors/check-identifiers', [SensorController::class, 'checkIdentifiers'])
        ->name('api.sensors.check-identifiers');

    // =====================================================
    // MEDICIONES
    // =====================================================
    Route::apiResource('/measurements', MeasurementController::class)
        ->except(['create', 'edit'])
        ->parameter('measurement', 'measurement');

    Route::get('/sensors/{sensor}/measurements', [MeasurementController::class, 'index'])
        ->name('api.sensors.measurements.index')
        ->where('sensor', '[0-9]+');

    Route::post('/measurements/upload-photo', [MeasurementController::class, 'uploadPhoto'])
        ->name('api.measurements.upload-photo');

    Route::get('/measurements/{measurement}/errors', [MeasurementController::class, 'getMeasurementErrors'])
        ->name('api.measurements.errors');

    Route::get('/measurements/errors/{errorType}', [MeasurementController::class, 'getMeasurementsByErrorType'])
        ->name('api.measurements.errors.by-type');

    Route::get('/measurements/errors/{errorType}/details', [MeasurementController::class, 'getErrorDetails'])
        ->name('api.measurements.errors.details');

    Route::get('/measurements/error-stats', [MeasurementController::class, 'getErrorStats'])
        ->name('api.measurements.error-stats');

    // =====================================================
    // GRUPOS DE SENSORES
    // =====================================================
    Route::apiResource('/sensor-groups', SensorGroupController::class)
        ->except(['create', 'edit'])
        ->parameter('group', 'group');

    // Acceso compartido a grupos de sensores
    Route::get('/sensor-groups/{group}/shared-access', [SensorGroupSharedAccessController::class, 'index'])
        ->name('api.sensor-groups.shared-access.index');

    Route::post('/sensor-groups/{group}/shared-access', [SensorGroupSharedAccessController::class, 'store'])
        ->name('api.sensor-groups.shared-access.store');

    Route::delete('/sensor-groups/{group}/shared-access/{access}', [SensorGroupSharedAccessController::class, 'destroy'])
        ->name('api.sensor-groups.shared-access.destroy');

    // Invitaciones pendientes
    Route::get('/sensor-groups/{group}/invitations', [PendingInvitationController::class, 'index'])
        ->name('api.sensor-groups.invitations.index');

    Route::post('/sensor-groups/{group}/invitations', [PendingInvitationController::class, 'store'])
        ->name('api.sensor-groups.invitations.store');

    Route::delete('/sensor-groups/{group}/invitations/{invitation}', [PendingInvitationController::class, 'destroy'])
        ->name('api.sensor-groups.invitations.destroy');

    // =====================================================
    // PLANTILLAS - ✅ RUTAS ESPECÍFICAS PRIMERO
    // =====================================================
    
    // ✅ 1. Ruta específica: Obtener campos predefinidos por tipo (SIN parámetros)
    Route::get('/templates/predefined-fields', [TemplateController::class, 'getPredefinedFields'])
        ->name('api.templates.predefined-fields');
    
    // ✅ 2. Ruta específica: Obtener campos de una plantilla (CON parámetro)
    Route::get('/templates/{template}/fields', [TemplateController::class, 'getFields'])
        ->name('api.templates.fields');
    
    // ✅ 3. Rutas CRUD de plantillas (apiResource)
    Route::apiResource('/templates', TemplateController::class)
        ->except(['create', 'edit'])
        ->parameter('template', 'template');

    // =====================================================
    // CONSUMOS
    // =====================================================
    Route::get('/consumptions', [ConsumptionController::class, 'index'])
        ->name('api.consumptions.index');

    Route::post('/consumptions/calculate', [ConsumptionController::class, 'calculate'])
        ->name('api.consumptions.calculate');

    Route::post('/consumptions/calculate-for-sensor', [ConsumptionController::class, 'calculateForSensor'])
        ->name('api.consumptions.calculate-for-sensor');

    Route::post('/consumptions/calculate-all', [ConsumptionController::class, 'calculateAll'])
        ->name('api.consumptions.calculate-all');

    // ✅ Exportar datos - SOLO PREMIUM (con middleware)
    Route::get('/consumptions/export', [ConsumptionController::class, 'export'])
        ->name('api.consumptions.export')
        ->middleware('subscription.gate:export_data');

    Route::get('/consumptions/{id}', [ConsumptionController::class, 'show'])
        ->name('api.consumptions.show')
        ->where('id', '[0-9]+');

    Route::delete('/consumptions/{consumption}', [ConsumptionController::class, 'destroy'])
        ->name('api.consumptions.destroy');

    // =====================================================
    // USUARIOS
    // =====================================================
    Route::get('/users', [UserController::class, 'findByEmail'])
        ->name('api.users.index');

    // =====================================================
    // IMPORTACIÓN MASIVA DE SENSORES - SOLO PREMIUM
    // =====================================================
    Route::prefix('sensor-groups')->group(function () {
        Route::post('/analyze-file', [BulkSensorImportController::class, 'analyzeFile'])
            ->name('api.sensor-groups.analyze-file')
            ->middleware('subscription.gate:import_sensors');
        
        Route::post('/bulk-import', [BulkSensorImportController::class, 'bulkImport'])
            ->name('api.sensor-groups.bulk-import')
            ->middleware('subscription.gate:import_sensors');
        
        Route::get('/download-template', [BulkSensorImportController::class, 'downloadTemplate'])
            ->name('api.sensor-groups.download-template')
            ->middleware('subscription.gate:import_sensors');
        
        Route::get('/{groupId}/template-fields', [BulkSensorImportController::class, 'getTemplateFields'])
            ->name('api.sensor-groups.template-fields');
    });

    // =====================================================
    // MEDICIONES MASIVAS (Bulk Measurement)
    // =====================================================
    Route::prefix('bulk/measurements')->group(function () {
        Route::get('/sensors', [BulkMeasurementController::class, 'getSensorsForBulkMeasurement'])
            ->name('api.bulk.measurements.sensors');

        Route::get('/stats', [BulkMeasurementController::class, 'getMeasurementStats'])
            ->name('api.bulk.measurements.stats');

        Route::post('/sensors/{sensor}/toggle-mark', [BulkMeasurementController::class, 'toggleMarkForMeasurement'])
            ->name('api.bulk.measurements.sensors.toggle-mark')
            ->where('sensor', '[0-9]+');

        Route::post('/groups/{group}/mark-all', [BulkMeasurementController::class, 'markAllForMeasurement'])
            ->name('api.bulk.measurements.groups.mark-all')
            ->where('group', '[0-9]+');

        Route::put('/sensors/{sensor}/next-date', [BulkMeasurementController::class, 'updateNextMeasurementDate'])
            ->name('api.bulk.measurements.update-next-date')
            ->where('sensor', '[0-9]+');

        Route::post('/toggle-all-marked', [BulkMeasurementController::class, 'toggleAllMarked'])
            ->name('api.bulk.measurements.toggle-all-marked');

        Route::get('/next-sensor', [BulkMeasurementController::class, 'getNextSensorForBulkMeasurement'])
            ->name('api.bulk.measurements.next-sensor');

        Route::post('/store', [BulkMeasurementController::class, 'store'])
            ->name('api.bulk.measurements.store');
    });

    // =====================================================
    // IMPORTACIÓN MASIVA DE MEDICIONES
    // =====================================================
    Route::prefix('measurements/bulk-import')->group(function () {
        Route::get('/groups', [BulkMeasurementImportController::class, 'getGroups'])
            ->name('api.measurements.bulk-import.groups');
        
        Route::get('/groups/{groupId}/sensors', [BulkMeasurementImportController::class, 'getSensorsByGroup'])
            ->name('api.measurements.bulk-import.sensors');
        
        Route::post('/analyze-file', [BulkMeasurementImportController::class, 'analyzeFile'])
            ->name('api.measurements.bulk-import.analyze');
        
        Route::post('/import', [BulkMeasurementImportController::class, 'bulkImport'])
            ->name('api.measurements.bulk-import.import');
        
        Route::get('/download-template', [BulkMeasurementImportController::class, 'downloadTemplate'])
            ->name('api.measurements.bulk-import.download-template');
        
        Route::get('/report', [BulkMeasurementImportController::class, 'generateReport'])
            ->name('api.measurements.bulk-import.report');
    });

    // =====================================================
    // AYUDA
    // =====================================================
    Route::get('/help', [HelpContentController::class, 'index'])
        ->name('api.help.index');

    Route::get('/help/{page}', [HelpContentController::class, 'getByPage'])
        ->name('api.help.by-page');

    Route::get('/help/key/{key}', [HelpContentController::class, 'getByKey'])
        ->name('api.help.by-key');

    // =====================================================
    // INVITACIONES (rutas sin autenticación para algunas)
    // =====================================================
    Route::post('/invitations/check', [PendingInvitationController::class, 'checkInvitations'])
        ->withoutMiddleware('auth:sanctum');

    Route::post('/invitations/{token}/use', [PendingInvitationController::class, 'useInvitation'])
        ->withoutMiddleware('auth:sanctum');

    // =====================================================
    // ⭐ COLABORACIÓN ENTRE USUARIOS (CON GESTIÓN COMPLETA)
    // =====================================================
    Route::prefix('collaborations')->group(function () {
        // Invitaciones - SOLO PREMIUM
        Route::post('/invite', [CollaborationController::class, 'invite'])
            ->middleware('subscription.gate:add_collaborator');
        
        Route::get('/list', [CollaborationController::class, 'listCollaborators']);
        Route::get('/pending', [CollaborationController::class, 'listPendingInvitations']);
        
        // Aceptar/Rechazar
        Route::post('/accept/{token}', [CollaborationController::class, 'acceptInvitation'])->name('collaborations.accept');
        Route::post('/reject/{token}', [CollaborationController::class, 'rejectInvitation'])->name('collaborations.reject');
        
        // ✅ GESTIÓN DE COLABORADORES (NUEVO) - SOLO PREMIUM
        Route::delete('/{id}', [CollaborationController::class, 'removeCollaborator'])
            ->middleware('subscription.gate:add_collaborator');
        Route::post('/{id}/pause', [CollaborationController::class, 'pauseCollaborator'])
            ->middleware('subscription.gate:add_collaborator');
        Route::post('/{id}/unpause', [CollaborationController::class, 'unpauseCollaborator'])
            ->middleware('subscription.gate:add_collaborator');
        Route::put('/{id}/role', [CollaborationController::class, 'changeRole'])
            ->middleware('subscription.gate:add_collaborator');
        Route::get('/activity', [CollaborationController::class, 'getActivityLog']);
    });

    // =====================================================
    // AUTENTICACIÓN CON GOOGLE (API)
    // =====================================================
    Route::prefix('auth/google')->group(function () {
        Route::get('/redirect', [GoogleController::class, 'redirectToGoogle'])
            ->name('api.auth.google.redirect');

        Route::get('/callback', [GoogleController::class, 'handleGoogleCallback'])
            ->name('api.auth.google.callback');

        Route::get('/url', [GoogleController::class, 'getRedirectUrl'])
            ->name('api.auth.google.url');

        Route::post('/callback', [GoogleController::class, 'handleApiCallback'])
            ->name('api.auth.google.callback.api');
    });

    // =====================================================
    // PERFIL DE USUARIO
    // =====================================================
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('api.profile.show');
        Route::put('/', [ProfileController::class, 'update'])->name('api.profile.update');
        Route::put('/subscription', [ProfileController::class, 'updateSubscription'])->name('api.profile.subscription');
        Route::get('/stats', [ProfileController::class, 'getStats'])->name('api.profile.stats');
    });

    // =====================================================
    // TOKENS DE ACCESO TEMPORAL
    // =====================================================
    Route::prefix('access-tokens')->group(function () {
        Route::post('/generate', [AccessTokenController::class, 'generate']);
        Route::get('/list', [AccessTokenController::class, 'list']);
        Route::delete('/{id}', [AccessTokenController::class, 'revoke']);
    });

    // =====================================================
    // ✅ SUSCRIPCIÓN - PLANES Y ESTADO (NUEVO)
    // =====================================================
    Route::prefix('subscription/plan')->group(function () {
        Route::get('/status', [SubscriptionPlanController::class, 'status']);
        Route::get('/available', [SubscriptionPlanController::class, 'availablePlans']);
        Route::get('/access-state', [SubscriptionPlanController::class, 'accessState']);
        Route::get('/can-create-sensor', [SubscriptionPlanController::class, 'canCreateSensor']);
        Route::get('/can-create-group', [SubscriptionPlanController::class, 'canCreateGroup']);
        Route::get('/can-add-collaborator', [SubscriptionPlanController::class, 'canAddCollaborator']);
    });

}); // ✅ CIERRE DEL GRUPO auth:sanctum

// =====================================================
// RUTAS PÚBLICAS PARA MEDICIONES CON TOKEN
// =====================================================
Route::get('/mediciones/public/{token}', [MeasurementController::class, 'publicMeasurementForm'])
    ->middleware('check.token.access');
Route::post('/mediciones/public/{token}', [MeasurementController::class, 'storePublicMeasurement'])
    ->middleware('check.token.access');

// ✅ Rutas de depuración (solo local)
if (app()->environment('local')) {
    Route::middleware('auth:sanctum')->prefix('subscription/debug')->group(function () {
        Route::post('/activate', [SubscriptionPaymentController::class, 'debugActivate']);
        Route::post('/expire', [SubscriptionPaymentController::class, 'debugExpire']);
        Route::post('/clear', [SubscriptionPaymentController::class, 'debugClear']);
    });
}