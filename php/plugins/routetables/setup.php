<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Routetables plugin for GLPI
  Copyright (C) 2003-2011 by the Routetables Development Team.

  https://forge.indepnet.net/projects/routetables
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Routetables.

  Routetables is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Routetables is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Routetables. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_routetables() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['routetables'] = true;
   $PLUGIN_HOOKS['change_profile']['routetables'] = array('PluginRoutetablesProfile', 'changeProfile');
   $PLUGIN_HOOKS['assign_to_ticket']['routetables'] = true;

   if (Session::getLoginUserID()) {

      Plugin::registerClass('PluginRoutetablesRoutetable', array(
          'document_types' => true,
          'ticket_types' => true,
          'helpdesk_visible_types' => true
      ));

      Plugin::registerClass('PluginRoutetablesProfile', array('addtabon' => 'Profile'));

      // Display a menu entry ?
      if (plugin_routetables_haveRight("routetables", "r")) {
         $PLUGIN_HOOKS['menu_entry']['routetables'] = 'front/routetable.php';
         $PLUGIN_HOOKS['submenu_entry']['routetables']['search'] = 'front/routetable.php';
      }

      if (plugin_routetables_haveRight("routetables", "w")) {
         $PLUGIN_HOOKS['submenu_entry']['routetables']['add'] = 'front/routetable.form.php?new=1';
         $PLUGIN_HOOKS['use_massive_action']['routetables'] = 1;
      }

      // Import from Data_Injection plugin
      $PLUGIN_HOOKS['migratetypes']['routetables'] = 'plugin_datainjection_migratetypes_routetables';

      if (class_exists('PluginRoutetablesRoutetable_Item')) { // only if plugin activated
         $PLUGIN_HOOKS['pre_item_purge']['routetables'] =
                 array('Profile' => array('PluginRoutetablesProfile', 'purgeProfiles'));
         $PLUGIN_HOOKS['plugin_datainjection_populate']['routetables'] = 'plugin_datainjection_populate_routetables';
      }

      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['routetables'] = 'plugin_routetables_postinit';
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_routetables() {

   return array(
       'name' => _n('Routing table', 'Routing tables', 2, 'routetables'),
       'version' => '1.5.0',
       'license' => 'GPLv2+',
       'oldname' => 'routetable',
       'author' => 'Xavier Caillaud, Franck Waechter',
       'homepage' => 'https://forge.indepnet.net/wiki/routetables',
       'minGlpiVersion' => '0.84', // For compatibility / no install in version < 0.80
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_routetables_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '0.84', 'lt') || version_compare(GLPI_VERSION, '0.85', 'ge')) {
      _e('This plugin requires GLPI >= 0.84', 'routetables');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_routetables_check_config() {
   return true;
}

function plugin_routetables_haveRight($module, $right) {
   $matches = array(
       "" => array("", "r", "w"), // ne doit pas arriver normalement
       "r" => array("r", "w"),
       "w" => array("w"),
       "1" => array("1"),
       "0" => array("0", "1"), // ne doit pas arriver non plus
   );
   if (isset($_SESSION["glpi_plugin_routetables_profile"][$module])
           && in_array($_SESSION["glpi_plugin_routetables_profile"][$module], $matches[$right]))
      return true;
   else
      return false;
}

function plugin_datainjection_migratetypes_routetables($types) {
   $types[5100] = 'PluginRoutetablesRoutetable';
   return $types;
}

?>