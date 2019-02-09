import os

criticalTemp = 7500
tempInfo = open("/sys/class/thermal/thermal_zone0/temp", "r")
cpuTemp = tempInfo.read()
if cpuTemp > criticalTemp:
    os.system() 
    os.system("sudo halt") 
