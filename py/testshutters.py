#!/usr/bin/env python3

import sys, json, time
import RPi.GPIO as GPIO
import logging
import traceback
from pprint import pprint


# GPIO numbers
GPIO.setmode(GPIO.BCM)
GPIO_REL_LEFT_UP = 2 
GPIO_REL_LEFT_DOWN = 3
GPIO_REL_RIGHT_UP = 4
GPIO_REL_RIGHT_DOWN = 17

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
    GPIO.output(GPIO_REL_LEFT_UP, GPIO.LOW)
    time.sleep(2)
    GPIO.output(GPIO_REL_LEFT_UP, GPIO.HIGH)
except Exception as e:
    print('[' + gpio1 + '] could not be set')
    print(traceback.format_exc())

finally:
    # Switch all relays off
    GPIO.output(GPIO_REL_LEFT_UP, GPIO.HIGH)
    GPIO.output(GPIO_REL_LEFT_DOWN, GPIO.HIGH)
    GPIO.output(GPIO_REL_RIGHT_UP, GPIO.HIGH)
    GPIO.output(GPIO_REL_RIGHT_DOWN, GPIO.HIGH)
    GPIO.cleanup()