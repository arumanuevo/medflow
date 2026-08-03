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
        $user = $request->user();
        $service = new SubscriptionService($user);

        return response()->json([
            'success' => true,
            'data' => $service->getFullStatus()
        ]);
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