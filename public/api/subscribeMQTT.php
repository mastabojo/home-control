<?php
/**
 * NOT USED: same functionality achieved with publishMQTT.php script called from Ajax
 *           currently as a backup script only
 * 
 * This script is run from cron and listens to MQTT messages independently from the app
 * When the applicaton publishes a message to the topic it stores message 
 * in the applicaton state table in the database to ensure persistentcy of states
 */


// Error messages
$errorMsg = [
    'mqttparams' => 'No MQTT parameters defined',
    'mqtttopicstruct' => 'Structure of MQTT topic is incorrect',
    'dbselect' => 'Could not query database',
];

include_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'env.php';
include_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'lib/functions.php';

/**
 * Check the existence of MQTT information
 */
if(
    !isset($MQTT_BROKER_ADDRESS) ||
    !isset($MQTT_BROKER_PORT) ||
    !isset($MQTT_BROKER_USER) ||
    !isset($MQTT_BROKER_PASS)
    ) {
    logError($errorMsg['mqttparams']);
}

$topic = 'home/#';

$client = new Mosquitto\Client();
$client->setCredentials($MQTT_BROKER_USER, $MQTT_BROKER_PASS);
$client->onConnect('connect');
$client->onDisconnect('disconnect');
$client->onSubscribe('subscribe');
$client->onMessage('message');
$client->connect($MQTT_BROKER_ADDRESS, $MQTT_BROKER_PORT, 60);
$client->subscribe($topic, 1);
$client->loopForever();

/** MQTT functions */

function connect($r) {
    logEvent("MQTT client succesfully connected wtih response code {$r}");
}

function subscribe() {
    logEvent("MQTT client subscribed to topic");
}

/**
 * Called when message arrives for the topic
 * Stores values to application state table
 * Topic structure: home/group/entity (i.e. home/switches/switch_03)
 */
function message($message) {

    logError('MESSAGE: ' . message);

    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

    $topicArr = explode('/', $message->topic);
    
    if(!isset($topicArr) || count($topicArr) < 3) {
        logError($errorMsg['mqtttopicstruct']);
        exit();
    }

    $DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

    // If topic is home/booted/... this is a signal, that device was (re)booted and should have settings restored from the database
    if($topicArr[1] == 'booted') {
        $entity = $topicArr[3];
        $publishTopic = $topicArr[0] . '/' . $topicArr[2] . '/' . $topicArr[3];

        // Code for reading settings from database and publishing to the switch

    // If regular topic 
    } else {

        // group and entity from the topic structure for writing into database
        $entity = $topicArr[2];

        // Check if the entry for this entity (param_name) exists in the table
        $q = "SELECT id FROM app_state WHERE param_name=:entity";
        $qp = [':entity' => $entity];
        $stmt = $DB->prepare($q);
        try {
            $stmt->execute($qp);
            $res = $stmt->fetch(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            logError($errorMsg['dbselect'] . ' (' . $e->getMessage() . ')');
            exit();
        }
        
        // Table entry for the entity (parameter_name) exists - do UPDATE
        if(isset($res) && isset($res[0])) {
            $entityId = $res[0];
            $q = "UPDATE app_state SET param_value=:paramvalue WHERE param_name=:paramname;";
            $qp = [':paramvalue' => $message->payload, ':paramname' => $entity];
        } 
        
        // Table entry for the entity (parameter_name) does not exist - do INSERT
        else {
            $q = "INSERT INTO app_state (param_name, param_value, param_comment) VALUES (:paramname, :paramvalue, 'Inserted thru MQTT message');";
            $qp = [':paramvalue' => $message->payload, ':paramname' => $entity];
        }

        // Get the query do its work
        $stmt = $DB->prepare($q);
        $stmt->execute($qp);
    }        
}



function disconnect() {
    logEvent("MQTT client disconnected");
}
