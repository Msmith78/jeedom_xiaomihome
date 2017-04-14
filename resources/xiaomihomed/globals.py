# coding: utf-8
import time
from devices.yeelight.flow import *
JEEDOM_COM = ''
log_level = "error"
pidfile = '/tmp/blead.pid'
apikey = ''
callback = ''
cycle = 0.3
daemonname=''
socketport=''
sockethost=''

DICT_MAPPING_YEELIGHT = {'wait' : SleepTransition,\
	'hsv' : HSVTransition, \
	'rgb' : RGBTransition, \
	'temp' : TemperatureTransition, \
	}

IV_AQUARA = bytearray([0x17, 0x99, 0x6d, 0x09, 0x3d, 0x28, 0xdd, 0xb3, 0xba, 0x69, 0x5a, 0x2e, 0x6f, 0x58, 0x56, 0x2e])

DICT_REFRESH_WIFI ={'purifier' : ['{"id":1,"method":"get_prop","params":["aqi","led","mode","filter1_life","buzzer","favorite_level","temp_dec","humidity","motor1_speed","led_b","child_lock"]}'],\
					'humidifier' :['{"id":1,"method":"get_prop","params":["humidity","temp_dec","power","mode","led_b","buzzer","child_lock","limit_hum","trans_level"]}'],\
					'vacuum' :['{"id":1,"method":"get_status"}','{"id":2,"method":"get_consumable"}'],\
					'pm25' :['{"id":1,"method":"get_prop","params":["aqi","battery","state"]}'],\
					'ricecooker' :['{"id":1,"method":"get_prop","params":["all"]}'],\
					'philipseyecare' :['{"id":1,"method":"get_prop","params":["power","bright","notifystatus","ambstatus","ambvalue","eyecare","scene_num","bls","dvalue"]}'],\
	}

DICT_STATE_WIFI ={'vacuum' : {
								1: 'Unknown 1',
								2: 'Chargeur déconnecté',
								3: 'Au repos',
								4: 'Unknown 4',
								5: 'En nettoyage',
								6: 'Retour à la base',
								7: 'Unknown 7',
								8: 'En charge',
								9: 'Unknown 9',
								10: 'En pause',
								11: 'Nettoyage Spot',
								12: 'Erreur'
							}
}

DICT_ERROR_WIFI ={'vacuum' : {
								0: "Tout va bien",
								1: "Problème sur le laser",
								2: "Problème capteur de collision",
								3: "Mes roues ont un soucis",
								4: "Nettoyer mes capteurs de sols",
								5: "Nettoyer la brosse",
								6: "Nettoyer la brossette",
								7: "Ma roue principale est bloquée",
								8: "Je suis bloqué",
								9: "Où est mon bac à poussières",
								10: "Nettoyer le filtre",
								11: "Bloqué sur ma barrière",
								12: "Batterie faible",
								13: "Problème de charge",
								14: "Problème de batterie",
								15: "Mes détecteurs sont sales",
								16: "Placez moi sur une surface plane",
								17: "Problème, redémarrez moi",
								18: "Problème d'aspiration",
								19: "La station de charge n'est pas alimentée",
							}
}
