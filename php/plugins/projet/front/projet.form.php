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

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";
if (!isset($_GET["helpdesk_id"])) $_GET["helpdesk_id"] = "";
if (!isset($_GET["helpdesk_itemtype"])) $_GET["helpdesk_itemtype"] = "";

$projet=new PluginProjetProjet();
$projet_item=new PluginProjetProjet_Item();
$projet_projet = new PluginProjetProjet_Projet();

//Project
if (isset($_POST["add"])) {
	
	if (isset($_POST["helpdesk_id"]) && !empty($_POST['helpdesk_id'])) {
      
      $projet->check(-1,'w',$_POST);
      $newID=$projet->add($_POST);
      
      $values["items_id"] = $_POST["helpdesk_id"];
      $values["itemtype"] = $_POST["helpdesk_itemtype"];
      $values["plugin_projet_projets_id"] = $newID;
		$projet_item->addItem($values);
		Html::redirect($CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php");
	} else {
      $projet->check(-1,'w',$_POST);
      $newID=$projet->add($_POST);
      Html::back();
   }
	
} 
else if (isset($_POST["delete"]))
{
	$projet->check($_POST['id'],'w');
   if (!empty($_POST["withtemplate"])) {
      $projet->delete($_POST,1);
   }else {
      $projet->delete($_POST);
	}
	if(!empty($_POST["withtemplate"])) {
		Html::redirect($CFG_GLPI["root_doc"]."/plugins/projet/front/setup.templates.php?add=0");
	} else {
		$projet->redirectToList();
   }
}
else if (isset($_POST["restore"]))
{
	$projet->check($_POST['id'],'w');
	$projet->restore($_POST);
	$projet->redirectToList();
}
else if (isset($_POST["purge"]))
{
	$projet->check($_POST['id'],'w');
	$projet->delete($_POST,1);
	if(!empty($_POST["withtemplate"])) {
		Html::redirect($CFG_GLPI["root_doc"]."/plugins/projet/front/setup.templates.php?add=0");
	} else {
		$projet->redirectToList();
   }
}
else if (isset($_POST["update"]))
{
	$projet->check($_POST['id'],'w');
	$projet->update($_POST);
	Html::back();
}
else if (isset($_POST['delete_link'])) {
   
   $projet_projet->check($_POST['id'],'w');
   $projet_projet->delete(array('id'=>$_POST["id"]));
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php?id=".$_POST['plugin_projet_projets_id']);

}
//Items Project
else if (isset($_POST["additem"]))
{

   if (!empty($_POST['itemtype']) 
         && $_POST['items_id']>0) {
      $projet_item->check(-1,'w',$_POST);
      $projet_item->addItem($_POST);
	}

	Html::back();

}
else if (isset($_POST["deleteitem"]))
{

	foreach ($_POST["item"] as $key => $val) {
   if ($val==1) {
      $projet_item->check($key,'w');
      $projet_item->delete(array('id'=>$key));
      }
   }

	Html::back();
}
else if (isset($_POST["deletedevice"]))
{

   $projet_item->check($_POST["id"],'w');
   $projet_item->delete(array('id'=>$_POST["id"]));
	Html::back();
}
else
{
	$projet->checkGlobal("r");

	Html::header(PluginProjetProjet::getTypeName(2), '',"plugins","projet");

	$projet->showForm($_GET["id"], array('withtemplate' => $_GET["withtemplate"],
                                          'helpdesk_id' => $_GET["helpdesk_id"],
                                          'helpdesk_itemtype' => $_GET["helpdesk_itemtype"]));
	
	Html::footer();
}

?>