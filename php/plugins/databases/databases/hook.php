<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Databases plugin for GLPI
 Copyright (C) 2003-2011 by the databases Development Team.

 https://forge.indepnet.net/projects/databases
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of databases.

 Databases is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Databases is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Databases. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_databases_install() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/databases/inc/profile.class.php");
   
   $update=false;
   if (!TableExists("glpi_plugin_sgbd") && !TableExists("glpi_plugin_databases_databases")) {
      
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/empty-1.5.0.sql");

   } else if (TableExists("glpi_plugin_sgbd") && !TableExists("glpi_plugin_sgbd_instances")) {
      
      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/update-1.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/update-1.2.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/update-1.2.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/update-1.3.0.sql");

   } else if (TableExists("glpi_plugin_sgbd") && !TableExists("glpi_dropdown_plugin_sgbd_category")) {
      
      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/update-1.2.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/update-1.2.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/update-1.3.0.sql");

   } else if (TableExists("glpi_plugin_sgbd") && !FieldExists("glpi_plugin_sgbd","helpdesk_visible")) {
      
      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/update-1.2.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/update-1.3.0.sql");

   } else if (!TableExists("glpi_plugin_databases_databases")) {
      
      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/update-1.3.0.sql");

   }
   //from 1.3 version
   if (TableExists("glpi_plugin_databases_databases") 
      && !FieldExists("glpi_plugin_databases_databases","users_id_tech")) {
      $DB->runFile(GLPI_ROOT ."/plugins/databases/sql/update-1.5.0.sql");
   }
   
   
   if ($update) {
      $query_="SELECT *
            FROM `glpi_plugin_databases_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_databases_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }
      
      $query="ALTER TABLE `glpi_plugin_databases_profiles`
               DROP `name` ;";
      $result=$DB->query($query);
      
      $query = "SELECT `entities_id`,`is_recursive`,`id` FROM `glpi_plugin_databases_databases` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number) {
         while ($data=$DB->fetch_array($result)) {
            $query = "UPDATE `glpi_plugin_databases_instances`
                  SET `entities_id` = '".$data["entities_id"]."'
                  AND `is_recursive` = '".$data["is_recursive"]."'
                  WHERE `plugin_databases_databases_id` = '".$data["id"]."' ";
            $DB->query($query) or die($DB->error());
            
            $query = "UPDATE `glpi_plugin_databases_scripts`
                  SET `entities_id` = '".$data["entities_id"]."'
                  AND `is_recursive` = '".$data["is_recursive"]."'
                  WHERE `plugin_databases_databases_id` = '".$data["id"]."' ";
            $DB->query($query) or die($DB->error());
         }
      }
      
      Plugin::migrateItemType(
         array(2400=>'PluginDatabasesDatabase'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
         array("glpi_plugin_databases_databases_items"));
      
      Plugin::migrateItemType(
         array(1200 => "PluginAppliancesAppliance",1300 => "PluginWebapplicationsWebapplication"),
         array("glpi_plugin_databases_databases_items"));
	}
	
   PluginDatabasesProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_databases_uninstall() {
	global $DB;

	$tables = array("glpi_plugin_databases_databases",
					"glpi_plugin_databases_databasetypes",
					"glpi_plugin_databases_databasecategories",
					"glpi_plugin_databases_servertypes",
					"glpi_plugin_databases_scripttypes",
					"glpi_plugin_databases_instances",
					"glpi_plugin_databases_scripts",
					"glpi_plugin_databases_databases_items",
					"glpi_plugin_databases_profiles");
  
   foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
   //old versions	
   $tables = array("glpi_plugin_sgbd",
					"glpi_dropdown_plugin_sgbd_type",
					"glpi_dropdown_plugin_sgbd_server_type",
					"glpi_plugin_sgbd_device",
					"glpi_plugin_sgbd_profiles",
					"glpi_dropdown_plugin_sgbd_script_type",
					"glpi_plugin_sgbd_instances",
					"glpi_plugin_sgbd_scripts",
					"glpi_dropdown_plugin_sgbd_category");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
		
   $tables_glpi = array("glpi_displaypreferences",
					"glpi_documents_items",
					"glpi_bookmarks",
					"glpi_logs",
					"glpi_tickets");

	foreach($tables_glpi as $table_glpi)
		$DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginDatabasesDatabase' ;");

	if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype'=>'PluginDatabasesDatabase'));
   }

	return true;
}

function plugin_databases_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['databases'] = array();

   foreach (PluginDatabasesDatabase::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['databases'][$type]
         = array('PluginDatabasesDatabase_Item','cleanForItem');

      CommonGLPI::registerStandardTab($type, 'PluginDatabasesDatabase_Item');
   }
}

function plugin_databases_AssignToTicket($types) {

	if (plugin_databases_haveRight("open_ticket","1"))
		$types['PluginDatabasesDatabase']=PluginDatabasesDatabase::getTypeName(2);

	return $types;
}

// Define dropdown relations
function plugin_databases_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("databases"))
      return array("glpi_entities"=>array(
                           "glpi_plugin_databases_databases"=>"entities_id",
                           "glpi_plugin_databases_databasetypes"=>"entities_id",
                           "glpi_plugin_databases_databasecategories"=>"entities_id",
                           "glpi_plugin_databases_instances"=>"entities_id",
                           "glpi_plugin_databases_scripts"=>"entities_id"),
                  "glpi_plugin_databases_databasecategories"=>array(
                           "glpi_plugin_databases_databases"=>"plugin_databases_databasecategories_id"),
                  "glpi_plugin_databases_databasetypes"=>array(
                           "glpi_plugin_databases_databases"=>"plugin_databases_databasetypes_id"),
                  "glpi_users"=>array(
                           "glpi_plugin_databases_databases"=>"users_id_tech"),
                  "glpi_groups"=>array(
                           "glpi_plugin_databases_databases"=>"groups_id_tech"),
                  "glpi_plugin_databases_servertypes"=>array(
                           "glpi_plugin_databases_databases"=>"plugin_databases_servertypes_id"),
                  "glpi_suppliers"=>array(
                           "glpi_plugin_databases_databases"=>"suppliers_id"),
                  "glpi_manufacturers"=>array(
                           "glpi_plugin_databases_databases"=>"manufacturers_id"),
                  "glpi_locations"=>array(
                           "glpi_plugin_databases_databases"=>"locations_id"),
                  "glpi_plugin_databases_databases"=>array(
                           "glpi_plugin_databases_instances"=>"plugin_databases_databases_id",
                           "glpi_plugin_databases_scripts"=>"plugin_databases_databases_id",
                           "glpi_plugin_databases_databases_items"=>"plugin_databases_databases_id"),
                  "glpi_plugin_databases_scripttypes"=>array(
                           "glpi_plugin_databases_scripts"=>"plugin_databases_scripttypes_id"),
                  "glpi_profiles" => array (
                           "glpi_plugin_databases_profiles" => "profiles_id"),
      );
   else
      return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_databases_getDropdown() {

	$plugin = new Plugin();
	if ($plugin->isActivated("databases"))
		return array("PluginDatabasesDatabaseType" => PluginDatabasesDatabaseType::getTypeName(2),
                "PluginDatabasesDatabaseCategory" => PluginDatabasesDatabaseCategory::getTypeName(2),
                "PluginDatabasesServerType" => PluginDatabasesServerType::getTypeName(2),
                "PluginDatabasesScriptType" => PluginDatabasesScriptType::getTypeName(2));
	else
		return array();
}

////// SEARCH FUNCTIONS ///////() {

function plugin_databases_getAddSearchOptions($itemtype) {

   $sopt=array();

   if (in_array($itemtype, PluginDatabasesDatabase::getTypes(true))) {
      if (plugin_databases_haveRight("databases","r")) {
    
         $sopt[2410]['table']='glpi_plugin_databases_databases';
         $sopt[2410]['field']='name';
         $sopt[2410]['name']= PluginDatabasesDatabase::getTypeName(2)." - ".__('Name');
         $sopt[2410]['forcegroupby']=true;
         $sopt[2410]['datatype']='itemlink';
         $sopt[2410]['massiveaction']  = false;
         $sopt[2410]['itemlink_type']='PluginDatabasesDatabase';
         $sopt[2410]['joinparams']     = array('beforejoin'
                                                => array('table'      => 'glpi_plugin_databases_databases_items',
                                                         'joinparams' => array('jointype' => 'itemtype_item')));
         
                                                         
         $sopt[2411]['table']='glpi_plugin_databases_databasecategories';
         $sopt[2411]['field']='name';
         $sopt[2411]['name']= PluginDatabasesDatabase::getTypeName(2)." - ".PluginDatabasesDatabaseCategory::getTypeName(1);
         $sopt[2411]['forcegroupby']=true;
         $sopt[2411]['joinparams']     = array('beforejoin' => array(
                                                   array('table'      => 'glpi_plugin_databases_databases',
                                                         'joinparams' => $sopt[2410]['joinparams'])));
         $sopt[2411]['datatype']       = 'dropdown';
         $sopt[2411]['massiveaction']  = false;

         $sopt[2412]['table']='glpi_plugin_databases_servertypes';
         $sopt[2412]['field']='name';
         $sopt[2412]['name']= PluginDatabasesDatabase::getTypeName(2)." - ".PluginDatabasesServerType::getTypeName(1);
         $sopt[2412]['forcegroupby']=true;
         $sopt[2412]['joinparams']     = array('beforejoin' => array(
                                                   array('table'      => 'glpi_plugin_databases_databases',
                                                         'joinparams' => $sopt[2410]['joinparams'])));
         $sopt[2412]['datatype']       = 'dropdown';
         $sopt[2412]['massiveaction']  = false;
                                                         
         $sopt[2413]['table']='glpi_plugin_databases_databasetypes';
         $sopt[2413]['field']='name';
         $sopt[2413]['name']= PluginDatabasesDatabase::getTypeName(2)." - ".PluginDatabasesDatabaseType::getTypeName(1);
         $sopt[2413]['forcegroupby']=true;
         $sopt[2413]['joinparams']     = array('beforejoin' => array(
                                                   array('table'      => 'glpi_plugin_databases_databases',
                                                         'joinparams' => $sopt[2410]['joinparams'])));
         $sopt[2413]['datatype']       = 'dropdown';
         $sopt[2413]['massiveaction']  = false;
      }
	}
	return $sopt;
}

function plugin_databases_giveItem($type,$ID,$data,$num) {
	global $DB;

	$searchopt=&Search::getOptions($type);
	$table=$searchopt[$ID]["table"];
	$field=$searchopt[$ID]["field"];

	switch ($table.'.'.$field) {
		case "glpi_plugin_databases_databases_items.items_id" :
			$query_device = "SELECT DISTINCT `itemtype`
							FROM `glpi_plugin_databases_databases_items`
							WHERE `plugin_databases_databases_id` = '".$data['id']."'
							ORDER BY `itemtype`";
			$result_device = $DB->query($query_device);
			$number_device = $DB->numrows($result_device);
			$y = 0;
			$out='';
			$databases=$data['id'];
			if ($number_device>0) {
				for ($i=0 ; $i < $number_device ; $i++) {
					$column = "name";
					$itemtype = $DB->result($result_device, $i, "itemtype");
					
					if (!class_exists($itemtype)) {
                  continue;
               }
					$item = new $itemtype();
					if ($item->canView()) {
                  $table_item = getTableForItemType($itemtype);

						$query = "SELECT `".$table_item."`.*, `glpi_plugin_databases_databases_items`.`id` AS items_id, `glpi_entities`.`id` AS entity "
						." FROM `glpi_plugin_databases_databases_items`, `".$table_item
						."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table_item."`.`entities_id`) "
						." WHERE `".$table_item."`.`id` = `glpi_plugin_databases_databases_items`.`items_id`
						AND `glpi_plugin_databases_databases_items`.`itemtype` = '$itemtype'
						AND `glpi_plugin_databases_databases_items`.`plugin_databases_databases_id` = '".$databases."' "
						. getEntitiesRestrictRequest(" AND ",$table_item,'','',$item->maybeRecursive());

						if ($item->maybeTemplate()) {
							$query.=" AND `".$table_item."`.`is_template` = '0'";
                  }
						$query.=" ORDER BY `glpi_entities`.`completename`, `".$table_item."`.`$column`";

						if ($result_linked = $DB->query($query))
							if ($DB->numrows($result_linked)) {
								$item = new $itemtype();
								while ($data = $DB->fetch_assoc($result_linked)) {
                           if ($item->getFromDB($data['id'])) {
                              $out .= $item::getTypeName(1)." - ".$item->getLink()."<br>";
                           }
								}
							} else
								$out.= ' ';
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

function plugin_databases_MassiveActions($type) {

   if (in_array($type, PluginDatabasesDatabase::getTypes(true))) {
      return array("plugin_databases_add_item"=>__('Associate to the database', 'databases'));
   }

	return array();
}

function plugin_databases_MassiveActionsDisplay($options=array()) {

	$database=new PluginDatabasesDatabase;
	
   if (in_array($options['itemtype'], PluginDatabasesDatabase::getTypes(true))) {
        
      $database->dropdownDatabases("plugin_databases_databases_id");
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".__s('Post')."\" >";
	}
	return "";
}

function plugin_databases_MassiveActionsProcess($data) {
   
   $res = array('ok' => 0,
            'ko' => 0,
            'noright' => 0);
            
   $database_item = new PluginDatabasesDatabase_Item();

	switch ($data['action']) {

		case "plugin_databases_add_item":
			foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('plugin_databases_databases_id' => $data['plugin_databases_databases_id'],
                              'items_id'      => $key,
                              'itemtype'      => $data['itemtype']);
               if ($database_item->can(-1,'w',$input)) {
                  if ($database_item->can(-1,'w',$input)) {
                     $database_item->add($input);
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

function plugin_datainjection_populate_databases() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginDatabasesDatabaseInjection'] = 'databases';
}

?>