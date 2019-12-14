<?php
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
<div class="environment-tab">


<div class="row  align-items-center justify-content-center" style="height: 340px;">

<div class="col text-center">

<div id="environment-temeperature-01">
<?php include '../public/img/environment/gauge-temperature.svg'?>
</div>
<?php echo $l->Get("environment-temperature-label");?>

</div><!-- .col -->

<div class="col text-center">

<div id="environment-humidity-01">
<?php include '../public/img/environment/gauge-humidity.svg'?>
</div>
<?php echo $l->Get("environment-humidity-label");?>

</div><!-- .col -->

<div class="col text-center">


</div><!-- .col -->

<div class="col text-center">


</div><!-- .col -->

</div><!-- .row -->


<div class="row  align-items-center justify-content-right" style="height: 20px;">

<div class="col text-center">Dnevni prostor
</div><!-- .col -->

<div class="col text-center">Zunaj
</div><!-- .col -->

</div><!-- .row -->


<div class="row  align-items-center justify-content-right" style="height: 20px;">

<div class="col text-center">
</div><!-- .col -->

<div class="col text-center">
</div><!-- .col -->

<div class="col text-center">
</div><!-- .col -->

<div class="col text-right">
<span class="updated-display"><i class="fa fa-refresh " ></i></span>&nbsp;<span id="environment-temp-and-humidity-last-updated"></span>
</div><!-- .col -->

</div><!-- .row -->

</div><!-- .environment-tab -->


