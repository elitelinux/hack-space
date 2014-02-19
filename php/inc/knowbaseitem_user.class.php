<?php
/*
 * @version $Id: knowbaseitem_user.class.php 20129 2013-02-04 16:53:59Z moyo $
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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// Class KnowbaseItem_User
/// since version 0.83
class KnowbaseItem_User extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1          = 'KnowbaseItem';
   static public $items_id_1          = 'knowbaseitems_id';
   static public $itemtype_2          = 'User';
   static public $items_id_2          = 'users_id';

   static public $checkItem_2_Rights  = self::DONT_CHECK_ITEM_RIGHTS;
   static public $logs_for_item_2     = false;


   /**
    * Get users for a knowbaseitem
    *
    * @param $knowbaseitems_id ID of the knowbaseitem
    *
    * @return array of users linked to a knowbaseitem
   **/
   static function getUsers($knowbaseitems_id) {
      global $DB;

      $users = array();
      $query = "SELECT `glpi_knowbaseitems_users`.*
                FROM `glpi_knowbaseitems_users`
                WHERE `knowbaseitems_id` = '$knowbaseitems_id'";

      foreach ($DB->request($query) as $data) {
         $users[$data['users_id']][] = $data;
      }
      return $users;
   }

}
?>