<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorGroup;
use App\Models\Sensor;
use App\Jobs\SendPublicVisorEmailJob;
use App\Services\EmailReputationService;
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

        $workspaceId = session('active_workspace', $user->id);

        // Extraer los grupos que el usuario puede administrar
        $groups = SensorGroup::where('user_id', $workspaceId)
            ->orWhereHas('sharedAccess', function ($q) use ($user) {
                $q->where('shared_with', $user->id)
                    ->whereIn('role', ['admin']);
            })
            ->withCount('sensors')
            ->orderBy('name')
            ->get();

        // Resumen de reputación y salud de envíos para el usuario
        $reputation = EmailReputationService::getWorkspaceReputationSummary($workspaceId);

        return view('notifications.bulk.index', compact('groups', 'reputation'));
    }

    /**
     * Procesa y encola la distribución de correos del Visor Público
     */
    public function dispatchCampaign(Request $request)
    {
        $user = $request->user();
        $workspaceId = session('active_workspace', $user->id);

        $request->validate([
            'group_id' => 'required|exists:sensor_groups,id',
            'message_body' => 'nullable|string|max:1000',
            'email_field' => 'required|string',
            'only_new' => 'nullable|boolean',
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
        if ($group->user_id !== $workspaceId && !$group->sharedAccess()->where('shared_with', $user->id)->where('role', 'admin')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos de administrador sobre este grupo.'
            ], 403);
        }

        // 🛡️ CONTROL DE REPUTACIÓN Y COOLDOWN DE 24 HS
        $eligibility = EmailReputationService::checkWorkspaceCampaignEligibility($workspaceId, $group->id);
        if (!$eligibility['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $eligibility['message'],
                'error_code' => $eligibility['error_code'] ?? 'CAMPAIGN_BLOCKED'
            ], 422);
        }

        $sensors = $group->sensors;

        if ($sensors->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'El grupo seleccionado no tiene dependencias/lotes cargados.'
            ], 400);
        }

        $onlyNew = $request->boolean('only_new', false);
        $enqueuedCount = 0;
        $skippedEmpty = 0;
        $skippedSuppressed = 0;
        $skippedInvalid = 0;
        $skippedAlreadyTokenized = 0;
        $seenEmails = [];

        $isLocal = app()->environment('local');
        $emailField = $request->email_field; // The dynamic field chosen by the admin

        foreach ($sensors as $sensor) {
            // Si el usuario eligió "Solo nuevos", salteamos sensores que ya tenían token generado previamente
            if ($onlyNew && !empty($sensor->public_token)) {
                $skippedAlreadyTokenized++;
                continue;
            }

            $destEmail = null;
            if (isset($sensor->metadata) && is_array($sensor->metadata)) {
                if (isset($sensor->metadata[$emailField]) && !empty($sensor->metadata[$emailField])) {
                    $destEmail = trim((string)$sensor->metadata[$emailField]);
                }
            }

            if (empty($destEmail)) {
                $skippedEmpty++;
                continue;
            }

            $cleanEmail = strtolower($destEmail);

            // Evitar duplicados exactos dentro de la misma tirada de campaña
            if (isset($seenEmails[$cleanEmail])) {
                continue;
            }
            $seenEmails[$cleanEmail] = true;

            // 🛡️ VALIDACIÓN DE CALIDAD Y LISTA DE SUPRESIÓN
            $quality = EmailReputationService::validateEmailQuality($cleanEmail);
            if (!$quality['valid']) {
                if (stripos($quality['reason'], 'suprimido') !== false) {
                    $skippedSuppressed++;
                } else {
                    $skippedInvalid++;
                }
                continue;
            }

            // Encolar con retraso escalonado (pacing de 2 segundos por correo) para proteger CPU y SMTP
            $delaySeconds = $enqueuedCount * 2;

            if ($isLocal) {
                if ($enqueuedCount < 2) {
                    SendPublicVisorEmailJob::dispatch($sensor, $request->message_body, 'scastellano10@gmail.com', $workspaceId)
                        ->delay(now()->addSeconds($delaySeconds));
                    $enqueuedCount++;
                } else {
                    $skippedEmpty++;
                }
            } else {
                SendPublicVisorEmailJob::dispatch($sensor, $request->message_body, $cleanEmail, $workspaceId)
                    ->delay(now()->addSeconds($delaySeconds));
                $enqueuedCount++;
            }
        }

        // Registrar la campaña para métricas y cooldown
        EmailReputationService::recordDispatch($workspaceId, $group->id, $user->id, [
            'total' => $sensors->count(),
            'enqueued' => $enqueuedCount,
            'skipped_empty' => $skippedEmpty,
            'skipped_suppressed' => $skippedSuppressed,
            'skipped_invalid' => $skippedInvalid,
        ]);

        $totalSkipped = $skippedEmpty + $skippedSuppressed + $skippedInvalid + $skippedAlreadyTokenized;
        $approxMinutes = ceil(($enqueuedCount * 2) / 60);

        $msg = "Campaña procesada exitosamente. Se encolaron {$enqueuedCount} correos con envío dosificado (~{$approxMinutes} min).";
        if ($totalSkipped > 0) {
            $msg .= " Detalle de omitidos: {$skippedEmpty} sin email";
            if ($skippedSuppressed > 0) $msg .= ", {$skippedSuppressed} suprimidos por rebote previo";
            if ($skippedInvalid > 0) $msg .= ", {$skippedInvalid} con formato o dominio inválido";
            if ($skippedAlreadyTokenized > 0) $msg .= ", {$skippedAlreadyTokenized} ya notificados anteriormente";
            $msg .= ".";
        }

        if ($isLocal) {
            $msg = "⚠️ [DEV TRAP] Modo local activo: Se encolaron únicamente {$enqueuedCount} correos de prueba hacia scastellano10@gmail.com y el resto fue protegido.";
        }

        Log::info("Dispatching bulk notification: {$msg}", ['user_id' => $user->id, 'workspace_id' => $workspaceId, 'group_id' => $group->id]);

        return response()->json([
            'success' => true,
            'message' => $msg,
            'enqueued' => $enqueuedCount,
            'skipped_empty' => $skippedEmpty,
            'skipped_suppressed' => $skippedSuppressed,
            'skipped_invalid' => $skippedInvalid,
            'skipped_already_tokenized' => $skippedAlreadyTokenized,
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
