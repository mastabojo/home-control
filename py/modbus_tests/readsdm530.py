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
sleepTime = 0.6

# Function codes
# --------------

READ_HOLDING_REGISTER = 3
READ_INPUT_REGISTER = 4

sdm530addresses = (
    modbus_SDM530_data.ADDR_INPUT_AVERAGE_LINE_TO_NEUTRAL_30043,
    modbus_SDM530_data.ADDR_INPUT_TOTAL_KWH_30343
)

# Readings
# --------

result = ''
instrument = minimalmodbus.Instrument(portName, slaveAddress, mode)
for addr in sdm530addresses:
    try:
        # while 1:
        for i in range (0, 20):
            time.sleep(sleepTime)
            reading = instrument.read_float(addr, modbus_SDM530_data.READ_INPUT_REGISTER)
            if reading > 0:
                break
        # print reading
        print str(i) + ': ' + str(reading) + ' (' + str(addr) + ')'

    except IOError:
        pass
        # print("Failed to read address " + str(addr) + " (" + desc + ")" + " from instrument")
