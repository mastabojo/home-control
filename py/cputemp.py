t = open("/sys/class/thermal/thermal_zone0/temp", "r")
print(int(int(t.read())/1000))
