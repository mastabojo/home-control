<?php
/**
 * Publishes message to MQTT from Ajax call
 * Writes state to the app_state tabel in the database
 * Currently supports switches
 */

// ERROR MESSAGES
$errorMsg = [
    'postparams' => 'Missing parameters',
    'mqttparams' => 'No MQTT parameters defined',
    'postparamstruct' => 'Structure of parameters is incorrect',
    'entitiescount' => 'Number of entities (relays) no defined',
    'mqttconnect1' =>  "Connection refused (unacceptable protocol version)",
    'mqttconnect2' =>  "Connection refused (identifier rejected)",
    'mqttconnect3' =>  "Connection refused (broker unavailable )",
    'mqttpublish' => 'MQTT publish exception',
    'fileread' => 'Can not read file',
    'filewrite' => ' Can not write file',
    'entitykey' => 'Entitiy key not existing',
    'sqlexec' => 'Can not read/write database',
];

// This will be retruned to originator of ajax call (a light switch) to set the visual state feedback
$response = 0;

// Check if post parameters exists
if(!isset($_POST["sw"])) {
    logError($errorMsg['postparams']);
    exit();
}

include_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'env.php';
include_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'lib/functions.php';

/**
 * Check the existence of MQTT information
 */
if(
    !isset($MQTT_BROKER_ADDRESS) ||
    !isset($MQTT_BROKER_USER) ||
    !isset($MQTT_BROKER_PASS) ||
    !isset($TOPIC_SWITCHES) ||
    !isset($SAVED_STATE_FILE)
    ) {
    logError($errorMsg['mqttparams']);
    exit();
}

$savedStateFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $SAVED_STATE_FILE;

 /**
  * Break message into message parts array
  *
  * $messagePartsArr[0] -> switch number
  * $messagePartsArr[1] -> switch entitiy (relay)
  */
$messagePartsArr = explode("_", $_POST["sw"]);

/**
 * Check the structure of message parts
 *
 * Messages have the following structure
 * SS_TT
 *   SS [01 - 99] - switch number
 *   EE [01 - 99] - entitiy (relay) number in a switch
 */
 if(
    !isset($messagePartsArr[0]) || 
    !isset($messagePartsArr[1]) ||
    preg_match('/[0-9][0-9]/', $messagePartsArr[0]) !== 1 ||
    preg_match('/[0-9][0-9]/', $messagePartsArr[1]) !== 1) {
    logError($errorMsg['postparamstruct']);
    exit();
}

$witchId = $messagePartsArr[0];
$entityId = $messagePartsArr[1];

/**
 * Check if number of entities in a switch exists
 */
if(!isset($SWITCH_ENTITIES[$witchId])) {
    logError($errorMsg['entitiescount']);
    exit();
}

/**
 * Open the file with last status of various app and device settings
 */
if(($lastStatus = file_get_contents($savedStateFile)) === false) {
    logError($errorMsg['fileread']);
    exit();
}

$statusArr = json_decode($lastStatus, true);

$currentSwitchState = isset($statusArr['switches'][$witchId]) ? $statusArr['switches'][$witchId] : 0;

$switchStateDecoded = decodeSwitchEntityStates($currentSwitchState, $SWITCH_ENTITIES[$witchId]);

// Toggle the state of current entity
$entityIdKey = intval($entityId) - 1;
if(isset($switchStateDecoded[$entityIdKey])) {
    $switchStateDecoded[$entityIdKey] = $switchStateDecoded[$entityIdKey] == 0 ? 1 : 0;
    $response = $switchStateDecoded[$entityIdKey];
} else {
    logError($errorMsg['entitykey']);
    exit();
}

// Encode back to decimal value
$newSwitchState = bindec(implode('', $switchStateDecoded));

$topic = $TOPIC_SWITCHES . '/' . 'switch_' . $witchId;

$mqttPort = isset($MQTT_BROKER_PORT) ? $MQTT_BROKER_PORT : '1883';
$keepalive = 5;

// publish $newSwitchState to the $topic
publish_message($newSwitchState, $topic, $MQTT_BROKER_ADDRESS, $mqttPort, $MQTT_BROKER_USER, $MQTT_BROKER_PASS, $keepalive);

// Update and save status in the file (will be replaced with database)
$statusArr['switches'][$witchId] = $newSwitchState;
if(file_put_contents($savedStateFile, json_encode($statusArr)) === false) {
    logError($errorMsg['filewrite']);
    exit();
}

// Update app_state table in the database
$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

// Check if the entry for this param_name exists in the table
$q = "SELECT id FROM app_state WHERE param_name=:paramname";
$qp = [':paramname' => $witchId];
$stmt = $DB->prepare($q);
try {
    $stmt->execute($qp);
    $res = $stmt->fetch(PDO::FETCH_NUM);
} catch (PDOException $e) {
    logError($errorMsg['dbselect'] . ' (' . $e->getMessage() . ')');
    exit();
}

// Table entry for the parameter_name exists - do UPDATE
if(isset($res) && isset($res[0])) {
    $entityId = $res[0];
    $q = "UPDATE app_state SET param_value=:paramvalue WHERE param_name=:paramname;";
    $qp = [':paramvalue' => $newSwitchState, ':paramname' => $witchId];
} 

// Table entry for the entity (parameter_name) does not exist - do INSERT
else {
    $q = "INSERT INTO app_state (param_name, param_value, param_comment) VALUES (:paramvalue, :paramname, 'Inserted thru MQTT message');";
    $qp = [':paramvalue' => $newSwitchState, ':paramname' => $witchId];
}

// Get the query do its work
$stmt = $DB->prepare($q);
try {
    $stmt->execute($qp);
} catch (PDOException $e) {
    logError($errorMsg['sqlexec'] . ' (' . $e->getMessage() . ')');
    exit();
}

/** MQTT functions */

function publish_message($msg, $topic, $server, $port, $user, $pass, $keepalive) {
    $client = new Mosquitto\Client();
    $client->setCredentials($user, $pass);
    $client->onConnect('connect');
    $client->onDisconnect('disconnect');
    $client->onPublish('publish');
    $client->connect($server, $port, $keepalive);
    try {
        $client->loop();
        $mid = $client->publish($topic, $msg);
        $client->loop();
    }
    catch(Mosquitto\Exception $e) {
        logError($errorMsg['mqttpublish']);
        exit();
    }
    $client->disconnect();
    unset($client);	
}

// Call back functions required for publish function
function connect($r) {

    /*
    if($r == 0) echo "{$r}-CONX-OK|";
    if($r == 1) echo "{$r}-Connection refused (unacceptable protocol version)|";
    if($r == 2) echo "{$r}-Connection refused (identifier rejected)|";
    if($r == 3) echo "{$r}-Connection refused (broker unavailable )|";   
    */
    
    switch($r) {
        case 0 : break; // success - CONX-OK
        case 1: logError($errorMsg['mqttconnect1']); break;
        case 2: logError($errorMsg['mqttconnect2']); break;
        case 3: logError($errorMsg['mqttconnect3']); break;
        default: break;
    }
}
 
function publish() {
    global $client;
    global $response;
    echo $response;
}
 
function disconnect() {
    // echo "Disconnected|";
}