<?php

?>
<!-- Row 1 - consumption table -->
<div class="row no-gutters align-items-end" style="height: 180px;">

<!-- col 1 -->
<div class="col-9">

<table class="table table-sm">
<thead>
<tr>
<th>Poraba (KWh)</th><th>MT</th><th>VT</th><th>Skupaj</th><th>Cena (Eur)</th>
</tr>
</thead>

<tbody>
<tr id="heating-current-daily-consumption">
<td>Dnevna poraba</td><td></td><td></td><td></td><td></td>
</tr>
<tr id="heating-current-monthly-consumption">
<td>Mesečna poraba</td><td></td><td></td><td></td><td></td>
</tr>
<!-- Empty row -->
<tr>
<td></td><td></td><td></td><td></td><td></td>
</tr>
</tbody>

</table>

</div><!-- .col -->

<div class="col text-center">
Dnevna poraba<br>
<span id="heating-total-daily-consumption-value-big"></span><span id="heating-total-daily-consumption-unit">KWh</span>
</div><!-- .col -->

</div><!-- .row -->

<!-- Row 2 - consumption chart -->
<div class="row no-gutters align-items-end">

<!-- col 1 -->
<div class="col">

<canvas id="hpchart" width="766" height="180"></canvas>

</div><!-- .col -->

</div><!-- .row -->

<script src="js/moment.min.js"></script>

<script>

var interval = 4000;
setInterval(function() {

    // get heat pump data
    $.get("api/getHpConsumptionData.php", function(data) {
        hpData = JSON.parse(data);
    });
    // console.log(hpData);

    var price = hpData.daily_consumption.highTariffCost + hpData.daily_consumption.lowTariffCost;
    $("#heating-current-daily-consumption td:nth-child(2)").text(hpData.daily_consumption.lowTariff);
    $("#heating-current-daily-consumption td:nth-child(3)").text(hpData.daily_consumption.highTariff);
    $("#heating-current-daily-consumption td:nth-child(4)").text((hpData.daily_consumption.total));
    $("#heating-current-daily-consumption td:nth-child(5)").text(price.toFixed(2) + '€');
    $("#heating-total-daily-consumption-value-big").text((hpData.daily_consumption.total));
       
    // localStorage.setItem('heating-hpData', JSON.stringify(hpData.consumption));

    var lowTariffColor = 'rgba(255, 255, 255, 0.3)';
    var highTariffColor = 'rgba(255, 255, 255, 0.6)';
    var dailyBarColors = [];
    var dailyLabels = [];
    for(var h = 0; h < 24; h++) {
        dailyBarColors[h] = (h < (hpData.high_tariff_boundaries[0] - 1) || h >= (hpData.high_tariff_boundaries[1] - 1)) ? lowTariffColor : highTariffColor;
        dailyLabels[h] = h + 1;
    }
    var monthlyLabels = [];
    for(var m = 0; m < 31; m++) {
        monthlyLabels[m] = m + 1;
    }

    var chartType = 'daily';
    // Define options for supported chart types
    switch(chartType) {
        case 'daily':
            var chartTitle = "Dnevna poraba";
            var chartData = hpData.hourly_data_diffs;
            // var chartData = hpData.hourly_data;
            var chartLabels = dailyLabels;
            var barColors = dailyBarColors;
            var chartStacked = false;
            break;
        case 'monthly':
            var chartTitle = "Mesečna poraba";
            var chartData = hpData.monthly_consumption;
            var chartLabels = monthlyLabels;
            var chartStacked = true;
    }

    // Chart
    var ctx = document.getElementById('hpchart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                data: chartData,
                backgroundColor: barColors,
                borderWidth: 0
            }]
        },
        options: {
            title: {text: chartTitle, display: false},
            legend: {display: false},
            animation: false,
            scales: {
                yAxes: [{
                    stacked: chartStacked,
                    ticks: {beginAtZero: true}
                }],
                xAxes: [{
                    stacked: chartStacked,
                    barThickness: 'flex',
                    barPercentage: 1.0,
                    gridLines: {offsetGridLines: true}
                }]
            }
        }
    });
}, interval);

</script>