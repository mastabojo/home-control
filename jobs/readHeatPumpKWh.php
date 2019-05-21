<?php
$baseDir = dirname(__DIR__, 1);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';
include_once $baseDir . '/public/api/class.CalendarHolidays.php';
include_once $baseDir . '/public/api/class.Lang.php';

// Read time
$read_time = date("Y-m-d H:i:s");

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

    // Non working holidays (Slovenia)
    $nonWorkingHolidays = ['01-01', '01-02', '02-08', '04-27', '05-01', '05-02', '06-25', '08-15', '10-31', '11-01', '12-25', '12-26'];

    // Easter Mondays for next 2 years
    $easterMondays = ['2020-04-13', '2021-04-05', '2022-04-18', '2023-04-10', '2024-04-01', '2025-04-21', '2026-04-06', '2027-03-29', '2028-04-17', '2029-04-02', 
    '2030-04-22', '2031-04-14', '2032-03-29', '2033-04-18', '2034-04-10', '2035-03-26', '2036-04-14', '2037-04-06', '2038-04-26', '2039-04-11', '2040-04-02'];

    // Find out tariff

    // Is it Saturday or Sunday or a non working holiday
    if(date("N") == 6 || date("N") == 7 || in_array(date("m-d"), $nonWorkingHolidays) || in_array(date("Y-m-d"), $easterMondays)) {
        $tariff = 'mt';
    }

    // Is it a work day
    else {
        $currentSecontsFromMidnight = time() - strtotime("today");
        $highTarrifStart = isset($ELECTRIC_POWER_HIGH_TARIFF_START) ? ($ELECTRIC_POWER_HIGH_TARIFF_START * 60 * 60) : (6 * 60 * 60);
        $highTarrifSEnd = isset($ELECTRIC_POWER_HIGH_TARIFF_END) ? ($ELECTRIC_POWER_HIGH_TARIFF_END * 60 * 60) : (22 * 60 * 60);
        $tariff = ($currentSecontsFromMidnight < $highTarrifStart || $currentSecontsFromMidnight > $highTarrifSEnd) ? 'mt' : 'vt';
    } 

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
 
