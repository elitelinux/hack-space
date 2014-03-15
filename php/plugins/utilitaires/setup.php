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

// Init the hooks of the plugins -Needed
function plugin_init_utilitaires() {
	global $PLUGIN_HOOKS;

	$PLUGIN_HOOKS['csrf_compliant']['utilitaires'] = true;
   $PLUGIN_HOOKS['change_profile']['utilitaires'] = array('PluginUtilitairesProfile','changeProfile');
   //Clean Plugin on Profile delete
   $PLUGIN_HOOKS['pre_item_purge']['utilitaires'] = array('Profile'=>array('PluginUtilitairesProfile', 'purgeProfiles'));
   
	if (Session::getLoginUserID()) {
      
      Plugin::registerClass('PluginUtilitairesProfile',
                         array('addtabon' => 'Profile'));
                         
      if (plugin_utilitaires_haveRight("utilitaires","w")) {
        $PLUGIN_HOOKS['menu_entry']['utilitaires'] = 'front/utilitaire.php';
      }
      
   }
}


// Get the name and the version of the plugin - Needed
function plugin_version_utilitaires() {

	return array (
		'name'            => _n('Utility', 'Utilities', 2, 'utilitaires'),
		'version'         => '1.6.0',
		'license'         => 'GPLv2+',
		'author'          => 'Frederic Van Beveren, David Durieux, Xavier Caillaud',
		'homepage'        => 'https://forge.indepnet.net/projects/show/utilitaires',
		'minGlpiVersion'  => '0.84',// For compatibility / no install in version < 0.84
	);

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_utilitaires_check_prerequisites() {
	if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      _e('This plugin requires GLPI >= 0.84', 'utilitaires');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_utilitaires_check_config() {
	return true;
}

function plugin_utilitaires_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_utilitaires_profile"][$module])
            &&in_array($_SESSION["glpi_plugin_utilitaires_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

?>