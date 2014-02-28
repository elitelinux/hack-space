<?php
/*
 * @version $Id: commonitilactor.class.php 22657 2014-02-12 16:17:54Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

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

/// Class Ticket_User
abstract class CommonITILActor extends CommonDBRelation {

   // items_id_1, items_id_2, itemtype_1 and itemtype_2 are defined inside the inherited classes
   static public $checkItem_2_Rights  = self::DONT_CHECK_ITEM_RIGHTS;
   static public $logs_for_item_2     = false;



   // Requester
   const REQUESTER = 1;
   // Assign
   const ASSIGN    = 2;
   // Observer
   const OBSERVER  = 3;


   function getActorForeignKey() {
      return static::$items_id_2;
   }


   static function getItilObjectForeignKey() {
      return static::$items_id_1;
   }


   /**
    * @since version 0.84
    *
    * @param $input  array of data to be added
    *
    * @see CommonDBRelation::isAttach2Valid()
   **/
   function isAttach2Valid(Array &$input) {

      // Anonymous user (only email) as requester or observer
      if (isset($input['users_id']) && ($input['users_id'] == 0)
          && isset($input['alternative_email']) && !empty($input['alternative_email'])
          && isset($input['type']) && ($input['type'] != CommonITILActor::ASSIGN)) {
         return true;
      }
      return false;
   }


   /**
    * @param $items_id
   **/
   function getActors($items_id) {
      global $DB;

      $users = array();
      $query = "SELECT `".$this->getTable()."`.*
                FROM `".$this->getTable()."`
                WHERE `".static::getItilObjectForeignKey()."` = '$items_id'";

      foreach ($DB->request($query) as $data) {
         $users[$data['type']][] = $data;
      }
      return $users;
   }


   /**
    * @param $items_id
    * @param $email
   **/
   function isAlternateEmailForITILObject($items_id, $email) {
      global $DB;

      $query = "SELECT `".$this->getTable()."`.*
                FROM `".$this->getTable()."`
                WHERE `".static::getItilObjectForeignKey()."` = '$items_id'
                  AND `alternative_email` = '$email'";

      foreach ($DB->request($query) as $data) {
         return true;
      }
      return false;
   }


   function canUpdateItem() {

      return (parent::canUpdateItem()
              || (isset($this->fields['users_id'])
                  && ($this->fields['users_id'] == Session::getLoginUserID())));
   }


   /**
    * @since version 0.84
   **/
   function canDeleteItem() {

      return (parent::canDeleteItem()
              || (isset($this->fields['users_id'])
                  && ($this->fields['users_id'] == Session::getLoginUserID())));
   }


   /**
    * Print the object user form for notification
    *
    * @param $ID              integer ID of the item
    * @param $options   array
    *
    * @return Nothing (display)
   **/
   function showUserNotificationForm($ID, $options=array()) {
      global $CFG_GLPI;

      $this->check($ID,'w');

      if (!isset($this->fields['users_id'])) {
         return false;
      }
      $item = new static::$itemtype_1();

      echo "<br><form method='post' action='".$this->getFormURL()."'>";
      echo "<div class='center'>";
      echo "<table class='tab_cadre'>";
      echo "<tr class='tab_bg_2'><td>".$item->getTypeName(1)."</td>";
      echo "<td>";
      if ($item->getFromDB($this->fields[static::getItilObjectForeignKey()])) {
         echo $item->getField('name');
      }
      echo "</td></tr>";

      $user          = new User();
      $default_email = "";
      if ($user->getFromDB($this->fields["users_id"])) {
         $default_email = $user->getDefaultEmail();
         $emails        = $user->getAllEmails();
      }

      echo "<tr class='tab_bg_2'><td>".__('User')."</td>";
      echo "<td>".$user->getName()."</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Email Followup')."</td>";
      echo "<td>";
      Dropdown::showYesNo('use_notification', $this->fields['use_notification']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Email')."</td>";
      echo "<td>";
      if ((count($emails) ==  1)
          && !empty($default_email)
          && NotificationMail::isUserAddressValid($default_email)) {
         echo $default_email;

      } else if (count($emails) > 1) {
         // Several emails : select in the list
         echo "<select name='alternative_email' value=''>";
         echo "<option value='' ".(empty($this->fields['alternative_email'])?'selected':'').">".
                "$default_email</option>";
         foreach ($emails as $new_email) {
            if ($new_email != $default_email) {
               echo "<option value='$new_email' ".
                     ($this->fields['alternative_email'] == $new_email?'selected':'').
                     ">$new_email</option>";
            }
         }
         echo "</select>";
      } else {
         echo "<input type='text' size='40' name='alternative_email' value='".
                $this->fields['alternative_email']."'>";
      }
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td class='center' colspan='2'>";
      echo "<input type='submit' name='update' value=\""._sx('button','Save')."\" class='submit'>";
      echo "<input type='hidden' name='id' value='$ID'>";
      echo "<input type='hidden' name='itemtype' value='".static::$itemtype_1."'>";
      echo "</td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }


   function post_deleteFromDB() {
      global $CFG_GLPI;

      $donotif = $CFG_GLPI["use_mailing"];

      if (isset($this->input["_no_notif"]) && $this->input["_no_notif"]) {
         $donotif = false;
      }

      $item = $this->getConnexityItem(static::$itemtype_1, static::getItilObjectForeignKey());

      if ($item instanceof CommonDBTM) {
         if (($item->countSuppliers(CommonITILActor::ASSIGN) == 0)
             && ($item->countUsers(CommonITILActor::ASSIGN) == 0)
             && ($item->countGroups(CommonITILActor::ASSIGN) == 0)
             && ($item->fields['status'] != CommonITILObject::CLOSED)
             && ($item->fields['status'] != CommonITILObject::SOLVED)) {

            $item->update(array('id'     => $this->fields[static::getItilObjectForeignKey()],
                                'status' => CommonITILObject::INCOMING));
         } else {
            $item->updateDateMod($this->fields[static::getItilObjectForeignKey()]);

            if ($donotif) {
               $options = array();
               if (isset($this->fields['users_id'])) {
                  $options = array('_old_user' => $this->fields);
               }
               NotificationEvent::raiseEvent("update", $item, $options);
            }
         }

      }
      parent::post_deleteFromDB();
   }


   function post_addItem() {

      $item = new static::$itemtype_1();

      $no_stat_computation = true;
      if ($this->input['type'] == CommonITILActor::ASSIGN) {
         $no_stat_computation = false;
      }
      $item->updateDateMod($this->fields[static::getItilObjectForeignKey()], $no_stat_computation);

      // Check object status and update it if needed
      if (!isset($this->input['_from_object'])) {
         if ($item->getFromDB($this->fields[static::getItilObjectForeignKey()])) {
            if ($item->fields["status"] == CommonITILObject::INCOMING) {
               $item->update(array('id'     => $item->getID(),
                                   'status' => CommonITILObject::ASSIGNED));
            }
         }
      }
      parent::post_addItem();
   }

}
?>
