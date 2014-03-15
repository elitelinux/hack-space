<?php
/*
 *
 -------------------------------------------------------------------------
 Themes
 Copyright (C) 2012 by iizno.

 https://forge.indepnet.net/projects/themes
 -------------------------------------------------------------------------

 LICENSE

 This file is part of themes.

 themes is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 themes is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with themes. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// Original Author of file: Jérôme Ansel <jerome@ansel.im>
// ----------------------------------------------------------------------

if(!defined('PLUGIN_THEMES_UPLOAD_DIR')) {
      define("PLUGIN_THEMES_UPLOAD_DIR", GLPI_PLUGIN_DOC_DIR."/themes");
}

// Init the hooks of the plugins -Needed
function plugin_init_themes() {
   global $PLUGIN_HOOKS;
   
   $PLUGIN_HOOKS['csrf_compliant']['themes'] = true;

   $PLUGIN_HOOKS['change_profile']['themes']  = 
      array('PluginThemesProfile','changeProfile');

   $plugin = new Plugin();
   if($plugin->isInstalled("themes") 
      && $plugin->isActivated("themes")) {
      
      Plugin::registerClass('PluginThemesProfile', array(
         'addtabon' => 'Profile'
      ));

      Plugin::registerClass('PluginThemesTheme', array(
         'addtabon' => 'Preference'
      ));
      
      // Default new css and js file.
      if(plugin_themes_haveRight('themes', 'w')) {
         $PLUGIN_HOOKS['menu_entry']["themes"]="front/themes.php";
         $PLUGIN_HOOKS['submenu_entry']['themes']['search'] = 'front/themes.php';
         $PLUGIN_HOOKS['submenu_entry']['themes']['add'] = 'front/themes.form.php';
      }
      
      /*** Search for a default theme to display***/
      $activeTheme = new PluginThemesTheme();
      $activeTheme->getFromDB(PluginThemesTheme::getUserTheme());

      if($activeTheme->fields['name'] != "GLPI") {
         $PLUGIN_HOOKS['add_css']['themes'] =
            'front/getfile.php?theme_id='.$activeTheme->fields['id'].
            '&type=css&file='.$activeTheme->fields['name'].'.css';
         $PLUGIN_HOOKS['add_javascript']['themes'] =
            'front/getfile.php?theme_id='.$activeTheme->fields['id'].
            '&type=js&file='.$activeTheme->fields['name'].'.js';
      }

   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_themes() {
   return array('name'           => __('Themes manager', 'themes'),
                'version'        => '1.2.0',
                'author'         => '<a href="mailto:ansel.jerome@gmail.com">Jérôme Ansel</a>',
                'homepage'       => 'https://forge.indepnet.net/projects/themes',
                'license'        => 'GPLv2+',
                'minGlpiVersion' => '0.84');
}

// Optional : check prerequisites before install : 
// may print errors or add to message after redirect
function plugin_themes_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.84','lt')
       || version_compare(GLPI_VERSION,'0.85','ge')) {
      echo __('This plugin requires GLPI >= 0.84 and GLPI < 0.85', 'themes');
      
      return false;
   }
   return true;
}

function plugin_themes_check_config() {
   return true;
}

function plugin_themes_haveRight($module,$right) {
   $matches=array(""  => array("", "r", "w"), // should never happend; 
                  "r" => array("r", "w"),
                  "w" => array("w"),
                  "1" => array("1"),
                  "0" => array("0", "1")); // should never happend;

   if (  isset($_SESSION["glpi_plugin_themes_profile"][$module])
         && in_array($_SESSION["glpi_plugin_themes_profile"][$module],
         $matches[$right])) {
      return true;
   } else {
      return false;
   }
}