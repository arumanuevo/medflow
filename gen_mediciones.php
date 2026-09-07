<?php
$sql = "-- SQL Seeder para Mediciones (Water Sensors) en Wiroos

-- 1. Identificar al usuario y grupo logístico principal
SELECT @user_id := id FROM users WHERE email = 'medflowsistemas@gmail.com' COLLATE utf8mb4_unicode_ci LIMIT 1;

-- 2. Buscamos el ID del grupo que creamos recientemente ('Torre Central - Medidores de Agua')
SELECT @group_id := id FROM sensor_groups WHERE user_id = @user_id AND name = 'Torre Central - Medidores de Agua' ORDER BY id DESC LIMIT 1;

-- 3. Insertar Mediciones simuladas en los primeros 10 Medidores de ese grupo
";

for ($i = 1; $i <= 10; $i++) {
    // Generate some mock data
    $valor = mt_rand(10, 80); // random cubic meters
    $estados = ["Abierta", "Cerrada", "Fuga Detectada"];
    $estado_val = $estados[($i % 10 == 0) ? 2 : 0]; // Every 10th has a leak, else Abierta
    $dueno = "Propietario 10" . $i;

    // JSON data array
    $data = [
        "nombre_dueno" => $dueno,
        "email_dueno" => "depto10$i@edificio.com",
        "valor" => $valor,
        "estado_valvula" => $estado_val
    ];
    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);

    $sql .= "
-- Medición $i
SET @sensor_id_$i = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET " . ($i - 1) . ");
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_$i, 
    NOW() - INTERVAL " . mt_rand(1, 5) . " DAY, 
    NOW() + INTERVAL 25 DAY, 
    30, 
    '$json_data', 
    @user_id, 
    NOW(), 
    NOW()
);
UPDATE sensors SET ultima_medicion = NOW() - INTERVAL " . mt_rand(1, 5) . " DAY WHERE id = @sensor_id_$i;
";
}

file_put_contents('k:\desarrollo\medflow\mediciones_wiroos.sql', $sql);
echo "SQL mediciones generated!\n";
