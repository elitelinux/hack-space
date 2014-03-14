<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Ideabox plugin for GLPI
 Copyright (C) 2003-2011 by the Ideabox Development Team.

 https://forge.indepnet.net/projects/ideabox
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Ideabox.

 Ideabox is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Ideabox is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Ideabox. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$idea=new PluginIdeaboxIdeabox();

if (isset($_POST["add"])) {

	$idea->check(-1,'w',$_POST);
   $newID=$idea->add($_POST);
	Html::back();
	
} else if (isset($_POST["delete"])) {

	$idea->check($_POST['id'],'w');
   $idea->delete($_POST);
	$idea->redirectToList();
	
} else if (isset($_POST["restore"])) {

	$idea->check($_POST['id'],'w');
   $idea->restore($_POST);
	$idea->redirectToList();
	
} else if (isset($_POST["purge"])) {

	$idea->check($_POST['id'],'w');
   $idea->delete($_POST,1);
	$idea->redirectToList();
	
} else if (isset($_POST["update"])) {

	$idea->check($_POST['id'],'w');
   $idea->update($_POST);
	Html::back();
	
} else {

	$idea->checkGlobal("r");
	
	if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
		Html::header(PluginIdeaboxIdeabox::getTypeName(2),'',"plugins","ideabox");
	} else {
		Html::helpHeader(PluginIdeaboxIdeabox::getTypeName(2));
	}
	
	$idea->showForm($_GET["id"]);
	
	if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
		Html::footer();
	} else {
		Html::helpFooter();
	}
}

?>