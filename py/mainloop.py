'''
Endless loop, reading the cmdFileName file contents. 
If not empty, a line represents a command in JSON format to be executed. 
The file is truncated after line is read, ready to accept new command
Commands are writen  to the cmdFilename by other programs (sort of a poor man's mqtt)
command format: {"mode": "auto | manual", "side": "left | right | both", "direction": "up | down", "timeDivider": 1 | 2 | 3 | 4}
'''

import sys, json, time
###[RPI] 
import RPi.GPIO as GPIO
import logging
import traceback

logger = logging.getLogger()
# handler = logging.StreamHandler()
handler = logging.FileHandler(filename="hcc.log")
formatter = logging.Formatter('%(asctime)s %(levelname)s: %(message)s')
handler.setFormatter(formatter)
logger.addHandler(handler)
logger.setLevel(logging.INFO)

logger.info('Started main loop')

# GPIO numbers
###[RPI] 
GPIO.setmode(GPIO.BCM)
GPIO_REL_LEFT_UP = 17
GPIO_REL_LEFT_DOWN = 4
GPIO_REL_RIGHT_UP = 2
GPIO_REL_RIGHT_DOWN = 3
allGPIOs = [GPIO_REL_LEFT_UP, GPIO_REL_LEFT_DOWN, GPIO_REL_RIGHT_UP, GPIO_REL_RIGHT_DOWN]

# Set GPIOs for all relays
GPIO.setup(allGPIOs, GPIO.OUT)

# Switch all relays off
GPIO.output(allGPIOs, GPIO.HIGH)

cmdFileName = "commandqueue.txt"

# Time it takes shutters to fully open/close (seconds)
maxShutterTravelTime = 16

while 1:
    try:
        # first take a little nap / count sheep
        time.sleep(1)

        # See if there are any new commands, read them and erase them
        cmdFile = open(cmdFileName, "r+")
        line = cmdFile.readline()
        cmdFile.seek(0) 
        cmdFile.truncate()        
        cmdFile.close()

        # If there is no command in the queue skip to the next loop iterration
        if len(line) == 0:
            continue

        logger.info('Run command: ' + line)

        # convert line to json
        action = json.loads(line)
        shutterTravelTime = int(maxShutterTravelTime / int(action['timeDivider']))

        # both left and right roller shutter
        if action['side'] == 'both':
            gpio1 = eval('GPIO_REL_LEFT_' + action['direction'].upper())
            gpio2 = eval('GPIO_REL_RIGHT_' + action['direction'].upper())
            logger.info('GPIOs SET FOR ACTION: ' + str(gpio1) + ' AND ' + str(gpio1))
            gpioList = [gpio1, gpio2]

        # only left or right shutter
        elif action['side'] in ['left', 'right']:
            gpio1 = eval('GPIO_REL_' + action['side'].upper() + '_' + action['direction'].upper())
            logger.info('GPIO SET FOR ACTION: ' + str(gpio1))
            gpioList = [gpio1]

        # non-existent shutters
        else: 
            logger.info('Wrong -side- argument: ' + action['side'])
            break

        # Internal loop for operating shutters
        for t in range(0, shutterTravelTime):
            # print("TIME: " + str(t))
            # on first iterration switch the relay(s) ON
            if t == 0:
                ###[RPI] 
                GPIO.output(gpioList, GPIO.LOW)
                logger.info("SHUTTERS: " + action['side'] + " - SWITCHED ON")
                pass
            # on last iterration switch the relay(s) OFF
            if t == shutterTravelTime - 1:
                ###[RPI] 
                GPIO.output(gpioList, GPIO.HIGH)
                logger.info("SHUTTERS: " + action['side'] + " - SWITCHED OFF")
                pass

            # take a nap (for loop)
            time.sleep(1)

        # switch all relays off
        GPIO.output(gpioList, GPIO.HIGH)

    except IOError:
        logger.info('An error occured trying to read the file ' + cmdFileName)
        GPIO.cleanup()
        break
   
    except EOFError:
        logger.info('An EOF error occured')
        GPIO.cleanup()
        break

    # Ctrl+C exits the loop
    except (KeyboardInterrupt):
        logger.info('Program exited by keyboard interrupt')
        # logger.info(traceback.format_exc())
        GPIO.cleanup()
        break

print "\n\n"
