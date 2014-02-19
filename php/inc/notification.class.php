<?php
/*
 * @version $Id: notification.class.php 21577 2013-08-27 10:05:08Z moyo $
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

// Class Notification
class Notification extends CommonDBTM {

// MAILING TYPE
//Notification to a user (sse mailing users type below)
   const USER_TYPE             = 1;
   //Notification to users of a profile
   const PROFILE_TYPE          = 2;
   //Notification to a users of a group
   const GROUP_TYPE            = 3;
   //Notification to the people in charge of the database synchronisation
   const MAILING_TYPE          = 4;
   //Notification to the supervisor of a group
   const SUPERVISOR_GROUP_TYPE = 5;
   //Notification to all user of a group except supervisor
   const GROUP_WITHOUT_SUPERVISOR_TYPE = 6;
   
   // MAILING USERS TYPE

   //Notification to the GLPI global administrator
   const GLOBAL_ADMINISTRATOR       = 1;
   //Notification to the technicial who's assign to a ticket
   const ASSIGN_TECH                = 2;
   //Notification to the owner of the item
   const AUTHOR                     = 3;
   //Notification to the technician previously in charge of the ticket
   const OLD_TECH_IN_CHARGE         = 4;
   //Notification to the technician in charge of the item
   const ITEM_TECH_IN_CHARGE        = 5;
   //Notification to the item's user
   const ITEM_USER                  = 6;
   //Notification to the ticket's recipient
   const RECIPIENT                  = 7;
   //Notificartion to the ticket's assigned supplier
   const SUPPLIER                   = 8;
   //Notification to a group of people
   const ASSIGN_GROUP               = 9;
   //Notification to the supervisor of the ticket's assigned group
   const SUPERVISOR_ASSIGN_GROUP    = 10;
   //Notification to the entity administrator
   const ENTITY_ADMINISTRATOR       = 11;
   //Notification to the supervisor of the ticket's requester group
   const SUPERVISOR_REQUESTER_GROUP = 12;
   //Notification to the ticket's requester group
   const REQUESTER_GROUP            = 13;
   //Notification to the ticket validation approver
   const VALIDATION_APPROVER        = 14;
   //Notification to the ticket validation requester
   const VALIDATION_REQUESTER       = 15;
   //Notification to the task assign user
   const TASK_ASSIGN_TECH           = 16;
   //Notification to the task author
   const TASK_AUTHOR                = 17;
   //Notification to the followup author
   const FOLLOWUP_AUTHOR            = 18;
   //Notification to the user
   const USER                       = 19;
   //Notification to the ticket's observer group
   const OBSERVER_GROUP             = 20;
   //Notification to the ticket's observer user
   const OBSERVER                   = 21;
   //Notification to the supervisor of the ticket's observer group
   const SUPERVISOR_OBSERVER_GROUP  = 22;
   //Notification to the group of technician in charge of the item
   const ITEM_TECH_GROUP_IN_CHARGE  = 23;
   // Notification to group of people without supervisor
   const ASSIGN_GROUP_WITHOUT_SUPERVISOR     = 24;
   //Notification to the ticket's requester group without supervisor
   const REQUESTER_GROUP_WITHOUT_SUPERVISOR  = 25;
   //Notification to the ticket's observer group without supervisor
   const OBSERVER_GROUP_WITHOUT_SUPERVISOR   = 26;

   // From CommonDBTM
   public $dohistory = true;


   static function getTypeName($nb=0) {
      return _n('Notification', 'Notifications', $nb);
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab('NotificationTarget', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }


   function showForm($ID, $options=array()) {
      global $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'><td>" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";

      echo "<td rowspan='6' class='middle right'>".__('Comments')."</td>";
      echo "<td class='center middle' rowspan='6'><textarea cols='45' rows='9' name='comment' >".
             $this->fields["comment"]."</textarea></td></tr>";

      echo "<tr class='tab_bg_1'><td>" . __('Active') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('is_active', $this->fields['is_active']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . __('Type') . "</td>";
      echo "<td>";
      if (Session::haveRight('config', 'w')
          && ($this->getEntityID() == 0)) {
         $rand = Dropdown::showItemTypes('itemtype', $CFG_GLPI["notificationtemplates_types"],
                                          array('value' => $this->fields['itemtype']));
      } else {
         $rand = Dropdown::showItemTypes('itemtype',
                                         array_diff($CFG_GLPI["notificationtemplates_types"],
                                                    array('Crontask', 'DBConnection', 'User')),
                                         array('value' => $this->fields['itemtype']));
      }

      $params = array('itemtype' => '__VALUE__');
      Ajax::updateItemOnSelectEvent("dropdown_itemtype$rand", "show_events",
                                    $CFG_GLPI["root_doc"]."/ajax/dropdownNotificationEvent.php",
                                    $params);
      Ajax::updateItemOnSelectEvent("dropdown_itemtype$rand", "show_templates",
                                    $CFG_GLPI["root_doc"]."/ajax/dropdownNotificationTemplate.php",
                                    $params);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . __('Notification method') . "</td>";
      echo "<td>";
      self::dropdownMode(array('value'=>$this->fields['mode']));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . NotificationEvent::getTypeName(1) . "</td>";
      echo "<td><span id='show_events'>";
      NotificationEvent::dropdownEvents($this->fields['itemtype'],
                                        array('value'=>$this->fields['event']));
      echo "</span></td></tr>";

      echo "<tr class='tab_bg_1'><td>". NotificationTemplate::getTypeName(1)."</td>";
      echo "<td><span id='show_templates'>";
      NotificationTemplate::dropdownTemplates('notificationtemplates_id', $this->fields['itemtype'],
                                              $this->fields['notificationtemplates_id']);
      echo "</span></td></tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();
      return true;
   }


   /**
    * @since version 0.84
    *
    * @param $field
    * @param $values
    * @param $options   array
   **/
   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'event':
            if (isset($values['itemtype']) && !empty($values['itemtype'])) {
               return NotificationEvent::getEventName($values['itemtype'],$values[$field]);
            }
            break;

         case 'mode':
            return self::getMode($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * @since version 0.84
    *
    * @param $field
    * @param $name               (default '')
    * @param $values             (default '')
    * @param $options      array
   **/
   static function getSpecificValueToSelect($field, $name='', $values='', array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      $options['display'] = false;
      switch ($field) {
         case 'event' :
            if (isset($values['itemtype'])
                && !empty($values['itemtype'])) {
               $options['value'] = $values[$field];
               $options['name']  = $name;
               return NotificationEvent::dropdownEvents($values['itemtype'],$options);
            }
            break;

         case 'mode' :
            $options['value'] = $values[$field];
            $options['name']  = $name;
            return self::dropdownMode($options);
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }


   function getSearchOptions() {

      $tab                       = array();
      $tab['common']             = __('Characteristics');

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['massiveaction']   = false;

      $tab[2]['table']           = $this->getTable();
      $tab[2]['field']           = 'event';
      $tab[2]['name']            = _n('Event', 'Events', 1);
      $tab[2]['massiveaction']   = false;
      $tab[2]['datatype']        = 'specific';
      $tab[2]['additionalfields'] = array('itemtype');

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'mode';
      $tab[3]['name']            = __('Notification method');
      $tab[3]['massiveaction']   = false;
      $tab[3]['datatype']        = 'specific';

      $tab[4]['table']           = 'glpi_notificationtemplates';
      $tab[4]['field']           = 'name';
      $tab[4]['name']            = _n('Notification template', 'Notification templates', 2);
      $tab[4]['datatype']        = 'itemlink';

      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'itemtype';
      $tab[5]['name']            = __('Type');
      $tab[5]['datatype']        = 'itemtypename';
      $tab[5]['itemtype_list']   = 'notificationtemplates_types';
      $tab[5]['massiveaction']   = false;

      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'is_active';
      $tab[6]['name']            = __('Active');
      $tab[6]['datatype']        = 'bool';

      $tab[16]['table']          = $this->getTable();
      $tab[16]['field']          = 'comment';
      $tab[16]['name']           = __('Comments');
      $tab[16]['datatype']       = 'text';

      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['massiveaction']  = false;
      $tab[80]['datatype']       = 'dropdown';

      $tab[86]['table']          = $this->getTable();
      $tab[86]['field']          = 'is_recursive';
      $tab[86]['name']           = __('Child entities');
      $tab[86]['datatype']       = 'bool';

      return $tab;
   }


   static function canCreate() {
      return Session::haveRight('notification', 'w');
   }


   static function canView() {
      return Session::haveRight('notification', 'r');
   }


   function canViewItem() {

      if ((($this->fields['itemtype'] == 'Crontask')
           || ($this->fields['itemtype'] == 'DBConnection'))
          && !Session::haveRight('config', 'w')) {
          return false;
      }
      return Session::haveAccessToEntity($this->getEntityID(), $this->isRecursive());
   }


   /**
    * Is the current user have right to update the current notification ?
    *
    * @return boolean
   **/
   function canCreateItem() {

      if ((($this->fields['itemtype'] == 'Crontask')
           || ($this->fields['itemtype'] == 'DBConnection'))
          && !Session::haveRight('config', 'w')) {
          return false;
      }
      return Session::haveAccessToEntity($this->getEntityID());
   }


   /**
    * Display a dropdown with all the available notification modes
    *
    * @param $options array of options
   **/
   static function dropdownMode($options) {

      $p['name']    = 'mode';
      $p['display'] = true;
      $p['value']   = '';

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      Dropdown::showFromArray($p['name'], self::getModes(), $p);
   }


   /**
    * Get notification method label (email only for the moment)
    *
    * @param $mode the mode to use
    *
    * @return the mode's label
   **/
   static function getMode($mode) {

      $tab = self::getModes();
      if (isset($tab[$mode])) {
         return $tab[$mode];
      }
      return NOT_AVAILABLE;
   }

   /**
    * Get notification method label (email only for the moment)
    *
    * @since versin 0.84
    *
    * @return the mode's label
   **/
   static function getModes() {
      return array('mail' => __('Email'));
   }


   function cleanDBonPurge() {
      global $DB;

      $query = "DELETE
                FROM `glpi_notificationtargets`
                WHERE `notifications_id` = '".$this->fields['id']."'";
      $DB->query($query);
   }


   /**
    * @param $mailing_options
   **/
   static function send($mailing_options) {

      $mail = new NotificationMail();
      $mail->sendNotification($mailing_options);
      $mail->ClearAddresses();
   }


   /**
    * Get the mailing signature for the entity
    *
    * @param $entity
   **/
   static function getMailingSignature($entity) {
      global $DB, $CFG_GLPI;

      foreach ($DB->request('glpi_entities', array('id' => $entity)) as $data) {
         if (!empty($data['mailing_signature'])) {
            return $data['mailing_signature'];
         }
      }
      return $CFG_GLPI['mailing_signature'];
   }


   /**
    * @param $event
    * @param $itemtype
    * @param $entity
   **/
   static function getNotificationsByEventAndType($event, $itemtype, $entity) {
      global $DB;

      $query = "SELECT `glpi_notifications`.*
                FROM `glpi_notifications`
                LEFT JOIN `glpi_entities`
                  ON (`glpi_entities`.`id` = `glpi_notifications`.`entities_id`)
                WHERE `glpi_notifications`.`itemtype` = '$itemtype'
                      AND `glpi_notifications`.`event` = '$event' ".
                      getEntitiesRestrictRequest("AND", "glpi_notifications", 'entities_id',
                                                 $entity, true) ."
                      AND `glpi_notifications`.`is_active`='1'
                ORDER BY `glpi_entities`.`level` DESC";

      return $DB->request($query);
   }

}
?>
