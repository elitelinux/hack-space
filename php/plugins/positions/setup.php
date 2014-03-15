<?php
/*
 * @version $Id: HEADER 15930 2013-02-07 09:47:55Z tsmr $
 -------------------------------------------------------------------------
 Positions plugin for GLPI
 Copyright (C) 2003-2011 by the Positions Development Team.

 https://forge.indepnet.net/projects/positions
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Positions.

 Positions is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Positions is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Positions. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
 
// Init the hooks of the plugins -Needed
function plugin_init_positions() {
	global $PLUGIN_HOOKS,$CFG_GLPI;
   
   $PLUGIN_HOOKS['csrf_compliant']['positions'] = true;
	$PLUGIN_HOOKS['change_profile']['positions'] = array('PluginPositionsProfile','changeProfile');
	
   if (class_exists('PluginPositionsPosition')) { // only if plugin activated
      $PLUGIN_HOOKS['pre_item_purge']['positions'] = array('Profile'=>array(
                                                      'PluginPositionsProfile', 'purgeProfiles'));
   }
   
	if (Session::getLoginUserID()) {
		
		Plugin::registerClass('PluginPositionsProfile',
                         array('addtabon' => 'Profile'));
      
      Plugin::registerClass('PluginPositionsPosition',
                         array('addtabon' => 'Location'));                  
      // Display a menu entry ?
      if (plugin_positions_haveRight("positions","r")) {
         $PLUGIN_HOOKS['menu_entry']['positions'] = 'front/map.form.php';
         $PLUGIN_HOOKS['helpdesk_menu_entry']['positions'] = '/front/map.form.php';
         $PLUGIN_HOOKS['submenu_entry']['positions']['search'] = 'front/position.php';
         $PLUGIN_HOOKS['submenu_entry']['positions']["<img  src='".$CFG_GLPI["root_doc"].
         "/pics/menu_showall.png' title='".__('Map view', 'positions').
         "' alt='".__('Map view', 'positions')."'>"] = 'front/map.form.php'; 
      }
      
      if (plugin_positions_haveRight("positions","w")) {
         $PLUGIN_HOOKS['submenu_entry']['positions']['add'] = 'front/position.form.php';
         $PLUGIN_HOOKS['use_massive_action']['positions']=1;
         $PLUGIN_HOOKS['submenu_entry']['positions']['config'] = 'front/config.form.php';
         $PLUGIN_HOOKS['config_page']['positions'] = 'front/config.form.php';
         $PLUGIN_HOOKS['submenu_entry']['positions']['options']['info']['links']['search'] 
                                                            = '/plugins/positions/front/info.php';
         $PLUGIN_HOOKS['submenu_entry']['positions']['options']['info']['links']['add'] 
                                                         = '/plugins/positions/front/info.form.php';
         $PLUGIN_HOOKS['submenu_entry']['positions']['options']['info']['links']["<img  src='".$CFG_GLPI["root_doc"].
         "/pics/menu_showall.png' title='".__('Map view', 'positions').
         "' alt='".__('Map view', 'positions')."'>"] = '/plugins/positions/front/map.form.php';
         $PLUGIN_HOOKS['submenu_entry']['positions']['options']['info']['links']['config'] 
                                                      = '/plugins/positions/front/config.form.php';
      }
      
		// Add specific files to add to the header : javascript or css
		$PLUGIN_HOOKS['add_javascript']['positions']= array (
                                          //file upload
                                          "lib/plupload/plupload.full.js",
                                          "lib/js/jquery-1.6.2.min.js",
                                          "upload.js",
                                          "positions.js",
                                          "geoloc.js",
                                          "lib/canvas/canvasXpress.min.js",
                                          "lib/canvas/ext-canvasXpress.js",
                                          "lib/canvas/color-field.js",
                                          "lib/crop/jquery.color.js",
                                          "lib/crop/jquery.Jcrop.js",
                                          "lib/crop/jquery.Jcrop.min.js",
                                          );

		//css 
      $PLUGIN_HOOKS['add_css']['positions']= array ("positions.css",
                                                    "lib/canvas/color-field.css",
                                                    "lib/crop/jquery.Jcrop.css",
                                                    "lib/crop/jquery.Jcrop.min.css",
      );
      
      if (class_exists('PluginTreeviewConfig')) {
         $PLUGIN_HOOKS['treeview_params']['positions'] = array('PluginPositionsPosition','showPositionTreeview');
      }
	}
	// End init, when all types are registered
   $PLUGIN_HOOKS['post_init']['positions'] = 'plugin_positions_postinit';
}

// Get the name and the version of the plugin - Needed
function plugin_version_positions() {

	return array (
		'name' => _n('Cartography','Cartographies', 1, 'positions'),
		'version' => '4.0.0',
		'license' => 'GPLv2+',
		'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
		'homepage'=>'https://forge.indepnet.net/projects/show/positions',
		'minGlpiVersion' => '0.84',// For compatibility / no install in version < 0.84
	);

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_positions_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      _e('This plugin requires GLPI >= 0.84', 'positions');
      return false;
   }
   return true;
}


// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_positions_check_config() {
	return true;
}

function plugin_positions_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_positions_profile"][$module])
         &&in_array($_SESSION["glpi_plugin_positions_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

?>