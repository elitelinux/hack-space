<?php
/*
 * @version $Id: transfer.form.php 20129 2013-02-04 16:53:59Z moyo $
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

Session::checkRight("transfer", "r");

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$transfer = new Transfer();

if (isset($_POST["add"])) {
   $transfer->check(-1,'w',$_POST);

   $newID = $transfer->add($_POST);
   Event::log($newID, "transfers", 4, "setup",
              sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
   Html::back();

} else if (isset($_POST["delete"])) {
   $transfer->check($_POST["id"],'d');

   $transfer->delete($_POST);
   Event::log($_POST["id"], "transfers", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   Html::redirect($CFG_GLPI["root_doc"]."/front/transfer.php");

} else if (isset($_POST["update"])) {
   $transfer->check($_POST["id"],'w');

   $transfer->update($_POST);
   Event::log($_POST["id"], "transfers", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();
}

Html::header(__('Transfer'), '', 'admin', 'rule', 'transfer');

$transfer->showForm($_GET["id"], array('target' => $transfer->getFormURL()));

Html::footer();
?>