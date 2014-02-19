<?php
/*
 * @version $Id: subvisibility.php 20129 2013-02-04 16:53:59Z moyo $
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
if (strpos($_SERVER['PHP_SELF'],"subvisibility.php")) {
   $AJAX_INCLUDE = 1;
   include ('../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkLoginUser();

if (isset($_POST['type']) && !empty($_POST['type'])
    && isset($_POST['items_id']) && ($_POST['items_id'] > 0)) {

   switch ($_POST['type']) {
      case 'Group' :
      case 'Profile' :
         $params = array('value' => $_SESSION['glpiactive_entity']);
         if (Session::isViewAllEntities()) {
            $params['toadd'] = array(-1 => __('No restriction'));
         }
         _e('Entity');
         echo "&nbsp;";
         Entity::dropdown($params);
         echo "&nbsp;";
         _e('Child entities');
         echo "&nbsp;";
         Dropdown::showYesNo('is_recursive');
         break;
   }
}
?>