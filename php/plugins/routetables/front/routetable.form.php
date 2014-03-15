<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Routetables plugin for GLPI
  Copyright (C) 2003-2011 by the Routetables Development Team.

  https://forge.indepnet.net/projects/routetables
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Routetables.

  Routetables is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Routetables is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Routetables. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_GET["id"]))
   $_GET["id"] = "";
if (!isset($_GET["withtemplate"]))
   $_GET["withtemplate"] = "";

$route = new PluginRoutetablesRoutetable();
$route_item = new PluginRoutetablesRoutetable_Item();

if (isset($_POST["add"])) {

   $route->check(-1, 'w', $_POST);
   $newID = $route->add($_POST);
   Html::back();
} else if (isset($_POST["delete"])) {

   $route->check($_POST['id'], 'w');
   $route->delete($_POST);
   $route->redirectToList();
} else if (isset($_POST["restore"])) {

   $route->check($_POST['id'], 'w');
   $route->restore($_POST);
   $route->redirectToList();
} else if (isset($_POST["purge"])) {

   $route->check($_POST['id'], 'w');
   $route->delete($_POST, 1);
   $route->redirectToList();
} else if (isset($_POST["update"])) {

   $route->check($_POST['id'], 'w');
   $route->update($_POST);
   Html::back();
} else if (isset($_POST["additem"])) {

   if (!empty($_POST['itemtype']) && $_POST['items_id'] > 0) {
      $route_item->check(-1, 'w', $_POST);
      $route_item->AddItem($_POST);
   }
   Html::back();
} else if (isset($_POST["deleteitem"])) {

   foreach ($_POST["item"] as $key => $val) {
      $input = array('id' => $key);
      if ($val == 1) {
         $route_item->check($key, 'w');
         $route_item->delete($input);
      }
   }
   Html::back();
} else if (isset($_POST["deleteroutetables"])) {

   $input = array('id' => $_POST["id"]);
   $route_item->check($_POST["id"], 'w');
   $route_item->delete($input);
   Html::back();
} else {

   $route->checkGlobal("r");

   Html::header(PluginRoutetablesRoutetable::getTypeName(2), '', "plugins", "routetables");

   $route->showForm($_GET["id"]);

   Html::footer();
}
?>