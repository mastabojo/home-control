<?php
$theme = 'dark';
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
?>

<div class="row">

<div class="col">

<img src="/img/weather-icons/windy-weather.svg" style="width:60px;">

</div><!-- .col -->

<div class="col">
<iframe width="680" height="374" src="<?php echo $windyUrl;?>" frameborder="0"></iframe> 
</div><!-- .col -->

</div><!-- .row -->


