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

// Date for displaying heat pump daily/monthly/yearly chart [YYYY-MM-DD] (default current date)
var hpChartDisplayDate = moment().format('YYYY-MM-DD');

// Period for displaying heat pump chart (daily/monthly/yearly) (default daily)
var hpChartDisplayPeriod = 'daily';

// Should reload be forced on this run of the loop (to draw things immediately)
var forceReload = true;

// Heat pump chart options
var hpChartOptions = {
    title: {
        text: 'KWh',
        align: 'right',
        style: {
            fontSize:  '14px',
            color:  "rgba(255, 255, 255, 0.6)"
          },
    },
    chart: {
        type: 'bar',
        width: '766px',
        height: '180px',
        toolbar: {
            show: false
        },
        animations: {
            enabled: false
        },
        fontFamily: 'Ubuntu-Light, Helvetica, sans-serif',
        background: "rgba(255, 255, 255, 0.05)"
    },
    colors: ["rgba(255, 255, 255, 0.6)"],
    plotOptions: {
        bar: {
            columnWidth: '90%',
        }
    },
    series: [{
        name: 'consumption',
        data: []
    }],
    xaxis: {
        categories: [],
        labels: {
            show: true,
            style: {
                colors: "rgba(255, 255, 255, 0.6)"
            }
        }
    },
    yaxis: {
        show: true,
        labels: {
            show: true,
            style: {
                color: "rgba(255, 255, 255, 0.6)"
            }
        }
    },
    dataLabels: {
        enabled: false
    },
    grid: {
        show: true,
        yaxis: {
            lines: { 
                show: false,
            }
        }
    }
}

// Initialize heat pump chart
var hpChart = new ApexCharts(document.querySelector("#hpchart"), hpChartOptions);
hpChart.render();

