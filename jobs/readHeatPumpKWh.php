<?php
$baseDir = dirname(__DIR__, 1);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

// Get data from Modbus enabled Power meter by calling the python script
$pythonExec = DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python';
$pythonCommand = $pythonExec . ' ' . $baseDir . DIRECTORY_SEPARATOR . 'py' . DIRECTORY_SEPARATOR . 'modbus_read_KWh.py';

// Data output from python command
$totalEnergy = 0;
$totalEnergyArr = [];

try {
    // Read Modbus value calling python command
    exec($pythonCommand, $totalEnergyArr);

    // Assign cleansed value
    if(isset($totalEnergyArr[0]) && is_numeric($totalEnergyArr[0]) && $totalEnergyArr[0] > 0) {
        $totalEnergy = floatval($totalEnergyArr[0]);
    }
}
catch(Exception $e) {
    logError($e->getMessage());
    die($e->getMessage());
}

if(isset($totalEnergy) && $totalEnergy > 0.0) {
    // Read time
    $read_time = date("Y-m-d H:i:s");

    // Find out tariff
    $currentSecontsFromMidnight = time() - strtotime("today");
    $highTarrifStart = isset($ELECTRIC_POWER_HIGH_TARIFF_START) ? ($ELECTRIC_POWER_HIGH_TARIFF_START * 60 * 60) : (6 * 60 * 60);
    $highTarrifSEnd = isset($ELECTRIC_POWER_HIGH_TARIFF_END) ? ($ELECTRIC_POWER_HIGH_TARIFF_END * 60 * 60) : (22 * 60 * 60);
    $tariff = ($currentSecontsFromMidnight < $highTarrifStart || $currentSecontsFromMidnight > $highTarrifSEnd) ? 'mt' : 'vt';

    // Store data into database
    $DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);
    $dbTable = 'heat_pump_KWh';
    $q  = "INSERT INTO $dbTable (read_time, total_energy, tariff) VALUES ('$read_time', $totalEnergy, '$tariff');";

    try {
        $DB->exec($q);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
        logError('Could not write total energy to database: ' . $e->getMessage());
    }
} else {
    logError('Error reading total energy') ;
}
