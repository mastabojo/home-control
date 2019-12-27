<?php
/**
 * Various application states are saved in a file in rder for the application to restore states upon reboot or other event.
 * States are saved in JSON format
 * States can be retrieved by POST requests or by CLI calls
 */

$errorMsg = [
    'nofile' => 'File does not exist',
    'fileread' => 'Error reading file',
];

include_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'env.php';
include_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'lib/functions.php';

/*
 * Action to perform (read from file or write to file)
 * Element (JSON key) of the state that isrequested (null - all elements)
 */
$action = 'read';
$elements = [];

/*
 * If script was run form CLI (or cron) handle CLI parameters
 * Parameters are JSON element names (keys) to be read
 * 
 * -- Currently only reading from file is allowed from CLI, writing is not planned --
 */
if(php_sapi_name() == 'cli') {

    // Assign argument array to elements array (first getting rid of first elemnt of argv)
    if(count($argv) > 1) {
        unset($argv[0]);
        foreach($argv as $arg) {
            $elements[] = trim($arg);
        }
    }
}

/*
 * If script was called with Ajax handle POST parameters
 */
else {

    if(isset($_POST['action'])) {
        $postVars = $_POST;
        if(isset($postVars['action'])) {
            $action = in_array($postVars['action'], ['read', 'write']) ? $postVars['action'] : 'read';
            unset($postVars['action']);
        }
        if(isset($postVars) && !empty($postVars)) {
            foreach($postVars as $var) {
                $elements[] = trim(strip_tags($var));
            }
        }
    }
}

// Check for application state file
if(!isset($SAVED_STATE_FILE)) {
    logError($errorMsg['nofile']);
    die($errorMsg['nofile']);
}

if($action == 'read') {

    // If file can not be read exit script
    if(($appState = file_get_contents($SAVED_STATE_FILE)) === false) {
        logError($errorMsg['fileread']);
        die($errorMsg['fileread']);
    }

    // If no elements were specified return all elements
    if(!isset($elements) || empty($elements) || !is_array($elements)) {
        echo $appState;
    } else {
        $appStateArr = json_decode($appState);
        $ret = [];
        foreach($elements as $el) {
            $ret[$el] = getArrayKeyValue($appStateArr, $el);
        }
        echo json_encode($ret);
    }
}

