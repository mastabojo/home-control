<?php

// DEBUG
// $_POST["sw"] = "01_01";

// Check if post parameters exists
if(!isset($_POST["sw"])) {
    die('ERROR1');
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
    die('ERROR2');
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
    die('ERROR3');
}

$witchId = $messagePartsArr[0];
$entityId = $messagePartsArr[1];

/**
 * Check if number of entities in a switch exists
 */
if(!isset($SWITCH_ENTITIES[$witchId])) {
    die('ERROR4');
}

/**
 * Open the file with last status of various app and device settings
 */
if(($lastStatus = file_get_contents($savedStateFile)) === false) {
    die('ERROR5');
}

$statusArr = json_decode($lastStatus, true);

$currentSwitchState = isset($statusArr['switches'][$witchId]) ? $statusArr['switches'][$witchId] : 0;

$switchStateDecoded = decodeSwitchEntityStates($currentSwitchState, $SWITCH_ENTITIES[$witchId]);

// Toggle the state of current entity
$identityKey = intval($entityId) - 1;
if(isset($switchStateDecoded[$identityKey])) {
    $switchStateDecoded[$identityKey] = $switchStateDecoded[$identityKey] == 0 ? 1 : 0;
} else {
    die('ERROR6');
}

// Encode back to decimal value
$newSwitchState = bindec(implode('', $switchStateDecoded));

$topic = $TOPIC_SWITCHES . '/' . 'switch_' . $witchId;

$mqttPort = isset($MQTT_BROKER_PORT) ? $MQTT_BROKER_PORT : '1883';
$keepalive = 5;

// publish $newSwitchState to the $topic
publish_message($newSwitchState, $topic, $MQTT_BROKER_ADDRESS, $mqttPort, $MQTT_BROKER_USER, $MQTT_BROKER_PASS, $keepalive);

// Update and save status 
$statusArr['switches'][$witchId] = $newSwitchState;
if(file_put_contents($savedStateFile, json_encode($statusArr, JSON_PRETTY_PRINT)) === false) {
    die('ERROR6');
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
        }catch(Mosquitto\Exception $e){
            echo 'Exception'; 
            print_r($e);         
            return;
        }
    $client->disconnect();
    unset($client);	
}

// Call back functions required for publish function
function connect($r) {
    if($r == 0) echo "{$r}-CONX-OK|";
    if($r == 1) echo "{$r}-Connection refused (unacceptable protocol version)|";
    if($r == 2) echo "{$r}-Connection refused (identifier rejected)|";
    if($r == 3) echo "{$r}-Connection refused (broker unavailable )|";        
}
 
function publish() {
    global $client;
    echo "Message published:";
}
 
function disconnect() {
    echo "Disconnected|";
}