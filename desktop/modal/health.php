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

if (!isConnect('admin')) {
	throw new Exception('401 - Accès non autorisé');
}
$plugin = plugin::byId('xiaomihome');
$eqLogics = xiaomihome::byType('xiaomihome');
?>

<table class="table table-condensed tablesorter" id="table_healthxiaomihome">
	<thead>
		<tr>
			<th>{{Image}}</th>
			<th>{{Module}}</th>
			<th>{{ID}}</th>
			<th>{{Modèle}}</th>
			<th>{{Identifiant}}</th>
			<th>{{Online}}</th>
			<th>{{Statut}}</th>
			<th>{{Type}}</th>
			<th>{{Batterie}}</th>
			<th>{{Dernière communication}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
	 <?php
foreach ($eqLogics as $eqLogic) {
	if (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $eqLogic->getConfiguration('model') . '/' . $eqLogic->getConfiguration('model') . '.png')) {
		$image = '<img src="plugins/xiaomihome/core/config/devices/' . $eqLogic->getConfiguration('model') . '/' . $eqLogic->getConfiguration('model') . '.png' . '" height="55" width="55" />';
	} else {
		$image = '<img src="' . $plugin->getPathImgIcon() . '" height="55" width="55" />';
	}
	echo '<tr><td>' . $image . '</td><td><a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getHumanName(true) . '</a></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getId() . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('model') . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('sid') . '</span></td>';
	$online = $eqLogic->getCmd('info','online');
	if (is_object($online)){
		$onlinevalue= $online->execCmd();
	} else {
		$onlinevalue = '';
	}
	$onlineecho = '<span class="label label-success" style="font-size : 1em; cursor : default;">{{NA}}</span>';
	if ($onlinevalue == 1){
		$onlineecho = '<span class="label label-success" style="font-size : 1em; cursor : default;">{{OK}}</span>';
	} else if ($onlinevalue !== '' && $onlinevalue == 0){
		$onlineecho = '<span class="label label-danger" style="font-size : 1em; cursor : default;">{{KO}}</span>';
	}
	echo '<td>' . $onlineecho . '</td>';
	$status = '<span class="label label-success" style="font-size : 1em; cursor : default;">{{OK}}</span>';
	if ($eqLogic->getStatus('state') == 'nok') {
		$status = '<span class="label label-danger" style="font-size : 1em; cursor : default;">{{NOK}}</span>';
	}
	echo '<td>' . $status . '</td>';
	$battery_status = '<span class="label label-success" style="font-size : 1em;">{{OK}}</span>';
	$battery = $eqLogic->getStatus('battery');
	if ($battery == '') {
		$battery_status = '<span class="label label-primary" style="font-size : 1em;" title="{{Secteur}}"><i class="fa fa-plug"></i></span>';
  } elseif ($battery < 20) {
		$battery_status = '<span class="label label-danger" style="font-size : 1em;">' . $battery . '%</span>';
	} elseif ($battery < 60) {
		$battery_status = '<span class="label label-warning" style="font-size : 1em;">' . $battery . '%</span>';
	} elseif ($battery > 60) {
		$battery_status = '<span class="label label-success" style="font-size : 1em;">' . $battery . '%</span>';
	} else {
		$battery_status = '<span class="label label-primary" style="font-size : 1em;">' . $battery . '%</span>';
	}
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('type') . '</span></td>';
	echo '<td>' . $battery_status . '</td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
}
?>
	</tbody>
</table>
