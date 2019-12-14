<?php
/**
 * This script creates SVG gauge. Default settings are provided 
 * and can be changed inline, with command line parameters or query string
 * It requires php-svg library (https://github.com/meyfa/php-svg)
 * 
 * Usage on CLI
 * php test1.php 33 '{"doc":{"width":500, "height":500}, "scale": {"unit": "%"}}'
 */

require_once __DIR__ . '/lib/autoloader.php';

use SVG\SVG;
use SVG\Nodes\Texts\SVGText;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Structures\SVGFont;
use SVG\Nodes\Structures\SVGGroup;

// SVG document default values
$settings = [
    'doc' => [
        'width' => 200,
        'height' => 200,
        'background-color' => 'none',
        'border-width' => 0,
        'border-color' => 'none',
        'font-name' => 'Muli-Regular',
        'font' => 'Muli-Regular.ttf',
    ],

    'bars' => [
        'width-factor' => 0.06,
        'angle' => 240,
        'colors' => ['rgba(184, 184, 184, .55)', 'rgba(184, 184, 184, .7)', 'rgba(184, 184, 184, .4)'],
        'angle-parts' => [0.64, 0.20, 0.16],
        // 'angle-parts' => [0.25, 0.50, 0.25],
        'inner' => [
            'width-factor' => 0.004,
            // 'width-factor' => 0.00,
            'color' => 'rgba(184, 184, 184, .4)'
        ]
    ],

    'scale' => [
        'unit' => '0 ',
        // 'unit' => '%',
        'values' => ["-10", "-5", "0", "5", "10", "15", "20", "25", "30", "35"],
        // 'values' => ["0", "10", "20", "30", "40", "50", "60", "70", "80", "90", "100"],
        'radius-factor' => 0.95,
        'text-size-factor' => 0.05,
        'text-color' => 'rgba(184, 184, 184, .8)',
        'unit-text-size-factor' => 0.4
    ],

    'needle' => [
        'width-factor' => 0.08,
        'height-factor' => 0.7,
        'color' => 'rgba(184, 184, 184, .8)',
    ],

    'value-display' => [
        'width-factor' => .4,
        'height-factor' => .2,
        'text-color' => 'rgba(184, 184, 184, .8)',
        'fill' => 'rgba(24, 24, 24, .5)',
        'stroke' => 'rgba(184, 184, 184, 0.6)',
        'stroke-width' => 5,
        'rx' => 6,
        'text-size-factor' => 0.15,
        'text-color' => 'rgba(184, 184, 184, .8)',
        'unit-text-size-factor' => 0.4,
        'unit-vertical-position' => 'top',
        // 'unit-vertical-position' => 'bottom'
    ]
];

/**
 * If used from CLI
 * First argument constains value
 * Second argument (argv[2]) contains JSON encoded options (optional)
 */
if(php_sapi_name() === 'cli') {
    $value = isset($argv[1]) && is_numeric($argv[1]) ? $argv[1] : null;
    if(isset($argv[2]) && !empty($argv[2])) {
        $options = json_decode($argv[2], true);
        $settings = array_replace_recursive($settings, $options);
    }

/**
 * If used thru HTTP
 * $_GET['value'] constains value
 * Other $_GET elements contain options in URL encoded string (optional)
 */    
} else {
    $value = isset($_GET['value']) && is_numeric($_GET['value']) ? $_GET['value'] : null;
    unset($_GET['value']);
    if(isset($_GET) && !empty($_GET)) {
        $options = [];
        parse_str($_GET, $options);
        $settings = array_merge_recursive($settings, $options);
    }
}

// If there is no value return something
if(!$value) {
    $value = 0;
}

// Debuging only
// $settings['doc']['background-color'] = 'rgb(14, 68, 90)';

$centerX = $settings['doc']['width'] / 2;
$centerY = $settings['doc']['height'] / 2;
$valuesCount = count($settings['scale']['values']);

// image with defined viewport
$image = new SVG($settings['doc']['width'], $settings['doc']['height']);
$doc = $image->getDocument();
$doc->setAttribute('viewBox', "0 0 {$settings['doc']['width']} {$settings['doc']['height']}");
$doc->setAttribute('class', "svg-gauge");

// Font
// $font = new SVGFont($settings['doc']['font-name'], $settings['doc']['font']);
// $doc->addChild($font);

// Background square
$square = new SVGRect(0, 0, $settings['doc']['width'], $settings['doc']['height']);
$square->setAttribute('class', 'gauge-background');
// $square->setStyle('fill', $settings['doc']['background-color']);
// $square->setStyle('stroke', $settings['doc']['border-color']);
// $square->setStyle('stroke-width', $settings['doc']['border-width']);
$doc->addChild($square);

