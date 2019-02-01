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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - {{Accès non autorisé}}', __FILE__));
    }

	if (init('action') == 'autoDetectModule') {
		$eqLogic = xiaomihome::byId(init('id'));
		if (!is_object($eqLogic)) {
			throw new Exception(__('XiaomiHome eqLogic non trouvé : ', __FILE__) . init('id'));
		}
		if (init('createcommand') == 1){
			foreach ($eqLogic->getCmd() as $cmd) {
				$cmd->remove();
			}
		}
		$eqLogic->applyModuleConfiguration($eqLogic->getConfiguration('model'));
		ajax::success();
	}

	if (init('action') == 'discover') {
		xiaomihome::discover(init('mode'));
		ajax::success();
	}
	
	if (init('action') == 'InclusionGateway') {
		$eqLogic = xiaomihome::byId(init('id'));
		ajax::success($eqLogic->inclusion_mode());
	}
	
	if (init('action') == 'ExclusionGateway') {
		$eqLogics = eqLogic::byType('xiaomihome');
		foreach ($eqLogics as $eqLogicGateway) {
			if ($eqLogicGateway->getConfiguration('type') == 'aquara' && $eqLogicGateway->getConfiguration('model') == 'gateway') {
				if ($eqLogicGateway->getConfiguration('gateway') == init('gateway')){
					$eqLogic = $eqLogicGateway;
					break;
				}
			}
		 }
		ajax::success($eqLogic->exclusion_mode(init('id')));
	}

	if (init('action') == 'sync') {
		$eqLogic = xiaomihome::byId(init('id'));
		if (!is_object($eqLogic)) {
			throw new Exception(__('XiaomiHome eqLogic non trouvé : ', __FILE__) . init('id'));
		}
		ajax::success($eqLogic->get_wifi_info());
	}

    throw new Exception(__('{{Aucune méthode correspondante à}} : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
?>
