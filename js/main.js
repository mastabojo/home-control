/*
/ Main Javascript file
*/

function mainLoop() {

    setInterval(function() {

        // check interval for current weather data (in minutes)
        var checkPeriodWeatherCurrent = 10;
        // check interval for 5 day weather forecast (in hours)
        var checkPeriodWeatherForecast = 2;

        var currentTime = moment().format('H:mm:ss');
        var currentDate = moment().format('dddd, D.M.YYYY');
        var dayOfWeek = moment().day();
        var currentHour = parseInt(moment().format('H'));
        var currentMinute = parseInt(moment().format('m'));
        var currentSecond = parseInt(moment().format('s'));

        // Status pane - current time and date
        $('#span-time').text(currentTime);
        $('#span-date').text(currentDate);

        // check for current weather on checkPeriodWeatherCurrent offset by 7 minutes
        if((currentMinute - 7) % checkPeriodWeatherCurrent == 0 && currentSecond == 0) {
            console.log('Current weather checked on ' + currentTime);
            $.get("/jobs/getweatherdata.php?req=true", function(data) {
                var weatherData = JSON.parse(data);
                $("#img-icon-weather").attr("src", "/img/weather-icons/" + weatherData.weather_icon + ".svg");
                $("#span-temperature").html(Math.round(weatherData.temperature));
                $("#span-updated").html(moment.unix(weatherData.calc_time).format("D.M.YYYY H:mm:ss"));








                console.log(weatherData.weather_icon);
                console.log(moment.unix(weatherData.calc_time).format("D.M.YYYY H:mm:ss"));
            });
        }

        // check for current weather on checkPeriodWeatherCurrent offset by 3 minutes
        if((currentHour % checkPeriodWeatherForecast)  == 0 && currentMinute == 3 && currentSecond == 0) {
            console.log('Weather forecast checked on ' + currentTime);
        }

    }, 1000);
}

