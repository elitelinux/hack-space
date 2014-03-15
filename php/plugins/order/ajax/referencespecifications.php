<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkCentralAccess();

$PluginOrderReference = new PluginOrderReference();

if ($_POST["itemtype"]) {
   switch ($_POST["field"]) {
      case "types_id":
         if ($_POST["itemtype"] == 'PluginOrderOther') {
            $file = 'other';
         } else {
            $file = $_POST["itemtype"];
         }
         if (file_exists(GLPI_ROOT."/inc/".strtolower($_POST["itemtype"])."type.class.php")
               || file_exists(GLPI_ROOT."/plugins/order/inc/".strtolower($file)."type.class.php")) {
            Dropdown::show($_POST["itemtype"]."Type", array('name' => "types_id"));
         }
         break;
      case "models_id":
         if (file_exists(GLPI_ROOT."/inc/".strtolower($_POST["itemtype"])."model.class.php")) {
            Dropdown::show($_POST["itemtype"]."Model", array('name' => "models_id"));
         } else {
            return "";
         }
         break;
      case "templates_id":
         $item = new $_POST['itemtype']();
         if ($item->maybeTemplate()) {
            $table = getTableForItemType($_POST["itemtype"]);
            $PluginOrderReference->dropdownTemplate("templates_id", $_POST["entity_restrict"],
                                                    $table);
         } else {
            return "";
         }
         break;
   }
} else {
   return '';
}
?>