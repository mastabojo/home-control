<?php
if(!isset($_POST['cmd']) || empty($_POST['cmd'])) {
    exit();
}

include_once dirname(__DIR__, 2) . '/env.php';
include_once dirname(__DIR__, 2) . '/lib/functions.php';

$connectionTestAddress = isset($CONNECTION_CHECK_IP_ADDRESS) ? $CONNECTION_CHECK_IP_ADDRESS : '8.8.8.8';
$networkPort = 'wlan0';

$commandKey = $_POST['cmd'];

// Only these commands are allowed
$allowedCommands = [
    'exit-browser' => 'sudo /usr/bin/pkill chromium',
    'reboot'       => 'sudo /usr/bin/pkill chromium && sudo /sbin/reboot now',
    'shutdown'     => 'sudo /usr/bin/pkill chromium && sudo /sbin/halt',
    'get-ip'       => 'hostname -I',
    'uptime'       => 'cat /proc/uptime',
    // 'uptime'       => 'uptime -p',
    'test-connection'   => 'ping -c 4 ' . $connectionTestAddress,
    // Following command does not work anymore
    // 'restart-network'   => "/sbin/ifdown '$networkPort' && sleep 5 && /sbin/ifup --force '$networkPort'",
    'restart-network'   => "wpa_cli -i $networkPort reconfigure", // The command 
];

// get rid of all unexpected stuff
if(array_key_exists($commandKey, $allowedCommands)) {
    $command = $allowedCommands[$commandKey];
} else {
    logEvent("System command not allowed: $command");
    exit();        
}

switch($commandKey) {

    // These commands are logged and executed witout further feedback
    case 'exit-browser':
    case 'reboot':
    case 'shutdown':
    case 'restart-network':
        logEvent("System command executed ($command)");
        exec($command);
        die();
        break;

    // Command returns IP address
    case 'get-ip':
        $output = exec($command);
        die($output);
        break;

    // Command checks connectivity (i.e. with pinging known host)
    case 'test-connection':
        $commandOutput = [];
        $output = exec($command, $commandOutput);
        if(isset($commandOutput[1])) {
            $result = substr($commandOutput[1], 0, 2) == '64' ? '1' : '0';
        } else {
            $result = '0';
        }
        if($result == '0') {
            logEvent("System command failed ($command)");
        }
        die($result);
        break;

    // Command returns result of parsing output 
    case 'uptime':
        $commandOutput = [];
        $output = exec($command);
        // $parsed = parseUptime($output, 'uptime-p');
        $parsed = parseUptime($output, 'proc-uptime');
        // Return JSON with minutes, hours, days, months, and years keys (whatever exists)
        die($parsed ? json_encode($parsed) : 'NULL');
        break;
        
    // Command is not in allowed comands list
    default:
        logEvent("Unknown system command requested: $command");
        exit();
}