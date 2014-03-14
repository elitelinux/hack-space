<?php
/*
 * @version $Id: dropdownValue.php 180 2013-03-12 09:17:42Z yllen $
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

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"dropdownValue.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

// Security
if (!TableExists($_POST['table'])) {
   exit();
}

$item = new $_POST['itemtype']();

// Make a select box with preselected values
if (!isset($_POST["limit"])) {
   $_POST["limit"] = $CFG_GLPI["dropdown_chars_limit"];
}

$NBMAX = $CFG_GLPI["dropdown_max"];
$LIMIT = "LIMIT 0,$NBMAX";
if ($_POST['searchText']==$CFG_GLPI["ajax_wildcard"]) {
   $LIMIT = "";
}

$where = "WHERE `id` <> '".$_POST['value']."' ";
$field = "name";

if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"]) {
   $where .= " AND `$field` ".Search::makeTextSearch($_POST['searchText']);
}

$query = "SELECT *
          FROM `".$_POST['table']."`
          $where
          ORDER BY `$field`
          $LIMIT";

$result = $DB->query($query);

echo "<select id='dropdown_".$_POST["myname"].$_POST["rand"]."' name=\"".$_POST['myname']."\">";

if (($_POST['searchText'] != $CFG_GLPI["ajax_wildcard"])
    && ($DB->numrows($result) == $NBMAX)) {
   echo "<option value='0\'>--".__('Limited view')."--</option>";
} else {
   echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";
}
$number = $DB->numrows($result);
if ($number != 0) {
   echo "<option value=\"".$_POST['itemtype'].";-1\">".__('All types', 'archires')."</option>";
}
$output = Dropdown::getDropdownName($_POST['table'],$_POST['value']);
if (!empty($output) && ($output != "&nbsp;")) {
   echo "<option selected value='".$_POST['value']."'>".$output."</option>";
}

if ($DB->numrows($result)) {
   while ($data =$DB->fetch_array($result)) {
      $output     = $data[$field];
      $ID         = $data['id'];
      $addcomment = "";
      if (isset($data["comment"])) {
         $addcomment = " - ".$data["comment"];
      }
      if ($_SESSION['glpiis_ids_visible'] || empty($output)) {
         $output = sprintf(__('%1$s (%2$s)'), $output, $ID);
      }

      echo "<option value=\"".$_POST['itemtype'].";$ID\" title=\"$output$addcomment\">".
            substr($output,0,$_POST["limit"])."</option>";
   }
}
echo "</select>";
?>