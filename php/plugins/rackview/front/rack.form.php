<?php
/*
 * @version $Id: room.form.php 37 2009-01-06 18:41:29Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

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
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Dennis Plöger <dennis.ploeger@getit.de>
// Purpose of file: Rack CRUD form
// ----------------------------------------------------------------------

$NEEDED_ITEMS=array('plugin');

include ('../../../inc/includes.php');

if(!isset($_GET["id"])) $_GET["id"] = "-1";

if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$rack=new PluginRackviewRack();

if (isset($_POST['add'])){

    $rack->check(-1, 'w', $_POST);

    $newId = $rack->add($_POST);
    Html::redirect($_SERVER['HTTP_REFERER']);


} else if ((isset($_POST['delete'])) || (isset($_POST['purge']))) {

    $rack->check($_POST['id'], 'w');

    $rack->delete($_POST);

    Html::redirect($CFG_GLPI['root_doc'] . '/plugins/rackview/index.php');

} else if (isset($_POST['update'])) {

    $rack->check($_POST['id'], 'w');

    $rack->update($_POST);

    Html::redirect($_SERVER['HTTP_REFERER']);

} else {

	$rack->check($_GET["id"],'r');

	if (!isset($_SESSION['glpi_tab'])) $_SESSION['glpi_tab']=1;
	if (isset($_GET['tab'])) {
		$_SESSION['glpi_tab']=$_GET['tab'];
	}

	Html::header(
        $LANG['plugin_rackview']['title'],
        'plugins',
        'rackview'
    );

    print '<table>';
    print '<tr valign="top">';
    print '<td>';

    if (
        (isset($_GET['id'])) &&
        ($_GET['id'] != -1)
    ) {

        print '<div class="CSSTableGenerator">';
        $rack->showRack(false);
        print '</div>';

    }

    print '</td>';
    print '<td>';

	$rack->showForm($_GET["id"]);

    print '</td>';
    print '</tr>';
    print '</table>';

	Html::footer();
}