// Gauge bars
$gaugeBarWidth = $settings['doc']['width'] * $settings['bars']['width-factor'];
$gaugeBarRadius = ($settings['doc']['width'] * 0.5) - ($gaugeBarWidth / 2);
$gaugeBarStartAngle = -$settings['bars']['angle'] / 2;
$currentGaugeBarAngleStart = $gaugeBarStartAngle;
for($i = 0; $i < count($settings['bars']['angle-parts']); $i++) {

    $currentAngleEnd = $currentGaugeBarAngleStart + ($settings['bars']['angle'] * $settings['bars']['angle-parts'][$i]);

    // convert angles in radians
    $startAngleInRadians = ($currentGaugeBarAngleStart - 90.0) * M_PI / 180.0;
    $endAngleInRadians = ($currentAngleEnd - 90.0) * M_PI / 180.0;

    // coordinates for start and end of the arc
    $start = [
        'x' => $centerX + ($gaugeBarRadius * cos($endAngleInRadians)),
        'y' => $centerY + ($gaugeBarRadius * sin($endAngleInRadians))
    ];
    $end = [
        'x' => $centerX + ($gaugeBarRadius * cos($startAngleInRadians)),
        'y' => $centerY + ($gaugeBarRadius * sin($startAngleInRadians))
    ];
    $largeArcFlag = ($currentAngleEnd - $currentGaugeBarAngleStart) <= 180 ? "0" : "1";

    // create SVG d string
    $barDescriptionString = implode(
        ' ', 
        [
            "M", $start['x'], $start['y'], 
            "A", $gaugeBarRadius, $gaugeBarRadius, 0, $largeArcFlag, 0, $end['x'], $end['y']
        ]);
    $bar = new SVGPath($barDescriptionString);
    $bar->setAttribute('class', 'gauge-bar-' . $i);
    // $bar->setStyle('fill', 'none');
    // $bar->setStyle('stroke', $settings['bars']['colors'][$i]);
    $bar->setStyle('stroke-width', $gaugeBarWidth);
    $doc->addChild($bar);
    $currentGaugeBarAngleStart = $currentAngleEnd;
}

// Gauge inner bar
$gaugeBar2Width = $settings['doc']['width'] * $settings['bars']['inner']['width-factor'];
$gaugeBar2Radius = ($settings['doc']['width'] * 0.3) - ($gaugeBar2Width / 2);
$gaugeBar2StartAngle = -$settings['bars']['angle'] / 2;
$gaugeBar2EndAngle = $gaugeBar2StartAngle + $settings['bars']['angle'];

// convert angles in radians
$startAngleInRadians = ($gaugeBar2StartAngle - 90.0) * M_PI / 180.0;
$endAngleInRadians = ($gaugeBar2EndAngle - 90.0) * M_PI / 180.0;

// coordinates for start and end of the arc
$start = [
    'x' => $centerX + ($gaugeBar2Radius * cos($endAngleInRadians)),
    'y' => $centerY + ($gaugeBar2Radius * sin($endAngleInRadians))
];
$end = [
    'x' => $centerX + ($gaugeBar2Radius * cos($startAngleInRadians)),
    'y' => $centerY + ($gaugeBar2Radius * sin($startAngleInRadians))
];
$largeArcFlag = ($gaugeBar2EndAngle - $gaugeBar2StartAngle) <= 180 ? "0" : "1";

// create SVG d string
$barDescriptionString = implode(
    ' ', 
    [
        "M", $start['x'], $start['y'], 
        "A", $gaugeBar2Radius, $gaugeBar2Radius, 0, $largeArcFlag, 0, $end['x'], $end['y']
    ]);
$bar2 = new SVGPath($barDescriptionString);
$bar2->setAttribute('class', 'gauge-inner-bar');
$bar2->setStyle('fill', 'none');
// $bar2->setStyle('stroke', $settings['bars']['colors'][2]);
$bar2->setStyle('stroke-width', $gaugeBar2Width);
$doc->addChild($bar2);

// gauge scale values group
$scaleValuesGroup = new SVGGroup();
$doc->addChild($scaleValuesGroup);
$scaleValuesGroup->setAttribute('class', 'gauge-scale-values');

