<?php
/**
 * NOT USED: almost inished 
 * 
 * Various application states are saved in the app_state database table in order
 * for the application to restore states upon reboot or on demand
 * States are saved in fields
 * States can be retrieved by Ajax POST requests or by CLI calls (once supported)
 * If retrieved by Ajax the state is returned in JSON format to the calee and saved in a target object
 */

$errorMsg = [
    'unknown_action' => 'Unknown action',
    'dbselect' => 'Can not read from database'
];

include_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'env.php';
include_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'lib/functions.php';

/*
 * Element (JSON key) of the state that is requested (null - all elements)
 */
$elements = [];

/*
 * If script is run form CLI (or cron) handle CLI parameters
 * Parameters are JSON element names (keys) to be read (none - read all elements)
 */
if(php_sapi_name() == 'cli') {

    // CLI not yet supported
    exit("CLI not supported yet");

    // Assign argument array to elements array (first getting rid of first element of argv)
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
            $action = in_array($postVars['action'], ['read']) ? $postVars['action'] : 'read';
            unset($postVars['action']);
        }
        if(isset($postVars) && !empty($postVars)) {
            foreach($postVars as $var) {
                $elements[] = trim(strip_tags($var));
            }
        }
    }
}

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

/*
 * Reading from DB
 */
if($action == 'read') {

    // Param name from the $elements array
    $paramName = '';
    $q = "SELECT param_value FROM app_state WHERE param_name LIKE :paramname";
    $qp = [':paramname' => $paramName];

    $stmt = $DB->prepare($q);
    try {
        $stmt->execute($qp);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logError($errorMsg['dbselect'] . ' (' . $e->getMessage() . ')');
        exit();
    }

    if(isset($res) && is_arra($res) && !empty($res)) {
        die(json_encode($res));
    } else {
        die('');
    }
}
    
/*
    * Other actions are not valid
    */

else {
    logError($errorMsg['unknown_action']);
    exit();
}
