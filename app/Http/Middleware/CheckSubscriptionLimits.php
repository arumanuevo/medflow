<?php
// app/Http/Middleware/CheckSubscriptionLimits.php

namespace App\Http\Middleware;

use Closure;
use App\Services\Subscription\SubscriptionService;
use App\Services\Subscription\Exceptions\LimitExceededException;
use Illuminate\Http\Request;

class CheckSubscriptionLimits
{
    /**
     * Verificar límites de suscripción
     * 
     * @param string|null $action 'create_sensor' | 'create_group' | 'add_collaborator' | 'create_template'
     * @param string|null $requiredPlan 'free' | 'basico' | 'premium'
     */
    public function handle(Request $request, Closure $next, $action = null, $requiredPlan = null)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión.'
            ], 401);
        }

        try {
            $subscriptionService = new SubscriptionService($user);

            // ✅ Si se requiere un plan específico
            if ($requiredPlan) {
                $currentPlan = $subscriptionService->getPlan()->getPlanKey();
                
                // Mapeo de planes: premium > basico > free
                $planHierarchy = ['free' => 0, 'basico' => 1, 'premium' => 2];
                
                if (($planHierarchy[$currentPlan] ?? 0) < ($planHierarchy[$requiredPlan] ?? 0)) {
                    return $this->errorResponse(
                        "El plan {$subscriptionService->getPlan()->getPlanName()} no tiene acceso a esta funcionalidad. " .
                        "Actualiza a {$requiredPlan} para acceder.",
                        'plan_insufficient',
                        ['current_plan' => $currentPlan, 'required_plan' => $requiredPlan]
                    );
                }
            }

            // ✅ Verificar acción específica
            if ($action) {
                $canPerform = match ($action) {
                    'create_sensor' => $subscriptionService->canCreateSensor(),
                    'create_group' => $subscriptionService->canCreateGroup(),
                    'add_collaborator' => $subscriptionService->canAddCollaborator(),
                    'create_template' => $subscriptionService->getPlan()->canCreateCustomTemplates(),
                    'export_data' => $subscriptionService->getPlan()->canExportData(),
                    'view_analytics' => $subscriptionService->getPlan()->canViewAnalytics(),
                    default => true
                };

                if (!$canPerform) {
                    $limitStatus = $subscriptionService->getLimitStatus();
                    $planName = $subscriptionService->getPlan()->getPlanName();
                    
                    $messages = [
                        'create_sensor' => "Has alcanzado el límite de {$limitStatus['sensors']['max']} sensores para tu plan {$planName}. " .
                            ($limitStatus['sensors']['max'] === 1 ? 'Considera actualizar a Básico o Premium.' : ''),
                        'create_group' => "Has alcanzado el límite de {$limitStatus['groups']['max']} grupos para tu plan {$planName}. " .
                            ($limitStatus['groups']['max'] === 1 ? 'Considera actualizar a Básico o Premium.' : ''),
                        'add_collaborator' => "Tu plan {$planName} no permite agregar colaboradores. " .
                            'Actualiza a Premium para colaborar con otros usuarios.',
                        'create_template' => "Tu plan {$planName} no permite crear plantillas personalizadas. " .
                            'Actualiza a Premium para crear tus propias plantillas.',
                        'export_data' => "Tu plan {$planName} no permite exportar datos. " .
                            'Actualiza a Premium para exportar tus datos.',
                        'view_analytics' => "Tu plan {$planName} no permite ver análisis avanzados. " .
                            'Actualiza a Premium para acceder a análisis detallados.',
                    ];

                    return $this->errorResponse(
                        $messages[$action] ?? 'Funcionalidad no disponible para tu plan actual.',
                        'feature_not_allowed',
                        [
                            'action' => $action,
                            'limit_status' => $limitStatus,
                            'plan' => $subscriptionService->getPlan()->getPlanKey()
                        ]
                    );
                }
            }

            return $next($request);

        } catch (LimitExceededException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode(), $e->getContext());
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al verificar los límites de la suscripción: ' . $e->getMessage(),
                'subscription_check_error'
            );
        }
    }

    private function errorResponse(string $message, string $code, array $data = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'data' => $data
        ], 403);
    }
}