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
<td>Trenutna dnevna poraba</td><td></td><td></td><td></td><td></td>
</tr>
<tr>
<td>Mesečna poraba</td><td></td><td></td><td></td><td></td>
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

    // get heat pump data
    $.get("api/getHpConsumptionData.php", function(data) {
        hpData = JSON.parse(data);
    });

    var price = hpData.consumption.highTariffCost - hpData.consumption.lowTariffCost;
    $("#heating-current-daily-consumption td:nth-child(2)").text(hpData.consumption.lowTariff);
    $("#heating-current-daily-consumption td:nth-child(3)").text(hpData.consumption.highTariff);
    $("#heating-current-daily-consumption td:nth-child(4)").text((hpData.consumption.total));
    $("#heating-current-daily-consumption td:nth-child(5)").text(price + 'Eur');
    
    // localStorage.setItem('heating-hpData', JSON.stringify(data.consumption));
}, 2189);

</script>