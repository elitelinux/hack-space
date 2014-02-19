<?php
/*
 * @version $Id: report.location.list.php 20419 2013-03-15 08:13:17Z webmyster $
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
* @brief
*/

include ('../inc/includes.php');

Session::checkRight("reports", "r");

if (isset($_POST["locations_id"]) && $_POST["locations_id"]) {
   Html::header(Report::getTypeName(2), $_SERVER['PHP_SELF'], "utils", "report");

   Report::title();

   // Titre
   $name = Dropdown::getDropdownName("glpi_locations",$_POST["locations_id"]);
   echo "<div class='center spaced'><h2>".sprintf(__('Network report by location: %s'),$name).
        "</h2></div>";

   Report::reportForNetworkInformations(
       "`glpi_locations`
       INNER JOIN `glpi_netpoints` ON (`glpi_netpoints`.`locations_id` = `glpi_locations`.`id`)
       INNER JOIN `glpi_networkportethernets` ON (`glpi_networkportethernets`.`netpoints_id` = `glpi_netpoints`.`id`)",
       "PORT_1.`id` = `glpi_networkportethernets`.`networkports_id`",
       getRealQueryForTreeItem("glpi_locations",$_POST["locations_id"]),
       "`glpi_locations`.`completename`, PORT_1.`name`",
       "`glpi_netpoints`.`name` AS extra,",
       Netpoint::getTypeName());

   Html::footer();

} else  {
   Html::redirect($CFG_GLPI['root_doc']."/front/report.networking.php");
}
?>