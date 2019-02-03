<?php
/**
 * Connect to database
 */
function getDB($host, $database, $user, $pass) {

    if(!isset($host) || !isset($database) || !isset($user) || !isset($pass)) {
        return false;
    } 

    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // throw new PDOException($e->getMessage(), (int)$e->getCode());
        error_log("Could not connect ot database $database");
        return false;
    }
}

function callPyScript($script, $params = null, $isSudo = false) {
	$pythonPath = '/usr/bin/';
	$command  = $isSudo ? 'sudo -u www-data ' : '';
	$command .= $pythonPath . 'python3 ';
	$command .= $_SERVER['DOCUMENT_ROOT'] . "/py/{$script}";
	if($params) {
		$command .= " $params";
	}
	logError($command);
	shell_exec($command);
}

/**
 * Get timestamp for current timezone
 *  
 */
function getTimezonedTs($ts = null, $timezone = 'Europe/Ljubljana') {
	
	$ts = $ts ? $ts : time();

	switch($timezone) {
		case 'Europe/Ljubljana':
			$timeOffset = 3600; break;
		default: 
			$timeOffset = 3600; break;
	}
	$timeOffset = 3600;
	return $ts + $timeOffset;
}

/**
 * Function for error logging
 */
function logError($message, $comment = null) {
	error_log($message . ($comment ? " [$comment]" : ''));
}

// Debugging
function D($var, $comment = false, $die = true) {
	echo '<pre>';
	echo $comment ? "$comment<br>" : '';
	switch(gettype($var)) {
		case 'string':
		// case 'integer':
		// case 'float':
			echo $var;
			break;
		case 'array':
		case 'object' :
		default :
			print_r($var);
			break;
	}
	echo '</pre>';
	if($die) {
		die();
	}
}

function DE($var, $comment = false) {
	$s = $comment ? $comment : '';
	switch(gettype($var)) {
		case 'string':
		case 'integer':
		case 'float':
			$s .= $var;
			break;
		case 'array':
		case 'object' :
		default :
			$s .= print_r($var);
			break;
	}
	error_log($s);
}