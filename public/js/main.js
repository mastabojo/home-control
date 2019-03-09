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

        moment.locale('sl');
        var currentTime = moment().format('H:mm:ss');
        var currentDate = moment().format('dddd, D.M.YYYY');
        var currentTimeShort = moment().format('H:mm');
        var currentDateShort = moment().format('dddd, D.M.YYYY');
        // var dayOfWeek = moment().day();
        var currentHour = parseInt(moment().format('H'));
        var currentMinute = parseInt(moment().format('m'));
        var currentSecond = parseInt(moment().format('s'));

        // Status pane - current time and date
        $('#status-pane #span-time').text(currentTime);
        $('#span-date').text(currentDate);

        // every minute update main time and date display
        if(currentSecond == 0) {
            // Heat pump pane - TEMPORARY: current time and date
            $('#heat-pump-pane #span-main-time').text(currentTimeShort);
            $('#heat-pump-pane #span-main-date').text(currentDateShort);
        }

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

        // do this task every x minutes on tenth second
        // if(currentMinute % checkPeriodCommonTasks == 0 && currentSecond == 10) {
        if(currentSecond % 60 == 0) {

            // Heat pump hourly chart 
            // getHeatPumpchart();
        }

        // do these task every n seconds
        if(currentSecond % 5 == 0) {

            // CPU data
            $.get("../api/getCpuData.php", function(data) {
                var tempObj = JSON.parse(data);
                $('#span-cpu-data').html(tempObj.cpu_load + "% " + tempObj.cpu_temperature + '&deg; (' + tempObj.min_cpu_temperature + '&deg;/' + tempObj.max_cpu_temperature + '&deg;)');
            });
        }

        // Automatically open and close shutters at preset time
        var shuttersUpTime = "6:24:00";
        var shuttersDownTime = "18:10:00";
        if(String(currentTime) == shuttersUpTime) {
            $.post('../api/doshutters.php', {"action": "shutter-auto-both-up", "timeDivider": 1});
        }
        if(String(currentTime) == shuttersDownTime) {
            $.post('../api/doshutters.php', {"action": "shutter-auto-both-down", "timeDivider": 1});
        }

    }, 1000);
}

// set shutters time divider 
// 1 - shutters travel whole way
// 2 - shutters travel half way
$("#blinds-pane img.shutter-divider").on("click", function() {
    // Divider to be used
    var clickedDivider = $(this).attr("src").split("-")[4];
    $(".shutter-divider").each(function(i) {
        tempArr = $(this).attr("src").split("-");
        // console.log(tempArr);
        tempDivider = tempArr[4];
        if(tempDivider == clickedDivider) {
            tempArr[5] = "on.svg";
        } else {
            tempArr[5] = "off.svg";
        }
        $(this).attr("src", tempArr.join("-"));
    });
});

// operate shutters (blinds)
$("#blinds-pane img.shutter-action").on("click", function() {
    var clicked = $(this);
    attrSrcOff = $(this).attr("src");
    attrSrcOn = attrSrcOff.replace("-off", "-on");
    // Get time divider
    timeDivider = 1;
    $(".shutter-divider").each(function(i) {
        tempArr = $(this).attr("src").split("-");
        if(tempArr[5].split(".")[0] == "on") {
            timeDivider = tempArr[4];
        }
    });
    // simulate on and off
    clicked.attr("src", attrSrcOn);
    setTimeout(function() {$(clicked).attr("src", attrSrcOff);}, 1000);
    // Send post data
    var data = {"action": $(this).attr("id"), "timeDivider": timeDivider};
    $.post('../api/doshutters.php', data);
});

function getHeatPumpchart() {

    // get the daily data
    $.get("../api/getHpChartData.php", function(data) {
        // console.log(data);
    });

    // var ctx = document.getElementById("hp-daily").getContext('2d');
    // var myChart = new Chart(ctx, {
        // type: 'bar',
        // data: data,
        // options: options
    // });

}

