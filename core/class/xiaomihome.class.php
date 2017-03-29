<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class xiaomihome extends eqLogic {
    public static function cron() {
        if (config::byKey('yeeCron', 'xiaomihome') == '1') {
            $eqLogics = eqLogic::byType('xiaomihome');
            foreach ($eqLogics as $eqLogic) {
                if ($eqLogic->getConfiguration('type') == 'yeelight') {
                    $eqLogic->yeeStatus($eqLogic->getConfiguration('gateway'));
                }
            }
        }
    }
	
	public static function createFromDef($_def) {
		event::add('jeedom::alert', array(
			'level' => 'warning',
			'page' => 'xiaomihome',
			'message' => __('Nouveau module detecté', __FILE__),
		));
		if (!isset($_def['model']) || !isset($_def['sid'])) {
			log::add('xiaomihome', 'error', 'Information manquante pour ajouter l\'équipement : ' . print_r($_def, true));
			event::add('jeedom::alert', array(
				'level' => 'danger',
				'page' => 'xiaomihome',
				'message' => __('Information manquante pour ajouter l\'équipement. Inclusion impossible', __FILE__),
			));
			return false;
		}
		$logical_id = $_def['sid'];
		if ($_def['model'] == 'gateway') {
			$logical_id = $_def['source'];
		}
		$xiaomihome=xiaomihome::byLogicalId($logical_id, 'xiaomihome');
		if (!is_object($xiaomihome)) {
			if ($model == 'gateway') {
            //test si gateway qui a changé d'ip
				foreach (eqLogic::byType('xiaomihome') as $gateway) {
					if ($gateway->getConfiguration('sid') == $_def['sid']) {
						$gateway->setConfiguration('gateway',$_def['source']);
						$gateway->setLogicalId($logical_id );
						$gateway->save();
						return;
					}
				}
			}
			$device = self::devicesParameters($_def['model']);
			if (!is_array($device)) {
				return true;
			}
			$xiaomihome = new xiaomihome();
			$xiaomihome->setEqType_name('xiaomihome');
			$xiaomihome->setLogicalId($logical_id);
			$xiaomihome->setIsEnable(1);
			$xiaomihome->setIsVisible(1);
			$xiaomihome->setName($device['name'] . ' ' . $_def['sid']);
			$xiaomihome->setConfiguration('sid', $_def['sid']);
			if (isset($device['configuration'])) {
				foreach ($device['configuration'] as $key => $value) {
					$xiaomihome->setConfiguration($key, $value);
				}
			}
			event::add('jeedom::alert', array(
			'level' => 'warning',
			'page' => 'xiaomihome',
			'message' => __('Module inclu avec succès ' . $_def['model'], __FILE__),
			));
		}
		$xiaomihome->setConfiguration('short_id',$_def['short_id']);
		$xiaomihome->setConfiguration('gateway',$_def['source']);
		$xiaomihome->setConfiguration('lastCommunication',date('Y-m-d H:i:s'));
		$xiaomihome->save();
		return $xiaomihome;
	}
	
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'xiaomihome';
		$return['state'] = 'nok';
		$pid_file = jeedom::getTmpFolder('xiaomihome') . '/deamon.pid';
		if (file_exists($pid_file)) {
			if (@posix_getsid(trim(file_get_contents($pid_file)))) {
				$return['state'] = 'ok';
			} else {
				shell_exec(system::getCmdSudo() . 'rm -rf ' . $pid_file . ' 2>&1 > /dev/null');
			}
		}
		$return['launchable'] = 'ok';
		return $return;
	}

	public static function deamon_start() {
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$xiaomihome_path = realpath(dirname(__FILE__) . '/../../resources/xiaomihomed');
		$cmd = '/usr/bin/python ' . $xiaomihome_path . '/xiaomihomed.py';
		$cmd .= ' --loglevel ' . log::convertLogLevel(log::getLogLevel('xiaomihome'));
		$cmd .= ' --socketport ' . config::byKey('socketport', 'xiaomihome');
		$cmd .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/xiaomihome/core/php/jeeXiaomiHome.php';
		$cmd .= ' --apikey ' . jeedom::getApiKey('xiaomihome');
		$cmd .= ' --cycle ' . config::byKey('cycle', 'xiaomihome');
		$cmd .= ' --pid ' . jeedom::getTmpFolder('xiaomihome') . '/deamon.pid';
		log::add('xiaomihome', 'info', 'Lancement démon xiaomihome : ' . $cmd);
		$result = exec($cmd . ' >> ' . log::getPathToLog('xiaomihome') . ' 2>&1 &');
		$i = 0;
		while ($i < 5) {
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'ok') {
				break;
			}
			sleep(1);
			$i++;
		}
		if ($i >= 5) {
			log::add('xiaomihome', 'error', 'Impossible de lancer le démon openenocean, vérifiez la log', 'unableStartDeamon');
			return false;
		}
		message::removeAll('xiaomihome', 'unableStartDeamon');
		return true;
	}
	
	public static function deamon_stop() {
		$pid_file = jeedom::getTmpFolder('xiaomihome') . '/deamon.pid';
		if (file_exists($pid_file)) {
			$pid = intval(trim(file_get_contents($pid_file)));
			system::kill($pid);
		}
		system::kill('xiaomihomed.py');
		system::fuserk(config::byKey('socketport', 'xiaomihome'));
	}

	public static function dependancy_info() {
		$return = array();
		$return['log'] = 'xiaomihome_dep';
		$return['state'] = 'ok';
		return $return;
	}

	public static function dependancy_install() {
		log::add('xiaomihome','info','Installation des dépéndances nodejs');
		$resource_path = realpath(dirname(__FILE__) . '/../../resources');
		passthru('/bin/bash ' . $resource_path . '/nodejs.sh ' . $resource_path . ' > ' . log::getPathToLog('xiaomihome_dep') . ' 2>&1 &');
	}

    public function yeeAction($ip, $request, $option) {
        exec("sudo ping -c1 " . $ip, $output, $return_var);
        if ($return_var != 0) {
            log::add('xiaomihome', 'debug', 'Lampe Yeelight non joignable ' . $ip);
            $this->checkAndUpdateCmd('online', 0);
            return;
        }

        $cmd = 'python ' . realpath(dirname(__FILE__)) . '/../../resources/yeecli.py ' . $ip . ' ' . $request . ' ' . $option;
        //$cmd = 'yeecli --ip=' . $ip . ' ' . $request . ' ' . $option;
        log::add('xiaomihome', 'debug', 'Commande Yeelight ' . $cmd);
        exec($cmd);
    }

    public function yeeStatus($ip) {
        exec("sudo ping -c1 " . $ip, $iptest, $return_var);
        if ($return_var != 0) {
            log::add('xiaomihome', 'debug', 'Lampe Yeelight non joignable ' . $ip);
            $this->checkAndUpdateCmd('online', 0);
            exit();
        }
        //$cmd = 'yee --ip=' . $ip . ' status';
        $cmd = 'python ' . realpath(dirname(__FILE__)) . '/../../resources/yeecli.py ' . $ip . ' status';
        exec($cmd, $output, $return);
        //$output = shell_exec($cmd);
        log::add('xiaomihome', 'debug', 'Status ' . print_r($output, true));

        $status = explode(' : ',$output[3]);
        $color_mode = explode(' : ',$output[1]);
        $bright = explode(' : ',$output[7]);
        $rgb = explode(' : ',$output[6]);
        $hue = explode(' : ',$output[0]);
        $saturation = explode(' : ',$output[11]);
        $color_temp = explode(' : ',$output[10]);

        $power = ($status[1] == 'off')? 0:1;
        $this->checkAndUpdateCmd('status', $power);
        $this->checkAndUpdateCmd('brightness', $bright[1]);
        if ($this->getConfiguration('model') != 'mono' && $this->getConfiguration('model') != 'ceiling') {
            $this->checkAndUpdateCmd('color_mode', $color_mode[1]);
            $this->checkAndUpdateCmd('rgb', '#' . str_pad(dechex($rgb[1]), 6, "0", STR_PAD_LEFT));
            $this->checkAndUpdateCmd('hsv', $hue[1]);
            $this->checkAndUpdateCmd('saturation', $saturation[1]);
        }
        if ($this->getConfiguration('model') != 'mono') {
            $this->checkAndUpdateCmd('temperature', $color_temp[1]);
        }
        //log::add('xiaomihome', 'debug', $power . ' ' . $color_mode[1] . ' ' . $bright[1] . ' ' . '#' . str_pad(dechex($rgb[1]), 6, "0", STR_PAD_LEFT) . ' ' . $hue[1] . ' ' . $saturation[1] . ' ' . $color_temp[1]);
    }

    public function receiveYeelight($ip, $id, $model, $fw_ver, $power, $color_mode, $rgb, $bright, $hue, $saturation, $color_temp) {
        //log::add('xiaomihome', 'debug', 'Yeelight ' . print_r($device,true));
        $xiaomihome = self::byLogicalId($id, 'xiaomihome');
        if (!is_object($xiaomihome)) {
            $device = self::devicesParameters($model);
            if (!is_array($device)) {
                return true;
            }
            $xiaomihome = new xiaomihome();
            $xiaomihome->setEqType_name('xiaomihome');
            $xiaomihome->setLogicalId($id);
            $xiaomihome->setName($model . ' ' . $id);
            $xiaomihome->setConfiguration('sid', $id);
            $xiaomihome->setIsEnable(1);
            $xiaomihome->setIsVisible(1);
            if (isset($device['configuration'])) {
                foreach ($device['configuration'] as $key => $value) {
                    $xiaomihome->setConfiguration($key, $value);
                }
            }
            event::add('xiaomihome::includeDevice',
            array(
                'state' => 1
            )
        );
    }
    $xiaomihome->setConfiguration('model',$model);
    $xiaomihome->setConfiguration('short_id',$fw_ver);
    $xiaomihome->setConfiguration('gateway',$ip);
    $xiaomihome->setConfiguration('lastCommunication',date('Y-m-d H:i:s'));

    $xiaomihome->save();

    //use generic method if possible, so just create eqLogic
    $xiaomihome->yeeStatus($ip);

    /*
    $xiaomihome->checkAndUpdateCmd('online', 1);
    $power = ($power == 'off')? 0:1;
    $xiaomihome->checkAndUpdateCmd('status', $power);
    $xiaomihome->checkAndUpdateCmd('brightness', $bright);

    if ($model != 'mono') {
    $xiaomihome->checkAndUpdateCmd('temperature', $color_temp);
    if ($model != 'ceiling') {
    $xiaomihome->checkAndUpdateCmd('color_mode', $color_mode);
    $xiaomihome->checkAndUpdateCmd('rgb', '#' . str_pad(dechex($rgb), 6, "0", STR_PAD_LEFT));
    $xiaomihome->checkAndUpdateCmd('hsv', $hue);
    $xiaomihome->checkAndUpdateCmd('saturation', $saturation);
}
}
*/

	}

	public function postSave() {
		$this->applyModuleConfiguration($this->getConfiguration('model'));
	}

	public static function devicesParameters($_device = '') {
		$return = array();
		foreach (ls(dirname(__FILE__) . '/../config/devices', '*') as $dir) {
			$path = dirname(__FILE__) . '/../config/devices/' . $dir;
			if (!is_dir($path)) {
				continue;
			}
			$files = ls($path, '*.json', false, array('files', 'quiet'));
			foreach ($files as $file) {
				try {
					$content = file_get_contents($path . '/' . $file);
					if (is_json($content)) {
						$return += json_decode($content, true);
					}
				} catch (Exception $e) {
				}
			}
		}
		if (isset($_device) && $_device != '') {
			if (isset($return[$_device])) {
				return $return[$_device];
			}
			return array();
		}
		return $return;
	}

	public function applyModuleConfiguration($model) {
		$device = self::devicesParameters($model);
		if (!is_array($device)) {
			return true;
		}
	
		$link_cmds = array();
		$link_actions = array();
		foreach ($device['commands'] as $command) {
			$xiaomihomeCmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($this->getId(),$command['logicalId']);
			if (!is_object($xiaomihomeCmd)) {
				$xiaomihomeCmd = new xiaomihomeCmd();
				$xiaomihomeCmd->setEqLogic_id($this->getId());
				$xiaomihomeCmd->setEqType('xiaomihome');
				$xiaomihomeCmd->setLogicalId($command['logicalId']);
				utils::a2o($xiaomihomeCmd, $command);
				$xiaomihomeCmd->save();
				if (isset($command['value'])) {
					$link_cmds[$xiaomihomeCmd->getId()] = $command['value'];
				}
				if (isset($command['configuration']) && isset($command['configuration']['updateCmdId'])) {
					$link_actions[$xiaomihomeCmd->getId()] = $command['configuration']['updateCmdId'];
				}
			}
		}
		if (count($link_cmds) > 0) {
			foreach ($this->getCmd() as $eqLogic_cmd) {
				foreach ($link_cmds as $cmd_id => $link_cmd) {
					if ($link_cmd == $eqLogic_cmd->getName()) {
						$cmd = cmd::byId($cmd_id);
						if (is_object($cmd)) {
							$cmd->setValue($eqLogic_cmd->getId());
							$cmd->save();
						}
					}
				}
			}
		}
		if (count($link_actions) > 0) {
			foreach ($this->getCmd() as $eqLogic_cmd) {
				foreach ($link_actions as $cmd_id => $link_action) {
					if ($link_action == $eqLogic_cmd->getName()) {
						$cmd = cmd::byId($cmd_id);
						if (is_object($cmd)) {
							$cmd->setConfiguration('updateCmdId', $eqLogic_cmd->getId());
							$cmd->save();
						}
					}
				}
			}
		}
	}

	public static function receiveAquaraData($id, $model, $key, $value) {
		$xiaomihome = self::byLogicalId($id, 'xiaomihome');
		if (is_object($xiaomihome)) {
			if ($key == 'humidity' || $key == 'temperature') {
				$value = $value / 100;
			}
			if ($key == 'rotate') {
				if ($value  > 0) {
					$xiaomihome->checkAndUpdateCmd('status', 'rotate_right');
				} else {
					$xiaomihome->checkAndUpdateCmd('status', 'rotate_left');
				}
			}
			if ($key == 'rgb') {
				$value = str_pad(dechex($value), 8, "0", STR_PAD_LEFT);
				$light = hexdec(substr($value, 0, 2));
				$value = '#' . substr($value, -6);
				$xiaomihome->checkAndUpdateCmd('brightness', $light);
				$xiaomihome->checkAndUpdateCmd('rgb', $value);
			}
			if ($key == 'voltage') {
				$battery = ($value-2800) / 5;
				$value = $value / 1000;
				$xiaomihome->checkAndUpdateCmd('battery', $battery);
				$xiaomihome->setConfiguration('battery',$battery);
				$xiaomihome->batteryStatus($battery);
				$xiaomihome->save();
			}
			if ($key == 'no_motion') {
				$xiaomihome->checkAndUpdateCmd('status', 0);
			}
			if ($key == 'no_close') {
				$xiaomihome->checkAndUpdateCmd('status', 1);
			}
			if ($key == 'channel_0' || $key == 'channel_1') {
				$value = ($value == 'on') ? 1 : 0;
			}
			if ($key == 'status') {
				if ($model == 'motion') {
					if ($value == 'motion') {
						$xiaomihome->checkAndUpdateCmd('no_motion', 0);
						$value = 1;
					} else {
						$value = 0;
					}
				}
				if ($model == 'magnet') {
					if ($value == 'open') {
						$value = 1;
					} else {
						$value = 0;
						$xiaomihome->checkAndUpdateCmd('no_close', 0);
					}
				}
				if ($model == 'plug') {
					$value = ($value == 'on') ? 1 : 0;
				}
			}
			$xiaomihomeCmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($xiaomihome->getId(),$key);
			if (is_object($xiaomihomeCmd)) {
				$xiaomihomeCmd->setConfiguration('value',$value);
				$xiaomihomeCmd->save();
				$xiaomihomeCmd->event($value);
			}
		}
	}
}

