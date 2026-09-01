<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorGroup;
use App\Models\Measurement;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $groups = SensorGroup::where('user_id', $user->id)
            ->withCount('sensors')
            ->orderBy('name')
            ->get();

        return view('backup.index', compact('groups'));
    }

    public function fetchPhotoUrls(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:sensor_groups,id'
        ]);

        $user = $request->user();
        $group = SensorGroup::where('user_id', $user->id)
            ->where('id', $request->group_id)
            ->firstOrFail();

        $sensorIds = $group->sensors()->pluck('id');

        $measurements = Measurement::whereIn('sensor_id', $sensorIds)
            ->whereNotNull('data->foto')
            ->where('data->foto', '!=', 'Sin Foto')
            ->where('data->foto', '!=', 'Expirada')
            ->select('id', 'sensor_id', 'measured_at', 'data')
            ->with(['sensor:id,name,identifier'])
            ->get();

        $urls = [];
        foreach ($measurements as $m) {
            $data = $m->data;
            if (isset($data['foto'])) {
                $fotoFile = ltrim($data['foto'], '/');
                $localPath = public_path($fotoFile);
                if (file_exists($localPath)) {
                    $fecha = \Carbon\Carbon::parse($m->measured_at)->format('Y-m-d');
                    $sensorName = \Illuminate\Support\Str::slug($m->sensor->name ?? 'sensor');

                    // The filename saved in zip
                    $exportName = "{$sensorName}_{$fecha}_v{$m->id}.jpg";

                    $urls[] = [
                        'url' => asset($fotoFile),
                        'filename' => $exportName
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'count' => count($urls),
            'files' => $urls
        ]);
    }
}
