# Eastron SDM630 Modbus Electricity Power Meter
# Using minimalmodbus library (RTU RS485 only)
# https://github.com/pyhys/minimalmodbus
import time
import minimalmodbus

SDM530_BAUDRATE = 9600
SDM530_STOPBITS = 1
SDM530_PARITY = 'N'
SDM530_BYTESIZE = 8

# Communication settings
minimalmodbus.BAUDRATE = SDM530_BAUDRATE
minimalmodbus.STOPBITS = SDM530_STOPBITS
minimalmodbus.PARITY   = SDM530_PARITY
minimalmodbus.BYTESIZE = SDM530_BYTESIZE
minimalmodbus.debug = True

# USB port used for RS485
portName = '/dev/ttyUSB0'
# Eastron SDM630 address
slaveAddress = 1
# Use serial mode
mode = 'rtu'
sleepTime = 0.99
maxTries = 5

# Function codes
READ_HOLDING_REGISTER = 3
READ_INPUT_REGISTER = 4

# Register addresses definition
# -----------------------------
# Phase 1 - 3 Line to Neutral Volts
ADDR_INPUT_PHASE1_TO_NEUTRAL_30001        =  0
ADDR_INPUT_PHASE2_TO_NEUTRAL_30003        =  2
ADDR_INPUT_PHASE3_TO_NEUTRAL_30005        =  4
# Phase 1 - 3 Current
ADDR_INPUT_PHASE1_CURRENT_30007           =  6
ADDR_INPUT_PHASE2_CURRENT_30009           =  8
ADDR_INPUT_PHASE3_CURRENT_30011           = 10
# Phase 1 - 3 phase angle
ADDR_INPUT_PHASE1_PHASE_ANGLE_30037       = 36
ADDR_INPUT_PHASE2_PHASE_ANGLE_30039       = 38
ADDR_INPUT_PHASE3_PHASE_ANGLE_30041       = 40
# Averages
ADDR_INPUT_AVERAGE_LINE_TO_NEUTRAL_30043  = 42
ADDR_INPUT_AVERAGE_LINE_CURRENT_30047     = 46
ADDR_INPUT_SUM_OF_LINE_CURRENTS_30049     = 48
# Total phase angle
ADDR_INPUT_TOTAL_SYSTEM_PHASE_ANGLE_30067 = 66
# Frequency
ADDR_INPUT_FREQUENCY_30071                = 70

# Input registers to read
inputRegisters = [
    ADDR_INPUT_PHASE1_TO_NEUTRAL_30001,
    ADDR_INPUT_PHASE2_TO_NEUTRAL_30003,
    ADDR_INPUT_PHASE3_TO_NEUTRAL_30005,
    ADDR_INPUT_PHASE1_CURRENT_30007,
    ADDR_INPUT_PHASE2_CURRENT_30009,
    ADDR_INPUT_PHASE3_CURRENT_30011,
    ADDR_INPUT_PHASE1_PHASE_ANGLE_30037,
    ADDR_INPUT_PHASE2_PHASE_ANGLE_30039,
    ADDR_INPUT_PHASE3_PHASE_ANGLE_30041,
    ADDR_INPUT_AVERAGE_LINE_TO_NEUTRAL_30043,
    ADDR_INPUT_AVERAGE_LINE_CURRENT_30047,
    ADDR_INPUT_SUM_OF_LINE_CURRENTS_30049,
    ADDR_INPUT_TOTAL_SYSTEM_PHASE_ANGLE_30067,
    ADDR_INPUT_FREQUENCY_30071
]

instrument = minimalmodbus.Instrument(portName, slaveAddress, mode)

# Readings
for addr in inputRegisters:
    reading = 0.0
    for count in range(0, maxTries):
        try:
            reading = instrument.read_float(addr, READ_INPUT_REGISTER)
            time.sleep(sleepTime)
            if reading > 0.0:
                break;
        except IOError:
            pass
        except
            pass
    print str(reading)

