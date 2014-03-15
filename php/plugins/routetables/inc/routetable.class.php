<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Routetables plugin for GLPI
  Copyright (C) 2003-2011 by the Routetables Development Team.

  https://forge.indepnet.net/projects/routetables
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Routetables.

  Routetables is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Routetables is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Routetables. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginRoutetablesRoutetable extends CommonDBTM {

   public $dohistory = true;
   static $types = array('Computer', 'NetworkEquipment');

   static function getTypeName($nb=0) {
      return _n('Routing table', 'Routing tables', $nb, 'routetables');
   }

   public static function canCreate() {
      return plugin_routetables_haveRight('routetables', 'w');
   }

   public static function canView() {
      return plugin_routetables_haveRight('routetables', 'r');
   }

   function cleanDBonPurge() {
      $temp = new PluginRoutetablesRoutetable_Item();
      $temp->deleteByCriteria(array('plugin_routetables_routetables_id' => $this->fields['id']));
   }

   function getSearchOptions() {
      $tab = array();

      $tab['common']       = self::getTypeName(2);

      $tab[1]['table']     = $this->getTable();
      $tab[1]['field']     = 'name';
      $tab[1]['name']      = __('Name');
      $tab[1]['datatype']  = 'itemlink';

      $tab[2]['table'] = $this->getTable();
      $tab[2]['field'] = 'destination';
      $tab[2]['name'] =__('Network');

      $tab[3]['table'] = $this->getTable();
      $tab[3]['field'] = 'netmask';
      $tab[3]['name'] = __('Subnet mask');

      $tab[4]['table'] = $this->getTable();
      $tab[4]['field'] = 'gateway';
      $tab[4]['name'] = __('Gateway');

      $tab[5]['table'] = $this->getTable();
      $tab[5]['field'] = 'metric';
      $tab[5]['name'] = __('Metric', 'routetables');

      $tab[6]['table'] = $this->getTable();
      $tab[6]['field'] = 'interface';
      $tab[6]['name'] = __('Interface');

      $tab[7]['table'] = $this->getTable();
      $tab[7]['field'] = 'persistence';
      $tab[7]['name'] = __('Persistance', 'routetables');

      $tab[8]['table'] = $this->getTable();
      $tab[8]['field'] = 'comment';
      $tab[8]['name'] = __('Comments');
      $tab[8]['datatype'] = 'text';

      $tab[9]['table'] = 'glpi_plugin_routetables_routetables_items';
      $tab[9]['field'] = 'items_id';
      $tab[9]['nosearch'] = true;
      $tab[9]['massiveaction'] = false;
      $tab[9]['name'] = __('Associated items');
      $tab[9]['forcegroupby'] = true;
      $tab[9]['joinparams'] = array('jointype' => 'child');

      $tab[10]['table'] = $this->getTable();
      $tab[10]['field'] = 'is_helpdesk_visible';
      $tab[10]['name'] = __('Associable to a ticket');
      $tab[10]['datatype'] = 'bool';

      $tab[11]['table'] = $this->getTable();
      $tab[11]['field'] = 'date_mod';
      $tab[11]['massiveaction'] = false;
      $tab[11]['name'] = __('Last update');
      $tab[11]['datatype'] = 'datetime';

      $tab[30]['table'] = $this->getTable();
      $tab[30]['field'] = 'id';
      $tab[30]['name'] = __('ID');

      $tab[80]['table'] = 'glpi_entities';
      $tab[80]['field'] = 'completename';
      $tab[80]['name'] = __('Entity');

      return $tab;
   }

   //define header form
   function defineTabs($options = array()) {

      $ong = array();
      $this->addStandardTab('PluginRoutetablesRoutetable_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Document_item', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */

   function getSelectLinkedItem() {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_routetables_routetables_items`
              WHERE `plugin_routetables_routetables_id`='" . $this->fields['id'] . "'";
   }

   function showForm($ID, $options = array()) {
      global $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Name') . "</td><td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";

      echo "<td>" . __('Gateway') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "gateway");
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Network') . ":</td><td>";
      Html::autocompletionTextField($this, "destination");
      echo "</td>";

      echo "<td>" . __('Metric','routetables') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "metric");
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Subnet mask') . "</td><td>";
      Html::autocompletionTextField($this, "netmask");
      echo "</td>";

      echo "<td>" . __('Interface') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "interface");
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Associable to a ticket') . "</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible', $this->fields['is_helpdesk_visible']);
      echo "</td>";

      echo "<td>" . __('Persistance','routetables'). "</td>";
      echo "<td>";
      Dropdown::showYesNo("persistence", $this->fields["persistence"]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td colspan = '4'>";
      echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
      echo __('Comments') . ": </td></tr>";
      echo "<tr><td class='center'>";
      echo "<textarea cols='125' rows='3' name='comment'>" . $this->fields["comment"] . "</textarea>";
      $datestring = __('Last update') . ": ";
      $date = Html::convDateTime($this->fields["date_mod"]);
      echo "<br>" . $datestring . $date . "</td></table>";
      echo "</td>";

      echo "</tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }

   function dropdownRouteTables($myname, $entity_restrict = '', $used = array()) {
      global $DB, $CFG_GLPI;

      $rand = mt_rand();

      $where = " WHERE `" . $this->getTable() . "`.`is_deleted` = '0' ";
      $where.=getEntitiesRestrictRequest("AND", $this->getTable(), '', $entity_restrict, false);
      if (count($used)) {
         $where .= " AND id NOT IN (0";
         foreach ($used as $ID)
            $where .= ",$ID";
         $where .= ")";
      }

      $query = "SELECT *
          FROM `" . $this->getTable() . "`
          $where
          GROUP BY `name`
          ORDER BY `name`";
      $result = $DB->query($query);

      echo "<select name='" . $myname . "' id='" . $myname . "'>\n";
      echo "<option value='0'>" . Dropdown::EMPTY_VALUE . "</option>\n";
      while ($data = $DB->fetch_assoc($result)) {
         echo "<option value='" . $data['id'] . "'>" . $data['name'] . " ( " . $data['destination'] . " / " . $data['netmask'] . " / " . $data['gateway'] . ") </option>\n";
      }
      echo "</select>\n";
      return $rand;
   }

   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
    * */
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }

   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    * */
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

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
   
   /**
    * Get the specific massive actions
    * 
    * @since version 0.84
    * @param $checkitem link item to check right   (default NULL)
    * 
    * @return an array of massive actions
    **/
   public function getSpecificMassiveActions($checkitem = NULL) {
      $actions = parent::getSpecificMassiveActions($checkitem);

      $actions['Install'] = __('Associate');
      $actions['Desinstall'] = __('Dissociate');
      $actions['Duplicate'] = __('Duplicate','routetables');
      $actions['Transfert'] = __('Transfer');
            
      return $actions;
   }

   /**
    * Display specific options add action button for massive actions
    *
    * Parameters must not be : itemtype, action, is_deleted, check_itemtype or check_items_id
    * @param $input array of input datas
    * @since version 0.84
    * 
    * @return boolean if parameters displayed ?
    **/
   public function showSpecificMassiveActionsParameters($input = array()) {

      switch ($input['action']) {
         case "Install":
            Dropdown::showAllItems("item_item", 0, 0, -1, self::getTypes(true),false,false,'typeitem');
            echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . __('Post') . "\" >";
            return true;
            break;
         case "Desinstall":
            Dropdown::showAllItems("item_item", 0, 0, -1, self::getTypes(true),false,false,'typeitem');
            echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . __('Post') . "\" >";
            return true;
            break;
         case "Duplicate":
            Dropdown::show('Entity');
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . __('Post') . "\" >";
            return true;
            break;
         case "Transfert":
            Dropdown::show('Entity');
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . __('Post') . "\" >";
            return true;
            break;
         default :
            return parent::showSpecificMassiveActionsParameters($input);
            break;
      }
      return false;
   }

   /**
    * Do the specific massive actions
    *
    * @since version 0.84
    * 
    * @param $input array of input datas
    * 
    * @return an array of results (nbok, nbko, nbnoright counts)
    **/
   public function doSpecificMassiveActions($input = array()) {

      $res = array('ok' => 0,
               'ko' => 0,
               'noright' => 0);

      $route_item = new PluginRoutetablesRoutetable_Item();

      switch ($input['action']) {    
         case 'Duplicate':
            if ($input['itemtype'] == 'PluginRoutetablesRoutetable') {
               foreach ($input['item'] as $key => $val) {
                  if ($val == 1) {
                     if ($this->getFromDB($key)) {
                        unset($this->fields["id"]);
                        $this->fields["entities_id"] = $input["entities_id"];
                       
                        if ($newID = $this->add($this->fields)) {
                           $res['ok']++;
                        } else {
                           $res['ko']++;
                        }
                     } else {
                        $res['ko']++;
                     }
                  }
               }
            }
            break;
         case "Install" :
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  $values = array('plugin_routetables_routetables_id' => $key,
                           'items_id'      => $input["item_item"],
                           'itemtype'      => $input['typeitem']);
                  if ($route_item->can(-1,'w',$input)) {
                     if ($route_item->add($values)) {
                        $res['ok']++;
                     } else {
                        $res['ko']++;
                     }
                  } else {
                     $res['noright']++;
                  }
               }
            }
            break;
         case "Desinstall" :
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  if ($route_item->deleteItemByRoutetablesAndItem($key,$input['item_item'],$input['typeitem'])) {
                     $res['ok']++;
                  } else {
                     $res['ko']++;
                  }
               }
            }
            break;
            
         case "Transfert" :
            if ($input['itemtype'] == 'PluginRoutetablesRoutetable') {
               foreach ($input["item"] as $key => $val) {
                  if ($val == 1) {
                     $values["id"] = $key;
                     $values["entities_id"] = $input['entities_id'];
                     if ($this->update($values)) {
                        $res['ok']++;
                     } else {
                        $res['ko']++;
                     }
                  }
               }
            }
            break;
         default :
            return parent::doSpecificMassiveActions($input);
            break;
      }
      return $res;
   }

}

?>