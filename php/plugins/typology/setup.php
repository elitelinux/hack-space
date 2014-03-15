<?php
/*
 -------------------------------------------------------------------------
 Typology plugin for GLPI
 Copyright (C) 2006-2012 by the Typology Development Team.

 https://forge.indepnet.net/projects/typology
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Typology.

 Typology is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Typology is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Typology. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// Init the hooks of the plugins -Needed
function plugin_init_typology() {
   global $PLUGIN_HOOKS;
   
   $PLUGIN_HOOKS['add_css']['typology']        = 'typology.css';
   $PLUGIN_HOOKS['csrf_compliant']['typology'] = true;
   $PLUGIN_HOOKS['change_profile']['typology'] = array('PluginTypologyProfile','changeProfile');

   if (Session::getLoginUserID()) {

      Plugin::registerClass('PluginTypologyProfile',
         array('addtabon' => 'Profile'));
      
      Plugin::registerClass('PluginTypologyTypology', array(
         'notificationtemplates_types' => true,
      ));
      // Display a menu entry ?
      if (plugin_typology_haveRight("typology","r")) {
         // menu entry
         $PLUGIN_HOOKS['menu_entry']['typology'] = 'front/typology.php';
         // search link
         $PLUGIN_HOOKS['submenu_entry']['typology']['search'] = 'front/typology.php';
         $PLUGIN_HOOKS['redirect_page']['typology'] = 'front/typology.form.php';
      }
		
      if (plugin_typology_haveRight("typology","w")) {
         //add link  
         $PLUGIN_HOOKS['submenu_entry']['typology']['add'] = 'front/typology.form.php';
         //use massiveaction in the plugin
         $PLUGIN_HOOKS['use_massive_action']['typology']=1;
         $PLUGIN_HOOKS['redirect_page']['typology'] = 'front/typology.form.php';
      }

      Plugin::registerClass('PluginTypologyRuleTypologyCollection', array(
         'rulecollections_types' => true
      ));
      
      if (class_exists('PluginBehaviorsCommon')) {
         PluginBehaviorsCommon::addCloneType('PluginTypologyRuleTypology','PluginBehaviorsRule');
      }
      
      $PLUGIN_HOOKS['post_init']['typology'] = 'plugin_typology_postinit';
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_typology() {

   return array (
      'name'           => _n('Typology', 'Typologies', 2, 'typology'),
      'version'        => '2.0.0',
      'author'         => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
      'license'        => 'GPLv2+',
      'homepage'       => 'https://forge.indepnet.net/projects/show/typology',
      'minGlpiVersion' => '0.84');// For compatibility / no install in version < 0.84

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_typology_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      _e('This plugin requires GLPI >= 0.84', 'typology');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_typology_check_config() {
   return true;
}

//general right
function plugin_typology_haveRight($module,$right) {
   $matches=array(
      ""  => array("","r","w"), // ne doit pas arriver normalement
      "r" => array("r","w"),
      "w" => array("w"),
      "1" => array("1"),
      "0" => array("0","1"), // ne doit pas arriver non plus
   );
   if (isset($_SESSION["glpi_plugin_typology_profile"][$module])
      && in_array($_SESSION["glpi_plugin_typology_profile"][$module],$matches[$right]))
      return true;
   else return false;
}

?>