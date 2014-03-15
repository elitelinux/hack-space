<?php
/*
 * @version $Id: HEADER 15930 2012-03-08 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Projet plugin for GLPI
 Copyright (C) 2003-2012 by the Projet Development Team.

 https://forge.indepnet.net/projects/projet
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Projet.

 Projet is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Projet is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Projet. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if(!isset($_GET["id"])) $_GET["id"] = "";
if(!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";
if(!isset($_GET["plugin_projet_projets_id"])) $_GET["plugin_projet_projets_id"] = 0;

$task=new PluginProjetTask();
$task_item=new PluginProjetTask_Item();
$task_task = new PluginProjetTask_Task();
//add tasks
if (isset($_POST['add'])) {

	$task->check(-1,'w',$_POST);
	$newID=$task->add($_POST);
   Html::back();

} 
//update task
else if (isset($_POST["update"])) {

	$task->check($_POST['id'],'w');
   $task->update($_POST);
	Html::back();
	
}
//from central
//delete task
else if (isset($_POST["delete"])) {
	
	$task->check($_POST['id'],'w');
	$task->delete($_POST);
	Html::redirect(Toolbox::getItemTypeFormURL('PluginProjetProjet').
                                       "?id=".$_POST["plugin_projet_projets_id"]);
	
}
//from central
//restore task
else if (isset($_POST["restore"]))
{
	$task->check($_POST['id'],'w');
   $task->restore($_POST);
	Html::redirect(Toolbox::getItemTypeFormURL('PluginProjetProjet').
                                          "?id=".$_POST["plugin_projet_projets_id"]);
}
//from central
//purge task
else if (isset($_POST["purge"]))
{
	$task->check($_POST['id'],'w');
   $task->delete($_POST,1);
	Html::redirect(Toolbox::getItemTypeFormURL('PluginProjetProjet').
                                          "?id=".$_POST["plugin_projet_projets_id"]);
}
//from central
//add item to task
else if (isset($_POST["addtaskitem"])) {

	if($task->canCreate())
		$task_item->addTaskItem($_POST);

	Html::back();
}
//from central
//delete item to task
else if (isset($_POST["deletetaskitem"])) {

	if($task->canCreate())
      $task_item->delete(array('id'=>$_POST["id"]));
	Html::back();
}
else if (isset($_POST['delete_link'])) {
   
   $task_task->check($_POST['id'],'w');
   $task_task->delete(array('id'=>$_POST["id"]));
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/projet/front/task.form.php?id=".$_POST['plugin_projet_tasks_id']);

}
else
{

	$task->checkGlobal("r");
	
	Html::header(PluginProjetProjet::getTypeName(2),'',"plugins","projet");
   
	$task->showForm($_GET["id"], array('plugin_projet_projets_id' => $_GET["plugin_projet_projets_id"],
                                       'withtemplate' => $_GET["withtemplate"]));

	Html::footer();
}

?>