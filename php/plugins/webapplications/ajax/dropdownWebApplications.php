<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Webapplications plugin for GLPI
 Copyright (C) 2003-2011 by the Webapplications Development Team.

 https://forge.indepnet.net/projects/webapplications
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Webapplications.

 Webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"dropdownWebApplications.php")) {
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not access directly to this file");
}

Session::checkCentralAccess();
// Make a select box with all webapplications by type

$where = " WHERE (`glpi_plugin_webapplications_webapplications`.`plugin_webapplications_webapplicationtypes_id`
                           = '".$_POST['plugin_webapplications_webapplicationtypes_id']."')
                  AND `glpi_plugin_webapplications_webapplications`.`is_deleted` = '0'";

if (isset($_POST["entity_restrict"]) && $_POST["entity_restrict"]>=0) {
   $where .= getEntitiesRestrictRequest("AND", "glpi_plugin_webapplications_webapplications", '',
                                        $_POST["entity_restrict"], true);
} else {
   $where .= getEntitiesRestrictRequest("AND", "glpi_plugin_webapplications_webapplications", '',
                                        '', true);
}

if (isset($_POST['used'])) {
   $where .= " AND `id` NOT IN (0";
   if (is_array($_POST['used'])) {
      $used = $_POST['used'];
   } else {
      $used = Toolbox::decodeArrayFromInput($_POST['used']);
   }
   foreach($used as $val) {
      $where .= ",$val";
   }
   $where .= ") ";
}

if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"]) {
   $where .= " AND `glpi_plugin_webapplications_webapplications`.`name` ".
                    Search::makeTextSearch($_POST['searchText']);
}

$NBMAX   = $CFG_GLPI["dropdown_max"];
$LIMIT   = "LIMIT 0,$NBMAX";
if ($_POST['searchText']==$CFG_GLPI["ajax_wildcard"]) {
   $LIMIT = "";
}

$query = "SELECT *
          FROM `glpi_plugin_webapplications_webapplications`
          $where
          ORDER BY `entities_id`, `name`
          $LIMIT";

$result = $DB->query($query);

echo "<select name=\"".$_POST['myname']."\">";

echo "<option value=\"0\">".Dropdown::EMPTY_VALUE."</option>";

if ($DB->numrows($result)) {
   $prev = -1;
   while ($data=$DB->fetch_array($result)) {
      if ($data["entities_id"] != $prev) {
         if ($prev >= 0) {
            echo "</optgroup>";
         }
         $prev = $data["entities_id"];
         echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
      }
      $output = $data["name"];
      echo "<option value=\"".$data["id"]."\" title=\"$output\">".
             substr($output, 0, $CFG_GLPI["dropdown_chars_limit"])."</option>";
   }
   if ($prev>=0) {
      echo "</optgroup>";
   }
}
echo "</select>";
?>