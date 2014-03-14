<?php
/*
 * @version $Id: dropdownTypeAppliances.php 202 2013-03-06 16:16:52Z yllen $
 -------------------------------------------------------------------------
 appliances - Appliances plugin for GLPI
 Copyright (C) 2003-2013 by the appliances Development Team.

 https://forge.indepnet.net/projects/appliances
 -------------------------------------------------------------------------

 LICENSE

 This file is part of appliances.

 appliances is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 appliances is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with appliances. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (strpos($_SERVER['PHP_SELF'],"dropdownTypeAppliances.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkCentralAccess();

// Make a select box

if (isset($_POST["type_appliances"])) {
   $rand = $_POST['rand'];

   $use_ajax = false;
   if ($CFG_GLPI["use_ajax"]
       && (countElementsInTable('glpi_plugin_appliances_appliances',
                                "plugin_appliances_appliancetypes_id ='".$_POST["type_appliances"].
                                 "' ".getEntitiesRestrictRequest(" AND",
                                                                 "glpi_plugin_appliances_appliances",
                                                                 "", $_POST["entity_restrict"], true)
                               ) > $CFG_GLPI["ajax_limit_count"])) {
      $use_ajax = true;
   }

   $params = array('searchText'      => ' __VALUE__',
                   'type_appliances' => $_POST["type_appliances"],
                   'entity_restrict' => $_POST["entity_restrict"],
                   'rand'            => $_POST['rand'],
                   'myname'          => $_POST['myname'],
                   'used'            => $_POST['used']);

   $default = "<select name='".$_POST["myname"]."'>
               <option value='0'>".Dropdown::EMPTY_VALUE."</option></select>";
   Ajax::dropdown($use_ajax, "/plugins/appliances/ajax/dropdownappliances.php", $params, $default,
                $rand);

}
?>