function mainLoop() {

    setInterval(function() {

        moment.locale('sl');
        var currentTime = moment().format('H:mm:ss');
        var currentDate = moment().format('ddd, D.M.YYYY');
        var currentTimeShort = moment().format('H:mm');
        var currentDateShort = moment().format('D.M.YYYY');
        // var dayOfWeek = moment().day();
        var currentHour = parseInt(moment().format('H'));
        var currentMinute = parseInt(moment().format('m'));
        var currentSecond = parseInt(moment().format('s'));
        var dayNames = {'00' : "Ned", '01' : "Pon", '02' : "Tor", '03' : "Sre", '04' : "Čet", '05' : "Pet", '06' : "Sob"};

        // Status pane - current time and date
        $('#status-pane #span-time').text(currentTime);
        $('#span-date').text(currentDate);

        // every minute update main time and date display
        if(currentSecond == 0 || forceReload) {
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

        // Read temperature and humidity
        if(currentSecond % 3 == 0 || forceReload) {
            $.get("../api/getTempAndHumidity.php?source=db", function(data) {
                data = JSON.parse(data);

                $("#temperature-value").html(data.temperature + '&deg;');
                $("#humidity-value").html(data.humidity + '&#37;');
                $("#temp-and-humidity-last-updated").html(data.read_time);

                $("#environment-temeperature-01 > svg > path.gauge-needle").css("transform", "rotate(" + getGaugeRotationAngle(data.temperature, [-5, 35], 240) + "deg)");
                $("#environment-temeperature-01 > svg > text.gauge-value-display-text").text(data.temperature);

                $("#environment-humidity-01 > svg > path.gauge-needle").css("transform", "rotate(" + getGaugeRotationAngle(data.humidity, [0, 100], 240) + "deg)");
                $("#environment-humidity-01 > svg > text.gauge-value-display-text").text(data.humidity);

                $("#environment-temp-and-humidity-last-updated").html(data.read_time);

                // Notify if temperature and humidity were not refreshed for longer time (i.e. 30 min)
                if(moment().diff(moment(data.read_time_iso), 'minutes') > 30) {
                    $("#temp-and-humidity-last-updated").css("color", "#EB4E4E");
                }
            });
        }

        // get heat pump consumption info from local storage and display it somewhere
        if(currentSecond % 50 == 0 || forceReload) {

            // get heat pump data
            $.post(
                "api/getHpConsumptionData.php", 
                {dispDate: hpChartDisplayDate, dispPeriod: hpChartDisplayPeriod},
                function(data) {
                hpData = JSON.parse(data);
            });

            var price = hpData.daily_consumption.highTariffCost + hpData.daily_consumption.lowTariffCost;
            $("#heating-current-daily-consumption td:nth-child(2)").text(hpData.daily_consumption.lowTariff.toFixed(2));
            $("#heating-current-daily-consumption td:nth-child(3)").text(hpData.daily_consumption.highTariff.toFixed(2));
            $("#heating-current-daily-consumption td:nth-child(4)").text((hpData.daily_consumption.total).toFixed(2));
            $("#heating-current-daily-consumption td:nth-child(5)").text(price.toFixed(2) + "€");
            $("#heating-total-daily-consumption-value-big").text(hpData.daily_consumption.total.toFixed(2));

            $("#heating-current-monthly-consumption td:nth-child(2)").text(hpData.monthly_consumption.lowTariff.toFixed(2));
            $("#heating-current-monthly-consumption td:nth-child(3)").text(hpData.monthly_consumption.highTariff.toFixed(2));
            $("#heating-current-monthly-consumption td:nth-child(4)").text(hpData.monthly_consumption.total.toFixed(2));
            $("#heating-current-monthly-consumption td:nth-child(5)").text(hpData.monthly_consumption.totalCost.toFixed(2) + "€");
            
             var chartType = 'daily';
            // Define options for supported chart types
            switch(chartType) {
                case 'daily':
                    var chartStacked = false;
                    var chartTitle = moment(hpChartDisplayDate).format('D.M.YYYY');
                    var chartLabels = [];
                    for(var h = 0; h < 24; h++) {
                        // dailybarColorsArr[h] = (h < (hpData.high_tariff_boundaries[0] - 1) || h >= (hpData.high_tariff_boundaries[1] - 1)) ? lowTariffColor : highTariffColor;
                        chartLabels[h] = h + 1;
                    }
                    var chartData = hpData.hourly_data_diffs;
                    var barColorsArr = ['rgba(255, 255, 255, 0.6)'];
                    break;
                case 'monthly':
                    var chartStacked = true;
                    var chartTitle = moment(hpChartDisplayDate).format('M.YYYY');
                    var chartLabels = [];
                    for(var m = 0; m < moment().daysInMonth(); m++) {
                        chartLabels[m] = m + 1;
                    }
                    var chartData = hpData.monthly_consumption;
                    var barColorsArr = ['rgba(255, 255, 255, 0.6)', 'rgba(255, 255, 255, 0.3)'];                   
                    break;
            }

            // Update heat pump chart
            hpChart.updateOptions({
                chart: {stacked: chartStacked},
                title: {text: chartTitle},
                colors: barColorsArr,
                series: [{data: chartData}],
                xaxis: {categories: chartLabels}
            });
        }

        // do these task every n seconds
        if(currentSecond % 50 == 0 || forceReload) {

            // CPU data
            $.get("../api/getCpuData.php", function(data) {
                var tempObj = JSON.parse(data);
                $('#span-cpu-data').html(tempObj.cpu_load + "% " + tempObj.cpu_temperature + '&deg; (' + tempObj.min_cpu_temperature + '&deg;/' + tempObj.max_cpu_temperature + '&deg;)');
            });

            // update shutters open and close times with programmed or sunrise/sunset times from local storage if any exist
            if(localStorage.getItem('shuttersUpTime') != null && localStorage.getItem('shuttersDownTime') != null) {
                shuttersUpTime = localStorage.getItem('shuttersUpTime');
                shuttersDownTime = localStorage.getItem('shuttersDownTime');
            } else if(localStorage.getItem('sunrise') != null && localStorage.getItem('sunset') != null) {
                shuttersUpTime = localStorage.getItem('sunrise');
                // Roll shutters up 20 min before sunset
                shuttersUpTime = moment(shuttersUpTime, "H:mm:ss").subtract(20, 'minutes').format("H:mm:ss");
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
        if(String(currentTime) == "3:00:15" || String(currentTime) == "15:00:15" || forceReload) {
            getMoonPhaseInfo();
        }

        // Get IP address
        if(currentSecond % 56 == 0 || forceReload) {
            $.post(
                '../api/system_commands.php', 
                {"cmd": "get-ip"}, 
                function(data) {
                $(".system-tab #ip-address").html(data);
            });
        }

        // Check connectivity
        if((currentMinute % 3 == 0 && currentSecond % 43 == 0) || forceReload) {
            $.post(
                '../api/system_commands.php', 
                {"cmd": "test-connection"}, 
                function(data) {
                    if(data == '0') {
                        connectionStatus = 'FAIL';
                    } else if(data == '1') {
                        connectionStatus = 'OK';
                    } else {
                        connectionStatus = 'UNKNOWN';
                    }
                $(".system-tab #connection-status").html(connectionStatus);
            });
        }

        // Get uptime
        if((currentMinute % 2 == 0 && currentSecond % 32 == 0) || forceReload) {
            $.post(
                '../api/system_commands.php', 
                {"cmd": "uptime"}, 
                function(data) {
                    data = JSON.parse(data);
                    $(".system-tab #uptime-years").html(data.years != undefined && data.years > 0 ? data.years : '0');
                    $(".system-tab #uptime-months").html(data.months != undefined && data.months > 0 ? data.months : '0');
                    $(".system-tab #uptime-days").html(data.days != undefined && data.days > 0 ? data.days : '0');
                    $(".system-tab #uptime-hours").html(data.hours != undefined && data.hours > 0 ? data.hours : '0');
                    $(".system-tab #uptime-minutes").html(data.minutes != undefined && data.minutes > 0 ? data.minutes : '0');
            });
        }
        

        forceReload = false;

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

// Manually refresh temperature and humidity (tap the temp and humidity display area)
$(".temp-and-humidity").on("click", function() {
    $.get("../api/getTempAndHumidity.php?source=web", function(data) {
        data = JSON.parse(data);
        $("#temperature-value").html(data.temperature + '&deg;');
        $("#humidity-value").html(data.humidity + '&#37;');
        $("#temp-and-humidity-last-updated").html(data.read_time);
        // Notify if temperature and humidity were not refreshed for longer time (i.e. 30 min)
        if(moment().diff(moment(data.read_time_iso), 'minutes') > 30) {
            $("#temp-and-humidity-last-updated").css("color", "#EB4E4E");
        } else {
            // Temporary: green color indicates that there was a gap in temperture and humdity readings
            $("#temp-and-humidity-last-updated").css("color", "#90F08B");
        }
    });
})

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

// Heating pane - heat pump chart period selection
$("#hpchart-period-select button.btn-period-fixed").on("click", function() {
    $(this).siblings(".btn-period-fixed").removeClass("active");
    $(this).addClass("active");
});

// Heating pane - heat pump chart period shift forward or rewind one unit
$("#hpchart-period-select button.btn-period-shift").on("click", function() {
    diff = $(this).hasClass("shift-back") ? -1 : 1;
    period = $("#hpchart-period-select").find(".btn-period-fixed.active").prop("name");
    switch(period) {
        case 'daily': hpChartDisplayDate = moment(hpChartDisplayDate).add(diff, "days").format("YYYY-MM-DD"); break;
        case 'monthly': hpChartDisplayDate = moment(hpChartDisplayDate).add(diff, "months").format("YYYY-MM-DD"); break;
        case 'yearly': hpChartDisplayDate = moment(hpChartDisplayDate).add(diff, "years").format("YYYY-MM-DD"); break;
    }
    forceReload = true;
});

// On clicking on day buttons uncheck other buttons (not necessary on month and year selectors)
$("#modalHpChartPeriod #day-selector .btn-group label").on("click", function() {
    // Uncheck all buttons
    $.each($("#day-selector").find("label.btn"), function() {
        $(this).prop("checked", false);
        $(this).removeClass("active");
    })
});

$("#modalHpChartPeriod .modal-footer button#hpchart-period-ok").on("click", function() {
    var selectedDay = $("#modalHpChartPeriod #day-selector .btn-group input:checked").val();
    var selectedMonth = $("#modalHpChartPeriod #month-selector .btn-group input:checked").val();
    var selectedYear = $("#modalHpChartPeriod #year-selector .btn-group input:checked").val();
    hpChartDisplayDate = selectedYear + "-" + selectedMonth + "-" + selectedDay;
    // Hide modal dialog
    $('#modalHpChartPeriod').modal('hide');
    forceReload = true;
});

// System pane - 
$(".system-tab img").on("click", function() {
    var cmd = $(this).attr("id");
    if(cmd == "refresh-browser") {
        window.location = window.location.href+'?eraseCache=true';
        window.location.reload(true);
    } else {
        var data = {"cmd": $(this).attr("id")};
        $.post('../api/system_commands.php', data);       
    }
});

/*
if($gaugeValue < $gauge1StartAndEndValues[0]) {
    $rotationAngleFromStart = -12;
} else if($gaugeValue > $gauge1StartAndEndValues[1]) {
    $rotationAngleFromStart = $gagugeBarAngle + 12;
} else {
    $rotationAngleFromStart = ($gagugeBarAngle) * (($gaugeValue - $gauge1StartAndEndValues[0]) / ($gauge1StartAndEndValues[1] - $gauge1StartAndEndValues[0]));
}
$transformationAngle = $rotationAngleFromStart - 90 + (180 - $gagugeBarAngle) / 2;
*/
function getGaugeRotationAngle(value, range, barAngle) {
    if(value < range[0]) {
        var rotationAngleFromStart = -12;
    } else if(value > range[1]) {
        var rotationAngleFromStart = barAngle + 12;
    } else {
        var rotationAngleFromStart = (barAngle) * ((value - range[0]) / (range[1] - range[0]));
    }
    return rotationAngleFromStart - 90 + (180 - barAngle) / 2;
}