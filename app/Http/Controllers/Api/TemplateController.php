<?php

namespace App\Http\Controllers\Api;

use App\Models\Template;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    /**
     * Listar todas las plantillas (públicas y del usuario)
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // ✅ Obtener plantillas por defecto
            $defaultTemplates = Template::where('is_default', true)
                ->orWhereNull('created_by')
                ->orderBy('name')
                ->get();

            // ✅ Obtener plantillas personalizadas del usuario
            $userTemplates = Template::where('created_by', $user->id)
                ->where('is_default', false)
                ->orderBy('name')
                ->get();

            // ✅ NORMALIZAR - Convertir a array y modificar la copia
            $normalizedDefaults = $defaultTemplates->map(function ($template) {
                // Crear una copia del template como array
                $templateArray = $template->toArray();

                // Obtener los campos normalizados
                $normalizedFields = $template->getNormalizedFields();

                // Reemplazar el schema en la copia
                $templateArray['schema'] = [
                    'campos' => $normalizedFields
                ];

                // Agregar campos adicionales para la vista
                $templateArray['type_label'] = $template->getTypeLabel();
                $templateArray['main_unit'] = $template->getMainUnit();

                return $templateArray;
            });

            $normalizedUser = $userTemplates->map(function ($template) {
                $templateArray = $template->toArray();
                $normalizedFields = $template->getNormalizedFields();

                $templateArray['schema'] = [
                    'campos' => $normalizedFields
                ];

                $templateArray['type_label'] = $template->getTypeLabel();
                $templateArray['main_unit'] = $template->getMainUnit();
                $templateArray['in_use'] = $template->sensorGroups()->whereHas('sensors')->exists();

                return $templateArray;
            });

            return response()->json([
                'success' => true,
                'message' => 'Plantillas obtenidas correctamente',
                'data' => [
                    'default' => $normalizedDefaults->values(),
                    'custom' => $normalizedUser->values(),
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al cargar plantillas: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar plantillas: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Obtener los campos de una plantilla (incluyendo herencia)
     */
    public function getFields(Template $template)
    {
        $user = request()->user();

        $canAccess = $template->is_default ||
            $template->created_by === $user->id ||
            $template->created_by === null;

        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a esta plantilla',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Campos de la plantilla obtenidos correctamente',
            'data' => [
                'fields' => $template->getFields(),
                'full_schema' => $template->getFullSchema(),
                'parent_id' => $template->parent_template_id,
                'is_default' => $template->is_default,
                'required_fields' => $template->getRequiredFields(),
                'default_values' => $template->getDefaultValues()
            ]
        ]);
    }

    /**
     * Mostrar una plantilla específica
     */
    public function show(Template $template)
    {
        $user = request()->user();

        $canAccess = $template->is_default ||
            $template->created_by === $user->id ||
            $template->created_by === null;

        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a esta plantilla',
            ], 403);
        }

        $template->fields = $template->getFields();
        $template->full_schema = $template->getFullSchema();

        return response()->json([
            'success' => true,
            'message' => 'Plantilla obtenida correctamente',
            'data' => $template,
        ]);
    }

    /**
     * Crear una nueva plantilla personalizada
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:templates,name',
            'description' => 'nullable|string',
            'type' => 'required|string|in:agua,gas,electricidad,temperatura,presion,caudal,luz,personalizado',
            'schema' => 'required|array',
            'schema.campos' => 'required|array|min:2', // ✅ Mínimo 2 campos (principal + foto)
            'schema.campos.*.nombre' => 'required|string|distinct',
            'schema.campos.*.tipo' => 'required|string|in:numero,texto,fecha,booleano,string',
            'schema.campos.*.unidad' => 'nullable|string',
            'schema.campos.*.requerido' => 'boolean',
            'schema.campos.*.valor_por_defecto' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // ✅ Validar que tenga un campo principal (puede ser consumo_m3, energia_kwh, etc.)
        $campos = $request->schema['campos'];
        $hasMainField = false;
        $hasPhotoField = false;

        foreach ($campos as $field) {
            // Verificar campo principal (debe ser tipo número y requerido)
            if ($field['tipo'] === 'numero' && isset($field['requerido']) && $field['requerido'] === true) {
                // Si el nombre no es 'valor', es un campo principal descriptivo
                if ($field['nombre'] !== 'valor') {
                    $hasMainField = true;
                }
            }
            // Verificar campo foto
            if (isset($field['es_foto']) && $field['es_foto'] === true) {
                $hasPhotoField = true;
            }
            // Si el campo se llama 'foto', también es válido
            if ($field['nombre'] === 'foto') {
                $hasPhotoField = true;
            }
        }

        // ✅ Si no tiene campo principal descriptivo, pero tiene 'valor', lo aceptamos
        foreach ($campos as $field) {
            if ($field['nombre'] === 'valor' && $field['tipo'] === 'numero' && $field['requerido'] === true) {
                $hasMainField = true;
                break;
            }
        }

        // ❌ Si no tiene campo principal, error
        if (!$hasMainField) {
            return response()->json([
                'success' => false,
                'message' => 'La plantilla debe incluir un campo principal de tipo número y requerido (ej: consumo_m3, energia_kwh, temperatura_c, etc.)',
            ], 400);
        }

        // ❌ Si no tiene campo foto, error
        if (!$hasPhotoField) {
            return response()->json([
                'success' => false,
                'message' => 'La plantilla debe incluir un campo "foto" de tipo string y requerido',
            ], 400);
        }

        // Si se especifica un parent_template_id, verificar que exista
        $parentId = $request->input('parent_template_id');
        if ($parentId) {
            $parent = Template::find($parentId);
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'La plantilla padre no existe',
                ], 400);
            }
            if (!$parent->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se puede heredar de plantillas por defecto',
                ], 400);
            }
        }

        $template = Template::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'schema' => $request->schema,
            'is_default' => false,
            'created_by' => $user->id,
            'parent_template_id' => $parentId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plantilla creada correctamente',
            'data' => $template,
        ], 201);
    }

    /**
     * Actualizar una plantilla personalizada
     */
    public function update(Request $request, Template $template)
    {
        $user = request()->user();

        if ($template->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar esta plantilla',
            ], 403);
        }

        if ($template->sensorGroups()->whereHas('sensors')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes modificar esta plantilla porque ya está asignada a uno o más Grupos que contienen sensores. Por favor, crea una plantilla nueva si deseas cambiar la estructura, o elimina primero los sensores de los grupos.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:templates,name,' . $template->id,
            'description' => 'nullable|string',
            'type' => 'sometimes|string|in:agua,gas,electricidad,temperatura,presion,caudal,luz,personalizado',
            'schema' => 'sometimes|array',
            'schema.campos' => 'required_with:schema|array|min:2',
            'schema.campos.*.nombre' => 'required|string|distinct',
            'schema.campos.*.tipo' => 'required|string|in:numero,texto,fecha,booleano,string',
            'schema.campos.*.unidad' => 'nullable|string',
            'schema.campos.*.requerido' => 'boolean',
            'schema.campos.*.valor_por_defecto' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Si tiene padre, no se puede modificar el schema directamente
        if ($template->parent_template_id && $request->has('schema')) {
            return response()->json([
                'success' => false,
                'message' => 'Las plantillas que heredan de una plantilla por defecto no pueden modificar el schema directamente. Los campos vienen de la plantilla padre.',
            ], 400);
        }

        $template->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Plantilla actualizada correctamente',
            'data' => $template,
        ]);
    }

    /**
     * Eliminar una plantilla personalizada
     */
    public function destroy(Template $template)
    {
        $user = request()->user();

        if ($template->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar esta plantilla',
            ], 403);
        }

        if ($template->sensorGroups()->whereHas('sensors')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar esta plantilla porque está siendo usada por uno o más Grupos que contienen sensores',
            ], 400);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plantilla eliminada correctamente',
        ]);
    }

    /**
     * Obtener campos predefinidos para un tipo de plantilla
     */
    public function getPredefinedFields(Request $request)
    {
        $type = $request->input('type');

        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'El parámetro "type" es requerido'
            ], 400);
        }

        // ✅ Mapeo de campos predefinidos con nombres descriptivos
        $predefinedFields = [
            'agua' => [
                ['nombre' => 'presion_bar', 'tipo' => 'numero', 'unidad' => 'bar', 'requerido' => false],
                ['nombre' => 'temperatura_c', 'tipo' => 'numero', 'unidad' => '°C', 'requerido' => false]
            ],
            'gas' => [
                ['nombre' => 'presion_bar', 'tipo' => 'numero', 'unidad' => 'bar', 'requerido' => false],
                ['nombre' => 'temperatura_c', 'tipo' => 'numero', 'unidad' => '°C', 'requerido' => false]
            ],
            'electricidad' => [
                ['nombre' => 'voltaje_v', 'tipo' => 'numero', 'unidad' => 'V', 'requerido' => false],
                ['nombre' => 'corriente_a', 'tipo' => 'numero', 'unidad' => 'A', 'requerido' => false],
                ['nombre' => 'factor_potencia', 'tipo' => 'numero', 'unidad' => '', 'requerido' => false]
            ],
            'temperatura' => [
                ['nombre' => 'humedad', 'tipo' => 'numero', 'unidad' => '%', 'requerido' => false]
            ],
            'presion' => [
                ['nombre' => 'temperatura_c', 'tipo' => 'numero', 'unidad' => '°C', 'requerido' => false]
            ],
            'caudal' => [
                ['nombre' => 'presion_bar', 'tipo' => 'numero', 'unidad' => 'bar', 'requerido' => false]
            ],
            'luz' => [
                ['nombre' => 'temperatura_color', 'tipo' => 'numero', 'unidad' => 'K', 'requerido' => false]
            ],
            'personalizado' => []
        ];

        // ✅ Mapeo del campo principal según el tipo
        $mainFieldMapping = [
            'agua' => ['nombre' => 'consumo_m3', 'unidad' => 'm³'],
            'gas' => ['nombre' => 'consumo_m3', 'unidad' => 'm³'],
            'electricidad' => ['nombre' => 'energia_kwh', 'unidad' => 'kWh'],
            'temperatura' => ['nombre' => 'temperatura_c', 'unidad' => '°C'],
            'presion' => ['nombre' => 'presion_bar', 'unidad' => 'bar'],
            'caudal' => ['nombre' => 'caudal_lmin', 'unidad' => 'L/min'],
            'luz' => ['nombre' => 'iluminacion_lux', 'unidad' => 'lux'],
            'personalizado' => ['nombre' => 'medicion', 'unidad' => '']
        ];

        $mainField = $mainFieldMapping[$type] ?? ['nombre' => 'medicion', 'unidad' => ''];
        $fields = $predefinedFields[$type] ?? [];

        return response()->json([
            'success' => true,
            'message' => 'Campos predefinidos obtenidos correctamente',
            'data' => [
                'main_field' => $mainField,
                'fields' => $fields,
                'type' => $type
            ]
        ]);
    }
}