<?php
/*
 * @version $Id: appliancequery.class.php 195 2013-09-05 15:31:47Z yllen $
 -------------------------------------------------------------------------
 Archires plugin for GLPI
 Copyright (C) 2003-2013 by the archires Development Team.

 https://forge.indepnet.net/projects/archires
 -------------------------------------------------------------------------

 LICENSE

 This file is part of archires.

 Archires is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Archires is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Archires. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginArchiresApplianceQuery extends CommonDBTM {

   static function getTypeName($nb=0) {
      return PluginAppliancesAppliance::getTypeName($nb);
   }


   static function canCreate() {
      return plugin_archires_haveRight('archires', 'w');
   }


   static function canView() {
      return plugin_archires_haveRight('archires', 'r');
   }


   function cleanDBonPurge() {

      $querytype = new PluginArchiresQueryType();
      $querytype->deleteByCriteria(array('plugin_archires_queries_id' => $this->fields['id']));
   }


  function getSearchOptions() {

      $tab = array();

      $tab['common']             = self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();

      $tab[2]['table']           = 'glpi_plugin_appliances_appliances';
      $tab[2]['field']           = 'name';
      $tab[2]['name']            = PluginAppliancesAppliance::getTypeName(1);
      $tab[2]['datatype']        = 'dropdown';

      $tab[3]['table']           = 'glpi_networks';
      $tab[3]['field']           = 'name';
      $tab[3]['name']            = _n('Network', 'Networks', 2);
      $tab[3]['datatype']        = 'dropdown';

      $tab[4]['table']           = 'glpi_states';
      $tab[4]['field']           = 'name';
      $tab[4]['name']            = __('State');
      $tab[4]['datatype']        = 'dropdown';

      $tab[5]['table']           = 'glpi_groups';
      $tab[5]['field']           = 'completename';
      $tab[5]['name']            = __('Group');
      $tab[5]['datatype']        = 'dropdown';

      $tab[6]['table']           = 'glpi_vlans';
      $tab[6]['field']           = 'name';
      $tab[6]['name']            = __('VLAN');
      $tab[6]['datatype']        = 'dropdown';

      $tab[7]['table']           = 'glpi_plugin_archires_views';
      $tab[7]['field']           = 'name';
      $tab[7]['name']            = PluginArchiresView::getTypeName(1);
      $tab[7]['datatype']        = 'dropdown';

      $tab[30]['table']          = $this->getTable();
      $tab[30]['field']          = 'id';
      $tab[30]['name']           = __('ID');
      $tab[30]['datatype']       = 'number';

      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['datatype']       = 'dropdown';

   return $tab;
   }


   function prepareInputForAdd($input) {

      if (!isset ($input["plugin_archires_views_id"])
          || $input["plugin_archires_views_id"] == 0) {
         Session::addMessageAfterRedirect(__('Thanks to specify a default used view', 'archires'),
                                          false, ERROR);
         return array ();
      }
      return $input;
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab('PluginArchiresQueryType', $ong, $options);
      $this->addStandardTab('PluginArchiresView', $ong, $options);
      $this->addStandardTab('PluginArchiresPrototype', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      return $ong;
   }


   function showForm ($ID, $options=array()) {

      $this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";
      echo "<td>".__('Group')."</td><td>";
      Group::dropdown(array('name'   => "groups_id",
                            'value'  => $this->fields["groups_id"],
                            'entity' => $this->fields["entities_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".PluginAppliancesAppliance::getTypeName(1)."</td><td>";
      Dropdown::show('PluginAppliancesAppliance',array('name'   => "appliances_id",
                                                'value'  => $this->fields["plugin_appliances_appliances_id"],
                                                'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "<td>".__('VLAN')."</td><td>";
      Vlan::dropdown(array('name'  => "vlans_id",
                           'value' => $this->fields["vlans_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Network')."</td><td>";
      Network::dropdown(array('name'  => "networks_id",
                              'value' => $this->fields["networks_id"]));
      echo "</td>";
      echo "<td>".PluginArchiresView::getTypeName(1)."</td><td>";
      //View
      Dropdown::show('PluginArchiresView',
                     array('name'  => "plugin_archires_views_id",
                           'value' => $this->fields["plugin_archires_views_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('State')."</td><td colspan='3'>";
      State::dropdown(array('name' => "states_id"));
      echo "</td></tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }


   function Query ($ID,$PluginArchiresView,$for) {
      global $DB;

      $this->getFromDB($ID);

      $types   = array();
      $devices = array();
      $ports   = array();

      if ($PluginArchiresView->fields["computer"] != 0) {
         $types[] = 'Computer';
      }
      if ($PluginArchiresView->fields["printer"] != 0) {
         $types[] = 'Printer';
      }
      if ($PluginArchiresView->fields["peripheral"] != 0) {
         $types[] = 'Peripheral';
      }
      if ($PluginArchiresView->fields["phone"] != 0) {
         $types[] = 'Phone';
      }
      if ($PluginArchiresView->fields["networking"] != 0) {
         $types[] = 'NetworkEquipment';
      }
      foreach ($types as $key => $val) {
         $fieldsnp = "`np`.`id`, `np`.`items_id`, `np`.`logical_number`, `np`.`instantiation_type`,
                      `glpi_ipaddresses`.`name` AS ip, `ipn`.`netmask`, `np`.`name` AS namep";

         $itemtable = getTableForItemType($val);
         $query = "SELECT `$itemtable`.`id` AS idc, $fieldsnp , `$itemtable`.`name`,
                          `$itemtable`.`".getForeignKeyFieldForTable(getTableForItemType($val."Type"))."`
                              AS `type`,
                          `$itemtable`.`users_id`, `$itemtable`.`groups_id`, `$itemtable`.`contact`,
                          `$itemtable`.`states_id`, `$itemtable`.`entities_id`,
                          `$itemtable`.`locations_id`
                   FROM `glpi_networkports` np,
                        `$itemtable`,
                        `glpi_ipnetworks` AS ipn
                   LEFT JOIN `glpi_networknames`
                        ON (`glpi_networknames`.`itemtype` = 'NetworkPort'
                            AND `glpi_networkports`.`id` = `glpi_networknames`.`items_id`)
                   LEFT JOIN `glpi_ipaddresses`
                        ON (`glpi_ipaddresses`.`itemtype` = 'NetworkName'
                            AND `glpi_networknames`.`id` = `glpi_ipaddresses`.`items_id`)
                    WHERE `glpi_networkports`.`instantiation_type` = 'NetworkPortEthernet' ";

         if ($this->fields["vlans_id"] > "0") {
            $query .= ", `glpi_networkports_vlans` nv";
         }

         $query .= ", `glpi_plugin_appliances_appliances_items` app
                   WHERE `np`.`itemtype` = '$val'
                         AND `np`.`items_id` = `$itemtable`.`id`
                         AND `app`.`items_id` = `$itemtable`.`id`
                         AND `$itemtable`.`is_deleted` = '0'
                         AND `$itemtable`.`is_template` = '0'".
                         getEntitiesRestrictRequest(" AND",$itemtable);

         if ($this->fields["vlans_id"] > "0") {
            $query .= " AND `nv`.`networkports_id` = `np`.`id`
                        AND `vlans_id` = '".$this->fields["vlans_id"]."'";
         }
         if (($this->fields["networks_id"] > "0")
             && ($val != 'Phone')
             && ($val != 'Peripheral')) {
            $query .= " AND `$itemtable`.`networks_id` = '".$this->fields["networks_id"]."'";
         }
         if ($this->fields["states_id"] > "0") {
            $query .= " AND `$itemtable`.`states_id` = '".$this->fields["states_id"]."'";
         }
         if ($this->fields["groups_id"] > "0") {
            $query .= " AND `$itemtable`.`groups_id` = '".$this->fields["groups_id"]."'";
         }

         $query .= " AND `app`.`plugin_appliances_appliances_id` = '".$this->fields["appliances_id"]."'
                     AND `app`.`itemtype` = '$val' ";

         $PluginArchiresQueryType = new PluginArchiresQueryType();
         $query .= $PluginArchiresQueryType->queryTypeCheck($this->getType(), $ID, $val);

         $query .= "ORDER BY `glpi_ipaddresses`.`name` ASC ";

         if ($result = $DB->query($query)) {
            while ($data = $DB->fetch_array($result)) {

               if ($PluginArchiresView->fields["display_state"] != 0) {
                  $devices[$val][$data["items_id"]]["states_id"] = $data["states_id"];
               }
               $devices[$val][$data["items_id"]]["type"]         = $data["type"];
               $devices[$val][$data["items_id"]]["name"]         = $data["name"];
               $devices[$val][$data["items_id"]]["users_id"]     = $data["users_id"];
               $devices[$val][$data["items_id"]]["groups_id"]    = $data["groups_id"];
               $devices[$val][$data["items_id"]]["contact"]      = $data["contact"];
               $devices[$val][$data["items_id"]]["entity"]       = $data["entities_id"];
               $devices[$val][$data["items_id"]]["locations_id"] = $data["locations_id"];

               if ($data["ip"]) {
                  if (!empty($devices[$val][$data["items_id"]]["ip"])) {
                     $devices[$val][$data["items_id"]]["ip"]  .= " - ";
                     $devices[$val][$data["items_id"]]["ip"]  .= $data["ip"];
                  } else {
                     $devices[$val][$data["items_id"]]["ip"]  = $data["ip"];
                  }
               }

               $ports[$data["id"]]["items_id"]             = $data["items_id"];
               $ports[$data["id"]]["logical_number"]       = $data["logical_number"];
               $ports[$data["id"]]["networkinterfaces_id"] = $data["networkinterfaces_id"];
               $ports[$data["id"]]["ip"]                   = $data["ip"];
               $ports[$data["id"]]["netmask"]              = $data["netmask"];
               $ports[$data["id"]]["namep"]                = $data["namep"];
               $ports[$data["id"]]["idp"]                  = $data["id"];
               $ports[$data["id"]]["itemtype"]             = $val;
            }
         }
      }
      if ($for) {
         return $devices;
      }
      return $ports;
   }

}
?>