#!/bin/sh
# hcc_init.sh
# Starts from cron
# Does some initial tasks on boot

# Start the main loop for Python scripts
cd /
cd /var/www/hcc.local/py
/usr/bin/python /var/www/hcc.local/py/mainloop.py &
cd /
