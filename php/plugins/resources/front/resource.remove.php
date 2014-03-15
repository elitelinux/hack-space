<?php
/*
 * @version $Id: resource.remove.php 480 2012-11-09 tynet $
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
	Html::header(PluginResourcesResource::getTypeName(2),'',"plugins","resources");
} else {
	Html::helpHeader(PluginResourcesResource::getTypeName(2));
}

if(empty($_POST["date_end"])) $_POST["date_end"]=date("Y-m-d");

$resource = new PluginResourcesResource();
$checklistconfig = new PluginResourcesChecklistconfig();

if (isset($_POST["removeresources"]) && $_POST["plugin_resources_resources_id"]!=0) {

   $date=date("Y-m-d H:i:s");
   $CronTask=new CronTask();
   $CronTask->getFromDBbyName("PluginResourcesEmployment","ResourcesLeaving");

   $input["id"]= $_POST["plugin_resources_resources_id"];
   $input["date_end"]= $_POST["date_end"];
   if(($_POST["date_end"] < $date)
         || ($CronTask->fields["state"]==CronTask::STATE_DISABLE)){
      $input["is_leaving"]= "1";
   } else {
      $input["is_leaving"]= "0";
   }
   $input["plugin_resources_leavingreasons_id"] = $_POST["plugin_resources_leavingreasons_id"];
   $input["withtemplate"]= "0";
   $input["users_id_recipient_leaving"]= Session::getLoginUserID();
   $input['send_notification']=1;
   $resource->update($input);

   //test it
   $resource->getFromDB($_POST["plugin_resources_resources_id"]);
   $resources_checklist=PluginResourcesChecklist::checkIfChecklistExist($_POST["plugin_resources_resources_id"]);
   if (!$resources_checklist) {
      $checklistconfig->addChecklistsFromRules($resource,
                                                PluginResourcesChecklist::RESOURCES_CHECKLIST_OUT);
   }
	
	Session::addMessageAfterRedirect(__('Declaration of resource leaving OK', 'resources'));
   Html::back();

} else {

	if ($resource->canView() || Session::haveRight("config","w")) {
		//show remove resource form
		$resource->showResourcesToRemove();
	}
}

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
	Html::footer();
} else {
	Html::helpFooter();
}

?>