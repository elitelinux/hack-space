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
// Purpose of file: Hooks file for RackView-plugin
// ----------------------------------------------------------------------

require_once 'inc/common.function.php';

/**
 * Hook to be called when security profile is changed/applied
 */

function plugin_change_profile_rackview() {

    if (Session::haveRight('computer', 'w')) {
        $_SESSION["glpi_plugin_rackview_profile"] = array(
            'object' => 'w',
            'rack'=>'w'
        );

    } else if (Session::haveRight(COMPUTER_TYPE,'r')) {
        $_SESSION["glpi_plugin_rackview_profile"] = array(
            'object' => 'r',
            'rack'=>'r'
        );
    } else {
        unset($_SESSION["glpi_plugin_example_profile"]);
    }

}

function plugin_rackview_getAddSearchOptions($itemtype) {

    global $LANG;

    $opt = array();

    if (in_array($itemtype, rackview_get_supported_types())) {

        $itemTypeId = rackview_get_itemtypeid();

        $opt[$itemTypeId] = array();

        $opt[$itemTypeId]['table']  = 'glpi_plugin_rackview_objectmounts';
        $opt[$itemTypeId]['field'] = 'mounts';
        $opt[$itemTypeId]['linkfield'] = 'id';
        $opt[$itemTypeId]['name'] = $LANG['plugin_rackview']['label_mounted'];
        $opt[$itemTypeId]['massiveaction'] = false;
        $opt[$itemTypeId]['joinparams'] = array(
            'condition' => "AND NEWTABLE.type = '" . $itemtype ."'"
        );
    }

    return $opt;

}

/**
 * Return item description
 *
 * @param $itemtype
 * @param $ID
 * @param $data
 * @param $num
 * @return string
 */

function plugin_rackview_giveItem($itemtype,$ID,$data,$num) {

    $searchopt = Search::getOptions($itemtype);

    $NAME = "ITEM_";

    $unit = '';
    if (isset($searchopt[$ID]['unit'])) {
        $unit = $searchopt[$ID]['unit'];
    }

    if (isset($searchopt[$ID]["itemlink_type"])) {
        $link = Toolbox::getItemTypeFormURL($searchopt[$ID]["itemlink_type"]);
    } else {
        $link = Toolbox::getItemTypeFormURL($itemtype);
    }

    $out = "";

    $rackDisplay = "";

    if ($ID == 1) {

        $rack = new PluginRackviewRack();
        $rack->getFromDB($data['id']);

        $rackDisplay = '<div style="float:left; margin-right: 1em" ' .
            'class="CSSTableGenerator">' .
            $rack->buildRack(true) .
            '</div>';

    }

    $sub = "";

    if (isset($data[$NAME.$num."_2"])) {

        $sub = $data[$NAME.$num."_2"];

    }

    $out .= "<a id='".$itemtype."_".$sub."' href=\"".$link;
    $out .= (strstr($link,'?') ?'&amp;' :  '?');
    $out .= 'id='.$sub;

    if (isset($searchopt[$ID]['forcetab'])) {
        $out .= "&amp;forcetab=".$searchopt[$ID]['forcetab'];
    }
    $out .= "\">".$data[$NAME.$num].$unit;

    if (
        ($_SESSION["glpiis_ids_visible"] || empty($data[$NAME.$num])) &&
        (isset($data[$NAME.$num."_2"]))
    ) {
        $out .= " (".$data[$NAME.$num."_2"].")";
    }
    $out .= "</a>";

    if ($rackDisplay != "") {

        $out .= HTML::showToolTip(
            $rackDisplay,
            array(
                'applyto' => $itemtype."_".$sub,
                'display' => false
            )
        );

    }

    return $out;

}

/**
 * Install the plugin
 *
 * @return boolean Was the installation successful?
 */

function plugin_rackview_install() {

    global $DB;

    // Run SQL commands

    if (
        (!TableExists("glpi_plugin_rackview_racks")) &&
        (!TableExists("glpi_plugin_rackview_mount")) &&
        (!TableExists("glpi_plugin_rackview_object"))
    ) {

        // Read in SQL-commands

        $DB->runFile(dirname(__FILE__) . "/db/mysql.1.1.sql") or
            Html::displayErrorAndDie(
                "Error installing RackView plugin ". $DB->error()
            );

    }

    return true;

}

/**
 * Uninstall the plugin
 *
 * @return boolean Was the uninstallation successful?
 */

function plugin_rackview_uninstall() {
    global $DB;

    // Remove tables

    foreach (array("racks", "mount", "object") as $table) {

        if (TableExists("glpi_plugin_rackview_" . $table)) {

            $DB->query("drop table glpi_plugin_rackview_" . $table) or
                print "Cannot remove database table glpi_plugin_rackview_" .
                    $table;

        }

    }

    // Remove Views

    foreach (array("objectmounts") as $table) {

        if (TableExists("glpi_plugin_rackview_" . $table)) {

            $DB->query("drop view glpi_plugin_rackview_" . $table) or
                print "Cannot remove database view glpi_plugin_rackview_" .
                    $table;

        }

    }

    return true;

}



/**
 * Add the rackview massive actions
 *
 * @param $type  Object type
 * @return array Additional Massive Actions
 */

function plugin_rackview_MassiveActions($type){

    global $LANG;

    if ($type == "PluginRackviewRack") {

        return array(
            'plugin_rackview_print' =>
                $LANG['plugin_rackview']['massiveaction_print']
        );

    }

    return array();

}

/**
 * Display action button when massive action is selected
 *
 * @param $param Object Type and selected action
 */

function plugin_rackview_MassiveActionsDisplay($param) {
    global $LANG;

    if ($param['itemtype'] == 'PluginRackviewRack') {

        if ($param['action'] == 'plugin_rackview_print') {

            echo "&nbsp;<input type=\"submit\" name=\"masssiveaction\"
            class=\"submit\" value=\"".$LANG['buttons'][2]."\">";

        }

    }

}

/**
 * Process massive actions
 *
 * @param $data Massive action data
 */

function plugin_rackview_MassiveActionsProcess($data){

    if ($data["itemtype"] == 'PluginRackviewRack') {

        if ($data['action'] == 'plugin_rackview_print') {

            if (count($data["item"]) == 0) {

                // No items were selected. Escape.

                return;

            }

            $param = array("action=print");

            foreach (array_keys($data["item"]) as $item) {

                $param[] = "rack[]=$item";

            }

            Html::redirect(
                GLPI_ROOT.'/plugins/rackview/front/rackSummary.php?'.
                join("&", $param)
            );

        }

    }

}
