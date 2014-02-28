<?php
/*
 * @version $Id: commonitiltask.class.php 22698 2014-02-26 10:18:53Z moyo $
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

/// TODO extends it from CommonDBChild
abstract class CommonITILTask  extends CommonDBTM {


   // From CommonDBTM
   public $auto_message_on_action = false;

   function getItilObjectItemType() {
      return str_replace('Task','',$this->getType());
   }


   function canViewPrivates() {
      return false;
   }


   function canEditAll() {
      return false;
   }


   /**
    * Get the item associated with the current object.
    *
    * @since version 0.84
    *
    * @return object of the concerned item or false on error
   **/
   function getItem() {

      if ($item = getItemForItemtype($this->getItilObjectItemType())) {
         if ($item->getFromDB($this->fields[$item->getForeignKeyField()])) {
            return $item;
         }
     }
     return false;
   }


   /**
    * can read the parent ITIL Object ?
    *
    * @return boolean
   **/
   function canReadITILItem() {

      $itemtype = $this->getItilObjectItemType();
      $item     = new $itemtype();
      if (!$item->can($this->getField($item->getForeignKeyField()),'r')) {
         return false;
      }
      return true;
   }


   /**
    * Name of the type
    *
    * @param $nb : number of item in the type (default 0)
   **/
   static function getTypeName($nb=0) {
      return _n('Task', 'Tasks', $nb);

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
         case 'state' :
            return Planning::getState($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * @since version 0.84
    *
    * @param $field
    * @param $name            (default '')
    * @param $values          (default '')
    * @param $options   array
    *
    * @return string
   **/
   static function getSpecificValueToSelect($field, $name='', $values='', array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      $options['display'] = false;

      switch ($field) {
         case 'state':
            return Planning::dropdownState($name, $values[$field], false);
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (($item->getType() == $this->getItilObjectItemType())
          && $this->canView()) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            $restrict = "`".$item->getForeignKeyField()."` = '".$item->getID()."'";

            if ($this->maybePrivate()
                && !$this->canViewPrivates()) {
               $restrict .= " AND (`is_private` = '0'
                                   OR `users_id` = '" . Session::getLoginUserID() . "') ";
            }

            return self::createTabEntry(self::getTypeName(2),
                                        countElementsInTable($this->getTable(), $restrict));
         }
         return self::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      $itemtype = $item->getType().'Task';
      if ($task = getItemForItemtype($itemtype)) {
         $task->showSummary($item);
         return true;
      }
   }


   function post_deleteFromDB() {
      global $CFG_GLPI;
      
      $itemtype = $this->getItilObjectItemType();
      $item     = new $itemtype();
      $item->getFromDB($this->fields[$item->getForeignKeyField()]);
      $item->updateActiontime($this->fields[$item->getForeignKeyField()]);
      $item->updateDateMod($this->fields[$item->getForeignKeyField()]);

      // Add log entry in the ITIL object
      $changes[0] = 0;
      $changes[1] = '';
      $changes[2] = $this->fields['id'];
      Log::history($this->getField($item->getForeignKeyField()), $this->getItilObjectItemType(),
                   $changes, $this->getType(), Log::HISTORY_DELETE_SUBITEM);

      if ($CFG_GLPI["use_mailing"]) {
         $options = array('task_id'    => $this->fields["id"],
                           // Force is_private with data / not available
                        'is_private' => $this->isPrivate(),
                        // Pass users values
                        'task_users_id' => $this->fields['users_id'],
                        'task_users_id_tech' => $this->fields['users_id_tech']);
         NotificationEvent::raiseEvent('delete_task', $item, $options);
      }
   }


   function prepareInputForUpdate($input) {

      Toolbox::manageBeginAndEndPlanDates($input['plan']);

      if (isset($input['_planningrecall'])) {
         PlanningRecall::manageDatas($input['_planningrecall']);
      }

//      $input["actiontime"] = $input["hour"]*HOUR_TIMESTAMP+$input["minute"]*MINUTE_TIMESTAMP;

      if (isset($input['update'])
          && ($uid = Session::getLoginUserID())) { // Change from task form
         $input["users_id"] = $uid;
      }
      if (isset($input["plan"])) {
         $input["begin"]         = $input['plan']["begin"];
         $input["end"]           = $input['plan']["end"];
         $input["users_id_tech"] = $input['plan']["users_id"];

         $timestart              = strtotime($input["begin"]);
         $timeend                = strtotime($input["end"]);
         $input["actiontime"]    = $timeend-$timestart;

         unset($input["plan"]);

         if (!$this->test_valid_date($input)) {
            Session::addMessageAfterRedirect(__('Error in entering dates. The starting date is later than the ending date'),
                                             false, ERROR);
            return false;
         }
         Planning::checkAlreadyPlanned($input["users_id_tech"], $input["begin"], $input["end"],
                                       array($this->getType() => array($input["id"])));
      }

      return $input;
   }


   function post_updateItem($history=1) {
      global $CFG_GLPI;


      if (in_array("begin",$this->updates)) {
         PlanningRecall::managePlanningUpdates($this->getType(), $this->getID(),
                                               $this->fields["begin"]);
      }

      $update_done = false;
      $itemtype    = $this->getItilObjectItemType();
      $item        = new $itemtype();

      if ($item->getFromDB($this->fields[$item->getForeignKeyField()])) {
         $item->updateDateMod($this->fields[$item->getForeignKeyField()]);

         if (count($this->updates)) {
            $update_done = true;

            if (in_array("actiontime",$this->updates)) {
               $item->updateActionTime($this->input[$item->getForeignKeyField()]);
            }

            if (!empty($this->fields['begin'])
                && (($item->fields["status"] == CommonITILObject::INCOMING)
                     || ($item->fields["status"] == CommonITILObject::ASSIGNED))) {

               $input2['id']            = $item->getID();
               $input2['status']        = CommonITILObject::PLANNED;
               $input2['_disablenotif'] = true;
               $item->update($input2);
            }

            if ($CFG_GLPI["use_mailing"]) {
               $options = array('task_id'    => $this->fields["id"],
                                'is_private' => $this->isPrivate());
               NotificationEvent::raiseEvent('update_task', $item, $options);
            }

         }
      }

      if ($update_done) {
         // Add log entry in the ITIL object
         $changes[0] = 0;
         $changes[1] = '';
         $changes[2] = $this->fields['id'];
         Log::history($this->getField($item->getForeignKeyField()), $itemtype, $changes,
                      $this->getType(), Log::HISTORY_UPDATE_SUBITEM);
      }
   }


   function prepareInputForAdd($input) {

      $itemtype = $this->getItilObjectItemType();

      Toolbox::manageBeginAndEndPlanDates($input['plan']);

      if (isset($input["plan"])) {
         $input["begin"]         = $input['plan']["begin"];
         $input["end"]           = $input['plan']["end"];
         $input["users_id_tech"] = $input['plan']["users_id"];

         $timestart              = strtotime($input["begin"]);
         $timeend                = strtotime($input["end"]);
         $input["actiontime"]    = $timeend-$timestart;

         unset($input["plan"]);
         if (!$this->test_valid_date($input)) {
            Session::addMessageAfterRedirect(__('Error in entering dates. The starting date is later than the ending date'),
                                             false, ERROR);
            return false;
         }
      }

      $input["_job"] = new $itemtype();

      if (!$input["_job"]->getFromDB($input[$input["_job"]->getForeignKeyField()])) {
         return false;
      }

      // Pass old assign From object in case of assign change
      if (isset($input["_old_assign"])) {
         $input["_job"]->fields["_old_assign"] = $input["_old_assign"];
      }

      if (!isset($input["users_id"])
          && ($uid = Session::getLoginUserID())) {
         $input["users_id"] = $uid;
      }

      if (!isset($input["date"])) {
         $input["date"] = $_SESSION["glpi_currenttime"];
      }
      if (!isset($input["is_private"])) {
         $input['is_private'] = 0;
      }

      return $input;
   }


   function post_addItem() {
      global $CFG_GLPI;

      if (isset($this->input['_planningrecall'])) {
         $this->input['_planningrecall']['items_id'] = $this->fields['id'];
         PlanningRecall::manageDatas($this->input['_planningrecall']);
      }

      $donotif = $CFG_GLPI["use_mailing"];

      if (isset($this->fields["begin"]) && !empty($this->fields["begin"])) {
         Planning::checkAlreadyPlanned($this->fields["users_id_tech"], $this->fields["begin"],
                                       $this->fields["end"],
                                       array($this->getType() => array($this->fields["id"])));
      }

      if (isset($this->input["_no_notif"]) && $this->input["_no_notif"]) {
         $donotif = false;
      }

      $this->input["_job"]->updateDateMod($this->input[$this->input["_job"]->getForeignKeyField()]);

      if (isset($this->input["actiontime"]) && ($this->input["actiontime"] > 0)) {
         $this->input["_job"]->updateActionTime($this->input[$this->input["_job"]->getForeignKeyField()]);
      }

      if (!empty($this->fields['begin'])
          && (($this->input["_job"]->fields["status"] == CommonITILObject::INCOMING)
              || ($this->input["_job"]->fields["status"] == CommonITILObject::ASSIGNED))) {

         $input2['id']            = $this->input["_job"]->getID();
         $input2['status']        = CommonITILObject::PLANNED;
         $input2['_disablenotif'] = true;
         $this->input["_job"]->update($input2);
      }

      if ($donotif) {
         $options = array('task_id'    => $this->fields["id"],
                          'is_private' => $this->isPrivate());
         NotificationEvent::raiseEvent('add_task', $this->input["_job"], $options);
      }

      // Add log entry in the ITIL object
      $changes[0] = 0;
      $changes[1] = '';
      $changes[2] = $this->fields['id'];
      Log::history($this->getField($this->input["_job"]->getForeignKeyField()),
                   $this->input["_job"]->getTYpe(), $changes, $this->getType(),
                   Log::HISTORY_ADD_SUBITEM);
   }


   function post_getEmpty() {

      if ($this->maybePrivate()
          && isset($_SESSION['glpitask_private']) && $_SESSION['glpitask_private']) {

         $this->fields['is_private'] = 1;
      }
      // Default is todo
      $this->fields['state'] = 1;
   }


   /**
    * @see CommonDBTM::cleanDBonPurge()
    *
    * @since version 0.84
   **/
   function cleanDBonPurge() {

      $class = new PlanningRecall();
      $class->cleanDBonItemDelete($this->getType(), $this->fields['id']);
   }


   // SPECIFIC FUNCTIONS

   /**
    * Get the users_id name of the followup
    *
    * @param $link insert link ? (default 0)
    *
    *@return string of the users_id name
   **/
   //TODO function never used
   function getAuthorName($link=0) {
      return getUserName($this->fields["users_id"], $link);
   }


   /**
    * @see CommonDBTM::getName()
   **/
   function getName($options=array()) {

      $p['comments'] = false;

      if (is_array($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      if (!isset($this->fields['taskcategories_id'])) {
         return NOT_AVAILABLE;
      }

      if ($this->fields['taskcategories_id']) {
         $name = Dropdown::getDropdownName('glpi_taskcategories',
                                           $this->fields['taskcategories_id']);
      } else {
         $name = $this->getTypeName(1);
      }

      if ($p['comments']) {
         $addname  = Html::convDateTime($this->fields['date']);
         $addname = sprintf(__('%1$s, %2$s'), $addname, getUserName($this->fields['users_id']));
         // Manage private case
         if (isset($this->maybeprivate)) {
            $addname = sprintf(__('%1$s, %2$s'), $addname,
                               ($this->fields['is_private'] ? __('Private') : __('Public')));
         }
         $name = sprintf(__('%1$s (%2$s)'), $name, $addname);
      }
      return $name;
   }


   function getSearchOptions() {

      $tab                    = array();
      $tab['common']          = __('Characteristics');

      $tab[1]['table']        = $this->getTable();
      $tab[1]['field']        = 'content';
      $tab[1]['name']         = __('Description');
      $tab[1]['datatype']     = 'text';

      $tab[2]['table']        = 'glpi_taskcategories';
      $tab[2]['field']        = 'name';
      $tab[2]['name']         = __('Task category');
      $tab[2]['forcegroupby'] = true;
      $tab[2]['datatype']     = 'dropdown';

      $tab[3]['table']        = $this->getTable();
      $tab[3]['field']        = 'date';
      $tab[3]['name']         = __('Date');
      $tab[3]['datatype']     = 'datetime';

      if ($this->maybePrivate()) {
         $tab[4]['table']    = $this->getTable();
         $tab[4]['field']    = 'is_private';
         $tab[4]['name']     = __('Public followup');
         $tab[4]['datatype'] = 'bool';
      }

      $tab[5]['table']        = 'glpi_users';
      $tab[5]['field']        = 'name';
      $tab[5]['name']         = __('Technician');
      $tab[5]['datatype']     = 'dropdown';
      $tab[5]['right']        = 'own_ticket';


      $tab[6]['table']         = $this->getTable();
      $tab[6]['field']         = 'actiontime';
      $tab[6]['name']          = __('Total duration');
      $tab[6]['datatype']      = 'actiontime';
      $tab[6]['massiveaction'] = false;

      $tab[7]['table']         = $this->getTable();
      $tab[7]['field']         = 'state';
      $tab[7]['name']          = __('Status');
      $tab[7]['datatype']      = 'specific';

      return $tab;
   }


   /**
    * Current dates are valid ? begin before end
    *
    * @param $input
    *
    *@return boolean
   **/
   function test_valid_date($input) {

      return (!empty($input["begin"])
              && !empty($input["end"])
              && (strtotime($input["begin"]) < strtotime($input["end"])));
   }


   /**
    * Populate the planning with planned tasks
    *
    * @param $itemtype  itemtype
    * @param $options   array    of options must contains :
    *    - who ID of the user (0 = undefined)
    *    - who_group ID of the group of users (0 = undefined)
    *    - begin Date
    *    - end Date
    *
    * @return array of planning item
   **/
   static function genericPopulatePlanning($itemtype, $options=array()) {
      global $DB, $CFG_GLPI;

      $interv = array();

      if (!isset($options['begin']) || ($options['begin'] == 'NULL')
          || !isset($options['end']) || ($options['end'] == 'NULL')) {
         return $interv;
      }

      if (!$item = getItemForItemtype($itemtype)) {
         return;
      }
      $parentitemtype = $item->getItilObjectItemType();
      if (!$parentitem = getItemForItemtype($parentitemtype)) {
         return;
      }

      $who       = $options['who'];
      $who_group = $options['who_group'];
      $begin     = $options['begin'];
      $end       = $options['end'];

      // Get items to print
      $ASSIGN = "";

      if ($who_group === "mine") {
         if (count($_SESSION["glpigroups"])) {
            $groups = implode("','",$_SESSION['glpigroups']);
            $ASSIGN = "`".$item->getTable()."`.`users_id_tech`
                           IN (SELECT DISTINCT `users_id`
                               FROM `glpi_groups_users`
                               INNER JOIN `glpi_groups`
                                  ON (`glpi_groups_users`.`groups_id` = `glpi_groups`.`id`)
                               WHERE `glpi_groups_users`.`groups_id` IN ('$groups')
                                     AND `glpi_groups`.`is_assign`)
                                     AND ";
         } else { // Only personal ones
            $ASSIGN = "`".$item->getTable()."`.`users_id_tech` = '$who'
                       AND ";
         }

      } else {
         if ($who > 0) {
            $ASSIGN = "`".$item->getTable()."`.`users_id_tech` = '$who'
                       AND ";
         }

         if ($who_group > 0) {
            $ASSIGN = "`".$item->getTable()."`.`users_id_tech` IN (SELECT `users_id`
                                                                   FROM `glpi_groups_users`
                                                                   WHERE `groups_id` = '$who_group')
                                                                         AND ";
         }
      }
      if (empty($ASSIGN)) {
         $ASSIGN = "`".$item->getTable()."`.`users_id_tech`
                        IN (SELECT DISTINCT `glpi_profiles_users`.`users_id`
                            FROM `glpi_profiles`
                            LEFT JOIN `glpi_profiles_users`
                                 ON (`glpi_profiles`.`id` = `glpi_profiles_users`.`profiles_id`)
                            WHERE `glpi_profiles`.`interface` = 'central' ".
                                  getEntitiesRestrictRequest("AND", "glpi_profiles_users", '',
                                                             $_SESSION["glpiactive_entity"], 1).")
                     AND ";
      }

      $addrestrict = '';
      if ($parentitem->maybeDeleted()) {
         $addrestrict = 'AND NOT `'.$parentitem->getTable().'`.`is_deleted`';
      }

      $query = "SELECT `".$item->getTable()."`.*
                FROM `".$item->getTable()."`
                INNER JOIN `".$parentitem->getTable()."`
                  ON (`".$parentitem->getTable()."`.`id` = `".$item->getTable()."`.`".$parentitem->getForeignKeyField()."`)
                WHERE $ASSIGN
                      '$begin' < `".$item->getTable()."`.`end`
                      AND '$end' > `".$item->getTable()."`.`begin`
                      $addrestrict
                ORDER BY `".$item->getTable()."`.`begin`";

      $result = $DB->query($query);

      $interv = array();

      if ($DB->numrows($result) > 0) {
         for ($i=0 ; $data=$DB->fetch_assoc($result) ; $i++) {
            if ($item->getFromDB($data["id"])) {
               if ($parentitem->getFromDBwithData($item->fields[$parentitem->getForeignKeyField()],0)) {
                  $key = $data["begin"]."$$$".$i;
                  // Do not check entity here because webcal used non authenticated access
//                  if (Session::haveAccessToEntity($item->fields["entities_id"])) {
                     $interv[$key]['itemtype']  = $itemtype;
                     $interv[$data["begin"]."$$$".$i]["url"]
                                                = $CFG_GLPI["url_base"]."/index.php?redirect=".
                                                   strtolower($parentitemtype)."_".
                                                   $item->fields[$parentitem->getForeignKeyField()];

                     $interv[$key][$item->getForeignKeyField()] = $data["id"];
                     $interv[$key]["id"]                        = $data["id"];
                     if (isset($data["state"])) {
                        $interv[$key]["state"]                  = $data["state"];
                     }
                     $interv[$key][$parentitem->getForeignKeyField()]
                                                     = $item->fields[$parentitem->getForeignKeyField()];
                     $interv[$key]["users_id"]       = $data["users_id"];
                     $interv[$key]["users_id_tech"]  = $data["users_id_tech"];

                     if (strcmp($begin,$data["begin"]) > 0) {
                        $interv[$key]["begin"] = $begin;
                     } else {
                        $interv[$key]["begin"] = $data["begin"];
                     }

                     if (strcmp($end,$data["end"]) < 0) {
                        $interv[$key]["end"] = $end;
                     } else {
                        $interv[$key]["end"] = $data["end"];
                     }

                     $interv[$key]["name"]     = $parentitem->fields["name"];
                     $interv[$key]["content"]  = Html::resume_text($parentitem->fields["content"],
                                                                   $CFG_GLPI["cut"]);
                     $interv[$key]["status"]   = $parentitem->fields["status"];
                     $interv[$key]["priority"] = $parentitem->fields["priority"];

                     /// Specific for tickets
                     $interv[$key]["device"] = '';
                     if (isset($parentitem->hardwaredatas)) {
                        $interv[$key]["device"] = ($parentitem->hardwaredatas
                                                   ?$parentitem->hardwaredatas->getName() :'');
                     }
//                  }
               }
            }
         }
      }
      return $interv;
   }


   /**
    * Display a Planning Item
    *
    * @param $itemtype  itemtype
    * @param $val       Array    of the item to display
    *
    * @return Already planned information
   **/
   static function genericGetAlreadyPlannedInformation($itemtype, array $val) {
      global $CFG_GLPI;

      if ($item = getItemForItemtype($itemtype)) {
         $objectitemtype = $item->getItilObjectItemType();

         //TRANS: %1$s is a type, %2$$ is a date, %3$s is a date
         $out  = sprintf(__('%1$s: from %2$s to %3$s:'), $item->getTypeName(1),
                         Html::convDateTime($val["begin"]), Html::convDateTime($val["end"]));
         $out .= "<br><a href='".Toolbox::getItemTypeFormURL($objectitemtype)."?id=".
                       $val[getForeignKeyFieldForItemType($objectitemtype)]."&amp;forcetab=".$itemtype."$1'>";
         $out .= Html::resume_text($val["name"],80).'</a>';

         return $out;
      }
   }


   /**
    * Display a Planning Item
    *
    * @param $itemtype  itemtype
    * @param $val       Array of the item to display
    * @param $who             ID of the user (0 if all)
    * @param $type            position of the item in the time block (in, through, begin or end)
    *                         (default '')
    * @param $complete        complete display (more details) (default 0)
    *
    * @return Nothing (display function)
   **/
   static function genericDisplayPlanningItem($itemtype, array $val, $who, $type="", $complete=0) {
      global $CFG_GLPI;

      $rand      = mt_rand();
      $styleText = "";
      if (isset($val["state"])) {
         switch ($val["state"]) {
            case 2 : // Done
               $styleText = "color:#747474;";
               break;
         }
      }

      $parenttype = str_replace('Task','',$itemtype);
      if ($parent = getItemForItemtype($parenttype)) {
         $parenttype_fk = $parent->getForeignKeyField();
      } else {
         return;
      }

      echo "<img src='".$CFG_GLPI["root_doc"]."/pics/rdv_interv.png' alt='' title=\"".
             Html::entities_deep($parent->getTypeName(1))."\">&nbsp;&nbsp;";
      echo "<img src='".$parent->getStatusIconURL($val["status"])."' alt='".
             Html::entities_deep($parent->getStatus($val["status"]))."' title=\"".
             Html::entities_deep($parent->getStatus($val["status"]))."\">";
      echo "&nbsp;<a id='content_tracking_".$val["id"].$rand."'
                   href='".Toolbox::getItemTypeFormURL($parenttype)."?id=".$val[$parenttype_fk]."'
                   style='$styleText'>";

      switch ($type) {
         case "in" :
            //TRANS: %1$s is the start time of a planned item, %2$s is the end
            printf(__('From %1$s to %2$s :'),
                   date("H:i",strtotime($val["begin"])), date("H:i",strtotime($val["end"]))) ;
            break;

         case "through" :
            break;

         case "begin" :
            //TRANS: %s is the start time of a planned item
            printf(__('Start at %s:'), date("H:i", strtotime($val["begin"]))) ;
            break;

         case "end" :
            //TRANS: %s is the end time of a planned item
            printf(__('End at %s:'), date("H:i", strtotime($val["end"]))) ;
            break;
      }

      echo "<br>";
      //TRANS: %1$s is name of the item, %2$d is its ID
      printf(__('%1$s (#%2$d)'), Html::resume_text($val["name"],80), $val[$parenttype_fk]);

      if (!empty($val["device"])) {
         echo "<br>".$val["device"];
      }

      if ($who <= 0) { // show tech for "show all and show group"
         echo "<br>";
         //TRANS: %s is user name
         printf(__('By %s'), getUserName($val["users_id_tech"]));
      }

      echo "</a>";

      $recall = '';
      if (isset($val[getForeignKeyFieldForItemType($itemtype)])
          && PlanningRecall::isAvailable()) {
         $pr = new PlanningRecall();
         if ($pr->getFromDBForItemAndUser($val['itemtype'],
                                          $val[getForeignKeyFieldForItemType($itemtype)],
                                          Session::getLoginUserID())) {
            $recall = "<br><span class='b'>".sprintf(__('Recall on %s'),
                                                     Html::convDateTime($pr->fields['when'])).
                      "<span>";
         }
      }


      if ($complete) {
         echo "<br><span class='b'>";
         if (isset($val["state"])) {
            echo Planning::getState($val["state"])."<br>";
         }
         echo sprintf(__('%1$s: %2$s'), __('Priority'), $parent->getPriorityName($val["priority"]));
         echo "<br>".__('Description')."</span><br>".$val["content"];
         echo $recall;

      } else {
         $content = "<span class='b'>";
         if (isset($val["state"])) {
            $content .= Planning::getState($val["state"])."<br>";
         }
         $content .= sprintf(__('%1$s: %2$s'), __('Priority'), $parent->getPriorityName($val["priority"])).
                    "<br>".__('Description')."</span><br>".$val["content"].$recall.
                    "</div>";
         Html::showToolTip($content, array('applyto' => "content_tracking_".$val["id"].$rand));
      }
   }


   /**
    * @param $item         CommonITILObject
    * @param $rand
    * @param $showprivate  (false by default)
   **/
   function showInObjectSumnary(CommonITILObject $item, $rand, $showprivate=false) {
      global $DB, $CFG_GLPI;

      $canedit = $this->can($this->fields['id'],'w');

      echo "<tr class='tab_bg_";
      if ($this->maybePrivate()
          && ($this->fields['is_private'] == 1)) {
         echo "4' ";
      } else {
         echo "2' ";
      }

      if ($canedit) {
         echo "style='cursor:pointer' onClick=\"viewEditFollowup".$item->fields['id'].
               $this->fields['id']."$rand();\"";
      }

      echo " id='viewfollowup" . $this->fields[$item->getForeignKeyField()] . $this->fields["id"] .
            "$rand'>";

      echo "<td>";
      $typename = $this->getTypeName(1);
      if ($this->fields['taskcategories_id']) {
         printf(__('%1$s - %2$s'), $typename,
                Dropdown::getDropdownName('glpi_taskcategories',
                                          $this->fields['taskcategories_id']));
      } else {
         echo $typename;
      }
      echo "</td>";

      echo "<td>";
      if ($canedit) {
         echo "\n<script type='text/javascript' >\n";
         echo "function viewEditFollowup" . $item->fields['id'] . $this->fields["id"] . "$rand() {\n";
         $params = array('type'       => $this->getType(),
                         'parenttype' => $item->getType(),
                         $item->getForeignKeyField()
                                      => $this->fields[$item->getForeignKeyField()],
                         'id'         => $this->fields["id"]);
         Ajax::updateItemJsCode("viewfollowup" . $item->fields['id'] . "$rand",
                                $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
         echo "};";
         echo "</script>\n";
      }
      //else echo "--no--";
      echo Html::convDateTime($this->fields["date"]) . "</td>";
      echo "<td class='left'>" . nl2br($this->fields["content"]) . "</td>";
      echo "<td>";
      echo Html::timestampToString($this->fields["actiontime"], 0);

      echo "</td>";
      echo "<td>" . getUserName($this->fields["users_id"]) . "</td>";
      if ($this->maybePrivate() && $showprivate) {
         echo "<td>".Dropdown::getYesNo($this->fields["is_private"])."</td>";
      }

      echo "<td>";

      if (empty($this->fields["begin"])) {
         if (isset($this->fields["state"])) {
            echo Planning::getState($this->fields["state"])."<br>";
         }
         _e('None');
      } else {
         echo "<table>";
         if (isset($this->fields["state"])) {
            echo "<tr><td>"._x('item', 'State')."</td><td>";
            echo Planning::getState($this->fields["state"])."</td></tr>";
         }
         echo "<tr><td>".__('Begin')."</td><td>";
         echo Html::convDateTime($this->fields["begin"])."</td></tr>";
         echo "<tr><td>".__('End')."</td><td>";
         echo Html::convDateTime($this->fields["end"])."</td></tr>";
         echo "<tr><td>".__('By')."</td><td>";
         echo getUserName($this->fields["users_id_tech"])."</td></tr>";
         if (PlanningRecall::isAvailable()) {
            echo "<tr><td>"._x('Planning','Reminder')."</td><td>";
            PlanningRecall::specificForm(array('itemtype' => $this->getType(),
                                               'items_id' => $this->fields["id"]));

            echo "</td></tr>";
         }
         echo "</table>";
      }
      echo "</td>";

      echo "</tr>\n";
   }


   /** form for Task
    *
    * @param $ID        Integer : Id of the task
    * @param $options   array
    *     -  parent Object : the object
   **/
   function showForm($ID, $options=array()) {
      global $DB, $CFG_GLPI;

      if (isset($options['parent']) && !empty($options['parent'])) {
         $item = $options['parent'];
      }

      $fkfield = $item->getForeignKeyField();

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $options[$fkfield] = $item->getField('id');
         $this->check(-1,'w',$options);
      }

      $canplan = Session::haveRight("show_planning", "1");

      $this->showFormHeader($options);

      $rowspan = 5 ;
      if ($this->maybePrivate()) {
         $rowspan++;
      }
      // Recall
      if (!empty($this->fields["begin"])) {
         $rowspan++;
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td rowspan='$rowspan' class='middle right'>".__('Description')."</td>";
      echo "<td class='center middle' rowspan='$rowspan'>".
           "<textarea name='content' cols='50' rows='$rowspan'>".$this->fields["content"].
           "</textarea></td>";
      if ($ID > 0) {
         echo "<td>".__('Date')."</td>";
         echo "<td>";
         Html::showDateTimeFormItem("date", $this->fields["date"], 1, false);
      } else {
         echo "<td colspan='2'>&nbsp;";
      }
      echo "<input type='hidden' name='$fkfield' value='".$this->fields[$fkfield]."'>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Category')."</td><td>";
      TaskCategory::dropdown(array('value'  => $this->fields["taskcategories_id"],
                                   'entity' => $item->fields["entities_id"]));
      echo "</td></tr>\n";

      if (isset($this->fields["state"])) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>".__('Status')."</td><td>";
         Planning::dropdownState("state", $this->fields["state"]);
         echo "</td></tr>\n";
      }

      if ($this->maybePrivate()) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>".__('Private')."</td>";
         echo "<td>";
         Dropdown::showYesNo('is_private',$this->fields["is_private"]);
         echo "</td>";
         echo "</tr>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>". __('Duration')."</td><td>";

      $toadd = array();
      for ($i=9 ; $i<=100 ; $i++) {
         $toadd[] = $i*HOUR_TIMESTAMP;
      }

      Dropdown::showTimeStamp("actiontime", array('min'             => 0,
                                                  'max'             => 8*HOUR_TIMESTAMP,
                                                  'value'           => $this->fields["actiontime"],
                                                  'addfirstminutes' => true,
                                                  'inhours'         => true,
                                                  'toadd'           => $toadd));

      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Planning')."</td>";
      echo "<td>";

      if (!empty($this->fields["begin"])) {

         if (Session::haveRight('show_planning', 1)) {
            echo "<script type='text/javascript' >\n";
            echo "function showPlan".$ID."() {\n";
            echo "Ext.get('plan').setDisplayed('none');";
            $params = array('form'     => 'followups',
                            'users_id' => $this->fields["users_id_tech"],
                            'id'       => $this->fields["id"],
                            'begin'    => $this->fields["begin"],
                            'end'      => $this->fields["end"],
                            'entity'   => $item->fields["entities_id"],
                            'itemtype' => $this->getType(),
                            'items_id' => $this->getID());
            Ajax::updateItemJsCode('viewplan', $CFG_GLPI["root_doc"] . "/ajax/planning.php",
                                   $params);
            echo "}";
            echo "</script>\n";
            echo "<div id='plan' onClick='showPlan".$ID."()'>\n";
            echo "<span class='showplan'>";
         }

         if (isset($this->fields["state"])) {
            echo Planning::getState($this->fields["state"])."<br>";
         }
         printf(__('From %1$s to %2$s'), Html::convDateTime($this->fields["begin"]),
                Html::convDateTime($this->fields["end"]));
         echo "<br>".getUserName($this->fields["users_id_tech"]);

         if (Session::haveRight('show_planning', 1)) {
            echo "</span>";
            echo "</div>\n";
            echo "<div id='viewplan'></div>\n";
         }

      } else {
         if (Session::haveRight('show_planning', 1)) {
            echo "<script type='text/javascript' >\n";
            echo "function showPlanUpdate() {\n";
            echo "Ext.get('plan').setDisplayed('none');";
            $params = array('form'     => 'followups',
                            'users_id' => Session::getLoginUserID(),
                            'entity'   => $_SESSION["glpiactive_entity"],
                            'itemtype' => $this->getType(),
                            'items_id' => $this->getID());
            Ajax::updateItemJsCode('viewplan', $CFG_GLPI["root_doc"]."/ajax/planning.php", $params);
            echo "};";
            echo "</script>";

            echo "<div id='plan'  onClick='showPlanUpdate()'>\n";
            echo "<span class='showplan'>".__('Plan this task')."</span>";
            echo "</div>\n";
            echo "<div id='viewplan'></div>\n";

         } else {
            _e('None');
         }
      }

      echo "</td></tr>";

      if (!empty($this->fields["begin"])
          && PlanningRecall::isAvailable()) {

         echo "<tr class='tab_bg_1'><td>"._x('Planning','Reminder')."</td><td>";
         PlanningRecall::dropdown(array('itemtype' => $this->getType(),
                                        'items_id' => $this->getID()));
         echo "</td></tr>";
      }

      $this->showFormButtons($options);

      return true;
   }


   /**
    * Show the current task sumnary
    *
    * @param $item   CommonITILObject
   **/
   function showSummary(CommonITILObject $item) {
      global $DB, $CFG_GLPI;

      if (!static::canView()) {
         return false;
      }

      $tID = $item->fields['id'];

      // Display existing Followups
      $showprivate = $this->canViewPrivates();
      $caneditall  = $this->canEditAll();
      $tmp         = array($item->getForeignKeyField() => $tID);
      $canadd      = $this->can(-1, 'w', $tmp);

      $RESTRICT = "";
      if ($this->maybePrivate() && !$showprivate) {
         $RESTRICT = " AND (`is_private` = '0'
                            OR `users_id` ='" . Session::getLoginUserID() . "') ";
      }

      $query = "SELECT `id`, `date`
                FROM `".$this->getTable()."`
                WHERE `".$item->getForeignKeyField()."` = '$tID'
                      $RESTRICT
                ORDER BY `date` DESC";
      $result = $DB->query($query);

      $rand = mt_rand();

      if ($caneditall || $canadd) {
         echo "<div id='viewfollowup" . $tID . "$rand'></div>\n";
      }
      if ($canadd) {
         echo "<script type='text/javascript' >\n";
         echo "function viewAddFollowup" . $item->fields['id'] . "$rand() {\n";
         $params = array('type'                      => $this->getType(),
                         'parenttype'                => $item->getType(),
                         $item->getForeignKeyField() => $item->fields['id'],
                         'id'                        => -1);
         Ajax::updateItemJsCode("viewfollowup" . $item->fields['id'] . "$rand",
                                $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
         echo "};";
         echo "</script>\n";
         if (($item->fields["status"] != CommonITILObject::SOLVED)
             && ($item->fields["status"] != CommonITILObject::CLOSED)) {
            echo "<div class='center'>".
                 "<a class='vsubmit' href='javascript:viewAddFollowup".$item->fields['id']."$rand();'>";
            echo __('Add a new task')."</a></div></p><br>\n";
         }
      }

      if ($DB->numrows($result) == 0) {
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'><th>" . __('No task found.');
         echo "</th></tr></table>";
      } else {
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr><th>".__('Type')."</th><th>" . __('Date') . "</th>";
         echo "<th>" . __('Description') . "</th><th>" .  __('Duration') . "</th>";
         echo "<th>" . __('Writer') . "</th>";
         if ($this->maybePrivate() && $showprivate) {
            echo "<th>" . __('Private') . "</th>";
         }
         echo "<th>" . __('Planning') . "</th></tr>\n";

         while ($data = $DB->fetch_assoc($result)) {
            if ($this->getFromDB($data['id'])) {
               $this->showInObjectSumnary($item, $rand, $showprivate);
            }
         }
         echo "</table>";
      }
   }


   /**
    * Form for Ticket or Problem Task on Massive action
   **/
   function showFormMassiveAction() {

      echo "&nbsp;".__('Category')."&nbsp;";
      TaskCategory::dropdown();

      echo "<br>".__('Description')." ";
      echo "<textarea name='content' cols='50' rows='6'></textarea>&nbsp;";

      if ($this->maybePrivate()) {
         echo "<input type='hidden' name='is_private' value='".$_SESSION['glpitask_private']."'>";
      }
      echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
   }


}
?>
