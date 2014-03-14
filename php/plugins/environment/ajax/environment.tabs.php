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

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$env=new PluginEnvironmentDisplay();

echo "<div align='center'>";
echo "<table class='tab_cadre_central'>";
echo "<tr><td>";
switch ($_POST['plugin_environment_tab']) {
   case "appliances" :
      $_SESSION['glpi_plugin_environment_tab']="appliances";
      $env->showAppliances($_POST['appliances']);
      break;
   case "webapplications" :
      $_SESSION['glpi_plugin_environment_tab']="webapplications";
      $env->showWebapplications($_POST['webapplications']);
      break;
   case "certificates" :
      $_SESSION['glpi_plugin_environment_tab']="certificates";
      $env->showCertificates($_POST['certificates']);
      break;
   case "accounts" :
      $_SESSION['glpi_plugin_environment_tab']="accounts";
      $env->showAccounts($_POST['accounts']);
      break;
   case "domains" :
      $_SESSION['glpi_plugin_environment_tab']="domains";
      $env->showDomains($_POST['domains']);
      break;
   case "databases" :
      $_SESSION['glpi_plugin_environment_tab']="databases";
      $env->showDatabases($_POST['databases']);
      break;
   case "badges" :
      $_SESSION['glpi_plugin_environment_tab']="badges";
      $env->showBadges($_POST['badges']);
      break;
   case "all":
      $_SESSION['glpi_plugin_environment_tab']="all";
      $env->showAppliances($_POST['appliances']);
      $env->showWebapplications($_POST['webapplications']);
      $env->showCertificates($_POST['certificates']);
      $env->showAccounts($_POST['accounts']);
      $env->showDomains($_POST['domains']);
      $env->showDatabases($_POST['databases']);
      $env->showBadges($_POST['badges']);
      break;
   default :
      break;
}
echo "</td></tr>";
echo "</table></div>";

Html::ajaxFooter();

?>