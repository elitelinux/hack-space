<?php
/*
 * @version $Id: monitor.form.php 20129 2013-02-04 16:53:59Z moyo $
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

Session::checkRight("monitor", "r");

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$monitor = new Monitor();

if (isset($_POST["add"])) {
   $monitor->check(-1,'w',$_POST);

   $newID = $monitor->add($_POST);
   Event::log($newID, "monitors", 4, "inventory",
              sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
   Html::back();

} else if (isset($_POST["delete"])) {
   $monitor->check($_POST["id"],'d');
   $monitor->delete($_POST);

   Event::log($_POST["id"], "monitors", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
   $monitor->redirectToList();

} else if (isset($_POST["restore"])) {
   $monitor->check($_POST["id"],'d');

   $monitor->restore($_POST);
   Event::log($_POST["id"], "monitors", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s restores an item'), $_SESSION["glpiname"]));
   $monitor->redirectToList();

} else if (isset($_POST["purge"])) {
   $monitor->check($_POST["id"],'d');

   $monitor->delete($_POST,1);
   Event::log($_POST["id"], "monitors", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   $monitor->redirectToList();

} else if (isset($_POST["update"])) {
   $monitor->check($_POST["id"],'w');

   $monitor->update($_POST);
   Event::log($_POST["id"], "monitors", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["unglobalize"])) {
   $monitor->check($_POST["id"],'w');

   Computer_Item::unglobalizeItem($monitor);
   Event::log($_POST["id"], "monitors", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s sets unitary management'), $_SESSION["glpiname"]));

   Html::redirect($CFG_GLPI["root_doc"]."/front/monitor.form.php?id=".$_POST["id"]);

} else {
   Html::header(Monitor::getTypeName(2), $_SERVER['PHP_SELF'], "inventory", "monitor");
   $monitor->showForm($_GET["id"], array('withtemplate' => $_GET["withtemplate"]));
   Html::footer();
}
?>