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

// Update from 1.2.0 to 1.3.0
function update120to130() {
   global $DB;
   
   echo "<strong>Update 1.2.0 to 1.3.0</strong><br/>";
   
   // Put realtime in seconds
   if (FieldExists('glpi_plugin_projet_tasks','realtime')) {
         
      $query="ALTER TABLE `glpi_plugin_projet_tasks`
         ADD `actiontime` INT( 11 ) NOT NULL DEFAULT 0 ;";
      $DB->query($query)
      or die("0.83 ADD actiontime in glpi_plugin_projet_tasks". "Error during the database update" . $DB->error());
      
      $query = "UPDATE `glpi_plugin_projet_tasks`
                SET `actiontime` = ROUND(realtime * 3600)";
      $DB->query($query)
      or die("0.83 compute actiontime value in glpi_plugin_projet_tasks". "Error during the database update" . $DB->error());

      $query="ALTER TABLE `glpi_plugin_projet_tasks`
         DROP `realtime` ;";
      $DB->query($query)
      or die("0.83 DROP realtime in glpi_plugin_projet_tasks". "Error during the database update" . $DB->error());
   }
   
   $query="CREATE TABLE  IF NOT EXISTS `glpi_plugin_projet_projets_projets` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `plugin_projet_projets_id_1` int(11) NOT NULL DEFAULT '0',
              `plugin_projet_projets_id_2` int(11) NOT NULL DEFAULT '0',
              `link` int(11) NOT NULL DEFAULT '1',
              PRIMARY KEY (`id`),
              KEY `unicity` (`plugin_projet_projets_id_1`,`plugin_projet_projets_id_2`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
   $DB->query($query)
   or die("0.83 Create glpi_plugin_projet_projets_projets". "Error during the database update" . $DB->error());
   
   $restrict = "`plugin_projet_projets_id` <> 0";
   $projets = getAllDatasFromTable("glpi_plugin_projet_projets",$restrict);
   if (!empty($projets)) {
      foreach ($projets as $projet) {
         $query = "INSERT INTO `glpi_plugin_projet_projets_projets` (
                     `id` ,
                     `plugin_projet_projets_id_1` ,
                     `plugin_projet_projets_id_2` ,
                     `link`
                     )
                     VALUES (
                     NULL , '".$projet["id"]."', '".$projet["plugin_projet_projets_id"]."', '1'
                     );";
         $DB->query($query)
         or die("0.83 compute values in glpi_plugin_projet_projets_projets". "Error during the database update" . $DB->error());
      }
   }
   
   $query="ALTER TABLE `glpi_plugin_projet_projets`
      DROP `plugin_projet_projets_id` ;";
   $DB->query($query)
   or die("0.83 DROP plugin_projet_projets_id in glpi_plugin_projet_projets". "Error during the database update" . $DB->error());
   
   $query="CREATE TABLE IF NOT EXISTS `glpi_plugin_projet_tasks_tasks` (
           `id` int(11) NOT NULL AUTO_INCREMENT,
           `plugin_projet_tasks_id_1` int(11) NOT NULL DEFAULT '0',
           `plugin_projet_tasks_id_2` int(11) NOT NULL DEFAULT '0',
           `link` int(11) NOT NULL DEFAULT '1',
           PRIMARY KEY (`id`),
           KEY `unicity` (`plugin_projet_tasks_id_1`,`plugin_projet_tasks_id_2`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
   $DB->query($query)
   or die("0.83 Create glpi_plugin_projet_tasks_tasks". "Error during the database update" . $DB->error());
   
   
   $restrict = "`plugin_projet_tasks_id` <> 0";
   $tasks = getAllDatasFromTable("glpi_plugin_projet_tasks",$restrict);
   if (!empty($tasks)) {
      foreach ($tasks as $task) {
         $query = "INSERT INTO `glpi_plugin_projet_tasks_tasks` (
                     `id` ,
                     `plugin_projet_tasks_id_1` ,
                     `plugin_projet_tasks_id_2` ,
                     `link`
                     )
                     VALUES (
                     NULL , '".$task["id"]."', '".$task["plugin_projet_tasks_id"]."', '1'
                     );";
         $DB->query($query)
         or die("0.83 compute values in glpi_plugin_projet_tasks_tasks". "Error during the database update" . $DB->error());
      }
   }
   
   $query="ALTER TABLE `glpi_plugin_projet_tasks`
      DROP `plugin_projet_tasks_id` ;";
   $DB->query($query)
   or die("0.83 DROP plugin_projet_tasks_id in glpi_plugin_projet_tasks". "Error during the database update" . $DB->error());

}

?>