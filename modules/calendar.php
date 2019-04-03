<?php

include_once $BASEPATH . 'lib/functions.php';
include_once $BASEPATH . "public/api/class.HcCalendar.php";
include_once $BASEPATH . "public/api/class.GarbageCollection.php";

$dayLabels = ['Pon', 'Tor', 'Sre', 'Čet', 'Pet', 'Sob', 'Ned'];

$eventiconsPath = '/public/img/event-icons/dark/';

$calendar = new HcCalendar();
$calendar->setDayLabels($dayLabels);

$g = new GarbageCollection();
$garbageCollectionDates = $g->getAllDates();

$events = [];
foreach($garbageCollectionDates as $gDate => $gType) {

    switch($gType) {
        case 'bio':     $events[$gDate][0]['event_icon'] = 'icon-trashcan-brown.svg'; break;
        case 'plastic': $events[$gDate][0]['event_icon'] = 'icon-trashcan-yellow.svg'; break;
        case 'rest':    $events[$gDate][0]['event_icon'] = 'icon-trashcan-black.svg'; break;
    }
}


// Various events
// $events['2019-03-06'][0]['event_text'] = 'Event 1';

/*
// Garbage colletion events - March 2019
$gcEvents['2019-03-06'][0]['event_icon'] = 'icon-trashcan-black.svg';
$gcEvents['2019-03-08'][0]['event_icon'] = 'icon-trashcan-brown.svg';
$gcEvents['2019-03-13'][0]['event_icon'] = 'icon-trashcan-yellow.svg';
$gcEvents['2019-03-15'][0]['event_icon'] = 'icon-trashcan-brown.svg';
$gcEvents['2019-03-22'][0]['event_icon'] = 'icon-trashcan-brown.svg';
$gcEvents['2019-03-27'][0]['event_icon'] = 'icon-trashcan-black.svg';
$gcEvents['2019-03-29'][0]['event_icon'] = 'icon-trashcan-brown.svg';

// Garbage colletion events - April 2019
$gcEvents['2019-04-03'][0]['event_icon'] = 'icon-trashcan-yellow.svg';
$gcEvents['2019-04-05'][0]['event_icon'] = 'icon-trashcan-brown.svg';
$gcEvents['2019-04-12'][0]['event_icon'] = 'icon-trashcan-brown.svg';
$gcEvents['2019-04-17'][0]['event_icon'] = 'icon-trashcan-black.svg';
$gcEvents['2019-04-19'][0]['event_icon'] = 'icon-trashcan-brown.svg';
$gcEvents['2019-04-24'][0]['event_icon'] = 'icon-trashcan-yellow.svg';
$gcEvents['2019-04-26'][0]['event_icon'] = 'icon-trashcan-brown.svg';
*/

// Events
$calendar->setEvents($events);

echo $calendar->show();
