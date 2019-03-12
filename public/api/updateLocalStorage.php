<?php
$baseDir = dirname(__DIR__, 2);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);
$q = "SELECT sunrise, sunset FROM weather_current ORDER BY calc_time DESC LIMIT 1";
$stmt = $DB->prepare($q);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if(isset($row) && isset($row['sunrise']) & isset($row['sunset'])) {
    echo json_encode(['sunrise' => $row['sunrise'], 'sunset' => $row['sunset']], JSON_FORCE_OBJECT);
} else {
    echo json_encode(['sunrise' => -1, 'sunset' => -1], JSON_FORCE_OBJECT);
}
