<?php
/*
 * @version $Id: notificationtarget.class.php 21578 2013-08-27 10:09:39Z moyo $
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

// Class NotificationTarget
// @since version 0.84
class NotificationTarget extends CommonDBChild {

   var $prefix                      = '';
   // From CommonDBChild
   static public $itemtype          = 'Notification';
   static public $items_id          = 'notifications_id';
   public $table                    = 'glpi_notificationtargets';

   var $notification_targets        = array();
   var $notification_targets_labels = array();
   var $notificationoptions         = 0;

   // Tags which have data in HTML : do not try to clean them
   public $html_tags                = array();

   // Data from the objet which can be used by the template
   // See https://forge.indepnet.net/projects/5/wiki/NotificationTemplatesTags
   var $datas                       = array();
   var $tag_descriptions            = array();

   // From CommonDBTM
   public $dohistory                = true;

   //Array to store emails by notification
   var $target                      = array();
   var $entity                      = '';

   //Object which raises the notification event
   var $obj                         = null;

   //Object which is associated with the event
   var $target_object               = null;

   // array of event name => event label
   var $events                      = array();
   var $options                     = array();
   var $raiseevent                  = '';

   const TAG_LANGUAGE               = 'lang';
   const TAG_VALUE                  = 'tag';
   const TAG_FOR_ALL_EVENTS         = 0;


   const ANONYMOUS_USER             = 0;
   const GLPI_USER                  = 1;
   const EXTERNAL_USER              = 2;

   /**
    * @param $entity          (default '')
    * @param $event           (default '')
    * @param $object          (default null)
    * @param $options   array
   **/
   function __construct($entity='', $event='', $object=null, $options=array()) {

      if ($entity === '') {
         $this->entity = (isset($_SESSION['glpiactive_entity'])?$_SESSION['glpiactive_entity']:0);
      } else {
         $this->entity = $entity;
      }

      if ($object) {
         if ($object instanceof CommonDBTM
             && isset($object->fields['id'])) {
            // Reread to avoid slashes issue
            $object->getFromDB($object->fields['id']);
         }
         $this->obj = $object;
         $this->getObjectItem($event);
      }

      $this->raiseevent = $event;
      $this->options    = $options;
      $this->getNotificationTargets($entity);
      $this->getAdditionalTargets($event);
      asort($this->notification_targets);
   }


   // Temporary hack for this class since 0.84
   static function getTable() {
      return 'glpi_notificationtargets';
   }


   /**
    * Validate send before doing it (may be overloaded : exemple for private tasks or followups)
    *
    * @param $event     string   notification event
    * @param $infos     array    of destination of the notification
    * @param $notify_me boolean  notify me on my action ?
    *                            ($infos contains users_id to check if the target is me)
    *                            (false by default)
    *
    * @return true
   **/
   function validateSendTo($event, array $infos, $notify_me=false) {
      
      if (!$notify_me) {
         if (isset($infos['users_id'])
             // Check login user and not event launch by crontask
             && ($infos['users_id'] === Session::getLoginUserID(false))) {
            return false;
         }
      }

      return true;
   }


   /**
    * @param $event  (default '')
   **/
   function getSubjectPrefix($event='') {

      $perso_tag = trim(Entity::getUsedConfig('notification_subject_tag', $this->getEntity(),
                                              '', ''));
      if (empty($perso_tag)) {
         $perso_tag = "GLPI";
      }
      return "[$perso_tag] ";

   }

   /**
    * @since version 0.84
    *
    * @return message id for notification
   **/
   function getMessageID() {
      return "";
   }


   static function getTypeName($nb=0) {
      return _n('Recipient', 'Recipients', $nb);
   }


   /**
    * Get a notificationtarget class by giving the object which raises the event
    *
    * @see CommonDBTM::getName
   **/
   function getName($options=array()) {

      if (isset($this->notification_targets_labels[$this->getField("type")]
                                                  [$this->getField("items_id")])) {

         return $this->notification_targets_labels[$this->getField("type")]
                                                  [$this->getField("items_id")];
      }
      return NOT_AVAILABLE;
   }


   /**
    * Get a notificationtarget class by giving the object which raises the event
    *
    * @param $item            the object which raises the event
    * @param $event           the event which will be used (default '')
    * @param $options   array of options
    *
    * @return a notificationtarget class or false
   **/
   static function getInstance($item, $event='', $options=array()) {

      if ($plug = isPluginItemType($item->getType())) {
         $name = 'Plugin'.$plug['plugin'].'NotificationTarget'.$plug['class'];
      } else {
         $name = 'NotificationTarget'.$item->getType();
      }

      $entity = 0;
      if (class_exists($name)) {
         //Entity ID exists in the options array
         if (isset($options['entities_id'])) {
            $entity = $options['entities_id'];

         //Item which raises the event contains an entityID
         } else if ($item->getEntityID() >= 0) {
            $entity = $item->getEntityID();

         }

         return new $name($entity, $event, $item, $options);
      }
      return false;
   }


   /**
    * Get a notificationtarget class by giving an itemtype
    *
    * @param $itemtype           the itemtype of the object which raises the event
    * @param $event              the event which will be used (default '')
    * @param $options   array    of options
    *
    * @return a notificationtarget class or false
   **/
   static function getInstanceByType($itemtype, $event='', $options=array()) {

      if (($itemtype != '')
          && ($item = getItemForItemtype($itemtype))) {
         return self::getInstance($item, $event, $options);
      }
      return false;
   }


   /**
    * @param $notification Notification object
   **/
   function showForNotification(Notification $notification) {

      if (!Session::haveRight("notification", "r")) {
         return false;
      }

      echo "<form name='notificationtargets_form' id='notificationtargets_form'
             method='post' action=' ";
      echo Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>" . _n('Recipient', 'Recipients', 2) . "</th></tr>";
      echo "<tr class='tab_bg_2'>";
      echo "<input type='hidden' name='notifications_id' value='".$notification->getField('id')."'>";
      echo "<input type='hidden' name='itemtype' value='".$notification->getField('itemtype')."'>";
      $this->showNotificationTargets($notification);
      echo "</tr>";
      echo "</table>";
      Html::closeForm();
   }


   /**
    * Display notification targets
    *
    * @param $notification the Notification object
   **/
   function showNotificationTargets(Notification $notification) {
      global $DB;

      if ($notification->getField('itemtype') != '') {
         $notifications_id = $notification->fields['id'];
         $this->getNotificationTargets($notification->getEntityID());

         $canedit = $notification->can($notifications_id,'w');

         $options = "";
         // Get User mailing
         $query = "SELECT `glpi_notificationtargets`.`items_id`,
                          `glpi_notificationtargets`.`id`
                   FROM `glpi_notificationtargets`
                   WHERE `glpi_notificationtargets`.`notifications_id` = '$notifications_id'
                         AND `glpi_notificationtargets`.`type` = '" . Notification::USER_TYPE . "'
                   ORDER BY `glpi_notificationtargets`.`items_id`";

         foreach ($DB->request($query) as $data) {
            if (isset($this->notification_targets[Notification::USER_TYPE."_".$data["items_id"]])) {
               unset($this->notification_targets[Notification::USER_TYPE."_".$data["items_id"]]);
            }

            if (isset($this->notification_targets_labels[Notification::USER_TYPE]
                                                        [$data["items_id"]])) {
               $name = $this->notification_targets_labels[Notification::USER_TYPE][$data["items_id"]];
            } else {
                $name = "&nbsp;";
            }
            $options .= "<option value='" . $data["id"] . "'>" . $name . "</option>";
         }

         // Get Profile mailing
         $query = "SELECT `glpi_notificationtargets`.`items_id`,
                          `glpi_notificationtargets`.`id`,
                          `glpi_profiles`.`name` AS `prof`
                   FROM `glpi_notificationtargets`
                   LEFT JOIN `glpi_profiles`
                        ON (`glpi_notificationtargets`.`items_id` = `glpi_profiles`.`id`)
                   WHERE `glpi_notificationtargets`.`notifications_id` = '$notifications_id'
                         AND `glpi_notificationtargets`.`type` = '".Notification::PROFILE_TYPE."'
                   ORDER BY `prof`";

         foreach ($DB->request($query) as $data) {
            $options .= "<option value='".$data["id"]."'>".sprintf(__('%1$s: %2$s'), __('Profile'),
                                                                   $data["prof"]).
                        "</option>";

            if (isset($this->notification_targets[Notification::PROFILE_TYPE."_".$data["items_id"]])) {
               unset($this->notification_targets[Notification::PROFILE_TYPE."_".$data["items_id"]]);
            }
         }

         // Get Group mailing
         $query = "SELECT `glpi_notificationtargets`.`items_id`,
                          `glpi_notificationtargets`.`id`,
                          `glpi_groups`.`name` AS `name`
                   FROM `glpi_notificationtargets`
                   LEFT JOIN `glpi_groups`
                        ON (`glpi_notificationtargets`.`items_id` = `glpi_groups`.`id`)
                   WHERE `glpi_notificationtargets`.`notifications_id`='$notifications_id'
                         AND `glpi_notificationtargets`.`type` = '" . Notification::GROUP_TYPE . "'
                   ORDER BY `name`;";

         foreach ($DB->request($query) as $data) {
            //TRANS: %s is the name of the group
            $options .= "<option value='".$data["id"]."'>".sprintf(__('%1$s: %2$s'), __('Group'),
                                                                   $data["name"]).
                        "</option>";

            if (isset($this->notification_targets[Notification::GROUP_TYPE."_".$data["items_id"]])) {
               unset($this->notification_targets[Notification::GROUP_TYPE."_".$data["items_id"]]);
            }
         }

         // Get Group mailing
         $query = "SELECT `glpi_notificationtargets`.`items_id`,
                          `glpi_notificationtargets`.`id`,
                          `glpi_groups`.`name` AS `name`
                   FROM `glpi_notificationtargets`
                   LEFT JOIN `glpi_groups`
                        ON (`glpi_notificationtargets`.`items_id` = `glpi_groups`.`id`)
                   WHERE `glpi_notificationtargets`.`notifications_id`='$notifications_id'
                         AND `glpi_notificationtargets`.`type`
                                                         = '".Notification::SUPERVISOR_GROUP_TYPE."'
                   ORDER BY `name`;";

         foreach ($DB->request($query) as $data) {
            $options .= "<option value='" . $data["id"] . "'>";
            //TRANS: %s is the name of the group
            $options .= sprintf(__('Manager of group %s'), $data["name"]) . "</option>";

            if (isset($this->notification_targets[Notification::SUPERVISOR_GROUP_TYPE."_".
                                                  $data["items_id"]])) {

               unset($this->notification_targets[Notification::SUPERVISOR_GROUP_TYPE."_".
                                                 $data["items_id"]]);
            }
         }

         if ($canedit) {
            echo "<td class='right'>";

            if (count($this->notification_targets)) {
               echo "<select name='mailing_to_add[]' multiple size='5'>";

               foreach ($this->notification_targets as $key => $val) {
                  list ($type, $items_id) = explode("_", $key);
                  echo "<option value='$key'>".$this->notification_targets_labels[$type][$items_id].
                       "</option>";
               }

               echo "</select>";
            }

            echo "</td><td class='center'>";

            if (count($this->notification_targets)) {
               echo "<input type='submit' class='submit' name='mailing_add' value='>>'>";
            }
            echo "<br><br>";

            if (!empty($options)) {
               echo "<input type='submit' class='submit' name='mailing_delete' value='<<'>";
            }
            echo "</td><td>";

         } else {
            echo "<td class='center'>";
         }

         if (!empty($options)) {
            echo "<select name='mailing_to_delete[]' multiple size='5'>";
            echo $options ."</select>";
         } else {
            echo "&nbsp;";
         }
         echo "</td>";
      }
   }


   /**
    * @param $input
   **/
   static function updateTargets($input) {

      $type   = "";
      $action = "";
      $target = self::getInstanceByType($input['itemtype']);

      if (isset($input['mailing_add'])) {
         $action = 'add';
      } else {
         $action = 'delete';
      }

      if (count($input["mailing_to_" . $action]) > 0) {
         switch ($action) {
            case "add" :
               foreach ($input["mailing_to_add"] as $tmp => $val) {
                  list($type, $items_id)   = explode("_", $val);
                  $tmp                     = array();
                  $tmp['items_id']         = $items_id;
                  $tmp['type']             = $type;
                  $tmp['notifications_id'] = $input['notifications_id'];
                  $target->add($tmp);
               }
               break;

            case "delete" :
               foreach ($input["mailing_to_delete"] as $tmp => $val) {
                  $tmp       = array();
                  $tmp['id'] = $val;
                  $target->delete($tmp);
               }
               break;
         }
      }
   }


   function addAdditionnalInfosForTarget() {
   }


   /**
    * @param $data
    *
    * @return empty array
   **/
   function addAdditionnalUserInfo(array $data) {
      return array();
   }


   /**
    * Add new mail with lang to current email array
    *
    * @param $data   array of data (mail, lang[, id for user])
   **/
   function addToAddressesList(array $data) {
      global $CFG_GLPI;

      // No email set : get default for user
      if (!isset($data['email'])
          && isset($data['users_id'])) {
         $data['email'] = UserEmail::getDefaultForUser($data['users_id']);
      }

      $new_mail = trim(Toolbox::strtolower($data['email']));
      $new_lang = '';
      // Default USER TYPE is ANONYMOUS
      $notificationoption = array('usertype' => self::ANONYMOUS_USER);


      if (isset($data['language'])) {
         $new_lang = trim($data['language']);
      }

      $username = '';
      if (isset($data['name']) && !empty($data['name'])) {
         $username = $data['name'];
      }
      if (isset($data['users_id']) && ($data['users_id'] > 0)) {
         $user = new User();
         if (!$user->getFromDB($data['users_id'])
             || ($user->getField('is_deleted') == 1)
             || ($user->getField('is_active') == 0)) {
            // unknown, deleted or disabled user
            return false;
         }
         $filt = getEntitiesRestrictRequest('AND', 'glpi_profiles_users', '', $this->getEntity(),
                                            true);
         $prof = Profile_User::getUserProfiles($data['users_id'], $filt);
         if (!count($prof)) {
            // No right on the entity of the object
            return false;
         }
         if (empty($username)) {
            $username = formatUserName(0, $user->getField('name'), $user->getField('realname'),
                                       $user->getField('firstname'), 0, 0, true);
         }
         // It is a GLPI user :
         $notificationoption['usertype'] = self::GLPI_USER;
         if (Auth::isAlternateAuth($user->fields['authtype'])
             || (($user->fields['authtype'] == Auth::NOT_YET_AUTHENTIFIED)
                 && Auth::isAlternateAuth(Auth::checkAlternateAuthSystems()))) {
            $notificationoption['usertype'] = self::EXTERNAL_USER;
         }
      }

      // Pass user type as argument ? forced for specific cases
      if (isset($data['usertype'])) {
         $notificationoption['usertype'] = $data['usertype'];
      }

      $notificationoption = array_merge($this->addAdditionnalUserInfo($data),
                                        $notificationoption);
      if (!empty($new_mail)) {
         if (NotificationMail::isUserAddressValid($new_mail)
             && !isset($this->target[$new_mail])) {

            $param = array('language'           => (empty($new_lang)
                                                    ? $CFG_GLPI["language"] : $new_lang),
                           'email'              => $new_mail,
                           'additionnaloption'  => $notificationoption,
                           'username'           => $username);
            if (isset($data['users_id']) && $data['users_id']) {
               $param['users_id'] = $data['users_id'];
            }

            $this->target[$new_mail] = $param;
         }
      }
   }


   /**
    * @since version 0.84
   **/
   function getDefaultUserType() {

      if (Auth::isAlternateAuth(Auth::checkAlternateAuthSystems())) {
         return self::EXTERNAL_USER;
      }
      return self::GLPI_USER;
   }


   /**
    * @since version 0.84
    *
    * @param $usertype
    * @param $redirect
   **/
   function formatURL($usertype, $redirect) {
      global $CFG_GLPI;

      switch ($usertype) {
         case self::EXTERNAL_USER :
            return urldecode($CFG_GLPI["url_base"]."/index.php?redirect=$redirect");

         case self::ANONYMOUS_USER :
            // No URL
            return '';

         case self::GLPI_USER :
            return urldecode($CFG_GLPI["url_base"]."/index.php?redirect=$redirect&noAUTO=1");
      }
   }


   /**
    * Get GLPI's global administrator email
   **/
   function getAdminAddress() {
      global $CFG_GLPI;

      $this->addToAddressesList(array("email"    => $CFG_GLPI["admin_email"],
                                      "name"     => $CFG_GLPI["admin_email_name"],
                                      "language" => $CFG_GLPI["language"],
                                      'usertype' => self::getDefaultUserType()));
   }


   /**
    * Get the email of the item's user
   **/
   function getItemAuthorAddress() {

      $user = new User();
      if ($this->obj->isField('users_id')
          && $user->getFromDB($this->obj->getField('users_id'))) {
         $this->addToAddressesList(array('language' => $user->getField('language'),
                                         'users_id' => $user->getField('id')));
      }
   }


   /**
    * Get entity admin email
   **/
   function getEntityAdminAddress() {
      global $DB, $CFG_GLPI;

      foreach ($DB->request('glpi_entities', array('id' => $this->entity)) as $row) {
         $data['language'] = $CFG_GLPI['language'];
         $data['email']    = $row['admin_email'];
         $data['name']     = $row['admin_email_name'];
         $data['usertype'] = self::getDefaultUserType();
         $this->addToAddressesList($data);
      }
   }


   /**
    * Get targets for all the users of a group
    *
    * @param $manager      0 all users, 1 only supervisors, 2 all users without supervisors
    * @param $group_id     id of the group
   **/
   function getAddressesByGroup($manager, $group_id) {
      global $DB;

      // members/managers of the group allowed on object entity
      // filter group with 'is_assign' (attribute can be unset after notification)
      $query = $this->getDistinctUserSql()."
               FROM `glpi_groups_users`
               INNER JOIN `glpi_users` ON (`glpi_groups_users`.`users_id` = `glpi_users`.`id`) ".
               $this->getProfileJoinSql()."
               INNER JOIN `glpi_groups` ON (`glpi_groups_users`.`groups_id` = `glpi_groups`.`id`)
               WHERE `glpi_groups_users`.`groups_id` = '$group_id'
                     AND `glpi_groups`.`is_notify`";
      if ($manager == 1) {
         $query .= " AND `glpi_groups_users`.`is_manager` ";
      } else if ($manager == 2) {
         $query .= " AND NOT `glpi_groups_users`.`is_manager` ";
      }

      foreach ($DB->request($query) as $data) {
         $this->addToAddressesList($data);
      }
   }


   function getDistinctUserSql() {

      return  "SELECT DISTINCT `glpi_users`.`id` AS users_id,
                               `glpi_users`.`language` AS language";
   }


   /**
    * Return main notification events for the object type
    * Internal use only => should use getAllEvents
    *
    * @return an array which contains : event => event label
   **/
   function getEvents() {
      return array();
   }


   /**
    * Return all (GLPI + plugins) notification events for the object type
    *
    * @return an array which contains : event => event label
   **/
   function getAllEvents() {

      $this->events = $this->getEvents();
      //If plugin adds new events for an already defined type
      Plugin::doHook('item_get_events', $this);

      return $this->events;
   }


   /**
    * @param $target    (default '')
    * @param $label     (default '')
    * @param $type      (=Notification::USER_TYPE)
   **/
   function addTarget ($target='', $label='', $type=Notification::USER_TYPE) {

      $key                                               = $type.'_'.$target;
      // Value used for sort
      $this->notification_targets[$key]                  = $type.'_'.$label;
      // Displayed value
      $this->notification_targets_labels[$type][$target] = $label;
   }


   function addProfilesToTargets() {
      global $DB;

      foreach ($DB->request('glpi_profiles') as $data) {
         $this->addTarget($data["id"], sprintf(__('%1$s: %2$s'), __('Profile'), $data["name"]),
                          Notification::PROFILE_TYPE);
      }
   }


   /**
    * @param $entity
   **/
   function addGroupsToTargets($entity) {
      global $DB;

      // Filter groups which can be notified and have members (as notifications are sent to members)
      $query = "SELECT `id`, `name`
                FROM `glpi_groups`".
                getEntitiesRestrictRequest(" WHERE", 'glpi_groups', 'entities_id', $entity, true)."
                      AND `is_usergroup`
                      AND `is_notify`
                ORDER BY `name`";

      foreach ($DB->request($query) as $data) {
         //Add group
         $this->addTarget($data["id"], sprintf(__('%1$s: %2$s'), __('Group'), $data["name"]),
                          Notification::GROUP_TYPE);
         //Add group supervisor
         $this->addTarget($data["id"], sprintf(__('%1$s: %2$s'), __('Manager of group'),
                                               $data["name"]),
                          Notification::SUPERVISOR_GROUP_TYPE);
         //Add group without supervisor
         $this->addTarget($data["id"], sprintf(__('%1$s: %2$s'), __('Group without manager'),
                                               $data["name"]),
                          Notification::GROUP_WITHOUT_SUPERVISOR_TYPE);
      }
   }


   /**
    * Return all the targets for this notification
    * Values returned by this method are the ones for the alerts
    * Can be updated by implementing the getAdditionnalTargets() method
    * Can be overwitten (like dbconnection)
    *
    * @param $entity the entity on which the event is raised
   **/
   function getNotificationTargets($entity) {

      if (Session::haveRight("config", "w")) {
         $this->addTarget(Notification::GLOBAL_ADMINISTRATOR, __('Administrator'));
      }
      $this->addTarget(Notification::ENTITY_ADMINISTRATOR, __('Entity administrator'));

      $this->addProfilesToTargets();
      $this->addGroupsToTargets($entity);
   }


   /**
    * Allows to add more notification targets
    * Can be overridden in some case (for example Ticket)
    *
    * @param $event specif event to get additional targets (default '')
   **/
   function getAdditionalTargets($event='') {
   }


   /**
    * Get addresses by a method not defined in NotificationTarget (specific to an itemtype)
    *
    * @param $data
    * @param $options
   **/
   function getSpecificTargets($data, $options) {
   }


   /**
    * Get item associated with the object on which the event was raised
    *
    * @param $event  (default '')
    *
    * @return the object associated with the itemtype
   **/
   function getObjectItem($event='') {
      $this->target_object = $this->obj;
   }


   /**
    * Add user to the notified users list
    *
    * @param $field              look for user looking for this field in the object
    *                            which raises the event
    * @param $search_in_object   search is done in the object ? if not  in target object
    *                            (false by default)
   **/
   function getUserByField($field, $search_in_object=false) {
      global $DB;

      $id = 0;
      if (!$search_in_object) {
         $id = $this->obj->getField($field);

      } else if ($this->target_object) {
         $id = $this->target_object->getField($field);
      }

      if ($id) {
         //Look for the user by his id
         $query = $this->getDistinctUserSql()."
                  FROM `glpi_users`".
                  $this->getProfileJoinSql()."
                  WHERE `glpi_users`.`id` = '$id'";

         foreach ($DB->request($query) as $data) {
            //Add the user email and language in the notified users list
            $this->addToAddressesList($data);
         }
      }
   }


   /**
    * Get technician in charge of the item
   **/
   function getItemTechnicianInChargeAddress() {
      $this->getUserByField('users_id_tech', true);
   }


   /**
    * Get Group of technicians in charge of the item
   **/
   function getItemGroupTechInChargeAddress() {

      if ($this->target_object) {
         $id = $this->target_object->getField('groups_id_tech');
         if ($id > 0) {
            $this->getAddressesByGroup(0, $id);
         }
      }
   }


   /**
    * Get user owner of the material
   **/
   function getItemOwnerAddress() {
      $this->getUserByField('users_id', true);
   }


   /**
    * Get users emails by profile
    *
    * @param $profiles_id the profile ID to get users emails
    *
    * @return nothing
   **/
   function getUsersAddressesByProfile($profiles_id) {
      global $DB;

      $query = $this->getDistinctUserSql().",
               glpi_profiles_users.entities_id AS entity
               FROM `glpi_users`".
               $this->getProfileJoinSql()."
               WHERE `glpi_profiles_users`.`profiles_id` = '".$profiles_id."';";

      foreach ($DB->request($query) as $data) {
         $this->addToAddressesList($data);
      }
   }


   /**
    * Get admin which sends the notification
    *
    * @param $options   array
    *
    * @return the sender's address
   **/
   function getSender($options=array()) {
      global $DB, $CFG_GLPI;

      //If the entity administrator's address is defined, return it
      foreach ($DB->request('glpi_entities',
               array('id' => $this->getEntity())) as $data) {

         if (NotificationMail::isUserAddressValid($data['admin_email'])) {
            return array('email' => $data['admin_email'],
                         'name'  => $data['admin_email_name']);
         }
      }
      //Entity admin is not defined, return the global admin's address
      return array('email' => $CFG_GLPI['admin_email'],
                   'name'  => $CFG_GLPI['admin_email_name']);
   }


   /**
    * Get the reply to address
    *
    * @param $options   array
    *
    * @return the reply to address
   **/
   function getReplyTo($options=array()) {
      global $DB, $CFG_GLPI;

      //If the entity administrator's address is defined, return it
      foreach ($DB->request('glpi_entities',
               array('id' => $this->getEntity())) as $data) {

         if (NotificationMail::isUserAddressValid($data['admin_reply'])) {
            return array('email' => $data['admin_reply'],
                         'name'  => $data['admin_reply_name']);
         }
      }
      //Entity admin is not defined, return the global admin's address
      return array('email' => $CFG_GLPI['admin_reply'],
                   'name'  => $CFG_GLPI['admin_reply_name']);
   }


   /**
    * Get addresses by type of notification
    *
    * @param $data
    * @param $options   array
   **/
   function getAddressesByTarget($data, $options=array()) {

      //Look for all targets whose type is Notification::USER_TYPE
      switch ($data['type']) {
         //Notifications for one people
         case Notification::USER_TYPE :

            switch ($data['items_id']) {
               //Send to glpi's global admin (as defined in the mailing configuration)
               case Notification::GLOBAL_ADMINISTRATOR :
                  $this->getAdminAddress();
                  break;

               //Send to the entity's admninistrator
               case Notification::ENTITY_ADMINISTRATOR :
                  $this->getEntityAdminAddress();
                  break;

               //Technician in charge of the ticket
               case Notification::ITEM_TECH_IN_CHARGE :
                  $this->getItemTechnicianInChargeAddress();
                  break;

               //Group of technician in charge of the ticket
               case Notification::ITEM_TECH_GROUP_IN_CHARGE :
                  $this->getItemGroupTechInChargeAddress();
                  break;

               //User who's owner of the material
               case Notification::ITEM_USER :
                  $this->getItemOwnerAddress();
                  break;

               //Send to the author of the ticket
               case Notification::AUTHOR :
                  $this->getItemAuthorAddress();
                  break;

               default :
                  //Maybe a target specific to a type
                  $this->getSpecificTargets($data,$options);
            }
            break;

         //Send to all the users of a group
         case Notification::GROUP_TYPE :
            $this->getAddressesByGroup(0, $data['items_id']);
            break;

         //Send to all the managers of a group
         case Notification::SUPERVISOR_GROUP_TYPE :
            $this->getAddressesByGroup(1, $data['items_id']);
            break;

         //Send to all the users of a group without managers
         case Notification::GROUP_WITHOUT_SUPERVISOR_TYPE :
            $this->getAddressesByGroup(2, $data['items_id']);
            break;
            
         //Send to all the users of a profile
         case Notification::PROFILE_TYPE :
            $this->getUsersAddressesByProfile($data['items_id']);
            break;

         default :
            //Maybe a target specific to a type
            $this->getSpecificTargets($data,$options);
      }
   }


   /**
    * Get all data needed for template processing
    * Provides minimum information for alerts
    * Can be overridden by each NotificationTartget class if needed
    *
    * @param $event
    * @param $options   array
   **/
   function getDatasForTemplate($event, $options=array()) {
   }


   function getTargets() {
      return $this->target;
   }


   function getEntity() {
      return $this->entity;
   }


   function clearAddressesList() {
      $this->target = array();
   }


   function getProfileJoinSql() {

      return " INNER JOIN `glpi_profiles_users`
                     ON (`glpi_profiles_users`.`users_id` = `glpi_users`.`id` ".
                         getEntitiesRestrictRequest("AND", "glpi_profiles_users", "entities_id",
                                                    $this->getEntity(), true).")";
   }


   /**
    * @param $event
    * @param $options
   **/
   function &getForTemplate($event, $options) {
      global $CFG_GLPI;

      $this->datas = array();
      $this->addTagToList(array('tag'   => 'glpi.url',
                                'value' => $CFG_GLPI['root_doc'],
                                'label' => __('URL of the application')));

      $this->getDatasForTemplate($event, $options);
      Plugin::doHook('item_get_datas', $this);

      return $this->datas;
   }


   function getTags() {
      return $this->tag_descriptions;
   }


   /**
    * @param $options   array
   **/
   function addTagToList($options=array()) {

      $p['tag']            = false;
      $p['value']          = true;
      $p['label']          = false;
      $p['events']         = self::TAG_FOR_ALL_EVENTS;
      $p['foreach']        = false;
      $p['lang']           = true;
      $p['allowed_values'] = array();

      foreach ($options as $key => $value) {
         $p[$key] = $value;
      }

      if ($p['tag']) {
         if (is_array($p['events'])) {
            $events = $this->getEvents();
            $tmp = array();

            foreach ($p['events'] as $event) {
               $tmp[$event] = $events[$event];
            }

            $p['events'] = $tmp;
         }

         if ($p['foreach']) {
            $tag = "##FOREACH".$p['tag']."## ##ENDFOREACH".$p['tag']."##";
            $this->tag_descriptions[self::TAG_VALUE][$tag] = $p;

          } else {
            if ($p['value']) {
               $tag = "##".$p['tag']."##";
               $this->tag_descriptions[self::TAG_VALUE][$tag] = $p;
            }

            if ($p['label']&&$p['lang']) {
               $tag = "##lang.".$p['tag']."##";
               $p['label'] = $p['label'];
               $this->tag_descriptions[self::TAG_LANGUAGE][$tag] = $p;
            }
         }
      }
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate
          && Session::haveRight('notification', 'r')) {
         switch ($item->getType()) {
            case 'Group' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(Notification::getTypeName(2),
                                              self::countForGroup($item));
               }
               return Notification::getTypeName(2);

            case 'Notification' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(self::getTypeName(2),
                                              countElementsInTable($this->getTable(),
                                                                   "notifications_id
                                                                        = '".$item->getID()."'"));
               }
               return self::getTypeName(2);
         }
      }
      return '';
   }


   /**
    * Count Notification for a group
    *
    * @since version 0.83
    *
    * @param $group Group object
    *
    * @return integer
   **/
   static function countForGroup(Group $group) {
      global $DB;

      $sql = "SELECT COUNT(*)AS cpt
              FROM `glpi_notificationtargets`
              INNER JOIN `glpi_notifications`
                    ON (`glpi_notifications`.`id` = `glpi_notificationtargets`.`notifications_id`)
              WHERE `items_id` = '".$group->getID()."'
                    AND (`type` = '".Notification::SUPERVISOR_GROUP_TYPE."'
                         OR `type` = '".Notification::GROUP_TYPE."') ".
                    getEntitiesRestrictRequest('AND', 'glpi_notifications', '', '', true);
      $data = $DB->request($sql)->next();
      return $data['cpt'];
   }


   /**
    * Display notification registered for a group
    *
    * @since version 0.83
    *
    * @param $group Group object
    *
    * @return nothing
   **/
   static function showForGroup(Group $group) {
      global $DB;

      if (!Session::haveRight("notification", "r")) {
         return false;
      }

      $sql = "SELECT `glpi_notifications`.`id`
              FROM `glpi_notificationtargets`
              INNER JOIN `glpi_notifications`
                    ON (`glpi_notifications`.`id` = `glpi_notificationtargets`.`notifications_id`)
              WHERE `items_id` = '".$group->getID()."'
                    AND (`type` = '".Notification::SUPERVISOR_GROUP_TYPE."'
                         OR `type` = '".Notification::GROUP_TYPE."') ".
                    getEntitiesRestrictRequest('AND', 'glpi_notifications', '', '', true);
      $req = $DB->request($sql);

      echo "<table class='tab_cadre_fixe'>";

      if ($req->numrows()) {
         echo "<tr><th>".__('Name')."</th>";
         echo "<th>".Entity::getTypeName(1)."</th>";
         echo "<th>".__('Active')."</th>";
         echo "<th>".__('Type')."</th>";
         echo "<th>".__('Notification method')."</th>";
         echo "<th>".NotificationEvent::getTypeName(1)."</th>";
         echo "<th>".NotificationTemplate::getTypeName(1)."</th></tr>";

         $notif = new Notification();

         Session::initNavigateListItems('Notification',
         //TRANS : %1$s is the itemtype name, %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'), Group::getTypeName(1),
                                                $group->getName()));

         foreach ($req as $data) {
            Session::addToNavigateListItems('Notification', $data['id']);

            if ($notif->getFromDB($data['id'])) {
               echo "<tr class='tab_bg_2'><td>".$notif->getLink();
               echo "</td><td>".Dropdown::getDropdownName('glpi_entities', $notif->getEntityID());
               echo "</td><td>".Dropdown::getYesNo($notif->getField('is_active'))."</td><td>";
               $itemtype = $notif->getField('itemtype');
               if ($tmp = getItemForItemtype($itemtype)) {
                  echo $tmp->getTypeName(1);
               } else {
                  echo "&nbsp;";
               }
               echo "</td><td>".Notification::getMode($notif->getField('mode'));
               echo "</td><td>".NotificationEvent::getEventName($itemtype,
                                                                $notif->getField('event'));
               echo "</td>".
                    "<td>".Dropdown::getDropdownName('glpi_notificationtemplates',
                                                     $notif->getField('notificationtemplates_id'));
               echo "</td></tr>";
            }
         }
      } else {
      echo "<tr class='tab_bg_2'><td class='b center'>".__('No item found')."</td></tr>";
      }
      echo "</table>";
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Group') {
         self::showForGroup($item);

      } else if ($item->getType() == 'Notification') {
         $target = self::getInstanceByType($item->getField('itemtype'),
                                           $item->getField('event'),
                                           array('entities_id' => $item->getField('entities_id')));
         if ($target) {
            $target->showForNotification($item);
         }
      }
      return true;
   }

}
?>