class xiaomihomeCmd extends cmd {
    public function execute($_options = null) {
        if ($this->getType() == 'info') {
            return $this->getConfiguration('value');
        } else {
            $eqLogic = $this->getEqLogic();
            log::add('xiaomihome', 'debug', 'execute : ' . $this->getType() . ' ' . $eqLogic->getConfiguration('type') . ' ' . $this->getLogicalId());
            if ($eqLogic->getConfiguration('type') == 'yeelight') {
                switch ($this->getSubType()) {
                    case 'slider':
                    $option = $_options['slider'];
                    if ($this->getLogicalId() == 'hsvAct') {
                        $cplmtcmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'saturation');
                        $option = $option . ' ' . $cplmtcmd->execCmd();
                    }
                    if ($this->getLogicalId() == 'saturationAct') {
                        $cplmtcmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'hsv');
                        $option = $cplmtcmd->execCmd() . ' ' . $option;
                    }
                    log::add('xiaomihome', 'debug', 'Slider : ' . $option);
                    break;
                    case 'color':
                    $option = str_replace('#','',$_options['color']);
                    break;
                    case 'message':
                    if ($this->getLogicalId() == 'mid-scenar') {
                        $eqLogic->checkAndUpdateCmd('vol', $_options['message']);
                    }
                    $option = $_options['title'];
                    break;
                    default :
                    $option = '';
                    break;
                }
                //log::add('xiaomihome', 'debug', $eqLogic->getConfiguration('gateway') . ' ' . $this->getConfiguration('request') . ' ' . $option);
                if ($this->getLogicalId() != 'refresh') {
                    if ($option == '000000') {
                        $eqLogic->yeeAction($eqLogic->getConfiguration('gateway'),'turn','off');
                    } else {
                        $eqLogic->yeeAction($eqLogic->getConfiguration('gateway'),$this->getConfiguration('request'),$option);
                    }
                }
                $eqLogic->yeeStatus($eqLogic->getConfiguration('gateway'));
            } else {
                switch ($this->getSubType()) {
                    case 'color':
                    $option = $_options['color'];
                    if ($this->getConfiguration('switch') == 'rgb') {
                        $xiaomihomeCmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'brightness');
                        $bright = str_pad(dechex($xiaomihomeCmd->execCmd()), 2, "0", STR_PAD_LEFT);
                        $couleur = str_replace('#','',$option);
                        if ($couleur == '000000') {
                            $bright = '00';
                        } else {
                            if ($bright == '00') {
                                $bright = dechex(50);
                            }
                        }
                        $eqLogic->checkAndUpdateCmd('rgb', $_options['color']);
                        $rgbcomplet = $bright . $couleur;
                        $option = hexdec($rgbcomplet);
                        //log::add('xiaomihome', 'debug', 'RGB : dec ' . $option . ' hex ' . $rgbcomplet . ' bright ' . $bright . ' color ' . $couleur);
                    }
                    break;
                    case 'slider':
                    $option = dechex($_options['slider']);
                    if ($this->getConfiguration('switch') == 'rgb') {
                        $xiaomihomeCmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'rgb');
                        $couleur = str_replace('#','',$xiaomihomeCmd->execCmd());
                        $bright = str_pad($option, 2, "0", STR_PAD_LEFT);
                        $eqLogic->checkAndUpdateCmd('brightness', $_options['slider']);
                        $rgbcomplet = $bright . $couleur;
                        $option = hexdec($rgbcomplet);
                        //log::add('xiaomihome', 'debug', 'RGB : dec ' . $option . ' hex ' . $rgbcomplet . ' bright ' . $bright . ' color ' . $couleur);
                    }
                    if ($this->getConfiguration('switch') == 'vol') {
                        $eqLogic->checkAndUpdateCmd('vol', $_options['slider']);
                    }
                    break;
                    case 'message':
                    $option = $_options['title'];
                    break;
                    case 'select':
                    $option = $_options['select'];
                    //log::add('xiaomihome', 'debug', 'Options ' . print_r($_options,true));
                    break;
                    default :
                    if ($this->getConfiguration('switch') == 'rgb') {
                        if ($this->getLogicalId() == 'on') {
                            $xiaomihomeCmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'rgb');
                            $couleur = str_replace('#','',$xiaomihomeCmd->execCmd());
                            $rgbcomplet = dechex(50) . $couleur;
                            $option = hexdec($rgbcomplet);
                            $eqLogic->checkAndUpdateCmd('brightness', '50');
                        } else {
                            $xiaomihomeCmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'rgb');
                            $couleur = str_replace('#','',$xiaomihomeCmd->execCmd());
                            $rgbcomplet = dechex(00) . $couleur;
                            $option = hexdec($rgbcomplet);
                            $eqLogic->checkAndUpdateCmd('brightness', '00');
                        }
                    } else {
                        $option = $this->getConfiguration('request');
                    }
                    break;
                }
                $gateway = $eqLogic->getConfiguration('gateway');
                $xiaomihome = $eqLogic->byLogicalId($gateway, 'xiaomihome');
                $password = $xiaomihome->getConfiguration('password','');
                if ($password == '') {
                    log::add('xiaomihome', 'debug', 'Mot de passe manquant sur la gateway Aquara ' . $gateway);
                    return;
                }
                exec("sudo ping -c1 " . $gateway, $output, $return_var);
                if ($return_var != 0) {
                    log::add('xiaomihome', 'debug', 'Gateway Aquara non joignable ' . $gateway);
                    return;
                }
                $token = $xiaomihome->getConfiguration('token');
                $vol = xiaomihomeCmd::byEqLogicIdAndLogicalId($xiaomihome->getId(),'vol');
                $volume = $vol->execCmd();
                $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'aquara','cmd' => 'send', 'dest' => $gateway , 'password' => $password , 'token' => $token, 'model' => $eqLogic->getConfiguration('model'), 'sid' => $eqLogic->getConfiguration('sid'), 'short_id' => $eqLogic->getConfiguration('short_id'),'switch' => $this->getConfiguration('switch'), 'request' => $option	, 'vol'=> $volume ));
                $socket = socket_create(AF_INET, SOCK_STREAM, 0);
                socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'xiaomihome'));
                socket_write($socket, $value, strlen($value));
                socket_close($socket);
            }
        }
    }
}
