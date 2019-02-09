import os

'''
Shut down if CPU above critical temeperature
'''

criticalTemp = 75000
tempInfo = open("/sys/class/thermal/thermal_zone0/temp", "r")
cpuTemp = tempInfo.read()
if cpuTemp > criticalTemp:
    os.system("sudo /sbin/halt") 
