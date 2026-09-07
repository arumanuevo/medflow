-- SQL Seeder para Mediciones (Water Sensors) en Wiroos

-- 1. Identificar al usuario y grupo logístico principal
SELECT @user_id := id FROM users WHERE email = 'medflowsistemas@gmail.com' COLLATE utf8mb4_unicode_ci LIMIT 1;

-- 2. Buscamos el ID del grupo que creamos recientemente ('Torre Central - Medidores de Agua')
SELECT @group_id := id FROM sensor_groups WHERE user_id = @user_id AND name = 'Torre Central - Medidores de Agua' ORDER BY id DESC LIMIT 1;

-- 3. Insertar Mediciones simuladas en los primeros 10 Medidores de ese grupo

-- Medición 1
SET @sensor_id_1 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 0);
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_1, 
    NOW() - INTERVAL 4 DAY, 
    NOW() + INTERVAL 25 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 101","email_dueno":"depto101@edificio.com","valor":64,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW(), 
    NOW()
);
UPDATE sensors SET ultima_medicion = NOW() - INTERVAL 3 DAY WHERE id = @sensor_id_1;

-- Medición 2
SET @sensor_id_2 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 1);
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_2, 
    NOW() - INTERVAL 3 DAY, 
    NOW() + INTERVAL 25 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 102","email_dueno":"depto102@edificio.com","valor":70,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW(), 
    NOW()
);
UPDATE sensors SET ultima_medicion = NOW() - INTERVAL 4 DAY WHERE id = @sensor_id_2;

-- Medición 3
SET @sensor_id_3 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 2);
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_3, 
    NOW() - INTERVAL 3 DAY, 
    NOW() + INTERVAL 25 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 103","email_dueno":"depto103@edificio.com","valor":38,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW(), 
    NOW()
);
UPDATE sensors SET ultima_medicion = NOW() - INTERVAL 4 DAY WHERE id = @sensor_id_3;

-- Medición 4
SET @sensor_id_4 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 3);
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_4, 
    NOW() - INTERVAL 5 DAY, 
    NOW() + INTERVAL 25 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 104","email_dueno":"depto104@edificio.com","valor":53,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW(), 
    NOW()
);
UPDATE sensors SET ultima_medicion = NOW() - INTERVAL 1 DAY WHERE id = @sensor_id_4;

-- Medición 5
SET @sensor_id_5 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 4);
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_5, 
    NOW() - INTERVAL 2 DAY, 
    NOW() + INTERVAL 25 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 105","email_dueno":"depto105@edificio.com","valor":36,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW(), 
    NOW()
);
UPDATE sensors SET ultima_medicion = NOW() - INTERVAL 3 DAY WHERE id = @sensor_id_5;

-- Medición 6
SET @sensor_id_6 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 5);
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_6, 
    NOW() - INTERVAL 2 DAY, 
    NOW() + INTERVAL 25 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 106","email_dueno":"depto106@edificio.com","valor":79,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW(), 
    NOW()
);
UPDATE sensors SET ultima_medicion = NOW() - INTERVAL 4 DAY WHERE id = @sensor_id_6;

-- Medición 7
SET @sensor_id_7 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 6);
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_7, 
    NOW() - INTERVAL 1 DAY, 
    NOW() + INTERVAL 25 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 107","email_dueno":"depto107@edificio.com","valor":62,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW(), 
    NOW()
);
UPDATE sensors SET ultima_medicion = NOW() - INTERVAL 1 DAY WHERE id = @sensor_id_7;

-- Medición 8
SET @sensor_id_8 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 7);
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_8, 
    NOW() - INTERVAL 4 DAY, 
    NOW() + INTERVAL 25 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 108","email_dueno":"depto108@edificio.com","valor":44,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW(), 
    NOW()
);
UPDATE sensors SET ultima_medicion = NOW() - INTERVAL 1 DAY WHERE id = @sensor_id_8;

-- Medición 9
SET @sensor_id_9 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 8);
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_9, 
    NOW() - INTERVAL 1 DAY, 
    NOW() + INTERVAL 25 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 109","email_dueno":"depto109@edificio.com","valor":23,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW(), 
    NOW()
);
UPDATE sensors SET ultima_medicion = NOW() - INTERVAL 2 DAY WHERE id = @sensor_id_9;

-- Medición 10
SET @sensor_id_10 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 9);
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_10, 
    NOW() - INTERVAL 3 DAY, 
    NOW() + INTERVAL 25 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 1010","email_dueno":"depto1010@edificio.com","valor":34,"estado_valvula":"Fuga Detectada"}', 
    @user_id, 
    NOW(), 
    NOW()
);
UPDATE sensors SET ultima_medicion = NOW() - INTERVAL 3 DAY WHERE id = @sensor_id_10;
