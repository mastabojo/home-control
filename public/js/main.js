/*
/ Main Javascript file
*/

// check interval for current weather data (in minutes)
var checkPeriodWeatherCurrent = 20;

// check interval for 5 day weather forecast (in hours)
var checkPeriodWeatherForecast = 2;

// Do we operate shutters automatically
var shuttersTimerEnabled = true;

// default shutter opening and closing times
var shuttersUpTime = "6:21:00";
var shuttersDownTime = "18:30:00";

// Is this the first run of the loop (to draw things immediately)
var isfirstRun = true;

// Get info on moon phases
getMoonPhaseInfo();

function mainLoop() {

    setInterval(function() {

        moment.locale('sl');
        var currentTime = moment().format('H:mm:ss');
        var currentDate = moment().format('ddd, D.M.YYYY');
        var currentTimeShort = moment().format('H:mm');
        var currentDateShort = moment().format('dddd, D.M.YYYY');
        // var dayOfWeek = moment().day();
        var currentHour = parseInt(moment().format('H'));
        var currentMinute = parseInt(moment().format('m'));
        var currentSecond = parseInt(moment().format('s'));
        var dayNames = {'00' : "Ned", '01' : "Pon", '02' : "Tor", '03' : "Sre", '04' : "Čet", '05' : "Pet", '06' : "Sob"};

        // Status pane - current time and date
        $('#status-pane #span-time').text(currentTime);
        $('#span-date').text(currentDate);

        // every minute update main time and date display
        if(currentSecond == 0 || isfirstRun) {
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
            $.get("../api/getweather.php?type=forecast", function(data) {
                var weatherData = JSON.parse(data);
                // var dayNames = {'00' : "Ned", '01' : "Pon", '02' : "Tor", '03' : "Sre", '04' : "Čet", '05' : "Pet", '06' : "Sob"};
                for(day in weatherData) {
                    var shortDayName = dayNames[day];
                    $("#fcast-day-" + day + " .short-day-name").html(shortDayName);
                    $("#fcast-day-" + day + " .temperature")
                    .html(Math.round(weatherData[day]['temperature_day']) + '&deg; (' + Math.round(weatherData[day]['temperature_night']) + '&deg;)');
                }
            });
        }

        // get heat pump consumption info from local storage and display it somewhere
        if(currentSecond % 5 == 0 || isfirstRun) {

            // get heat pump data
            $.get("api/getHpConsumptionData.php", function(data) {
                hpData = JSON.parse(data);
            });

            var price = hpData.daily_consumption.highTariffCost + hpData.daily_consumption.lowTariffCost;
            $("#heating-current-daily-consumption td:nth-child(2)").text(hpData.daily_consumption.lowTariff);
            $("#heating-current-daily-consumption td:nth-child(3)").text(hpData.daily_consumption.highTariff);
            $("#heating-current-daily-consumption td:nth-child(4)").text((hpData.daily_consumption.total));
            $("#heating-current-daily-consumption td:nth-child(5)").text(price.toFixed(2) + "€");
            $("#heating-total-daily-consumption-value-big").text((hpData.daily_consumption.total));

            $("#heating-current-monthly-consumption td:nth-child(2)").text(hpData.monthly_consumption.lowTariff);
            $("#heating-current-monthly-consumption td:nth-child(3)").text(hpData.monthly_consumption.highTariff);
            $("#heating-current-monthly-consumption td:nth-child(4)").text(hpData.monthly_consumption.total);
            $("#heating-current-monthly-consumption td:nth-child(5)").text(hpData.monthly_consumption.totalCost + "€");
            
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
                    break;
            }

            var options = {
                chart: {
                    type: 'bar',
                    fontFamily: 'Helvetica, Arial, sans-serif',
                    // Text color
                    foreColor: '#333',
                    width: '600px',
                    height: '200px',
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: false
                    }
                },
                plotOptions: {
                    bar: {
                        columnWidth: '90%',
                        dataLabels: 'top',
                        colors: {
                            // Ranges just for example
                            ranges: [
                                {from: 0, to: 100, color: barColors},
                                {from: 100, to: 600, color: barColors},
                                {from: 600, to: 1200, color: barColors}
                            ]
                        }
                    }
                },
                series: [{
                    name: 'consumption',
                    // data: [30,40,35,55,49,40,30,50,65,30,40,35,50,49,60,70,20,65,30,40,35,50,90,60,70,45,65,45,62,55]
                    data: chartData
                }],
                xaxis: {
                    categories: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30]
                },
                dataLabels: {
                    enabled: false,
                    offsetY: 0,
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Helvetica, Arial, sans-serif',
                        colors: ['blue']
                    }
                }
            }

            // render chart every minute
            // if(currentSecond % 50 == 0 || isfirstRun) {
                console.log(currentSecond);
                var chart = new ApexCharts(document.querySelector("#hpchart"), options);
                chart.render();
            // }

            /*
            // Chart.js chart currently disabled

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
                    title: {text: chartTitle, display: true},
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
                            // barPercentage: 1.0,
                            gridLines: {offsetGridLines: true}
                        }]
                    }
                }
            });
            */
        }

        // do these task every n seconds
        if(currentSecond % 5 == 0) {

            // CPU data
            $.get("../api/getCpuData.php", function(data) {
                var tempObj = JSON.parse(data);
                $('#span-cpu-data').html(tempObj.cpu_load + "% " + tempObj.cpu_temperature + '&deg; (' + tempObj.min_cpu_temperature + '&deg;/' + tempObj.max_cpu_temperature + '&deg;)');
            });

            // update shutters open and close times with programmed or sunrise/sunset times from local storage if any exist
            if(localStorage.getItem('shuttersUpTime') != null && localStorage.getItem('shuttersDownTime') != null) {
                shuttersUpTime = localStorage.getItem('shuttersUpTime');
                shuttersDownTime = localStorage.getItem('shuttersDownTime');
            } else if(localStorage.getItem('sunrise') != null && localStorage.getItem('sunrise') != null) {
                shuttersUpTime =  localStorage.getItem('sunrise');
                shuttersDownTime = localStorage.getItem('sunset');
                // Roll shutters down some 20 minutes after sunset
                shuttersDownTime = moment(shuttersDownTime, "H:mm:ss").add(20, 'minutes').format("H:mm:ss");
            }
            $("span#home-shutters-auto-up").text(moment(shuttersUpTime, "H:mm:ss").format("H:mm"));
            $("span#home-shutters-auto-down").text(moment(shuttersDownTime, "H:mm:ss").format("H:mm"));
        }

        // open / close shutters on set times
        if(shuttersTimerEnabled && String(currentTime) == shuttersUpTime) {
            $.post('../api/doshutters.php', {"action": "shutter-auto-both-up", "timeDivider": 1});
        }
        if(shuttersTimerEnabled && String(currentTime) == shuttersDownTime) {
            $.post('../api/doshutters.php', {"action": "shutter-auto-both-down", "timeDivider": 1});
        }

        // once a day arround 2 am when all is quiet update the local storage from the database (sunrise time, sunset time)
        if(String(currentTime) == "2:00:05") {
            updateLocalStorage(function(data) {
                storageData = JSON.parse(data);
                localStorage.setItem('sunrise', moment.unix(storageData.sunrise).format("H:mm" + ":00"));
                localStorage.setItem('sunset', moment.unix(storageData.sunset).format("H:mm") + ":00");
            });
        }

        // Twice a day update moon phase info
        if(String(currentTime) == "3:00:15" || String(currentTime) == "15:00:15") {
            getMoonPhaseInfo();
        }
        

        isfirstRun = false;

    }, 1000);
}

