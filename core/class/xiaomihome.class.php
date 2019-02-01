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
        $eqLogics = eqLogic::byType('xiaomihome', true);
        foreach ($eqLogics as $eqLogic) {
            if ($eqLogic->getConfiguration('type') == 'yeelight') {
              if ($eqLogic->pingHost($eqLogic->getConfiguration('gateway')) == false) {
                  log::add('xiaomihome', 'debug', 'Equipement Yeelight déconnecté : ' . $eqLogic->getName());
              } else {
                log::add('xiaomihome', 'debug', 'Rafraîchissement de Yeelight : ' . $eqLogic->getName());
                $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'yeelight','cmd' => 'refresh', 'model' => $eqLogic->getConfiguration('model'), 'dest' => $eqLogic->getConfiguration('gateway') , 'token' => $eqLogic->getConfiguration('password') , 'devtype' => $eqLogic->getConfiguration('short_id'), 'serial' => $eqLogic->getConfiguration('sid'), 'id' => $eqLogic->getLogicalId()));
                xiaomihome::sendDaemon($value);
              }  
            }
        }
    }

    public static function cron5() {
        $deamon_info = self::deamon_info();
        if ($deamon_info['state'] != 'ok') {
            return;
        }
        $eqLogics = eqLogic::byType('xiaomihome', true);
        foreach($eqLogics as $xiaomihome) {
            if ($xiaomihome->getConfiguration('type') == 'wifi') {
              if ($xiaomihome->pingHost($xiaomihome->getConfiguration('ipwifi')) == false) {
                  log::add('xiaomihome', 'debug', 'Offline Wifi : ' . $xiaomihome->getName());
              } else {
                log::add('xiaomihome', 'debug', 'Rafraîchissement de XiaomiWifi : ' . $xiaomihome->getName());
                $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'wifi','cmd' => 'refresh', 'model' => $xiaomihome->getConfiguration('model'), 'dest' => $xiaomihome->getConfiguration('gateway') , 'token' => $xiaomihome->getConfiguration('password') , 'devtype' => $xiaomihome->getConfiguration('short_id'), 'serial' => $xiaomihome->getConfiguration('sid')));
                xiaomihome::sendDaemon($value);
              }
            }
            if ($xiaomihome->getConfiguration('type') == 'aquara' && $xiaomihome->getConfiguration('model') == 'gateway') {
                log::add('xiaomihome', 'debug', 'Rafraîchissement de Aqara : ' . $xiaomihome->getName());
                $xiaomihome->pingHost($xiaomihome->getConfiguration('gateway'));
            }
        }
    }

    public static function createFromDef($_def,$_type) {
        event::add('jeedom::alert', array(
            'level' => 'warning',
            'page' => 'xiaomihome',
            'message' => __('Nouveau module détecté', __FILE__),
        ));
        if ($_type == 'aquara') {
            if (!isset($_def['model']) || !isset($_def['sid'])) {
                log::add('xiaomihome', 'error', 'Information manquante pour ajouter l\'équipement : ' . print_r($_def, true));
                event::add('jeedom::alert', array(
                    'level' => 'danger',
                    'page' => 'xiaomihome',
                    'message' => __('Information manquante pour ajouter l\'équipement. Inclusion impossible.', __FILE__),
                ));
                return false;
            }
            $logical_id = $_def['sid'];
            if ($_def['model'] == 'gateway') {
                $logical_id = $_def['source'];
            }
            $xiaomihome=xiaomihome::byLogicalId($logical_id, 'xiaomihome');
            if (!is_object($xiaomihome)) {
                if ($_def['model'] == 'gateway') {
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
                if (!is_array($device) || count($device) == 0) {
                    log::add('xiaomihome', 'debug', 'Impossible d\'ajouter l\'équipement : ' . print_r($_def, true));
                    return true;
                }
                $xiaomihome = new xiaomihome();
                $xiaomihome->setEqType_name('xiaomihome');
                $xiaomihome->setLogicalId($logical_id);
                $xiaomihome->setIsEnable(1);
                $xiaomihome->setIsVisible(1);
                $xiaomihome->setName($device['name'] . ' ' . $_def['sid']);
                $xiaomihome->setConfiguration('sid', $_def['sid']);
                $xiaomihome->setConfiguration('type', 'aquara');
                if (isset($device['configuration'])) {
                    foreach ($device['configuration'] as $key => $value) {
                        $xiaomihome->setConfiguration($key, $value);
                    }
                }
                event::add('jeedom::alert', array(
                    'level' => 'warning',
                    'page' => 'xiaomihome',
                    'message' => __('Module inclus avec succès ' . $_def['model'], __FILE__),
                ));
            }
            $xiaomihome->setConfiguration('short_id',$_def['short_id']);
            $xiaomihome->setConfiguration('gateway',$_def['source']);
            $xiaomihome->setStatus('lastCommunication',date('Y-m-d H:i:s'));
            $xiaomihome->setConfiguration('applyDevice','');
            $xiaomihome->save();
        } elseif ($_type == 'yeelight') {
            if (!isset($_def['capabilities']['model']) || !isset($_def['capabilities']['id'])) {
                log::add('xiaomihome', 'error', 'Information manquante pour ajouter l\'équipement : ' . print_r($_def, true));
                event::add('jeedom::alert', array(
                    'level' => 'danger',
                    'page' => 'xiaomihome',
                    'message' => __('Information manquante pour ajouter l\'équipement. Inclusion impossible.', __FILE__),
                ));
                return false;
            }
            $logical_id = $_def['capabilities']['id'];
            $xiaomihome=xiaomihome::byLogicalId($logical_id, 'xiaomihome');
            if (!is_object($xiaomihome)) {
                $device = self::devicesParameters($_def['capabilities']['model']);
                if (!is_array($device)) {
                    return true;
                }
                $xiaomihome = new xiaomihome();
                $xiaomihome->setEqType_name('xiaomihome');
                $xiaomihome->setLogicalId($logical_id);
                $xiaomihome->setName($_def['capabilities']['model'] . ' ' . $logical_id);
                $xiaomihome->setConfiguration('sid', $logical_id);
                $xiaomihome->setIsEnable(1);
                $xiaomihome->setIsVisible(1);
                if (isset($device['configuration'])) {
                    foreach ($device['configuration'] as $key => $value) {
                        $xiaomihome->setConfiguration($key, $value);
                    }
                }
                event::add('jeedom::alert', array(
                    'level' => 'warning',
                    'page' => 'xiaomihome',
                    'message' => __('Module inclus avec succès ' . $_def['capabilities']['model'], __FILE__),
                ));
            }
            $xiaomihome->setConfiguration('model',$_def['capabilities']['model']);
            $xiaomihome->setConfiguration('short_id',$_def['capabilities']['fw_ver']);
            $xiaomihome->setConfiguration('gateway',$_def['ip']);
            $xiaomihome->setConfiguration('ipwifi', $_def['ip']);
            $xiaomihome->setStatus('lastCommunication',date('Y-m-d H:i:s'));
            $xiaomihome->setConfiguration('applyDevice','');
            $xiaomihome->setConfiguration('type', 'yeelight');
            $xiaomihome->save();
        }
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
        log::remove(__CLASS__ . '_update');
        log::remove(__CLASS__ . '_node');
        self::deamon_stop();
        $deamon_info = self::deamon_info();
        if ($deamon_info['launchable'] != 'ok') {
            throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
        }
	if (config::byKey('network::disableMangement')) {
		$url = network::getNetworkAccess('internal');
	} else {
		$url = network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp');
	}
        $xiaomihome_path = realpath(dirname(__FILE__) . '/../../resources/xiaomihomed');
        $cmd = '/usr/bin/python ' . $xiaomihome_path . '/xiaomihomed.py';
        $cmd .= ' --loglevel ' . log::convertLogLevel(log::getLogLevel('xiaomihome'));
        $cmd .= ' --socketport ' . config::byKey('socketport', 'xiaomihome');
        $cmd .= ' --callback ' . $url . '/plugins/xiaomihome/core/php/jeeXiaomiHome.php';
        $cmd .= ' --apikey ' . jeedom::getApiKey('xiaomihome');
        $cmd .= ' --cycle ' . config::byKey('cycle', 'xiaomihome');
        $cmd .= ' --pid ' . jeedom::getTmpFolder('xiaomihome') . '/deamon.pid';
        log::add('xiaomihome', 'info', 'Lancement démon xiaomihome : ' . $cmd);
        $result = exec($cmd . ' >> ' . log::getPathToLog('xiaomihome') . ' 2>&1 &');
        $i = 0;
        while ($i < 30) {
            $deamon_info = self::deamon_info();
            if ($deamon_info['state'] == 'ok') {
                break;
            }
            sleep(1);
            $i++;
        }
        if ($i >= 30) {
            log::add('xiaomihome', 'error', 'Impossible de lancer le démon xiaomihomed. Vérifiez le log.', 'unableStartDeamon');
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
        $return['progress_file'] = jeedom::getTmpFolder('xiaomihome') . '/dependance';
        $cmd = "pip list | grep pycrypto";
        exec($cmd, $output, $return_var);
        $cmd = "pip list | grep future";
        exec($cmd, $output2, $return_var);
        $return['state'] = 'nok';
        if (array_key_exists(0,$output) && array_key_exists(0,$output2)) {
            if ($output[0] != "" && $output2[0] != "") {
                $return['state'] = 'ok';
            }
        }
        return $return;
    }

    public static function dependancy_install() {
        $dep_info = self::dependancy_info();
        log::remove(__CLASS__ . '_dep');
        if ($dep_info['state'] != 'ok') {
            return array('script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder('xiaomihome') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_dep'));
        } else {
            return array('script' => dirname(__FILE__) . '/../../resources/install_force_#stype#.sh ' . jeedom::getTmpFolder('xiaomihome') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_dep'));
        }
    }

    public static function discover($_mode) {
        if ($_mode == 'wifi') {
            $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'cmd' => 'scanwifi'));
        } else {
            $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'cmd' => 'scanyeelight'));
        }
        $socket = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'xiaomihome'));
        socket_write($socket, $value, strlen($value));
        socket_close($socket);
    }

    public function get_wifi_info(){
        if ($this->getConfiguration('type') == 'wifi' && $this->getConfiguration('ipwifi') != ''){
            $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'wifi','cmd' => 'discover', 'dest' => $this->getConfiguration('ipwifi') , 'token' => $this->getConfiguration('password') , 'model' => $this->getConfiguration('model')));
            $socket = socket_create(AF_INET, SOCK_STREAM, 0);
            socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'xiaomihome'));
            socket_write($socket, $value, strlen($value));
            socket_close($socket);
        }
    }

    public function inclusion_mode(){
        $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'aquara','cmd' => 'send', 'password' => $this->getConfiguration('password',''),'sidG' => $this->getConfiguration('sid'), 'dest' => $this->getConfiguration('gateway') , 'token' => $this->getConfiguration('password') , 'model' => $this->getConfiguration('model'), 'sid' => $this->getConfiguration('sid'), 'short_id' => $this->getConfiguration('short_id'),'switch' => 'join_permission', 'request' => 'yes'));
        $socket = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'xiaomihome'));
        socket_write($socket, $value, strlen($value));
        socket_close($socket);
    }

    public function exclusion_mode($target_sid){
        $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'aquara','cmd' => 'send', 'password' => $this->getConfiguration('password',''),'sidG' => $this->getConfiguration('sid'), 'dest' => $this->getConfiguration('gateway') , 'token' => $this->getConfiguration('password') , 'model' => $this->getConfiguration('model'), 'sid' => $this->getConfiguration('sid'), 'short_id' => $this->getConfiguration('short_id'),'switch' => 'remove_device', 'request' => $target_sid));
        $socket = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'xiaomihome'));
        socket_write($socket, $value, strlen($value));
        socket_close($socket);
    }

    public function getImage() {
        if (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $this->getConfiguration('model') . '/' . $this->getConfiguration('model') . '.png')) {
            return 'plugins/xiaomihome/core/config/devices/' . $this->getConfiguration('model') . '/' . $this->getConfiguration('model') . '.png';
        } else {
            return 'plugins/xiaomihome/plugin_info/xiaomihome_icon.png';
        }
    }

    public function preSave() {
        if ($this->getLogicalId() != $this->getConfiguration('ipwifi') && $this->getConfiguration('ipwifi') != ''){
            $this->setLogicalId($this->getConfiguration('ipwifi'));
        }
        if ($this->getConfiguration('type') == 'yeelight'){
            if ($this->getConfiguration('gateway') != $this->getConfiguration('ipwifi') && $this->getConfiguration('ipwifi') != ''){
                $this->setConfiguration('gateway',$this->getConfiguration('ipwifi'));
            }
        }
    }

    public function postSave() {
        if (($this->getConfiguration('applyDevice') != $this->getConfiguration('model')) && $this->getConfiguration('type') == '') {
            foreach ($this->getCmd() as $cmd) {
                $cmd->remove();
            }
            $this->applyModuleConfiguration($this->getConfiguration('applyDevice'));
        } else if ($this->getConfiguration('type') == 'aquara' && $this->getConfiguration('applyDevice') != $this->getConfiguration('model')) {
			$this->applyModuleConfiguration($this->getConfiguration('model'));
		} else if ($this->getConfiguration('type') == 'yeelight' && $this->getConfiguration('applyDevice') != $this->getConfiguration('model')) {
			$this->applyModuleConfiguration($this->getConfiguration('model'));
		} else if ($this->getConfiguration('type') == 'wifi' && $this->getConfiguration('applyDevice') != $this->getConfiguration('model')) {
			$this->applyModuleConfiguration($this->getConfiguration('model'));
		}
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
        $this->setConfiguration('applyDevice', $model);
        $this->setConfiguration('applyDevice2', $model);
        $this->setConfiguration('model',$model);
        $this->save();
        //$this->import($device);
        if ($this->getConfiguration('model') == '') {
            return true;
        }
        $device = self::devicesParameters($model);
        if (!is_array($device)) {
            return true;
        }
        event::add('jeedom::alert', array(
            'level' => 'warning',
            'page' => 'xiaomihome',
            'message' => __('Périphérique reconnu, intégration en cours...', __FILE__),
        ));
        if (isset($device['configuration'])) {
            foreach ($device['configuration'] as $key => $value) {
                $this->setConfiguration($key, $value);
            }
        }
        if (isset($device['category'])) {
            foreach ($device['category'] as $key => $value) {
                $this->setCategory($key, $value);
            }
        }
        $cmd_order = 0;
        $link_cmds = array();
        $link_actions = array();
        event::add('jeedom::alert', array(
            'level' => 'warning',
            'page' => 'xiaomihome',
            'message' => __('Création des commandes...', __FILE__),
        ));

        $ids = array();
        $arrayToRemove = [];
        if (isset($device['commands'])) {
            foreach ($this->getCmd() as $eqLogic_cmd) {
                $exists = 0;
                foreach ($device['commands'] as $command) {
                    if ($command['logicalId'] == $eqLogic_cmd->getLogicalId()) {
                        $exists++;
                    }
                }
                if ($exists < 1) {
                    $arrayToRemove[] = $eqLogic_cmd;
                }
            }
            foreach ($arrayToRemove as $cmdToRemove) {
                try {
                    $cmdToRemove->remove();
                } catch (Exception $e) {

                }
            }
            foreach ($device['commands'] as $command) {
                $cmd = null;
                foreach ($this->getCmd() as $liste_cmd) {
                    if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
                    || (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
                        $cmd = $liste_cmd;
                        break;
                    }
                }
                try {
                    if ($cmd == null || !is_object($cmd)) {
                        $cmd = new xiaomihomeCmd();
                        $cmd->setOrder($cmd_order);
                        $cmd->setEqLogic_id($this->getId());
                    } else {
                        $command['name'] = $cmd->getName();
                        if (isset($command['display'])) {
                            unset($command['display']);
                        }
                    }
                    utils::a2o($cmd, $command);
                    $cmd->setConfiguration('logicalId', $cmd->getLogicalId());
                    $cmd->save();
                    if (isset($command['value'])) {
                        $link_cmds[$cmd->getId()] = $command['value'];
                    }
                    if (isset($command['configuration']) && isset($command['configuration']['updateCmdId'])) {
                        $link_actions[$cmd->getId()] = $command['configuration']['updateCmdId'];
                    }
                    $cmd_order++;
                } catch (Exception $exc) {

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
        $this->save();
        if (isset($device['afterInclusionSend']) && $device['afterInclusionSend'] != '') {
            event::add('jeedom::alert', array(
                'level' => 'warning',
                'page' => 'xiaomihome',
                'message' => __('Envoi des commandes post-inclusion...', __FILE__),
            ));
            sleep(5);
            $sends = explode('&&', $device['afterInclusionSend']);
            foreach ($sends as $send) {
                foreach ($this->getCmd('action') as $cmd) {
                    if (strtolower($cmd->getName()) == strtolower(trim($send))) {
                        $cmd->execute();
                    }
                }
                sleep(1);
            }

        }
        sleep(2);
        event::add('jeedom::alert', array(
            'level' => 'warning',
            'page' => 'xiaomihome',
            'message' => '',
        ));
    }
    public static function receiveAquaraData($id, $model, $key, $value) {
        $xiaomihome = self::byLogicalId($id, 'xiaomihome');
        if (is_object($xiaomihome)) {
            if ($key == 'humidity' || $key == 'temperature' || $key == 'pressure') {
                $value = $value / 100;
            }
            else if ($key == 'rotate') {
                $mvalues = explode(',',$value);
                $xiaomihome->checkAndUpdateCmd('rotatetime', $mvalues[1]);
                $value = $mvalues[0] * 3.6;
                if ($value  > 0) {
                    $xiaomihome->checkAndUpdateCmd('status', 'rotate_right');
                } else {
                    $xiaomihome->checkAndUpdateCmd('status', 'rotate_left');
                }
            }
            else if ($key == 'rgb') {
                $value = str_pad(dechex($value), 8, "0", STR_PAD_LEFT);
                $light = hexdec(substr($value, 0, 2));
                $value = '#' . substr($value, -6);
                $xiaomihome->checkAndUpdateCmd('brightness', $light);
                $xiaomihome->checkAndUpdateCmd('rgb', $value);
            }
            else if ($key == 'voltage') {
                if ($model != 'plug' && $model != 'gateway' && $model != 'natgas') {
                    if ($value>=3100) {
                        $battery = 100;
                    } else if ($value<3100 && $value>2800) {
                        $battery = ceil(($value - 2800) *0.33);
                    } else {
                        $battery = 1;
                    }
                    $xiaomihome->checkAndUpdateCmd('battery', $battery);
                    $xiaomihome->setConfiguration('battery',$battery);
                    $xiaomihome->batteryStatus($battery);
                    $xiaomihome->save();
                }

                $value = $value /1000;
            }
            else if ($key == 'density') {
                if ($model == 'smoke') {
                    if ($value > 1000) {
                        $visibility = 0;
                    } else {
                        $visibility = 100 - $value/10;
                    }
                    $xiaomihome->checkAndUpdateCmd('visibility', $visibility);
                }
            }
            else if ($key == 'power_consumed') {
                $value = $value /1000;
            }
            else if ($key == 'no_motion') {
                $xiaomihome->checkAndUpdateCmd('status', 0);
            }
            else if ($key == 'no_close') {
                $xiaomihome->checkAndUpdateCmd('status', 1);
            }
            else if (($key == 'channel_0' || $key == 'channel_1') && ($model == 'ctrl_neutral1' || $model == 'ctrl_neutral2')) {
                $value = ($value == 'on') ? 1 : 0;
            }
            else if ($key == 'status') {
                if ($model == 'motion' || $model == 'sensor_motion.aq2') {
                    if ($value == 'motion') {
                        $xiaomihome->checkAndUpdateCmd('no_motion', 0);
                        $value = 1;
                    } else {
                        $value = 0;
                    }
                }
                else if ($model == 'magnet' || $model == 'sensor_magnet.aq2') {
                    if ($value == 'open') {
                        $value = 1;
                    } else {
                        $value = 0;
                        $xiaomihome->checkAndUpdateCmd('no_close', 0);
                    }
                }
                else if ($model == 'sensor_wleak.aq1') {
                    $value = ($value == 'leak') ? 1 : 0;
                }
                else if ($model == 'plug' || $model == '86plug') {
                    $value = ($value == 'on') ? 1 : 0;
                }
            }
            $xiaomihome->checkAndUpdateCmd($key, $value);
            /*$xiaomihomeCmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($xiaomihome->getId(),$key);
            if (is_object($xiaomihomeCmd)) {
            $xiaomihomeCmd->setConfiguration('value',$value);
            $xiaomihomeCmd->save();
            $xiaomihomeCmd->event($value);
        }*/
    }
}

public function pingHost ($host, $timeout = 1) {
    exec(system::getCmdSudo() . "ping -c1 " . $host, $output, $return_var);
    if ($return_var == 0) {
        $result = true;
        $this->checkAndUpdateCmd('online', 1);
    } else {
        $result = false;
        $this->checkAndUpdateCmd('online', 0);
    }
    return $result;
}

public static function sendDaemon ($value) {
    $deamon_info = self::deamon_info();
    if ($deamon_info['state'] != 'ok') {
        return;
    }
    $socket = socket_create(AF_INET, SOCK_STREAM, 0);
    socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'xiaomihome'));
    socket_write($socket, $value, strlen($value));
    socket_close($socket);
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
                if ($eqLogic->pingHost($eqLogic->getConfiguration('gateway')) == false) {
                    log::add('xiaomihome', 'debug', 'Equipement Yeelight déconnecté : ' . $eqLogic->getName());
                    return;
                }
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
                $sup = '';
                if ($eqLogic->getConfiguration('model','') == 'desklamp'){
                    $brightCmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'brightness');
                    $sup = $brightCmd->execCmd();
                }
                if ($this->getLogicalId() != 'refresh') {
                    if ($option == '000000') {
                        $request ='turn off';
                    } else {
                        $request =$this->getConfiguration('request');
                    }
                    $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'yeelight','cmd' => 'send', 'dest' => $eqLogic->getConfiguration('gateway') , 'model' => $eqLogic->getConfiguration('model'), 'sid' => $eqLogic->getConfiguration('sid'), 'short_id' => $eqLogic->getConfiguration('short_id'),'command' => $request, 'option' => $option, 'id' => $eqLogic->getLogicalId(), 'sup' => $sup));
                    xiaomihome::sendDaemon($value);
                } else {
                    $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'yeelight','cmd' => 'refresh', 'model' => $eqLogic->getConfiguration('model'), 'dest' => $eqLogic->getConfiguration('gateway') , 'token' => $eqLogic->getConfiguration('password') , 'devtype' => $eqLogic->getConfiguration('short_id'), 'serial' => $eqLogic->getConfiguration('sid'), 'id' => $eqLogic->getLogicalId()));
                    xiaomihome::sendDaemon($value);
                    return;
                }
            } elseif ($eqLogic->getConfiguration('type') == 'aquara'){
                if ($this->getLogicalId() == 'refresh') {
                    $gateway = $eqLogic->getConfiguration('gateway');
                    $xiaomihome = $eqLogic->byLogicalId($gateway, 'xiaomihome');
                    if ($xiaomihome->pingHost($gateway) == false) {
                        log::add('xiaomihome', 'debug', 'Offline Aqara : ' . $xiaomihome->getName());
                        return;
                    }
                    $password = $xiaomihome->getConfiguration('password','');
                    $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'aquara','model' => 'read','cmd' => 'refresh', 'dest' => $gateway , 'password' => $password,'sidG' => $xiaomihome->getConfiguration('sid'), 'sid' => $eqLogic->getConfiguration('sid')));
                    xiaomihome::sendDaemon($value);
                    return;
                }

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
                    }
                    break;
                    case 'slider':
                    $option = $_options['slider'];
                    //$option = dechex($_options['slider']);
                    if ($this->getConfiguration('switch') == 'rgb') {
                        $xiaomihomeCmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'rgb');
                        $couleur = str_replace('#','',$xiaomihomeCmd->execCmd());
                        $bright = str_pad($option, 2, "0", STR_PAD_LEFT);
                        $eqLogic->checkAndUpdateCmd('brightness', $_options['slider']);
                        $rgbcomplet = dechex($bright) . $couleur;
                        $option = hexdec($rgbcomplet);
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
                if ($xiaomihome->pingHost($gateway) == false) {
                    log::add('xiaomihome', 'debug', 'Offline Aqara : ' . $xiaomihome->getName());
                    return;
                }
                $password = $xiaomihome->getConfiguration('password','');
                if ($password == '') {
                    log::add('xiaomihome', 'debug', 'Mot de passe manquant sur la passerelle Aqara ' . $gateway);
                    return;
                }
                if ($this->getLogicalId() == 'mid-scenar') {
                    $volume = intval($_options['message']);
                } else {
                    $vol = xiaomihomeCmd::byEqLogicIdAndLogicalId($xiaomihome->getId(),'vol');
                    $volume = $vol->execCmd();
                }
                $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'aquara','cmd' => 'send', 'dest' => $gateway , 'password' => $password , 'model' => $eqLogic->getConfiguration('model'),'sidG' => $xiaomihome->getConfiguration('sid'), 'sid' => $eqLogic->getConfiguration('sid'), 'short_id' => $eqLogic->getConfiguration('short_id'),'switch' => $this->getConfiguration('switch'), 'request' => $option, 'vol'=> $volume ));
                xiaomihome::sendDaemon($value);
            }
            else {
                if ($eqLogic->pingHost($eqLogic->getConfiguration('ipwifi')) == false) {
                    log::add('xiaomihome', 'debug', 'Offline Wifi : ' . $eqLogic->getName());
                    return;
                }
                if ($this->getLogicalId() == 'refresh') {
                    $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'wifi','cmd' => 'refresh', 'model' => $eqLogic->getConfiguration('model'), 'dest' => $eqLogic->getConfiguration('gateway') , 'token' => $eqLogic->getConfiguration('password') , 'devtype' => $eqLogic->getConfiguration('short_id'), 'serial' => $eqLogic->getConfiguration('sid')));
                    xiaomihome::sendDaemon($value);
                    return;
                }
                $params = $this->getConfiguration('params');
                switch ($this->getSubType()) {
                    case 'color':
                    $option = $_options['color'];
                    break;
                    case 'slider':
                    $option = $_options['slider'];
                    $params = trim(str_replace('#slider#',$option, $params));
                    break;
                    case 'message':
                    $option = $_options['title'];
                    break;
                    case 'select':
                    $option = $_options['select'];
                    break;
                    default :
                    $option = '';
                }
                $value = json_encode(array('apikey' => jeedom::getApiKey('xiaomihome'), 'type' => 'wifi','cmd' => 'send', 'model' => $eqLogic->getConfiguration('model'), 'dest' => $eqLogic->getConfiguration('gateway') , 'token' => $eqLogic->getConfiguration('password') , 'devtype' => $eqLogic->getConfiguration('short_id'), 'serial' => $eqLogic->getConfiguration('sid'), 'method' => $this->getConfiguration('request'),'param' => $params));
                xiaomihome::sendDaemon($value);
            }
        }
    }
}
