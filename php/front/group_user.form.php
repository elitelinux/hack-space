<?php
/*
 * @version $Id: group_user.form.php 20129 2013-02-04 16:53:59Z moyo $
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
* @since vesion 0.84
*/

include ('../inc/includes.php');

Session::checkCentralAccess();

$group_user = new Group_User();

if (isset($_POST["add"])) {
   $group_user->check(-1,'w',$_POST);
   if ($group_user->add($_POST)) {
      Event::log($_POST["groups_id"], "groups", 4, "setup",
                  //TRANS: %s is the user login
                  sprintf(__('%s adds a user to a group'), $_SESSION["glpiname"]));
   }
   Html::back();

}

Html::displayErrorAndDie("lost");
?>