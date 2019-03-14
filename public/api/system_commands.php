<?php
$allowedCommands = [
    'exit-browser' => 'sudo pkill chromium',
    'reboot' => 'sudo reboot now',
    'shutdown' => 'sudo halt',
];

// get rid of all unexpected stuff
if(!isset($_POST['cmd']) || empty($_POST['cmd']) || !array_key_exists($_POST['cmd'], $allowedCommands)) {
    exit();
}
// log action
error_log("System command executed ({$allowedCommands[$_POST['cmd']]})");
// execute command
exec('sudo ' . $allowedCommands[$_POST['cmd']]);
