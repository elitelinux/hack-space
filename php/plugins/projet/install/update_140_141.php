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

// Update from 1.4.0 to 1.4.1
function update140to141() {
   global $DB;
   
   echo "<strong>Update 1.4.0 to 1.4.1</strong><br/>";

   $query="CREATE TABLE `glpi_plugin_projet_followups` (
               `id` int(11) NOT NULL auto_increment,
               `date` datetime default NULL,
               `users_id` int(11) NOT NULL default '0', -- id du redacteur
               `content` text collate utf8_unicode_ci,
               `plugin_projet_projets_id` int(11) default NULL,
               PRIMARY KEY  (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
   $DB->query($query)
   or die("0.84 ADD glpi_plugin_projet_followups table". "Error during the database update" . $DB->error());
   
   
   $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginProjetProjet' AND `name` = 'Projets'";
   $result = $DB->query($query_id) or die ($DB->error());
   $itemtype = $DB->result($result,0,'id');
   
   $query = "INSERT INTO `glpi_notifications`
                                VALUES (NULL, 'Add Followup', 0, 'PluginProjetProjet', 'add_followup',
                                       'mail',".$itemtype.",
                                       '', 1, 1, '2013-10-21 15:26:22');";
   $result=$DB->query($query);
   
   $query = "INSERT INTO `glpi_notifications`
                                VALUES (NULL, 'Update Followup', 0, 'PluginProjetProjet', 'update_followup',
                                       'mail',".$itemtype.",
                                       '', 1, 1, '2013-10-21 15:26:22');";
   $result=$DB->query($query);
   
   $query = "INSERT INTO `glpi_notifications`
                                VALUES (NULL, 'Delete Followup', 0, 'PluginProjetProjet', 'delete_followup',
                                       'mail',".$itemtype.",
                                       '', 1, 1, '2013-10-21 15:26:22');";
   $result=$DB->query($query);

}

?>