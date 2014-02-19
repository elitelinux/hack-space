<?php
/*
 * @version $Id: notificationtemplatetranslation.form.php 20129 2013-02-04 16:53:59Z moyo $
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

Session::checkCentralAccess();

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$language = new NotificationTemplateTranslation();

if (isset($_POST["add"])) {
   $language->check(-1,'w',$_POST);
   $newID = $language->add($_POST);
   Event::log($newID, "notificationtemplatetranslations", 4, "notification",
              sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["language"]));
   Html::back();

} else if (isset($_POST["delete"])) {
   $language->check($_POST["id"],'d');
   $language->delete($_POST);

   Event::log($_POST["id"], "notificationtemplatetranslations", 4, "notification",
              //TRANS: %s is the user login
              sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   $language->redirectToList();

} else if (isset($_POST["update"])) {
   $language->check($_POST["id"],'w');
   $language->update($_POST);

   Event::log($_POST["id"], "notificationtemplatetranslations", 4, "notification",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

} else {
   Html::header(NotificationTemplate::getTypeName(2), $_SERVER['PHP_SELF'], "config", "mailing",
                "notificationtemplate");

   if ($_GET["id"] == '') {
      $options = array("notificationtemplates_id" => $_GET["notificationtemplates_id"]);
   } else {
      $options = array();
   }
   $language->showForm($_GET["id"], $options);
   Html::footer();
}
?>