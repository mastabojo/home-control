<?php
$baseDir = dirname(__DIR__, 1);

include_once $baseDir . '/lib/functions.php';
include_once $baseDir . "/public/api/class.HcCalendar.php";
include_once $baseDir . "/public/api/class.CalendarHolidays.php";
include_once $baseDir . "/public/api/class.GarbageCollection.php";

$eventiconsPath = '/public/img/event-icons/dark/';

$calendar = new HcCalendar();
$calendar->setDayLabels($l->Get("day_names_short"));
$calendar->setMonthLabels($l->Get("month_names"));


// All the events for selected month
$events = [];

// Holidays for selected month
$h = new CalendarHolidays(null, $l);
$h->setLangObj($l);
$holidayDates = $h->getHolidayDates();

foreach($holidayDates as $hDate => $hData) {
    $events[$hDate][0]['event_text'] = isset($hData['text']) ? $hData['text'] : '';
    $events[$hDate][0]['event_non_workday'] = $hData['non_workday'];
}

// Garbage collection dates for selected month
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
