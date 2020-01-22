### NOT USED

# Install with pip install paho-mqtt
import paho.mqtt.client as mqtt

DEBUG = True
MQTT_SERVER = "MQTTSERVER"
MQTT_PORT = "1883"
MQTT_USER = "MQTTUSER"
MQTT_PASS = "******"
MQTT_TOPIC = "test/#"

# The callback for when the client receives a connect response from the server.
def on_connect(client, userdata, flags, rc):
    dbgprint("Connected with result code " + str(rc))
    client.subscribe(MQTT_TOPIC)

# The callback for when a PUBLISH message is received from the server.
def on_message(client, userdata, msg):
    dbgprint(msg.topic + " " + str(msg.payload))


def dbgprint(var):
    if DEBUG:
        print(var)

client = mqtt.Client()
client.username_pw_set(MQTT_USER, MQTT_PASS)
client.on_connect = on_connect
client.on_message = on_message
client.connect(MQTT_SERVER, MQTT_PORT, 60)
client.loop_forever()