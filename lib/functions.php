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
        logError("Could not connect ot database $database");
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
 * Get easter Sunday/Monday date for given year taking into account local timezone
 * Taken from https://secure.php.net/manual/en/function.easter-date.php
 * @param int Year to find Easter for
 * @param bool Do we need result for Sunday or Monday
 * @return DateTime object | string Easter Sunday/Monday DT object / date string
 */
function getEasterDatetime($year, $dateOnly = false, $monday = false) {
	// get first spring day timestamp (21.3.) 
	$base = new DateTime("$year-03-21");
	// PHPs own function to get number of days till Easter after first spring day
	$days = easter_days($year); 
	// If we want date for Easter Monday
	if($monday) {
		$days = $days + 1;
	}
	// return the date
	return $dateOnly ?  $base->add(new DateInterval("P{$days}D"))->format("Y-m-d") : $base->add(new DateInterval("P{$days}D"));
}

/**
 * Find out if date is Easter Monday (for dates in next 20 years)
 * Usage: isEasterMonday('YYY-MM-DD')
 * @param string Date in "Y-m-d" format
 * @return bool	true if date is Easter Monday
 */
function isEasterMonday($date) {

	$year = date("Y", strtotime($date));
	$easterDate = getEasterDatetime($year, true, true);
	return date("Y-m-d", strtotime($easterDate)) == date("Y-m-d", strtotime($date)) ? true : false;
	
	// Alternative with hard coded Easter Monday dates
	/*
	$easterMondays = ['2020-04-13', '2021-04-05', '2022-04-18', '2023-04-10', '2024-04-01', '2025-04-21', '2026-04-06', '2027-03-29', '2028-04-17', '2029-04-02', 
	'2030-04-22', '2031-04-14', '2032-03-29', '2033-04-18', '2034-04-10', '2035-03-26', '2036-04-14', '2037-04-06', '2038-04-26', '2039-04-11', '2040-04-02'];
	return in_array(date("Y-m-d", strtotime($date)), $easterMondays);
	*/
}

/**
 * Find out if date is workday
 * Usage: isWorkDay('YYY-MM-DD')
 * @param string Date in "Y-m-d" format
 * @param string Country code
 * @return bool	true if date is workday
 */
function isWorkDay($date, $country = "SI") {
	// Non working holidays
	switch($country) {
		case "SI":
			$nonWorkingHolidays = ['01-01', '01-02', '02-08', '04-27', '05-01', '05-02', '06-25', '08-15', '10-31', '11-01', '12-25', '12-26'];
			break;
		default:
			$nonWorkingHolidays = ['01-01', '01-02', '02-08', '04-27', '05-01', '05-02', '06-25', '08-15', '10-31', '11-01', '12-25', '12-26'];
			break;		
	}
	return (date("N") == 6 || date("N") == 7 || in_array(date("m-d"), $nonWorkingHolidays) || isEasterMonday(date("Y-m-d"))) ? false : true;
}

/**
 * Returns array of differences; needs previous value fot first array element calculation
 * 
 * @param array Array of values
 * @param mixed Previous value (int | float) for calculating first diff array element
 * @return array Differences
 */
function arrayGetDiffs($values, $previousValue) {
	$diffs = [];
	$count = count($values);
	$diffs[0] = round($values[0] - $previousValue, 2);
	for($i = 1; $i < $count; $i++) {
		$diffs[$i] = round($values[$i] - $values[$i - 1], 2);
	}
	return $diffs;
}

/**
 * Iterates through array and returns array of defined length  with missing keys filled with values of previous key
 * If first key is missing the $initial value is used
 * 
 * @param array source array
 * @param int desired lenth of target array (target key count)
 * @param initial initial value if first key is missing
 */
function fillMissingKeys($arr, $targetKeyCount, $initial = 0) {
	$previous_temp = $initial;
	for($i = 0; $i < $targetKeyCount; $i++) {
        if(isset($arr[$i])) {
            $previous_temp = $arr[$i];
        } else {
            $arr[$i] = $previous_temp;
        }
    }

    ksort($arr);
	return $arr;
}

/**
 * Reindex array so it uses one elment for key and another for value(s)
 */
