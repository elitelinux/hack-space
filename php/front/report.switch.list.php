<?php
/*
 * @version $Id: report.switch.list.php 20419 2013-03-15 08:13:17Z webmyster $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */


/** @file
* @brief show network port by network equipment
*/


include ('../inc/includes.php');

Session::checkRight("reports", "r");

// Titre
if (isset($_POST["switch"]) && $_POST["switch"]) {
   Html::header(Report::getTypeName(2), $_SERVER['PHP_SELF'], "utils", "report");

   Report::title();

   $name = Dropdown::getDropdownName("glpi_networkequipments",$_POST["switch"]);
   echo "<div class='center spaced'><h2>".sprintf(__('Network report by hardware: %s'),$name).
        "</h2></div>";

   Report::reportForNetworkInformations("`glpi_networkequipments` AS ITEM",
                                        "PORT_1.`itemtype` = 'NetworkEquipment'
                                         AND PORT_1.`items_id` = ITEM.`id`",
                                        "ITEM.`id` = '".$_POST["switch"]."'");

   Html::footer();

} else  {
   Html::redirect($CFG_GLPI['root_doc']."/front/report.networking.php");
}
?>