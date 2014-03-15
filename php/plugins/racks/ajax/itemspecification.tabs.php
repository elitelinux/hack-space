<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!isset($_POST["id"])) {
	exit();
}

$PluginRacksItemSpecification = new PluginRacksItemSpecification();

$PluginRacksItemSpecification->checkGlobal("r");

if (empty($_POST["id"])) {
	switch($_POST['plugin_racks_tab']) {
		default :
			break;
	}
} else {
   $target = $CFG_GLPI['root_doc']."/plugins/racks/front/itemspecification.form.php";
	switch($_POST['plugin_racks_tab']) {
		case "all" :
			$_SESSION['glpi_plugin_racks_tab']="all";
			$PluginRacksItemSpecification->showList($target,$_POST["id"],'ComputerModel');
			$PluginRacksItemSpecification->showList($target,$_POST["id"],'NetworkEquipmentModel');
			$PluginRacksItemSpecification->showList($target,$_POST["id"],'PeripheralModel');
			$PluginRacksItemSpecification->showList($target,$_POST["id"],'PluginRacksOtherModel');
			break;
		case 'ComputerModel' :
			$_SESSION['glpi_plugin_racks_tab']='ComputerModel';
			$PluginRacksItemSpecification->showList($target,$_POST["id"],$_SESSION['glpi_plugin_racks_tab']);
			break;
		case 'NetworkEquipmentModel' :
			$_SESSION['glpi_plugin_racks_tab']='NetworkEquipmentModel';
			$PluginRacksItemSpecification->showList($target,$_POST["id"],$_SESSION['glpi_plugin_racks_tab']);
			break;
		case 'PeripheralModel' :
			$_SESSION['glpi_plugin_racks_tab']='PeripheralModel';
			$PluginRacksItemSpecification->showList($target,$_POST["id"],$_SESSION['glpi_plugin_racks_tab']);
			break;
		case 'PluginRacksOtherModel' :
			$_SESSION['glpi_plugin_racks_tab']='PluginRacksOtherModel';
			$PluginRacksItemSpecification->showList($target,$_POST["id"],$_SESSION['glpi_plugin_racks_tab']);
			break;
		default :
			break;
	}
}

Html::ajaxFooter();

?>