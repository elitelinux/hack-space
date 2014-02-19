<?php
/*
 * @version $Id: cartridge.form.php 20129 2013-02-04 16:53:59Z moyo $
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

Session::checkRight("cartridge", "r");

$cart    = new Cartridge();
$cartype = new CartridgeItem();

if (isset($_POST["add"])) {
   $cartype->check($_POST["cartridgeitems_id"],'w');

   for ($i=0 ; $i<$_POST["to_add"] ; $i++) {
      unset($cart->fields["id"]);
      $cart->add($_POST);
   }
   Event::log($_POST["cartridgeitems_id"], "cartridges", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s adds cartridges'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["delete"])) {
   $cartype->check($_POST["cartridgeitems_id"],'w');

   if ($cart->delete($_POST)) {
      Event::log($_POST["cartridgeitems_id"], "cartridges", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s deletes a cartridge'), $_SESSION["glpiname"]));
   }
   Html::back();

} else if (isset($_POST["install"])) {
   if ($_POST["cartridgeitems_id"]) {
      $cartype->check($_POST["cartridgeitems_id"],'w');
      if ($cart->install($_POST["printers_id"],$_POST["cartridgeitems_id"])) {
         Event::log($_POST["printers_id"], "printers", 5, "inventory",
                    //TRANS: %s is the user login
                    sprintf(__('%s installs a cartridge'), $_SESSION["glpiname"]));
      }
   }
   Html::redirect($CFG_GLPI["root_doc"]."/front/printer.form.php?id=".$_POST["printers_id"]);

} else if (isset($_POST["update"])) {
   $cart->check($_POST["id"],'w');

   if ($cart->update($_POST)) {
      Event::log($_POST["printers_id"], "printers", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s updates a cartridge'), $_SESSION["glpiname"]));
   }
   Html::back();

} else {
   Html::back();
}
?>
