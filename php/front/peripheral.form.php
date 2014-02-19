<?php
/*
 * @version $Id: peripheral.form.php 20129 2013-02-04 16:53:59Z moyo $
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

Session::checkRight("peripheral", "r");

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$peripheral = new Peripheral();

if (isset($_POST["add"])) {
   $peripheral->check(-1,'w',$_POST);

   $newID = $peripheral->add($_POST);
   Event::log($newID, "peripherals", 4, "inventory",
              sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
   Html::back();

} else if (isset($_POST["delete"])) {
   $peripheral->check($_POST["id"],'d');
   $peripheral->delete($_POST);

   Event::log($_POST["id"], "peripherals", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
   $peripheral->redirectToList();

} else if (isset($_POST["restore"])) {
   $peripheral->check($_POST["id"],'d');

   $peripheral->restore($_POST);
   Event::log($_POST["id"], "peripherals", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s restores an item'), $_SESSION["glpiname"]));
   $peripheral->redirectToList();

} else if (isset($_POST["purge"])) {
   $peripheral->check($_POST["id"],'d');

   $peripheral->delete($_POST,1);
   Event::log($_POST["id"], "peripherals", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   $peripheral->redirectToList();

} else if (isset($_POST["update"])) {
   $peripheral->check($_POST["id"],'w');

   $peripheral->update($_POST);
   Event::log($_POST["id"], "peripherals", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["unglobalize"])) {
   $peripheral->check($_POST["id"],'w');

   Computer_Item::unglobalizeItem($peripheral);
   Event::log($_POST["id"], "peripherals", 4, "inventory",
               //TRANS: %s is the user login
               sprintf(__('%s sets unitary management'), $_SESSION["glpiname"]));

   Html::redirect($CFG_GLPI["root_doc"]."/front/peripheral.form.php?id=".$_POST["id"]);

} else {
   Html::header(Peripheral::getTypeName(2), $_SERVER['PHP_SELF'], "inventory", "peripheral");
   $peripheral->showForm($_GET["id"], array('withtemplate' => $_GET["withtemplate"]));
   Html::footer();
}
?>