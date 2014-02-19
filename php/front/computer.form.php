<?php
/*
 * @version $Id: computer.form.php 20129 2013-02-04 16:53:59Z moyo $
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

Session::checkRight("computer", "r");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$computer = new Computer();
//Add a new computer
if (isset($_POST["add"])) {
   $computer->check(-1, 'w', $_POST);
   if ($newID = $computer->add($_POST)) {
      Event::log($newID, "computers", 4, "inventory",
                 sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
   }
   Html::back();

// delete a computer
} else if (isset($_POST["delete"])) {
   $computer->check($_POST['id'], 'd');
   $ok = $computer->delete($_POST);
   if ($ok) {
      Event::log($_POST["id"], "computers", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
   }
   $computer->redirectToList();

} else if (isset($_POST["restore"])) {
   $computer->check($_POST['id'], 'd');
   if ($computer->restore($_POST)) {
      Event::log($_POST["id"],"computers", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s restores an item'), $_SESSION["glpiname"]));
   }
   $computer->redirectToList();

} else if (isset($_POST["purge"])) {
   $computer->check($_POST['id'], 'd');
   if ($computer->delete($_POST,1)) {
      Event::log($_POST["id"], "computers", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   }
   $computer->redirectToList();

//update a computer
} else if (isset($_POST["update"])) {
   $computer->check($_POST['id'], 'w');
   $computer->update($_POST);
   Event::log($_POST["id"], "computers", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

// Disconnect a computer from a printer/monitor/phone/peripheral
} else {//print computer information
   Html::header(Computer::GetTypeName(2), $_SERVER['PHP_SELF'], "inventory", "computer");
   //show computer form to add
   $computer->showForm($_GET["id"], array('withtemplate' => $_GET["withtemplate"]));
   Html::footer();
}
?>