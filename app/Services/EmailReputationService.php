<?php

namespace App\Services;

use App\Models\EmailSuppression;
use App\Models\CampaignDispatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmailReputationService
{
    /**
     * Asegura la existencia de las tablas necesarias sin depender de migraciones manuales.
     */
    public static function ensureTablesExist(): void
    {
        try {
            DB::statement("
                CREATE TABLE IF NOT EXISTS `email_suppressions` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `email` varchar(191) NOT NULL,
                    `reason` varchar(50) NOT NULL DEFAULT 'bounce',
                    `sub_type` varchar(50) DEFAULT NULL,
                    `workspace_id` bigint unsigned DEFAULT NULL,
                    `source` varchar(50) DEFAULT 'sns_webhook',
                    `details` text,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `email_suppressions_email_unique` (`email`),
                    KEY `email_suppressions_workspace_id_index` (`workspace_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            DB::statement("
                CREATE TABLE IF NOT EXISTS `campaign_dispatches` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `workspace_id` bigint unsigned NOT NULL,
                    `group_id` bigint unsigned NOT NULL,
                    `user_id` bigint unsigned NOT NULL,
                    `total_sensors` int NOT NULL DEFAULT '0',
                    `enqueued_count` int NOT NULL DEFAULT '0',
                    `skipped_empty` int NOT NULL DEFAULT '0',
                    `skipped_suppressed` int NOT NULL DEFAULT '0',
                    `skipped_invalid` int NOT NULL DEFAULT '0',
                    `bounces_count` int NOT NULL DEFAULT '0',
                    `complaints_count` int NOT NULL DEFAULT '0',
                    `status` varchar(50) NOT NULL DEFAULT 'dispatched',
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `campaign_dispatches_workspace_id_index` (`workspace_id`),
                    KEY `campaign_dispatches_group_id_index` (`group_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (\Throwable $e) {
            Log::warning('EmailReputationService::ensureTablesExist falló: ' . $e->getMessage());
        }
    }

    /**
     * Verifica si una dirección de email está suprimida (en lista negra de rebotes/quejas).
     */
    public static function isSuppressed(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        $cleanEmail = strtolower(trim($email));

        try {
            return EmailSuppression::where('email', $cleanEmail)->exists();
        } catch (\Throwable $e) {
            self::ensureTablesExist();
            try {
                return EmailSuppression::where('email', $cleanEmail)->exists();
            } catch (\Throwable $ex2) {
                return false;
            }
        }
    }

    /**
     * Registra un email en la lista de supresión.
     */
    public static function addSuppression(
        string $email,
        string $reason = 'bounce',
        ?string $subType = null,
        ?int $workspaceId = null,
        string $source = 'sns_webhook',
        $details = null
    ): void {
        $cleanEmail = strtolower(trim($email));
        if (empty($cleanEmail)) {
            return;
        }

        self::ensureTablesExist();

        try {
            EmailSuppression::updateOrCreate(
                ['email' => $cleanEmail],
                [
                    'reason' => $reason,
                    'sub_type' => $subType,
                    'workspace_id' => $workspaceId,
                    'source' => $source,
                    'details' => is_array($details) ? json_encode($details) : (string)$details,
                    'updated_at' => now(),
                ]
            );

            // Si es un rebote o queja con workspace asignado, incrementamos en la última campaña
            if ($workspaceId) {
                $lastDispatch = CampaignDispatch::where('workspace_id', $workspaceId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($lastDispatch) {
                    if ($reason === 'complaint') {
                        $lastDispatch->increment('complaints_count');
                        $lastDispatch->update(['status' => 'warning']);
                    } elseif ($reason === 'bounce') {
                        $lastDispatch->increment('bounces_count');
                    }
                }
            }

            Log::warning("EmailReputationService: Email suprimido [{$cleanEmail}] Razón: {$reason} ({$subType}) Workspace: {$workspaceId}");
        } catch (\Throwable $e) {
            Log::error('EmailReputationService::addSuppression error: ' . $e->getMessage());
        }
    }

    /**
     * Valida sintaxis, dominios prohibidos/falsos y registros MX antes de enviar.
     */
    public static function validateEmailQuality(string $email): array
    {
        $clean = strtolower(trim($email));

        if (!filter_var($clean, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'reason' => 'Formato de correo inválido'];
        }

        // Extraer dominio
        $parts = explode('@', $clean);
        $domain = $parts[1] ?? '';

        // Bloqueo de dominios de prueba/falsos comunes usados por descuido
        $fakeDomains = [
            'test.com', 'example.com', 'sample.com', 'correo.com', 'email.com',
            'mail.com', 'asdasd.com', 'fake.com', 'algo.com', 'nadie.com', 'falso.com'
        ];

        if (in_array($domain, $fakeDomains, true)) {
            return ['valid' => false, 'reason' => "Dominio no permitido para envíos ({$domain})"];
        }

        // Verificar si ya está en lista de supresión
        if (self::isSuppressed($clean)) {
            return ['valid' => false, 'reason' => 'Correo suprimido por rebote previo o reporte de spam'];
        }

        // Validación DNS MX si la función está disponible y habilitada en el host
        if (function_exists('checkdnsrr') && app()->environment('production')) {
            try {
                if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
                    return ['valid' => false, 'reason' => "El dominio {$domain} no posee servidores de correo (MX) válidos"];
                }
            } catch (\Throwable $dnsEx) {
                // Si el lookup DNS falla por timeout de red del server, permitimos continuar
            }
        }

        return ['valid' => true, 'reason' => null];
    }

    /**
     * Evalúa si un workspace y grupo están habilitados para disparar una campaña masiva.
     * Controla:
     * 1. Cooldown de 24 hs por grupo (evita reenvíos masivos repetitivos).
     * 2. Tasa de rebotes y quejas del workspace (evita suspensión en Amazon SES).
     */
    public static function checkWorkspaceCampaignEligibility(int $workspaceId, int $groupId): array
    {
        self::ensureTablesExist();

        try {
            // 1. Cooldown por Grupo (24 horas)
            $lastGroupDispatch = CampaignDispatch::where('group_id', $groupId)
                ->where('created_at', '>=', now()->subHours(24))
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastGroupDispatch && !app()->environment('local')) {
                $availableAt = $lastGroupDispatch->created_at->addHours(24);
                $hoursLeft = ceil(now()->diffInMinutes($availableAt) / 60);
                return [
                    'allowed' => false,
                    'error_code' => 'COOLDOWN_ACTIVE',
                    'message' => "Este grupo ya recibió un despacho masivo recientemente. Por políticas anti-spam y protección de la reputación SMTP, debes esperar {$hoursLeft} hora(s) (hasta {$availableAt->format('d/m H:i')}) para volver a notificar a este mismo grupo.",
                    'available_at' => $availableAt->toIso8601String(),
                ];
            }

            // 2. Reputación del Workspace: revisar últimas 5 campañas
            $recentDispatches = CampaignDispatch::where('workspace_id', $workspaceId)
                ->where('created_at', '>=', now()->subDays(30))
                ->get();

            $totalSent = $recentDispatches->sum('enqueued_count');
            $totalBounces = $recentDispatches->sum('bounces_count');
            $totalComplaints = $recentDispatches->sum('complaints_count');

            // Si hay quejas de spam recientes (> 0), se bloquea para auditoría
            if ($totalComplaints > 0) {
                return [
                    'allowed' => false,
                    'error_code' => 'COMPLAINT_LOCK',
                    'message' => 'Los envíos masivos para tu cuenta se encuentran temporalmente en pausa preventiva debido a reportes de spam recibidos. Por favor contacta a soporte técnico para validar tu lista de destinatarios.',
                ];
            }

            // Si la tasa de rebotes supera el 5% con un volumen mínimo de 20 correos
            if ($totalSent >= 20 && ($totalBounces / $totalSent) > 0.05) {
                $bouncePct = round(($totalBounces / $totalSent) * 100, 1);
                return [
                    'allowed' => false,
                    'error_code' => 'BOUNCE_RATE_HIGH',
                    'message' => "La tasa de rebotes de tus envíos masivos alcanzó el {$bouncePct}% (máximo permitido por Amazon SES: 5%). Revisa y corrige los correos inexistentes antes de lanzar nuevas campañas masivas.",
                ];
            }

            return ['allowed' => true];
        } catch (\Throwable $e) {
            Log::warning('EmailReputationService::checkWorkspaceCampaignEligibility error: ' . $e->getMessage());
            return ['allowed' => true]; // Fallback permisivo si falla la consulta
        }
    }

    /**
     * Registra un despacho de campaña masiva.
     */
    public static function recordDispatch(
        int $workspaceId,
        int $groupId,
        int $userId,
        array $stats
    ): ?CampaignDispatch {
        self::ensureTablesExist();

        try {
            return CampaignDispatch::create([
                'workspace_id' => $workspaceId,
                'group_id' => $groupId,
                'user_id' => $userId,
                'total_sensors' => $stats['total'] ?? 0,
                'enqueued_count' => $stats['enqueued'] ?? 0,
                'skipped_empty' => $stats['skipped_empty'] ?? 0,
                'skipped_suppressed' => $stats['skipped_suppressed'] ?? 0,
                'skipped_invalid' => $stats['skipped_invalid'] ?? 0,
                'status' => 'dispatched',
            ]);
        } catch (\Throwable $e) {
            Log::error('EmailReputationService::recordDispatch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Devuelve el estado de reputación y estadísticas de un workspace para la interfaz.
     */
    public static function getWorkspaceReputationSummary(int $workspaceId): array
    {
        self::ensureTablesExist();

        try {
            $dispatches = CampaignDispatch::where('workspace_id', $workspaceId)
                ->where('created_at', '>=', now()->subDays(30))
                ->get();

            $totalSent = $dispatches->sum('enqueued_count');
            $totalBounces = $dispatches->sum('bounces_count');
            $totalComplaints = $dispatches->sum('complaints_count');

            $bounceRate = $totalSent > 0 ? round(($totalBounces / $totalSent) * 100, 1) : 0.0;

            $status = 'optima';
            $statusLabel = 'Reputación Óptima (100% segura)';
            $badgeClass = 'bg-success';

            if ($totalComplaints > 0) {
                $status = 'bloqueada';
                $statusLabel = 'Bloqueo preventivo por reporte de spam';
                $badgeClass = 'bg-danger';
            } elseif ($bounceRate > 5.0 && $totalSent >= 20) {
                $status = 'en_riesgo';
                $statusLabel = "Atención: Tasa de rebotes alta ({$bounceRate}%)";
                $badgeClass = 'bg-warning text-dark';
            } elseif ($bounceRate > 2.0) {
                $status = 'vigilada';
                $statusLabel = "Tasa de rebotes moderada ({$bounceRate}%)";
                $badgeClass = 'bg-info text-dark';
            }

            return [
                'status' => $status,
                'status_label' => $statusLabel,
                'badge_class' => $badgeClass,
                'total_sent_30d' => $totalSent,
                'bounces_30d' => $totalBounces,
                'complaints_30d' => $totalComplaints,
                'bounce_rate' => $bounceRate,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'optima',
                'status_label' => 'Reputación Óptima',
                'badge_class' => 'bg-success',
                'total_sent_30d' => 0,
                'bounces_30d' => 0,
                'complaints_30d' => 0,
                'bounce_rate' => 0.0,
            ];
        }
    }
}
