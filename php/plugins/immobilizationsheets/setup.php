<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Immobilizationsheets plugin for GLPI
  Copyright (C) 2003-2011 by the Immobilizationsheets Development Team.

  https://forge.indepnet.net/projects/immobilizationsheets
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Immobilizationsheets.

  Immobilizationsheets is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Immobilizationsheets is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Immobilizationsheets. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_immobilizationsheets() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['immobilizationsheets'] = true;
   $PLUGIN_HOOKS['change_profile']['immobilizationsheets']
           = array('PluginImmobilizationsheetsProfile', 'changeProfile');

   //Clean Plugin on Profile delete
   $PLUGIN_HOOKS['pre_item_purge']['immobilizationsheets']
           = array('Profile' => array('PluginImmobilizationsheetsProfile', 'purgeProfiles'));

   if (Session::getLoginUserID()) {

      Plugin::registerClass('PluginImmobilizationsheetsProfile', array('addtabon' => 'Profile'));

      if (plugin_immobilizationsheets_haveRight("immobilizationsheets", "r")) {
         $PLUGIN_HOOKS['menu_entry']['immobilizationsheets'] = 'front/immobilizationsheet.php';
         $PLUGIN_HOOKS['submenu_entry']['immobilizationsheets']['config'] = 'front/config.form.php';
         $PLUGIN_HOOKS['use_massive_action']['immobilizationsheets'] = 1;
      }

      // Config page
      if (plugin_immobilizationsheets_haveRight("immobilizationsheets", "r")
              || Session::haveRight("config", "w")) {
         $PLUGIN_HOOKS['config_page']['immobilizationsheets'] = 'front/config.form.php';
      }

      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['immobilizationsheets'] = 'plugin_immobilizationsheets_postinit';
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_immobilizationsheets() {

   return array(
       'name' => _n('Immobilization sheet', 'Immobilization sheets', 2, 'immobilizationsheets'),
       'version' => '1.6.0',
       'license' => 'GPLv2+',
       'oldname' => 'immo',
       'author' => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
       'homepage' => 'https://forge.indepnet.net/projects/show/immobilizationsheets',
       'minGlpiVersion' => '0.84', // For compatibility / no install in version < 0.84
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_immobilizationsheets_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '0.84', 'lt') || version_compare(GLPI_VERSION, '0.85', 'ge')) {
      _e('This plugin requires GLPI >= 0.84', 'immobilizationsheets');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_immobilizationsheets_check_config() {
   return true;
}

function plugin_immobilizationsheets_haveRight($module, $right) {
   $matches = array(
       "" => array("", "r", "w"), // ne doit pas arriver normalement
       "r" => array("r", "w"),
       "w" => array("w"),
       "1" => array("1"),
       "0" => array("0", "1"), // ne doit pas arriver non plus
   );
   if (isset($_SESSION["glpi_plugin_immobilizationsheets_profile"][$module])
           && in_array($_SESSION["glpi_plugin_immobilizationsheets_profile"][$module], $matches[$right]))
      return true;
   else
      return false;
}

?>