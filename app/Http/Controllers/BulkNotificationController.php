<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorGroup;
use App\Models\Sensor;
use App\Jobs\SendPublicVisorEmailJob;
use Illuminate\Support\Facades\Log;

class BulkNotificationController extends Controller
{
    /**
     * Devuelve la vista principal del sistema de campañas de comunicación masiva
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $subService = app(\App\Services\Subscription\SubscriptionService::class, ['user' => $user]);
        if ($subService->getPlan()->getPlanKey() !== 'premium') {
            return redirect()->route('dashboard')->with('error', 'Las Campañas Públicas son exclusivas del plan Premium.');
        }

        // Extraer los grupos que el usuario puede administrar
        $groups = SensorGroup::where('user_id', $user->id)
            ->orWhereHas('sharedAccess', function ($q) use ($user) {
                $q->where('shared_with', $user->id)
                    ->whereIn('role', ['admin']);
            })
            ->withCount('sensors')
            ->orderBy('name')
            ->get();

        return view('notifications.bulk.index', compact('groups'));
    }

    /**
     * Procesa y encola la distribución de correos del Visor Público
     */
    public function dispatchCampaign(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'group_id' => 'required|exists:sensor_groups,id',
            'message_body' => 'nullable|string|max:1000',
            'email_field' => 'required|string',
        ]);

        $subService = app(\App\Services\Subscription\SubscriptionService::class, ['user' => $user]);
        if ($subService->getPlan()->getPlanKey() !== 'premium') {
            return response()->json([
                'success' => false,
                'message' => 'Necesitas una suscripción Premium activa para realizar esta acción.'
            ], 403);
        }

        $group = SensorGroup::findOrFail($request->group_id);

        // Control de permisos estrictos sobre el grupo
        if ($group->user_id !== $user->id && !$group->sharedAccess()->where('shared_with', $user->id)->where('role', 'admin')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos de administrador sobre este grupo.'
            ], 403);
        }

        $sensors = $group->sensors;

        if ($sensors->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'El grupo seleccionado no tiene dependencias/lotes cargados.'
            ], 400);
        }

        $enqueuedCount = 0;
        $ignoredCount = 0;

        $isLocal = app()->environment('local');
        $emailField = $request->email_field; // The dynamic field chosen by the admin

        foreach ($sensors as $sensor) {
            $destEmail = null;
            if (isset($sensor->metadata) && is_array($sensor->metadata)) {
                if (isset($sensor->metadata[$emailField]) && !empty($sensor->metadata[$emailField])) {
                    $destEmail = $sensor->metadata[$emailField];
                }
            }

            if ($destEmail) {
                if ($isLocal) {
                    if ($enqueuedCount < 2) {
                        SendPublicVisorEmailJob::dispatch($sensor, $request->message_body, 'scastellano10@gmail.com');
                        $enqueuedCount++;
                    } else {
                        $ignoredCount++;
                    }
                } else {
                    SendPublicVisorEmailJob::dispatch($sensor, $request->message_body, $destEmail);
                    $enqueuedCount++;
                }
            } else {
                $ignoredCount++;
            }
        }

        // Si es entorno de producción, la cuenta de encolados es real
        $msg = "Campaña procesada. $enqueuedCount correos puestos en cola de envío automático de forma escalonada. ($ignoredCount omitidos o sin dirección de correo)";
        if ($isLocal) {
            $msg = "⚠️ [DEV TRAP] Modo desarrollo activo. Se ignoró y canceló el correo para " . ($sensors->count() - $enqueuedCount) . " sensores reales. Se enviaron únicamente $enqueuedCount correos de prueba hacia scastellano10@gmail.com y el resto fue salteado para proteger tu base de usuarios.";
        }

        Log::info("Dispatching bulk notification: {$msg}", ['user_id' => $user->id, 'group_id' => $group->id]);

        return response()->json([
            'success' => true,
            'message' => $msg,
            'enqueued' => $enqueuedCount,
            'ignored' => $ignoredCount
        ]);
    }

    /**
     * Devuelve el esquema JSON de la plantilla de un Grupo de Sensores para llenar dropdowns Ajax
     */
    public function getGroupSchema(Request $request, $id)
    {
        $group = SensorGroup::with('template')->find($id);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Grupo no encontrado'], 404);
        }

        $fields = $group->template->schema['campos'] ?? [];

        // Filtramos solo los campos estáticos/contexto de sensor y preferentemente tipo texto, 
        // aunque devolver todos es más seguro y dejamos a la vista discriminar.
        return response()->json([
            'success' => true,
            'fields' => $fields
        ]);
    }

    /**
     * Exporta un listado plano (CSV) con todos los enlaces de acceso público para imprimir
     */
    public function exportLinks(Request $request, $id)
    {
        $user = $request->user();

        $subService = app(\App\Services\Subscription\SubscriptionService::class, ['user' => $user]);
        if ($subService->getPlan()->getPlanKey() !== 'premium') {
            abort(403, 'Requiere plan Premium');
        }

        $group = SensorGroup::with('sensors')->find($id);

        if (!$group) {
            abort(404, 'Grupo no encontrado');
        }

        // Control de permisos estrictos sobre el grupo
        if ($group->user_id !== $user->id && !$group->sharedAccess()->where('shared_with', $user->id)->where('role', 'admin')->exists()) {
            abort(403, 'No tienes permisos administrativos sobre este grupo.');
        }

        if ($request->wantsJson()) {
            $data = [];
            foreach ($group->sensors as $sensor) {
                if (empty($sensor->public_token)) {
                    $sensor->public_token = \Illuminate\Support\Str::random(32);
                    $sensor->save();
                }
                $data[] = [
                    'id' => $sensor->id,
                    'name' => $sensor->name,
                    'url' => route('public.visor', ['token' => $sensor->public_token])
                ];
            }
            return response()->json(['success' => true, 'data' => $data]);
        }

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=enlacesPublicos_{$group->name}.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID Interno', 'Medidor (Nombre)', 'Enlace Unico de Acceso (QR)'];

        $callback = function () use ($group, $columns) {
            $file = fopen('php://output', 'w');

            // Forzar BOM para UTF-8 Excel compatibilidad
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($group->sensors as $sensor) {
                // Ensure a token exists
                if (empty($sensor->public_token)) {
                    $sensor->public_token = \Illuminate\Support\Str::random(32);
                    $sensor->save();
                }

                $url = route('public.visor', ['token' => $sensor->public_token]);
                fputcsv($file, [$sensor->id, $sensor->name, $url]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
