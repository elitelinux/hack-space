<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Utilitaires plugin for GLPI
 Copyright (C) 2003-2011 by the Utilitaires Development Team.

 https://forge.indepnet.net/projects/utilitaires
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Utilitaires.

 Utilitaires is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Utilitaires is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with utilitaires. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_utilitaires_install() {
   global $DB;
   
	include_once (GLPI_ROOT."/plugins/utilitaires/inc/profile.class.php");
   
   if (!TableExists("glpi_plugin_utilitaires_profiles")) {

		$DB->runFile(GLPI_ROOT ."/plugins/utilitaires/sql/empty-1.5.0.sql");

	}
	
   PluginUtilitairesProfile::createfirstAccess($_SESSION['glpiactiveprofile']['id']);
   
	return true;
}

function plugin_utilitaires_uninstall() {
	global $DB;

	$tables = array("glpi_plugin_utilitaires_profiles");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");

	return true;
}

// Define dropdown relations
function plugin_utilitaires_getDatabaseRelations() {

	$plugin = new Plugin();

	if ($plugin->isActivated("utilitaires"))
		return array("glpi_profiles" => array ("glpi_plugin_utilitaires_profiles" => "profiles_id"));
	else
		return array();
}

?>