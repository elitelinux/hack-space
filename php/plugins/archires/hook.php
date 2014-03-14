<?php
/*
 * @version $Id: hook.php 189 2013-08-03 14:06:18Z tsmr $
 -------------------------------------------------------------------------
 Archires plugin for GLPI
 Copyright (C) 2003-2013 by the archires Development Team.

 https://forge.indepnet.net/projects/archires
 -------------------------------------------------------------------------

 LICENSE

 This file is part of archires.

 Archires is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Archires is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Archires. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_archires_install() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/archires/inc/profile.class.php");
   $update = false;


   if (!TableExists("glpi_plugin_archires_config") && !TableExists("glpi_plugin_archires_views")) {
      $DB->runFile(GLPI_ROOT ."/plugins/archires/sql/empty-2.1.0.sql");

   } else {
      $update = true;

      // update to 1.3
      if (TableExists("glpi_plugin_archires_display")
          && !FieldExists("glpi_plugin_archires_display","display_ports")) {

         $migration = new Migration(13);

         $migration->addField("glpi_plugin_archires_display", "display_ports",
                              "ENUM('1', '0') NOT NULL DEFAULT '0'");

         $migration->executeMigration();
      }

      // update to 1.4
      if (TableExists("glpi_plugin_archires_display")
          && !TableExists("glpi_plugin_archires_profiles")) {

         plugin_archires_updateTo14();
      }

      // update to 1.5
      if (TableExists("glpi_plugin_archires_display")
          && !TableExists("glpi_plugin_archires_image_device")) {

         plugin_archires_updateTo15();
      }

      // update to 1.7.0
      if (TableExists("glpi_plugin_archires_profiles")
          && FieldExists("glpi_plugin_archires_profiles","interface")) {

         plugin_archires_updateTo170();
      }

      // update to 1.7.2
      if (TableExists("glpi_plugin_archires_config")
          && FieldExists("glpi_plugin_archires_config","system")) {

         $migration = new Migration(172);

         $migration->dropField("glpi_plugin_archires_config", "system");

         $migration->executeMigration();
      }

      // update to 1.8.0
      if (!TableExists("glpi_plugin_archires_views")) {
         plugin_archires_updateTo180();
      }

      // update to 2.1.0
      if (TableExists("glpi_plugin_archires_appliancequeries")
          && !FieldExists("glpi_plugin_archires_appliancequeries", "plugin_appliances_appliances_id")) {
         plugin_archires_updateTo210();
      }
   }

   if ($update) {

      $table = "glpi_plugin_archires_statecolors";
      $index = "state";
      if (isIndex($table, $index)) {
         $query = "ALTER TABLE `$table` DROP INDEX `$index`;";
         $result = $DB->query($query);
      }

      $query_  = "SELECT *
                  FROM `glpi_plugin_archires_profiles` ";
      $result_ = $DB->query($query_);
      if ($DB->numrows($result_) > 0) {

         while ($data=$DB->fetch_array($result_)) {
            $query = "UPDATE `glpi_plugin_archires_profiles`
                      SET `profiles_id` = '".$data["id"]."'
                      WHERE `id` = '".$data["id"]."';";
            $result = $DB->query($query);

         }
      }

      if (FieldExists("glpi_plugin_archires_profiles", "name")) {
         $query  = "ALTER TABLE `glpi_plugin_archires_profiles`
                    DROP `name`";
      }
      $result = $DB->query($query);

      Plugin::migrateItemType(array(3000 => 'PluginArchiresLocationQuery',
                                    3001 => 'PluginArchiresNetworkEquipmentQuery',
                                    3002 => 'PluginArchiresApplianceQuery',
                                    3003 => 'PluginArchiresView'),
                              array("glpi_bookmarks", "glpi_bookmarks_users",
                                    "glpi_displaypreferences", "glpi_documents_items",
                                    "glpi_infocoms", "glpi_logs", "glpi_tickets"),
                              array("glpi_plugin_archires_querytypes",
                                    "glpi_plugin_archires_imageitems"));
   }

   $rep_files_archires = realpath(GLPI_PLUGIN_DOC_DIR)."/archires";
   if (!is_dir($rep_files_archires)
       && !mkdir($rep_files_archires)) {
      die(sprintf(__('Failed to create the directory %s. Verify that you have the correct permission'),
                  $rep_files_archires));
   }

   PluginArchiresProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}


function plugin_archires_updateTo14() {

   $migration = new Migration(14);

   if (!TableExists("glpi_plugin_archires_color")) {
      $query = "CREATE TABLE `glpi_plugin_archires_color` (
                  `ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `iface` INT( 11 ) NOT NULL ,
                  `color` VARCHAR( 50 ) collate utf8_unicode_ci NOT NULL
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, __('1.4 add glpi_plugin_archires_color ', 'archires').$DB->error());
   }

   if (!TableExists("glpi_plugin_archires_profiles")) {
      $query = "CREATE TABLE `glpi_plugin_archires_profiles` (
                  `ID` int(11) NOT NULL auto_increment,
                  `name` varchar(255) collate utf8_unicode_ci default NULL,
                  `interface` varchar(50) collate utf8_unicode_ci NOT NULL default 'archires',
                  `is_default` enum('0','1') NOT NULL default '0',
                  `archires` char(1) default NULL,
                  PRIMARY KEY  (`ID`),
                  KEY `interface` (`interface`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryorDie($query, __('1.4 add glpi_plugin_archires_profiles ', 'archires').$DB->error());

      $query = "INSERT INTO `glpi_plugin_archires_profiles`
                       (`ID`, `name` , `interface`, `is_default`, `archires`)
                VALUES ('1', 'post-only', 'archires', '1', NULL),
                       ('2', 'normal', 'archires', '0', NULL),
                       ('3', 'admin', 'archires', '0', 'r'),
                       ('4', 'super-admin', 'archires', '0', 'r')";
      $DB->queryOrDie($query, __('1.4 insert into glpi_plugin_archires_profiles ', 'archires')
                                 .$DB->error());
   }

   $migration->addField("glpi_plugin_archires_display", "display_ip",
                        "ENUM('1', '0') NOT NULL DEFAULT '0'");
   $migration->addField("glpi_plugin_archires_display", "system",
                        "ENUM('1', '0') NOT NULL DEFAULT '0'");

   $migration->executeMigration();
}


function plugin_archires_updateTo15() {

   $migration = new Migration(15);

   $migration->changeField("glpi_plugin_archires_profiles", "is_default", "is_default",
                           "smallint(6) NOT NULL default '0'");

   $query = "UPDATE `glpi_plugin_archires_profiles`
             SET `is_default` = '0'
             WHERE `is_default` = '1'";
   $DB->queryOrDie($query, __('1.5 insert into glpi_plugin_archires_profiles ', 'archires')
                              .$DB->error());

   $query = "UPDATE `glpi_plugin_archires_profiles`
             SET `is_default` = '1'
             WHERE `is_default` = '2'";
   $DB->queryOrDie($query, __('1.5 insert into glpi_plugin_archires_profiles ', 'archires')
                              .$DB->error());

   $migration->renameTable("glpi_plugin_archires_color", "glpi_plugin_archires_color_iface");

   $migration->renameTable("glpi_plugin_archires_config", "glpi_plugin_archires_image_device");

   $migration->dropTable("glpi_plugin_archires_display");

   if (!TableExists("glpi_plugin_archires_color_state")) {
      $query = "CREATE TABLE `glpi_plugin_archires_color_state` (
                  `ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `state` INT( 11 ) NOT NULL ,
                  `color` VARCHAR( 50 ) collate utf8_unicode_ci NOT NULL
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, __('1.5 create glpi_plugin_archires_color_state ', 'archires')
                                 .$DB->error());
   }

   if (!TableExists("glpi_plugin_archires_query_location")) {
      $query = "CREATE TABLE `glpi_plugin_archires_query_location` (
                  `ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `FK_entities` int(11) NOT NULL default '0',
                  `name` VARCHAR( 50 ) collate utf8_unicode_ci NOT NULL,
                  `location` VARCHAR( 50 ) collate utf8_unicode_ci NOT NULL DEFAULT '0',
                  `child` smallint(6) NOT NULL default '0',
                  `network` INT( 11 ) NOT NULL DEFAULT '0',
                  `status` INT( 11 ) NOT NULL DEFAULT '0',
                  `FK_group` INT( 11 ) NOT NULL DEFAULT '0',
                  `FK_config` INT( 11 ) NOT NULL DEFAULT '0',
                  `FK_vlan` INT( 11 ) NOT NULL DEFAULT '0',
                  `link` smallint(6) NOT NULL default '1',
                  `notes` LONGTEXT,
                  `deleted` smallint(6) NOT NULL default '0'
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, __('1.5 create glpi_plugin_archires_query_location ', 'archires')
                                 .$DB->error());
   }

   if (!TableExists("glpi_plugin_archires_query_switch")) {
      $query = "CREATE TABLE `glpi_plugin_archires_query_switch` (
                  `ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `FK_entities` int(11) NOT NULL default '0',
                  `name` VARCHAR( 50 ) collate utf8_unicode_ci NOT NULL,
                  `switch` INT( 11 ) NOT NULL DEFAULT '0',
                  `network` INT( 11 ) NOT NULL DEFAULT '0',
                  `status` INT( 11 ) NOT NULL DEFAULT '0',
                  `FK_group` INT( 11 ) NOT NULL DEFAULT '0',
                  `FK_config` INT( 11 ) NOT NULL DEFAULT '0',
                  `FK_vlan` INT( 11 ) NOT NULL DEFAULT '0',
                  `link` smallint(6) NOT NULL default '1',
                  `notes` LONGTEXT,
                  `deleted` smallint(6) NOT NULL default '0'
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, __('1.5 create glpi_plugin_archires_query_switch ', 'archires')
                                 .$DB->error());
   }

   if (!TableExists("glpi_plugin_archires_query_applicatifs")) {
      $query = "CREATE TABLE `glpi_plugin_archires_query_applicatifs` (
                  `ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `FK_entities` int(11) NOT NULL default '0',
                  `name` VARCHAR( 50 ) collate utf8_unicode_ci NOT NULL,
                  `applicatifs` INT( 11 ) NOT NULL DEFAULT '0',
                  `network` INT( 11 ) NOT NULL DEFAULT '0',
                  `status` INT( 11 ) NOT NULL DEFAULT '0',
                  `FK_group` INT( 11 ) NOT NULL DEFAULT '0',
                  `FK_config` INT( 11 ) NOT NULL DEFAULT '0',
                  `FK_vlan` INT( 11 ) NOT NULL DEFAULT '0',
                  `link` smallint(6) NOT NULL default '1',
                  `notes` LONGTEXT,
                  `deleted` smallint(6) NOT NULL default '0'
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, __('1.5 create glpi_plugin_archires_query_applicatifs ', 'archires')
                                 .$DB->error());
   }

   if (!TableExists("glpi_plugin_archires_query_type")) {
      $query = "CREATE TABLE `glpi_plugin_archires_query_type` (
                  `ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `type_query` INT( 11 ) NOT NULL ,
                  `type` INT( 11 ) NOT NULL ,
                  `device_type` INT( 11 ) NOT NULL,
                  `FK_query` INT( 11 ) NOT NULL
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, __('1.5 create glpi_plugin_archires_query_type ', 'archires')
                                 .$DB->error());
   }

   if (!TableExists("glpi_plugin_archires_config")) {
      $query = "CREATE TABLE `glpi_plugin_archires_config` (
                  `ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `FK_entities` int(11) NOT NULL default '0',
                  `name` VARCHAR( 250 ) collate utf8_unicode_ci NOT NULL,
                  `computer` smallint(6) NOT NULL default '0',
                  `networking` smallint(6) NOT NULL default '0',
                  `printer` smallint(6) NOT NULL default '0',
                  `peripheral` smallint(6) NOT NULL default '0',
                  `phone` smallint(6) NOT NULL default '0',
                  `display_ports` smallint(6) NOT NULL default '0',
                  `display_ip` smallint(6) NOT NULL default '0',
                  `display_type` smallint(6) NOT NULL default '0',
                  `display_state` smallint(6) NOT NULL default '0',
                  `display_location` smallint(6) NOT NULL default '0',
                  `display_entity` smallint(6) NOT NULL default '0',
                  `system` smallint(6) NOT NULL default '0',
                  `engine` smallint(6) NOT NULL default '0',
                  `format` smallint(6) NOT NULL default '0',
                  `deleted` smallint(6) NOT NULL default '0'
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, __('1.5 create glpi_plugin_archires_config ', 'archires').$DB->error());
   }

   $query = "INSERT INTO `glpi_plugin_archires_config`
                    (`ID`,`FK_entities`,`name`, `computer` , `networking`, `printer`, `peripheral`,
                     `phone`, `display_ports`, `display_ip`, `display_type`, `display_state`,
                     `display_location`, `display_entity`, `system`,`engine`, `format`)
             VALUES ('1', '0', 'default', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0',
                     '0', '0', '1')";
   $DB->queryOrDie($query, __('1.5 insert into glpi_plugin_archires_config ', 'archires').$DB->error());

   $query = "INSERT INTO `glpi_displaypreferences`
                    (`ID` , `type` , `num` , `rank` , `FK_users` )
             VALUES (NULL, '3000', '2', '1', '0'),
                    (NULL, '3000', '3', '2', '0'),
                    (NULL, '3000', '4', '3', '0'),
                    (NULL, '3000', '5', '4', '0'),
                    (NULL, '3000', '6', '5', '0'),
                    (NULL, '3000', '7', '6', '0'),
                    (NULL, '3000', '8', '7', '0'),
                    (NULL, '3000', '9', '8', '0'),
                    (NULL, '3001', '2', '1', '0'),
                    (NULL, '3001', '3', '2', '0'),
                    (NULL, '3001', '4', '3', '0'),
                    (NULL, '3001', '5', '4', '0'),
                    (NULL, '3001', '6', '5', '0'),
                    (NULL, '3001', '7', '6', '0'),
                    (NULL, '3001', '8', '7', '0'),
                    (NULL, '3002', '2', '1', '0'),
                    (NULL, '3002', '3', '2', '0'),
                    (NULL, '3002', '4', '3', '0'),
                    (NULL, '3002', '5', '4', '0'),
                    (NULL, '3002', '6', '5', '0'),
                    (NULL, '3002', '7', '6', '0'),
                    (NULL, '3002', '8', '7', '0')";
   $DB->queryOrDie($query, __('1.5 insert into glpi_displaypreferences ', 'archires').$DB->error());

   $migration->executeMigration();
}


function plugin_archires_updateTo170() {

   $migration = new Migration(170);

   $migration->addKey("glpi_plugin_archires_query_location", "deleted");

   $migration->addKey("glpi_plugin_archires_query_switch", "deleted");

   $migration->addKey("glpi_plugin_archires_query_applicatifs", "deleted");

   $migration->addKey("glpi_plugin_archires_image_device", "device_type");

   $migration->addKey("glpi_plugin_archires_query_type", "FK_query");
   $migration->addKey("glpi_plugin_archires_query_type", "type_query");
   $migration->addKey("glpi_plugin_archires_query_type", "type");
   $migration->addKey("glpi_plugin_archires_query_type", "device_type");

   $migration->addKey("glpi_plugin_archires_color_iface", "iface");

   $migration->addKey("glpi_plugin_archires_config", "deleted");
   $migration->addKey("glpi_plugin_archires_config", "FK_entities");
   $migration->addKey("glpi_plugin_archires_config", "name");
   $migration->addField("glpi_plugin_archires_config", "color", "smallint(6) NOT NULL default '0'");

   if (!TableExists("glpi_plugin_archires_color_vlan")) {
      $query = "CREATE TABLE `glpi_plugin_archires_color_vlan` (
                  `ID` INT( 11 ) NOT NULL auto_increment,
                  `vlan` INT( 11 ) NOT NULL ,
                  `color` VARCHAR( 50 ) collate utf8_unicode_ci NOT NULL,
                  PRIMARY KEY  (`ID`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, __('1.7.0 create glpi_plugin_archires_color_vlan ', 'archires')
                                 .$DB->error());
   }

   $migration->dropField("glpi_plugin_archires_profiles", "interface");
   $migration->dropField("glpi_plugin_archires_profiles", "is_default");

   $migration->changeField("glpi_plugin_archires_query_location", "`status", "state", 'integer');

   $migration->changeField("glpi_plugin_archires_query_switch", "`status", "state", 'integer');

   $migration->changeField("glpi_plugin_archires_query_applicatifs", "`status", "state", 'integer');

   $migration->executeMigration();
}


function plugin_archires_updateTo180() {

   $migration = new Migration(180);

   $migration->renameTable("glpi_plugin_archires_query_location",
                           "glpi_plugin_archires_locationqueries");

   $migration->renameTable("glpi_plugin_archires_query_switch",
                           "glpi_plugin_archires_networkequipmentqueries");

   $migration->renameTable("glpi_plugin_archires_query_applicatifs",
                           "glpi_plugin_archires_appliancequeries");

   $migration->renameTable("glpi_plugin_archires_image_device", "glpi_plugin_archires_imageitems");

   $migration->renameTable("glpi_plugin_archires_query_type", "glpi_plugin_archires_querytypes");

   $migration->renameTable("glpi_plugin_archires_color_iface",
                           "glpi_plugin_archires_networkinterfacecolors");

   $migration->renameTable("glpi_plugin_archires_color_state", "glpi_plugin_archires_statecolors");

   $migration->renameTable("glpi_plugin_archires_color_vlan", "glpi_plugin_archires_vlancolors");

   $migration->renameTable("glpi_plugin_archires_config", "glpi_plugin_archires_views");


   $migration->dropKey("glpi_plugin_archires_locationqueries", "deleted");
   $migration->changeField("glpi_plugin_archires_locationqueries", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_archires_locationqueries", "name", "name", 'string');
   $migration->changeField("glpi_plugin_archires_locationqueries", "FK_entities", "entities_id",
                           'integer');
   $migration->changeField("glpi_plugin_archires_locationqueries", "location", "locations_id",
                           'integer', array('comment' => 'RELATION to glpi_locations (id)'));
   $migration->changeField("glpi_plugin_archires_locationqueries", "network", "networks_id",
                           'integer', array('comment' => 'RELATION to glpi_networks (id)'));
   $migration->changeField("glpi_plugin_archires_locationqueries", "state", "states_id",
                           'integer', array('comment' => 'RELATION to glpi_states (id)'));
   $migration->changeField("glpi_plugin_archires_locationqueries", "FK_group", "groups_id",
                           'integer', array('comment' => 'RELATION to glpi_groups (id)'));
   $migration->changeField("glpi_plugin_archires_locationqueries", "FK_config",
                           "plugin_archires_views_id", 'integer',
                           array('comment' => 'RELATION to glpi_plugin_archires_views (id)'));
   $migration->changeField("glpi_plugin_archires_locationqueries", "FK_vlan", "vlans_id",
                           'integer', array('comment' => 'RELATION to glpi_vlans (id)'));
   $migration->changeField("glpi_plugin_archires_locationqueries", "deleted", "is_deleted", 'bool');
   $migration->changeField("glpi_plugin_archires_locationqueries", "notes", "notepad", 'longtext');
   $migration->dropField("glpi_plugin_archires_locationqueries", "link");
   $migration->addKey("glpi_plugin_archires_locationqueries", "name");
   $migration->addKey("glpi_plugin_archires_locationqueries", "entities_id");
   $migration->addKey("glpi_plugin_archires_locationqueries", "locations_id");
   $migration->addKey("glpi_plugin_archires_locationqueries", "networks_id");
   $migration->addKey("glpi_plugin_archires_locationqueries", "groups_id");
   $migration->addKey("glpi_plugin_archires_locationqueries", "plugin_archires_views_id");
   $migration->addKey("glpi_plugin_archires_locationqueries", "states_id");
   $migration->addKey("glpi_plugin_archires_locationqueries", "vlans_id");
   $migration->addKey("glpi_plugin_archires_locationqueries", "is_deleted");


   $migration->dropKey("glpi_plugin_archires_networkequipmentqueries", "deleted");
   $migration->changeField("glpi_plugin_archires_networkequipmentqueries", "ID", "id",
                           'autoincrement');
   $migration->changeField("glpi_plugin_archires_networkequipmentqueries", "name", "name", 'string');
   $migration->changeField("glpi_plugin_archires_networkequipmentqueries", "FK_entities",
                           "entities_id", 'integer');
   $migration->changeField("glpi_plugin_archires_networkequipmentqueries", "switch",
                           "networkequipments_id", 'integer',
                           array('comment' => 'RELATION to glpi_networkequipments (id)'));
   $migration->changeField("glpi_plugin_archires_networkequipmentqueries", "network", "networks_id",
                           'integer', array('comment' => 'RELATION to glpi_networks (id)'));
   $migration->changeField("glpi_plugin_archires_networkequipmentqueries", "state", "states_id",
                           'integer', array('comment' => 'RELATION to glpi_states (id)'));
   $migration->changeField("glpi_plugin_archires_networkequipmentqueries", "FK_group", "groups_id",
                           'integer', array('comment' => 'RELATION to glpi_groups (id)'));
   $migration->changeField("glpi_plugin_archires_networkequipmentqueries", "FK_config",
                           "plugin_archires_views_id", 'integer',
                           array('comment' => 'RELATION to glpi_plugin_archires_views (id)'));
   $migration->changeField("glpi_plugin_archires_networkequipmentqueries", "FK_vlan", "vlans_id",
                           'integer', array('comment' => 'RELATION to glpi_vlans (id)'));
   $migration->changeField("glpi_plugin_archires_networkequipmentqueries", "deleted", "is_deleted",
                           'bool');
   $migration->changeField("glpi_plugin_archires_networkequipmentqueries", "notes", "notepad",
                           'longtext');
   $migration->dropField("glpi_plugin_archires_networkequipmentqueries", "link");
   $migration->addKey("glpi_plugin_archires_networkequipmentqueries", "name");
   $migration->addKey("glpi_plugin_archires_networkequipmentqueries", "entities_id");
   $migration->addKey("glpi_plugin_archires_networkequipmentqueries", "networkequipments_id");
   $migration->addKey("glpi_plugin_archires_networkequipmentqueries", "networks_id");
   $migration->addKey("glpi_plugin_archires_networkequipmentqueries", "groups_id");
   $migration->addKey("glpi_plugin_archires_networkequipmentqueries", "plugin_archires_views_id");
   $migration->addKey("glpi_plugin_archires_networkequipmentqueries", "states_id");
   $migration->addKey("glpi_plugin_archires_networkequipmentqueries", "vlans_id");
   $migration->addKey("glpi_plugin_archires_networkequipmentqueries", "is_deleted");


   $migration->dropKey("glpi_plugin_archires_appliancequeries", "deleted");
   $migration->changeField("glpi_plugin_archires_appliancequeries", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_archires_appliancequeries", "name", "name", 'string');
   $migration->changeField("glpi_plugin_archires_appliancequeries", "FK_entities", "entities_id",
                           'integer');
   $migration->changeField("glpi_plugin_archires_appliancequeries", "applicatifs", "appliances_id",
                           'integer', array('comment' => 'RELATION to glpi_plugin_appliances (id)'));
   $migration->changeField("glpi_plugin_archires_appliancequeries", "network", "networks_id",
                           'integer', array('comment' => 'RELATION to glpi_networks (id)'));
   $migration->changeField("glpi_plugin_archires_appliancequeries", "state", "states_id",
                           'integer', array('comment' => 'RELATION to glpi_states (id)'));
   $migration->changeField("glpi_plugin_archires_appliancequeries", "FK_group", "groups_id",
                           'integer', array('comment' => 'RELATION to glpi_groups (id)'));
   $migration->changeField("glpi_plugin_archires_appliancequeries", "FK_config",
                           "plugin_archires_views_id", 'integer',
                           array('comment' => 'RELATION to glpi_plugin_archires_views (id)'));
   $migration->changeField("glpi_plugin_archires_appliancequeries", "FK_vlan", "vlans_id",
                           'integer', array('comment' => 'RELATION to glpi_vlans (id)'));
   $migration->changeField("glpi_plugin_archires_appliancequeries", "deleted", "is_deleted", 'bool');
   $migration->changeField("glpi_plugin_archires_appliancequeries", "notes", "notepad", 'longtext');
   $migration->dropField("glpi_plugin_archires_appliancequeries", "link");
   $migration->addKey("glpi_plugin_archires_appliancequeries", "name");
   $migration->addKey("glpi_plugin_archires_appliancequeries", "entities_id");
   $migration->addKey("glpi_plugin_archires_appliancequeries", "appliances_id");
   $migration->addKey("glpi_plugin_archires_appliancequeries", "networks_id");
   $migration->addKey("glpi_plugin_archires_appliancequeries", "groups_id");
   $migration->addKey("glpi_plugin_archires_appliancequeries", "plugin_archires_views_id");
   $migration->addKey("glpi_plugin_archires_appliancequeries", "states_id");
   $migration->addKey("glpi_plugin_archires_appliancequeries", "vlans_id");
   $migration->addKey("glpi_plugin_archires_appliancequeries", "is_deleted");


   $migration->dropKey("glpi_plugin_archires_imageitems", "device_type");
   $migration->changeField("glpi_plugin_archires_imageitems", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_archires_imageitems", "type", "type", 'integer');
   $migration->changeField("glpi_plugin_archires_imageitems", "device_type", "itemtype",
                           "varchar(100) collate utf8_unicode_ci NOT NULL",
                           array('comment' => 'see .class.php file'));


   $migration->dropKey("glpi_plugin_archires_querytypes", "FK_query");
   $migration->dropKey("glpi_plugin_archires_querytypes", "type");
   $migration->dropKey("glpi_plugin_archires_querytypes", "type_query");
   $migration->dropKey("glpi_plugin_archires_querytypes", "device_type");
   $migration->changeField("glpi_plugin_archires_querytypes", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_archires_querytypes", "type_query", "querytype",
                           "varchar(100) collate utf8_unicode_ci NOT NULL",
                           array('comment' => 'RELATION to the 3 type of archires (type)'));
   $migration->changeField("glpi_plugin_archires_querytypes", "type", "type", 'integer');
   $migration->changeField("glpi_plugin_archires_querytypes", "device_type", "itemtype",
                           "varchar(100) collate utf8_unicode_ci NOT NULL",
                           array('comment' => 'see .class.php file'));
   $migration->changeField("glpi_plugin_archires_querytypes", "FK_query",
                           "plugin_archires_queries_id", 'integer',
                           array('comment' => 'RELATION to the 3 queries tables (id)'));
   $migration->addKey("glpi_plugin_archires_querytypes", "querytype");
   $migration->addKey("glpi_plugin_archires_querytypes", "type");
   $migration->addKey("glpi_plugin_archires_querytypes", "itemtype");
   $migration->addKey("glpi_plugin_archires_querytypes", "plugin_archires_queries_id");

   $migration->migrationOneTable("glpi_plugin_archires_querytypes");

   $query = "UPDATE `glpi_plugin_archires_querytypes`
             SET `querytype` = 'PluginArchiresLocationQuery'
             WHERE `querytype` = 0";
   $DB->queryOrDie($query, __('1.8.0 update glpi_plugin_archires_querytypes (querytype) ',
                              'archires').$DB->error());

   $query = "UPDATE `glpi_plugin_archires_querytypes`
             SET `querytype` = 'PluginArchiresNetworkEquipmentQuery'
             WHERE `querytype` = 1";
   $DB->queryOrDie($query, __('1.8.0 update glpi_plugin_archires_querytypes (querytype) ',
                              'archires').$DB->error());

   $query = "UPDATE `glpi_plugin_archires_querytypes`
             SET `querytype` = 'PluginArchiresApplianceQuery'
             WHERE `querytype` = 2";
   $DB->queryOrDie($query, __('1.8.0 update glpi_plugin_archires_querytypes (querytype) ',
                              'archires').$DB->error());


   $migration->dropKey("glpi_plugin_archires_networkinterfacecolors", "iface");
   $migration->changeField("glpi_plugin_archires_networkinterfacecolors", "ID", "id",
                           'autoincrement');
   $migration->changeField("glpi_plugin_archires_networkinterfacecolors", "iface",
                           "networkinterfaces_id", 'integer',
                           array('comment' => 'RELATION to glpi_networkinterfaces (id)'));
   $migration->addKey("glpi_plugin_archires_networkinterfacecolors", "networkinterfaces_id");


   $migration->changeField("glpi_plugin_archires_statecolors", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_archires_statecolors", "state", "states_id", 'integer',
                           array('comment' => 'RELATION to glpi_states (id)'));
   $migration->addKey("glpi_plugin_archires_statecolors", "states_id");


   $migration->changeField("glpi_plugin_archires_vlancolors", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_archires_vlancolors", "vlan", "vlans_id", 'integer',
                           array('comment' => 'RELATION to glpi_vlans (id)'));
   $migration->addKey("glpi_plugin_archires_vlancolors", "vlans_id");


   $migration->dropKey("glpi_plugin_archires_views", "deleted");
   $migration->dropKey("glpi_plugin_archires_views", "FK_entities");
   $migration->changeField("glpi_plugin_archires_views", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_archires_views", "name", "name", 'string');
   $migration->changeField("glpi_plugin_archires_views", "FK_entities", "entities_id", 'integer');
   $migration->changeField("glpi_plugin_archires_views", "deleted", "is_deleted", 'bool');
   $migration->addKey("glpi_plugin_archires_views", "entities_id");
   $migration->addKey("glpi_plugin_archires_views", "is_deleted");


   $migration->changeField("glpi_plugin_archires_profiles", "ID", "id", 'autoincrement');
   $migration->addField("glpi_plugin_archires_profiles", "profiles_id", 'integer',
                        array('comment' => 'RELATION to glpi_profiles (id)'));
   $migration->changeField("glpi_plugin_archires_profiles", "archires", "archires", 'char');
   $migration->addKey("glpi_plugin_archires_profiles", "profiles_id");


   $query = "DELETE
             FROM `glpi_displaypreferences`
             WHERE `itemtype` = 3000 AND `num` = 9";
   $DB->queryOrDie($query, __('1.8.0 delete glpi_displaypreferences (itemtype) ', 'archires')
                              .$DB->error());

   $query = "DELETE
             FROM `glpi_displaypreferences`
             WHERE `itemtype` = 3001 AND `num` = 8";
   $DB->queryOrDie($query, __('1.8.0 delete glpi_displaypreferences (itemtype) ', 'archires')
                              .$DB->error());

   $query = "DELETE
             FROM `glpi_displaypreferences`
             WHERE `itemtype` = 3002 AND `num` = 8";
   $DB->queryOrDie($query, __('1.8.0 delete glpi_displaypreferences (itemtype) ', 'archires')
                              .$DB->error());


   $migration->executeMigration();
}


function plugin_archires_updateTo210() {

   $migration = new Migration(210);

   $migration->changeField("glpi_plugin_archires_appliancequeries", "appliances_id",
                           "plugin_appliances_appliances_id",
                           'integer', array('comment' => 'RELATION to glpi_plugin_appliances (id)'));

   $migration->executeMigration();
}


function plugin_archires_uninstall() {
   global $DB;

   $tables = array("glpi_plugin_archires_imageitems",
                   "glpi_plugin_archires_views",
                   "glpi_plugin_archires_networkinterfacecolors",
                   "glpi_plugin_archires_vlancolors",
                   "glpi_plugin_archires_statecolors",
                   "glpi_plugin_archires_profiles",
                   "glpi_plugin_archires_locationqueries",
                   "glpi_plugin_archires_networkequipmentqueries",
                   "glpi_plugin_archires_appliancequeries",
                   "glpi_plugin_archires_querytypes");

   foreach($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }
   //old versions
   $tables = array("glpi_plugin_archires_query_location",
                   "glpi_plugin_archires_query_switch",
                   "glpi_plugin_archires_query_applicatifs",
                   "glpi_plugin_archires_image_device",
                   "glpi_plugin_archires_query_type",
                   "glpi_plugin_archires_color_iface",
                   "glpi_plugin_archires_color_state",
                   "glpi_plugin_archires_config",
                   "glpi_plugin_archires_color_vlan");

   foreach($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   $rep_files_archires = GLPI_PLUGIN_DOC_DIR."/archires";

   Toolbox::deleteDir($rep_files_archires);

   $tables_glpi = array("glpi_displaypreferences",
                        "glpi_documents_items",
                        "glpi_bookmarks",
                        "glpi_logs");

   foreach($tables_glpi as $table_glpi)
      $DB->query("DELETE FROM `$table_glpi`
                  WHERE `itemtype` = 'PluginArchiresLocationQuery'
                        OR `itemtype` = 'PluginArchiresNetworkEquipmentQuery'
                        OR `itemtype` = 'PluginArchiresApplianceQuery'
                        OR `itemtype` = 'PluginArchiresView';");

   return true;
}


// Define dropdown relations
function plugin_archires_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("archires")) {
      return array("glpi_locations"
                        => array("glpi_plugin_archires_locationqueries" => "locations_id"),
                   "glpi_networks"
                        => array("glpi_plugin_archires_locationqueries"         => "networks_id",
                                 "glpi_plugin_archires_appliancequeries"        => "networks_id",
                                 "glpi_plugin_archires_networkequipmentqueries" => "networks_id"),
                   "glpi_states"
                        => array("glpi_plugin_archires_locationqueries"          => "states_id",
                                 "glpi_plugin_archires_appliancequeries"         => "states_id",
                                 "glpi_plugin_archires_networkequipmentqueries"  => "states_id",
                                 "glpi_plugin_archires_statecolors"              => "states_id"),
                   "glpi_groups"
                        => array("glpi_plugin_archires_locationqueries"          => "groups_id",
                                 "glpi_plugin_archires_appliancequeries"         => "groups_id",
                                 "glpi_plugin_archires_networkequipmentqueries"  => "groups_id"),
                   "glpi_vlans"
                        => array("glpi_plugin_archires_locationqueries"          => "vlans_id",
                                 "glpi_plugin_archires_appliancequeries"         => "vlans_id",
                                 "glpi_plugin_archires_networkequipmentqueries"  => "vlans_id",
                                 "glpi_plugin_archires_vlancolors"               => "vlans_id"),
                   "glpi_entities"
                        => array("glpi_plugin_archires_locationqueries"          => "entities_id",
                                 "glpi_plugin_archires_networkequipmentqueries"  => "entities_id",
                                 "glpi_plugin_archires_appliancequeries"         => "entities_id",
                                 "glpi_plugin_archires_views"                    => "entities_id"),
                   "glpi_plugin_archires_views"
                        => array("glpi_plugin_archires_locationqueries"         => "plugin_archires_views_id",
                                 "glpi_plugin_archires_networkequipmentqueries" => "plugin_archires_views_id",
                                 "glpi_plugin_archires_appliancequeries"        => "plugin_archires_views_id"),
                   "glpi_plugin_appliances_appliances"
                        => array("glpi_plugin_archires_appliancequeries" => "appliances_id"),
                   "glpi_profiles"
                        => array("glpi_plugin_addressing_profiles" => "profiles_id"),
                   "glpi_networkinterfaces"
                        => array("glpi_plugin_archires_networkinterfacecolors" => "networkinterfaces_id"));
   } else {
      return array();
   }
}


////// SEARCH FUNCTIONS ///////() {

function plugin_archires_giveItem($type,$ID,$data,$num) {

   $searchopt = &Search::getOptions($type);

   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi_locations.completename" :
         if (empty($data["ITEM_$num"])) {
            $out = __('All root locations', 'archires');
         } else {
            $out = $data["ITEM_$num"];
         }
         return $out;

      case "glpi_networks.name" :
      case "glpi_states.name" :
      case "glpi_vlans.name" :
         if (empty($data["ITEM_$num"])) {
            $out = __('All');
         } else {
            $out = $data["ITEM_$num"];
         }
         return $out;

      case "glpi_networkequipments.name" :
      case "glpi_plugin_appliances_appliances.name" :
         if (empty($data["ITEM_$num"])) {
            $out = __('None');
         } else {
            $out = $data["ITEM_$num"];
         }
         return $out;

      case "glpi_plugin_archires_views.display_ports" :
         if (empty($data["ITEM_$num"])) {
            $out = __('No');
         } else if ($data["ITEM_$num"] == '1') {
            $out = __('See numbers', 'archires');
         } else if ($data["ITEM_$num"] == '2') {
            $out = __('See names', 'archires');
         }
         return $out;

      case "glpi_plugin_archires_views.engine" :
         if (empty($data["ITEM_$num"])) {
            $out = "Dot";
         } else if ($data["ITEM_$num"] == '1') {
            $out = "Neato";
         }
         return $out;

      case "glpi_plugin_archires_views.format" :
         if ($data["ITEM_$num"] == PluginArchiresView::PLUGIN_ARCHIRES_JPEG_FORMAT) {
            $out = "jpeg";
         } else if ($data["ITEM_$num"] == PluginArchiresView::PLUGIN_ARCHIRES_PNG_FORMAT) {
            $out = "png";
         } else if ($data["ITEM_$num"]  == PluginArchiresView::PLUGIN_ARCHIRES_GIF_FORMAT) {
            $out = "gif";
         }
         return $out;

      case "glpi_plugin_archires_views.color" :
         if (empty($data["ITEM_$num"])) {
            $out = __('Type of network', 'archires');
         } else if ($data["ITEM_$num"] == '1') {
            $out = __('VLAN');
         }
         return $out;
   }
   return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_archires_MassiveActions($type) {

   // Specific one
   switch ($type) {
      case 'PluginArchiresLocationQuery' :
      case 'PluginArchiresNetworkEquipmentQuery' :
      case 'PluginArchiresApplianceQuery' :
      case 'PluginArchiresView' :
         return array("plugin_archires_duplicate" => __('Duplicate'),
                      "plugin_archires_transfert" => __('Transfer'));
   }
   return array();
}


// How to display specific actions ?
function plugin_archires_MassiveActionsDisplay($options=array()) {

   switch ($options['itemtype']) {
      case 'PluginArchiresLocationQuery':
      case 'PluginArchiresNetworkEquipmentQuery' :
      case 'PluginArchiresApplianceQuery' :
      case 'PluginArchiresView':
         switch ($options['action']) {
            // No case for add_document : use GLPI core one
            case "plugin_archires_duplicate" :
            case "plugin_archires_transfert" :
               Dropdown::show('Entity');
               echo "&nbsp;<input type='submit' name='massiveaction' class='submit' value='".
                     _sx('button', 'Post')."'>";
               break;
         }
         break;
   }
   return "";
}


// How to process specific actions ?
function plugin_archires_MassiveActionsProcess($data) {
   global $DB;

   switch ($data['action']) {
      case 'plugin_archires_duplicate' :
         if (($data['itemtype'] == 'PluginArchiresLocationQuery')
             || ($data['itemtype']=='PluginArchiresNetworkEquipmentQuery')
             || ($data['itemtype']=='PluginArchiresApplianceQuery')
             || ($data['itemtype']=='PluginArchiresView')) {

            $item = new $data['itemtype']();
            foreach ($data['item'] as $key => $val) {
               if (($val == 1) && $item->getFromDB($key)) {
                  unset($item->fields["id"]);
                  $item->fields["entities_id"] = $data["entities_id"];
                  if ($item->can(-1,'w',$item->fields)) {
                     $item->add($item->fields);
                  }
               }
            }
         }
         break;

      case 'plugin_archires_transfert' :
         if (($data['itemtype']=='PluginArchiresLocationQuery')
             || ($data['itemtype']=='PluginArchiresNetworkEquipmentQuery')
             || ($data['itemtype']=='PluginArchiresApplianceQuery')
             || ($data['itemtype']=='PluginArchiresView')) {

            $item = new $data['itemtype']();
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  $values["id"]          = $key;
                  $values["entities_id"] = $data['entities_id'];
                  $item->update($values);

               }
            }
         }
         break;
   }
}
?>