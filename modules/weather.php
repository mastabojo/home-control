<?php
$theme = 'dark';

$arsoUrl = 'http://vreme.arso.gov.si/napoved/Ljubljana/graf#zoom=50';

$openUrl = "https://openweathermap.org/weathermap?basemap=map&cities=true&layer=temperature&lat=46.0732&lon=14.7574&zoom=10";

$windyUrl = "https://embed.windy.com/embed2.html
?lat=46.048
&lon=14.505
&zoom=11
&level=surface
&overlay=temp
&menu=
&message=true
&marker=
&calendar=
&pressure=
&type=map
&location=coordinates
&detail=
&detailLat=50.090
&detailLon=14.420
&metricWind=m%2Fs
&metricTemp=%C2%B0C
&radarRange=-1";

// $accuUrl = "https://www.accuweather.com/en/si/ljubljana/299198/daily-weather-forecast/299198";
$accuUrl = "https://www.accuweather.com/en/si/sostro/1560826/daily-weather-forecast/1560826";
?>

<div class="row">

<div class="col weather-display-icons">
<div class="weather-display-icon"><img src="/img/weather-icons/arso-weather.svg" id="arso"></div>
<div class="weather-display-icon"><img src="/img/weather-icons/open-weather.svg" id="open"></div>
<div class="weather-display-icon"><img src="/img/weather-icons/windy-weather.svg" id="windy"></div>
<div class="weather-display-icon"><img src="/img/weather-icons/accu-weather.svg" id="accu"></div>
</div><!-- .col -->

<div class="col">
<iframe id="weather-display" width="680" height="374" src="<?php echo $arsoUrl;?>" frameborder="0"></iframe> 
</div><!-- .col -->

</div><!-- .row -->


