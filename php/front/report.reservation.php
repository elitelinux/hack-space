<?php
/*
 * @version $Id: report.reservation.php 20129 2013-02-04 16:53:59Z moyo $
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

Html::header(Report::getTypeName(2), $_SERVER['PHP_SELF'], "utils", "report");

if (!isset($_GET["id"])) {
   $_GET["id"] = 0;
}

Report::title();

echo "<form method='get' name='form' action='report.reservation.php'>";
echo "<table class='tab_cadre'><tr class='tab_bg_2'>";
echo "<td rowspan='2' class='center'>";
User::dropdown(array('name'   => 'id',
                     'value'  => $_GET["id"],
                     'right'  => 'reservation_helpdesk'));

echo "</td>";
echo "<td rowspan='2' class='center'><input type='submit' class='submit' name='submit' value='".
      __s('Display report')."'></td></tr>";
echo "</table>";
Html::closeForm();

if ($_GET["id"] > 0) {
   Reservation::showForUser($_GET["id"]);
}
Html::footer();
?>