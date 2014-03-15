<?php
/*
 * @version $Id: setup.templates.php 480 2012-11-09 tsmr $
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

$resource=new PluginResourcesResource();

if($resource->canView() || Session::haveRight("config","w")) {

	if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
		Html::header(PluginResourcesResource::getTypeName(2),'',"plugins","resources");
	} else {
		Html::helpHeader(PluginResourcesResource::getTypeName(2));
	}
	
   $resource->listOfTemplates(
               $CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php",$_GET["add"]);

	if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
		Html::footer();
	} else {
		Html::helpFooter();
	}

}

?>