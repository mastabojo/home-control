# home-control

## Home control center for Raspberry Pi

Automation of some parts of a house:
* shutters
* lights
* heat pump metering

## Start Chrominum in kiosk mode

Create `~/.config/autostart/autoChromium.desktop` with following contents:

<pre><code>
[Desktop Entry]
Type=Application
Exec=/usr/bin/chromium-browser --noerrdialogs --disable-session-crashed-bubble --disable-infobars --kiosk -app=http://hcc.local
Hidden=false
X-GNOME-Autostart-enabled=true
Name[en_US]=AutoChromium
Name=AutoChromium
Comment=Start Chromium when GNOME starts
</code></pre>
