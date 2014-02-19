<?php
/*
 * @version $Id: document.form.php 20475 2013-03-19 16:29:40Z moyo $
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

Session::checkLoginUser();

if (!isset($_GET["id"])) {
   $_GET["id"] = -1;
}

$doc          = new Document();
$documentitem = new Document_Item();

if (isset($_POST["add"])) {
   $doc->check(-1,'w',$_POST);

   if ($newID = $doc->add($_POST)) {
      Event::log($newID, "documents", 4, "login",
                 sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $doc->fields["name"]));
   }

   Html::back();

} else if (isset($_POST["delete"])) {
   $doc->check($_POST["id"],'d');

   if ($doc->delete($_POST)) {
      Event::log($_POST["id"], "documents", 4, "document",
                 //TRANS: %s is the user login
                 sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
   }
   $doc->redirectToList();

} else if (isset($_POST["restore"])) {
   $doc->check($_POST["id"],'d');

   if ($doc->restore($_POST)) {
      Event::log($_POST["id"], "documents", 4, "document",
                 //TRANS: %s is the user login
                 sprintf(__('%s restores an item'), $_SESSION["glpiname"]));
   }
   $doc->redirectToList();

} else if (isset($_POST["purge"])) {
   $doc->check($_POST["id"],'d');

   if ($doc->delete($_POST,1)) {
      Event::log($_POST["id"], "documents", 4, "document",
                 //TRANS: %s is the user login
                 sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   }
   $doc->redirectToList();

} else if (isset($_POST["update"])) {
   $doc->check($_POST["id"],'w');

   if ($doc->update($_POST)) {
      Event::log($_POST["id"], "documents", 4, "document",
                 //TRANS: %s is the user login
                 sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   }
   Html::back();

} else {
   Html::header(Document::getTypeName(2), $_SERVER['PHP_SELF'], "financial","document");
   $doc->showForm($_GET["id"]);
   Html::footer();
}
?>