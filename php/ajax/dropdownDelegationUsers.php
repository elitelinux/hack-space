<?php
/*
 * @version $Id: dropdownDelegationUsers.php 21179 2013-06-25 07:08:30Z moyo $
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

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"dropdownDelegationUsers.php")) {
   $AJAX_INCLUDE = 1;
   include ('../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

if ($_POST["nodelegate"] == 1) {
   $_POST['_users_id_requester'] = Session::getLoginUserID();
   $_POST['_right']              = "id";
} else {
   $_POST['_right'] = "delegate";
}

if (isset($_POST['_users_id_requester_notif']) && !empty($_POST['_users_id_requester_notif'])) {
   $_POST['_users_id_requester_notif']
            = Toolbox::decodeArrayFromInput($_POST['_users_id_requester_notif']);
}

$ticket = new Ticket();
$ticket->showActorAddFormOnCreate(Ticket_User::REQUESTER, $_POST);
?>