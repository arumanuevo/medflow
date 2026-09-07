-- SET THE USER EMAIL HERE
SET @email_titular = 'medflowsistemas@gmail.com';

-- Encontrar el ID del usuario
SELECT @user_id := id FROM users WHERE email = @email_titular LIMIT 1;

-- 1. Insert Template
INSERT INTO templates (user_id, name, description, fields, is_global, is_active, created_at, updated_at)
VALUES (
    @user_id,
    'Medición de Agua (Consorcios)',
    'Plantilla custom para registro de agua por consorcio con información de los dueños.',
    '[{"name":"nombre_dueno","label":"Nombre del Titular","type":"text","required":true,"is_key":false},{"name":"email_dueno","label":"Email del Titular","type":"text","required":false,"is_key":false},{"name":"lectura_m3","label":"Lectura de Agua (m³)","type":"number","required":true,"is_key":true},{"name":"estado_valvula","label":"Estado de Válvula","type":"select","options":["Abierta","Cerrada","Fuga Automática Detectada"],"required":true,"is_key":false}]',
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
    'Calle Principal 1234, Ciudad',
    'Agrupación logística masiva de los 100 medidores del ala sur para facturación hídrica.',
    1,
    NOW(),
    NOW()
);

-- Obtener el ID del grupo generado
SET @group_id = LAST_INSERT_ID();

-- 3. Inserción Masiva
DELIMITER $$
CREATE PROCEDURE InsertarMedidores()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE piso INT;
    DECLARE depto_num INT;
    DECLARE depto_str VARCHAR(5);
    DECLARE nom_medidor VARCHAR(255);
    DECLARE id_fisico VARCHAR(20);
    
    WHILE i <= 100 DO
        SET piso = CEILING(i / 10);
        SET depto_num = i - ((piso - 1) * 10);
        SET depto_str = LPAD(depto_num, 2, '0');
        SET nom_medidor = CONCAT('Medidor Agua - Piso ', piso, ' Depto ', depto_str);
        SET id_fisico = CONCAT('H20-', UPPER(SUBSTRING(MD5(RAND()), 1, 6)));
        
        INSERT INTO sensors (group_id, name, identificador_fisico, tipo, is_active, created_at, updated_at)
        VALUES (@group_id, nom_medidor, id_fisico, 'Agua', 1, NOW(), NOW());
        
        SET i = i + 1;
    END WHILE;
END$$
DELIMITER ;

CALL InsertarMedidores();
DROP PROCEDURE InsertarMedidores;
