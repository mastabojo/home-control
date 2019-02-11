/*
/ Main Javascript file
*/

function mainLoop() {

    setInterval(function() {

        // check interval for current weather data (in minutes)
        var checkPeriodWeatherCurrent = 20;
        
        // check interval for 5 day weather forecast (in hours)
        var checkPeriodWeatherForecast = 2;

        // check interval for common tasks (in minutes)
        var checkPeriodCommonTasks = 2

        var currentTime = moment().format('H:mm:ss');
        var currentDate = moment().format('dddd, D.M.YYYY');
        // var dayOfWeek = moment().day();
        var currentHour = parseInt(moment().format('H'));
        var currentMinute = parseInt(moment().format('m'));
        var currentSecond = parseInt(moment().format('s'));

        // Status pane - current time and date
        $('#status-pane #span-time').text(currentTime);
        $('#span-date').text(currentDate);

        // check for current weather on checkPeriodWeatherCurrent offset by 7 minutes
        if((currentMinute - 7) % checkPeriodWeatherCurrent == 0 && currentSecond == 0) {
            // read from weather provider API
            $.get("../api/getweather.php?type=current", function(data) {
                var weatherData = JSON.parse(data);
                $("#img-icon-weather").attr("src", "/img/weather-icons/" + weatherData.weather_icon + ".svg");
                $("#span-temperature").html(Math.round(weatherData.temperature) + '&deg;');
                $("#span-updated").html(moment.unix(weatherData.calc_time).format("D.M.YY H:mm"));
            });
        }

        // check for weather forecast on checkPeriodWeatherForecast offset by 3 minutes
        if((currentHour % checkPeriodWeatherForecast)  == 0 && currentMinute == 3 && currentSecond == 0) {
            console.log('Weather forecast checked on ' + currentTime);
            $.get("../api/getweather.php?type=forecast", function(data) {
                var weatherData = JSON.parse(data);
                // console.log(weatherData);
                // console.log(weatherData[Object.keys(weatherData)[0]]);

                var dayNames = {'00' : "Ned", '01' : "Pon", '02' : "Tor", '03' : "Sre", '04' : "Čet", '05' : "Pet", '06' : "Sob"};

                for(day in weatherData) {
                    var shortDayName = dayNames[day];
                    $("#fcast-day-" + day + " .short-day-name").html(shortDayName);
                    $("#fcast-day-" + day + " .temperature")
                    .html(Math.round(weatherData[day]['temperature_day']) + '&deg; (' + Math.round(weatherData[day]['temperature_night']) + '&deg;)');
                }
            });
        }

        // do these common task every x minutes on tenth second
        // if(currentMinute % checkPeriodCommonTasks == 0 && currentSecond == 10) {
        if(currentSecond) {

            // Heat pump hourly chart 
            // getHeatPumpchart();

            // CPU temperature
            $.get("../api/getCpuTemperature.php", function(data) {
                var tempObj = JSON.parse(data);
                $('#span-cpu-temperature').html(tempObj.cpu_temperature + '&deg; (' + tempObj.min_cpu_temperature + '&deg;/' + tempObj.max_cpu_temperature + '&deg;)');
            });
        }



    }, 1000);
}

// operate shutters (blinds)
$("#blinds-pane img").on("click", function() {
    
    var data = {"action": $(this).attr("id")};
    $.post('../api/doshutters.php', data, function() {
        console.log(data);
    });

});

function getHeatPumpchart() {

    // get the daily data
    $.get("../api/getHpChartData.php", function(data) {
        console.log(data);
    });
    console.log("THE CHART");

    var ctx = document.getElementById("hp-daily").getContext('2d');
    var myChart = new Chart(ctx, {
        // type: 'bar',
        // data: data,
        // options: options
    });

}
