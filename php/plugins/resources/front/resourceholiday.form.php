<?php
/*
 * @version $Id: resourceholiday.form.php 480 2012-11-09 tsmr $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   //from central
   Html::header(PluginResourcesResource::getTypeName(2),'',"plugins","resources");
} else {
   //from helpdesk
   Html::helpHeader(PluginResourcesResource::getTypeName(2));
}

if(!isset($_GET["id"])) $_GET["id"] = "";

$holiday=new PluginResourcesResourceHoliday();

if (isset($_POST["addholidayresources"]) && $_POST["plugin_resources_resources_id"]!=0) {
  
   $holiday->add($_POST);
   Html::back();

} else if (isset($_POST["updateholidayresources"]) && $_POST["plugin_resources_resources_id"]!=0) {
  
   $holiday->update($_POST);
   Html::back();

} else if (isset($_POST["deleteholidayresources"]) && $_POST["plugin_resources_resources_id"]!=0) {
  
   $holiday->delete($_POST,1);
   $holiday->redirectToList();

} else {

	if ($holiday->canView() || Session::haveRight("config","w")) {
		$holiday->showForm($_GET["id"], array());
	}
}

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}

?>