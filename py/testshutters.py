#!/usr/bin/env python3

import sys, json, time
import RPi.GPIO as GPIO
import logging
import traceback
from pprint import pprint


# GPIO numbers
GPIO.setmode(GPIO.BCM)
GPIO_REL_LEFT_UP = 17
GPIO_REL_LEFT_DOWN = 4
GPIO_REL_RIGHT_UP = 2
GPIO_REL_RIGHT_DOWN = 3
allGPIOs = [GPIO_REL_LEFT_UP, GPIO_REL_LEFT_DOWN, GPIO_REL_RIGHT_UP, GPIO_REL_RIGHT_DOWN]

# Set GPIOs for all relays
# Set GPIOs for all relays
GPIO.setup(allGPIOs, GPIO.OUT)

# Switch all relays off
GPIO.output(allGPIOs, GPIO.HIGH)

# Switch relays on and off after shutterTravelTime
try:
    testGpios = [GPIO_REL_LEFT_UP]
    print('GPIO ON')
    GPIO.output(testGpios, GPIO.LOW)
    time.sleep(2)
    GPIO.output(testGpios, GPIO.HIGH)
    print('GPIO OFF')
except Exception as e:
    print('[' + gpio1 + '] could not be set')
    print(traceback.format_exc())

finally:
    # Switch all relays off
    GPIO.output(allGPIOs, GPIO.HIGH)
    GPIO.cleanup()