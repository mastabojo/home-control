<?php
/**
 * Script for calling python script using Javascript/Ajax
 */

$rootPath = dirname(__DIR__, 2);

include_once "{$rootPath}/lib/functions.php";

$allowedActions = [
    'shutter-auto-left-up', 'shutter-auto-right-up', 'shutter-auto-both-up', 
    'shutter-auto-left-down', 'shutter-auto-right-down', 'shutter-auto-both-down',
    'shutter-manual-left-up', 'shutter-manual-right-up', 'shutter-manual-both-up', 
    'shutter-manual-left-down', 'shutter-manual-right-down', 'shutter-manual-both-down'
];

if(!isset($_POST['action']) || !in_array($_POST['action'], $allowedActions)) {
    logError("Wrong command for shutters");
    exit();
}

$action = explode('-', trim($_POST['action']));
$mode = $action[1];
$side =  $action[2];
$direction = $action[3];
$timeDivider = isset($_POST['timeDivider']) && ($_POST['timeDivider'] >= 1 && $_POST['timeDivider'] <= 4) ? (int) $_POST['timeDivider'] : 1;

$args = json_encode(["mode" => $mode, "side" => $side, "direction" => $direction, "timeDivider" => $timeDivider], JSON_NUMERIC_CHECK);

// Write command into the command queue file
$cmdFileName = "{$rootPath}/py/commandqueue.txt";
if(!$handle = fopen($cmdFileName, 'w')) {
   logError("Cannot open file ($cmdFileName)") ;
   exit;
}
if(fwrite($handle, $args) === FALSE) {
   logError("Cannot write to file ($cmdFileName)");
   exit;
}
fclose($handle);

// This script is not needed anymore, the mainloop.py does the work
// callPyScript("shutters.py '$args'");
