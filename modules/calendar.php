<?php
$baseDir = dirname(__DIR__, 1);

include_once $baseDir . '/lib/functions.php';
include_once $baseDir . "/public/api/class.HcCalendar.php";
include_once $baseDir . "/public/api/class.CalendarHolidays.php";
include_once $baseDir . "/public/api/class.GarbageCollection.php";

$dayLabels = ['Pon', 'Tor', 'Sre', 'Čet', 'Pet', 'Sob', 'Ned'];

$eventiconsPath = '/public/img/event-icons/dark/';

$calendar = new HcCalendar();
$calendar->setDayLabels($dayLabels);



$events = [];

$h = new CalendarHolidays();
$holidayDates = $h->getHolidayDates();

foreach($holidayDates as $hDate => $hData) {
    $events[$hDate][0]['event_text'] = $hData['text'];
    $events[$hDate][0]['event_non_workday'] = $hData['non_workday'];
}

$g = new GarbageCollection();
$garbageCollectionDates = $g->getAllDates();

foreach($garbageCollectionDates as $gDate => $gType) {

    switch($gType) {
        case 'bio':     $events[$gDate][0]['event_icon'] = 'icon-trashcan-brown.svg'; break;
        case 'plastic': $events[$gDate][0]['event_icon'] = 'icon-trashcan-yellow.svg'; break;
        case 'rest':    $events[$gDate][0]['event_icon'] = 'icon-trashcan-black.svg'; break;
    }
}

// Events
$calendar->setEvents($events);

echo $calendar->show();
