# home-control

## Home control center for Raspberry Pi

Automation of some parts of a house:
* shutters
* lights
* heat pump metering
* weather forecast
* ...

## Hardware
The system runs on a Raspberry Pi 3 with Raspbian, Wlan, relay switches and 433MHz radio.

## User interface
Application is implemented as a web application accessible from a browser. Technologies used are HTML5/CSS3, PHP, Javascript, Python, MQTT and Websockets. The user interface wil be accessible in house on a central touch display powered by Raspberry Pi through a web application in a Chromium browser in kiosk mode. User interface will be also available on mobile devices or computers on a domain / IP address. Users will have to log-in with username and password. Blockchain authentication mechanism will be added in future.

## Functionaities
Functionalities are being dinamically added and are covered below.

### Shutters
Currently in implementation phase. Two motorized shutters are controlled to enable automatic or manual (local or remote) opening and closing.

### Lights
Currently in planning phase. Lights in various rooms and outside will be switched on or off manually or automatically per stored schedules. Both Wifi enabled switcheas and 433 MHz switches will be used.

### Heat pump metering
Currently in planning phase. A Modbus enabled power meter (i.e. Eastron 530/630) will be inserted between a three-phase power source and a heat pump power socket. A RS485 link will be connected to the Rspberry Pi with a USB to serial connector to read consumption and other parameters. Readings will be saved in database in regular intervals (i.e. 10 min). Hourly, daily and monthly consumption and costs will be calculated and displayed.

### Weather info and forecast
Currently in implementation phase. Weather daily and forecast data will be fetched from several weather web services. Daily weather information as well as 5 day forecast will be displayed for a selected provider. Sunrise and sunset times adn moon phases will be saved for other functionalities.

Foolowing weather services are planned to be used:
- OpenWeatherMap (https://openweathermap.org)
- Apixu (https://www.apixu.com/)
- Arso (http://meteo.arso.gov.si/met/sl/service/, https://github.com/zejn/arsoapi)

# Useful tips

## Start Chrominum in kiosk mode
To start Chromium in kiosk mode create `~/.config/autostart/autoChromium.desktop` with following contents:

```bash
[Desktop Entry]
Type=Application
Exec=/usr/bin/chromium-browser --noerrdialogs --disable-session-crashed-bubble --disable-infobars --kiosk -app=http://hcc.local
Hidden=false
X-GNOME-Autostart-enabled=true
Name[en_US]=AutoChromium
Name=AutoChromium
Comment=Start Chromium when GNOME starts
```
## Install and enable Unclutter_

Install and enable _unclutter_ to hide mouse cursor.

```
sudo apt-get install unclutter
```
Add this to the end of `~/.config/lxsession/LXDE-pi/autostart`:
```
@unclutter -idle 0
```

# Links

**MQTT**
- http://www.projects.privateeyepi.com/home/on-off-project

**Modbus**
- pymodbus: https://github.com/riptideio/pymodbus
- Modbus master/slave simulator: https://www.modbusdriver.com/evaluation.html

