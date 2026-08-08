<?php
// app/Http/Controllers/Api/SubscriptionPlanController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Subscription\SubscriptionService;
use App\Services\Subscription\Plans\PlanFactory;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function status(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            $service = new SubscriptionService($user);
            $status = $service->getFullStatus();
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            \Log::error('❌ Error en SubscriptionPlanController@status: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado de la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    public function availablePlans(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => PlanFactory::getAvailablePlans()
        ]);
    }

    public function canCreateSensor(Request $request)
    {
        $user = $request->user();
        $service = new SubscriptionService($user);

        return response()->json([
            'success' => true,
            'data' => [
                'can_create' => $service->canCreateSensor(),
                'limit_status' => $service->getLimitStatus()['sensors']
            ]
        ]);
    }

    public function canCreateGroup(Request $request)
    {
        $user = $request->user();
        $service = new SubscriptionService($user);

        return response()->json([
            'success' => true,
            'data' => [
                'can_create' => $service->canCreateGroup(),
                'limit_status' => $service->getLimitStatus()['groups']
            ]
        ]);
    }

    public function canAddCollaborator(Request $request)
    {
        $user = $request->user();
        $service = new SubscriptionService($user);

        return response()->json([
            'success' => true,
            'data' => [
                'can_add' => $service->canAddCollaborator(),
                'limit_status' => $service->getLimitStatus()['collaborators']
            ]
        ]);
    }

    /**
     * Obtener estado de acceso a todas las puertas
     */
    public function accessState(Request $request)
    {
        $user = $request->user();
        $gateService = new SubscriptionGate($user);

        return response()->json([
            'success' => true,
            'data' => $gateService->getAccessState()
        ]);
    }
}