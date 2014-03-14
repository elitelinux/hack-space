<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Environment plugin for GLPI
 Copyright (C) 2003-2011 by the Environment Development Team.

 https://forge.indepnet.net/projects/environment
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Environment.

 Environment is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Environment is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Environment. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

include ('../../../inc/includes.php');

Html::Header(PluginEnvironmentDisplay::getTypeName(1),'',"plugins","environment");

if(plugin_environment_haveRight("environment","r") || Session::haveRight("config","w")) {
   
   $env = new PluginEnvironmentDisplay();
   
	// show "my view" in first
   if (!isset($_SESSION['glpi_plugin_environment_tab'])) $_SESSION['glpi_plugin_environment_tab']="all";
   if (isset($_GET['onglet'])) {
      $_SESSION['glpi_plugin_environment_tab']=$_GET['onglet'];
      //		glpi_header($_SERVER['HTTP_REFERER']);
   }

	$plugin = new Plugin();

	if ($plugin->isActivated("appliances")) $appliances=1; else $appliances=0;
	if ($plugin->isActivated("webapplications")) $webapplications=1; else $webapplications=0;
	if ($plugin->isActivated("certificates")) $certificates=1; else $certificates=0;
	if ($plugin->isActivated("accounts")) $accounts=1; else $accounts=0;
	if ($plugin->isActivated("domains")) $domains=1; else $domains=0;
	if ($plugin->isActivated("databases")) $databases=1; else $databases=0;
	if ($plugin->isActivated("badges")) $badges=1; else $badges=0;


	if ($appliances!=0 || $webapplications!=0 
         || $certificates!=0 || $domains!=0 
            || $databases!=0 || $accounts!=0 
               || $badges!=0) {

		if ($appliances!=0 && plugin_environment_haveRight("appliances","r"))
			$tabs['appliances']=array('title'=>__('Appliances', 'environment'),
			'url'=>$CFG_GLPI['root_doc']."/plugins/environment/ajax/environment.tabs.php",
			'params'=>"target=".$_SERVER['PHP_SELF']."&appliances=$appliances&plugin_environment_tab=appliances");

		if ($webapplications!=0 && plugin_environment_haveRight("webapplications","r"))
			$tabs['webapplications']=array('title'=>__('Web applications', 'environment'),
			'url'=>$CFG_GLPI['root_doc']."/plugins/environment/ajax/environment.tabs.php",
			'params'=>"target=".$_SERVER['PHP_SELF']."&webapplications=$webapplications&plugin_environment_tab=webapplications");

		if ($certificates!=0 && plugin_environment_haveRight("certificates","r"))
			$tabs['certificates']=array('title'=>__('Certificates', 'environment'),
			'url'=>$CFG_GLPI['root_doc']."/plugins/environment/ajax/environment.tabs.php",
			'params'=>"target=".$_SERVER['PHP_SELF']."&certificates=$certificates&plugin_environment_tab=certificates");

		if ($accounts!=0 && plugin_environment_haveRight("accounts","r"))
			$tabs['accounts']=array('title'=>__('Accounts', 'environment'),
			'url'=>$CFG_GLPI['root_doc']."/plugins/environment/ajax/environment.tabs.php",
			'params'=>"target=".$_SERVER['PHP_SELF']."&accounts=$accounts&plugin_environment_tab=accounts");

		if ($domains!=0 && plugin_environment_haveRight("domains","r"))
			$tabs['domains']=array('title'=>__('Domains', 'environment'),
			'url'=>$CFG_GLPI['root_doc']."/plugins/environment/ajax/environment.tabs.php",
			'params'=>"target=".$_SERVER['PHP_SELF']."&domains=$domains&plugin_environment_tab=domains");

		if ($databases!=0 && plugin_environment_haveRight("databases","r"))
			$tabs['databases']=array('title'=>__('Databases', 'environment'),
			'url'=>$CFG_GLPI['root_doc']."/plugins/environment/ajax/environment.tabs.php",
			'params'=>"target=".$_SERVER['PHP_SELF']."&databases=$databases&plugin_environment_tab=databases");

		if ($badges!=0 && plugin_environment_haveRight("badges","r"))
			$tabs['badges']=array('title'=>__('Badges', 'environment'),
			'url'=>$CFG_GLPI['root_doc']."/plugins/environment/ajax/environment.tabs.php",
			'params'=>"target=".$_SERVER['PHP_SELF']."&badges=$badges&plugin_environment_tab=badges");

		$tabs['all']=array('title'=>__('All'),
		'url'=>$CFG_GLPI['root_doc']."/plugins/environment/ajax/environment.tabs.php",
		'params'=>"target=".$_SERVER['PHP_SELF']."&appliances=$appliances&webapplications=$webapplications&certificates=$certificates&accounts=$accounts&domains=$domains&databases=$databases&badges=$badges&plugin_environment_tab=all");

	echo "<div id='tabspanel' class='center-h'></div>";
	Ajax::createTabs('tabspanel','tabcontent',$tabs,'PluginEnvironmentDisplay');
	$env->addDivForTabs();

	} else {
		Html::displayRightError();
	}

} else {
	Html::displayRightError();
}

Html::footer();

?>