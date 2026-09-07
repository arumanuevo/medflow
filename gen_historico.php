<?php
$sql = "-- SQL Seeder para Historial de Consumos Anteriores en Wiroos

-- 1. Identificar al usuario y grupo logístico
SELECT @user_id := id FROM users WHERE email = 'medflowsistemas@gmail.com' COLLATE utf8mb4_unicode_ci LIMIT 1;
SELECT @group_id := id FROM sensor_groups WHERE user_id = @user_id AND name = 'Torre Central - Medidores de Agua' ORDER BY id DESC LIMIT 1;

-- 3. Insertar Histórico de Mediciones (Meses Pasados) en los MISMOS 10 Medidores
";

for ($i = 1; $i <= 10; $i++) {
    $dueno = "Propietario 10" . $i;
    $email = "depto10$i@edificio.com";

    // Para cada sensor generaremos 2 mediciones hacia atrás (Hace 60 días y hace 30 días)

    // -- LECTURA DE HACE 2 MESES --
    $valor60 = mt_rand(10, 40);
    $data_60 = [
        "nombre_dueno" => $dueno,
        "email_dueno" => $email,
        "valor" => $valor60,
        "estado_valvula" => "Abierta"
    ];
    $json_60 = json_encode($data_60, JSON_UNESCAPED_UNICODE);

    // -- LECTURA DE HACE 1 MES --
    $valor30 = $valor60 + mt_rand(10, 30); // Incremento lógico de consumo
    $data_30 = [
        "nombre_dueno" => $dueno,
        "email_dueno" => $email,
        "valor" => $valor30,
        "estado_valvula" => "Abierta"
    ];
    $json_30 = json_encode($data_30, JSON_UNESCAPED_UNICODE);

    $sql .= "
-- =================== SENSOR $i (Piso 1 Depto $i) ===================
SET @sensor_id_$i = (SELECT id FROM sensors WHERE group_id = @group_id ORDER BY id ASC LIMIT 1 OFFSET " . ($i - 1) . ");

-- Lectura de hace 2 meses
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_$i, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 30 DAY, 
    30, 
    '$json_60', 
    @user_id, 
    NOW() - INTERVAL 60 DAY, 
    NOW() - INTERVAL 60 DAY
);

-- Lectura de hace 1 mes
INSERT INTO measurements (sensor_id, measured_at, proxima_medicion, periodo_medicion, data, created_by, created_at, updated_at)
VALUES (
    @sensor_id_$i, 
    NOW() - INTERVAL 30 DAY, 
    NOW(), 
    30, 
    '$json_30', 
    @user_id, 
    NOW() - INTERVAL 30 DAY, 
    NOW() - INTERVAL 30 DAY
);
";
}

file_put_contents('k:\desarrollo\medflow\mediciones_historico_wiroos.sql', $sql);
echo "SQL historico generated!\n";
