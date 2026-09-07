<?php
$sql = "-- SETTING THE USER EMAIL
SET @email_titular = 'medflowsistemas@gmail.com';

-- Encontrar el ID del usuario
SELECT @user_id := id FROM users WHERE email = @email_titular LIMIT 1;

-- 1. Insert Template
INSERT INTO templates (user_id, name, description, fields, is_global, is_active, created_at, updated_at)
VALUES (
    @user_id,
    'Medición de Agua (Consorcios)',
    'Plantilla custom para registro de agua por consorcio con información de los dueños.',
    '[{\"name\":\"nombre_dueno\",\"label\":\"Nombre del Titular\",\"type\":\"text\",\"required\":true,\"is_key\":false},{\"name\":\"email_dueno\",\"label\":\"Email del Titular\",\"type\":\"text\",\"required\":false,\"is_key\":false},{\"name\":\"lectura_m3\",\"label\":\"Lectura de Agua (m³)\",\"type\":\"number\",\"required\":true,\"is_key\":true},{\"name\":\"estado_valvula\",\"label\":\"Estado de Válvula\",\"type\":\"select\",\"options\":[\"Abierta\",\"Cerrada\",\"Fuga Detectada\"],\"required\":true,\"is_key\":false}]',
    0,
    1,
    NOW(),
    NOW()
);

-- Obtener el ID del Template generado
SET @template_id = LAST_INSERT_ID();

-- 2. Insert Sensor Group
INSERT INTO sensor_groups (user_id, template_id, name, location, description, is_active, created_at, updated_at)
VALUES (
    @user_id,
    @template_id,
    'Torre Central - Medidores de Agua',
    'Calle Principal 1234',
    'Grupo logístico masivo de los 100 medidores del ala sur para facturación hídrica.',
    1,
    NOW(),
    NOW()
);

-- Obtener el ID del grupo generado
SET @group_id = LAST_INSERT_ID();

-- 3. Insercion de Sensores (SIN Procedures para evitar bloqueos cPanel)
";

for ($i = 1; $i <= 100; $i++) {
    $p = ceil($i / 10);
    $d = sprintf('%02d', $i - (($p - 1) * 10));
    $h = 'H2O-' . strtoupper(substr(md5(mt_rand()), 0, 6));

    $sql .= "INSERT INTO sensors (group_id, name, identificador_fisico, tipo, is_active, created_at, updated_at) VALUES (@group_id, 'Medidor Agua - Piso $p Depto $d', '$h', 'Agua', 1, NOW(), NOW());\n";
}

file_put_contents('k:\desarrollo\medflow\wiroos.sql', $sql);
