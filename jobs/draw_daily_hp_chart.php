<?php
/**
 * WORK IN PROGRESS
 * 
 * Not sure if this will be used or not
 * 
 * Draws daily bar chart for heat pump KWh consumption displaying low / high / single tariff
 * Charts are dumped in a directory as SVG files that are included in hcc app in various places
 * Charts are created in regular intervals as new readings are available or on demand by Ajax calls for custom periods
 * Old charts are being automatically deleted by this script
 * 
 * TO DO: make width and height also CLI/GET/POST parameters
 */

include_once dirname(__DIR__) . '/env.php';
include_once dirname(__DIR__) . '/lib//functions.php';

// If called from the CLI (by cron job) the period is current time
if(php_sapi_name() == 'cli') {
    $date = date('Y-m-d');
}
// If called by Ajax call the period must be provided (or defaults are used)
else {
    $date = isset($_GET['period']) ? $_GET['period'] : date('Y-m-d');
}

// Read tariffs form configuration
$singleTarrif = isset($ELECTRIC_POWER_SINGLE_TARIFF) ? $ELECTRIC_POWER_SINGLE_TARIFF : true;
$highTariffStart = isset($ELECTRIC_POWER_HIGH_TARIFF_START) ? $ELECTRIC_POWER_HIGH_TARIFF_START : 6;
$highTariffEnd = isset($ELECTRIC_POWER_HIGH_TARIFF_END) ? $ELECTRIC_POWER_HIGH_TARIFF_END : 22;

// Tariff abbreviations
$ST = 'et';
$LT = 'mt';
$HT = 'vt';

$month = date('m', strtotime($date));
$previuousMonth = date('n', strtotime($date)) > 1 ? str_pad(date('n', strtotime($date)) - 1, 2, '0', STR_PAD_LEFT)  : '12';
$year = date('Y', strtotime($date));
$previousMonthsYear = date('n', strtotime($date)) > 1 ? date('Y', strtotime($date)) : date('Y', strtotime($date)) - 1;
$daysInMonth = date('t', strtotime($date));

$DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

// get last tariff from previous day
$q = "SELECT ROUND(MAX(total_energy), 2) AS read_energy 
FROM heat_pump_KWh WHERE read_time < '$date';";
$stmt = $DB->prepare($q);
$stmt->execute();
$total_energy_prev = $stmt->fetchColumn(0);

 // Get all tariff readings for current day from database
$q = "SELECT HOUR(read_time) AS read_time, ROUND(MAX(total_energy), 2) AS read_value 
FROM heat_pump_KWh WHERE read_time LIKE CONCAT('$date', '%') GROUP BY HOUR(read_time);";
$stmt = $DB->prepare($q);
$stmt->execute();
// $rows_all_tariffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hourlyDataTotals = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE);

// How many values there are for selected day
$count = count($hourlyDataTotals);

// Fill in missing values
$hourlyDataTotals = fillMissingKeys($hourlyDataTotals, 24, $total_energy_prev);

// Get differences
$chartData = arrayGetDiffs($hourlyDataTotals, $total_energy_prev);

$svgWidth = 766;
$svgHeight = 180;
$barCount = count($chartData);
$paddings = ['top' => 5, 'right' => 5, 'bottom' => 5, 'left' => 5];
$maxBarHeight = 146;
$barBorder = 0;
$barSpacing = 3;
$xAxisLineWidth = 2;
$xAxisLabelHeight = 8;
$xAxisLabelTopMargin = 2;
$chartAreaWidth = $svgWidth - $paddings['left'] - $paddings['right'];
$barAreaWidth = $chartAreaWidth / $barCount;
$barWidth = $barAreaWidth - $barSpacing;

// Start SVG
$svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"$svgWidth\" height=\"$svgHeight\">";

// X axis
$x1 = $paddings['left'];
$y1 = $svgHeight - $paddings['bottom'] - $xAxisLabelHeight - $xAxisLabelTopMargin;
$x2 = $svgWidth - $paddings['right'];
$y2 = $y1;
$svg .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" class=\"svg-chart-x-axis\" />";

// Bars and labels
for($i = 0; $i < $barCount; $i++) {
    // center of the bar
    $cx = $paddings['left'] + ($i * $barAreaWidth) + ($barAreaWidth / 2);

    // labels
    $textY = $y1 + $xAxisLabelHeight + $xAxisLabelTopMargin + $xAxisLineWidth;
    $textLabel = $i + 1;
    $svg .= "<text x=\"$cx\" y=\"$textY\" class=\"chart-text chart-label-text\" style=\"text-anchor: middle\">$textLabel</text>";


    // bars
    // $barValue = round($chartData[$i], 2);
    $x1 = $cx - ($barWidth / 2);
    $barHeight = max($chartData) > 0 ? $maxBarHeight * ($chartData[$i] / max($chartData)) : 0;

    $barClass = $singleTarrif || ($i > $highTariffStart && $i < $highTariffEnd) ? 'bar-light' : 'bar-dark';
    
    $svg .= "<rect x=\"$x1\" y=\"$y1\" width=\"$barWidth\" height=\"$barHeight\" class=\"$barClass\" transform=\"translate(0, -$barHeight)\" />";
}

// Finish off SVG
$svg .= '</svg>';

// Write the damn thing
$file = 'hp_chart_daily_1.svg';
$h = fopen($file, 'w'); // or die("\n\nFile could not be opened\n\n");
fwrite($h, $svg); // or die("\n\nFile could not be written\n\n");
fclose($h);
// echo "\n\nFile $file writen\n\n";
