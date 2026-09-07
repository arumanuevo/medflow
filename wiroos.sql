-- SQL optimizado y adaptado EXACTAMENTE al esquema de la DB

SELECT @user_id := id FROM users WHERE email = 'medflowsistemas@gmail.com' COLLATE utf8mb4_unicode_ci LIMIT 1;

-- 1. Insert Template (Adaptado a schema y created_by)
INSERT INTO templates (name, description, type, `schema`, is_default, created_by, created_at, updated_at)
VALUES (
    'Medición de Agua (Consorcios)',
    'Plantilla custom para registro de agua por consorcio.',
    'agua',
    '{"campos": [{"nombre": "nombre_dueno", "tipo": "texto", "requerido": true}, {"nombre": "email_dueno", "tipo": "texto", "requerido": false}, {"nombre": "valor", "tipo": "numero", "requerido": true, "unidad": "m³"}, {"nombre": "estado_valvula", "tipo": "opciones", "opciones": ["Abierta","Cerrada","Fuga Detectada"], "requerido": true}]}',
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
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 1 Depto 01', 'AG-078E32E3', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 1 Depto 02', 'AG-ED917025', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 1 Depto 03', 'AG-5CD5BEB3', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 1 Depto 04', 'AG-0301CEB8', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 1 Depto 05', 'AG-93B6FC58', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 1 Depto 06', 'AG-07822337', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 1 Depto 07', 'AG-356BF118', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 1 Depto 08', 'AG-0C302998', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 1 Depto 09', 'AG-0436E121', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 1 Depto 10', 'AG-26BF87E1', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 2 Depto 01', 'AG-93DA3EBA', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 2 Depto 02', 'AG-1EFC6F43', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 2 Depto 03', 'AG-8E861D9B', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 2 Depto 04', 'AG-4300D21A', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 2 Depto 05', 'AG-588D3BC5', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 2 Depto 06', 'AG-CFC4C5E1', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 2 Depto 07', 'AG-15E55C61', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 2 Depto 08', 'AG-8B55C918', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 2 Depto 09', 'AG-2AE65A43', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 2 Depto 10', 'AG-B16DD4AC', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 3 Depto 01', 'AG-7DDBD91A', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 3 Depto 02', 'AG-2112457C', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 3 Depto 03', 'AG-242BFF3E', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 3 Depto 04', 'AG-E522ABAD', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 3 Depto 05', 'AG-7DC09DC1', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 3 Depto 06', 'AG-35A39DBD', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 3 Depto 07', 'AG-E8B99593', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 3 Depto 08', 'AG-0B066709', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 3 Depto 09', 'AG-DAE309F3', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 3 Depto 10', 'AG-6015D56F', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 4 Depto 01', 'AG-BCA57098', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 4 Depto 02', 'AG-2A385884', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 4 Depto 03', 'AG-22330408', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 4 Depto 04', 'AG-2E669928', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 4 Depto 05', 'AG-7DB4886F', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 4 Depto 06', 'AG-DE46F86B', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 4 Depto 07', 'AG-2C5B0C91', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 4 Depto 08', 'AG-0B43686E', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 4 Depto 09', 'AG-74D12A70', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 4 Depto 10', 'AG-14BD6912', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 5 Depto 01', 'AG-72481172', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 5 Depto 02', 'AG-D7043C40', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 5 Depto 03', 'AG-BF69BF9F', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 5 Depto 04', 'AG-2D417343', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 5 Depto 05', 'AG-44E9EF2E', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 5 Depto 06', 'AG-D4AEC8A7', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 5 Depto 07', 'AG-AEE9B254', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 5 Depto 08', 'AG-EAC9BC65', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 5 Depto 09', 'AG-863632FC', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 5 Depto 10', 'AG-69024F09', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 6 Depto 01', 'AG-84FE0654', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 6 Depto 02', 'AG-2CACB0E6', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 6 Depto 03', 'AG-4C580337', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 6 Depto 04', 'AG-65080D3B', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 6 Depto 05', 'AG-9CD39862', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 6 Depto 06', 'AG-33A37C64', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 6 Depto 07', 'AG-085BD1F9', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 6 Depto 08', 'AG-72ED5141', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 6 Depto 09', 'AG-E3035494', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 6 Depto 10', 'AG-763139F4', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 7 Depto 01', 'AG-5B87BF74', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 7 Depto 02', 'AG-4740E4A9', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 7 Depto 03', 'AG-CC438235', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 7 Depto 04', 'AG-5DEB7540', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 7 Depto 05', 'AG-2F248656', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 7 Depto 06', 'AG-1A7C0B53', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 7 Depto 07', 'AG-F96DB2C4', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 7 Depto 08', 'AG-7A89D128', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 7 Depto 09', 'AG-36B93B99', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 7 Depto 10', 'AG-DC991651', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 8 Depto 01', 'AG-EE7E0433', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 8 Depto 02', 'AG-720E1818', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 8 Depto 03', 'AG-C2D03128', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 8 Depto 04', 'AG-B8A515EF', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 8 Depto 05', 'AG-210B4D9C', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 8 Depto 06', 'AG-89A87010', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 8 Depto 07', 'AG-9A1B1717', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 8 Depto 08', 'AG-253DF42A', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 8 Depto 09', 'AG-1D97923E', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 8 Depto 10', 'AG-8A25B853', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 9 Depto 01', 'AG-F3C89D94', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 9 Depto 02', 'AG-BAA3FE3B', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 9 Depto 03', 'AG-C3DE8161', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 9 Depto 04', 'AG-F08627B4', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 9 Depto 05', 'AG-57E019CF', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 9 Depto 06', 'AG-C7F45E14', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 9 Depto 07', 'AG-CD28C95C', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 9 Depto 08', 'AG-EA4AD7D4', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 9 Depto 09', 'AG-E9E11B5E', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 9 Depto 10', 'AG-549FCCC2', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 10 Depto 01', 'AG-2AA6E8B2', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 10 Depto 02', 'AG-A9AE514A', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 10 Depto 03', 'AG-3CF550CC', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 10 Depto 04', 'AG-797CCFEB', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 10 Depto 05', 'AG-88A9D409', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 10 Depto 06', 'AG-48F57C7B', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 10 Depto 07', 'AG-FF3BA2F2', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 10 Depto 08', 'AG-746C4E54', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 10 Depto 09', 'AG-F606C784', 0, 1, NOW(), NOW());
INSERT INTO sensors (group_id, name, identifier, is_community, marcado_para_medicion, created_at, updated_at) VALUES (@group_id, 'Agua Piso 10 Depto 10', 'AG-497494DB', 0, 1, NOW(), NOW());
