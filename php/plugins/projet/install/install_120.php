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
 
function install120() {
   global $DB;
   
   //Do One time on 0.80
   
   $query="INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Projets', 'PluginProjetProjet', '2010-12-29 11:04:46','',NULL);";
   $result=$DB->query($query);
   
   $query="INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert Projets Tasks', 'PluginProjetProjet', '2010-12-29 11:04:46','',NULL);";
   $result=$DB->query($query);
   
   
   $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginProjetProjet' AND `name` = 'Projets'";
   $result = $DB->query($query_id) or die ($DB->error());
   $itemtype = $DB->result($result,0,'id');
   
   $query="INSERT INTO `glpi_notificationtemplatetranslations`
                              VALUES(NULL, ".$itemtype.", '','##lang.projet.title## - ##projet.name##',
                     '##lang.projet.url## : ##projet.url##

##lang.projet.entity## : ##projet.entity##
##IFprojet.name####lang.projet.name## : ##projet.name####ENDIFprojet.name##
##IFprojet.datebegin####lang.projet.datebegin## : ##projet.datebegin####ENDIFprojet.datebegin##
##IFprojet.dateend####lang.projet.dateend## : ##projet.dateend####ENDIFprojet.dateend##
##IFprojet.users####lang.projet.users## : ##projet.users####ENDIFprojet.users##
##IFprojet.groups####lang.projet.groups## : ##projet.groups####ENDIFprojet.groups##
##IFprojet.status####lang.projet.status## : ##projet.status####ENDIFprojet.status##
##IFprojet.parent####lang.projet.parent## : ##projet.parent####ENDIFprojet.parent##
##IFprojet.advance####lang.projet.advance## : ##projet.advance####ENDIFprojet.advance##
##IFprojet.comment## ##lang.projet.comment## : ##projet.comment####ENDIFprojet.comment##
##IFprojet.description####lang.projet.description## : ##projet.description####ENDIFprojet.description##
##IFprojet.helpdesk####lang.projet.helpdesk## : ##projet.helpdesk####ENDIFprojet.helpdesk##
##FOREACHupdates##----------
##lang.update.title## : 
##IFupdate.name####lang.projet.name## : ##update.name####ENDIFupdate.name##
##IFupdate.datebegin####lang.projet.datebegin## : ##update.datebegin####ENDIFupdate.datebegin##
##IFupdate.dateend####lang.projet.dateend## : ##update.dateend####ENDIFupdate.dateend##
##IFupdate.users####lang.projet.users## : ##update.users####ENDIFupdate.users##
##IFupdate.groups####lang.projet.groups## : ##update.groups####ENDIFupdate.groups##
##IFupdate.status####lang.projet.status## : ##update.status####ENDIFupdate.status##
##IFupdate.parent####lang.projet.parent## : ##update.parent####ENDIFupdate.parent##
##IFupdate.advance####lang.projet.advance## : ##update.advance####ENDIFupdate.advance##
##IFupdate.comment## ##lang.projet.comment## : ##update.comment####ENDIFupdate.comment##
##IFupdate.description####lang.projet.description## : ##update.description####ENDIFupdate.description##
##IFupdate.helpdesk####lang.projet.helpdesk## : ##update.helpdesk####ENDIFupdate.helpdesk##
##ENDFOREACHupdates##----------
##IFtask.title## ##lang.task.title####ENDIFtask.title##
##FOREACHtasks##----------
##IFtask.name####lang.task.name## : ##task.name####ENDIFtask.name##
##IFtask.users####lang.task.users## : ##task.users####ENDIFtask.users##
##IFtask.groups####lang.task.groups## : ##task.groups####ENDIFtask.groups##
##IFtask.contacts## ##lang.task.contacts## : ##task.contacts####ENDIFtask.contacts##
##IFtask.type####lang.task.type## : ##task.type####ENDIFtask.type##
##IFtask.status####lang.task.status## : ##task.status####ENDIFtask.status##
##IFtask.location####lang.task.location## : ##task.location####ENDIFtask.location##
##IFtask.advance####lang.task.advance## : ##task.advance####ENDIFtask.advance##
##IFtask.priority####lang.task.priority## : ##task.priority####ENDIFtask.priority##
##IFtask.comment####lang.task.comment## : ##task.comment####ENDIFtask.comment##
##IFtask.sub####lang.task.sub## : ##task.sub####ENDIFtask.sub##
##IFtask.others####lang.task.others## : ##task.others####ENDIFtask.others##
##IFtask.affect####lang.task.affect## : ##task.affect####ENDIFtask.affect##
##IFtask.depends####lang.task.depends## : ##task.depends####ENDIFtask.depends##
##IFtask.parenttask####lang.task.parenttask## : ##task.parenttask####ENDIFtask.parenttask##
##IFtask.realtime####lang.task.realtime## : ##task.realtime## ##ENDIFtask.realtime##
----------##ENDFOREACHtasks##',
                     '&lt;p&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.url##&lt;/strong&gt; : &lt;a href=\"##projet.url##\"&gt;##projet.url##&lt;/a&gt;&lt;/span&gt; &lt;br /&gt;&lt;br /&gt; &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.entity##&lt;/strong&gt; : ##projet.entity##&lt;/span&gt; &lt;br /&gt; ##IFprojet.name##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.name##&lt;/strong&gt; : ##projet.name##&lt;br /&gt;&lt;/span&gt;##ENDIFprojet.name## ##IFprojet.datebegin##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.datebegin##&lt;/strong&gt; :  ##projet.datebegin##&lt;br /&gt;&lt;/span&gt;##ENDIFprojet.datebegin## ##IFprojet.dateend##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.dateend##&lt;/strong&gt; :  ##projet.dateend##&lt;br /&gt;&lt;/span&gt;##ENDIFprojet.dateend## ##IFprojet.users##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.users##&lt;/strong&gt; :  ##projet.users##&lt;br /&gt;&lt;/span&gt;##ENDIFprojet.users## ##IFprojet.groups##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.groups##&lt;/strong&gt; :  ##projet.groups##&lt;br /&gt;&lt;/span&gt;##ENDIFprojet.groups## ##IFprojet.status##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.status##&lt;/strong&gt; :  ##projet.status##&lt;br /&gt;&lt;/span&gt;##ENDIFprojet.status## ##IFprojet.parent##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.parent##&lt;/strong&gt; :  ##projet.parent##&lt;br /&gt;&lt;/span&gt;##ENDIFprojet.parent## ##IFprojet.advance##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.advance##&lt;/strong&gt; :  ##projet.advance##&lt;br /&gt;&lt;/span&gt;##ENDIFprojet.advance## ##IFprojet.comment##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.comment##&lt;/strong&gt; :  ##projet.comment##&lt;br /&gt;&lt;/span&gt;##ENDIFprojet.comment## ##IFprojet.description##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.description##&lt;/strong&gt; :  ##projet.description##&lt;br /&gt;&lt;/span&gt;##ENDIFprojet.description## ##IFprojet.helpdesk##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.helpdesk##&lt;/strong&gt; :  ##projet.helpdesk##&lt;br /&gt;&lt;/span&gt;##ENDIFprojet.helpdesk##  ##FOREACHupdates##----------&lt;br /&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.update.title## :&lt;/strong&gt;&lt;/span&gt; &lt;br /&gt; ##IFupdate.name##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.name##&lt;/strong&gt; : ##update.name##&lt;br /&gt;&lt;/span&gt;##ENDIFupdate.name## ##IFupdate.datebegin##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.datebegin##&lt;/strong&gt; : ##update.datebegin##&lt;br /&gt;&lt;/span&gt;##ENDIFupdate.datebegin## ##IFupdate.dateend##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.dateend##&lt;/strong&gt; : ##update.dateend##&lt;br /&gt;&lt;/span&gt;##ENDIFupdate.dateend## ##IFupdate.users##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.users##&lt;/strong&gt; : ##update.users##&lt;br /&gt;&lt;/span&gt;##ENDIFupdate.users## ##IFupdate.groups##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.groups##&lt;/strong&gt; : ##update.groups##&lt;br /&gt;&lt;/span&gt;##ENDIFupdate.groups## ##IFupdate.status##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.status##&lt;/strong&gt; : ##update.status##&lt;br /&gt;&lt;/span&gt;##ENDIFupdate.status## ##IFupdate.parent##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.parent##&lt;/strong&gt; : ##update.parent##&lt;br /&gt;&lt;/span&gt;##ENDIFupdate.parent## ##IFupdate.advance##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.advance##&lt;/strong&gt; : ##update.advance##&lt;br /&gt;&lt;/span&gt;##ENDIFupdate.advance## ##IFupdate.comment##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.comment##&lt;/strong&gt; : ##update.comment##&lt;br /&gt;&lt;/span&gt;##ENDIFupdate.comment## ##IFupdate.description##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.description##&lt;/strong&gt; : ##update.description##&lt;br /&gt;&lt;/span&gt;##ENDIFupdate.description## ##IFupdate.helpdesk##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.projet.helpdesk##&lt;/strong&gt; : ##update.helpdesk##&lt;br /&gt;&lt;/span&gt;##ENDIFupdate.helpdesk##  ##ENDFOREACHupdates##----------&lt;br /&gt; ##IFtask.title##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.title##&lt;/strong&gt;&lt;/span&gt;&lt;br /&gt;##ENDIFtask.title## ##FOREACHtasks##----------&lt;br /&gt; ##IFtask.name##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.name##&lt;/strong&gt; : ##task.name##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.name## ##IFtask.users##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.users##&lt;/strong&gt; : ##task.users##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.users## ##IFtask.groups##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.groups##&lt;/strong&gt; : ##task.groups##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.groups## ##IFtask.contacts##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.contacts##&lt;/strong&gt; : ##task.contacts##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.contacts## ##IFtask.type##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.type##&lt;/strong&gt; : ##task.type##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.type## ##IFtask.status##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.status##&lt;/strong&gt; : ##task.status##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.status## ##IFtask.location##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.location##&lt;/strong&gt; : ##task.location##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.location## ##IFtask.advance##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.advance##&lt;/strong&gt; : ##task.advance##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.advance## ##IFtask.priority##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.priority##&lt;/strong&gt; : ##task.priority##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.priority## ##IFtask.comment##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.comment##&lt;/strong&gt; : ##task.comment##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.comment## ##IFtask.sub##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.sub##&lt;/strong&gt; : ##task.sub##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.sub## ##IFtask.others##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.others##&lt;/strong&gt; : ##task.others##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.others## ##IFtask.affect##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.affect##&lt;/strong&gt; : ##task.affect##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.affect## ##IFtask.depends##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.depends##&lt;/strong&gt; : ##task.depends##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.depends## ##IFtask.parenttask##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.parenttask##&lt;/strong&gt; : ##task.parenttask##&lt;br /&gt;&lt;/span&gt;##ENDIFtask.parenttask## ##IFtask.realtime##&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;&lt;strong&gt;##lang.task.realtime##&lt;/strong&gt; : ##task.realtime##&lt;/span&gt;##ENDIFtask.realtime## &lt;br /&gt;----------##ENDFOREACHtasks##&lt;/p&gt;');";
   $result=$DB->query($query);
   
   $query = "INSERT INTO `glpi_notifications`
                                VALUES (NULL, 'New Project', 0, 'PluginProjetProjet', 'new',
                                       'mail',".$itemtype.",
                                       '', 1, 1, '2010-05-16 22:36:46');";
   $result=$DB->query($query);
   $query = "INSERT INTO `glpi_notifications`
                                VALUES (NULL, 'Update Project', 0, 'PluginProjetProjet', 'update',
                                       'mail',".$itemtype.",
                                       '', 1, 1, '2010-05-16 22:36:46');";
   $result=$DB->query($query);
   $query = "INSERT INTO `glpi_notifications`
                                VALUES (NULL, 'Delete Project', 0, 'PluginProjetProjet', 'delete',
                                       'mail',".$itemtype.",
                                       '', 1, 1, '2010-05-16 22:36:46');";
   $result=$DB->query($query);
   $query = "INSERT INTO `glpi_notifications`
                                VALUES (NULL, 'New Project Task', 0, 'PluginProjetProjet', 'newtask',
                                       'mail',".$itemtype.",
                                       '', 1, 1, '2010-05-16 22:36:46');";
   $result=$DB->query($query);
   $query = "INSERT INTO `glpi_notifications`
                                VALUES (NULL, 'Update Project Task', 0, 'PluginProjetProjet', 'updatetask',
                                       'mail',".$itemtype.",
                                       '', 1, 1, '2010-05-16 22:36:46');";
   $result=$DB->query($query);
   $query = "INSERT INTO `glpi_notifications`
                                VALUES (NULL, 'Delete Project Task', 0, 'PluginProjetProjet', 'deletetask',
                                       'mail',".$itemtype.",
                                       '', 1, 1, '2010-05-16 22:36:46');";
   
   $result=$DB->query($query);
   
   $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginProjetProjet' AND `name` = 'Alert Projets Tasks'";
   $result = $DB->query($query_id) or die ($DB->error());
   $itemtype = $DB->result($result,0,'id');
   
   $query="INSERT INTO `glpi_notificationtemplatetranslations`
                                 VALUES(NULL, ".$itemtype.", '','##projet.action## : ##projet.entity##',
                        '##FOREACHtasks## 
   ##lang.task.name## : ##task.name##
   ##lang.task.type## : ##task.type##
   ##lang.task.users## : ##task.users##
   ##lang.task.groups## : ##task.groups##
   ##lang.task.datebegin## : ##task.datebegin##
   ##lang.task.dateend## : ##task.dateend##
   ##lang.task.comment## : ##task.comment##
   ##lang.task.projet## : ##task.projet##
   ##ENDFOREACHtasks##',
                           '&lt;table class=\"tab_cadre\" border=\"1\" cellspacing=\"2\" cellpadding=\"3\"&gt;
   &lt;tbody&gt;
   &lt;tr&gt;
   &lt;td style=\"text-align: left;\" bgcolor=\"#cccccc\"&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##lang.task.name##&lt;/span&gt;&lt;/td&gt;
   &lt;td style=\"text-align: left;\" bgcolor=\"#cccccc\"&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##lang.task.type##&lt;/span&gt;&lt;/td&gt;
   &lt;td style=\"text-align: left;\" bgcolor=\"#cccccc\"&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##lang.task.users##&lt;/span&gt;&lt;/td&gt;
   &lt;td style=\"text-align: left;\" bgcolor=\"#cccccc\"&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##lang.task.groups##&lt;/span&gt;&lt;/td&gt;
   &lt;td style=\"text-align: left;\" bgcolor=\"#cccccc\"&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##lang.task.datebegin##&lt;/span&gt;&lt;/td&gt;
   &lt;td style=\"text-align: left;\" bgcolor=\"#cccccc\"&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##lang.task.dateend##&lt;/span&gt;&lt;/td&gt;
   &lt;td style=\"text-align: left;\" bgcolor=\"#cccccc\"&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##lang.task.comment##&lt;/span&gt;&lt;/td&gt;
   &lt;td style=\"text-align: left;\" bgcolor=\"#cccccc\"&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##lang.task.projet##&lt;/span&gt;&lt;/td&gt;
   &lt;/tr&gt;
   ##FOREACHtasks##                 
   &lt;tr&gt;
   &lt;td&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##task.name##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##task.type##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##task.users##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##task.groups##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##task.datebegin##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##task.dateend##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##task.comment##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;##task.projet##&lt;/span&gt;&lt;/td&gt;
   &lt;/tr&gt;
   ##ENDFOREACHtasks##
   &lt;/tbody&gt;
   &lt;/table&gt;');";
   $result=$DB->query($query);
   
   $query = "INSERT INTO `glpi_notifications`
                                VALUES (NULL, 'Alert Expired Projects Tasks', 0, 'PluginProjetProjet', 'AlertExpiredTasks',
                                       'mail',".$itemtype.",
                                       '', 1, 1, '2010-02-17 22:36:46');";
   $result=$DB->query($query);
   
   CronTask::Register('PluginProjetTask', 'ProjetTask', WEEK_TIMESTAMP);
}

?>