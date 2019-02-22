<?php
include_once $BASEPATH . 'lib/functions.php';
include_once $BASEPATH . 'public/api/class.WeatherOWM.php';

$Weather = new WeatherOWM;
$wcurr = $Weather->getWeatherCurrentDigest();
$wfcst = $Weather->getWeatherForecastDigest();

$days = ['Nedelja', 'Ponedeljek', 'Torek', 'Sreda', 'Četrtek', 'Petek', 'Sobota'];
// add day names to forecast data
foreach($wfcst as $key => $fcData) {
  $wfcst[$key]['short_day_name'] = mb_substr($days[$wfcst[$key]['day_no']], 0, 3);
}
?>

<div class="row" style="height: 186px;">
<div id="weather-pane" class="home-panes col-sm border rounded border-dark mr-1 mb-1">

<div id="weather-current" class="row align-items-end">

<div class="col">


<div class="row no-gutters">

<div class="col-8">
<span  id="img-icon-weather"><img src="/img/weather-icons/<?php echo $theme . '/' . $wcurr['weather_icon'];?>.svg"></span>&nbsp;&nbsp;
<span id="span-temperature" class="temperature-display"><?php echo round($wcurr['temperature']);?>&deg;</span>&nbsp;
<span id="span-city_name"><?php echo $wcurr['city_name'];?></span>
</div><!-- .col -->

<div class="col text-right">
<span class="updated-display">Zadnji podatki:</span><span id="span-updated" class="updated-display"><?php echo date('d.m. H:i', $wcurr['calc_time']);?></span>
</div><!-- .col -->

</div><!-- .row -->

</div><!-- .col -->

</div><!-- #weather-current -->


<div id="weather-forecast" class="row align-items-end">
<div class="col text-right">

<div class="row no-gutters align-items-end">
<?php
foreach($wfcst as $key => $fc) {
  echo '<div class="col text-center">';
  echo '<div id="fcast-day-' . $key . '" class="fcast-day">';
  echo "<span class=\"short-day-name\">{$fc['short_day_name']}</span><br>";
  echo '<span class="temperature">' . round($fc['temperature_day']) . '&deg; (' . round($fc['temperature_night']) . '&deg;)</span><br>';
  echo "<img src=\"/img/weather-icons/{$theme}/{$fc['weather_icon']}.svg\">";
  echo '</div><!-- .fcast-day -->';
  echo '</div><!-- .col -->';
}
?>
</div><!-- .row -->

</div><!-- .col -->

</div><!-- #weather-forecast -->

</div><!-- #weather-pane -->

<div id="heat-pump-pane" class="home-panes col-sm border rounded border-dark ml-1 mb-1">

<canvas id="hp-daily" width="200" height="88" style="background-color: grey;"></canvas>













</div><!-- #heat-pump-pane -->
</div><!-- .row -->

<div class="row" style="height: 186px;">
<div id="blinds-pane" class="home-panes col-sm border rounded border-dark mr-1 mt-1">

<!-- Shutters - row 1 (roll automatic and manual up) -->
<div class="row no-gutters align-items-end">

<!-- Shutters - col 1 (roll automatic up) -->
<div class="col text-center">
<img src="img/shutter-icons/<?php echo $theme; ?>/icon-sht-up-1-auto-off.svg" id="shutter-auto-left-up">
<img src="img/shutter-icons/<?php echo $theme; ?>/icon-sht-up-1-auto-off.svg" id="shutter-auto-right-up">
<img src="img/shutter-icons/<?php echo $theme; ?>/icon-sht-up-2-auto-off.svg" id="shutter-auto-both-up">
</div><!-- .col -->


</div><!-- .row -->

<!-- Shutters - row 2 (roll automatic and manual down) -->
<div class="row no-gutters align-items-start">
  
<!-- Shutters - col 1 (roll automatic up) -->
<div class="col text-center">
<img src="img/shutter-icons/<?php echo $theme; ?>/icon-sht-down-1-auto-off.svg" id="shutter-auto-left-down">
<img src="img/shutter-icons/<?php echo $theme; ?>/icon-sht-down-1-auto-off.svg" id="shutter-auto-right-down">
<img src="img/shutter-icons/<?php echo $theme; ?>/icon-sht-down-2-auto-off.svg" id="shutter-auto-both-down">
</div><!-- .col -->


</div><!-- .row -->

</div>
<div id="lights-pane" class="home-panes col-sm border rounded border-dark ml-1 mt-1">

<!-- Lights - row 1 -->
<div class="row no-gutters align-items-end">

<!-- lights - col 1 -->
<div class="col text-center">
<img src="img/light-icons/<?php echo $theme; ?>/icon-light-off.svg">
</div><!-- .col -->

<!-- lights - col 2 -->
<div class="col text-center">
<img src="img/light-icons/<?php echo $theme; ?>/icon-light-off.svg">
</div><!-- .col -->

<!-- lights - col 3 -->
<div class="col text-center">
<img src="img/light-icons/<?php echo $theme; ?>/icon-light-off.svg">
</div><!-- .col -->

</div><!-- .row -->

<!-- Lights - row 2 -->
<div class="row no-gutters align-items-center">

<!-- lights - col 1 -->
<div class="col text-center">
Dnevni prostor
</div><!-- .col -->

<!-- lights - col 2 -->
<div class="col text-center">
Hodnik
</div><!-- .col -->

<!-- lights - col 3 -->
<div class="col text-center">
Vhod
</div><!-- .col -->

</div><!-- .row -->






</div>
</div>

 
<?php /*
<div class="row" style="height: 800px;" class="color:white; font-size: 9px;">
<div class="col-sm border rounded border-secondary m-1">
<h4>DEBUG</h4>
    
<?php echo('<pre>' . print_r($wfcst, 1) . '</pre>'); ?>

</div><!-- .col -->
</div><!-- .row -->
*/?>
