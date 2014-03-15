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
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

if (isset($_POST["action"]) 
      && !empty($_POST["action"])) {

   $types = PluginUtilitairesUtilitaire::getActions($_POST["action"]);
   echo "&nbsp;";
   $rand = Dropdown::showFromArray("actionId", $types);
   
   $params = array ('actionId' => '__VALUE__', 'action' => $_POST["action"]);
      Ajax::updateItemOnSelectEvent("dropdown_actionId$rand", "show_Date$rand",
                                  $CFG_GLPI["root_doc"] . "/plugins/utilitaires/ajax/showdates.php",
                                  $params);
       echo "<span id='show_Date$rand'>&nbsp;</span>";
}

?>