<?php
$baseDir = dirname(__DIR__, 1);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

// Get data from Modbus enabled Power meter by calling the python script
$pythonExec = DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python';
$pythonCommand = $pythonExec . ' ' . $baseDir . DIRECTORY_SEPARATOR . 'py' . DIRECTORY_SEPARATOR . 'modbus_read_data.py';

// Data output from python command
$commandOutputData = [];

// Table fields
$heatPumpDataFields = [
    'phase_1_to_neutral',
    'phase_2_to_neutral',
    'phase_3_to_neutral',
    'phase_1_current',
    'phase_2_current',
    'phase_3_current',
    'phase_1_angle',
    'phase_2_angle',
    'phase_3_angle',
    'average_to_neutral',
    'average_current',
    'sum_current',
    'total_phase_angle',
    'input_frequency',
    'total_energy',
];

try {
    // Read Modbus values calling python command
    exec($pythonCommand, $commandOutputData);

    // Assign cleansed values to array of data
    if(count($commandOutputData) == count($heatPumpDataFields)) {
        $heatPumpData = array_map('floatval', $commandOutputData);
    }
}
catch(Exception $e) {
    die($e->getMessage());
}

if(isset($heatPumpData) && count($heatPumpData) == count($heatPumpDataFields)) {
    // Read time
    $read_time = date("Y-m-d H:i:s");

    // Find out tariff
    $currentSecontsFromMidnight = time() - strtotime("today");
    $highTarrifStart = isset($ELECTRIC_POWER_HIGH_TARIFF_START) ? ($ELECTRIC_POWER_HIGH_TARIFF_START * 60 * 60) : (6 * 60 * 60);
    $highTarrifSEnd = isset($ELECTRIC_POWER_HIGH_TARIFF_END) ? ($ELECTRIC_POWER_HIGH_TARIFF_END * 60 * 60) : (22 * 60 * 60);
    $tariff = ($currentSecontsFromMidnight < $highTarrifStart || $currentSecontsFromMidnight > $highTarrifSEnd) ? 'mt' : 'vt';

    // Store array into database
    $DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);
    $dbTable = 'heat_pump_readings';
    $q  = "INSERT INTO $dbTable (";
    $q .= "read_time, " . implode(', ', $heatPumpDataFields) . ", tariff) VALUES (";
    $q .= "'$read_time', " . implode(', ', $heatPumpData) . ", '$tariff');";

    try {
        $DB->exec($q);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
        error_log('Could not write heat pump data: ' . $e->getMessage());
    }
} else {
    echo !isset($heatPumpData) ? '$heatPumpData not set' : '$heatPumpData not complete';
}
