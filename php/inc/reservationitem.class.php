<?php
/*
 * @version $Id: reservationitem.class.php 21712 2013-09-09 18:01:43Z yllen $
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

/// Reservation item class
class ReservationItem extends CommonDBChild {

   /// From CommonDBChild
   static public $itemtype          = 'itemtype';
   static public $items_id          = 'items_id';

   static public $checkParentRights = self::HAVE_VIEW_RIGHT_ON_ITEM;


   static function getTypeName($nb=0) {
      return _n('Reservable item', 'Reservable items',$nb);
   }


   /**
    * @since 0.84
   **/
   static function canCreate() {
      return static::canUpdate();
   }


   /**
    * @since 0.84
   **/
   static function canView() {
      return true;
   }


   /**
    * @since 0.84
   **/
   static function canUpdate() {
      return Session::haveRight("reservation_central", "w");
   }


   /**
    * @since 0.84
   **/
   static function canDelete() {
      return static::canUpdate();
   }


   // From CommonDBTM
   /**
    * Retrieve an item from the database for a specific item
    *
    * @param $itemtype   type of the item
    * @param $ID         ID of the item
    *
    * @return true if succeed else false
   **/
   function getFromDBbyItem($itemtype, $ID) {

      return $this->getFromDBByQuery("WHERE `".$this->getTable()."`.`itemtype` = '$itemtype'
                                            AND `".$this->getTable()."`.`items_id` = '$ID'");
   }


   function cleanDBonPurge() {

      $class = new Reservation();
      $class->cleanDBonItemDelete($this->getType(), $this->fields['id']);

      $class = new Alert();
      $class->cleanDBonItemDelete($this->getType(), $this->fields['id']);

   }


   /**
    * @see CommonDBTM::prepareInputForAdd()
   **/
   function prepareInputForAdd($input) {

      // TODO : I don't understand ! If the reservationitem is not found, then is_active == 1 ?
      // We should define $mustBeAttached to false, otherwise, any further update will fail
      // if the ReservationItem is not attached (ie: getFromDBByItem(...) == false)

      if (!$this->getFromDBbyItem($input['itemtype'], $input['items_id'])) {
         if (!isset($input['is_active'])) {
            $input['is_active'] = 1;
         }
         return $input;
      }
      return false;
   }


   function getSearchOptions() {

      $tab                       = array();

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'comment';
      $tab[4]['name']            = __('Comments');
      $tab[4]['datatype']        = 'text';

      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'is_active';
      $tab[5]['name']            = __('Active');
      $tab[5]['datatype']        = 'bool';

      $tab['common']             = __('Characteristics');

      $tab[1]['table']           = 'reservation_types';
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['massiveaction']   = false;
      $tab[1]['addobjectparams'] = array('forcetab' => 'Reservation$1');

      $tab[2]['table']           = 'reservation_types';
      $tab[2]['field']           = 'id';
      $tab[2]['name']            = __('ID');
      $tab[2]['massiveaction']   = false;
      $tab[2]['datatype']        = 'number';

      $loc = Location::getSearchOptionsToAdd();
      // Force massive actions to false
      foreach ($loc as $key => $val) {
         $tab[$key]                  = $val;
         $tab[$key]['massiveaction'] = false;
      }

      $tab[16]['table']          = 'reservation_types';
      $tab[16]['field']          = 'comment';
      $tab[16]['name']           = __('Comments');
      $tab[16]['datatype']       = 'text';
      $tab[16]['massiveaction']  = false;

      $tab[70]['table']          = 'glpi_users';
      $tab[70]['field']          = 'name';
      $tab[70]['name']           = __('User');
      $tab[70]['datatype']       = 'dropdown';
      $tab[70]['right']          = 'all';
      $tab[70]['massiveaction']  = false;

      $tab[71]['table']          = 'glpi_groups';
      $tab[71]['field']          = 'completename';
      $tab[71]['name']           = __('Group');
      $tab[71]['datatype']       = 'dropdown';
      $tab[71]['massiveaction']  = false;

      $tab[19]['table']          = 'reservation_types';
      $tab[19]['field']          = 'date_mod';
      $tab[19]['name']           = __('Last update');
      $tab[19]['datatype']       = 'datetime';
      $tab[19]['massiveaction']  = false;

      $tab[23]['table']          = 'glpi_manufacturers';
      $tab[23]['field']          = 'name';
      $tab[23]['name']           = __('Manufacturer');
      $tab[23]['datatype']       = 'dropdown';
      $tab[23]['massiveaction']  = false;

      $tab[24]['table']          = 'glpi_users';
      $tab[24]['field']          = 'name';
      $tab[24]['linkfield']      = 'users_id_tech';
      $tab[24]['name']           = __('Technician in charge of the hardware');
      $tab[24]['datatype']       = 'dropdown';
      $tab[24]['right']          = 'interface';
      $tab[24]['massiveaction']  = false;

      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['massiveaction']  = false;
      $tab[80]['datatype']       = 'dropdown';
      $tab[80]['massiveaction']  = false;

      return $tab;
   }


   /**
    * @param $item   CommonDBTM object
   **/
   static function showActivationFormForItem(CommonDBTM $item) {

      if (!Session::haveRight("reservation_central","w")) {
         return false;
      }
      if ($item->getID()) {
         // Recursive type case => need entity right
         if ($item->isRecursive()) {
            if (!Session::haveAccessToEntity($item->fields["entities_id"])) {
               return false;
            }
         }
      } else {
         return false;
      }

      $ri = new self();

      echo "<div>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__('Reserve an item')."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      if ($ri->getFromDBbyItem($item->getType(),$item->getID())) {
         echo "<td class='center'>";
         //Switch reservation state

         if ($ri->fields["is_active"]) {
            Html::showSimpleForm(static::getFormURL(), 'update', __('Make unavailable'),
                                 array('id'        => $ri->fields['id'],
                                       'is_active' => 0));
         } else {
            Html::showSimpleForm(static::getFormURL(), 'update', __('Make available'),
                                 array('id'        => $ri->fields['id'],
                                       'is_active' => 1));
         }

         echo '</td><td>';
         Html::showSimpleForm(static::getFormURL(), 'purge', __('Prohibit reservations'),
                              array('id' => $ri->fields['id']),'','',
                              array(__('Are you sure you want to return this non-reservable item?'),
                                    __('That will remove all the reservations in progress.')));

         echo "</td>";
      } else {
         echo "<td class='center'>";
               Html::showSimpleForm(static::getFormURL(), 'add', __('Authorize reservations'),
                                    array('items_id'     => $item->getID(),
                                          'itemtype'     => $item->getType(),
                                          'entities_id'  => $item->getEntityID(),
                                          'is_recursive' => $item->isRecursive(),));
         echo "</td>";
      }
      echo "</tr></table>";
      echo "</div>";
   }


   function showForm($ID, $options=array()) {

      if (!Session::haveRight("reservation_central","w")) {
         return false;
      }

      $r = new self();

      if ($r->getFromDB($ID)) {
         $type = $r->fields["itemtype"];
         $name = NOT_AVAILABLE;
         if ($item = getItemForItemtype($r->fields["itemtype"])) {
            $type = $item->getTypeName();
            if ($item->getFromDB($r->fields["items_id"])) {
               $name = $item->getName();
            }
         }

         echo "<div class='center'><form method='post' name=form action='".$this->getFormURL()."'>";
         echo "<input type='hidden' name='id' value='$ID'>";
         echo "<table class='tab_cadre'>";
         echo "<tr><th colspan='2'>".__s('Modify the comment')."</th></tr>";

         // Ajouter le nom du materiel
         echo "<tr class='tab_bg_1'><td>".__('Item')."</td>";
         echo "<td class='b'>".sprintf(__('%1$s - %2$s'), $type, $name)."</td></tr>\n";

         echo "<tr class='tab_bg_1'><td>".__('Comments')."</td>";
         echo "<td><textarea name='comment' cols='30' rows='10' >".$r->fields["comment"];
         echo "</textarea></td></tr>\n";

         echo "<tr class='tab_bg_2'><td colspan='2' class='top center'>";
         echo "<input type='submit' name='update' value=\""._sx('button','Save')."\" class='submit'>";
         echo "</td></tr>\n";

         echo "</table>";
         Html::closeForm();
         echo "</div>";
         return true;

      }
      return false;
   }


   static function showListSimple() {
      global $DB, $CFG_GLPI;

      if (!Session::haveRight("reservation_helpdesk","1")) {
         return false;
      }

      $ri         = new self();
      $ok         = false;
      $showentity = Session::isMultiEntitiesMode();

      // GET method passed to form creation
      echo "<div class='center'><form name='form' method='GET' action='reservation.form.php'>";
      echo "<table class='tab_cadre'>";
      echo "<tr><th colspan='".($showentity?"5":"4")."'>".self::getTypeName(1)."</th></tr>\n";

      foreach ($CFG_GLPI["reservation_types"] as $itemtype) {
         if (!($item = getItemForItemtype($itemtype))) {
            continue;
         }
         $itemtable = getTableForItemType($itemtype);
         $query = "SELECT `glpi_reservationitems`.`id`,
                          `glpi_reservationitems`.`comment`,
                          `$itemtable`.`name` AS name,
                          `$itemtable`.`entities_id` AS entities_id,
                          `glpi_locations`.`completename` AS location,
                          `glpi_reservationitems`.`items_id` AS items_id
                   FROM `glpi_reservationitems`
                   INNER JOIN `$itemtable`
                        ON (`glpi_reservationitems`.`itemtype` = '$itemtype'
                            AND `glpi_reservationitems`.`items_id` = `$itemtable`.`id`)
                   LEFT JOIN `glpi_locations`
                        ON (`$itemtable`.`locations_id` = `glpi_locations`.`id`)
                   WHERE `glpi_reservationitems`.`is_active` = '1'
                         AND `glpi_reservationitems`.`is_deleted` = '0'
                         AND `$itemtable`.`is_deleted` = '0'".
                         getEntitiesRestrictRequest(" AND", $itemtable, '',
                                                    $_SESSION['glpiactiveentities'],
                                                    $item->maybeRecursive())."
                   ORDER BY `$itemtable`.`entities_id`,
                            `$itemtable`.`name`";

         if ($result = $DB->query($query)) {
            while ($row = $DB->fetch_assoc($result)) {
               echo "<tr class='tab_bg_2'><td>";
               echo "<input type='checkbox' name='item[".$row["id"]."]' value='".$row["id"]."'>".
                    "</td>";
               $typename = $item->getTypeName();
               if ($itemtype == 'Peripheral') {
                  $item->getFromDB($row['items_id']);
                  if (isset($item->fields["peripheraltypes_id"])
                      && ($item->fields["peripheraltypes_id"] != 0)) {

                     $typename = Dropdown::getDropdownName("glpi_peripheraltypes",
                                                           $item->fields["peripheraltypes_id"]);
                  }
               }
               echo "<td><a href='reservation.php?reservationitems_id=".$row['id']."'>".
                          sprintf(__('%1$s - %2$s'), $typename, $row["name"])."</a></td>";
               echo "<td>".$row["location"]."</td>";
               echo "<td>".nl2br($row["comment"])."</td>";
               if ($showentity) {
                  echo "<td>".Dropdown::getDropdownName("glpi_entities", $row["entities_id"]).
                       "</td>";
               }
               echo "</tr>\n";
               $ok = true;
            }
         }
      }
      if ($ok) {
         echo "<tr class='tab_bg_1 center'><td colspan='".($showentity?"5":"4")."'>";
         echo "<input type='submit' value=\""._sx('button','Add')."\" class='submit'></td></tr>\n";
      }
      echo "</table>\n";
      echo "<input type='hidden' name='id' value=''>";
      echo "</form>";// No CSRF token needed
      echo "</div>\n";
   }


   /**
    * @param $name
    *
    * @return an array
   **/
   static function cronInfo($name) {
      return array('description' => __('Alerts on reservations'));
   }


   /**
    * Cron action on reservation : alert on end of reservations
    *
    * @param $task to log, if NULL use display (default NULL)
    *
    * @return 0 : nothing to do 1 : done with success
   **/
   static function cronReservation($task=NULL) {
      global $DB, $CFG_GLPI;

      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message        = array();
      $cron_status    = 0;
      $items_infos    = array();
      $items_messages = array();

      foreach (Entity::getEntitiesToNotify('use_reservations_alert') as $entity => $value) {
         $secs = $value * HOUR_TIMESTAMP;

         // Reservation already begin and reservation ended in $value hours
         $query_end = "SELECT `glpi_reservationitems`.*,
                              `glpi_reservations`.`end` AS `end`,
                              `glpi_reservations`.`id` AS `resaid`
                       FROM `glpi_reservations`
                       LEFT JOIN `glpi_alerts`
                           ON (`glpi_reservations`.`id` = `glpi_alerts`.`items_id`
                               AND `glpi_alerts`.`itemtype` = 'Reservation'
                               AND `glpi_alerts`.`type` = '".Alert::END."')
                       LEFT JOIN `glpi_reservationitems`
                           ON (`glpi_reservations`.`reservationitems_id`
                                 = `glpi_reservationitems`.`id`)
                       WHERE `glpi_reservationitems`.`entities_id` = '$entity'
                             AND (UNIX_TIMESTAMP(`glpi_reservations`.`end`) - $secs) < UNIX_TIMESTAMP()
                             AND `glpi_reservations`.`begin` < NOW()
                             AND `glpi_alerts`.`date` IS NULL";

         foreach ($DB->request($query_end) as $data) {
            if ($item_resa = getItemForItemtype($data['itemtype'])) {
               if ($item_resa->getFromDB($data["items_id"])) {
                  $data['item_name']                     = $item_resa->getName();
                  $data['entity']                        = $entity;
                  $items_infos[$entity][$data['resaid']] = $data;

                  if (!isset($items_messages[$entity])) {
                     $items_messages[$entity] = __('Device reservations expiring today')."<br>";
                  }
                  $items_messages[$entity] .= sprintf(__('%1$s - %2$s'), $item_resa->getTypeName(),
                                                      $item_resa->getName())."<br>";
               }
            }
         }
      }

      foreach ($items_infos as $entity => $items) {
         $resitem = new self();
         if (NotificationEvent::raiseEvent("alert", new Reservation(),
                                           array('entities_id' => $entity,
                                                 'items'       => $items))) {
            $message     = $items_messages[$entity];
            $cron_status = 1;
            if ($task) {
               $task->addVolume(1);
               $task->log(sprintf(__('%1$s: %2$s')."\n",
                                  Dropdown::getDropdownName("glpi_entities", $entity),
                                  $message));
            } else {
               //TRANS: %1$s is a name, %2$s is text of message
               Session::addMessageAfterRedirect(sprintf(__('%1$s: %2$s'),
                                                        Dropdown::getDropdownName("glpi_entities",
                                                                                  $entity),
                                                        $message));
            }

            $alert             = new Alert();
            $input["itemtype"] = 'Reservation';
            $input["type"]     = Alert::END;
            foreach ($items as $resaid => $item) {
               $input["items_id"] = $resaid;
               $alert->add($input);
               unset($alert->fields['id']);
            }

         } else {
            $entityname = Dropdown::getDropdownName('glpi_entities', $entity);
            //TRANS: %s is entity name
            $msg = sprintf(__('%1$s: %2$s'), $entityname, __('Send reservation alert failed'));
            if ($task) {
               $task->log($msg);
            } else {
               Session::addMessageAfterRedirect($msg, false, ERROR);
            }
         }
      }
      return $cron_status;
   }


   /**
    * Display debug information for reservation of current object
   **/
   function showDebugResa() {

      $resa                                = new Reservation();
      $resa->fields['id']                  = '1';
      $resa->fields['reservationitems_id'] = $this->getField('id');
      $resa->fields['begin']               = $_SESSION['glpi_currenttime'];
      $resa->fields['end']                 = $_SESSION['glpi_currenttime'];
      $resa->fields['users_id']            = Session::getLoginUserID();
      $resa->fields['comment']             = '';

      NotificationEvent::debugEvent($resa);
   }

}
?>