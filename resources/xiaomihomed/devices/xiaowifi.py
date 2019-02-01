from past.builtins import basestring
import socket
import binascii
import struct
import json
import logging
import globals
import utils
import threading
import socket
import time
from random import randint
from xiaomi.xiaomipacket import *

def discover(message):
	device = message['model']
	result={}
	default={}
	i = 0
	while i<3:
		try:
			Packet = XiaomiPacket()
			Packet = GetSessionInfo(message['dest'],message['token'])
			k = utils.key_iv(Packet.token)
		except:
			default['ip']=message['dest'];
			default['notfound'] =1;
			globals.JEEDOM_COM.send_change_immediate({'devices':{'wifi':default}})
			logging.debug('Did not find the device try again')
		if Packet.devicetype.encode('hex') != 'ffff':
			logging.debug('Found the device : ' + message['dest'])
			default['model']=device
			default['ip']=message['dest']
			default['serial']=Packet.serial.encode('hex')
			default['devtype']=Packet.devicetype.encode('hex')
			default['token']=Packet.token.encode('hex')
			default['found']=1
			result[message['dest']]=default
			globals.JEEDOM_COM.send_change_immediate({'devices':{'wifi':result[message['dest']]}})
			break
		else:
			default['ip']=message['dest'];
			default['notfound'] =1;
			globals.JEEDOM_COM.send_change_immediate({'devices':{'wifi':default}})
			logging.debug('Did not find the device try again')
		i = i+1
	return

def execute_action(message):
	i = 0
	while i<3:
		try:
			randid = randint(1, 65000)
			device = message['model']
			Packet  = XiaomiPacket()
			Packet = GetSessionInfo(message['dest'],message['token'])
			if message['param'] == '':
				if device == 'vacuum' and str(message['method']) == 'app_charge':
					logging.debug('{"id":'+str(randid)+',"method":"app_stop"}')
					Packet.setPlainData('{"id":'+str(randid)+',"method":"app_stop"}')
					SendRcv(Packet,message['dest'])
				logging.debug('{"id":'+str(randid+1)+',"method":"'+str(message['method'])+'"}')
				Packet.setPlainData('{"id":'+str(randid+1)+',"method":"'+str(message['method'])+'"}')
			else:
				logging.debug('{"id":'+str(randid)+',"method":"'+str(message['method'])+'","params":'+str(message['param'])+'}')
				Packet.setPlainData('{"id":'+str(randid)+',"method":"'+str(message['method'])+'","params":'+str(message['param'])+'}')
			SendRcv(Packet,message['dest'])
			t = threading.Timer(2, refresh,args=(message,))
			t.start()
			break
		except Exception, e:
			logging.debug(str(e))
		i = i+1
	return

def refresh(message):
	i = 0
	while i<3:
		try:
			
			device = message['model']
			result={}
			status ={}
			result['model'] = device
			result['ip'] = message['dest']
			if device in globals.DICT_REFRESH_WIFI:
				Packet  = XiaomiPacket()
				Packet = GetSessionInfo(message['dest'],message['token'])
				for info in globals.DICT_REFRESH_WIFI[device]:
					jsoninfo = json.loads(info)
					randid = randint(1, 65000)
					jsoninfo['id'] = randid
					info = json.dumps(jsoninfo)
					Packet.setPlainData(info)
					logging.debug(info)
					SendRcv(Packet,message['dest'])
					logging.debug(Packet.getPlainData())
					dict_params = json.loads(info)
					if 'params' in info:
						dict_result = json.loads(str(Packet.getPlainData()))
						results = dict_result['result']
						i=0
						for param in dict_params['params']:
							if param == 'temp_dec':
								status[param] = results[i]/10.0
							elif param == 'current':
								status[param] = results[i]
								status['powercalc'] = results[i]*220.0
							else:
								status[param] = results[i]
							i = i+1
							result['status'] = status
					else:
						if device in ['vacuum','vacuum2']:
							real_result= Packet.getPlainData().split('{',2)[2].split('}',1)[0]
							logging.debug(json.loads('{'+real_result+'}'))
							result[dict_params['method']] =json.loads('{'+real_result+'}')
			result = utils.clean_result(device,result)
			globals.JEEDOM_COM.add_changes('devices::wifi_'+message['dest'],result)
			break
		except Exception, e:
			if i == 2:
				logging.debug(str(e))
		i = i + 1
	return

def GetSessionInfo(ip,token=None):
	try:
		sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
	except socket.error:
		logging.debug('Failed to create socket')
		return
	try:
		PACKET  = XiaomiPacket()
		PACKET.setHelo()
		sock.sendto(PACKET.getRaw(), (ip, 54321))
		sock.settimeout(1.0)
		try:
			d = sock.recvfrom(1024)
		except socket.timeout:
			logging.debug("Timeout")
		PACKET.setRaw(d[0])
		if token and token != '':
			PACKET.token=token.decode('hex')
		return PACKET
	except socket.error, msg:
		logging.debug('Error Code: '+str(msg[0])+' Message: '+msg[1])

def SendRcv(PACKET,ip):
	try:
		sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
	except socket.error:
		logging.debug('Failed to create socket')
		return
	try:
		sock.sendto(PACKET.getRaw(), (ip, 54321))
		sock.settimeout(1.0)
		d = sock.recvfrom(1024)
		PACKET.setRaw(d[0])
		return
	except socket.error, msg:
		logging.debug('Error Code: '+str(msg[0])+' Messge: '+msg[1])
		return
	return
