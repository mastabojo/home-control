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
<td>Trenutna dnevna poraba</td><td></td><td></td><td></td><td></td>
</tr>
<!--
<tr>
<td>Mesečna poraba</td><td></td><td></td><td></td><td></td>
</tr>
-->
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

    var price = hpData.consumption.highTariffCost + hpData.consumption.lowTariffCost;
    $("#heating-current-daily-consumption td:nth-child(2)").text(hpData.consumption.lowTariff);
    $("#heating-current-daily-consumption td:nth-child(3)").text(hpData.consumption.highTariff);
    $("#heating-current-daily-consumption td:nth-child(4)").text((hpData.consumption.total));
    $("#heating-current-daily-consumption td:nth-child(5)").text(price + '€');
    $("#heating-total-daily-consumption-value-big").text((hpData.consumption.total));
       
    // localStorage.setItem('heating-hpData', JSON.stringify(hpData.consumption));

    // Chart data
    // Refresh chart every 10 min and 10 seconds (so the data is read)
    var currentMinute = parseInt(moment().format('m'));
    var currentSecond = parseInt(moment().format('s'));
    // if((currentMinute % 2 == 0) && (currentSecond == 10)) {
    if(1) {
        var lowTariffColor = 'rgba(255, 255, 255, 0.3)';
        var highTariffColor = 'rgba(255, 255, 255, 0.6)';
        var barColors = [];
        for(var h = 0; h < 24; h++) {
            barColors[h] = (h < (hpData.high_tariff_boundaries[0] - 1) || h >= (hpData.high_tariff_boundaries[1] - 1)) ? lowTariffColor : highTariffColor;
        }
        var ctx = document.getElementById('hpchart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24'],
                datasets: [{
                    label: 'KWh',
                    // data: hpData.hourly_data,
                    data: hpData.hourly_data_diffs,
                    backgroundColor: barColors,
                    /*
                    backgroundColor: function(context) {
                        var index = context.dataIndex;
                        return (index >= 6 && index < 22) ? 'rgb(204,255,238)' : 'rgb(255,255,204)';
                    },
                    */
                    borderWidth: 0
                }]
            },
            options: {
                title: {display: false},
                legend: {display: false},
                animation: false,
                scales: {
                    yAxes: [{
                        ticks: {beginAtZero: true}
                    }],
                    xAxes: [{
                        barThickness: 'flex',
                        barPercentage: 1.0,
                        gridLines: {offsetGridLines: true}
                    }]
                }
            }
        });
    }
}, interval);

</script>