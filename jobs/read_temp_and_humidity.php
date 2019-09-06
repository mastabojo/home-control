<?php
$baseDir = dirname(__DIR__, 1);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

// Sensor: DHT22 sensor / NodeMcu
$sensor = [
    'id' => 's1',
    'name' => 'NodeMCU v.3 - DHT22',
    'type' => 'DHT22',
    'server' => 'NodeMcu v.3',
    'ip' => '192.168.2.181'
];

// Get data with curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sensor['ip']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch); 

$outputArr = explode("|", $output);
$temperature = trim($outputArr[0]);
$humidity = trim($outputArr[1]);
// echo "\n\nTEMP: $temperature, HUMIDITY: $humidity\n\n";

// --- Store values in database -- 

if(is_numeric($temperature) && is_numeric($humidity)) {

    $read_time = date("Y-m-d H:i:s");

    // Store array into database
    $DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);
    $q  = "INSERT INTO temp_and_humidity_readings (read_time, sensor_id, temperature, humidity) VALUES (";
    $q .= "'$read_time', '{$sensor['id']}', $temperature, $humidity);";

    try {
        $DB->exec($q);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
        error_log("Could not write sensor {$sensor['id']} data: " . $e->getMessage());
    }
}
