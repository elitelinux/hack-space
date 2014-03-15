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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

Session::checkRight("profile","r");

$prof=new PluginUtilitairesProfile();

//Save profile
if (isset ($_POST['update'])) {
	$prof->update($_POST);
	Html::back();
}

?>