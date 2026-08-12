<?php

namespace App\Services;

use App\Models\Measurement;
use App\Models\Sensor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
     * Verificar si el usuario puede acceder a un sensor
     */
    public function canAccessSensor($user, $sensor)
    {
        if (!$sensor || !$sensor->group) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($sensor->group->user_id === $user->id) {
            return true;
        }

        $sharedAccess = $sensor->group->sharedAccess()
            ->where('shared_with', $user->id)
            ->whereIn('role', ['inspector', 'admin'])
            ->exists();

        return $sharedAccess;
    }

    /**
     * Calcula las estadísticas de error para un usuario (propietario o con acceso compartido)
     */
    public function calculateErrorStats($user)
    {
        $allMeasurements = Measurement::with(['sensor', 'sensor.group', 'sensor.group.template'])
            ->whereHas('sensor.group', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('sharedAccess', function ($q2) use ($user) {
                        $q2->where('shared_with', $user->id)
                            ->whereIn('role', ['inspector', 'admin']);
                    });
            })
            ->orderBy('sensor_id')
            ->orderBy('measured_at', 'asc')
            ->get();

        $errorStats = [
            'negative_consumption' => 0,
            'inconsistent_date' => 0,
            'first_measurement' => 0,
            'valid' => 0
        ];

        $allMeasurementsBySensor = $allMeasurements->groupBy('sensor_id');

        foreach ($allMeasurementsBySensor as $sensorId => $sensorMeasurements) {
            $sensor = $sensorMeasurements->first()->sensor;
            $mainField = $this->getMainFieldFromSensor($sensor);

            $sortedMeasurements = $sensorMeasurements->sortBy('measured_at');

            foreach ($sortedMeasurements as $index => $measurement) {
                $previousMeasurement = ($index > 0) ? $sortedMeasurements->get($index - 1) : null;

                if (!$previousMeasurement) {
                    $errorStats['first_measurement']++;
                    continue;
                }

                $lastDate = Carbon::parse($previousMeasurement->measured_at);
                $currentDate = Carbon::parse($measurement->measured_at);

                if ($currentDate->lt($lastDate)) {
                    $errorStats['inconsistent_date']++;
                    continue;
                }

                $lastValue = $previousMeasurement->data[$mainField] ?? 0;
                $currentValue = $measurement->data[$mainField] ?? 0;
                $consumption = $currentValue - $lastValue;

                if ($consumption < 0) {
                    $errorStats['negative_consumption']++;
                } else {
                    $errorStats['valid']++;
                }
            }
        }

        return $errorStats;
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
                return "Consumo negativo ($diff detectado). El valor actual ($currentValue) es menor que el anterior ($previousValue).";
            default:
                return "Error desconocido en la medición.";
        }
    }
    /**
     * Construye la consulta base para mediciones, aplicando filtros y permisos
     */
    public function buildMeasurementQuery($user, $filters, $sortField = 'measured_at', $sortDirection = 'desc')
    {
        $query = Measurement::with(['sensor', 'sensor.group', 'sensor.group.template'])
            ->whereHas('sensor.group', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('sharedAccess', function ($q2) use ($user) {
                        $q2->where('shared_with', $user->id)
                            ->whereIn('role', ['inspector', 'admin']);
                    });
            });

        if (!empty($filters['sensor_id'])) {
            $query->where('sensor_id', $filters['sensor_id']);
        }

        if (!empty($filters['group_id'])) {
            $groupId = $filters['group_id'];
            $query->whereHas('sensor.group', function ($q) use ($groupId) {
                $q->where('id', $groupId);
            });
        }

        if (!empty($filters['date_from'])) {
            $query->where('measured_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('measured_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search, $user) {
                $q->whereHas('sensor', function ($q2) use ($search, $user) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('identifier', 'like', "%{$search}%")
                        ->orWhere('metadata', 'like', "%{$search}%");
                })
                    ->orWhere('data->valor', 'like', "%{$search}%")
                    ->orWhere('data->tipo', 'like', "%{$search}%");
            });
        }

        if ($sortField === 'sensor') {
            $query->orderBy('sensor_id', $sortDirection)->orderBy('measured_at', 'desc');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query;
    }
}
