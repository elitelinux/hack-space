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

Plugin::load('racks',true);
// Make a select box

if (isset($_POST["modeltable"])) {

	// Link to user for search only > normal users
	$link="dropdownValue.php";

	$rand=mt_rand();
   
   $itemtype = substr($_POST['modeltable'], 0, -5);
   $modelfield = getForeignKeyFieldForTable(getTableForItemType($_POST['modeltable']));
   $table = getTableForItemType($itemtype);
	$use_ajax=false;
	if ($CFG_GLPI["use_ajax"]&&countElementsInTable($table)>$CFG_GLPI["ajax_limit_count"]) {
		$use_ajax=true;
	}

   $params=array('searchText'=>'__VALUE__',
                  'modeltable'=>$_POST["modeltable"],
                  'modelfield'=>$modelfield,
                  'itemtype'=>$itemtype,
                  'rand'=>$rand,
                  'myname'=>$_POST["myname"],
                  );

	if (isset($_POST['value'])) {
		$params['value']=$_POST['value'];
	}
	if (isset($_POST['entity_restrict'])) {
		$params['entity_restrict']=$_POST['entity_restrict'];
	}
	
	$default="<select name='".$_POST["myname"]."'><option value='0'>".Dropdown::EMPTY_VALUE."</option></select>";
	Ajax::Dropdown($use_ajax,"/plugins/racks/ajax/$link",$params,$default,$rand);

}
	
?>