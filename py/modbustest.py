# Eastron SDM630 Modbus Electricity Power Meter
# Using minimalmodbus library (RTU RS485 only)
# https://github.com/pyhys/minimalmodbus
import time
import minimalmodbus
import modbus_SDM530_data

# Communication settings
minimalmodbus.BAUDRATE = modbus_SDM530_data.SDM530_BAUDRATE
minimalmodbus.STOPBITS = modbus_SDM530_data.SDM530_STOPBITS
minimalmodbus.PARITY = modbus_SDM530_data.SDM530_PARITY
minimalmodbus.BYTESIZE = modbus_SDM530_data.SDM530_BYTESIZE
minimalmodbus.debug = True

# USB port used for RS485
portName = '/dev/ttyUSB0'
# Eastron SDM630 address
slaveAddress = 1
# Use serial mode
mode = 'rtu'
sleepTime = 0.99

# Function codes
# --------------

READ_HOLDING_REGISTER = 3
READ_INPUT_REGISTER = 4

# Readings
# --------

instrument = minimalmodbus.Instrument(portName, slaveAddress, mode)
for addr, desc in modbus_SDM530_data.inputRegisters.items():
    try:
        ## print desc + ': ' + str(instrument.read_float(addr, READ_INPUT_REGISTER))
        print str(addr) + ': ' + str(instrument.read_float(addr, READ_INPUT_REGISTER))
        time.sleep(sleepTime)
    except IOError:
        print("Failed to read address " + str(addr) + " (" + desc + ")" + " from instrument")