// Function to be called to update local storage with data from database
function updateLocalStorage(callBack) {
    $.ajax({
        url: "../api/updateLocalStorage.php",
        type: "POST",
        dataType: "text",
        cache: false,
        success: function (data) {
          callBack(data);
        }
    });
}

// set shutters time divider 
// 1 - shutters travel whole way
// 2 - shutters travel half way
$("#blinds-pane img.shutter-divider").on("click", function() {
    // Divider to be used
    var clickedDivider = $(this).attr("src").split("-")[4];
    $(".shutter-divider").each(function(i) {
        tempArr = $(this).attr("src").split("-");
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

function getMoonPhaseInfo() {
    var iconPath = "/img/lunar-phase-icons/dark/";
    $.get("../api/getMoonPhaseInfo.php", function(data) {
        var moonPhaseData = JSON.parse(data);
        $("#moon-phase-icon").attr("src", iconPath + "moon-" + moonPhaseData.phaseID + ".svg");
        // $("#span-when-full-moon").text(moonPhaseData.daysUntilNextFullMoon);
        // $("#span-when-new-moon").text(moonPhaseData.daysUntilNextNewMoon);
        if(moonPhaseData.nextFullMoonDateTS < moonPhaseData.nextNewMoonDateTS) {
            $("#img-moon-phase-icon-1").attr("src", iconPath + "moon-4.svg");
            $("#span-moon-phase-info-1").text(moment.unix(moonPhaseData.nextFullMoonDateTS).format("D.M.") + " (" + moonPhaseData.daysUntilNextFullMoon + ")");
            $("#img-moon-phase-icon-2").attr("src", iconPath + "moon-0.svg");
            $("#span-moon-phase-info-2").text(moment.unix(moonPhaseData.nextNewMoonDateTS).format("D.M.") + " (" + moonPhaseData.daysUntilNextNewMoon + ")");
        } else {
            $("#img-moon-phase-icon-1").attr("src", iconPath + "moon-0.svg");
            $("#span-moon-phase-info-1").text(moment.unix(moonPhaseData.nextNewMoonDateTS).format("D.M.") + " (" + moonPhaseData.daysUntilNextNewMoon + ")");
            $("#img-moon-phase-icon-2").attr("src", iconPath + "moon-4.svg");
            $("#span-moon-phase-info-2").text(moment.unix(moonPhaseData.nextFullMoonDateTS).format("D.M.") + " (" + moonPhaseData.daysUntilNextFullMoon + ")");
        }
    });

    
}

// Weather pane - load weather display from selected provider into iframe
$(".weather-display-icons").on("click", function(e) {
    var weatherProviders = {
        "arso": "http://vreme.arso.gov.si/napoved/Ljubljana/graf#zoom=50",
        "open": "https://openweathermap.org/weathermap?basemap=map&cities=true&layer=temperature&lat=46.0732&lon=14.7574&zoom=10",
        "windy": "https://embed.windy.com/embed2.html?lat=46.048&lon=14.505&zoom=11&level=surface&overlay=temp&menu=&message=true&marker=&calendar=&pressure=&type=map&location=coordinates&detail=&detailLat=50.090&detailLon=14.420&metricWind=m%2Fs&metricTemp=%C2%B0C&radarRange=-1",
        "accu": "https://www.accuweather.com/en/si/ljubljana/299198/daily-weather-forecast/299198"
    };
    var selectedProvider = e.target.id;
    // set src attribute of iframe
    $("#weather-display").attr("src", weatherProviders[selectedProvider]);
});

// System pane - 
$(".system-tab img").on("click", function() {
    var cmd = $(this).attr("id");
    if(cmd == "refresh-browser") {
        window.location.reload(true);
    } else {
        var data = {"cmd": $(this).attr("id")};
        $.post('../api/system_commands.php', data);       
    }
});