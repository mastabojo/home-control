<?php
?>
<!-- Row 1 - consumption table -->
<div class="row no-gutters align-items-end" style="height: 180px;">

<!-- col 1 -->
<div class="col">

<table class="table table-sm">
<thead>
<tr>
<th>Poraba (KWh)</th><th>MT</th><th>VT</th><th>Skupaj</th><th>Cena (Eur)</th>
</tr>
</thead>

<tbody>
<tr id="heating-current-daily-consumption">
<td>Trenutna dnevna poraba</td><td>0</td><td>0</td><td>0</td><td>0</td>
</tr>
<tr>
<td>Mesečna poraba</td><td>0</td><td>0</td><td>0</td><td>0</td>
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
    data = JSON.parse(dataStr);
    $("#heating-current-daily-consumption td:nth-child(2)").text(data.consumption.mt);
    $("#heating-current-daily-consumption td:nth-child(3)").text(data.consumption.vt);
    $("#heating-current-daily-consumption td:nth-child(4)").text((data.consumption.mt + data.consumption.vt));
    $("#heating-current-daily-consumption td:nth-child(4)").text(((data.consumption.mt * data.rates.low_rate) + (data.consumption.vt * data.rates.high_rate));


}, 1233);

</script>