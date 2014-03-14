<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Domains plugin for GLPI
 Copyright (C) 2003-2011 by the Domains Development Team.

 https://forge.indepnet.net/projects/domains
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Domains.

 Domains is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Domains is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Domains. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$domain=new PluginDomainsDomain();
$domain_item=new PluginDomainsDomain_Item();

if (isset($_POST["add"])) {
	$domain->check(-1,'w',$_POST);
   $newID=$domain->add($_POST);
	Html::back();
	
} else if (isset($_POST["delete"])) {

	$domain->check($_POST['id'],'w');
   $domain->delete($_POST);
	$domain->redirectToList();
	
} else if (isset($_POST["restore"])) {

	$domain->check($_POST['id'],'w');
   $domain->restore($_POST);
	$domain->redirectToList();
	
} else if (isset($_POST["purge"])) {

	$domain->check($_POST['id'],'w');
   $domain->delete($_POST,1);
	$domain->redirectToList();
	
} else if (isset($_POST["update"])) {

	$domain->check($_POST['id'],'w');
   $domain->update($_POST);
	Html::back();
	
} else if (isset($_POST["additem"])) {

	if (!empty($_POST['itemtype'])&&$_POST['items_id']>0) {
      $domain_item->check(-1,'w',$_POST);
		$domain_item->addItem($_POST);
	}
	Html::back();
	
} else if (isset($_POST["deleteitem"])) {
   
   foreach ($_POST["item"] as $key => $val) {
      $input = array('id' => $key);
      if ($val==1) {
         $domain_item->check($key,'w');
         $domain_item->delete($input);
      }
   }

	Html::back();
	
} else if (isset($_POST["deletedomains"])) {

	$input = array('id' => $_POST["id"]);
   $domain_item->check($_POST["id"],'w');
	$domain_item->delete($input);
	Html::back();
	
} else {

	$domain->checkGlobal("r");
	
	$plugin = new Plugin();
	if ($plugin->isActivated("environment"))
		Html::header(PluginDomainsDomain::getTypeName(2),'',"plugins","environment","domains");
	else
		Html::header(PluginDomainsDomain::getTypeName(2),'',"plugins","domains");

	$domain->showForm($_GET["id"]);

	Html::footer();
}

?>