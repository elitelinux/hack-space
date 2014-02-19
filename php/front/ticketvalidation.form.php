<?php
/*
 * @version $Id: ticketvalidation.form.php 20129 2013-02-04 16:53:59Z moyo $
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
   $_GET["id"] = "";
}

$validation = new Ticketvalidation();
$ticket     = new Ticket();
$user       = new User();

if (isset($_POST["add"])) {
   $validation->check(-1,'w',$_POST);
   $validation->add($_POST);

   Event::log($validation->getField('tickets_id'), "ticket", 4, "tracking",
              //TRANS: %s is the user login
              sprintf(__('%s adds an approval'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["update"])) {
   $validation->check($_POST['id'],'w');
   $validation->update($_POST);

   Event::log($validation->getField('tickets_id'), "ticket", 4, "tracking",
              //TRANS: %s is the user login
              sprintf(__('%s updates an approval'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["delete"])) {
   $validation->check($_POST['id'], 'd');
   $validation->delete($_POST);

   Event::log($validation->getField('tickets_id'), "ticket", 4, "tracking",
              //TRANS: %s is the user login
              sprintf(__('%s deletes an approval'), $_SESSION["glpiname"]));
   Html::back();
}

Html::displayErrorAndDie('Lost');
?>