<?php
/*
 * @version $Id: ipnetwork.form.php 20133 2013-02-04 18:59:06Z yllen $
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

if (isset($_POST['reinit_network'])) {

   if (Session::haveRight('internet', 'w')
       // Check access to all entities
       && Session::isViewAllEntities()) {
      IPNetwork::recreateTree();
      Session::addMessageAfterRedirect(__('Successfully recreated network tree'));
      Html::back();
   } else {
      Html::displayRightError();
   }

}

$dropdown = new IPNetwork();
include (GLPI_ROOT . "/front/dropdown.common.form.php");
?>