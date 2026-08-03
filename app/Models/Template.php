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

    /**
     * Relación con el usuario que creó la plantilla.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con los grupos de sensores que usan esta plantilla.
     */
    public function sensorGroups(): HasMany
    {
        return $this->hasMany(SensorGroup::class, 'template_id');
    }

    /**
     * Relación con la plantilla padre (de la que hereda).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'parent_template_id');
    }

    /**
     * Relación con las plantillas hijas (que heredan de esta).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Template::class, 'parent_template_id');
    }

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
     * Obtener el schema completo (incluyendo herencia)
     */
    public function getFullSchema(): array
    {
        $fields = $this->getFields();
        
        // Agregar campo "valor" si no existe
        $hasValor = false;
        foreach ($fields as $field) {
            if ($field['nombre'] === 'valor') {
                $hasValor = true;
                break;
            }
        }
        
        if (!$hasValor) {
            array_unshift($fields, [
                'nombre' => 'valor',
                'tipo' => 'numero',
                'unidad' => null,
                'requerido' => true,
                'valor_por_defecto' => null
            ]);
        }
        
        return [
            'campos' => $fields
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
}