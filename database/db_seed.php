<?php
include '../env.php';
include '../lib/functions.php';

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

$testPass = password_hash($TEST_PASS, PASSWORD_DEFAULT);

// Uncomment tables to be seeded
$seedTables = [
    // 'cities',
    // 'hccusers'
];

$q = [];
$q['cities'] = "INSERT INTO cities (city_id, city_name, city_country) VALUES 
    (3196359, 'Ljubljana', 'SI'),
    (3190310, 'Sostro', 'SI')";

$q['hccusers'] = "INSERT INTO hccusers (username, passwrd, userlevel, firstname, lastname) VALUES
    ('bojo', '$testPass', 1, 'Bojo', 'R')";

$q['holiday_dates'] = "INSERT INTO holiday_dates (country_code, holiday_date, holiday_name, non_working_day) VALUES
    ('SI', '01-01', 'Novo leto', 'y'),
    ('SI', '01-02', 'Novo leto', 'y'),
    ('SI', '02-08', 'Prešernov dan', 'y'), 
    ('SI', '04-27', 'Dan upora proti okupatorju', 'y'),
    ('SI', '05-01', 'Praznik dela', 'y'),
    ('SI', '05-02', 'Praznik dela', 'y'),
    ('SI', '06-08', 'Dan Primoža Trubarja', 'n'),
    ('SI', '06-25', 'Dan državnosti', 'y'),
    ('SI', '08-15', 'Dan MV', 'y'),
    ('SI', '08-17', 'Združitev prekmurskih Slovencev z matičnim narodom', 'n'),
    ('SI', '09-15', 'Vrnitev Primorske k matični domovini', 'n'),
    ('SI', '10-25', 'Dan suverenosti', 'n'),
    ('SI', '10-31', 'Dan reformacije', 'y'),
    ('SI', '11-01', 'Dan spomina na mrtve', 'y'),
    ('SI', '11-23', 'Dan Rudolfa Maistra', 'n'),
    ('SI', '12-25', 'Božič', 'y'),
    ('SI', '12-26', 'Dan samostojnosti in enotnosti', 'y');";

// Seed selected tables
foreach($qry as $key => $q) {
    // create only selected tables present in the $createTables array
    if(in_array($key, $seedTables)) {
        echo $q . "\n\n";
        $stmt = $DB->prepare($q);
        $stmt->execute();
    }
}
