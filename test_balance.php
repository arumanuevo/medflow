<?php
$ch = curl_init('https://api.deepseek.com/user/balance');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Authorization: Bearer sk-e2c1e78a645c419ca4557abab4a3d4d1'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
echo $response;
