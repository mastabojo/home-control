# Eastron SDM630 Modbus Electricity Power Meter
# Using minimalmodbus library (RTU RS485 only)
# https://github.com/pyhys/minimalmodbus
import time
import minimalmodbus

# Communication settings
minimalmodbus.BAUDRATE = 9600
minimalmodbus.STOPBITS = 1
minimalmodbus.PARITY = 'N'
minimalmodbus.BYTESIZE = 8
minimalmodbus.debug = True

# Raspberry Pi USB port used for RS485
portName = '/dev/ttyUSB0'
# Eastrom SDM630 address
slaveAddress = 1
# Use serial mode
mode = 'rtu'

# Modbus function codes
READ_HOLDING_REGISTER = 3
READ_INPUT_REGISTER = 4

# Input register addresses
ADDR_INPUT_TOTAL_KWH_30343 = 342
ADDR_INPUT_TOTAL_CURRENT_MONTH_ENERGY_30514 = 513
ADDR_HOLDING_SYSTEM_VOLTS_40007 = 6

instrument = minimalmodbus.Instrument(portName, slaveAddress, mode)

# Read some input registers (30000+, zero based)
# Function code: 4
# ----------------------------------------------
print "Input reg.: " + str(instrument.read_float(ADDR_INPUT_TOTAL_KWH_30343, READ_INPUT_REGISTER))
print "Input reg.: " + str(instrument.read_float(ADDR_INPUT_TOTAL_CURRENT_MONTH_ENERGY_30514, READ_INPUT_REGISTER))

# Read some holding registers (40000+, zero based)
# Function code: 3
# ------------------------------------------------
print "Holding reg.:" + str(instrument.read_float(ADDR_HOLDING_SYSTEM_VOLTS_40007, READ_HOLDING_REGISTER))
