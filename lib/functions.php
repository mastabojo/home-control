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
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '" . date('P') . "'"
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
 * Get timestamps for beginning of the day and end of the day for given timezone
 * @return array beginning timestamp and ending timestamp for a given day (or today)
 */
function getDayStartAndEndTs($ts = null, $timezone = 'Europe/Ljubljana') {
	
	// To DO: set timezones

	// if no timestamp, get timestamps for today
	$ts = $ts != null ? $ts : time();
	$beginOfDay = DateTime::createFromFormat('Y-m-d H:i:s', (new DateTime())->setTimestamp($ts)->format('Y-m-d 00:00:00'))->getTimestamp() + 1;
	$endOfDay = DateTime::createFromFormat('Y-m-d H:i:s', (new DateTime())->setTimestamp($ts)->format('Y-m-d 23:59:59'))->getTimestamp();
	return [$beginOfDay, $endOfDay];
}

/**
 * Get easter start timestamp for given year taking into account local timezone
 * Taken from https://secure.php.net/manual/en/function.easter-date.php
 */
function getEasterDatetime($year) {
	// get first spring day timestamp (21.3.) 
	$base = new DateTime("$year-03-21");
	// PHPs own function to get number of days till Easter after first spring day
	$days = easter_days($year);
	// return the date
    return $base->add(new DateInterval("P{$days}D"));
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