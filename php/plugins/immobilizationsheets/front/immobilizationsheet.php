<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Immobilizationsheets plugin for GLPI
 Copyright (C) 2003-2011 by the Immobilizationsheets Development Team.

 https://forge.indepnet.net/projects/immobilizationsheets
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Immobilizationsheets.

 Immobilizationsheets is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Immobilizationsheets is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Immobilizationsheets. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

include ('../../../inc/includes.php');

include_once (GLPI_ROOT."/lib/ezpdf/class.ezpdf.php");

Html::header(PluginImmobilizationsheetsConfig::getTypeName(2),'',"plugins","immobilizationsheets");

if(isset($_GET)) $tab = $_GET;
if(empty($tab) && isset($_POST)) $tab = $_POST;
if(!isset($tab["id"])) $tab["id"] = "";
if(!isset($tab["itemtype"])) $tab["itemtype"] = "";

$config=new PluginImmobilizationsheetsConfig();

if ($config->canView() || Session::haveRight("config","w")) {

	if (isset($_POST["massiveaction"])) {
		
		$config->showForm($_POST["item_item"]);
		echo "<br><div align='center'>";
		echo "<a href=\"export.php?id=".$_POST["item_item"]."&amp;itemtype=".$_POST["itemtype"]."\" 
               target=\"blank\" class='icon_consol'>";
		echo __('Generation of immobilization sheet', 'immobilizationsheets')." : ";
      
      $itemtable=getTableForItemType($_POST["itemtype"]);

		$query = "SELECT * 
					FROM `".$itemtable."` 
					WHERE `id` = '".$_POST["item_item"]."'";
		$result = $DB->query($query);
		while ($data=$DB->fetch_array($result)) {
			echo $data["name"];
		}
		echo "</a></div>";
		
	} else {

		$config->showForm($tab["id"]);
	}

} else {
	Html::displayRightError();
}

Html::footer();

?>