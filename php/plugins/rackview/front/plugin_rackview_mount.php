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
// Purpose of file: Mount object
// ----------------------------------------------------------------------

// Start GLPI

include ('../../../inc/includes.php');
include (GLPI_ROOT."/plugins/rackview/hook.php");

// Check sanity

$errors = array();

// Was an existing rack specified?

$rack_id = -1;

if (!is_numeric($_POST['plugin_rackview_racks_id'])) {

    $errors[] = $LANG['plugin_rackview']['error_invalidrackid'];

} else {

    $rack_id = intval($_POST['plugin_rackview_racks_id']);

    $table = getTableForItemType('PluginRackviewRack');

    $object = $DB->request(
        $table,
        array(
            "id" => $_POST['plugin_rackview_racks_id']
        )
    );

    if ($object->numrows() == 0) {

        $errors[] = $LANG['plugin_rackview']['error_invalidrackid'];

    }

}

// Was a valid type specified

$valid_types = rackview_get_supported_types();

if (!in_array($_POST['object_type'], $valid_types)) {

    $errors[] = $LANG['plugin_rackview']['error_invalidtype'];

}

// Is the object id numeric?

if (!is_numeric($_POST['object_id'])) {

    $errors[] = $LANG['plugin_rackview']['error_invalidid'];

}

// Try to get the object

$table = getTableForItemType($_POST['object_type']);

$object = $DB->request($table, array("id" => $_POST['object_id']));

if ($object->numrows() == 0) {

    $errors[] = $LANG['plugin_rackview']['error_invalidid'];

}

// Is the starting Unit valid?

if (
    (!is_numeric($_POST['startu'])) ||
    ($_POST['startu'] < 0) ||
    ($_POST['startu'] > 99)
) {

    $errors[] = $LANG['plugin_rackview']['error_invalidstartu'];

}

// Is the depth field valid?

if(
    (!is_numeric($_POST['depth'])) ||
    (!in_array($_POST['depth'], array(0, 1, 2)))
) {

    $errors[] = $LANG['plugin_rackview']['error_invaliddepth'];

}

// Is the horizontal field valid?

if(
    (!is_numeric($_POST['horizontal'])) ||
    (!in_array($_POST['horizontal'], array(0, 1, 2, 3)))
) {

    $errors[] = $LANG['plugin_rackview']['error_invalidhorizontal'];

}

// Was a custom size specified

$mount_size = "null";

if (
    (!isset($_POST['use_default'])) &&
    (!is_numeric($_POST['mount_size']))
) {

    $errors[] = $LANG['plugin_rackview']['error_invalidmountsize'];

} else if (!isset($_POST['use_default'])) {

    $mount_size = $_POST['mount_size'];

}

// Is the action valid

if (
    (!isset($_POST['mount_action'])) || (
        (!in_array($_POST['mount_action'], array('mount', 'remount', 'unmount')))
    )
){

    $errors[] = $LANG['plugin_rackview']['error_invalidmountaction'];

}

// Carry out the action

if (count($errors) == 0) {

    if ($_POST['mount_action'] == 'mount') {

        // Insert mount into database

        $sql = sprintf(
            "INSERT INTO glpi_plugin_rackview_mount " .
                "(object_type, object_id, rack_id, startu, horizontal," .
                "depth, mount_size, description) " .
                "VALUES ('%s', %d, %d, %d, %d, %d, %s, '%s')",
            $_POST['object_type'],
            $_POST['object_id'],
            $rack_id,
            $_POST['startu'],
            $_POST['horizontal'],
            $_POST['depth'],
            $mount_size,
            $_POST['description']
        );

        $result = $DB->query($sql);

        if (!$result) {

            // Error inserting.

            $errors[] = $LANG['plugin_rackview']['error_mounting'];

        }

    } else if ($_POST['mount_action'] == 'remount') {

        // Update mount

        $sql = sprintf(
            "UPDATE glpi_plugin_rackview_mount " .
                "SET rack_id = %d, " .
                "startu = %d, " .
                "horizontal = %d, " .
                "depth = %d, " .
                "mount_size = %s, " .
                "description = '%s' " .
                "WHERE id = %d",
            $rack_id,
            $_POST['startu'],
            $_POST['horizontal'],
            $_POST['depth'],
            $mount_size,
            $_POST['description'],
            $_POST['mount_id']
        );

        $result = $DB->query($sql);

        if (!$result) {

            // Error inserting.

            $errors[] = $LANG['plugin_rackview']['error_remounting'];

        }

    } else if ($_POST['mount_action'] == 'unmount') {

        // Delete mount

        if (!isset($_POST['mount_id'])) {

            $errors[] = $LANG['plugin_rackview']['error_invalidmountid'];

        } else {

            $sql = sprintf(
                "DELETE FROM glpi_plugin_rackview_mount " .
                    "WHERE id = %d",
                $_POST['mount_id']
            );

            $result = $DB->query($sql);

            if (!$result) {

                // Error deleting.

                $errors[] = $LANG['plugin_rackview']['error_unmounting'];

            }

        }

    }

}

if (count($errors) > 0) {

    $_SESSION["MESSAGE_AFTER_REDIRECT"] =
        join("<br />", $errors);

}

Html::back();