from yeelight.main import Bulb, BulbType, BulbException, discover_bulbs
from yeelight.flow import (
		Flow,
		HSVTransition,
		RGBTransition,
		TemperatureTransition,
		SleepTransition
)
from yeelight import enums
import logging
import globals
import time
import utils
import threading

def scan(timeout=2):
	for x in range(4):
		bulbs  = discover_bulbs(timeout)
		logging.debug('Scan found ' + str(bulbs))
		for bulb in bulbs:
			globals.JEEDOM_COM.send_change_immediate({'devices':{'yeelight':bulb}})
			time.sleep(1)

def execute_action(message):
	logging.debug(str(message))
	bulb = Bulb(message['dest'], 55443, 'smooth', 500, True,model=message['model'])
	command_list= message['command'].split(' ')
	if command_list[0] == 'turn':
		if command_list[1] == 'on':
			bulb.turn_on()
		else:
			bulb.turn_off()
	elif command_list[0] == 'stop':
		bulb.stop_flow()
	elif command_list[0] == 'rgb':
		red, green, blue = utils.hex_color_to_rgb(message['option'])
		bulb.set_rgb(red, green, blue)
	elif command_list[0] == 'toggle':
		bulb.toggle()
	elif command_list[0] == 'brightness':
		bulb.set_brightness(int(message['option']))
	elif command_list[0] == 'temperature':
		if message['model'] == 'desklamp':
			if len(command_list)>1:
				bulb.set_color_temp_desk(int(command_list[1]), int(message['sup']))
			else:
				bulb.set_color_temp_desk(int(message['option']), int(message['sup']))
		else:
			if len(command_list)>1:
				bulb.set_color_temp(int(command_list[1]))
			else:
				bulb.set_color_temp(int(message['option']))
	elif command_list[0] == 'flow':
		flow_params = message['option'].split(' ')
		translist = flow_params[2].split('-')
		list =[]
		for transition in translist:
			elements = transition.split(',')
			if elements[0] in globals.DICT_MAPPING_YEELIGHT:
				effect = globals.DICT_MAPPING_YEELIGHT[elements[0]]
				if elements[0] == 'hsv':
					list.append(effect(int(elements[1]),int(elements[2]),int(elements[3]),int(elements[4])))
				elif elements[0] == 'rgb' :
					list.append(effect(int(elements[1]),int(elements[2]),int(elements[3]),int(elements[4]),int(elements[5])))
				elif elements[0] == 'temp' :
					list.append(effect(int(elements[1]),int(elements[2]),int(elements[3])))
				else:
					list.append(effect(int(elements[1])))
			else:
				logging.debug("Not an effect")
			if flow_params[1] == 'recover':
				flow = Flow(int(flow_params[0]),Flow.actions.recover,list)
			elif flow_params[1] == 'stay' :
				flow = Flow(int(flow_params[0]),Flow.actions.stay,list)
			else:
				flow = Flow(int(flow_params[0]),Flow.actions.off,list)
			bulb.start_flow(flow)
	elif command_list[0] == 'cron':
		bulb.cron_add(enums.CronType.off, int(message['option']))
	elif command_list[0] == 'hsv':
		hsv_option = message['option'].split(' ')
		bulb.set_hsv(int(hsv_option[0]), int(hsv_option[1]))
	t = threading.Timer(2, refresh,args=(message,))
	t.start()
	return

def refresh(message):
	result={}
	data={}
	result['ip']=message['dest']
	data['id'] = message['id']
	bulb = Bulb(message['dest'], 55443, 'smooth', 500, True,model=message['model'])
	result_brut = bulb.get_properties().items()
	for key, value in result_brut:
		data[key] = value
	result['capabilities'] = data
	globals.JEEDOM_COM.add_changes('devices::yeelight_'+message['id'],result)
	return
