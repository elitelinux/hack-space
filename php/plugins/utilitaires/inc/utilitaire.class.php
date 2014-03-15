<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Utilitaires plugin for GLPI
 Copyright (C) 2003-2011 by the Utilitaires Development Team.

 https://forge.indepnet.net/projects/utilitaires
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Utilitaires.

 Utilitaires is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Utilitaires is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with utilitaires. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginUtilitairesUtilitaire extends CommonDBTM {
   
   const PURGE_ACTION            = 1;
   const DELETE_UNLINKED_ACTION  = 2;
   const DELETE_ACTION           = 3;
   const CREATE_MODEL_ACTION     = 4;
   const LINK_MODEL_ACTION       = 5;
   const RESET_OCS_ACTION        = 6;
   const IMPORT_PRINTERS_ACTION  = 7;
   const RESET_OCS_LOCK          = 8;
   const TRUNCATE_TABLE          = 9;
   const SYNC_OCS_ACTION         = 10;
   const DELETE_DELAY            = 11;
   
   const TEST        = 0;
   const TODO        = 1;
   const TRUNCATE    = 2;
   const LINK        = 3;
   
   
   
   static $itemtypes = array('Computer','Monitor', 'Software','NetworkEquipment','Peripheral',
         'Printer','Phone', 'Cartridge', 'Consumable');

   static $components = array('ComputerDisk','ComputerVirtualMachine', 'NetworkPort', 'RegistryKey', 'Computer_SoftwareVersion', 'Computer_SoftwareLicense');

   static $helpdesk = array('Ticket', 'Problem');
   
   static $management = array('Budget', 'Supplier', 'Contact', 'Contract', 'Document');
   
   static $tools = array('KnowbaseItem');
   
   static function getTypeName($nb=0) {

      return _n('Utility', 'Utilities', $nb, 'utilitaires');
   }
   
   static function canCreate() {
      return plugin_utilitaires_haveRight('utilitaires', 'w');
   }

   static function canView() {
      return plugin_utilitaires_haveRight('utilitaires', 'w');
   }
   
   static function MenuDisplay() {
      global $CFG_GLPI;
      
      echo "<form method='post' action='".$_SERVER["PHP_SELF"]."'>";
      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr><th colspan='2'>".self::getTypeName(2)."</th></tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<th class='center'>";
      _e('Entity');
      echo "</th>";
      echo "<th class='left'>";
      $options = array('toadd' => array(-1 => __('All'),
                        'name' => "entities_id"));
      Dropdown::show('Entity', $options);
      echo "</th>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'>";
      _e('Inventory objects', 'utilitaires');
      echo "</td>";
      echo "<td class='left'>";
      self::dropdownTypes("objectItemType",self::getTypes(self::$itemtypes, true));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'>";
      _e('Computer details', 'utilitaires');
      echo "</td>";
      echo "<td class='left'>";
      $comps = array();
      $devices = Item_Devices::getDeviceTypes();
      foreach ($devices as $k => $device) {
         $comps[] = $device;
      }
      foreach (self::$components as $k => $component) {
         $comps[] = $component;
      }

      self::dropdownTypes("objectComponentType",self::getTypes($comps, true));
      echo "</td>";
      echo "</tr>";

      
      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'>";
      _e('Helpdesk', 'utilitaires');
      echo "</td>";
      echo "<td class='left'>";
      self::dropdownTypes("objectHelpdeskType",self::getTypes(self::$helpdesk, true));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'>";
      _e('Management', 'utilitaires');
      echo "</td>";
      echo "<td class='left'>";
      self::dropdownTypes("objectManagemenType",self::getTypes(self::$management, true));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'>";
      _e('Tools', 'utilitaires');
      echo "</td>";
      echo "<td class='left'>";
      self::dropdownTypes("objectToolType",self::getTypes(self::$tools, true));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr><th colspan='2'>".__('Caution: it is strongly recommended to backup the database before using these utilities', 'utilitaires')."</th></tr>";
      echo "</table></div>";
      Html::closeForm();
	}
	
	static function dropdownTypes($name, $types) {
      global $CFG_GLPI;
      
      $options = array('' => Dropdown::EMPTY_VALUE);

      if (count($types)) {
         foreach ($types as $type) {
            if ($item = getItemForItemtype($type)) {
               
               if($type != "Computer_SoftwareVersion"
                  && $type != "Computer_SoftwareLicense"
                   && !in_array($type,Item_Devices::getDeviceTypes())) {
                  $options[$type] = $item->getTypeName(2);
               } else if ($type == "Computer_SoftwareVersion") {
                  $options[$type] = __('Version')." - ".__('installed softwares', 'utilitaires');
               } else if ($type == "Computer_SoftwareLicense") {
                  $options[$type] = __('License')." - ".__('installed softwares', 'utilitaires');
               } else if (in_array($type,Item_Devices::getDeviceTypes())) {
                  $associated_type  = str_replace('Item_', '', $type);
                  $options[$associated_type] = $associated_type::getTypeName();
               }
            }
         }
      }
      //asort($options);
      $rand = Dropdown::showFromArray($name, $options);
         
      $params = array ('action' => '__VALUE__');
      Ajax::updateItemOnSelectEvent("dropdown_$name$rand", "show_Actions$rand",
                                  $CFG_GLPI["root_doc"] . "/plugins/utilitaires/ajax/showactions.php",
                                  $params);
       echo "<span id='show_Actions$rand'>&nbsp;</span>";
         
   }
   
   static function getActions($type) {
      global $CFG_GLPI;
      
      $actions = array();
      $actions[] = Dropdown::EMPTY_VALUE;

      $item = new $type();
      if($item->maybeDeleted()
            && !in_array($type, Item_Devices::getDeviceTypes())
               && !in_array($type, self::$components)) {
         $actions[self::PURGE_ACTION] = __('Purge trash', 'utilitaires');
      }
      
      $actions[self::DELETE_ACTION] = __('Delete and purge all', 'utilitaires');
      if ($type == 'Computer') {
         $actions[self::TRUNCATE_TABLE] = __('ONLY FOR TEST : Truncate Mysql tables', 'utilitaires');
      }
      if ($type == 'Ticket'
            || $type == 'Problem') {
         $actions[self::DELETE_DELAY] = __('Purge closed tickets/ problems from a selected date', 'utilitaires');
      }
      //TODO
      $plugin = new Plugin();
      if ($plugin->isActivated("ocsinventoryng")
            && $type == 'Computer') {
         $actions[self::RESET_OCS_ACTION] = __('Reset All OCS links', 'utilitaires');
         $actions[self::RESET_OCS_LOCK] = __('Reset OCS locks', 'utilitaires');
         $actions[self::SYNC_OCS_ACTION] = __('Force OCS synchronization', 'utilitaires');
         
      }

      if ($type == 'Printer' 
            || $type == 'Monitor'
               || $type == 'Peripheral'
                  || $type == 'Phone') {
         $actions[self::DELETE_UNLINKED_ACTION] = __('Purge not linked objects', 'utilitaires');
         $actions[self::CREATE_MODEL_ACTION] = __('Create the models from object names', 'utilitaires');
         $actions[self::LINK_MODEL_ACTION] = __('Affect the models', 'utilitaires');
      }
      //if ($CFG_GLPI["use_ocs_mode"] 
      //      && $type == 'Printer') {
      //   $actions[self::IMPORT_PRINTERS_ACTION] = __('Import from OCS', 'utilitaires');
      //}
      if ($type == 'Software') {
         $actions[self::DELETE_UNLINKED_ACTION] = __('Purge not linked objects', 'utilitaires');
      }
      
      return $actions;
   
   }
	
	static function ShowActions($itemtype, $actionId, $entities, $date) {

      $item = new $itemtype();
      echo "<form method='post' action='".$_SERVER["PHP_SELF"]."'>";
      echo "<input type='hidden' name='itemtype' value='$itemtype'>";
      echo "<input type='hidden' name='actionId' value='$actionId'>";
      echo "<input type='hidden' name='entities_id' value='$entities'>";
      echo "<input type='hidden' name='date' value='$date'>";
      echo "<table class='tab_cadre_fixe' align=center cellpadding='5' width='288px'>";
      echo "<tr><th colspan = 2>".$item->getTypeName(2)." : ";
      $actions = self::getActions($itemtype);
      if (!empty($actions)) {
         echo $actions[$actionId];
      }

      echo "</th></tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td align='center'>";
      echo "<input class='submit' type='submit' name='do_action' value='".__s('Execute')."'>";
      echo "</td>";
      echo "<td align='center'>";
      echo "<input class='submit' type='submit' name='abort' value='"._sx('button', 'Cancel')."'>";
      echo "</td>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      Html::closeForm(); 
	}

   
   static function getStructure($itemtype, $actionId, $entities, $date) {

      $infocom = getTableForItemType("Infocom");
      $ticket = getTableForItemType("Ticket");
      $item_problem = getTableForItemType("Item_Problem");
      $contract_item = getTableForItemType("Contract_Item");
      $doc_item = getTableForItemType("Document_Item");
      $resa_item = getTableForItemType("ReservationItem");
      $resa = getTableForItemType("Reservation");
      $log = getTableForItemType("Log");
      $computer_item = getTableForItemType("Computer_Item");
      $networkport = getTableForItemType("NetworkPort");
      $networkport_networkport = getTableForItemType("NetworkPort_NetworkPort");
      $computer_softwareversion = getTableForItemType("Computer_SoftwareVersion");
      $computer_softwarelicense = getTableForItemType("Computer_SoftwareLicense");
      $masterTable = getTableForItemType($itemtype);
      
      $item = new $itemtype();
      
      $masterCondition = "";
      if ($item->maybeTemplate()) {
         $masterCondition .= "AND `$masterTable`.`is_template` = '0'";
      }
      if ($actionId == self::PURGE_ACTION 
            && $item->maybeDeleted()) {
         $masterCondition .= " AND `$masterTable`.`is_deleted` = '1'";
      }
      if ($actionId == self::DELETE_DELAY
            && ($itemtype == 'Ticket'
                  || $itemtype == 'Problem')) {
         $masterCondition .= " AND `$masterTable`.`status` = '".CommonITILObject::CLOSED."'
                              AND (`$masterTable`.`closedate` < '".$date."' 
                              OR `$masterTable`.`closedate` IS NULL)";
      }

      if ($item->isEntityAssign() 
            && $entities != -1) {
         $masterCondition .= getEntitiesRestrictRequest(" AND ",$masterTable,'',$entities,$item->maybeRecursive());
      }
      
      $object = new PluginUtilitairesObject($masterTable, null, 'id', '', $masterCondition);
      
      if($itemtype != "Computer_SoftwareVersion"
         && $itemtype != "Computer_SoftwareLicense"
          && !in_array($itemtype,Item_Devices::getDeviceTypes())) {
         $object->title = $item->getTypeName(2);
      } else if ($itemtype == "Computer_SoftwareVersion") {
         $object->title = __('Version')." - ".__('installed softwares', 'utilitaires');
      } else if ($itemtype == "Computer_SoftwareLicense") {
         $object->title = __('License')." - ".__('installed softwares', 'utilitaires');
      } else if (in_array($itemtype,Item_Devices::getDeviceTypes())) {
         $associated_type  = str_replace('Item_', '', $itemtype);
         $object->title = $associated_type::getTypeName();
      }

      if (!in_array('Item_'.$itemtype, Item_Devices::getDeviceTypes())
            && !in_array($itemtype, self::$components)) {
         //cleanRelationTable
         $obj1 = new PluginUtilitairesObject($infocom, null, 'id', 'items_id', "`$infocom`.`itemtype` = '".$itemtype."'", $object);
         //cleanRelationData
         if ($itemtype != "Ticket") {
            $obj1 = new PluginUtilitairesObject($ticket, null, 'id', 'items_id', "`$ticket`.`itemtype` = '".$itemtype."'", $object);
         }
         //??
         $obj1 = new PluginUtilitairesObject($item_problem, null, 'id', 'items_id', "`$item_problem`.`itemtype` = '".$itemtype."'", $object);
         
         //cleanRelationTable
         $obj1 = new PluginUtilitairesObject($contract_item, null, 'id', 'items_id', "`$contract_item`.`itemtype` = '".$itemtype."'", $object);
         //cleanRelationTable
         $obj1 = new PluginUtilitairesObject($doc_item, null, 'id', 'items_id', "`$doc_item`.`itemtype` = '".$itemtype."'", $object);
         //cleanRelationTable
         $obj1 = new PluginUtilitairesObject($resa_item, null, 'id', 'items_id', "`$resa_item`.`itemtype` = '".$itemtype."'", $object);
         //cleanRelationTable
         $obj2 = new PluginUtilitairesObject($resa, null, 'id', 'reservationitems_id', "", $obj1, 'id');
         //cleanHistory
         $obj1 = new PluginUtilitairesObject($log, null, 'id', 'items_id', "`$log`.`itemtype` = '".$itemtype."'", $object);
      }
      $mycomp = "";
      $devices = Item_Devices::getDeviceTypes();
      foreach ($devices as $k => $device) {
         if($itemtype == $device) {
            $itemtype = "Device";
            $mycomp = $device;
         }
      }
      
      foreach (self::$components as $k => $component) {
         if($itemtype == $component) {
            $itemtype = "Component";
            $mycomp = $component;
         }
      }

      switch ($itemtype) {
         case 'Computer' :
            //cleanDBonPurge
            $components = Item_Devices::getDeviceTypes();
            foreach ($components as $k => $component) {
               $table = getTableForItemType($component);
               $obj1 = new PluginUtilitairesObject($table, null, 'id', 'items_id', "`$table`.`itemtype` = '".$itemtype."'", $object);
            }
            //cleanDBonPurge
            $obj1 = new PluginUtilitairesObject("glpi_computerdisks", null, 'id', 'computers_id', "", $object);
            //cleanDBonPurge
            $obj1 = new PluginUtilitairesObject("glpi_computervirtualmachines", null, 'id', 'computers_id', "", $object);
            
            //cleanDBonPurge
            $obj1 = new PluginUtilitairesObject($computer_item, null, 'id', 'computers_id', "", $object);
            
            //cleanRelationTable
            $obj1 = new PluginUtilitairesObject($networkport, null, 'id', 'items_id', "`$networkport`.`itemtype` = '".$itemtype."'", $object);
            //cleanRelationTable
            $obj2 = new PluginUtilitairesObject($networkport_networkport, null, 'id', array('networkports_id_1', 'networkports_id_2'), "", $obj1,"id");
            
            //cleanDBonPurge
            $obj1 = new PluginUtilitairesObject($computer_softwareversion, null, 'id', 'computers_id', "", $object);
            
            //???
            $obj1 = new PluginUtilitairesObject($computer_softwarelicense, null, 'id', 'computers_id', "", $object);
            
            //cleanDBonPurge
            //go ocsinventoryng ?
            //TODO ?
            //$obj1 = new PluginUtilitairesObject('glpi_ocslinks', null, 'id', 'computers_id', "", $object);
            //cleanDBonPurge
            //$obj1 = new PluginUtilitairesObject("glpi_registrykeys", null, 'id', 'computers_id', "", $object);
            //Plugins = cleanRelationTable
            break;

         case 'Printer' :
         case 'Monitor' :
         case 'Peripheral' :
         case 'Phone' :
         case 'NetworkEquipment' :
            //TODO cartridges ??
            
            //cleanDBonPurge
            if ($actionId == self::DELETE_UNLINKED_ACTION && $itemtype != 'NetworkEquipment')
               $obj1 = new PluginUtilitairesObject($computer_item, 'B', 'id', 'items_id', "`B`.`itemtype` = '".$itemtype."'", $object, null, true, true);
            else
               $obj1 = new PluginUtilitairesObject($computer_item, null, 'id', 'items_id', "`$computer_item`.`itemtype` = '".$itemtype."'", $object);

            if ($itemtype != 'Monitor') {
               //cleanRelationTable
               $obj1 = new PluginUtilitairesObject($networkport, null, 'id', 'items_id', "`$networkport`.`itemtype` = '".$itemtype."'", $object);
               $obj2 = new PluginUtilitairesObject($networkport_networkport, null, 'id', array('networkports_id_1', 'networkports_id_2'), "", $obj1,"id");
            }

            break;
            
         case 'Software' :

            if ($actionId == self::DELETE_UNLINKED_ACTION) {
               $obj1 = new PluginUtilitairesObject('glpi_softwarelicenses', 'B', 'id', 'softwares_id', "", $object, null, true, true);
               $obj2 = new PluginUtilitairesObject('glpi_softwareversions', 'C', 'id', 'softwares_id', "", $object, null, true, true);
            } else {
               //cleanDBonPurge
               $obj1 = new PluginUtilitairesObject('glpi_softwarelicenses', null, 'id', 'softwares_id', "", $object);
               $obj2 = new PluginUtilitairesObject($computer_softwarelicense, null, 'id', 'softwarelicenses_id', "", $obj1);
               //cleanDBonPurge
               $obj3 = new PluginUtilitairesObject('glpi_softwareversions', null, 'id', 'softwares_id', "", $object);
               $obj4 = new PluginUtilitairesObject($computer_softwareversion, null, 'id', 'softwareversions_id', "", $obj3);
            }

            break;
            
         case 'Component' :

            //??
            $table = getTableForItemType($mycomp);
            $obj1 = new PluginUtilitairesObject($table, null, 'id', 'computers_id', "");
            if ($mycomp == 'NetworkPort') {
               $obj2 = new PluginUtilitairesObject($networkport_networkport, null, 'id', array('networkports_id_1', 'networkports_id_2'), "", $obj1,"id");
            }
            break;
            
         case 'Device' :
            //cleanDBonItemDelete
            //$associated_type  = str_replace('Item_', '', $mycomp);
            $table = getTableForItemType($mycomp);
            
            $field = getForeignKeyFieldForTable(getTableForItemType($mycomp));
            $obj1 = new PluginUtilitairesObject($table, null, 'id', $field, "", $object);
         
         case 'Supplier' :
            //cleanDBonPurge
            $obj1 = new PluginUtilitairesObject('glpi_contacts_suppliers', null, 'id', 'suppliers_id', "", $object);
            //cleanDBonPurge
            $obj1 = new PluginUtilitairesObject("glpi_contracts_suppliers", null, 'id', 'suppliers_id', "", $object);
            break;
         
         case 'Contact' :
            //cleanDBonPurge
            $obj1 = new PluginUtilitairesObject('glpi_contacts_suppliers', null, 'id', 'contacts_id', "", $object);
            break;
         
         case 'Contract' :
            //cleanDBonPurge
            $obj1 = new PluginUtilitairesObject('glpi_contracts_suppliers', null, 'id', 'contracts_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_contracts_items', null, 'id', 'contracts_id', "", $object);
            break;
         
         case 'Ticket' :
            //cleanDBonPurge
            $obj1 = new PluginUtilitairesObject('glpi_tickettasks', null, 'id', 'tickets_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_ticketfollowups', null, 'id', 'tickets_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_ticketvalidations', null, 'id', 'tickets_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_ticketsatisfactions', null, 'id', 'tickets_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_tickets_tickets', null, 'id', array('tickets_id_1', 'tickets_id_2'), "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_tickets_users', null, 'id', 'tickets_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_groups_tickets', null, 'id', 'tickets_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_suppliers_tickets', null, 'id', 'tickets_id', "", $object);
            break;
         
         case 'Problem' :
            //cleanDBonPurge
            $obj1 = new PluginUtilitairesObject('glpi_problemtasks', null, 'id', 'problems_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_problems_users', null, 'id', 'problems_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_groups_problems', null, 'id', 'problems_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_problems_tickets', null, 'id', 'problems_id', "", $object);
            break;
          
         case 'KnowbaseItem' :
            //cleanDBonPurge
            $obj1 = new PluginUtilitairesObject('glpi_knowbaseitems_profiles', null, 'id', 'knowbaseitems_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_knowbaseitems_users', null, 'id', 'knowbaseitems_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_entities_knowbaseitems', null, 'id', 'knowbaseitems_id', "", $object);
            $obj1 = new PluginUtilitairesObject('glpi_groups_knowbaseitems', null, 'id', 'knowbaseitems_id', "", $object);
            break;
      }
      return $object;
	}
   
   static function countObjectsToProcess($objectId, $actionId, $entities, $date) {
      
      //include_once (GLPI_ROOT."/plugins/utilitaires/inc/plugin_utilitaires.functions.php");
      
      switch ($actionId) {
         case self::CREATE_MODEL_ACTION:
         case self::LINK_MODEL_ACTION:
         case self::RESET_OCS_ACTION:
         case self::RESET_OCS_LOCK:
         case self::SYNC_OCS_ACTION:
            $nb = 1;
            break;
         case self::TRUNCATE_TABLE:
            $object = self::getStructure($objectId, $actionId, $entities, $date);
            $nb = $object->process(self::TRUNCATE, $objectId, $actionId, $entities, $date, true);
            return $nb;
            break;
         //case self::IMPORT_PRINTERS_ACTION:
         //   $nb = glpiuImportPrinters(0, $start);
         //   break;
         default :
            $object = self::getStructure($objectId, $actionId, $entities, $date);
            $nb = $object->process(self::TEST, $objectId, $actionId, $entities, $date);
            break;
      }

      return $nb;
   }


   static function processObjects($objectId, $actionId, $entities, $date) {
      
      //include_once (GLPI_ROOT."/plugins/utilitaires/inc/plugin_utilitaires.functions.php");
      ini_set("max_execution_time", "0");

      switch ($actionId) {

         case self::CREATE_MODEL_ACTION:
            self::addModels($objectId, $entities);
            break;
         case self::LINK_MODEL_ACTION:
            self::linkModels($objectId, $entities);
            break;
         case self::RESET_OCS_ACTION:
            self::resetOCS($objectId, $entities);
            break;
         case self::RESET_OCS_LOCK:
            $res = self::resetOCSLocks($objectId, $entities);
            return $res;
            break;
         case self::SYNC_OCS_ACTION:
            $res = self::syncOCS($objectId, $entities);
            return $res;
            break;
         case self::TRUNCATE_TABLE:
            $object = self::getStructure($objectId, $actionId, $entities);
            $nb = $object->process(self::TRUNCATE, $objectId, $actionId, $entities);
            return $nb;
            break;
         case self::DELETE_UNLINKED_ACTION:
            $object = self::getStructure($objectId, $actionId, $entities);
            $nb = $object->process(self::LINK, $objectId, $actionId, $entities);
            return $nb;
            break;
         //case self::IMPORT_PRINTERS_ACTION:
         //   $nb = glpiuImportPrinters(1, $start);
         //   break;
         default :
            $object = self::getStructure($objectId, $actionId, $entities, $date);
            $res = $object->process(self::TODO, $objectId, $actionId, $entities, $date);
            return $res;
            break;
      }
      return true;
   }
   
   
   static function addModels($objectId, $entities) {
      global $DB;
      
      $object = new $objectId();
      $objectTable = getTableForItemType($objectId);
      $dropdown = getTableForItemType($objectId."Model");

      $query = "INSERT INTO $dropdown (name) "
               ."SELECT DISTINCT `$objectTable`.`name` FROM `$objectTable` "
               ."LEFT OUTER JOIN `$dropdown` "
               ."ON `$dropdown`.`name` = `$objectTable`.`name` "
               ."WHERE `$dropdown`.`id` IS NULL AND `$objectTable`.`is_template` = '0'";
      
      if ($entities != -1)
         $query .= getEntitiesRestrictRequest(" AND ",$objecttable,'',$entities,$object->maybeRecursive());
      
      $result = $DB->query($query) or die($DB->error());
   }

   static function linkModels($objectId, $entities) {
      global $DB;
      
      $object = new $objectId();
      $objectTable = getTableForItemType($objectId);
      $dropdown = getTableForItemType($objectId."Model");
      $modelfield = getForeignKeyFieldForTable(getTableForItemType($objectId."Model"));
      
      $query = "UPDATE `$objectTable` "
            ."INNER JOIN `$dropdown` ON `$dropdown`.`name` = `$objectTable`.`name` "
            ."SET `$objectTable`.`".$modelfield."` = `$dropdown`.`id` WHERE `$objectTable`.`is_template` = '0'";
      
      if ($entities != -1)
         $query .= getEntitiesRestrictRequest(" AND ",$objecttable,'',$entities,$object->maybeRecursive());
      $result = $DB->query($query) or die($DB->error());
   }

   static function resetOCS($objectId, $entities) {
      global $DB;
      
      //TO DO Check entities of server ?
      //$DBocs = new DBocs($_SESSION["ocsservers_id"]);
      //$query = "UPDATE HARDWARE SET CHECKSUM=".OcsServer::MAX_CHECKSUM;
      //$result = $DBocs->query($query) or die($DB->error());

      $query = "DELETE FROM `glpi_plugin_ocsinventoryng_ocslinks`";
      if ($entities != -1)
         $query .= getEntitiesRestrictRequest(" WHERE ","glpi_plugin_ocsinventoryng_ocslinks",'',$entities,false);
      $result = $DB->query($query) or die($DB->error());
   }
   
   static function resetOCSLocks($objectId, $entities) {
      global $DB;
      
      $res = array('ok' => 0,
                    'ko' => 0);
   
      $object = new $objectId();
      $objecttable = getTableForItemType($objectId);
      $restrict = "";
      if ($object->maybeTemplate()) {
         $restrict = "`is_template` = '0'";
      }
      if ($object->isEntityAssign()
            && $entities != -1) {
         $restrict .= getEntitiesRestrictRequest(" AND ",$objecttable,'',$entities,$object->maybeRecursive());
      }
      $count = countElementsInTable($objecttable,$restrict);
      $items = getAllDatasFromTable($objecttable,$restrict);
      
      $computers = array();
      if (!empty($items)) {
         
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><td>";
         Html::createProgressBar(__('Work in progress...'));
         echo "</td></tr></table></div></br>\n";
         $i = 0;
         foreach ($items as $item) {
            
            //Fields
            if (PluginOcsinventoryngOcsServer::deleteInOcsArray($item['id'], 'all', "computer_update",
                                                     true)) {
               $res['ok']++;
            }
            $i++;
            
            $computers[$item['id']]= 1;
            Html::changeProgressBarPosition($i, $count);
         }
         
         foreach (Item_Devices::getDeviceTypes() as $itemtype) {
            $res[] = Lock::unlockItems($itemtype, 'Computer', $computers);
         }
            
         $res[] = Lock::unlockItems('Monitor', 'Monitor', $computers);
         $res[] = Lock::unlockItems('Printer', 'Printer', $computers);
         $res[] = Lock::unlockItems('Peripheral', 'Peripheral', $computers);
         $res[] = Lock::unlockItems('SoftwareVersion', 'SoftwareVersion', $computers);
         $res[] = Lock::unlockItems('NetworkPort', 'Computer', $computers);
         $res[] = Lock::unlockItems('NetworkName', 'NetworkPort', $computers);
         $res[] = Lock::unlockItems('IPAddress', 'NetworkName', $computers);
         $res[] = Lock::unlockItems('ComputerDisk', 'ComputerDisk', $computers);
         $res[] = Lock::unlockItems('ComputerVirtualMachine', 'ComputerVirtualMachine', $computers);
         
         Html::changeProgressBarPosition($i, $count, __('Task completed.'));
      }

      return $res;
   }
   
   
   static function syncOCS($objectId, $entities) {
      global $DB;
      
      $res = array('ok' => 0,
                    'ko' => 0);
      
      $object = new $objectId();
      $objecttable = getTableForItemType($objectId);
      $restrict = "";
      if ($object->maybeTemplate()) {
         $restrict = "`is_template` = '0'";
      }
      if ($object->isEntityAssign() 
         && $entities != -1) {
         $restrict .= getEntitiesRestrictRequest(" AND ",$objecttable,'',$entities,$object->maybeRecursive());
      }
      $count = countElementsInTable($objecttable,$restrict);
      $items = getAllDatasFromTable($objecttable,$restrict);
      
      if (!empty($items)) {
         
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><td>";
         Html::createProgressBar(__('Work in progress...'));
         echo "</td></tr></table></div></br>\n";
         $i = 0;
         foreach ($items as $item) {
            

            //Try to get the OCS server whose machine belongs
            $query = "SELECT `plugin_ocsinventoryng_ocsservers_id`, `id`
                        FROM `glpi_plugin_ocsinventoryng_ocslinks`
                        WHERE `computers_id` = '".$item['id']."'
                           AND `entities_id` = '".$entities."'";
            $result = $DB->query($query);
            if ($DB->numrows($result) == 1) {
               $data = $DB->fetch_assoc($result);
               if ($data['plugin_ocsinventoryng_ocsservers_id'] != -1) {
                  //Force update of the machine
                  if (PluginOcsinventoryngOcsServer::updateComputer($data['id'], $data['plugin_ocsinventoryng_ocsservers_id'], 0, 1)) {
                     $res['ok']++;
                  } else {
                     $res['ko']++;
                  }
               }
            }
            $i++;
            Html::changeProgressBarPosition($i, $count);
         }
         
         Html::changeProgressBarPosition($i, $count, __('Task completed.'));
      }

      return $res;
   }
   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$itemtypes)) {
         self::$itemtypes[] = $type;
      }
   }


   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($mytypes, $all=false) {

      if ($all) {
         return $mytypes;
      }

      // Only allowed types
      $types = $mytypes;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
}

?>