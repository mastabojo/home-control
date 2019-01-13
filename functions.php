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