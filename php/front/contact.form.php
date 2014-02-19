<?php
/*
 * @version $Id: contact.form.php 20129 2013-02-04 16:53:59Z moyo $
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

Session::checkRight("contact_enterprise", "r");

if (empty($_GET["id"])) {
   $_GET["id"] = -1;
}

$contact = new Contact();

if (isset($_GET['getvcard'])) {
   if ($_GET["id"] < 0) {
      Html::redirect($CFG_GLPI["root_doc"]."/front/contact.php");
   }
   $contact->check($_GET["id"],'r');
   $contact->generateVcard();

} else if (isset($_POST["add"])) {
   $contact->check(-1,'w',$_POST);

   if ($newID = $contact->add($_POST)) {
      Event::log($newID, "contacts", 4, "financial",
                 sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $contact->check($_POST["id"],'d');

   if ($contact->delete($_POST)) {
      Event::log($_POST["id"], "contacts", 4, "financial",
                 //TRANS: %s is the user login
                 sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
   }
   $contact->redirectToList();

} else if (isset($_POST["restore"])) {
   $contact->check($_POST["id"],'d');

   if ($contact->restore($_POST)) {
      Event::log($_POST["id"], "contacts", 4, "financial",
                 //TRANS: %s is the user login
                 sprintf(__('%s restores an item'), $_SESSION["glpiname"]));
   }
   $contact->redirectToList();

} else if (isset($_POST["purge"])) {
   $contact->check($_POST["id"],'d');

   if ($contact->delete($_POST,1)) {
      Event::log($_POST["id"], "contacts", 4, "financial",
                 //TRANS: %s is the user login
                 sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   }
   $contact->redirectToList();

} else if (isset($_POST["update"])) {
   $contact->check($_POST["id"],'w');

   if ($contact->update($_POST)) {
      Event::log($_POST["id"], "contacts", 4, "financial",
                 //TRANS: %s is the user login
                 sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   }
   Html::back();

} else {
   Html::header(Contact::getTypeName(2), $_SERVER['PHP_SELF'], "financial", "contact");
   $contact->showForm($_GET["id"]);
   Html::footer();
}
?>