<?php

return [
    'public_key' => env('MERCADO_PAGO_PUBLIC_KEY'),
    'access_token' => env('MERCADO_PAGO_ACCESS_TOKEN'),
    'plans' => [
        'basico' => [
            'name' => 'Plan Básico',
            'price' => env('MERCADO_PAGO_PLAN_BASICO', 1000), // $10.00 ARS en centavos
            'currency' => 'ARS',
            'description' => 'Acceso a funcionalidades básicas de MedFlow'
        ],
        'premium' => [
            'name' => 'Plan Premium',
            'price' => 2500, // $25.00 ARS
            'currency' => 'ARS',
            'description' => 'Acceso a todas las funcionalidades de MedFlow'
        ]
    ]
];