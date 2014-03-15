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

function plugin_routetables_install() {
   global $DB;

   include_once (GLPI_ROOT . "/plugins/routetables/inc/profile.class.php");

   $update = false;
   if (!TableExists("glpi_plugin_routetable_profiles") && !TableExists("glpi_plugin_routetables_profiles")) {

      $DB->runFile(GLPI_ROOT . "/plugins/routetables/sql/empty-1.2.0.sql");
   } else if (TableExists("glpi_plugin_routetable_profiles") && FieldExists("glpi_plugin_routetable_profiles", "interface")) {

      $update = true;
      $DB->runFile(GLPI_ROOT . "/plugins/routetables/sql/update-1.1.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/routetables/sql/update-1.1.1.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/routetables/sql/update-1.2.0.sql");
   } else if (TableExists("glpi_plugin_routetable") && !FieldExists("glpi_plugin_routetable", "helpdesk_visible")) {

      $update = true;
      $DB->runFile(GLPI_ROOT . "/plugins/routetables/sql/update-1.1.1.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/routetables/sql/update-1.2.0.sql");
   } else if (!TableExists("glpi_plugin_routetables_profiles")) {

      $update = true;
      $DB->runFile(GLPI_ROOT . "/plugins/routetables/sql/update-1.2.0.sql");
   }

   if ($update) {

      $query_ = "SELECT *
            FROM `glpi_plugin_routetables_profiles` ";
      $result_ = $DB->query($query_);
      if ($DB->numrows($result_) > 0) {

         while ($data = $DB->fetch_array($result_)) {
            $query = "UPDATE `glpi_plugin_routetables_profiles`
                  SET `profiles_id` = '" . $data["id"] . "'
                  WHERE `id` = '" . $data["id"] . "';";
            $result = $DB->query($query);
         }
      }

      $query = "ALTER TABLE `glpi_plugin_routetables_profiles`
               DROP `name` ;";
      $result = $DB->query($query);

      Plugin::migrateItemType(
              array(5100 => 'PluginRoutetablesRoutetable'), array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
          "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"), array("glpi_plugin_routetables_routetables_items"));
   }

   PluginRoutetablesProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_routetables_uninstall() {
   global $DB;

   $tables = array("glpi_plugin_routetables_routetables",
       "glpi_plugin_routetables_routetables_items",
       "glpi_plugin_routetables_profiles");

   foreach ($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   //old versions	
   $tables = array("glpi_plugin_routetable",
       "glpi_plugin_routetable_device",
       "glpi_plugin_routetable_profiles");

   foreach ($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   $tables_glpi = array("glpi_displaypreferences",
       "glpi_documents_items",
       "glpi_bookmarks",
       "glpi_logs",
       "glpi_tickets");

   foreach ($tables_glpi as $table_glpi)
      $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginRoutetablesRoutetable' ;");

   if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype' => 'PluginRoutetablesRoutetable'));
   }

   return true;
}

function plugin_routetables_postinit() {
   global $CFG_GLPI, $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['routetables'] = array();

   foreach (PluginRoutetablesRoutetable::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['routetables'][$type]
              = array('PluginRoutetablesRoutetable_Item', 'cleanForItem');

      CommonGLPI::registerStandardTab($type, 'PluginRoutetablesRoutetable_Item');
   }
}

function plugin_routetables_AssignToTicket($types) {

   if (plugin_routetables_haveRight("open_ticket", "1"))
      $types['PluginRoutetablesRoutetable'] = PluginRoutetablesRoutetable::getTypeName(2);

   return $types;
}

function plugin_routetables_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("routetables"))
      return array("glpi_entities" => array("glpi_plugin_routetables_routetables" => "entities_id"),
          "glpi_profiles" => array("glpi_plugin_routetables_profiles" => "profiles_id"),
          "glpi_plugin_routetables_routetables" => array(
              "glpi_plugin_routetables_routetables_items" => "plugin_routetables_routetables_id"
              ));
   else
      return array();
}

////// SEARCH FUNCTIONS ///////() {

