<?php
$allowedCommands = [
    'exit-browser' => 'pkill chromium',
    'reboot' => 'reboot now',
    'shutdown' => 'halt',
];

// get rid of all unexpected stuff
if(!isset($_POST['cmd']) || empty($_POST['cmd']) || !array_key_exists($_POST['cmd'], $allowedCommands)) {
    exit();
}
// execute command
exec('sudo ' . $allowedCommands[$_POST['cmd']]);
