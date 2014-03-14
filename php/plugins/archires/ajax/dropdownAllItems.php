<?php
/*
 * @version $Id: dropdownAllItems.php 180 2013-03-12 09:17:42Z yllen $
 -------------------------------------------------------------------------
 Archires plugin for GLPI
 Copyright (C) 2003-2013 by the archires Development Team.

 https://forge.indepnet.net/projects/archires
 -------------------------------------------------------------------------

 LICENSE

 This file is part of archires.

 Archires is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Archires is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Archires. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

// Make a select box

if (isset($_POST["typetable"])) {
   $test     = explode(";", $_POST['typetable']);
   $itemtype = $test[0];
   $table    = $test[1];
   // Link to user for search only > normal users
   $rand     = mt_rand();

   $use_ajax = false;
   if ($CFG_GLPI["use_ajax"] && (countElementsInTable($table) > $CFG_GLPI["ajax_limit_count"])) {
      $use_ajax=true;
   }

   $params = array('searchText' => '__VALUE__',
                   'itemtype'   => $itemtype,
                   'table'      => $table,
                   'rand'       => $rand,
                   'myname'     => $_POST["myname"]);

   if (isset($_POST['value'])) {
      $params['value'] = $_POST['value'];
   }
   if (isset($_POST['entity_restrict'])) {
      $params['entity_restrict'] = $_POST['entity_restrict'];
   }

   $default = "<select name='".$_POST["myname"]."'><option value='0'>".Dropdown::EMPTY_VALUE.
              "</option></select>";
   Ajax::dropdown($use_ajax,"/plugins/archires/ajax/dropdownValue.php", $params, $default, $rand);

   if (isset($_POST['value']) && ($_POST['value'] > 0)) {
      $params['searchText'] = $CFG_GLPI["ajax_wildcard"];
      echo "<script type='text/javascript' >\n";
      echo "document.getElementById('search_$rand').value='".$CFG_GLPI["ajax_wildcard"]."';";
      echo "</script>\n";
      Ajax::pdateItem("results_$rand",
                      $CFG_GLPI["root_doc"]."/plugins/archires/ajax/dropdownValue.php",
                      $params);
   }
}
?>