function plugin_routetables_getAddSearchOptions($itemtype) {

   $sopt = array();

   if (in_array($itemtype, PluginRoutetablesRoutetable::getTypes(true))) {
      if (plugin_routetables_haveRight("routetables", "r")) {
         $sopt[5110]['table'] = 'glpi_plugin_routetables_routetables';
         $sopt[5110]['field'] = 'name';
         $sopt[5110]['name'] = PluginRoutetablesRoutetable::getTypeName(2) . " - " . __('Name');
         $sopt[5110]['forcegroupby'] = true;
         $sopt[5110]['massiveaction']  = false;
         $sopt[5110]['datatype'] = 'itemlink';
         $sopt[5110]['itemlink_type'] = 'PluginRoutetablesRoutetable';
         $sopt[5110]['joinparams'] = array('beforejoin'
             => array('table' => 'glpi_plugin_routetables_routetables_items',
                 'joinparams' => array('jointype' => 'itemtype_item')));

         $sopt[5111]['table'] = 'glpi_plugin_routetables_routetables';
         $sopt[5111]['field'] = 'destination';
         $sopt[5111]['name'] = PluginRoutetablesRoutetable::getTypeName(2) . " - " . __('Network');
         $sopt[5111]['forcegroupby'] = true;
         $sopt[5111]['massiveaction']  = false;
         $sopt[5111]['joinparams'] = array('beforejoin'
             => array('table' => 'glpi_plugin_routetables_routetables_items',
                 'joinparams' => array('jointype' => 'itemtype_item')));

         $sopt[5112]['table'] = 'glpi_plugin_routetables_routetables';
         $sopt[5112]['field'] = 'gateway';
         $sopt[5112]['name'] = PluginRoutetablesRoutetable::getTypeName(2) . " - " . __('Gateway');
         $sopt[5112]['forcegroupby'] = true;
         $sopt[5112]['massiveaction']  = false;
         $sopt[5112]['joinparams'] = array('beforejoin'
             => array('table' => 'glpi_plugin_routetables_routetables_items',
                 'joinparams' => array('jointype' => 'itemtype_item')));
      }
   }
   return $sopt;
}

function plugin_routetables_giveItem($type, $ID, $data, $num) {
   global $CFG_GLPI, $DB;

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   switch ($table . '.' . $field) {
      case "glpi_plugin_routetables_routetables_items.items_id" :
         $query_device = "SELECT DISTINCT `itemtype` 
                             FROM `glpi_plugin_routetables_routetables_items` 
                             WHERE `plugin_routetables_routetables_id` = '" . $data['id'] . "' 
                             ORDER BY `itemtype`";
         $result_device = $DB->query($query_device);
         $number_device = $DB->numrows($result_device);

         $out = '';
         $route = $data['id'];
         if ($number_device > 0) {
            for ($i = 0; $i < $number_device; $i++) {
               $column = "name";
               $itemtype = $DB->result($result_device, $i, "itemtype");

               if (!class_exists($itemtype)) {
                  continue;
               }
               $item = new $itemtype();
               if ($item->canView()) {
                  $table_item = getTableForItemType($itemtype);
                  $query = "SELECT `" . $table_item . "`.*, `glpi_entities`.`id` AS entity "
                          . " FROM `glpi_plugin_routetables_routetables_items`, `" . $table_item
                          . "` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `" . $table_item . "`.`entities_id`) "
                          . " WHERE `" . $table_item . "`.`id` = `glpi_plugin_routetables_routetables_items`.`items_id` 
						AND `glpi_plugin_routetables_routetables_items`.`itemtype` = '$itemtype' 
						AND `glpi_plugin_routetables_routetables_items`.`plugin_routetables_routetables_id` = '" . $route . "' "
                          . getEntitiesRestrictRequest(" AND ", $table_item, '', '', $item->maybeRecursive());

                  if ($item->maybeTemplate()) {
                     $query.=" AND `" . $table_item . "`.`is_template` = '0'";
                  }
                  $query.=" ORDER BY `glpi_entities`.`completename`, `" . $table_item . "`.`$column`";

                  if ($result_linked = $DB->query($query))
                     if ($DB->numrows($result_linked)) {
                        $item = new $itemtype();
                        while ($data = $DB->fetch_assoc($result_linked)) {
                           if ($item->getFromDB($data['id'])) {
                              $out .= $item->getTypeName() . " - " . $item->getLink() . "<br>";
                           }
                        }
                     } else
                        $out.=' ';
               } else
                  $out.=' ';
            }
         }
         return $out;
         break;
   }
   return "";
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_routetables_MassiveActions($type) {

   if (in_array($type, PluginRoutetablesRoutetable::getTypes(true))) {
      return array("plugin_routetables_add_item" => __('Associate with route', 'routetables'));
   }
   return array();
}

function plugin_routetables_MassiveActionsDisplay($options = array()) {

   $route = new PluginRoutetablesRoutetable();

   if (in_array($options['itemtype'], PluginRoutetablesRoutetable::getTypes(true))) {
      $route->dropdownRouteTables("plugin_routetables_routetables_id");
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . __('Post') . "\" >";
   }
   return "";
}

function plugin_routetables_MassiveActionsProcess($data) {
   
   $route_item = new PluginRoutetablesRoutetable_Item();
   
   $res = array('ok' => 0,
            'ko' => 0,
            'noright' => 0);

   switch ($data['action']) {
      case "plugin_routetables_add_item":     
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('plugin_routetables_routetables_id' => $data['plugin_routetables_routetables_id'],
                        'items_id'      => $key,
                        'itemtype'      => $data['itemtype']);
               if ($route_item->can(-1,'w',$input)) {
                  if ($route_item->add($input)){
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
   }
   return $res;
}


function plugin_datainjection_populate_routetables() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginRoutetablesRoutetableInjection'] = 'routetables';
}

?>