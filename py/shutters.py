#!/usr/bin/env python3

import sys, json, time
import RPi.GPIO as GPIO
import logging
import traceback
from pprint import pprint

'''
Structure of json:
{"mode": "auto | manual", "side": "left | right | both", "direction": "up | down"}
Command line call: python shutters.py '{"mode": "auto", "side": "left", "direction": "up"}'
'''

logger = logging.getLogger()
handler = logging.StreamHandler()
formatter = logging.Formatter('%(asctime)s %(name)-12s %(levelname)-8s %(message)s')
handler.setFormatter(formatter)
logger.addHandler(handler)
logger.setLevel(logging.DEBUG)

# GPIO numbers
GPIO.setmode(GPIO.BCM)
GPIO_REL_LEFT_UP = 2 
GPIO_REL_LEFT_DOWN = 3
GPIO_REL_RIGHT_UP = 4
GPIO_REL_RIGHT_DOWN = 17

# Time it takes shutters to fully open/close (seconds)
shutterTravelTime = 16

action = json.loads(sys.argv[1])

# Set GPIOs for all relays
GPIO.setup(GPIO_REL_LEFT_UP, GPIO.OUT)
GPIO.setup(GPIO_REL_LEFT_DOWN, GPIO.OUT)
GPIO.setup(GPIO_REL_RIGHT_DOWN, GPIO.OUT)
GPIO.setup(GPIO_REL_RIGHT_UP, GPIO.OUT)
# Switch all relays off
GPIO.output(GPIO_REL_LEFT_UP, GPIO.HIGH)
GPIO.output(GPIO_REL_LEFT_DOWN, GPIO.HIGH)
GPIO.output(GPIO_REL_RIGHT_UP, GPIO.HIGH)
GPIO.output(GPIO_REL_RIGHT_DOWN, GPIO.HIGH)

# Switch relays on and off after shutterTravelTime
try:
    if action['side'] == 'both':
        gpio1 = eval('GPIO_REL_LEFT_' + action['direction'].upper())
        gpio2 = eval('GPIO_REL_RIGHT_' + action['direction'].upper())
        # Switch relays on only if they are not on
        if GPIO.input(gpio1) == GPIO.HIGH and GPIO.input(gpio2) == GPIO.HIGH:
            GPIO.output(gpio1, GPIO.LOW)
            GPIO.output(gpio2, GPIO.LOW)
            time.sleep(shutterTravelTime)
            GPIO.output(gpio1, GPIO.HIGH)
            GPIO.output(gpio2, GPIO.HIGH)
        else:
            logger.debug('Can not switch ' + gpio1 + ' and ' + gpio2 + ' on since they are already switched on')
    elif action['side'] in ['left', 'right']:
        gpio1 = eval('GPIO_REL_' + action['side'].upper() + '_' + action['direction'].upper())
        # Switch relay on only if it is not on
        if GPIO.input(gpio1) == GPIO.HIGH:
            GPIO.output(gpio1, GPIO.LOW)
            time.sleep(shutterTravelTime)
            GPIO.output(gpio1, GPIO.HIGH)
        else:
            logger.debug('Can not switch ' + gpio1 + ' on since it is already switched on')
    else: 
        logger.debug('Wrong -side- argument: ' + action['side'])

# If errors log them
except Exception as e:
    logger.debug('[' + gpio1 + '] could not be set')
    logger.debug(traceback.format_exc())

# Switch all relays off and do a cleanup
finally:
    # Switch all relays off
    GPIO.output(GPIO_REL_LEFT_UP, GPIO.HIGH)
    GPIO.output(GPIO_REL_LEFT_DOWN, GPIO.HIGH)
    GPIO.output(GPIO_REL_RIGHT_UP, GPIO.HIGH)
    GPIO.output(GPIO_REL_RIGHT_DOWN, GPIO.HIGH)
    GPIO.cleanup()