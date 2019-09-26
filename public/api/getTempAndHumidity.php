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
    // This is for calcs
    $read_time_iso = isset($data[0]['read_time']) ? date("Y-m-d H:i", strtotime($data[0]['read_time'])) : '';
    echo json_encode(
        [
            'temperature' => $temperature, 
            'humidity' => $humidity, 
            'read_time' => $read_time,
            'read_time_iso' => $read_time_iso
        ]
    );
} elseif(isset($_GET['source']) && $_GET['source'] == 'web') {
    $sensorIp = '192.168.2.181';
    // Get data with curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sensorIp);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch); 
    $outputArr = explode("|", $output);
    echo json_encode(
        [
            'temperature' => round(trim($outputArr[0])), 
            'humidity' => round(trim($outputArr[1])), 
            'read_time' => date("d.m H:i"),
            'read_time_iso' => date("Y-m-d H:i")
        ]
    );
}