<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Dennis Ploeger <dennis.ploeger@getit.de>
// Purpose of file: Setup file for RackView-plugin
// ----------------------------------------------------------------------

require_once 'inc/common.function.php';

/**
 * Hook when object is deleted
 *
 * @param commonDBTM $item Item to be deleted
 */

function plugin_item_purge_rackview($item) {

    global $DB;

    // Delete possible mounts for this item

    $sql = sprintf(
        'DELETE FROM glpi_plugin_rackview_mount ' .
        'WHERE object_type= \'%s\' ' .
        'AND object_id = %d',
        $item->getType(),
        $item->getID()
    );

    $DB->query($sql);

}

/**
 * Initialize plugin
 */
function plugin_init_rackview() {

    global $PLUGIN_HOOKS;

    Plugin::registerClass(
        "PluginRackviewRack",
        array(
            "addtabon" => rackview_get_supported_types()
        )
    );

    $PLUGIN_HOOKS['change_profile']['rackview'] =
        'plugin_change_profile_rackview';

    $PLUGIN_HOOKS['add_css']['rackview'] = array(
        "css/cssTableGenerator.css",
        "css/rackview.css"
    );

    $PLUGIN_HOOKS['item_purge']['rackview'] = array();

    foreach (rackview_get_supported_types() as $type) {

        $PLUGIN_HOOKS['item_purge']['rackview'][$type] =
            'plugin_item_purge_rackview';

    }

    $PLUGIN_HOOKS['add_javascript']['rackview'] = 'js/rackview.js';

    if (Session::getLoginUserID()) {

        if (plugin_rackview_haveRight('rack', 'r')) {

            $PLUGIN_HOOKS['menu_entry']['rackview'] = 'index.php';
            $PLUGIN_HOOKS['submenu_entry']['rackview']['search'] =
                'index.php';

            $PLUGIN_HOOKS['submenu_entry']['rackview']['summary'] =
                'front/rackSummary.php';

        }

        if (plugin_rackview_haveRight('rack', 'w')) {

            $PLUGIN_HOOKS['submenu_entry']['rackview']['add'] =
                'front/rack.form.php?new=1';

        }

    }

    $PLUGIN_HOOKS['use_massive_action']['rackview']=1;

    $PLUGIN_HOOKS['csrf_compliant']['rackview'] = true;

}

/**
 * Return information about plugin
 *
 * @return array:string Informational array
 */
function plugin_version_rackview() {

   return array('name'           => 'Rackview',
                'version'        => '1.0.1',
                'author'         => 'Dennis Plöger <dennis.ploeger@getit.de>',
                'license'        => 'GPLv2+',
                'homepage'       => 'http://www.getit.de',
                'minGlpiVersion' => '0.84');
}

/**
 * Check prerequisites.
 *
 * @return boolean Can plugin be installed?
 */
function plugin_rackview_check_prerequisites() {

   if (version_compare(GLPI_VERSION,'0.84','lt')) {
      echo "This plugin requires GLPI >= 0.84";
      return false;
   }
   return true;
}

/**
 * Configuration checks
 *
 * @return boolean Is the GPLI-configuration valid for the plugin
 */
function plugin_rackview_check_config() {

    return true;

}

/**
 * Check, if user has a specific module right.
 *
 * @param  string $module Module name
 * @param  string $right  Right
 * @return bool           If the user has the right
 */

function plugin_rackview_haveRight($module,$right){

    $matches=array(
        ""  => array("","r","w"),
        "r" => array("r","w"),
        "w" => array("w"),
        "1" => array("1"),
        "0" => array("0","1"),
    );

    if (
        isset($_SESSION["glpi_plugin_rackview_profile"][$module]) &&
        in_array(
            $_SESSION["glpi_plugin_rackview_profile"][$module],
            $matches[$right]
        )
    ) {

        return true;

    } else {
        return false;

    }
}