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
// Purpose of file: Display rack summary
// ----------------------------------------------------------------------

// Start GLPI

include ('../../../inc/includes.php');

plugin_rackview_haveRight('rackview',"r");

$printMode = false;

if (
    (array_key_exists('action', $_GET)) &&
    ($_GET['action'] == 'print')
) {

    print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
             "http://www.w3.org/TR/html4/loose.dtd">';

    print "<html>" .
        "<head>" .
        "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
        "<title>" .
        $LANG['plugin_rackview']['title'] .
        "</title>" .
        "<link rel='stylesheet' type='text/css' href='" .
        GLPI_ROOT .
        "/plugins/rackview/css/rackview.css' media='screen'>" .
        "<link rel='stylesheet' type='text/css' href='" .
        GLPI_ROOT .
        "/plugins/rackview/css/cssTableGenerator.css' media='screen'>" .
        "<link rel='stylesheet' type='text/css' href='" .
        GLPI_ROOT .
        "/plugins/rackview/css/rackview.print.css' media='print'>" .
        "</head><body>";

    $printMode = true;

} else {

    Html::header(
        $LANG['plugin_rackview']['title'],
        $_SERVER['PHP_SELF'],
        'plugins',
        'rackview'
    );

}

print '<div class="rackview_summary">';

if (!$printMode) {

    print '<h1>' .
        $LANG['plugin_rackview']['label_summary_title'] .
        '</h1>';

    print '<h2>' .
        $LANG['plugin_rackview']['label_summary_locations'] .
        '</h2>';

}

// Get all locations

$tableRacks = getTableForItemType('PluginRackviewRack');
$tableLocations = getTableForItemType('Location');

$dBLocations = $DB->request(
    array(
        $tableRacks,
        $tableLocations
    ),
    array(
        'FIELDS' => array(
            $tableLocations . '.completename',
            $tableLocations . '.id'
        ),
        'ORDER' => array(
            'completename'
        ),
        'FKEY' => array(
            $tableRacks => 'locations_id',
            $tableLocations => 'id'
        )
    )
);

$locations = array();

if ($dBLocations->numrows() > 0) {

    while ($location = $dBLocations->next()) {

        if (!in_array($location, $locations)) {
            $locations[] = $location;
        }

    }

}

if (! $printMode) {

    print '<ul>';

    foreach ($locations as $location) {

        print '<li>' .
            '<a href="#' .
            urlencode($location['completename']) .
            '">' .
            htmlentities($location['completename']) .
            '</a>' .
            '</li>';

    }

    print '</ul>';

}

$rackCount = 0;

foreach ($locations as $location) {

    if (! $printMode) {

        print '<a name="' .
            urlencode($location['completename']) .
            '"><h2>' .
            htmlentities($location['completename']) .
            '</h2></a>';

        print '<div class="rackview_rackSummaryLine"><div>';

    }

    $dbRacks = $DB->request(
        $tableRacks,
        array(
            'locations_id' => $location['id'],
            'ORDER' => 'name'
        )
    );

    $printBreak = false;

    while ($dbRack = $dbRacks->next()) {

        if ($printBreak) {

            print '<div class="pagebreak"></div>';

            $printBreak = false;

        }

        if (
            (array_key_exists('rack', $_GET)) &&
            (!in_array($dbRack['id'], $_GET['rack']))
        ) {

            continue;

        }

        $rack = new PluginRackviewRack();
        $rack->getFromDB($dbRack['id']);

        print '<div class="rackview_rackSummary CSSTableGenerator">' .
            $rack->buildRack(false) .
            '</div>';

        $rackCount++;

        if ($rackCount == 3) {

            $printBreak = true;

            $rackCount = 0;

        }

    }

    if (!$printMode) {

        print '</div></div>';

    }

}

print '</div>';

if (
    (array_key_exists('action', $_GET)) &&
    ($_GET['action'] == 'print')
) {

    print '<script type="text/javascript">window.print();</script>';
    print '</body>';

} else {

    Html::footer();

}