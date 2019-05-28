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
Total consumption
<span id="heating-total-daily-consumption-value">3.60</span><br><span id="heating-total-daily-consumption-unit">KWh</span>
</div><!-- .col -->

</div><!-- .row -->

<!-- Row 2 - consumption chart -->
<div class="row no-gutters align-items-end">

<!-- col 1 -->
<div class="col">

<canvas id="hpchart" width="766" height="180" style="border: 1px solid silver;"></canvas>

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

hpChartOptions = {
    scales: {
        yAxes: [{
            ticks: {beginAtZero: true}
        }],
        xAxes: [{
            barPercentage: 1.0,
            gridLines: {offsetGridLines: true}
        }]
    }
};

var interval = 2000;
var chartRefreshInterval = 4 * interval;
var counter = 0;

// moment.locale('sl');

setInterval(function() {

    // get heat pump data
    $.get("api/getHpConsumptionData.php", function(data) {
        hpData = JSON.parse(data);
        
    });

    var price = hpData.consumption.highTariffCost + hpData.consumption.lowTariffCost;
    $("#heating-current-daily-consumption td:nth-child(2)").text(hpData.consumption.lowTariff);
    $("#heating-current-daily-consumption td:nth-child(3)").text(hpData.consumption.highTariff);
    $("#heating-current-daily-consumption td:nth-child(4)").text((hpData.consumption.total));
    $("#heating-current-daily-consumption td:nth-child(5)").text(price + '€');
    
    // localStorage.setItem('heating-hpData', JSON.stringify(hpData.consumption));

    // Chart data
    // Refresh chart every 10 min and 10 seconds (so the data is read)
    var currentMinute = parseInt(moment().format('m'));
    var currentSecond = parseInt(moment().format('s'));
    if((currentMinute % 10 == 0) && (currentSecond == 10) {
    // if(1) {
        var ctx = document.getElementById('hpchart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24'],
                datasets: [{
                    label: 'KWh',
                    data: hpData.hourly_data,
                    // data: tempData,
                    backgroundColor: function(context) {
                        var index = context.dataIndex;
                        return (index >= 6 && index < 22) ? 'rgba(182, 99, 58, 1)' : 'rgba(78, 99, 132, 1)';
                    },
                    borderWidth: 0
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {beginAtZero: true}
                    }],
                    xAxes: [{
                        barPercentage: 1.0,
                        gridLines: {offsetGridLines: true}
                    }]
                }
            }
        });
    }
}, interval);

setInterval(function() {




}, 8200);

</script>