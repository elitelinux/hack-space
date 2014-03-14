<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Domains plugin for GLPI
 Copyright (C) 2003-2011 by the Domains Development Team.

 https://forge.indepnet.net/projects/domains
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Domains.

 Domains is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Domains is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Domains. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
 
// Init the hooks of the plugins -Needed
function plugin_init_domains() {
	global $PLUGIN_HOOKS,$CFG_GLPI;
   
   $PLUGIN_HOOKS['csrf_compliant']['domains'] = true;
	$PLUGIN_HOOKS['change_profile']['domains'] = array('PluginDomainsProfile','changeProfile');
	$PLUGIN_HOOKS['assign_to_ticket']['domains'] = true;

	if (Session::getLoginUserID()) {
		
		Plugin::registerClass('PluginDomainsDomain', array(
         'linkuser_tech_types' => true,
         'linkgroup_tech_types' => true,
         'document_types' => true,	
         'contract_types' => true,
         'ticket_types'         => true,
         'helpdesk_visible_types' => true,
         'notificationtemplates_types' => true
      ));
      
      Plugin::registerClass('PluginDomainsConfig',
                         array('addtabon' => 'CronTask'));
                         
      Plugin::registerClass('PluginDomainsProfile',
                         array('addtabon' => 'Profile'));
      
      Plugin::registerClass('PluginDomainsDomain',
                         array('addtabon' => 'Supplier'));
                         
		if ((isset($_SESSION["glpi_plugin_environment_installed"]) && $_SESSION["glpi_plugin_environment_installed"]==1)) {
			
			$_SESSION["glpi_plugin_environment_domains"]=1;
			
			// Display a menu entry ?
			if (plugin_domains_haveRight("domains","r")) {
				$PLUGIN_HOOKS['menu_entry']['domains'] = false;
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['domains']['title'] = PluginDomainsDomain::getTypeName(2);;
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['domains']['page'] = '/plugins/domains/front/domain.php';
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['domains']['links']['search'] = '/plugins/domains/front/domain.php';
			}
			
			  if (plugin_domains_haveRight("domains","w")) {
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['domains']['links']['add'] = '/plugins/domains/front/domain.form.php';
				$PLUGIN_HOOKS['use_massive_action']['domains']=1;
				
			}		
		} else {
		
			// Display a menu entry ?
			if (plugin_domains_haveRight("domains","r")) {
				$PLUGIN_HOOKS['menu_entry']['domains'] = 'front/domain.php';
				$PLUGIN_HOOKS['submenu_entry']['domains']['search'] = 'front/domain.php';
			}
			
			if (plugin_domains_haveRight("domains","w")) {
				$PLUGIN_HOOKS['submenu_entry']['domains']['add'] = 'front/domain.form.php?new=1';
				$PLUGIN_HOOKS['use_massive_action']['domains']=1;
				
			}
		}
		
		if (class_exists('PluginDomainsDomain_Item')) { // only if plugin activated
         $PLUGIN_HOOKS['pre_item_purge']['domains'] = 
                           array('Profile'=>array('PluginDomainsProfile', 'purgeProfiles'));
         $PLUGIN_HOOKS['plugin_datainjection_populate']['domains'] = 
                                                   'plugin_datainjection_populate_domains';
      }
		// Import from Data_Injection plugin
		$PLUGIN_HOOKS['migratetypes']['domains'] = 'plugin_datainjection_migratetypes_domains';
      
      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['domains'] = 'plugin_domains_postinit';
	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_domains() {

	return array (
		'name' => _n('Domain', 'Domains', 2, 'domains'),
		'version' => '1.6.0',
		'oldname' => 'domain',
		'license' => 'GPLv2+',
		'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
		'homepage'=>'https://forge.indepnet.net/projects/domains',
		'minGlpiVersion' => '0.84',// For compatibility / no install in version < 0.80
	);

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_domains_check_prerequisites() {
	if (version_compare(GLPI_VERSION, '0.84', 'lt') || version_compare(GLPI_VERSION, '0.85', 'ge')) {
      _e('This plugin requires GLPI >= 0.84', 'domains');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_domains_check_config() {
	return true;
}

function plugin_domains_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_domains_profile"][$module]) 
         && in_array($_SESSION["glpi_plugin_domains_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

function plugin_datainjection_migratetypes_domains($types) {
   $types[4400] = 'PluginDomainsDomain';
   return $types;
}

?>