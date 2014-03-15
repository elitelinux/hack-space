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
// Purpose of file: Save object data
// ----------------------------------------------------------------------

// Start GLPI

include ("../../../inc/includes.php");
include (GLPI_ROOT."/plugins/rackview/hook.php");

// Check sanity

$errors = array();

// Is rack-size numeric?

if (!is_numeric($_POST['rackview_size'])) {

    $errors[] = $LANG['plugin_rackview']['error_sizenotnumeric'];

} else {

    $rackview_size = intval($_POST['rackview_size']);

}

if ($rackview_size > 99) {

    // That's one big rack!

    $errors[] = $LANG['plugin_rackview']['error_sizetoolarge'];

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

if (count($errors) == 0) {

    // Check, if object exists

    $object = $DB->request(
        "glpi_plugin_rackview_object",
        array(
            "object_id" => $_POST['object_id'],
            "object_type" => $_POST['object_type']
        )
    );

    if ($object->numrows() > 0) {

        // Update object

        $sql = sprintf(
            "UPDATE glpi_plugin_rackview_object " .
                "SET size = %d " .
                "WHERE object_id = %d " .
                "AND object_type = '%s'",
            $rackview_size,
            $_POST['object_id'],
            $_POST['object_type']
        );

        $result = $DB->query($sql);

        if (!$result) {

            // Error updating.

            $errors[] = $LANG['plugin_rackview']['error_updating'];

        }

    } else {

        // Insert object

        $sql = sprintf(
            "INSERT INTO glpi_plugin_rackview_object " .
                "(object_type, object_id, size) " .
                "VALUES ('%s', %d, %d)",
            $_POST['object_type'],
            $_POST['object_id'],
            $rackview_size
        );

        $result = $DB->query($sql);

        if (!$result) {

            // Error inserting.

            $errors[] = $LANG['plugin_rackview']['error_inserting'];

        }

    }

}

if (count($errors) > 0) {

    $_SESSION["MESSAGE_AFTER_REDIRECT"] =
        join("<br />", $errors);

}

Html::back();