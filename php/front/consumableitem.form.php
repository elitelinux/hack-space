<?php
/*
 * @version $Id: consumableitem.form.php 20129 2013-02-04 16:53:59Z moyo $
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

Session::checkRight("consumable", "r");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$constype = new ConsumableItem();

if (isset($_POST["add"])) {
   $constype->check(-1,'w',$_POST);

   if ($newID = $constype->add($_POST)) {
      Event::log($newID, "consumables", 4, "inventory",
                 sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $constype->check($_POST["id"],'d');

   if ($constype->delete($_POST)) {
      Event::log($_POST["id"], "consumables", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
   }
   $constype->redirectToList();

} else if (isset($_POST["restore"])) {
   $constype->check($_POST["id"],'d');

   if ($constype->restore($_POST)) {
      Event::log($_POST["id"], "consumables", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s restores an item'), $_SESSION["glpiname"]));
   }
   $constype->redirectToList();

} else if (isset($_POST["purge"])) {
   $constype->check($_POST["id"],'d');

   if ($constype->delete($_POST,1)) {
      Event::log($_POST["id"], "consumables", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   }
   $constype->redirectToList();

} else if (isset($_POST["update"])) {
   $constype->check($_POST["id"],'w');

   if ($constype->update($_POST)) {
      Event::log($_POST["id"], "consumables", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   }
   Html::back();

} else {
   Html::header(_n('Consumable', 'Consumables', 2), $_SERVER['PHP_SELF'], "inventory", "consumable");
   $constype->showForm($_GET["id"]);
   Html::footer();
}
?>