function ArrayValueToKey($arr, $key = 0, $val = 1) {
	$newArr = [];
	foreach($arr as $val) {
		$newArr[$arr[$key]] = $arr[$val];
	}
	unset($arr);
	return $newArr;
}


/**
 * Parse uptime information
 * Currently only uptime -p is supported
 * TODO: support other command s (proc/uptime, top...)
 * 
 * @param string Uptime information
 * @param string Command that returned uptime information
 * @return array Array of parsed values | null
 */
function parseUptime($uptime, $source = 'uptime-p') {

	// Returned data
	$data = ['years' => 0, 'months' => 0, 'days' => 0, 'hours' => 0, 'minutes' => 0];

	switch($source) {

		case 'uptime-p':
			// example of uptime -p command output: up 1 hour, 2 minutes
			if(empty($uptime)) {
				return null;
			}
			// Get rid of the text 'up '
			$uptime = substr($uptime, 3);
			// Convert string to array and reverse it, so minutes are first
			$uptimeArr = array_reverse(explode(', ', $uptime));
			// Fill the data array with values, that exist
			for($i = 0; $i < count($uptimeArr); $i++) {
				// Find  appropriate key corresponding to the $data array keys (if singular append 's' to it)
				$key = trim(preg_replace('/[0-9]/', '', $uptimeArr[$i]));
				$key = substr($key, -1) != 's' ? $key . 's' : $key;
				// Find value
				$val = preg_replace('/[^0-9]/', '', $uptimeArr[$i]);
				// Set the $data array element
				$data[$key] = $val;
			}
			return $data;
			break;
		
		case 'proc-uptime':
			// example of cat /proc/uptime command output: 6257.30 23276.94 - 1st number is number of uptime in seconds
			$uptime = explode(' ',$uptime);
			$uptime = explode('.', $uptime[0]);
			$uptimeSeconds = $uptime[0];
			$data['minutes'] = floor(((($uptimeSeconds % 31556926) % 86400) % 3600) / 60);
			$data['hours'] = floor((($uptimeSeconds % 31556926) % 86400) / 3600);
			$data['days'] = floor(($uptimeSeconds % 31556926) / 86400);
			return $data;
			break;

		default:
			return null;
	}
}

/**
 * Function for event logging
 */
function logEvent($message) {
	$file = dirname(__DIR__) . '/log/event.log';
	$s = '[' . date("d.m.Y H:i:s") . "] $message\n";
	file_put_contents($file, $s, FILE_APPEND);
}

/**
 * Function for error logging
 */
function logError($message, $comment = null) {
	error_log('HCC_EROR: ' . $message . ($comment ? " [$comment]" : ''));
}

// Debugging
function D($var, $comment = false, $die = true) {
	echo php_sapi_name() == 'cli' ? '' : '<pre>';
	echo php_sapi_name() == 'cli' ? "$comment\n" : "$comment<br>";
	switch(gettype($var)) {
		case 'string':
		case 'integer':
		case 'float':
			echo $var;
			break;
		case 'array':
		case 'object' :
		default :
			print_r($var);
			break;
	}
	echo php_sapi_name() == 'cli' ? '' : '</pre>';
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
			$s .= var_export($var, 1);
			break;
	}
	error_log($s);
}

// Debug into text file
function DF($var, $append = true) {
	$debugFile = dirname(__DIR__) . '/log/debug.txt';
	$s = '[' . date("d.m.Y H:i:s") . "] ====\n";
	switch(gettype($var)) {
		case 'string':
		case 'integer':
		case 'float':
			$s .= $var;
			break;
		case 'array':
		case 'object' :
		default :
			$s .= var_export($var, 1);
			break;
	}
	$s .= "\n";
	if($append) {
		file_put_contents($debugFile, $s, FILE_APPEND);
	} else {
		file_put_contents($debugFile, $s);
	}
}

// Debug into JS console
function DC($var, $comment = false) {
	$s = $comment ? "$comment\n" : '';
	switch(gettype($var)) {
		case 'string':
		case 'integer':
		case 'float':
			$s .= $var;
		break;
		case 'array':
		case 'object' :
		default :
			$s .= var_export($var, 1);
			break;
	}
	echo "<script>console.log(\"HCC: \" + $s)</script>";
}