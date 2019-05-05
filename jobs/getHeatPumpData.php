<?php
$baseDir = dirname(__DIR__, 1);

include_once $baseDir . '/env.php';
include_once $baseDir . '/lib/functions.php';

// Get data from Modbus enabled Power meter by calling the python script
$pythonExec = DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python';
$pythonCommand = $pythonExec . ' ' . $baseDir . DIRECTORY_SEPARATOR . 'py' . DIRECTORY_SEPARATOR . 'modbustest.py';
$outputData = [];
try {
    exec($pythonCommand, $outputData);
    foreach($outputData as $line) {
        echo $line . "\n";
    }
}
catch(Exception $e) {
    echo $e->getMessage();
}

// $DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);
