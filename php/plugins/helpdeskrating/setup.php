<?php

/************************************************************************************************
 *
 * File: setup.php
 *
 ************************************************************************************************
 *
 * Helpdeskrating - A Plugin for GLPI Software
 * Copyright (c) 2010-2013 Christian Deinert
 *
 * http://sourceforge.net/projects/helpdeskrating/
 *
 ************************************************************************************************
 *
 * LICENSE
 *
 *     This file is part of the GLPI Plugin Helpdeskrating.
 *
 *     The GLPI Plugin Helpdeskrating is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Lesser Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     The GLPI Plugin Helpdeskrating is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Lesser Public License for more details.
 *
 *     You should have received a copy of the GNU Lesser Public License
 *     along with the GLPI Plugin Helpdeskrating.  If not, see <http://www.gnu.org/licenses/>.
 *
 ************************************************************************************************/

/**
 * Init the hooks of the plugins -Needed
 */
function plugin_init_helpdeskrating()
{
    global $PLUGIN_HOOKS, $CFG_GLPI;
    $types = array(
        'Ticket'
    );
    Plugin::registerClass('PluginHelpdeskratingHelpdeskrating', array(
        'notificationtemplates_types' => true,
        'addtabon' => $types
    ));
    
    // Display a menu entry ?
    if (isset($_SESSION["glpi_plugin_helpdeskrating_profile"])) { // Right set in change_profile hook
                                                                  // Link to Statistic
        $PLUGIN_HOOKS['menu_entry']['helpdeskrating'] = 'front/statistic.php?user=' . $_SESSION['glpiID'];
        $PLUGIN_HOOKS["helpdesk_menu_entry"]['helpdeskrating'] = true;
    }
    
    $_SessionHaveRight = Session::haveRight('update_ticket', 1);
    
    if ($_SessionHaveRight) {
        // Link to Statistic
        $PLUGIN_HOOKS['menu_entry']['helpdeskrating'] = 'front/statistic.php?user=' . $_SESSION['glpiID'];
        $PLUGIN_HOOKS["helpdesk_menu_entry"]['helpdeskrating'] = true;
    }
    
    // redirect
    // Simple redirect : ttp://localhost/glpi/index.php?redirect=plugin_helpdeskrating
    $PLUGIN_HOOKS['redirect_page']['helpdeskrating'] = 'helpdeskrating.form.php';
        // CSRF compliance : All actions must be done via POST and forms closed by Html::closeForm();
    $PLUGIN_HOOKS['csrf_compliant']['helpdeskrating'] = true;
}

/**
 * Get the name and the version of the plugin - Needed
 */
function plugin_version_helpdeskrating()
{
    return array(
        'name' => 'Plugin Helpdeskrating',
        'version' => '1.1.0',
        'license' => 'LGPLv3+',
        'author' => 'Christian Deinert',
        'homepage' => 'http://sourceforge.net/projects/helpdeskrating/',
        'minGlpiVersion' => '0.84'
    ); // For compatibility / no install in version < 0.84
}

/**
 * Check prerequisites before install : may print errors or add to message after redirect - Optional
 */
function plugin_helpdeskrating_check_prerequisites()
{
    // Strict version check (could be less strict, or could allow various version)
    if (version_compare(GLPI_VERSION, '0.84', 'lt')) {
        echo "This plugin requires GLPI >= 0.84";
        return false;
    } else {
        return true;
    }
}

/**
 * Check configuration process for plugin : need to return true if succeeded
 *
 * Can display a message only if failure and $verbose is true
 */
function plugin_helpdeskrating_check_config($verbose = false)
{
    return true;
}

?>
