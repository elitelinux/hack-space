<?php
/*
 * @version $Id: dropdownResources.php 480 2012-11-09 tsmr $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"dropdownResources.php")) {
   $AJAX_INCLUDE = 1;
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();
// Default view : Nobody
if (!isset($_POST['all'])) {
   $_POST['all'] = 0;
}

$used = array();

if (isset($_POST['used'])) {
   if (is_array($_POST['used'])) {
      $used = $_POST['used'];
   } else {
      $used = Toolbox::decodeArrayFromInput($_POST['used']);
   }
}

if (isset($_POST["entity_restrict"])
    && !is_numeric($_POST["entity_restrict"])
    && !is_array($_POST["entity_restrict"])) {

   $_POST["entity_restrict"] = Toolbox::decodeArrayFromInput($_POST["entity_restrict"]);
}

$plugin_resources_contracttypes_id=0;
if (isset($_POST["plugin_resources_contracttypes_id"])&&
   $_POST["plugin_resources_contracttypes_id"]>0) {

   $plugin_resources_contracttypes_id = $_POST["plugin_resources_contracttypes_id"];
}

$result = PluginResourcesResource::getSqlSearchResult(false, $_POST["entity_restrict"],
                                   $_POST['value'], $used, $_POST['searchText']);

$users = array();

if ($DB->numrows($result)) {
   while ($data=$DB->fetch_array($result)) {
      $users[$data["id"]] = formatUserName($data["id"], $data["username"], $data["name"],
                                           $data["firstname"]);
      $logins[$data["id"]] = $data["name"];
   }
}

if (!function_exists('dpuser_cmp')) {
   function dpuser_cmp($a, $b) {
      return strcasecmp($a, $b);
   }
}

// Sort non case sensitive
uasort($users, 'dpuser_cmp');

echo "<select id='dropdown_".$_POST["myname"].$_POST["rand"]."' name='".$_POST['myname']."'";

if (isset($_POST["on_change"]) && !empty($_POST["on_change"])) {
   echo " onChange='".$_POST["on_change"]."'";
}

echo ">";

if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"]
    && $DB->numrows($result)==$CFG_GLPI["dropdown_max"]) {

   echo "<option value='0'>--".__('Limited view')."--</option>";
}

if ($_POST['all']==0) {
   echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";
} else if ($_POST['all']==1) {
   echo "<option value='0'>[".__('All')."]</option>";
}

if (isset($_POST['value'])) {
   $output = PluginResourcesResource::getResourceName($_POST['value']);

   if (!empty($output) && $output!="&nbsp;") {
      echo "<option selected value='".$_POST['value']."'>".$output."</option>";
   }
}

if (count($users)) {
   foreach ($users as $ID => $output) {
      echo "<option value='$ID' title=\"".Html::cleanInputText($output." - ".$logins[$ID])."\">".
             Toolbox::substr($output, 0, $_SESSION["glpidropdown_chars_limit"])."</option>";
   }
}
echo "</select>";

if (isset($_POST["comment"]) && $_POST["comment"]) {
   $paramscomment = array('value' => '__VALUE__',
                          'table' => "glpi_plugin_resources_resources");

   if (isset($_POST['update_link'])) {
      $paramscomment['withlink'] = "comment_link_".$_POST["myname"].$_POST["rand"];
   }
   Ajax::updateItemOnSelectEvent("dropdown_".$_POST["myname"].$_POST["rand"],
                                 "comment_".$_POST["myname"].$_POST["rand"],
                                 $CFG_GLPI["root_doc"]."/plugins/resources/ajax/comments.php", $paramscomment);
}

Ajax::commonDropdownUpdateItem($_POST);
?>