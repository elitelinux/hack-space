<?php
/*
 * @version $Id: networkequipment.class.php 20705 2013-04-10 18:11:01Z yllen $
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

// CLASSES Networking

class NetworkEquipment extends CommonDBTM {

   // From CommonDBTM
   public $dohistory                   = true;
   static protected $forward_entity_to = array('Infocom', 'NetworkPort', 'ReservationItem');


   /**
    * Name of the type
    *
    * @param $nb  integer  number of item in the type (default 0)
   **/
   static function getTypeName($nb=0) {
      return _n('Network device', 'Network devices', $nb);
   }


   static function canCreate() {
      return Session::haveRight('networking', 'w');
   }


   static function canView() {
      return Session::haveRight('networking', 'r');
   }


   /**
    * @since version 0.84
    *
    * @see CommonDBTM::cleanDBonPurge()
   **/
   function cleanDBonPurge() {

      $ip = new Item_Problem();
      $ip->cleanDBonItemDelete(__CLASS__, $this->fields['id']);
   }

   /**
    * @see CommonDBTM::useDeletedToLockIfDynamic()
    *
    * @since version 0.84
   **/
   function useDeletedToLockIfDynamic() {
      return false;
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab('NetworkPort', $ong, $options);
      $this->addStandardTab('NetworkName', $ong, $options);
      $this->addStandardTab('Infocom', $ong, $options);
      $this->addStandardTab('Contract_Item', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Link', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      $this->addStandardTab('Reservation', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }


   function prepareInputForAdd($input) {

      if (isset($input["id"]) && ($input["id"] > 0)) {
         $input["_oldID"] = $input["id"];
      }
      unset($input['id']);
      unset($input['withtemplate']);

      return $input;
   }


   function post_addItem() {
      global $DB;

      // Manage add from template
      if (isset($this->input["_oldID"])) {
         // ADD Infocoms
         Infocom::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);

         // ADD Ports
         NetworkPort::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);

         // ADD Contract
         Contract_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);

         // ADD Documents
         Document_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);

      }
   }


   /**
    * Can I change recursive flag to false
    * check if there is "linked" object in another entity
    *
    * Overloaded from CommonDBTM
    *
    * @return booleen
   **/
   function canUnrecurs() {
      global $DB;

      $ID = $this->fields['id'];
      if (($ID < 0)
          || !$this->fields['is_recursive']) {
         return true;
      }
      if (!parent::canUnrecurs()) {
         return false;
      }
      $entities = "(".$this->fields['entities_id'];
      foreach (getAncestorsOf("glpi_entities", $this->fields['entities_id']) as $papa) {
         $entities .= ",$papa";
      }
      $entities .= ")";

      // RELATION : networking -> _port -> _wire -> _port -> device

      // Evaluate connection in the 2 ways
      for ($tabend=array("networkports_id_1" => "networkports_id_2",
                         "networkports_id_2" => "networkports_id_1") ; list($enda,$endb)=each($tabend) ; ) {

         $sql = "SELECT `itemtype`,
                        GROUP_CONCAT(DISTINCT `items_id`) AS ids
                 FROM `glpi_networkports_networkports`,
                      `glpi_networkports`
                 WHERE `glpi_networkports_networkports`.`$endb` = `glpi_networkports`.`id`
                       AND `glpi_networkports_networkports`.`$enda`
                                 IN (SELECT `id`
                                     FROM `glpi_networkports`
                                     WHERE `itemtype` = '".$this->getType()."'
                                           AND `items_id` = '$ID')
                 GROUP BY `itemtype`";

         $res = $DB->query($sql);
         if ($res) {
            while ($data = $DB->fetch_assoc($res)) {
               $itemtable = getTableForItemType($data["itemtype"]);
               if ($item = getItemForItemtype($data["itemtype"])) {
                   // For each itemtype which are entity dependant
                   if ($item->isEntityAssign()) {
                      if (countElementsInTable($itemtable, "id IN (".$data["ids"].")
                                               AND entities_id NOT IN $entities") > 0) {
                         return false;
                      }
                   }
               }
            }
         }
      }
      return true;
   }


   /**
    * Print the networking form
    *
    * @param $ID        integer ID of the item
    * @param $options   array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    *@return boolean item found
   **/
   function showForm($ID, $options=array()) {

      $this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      //TRANS: %1$s is a string, %2$s a second one without spaces between them : to change for RTL
      echo "<td>".sprintf(__('%1$s%2$s'),__('Name'),
                          (isset($options['withtemplate']) && $options['withtemplate']?"*":"")).
           "</td>";
      echo "<td>";
      $objectName = autoName($this->fields["name"], "name",
                             (isset($options['withtemplate']) && ($options['withtemplate'] == 2)),
                             $this->getType(), $this->fields["entities_id"]);
      Html::autocompletionTextField($this, "name", array('value' => $objectName));
      echo "</td>";
      echo "<td>".__('Status')."</td>";
      echo "<td>";
      State::dropdown(array('value' => $this->fields["states_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Location')."</td>";
      echo "<td>";
      Location::dropdown(array('value'  => $this->fields["locations_id"],
                              'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "<td>".__('Type')."</td>";
      echo "<td>";
      NetworkEquipmentType::dropdown(array('value' => $this->fields["networkequipmenttypes_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Technician in charge of the hardware')."</td>";
      echo "<td>";
      User::dropdown(array('name'   => 'users_id_tech',
                           'value'  => $this->fields["users_id_tech"],
                           'right'  => 'own_ticket',
                           'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "<td>".__('Manufacturer')."</td>";
      echo "<td>";
      Manufacturer::dropdown(array('value' => $this->fields["manufacturers_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group in charge of the hardware')."</td>";
      echo "<td>";
      Group::dropdown(array('name'      => 'groups_id_tech',
                                    'value'     => $this->fields['groups_id_tech'],
                                    'entity'    => $this->fields['entities_id'],
                                    'condition' => '`is_assign`'));
      echo "</td>";
      echo "<td>".__('Model')."</td>";
      echo "<td>";
      NetworkEquipmentModel::dropdown(array('value' => $this->fields["networkequipmentmodels_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Alternate username number')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "contact_num");
      echo "</td>";
      echo "<td>".__('Serial number')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "serial");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Alternate username')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "contact");
      echo "</td>";
      echo "<td>".sprintf(__('%1$s%2$s'), __('Inventory number'),
                          (isset($options['withtemplate']) && $options['withtemplate']?"*":"")).
           "</td>";
      echo "<td>";
      $objectName = autoName($this->fields["otherserial"], "otherserial",
                             (isset($options['withtemplate']) && ($options['withtemplate'] == 2)),
                             $this->getType(), $this->fields["entities_id"]);
      Html::autocompletionTextField($this, "otherserial", array('value' => $objectName));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('User')."</td>";
      echo "<td>";
      User::dropdown(array('value'  => $this->fields["users_id"],
                           'entity' => $this->fields["entities_id"],
                           'right'  => 'all'));
      echo "</td>";
      echo "<td>".__('Network')."</td>";
      echo "<td>";
      Network::dropdown(array('value' => $this->fields["networks_id"]));
      echo "</td></tr>";

      // Display auto inventory informations
      $rowspan        = 6;
      $inventory_show = false;

       if (!empty($ID)
           && $this->fields["is_dynamic"]) {
          $inventory_show = true;
          $rowspan       -= 3;
       }

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group')."</td>";
      echo "<td>";
      Group::dropdown(array('value'     => $this->fields["groups_id"],
                            'entity'    => $this->fields["entities_id"],
                            'condition' => '`is_itemgroup`'));
      echo "</td>";
      echo "<td rowspan='$rowspan'>".__('Comments')."</td>";
      echo "<td rowspan='$rowspan'>
            <textarea cols='45' rows='".($rowspan+3)."' name='comment' >".$this->fields["comment"];
      echo "</textarea></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Domain')."</td>";
      echo "<td>";
      Domain::dropdown(array('value' => $this->fields["domains_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan=2>".__('The MAC address and the IP of the equipment are included in an aggregated network port')."</td>";
      echo "</tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>"._n('Firmware', 'Firmwares', 1)."</td>";
      echo "<td>";
      NetworkEquipmentFirmware::dropdown(array('value' => $this->fields["networkequipmentfirmwares_id"]));
      echo "</td>";
      if ($inventory_show) {
         echo "<td rowspan='3'>";
         _e('Automatic inventory');
         echo "</td>";
         echo "<td rowspan='3'>";
         Plugin::doHook("autoinventory_information", $this);
         echo "</td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".sprintf(__('%1$s (%2$s)'),__('Memory'),__('Mio'))."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "ram");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      if ((!isset($options['withtemplate']) || ($options['withtemplate'] == 0))
          && !empty($this->fields['template_name'])) {
         echo "<span class='small_space'>";
         printf(__('Created from the template %s'), $this->fields['template_name']);
         echo "</span>";
      } else {
         echo "&nbsp;";
      }
      echo "</td><td>";
      if (isset($options['withtemplate']) && $options['withtemplate']) {
         //TRANS: %s is the datetime of insertion
         printf(__('Created on %s'), Html::convDateTime($_SESSION["glpi_currenttime"]));
      } else {
         //TRANS: %s is the datetime of insertion
         printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      }
      echo "</td></tr>\n";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }


   /**
    * @see CommonDBTM::getSpecificMassiveActions()
   **/
   function getSpecificMassiveActions($checkitem=NULL) {

      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if (Session::haveRight('transfer','r')
          && Session::isMultiEntitiesMode()
          && $isadmin) {
         $actions['add_transfer_list'] = _x('button', 'Add to transfer list');
      }
      return $actions;
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
      $tab[2]['field']           = 'id';
      $tab[2]['name']            = __('ID');
      $tab[2]['massiveaction']   = false;
      $tab[2]['datatype']        = 'number';

      $tab += Location::getSearchOptionsToAdd();

      $tab[4]['table']           = 'glpi_networkequipmenttypes';
      $tab[4]['field']           = 'name';
      $tab[4]['name']            = __('Type');
      $tab[4]['datatype']        = 'dropdown';

      $tab[40]['table']          = 'glpi_networkequipmentmodels';
      $tab[40]['field']          = 'name';
      $tab[40]['name']           = __('Model');
      $tab[40]['datatype']       = 'dropdown';

      $tab[31]['table']          = 'glpi_states';
      $tab[31]['field']          = 'completename';
      $tab[31]['name']           = __('Status');
      $tab[31]['datatype']       = 'dropdown';

      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'serial';
      $tab[5]['name']            = __('Serial number');
      $tab[5]['datatype']        = 'string';

      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'otherserial';
      $tab[6]['name']            = __('Inventory number');
      $tab[6]['datatype']        = 'string';

      $tab[7]['table']           = $this->getTable();
      $tab[7]['field']           = 'contact';
      $tab[7]['name']            = __('Alternate username');
      $tab[7]['datatype']        = 'string';

      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'contact_num';
      $tab[8]['name']            = __('Alternate username number');
      $tab[8]['datatype']        = 'string';

      $tab[70]['table']          = 'glpi_users';
      $tab[70]['field']          = 'name';
      $tab[70]['name']           = __('User');
      $tab[70]['datatype']       = 'dropdown';
      $tab[70]['right']          = 'all';

      $tab[71]['table']          = 'glpi_groups';
      $tab[71]['field']          = 'completename';
      $tab[71]['name']           = __('Group');
      $tab[71]['datatype']       = 'dropdown';
      $tab[71]['condition']      = '`is_itemgroup`';

      $tab[19]['table']          = $this->getTable();
      $tab[19]['field']          = 'date_mod';
      $tab[19]['name']           = __('Last update');
      $tab[19]['datatype']       = 'datetime';
      $tab[19]['massiveaction']  = false;

      $tab[16]['table']          = $this->getTable();
      $tab[16]['field']          = 'comment';
      $tab[16]['name']           = __('Comments');
      $tab[16]['datatype']       = 'text';

      $tab[90]['table']          = $this->getTable();
      $tab[90]['field']          = 'notepad';
      $tab[90]['name']           = __('Notes');
      $tab[90]['massiveaction']  = false;
      $tab[90]['datatype']       = 'text';

      $tab[11]['table']          = 'glpi_networkequipmentfirmwares';
      $tab[11]['field']          = 'name';
      $tab[11]['name']           = _n('Firmware', 'Firmwares', 1);
      $tab[11]['datatype']       = 'dropdown';

      $tab[14]['table']          = $this->getTable();
      $tab[14]['field']          = 'ram';
      $tab[14]['name']           = sprintf(__('%1$s (%2$s)'),__('Memory'),__('Mio'));
      $tab[14]['datatype']       = 'number';

      $tab[32]['table']          = 'glpi_networks';
      $tab[32]['field']          = 'name';
      $tab[32]['name']           = __('Network');
      $tab[32]['datatype']       = 'dropdown';

      $tab[33]['table']          = 'glpi_domains';
      $tab[33]['field']          = 'name';
      $tab[33]['name']           = __('Domain');
      $tab[33]['datatype']       = 'dropdown';

      $tab[23]['table']          = 'glpi_manufacturers';
      $tab[23]['field']          = 'name';
      $tab[23]['name']           = __('Manufacturer');
      $tab[23]['datatype']       = 'dropdown';

      $tab[24]['table']          = 'glpi_users';
      $tab[24]['field']          = 'name';
      $tab[24]['linkfield']      = 'users_id_tech';
      $tab[24]['name']           = __('Technician in charge of the hardware');
      $tab[24]['datatype']       = 'dropdown';
      $tab[24]['right']          = 'own_ticket';

      $tab[49]['table']          = 'glpi_groups';
      $tab[49]['field']          = 'completename';
      $tab[49]['linkfield']      = 'groups_id_tech';
      $tab[49]['name']           = __('Group in charge of the hardware');
      $tab[49]['condition']      = '`is_assign`';
      $tab[49]['datatype']       = 'dropdown';

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

}
?>
