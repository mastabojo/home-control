<?php
include_once dirname(__DIR__) . '/functions.php';
include_once dirname(__DIR__) . '/api/class.Weather.php';

$Weather = new Weather;
$w = $Weather->getWeatherCurrentDigest();
?>
<!-- div class="container" -->

  <div class="row" style="height: 186px;">
    <div id="weather-pane" class="col-sm border rounded border-dark mr-1 mb-1">
      <img id="img-icon-weather" src="/img/weather-icons/<?php echo $w['weather_icon'];?>.svg" class="align-top">
      <span id="span-temperature" class="temperature-display"><?php echo round($w['temperature']);?>&deg;</span>
      <span class="updated-display">Updated: </span><span id="span-updated" class="updated-display"><?php echo date('d.m.Y H:i:s', $w['calc_time']);?></span>
    </div>
    <div id="heat-pump-pane" class="col-sm border rounded border-dark ml-1 mb-1">
      Heat pump
    </div>
  </div>

  <div class="row" style="height: 186px;">
    <div id="blinds-pane" class="col-sm border rounded border-dark mr-1 mt-1">
      Blinds
    </div>
    <div id="lights-pane" class="col-sm border rounded border-dark ml-1 mt-1">
      Lights
    </div>
  </div>

 
<?php /*
<div class="row" style="height: 150px;">
  <div class="col-sm border rounded border-secondary m-1">
      <h4>DEBUG</h4>
    
<?php
// echo(print_r($w, 1));
?>

  </div>
</div>
*/?>

<!-- /div -->