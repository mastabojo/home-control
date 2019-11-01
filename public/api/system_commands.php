<?php
$allowedCommands = [
    'exit-browser' => 'sudo /usr/bin/pkill chromium',
    'reboot'       => 'sudo /usr/bin/pkill chromium && sudo /sbin/reboot now',
    'shutdown'     => 'sudo /usr/bin/pkill chromium && sudo /sbin/halt',
    'get-ip'       => 'hostname -I',
    'ping-test'    => 'ping -c 4 8.8.8.8',
];

$cmd = $_POST['cmd'];

// get rid of all unexpected stuff
if(!isset($cmd) || empty($cmd) || !array_key_exists($cmd, $allowedCommands)) {
    exit();
}
// log action
error_log("System command executed ({$allowedCommands[$cmd]})");

// execute command
$result = exec($allowedCommands[$cmd], $output);

// Command for returning IP address
if($cmd == 'get-ip') {
    echo $result;
}

// Command for testing connectivity
if($cmd == 'ping-test') {
    echo substr($result, 0, 2) == '64' ? 'OK' : 'Failed';
}
