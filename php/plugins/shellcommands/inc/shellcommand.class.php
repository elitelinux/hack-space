<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Shellcommands plugin for GLPI
  Copyright (C) 2003-2011 by the Shellcommands Development Team.

  https://forge.indepnet.net/projects/shellcommands
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Shellcommands.

  Shellcommands is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Shellcommands is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with shellcommands. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginShellcommandsShellcommand extends CommonDBTM {

   static $types = array('Computer', 'NetworkEquipment', 'Peripheral',
       'Phone', 'Printer');
   public $dohistory = true;

   public static function getTypeName($nb = 0) {
      return _n('Shell Command', 'Shell Commands', $nb, 'shellcommands');
   }

   public static function canCreate() {
      return plugin_shellcommands_haveRight('shellcommands', 'w');
   }

   public static function canView() {
      return plugin_shellcommands_haveRight('shellcommands', 'r');
   }

   function getFromDBbyName($name) {
      global $DB;

      $query = "SELECT * FROM `".$this->gettable()."` ".
              "WHERE (`name` = '".$name."') ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   function cleanDBonPurge() {
      global $DB;

      $temp = new PluginShellcommandsShellcommand_Item();
      $temp->deleteByCriteria(array('plugin_shellcommands_shellcommands_id' => $this->fields['id']));

      $path = new PluginShellcommandsShellcommandPath();
      $path->deleteByCriteria(array('plugin_shellcommands_shellcommands_id' => $this->fields['id']));
   }

   function getSearchOptions() {
      $tab = array();

      $tab['common'] = self::getTypeName(2);

      $tab[1]['table']         = $this->gettable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = __('Name');
      $tab[1]['datatype']      = 'itemlink';

      $tab[2]['table']         = $this->gettable();
      $tab[2]['field']         = 'link';
      $tab[2]['name']          = __('Tag');

      $tab[3]['table']         = 'glpi_plugin_shellcommands_shellcommandpaths';
      $tab[3]['field']         = 'name';
      $tab[3]['linkfield']     = 'plugin_shellcommands_shellcommandpaths_id';
      $tab[3]['name']          = __('Path','shellcommands');
      $tab[3]['datatype']      = 'itemlink';

      $tab[4]['table']         = $this->gettable();
      $tab[4]['field']         = 'parameters';
      $tab[4]['name']          = __('Windows','shellcommands');

      $tab[5]['table']         = 'glpi_plugin_shellcommands_shellcommands_items';
      $tab[5]['field']         = 'itemtype';
      $tab[5]['nosearch']      = true;
      $tab[5]['massiveaction'] = false;
      $tab[5]['name']          = __('Associated hardware types');
      $tab[5]['forcegroupby']  = true;
      $tab[5]['joinparams']    = array('jointype' => 'child');
      $tab[5]['datatype']      = 'dropdown';

      $tab[30]['table']        = $this->gettable();
      $tab[30]['field']        = 'id';
      $tab[30]['name']         = __('ID');
      $tab[30]['datatype']     = 'integer';

      $tab[80]['table']        = 'glpi_entities';
      $tab[80]['field']        = 'completename';
      $tab[80]['name']         = __('Entity');
      $tab[5]['datatype']      = 'dropdown';

      $tab[81]['table']        = $this->gettable();
      $tab[81]['field']        = 'is_recursive';
      $tab[81]['name']         = __('Child entities');
      $tab[81]['datatype']     = 'bool';

      return $tab;
   }

   function defineTabs($options = array()) {
      $ong = array();
      $this->addStandardTab('PluginShellcommandsShellcommand_Item', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   function showForm($ID, $options = array()) {
      global $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";

      echo "<td>".__('Valid tags')."</td>";
      echo "<td>[ID], [NAME], [IP], [MAC], [NETWORK], [DOMAIN]</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Tag')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "link", array('size' => "50"));
      echo "</td>";

      echo "<td>".__('Path','shellcommands')."</td>";
      echo "<td>";
      Dropdown::show('PluginShellcommandsShellcommandPath', array('value' => $this->fields["plugin_shellcommands_shellcommandpaths_id"]));
      echo "</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Windows','shellcommands')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "parameters");
      echo "</td>";

      echo "<td></td>";
      echo "<td></td>";

      echo "</tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }

   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */

   function getSelectLinkedItem() {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_shellcommands_shellcommands_items`
              WHERE `plugin_shellcommands_shellcommands_id`='".$this->fields['id']."'";
   }

   function dropdownCommands($itemtype) {
      global $DB;

      $query = "SELECT `".$this->gettable()."`.`id`, `".$this->gettable()."`.`name`,`".$this->gettable()."`.`link`
          FROM `".$this->gettable()."`,`glpi_plugin_shellcommands_shellcommands_items`
          WHERE `".$this->gettable()."`.`id` = `glpi_plugin_shellcommands_shellcommands_items`.`plugin_shellcommands_shellcommands_id`
          AND `glpi_plugin_shellcommands_shellcommands_items`.`itemtype` = '".$itemtype."'
          AND `".$this->gettable()."`.`is_deleted` = '0'
          ORDER BY `".$this->gettable()."`.`name`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number != "0") {
         echo "<select name='command'>";
         echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";
         while ($data = $DB->fetch_assoc($result)) {
            echo "<option value='".$data["id"]."'>".$data["name"]."</option>";
         }

         echo "</select>";
      }
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

      $actions['InstallTEST'] = __('Associate');
      $actions['Desinstall'] = __('Dissociate');
            
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
         case "InstallTEST":
            Dropdown::showItemTypes("item_item",self::getTypes(true));
            echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . __('Post') . "\" >";
            return true;
            break;
         case "Desinstall":
            Dropdown::showItemTypes("item_item",self::getTypes(true));
            echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . __('Post') . "\" >";
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

      $command_item = new PluginShellcommandsShellcommand_Item();

      switch ($input['action']) {    
         case "InstallTEST" :
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  if($command_item->addItem($key, $input['item_item'])){
                     $res['ok']++;
                  } else {                
                     $res['ko']++;
                  }
               }
            }
            break;
         case "Desinstall" :
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  if($command_item->deleteItemByShellCommandsAndItem($key, $input['item_item'])){
                     $res['ok']++;
                  } else {
                     $res['ko']++;
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