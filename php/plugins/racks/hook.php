<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_racks_install() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/racks/inc/profile.class.php");

   $update=false;
   if (!TableExists("glpi_plugin_rack_profiles") && !TableExists("glpi_plugin_racks_profiles")) {

      $DB->runFile(GLPI_ROOT ."/plugins/racks/sql/empty-1.4.1.sql");

   } else if (TableExists("glpi_plugin_rack_content") && !FieldExists("glpi_plugin_rack_content","first_powersupply")) {

      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/racks/sql/update-1.0.2.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/racks/sql/update-1.1.0.sql");

   } else if (!TableExists("glpi_plugin_racks_profiles")) {

      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/racks/sql/update-1.1.0.sql");

   }
   //from 1.1 version
   if (TableExists("glpi_plugin_racks_racks") 
      && !FieldExists("glpi_plugin_racks_racks","otherserial")) {
      $DB->runFile(GLPI_ROOT ."/plugins/racks/sql/update-1.2.1.sql");
   }

   if (TableExists("glpi_plugin_racks_racks")
      && !FieldExists("glpi_plugin_racks_racks","users_id_tech")) {
      $DB->runFile(GLPI_ROOT ."/plugins/racks/sql/update-1.3.0.sql");
   }

   if (!TableExists("glpi_plugin_racks_racktypes")) {
      $DB->runFile(GLPI_ROOT ."/plugins/racks/sql/update-1.3.2.sql");
   }
   
   if (TableExists("glpi_plugin_racks_racktypes") 
                  && !FieldExists("glpi_plugin_racks_racktypes","is_recursive")) {
      $DB->runFile(GLPI_ROOT ."/plugins/racks/sql/update-1.4.1.sql");
   }

   if ($update) {

      $query_="SELECT *
            FROM `glpi_plugin_racks_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_racks_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }

      $query="ALTER TABLE `glpi_plugin_racks_profiles`
               DROP `name` ;";
      $result=$DB->query($query);

      Plugin::migrateItemType(
         array(4450=>'PluginRacksRack',4451=>'PluginRacksOther'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
         array("glpi_plugin_racks_racks_items","glpi_plugin_racks_itemspecifications"));
   }

   PluginRacksProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_racks_uninstall() {
   global $DB;

   foreach (array (
         "glpi_plugin_racks_racks",
         "glpi_plugin_racks_racks_items",
         "glpi_plugin_racks_profiles",
         "glpi_plugin_racks_itemspecifications",
         "glpi_plugin_racks_rackmodels",
         "glpi_plugin_racks_roomlocations",
         "glpi_plugin_racks_connections",
         "glpi_plugin_racks_configs",
         "glpi_plugin_racks_others",
         "glpi_plugin_racks_othermodels",
         "glpi_plugin_racks_racktypes",
         "glpi_plugin_racks_rackstates"
      ) as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   //old versions
   $tables = array("glpi_plugin_rack",
               "glpi_plugin_rack_content",
               "glpi_plugin_rack_device_spec",
               "glpi_plugin_rack_profiles",
               "glpi_plugin_rack_config",
               "glpi_dropdown_plugin_rack_room_locations",
               "glpi_dropdown_plugin_rack_ways",
               "glpi_plugin_rack_others",
               "glpi_dropdown_plugin_rack_others_type");

   foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   $tables_glpi = array("glpi_displaypreferences",
               "glpi_documents_items",
               "glpi_bookmarks",
               "glpi_logs");

   foreach($tables_glpi as $table_glpi)
    $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginRacksRack';");

   return true;
}

function plugin_racks_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['racks'] = array();
   foreach (PluginRacksRack::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['racks'][$type]
         = array('PluginRacksRack_Item','cleanForItem');

      CommonGLPI::registerStandardTab($type, 'PluginRacksRack_Item');
   }
   foreach (PluginRacksItemSpecification::getModelClasses() as $model) {

      CommonGLPI::registerStandardTab($model, 'PluginRacksItemSpecification');
   }
}

// Define dropdown relations
function plugin_racks_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("racks"))
      return array("glpi_plugin_racks_roomlocations"=>array("glpi_plugin_racks_racks"=>"plugin_racks_roomlocations_id"),
               "glpi_plugin_racks_rackmodels"=>array("glpi_plugin_racks_racks"=>"plugin_racks_rackmodels_id"),
               "glpi_locations"=>array("glpi_plugin_racks_racks"=>"locations_id"),
               "glpi_users"=>array("glpi_plugin_racks_racks"=>"users_id_tech"),
               "glpi_groups"=>array("glpi_plugin_racks_racks"=>"groups_id_tech"),
               "glpi_manufacturers"=>array("glpi_plugin_racks_racks"=>"manufacturers_id"),
               "glpi_plugin_racks_racks"=>array("glpi_plugin_racks_racks_items"=>"plugin_racks_racks_id"),
               "glpi_plugin_racks_itemspecifications"=>array("glpi_plugin_racks_racks_items"=>"plugin_racks_itemspecifications_id"),
                "glpi_plugin_racks_connections"=>array("glpi_plugin_racks_racks_items"=>"first_powersupply"),
                "glpi_plugin_racks_connections"=>array("glpi_plugin_racks_racks_items"=>"second_powersupply"),
                "glpi_plugin_racks_othermodels"=>array("glpi_plugin_racks_others"=>"plugin_racks_othermodels_id"),
                "glpi_plugin_racks_racktypes"=>array("glpi_plugin_racks_racks"=>"plugin_racks_racktypes_id"),
                "glpi_plugin_racks_rackstates"=>array("glpi_plugin_racks_racks"=>"plugin_racks_rackstates_id"),
                "glpi_profiles" => array ("glpi_plugin_racks_profiles" => "profiles_id"),
                "glpi_entities"=>array("glpi_plugin_racks_racks"=>"entities_id",
                                 "glpi_plugin_racks_roomlocations"=>"entities_id",
                                 "glpi_plugin_racks_others"=>"entities_id"));
   else
      return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_racks_getDropdown() {

   $plugin = new Plugin();
   if ($plugin->isActivated("racks"))
      return array('PluginRacksRoomLocation'=>__('Location'),
                  'PluginRacksRackModel'=>__('Model'),
                 'PluginRacksConnection'=>__('Power supply connection', 'racks'),
                 'PluginRacksOtherModel'=>__('Others equipments', 'racks'),
                 'PluginRacksRackType'=>__('Type'),
                 'PluginRacksRackState'=>__('State'));
   else
      return array();
}

function plugin_racks_getAddSearchOptions($itemtype) {

   $sopt=array();

   if (in_array($itemtype, PluginRacksRack::getTypes(true))) {
      if (plugin_racks_haveRight("racks", "r")) {
         $sopt[4460]['table'] = 'glpi_plugin_racks_racks';
         $sopt[4460]['field'] = 'name';
         $sopt[4460]['name'] = _n('Rack enclosure', 'Rack enclosures', 2, 'racks'). " - ". __('Name');
         $sopt[4460]['forcegroupby']='1';
         $sopt[4460]['datatype']='itemlink';
         $sopt[4460]['itemlink_type']='PluginRacksRack';
         $sopt[4460]['massiveaction']=false;
      }
   }
   return $sopt;
}

//for search
function plugin_racks_addLeftJoin($type, $ref_table, $new_table, $linkfield, & $already_link_tables) {

   switch ($new_table) {
      case "glpi_plugin_racks_racks_items" :
         return " LEFT JOIN `glpi_plugin_racks_racks_items` 
         ON (`$ref_table`.`id` = `glpi_plugin_racks_racks_items`.`items_id`
         AND `glpi_plugin_racks_racks_items`.`itemtype`= '".$type."Model') ";
         break;
      case "glpi_plugin_racks_racks" : // From items
         $out=Search::addLeftJoin($type,$ref_table,$already_link_tables,"glpi_plugin_racks_racks_items","plugin_racks_racks_id");
         $out .= " LEFT JOIN `glpi_plugin_racks_racks`
                  ON (`glpi_plugin_racks_racks`.`id` = `glpi_plugin_racks_racks_items`.`plugin_racks_racks_id`) ";
         return $out;
         break;
   }

   return "";
}

// Hook done on purge item case
function plugin_item_purge_racks($item) {

   $type = get_class($item);
   $temp = new PluginRacksRack_Item();
   $temp->deleteByCriteria(array('itemtype' => $type."Model",
                                    'items_id' => $item->getField('id')));
   return true;

}

?>
