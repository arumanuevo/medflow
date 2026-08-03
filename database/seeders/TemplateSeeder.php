<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'Medición de Agua',
                'type' => 'agua',
                'description' => 'Plantilla para mediciones de consumo de agua',
                'schema' => [
                    'campos' => [
                        [
                            'nombre' => 'valor',
                            'tipo' => 'numero',
                            'unidad' => 'm³',
                            'requerido' => true,
                            'valor_por_defecto' => null
                        ],
                        [
                            'nombre' => 'foto',
                            'tipo' => 'string',
                            'unidad' => null,
                            'requerido' => true,
                            'valor_por_defecto' => 'Sin Foto',
                            'es_foto' => true
                        ],
                        [
                            'nombre' => 'presion',
                            'tipo' => 'numero',
                            'unidad' => 'bar',
                            'requerido' => false,
                            'valor_por_defecto' => null
                        ]
                    ]
                ],
                'is_default' => true
            ],
            [
                'name' => 'Medición Eléctrica',
                'type' => 'electricidad',
                'description' => 'Plantilla para mediciones de consumo eléctrico',
                'schema' => [
                    'campos' => [
                        [
                            'nombre' => 'valor',
                            'tipo' => 'numero',
                            'unidad' => 'kWh',
                            'requerido' => true,
                            'valor_por_defecto' => null
                        ],
                        [
                            'nombre' => 'foto',
                            'tipo' => 'string',
                            'unidad' => null,
                            'requerido' => true,
                            'valor_por_defecto' => 'Sin Foto',
                            'es_foto' => true
                        ],
                        [
                            'nombre' => 'voltaje',
                            'tipo' => 'numero',
                            'unidad' => 'V',
                            'requerido' => false,
                            'valor_por_defecto' => null
                        ],
                        [
                            'nombre' => 'corriente',
                            'tipo' => 'numero',
                            'unidad' => 'A',
                            'requerido' => false,
                            'valor_por_defecto' => null
                        ]
                    ]
                ],
                'is_default' => true
            ],
            [
                'name' => 'Medición de Gas',
                'type' => 'gas',
                'description' => 'Plantilla para mediciones de consumo de gas',
                'schema' => [
                    'campos' => [
                        [
                            'nombre' => 'valor',
                            'tipo' => 'numero',
                            'unidad' => 'm³',
                            'requerido' => true,
                            'valor_por_defecto' => null
                        ],
                        [
                            'nombre' => 'foto',
                            'tipo' => 'string',
                            'unidad' => null,
                            'requerido' => true,
                            'valor_por_defecto' => 'Sin Foto',
                            'es_foto' => true
                        ],
                        [
                            'nombre' => 'presion',
                            'tipo' => 'numero',
                            'unidad' => 'bar',
                            'requerido' => false,
                            'valor_por_defecto' => null
                        ]
                    ]
                ],
                'is_default' => true
            ],
            // ... más plantillas
        ];

        foreach ($templates as $template) {
            Template::create($template);
        }
    }
}