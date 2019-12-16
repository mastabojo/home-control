<?php
// Check if post parameter exists
if(!isset($_POST["sw"])) {
    die('ERROR1');
}

include_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'env.php';

$sw = 'switch_' . $_POST["sw"];

// Check if URL exist in env
if(
    !isset($SWITCH_O1_URL)
) {
    die('ERROR2');
}

$switchAddresses = [
    'switch_01' => $SWITCH_O2_URL,
    'switch_02' => $SWITCH_O1_URL,
    'switch_03' => $SWITCH_O3_URL,
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

echo $response == '0' || $response == '1' ? $response : '0';
