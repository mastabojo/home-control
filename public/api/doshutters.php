<?php
/**
 * Script for calling python script using Javascript/Ajax
 */

include_once dirname(__DIR__) . '/functions.php';

$allowedActions = [
    'shutter-auto-left-up', 'shutter-auto-right-up', 'shutter-auto-both-up', 
    'shutter-auto-left-down', 'shutter-auto-right-down', 'shutter-auto-both-down',
    'shutter-manual-left-up', 'shutter-manual-right-up', 'shutter-manual-both-up', 
    'shutter-manual-left-down', 'shutter-manual-right-down', 'shutter-manual-both-down'
];

 if(!isset($_POST['action']) || !in_array($_POST['action'], $allowedActions)) {
    logError("Wrong command for shutters");
    exit();
 } else {
    $action = explode('-', trim($_POST['action']));
    $mode = $action[1];
    $side =  $action[2];
    $direction = $action[3];
 }

 $args = json_encode(["mode" => $mode, "side" => $side, "direction" => $direction], JSON_NUMERIC_CHECK);

callPyScript("shutters.py '$args'");
