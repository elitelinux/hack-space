<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2013 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author 
   @comment   
   @copyright Copyright (c) 2011-2013 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011
 
   ------------------------------------------------------------------------
 */


include ("../../../inc/includes.php");

Session::checkCentralAccess();

Html::header(__('Monitoring', 'monitoring'),$_SERVER["PHP_SELF"], "plugins", 
             "monitoring", "hostconfig");


$pmHostconfig = new PluginMonitoringHostconfig();

if (isset($_POST["update"])) {
   if ($_POST['plugin_monitoring_commands_id'] != '-1'
           OR $_POST['plugin_monitoring_checks_id'] != '-1'
           OR $_POST['calendars_id'] != '-1'
           OR $_POST['plugin_monitoring_realms_id'] != '-1') {
   
      if (isset($_POST['id'])) {
         $pmHostconfig->update($_POST);
      } else {
         $pmHostconfig->add($_POST);
      }      
   } else if (isset($_POST['id'])) {
      $pmHostconfig->delete($_POST);
   }
   Html::back();
}

Html::footer();

?>