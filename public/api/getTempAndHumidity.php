<?php
$baseDir = dirname(__DIR__, 2);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

if(isset($_GET['source']) && $_GET['source'] == 'db') {
    $DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);
    // Get last reading for temperature and humidity
    $q = "SELECT read_time, temperature, humidity FROM temp_and_humidity_readings ORDER BY read_time DESC LIMIT 1";
    $stmt = $DB->prepare($q);
    $stmt->execute();
    $data = $stmt->fetchAll();
    $temperature = isset($data[0]['temperature']) ? round($data[0]['temperature']) : 'n/a';
    $humidity = isset($data[0]['humidity']) ? round($data[0]['humidity']) : 'n/a';
    $read_time = isset($data[0]['read_time']) ? date("d.m H:i", strtotime($data[0]['read_time'])) : 'n/a';
    DE(json_encode(
        [
            'temperature' => $temperature, 
            'humidity' => $humidity, 
            'read_time' => $read_time
        ]
    ));
    echo json_encode(
        [
            'temperature' => $temperature, 
            'humidity' => $humidity, 
            'read_time' => $read_time
        ]
    );
} 