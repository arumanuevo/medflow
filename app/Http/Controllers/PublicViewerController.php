<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PublicViewerController extends Controller
{
    /**
     * Show the public analytics dashboard for a specific sensor.
     *
     * @param  string  $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($token)
    {
        $sensor = Sensor::where('public_token', $token)->first();

        if (!$sensor) {
            abort(404, 'Enlace no válido o expirado.');
        }

        // Obtener el período (por defecto últimos 30 días o todo desde la primer medición)
        $measurements = $sensor->measurements()->orderBy('measured_at', 'asc')->get();

        if ($measurements->isEmpty()) {
            return view('public.visor', [
                'sensor' => $sensor,
                'hasData' => false
            ]);
        }

        // Lógica de cálculo matemático reutilizada para consumo y gráfico (similar a ConsumptionController)
        $chartData = [];
        $totalConsumption = 0;
        $previousValue = null;
        $previousDate = null;
        $previousDailyRate = null;

        // Defaults parameters para anomalías (Threshold de 50%, stagnation de 15 días)
        $thresholdMultiplier = 0.50;
        $stagnationThresh = 15;

        // Determinar unidad a partir de la plantilla
        $unit = 'unidades';
        if ($sensor->group && $sensor->group->template) {
            $unitField = collect($sensor->group->template->fields)->where('name', 'unidad_medida')->first();
            if ($unitField) {
                $unit = $unitField['default_value'] ?? 'Unidades';
            }
        }

        foreach ($measurements as $m) {
            $val = 0;
            // Intentar extraer el valor numérico
            $fieldsToTry = ['valor', 'consumo_m3', 'consumo', 'value', 'medicion'];
            foreach ($fieldsToTry as $f) {
                if (isset($m->data[$f])) {
                    $val = (float) $m->data[$f];
                    break;
                }
            }
            if ($val == 0) {
                foreach ($m->data as $k => $v) {
                    if (is_numeric($v)) {
                        $val = (float) $v;
                        break;
                    }
                }
            }

            $currentDate = Carbon::parse($m->measured_at);
            $isAnomaly = false;
            $anomalyDesc = null;

            if ($previousValue !== null && $previousDate !== null) {
                // Determine days diff precisely
                $hoursDiff = $previousDate->diffInHours($currentDate);
                $daysDiff = $hoursDiff > 0 ? ($hoursDiff / 24) : 1;
                $deltaRaw = $val - $previousValue;
                $currentDailyRate = $deltaRaw / $daysDiff;

                // 1. Estancamiento (Stagnation)
                if ($deltaRaw == 0 && $daysDiff > $stagnationThresh) {
                    $isAnomaly = true;
                    $anomalyDesc = "Medidor estancado. Sin variaciones de volumen registradas tras " . round($daysDiff) . " días.";
                }
                // 2. Variación inusual de tasa diaria (Salto o Caída brusca)
                elseif ($previousDailyRate !== null && $previousDailyRate > 0) {
                    $rateChangePercentage = (($currentDailyRate - $previousDailyRate) / $previousDailyRate) * 100;
                    if (abs($rateChangePercentage) > ($thresholdMultiplier * 100)) {
                        $isAnomaly = true;
                        if ($rateChangePercentage > 0) {
                            $anomalyDesc = "Aceleración de consumo. El volumen diario trepó un " . round($rateChangePercentage) . "% respecto al promedio previo.";
                        } else {
                            $anomalyDesc = "Caída de tasa abrupta. El volumen diario cayó fuertemente un " . round(abs($rateChangePercentage)) . "% respecto al periodo anterior.";
                        }
                    }
                }

                $previousDailyRate = $currentDailyRate;
                $totalConsumption += $deltaRaw;
            }

            // Normalizar URL de la foto para internet
            $photoPath = $m->data['foto'] ?? null;
            if ($photoPath && $photoPath !== 'Sin Foto') {
                if (!Str::startsWith($photoPath, 'http')) {
                    $photoPath = url($photoPath);
                }
            } else {
                $photoPath = null;
            }

            $chartData[] = [
                'id' => $m->id,
                'date' => $currentDate->format('d/m/Y H:i'),
                'value' => $val,
                'anomaly' => $isAnomaly,
                'anomaly_desc' => $anomalyDesc,
                'photo' => $photoPath
            ];

            $previousValue = $val;
            $previousDate = $currentDate;
        }

        // Estadísticas secundarias
        $startVal = $chartData[0]['value'] ?? 0;
        $endVal = $chartData[count($chartData) - 1]['value'] ?? 0;
        $totalDelta = $endVal - $startVal;

        $firstDate = Carbon::createFromFormat('d/m/Y H:i', $chartData[0]['date']);
        $lastDate = Carbon::createFromFormat('d/m/Y H:i', $chartData[count($chartData) - 1]['date']);
        // Remove trailing decimals for days
        $daysBetween = (float) max(1, $firstDate->diffInHours($lastDate) / 24);
        $daysBetween = round($daysBetween);

        $dailyAverage = round($totalDelta / max(1, $daysBetween), 2);

        $anomaliesCount = collect($chartData)->where('anomaly', true)->count();

        // 🌿 Prorrateo Comunitario
        $communityContribution = 0;
        if (!$sensor->is_community && $sensor->group) {
            $communitySensors = \App\Models\Sensor::where('group_id', $sensor->group_id)->where('is_community', true)->get();
            $privateSensorsCount = \App\Models\Sensor::where('group_id', $sensor->group_id)->where('is_community', false)->count();

            if ($privateSensorsCount > 0 && $communitySensors->count() > 0) {
                $totalCommunityDelta = 0;
                foreach ($communitySensors as $cSensor) {
                    $isProrated = ($cSensor->metadata['prorratear_comunidad'] ?? '1') == '1';
                    if (!$isProrated)
                        continue;
                    $cStart = \App\Models\Measurement::where('sensor_id', $cSensor->id)
                        ->where('measured_at', '>=', $firstDate)
                        ->orderBy('measured_at', 'asc')->first();
                    $cEnd = \App\Models\Measurement::where('sensor_id', $cSensor->id)
                        ->where('measured_at', '<=', $lastDate)
                        ->orderBy('measured_at', 'desc')->first();

                    if ($cStart && $cEnd && $cStart->measured_at < $cEnd->measured_at) {
                        $cStartVal = (float) ($cStart->data['valor'] ?? 0);
                        $cEndVal = (float) ($cEnd->data['valor'] ?? 0);
                        $totalCommunityDelta += round(max(0, $cEndVal - $cStartVal), 2);
                    }
                }
                $communityContribution = round($totalCommunityDelta / $privateSensorsCount, 2);
            }
        }

        $finalBilledTotal = $totalDelta + $communityContribution;

        $financialCost = null;
        $currency = null;
        if ($sensor->group && !empty($sensor->group->billing_settings) && ($sensor->group->billing_settings['is_enabled'] ?? false)) {
            $billing = $sensor->group->billing_settings;
            // Only calc if allowed in public viewer
            if (($billing['show_in_public_viewer'] ?? 1) == 1 || ($billing['show_in_public_viewer'] ?? '1') == 'true') {
                $fixedCharge = (float) ($billing['fixed_charge'] ?? 0);
                $pricePerUnit = (float) ($billing['price_per_unit'] ?? 0);
                $financialCost = round($fixedCharge + ($finalBilledTotal * $pricePerUnit), 2);
                $currency = $billing['currency'] ?? 'ARS';
            }
        }

        return view('public.visor', compact(
            'sensor',
            'chartData',
            'totalDelta',
            'dailyAverage',
            'daysBetween',
            'firstDate',
            'lastDate',
            'unit',
            'anomaliesCount',
            'communityContribution',
            'finalBilledTotal',
            'financialCost',
            'currency'
        ));
    }
}
