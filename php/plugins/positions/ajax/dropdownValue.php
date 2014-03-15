<?php
/*
 * @version $Id: HEADER 15930 2013-02-07 09:47:55Z tsmr $
 -------------------------------------------------------------------------
 Positions plugin for GLPI
 Copyright (C) 2003-2011 by the Positions Development Team.

 https://forge.indepnet.net/projects/positions
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Positions.

 Positions is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Positions is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Positions. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"dropdownValue.php")) {
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkLoginUser();

// Security
if (!TableExists($_POST['table'])) {
   exit();
}

$item = new $_POST['itemtype']();

if (isset($_POST["entity_restrict"])
    && !is_numeric($_POST["entity_restrict"])
    && !is_array($_POST["entity_restrict"])) {

   $_POST["entity_restrict"] = Toolbox::decodeArrayFromInput($_POST["entity_restrict"]);
}

// Make a select box with preselected values
if (!isset($_POST["limit"])) {
   $_POST["limit"] = $CFG_GLPI["dropdown_chars_limit"];
}

$NBMAX = $CFG_GLPI["dropdown_max"];
$LIMIT = "LIMIT 0,$NBMAX";
if ($_POST['searchText']==$CFG_GLPI["ajax_wildcard"]) {
   $LIMIT = "";
}

$where =" WHERE `".$_POST['table']."`.`id` NOT IN ('".$_POST['value']."'";

$used = array();

if ($_POST["myname"] != "type") {
   $datas = getAllDatasFromTable("glpi_plugin_positions_positions", "`itemtype` = '".$_POST['itemtype']."'");
} else {
   $datas = getAllDatasFromTable("glpi_plugin_positions_imageitems", "`itemtype` = '".$_POST['itemtype']."'");
}
if (!empty($datas)) {
   foreach ($datas as $data) {
      if ($_POST["myname"] != "type") {
         $used[]=$data["items_id"];
      } else {
         $used[]=$data["type"];
      }
   }
}

if (count($used)) {
   $where .= ",'".implode("','",$used)."'";
}

$where .= ")";

$multi = false;

if (isset($_POST["myname"]) && $_POST["myname"] != "type") {
   if ($_POST['locations_id'] != -1) {
      $where .= " AND `locations_id` = '".$_POST['locations_id']."'";
   }

   if ($item->maybeDeleted()) {
      $where .= " AND `is_deleted` = '0' ";
   }
   if ($item->maybeTemplate()) {
      $where .= " AND `is_template` = '0' ";
   }

   if ($item->isEntityAssign()) {
      $multi = $item->maybeRecursive();

      if (isset($_POST["entity_restrict"]) && !($_POST["entity_restrict"]<0)) {
         $where .= getEntitiesRestrictRequest("AND", $_POST['table'], "entities_id",
                                              $_POST["entity_restrict"], $multi);

         if (is_array($_POST["entity_restrict"]) && count($_POST["entity_restrict"])>1) {
            $multi = true;
         }

      } else {
         $where .= getEntitiesRestrictRequest("AND", $_POST['table'], '', '', $multi);

         if (count($_SESSION['glpiactiveentities'])>1) {
            $multi = true;
         }
      }
   }
}
   
$field = "name";

if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"]) {
   $where .= " AND $field ".Search::makeTextSearch($_POST['searchText']);
}

$query = "SELECT *
          FROM `".$_POST['table']."`
          $where ";
if ($multi) {
   $query .= " ORDER BY `entities_id`, $field
              $LIMIT";
} else {
   $query .= " ORDER BY $field
              $LIMIT";
}

$result = $DB->query($query);

echo "<select id='dropdown_".$_POST["myname"].$_POST["rand"]."' name=\"".$_POST['myname']."\">";

if ($_POST['searchText'] != $CFG_GLPI["ajax_wildcard"] && $DB->numrows($result)==$NBMAX) {
   echo "<option value='0\'>--".__('Limited view')."--</option>";
} else {
   echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";
}
$number = $DB->numrows($result);
if ($number != 0 && $_POST["locations_id"]== -1) {
   echo "<option value=\"".$_POST['itemtype'].";-1\">".__('All types', 'positions').
            "</option>";
}
$output = Dropdown::getDropdownName($_POST['table'],$_POST['value']);
if (!empty($output)&&$output!="&nbsp;") {
   echo "<option selected value='".$_POST['value']."'>".$output."</option>";
}

if ($DB->numrows($result)) {
   while ($data =$DB->fetch_array($result)) {
      $output = $data[$field];
      $ID = $data['id'];
      $addcomment = "";
      if (isset($data["comment"])) {
         $addcomment = " - ".$data["comment"];
      }
      if (empty($output)) {
         $output = "($ID)";
      }

      echo "<option value=\"".$_POST['itemtype'].";$ID\" title=\"$output$addcomment\">".
            substr($output,0,$_POST["limit"])."</option>";
   }
}
echo "</select>";

?>