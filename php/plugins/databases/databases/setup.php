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

// Init the hooks of the plugins -Needed
function plugin_init_databases() {
	global $PLUGIN_HOOKS;
   
   $PLUGIN_HOOKS['csrf_compliant']['databases'] = true;
	$PLUGIN_HOOKS['change_profile']['databases'] = array('PluginDatabasesProfile','changeProfile');
	$PLUGIN_HOOKS['assign_to_ticket']['databases'] = true;
   
	if (Session::getLoginUserID()) {
		
		Plugin::registerClass('PluginDatabasesDatabase', array(
         'linkgroup_tech_types' => true,
         'linkuser_tech_types' => true,
         'document_types' => true,
         'ticket_types'         => true,
         'helpdesk_visible_types' => true,
         'addtabon' => 'Supplier'
      ));
      
      Plugin::registerClass('PluginDatabasesProfile',
                         array('addtabon' => 'Profile'));
                         
      if (class_exists('PluginAccountsAccount')) {
         PluginAccountsAccount::registerType('PluginDatabasesDatabase');
      }
   
		if (isset($_SESSION["glpi_plugin_environment_installed"]) && $_SESSION["glpi_plugin_environment_installed"]==1) {
			
			$_SESSION["glpi_plugin_environment_databases"]=1;
			
			// Display a menu entry ?
			if (plugin_databases_haveRight("databases","r")) {
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['databases']['title'] = PluginDatabasesDatabase::getTypeName(2);
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['databases']['page'] = '/plugins/databases/front/database.php';
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['databases']['links']['search'] = '/plugins/databases/front/database.php';
			}
			
			if (plugin_databases_haveRight("databases","w")) {
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['databases']['links']['add'] = '/plugins/databases/front/database.form.php';
				$PLUGIN_HOOKS['use_massive_action']['databases']=1;
			}		
		} else {
		
			// Display a menu entry ?
			if (plugin_databases_haveRight("databases","r")) {
				$PLUGIN_HOOKS['menu_entry']['databases'] = 'front/database.php';
				$PLUGIN_HOOKS['submenu_entry']['databases']['search'] = 'front/database.php';
			}
			
			if (plugin_databases_haveRight("databases","w")) {
				$PLUGIN_HOOKS['submenu_entry']['databases']['add'] = 'front/database.form.php?new=1';
				$PLUGIN_HOOKS['use_massive_action']['databases']=1;
			}
		}
      
      if (class_exists('PluginDatabasesDatabase_Item')) { // only if plugin activated
         $PLUGIN_HOOKS['pre_item_purge']['databases'] 
                        = array('Profile'=>array('PluginDatabasesProfile', 'purgeProfiles'));
         $PLUGIN_HOOKS['plugin_datainjection_populate']['databases'] = 'plugin_datainjection_populate_databases';
      }
      
      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['databases'] = 'plugin_databases_postinit';

		// Import from Data_Injection plugin
		$PLUGIN_HOOKS['migratetypes']['databases'] = 'plugin_datainjection_migratetypes_databases';
	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_databases() {

	return array (
		'name' => _n('Database', 'Databases', 2, 'databases'),
		'version' => '1.6.0',
		'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
		'oldname' => 'sgbd',
		'license' => 'GPLv2+',
		'homepage'=>'https://forge.indepnet.net/projects/show/databases',
		'minGlpiVersion' => '0.84',// For compatibility / no install in version < 0.80
	);

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_databases_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      _e('This plugin requires GLPI >= 0.84', 'databases');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_databases_check_config() {
	return true;
}

function plugin_databases_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_databases_profile"][$module])
            && in_array($_SESSION["glpi_plugin_databases_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

function plugin_datainjection_migratetypes_databases($types) {
   $types[2400] = 'PluginDatabasesDatabase';
   return $types;
}

?>