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
 
// Update from 1.0.1 to 1.1.0
function update101to110() {
   global $DB, $CFG_GLPI;

   echo "<strong>Update 1.0.1 to 1.1.0</strong><br/>";

   // ** Update glpi_plugin_projet_projets
   if (TableExists("glpi_plugin_projet")) {
      $query = "RENAME TABLE `glpi_plugin_projet` TO `glpi_plugin_projet_projets` ";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projets", "ID")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projets` 
                  CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projets", "FK_entities")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projets` 
                  CHANGE `FK_entities` `entities_id` INT( 11 ) NOT NULL DEFAULT '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projets", "recursive")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projets` 
                  CHANGE `recursive` `is_recursive` tinyint(1) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projets", "FK_users")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projets` 
                  CHANGE `FK_users` `users_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projets", "FK_groups")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projets` 
                  CHANGE `FK_groups` `groups_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projets", "status")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projets` 
                  CHANGE `status` `plugin_projet_projetstates_id` INT( 4 ) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projets", "parentID")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projets` 
                  CHANGE `parentID` `plugin_projet_projets_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projets", "comments")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projets` 
                  CHANGE `comments` `comment` TEXT CHARACTER 
                     SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projets", "tplname")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projets` 
                  CHANGE `tplname` `template_name` varchar(255) collate utf8_unicode_ci NOT NULL default ''";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projets", "deleted")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projets` 
                  CHANGE `deleted` `is_deleted` smallint(6) NOT NULL default '0'";
      $DB->query($query);
   }
   
   // ** Update glpi_plugin_projet_projetitems
   if (TableExists("glpi_plugin_projet_items")) {
      $query = "RENAME TABLE `glpi_plugin_projet_items` TO `glpi_plugin_projet_projetitems` ";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projetitems", "ID")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projetitems` 
                  CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projetitems", "FK_projet")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projetitems` 
                  CHANGE `FK_projet` `plugin_projet_projets_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projetitems", "FK_device")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projetitems` 
                  CHANGE `FK_device` `items_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projetitems", "device_type")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projetitems` 
                  CHANGE `device_type` `itemtype` varchar(100) NOT NULL default '0'";
      $DB->query($query);
   }
   if (isIndex("glpi_plugin_projet_projetitems", "FK_device")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projetitems` 
                  DROP INDEX `FK_device` ,
                  ADD INDEX `items_id` (`items_id`,`plugin_projet_projets_id`) ";
      $DB->query($query);
   }
   if (isIndex("glpi_plugin_projet_projetitems", "FK_projet")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projetitems` 
                  DROP INDEX `FK_projet` ,
                  ADD INDEX `plugin_projet_projets_id` (`plugin_projet_projets_id`) ";
      $DB->query($query);
   }
   if (isIndex("glpi_plugin_projet_projetitems", "FK_device_2")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projetitems` 
                  DROP INDEX `FK_device_2` ,
                  ADD INDEX `items_id_2` (`items_id`) ";
      $DB->query($query);
   }
   if (isIndex("glpi_plugin_projet_projetitems", "device_type")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projetitems` 
                  DROP INDEX `device_type` ,
                  ADD INDEX itemtype (`itemtype`) ";
      $DB->query($query);
   }
   
   // ** Update glpi_plugin_projet_tasks
   if (FieldExists("glpi_plugin_projet_tasks", "ID")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasks` 
                  CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_tasks", "FK_projet")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasks` 
                  CHANGE `FK_projet` `plugin_projet_projets_id` int(4) NOT NULL default '0'";
      $DB->query($query);
   }   
   if (FieldExists("glpi_plugin_projet_tasks", "FK_users")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasks` 
                  CHANGE `FK_users` `users_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }   
   if (FieldExists("glpi_plugin_projet_tasks", "FK_groups")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasks` 
                  CHANGE `FK_groups` `groups_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }   
   if (FieldExists("glpi_plugin_projet_tasks", "FK_enterprise")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasks` 
                  CHANGE `FK_enterprise` `suppliers_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_tasks", "type_task")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasks` 
                  CHANGE `type_task` `plugin_projet_tasktypes_id` int(4) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_tasks", "status")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasks` 
                  CHANGE `status` `plugin_projet_taskstates_id` int(4) NOT NULL default '0'";
      $DB->query($query);
   }   
   if (FieldExists("glpi_plugin_projet_tasks", "parentID")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasks` 
                  CHANGE `parentID` `plugin_projet_tasks_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }   
   if (FieldExists("glpi_plugin_projet_tasks", "location")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasks` 
                  CHANGE `location` `locations_id` INT( 11 ) NOT NULL";
      $DB->query($query);
   }   
   if (FieldExists("glpi_plugin_projet_tasks", "tplname")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasks` 
                  CHANGE `tplname` `template_name` varchar(255) collate utf8_unicode_ci NOT NULL default ''";
      $DB->query($query);
   }   
   if (FieldExists("glpi_plugin_projet_tasks", "deleted")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasks` 
                  CHANGE `deleted` `is_deleted` smallint(6) NOT NULL default '0'";
      $DB->query($query);
   }
   
   // ** Update glpi_plugin_projet_taskitems
   if (TableExists("glpi_plugin_projet_tasks_items")) {
      $query = "RENAME TABLE `glpi_plugin_projet_tasks_items` TO `glpi_plugin_projet_taskitems` ";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_taskitems", "ID")) {
      $query = "ALTER TABLE `glpi_plugin_projet_taskitems` 
                  CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
      $DB->query($query);
   }   
   if (FieldExists("glpi_plugin_projet_taskitems", "FK_task")) {
      $query = "ALTER TABLE `glpi_plugin_projet_taskitems` 
                  CHANGE `FK_task` `plugin_projet_tasks_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_taskitems", "FK_device")) {
      $query = "ALTER TABLE `glpi_plugin_projet_taskitems` 
                  CHANGE `FK_device` `items_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }
   
   if (FieldExists("glpi_plugin_projet_taskitems", "device_type")) {
      $query = "ALTER TABLE `glpi_plugin_projet_taskitems` 
                  CHANGE `device_type` `itemtype` int(11) NOT NULL default '0'";
      $DB->query($query);
   }
   if (isIndex("glpi_plugin_projet_taskitems", "FK_device")) {
      $query = "ALTER TABLE `glpi_plugin_projet_taskitems` 
                  DROP INDEX `FK_device` ,
                  ADD INDEX `items_id` (`items_id`,`plugin_projet_tasks_id`) ";
      $DB->query($query);
   }
   if (isIndex("glpi_plugin_projet_taskitems", "FK_task")) {
      $query = "ALTER TABLE `glpi_plugin_projet_taskitems` 
                  DROP INDEX `FK_task` ,
                  ADD INDEX `plugin_projet_tasks_id` (`plugin_projet_tasks_id`)";
      $DB->query($query);
   }   
   if (isIndex("glpi_plugin_projet_taskitems", "FK_device_2")) {
      $query = "ALTER TABLE `glpi_plugin_projet_taskitems` 
                  DROP INDEX `FK_device_2` ,
                  ADD INDEX `items_id_2` (`items_id`) ";
      $DB->query($query);
   }   
   if (isIndex("glpi_plugin_projet_taskitems", "device_type")) {
      $query = "ALTER TABLE `glpi_plugin_projet_taskitems` 
                  DROP INDEX `device_type` ,
                  ADD INDEX `itemtype` (`itemtype`) ";
      $DB->query($query);
   }
   
   // ** Update glpi_plugin_projet_projetstates
   if (TableExists("glpi_dropdown_plugin_projet_status")) {
      $query = "RENAME TABLE `glpi_dropdown_plugin_projet_status` TO `glpi_plugin_projet_projetstates` ";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_projetstates", "ID")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projetstates` 
                  CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
      $DB->query($query);
   }   
   if (FieldExists("glpi_plugin_projet_projetstates", "comments")) {
      $query = "ALTER TABLE `glpi_plugin_projet_projetstates` 
                  CHANGE `comments` `comment` TEXT CHARACTER 
                     SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL";

      $DB->query($query);
   }
   
   // ** Update glpi_plugin_projet_tasktypes
   if (TableExists("glpi_dropdown_plugin_projet_tasks_type")) {
      $query = "RENAME TABLE `glpi_dropdown_plugin_projet_tasks_type` TO `glpi_plugin_projet_tasktypes` ";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_tasktypes", "ID")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasktypes` 
                  CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
      $DB->query($query);
   }   
   if (FieldExists("glpi_plugin_projet_tasktypes", "FK_entities")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasktypes` 
                  CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_tasktypes", "comments")) {
      $query = "ALTER TABLE `glpi_plugin_projet_tasktypes` 
                  CHANGE `comments` `comment` TEXT CHARACTER 
                     SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL";
      $DB->query($query);
   }
   
   // ** Update glpi_plugin_projet_taskstates
   if (TableExists("glpi_dropdown_plugin_projet_task_status")) {
      $query = "RENAME TABLE `glpi_dropdown_plugin_projet_task_status` TO `glpi_plugin_projet_taskstates` ";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_taskstates", "ID")) {
      $query = "ALTER TABLE `glpi_plugin_projet_taskstates` 
                  CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_taskstates", "comments")) {
      $query = "ALTER TABLE `glpi_plugin_projet_taskstates` 
                  CHANGE `comments` `comment` TEXT CHARACTER 
                     SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL";
      $DB->query($query);
   }
   
   // ** Update glpi_plugin_projet_mailings
   if (TableExists("glpi_plugin_projet_mailing")) {
      $query = "RENAME TABLE `glpi_plugin_projet_mailing` TO `glpi_plugin_projet_mailings` ";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_mailings", "ID")) {
      $query = "ALTER TABLE `glpi_plugin_projet_mailings` 
                  CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_mailings", "FK_item")) {
      $query = "ALTER TABLE `glpi_plugin_projet_mailings` 
                  CHANGE `FK_item` `plugin_projet_items_id` int(11) NOT NULL default '0'";
      $DB->query($query);
   }
   if (FieldExists("glpi_plugin_projet_mailings", "item_type")) {
      $query = "ALTER TABLE `glpi_plugin_projet_mailings` 
                  CHANGE `item_type` `itemtype` int(11) NOT NULL default '0'";
      $DB->query($query);
   }   
   if (isIndex("glpi_plugin_projet_mailings", "FK_item")) {
      $query = "ALTER TABLE `glpi_plugin_projet_mailings` 
                  DROP INDEX `FK_item` ,
                  ADD INDEX `plugin_projet_items_id` (`plugin_projet_items_id`) ";
      $DB->query($query);
   }
   if (isIndex("glpi_plugin_projet_mailings", "item_type")) {
      $query = "ALTER TABLE `glpi_plugin_projet_mailings` 
                  DROP INDEX `item_type` ,
                  ADD INDEX `itemtype` (`itemtype`) ";
      $DB->query($query);
   }
   
   // ** Update glpi_plugin_projet_profiles
   if (FieldExists("glpi_plugin_projet_profiles", "ID")) {
      $query = "ALTER TABLE `glpi_plugin_projet_profiles` 
                  CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
      $DB->query($query);
   }
   
   // Update itemtype in display preferences
   $query = "UPDATE `glpi_displaypreferences` 
               SET `itemtype` = 'PluginProjetProjet' WHERE `itemtype` = '2300'";
   $DB->query($query);
   $query = "UPDATE `glpi_displaypreferences` 
               SET `itemtype` = 'PluginProjetTask' WHERE `itemtype` = '2301'";
   $DB->query($query);
   
   // Update itemtype in tickets
   $query = "UPDATE `glpi_tickets` 
               SET `itemtype` = 'PluginProjetProjet' WHERE `itemtype` = '2300'";
   $DB->query($query);
   $query = "UPDATE `glpi_tickets` 
               SET `itemtype` = 'PluginProjetTask' WHERE `itemtype` = '2301'";
   $DB->query($query);
   
   // Update itemtype in bookmarks
   $query = "UPDATE `glpi_bookmarks` 
               SET `itemtype` = 'PluginProjetProjet' WHERE `itemtype` = '2300'";
   $DB->query($query);
   $query = "UPDATE `glpi_bookmarks` 
               SET `itemtype` = 'PluginProjetTask' WHERE `itemtype` = '2301'";
   $DB->query($query);
   
   $query = "UPDATE `glpi_bookmarks_users` 
               SET `itemtype` = 'PluginProjetProjet' WHERE `itemtype` = '2300'";
   $DB->query($query);
   $query = "UPDATE `glpi_bookmarks_users` 
               SET `itemtype` = 'PluginProjetTask' WHERE `itemtype` = '2301'";
   $DB->query($query);
   
   // Update itemtype for contracts
   $query = "UPDATE `glpi_contracts_items` 
               SET `itemtype` = 'PluginProjetProjet' WHERE `itemtype` = '2300'";
   $DB->query($query);
   $query = "UPDATE `glpi_contracts_items` 
               SET `itemtype` = 'PluginProjetTask' WHERE `itemtype` = '2301'";
   $DB->query($query);
   
   // Update itemtype for documents
   $query = "UPDATE `glpi_documents_items` 
               SET `itemtype` = 'PluginProjetProjet' WHERE `itemtype` = '2300'";
   $DB->query($query);
   $query = "UPDATE `glpi_documents_items` 
               SET `itemtype` = 'PluginProjetTask' WHERE `itemtype` = '2301'";
   $DB->query($query);
   
   // Update itemtype for logs
   $query = "UPDATE `glpi_logs` 
               SET `itemtype` = 'PluginProjetProjet' WHERE `itemtype` = '2300'";
   $DB->query($query);
   $query = "UPDATE `glpi_logs` 
               SET `itemtype` = 'PluginProjetTask' WHERE `itemtype` = '2301'";
   $DB->query($query);
   
   
   
   
   // Update itemtype
   // Convert itemtype to Class names
   $typetoname=array(
      GENERAL_TYPE => "",// For tickets
      COMPUTER_TYPE => "Computer",
      NETWORKING_TYPE => "NetworkEquipment",
      PRINTER_TYPE => "Printer",
      MONITOR_TYPE => "Monitor",
      PERIPHERAL_TYPE => "Peripheral",
      SOFTWARE_TYPE => "Software",
      CONTACT_TYPE => "Contact",
      ENTERPRISE_TYPE => "Supplier",
      INFOCOM_TYPE => "Infocom",
      CONTRACT_TYPE => "Contract",
      CARTRIDGEITEM_TYPE => "CartridgeItem",
      TYPEDOC_TYPE => "DocumentType",
      DOCUMENT_TYPE => "Document",
      KNOWBASE_TYPE => "KnowbaseItem",
      USER_TYPE => "User",
      TRACKING_TYPE => "Ticket",
      CONSUMABLEITEM_TYPE => "ConsumableItem",
      CONSUMABLE_TYPE => "Consumable",
      CARTRIDGE_TYPE => "Cartridge",
      SOFTWARELICENSE_TYPE => "SoftwareLicense",
      LINK_TYPE => "Link",
      STATE_TYPE => "States",
      PHONE_TYPE => "Phone",
      DEVICE_TYPE => "Device",
      REMINDER_TYPE => "Reminder",
      STAT_TYPE => "Stat",
      GROUP_TYPE => "Group",
      ENTITY_TYPE => "Entity",
      RESERVATION_TYPE => "ReservationItem",
      AUTHMAIL_TYPE => "AuthMail",
      AUTHLDAP_TYPE => "AuthLDAP",
      OCSNG_TYPE => "OcsServer",
      REGISTRY_TYPE => "RegistryKey",
      PROFILE_TYPE => "Profile",
      MAILGATE_TYPE => "MailCollector",
      RULE_TYPE => "Rule",
      TRANSFER_TYPE => "Transfer",
      BOOKMARK_TYPE => "Bookmark",
      SOFTWAREVERSION_TYPE => "SoftwareVersion",
      PLUGIN_TYPE => "Plugin",
      COMPUTERDISK_TYPE => "ComputerDisk",
      NETWORKING_PORT_TYPE => "NetworkPort",
      FOLLOWUP_TYPE => "TicketFollowup",
      BUDGET_TYPE => "Budget",
      // End is not used in 0.72.x
   );
   foreach ($typetoname as $key => $val) {
      $query = "UPDATE `glpi_plugin_projet_projetitems` 
                  SET `itemtype` = '$val' WHERE `itemtype` = '$key'";
      $DB->query($query);
      $query = "UPDATE `glpi_plugin_projet_taskitems` 
                  SET `itemtype` = '$val' WHERE `itemtype` = '$key'";
      $DB->query($query);
      $query = "UPDATE `glpi_plugin_projet_mailings` 
                  SET `itemtype` = '$val' WHERE `itemtype` = '$key'";
      $DB->query($query);      
   }

}

?>