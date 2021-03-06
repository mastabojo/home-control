<?php
include_once $BASEPATH . 'lib/functions.php';
include_once $BASEPATH . "public/api/class.WeatherARSO.php";

$Weather = new WeatherARSO;
$wcurr = $Weather->getWeatherCurrentDigest();
$wfcst = $Weather->getWeatherForecastDigest();

// add day names to forecast data
foreach($wfcst as $key => $fcData) {
  $wfcst[$key]['short_day_name'] = $l->Get("day_names_short")[$fcData['day_no'] - 1];
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
<div class="temp-and-humidity">
<span id="temperature-value"></span>&nbsp;&nbsp;&nbsp;<span id="humidity-value"></span><br>
<span class="updated-display"><i class="fa fa-refresh " ></i></span>&nbsp;<span id="temp-and-humidity-last-updated" class="updated-display"></span>&nbsp;

</div><!-- #temp-and-humidity -->
</div><!-- .col .text-right -->

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

<span class="updated-display"><i class="fa fa-refresh " ></i><?php echo '&nbsp;(', $WEATHER_PROVIDER, ')';?>:</span>&nbsp;
<span id="span-updated" class="updated-display"><?php echo date('d.m. H:i', $wcurr['calc_time']);?></span>

</div><!-- .col -->

</div><!-- #weather-forecast -->

</div><!-- #weather-pane -->

<div id="heat-pump-pane" class="home-panes col-sm border rounded border-dark ml-1 mb-1 align-items-center">

<?php /*
<canvas id="hp-daily" width="200" height="88" style="background-color: none;"></canvas>
*/?>

<div class="row">
<div class="col text-center">
<span id="span-main-time" class="align-middle"></span>
</div><!-- .col -->
</div><!-- .row -->

<div class="row">
<div class="col text-center">
<span id="span-main-date" class="align-middle"></span>
</div><!-- .col -->
</div><!-- .row -->




<div class="row">
<div class="col text-center">

<?php /* include "img/common-icons/$theme/sunrise_sunset_bar.svg";*/?>
<?php include "img/common-icons/$theme/sunrise_sunset_bar_2.svg";?>

</div><!-- .col -->
</div><!-- .row -->






</div><!-- #heat-pump-pane -->
</div><!-- .row -->

<div class="row" style="height: 186px;">

<div id="blinds-pane" class="home-panes col-sm border rounded border-dark mr-1 mt-1">

<!-- Shutters - row 1 (roll left/right/both up) -->
<div class="row no-gutters align-items-end">
<div class="col text-center">

<div class="icon-wrapper shutter-divider" data-ident="1"><?php include "img/shutter-icons/$theme/icon-sht-divider-1.svg";?></div>
<div class="icon-wrapper shutter-action" data-ident="shutter-auto-left-up"><?php include "img/shutter-icons/$theme/icon-sht-up-1-auto.svg";?></div>
<div class="icon-wrapper shutter-action" data-ident="shutter-auto-right-up"><?php include "img/shutter-icons/$theme/icon-sht-up-1-auto.svg";?></div>
<div class="icon-wrapper shutter-action" data-ident="shutter-auto-both-up"><?php include "img/shutter-icons/$theme/icon-sht-up-2-auto.svg";?></div>

</div><!-- .col -->
</div><!-- .row -->

<!-- Shutters - row 2 (roll left/right/both down) -->
<div class="row no-gutters align-items-start">
<div class="col text-center">

<div class="icon-wrapper shutter-divider" data-ident="2"><?php include "img/shutter-icons/$theme/icon-sht-divider-2.svg";?></div>
<div class="icon-wrapper shutter-action" data-ident="shutter-auto-left-down"><?php include "img/shutter-icons/$theme/icon-sht-down-1-auto.svg";?></div>
<div class="icon-wrapper shutter-action" data-ident="shutter-auto-right-down"><?php include "img/shutter-icons/$theme/icon-sht-down-1-auto.svg";?></div>
<div class="icon-wrapper shutter-action" data-ident="shutter-auto-both-down"><?php include "img/shutter-icons/$theme/icon-sht-down-2-auto.svg";?></div>

</div><!-- .col -->
</div><!-- .row -->

<!-- Shutters - row 3 (roll by timer - show times) -->
<div class="row no-gutters align-items-start">
<div class="col text-center">
  <div class="home-shutters-auto-info">
  <i class="fa fa-angle-double-up"></i>&nbsp;<span id="home-shutters-auto-up"></span>&nbsp;&nbsp;
  <i class="fa fa-angle-double-down"></i>&nbsp;<span id="home-shutters-auto-down"></span>
  </div>
</div><!-- .col -->
</div><!-- .row -->

</div><!-- #blinds-pane -->

<div id="lights-pane" class="home-panes col-sm border rounded border-dark ml-1 mt-1">

<!-- Lights - row 1 -->
<div class="row no-gutters align-items-end">

<!-- lights - col 1 -->
<div class="col text-center">
<span class="span-light-switch" id="01_01" data-switch="01" data-relay="01">
<?php include "img/light-icons/$theme/icon-light-switch.svg";?>
</span>
</div><!-- .col -->

<!-- lights - col 2 -->
<div class="col text-center">
<span class="span-light-switch" id="01_02" data-switch="01" data-relay="02">
<?php include "img/light-icons/$theme/icon-light-switch.svg";?>
</span>
</div><!-- .col -->

<!-- lights - col 3 -->
<div class="col text-center">
<span class="span-light-switch" id="02_01" data-switch="02" data-relay="01">
<?php include "img/light-icons/$theme/icon-light-switch.svg";?>
</span>
</div><!-- .col -->

</div><!-- .row -->

<!-- Lights - row 2 -->
<div class="row no-gutters align-items-center">

<!-- lights - col 1 -->
<div class="col text-center">
<?php echo $l->Get('lights_room_01');?>
</div><!-- .col -->

<!-- lights - col 2 -->
<div class="col text-center">
<?php echo $l->Get('lights_room_02');?>
</div><!-- .col -->

<!-- lights - col 3 -->
<div class="col text-center">
<?php echo $l->Get('lights_room_03');?>
</div><!-- .col -->

</div><!-- .row -->






</div>
</div>

 
<?php /*
<div class="row" style="height: 800px;" class="color:white; font-size: 9px;">
<div class="col-sm border rounded border-secondary m-1" id="debug-cell-1">
<br><br>
<h4>DEBUG</h4>

</div><!-- .col -->
</div><!-- .row -->

*/?>