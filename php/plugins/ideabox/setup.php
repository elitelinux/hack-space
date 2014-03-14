<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Ideabox plugin for GLPI
 Copyright (C) 2003-2011 by the Ideabox Development Team.

 https://forge.indepnet.net/projects/ideabox
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Ideabox.

 Ideabox is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Ideabox is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Ideabox. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_ideabox() {
	global $PLUGIN_HOOKS,$CFG_GLPI;
   
   $PLUGIN_HOOKS['csrf_compliant']['ideabox'] = true;
	$PLUGIN_HOOKS['change_profile']['ideabox'] = array('PluginIdeaboxProfile','changeProfile');
	$PLUGIN_HOOKS['pre_item_purge']['ideabox'] 
                           = array('Profile'=>array('PluginIdeaboxProfile', 'purgeProfiles'));
   $PLUGIN_HOOKS['plugin_datainjection_populate']['ideabox'] = 'plugin_datainjection_populate_ideabox';
   $PLUGIN_HOOKS['assign_to_ticket']['ideabox'] = true;
	
	if (Session::getLoginUserID()) {
		
		Plugin::registerClass('PluginIdeaboxIdeabox', array(
		'massiveaction_noupdate_types' => true,
		'notificationtemplates_types' => true,
      'ticket_types'         => true,
		'helpdesk_visible_types' => true,
      'linkuser_types' => true,
      'document_types' => true,
      ));
      
      Plugin::registerClass('PluginIdeaboxComment', array(
         'notificationtemplates_types' => true
      ));
      
      Plugin::registerClass('PluginIdeaboxProfile',
                         array('addtabon' => 'Profile'));
	
		// Display a menu entry ?
		if (plugin_ideabox_haveRight("ideabox","r")) {
			$PLUGIN_HOOKS['menu_entry']['ideabox'] = 'front/ideabox.php';
			$PLUGIN_HOOKS['helpdesk_menu_entry']['ideabox'] = '/front/ideabox.php';
			$PLUGIN_HOOKS['submenu_entry']['ideabox']['search'] = 'front/ideabox.php';
			$PLUGIN_HOOKS['redirect_page']['ideabox'] = "front/ideabox.form.php";
		}
		
      if (plugin_ideabox_haveRight("ideabox","w")) {
         $PLUGIN_HOOKS['submenu_entry']['ideabox']['add'] = 'front/ideabox.form.php';
         $PLUGIN_HOOKS['use_massive_action']['ideabox']=1;  
      }
      
		$PLUGIN_HOOKS['migratetypes']['ideabox'] = 'plugin_datainjection_migratetypes_ideabox';
	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_ideabox() {

	return array (
		'name' => _n('Idea box', 'Ideas box', 2, 'ideabox'),
		'version' => '2.0.0',
		'license' => 'GPLv2+',
		'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
		'homepage'=>'https://forge.indepnet.net/repositories/show/ideabox',
		'minGlpiVersion' => '0.84',// For compatibility / no install in version < 0.84
	);

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_ideabox_check_prerequisites() {
	if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      _e('This plugin requires GLPI >= 0.84', 'ideabox');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_ideabox_check_config() {
	return true;
}

function plugin_ideabox_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_ideabox_profile"][$module]) 
                  && in_array($_SESSION["glpi_plugin_ideabox_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

function plugin_datainjection_migratetypes_ideabox($types) {
   $types[4900] = 'PluginIdeaboxIdeabox';
   return $types;
}

?>