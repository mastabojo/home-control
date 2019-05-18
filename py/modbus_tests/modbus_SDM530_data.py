# Eastron SDM530 Modbus Electricity Power Meter
# Communication and registers definition

# Communication settings
# ----------------------
SDM530_BAUDRATE = 9600
SDM530_STOPBITS = 1
SDM530_PARITY = 'N'
SDM530_BYTESIZE = 8

# Function codes
# --------------
READ_HOLDING_REGISTER = 3
READ_INPUT_REGISTER = 4

# Register addresses
# ------------------
# Phase 1 - 3 Line to Neutral Volts
ADDR_INPUT_PHASE1_TO_NEUTRAL_30001 =         0
ADDR_INPUT_PHASE2_TO_NEUTRAL_30003 =         2
ADDR_INPUT_PHASE3_TO_NEUTRAL_30005 =         4
# Phase 1 - 3 Current
ADDR_INPUT_PHASE1_CURRENT_30007 =            6
ADDR_INPUT_PHASE2_CURRENT_30009 =            8
ADDR_INPUT_PHASE3_CURRENT_30011 =           10
# Phase 1 - 3 phase angle
ADDR_INPUT_PHASE1_PHASE_ANGLE_30037 =       36
ADDR_INPUT_PHASE2_PHASE_ANGLE_30039 =       38
ADDR_INPUT_PHASE3_PHASE_ANGLE_30041 =       40
# Averages
ADDR_INPUT_AVERAGE_LINE_TO_NEUTRAL_30043 =  42
ADDR_INPUT_AVERAGE_LINE_CURRENT_30047 =     46
ADDR_INPUT_SUM_OF_LINE_CURRENTS_30049 =     48
# Total phase angle
ADDR_INPUT_TOTAL_SYSTEM_PHASE_ANGLE_30067 = 66
# Frequency
ADDR_INPUT_FREQUENCY_30071 =                70
# Total energy (30343)
ADDR_INPUT_TOTAL_KWH_30343 =               342

# Descriptions
# ------------------
inputRegisters = {
    ADDR_INPUT_PHASE1_TO_NEUTRAL_30001:         'Phase 1 Line to Neutral [V]',
    ADDR_INPUT_PHASE2_TO_NEUTRAL_30003:         'Phase 2 Line to Neutral [V]',
    ADDR_INPUT_PHASE3_TO_NEUTRAL_30005:         'Phase 3 Line to Neutral [V]',
    ADDR_INPUT_PHASE1_CURRENT_30007:            'Phase 1 Line current [A]',
    ADDR_INPUT_PHASE2_CURRENT_30009:            'Phase 2 Line current [A]',
    ADDR_INPUT_PHASE3_CURRENT_30011:            'Phase 3 Line current [A]',
    ADDR_INPUT_PHASE1_PHASE_ANGLE_30037:        'Phase 1 phase angle [Deg]',
    ADDR_INPUT_PHASE2_PHASE_ANGLE_30039:        'Phase 2 phase angle [Deg]',
    ADDR_INPUT_PHASE3_PHASE_ANGLE_30041:        'Phase 3 phase angle [Deg]',
    ADDR_INPUT_AVERAGE_LINE_TO_NEUTRAL_30043:   'Average Line to Neutral [V]',
    ADDR_INPUT_AVERAGE_LINE_CURRENT_30047:      'Average Line current [A]',
    ADDR_INPUT_SUM_OF_LINE_CURRENTS_30049:      'Sum of Line current [A]',
    ADDR_INPUT_TOTAL_SYSTEM_PHASE_ANGLE_30067:  'Total system phase angle [Deg]',
    ADDR_INPUT_FREQUENCY_30071:                 'Input frequency [Hz]',
    ADDR_INPUT_TOTAL_KWH_30343:                 'Total energy [KWh]',
}

