<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Badges plugin for GLPI
 Copyright (C) 2003-2011 by the badges Development Team.

 https://forge.indepnet.net/projects/badges
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of badges.

 Badges is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Badges is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Badges. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$badge=new PluginBadgesBadge();

if (isset($_POST["add"])) {

	$badge->check(-1,'w',$_POST);
   $newID=$badge->add($_POST);
	Html::back();
	
} else if (isset($_POST["delete"])) {

	$badge->check($_POST['id'],'w');
   $badge->delete($_POST);
	$badge->redirectToList();
	
} else if (isset($_POST["restore"])) {

	$badge->check($_POST['id'],'w');
   $badge->restore($_POST);
	$badge->redirectToList();
	
} else if (isset($_POST["purge"])) {

	$badge->check($_POST['id'],'w');
   $badge->delete($_POST,1);
	$badge->redirectToList();
	
} else if (isset($_POST["update"])) {

	$badge->check($_POST['id'],'w');
   $badge->update($_POST);
	Html::back();
	
} else {
   
   $badge->checkGlobal("r");

	$plugin = new Plugin();
	if ($plugin->isActivated("environment"))
		Html::header(PluginBadgesBadge::getTypeName(2),'',"plugins","environment","badges");
	else
		Html::header(PluginBadgesBadge::getTypeName(2),'',"plugins","badges");

	$badge->showForm($_GET["id"]);

	Html::footer();
}

?>