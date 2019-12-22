<?php
// Check if post parameter exists
if(!isset($_POST["sw"])) {
    die('ERROR1');
}

include_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'env.php';

$sw = 'switch_' . $_POST["sw"];

// Check if URL exist in env
if(
    !isset($SWITCH_01_O1_URL) ||
    !isset($SWITCH_01_O2_URL) ||
    !isset($SWITCH_01_STATE_URL)
) {
    die('ERROR2');
}

$switchAddresses = [
    'switch_01_01' => $SWITCH_01_O1_URL,
    'switch_01_02' => $SWITCH_01_O2_URL,
    'switch_01_state' => $SWITCH_01_STATE_URL
];

// Check if valid switch parameter was sent in post request
if(!in_array($sw, array_keys($switchAddresses))) {
    die('ERROR3');
}

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $switchAddresses[$sw],
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => ['id' => '']
]);
$response = curl_exec($curl);   
curl_close();

$allowedResponses = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15'];
echo in_array($response, $allowedResponses) ? $response : '0';  
    