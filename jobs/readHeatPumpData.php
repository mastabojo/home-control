<?php
$baseDir = dirname(__DIR__, 1);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

// Get data from Modbus enabled Power meter by calling the python script
$pythonExec = DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python';
$pythonCommand = $pythonExec . ' ' . $baseDir . DIRECTORY_SEPARATOR . 'py' . DIRECTORY_SEPARATOR . 'modbustest.py';

// Data output from python command
$commandOutputData = [];

// Data to be cleansed and inserted into database
$heatPumpData = [
    // Total energy [KWh]
    'total_energy' => 0.0,
    // Phase 1 Line to Neutral [V]
    'phase_1_to_neutral' => 0.0,
    // Phase 2 Line to Neutral [V]
    'phase_2_to_neutral' => 0.0,
    // Phase 3 Line to Neutral [V]
    'phase_3_to_neutral' => 0.0,
    // Average Line to Neutral [V]
    'average_to_neutral' => 0.0,
    // Phase 1 Line current [A]
    'phase_1_current' => 0.0,
    // Phase 2 Line current [A]
    'phase_2_current' => 0.0,
    // Phase 3 Line current [A]
    'phase_3_current' => 0.0,
    // Average Line current [A]
    'average_current' => 0.0,
    // Sum of Line current [A]
    'sum_current' => 0.0,
    // Phase 1 phase angle [Deg]
    'phase_1_angle' => 0.0,
    // Phase 2 phase angle [Deg]
    'phase_2_angle' => 0.0,
    // Phase 3 phase angle [Deg]
    'phase_3_angle' => 0.0,
    // Total system phase angle [Deg]
    'total_phase_angle' => 0.0,
    // Input frequency [Hz]
    'input_frequency' => 0.0,
];
$heatPumpDataKeys = array_keys($heatPumpData);

try {
    // Read Modbus values calling python command
    exec($pythonCommand, $commandOutputData);

    // Assign cleansed values to array of data
    if(count($commandOutputData) == count($heatPumpData)) {
        foreach($commandOutputData as $key => $line) {
            echo "$line\n";
            $heatPumpData[$heatPumpDataKeys[$key]] = is_numeric($line) ? $heatPumpData[$heatPumpDataKeys[$key]] = (float) $line : 0.0;
        }
    }
}
catch(Exception $e) {
    die($e->getMessage());
}

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
$q = "INSERT INTO $dbTable (
    total_energy, 
    phase_1_to_neutral, 
    phase_2_to_neutral, 
    phase_3_to_neutral,
    average_to_neutral,
    phase_1_current,
    phase_2_current,
    phase_3_current,
    average_current,
    sum_current,
    phase_1_angle,
    phase_2_angle,
    phase_3_angle,
    total_phase_angle,
    input_frequency, 
    tariff
    ) VALUES (";
$q .= implode(', ', $heatPumpData);
$q .= ", '$tariff');";

echo $q . "\n";




