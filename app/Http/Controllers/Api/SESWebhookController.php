<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\EmailReputationService;
use App\Models\Sensor;

class SESWebhookController extends Controller
{
    /**
     * Endpoint receptor de notificaciones SNS de Amazon SES (Rebotes, Quejas y Confirmaciones).
     */
    public function handle(Request $request)
    {
        $rawPayload = $request->getContent();
        $data = json_decode($rawPayload, true);

        if (!$data && $request->isJson()) {
            $data = $request->all();
        }

        if (empty($data) || !isset($data['Type'])) {
            return response()->json(['status' => 'ignored', 'message' => 'No SNS payload detected'], 200);
        }

        $messageType = $data['Type'] ?? $request->header('x-amz-sns-message-type');

        // 1. Confirmación automática de suscripción SNS
        if ($messageType === 'SubscriptionConfirmation') {
            $subscribeUrl = $data['SubscribeURL'] ?? null;
            if ($subscribeUrl) {
                try {
                    $response = Http::timeout(10)->get($subscribeUrl);
                    Log::info("SESWebhook: Suscripción SNS confirmada automáticamente para Topic: " . ($data['TopicArn'] ?? 'N/A'));
                    return response()->json(['status' => 'subscription_confirmed'], 200);
                } catch (\Throwable $e) {
                    Log::error("SESWebhook: Error confirmando suscripción SNS: " . $e->getMessage());
                    return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
                }
            }
        }

        // 2. Procesamiento de Notificaciones de Rebotes y Quejas
        if ($messageType === 'Notification') {
            $messageContent = $data['Message'] ?? null;
            if (is_string($messageContent)) {
                $messageData = json_decode($messageContent, true);
            } else {
                $messageData = $messageContent;
            }

            if (!is_array($messageData)) {
                return response()->json(['status' => 'ignored', 'message' => 'Invalid notification message'], 200);
            }

            $notificationType = $messageData['notificationType'] ?? ($messageData['eventType'] ?? null);

            // A) REBOTES (Bounces)
            if ($notificationType === 'Bounce') {
                $bounce = $messageData['bounce'] ?? [];
                $bounceType = $bounce['bounceType'] ?? 'Undetermined'; // Permanent, Transient, etc.
                $recipients = $bounce['bouncedRecipients'] ?? [];

                foreach ($recipients as $recipient) {
                    $email = $recipient['emailAddress'] ?? null;
                    if ($email) {
                        // En rebotes permanentes (Hard Bounce) o indeterminados suprimimos de inmediato
                        if ($bounceType === 'Permanent' || $bounceType === 'Undetermined') {
                            EmailReputationService::addSuppression(
                                $email,
                                'bounce',
                                $bounceType,
                                null,
                                'sns_webhook',
                                $recipient
                            );
                        } else {
                            Log::info("SESWebhook: Rebote transitorio recibido para {$email} (Tipo: {$bounceType}). No se suprime aún.");
                        }
                    }
                }

                return response()->json(['status' => 'bounce_processed'], 200);
            }

            // B) QUEJAS DE SPAM (Complaints)
            if ($notificationType === 'Complaint') {
                $complaint = $messageData['complaint'] ?? [];
                $complaintFeedbackType = $complaint['complaintFeedbackType'] ?? 'abuse';
                $recipients = $complaint['complainedRecipients'] ?? [];

                foreach ($recipients as $recipient) {
                    $email = $recipient['emailAddress'] ?? null;
                    if ($email) {
                        EmailReputationService::addSuppression(
                            $email,
                            'complaint',
                            $complaintFeedbackType,
                            null,
                            'sns_webhook',
                            $recipient
                        );
                    }
                }

                return response()->json(['status' => 'complaint_processed'], 200);
            }
        }

        return response()->json(['status' => 'acknowledged'], 200);
    }

    /**
     * Permite a un residente / propietario desuscribirse de notificaciones por email
     * con un clic seguro, evitando que reporte el correo como spam a su proveedor.
     */
    public function unsubscribe(Request $request, $token)
    {
        $sensor = Sensor::where('public_token', $token)->first();

        $email = $request->query('email');

        if ($email) {
            EmailReputationService::addSuppression(
                $email,
                'unsubscribe',
                'UserOptOut',
                $sensor ? $sensor->user_id : null,
                'user_action',
                ['sensor_id' => $sensor ? $sensor->id : null]
            );
        }

        return view('emails.unsubscribed', [
            'email' => $email,
            'sensorName' => $sensor ? $sensor->name : null,
        ]);
    }
}
