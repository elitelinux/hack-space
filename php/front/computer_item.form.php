<?php
/*
 * @version $Id: computer_item.form.php 20129 2013-02-04 16:53:59Z moyo $
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
* @since version 0.84
*/

include ('../inc/includes.php');

Session::checkCentralAccess();

$conn = new Computer_Item();

if (isset($_POST["disconnect"])) {
   $conn->check($_POST["id"], 'd');
   $conn->delete($_POST);
   Event::log($_POST["computers_id"], "computers", 5, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s disconnects an item'), $_SESSION["glpiname"]));
   Html::back();

// Connect a computer to a printer/monitor/phone/peripheral
} else if (isset($_POST["add"])) {
   if (isset($_POST["items_id"]) && ($_POST["items_id"] > 0)) {
      $conn->check(-1, 'w', $_POST);
      $conn->add($_POST);
      Event::log($_POST["computers_id"], "computers", 5, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s connects an item'), $_SESSION["glpiname"]));
   }
   Html::back();

}

Html::displayErrorAndDie('Lost');
?>