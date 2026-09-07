<?php
$sql = "-- SQL optimizado y adaptado EXACTAMENTE al esquema de la DB

SELECT @user_id := id FROM users WHERE email = 'medflowsistemas@gmail.com' COLLATE utf8mb4_unicode_ci LIMIT 1;

-- 1. Insert Template (Adaptado a schema y created_by)
INSERT INTO templates (name, description, type, `schema`, is_default, created_by, created_at, updated_at)
VALUES (
    'Medición de Agua (Consorcios)',
    'Plantilla custom para registro de agua por consorcio.',
    'agua',
    '{\"campos\": [{\"nombre\": \"nombre_dueno\", \"tipo\": \"texto\", \"requerido\": true}, {\"nombre\": \"email_dueno\", \"tipo\": \"texto\", \"requerido\": false}, {\"nombre\": \"valor\", \"tipo\": \"numero\", \"requerido\": true, \"unidad\": \"m³\"}, {\"nombre\": \"estado_valvula\", \"tipo\": \"opciones\", \"opciones\": [\"Abierta\",\"Cerrada\",\"Fuga Detectada\"], \"requerido\": true}]}',
    0,
    @user_id,
    NOW(),
    NOW()
);

SET @template_id = LAST_INSERT_ID();

-- 2. Insert Sensor Group
INSERT INTO sensor_groups (user_id, template_id, name, description, periodo_medicion, dias_vencimiento, created_at, updated_at)
VALUES (
    @user_id,
    @template_id,
    'Torre Central - Medidores de Agua',
    'Agrupación logística de agua consorcial.',
    30,
    5,
    NOW(),
    NOW()
);

SET @group_id = LAST_INSERT_ID();

-- 3. Sensores
";

for ($i = 1; $i <= 100; $i++) {
    $p = ceil($i / 10);
    $d = sprintf('%02d', $i - (($p - 1) * 10));
    $h = 'AG-' . strtoupper(substr(md5(mt_rand()), 0, 8));

    $sql .= "INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso $p Depto $d', '$h', 0, 1, NOW(), NOW());\n";
}

file_put_contents('k:\desarrollo\medflow\wiroos.sql', $sql);
