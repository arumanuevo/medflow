<?php
// app/Models/Template.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'schema',
        'is_default',
        'created_by',
        'parent_template_id',
    ];

    protected $casts = [
        'schema' => 'array',
        'is_default' => 'boolean',
    ];

    // ✅ Mapeo de tipos de plantilla a nombres de campo principal
    public static $mainFieldMapping = [
        'agua' => 'valor',
        'gas' => 'valor',
        'electricidad' => 'valor',
        'temperatura' => 'valor',
        'presion' => 'valor',
        'caudal' => 'valor',
        'luz' => 'valor',
        'personalizado' => 'valor',
    ];

    // ✅ Mapeo de tipos a unidades por defecto
    public static $defaultUnits = [
        'agua' => 'm³',
        'gas' => 'm³',
        'electricidad' => 'kWh',
        'temperatura' => '°C',
        'presion' => 'bar',
        'caudal' => 'L/min',
        'luz' => 'lux',
        'personalizado' => '',
    ];

    // ✅ Mapeo de tipos a nombres descriptivos
    public static $typeLabels = [
        'agua' => 'Consumo de Agua',
        'gas' => 'Consumo de Gas',
        'electricidad' => 'Consumo Eléctrico',
        'temperatura' => 'Temperatura',
        'presion' => 'Presión',
        'caudal' => 'Caudal',
        'luz' => 'Iluminación',
        'personalizado' => 'Personalizado',
    ];

    // Mapeo de tipos a iconos (Font Awesome 6 classes)
    public static $typeIcons = [
        'agua' => 'fa-solid fa-droplet',
        'gas' => 'fa-solid fa-fire-flame-curved',
        'electricidad' => 'fa-solid fa-bolt-lightning',
        'temperatura' => 'fa-solid fa-temperature-half',
        'presion' => 'fa-solid fa-gauge-high',
        'caudal' => 'fa-solid fa-water',
        'luz' => 'fa-solid fa-lightbulb',
        'personalizado' => 'fa-solid fa-gear',
    ];

    // =============================================
    // RELACIONES
    // =============================================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sensorGroups(): HasMany
    {
        return $this->hasMany(SensorGroup::class, 'template_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'parent_template_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Template::class, 'parent_template_id');
    }

    // =============================================
    // MÉTODOS DE CAMPOS
    // =============================================

    /**
     * Obtener los campos de la plantilla (incluyendo los heredados)
     */
    public function getFields(): array
    {
        // Si tiene padre, usar sus campos
        if ($this->parent_template_id && $this->parent) {
            return $this->parent->getFields();
        }
        
        // Si no tiene padre, usar sus propios campos
        return $this->schema['campos'] ?? [];
    }

    /**
     * Obtener los campos normalizados (siempre con "valor" como principal)
     */
    public function getNormalizedFields(): array
    {
        $fields = $this->getFields();
        return $this->normalizeFields($fields);
    }

    /**
     * Normalizar los campos para que el principal siempre sea "valor"
     */
    private function normalizeFields(array $fields): array
    {
        $normalized = [];
        $mainFieldFound = false;
        
        foreach ($fields as $field) {
            // Si es un campo numérico requerido (el principal)
            if (($field['tipo'] ?? '') === 'numero' && ($field['requerido'] ?? false)) {
                // Si ya encontramos un campo principal, este es adicional
                if ($mainFieldFound) {
                    // Renombrar para evitar duplicados
                    $field['nombre'] = 'campo_' . $field['nombre'];
                    $normalized[] = $field;
                } else {
                    // Este es el campo principal → lo renombramos a "valor"
                    $mainFieldFound = true;
                    $field['nombre'] = 'valor';
                    // Mantener la unidad original si existe
                    if (!isset($field['unidad']) && isset(self::$defaultUnits[$this->type])) {
                        $field['unidad'] = self::$defaultUnits[$this->type];
                    }
                    $normalized[] = $field;
                }
            } else {
                // Campos que no son el principal
                $normalized[] = $field;
            }
        }
        
        // Si no se encontró un campo principal, agregarlo
        if (!$mainFieldFound) {
            array_unshift($normalized, [
                'nombre' => 'valor',
                'tipo' => 'numero',
                'unidad' => self::$defaultUnits[$this->type] ?? '',
                'requerido' => true,
                'valor_por_defecto' => null
            ]);
        }
        
        return $normalized;
    }

    /**
     * Obtener el nombre del campo principal (siempre "valor")
     */
    public function getMainField(): string
    {
        return 'valor';
    }

    /**
     * Obtener la unidad del campo principal
     */
    public function getMainUnit(): string
    {
        $fields = $this->getFields();
        foreach ($fields as $field) {
            if ($field['nombre'] === 'valor') {
                return $field['unidad'] ?? self::$defaultUnits[$this->type] ?? '';
            }
        }
        return self::$defaultUnits[$this->type] ?? '';
    }

    /**
     * Obtener la etiqueta descriptiva del tipo
     */
    public function getTypeLabel(): string
    {
        return self::$typeLabels[$this->type] ?? $this->type;
    }

    // =============================================
    // MÉTODOS DE VALIDACIÓN
    // =============================================

    /**
     * Obtener el schema completo (incluyendo herencia)
     */
    public function getFullSchema(): array
    {
        return [
            'campos' => $this->getFields()
        ];
    }

    /**
     * Verificar si la plantilla es válida para usar
     */
    public function isValid(): bool
    {
        $fields = $this->getFields();
        
        if (empty($fields)) {
            return false;
        }
        
        $hasValor = false;
        foreach ($fields as $field) {
            if ($field['nombre'] === 'valor' && ($field['tipo'] ?? '') === 'numero') {
                $hasValor = true;
                break;
            }
        }
        
        return $hasValor;
    }

    /**
     * Obtener campos con sus valores por defecto
     */
    public function getDefaultValues(): array
    {
        $fields = $this->getFields();
        $defaults = [];
        
        foreach ($fields as $field) {
            $defaults[$field['nombre']] = $field['valor_por_defecto'] ?? null;
        }
        
        return $defaults;
    }

    /**
     * Obtener campos requeridos
     */
    public function getRequiredFields(): array
    {
        $fields = $this->getFields();
        $required = [];
        
        foreach ($fields as $field) {
            if ($field['requerido'] ?? false) {
                $required[] = $field['nombre'];
            }
        }
        
        return $required;
    }

    /**
     * Verificar si un campo existe en la plantilla
     */
    public function hasField(string $fieldName): bool
    {
        $fields = $this->getFields();
        foreach ($fields as $field) {
            if ($field['nombre'] === $fieldName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtener un campo específico por su nombre
     */
    public function getField(string $fieldName): ?array
    {
        $fields = $this->getFields();
        foreach ($fields as $field) {
            if ($field['nombre'] === $fieldName) {
                return $field;
            }
        }
        return null;
    }
}