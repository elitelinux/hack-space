<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Immobilizationsheets plugin for GLPI
 Copyright (C) 2003-2011 by the Immobilizationsheets Development Team.

 https://forge.indepnet.net/projects/immobilizationsheets
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Immobilizationsheets.

 Immobilizationsheets is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Immobilizationsheets is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Immobilizationsheets. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Session::checkRight("profile","r");

$prof=new PluginImmobilizationsheetsProfile();

//Save profile
if (isset ($_POST['update'])) {
	$prof->update($_POST);
	Html::back();
}

?>