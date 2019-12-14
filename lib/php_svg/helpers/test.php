<?php
/*
 * This script loads SVG gauges in divs and changes properties and attributes with js and css
 */

$gagugeBarAngle = 240;
$gaugeScale = .2;
$gaugeValue = 30;
$gauge1StartAndEndValues = [-10, 35];

if($gaugeValue < $gauge1StartAndEndValues[0]) {
    $rotationAngleFromStart = -12;
} else if($gaugeValue > $gauge1StartAndEndValues[1]) {
    $rotationAngleFromStart = $gagugeBarAngle + 12;
} else {
    $rotationAngleFromStart = ($gagugeBarAngle) * (($gaugeValue - $gauge1StartAndEndValues[0]) / ($gauge1StartAndEndValues[1] - $gauge1StartAndEndValues[0]));
}
$transformationAngle = $rotationAngleFromStart - 90 + (180 - $gagugeBarAngle) / 2;
?>
<!DOCTYPE html>
<html>
<head>
<style>
@font-face {font-family: 'Muli-Regular'; src:url('Muli-Regular.ttf');}
.svg-gauge {width: <?php echo ($gaugeScale * 100)?>%; height: <?php echo ($gaugeScale * 100)?>%;}
.svg-gauge .gauge-background {fill: none; stroke: none; stroke-width: 0}
.svg-gauge .gauge-bar-0 {fill: none; stroke: rgba(184, 184, 184, .55); stroke-width: 21.6}
.svg-gauge .gauge-bar-1 {fill: none; stroke: rgba(184, 184, 184, .7); stroke-width: 21.6}
.svg-gauge .gauge-bar-2 {fill: none; stroke: rgba(184, 184, 184, .4); stroke-width: 21.6}
.svg-gauge .gauge-inner-bar {fill: none; stroke: rgba(184, 184, 184, .4); stroke-width: 1.44}
.svg-gauge .gauge-scale-values {}
.svg-gauge .gauge-scale-value {fill: rgba(184, 184, 184, .8)}
.svg-gauge .gauge-needle {fill: rgba(184, 184, 184, .8);transform-origin: 50% 50%;}
.svg-gauge .gauge-value-display {fill: rgba(24, 24, 24, .5); stroke: rgba(184, 184, 184, 0.6); stroke-width: 5; rx: 6}
.svg-gauge .gauge-value-display-text {text-anchor: middle; alignment-baseline: central; font-size: 54px; fill: rgba(184, 184, 184, .8)}
.svg-gauge .gauge-value-display-unit {font-size: 21.6px; text-anchor: middle; fill: rgba(184, 184, 184, .8)}

#gauge-1 {background-color: rgba(84, 0, 84, .95);}
</style>
</head>    

<body>

<div id="gauge-1">
<?php include 'gauge.svg'?>
</div>

<script>
var needles = document.getElementsByClassName("gauge-needle");
var gauges = document.getElementsByClassName("gauge-value-display-text");

gauges[0]["textContent"] = "<?php echo $gaugeValue;?>";
needles[0].style.transform = "rotate(<?php echo $transformationAngle;?>deg)";
console.log(<?php echo $transformationAngle;?>);
</script>
</body>

</html>