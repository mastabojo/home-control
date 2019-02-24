#!/usr/bin/env python3

'''

CURRENTLY NOT IN USE - replaced by mainloop.py

'''

import sys, json, time
import RPi.GPIO as GPIO
import logging
import traceback
from pprint import pprint

logger = logging.getLogger()
handler = logging.StreamHandler()
formatter = logging.Formatter('%(asctime)s %(name)-12s %(levelname)-8s %(message)s')
handler.setFormatter(formatter)
logger.addHandler(handler)
logger.setLevel(logging.DEBUG)

'''
Structure of json:
{"mode": "auto | manual", "side": "left | right | both", "direction": "up | down", "timeDivider": 1 | 2 | 3 | 4}
Command line call: python shutters.py '{"mode": "auto", "side": "left", "direction": "up", "timeDivider": 1}'
'''
action = json.loads(sys.argv[1])

# GPIO numbers
GPIO.setmode(GPIO.BCM)
GPIO_REL_LEFT_UP = 17
GPIO_REL_LEFT_DOWN = 4
GPIO_REL_RIGHT_UP = 2
GPIO_REL_RIGHT_DOWN = 3
allGPIOs = [GPIO_REL_LEFT_UP, GPIO_REL_LEFT_DOWN, GPIO_REL_RIGHT_UP, GPIO_REL_RIGHT_DOWN]

# Time it takes shutters to fully open/close (seconds)
maxShutterTravelTime = 16
shutterTravelTime = int(maxShutterTravelTime / int(action['timeDivider']))

# Set GPIOs for all relays
GPIO.setup(allGPIOs, GPIO.OUT)

# Switch all relays off
GPIO.output(allGPIOs, GPIO.HIGH)

# Switch relays on and off after shutterTravelTime
error = False
try:
    if action['side'] == 'both':
        gpio1 = eval('GPIO_REL_LEFT_' + action['direction'].upper())
        gpio2 = eval('GPIO_REL_RIGHT_' + action['direction'].upper())
        gpioList = [gpio1, gpio2]

        print('RUNNING: ' + str(gpio1) + ' AND + ' str(gpio12))

    elif action['side'] in ['left', 'right']:
        gpio1 = eval('GPIO_REL_' + action['side'].upper() + '_' + action['direction'].upper())
        gpioList = [gpio1]

        print('RUNNING: ' + str(gpio1))

    else:
        error = True
        logger.debug('Wrong -side- argument: ' + action['side'])

    if error == False:
        GPIO.output(gpioList, GPIO.LOW)
        sleep(shutterTravelTime)
        GPIO.output(gpioList, GPIO.HIGH)

# If exceptions, log them
except Exception as e:
    logger.debug('GPIOs could not be set')
    logger.debug(traceback.format_exc())

# Switch all relays off and do a cleanup
finally:
    # Switch all relays off
    GPIO.output(allGPIOs, GPIO.HIGH)
    GPIO.cleanup()