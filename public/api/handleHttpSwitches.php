<?php
if(!isset($_POST["sw"])) {
    die('ERROR1');
}

$sw = 'switch_' . $_POST["sw"];

$switchAddresses = [
    'switch_01' => '192.168.2.185/Relay1',
    'switch_02' => '192.168.2.185/Relay1',
    'switch_03' => '192.168.2.185/Relay1',
];

if(!in_array($sw, array_keys($switchAddresses))) {
    die('ERROR2');
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
