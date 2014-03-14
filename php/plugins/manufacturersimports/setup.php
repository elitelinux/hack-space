<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manufacturersimports plugin for GLPI
 Copyright (C) 2003-2011 by the Manufacturersimports Development Team.

 https://forge.indepnet.net/projects/manufacturersimports
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Manufacturersimports.

 Manufacturersimports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manufacturersimports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manufacturersimports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_manufacturersimports() {
	global $PLUGIN_HOOKS,$CFG_GLPI;
   
   $PLUGIN_HOOKS['csrf_compliant']['manufacturersimports'] = true;
	$PLUGIN_HOOKS['change_profile']['manufacturersimports'] 
                              = array('PluginManufacturersimportsProfile','changeProfile');
	
	//Clean Plugin on Profile delete
   $PLUGIN_HOOKS['pre_item_purge']['manufacturersimports'] 
            = array('Profile'=>array('PluginManufacturersimportsProfile', 'purgeProfiles'));
   
	if (Session::getLoginUserID()) {
      
      Plugin::registerClass('PluginManufacturersimportsProfile',
                         array('addtabon' => 'Profile'));
                         
		if (plugin_manufacturersimports_haveRight("manufacturersimports","r")) {
         $PLUGIN_HOOKS['menu_entry']['manufacturersimports'] = 'front/import.php';
         $PLUGIN_HOOKS['submenu_entry']['manufacturersimports']['config'] = 'front/config.php';
         $PLUGIN_HOOKS['add_css']['manufacturersimports']="manufacturersimports.css";
         $PLUGIN_HOOKS['use_massive_action']['manufacturersimports']=1;

		}
		
		if (plugin_manufacturersimports_haveRight("manufacturersimports","w") 
                                                      || Session::haveRight("config","w")) {
         $PLUGIN_HOOKS['config_page']['manufacturersimports'] = 'front/config.php';
         $PLUGIN_HOOKS['submenu_entry']['manufacturersimports']['options']['config']['links']['search']
                  = '/plugins/manufacturersimports/front/config.php';
         $PLUGIN_HOOKS['submenu_entry']['manufacturersimports']['options']['config']['links']['add']
                  = '/plugins/manufacturersimports/front/config.form.php';
      }
      
      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['manufacturersimports'] = 'plugin_manufacturersimports_postinit';
	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_manufacturersimports() {

	return array (
		'name' => _n('Suppliers import', 'Suppliers imports', 2, 'manufacturersimports'),
		'oldname' => 'suppliertag',
		'version' => '1.6.0',
		'license' => 'GPLv2+',
		'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
		'homepage'=>'https://forge.indepnet.net/projects/show/manufacturersimports',
		'minGlpiVersion' => '0.84',// For compatibility / no install in version < 0.80
	);

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_manufacturersimports_check_prerequisites() {

   if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      echo __('This plugin requires GLPI >= 0.84', 'manufacturersimports');
   } else if (!extension_loaded("soap")) {
      echo __('Incompatible PHP Installation. Requires module', 'manufacturersimports'). " soap";
   } else if (!extension_loaded("curl")) {
      echo __('Incompatible PHP Installation. Requires module', 'manufacturersimports'). " curl";
   } else {
      return true;
   }
   return false;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_manufacturersimports_check_config() {
	return true;
}

function plugin_manufacturersimports_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_manufacturersimports_profile"][$module]) 
      && in_array($_SESSION["glpi_plugin_manufacturersimports_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

?>