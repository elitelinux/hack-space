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

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"dropdownValue.php")) {
	include ('../../../inc/includes.php');
	header("Content-Type: text/html; charset=UTF-8");
	Html::header_nocache();
	Plugin::load('racks',true);
}

if (!defined('GLPI_ROOT')) {
	die("Can not acces directly to this file");
}

Session::checkLoginUser();

$item = new $_POST['itemtype']();
$table = getTableForItemType($_POST['itemtype']);

// Security
if (!TableExists($table)) {
	exit();
}

// Make a select box with preselected values
if (!isset ($_POST["limit"]))
	$_POST["limit"] = $_SESSION["glpidropdown_chars_limit"];
$first = true;
$where = "WHERE ";

if ($item->maybeDeleted()) {
	if (!$first)
		$where .= " AND ";
	else
		$first = false;
	$where .= " `is_deleted` = '0' ";
}
if ($item->maybeTemplate()) {
	if (!$first)
		$where .= " AND ";
	else
		$first = false;
	$where .= " `is_template` = '0' ";
}

$NBMAX = $CFG_GLPI["dropdown_max"];
$LIMIT = "LIMIT 0,$NBMAX";
if ($_POST['searchText'] == $CFG_GLPI["ajax_wildcard"])
	$LIMIT = "";

if (!$first)
	$where .= " AND ";
else
	$first = false;

//why ?
$PluginRacksRack_Item=new PluginRacksRack_Item();

if (in_array(get_class($item), PluginRacksRack::getTypes())) {

	$where .= " `" .$table. "`.`id` <> '" . $_POST['value'] . "' ";
	
	if ($item->isEntityAssign()) {

			$multi=$item->maybeRecursive();

			$field="entities_id";
			$add_order=" entities_id, ";

			if (isset($_POST["entity_restrict"]) && !($_POST["entity_restrict"]<0)) {
				$where.=getEntitiesRestrictRequest(" AND ",$table,$field,$_POST["entity_restrict"]);
				if (is_array($_POST["entity_restrict"]) && count($_POST["entity_restrict"])>1) {
					$multi=true;	
				}
			} else {
				$where.=getEntitiesRestrictRequest(" AND ",$table,$field);
				if (count($_SESSION['glpiactiveentities'])>1) {
					$multi=true;	
				}
			}
		}
	
	$field = "name";
	
	if ($_POST['searchText'] != $CFG_GLPI["ajax_wildcard"])
		$where .= " AND $field " .
		Search::makeTextSearch($_POST['searchText']);
	
	$where .= " AND `" . $table . "`.`id` NOT IN (0";
	$where .= $PluginRacksRack_Item->findItems($DB, $_POST['modeltable']);
	$where .= ") ";
   
	$query = "SELECT `" . $table . "`.`name` AS name,`" . $table . "`.`entities_id` AS entities_id,`" . $table . "`.`id`, `glpi_plugin_racks_itemspecifications`.`id` AS spec " .
			" FROM `glpi_plugin_racks_itemspecifications`,`" . $table . "` " .
			" $where AND `glpi_plugin_racks_itemspecifications`.`model_id` = `" . $table . "`.`".$_POST['modelfield']."` 
			AND `glpi_plugin_racks_itemspecifications`.`itemtype` = '" . $_POST['modeltable'] . "' " .
			" ORDER BY $add_order  `" . $table . "`.`name` $LIMIT";
	$result = $DB->query($query);

} else {
	$multi=false;
	$query = "SELECT `glpi_plugin_racks_othermodels`.`id`,`glpi_plugin_racks_othermodels`.`name`,`glpi_plugin_racks_othermodels`.`comment`, `glpi_plugin_racks_itemspecifications`.`id` AS spec 
			FROM `glpi_plugin_racks_othermodels`,`glpi_plugin_racks_itemspecifications` 
			WHERE `glpi_plugin_racks_itemspecifications`.`model_id` = `glpi_plugin_racks_othermodels`.`id` AND `glpi_plugin_racks_itemspecifications`.`itemtype` = '".$_POST['modeltable']."' " .
			" ORDER BY `glpi_plugin_racks_othermodels`.`name` $LIMIT";
	$result = $DB->query($query);

}
echo "<select id='dropdown_" . $_POST["myname"] . $_POST["rand"] . "' name=\"" . $_POST['myname'] . "\" size='1'>";

if ($_POST['searchText'] != $CFG_GLPI["ajax_wildcard"] && $DB->numrows($result) == $NBMAX)
	echo "<option value=\"0\">--" . __('Limited view') . "--</option>";

echo "<option value=\"0\">".Dropdown::EMPTY_VALUE."</option>";
$number = $DB->numrows($result);

if ($number != 0)
	$output = Dropdown::getDropdownName($_POST['modeltable'], $_POST['value']);
if (!empty ($output) && $output != "&nbsp;") {
	echo "<option selected value='" . $_POST['value'] . "'>" . $output . "</option>";
}

if ($DB->numrows($result)) {
	$prev=-1;
	while ($data = $DB->fetch_array($result)) {
		$output = $data["name"];
		$id = $data['id'];
		$addcomment = "";
		
		if ($multi && $data["entities_id"]!=$prev) {
			if ($prev>=0) {
				echo "</optgroup>";
			}
			$prev=$data["entities_id"];
			echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
		}
		
		$PluginRacksItemSpecification = new PluginRacksItemSpecification();
		if ($PluginRacksItemSpecification->GetfromDB($data['spec'])) {
			
			$output.= " - ".$PluginRacksItemSpecification->fields["size"]."U";
		}

		if (isset ($data["comment"]))
			$addcomment = " - " . $data["comment"];

		if (empty ($output))
			$output = "($id)";
			
		echo "<option value=\"" . $_POST["modeltable"] . ";$id;".$data['spec']."\" title=\"$output$addcomment\">" . substr($output, 0, $_POST["limit"]) . "</option>";
	}
}
echo "</select>";

if (isset($_POST["comment"])&&$_POST["comment"]) {
	$paramscomments=array('value'=>'__VALUE__','table'=>$_POST["table"]);
	Ajax::UpdateItemOnSelectEvent("dropdown_".$_POST["myname"].$_POST["rand"],"comments_".$_POST["myname"].$_POST["rand"],$CFG_GLPI["root_doc"]."/ajax/comments.php",$paramscomments,false);
}

?>