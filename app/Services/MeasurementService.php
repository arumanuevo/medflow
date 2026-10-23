<?php

namespace App\Services;

use App\Models\Measurement;
use App\Models\Sensor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class MeasurementService
{
    /**
     * Obtener el campo principal de un sensor según su plantilla
     */
    public function getMainFieldFromSensor($sensor)
    {
        $mainField = 'valor';

        if ($sensor && $sensor->group && $sensor->group->template) {
            $template = $sensor->group->template;
            if (isset($template->schema['campos'])) {
                foreach ($template->schema['campos'] as $campo) {
                    if ($campo['tipo'] === 'numero' && ($campo['requerido'] ?? false)) {
                        $mainField = $campo['nombre'];
                        break;
                    }
                }
            }
        }

        return $mainField;
    }

    /**
     * Obtiene los IDs de propietarios de grupos accesibles para el usuario
     * (el propio usuario + workspaces colaborativos activos).
     */
    public function getAllowedOwnerIds($user): array
    {
        $allowedOwnerIds = [$user->id];

        try {
            if (Schema::hasTable('workspace_collaborators')) {
                $collabIds = DB::table('workspace_collaborators')
                    ->where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where('is_paused', 0)
                    ->pluck('workspace_id')
                    ->all();

                if (!empty($collabIds)) {
                    $allowedOwnerIds = array_values(array_unique(array_merge($allowedOwnerIds, $collabIds)));
                }
            }
        } catch (\Throwable $e) {
            // Silencioso: mantener al menos el propio usuario si la tabla no está disponible
        }

        return $allowedOwnerIds;
    }

    /**
     * Verificar si el usuario puede acceder a un sensor
     */
    public function canAccessSensor($user, $sensor)
    {
        if (!$sensor || !$sensor->group) {
            return false;
        }

        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            return true;
        }

        $allowedOwnerIds = $this->getAllowedOwnerIds($user);
        return in_array($sensor->group->user_id, $allowedOwnerIds);
    }

    /**
     * Calcula las estadísticas de error para un usuario de forma eficiente con caché
     */
    public function calculateErrorStats($user)
    {
        return Cache::remember("user_{$user->id}_error_stats", 30, function () use ($user) {
            $query = DB::table('measurements')
                ->join('sensors', 'sensors.id', '=', 'measurements.sensor_id')
                ->join('sensor_groups', 'sensor_groups.id', '=', 'sensors.group_id');

            if (!$user->hasRole('admin') && !$user->hasRole('superadmin')) {
                $allowedOwnerIds = $this->getAllowedOwnerIds($user);
                $query->whereIn('sensor_groups.user_id', $allowedOwnerIds);
            }

            $measurements = $query->select(
                    'measurements.id',
                    'measurements.sensor_id',
                    'measurements.measured_at',
                    'measurements.data'
                )
                ->distinct()
                ->orderBy('measurements.sensor_id')
                ->orderBy('measurements.measured_at', 'asc')
                ->get();

            $errorStats = [
                'negative_consumption' => 0,
                'inconsistent_date' => 0,
                'first_measurement' => 0,
                'valid' => 0
            ];

            $bySensor = [];
            foreach ($measurements as $m) {
                $bySensor[$m->sensor_id][] = $m;
            }

            foreach ($bySensor as $sensorId => $list) {
                $count = count($list);
                for ($i = 0; $i < $count; $i++) {
                    if ($i === 0) {
                        $errorStats['first_measurement']++;
                        continue;
                    }

                    $prev = $list[$i - 1];
                    $curr = $list[$i];

                    $prevTime = strtotime($prev->measured_at);
                    $currTime = strtotime($curr->measured_at);

                    if ($currTime < $prevTime) {
                        $errorStats['inconsistent_date']++;
                        continue;
                    }

                    $prevData = json_decode($prev->data, true);
                    $currData = json_decode($curr->data, true);

                    // Si la medición actual está marcada como cambio de medidor / reseteo, no es error
                    if (!empty($currData['is_reset'])) {
                        $errorStats['valid']++;
                        continue;
                    }

                    $prevVal = (float) ($prevData['valor'] ?? 0);
                    $currVal = (float) ($currData['valor'] ?? 0);

                    if ($currVal < $prevVal) {
                        $errorStats['negative_consumption']++;
                    } else {
                        $errorStats['valid']++;
                    }
                }
            }

            return $errorStats;
        });
    }

    /**
     * Obtener IDs de mediciones que coinciden con un tipo de error específico
     */
    public function getMeasurementIdsByErrorType($user, $errorType, $groupId = null)
    {
        $query = DB::table('measurements')
            ->join('sensors', 'sensors.id', '=', 'measurements.sensor_id')
            ->join('sensor_groups', 'sensor_groups.id', '=', 'sensors.group_id');

        if (!$user->hasRole('admin') && !$user->hasRole('superadmin')) {
            $allowedOwnerIds = $this->getAllowedOwnerIds($user);
            $query->whereIn('sensor_groups.user_id', $allowedOwnerIds);
        }

        if (!empty($groupId)) {
            $query->where('sensor_groups.id', $groupId);
        }

        $measurements = $query->select(
                'measurements.id',
                'measurements.sensor_id',
                'measurements.measured_at',
                'measurements.data'
            )
            ->distinct()
            ->orderBy('measurements.sensor_id')
            ->orderBy('measurements.measured_at', 'asc')
            ->get();

        $bySensor = [];
        foreach ($measurements as $m) {
            $bySensor[$m->sensor_id][] = $m;
        }

        $matchingIds = [];

        foreach ($bySensor as $sensorId => $list) {
            $count = count($list);
            for ($i = 0; $i < $count; $i++) {
                if ($i === 0) {
                    if ($errorType === 'first_measurement') {
                        $matchingIds[] = $list[$i]->id;
                    }
                    continue;
                }

                $prev = $list[$i - 1];
                $curr = $list[$i];

                $prevTime = strtotime($prev->measured_at);
                $currTime = strtotime($curr->measured_at);

                if ($currTime < $prevTime) {
                    if ($errorType === 'inconsistent_date' || $errorType === 'any_error') {
                        $matchingIds[] = $curr->id;
                    }
                    continue;
                }

                $prevData = json_decode($prev->data, true);
                $currData = json_decode($curr->data, true);

                // Si la medición actual está marcada como cambio de medidor / reseteo
                if (!empty($currData['is_reset'])) {
                    if ($errorType === 'valid') {
                        $matchingIds[] = $curr->id;
                    }
                    continue;
                }

                $prevVal = (float) ($prevData['valor'] ?? 0);
                $currVal = (float) ($currData['valor'] ?? 0);

                if ($currVal < $prevVal) {
                    if ($errorType === 'negative_consumption' || $errorType === 'any_error') {
                        $matchingIds[] = $curr->id;
                    }
                } elseif ($errorType === 'valid') {
                    $matchingIds[] = $curr->id;
                }
            }
        }

        return $matchingIds;
    }

    /**
     * Obtener mensaje de error personalizado.
     */
    public function getErrorMessage($errorType, $current, $previous, $mainField)
    {
        $currentDate = Carbon::parse($current->measured_at)->format('d/m/Y H:i');
        $currentValue = $current->data[$mainField] ?? 0;

        if (!$previous) {
            return "Primera medición registrada ($currentValue el $currentDate). No se puede calcular consumo.";
        }

        $previousDate = Carbon::parse($previous->measured_at)->format('d/m/Y H:i');
        $previousValue = $previous->data[$mainField] ?? 0;

        switch ($errorType) {
            case 'inconsistent_date':
                return "Fecha inconsistente: Esta medición ($currentDate) es anterior a la medición previa ($previousDate).";
            case 'negative_consumption':
                $diff = $currentValue - $previousValue;
                return "Consumo negativo ($diff detectado). La lectura actual ($currentValue el $currentDate) es menor que la anterior ($previousValue el $previousDate).";
            default:
                return "Error desconocido en la medición.";
        }
    }
    /**
     * Construye la consulta base para mediciones, aplicando filtros y permisos
     */
    public function buildMeasurementQuery($user, $filters, $sortField = 'measured_at', $sortDirection = 'desc')
    {
        $query = Measurement::with(['sensor', 'sensor.group', 'sensor.group.template']);

        if (!$user->hasRole('admin') && !$user->hasRole('superadmin')) {
            $allowedOwnerIds = $this->getAllowedOwnerIds($user);
            $query->whereHas('sensor.group', function ($q) use ($allowedOwnerIds) {
                $q->whereIn('user_id', $allowedOwnerIds);
            });
        }

        if (!empty($filters['sensor_id'])) {
            $query->where('sensor_id', $filters['sensor_id']);
        }

        if (!empty($filters['group_id'])) {
            $groupId = $filters['group_id'];
            $query->whereHas('sensor.group', function ($q) use ($groupId) {
                $q->where('id', $groupId);
            });
        }

        if (array_key_exists('is_community', $filters) && $filters['is_community'] !== '') {
            $isCommunity = filter_var($filters['is_community'], FILTER_VALIDATE_BOOLEAN);
            $query->whereHas('sensor', function ($q) use ($isCommunity) {
                $q->where('is_community', $isCommunity);
            });
        }

        if (!empty($filters['date_from'])) {
            $query->where('measured_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('measured_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        // Búsqueda por Identificador (admite múltiples separados por coma, ej: "MED-01, MED-02, 144")
        if (!empty($filters['identifier'])) {
            $rawId = $filters['identifier'];
            $idParts = array_values(array_filter(array_map('trim', explode(',', $rawId)), fn($x) => $x !== ''));
            if (count($idParts) > 1) {
                $query->whereHas('sensor', function ($q) use ($idParts) {
                    $q->where(function ($q2) use ($idParts) {
                        foreach ($idParts as $part) {
                            $q2->orWhere('identifier', 'like', "%{$part}%");
                        }
                    });
                });
            } else {
                $query->whereHas('sensor', function ($q) use ($rawId) {
                    $q->where('identifier', 'like', "%{$rawId}%");
                });
            }
        }

        // Búsqueda General (admite operadores OR con comas o 'OR', ej: "144, 178" o "Lote 14 OR Lote 15")
        if (!empty($filters['search'])) {
            $rawSearch = $filters['search'];
            $normalizedSearch = str_ireplace(' OR ', ',', $rawSearch);
            $terms = array_values(array_filter(array_map('trim', explode(',', $normalizedSearch)), fn($v) => $v !== ''));
            if (count($terms) > 1) {
                $query->where(function ($q) use ($terms) {
                    foreach ($terms as $t) {
                        $q->orWhere(function ($q2) use ($t) {
                            $q2->whereHas('sensor', function ($q3) use ($t) {
                                $q3->where('name', 'like', "%{$t}%")
                                    ->orWhere('identifier', 'like', "%{$t}%")
                                    ->orWhere('metadata', 'like', "%{$t}%");
                            })
                            ->orWhere('data->valor', 'like', "%{$t}%")
                            ->orWhere('data->tipo', 'like', "%{$t}%");
                        });
                    }
                });
            } else {
                $search = $rawSearch;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('sensor', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('identifier', 'like', "%{$search}%")
                            ->orWhere('metadata', 'like', "%{$search}%");
                    })
                        ->orWhere('data->valor', 'like', "%{$search}%")
                        ->orWhere('data->tipo', 'like', "%{$search}%");
                });
            }
        }

        // ✅ FILTRADO POTENTE EN CAMPOS DINÁMICOS / PERSONALIZADOS (METADATA)
        // Soporta AND, OR, exacto y no contiene (ej: campo "lote" con valores "144, 178")
        if (!empty($filters['custom_values'])) {
            $customField = trim($filters['custom_field'] ?? '');
            $customOp = $filters['custom_operator'] ?? 'or'; // 'or', 'and', 'exact', 'not'
            $rawCustom = $filters['custom_values'];
            $cleanCustom = str_ireplace(' OR ', ',', $rawCustom);
            $customTokens = array_values(array_filter(array_map('trim', explode(',', $cleanCustom)), fn($v) => $v !== ''));

            if (!empty($customTokens)) {
                $query->whereHas('sensor', function ($q) use ($customField, $customTokens, $customOp) {
                    if ($customOp === 'and') {
                        // Cumplir con todos los tokens buscados
                        foreach ($customTokens as $token) {
                            $q->where(function ($q2) use ($customField, $token) {
                                if (empty($customField) || $customField === 'any') {
                                    $q2->where('metadata', 'LIKE', "%{$token}%");
                                } else {
                                    $q2->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, ?)) LIKE ?", ["$.{$customField}", "%{$token}%"])
                                       ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, ?)) = ?", ["$.{$customField}", $token]);
                                }
                            });
                        }
                    } elseif ($customOp === 'not') {
                        // Excluir tokens
                        foreach ($customTokens as $token) {
                            $q->where(function ($q2) use ($customField, $token) {
                                if (empty($customField) || $customField === 'any') {
                                    $q2->where('metadata', 'NOT LIKE', "%{$token}%");
                                } else {
                                    $q2->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, ?)) NOT LIKE ?", ["$.{$customField}", "%{$token}%"])
                                       ->orWhereRaw("JSON_EXTRACT(metadata, ?) IS NULL", ["$.{$customField}"]);
                                }
                            });
                        }
                    } elseif ($customOp === 'exact') {
                        // Coincidencia exacta con alguno de los tokens
                        $q->where(function ($q2) use ($customField, $customTokens) {
                            foreach ($customTokens as $token) {
                                if (empty($customField) || $customField === 'any') {
                                    $q2->orWhereRaw("metadata LIKE ?", ["%\"{$token}\"%"]);
                                } else {
                                    $q2->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, ?)) = ?", ["$.{$customField}", $token]);
                                }
                            }
                        });
                    } else {
                        // Por defecto 'or': Coincide con cualquiera de los tokens (ej: 144 o 178)
                        $q->where(function ($q2) use ($customField, $customTokens) {
                            foreach ($customTokens as $token) {
                                if (empty($customField) || $customField === 'any') {
                                    $q2->orWhere('metadata', 'LIKE', "%{$token}%");
                                } else {
                                    $q2->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, ?)) LIKE ?", ["$.{$customField}", "%{$token}%"])
                                       ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, ?)) = ?", ["$.{$customField}", $token]);
                                }
                            }
                        });
                    }
                });
            }
        }

        // ✅ ORDENAMIENTO COMPLETO Y FLEXIBLE EN CUALQUIER COLUMNA
        $query->select('measurements.*');
        $direction = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        if ($sortField === 'sensor' || $sortField === 'sensor_name') {
            $query->join('sensors', 'sensors.id', '=', 'measurements.sensor_id')
                  ->orderBy('sensors.name', $direction)
                  ->orderBy('measurements.measured_at', 'desc');
        } elseif ($sortField === 'sensor_id') {
            $query->orderBy('measurements.sensor_id', $direction)
                  ->orderBy('measurements.measured_at', 'desc');
        } elseif ($sortField === 'identifier') {
            $query->join('sensors', 'sensors.id', '=', 'measurements.sensor_id')
                  ->orderBy('sensors.identifier', $direction)
                  ->orderBy('measurements.measured_at', 'desc');
        } elseif ($sortField === 'group' || $sortField === 'group_name') {
            $query->join('sensors', 'sensors.id', '=', 'measurements.sensor_id')
                  ->leftJoin('sensor_groups', 'sensor_groups.id', '=', 'sensors.group_id')
                  ->orderBy('sensor_groups.name', $direction)
                  ->orderBy('measurements.measured_at', 'desc');
        } elseif ($sortField === 'valor') {
            $query->orderByRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(measurements.data, '$.valor')) AS DECIMAL(14,4)) {$direction}");
        } elseif ($sortField === 'measured_at') {
            $query->orderBy('measurements.measured_at', $direction);
        } elseif (str_starts_with($sortField, 'extra:') || str_starts_with($sortField, 'dyn:')) {
            // Ordenamiento natural para campos dinámicos (números de lote ordenados numéricamente y alfanuméricamente)
            $extraKey = preg_replace('/^(extra:|dyn:)/', '', $sortField);
            $query->join('sensors', 'sensors.id', '=', 'measurements.sensor_id')
                  ->orderByRaw("(JSON_UNQUOTE(JSON_EXTRACT(sensors.metadata, '$.{$extraKey}')) + 0) {$direction}, JSON_UNQUOTE(JSON_EXTRACT(sensors.metadata, '$.{$extraKey}')) {$direction}")
                  ->orderBy('measurements.measured_at', 'desc');
        } else {
            // Fallback genérico: si coincide con columna directa o campo dinámico
            $query->join('sensors', 'sensors.id', '=', 'measurements.sensor_id')
                  ->orderByRaw("(JSON_UNQUOTE(JSON_EXTRACT(sensors.metadata, '$.{$sortField}')) + 0) {$direction}, JSON_UNQUOTE(JSON_EXTRACT(sensors.metadata, '$.{$sortField}')) {$direction}")
                  ->orderBy('measurements.measured_at', 'desc');
        }

        return $query;
    }
}
