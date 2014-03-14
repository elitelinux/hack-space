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

$immo_item=new PluginImmobilizationsheetsItem();
$immo_item->checkGlobal("r");

$itemtype = $_SESSION["plugin_immobilizationsheets"]["itemtype"];

unset($_SESSION["plugin_immobilizationsheets"]["itemtype"]);

$tab_id = unserialize($_SESSION["plugin_immobilizationsheets"]["tab_id"]);

unset($_SESSION["plugin_immobilizationsheets"]["tab_id"]);

$nb_id = count($tab_id);

$immo_item->mainPdf($itemtype,$tab_id,1,1);

if ($_SESSION["plugin_immobilizationsheets"]["nb_items"]==$nb_id) {

	$config= new PluginImmobilizationsheetsConfig();
	
	if ($config->getFromDB(1)) {
	
		if ($config->fields["use_backup"]==1) {
						
			$REDIRECT=$CFG_GLPI['root_doc'].'/plugins/immobilizationsheets/front/immobilizationsheet.php';
			
			unset($_SESSION["plugin_immobilizationsheets"]["nb_items"]);
			Html::redirect(Toolbox::getItemTypeSearchURL($itemtype));
                        
		} else {

         $immo_item->mainPdf($itemtype,$tab_id,0,1);
		
		}
	}
}	

?>