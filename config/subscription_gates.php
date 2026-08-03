<?php
// config/subscription_gates.php

return [
    /*
    |--------------------------------------------------------------------------
    | Definición de Puertas de Acceso por Plan
    |--------------------------------------------------------------------------
    | Cada clave es un "gate" (puerta) que representa una funcionalidad.
    | Los valores son los planes que pueden acceder a esa funcionalidad.
    |
    | Planes disponibles: 'free', 'basico', 'premium'
    */
    
    'gates' => [
        // =============================================
        // SENSORES
        // =============================================
        'view_sensors' => ['free', 'basico', 'premium'],
        'create_sensor' => ['free', 'basico', 'premium'],
        'edit_sensor' => ['free', 'basico', 'premium'],
        'delete_sensor' => ['free', 'basico', 'premium'],
        
        // ✅ Importación de sensores - SOLO PREMIUM
        'import_sensors' => ['premium'],
        
        // =============================================
        // GRUPOS
        // =============================================
        'view_groups' => ['free', 'basico', 'premium'],
        'create_group' => ['free', 'basico', 'premium'],
        'edit_group' => ['free', 'basico', 'premium'],
        'delete_group' => ['free', 'basico', 'premium'],
        
        // =============================================
        // MEDICIONES
        // =============================================
        'view_measurements' => ['free', 'basico', 'premium'],
        'create_measurement' => ['free', 'basico', 'premium'],
        'edit_measurement' => ['free', 'basico', 'premium'],
        'delete_measurement' => ['free', 'basico', 'premium'],
        
        // =============================================
        // PLANTILLAS
        // =============================================
        'view_templates' => ['free', 'basico', 'premium'],
        'create_template' => ['premium'], // ✅ SOLO PREMIUM
        'edit_template' => ['premium'],   // ✅ SOLO PREMIUM
        'delete_template' => ['premium'], // ✅ SOLO PREMIUM
        
        // =============================================
        // COLABORADORES
        // =============================================
        'view_collaborators' => ['free', 'basico', 'premium'],
        'add_collaborator' => ['premium'], // ✅ SOLO PREMIUM
        'remove_collaborator' => ['premium'], // ✅ SOLO PREMIUM
        
        // =============================================
        // EXPORTACIÓN
        // =============================================
        'export_data' => ['premium'], // ✅ SOLO PREMIUM
        
        // =============================================
        // ANÁLISIS
        // =============================================
        'view_analytics' => ['premium'], // ✅ SOLO PREMIUM
        
        // =============================================
        // ADMINISTRACIÓN
        // =============================================
        'admin_access' => ['premium'], // ✅ SOLO PREMIUM
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Mensajes de error personalizados
    |--------------------------------------------------------------------------
    */
    'messages' => [
        'default' => 'Tu plan actual no tiene acceso a esta funcionalidad.',
        'import_sensors' => 'La importación masiva de sensores es una funcionalidad exclusiva para usuarios Premium. ' .
                            'Activa tu suscripción Premium para acceder.',
        'create_template' => 'Crear plantillas personalizadas es una funcionalidad exclusiva para usuarios Premium. ' .
                             'Activa tu suscripción Premium para acceder.',
        'add_collaborator' => 'Agregar colaboradores es una funcionalidad exclusiva para usuarios Premium. ' .
                              'Activa tu suscripción Premium para acceder.',
        'export_data' => 'Exportar datos es una funcionalidad exclusiva para usuarios Premium. ' .
                         'Activa tu suscripción Premium para acceder.',
        'view_analytics' => 'Los análisis avanzados son una funcionalidad exclusiva para usuarios Premium. ' .
                            'Activa tu suscripción Premium para acceder.',
    ],
];