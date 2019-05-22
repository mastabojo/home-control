<?php
?>
<!-- Row 1 - consumption table -->
<div class="row no-gutters align-items-end" style="height: 180px; background-color: green;">

<!-- col 1 -->
<div class="col">

<table>
<thead>
<tr>
</tr>
</thead>

<tbody>
<tr>
<td>Trenutna dnevna poraba</td><td id="heating-current-daily-consumption">0</td>
</tr>
<tr>
<td>Min dnevna poraba v mesecu</td><td>0</td>
</tr>
<tr>
<td>Max dnevna poraba v mesecu</td><td>0</td>
</tr>
<tr>
<td>Mesečna poraba</td><td>0</td>
</tr>
</tbody>

</table>

</div><!-- .col -->

</div><!-- .row -->

<!-- Row 2 - consumption chart -->
<div class="row no-gutters align-items-end">

<!-- col 1 -->
<div class="col">

<canvas id="hpchart" width="600" height="180"></canvas>



</div><!-- .col -->

</div><!-- .row -->

<script>
var hpChartOptions = {
    title: {display: false},
    legend: {display: false},
    scales: {
        yAxes: [{
            ticks: {beginAtZero: true},
            gridLines: {display: false}
            
        }],
        xAxes: [{
            barPercentage: 1.2,
            barThickness: 'flex',
            gridLines: {
                display: false,
                drawBorder: true
            }
        }],
    }
}
setInterval(function() {
    dataStr = localStorage.getItem('heating-hpData')
    data = JSON.parse(JSON.parse(dataStr));
    console.log(data.consumption.vt + '/' + data.consumption.mt);
    $("#heating-current-daily-consumption").text('VT: ' + data.consumption.vt + ' MT: ' + data.consumption.mt);



}, 1933);

</script>