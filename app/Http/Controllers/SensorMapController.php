<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorGroup;
use App\Models\User;
use App\Models\WorkspaceCollaborator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SensorMapController extends Controller
{
    /**
     * Verifica acceso al módulo Premium de Mapa GIS y resuelve los grupos accesibles en el espacio activo.
     */
    private function resolveWorkspaceAccess(Request $request): array
    {
        $user = $request->user();
        $activeWorkspace = session('active_workspace', $user->id);
        $isOwner = (int) $activeWorkspace === (int) $user->id;

        $workspaceOwner = $isOwner ? $user : User::find($activeWorkspace);
        if (!$workspaceOwner) {
            return ['allowed' => false, 'reason' => 'Espacio de trabajo no encontrado.'];
        }

        // Validar que el propietario del espacio tenga plan Premium (o sea SuperAdmin)
        $subService = app(\App\Services\Subscription\SubscriptionService::class, ['user' => $workspaceOwner]);
        $isSuperAdmin = $user->email === 'scastellanoadmin@gmail.com';

        if (!$isSuperAdmin && $subService->getPlan()->getPlanKey() !== 'premium') {
            return [
                'allowed' => false,
                'reason' => 'El Mapa Interactivo de Sensores (GIS) es una aplicación exclusiva del Plan Premium.'
            ];
        }

        if (!$isOwner) {
            $collaboration = WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('is_paused', false)
                ->whereIn('role', ['admin', 'inspector'])
                ->first();

            if (!$collaboration) {
                return ['allowed' => false, 'reason' => 'No tienes permisos para acceder al mapa de este espacio.'];
            }
        }

        $groups = SensorGroup::where(function ($q) use ($activeWorkspace, $user, $isOwner) {
            if ($isOwner) {
                $q->where('user_id', $activeWorkspace)
                    ->orWhereHas('sharedAccess', function ($q2) use ($user) {
                        $q2->where('shared_with', $user->id)
                            ->whereIn('role', ['admin', 'inspector']);
                    });
            } else {
                $q->where('user_id', $activeWorkspace);
            }
        })
            ->with('template')
            ->withCount('sensors')
            ->orderBy('name')
            ->get();

        return [
            'allowed' => true,
            'user' => $user,
            'activeWorkspace' => $activeWorkspace,
            'isOwner' => $isOwner,
            'groups' => $groups,
        ];
    }

    /**
     * Extrae latitud y longitud normalizadas desde el atributo coordinates del sensor.
     */
    private function extractLatLng($coordinates): array
    {
        if (is_string($coordinates)) {
            $decoded = json_decode($coordinates, true);
            if (is_array($decoded)) {
                $coordinates = $decoded;
            }
        }

        if (!is_array($coordinates)) {
            return ['lat' => null, 'lng' => null];
        }

        $lat = $coordinates['lat'] ?? $coordinates['latitude'] ?? $coordinates[0] ?? null;
        $lng = $coordinates['lng'] ?? $coordinates['lon'] ?? $coordinates['longitude'] ?? $coordinates[1] ?? null;

        if ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng)) {
            $latFloat = (float) $lat;
            $lngFloat = (float) $lng;
            if ($latFloat >= -90 && $latFloat <= 90 && $lngFloat >= -180 && $lngFloat <= 180) {
                return ['lat' => $latFloat, 'lng' => $lngFloat];
            }
        }

        return ['lat' => null, 'lng' => null];
    }

    /**
     * Vista principal del Mapa Interactivo de Sensores (Exclusivo Premium).
     */
    public function index(Request $request)
    {
        $access = $this->resolveWorkspaceAccess($request);
        if (!$access['allowed']) {
            return redirect()->route('dashboard')->with('error', $access['reason']);
        }

        $groups = $access['groups'];
        $groupIds = $groups->pluck('id')->toArray();

        $sensors = Sensor::whereIn('group_id', $groupIds)
            ->with(['group.template', 'lastMeasurement'])
            ->orderBy('name')
            ->get();

        $sensorsData = [];
        $locatedCount = 0;

        foreach ($sensors as $sensor) {
            $coords = $this->extractLatLng($sensor->coordinates);
            $hasCoords = $coords['lat'] !== null && $coords['lng'] !== null;
            if ($hasCoords) {
                $locatedCount++;
            }

            $templateType = $sensor->group && $sensor->group->template
                ? ($sensor->group->template->type ?? 'personalizado')
                : 'personalizado';

            $lastMeasurement = $sensor->lastMeasurement;
            $lastValue = null;
            $lastDate = null;

            if ($lastMeasurement) {
                $lastValue = $lastMeasurement->value;
                $lastDate = $lastMeasurement->measured_at
                    ? $lastMeasurement->measured_at->format('d/m/Y H:i')
                    : $lastMeasurement->created_at->format('d/m/Y H:i');
            }

            $sensorsData[] = [
                'id' => $sensor->id,
                'name' => $sensor->name,
                'identifier' => $sensor->identifier,
                'description' => $sensor->description ?? '',
                'group_id' => $sensor->group_id,
                'group_name' => $sensor->group ? $sensor->group->name : 'Sin grupo',
                'template_type' => $templateType,
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
                'has_coords' => $hasCoords,
                'last_value' => $lastValue,
                'last_measured_at' => $lastDate,
                'show_url' => url('/sensors/' . $sensor->id),
                'edit_url' => url('/sensors/' . $sensor->id . '/edit'),
                'measure_url' => url('/mediciones/create/' . $sensor->id),
            ];
        }

        $totalCount = count($sensorsData);
        $unlocatedCount = max(0, $totalCount - $locatedCount);

        $stats = [
            'total' => $totalCount,
            'located' => $locatedCount,
            'unlocated' => $unlocatedCount,
            'coverage_percent' => $totalCount > 0 ? round(($locatedCount / $totalCount) * 100) : 0,
        ];

        return view('sensor-map.index', compact('groups', 'sensorsData', 'stats'));
    }

    /**
     * Guarda o actualiza las coordenadas (lat, lng) de un sensor vía AJAX.
     */
    public function updateCoordinates(Request $request, Sensor $sensor)
    {
        $access = $this->resolveWorkspaceAccess($request);
        if (!$access['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $access['reason']
            ], 403);
        }

        $allowedGroupIds = $access['groups']->pluck('id')->toArray();
        if (!in_array($sensor->group_id, $allowedGroupIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar este sensor.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Coordenadas inválidas. Latitud debe estar entre -90 y 90, y Longitud entre -180 y 180.',
                'errors' => $validator->errors()
            ], 422);
        }

        $lat = round((float) $request->input('lat'), 7);
        $lng = round((float) $request->input('lng'), 7);

        $sensor->coordinates = [
            'lat' => $lat,
            'lng' => $lng,
        ];
        $sensor->save();

        return response()->json([
            'success' => true,
            'message' => "Coordenadas guardadas para \"{$sensor->name}\".",
            'data' => [
                'id' => $sensor->id,
                'lat' => $lat,
                'lng' => $lng,
                'has_coords' => true,
            ]
        ]);
    }

    /**
     * Elimina las coordenadas asignadas a un sensor vía AJAX.
     */
    public function clearCoordinates(Request $request, Sensor $sensor)
    {
        $access = $this->resolveWorkspaceAccess($request);
        if (!$access['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $access['reason']
            ], 403);
        }

        $allowedGroupIds = $access['groups']->pluck('id')->toArray();
        if (!in_array($sensor->group_id, $allowedGroupIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar este sensor.'
            ], 403);
        }

        $sensor->coordinates = null;
        $sensor->save();

        return response()->json([
            'success' => true,
            'message' => "Se quitó la ubicación en el mapa de \"{$sensor->name}\".",
            'data' => [
                'id' => $sensor->id,
                'lat' => null,
                'lng' => null,
                'has_coords' => false,
            ]
        ]);
    }
}