// gauge scale values
$textPositionDistance = ($gaugeBarRadius * $settings['scale']['radius-factor']) - $gaugeBarWidth;
for($i = 0; $i < $valuesCount; $i++) {
    $currentAngle = $gaugeBarStartAngle + ($settings['bars']['angle'] / ($valuesCount - 1)) * $i;
    $currentAngleInRadians = ($currentAngle - 90) * M_PI / 180.0;
    $textX = ($centerX + ($textPositionDistance * cos($currentAngleInRadians)));
    $textY = ($centerY + ($textPositionDistance * sin($currentAngleInRadians))) + 3;
    $scaleText = $settings['scale']['values'][$i] == '0' ? $settings['scale']['values'][$i] . ' ' : $settings['scale']['values'][$i];
    $text = new SVGText($scaleText, $textX, $textY);
    $text->setAttribute('class', 'gauge-scale-value');
    // $text->setFont($font);
    $text->setStyle('font-size', $settings['doc']['width'] * $settings['scale']['text-size-factor']);
    $text->setStyle('text-anchor', 'middle');
    // $text->setStyle('fill', $settings['scale']['text-color']);
    $doc->addChild($text);
    // Add scale value to the group
    $scaleValuesGroup->addChild($text);
}

// Needle rotation angle (depending on value)
$minGaugeScaleValue = $settings['scale']['values'][0];
$maxGaugeScaleValue = $settings['scale']['values'][$valuesCount - 1];
if($value < $minGaugeScaleValue) {
    $rotationAngleFromStart = - 15;
} else if($value > $maxGaugeScaleValue) {
    $rotationAngleFromStart = $settings['bars']['angle'] + 15;
} else {
    $valueRange = $maxGaugeScaleValue - $minGaugeScaleValue;
    $relativeValue = $value - $minGaugeScaleValue;
    $rotationAngleFromStart = ($settings['bars']['angle']) * ($relativeValue / $valueRange);
}
$needleRotationAngle = $rotationAngleFromStart - 90 + (180 - $settings['bars']['angle']) / 2;

// Needle
$needleWidth = $settings['doc']['width'] * $settings['needle']['width-factor'];
$needleDescriptionString = implode(
    ' ', [
        'M', 
        $centerX - ($needleWidth / 2), 
        $centerY, 
        'L', 
        $centerX, 
        $centerY - ($gaugeBarRadius * $settings['needle']['height-factor']),
        'L',
        $centerX + ($needleWidth / 2), 
        $centerY, 
        'Z']);
$needle = new SVGPath($needleDescriptionString);
$needle->setAttribute('class', 'gauge-needle');
// $needle->setStyle('fill', $settings['needle']['color']);
$needle->setAttribute('transform', "rotate($needleRotationAngle, $centerX, $centerY)");
$doc->addChild($needle);

// Value display
$valueDisplayWidth = $settings['doc']['width'] * $settings['value-display']['width-factor'];
$valueDisplayHeight = $settings['doc']['height'] * $settings['value-display']['height-factor'];
$valueDisplayX = $centerX - ($valueDisplayWidth / 2);
$valueDisplayY = $settings['doc']['height'] - $valueDisplayHeight - $settings['doc']['height'] * 0.1;
$valueDisplay = new SVGRect($valueDisplayX, $valueDisplayY, $valueDisplayWidth, $valueDisplayHeight);
$valueDisplay->setAttribute('class', 'gauge-value-display');
// $valueDisplay->setStyle('fill', $settings['value-display']['fill']);
$valueDisplay->setStyle('stroke', $settings['value-display']['stroke']);
$valueDisplay->setStyle('stroke-width', $settings['value-display']['stroke-width']);
$valueDisplay->setStyle('rx', $settings['value-display']['rx']);
$doc->addChild($valueDisplay);

// Value display text
$valueDisplayTextSize = $settings['doc']['height'] * $settings['value-display']['text-size-factor'];
$value = $value == 0 ? $value . ' ' : $value;
$text = new SVGText($value, $centerX, $valueDisplayY + ($valueDisplayHeight / 2));
$text->setAttribute('class', 'gauge-value-display-text');
$text->setStyle('text-anchor', 'middle');
$text->setStyle('alignment-baseline', 'central');
// $text->setFont($font);
$text->setSize($valueDisplayTextSize);
// $text->setStyle('fill', $settings['value-display']['text-color']);
$doc->addChild($text);

// Value display unit
$unitX = $centerX + $valueDisplayWidth * 0.3;
$unitY = $settings['value-display']['unit-vertical-position'] == 'top' ? $centerY + $valueDisplayHeight + $valueDisplayTextSize * 0.6 : $centerY + $valueDisplayHeight + $valueDisplayTextSize;
$text = new SVGText($settings['scale']['unit'], $unitX, $unitY);
$text->setAttribute('class', 'gauge-value-display-unit');
// $text->setFont($font);
$text->setSize($valueDisplayTextSize * $settings['scale']['unit-text-size-factor']);
$text->setStyle('text-anchor', 'middle');
// $text->setStyle('fill', $settings['value-display']['text-color']);
$doc->addChild($text);

header('Content-Type: image/svg+xml');
echo $image;
