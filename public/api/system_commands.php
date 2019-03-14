<?php
$allowedCommands = [
    'exit-browser' => 'sudo /usr/bin/pkill chromium',
    'reboot' => 'sudo /sbin/reboot/reboot now',
    'shutdown' => 'sudo /sbin/halt',
];

// get rid of all unexpected stuff
if(!isset($_POST['cmd']) || empty($_POST['cmd']) || !array_key_exists($_POST['cmd'], $allowedCommands)) {
    exit();
}
// log action
error_log("System command executed ({$allowedCommands[$_POST['cmd']]})");
// execute command
exec($allowedCommands[$_POST['cmd']]);
