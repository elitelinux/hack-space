<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Financialreports plugin for GLPI
 Copyright (C) 2003-2011 by the Financialreports Development Team.

 https://forge.indepnet.net/projects/financialreports
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Financialreports.

 Financialreports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Financialreports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Financialreports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_financialreports() {
	global $PLUGIN_HOOKS,$CFG_GLPI;
   
   $PLUGIN_HOOKS['csrf_compliant']['financialreports'] = true;
	$PLUGIN_HOOKS['change_profile']['financialreports'] = 
                                       array('PluginFinancialreportsProfile','changeProfile');
   
   //Clean Plugin on Profile delete
   $PLUGIN_HOOKS['pre_item_purge']['financialreports'] = array('Profile'=>
                                       array('PluginFinancialreportsProfile', 'purgeProfiles'));

   
	if (Session::getLoginUserID()) {

		if (plugin_financialreports_haveRight("financialreports","r")) {

			$PLUGIN_HOOKS['reports']['financialreports'] = 
               array('front/financialreport.php'=>__('Report'));
         $PLUGIN_HOOKS['use_massive_action']['financialreports']=1;

		}
		
      if (plugin_financialreports_haveRight("financialreports","r") || Session::haveRight("config","w")) {
         $PLUGIN_HOOKS['config_page']['financialreports'] = 'front/config.form.php';
      }
      
      $PLUGIN_HOOKS['post_init']['financialreports'] = 'plugin_financialreports_postinit';
	}

}
// Get the name and the version of the plugin - Needed
function plugin_version_financialreports() {

	return array (
		'name' => __('Asset situation', 'financialreports'),
		'version' => '2.0.0',
		'oldname' => 'state',
		'license' => 'GPLv2+',
		'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
		'homepage'=>'https://forge.indepnet.net/projects/show/financialreports',
		'minGlpiVersion' => '0.84',// For compatibility
	);
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_financialreports_check_prerequisites() {
	if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      _e('This plugin requires GLPI >= 0.84', 'financialreports');
      return false;
   }
   return true;
}

// Uninstall process for plugin
//need to return true if succeeded : may display messages or add to message after redirect
function plugin_financialreports_check_config() {
	return true;
}

function plugin_financialreports_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_financialreports_profile"][$module])
      &&in_array($_SESSION["glpi_plugin_financialreports_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

?>