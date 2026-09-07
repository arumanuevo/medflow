-- SQL Seeder para Historial de Consumos Anteriores en Wiroos

-- 1. Identificar al usuario y grupo logístico
SELECT @user_id := id FROM users WHERE email = 'medflowsistemas@gmail.com' COLLATE utf8mb4_unicode_ci LIMIT 1;
SELECT @group_id := id FROM sensor_groups WHERE user_id = @user_id AND name = 'Torre Central - Medidores de Agua' ORDER BY id DESC LIMIT 1;

-- 3. Insertar Histórico de Mediciones (Meses Pasados) en los MISMOS 10 Medidores

-- =================== SENSOR 1 (Piso 1 Depto 1) ===================
SET @sensor_id_1 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 0);

-- Lectura de hace 2 meses
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_1, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 30 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 101","email_dueno":"depto101@edificio.com","valor":11,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 60 DAY
);

-- Lectura de hace 1 mes
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_1, 
    NOW() - INTERVAL 30 DAY, 
    NOW(), 
    30, 
    '{"nombre_dueno":"Propietario 101","email_dueno":"depto101@edificio.com","valor":39,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 30 DAY, 
    NOW() - INTERVAL 30 DAY
);

-- =================== SENSOR 2 (Piso 1 Depto 2) ===================
SET @sensor_id_2 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 1);

-- Lectura de hace 2 meses
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_2, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 30 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 102","email_dueno":"depto102@edificio.com","valor":10,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 60 DAY
);

-- Lectura de hace 1 mes
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_2, 
    NOW() - INTERVAL 30 DAY, 
    NOW(), 
    30, 
    '{"nombre_dueno":"Propietario 102","email_dueno":"depto102@edificio.com","valor":35,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 30 DAY, 
    NOW() - INTERVAL 30 DAY
);

-- =================== SENSOR 3 (Piso 1 Depto 3) ===================
SET @sensor_id_3 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 2);

-- Lectura de hace 2 meses
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_3, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 30 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 103","email_dueno":"depto103@edificio.com","valor":30,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 60 DAY
);

-- Lectura de hace 1 mes
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_3, 
    NOW() - INTERVAL 30 DAY, 
    NOW(), 
    30, 
    '{"nombre_dueno":"Propietario 103","email_dueno":"depto103@edificio.com","valor":47,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 30 DAY, 
    NOW() - INTERVAL 30 DAY
);

-- =================== SENSOR 4 (Piso 1 Depto 4) ===================
SET @sensor_id_4 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 3);

-- Lectura de hace 2 meses
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_4, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 30 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 104","email_dueno":"depto104@edificio.com","valor":10,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 60 DAY
);

-- Lectura de hace 1 mes
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_4, 
    NOW() - INTERVAL 30 DAY, 
    NOW(), 
    30, 
    '{"nombre_dueno":"Propietario 104","email_dueno":"depto104@edificio.com","valor":34,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 30 DAY, 
    NOW() - INTERVAL 30 DAY
);

-- =================== SENSOR 5 (Piso 1 Depto 5) ===================
SET @sensor_id_5 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 4);

-- Lectura de hace 2 meses
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_5, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 30 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 105","email_dueno":"depto105@edificio.com","valor":31,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 60 DAY
);

-- Lectura de hace 1 mes
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_5, 
    NOW() - INTERVAL 30 DAY, 
    NOW(), 
    30, 
    '{"nombre_dueno":"Propietario 105","email_dueno":"depto105@edificio.com","valor":61,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 30 DAY, 
    NOW() - INTERVAL 30 DAY
);

-- =================== SENSOR 6 (Piso 1 Depto 6) ===================
SET @sensor_id_6 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 5);

-- Lectura de hace 2 meses
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_6, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 30 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 106","email_dueno":"depto106@edificio.com","valor":35,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 60 DAY
);

-- Lectura de hace 1 mes
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_6, 
    NOW() - INTERVAL 30 DAY, 
    NOW(), 
    30, 
    '{"nombre_dueno":"Propietario 106","email_dueno":"depto106@edificio.com","valor":55,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 30 DAY, 
    NOW() - INTERVAL 30 DAY
);

-- =================== SENSOR 7 (Piso 1 Depto 7) ===================
SET @sensor_id_7 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 6);

-- Lectura de hace 2 meses
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_7, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 30 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 107","email_dueno":"depto107@edificio.com","valor":21,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 60 DAY
);

-- Lectura de hace 1 mes
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_7, 
    NOW() - INTERVAL 30 DAY, 
    NOW(), 
    30, 
    '{"nombre_dueno":"Propietario 107","email_dueno":"depto107@edificio.com","valor":36,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 30 DAY, 
    NOW() - INTERVAL 30 DAY
);

-- =================== SENSOR 8 (Piso 1 Depto 8) ===================
SET @sensor_id_8 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 7);

-- Lectura de hace 2 meses
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_8, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 30 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 108","email_dueno":"depto108@edificio.com","valor":11,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 60 DAY
);

-- Lectura de hace 1 mes
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_8, 
    NOW() - INTERVAL 30 DAY, 
    NOW(), 
    30, 
    '{"nombre_dueno":"Propietario 108","email_dueno":"depto108@edificio.com","valor":37,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 30 DAY, 
    NOW() - INTERVAL 30 DAY
);

-- =================== SENSOR 9 (Piso 1 Depto 9) ===================
SET @sensor_id_9 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 8);

-- Lectura de hace 2 meses
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_9, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 30 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 109","email_dueno":"depto109@edificio.com","valor":40,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 60 DAY
);

-- Lectura de hace 1 mes
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_9, 
    NOW() - INTERVAL 30 DAY, 
    NOW(), 
    30, 
    '{"nombre_dueno":"Propietario 109","email_dueno":"depto109@edificio.com","valor":57,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 30 DAY, 
    NOW() - INTERVAL 30 DAY
);

-- =================== SENSOR 10 (Piso 1 Depto 10) ===================
SET @sensor_id_10 = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET 9);

-- Lectura de hace 2 meses
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_10, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 30 DAY, 
    30, 
    '{"nombre_dueno":"Propietario 1010","email_dueno":"depto1010@edificio.com","valor":28,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 60 DAY
);

-- Lectura de hace 1 mes
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_10, 
    NOW() - INTERVAL 30 DAY, 
    NOW(), 
    30, 
    '{"nombre_dueno":"Propietario 1010","email_dueno":"depto1010@edificio.com","valor":46,"estado_valvula":"Abierta"}', 
    @user_id, 
    NOW() - INTERVAL 30 DAY, 
    NOW() - INTERVAL 30 DAY
);
