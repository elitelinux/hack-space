<?php
/*
 * @version $Id: authmail.form.php 20129 2013-02-04 16:53:59Z moyo $
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

Session::checkRight("config", "w");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$config_mail = new AuthMail();

//IMAP/POP Server add/update/delete
if (isset($_POST["update"])) {
   $config_mail->update($_POST);
   Html::back();

} else if (isset($_POST["add"])) {
   //If no name has been given to this configuration, then go back to the page without adding
   if ($_POST["name"] != "") {
      $newID = $config_mail->add($_POST);
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $config_mail->delete($_POST);
   $_SESSION['glpi_authconfig'] = 2;
   $config_mail->redirectToList();

} else if (isset($_POST["test"])) {
   if (AuthMail::testAuth($_POST["imap_string"], $_POST["imap_login"], $_POST["imap_password"])) {
      Session::addMessageAfterRedirect(__('Test successful'));
   } else {
      Session::addMessageAfterRedirect(__('Test failed'), false, ERROR);
   }
   Html::back();
}

Html::header(AuthMail::getTypeName(1), $_SERVER['PHP_SELF'], "config", "extauth", "imap");

$config_mail->showForm($_GET["id"]);

Html::footer();
?>