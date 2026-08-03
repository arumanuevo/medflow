<?php
namespace App\Http\Controllers\Api;

use App\Models\HelpContent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HelpContentController extends Controller
{
    /**
     * Obtener contenido de ayuda por página
     */
    public function getByPage(Request $request, $page)
{
    try {
        $helpContents = HelpContent::where('target_page', $page)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Depuración: Loguear los resultados
        \Log::info('Contenido de ayuda para la página ' . $page, [
            'count' => $helpContents->count(),
            'data' => $helpContents->toArray()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contenido de ayuda obtenido correctamente',
            'data' => $helpContents
        ]);
    } catch (\Exception $e) {
        \Log::error('Error al obtener contenido de ayuda: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener contenido de ayuda: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Obtener contenido de ayuda por clave
     */
    public function getByKey(Request $request, $key)
    {
        try {
            $helpContent = HelpContent::where('key', $key)
                ->where('is_active', true)
                ->first();

            if (!$helpContent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contenido de ayuda no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Contenido de ayuda obtenido correctamente',
                'data' => $helpContent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener contenido de ayuda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todo el contenido de ayuda
     */
    public function index(Request $request)
    {
        try {
            $helpContents = HelpContent::where('is_active', true)
                ->orderBy('target_page')
                ->orderBy('order')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Contenido de ayuda obtenido correctamente',
                'data' => $helpContents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener contenido de ayuda: ' . $e->getMessage()
            ], 500);
        }
    }
}