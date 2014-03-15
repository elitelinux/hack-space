<?php
/*
 * @version $Id: HEADER 15930 2012-12-15 11:10:55Z tsmr $
-------------------------------------------------------------------------
Ocsinventoryng plugin for GLPI
Copyright (C) 2012-2013 by the ocsinventoryng plugin Development Team.

https://forge.indepnet.net/projects/ocsinventoryng
-------------------------------------------------------------------------

LICENSE

This file is part of ocsinventoryng.

Ocsinventoryng plugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Ocsinventoryng plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ocsinventoryng. If not, see <http://www.gnu.org/licenses/>.
-------------------------------------------------------------------------- */

function plugin_ocsinventoryng_install() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/ocsinventoryng/inc/profile.class.php");

    $migration = new Migration(100);


   if (!TableExists("glpi_plugin_ocsinventoryng_ocsservers")
       && !TableExists("ocs_glpi_ocsservers")) {

      $install = true;
      $DB->runFile(GLPI_ROOT ."/plugins/ocsinventoryng/install/mysql/1.0.0-empty.sql");
      CronTask::Register('PluginOcsinventoryngOcsServer', 'ocsng', MINUTE_TIMESTAMP*5);

      $migration->createRule(array('sub_type'      => 'RuleImportEntity',
                                   'entities_id'   => 0,
                                   'is_recursive'  => 1,
                                   'is_active'     => 1,
                                   'match'         => 'AND',
                                   'name'          => 'RootOcs'),
                            array(array('criteria'   => 'TAG',
                                        'condition'  => Rule::PATTERN_IS,
                                        'pattern'    => '*'),
                                  array('criteria'   => 'OCS_SERVER',
                                        'condition'  =>  Rule::PATTERN_IS,
                                        'pattern'    => 1)),
                            array(array('field'        => 'entities_id',
                                        'action_type'  => 'assign',
                                        'value'        => 0)));

   } else if (!TableExists("glpi_plugin_ocsinventoryng_ocsservers")
              && TableExists("ocs_glpi_ocsservers")) {

      $update = true;
      $DB->runFile(GLPI_ROOT ."/plugins/ocsinventoryng/install/mysql/1.0.0-update.sql");

      // recuperation des droits du core
      // creation de la table glpi_plugin_ocsinventoryng_profiles vide
      If (TableExists("ocs_glpi_profiles")
          && (TableExists('ocs_glpi_ocsservers')
              && countElementsInTable('ocs_glpi_ocsservers') > 0)) {

         $query = "INSERT INTO `glpi_plugin_ocsinventoryng_profiles`
                          (`profiles_id`, `ocsng`, `sync_ocsng`, `view_ocsng`, `clean_ocsng`,
                           `rule_ocs`)
                           SELECT `id`, `ocsng`, `sync_ocsng`, `view_ocsng`, `clean_ocsng`,
                                  `rule_ocs`
                           FROM `ocs_glpi_profiles`";
         $DB->queryOrDie($query, "1.0.0 insert profiles for OCS in plugin");
      }


      // recuperation des paramètres du core
      If (TableExists("ocs_glpi_crontasks")) {
         $query = "INSERT INTO `glpi_crontasks`
                          SELECT *
                          FROM `ocs_glpi_crontasks`
                          WHERE `itemtype` = 'OcsServer'";

         $DB->queryOrDie($query, "1.0.0 insert crontasks for plugin ocsinventoryng");

         $query = "UPDATE `glpi_crontasks`
                   SET `itemtype` = 'PluginOcsinventoryngOcsServer'
                   WHERE `itemtype` = 'OcsServer'";
         $DB->queryOrDie($query, "1.0.0 update ocsinventoryng crontask");
      }

      If (TableExists("ocs_glpi_displaypreferences")) {
         $query = "INSERT INTO `glpi_displaypreferences`
                          SELECT *
                          FROM `ocs_glpi_displaypreferences`
                          WHERE `itemtype` = 'OcsServer'";

         $DB->queryOrDie($query, "1.0.0 insert displaypreferences for plugin ocsinventoryng");

         $query = "UPDATE `glpi_displaypreferences`
                   SET `itemtype` = 'PluginOcsinventoryngOcsServer'
                   WHERE `itemtype` = 'OcsServer'";
         $DB->queryOrDie($query, "1.0.0 update ocsinventoryng displaypreferences");
      }
      plugin_ocsinventoryng_migrateComputerLocks($migration);

   }

   PluginOcsinventoryngProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

   // Si massocsimport import est installe, on verifie qu'il soit bien dans la dernière version
   if (TableExists("glpi_plugin_mass_ocs_import")) { //1.1 ou 1.2
      if (!FieldExists('glpi_plugin_mass_ocs_import_config','warn_if_not_imported')) { //1.1
         plugin_ocsinventoryng_upgrademassocsimport11to12();
      }
   }
   if (TableExists("glpi_plugin_mass_ocs_import")) { //1.2 because if before
      plugin_ocsinventoryng_upgrademassocsimport121to13();
   }
   if (TableExists("glpi_plugin_massocsimport")) { //1.3 ou 1.4
      if (FieldExists('glpi_plugin_massocsimport','ID')) { //1.3
         plugin_ocsinventoryng_upgrademassocsimport13to14();
      }
   }
   if (TableExists('glpi_plugin_massocsimport_threads')
         && !FieldExists('glpi_plugin_massocsimport_threads','not_unique_machines_number')) {
         plugin_ocsinventoryng_upgrademassocsimport14to15();
   }

   //Tables from massocsimport
   if (!TableExists('glpi_plugin_ocsinventoryng_threads')
       && !TableExists('glpi_plugin_massocsimport_threads')) { //not installed

      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_ocsinventoryng_threads` (
                  `id` int(11) NOT NULL auto_increment,
                  `threadid` int(11) NOT NULL default '0',
                  `start_time` datetime default NULL,
                  `end_time` datetime default NULL,
                  `status` int(11) NOT NULL default '0',
                  `error_msg` text NOT NULL,
                  `imported_machines_number` int(11) NOT NULL default '0',
                  `synchronized_machines_number` int(11) NOT NULL default '0',
                  `failed_rules_machines_number` int(11) NOT NULL default '0',
                  `linked_machines_number` int(11) NOT NULL default '0',
                  `notupdated_machines_number` int(11) NOT NULL default '0',
                  `not_unique_machines_number` int(11) NOT NULL default '0',
                  `link_refused_machines_number` int(11) NOT NULL default '0',
                  `total_number_machines` int(11) NOT NULL default '0',
                  `plugin_ocsinventoryng_ocsservers_id` int(11) NOT NULL default '1',
                  `processid` int(11) NOT NULL default '0',
                  `entities_id` int(11) NOT NULL DEFAULT 0,
                  `rules_id` int(11) NOT NULL DEFAULT 0,
                  PRIMARY KEY  (`id`),
                  KEY `end_time` (`end_time`),
                  KEY `process_thread` (`processid`,`threadid`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";

      $DB->queryOrDie($query, $DB->error());


      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_ocsinventoryng_configs` (
                  `id` int(11) NOT NULL auto_increment,
                  `thread_log_frequency` int(11) NOT NULL default '10',
                  `is_displayempty` int(1) NOT NULL default '1',
                  `import_limit` int(11) NOT NULL default '0',
                  `delay_refresh` int(11) NOT NULL default '0',
                  `allow_ocs_update` tinyint(1) NOT NULL default '0',
                  `comment` text,
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";

      $DB->queryOrDie($query, $DB->error());

      $query = "INSERT INTO `glpi_plugin_ocsinventoryng_configs`
                       (`id`,`thread_log_frequency`,`is_displayempty`,`import_limit`)
                VALUES (1, 2, 1, 0);";
      $DB->queryOrDie($query, $DB->error());


      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_ocsinventoryng_details` (
                  `id` int(11) NOT NULL auto_increment,
                  `entities_id` int(11) NOT NULL default '0',
                  `plugin_ocsinventoryng_threads_id` int(11) NOT NULL default '0',
                  `rules_id` TEXT,
                  `threadid` int(11) NOT NULL default '0',
                  `ocsid` int(11) NOT NULL default '0',
                  `computers_id` int(11) NOT NULL default '0',
                  `action` int(11) NOT NULL default '0',
                  `process_time` datetime DEFAULT NULL,
                  `plugin_ocsinventoryng_ocsservers_id` int(11) NOT NULL default '1',
                  PRIMARY KEY (`id`),
                  KEY `end_time` (`process_time`),
                  KEY `process_thread` (`plugin_ocsinventoryng_threads_id`,`threadid`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->queryOrDie($query, $DB->error());

      $query = "INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`)
                VALUES ('PluginOcsinventoryngNotimportedcomputer', 2, 1, 0),
                       ('PluginOcsinventoryngNotimportedcomputer', 3, 2, 0),
                       ('PluginOcsinventoryngNotimportedcomputer', 4, 3, 0),
                       ('PluginOcsinventoryngNotimportedcomputer', 5, 4, 0),
                       ('PluginOcsinventoryngNotimportedcomputer', 6, 5, 0),
                       ('PluginOcsinventoryngNotimportedcomputer', 7, 6, 0),
                       ('PluginOcsinventoryngNotimportedcomputer', 8, 7, 0),
                       ('PluginOcsinventoryngNotimportedcomputer', 9, 8, 0),
                       ('PluginOcsinventoryngNotimportedcomputer', 10, 9, 0),
                       ('PluginOcsinventoryngDetail', 5, 1, 0),
                       ('PluginOcsinventoryngDetail', 2, 2, 0),
                       ('PluginOcsinventoryngDetail', 3, 3, 0),
                       ('PluginOcsinventoryngDetail', 4, 4, 0),
                       ('PluginOcsinventoryngDetail', 6, 5, 0),
                       ('PluginOcsinventoryngDetail', 80, 6, 0)";
      $DB->queryOrDie($query, $DB->error());


      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_ocsinventoryng_notimportedcomputers` (
                  `id` INT( 11 ) NOT NULL  auto_increment,
                  `entities_id` int(11) NOT NULL default '0',
                  `rules_id` TEXT,
                  `comment` text NULL,
                  `ocsid` INT( 11 ) NOT NULL DEFAULT '0',
                  `plugin_ocsinventoryng_ocsservers_id` INT( 11 ) NOT NULL ,
                  `ocs_deviceid` VARCHAR( 255 ) NOT NULL ,
                  `useragent` VARCHAR( 255 ) NOT NULL ,
                  `tag` VARCHAR( 255 ) NOT NULL ,
                  `serial` VARCHAR( 255 ) NOT NULL ,
                  `name` VARCHAR( 255 ) NOT NULL ,
                  `ipaddr` VARCHAR( 255 ) NOT NULL ,
                  `domain` VARCHAR( 255 ) NOT NULL ,
                  `last_inventory` DATETIME ,
                  `reason` INT( 11 ) NOT NULL ,
                  PRIMARY KEY ( `id` ),
                  UNIQUE KEY `ocs_id` (`plugin_ocsinventoryng_ocsservers_id`,`ocsid`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";

      $DB->queryOrDie($query, $DB->error());


      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_ocsinventoryng_servers` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `plugin_ocsinventoryng_ocsservers_id` int(11) NOT NULL DEFAULT '0',
                  `max_ocsid` int(11) DEFAULT NULL,
                  `max_glpidate` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `plugin_ocsinventoryng_ocsservers_id` (`plugin_ocsinventoryng_ocsservers_id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";

      $DB->queryOrDie($query, $DB->error());

      $query = "SELECT `id`
                FROM `glpi_notificationtemplates`
                WHERE `itemtype` = 'PluginOcsinventoryngNotimportedcomputer'";
      $result = $DB->query($query);

      if (!$DB->numrows($result)) {
         //Add template
         $query = "INSERT INTO `glpi_notificationtemplates`
                   VALUES (NULL, 'Computers not imported', 'PluginOcsinventoryngNotimportedcomputer',
                           NOW(), '', NULL);";
         $DB->queryOrDie($query, $DB->error());
         $templates_id = $DB->insert_id();
         $query = "INSERT INTO `glpi_notificationtemplatetranslations`
                   VALUES (NULL, $templates_id, '',
                           '##lang.notimported.action## : ##notimported.entity##',
                   '\r\n\n##lang.notimported.action## :&#160;##notimported.entity##\n\n" .
                  "##FOREACHnotimported##&#160;\n##lang.notimported.reason## : ##notimported.reason##\n" .
                  "##lang.notimported.name## : ##notimported.name##\n" .
                  "##lang.notimported.deviceid## : ##notimported.deviceid##\n" .
                  "##lang.notimported.tag## : ##notimported.tag##\n##lang.notimported.serial## : ##notimported.serial## \r\n\n" .
                  " ##notimported.url## \n##ENDFOREACHnotimported## \r\n', '&lt;p&gt;##lang.notimported.action## :&#160;##notimported.entity##&lt;br /&gt;&lt;br /&gt;" .
                  "##FOREACHnotimported##&#160;&lt;br /&gt;##lang.notimported.reason## : ##notimported.reason##&lt;br /&gt;" .
                  "##lang.notimported.name## : ##notimported.name##&lt;br /&gt;" .
                  "##lang.notimported.deviceid## : ##notimported.deviceid##&lt;br /&gt;" .
                  "##lang.notimported.tag## : ##notimported.tag##&lt;br /&gt;" .
                  "##lang.notimported.serial## : ##notimported.serial##&lt;/p&gt;\r\n&lt;p&gt;&lt;a href=\"##notimported.url##\"&gt;" .
                  "##notimported.url##&lt;/a&gt;&lt;br /&gt;##ENDFOREACHnotimported##&lt;/p&gt;');";
         $DB->queryOrDie($query, $DB->error());

         $query = "INSERT INTO `glpi_notifications`
                   VALUES (NULL, 'Computers not imported', 0, 'PluginOcsinventoryngNotimportedcomputer',
                           'not_imported', 'mail',".$templates_id.", '', 1, 1, NOW());";
         $DB->queryOrDie($query, $DB->error());

      }

      CronTask::Register('PluginOcsinventoryngThread', 'CleanOldThreads', HOUR_TIMESTAMP,
                         array('param' => 24));

   } else if (!TableExists('glpi_plugin_ocsinventoryng_threads')
              && TableExists('glpi_plugin_massocsimport_threads')) {

      if (TableExists('glpi_plugin_massocsimport_threads')
         && !FieldExists('glpi_plugin_massocsimport_threads','not_unique_machines_number')) {
            plugin_ocsinventoryng_upgrademassocsimport14to15();
      }
      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_ocsinventoryng_threads` (
                  `id` int(11) NOT NULL auto_increment,
                  `threadid` int(11) NOT NULL default '0',
                  `start_time` datetime default NULL,
                  `end_time` datetime default NULL,
                  `status` int(11) NOT NULL default '0',
                  `error_msg` text NOT NULL,
                  `imported_machines_number` int(11) NOT NULL default '0',
                  `synchronized_machines_number` int(11) NOT NULL default '0',
                  `failed_rules_machines_number` int(11) NOT NULL default '0',
                  `linked_machines_number` int(11) NOT NULL default '0',
                  `notupdated_machines_number` int(11) NOT NULL default '0',
                  `not_unique_machines_number` int(11) NOT NULL default '0',
                  `link_refused_machines_number` int(11) NOT NULL default '0',
                  `total_number_machines` int(11) NOT NULL default '0',
                  `ocsservers_id` int(11) NOT NULL default '1',
                  `processid` int(11) NOT NULL default '0',
                  `entities_id` int(11) NOT NULL DEFAULT 0,
                  `rules_id` int(11) NOT NULL DEFAULT 0,
                  PRIMARY KEY  (`id`),
                  KEY `end_time` (`end_time`),
                  KEY `process_thread` (`processid`,`threadid`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";

      $DB->queryOrDie($query, $DB->error());

      //error of massocsimport 1.5.0 installaton
      $migration->addField("glpi_plugin_massocsimport_threads", "entities_id", 'integer');
      $migration->addField("glpi_plugin_massocsimport_threads", "rules_id", 'integer');

      foreach (getAllDatasFromTable('glpi_plugin_massocsimport_threads') as $thread) {
         if (is_null($thread['rules_id']) || $thread['rules_id'] == '') {
            $rules_id = 0;
         } else {
            $rules_id = $thread['rules_id'];
         }
         $query = "INSERT INTO `glpi_plugin_ocsinventoryng_threads`
                   VALUES ('".$thread['id']."',
                           '".$thread['threadid']."',
                           '".$thread['start_time']."',
                           '".$thread['end_time']."',
                           '".$thread['status']."',
                           '".$thread['error_msg']."',
                           '".$thread['imported_machines_number']."',
                           '".$thread['synchronized_machines_number']."',
                           '".$thread['failed_rules_machines_number']."',
                           '".$thread['linked_machines_number']."',
                           '".$thread['notupdated_machines_number']."',
                           '".$thread['not_unique_machines_number']."',
                           '".$thread['link_refused_machines_number']."',
                           '".$thread['total_number_machines']."',
                           '".$thread['ocsservers_id']."',
                           '".$thread['processid']."',
                           '".$thread['entities_id']."',
                           '".$rules_id."');";
         $DB->queryOrDie($query, $DB->error());
      }

      $migration->renameTable("glpi_plugin_massocsimport_threads",
                              "backup_glpi_plugin_massocsimport_threads");

      $migration->changeField("glpi_plugin_ocsinventoryng_threads", "ocsservers_id",
                              "plugin_ocsinventoryng_ocsservers_id", 'integer');

      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_ocsinventoryng_configs` (
                  `id` int(11) NOT NULL auto_increment,
                  `thread_log_frequency` int(11) NOT NULL default '10',
                  `is_displayempty` int(1) NOT NULL default '1',
                  `import_limit` int(11) NOT NULL default '0',
                  `ocsservers_id` int(11) NOT NULL default '-1',
                  `delay_refresh` int(11) NOT NULL default '0',
                  `allow_ocs_update` tinyint(1) NOT NULL default '0',
                  `comment` text,
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";

      $DB->query($query) or die($DB->error());

      foreach (getAllDatasFromTable('glpi_plugin_massocsimport_configs') as $thread) {

         $query = "INSERT INTO `glpi_plugin_ocsinventoryng_configs`
                   VALUES('".$thread['id']."',
                          '".$thread['thread_log_frequency']."',
                          '".$thread['is_displayempty']."',
                          '".$thread['import_limit']."',
                          '".$thread['ocsservers_id']."',
                          '".$thread['delay_refresh']."',
                          '".$thread['allow_ocs_update']."',
                          '".$thread['comment']."');";
         $DB->queryOrDie($query, $DB->error());
      }

      $migration->renameTable("glpi_plugin_massocsimport_configs",
                              "backup_glpi_plugin_massocsimport_configs");

      $migration->dropField("glpi_plugin_ocsinventoryng_configs", "ocsservers_id");


      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_ocsinventoryng_details` (
                  `id` int(11) NOT NULL auto_increment,
                  `entities_id` int(11) NOT NULL default '0',
                  `plugin_massocsimport_threads_id` int(11) NOT NULL default '0',
                  `rules_id` TEXT,
                  `threadid` int(11) NOT NULL default '0',
                  `ocsid` int(11) NOT NULL default '0',
                  `computers_id` int(11) NOT NULL default '0',
                  `action` int(11) NOT NULL default '0',
                  `process_time` datetime DEFAULT NULL,
                  `ocsservers_id` int(11) NOT NULL default '1',
                  PRIMARY KEY (`id`),
                  KEY `end_time` (`process_time`),
                  KEY `process_thread` (`ocsservers_id`,`threadid`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->queryOrDie($query, $DB->error());

      foreach (getAllDatasFromTable('glpi_plugin_massocsimport_details') as $thread) {

         $query = "INSERT INTO `glpi_plugin_ocsinventoryng_details`
                   VALUES ('".$thread['id']."',
                           '".$thread['entities_id']."',
                           '".$thread['plugin_massocsimport_threads_id']."',
                           '".$thread['rules_id']."',
                           '".$thread['threadid']."',
                           '".$thread['ocsid']."',
                           '".$thread['computers_id']."',
                           '".$thread['action']."',
                           '".$thread['process_time']."',
                           '".$thread['ocsservers_id']."');";
         $DB->query($query) or die($DB->error());
      }

      $migration->renameTable("glpi_plugin_massocsimport_details",
                              "backup_glpi_plugin_massocsimport_details");

      $migration->changeField("glpi_plugin_ocsinventoryng_details",
                              "plugin_massocsimport_threads_id", "plugin_ocsinventoryng_threads_id",
                              'integer');

      $migration->changeField("glpi_plugin_ocsinventoryng_details", "ocsservers_id",
                              "plugin_ocsinventoryng_ocsservers_id", 'integer');


      $query = "UPDATE `glpi_displaypreferences`
                SET `itemtype` = 'PluginOcsinventoryngNotimportedcomputer'
                WHERE `itemtype` = 'PluginMassocsimportNotimported'";

      $DB->queryOrDie($query, $DB->error());

      $query = "UPDATE `glpi_displaypreferences`
                SET `itemtype` = 'PluginOcsinventoryngDetail'
                WHERE `itemtype` = 'PluginMassocsimportDetail';";

      $DB->queryOrDie($query, $DB->error());


      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_ocsinventoryng_notimportedcomputers` (
                  `id` INT( 11 ) NOT NULL  auto_increment,
                  `entities_id` int(11) NOT NULL default '0',
                  `rules_id` TEXT,
                  `comment` text NULL,
                  `ocsid` INT( 11 ) NOT NULL DEFAULT '0',
                  `ocsservers_id` INT( 11 ) NOT NULL ,
                  `ocs_deviceid` VARCHAR( 255 ) NOT NULL ,
                  `useragent` VARCHAR( 255 ) NOT NULL ,
                  `tag` VARCHAR( 255 ) NOT NULL ,
                  `serial` VARCHAR( 255 ) NOT NULL ,
                  `name` VARCHAR( 255 ) NOT NULL ,
                  `ipaddr` VARCHAR( 255 ) NOT NULL ,
                  `domain` VARCHAR( 255 ) NOT NULL ,
                  `last_inventory` DATETIME ,
                  `reason` INT( 11 ) NOT NULL ,
                  PRIMARY KEY ( `id` ),
                  UNIQUE KEY `ocs_id` (`ocsservers_id`,`ocsid`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";

      $DB->queryOrDie($query, $DB->error());

      if (TableExists("glpi_plugin_massocsimport_notimported")) {
         foreach (getAllDatasFromTable('glpi_plugin_massocsimport_notimported') as $thread) {

            $query = "INSERT INTO `glpi_plugin_ocsinventoryng_notimportedcomputers`
                      VALUES ('".$thread['id']."', '".$thread['entities_id']."',
                              '".$thread['rules_id']."', '".$thread['comment']."',
                              '".$thread['ocsid']."', '".$thread['ocsservers_id']."',
                              '".$thread['ocs_deviceid']."', '".$thread['useragent']."',
                              '".$thread['tag']."', '".$thread['serial']."', '".$thread['name']."',
                              '".$thread['ipaddr']."', '".$thread['domain']."',
                              '".$thread['last_inventory']."', '".$thread['reason']."')";
            $DB->queryOrDie($query, $DB->error());
         }

         $migration->renameTable("glpi_plugin_massocsimport_notimported",
                                 "backup_glpi_plugin_massocsimport_notimported");
      }

      $migration->changeField("glpi_plugin_ocsinventoryng_notimportedcomputers", "ocsservers_id",
                              "plugin_ocsinventoryng_ocsservers_id", 'integer');

      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_ocsinventoryng_servers` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `ocsservers_id` int(11) NOT NULL DEFAULT '0',
                  `max_ocsid` int(11) DEFAULT NULL,
                  `max_glpidate` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `ocsservers_id` (`ocsservers_id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";

      $DB->query($query) or die($DB->error());

      foreach (getAllDatasFromTable('glpi_plugin_massocsimport_servers') as $thread) {

         $query = "INSERT INTO `glpi_plugin_ocsinventoryng_servers`
                          (`id` ,`ocsservers_id` ,`max_ocsid` ,`max_glpidate`)
                   VALUES ('".$thread['id']."',
                           '".$thread['ocsservers_id']."',
                           '".$thread['max_ocsid']."',
                           '".$thread['max_glpidate']."');";
         $DB->queryOrDie($query, $DB->error());
      }

      $migration->renameTable("glpi_plugin_massocsimport_servers",
                              "backup_glpi_plugin_massocsimport_servers");

      $migration->changeField("glpi_plugin_ocsinventoryng_servers", "ocsservers_id",
                              "plugin_ocsinventoryng_ocsservers_id", 'integer');


      $query = "UPDATE `glpi_notificationtemplates`
                SET `itemtype` = 'PluginOcsinventoryngNotimportedcomputer'
                WHERE `itemtype` = 'PluginMassocsimportNotimported'";

      $DB->queryOrDie($query, $DB->error());

      $query = "UPDATE `glpi_notifications`
                SET `itemtype` = 'PluginOcsinventoryngNotimportedcomputer'
                WHERE `itemtype` = 'PluginMassocsimportNotimported'";

      $DB->queryOrDie($query, $DB->error());

      $query = "UPDATE `glpi_crontasks`
                SET `itemtype` = 'PluginOcsinventoryngThread'
                WHERE `itemtype` = 'PluginMassocsimportThread';";
      $DB->queryOrDie($query, $DB->error());

      $query = "UPDATE `glpi_alerts`
                SET `itemtype` = 'PluginOcsinventoryngNotimportedcomputer'
                WHERE `itemtype` IN ('PluginMassocsimportNotimported')";

      $DB->queryOrDie($query, $DB->error());
   }

   $migration->executeMigration();

   $cron = new CronTask();
   if (!$cron->getFromDBbyName('PluginOcsinventoryngNotimportedcomputer','SendAlerts')) {
      // creation du cron - param = duree de conservation
      CronTask::Register('PluginOcsinventoryngNotimportedcomputer', 'SendAlerts', 10 * MINUTE_TIMESTAMP,
                         array('param' => 24));
   }

   return true;
}



function plugin_ocsinventoryng_upgrademassocsimport11to12() {
   global $DB;

   $migration= new Migration(12);

   if (!TableExists("glpi_plugin_mass_ocs_import_config")) {
      $query = "CREATE TABLE `glpi_plugin_mass_ocs_import_config` (
                  `ID` int(11) NOT NULL,
                  `enable_logging` int(1) NOT NULL default '1',
                  `thread_log_frequency` int(4) NOT NULL default '10',
                  `display_empty` int(1) NOT NULL default '1',
                  `delete_frequency` int(4) NOT NULL default '0',
                  `import_limit` int(11) NOT NULL default '0',
                  `default_ocs_server` int(11) NOT NULL default '-1',
                  `delay_refresh` varchar(4) NOT NULL default '0',
                  `delete_empty_frequency` int(4) NOT NULL default '0',
                  PRIMARY KEY  (`ID`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";

      $DB->queryOrDie($query, "1.1 to 1.2 ".$DB->error());

      $query = "INSERT INTO `glpi_plugin_mass_ocs_import_config`
                     (`ID`, `enable_logging`, `thread_log_frequency`, `display_empty`,
                      `delete_frequency`, `delete_empty_frequency`, `import_limit`,
                      `default_ocs_server` )
                VALUES (1, 1, 5, 1, 2, 2, 0,-1)";

      $DB->queryOrDie($query, "1.1 to 1.2 ".$DB->error());
   }

   $migration->addField("glpi_plugin_mass_ocs_import_config", "warn_if_not_imported", 'integer');
   $migration->addField("glpi_plugin_mass_ocs_import_config", "not_imported_threshold", 'integer');

   $migration->executeMigration();
}


function plugin_ocsinventoryng_upgrademassocsimport121to13() {
   global $DB;

   $migration = new Migration(13);

   if (TableExists("glpi_plugin_mass_ocs_import_config")) {
      $tables = array("glpi_plugin_massocsimport_servers" => "glpi_plugin_mass_ocs_import_servers",
                      "glpi_plugin_massocsimport"         => "glpi_plugin_mass_ocs_import",
                      "glpi_plugin_massocsimport_config"  => "glpi_plugin_mass_ocs_import_config",
                      "glpi_plugin_massocsimport_not_imported"
                                                          => "glpi_plugin_mass_ocs_import_not_imported");

      foreach ($tables as $new => $old) {
         $migration->renameTable($old, $new);
      }

      $migration->changeField("glpi_plugin_massocsimport", "process_id", "process_id",
                              "BIGINT(20) NOT NULL DEFAULT '0'");

      $migration->addField("glpi_plugin_massocsimport_config", "comments", 'text');

      $migration->addField("glpi_plugin_massocsimport", "noupdate_machines_number", 'integer');

      if (!TableExists("glpi_plugin_massocsimport_details")) {
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_massocsimport_details` (
                     `ID` int(11) NOT NULL auto_increment,
                     `process_id` bigint(10) NOT NULL default '0',
                     `thread_id` int(4) NOT NULL default '0',
                     `ocs_id` int(11) NOT NULL default '0',
                     `glpi_id` int(11) NOT NULL default '0',
                     `action` int(11) NOT NULL default '0',
                     `process_time` datetime DEFAULT NULL,
                     `ocs_server_id` int(4) NOT NULL default '1',
                     PRIMARY KEY  (`ID`),
                     KEY `end_time` (`process_time`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->queryOrDie($query, "1.2.1 to 1.3 ".$DB->error());
      }

      //Add fields to the default view
      $query = "INSERT INTO `glpi_displayprefs` (`itemtype`, `num`, `rank`, `users_id`)
                VALUES (" . PLUGIN_MASSOCSIMPORT_NOTIMPORTED . ", 2, 1, 0),
                       (" . PLUGIN_MASSOCSIMPORT_NOTIMPORTED . ", 3, 2, 0),
                       (" . PLUGIN_MASSOCSIMPORT_NOTIMPORTED . ", 4, 3, 0),
                       (" . PLUGIN_MASSOCSIMPORT_NOTIMPORTED . ", 5, 4, 0),
                       (" . PLUGIN_MASSOCSIMPORT_NOTIMPORTED . ", 6, 5, 0),
                       (" . PLUGIN_MASSOCSIMPORT_NOTIMPORTED . ", 7, 6, 0),
                       (" . PLUGIN_MASSOCSIMPORT_NOTIMPORTED . ", 8, 7, 0),
                       (" . PLUGIN_MASSOCSIMPORT_NOTIMPORTED . ", 8, 7, 0),
                       (" . PLUGIN_MASSOCSIMPORT_NOTIMPORTED . ", 10, 9, 0),
                       (" . PLUGIN_MASSOCSIMPORT_DETAIL . ", 5, 1, 0),
                       (" . PLUGIN_MASSOCSIMPORT_DETAIL . ", 2, 2, 0),
                       (" . PLUGIN_MASSOCSIMPORT_DETAIL . ", 3, 3, 0),
                       (" . PLUGIN_MASSOCSIMPORT_DETAIL . ", 4, 4, 0),
                       (" . PLUGIN_MASSOCSIMPORT_DETAIL . ", 6, 5, 0)";
      $DB->query($query);// or die($DB->error());

      $drop_fields = array (//Was not used, debug only...
                            "glpi_plugin_massocsimport_config" => "warn_if_not_imported",
                            "glpi_plugin_massocsimport_config" => "not_imported_threshold",
                            //Logging must always be enable !
                            "glpi_plugin_massocsimport_config" => "enable_logging",
                            "glpi_plugin_massocsimport_config" => "delete_empty_frequency");

      foreach ($drop_fields as $table => $field) {
         $migration->dropField($table, $field);
      }
   }
   $migration->executeMigration();
}


function plugin_ocsinventoryng_upgrademassocsimport13to14() {
   global $DB;

   $migration = new Migration(14);

   $migration->renameTable("glpi_plugin_massocsimport", "glpi_plugin_massocsimport_threads");

   $migration->changeField("glpi_plugin_massocsimport_threads", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_massocsimport_threads", "thread_id", "threadid", 'integer');
   $migration->changeField("glpi_plugin_massocsimport_threads", "status", "status", 'integer');
   $migration->changeField("glpi_plugin_massocsimport_threads", "ocs_server_id", "ocsservers_id",
                           'integer', array('value' => 1));
   $migration->changeField("glpi_plugin_massocsimport_threads", "process_id", "processid",
                           'integer');
   $migration->changeField("glpi_plugin_massocsimport_threads", "noupdate_machines_number",
                           "notupdated_machines_number", 'integer');

   $migration->migrationOneTable("glpi_plugin_massocsimport_threads");

   $migration->addKey("glpi_plugin_massocsimport_threads", array("processid", "threadid"),
                      "process_thread");


   $migration->renameTable("glpi_plugin_massocsimport_config", "glpi_plugin_massocsimport_configs");

   $migration->dropField("glpi_plugin_massocsimport_configs", "delete_frequency");
   $migration->dropField("glpi_plugin_massocsimport_configs", "enable_logging");
   $migration->dropField("glpi_plugin_massocsimport_configs", "delete_empty_frequency");
   $migration->dropField("glpi_plugin_massocsimport_configs", "warn_if_not_imported");
   $migration->dropField("glpi_plugin_massocsimport_configs", "not_imported_threshold");

   $migration->changeField("glpi_plugin_massocsimport_configs", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_massocsimport_configs", "thread_log_frequency",
                           "thread_log_frequency", 'integer', array('value' => 10));
   $migration->changeField("glpi_plugin_massocsimport_configs", "display_empty", "is_displayempty",
                           'int(1) NOT NULL default 1');
   $migration->changeField("glpi_plugin_massocsimport_configs", "default_ocs_server",
                           "ocsservers_id", 'integer', array('value' => -1));
   $migration->changeField("glpi_plugin_massocsimport_configs", "delay_refresh", "delay_refresh",
                           'integer');
   $migration->changeField("glpi_plugin_massocsimport_configs", "comments", "comment", 'text');


   $migration->changeField("glpi_plugin_massocsimport_details", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_massocsimport_details", "process_id",
                           "plugin_massocsimport_threads_id", 'integer');
   $migration->changeField("glpi_plugin_massocsimport_details", "thread_id", "threadid", 'integer');
   $migration->changeField("glpi_plugin_massocsimport_details", "ocs_id", "ocsid", 'integer');
   $migration->changeField("glpi_plugin_massocsimport_details", "glpi_id", "computers_id",
                           'integer');
   $migration->changeField("glpi_plugin_massocsimport_details", "ocs_server_id",
                           "ocsservers_id", 'integer', array('value' => 1));

   $migration->migrationOneTable('glpi_plugin_massocsimport_details');
   $migration->addKey("glpi_plugin_massocsimport_details",
                      array("plugin_massocsimport_threads_id", "threadid"), "process_thread");


   $migration->renameTable("glpi_plugin_massocsimport_not_imported",
                           "glpi_plugin_massocsimport_notimported");

   $migration->changeField("glpi_plugin_massocsimport_notimported", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_massocsimport_notimported", "ocs_id", "ocsid", 'integer');
   $migration->changeField("glpi_plugin_massocsimport_notimported", "ocs_server_id", "ocsservers_id",
                           'integer');
   $migration->changeField("glpi_plugin_massocsimport_notimported", "deviceid", "ocs_deviceid",
                           'string');



   $migration->changeField("glpi_plugin_massocsimport_servers", "ID", "id", 'autoincrement');
   $migration->changeField("glpi_plugin_massocsimport_servers", "ocs_server_id", "ocsservers_id",
                           'integer');
   $migration->changeField("glpi_plugin_massocsimport_servers", "max_ocs_id", "max_ocsid",
                           'int(11) DEFAULT NULL');
   $migration->changeField("glpi_plugin_massocsimport_servers", "max_glpi_date", "max_glpidate",
                           'datetime DEFAULT NULL');

   $migration->executeMigration();
}

function plugin_ocsinventoryng_upgrademassocsimport14to15() {
   global $DB;

   $migration = new Migration(15);

   $migration->addField("glpi_plugin_massocsimport_threads", "not_unique_machines_number",
                        'integer');
   $migration->addField("glpi_plugin_massocsimport_threads", "link_refused_machines_number",
                        'integer');
   $migration->addField("glpi_plugin_massocsimport_threads", "entities_id", 'integer');
   $migration->addField("glpi_plugin_massocsimport_threads", "rules_id", 'text');

   $migration->addField("glpi_plugin_massocsimport_configs", "allow_ocs_update", 'bool');

   $migration->addField("glpi_plugin_massocsimport_notimported", "reason", 'integer');

   if (!countElementsInTable('glpi_displaypreferences',
                              "`itemtype`='PluginMassocsimportNotimported'
                               AND `num`='10' AND `users_id`='0'")) {
      $query = "INSERT INTO `glpi_displaypreferences`
                (`itemtype`, `num`, `rank`, `users_id`)
                VALUES ('PluginMassocsimportNotimported', 10, 9, 0)";
       $DB->queryOrDie($query, "1.5 insert into glpi_displaypreferences " .$DB->error());
   }

   $migration->addField("glpi_plugin_massocsimport_notimported", "serial", 'string',
                        array('value' => ''));
   $migration->addField("glpi_plugin_massocsimport_notimported", "comment", "TEXT NOT NULL");
   $migration->addField("glpi_plugin_massocsimport_notimported", "rules_id", 'text');
   $migration->addField("glpi_plugin_massocsimport_notimported", "entities_id", 'integer');

   $migration->addField("glpi_plugin_massocsimport_details", "entities_id", 'integer');
   $migration->addField("glpi_plugin_massocsimport_details", "rules_id", 'text');

   $query = "SELECT id " .
            "FROM `glpi_notificationtemplates` " .
            "WHERE `itemtype`='PluginMassocsimportNotimported'";
   $result = $DB->query($query);
   if (!$DB->numrows($result)) {

      //Add template
      $query = "INSERT INTO `glpi_notificationtemplates` " .
               "VALUES (NULL, 'Computers not imported', 'PluginMassocsimportNotimported',
                        NOW(), '', '');";
      $DB->queryOrDie($query, $DB->error());
      $templates_id = $DB->insert_id();
      $query = "INSERT INTO `glpi_notificationtemplatetranslations` " .
               "VALUES(NULL, $templates_id, '', '##lang.notimported.action## : ##notimported.entity##'," .
               " '\r\n\n##lang.notimported.action## :&#160;##notimported.entity##\n\n" .
               "##FOREACHnotimported##&#160;\n##lang.notimported.reason## : ##notimported.reason##\n" .
               "##lang.notimported.name## : ##notimported.name##\n" .
               "##lang.notimported.deviceid## : ##notimported.deviceid##\n" .
               "##lang.notimported.tag## : ##notimported.tag##\n##lang.notimported.serial## : ##notimported.serial## \r\n\n" .
               " ##notimported.url## \n##ENDFOREACHnotimported## \r\n', '&lt;p&gt;##lang.notimported.action## :&#160;##notimported.entity##&lt;br /&gt;&lt;br /&gt;" .
               "##FOREACHnotimported##&#160;&lt;br /&gt;##lang.notimported.reason## : ##notimported.reason##&lt;br /&gt;" .
               "##lang.notimported.name## : ##notimported.name##&lt;br /&gt;" .
               "##lang.notimported.deviceid## : ##notimported.deviceid##&lt;br /&gt;" .
               "##lang.notimported.tag## : ##notimported.tag##&lt;br /&gt;" .
               "##lang.notimported.serial## : ##notimported.serial##&lt;/p&gt;\r\n&lt;p&gt;&lt;a href=\"##infocom.url##\"&gt;" .
               "##notimported.url##&lt;/a&gt;&lt;br /&gt;##ENDFOREACHnotimported##&lt;/p&gt;');";
      $DB->queryOrDie($query, $DB->error());

      $query = "INSERT INTO `glpi_notifications`
                VALUES (NULL, 'Computers not imported', 0, 'PluginMassocsimportNotimported',
                        'not_imported', 'mail',".$templates_id.", '', 1, 1, NOW());";
      $DB->queryOrDie($query, $DB->error());
   }
   $migration->executeMigration();
}


function plugin_ocsinventoryng_uninstall() {
   global $DB;

   $tables = array("glpi_plugin_ocsinventoryng_ocsservers",
                   "glpi_plugin_ocsinventoryng_ocslinks",
                   "glpi_plugin_ocsinventoryng_ocsadmininfoslinks",
                   "glpi_plugin_ocsinventoryng_profiles",
                   "glpi_plugin_ocsinventoryng_threads",
                   "glpi_plugin_ocsinventoryng_servers",
                   "glpi_plugin_ocsinventoryng_configs",
                   "glpi_plugin_ocsinventoryng_notimportedcomputers",
                   "glpi_plugin_ocsinventoryng_details",
                   "glpi_plugin_ocsinventoryng_registrykeys",
                   "glpi_plugin_ocsinventoryng_networkports",
                   "glpi_plugin_ocsinventoryng_networkporttypes");

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   $tables_glpi = array("glpi_bookmarks", "glpi_displaypreferences",
                        "glpi_documents_items", "glpi_logs", "glpi_tickets");

   foreach ($tables_glpi as $table_glpi) {
      $DB->query("DELETE
                  FROM `".$table_glpi."`
                  WHERE `itemtype` IN ('PluginMassocsimportNotimported',
                                       'PluginMassocsimportDetail',
                                       'PluginOcsinventoryngOcsServer',
                                       'PluginOcsinventoryngNotimportedcomputer',
                                       'PluginOcsinventoryngDetail')");
   }
   $query = "DELETE
             FROM `glpi_alerts`
             WHERE `itemtype` IN ('PluginMassocsimportNotimported',
                                  'PluginOcsinventoryngNotimportedcomputer')";
   $DB->queryOrDie($query, $DB->error());

   // clean rules
   $rule = new RuleImportEntity();
   foreach ($DB->request("glpi_rules", array('sub_type' => 'RuleImportEntity',
                                             'name'     => 'RootOcs')) AS $data) {
      $rule->delete($data);
   }


   $notification = new Notification();
   foreach (getAllDatasFromTable($notification->getTable(),
                                 "`itemtype` IN ('PluginMassocsimportNotimported',
                                                 'PluginOcsinventoryngNotimportedcomputer')") as $data) {
      $notification->delete($data);
   }
   $template = new NotificationTemplate();
   foreach (getAllDatasFromTable($template->getTable(),
                                 "`itemtype` IN ('PluginMassocsimportNotimported',
                                                 'PluginOcsinventoryngNotimportedcomputer')") as $data) {
      $template->delete($data);
   }

   $cron = new CronTask;
   if ($cron->getFromDBbyName('PluginMassocsimportThread', 'CleanOldThreads')) {
      // creation du cron - param = duree de conservation
      CronTask::Unregister('massocsimport');
   }
   if ($cron->getFromDBbyName('PluginOcsinventoryngThread', 'CleanOldThreads')) {
      // creation du cron - param = duree de conservation
      CronTask::Unregister('ocsinventoryng');
   }

   return true;
}


function plugin_ocsinventoryng_getDropdown() {
   // Table => Name
   return array('PluginOcsinventoryngNetworkPortType' => PluginOcsinventoryngNetworkPortType::getTypeName(0));
}



/**
 * Define dropdown relations
**/
function plugin_ocsinventoryngs_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("ocsinventoryng")) {
      return array("glpi_plugin_ocsinventoryng_ocsservers"
                     => array("glpi_plugin_ocsinventoryng_ocslinks"
                                                         => "plugin_ocsinventoryng_ocsservers_id",
                              "glpi_plugin_ocsinventoryng_ocsadmininfoslinks"
                                                         => "plugin_ocsinventoryng_ocsservers_id"),

                   "glpi_entities"
                     => array("glpi_plugin_ocsinventoryng_ocslinks" => "entities_id"),

                   "glpi_computers"
                     => array("glpi_plugin_ocsinventoryng_ocslinks"     => "computers_id",
                              "glpi_plugin_ocsinventoryng_registrykeys" => "computers_id"),

                   "glpi_profiles"
                     => array("glpi_plugin_ocsinventoryng_profiles" => "profiles_id"),

                   "glpi_states"
                     => array("glpi_plugin_ocsinventoryng_ocsservers" => "states_id_default"));
   }
   return array ();
}


function plugin_ocsinventoryng_postinit() {
   global $CFG_GLPI, $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['pre_item_purge']['ocsinventoryng']
                              = array('Profile' =>  array('PluginOcsinventoryngProfile',
                                                          'purgeProfiles'));

   $PLUGIN_HOOKS['pre_item_add']['ocsinventoryng']    = array();
   $PLUGIN_HOOKS['item_update']['ocsinventoryng']     = array();

      $PLUGIN_HOOKS['pre_item_add']['ocsinventoryng']
         = array('Computer_Item' => array('PluginOcsinventoryngOcslink', 'addComputer_Item'));

      $PLUGIN_HOOKS['item_update']['ocsinventoryng']
         = array('Computer' => array('PluginOcsinventoryngOcslink', 'updateComputer'));

      $PLUGIN_HOOKS['pre_item_purge']['ocsinventoryng']
         = array('Computer'      => array('PluginOcsinventoryngOcslink', 'purgeComputer'),
                 'Computer_Item' => array('PluginOcsinventoryngOcslink', 'purgeComputer_Item'));

   foreach (PluginOcsinventoryngOcsServer::getTypes(true) as $type) {

      CommonGLPI::registerStandardTab($type, 'PluginOcsinventoryngOcsServer');
   }
}

/**
 * @param $type
**/
function plugin_ocsinventoryng_MassiveActions($type) {

   switch ($type) {
      case 'PluginOcsinventoryngNotimportedcomputer' :
         $actions = array ();
         $actions["plugin_ocsinventoryng_replayrules"] = __("Restart import", 'ocsinventoryng');
         $actions["plugin_ocsinventoryng_import"]      = __("Import in the entity",
                                                            'ocsinventoryng');

         if (isset ($_POST['target'])
             && $_POST['target'] == Toolbox::getItemTypeFormURL('PluginOcsinventoryngNotimportedcomputer')) {

            $actions["plugin_ocsinventoryng_link"]
                                          = __('Link new OCSNG computers to existing GLPI computers',
                                               'ocsinventoryng');
         }
         $plugin = new Plugin;
         if ($plugin->isActivated("uninstall")) {
            $actions["plugin_ocsinventoryng_delete"]   = __('Delete computer in OCSNG',
                                                            'ocsinventoryng');
         }
         return $actions;

      case 'Computer' :
         if (plugin_ocsinventoryng_haveRight("ocsng","w")
             || plugin_ocsinventoryng_haveRight("sync_ocsng","w")) {

                return array(// Specific one
                      "plugin_ocsinventoryng_force_ocsng_update"
                      => __('Force synchronization OCSNG',
                            'ocsinventoryng'),
                      "plugin_ocsinventoryng_unlock_ocsng_field"      => __('Unlock fields',
                            'ocsinventoryng'));

         }
         break;
   }
   return array ();
}


/**
 * @param $options   array
*/
function plugin_ocsinventoryng_MassiveActionsDisplay($options=array()) {

   switch ($options['itemtype']) {
      case 'PluginOcsinventoryngNotimportedcomputer' :
         switch ($options['action']) {
            case "plugin_ocsinventoryng_import" :
               Entity::dropdown(array('name' => 'entity'));
               break;

            case "plugin_ocsinventoryng_link" :
               Computer::dropdown(array('name' => 'computers_id'));
               break;

            case "plugin_ocsinventoryng_replayrules" :
            case "plugin_ocsinventoryng_delete" :
               break;
         }
         echo "&nbsp;<input type='submit' name='massiveaction' class='submit' " .
              "value='"._sx('button', 'Post')."'>";
         break;

      case 'Computer' :
         switch ($options['action']) {
            case "plugin_ocsinventoryng_force_ocsng_update" :
               echo "<input type='submit' name='massiveaction' class='submit' value='".
                      _sx('button', 'Post')."'>\n";
               break;
            case "plugin_ocsinventoryng_unlock_ocsng_field" :
               $fields['all'] = __('All');
               $fields       += PluginOcsinventoryngOcsServer::getLockableFields();
               Dropdown::showFromArray("field", $fields);
               echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                              _sx('button', 'Post')."'>";
               break;

         }
   }
   return "";
}


/**
 * @param $data   array
**/
function plugin_ocsinventoryng_MassiveActionsProcess($data) {
   global $CFG_GLPI, $DB, $REDIRECT;

   $nbok      = 0;
   $nbko      = 0;
   $noright = 0;
   
   $notimport = new PluginOcsinventoryngNotimportedcomputer();
   if (!$item = getItemForItemtype($data['itemtype'])) {
      return;
   }
   switch ($data["action"]) {
      case "plugin_ocsinventoryng_import" :
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               PluginOcsinventoryngNotimportedcomputer::computerImport(array('id'     => $key,
                                                                             'force'  => true,
                                                                             'entity' => $data['entity']));
            }
         }
         break;

      case "plugin_ocsinventoryng_replayrules" :
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               PluginOcsinventoryngNotimportedcomputer::computerImport(array('id' => $key));
            }
         }
         break;

      case "plugin_ocsinventoryng_delete" :
         $plugin = new Plugin();
         if ($plugin->isActivated("uninstall")) {
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  $notimport->deleteNotImportedComputer($key);
               }
            }
         }
         break;
      
      case "plugin_ocsinventoryng_unlock_ocsng_field" :
         $nbok      = 0;
         $nbnoright = 0;
         $nbko      = 0;
         $fields = PluginOcsinventoryngOcsServer::getLockableFields();
         if ($_POST['field'] == 'all' || isset($fields[$_POST['field']])) {
            foreach ($_POST["item"] as $key => $val) {
               if ($val == 1) {
                  if ($item->can($key,'w')) {
                     if ($_POST['field'] == 'all') {
                        if (PluginOcsinventoryngOcsServer::replaceOcsArray($key, array(),
                                                                           "computer_update")) {
                           $nbok++;
                        } else {
                           $nbko++;
                        }
                     } else {
                        if (PluginOcsinventoryngOcsServer::deleteInOcsArray($key, $_POST['field'],
                                                                            "computer_update",
                                                                            true)) {
                           $nbok++;
                        } else {
                           $nbko++;
                        }
                     }
                  } else {
                     $nbnoright++;
                  }
               }
            }
         }
         break;
         
      case "plugin_ocsinventoryng_force_ocsng_update" :
         // First time
         if (!isset($_GET['multiple_actions'])) {
            $_SESSION['glpi_massiveaction']['POST']      = $_POST;
            $_SESSION['glpi_massiveaction']['REDIRECT']  = $REDIRECT;
            $_SESSION['glpi_massiveaction']['items']     = array();
            foreach ($_POST["item"] as $key => $val) {
               if ($val == 1) {
                  $_SESSION['glpi_massiveaction']['items'][$key] = $key;
               }
            }
            $_SESSION['glpi_massiveaction']['item_count']
                  = count($_SESSION['glpi_massiveaction']['items']);
            $_SESSION['glpi_massiveaction']['items_ok']        = 0;
            $_SESSION['glpi_massiveaction']['items_ko']        = 0;
            $_SESSION['glpi_massiveaction']['items_nbnoright'] = 0;
            Html::redirect($_SERVER['PHP_SELF'].'?multiple_actions=1');

         } else {
            if (count($_SESSION['glpi_massiveaction']['items']) > 0) {
               $key = array_pop($_SESSION['glpi_massiveaction']['items']);
               if ($item->can($key,'w')) {
                  //Try to get the OCS server whose machine belongs
                  $query = "SELECT `plugin_ocsinventoryng_ocsservers_id`, `id`
                            FROM `glpi_plugin_ocsinventoryng_ocslinks`
                            WHERE `computers_id` = '".$key."'";
                  $result = $DB->query($query);
                  if ($DB->numrows($result) == 1) {
                     $data = $DB->fetch_assoc($result);
                     if ($data['plugin_ocsinventoryng_ocsservers_id'] != -1) {
                        //Force update of the machine
                        PluginOcsinventoryngOcsServer::updateComputer($data['id'],
                                                                      $data['plugin_ocsinventoryng_ocsservers_id'],
                                                                      1, 1);
                        $_SESSION['glpi_massiveaction']['items_ok']++;
                     } else {
                        $_SESSION['glpi_massiveaction']['items_ko']++;
                     }
                  } else {
                     $_SESSION['glpi_massiveaction']['items_ko']++;
                  }
               } else {
                  $_SESSION['glpi_massiveaction']['items_nbnoright']++;
               }
               Html::redirect($_SERVER['PHP_SELF'].'?multiple_actions=1');
            } else {
               $REDIRECT  = $_SESSION['glpi_massiveaction']['REDIRECT'];
               $nbok      = $_SESSION['glpi_massiveaction']['items_ok'];
               $nbko      = $_SESSION['glpi_massiveaction']['items_ko'];
               $nbnoright = $_SESSION['glpi_massiveaction']['items_nbnoright'];
               unset($_SESSION['glpi_massiveaction']);
            }
         }
         break;
   }

   return array('ok'      => $nbok,
                'ko'      => $nbko,
                'noright' => $noright);   
}


/**
 * @param $itemtype
**/
function plugin_ocsinventoryng_getAddSearchOptions($itemtype) {

    $sopt = array();

   if ($itemtype == 'Computer') {
      if (plugin_ocsinventoryng_haveRight("ocsng","r")) {

         $sopt[10002]['table']         = 'glpi_plugin_ocsinventoryng_ocslinks';
         $sopt[10002]['field']         = 'last_update';
         $sopt[10002]['name']          = __('GLPI import date', 'ocsinventoryng');
         $sopt[10002]['datatype']      = 'datetime';
         $sopt[10002]['massiveaction'] = false;
         $sopt[10002]['joinparams']    = array('jointype' => 'child');

         $sopt[10003]['table']         = 'glpi_plugin_ocsinventoryng_ocslinks';
         $sopt[10003]['field']         = 'last_ocs_update';
         $sopt[10003]['name']          = __('Last OCSNG inventory date', 'ocsinventoryng');
         $sopt[10003]['datatype']      = 'datetime';
         $sopt[10003]['massiveaction'] = false;
         $sopt[10003]['joinparams']    = array('jointype' => 'child');


         $sopt[10001]['table']         = 'glpi_plugin_ocsinventoryng_ocslinks';
         $sopt[10001]['field']         = 'use_auto_update';
         $sopt[10001]['linkfield']     = '_auto_update_ocs'; // update through compter update process
         $sopt[10001]['name']          = __('Automatic update OCSNG', 'ocsinventoryng');
         $sopt[10001]['datatype']      = 'bool';
         $sopt[10001]['joinparams']    = array('jointype' => 'child');

         $sopt[10004]['table']         = 'glpi_plugin_ocsinventoryng_ocslinks';
         $sopt[10004]['field']         = 'ocs_agent_version';
         $sopt[10004]['name']          = __('Inventory agent', 'ocsinventoryng');
         $sopt[10004]['massiveaction'] = false;
         $sopt[10004]['joinparams']    = array('jointype' => 'child');

         $sopt[10005]['table']         = 'glpi_plugin_ocsinventoryng_ocslinks';
         $sopt[10005]['field']         = 'tag';
         $sopt[10005]['name']          = __('OCSNG TAG', 'ocsinventoryng');
         $sopt[10005]['datatype']      = 'string';
         $sopt[10005]['massiveaction'] = false;
         $sopt[10005]['joinparams']    = array('jointype' => 'child');

         $sopt[10006]['table']         = 'glpi_plugin_ocsinventoryng_ocslinks';
         $sopt[10006]['field']         = 'ocsid';
         $sopt[10006]['name']          = __('OCSNG ID', 'ocsinventoryng');
         $sopt[10006]['datatype']      = 'number';
         $sopt[10006]['massiveaction'] = false;
         $sopt[10006]['joinparams']    = array('jointype' => 'child');
         
         //$sopt['registry']           = __('Registry', 'ocsinventoryng');

         $sopt[10010]['table']         = 'glpi_plugin_ocsinventoryng_registrykeys';
         $sopt[10010]['field']         = 'value';
         $sopt[10010]['name']          = sprintf(__('%1$s: %2$s'), __('Registry',
                                               'ocsinventoryng'), __('Key/Value',
                                               'ocsinventoryng'));
         $sopt[10010]['forcegroupby']  = true;
         $sopt[10010]['massiveaction'] = false;
         $sopt[10010]['joinparams']    = array('jointype' => 'child');

         $sopt[10011]['table']         = 'glpi_plugin_ocsinventoryng_registrykeys';
         $sopt[10011]['field']         = 'ocs_name';
         $sopt[10011]['name']          = sprintf(__('%1$s: %2$s'), __('Registry',
                                               'ocsinventoryng'), __('OCSNG name',
                                               'ocsinventoryng'));
         $sopt[10011]['forcegroupby']  = true;
         $sopt[10011]['massiveaction'] = false;
         $sopt[10011]['joinparams']    = array('jointype' => 'child');
      }
   }
   return $sopt;
}


/**
 * @param $type
 * @param $ID
 * @param $data
 * @param $num
**/
function plugin_ocsinventoryng_displayConfigItem($type, $ID, $data, $num) {

   $searchopt  = &Search::getOptions($type);
   $table      = $searchopt[$ID]["table"];
   $field      = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi_plugin_ocsinventoryng_ocslinks.last_update" :
      case "glpi_plugin_ocsinventoryng_ocslinks.last_ocs_update" :
         return " class='center'";
   }
   return "";
}


/**
 * @param $type
 * @param $id
 * @param $num
**/
function plugin_ocsinventoryng_addSelect($type, $id, $num) {

   $searchopt  = &Search::getOptions($type);
   $table      = $searchopt[$id]["table"];
   $field      = $searchopt[$id]["field"];

   $out = "`$table`.`$field` AS ITEM_$num,
           `$table`.`ocsid` AS ocsid,
           `$table`.`plugin_ocsinventoryng_ocsservers_id` AS plugin_ocsinventoryng_ocsservers_id, ";

   if ($num == 0) {
      switch ($type) {
         case 'PluginOcsinventoryngNotimportedcomputer' :
            return $out;

         case 'PluginOcsinventoryngDetail' :
            $out .= "`$table`.`plugin_ocsinventoryng_threads_id`,
                     `$table`.`threadid`, ";
            return $out;
      }
      return "";
   }
}


/**
 * @param $link
 * @param $nott
 * @param $type
 * @param $ID
 * @param $val
**/
function plugin_ocsinventoryng_addWhere($link, $nott, $type, $ID, $val) {

   $searchopt  = &Search::getOptions($type);
   $table      = $searchopt[$ID]["table"];
   $field      = $searchopt[$ID]["field"];

   $SEARCH     = Search::makeTextSearch($val,$nott);
    switch ($table.".".$field) {
         case "glpi_plugin_ocsinventoryng_details.action" :
               return $link." `$table`.`$field` = '$val' ";
    }
   return "";
}


/**
 * @param $type
 * @param $id
 * @param $data
 * @param $num
**/
function plugin_ocsinventoryng_giveItem($type, $id, $data, $num) {
   global $CFG_GLPI, $DB;

   $searchopt  = &Search::getOptions($type);
   $table      = $searchopt[$id]["table"];
   $field      = $searchopt[$id]["field"];

   switch ("$table.$field") {
      case "glpi_plugin_ocsinventoryng_details.action" :
         $detail = new PluginOcsinventoryngDetail();
         return $detail->giveActionNameByActionID($data["ITEM_$num"]);

      case "glpi_plugin_ocsinventoryng_details.computers_id" :
         $comp = new Computer();
         $comp->getFromDB($data["ITEM_$num"]);
         return "<a href='".Toolbox::getItemTypeFormURL('Computer')."?id=".$data["ITEM_$num"]."'>".
                  $comp->getName()."</a>";

      case "glpi_plugin_ocsinventoryng_details.plugin_ocsinventoryng_ocsservers_id" :
         $ocs = new PluginOcsinventoryngOcsServer();
         $ocs->getFromDB($data["ITEM_$num"]);
         return "<a href='".Toolbox::getItemTypeFormURL('PluginOcsinventoryngOcsServer')."?id=".
                  $data["ITEM_$num"]."'>".$ocs->getName()."</a>";

      case "glpi_plugin_ocsinventoryng_details.rules_id" :
         $detail = new PluginOcsinventoryngDetail();
         $detail->getFromDB($data['id']);
         return PluginOcsinventoryngNotimportedcomputer::getRuleMatchedMessage($detail->fields['rules_id']);

      case "glpi_plugin_ocsinventoryng_notimportedcomputers.reason" :
         return PluginOcsinventoryngNotimportedcomputer::getReason($data["ITEM_$num"]);
   }
   return '';
}


/**
 * @param $params array
**/
function plugin_ocsinventoryng_searchOptionsValues($params=array()) {

   switch($params['searchoption']['field']) {
      case "action":
         PluginOcsinventoryngDetail::showActions($params['name'],$params['value']);
         return true;
   }
   return false;
}

/**
 *
 * Criteria for rules
 * @since 0.84
 * @param $params           input data
 * @return an array of criteria
 */
function plugin_ocsinventoryng_getRuleCriteria($params) {
   $criteria = array();

   switch ($params['rule_itemtype']) {
      case 'RuleImportEntity':
         $criteria['TAG']['table']                = 'accountinfo';
         $criteria['TAG']['field']                = 'TAG';
         $criteria['TAG']['name']                 = __('OCSNG TAG', 'ocsinventoryng');
         $criteria['TAG']['linkfield']            = 'HARDWARE_ID';

         $criteria['DOMAIN']['table']             = 'hardware';
         $criteria['DOMAIN']['field']             = 'WORKGROUP';
         $criteria['DOMAIN']['name']              = __('Domain');
         $criteria['DOMAIN']['linkfield']         = '';

         $criteria['OCS_SERVER']['table']         = 'glpi_plugin_ocsinventoryng_ocsservers';
         $criteria['OCS_SERVER']['field']         = 'name';
         $criteria['OCS_SERVER']['name']          = _n('OCSNG server', 'OCSNG servers', 1,
                                                       'ocsinventoryng');
         $criteria['OCS_SERVER']['linkfield']     = '';
         $criteria['OCS_SERVER']['type']          = 'dropdown';
         $criteria['OCS_SERVER']['virtual']       = true;
         $criteria['OCS_SERVER']['id']            = 'ocs_server';

         $criteria['IPSUBNET']['table']           = 'networks';
         $criteria['IPSUBNET']['field']           = 'IPSUBNET';
         $criteria['IPSUBNET']['name']            = __('Subnet');
         $criteria['IPSUBNET']['linkfield']       = 'HARDWARE_ID';

         $criteria['IPADDRESS']['table']          = 'networks';
         $criteria['IPADDRESS']['field']          = 'IPADDRESS';
         $criteria['IPADDRESS']['name']           = __('IP address');
         $criteria['IPADDRESS']['linkfield']      = 'HARDWARE_ID';

         $criteria['MACHINE_NAME']['table']       = 'hardware';
         $criteria['MACHINE_NAME']['field']       = 'NAME';
         $criteria['MACHINE_NAME']['name']        = __("Computer's name");
         $criteria['MACHINE_NAME']['linkfield']   = '';

         $criteria['DESCRIPTION']['table']        = 'hardware';
         $criteria['DESCRIPTION']['field']        = 'DESCRIPTION';
         $criteria['DESCRIPTION']['name']         = __('Description');
         $criteria['DESCRIPTION']['linkfield']    = '';

         $criteria['SSN']['table']                = 'bios';
         $criteria['SSN']['field']                = 'SSN';
         $criteria['SSN']['name']                 = __('Serial number');
         $criteria['SSN']['linkfield']            = 'HARDWARE_ID';
         break;

      case 'RuleImportComputer':
         $criteria['ocsservers_id']['table']       = 'glpi_plugin_ocsinventoryng_ocsservers';
         $criteria['ocsservers_id']['field']       = 'name';
         $criteria['ocsservers_id']['name']        = _n('OCSNG server', 'OCSNG servers', 1,
                                                        'ocsinventoryng');
         $criteria['ocsservers_id']['linkfield']   = '';
         $criteria['ocsservers_id']['type']        = 'dropdown';

         $criteria['TAG']['name']                  = __('OCSNG TAG', 'ocsinventoryng');
         break;
   }

   return $criteria;
}

/**
 *
 * Actions for rules
 * @since 0.84
 * @param $params           input data
 * @return an array of actions
 */
function plugin_ocsinventoryng_getRuleActions($params) {
//
   $actions = array();

   switch ($params['rule_itemtype']) {
      case 'RuleImportEntity':
         $actions['_affect_entity_by_tag']['name']          = __('Entity from TAG');
         $actions['_affect_entity_by_tag']['type']          = 'text';
         $actions['_affect_entity_by_tag']['force_actions'] = array('regex_result');
         break;
      case 'RuleImportComputer':
         $actions['_fusion']['name']        = _n('OCSNG link', 'OCSNG links', 1, 'ocsinventoryng');
         $actions['_fusion']['type']        = 'fusion_type';
      break;
   }

   return $actions;
}

/**
 * @see inc/RuleCollection::prepareInputDataForProcess()
 * @since 0.84
 * @param $params           input data
 * @return an array of criteria value to add for processing
 **/
function plugin_ocsinventoryng_ruleCollectionPrepareInputDataForProcess($params) {
   global $PluginOcsinventoryngDBocs;
   switch ($params['rule_itemtype']) {
      case 'RuleImportEntity':
      case 'RuleImportComputer':

         if ($params['rule_itemtype'] == 'RuleImportEntity') {
            $ocsservers_id   = $params['values']['input']['ocsservers_id'];
         } else {
            $ocsservers_id   = $params['values']['params']['plugin_ocsinventoryng_ocsservers_id'];
         }

         $tables          = plugin_ocsinventoryng_getTablesForQuery();
         $fields          = plugin_ocsinventoryng_getFieldsForQuery();
         $rule_parameters = array('ocsservers_id' => $ocsservers_id);
         $select_sql      = "";
         if (isset($params['values']['params']['ocsid'])) {
            $ocsid = $params['values']['params']['ocsid'];
         } else if ($params['values']['input']['id']) {
            $ocsid = $params['values']['input']['id'];
         }

         //Get information about network ports
         $query = "SELECT *
                   FROM `networks`
                   WHERE `HARDWARE_ID` = '$ocsid'";

         $ipblacklist  = Blacklist::getIPs();
         $macblacklist = Blacklist::getMACs();

         foreach ($PluginOcsinventoryngDBocs->request($query) as $data) {
            if (isset($data['IPSUBNET'])) {
               $rule_parameters['IPSUBNET'][] = $data['IPSUBNET'];
            }
            if (isset($data['MACADDR']) && !in_array($data['MACADDR'], $macblacklist)) {
               $rule_parameters['MACADDRESS'][] = $data['MACADDR'];
            }
            if (isset($data['IPADDRESS']) && !in_array($data['IPADDRESS'], $ipblacklist)) {
               $rule_parameters['IPADDRESS'][] = $data['IPADDRESS'];
            }
         }

         //Build the select request
         foreach ($fields as $field) {
            switch (Toolbox::strtoupper($field)) {
               //OCS server ID is provided by extra_params -> get the configuration associated with the ocs server
               case "OCS_SERVER" :
                  $rule_parameters["OCS_SERVER"] = $ocsservers_id;
                  break;

                  //TAG and DOMAIN should come from the OCS DB
               default :
                  $select_sql .= ($select_sql != "" ? " , " : "") . $field;
            }
         }

         //Build the FROM part of the request
         //Remove all the non duplicated table names
         $from_sql = "FROM `hardware` ";
         foreach ($tables as $table => $linkfield) {
            if ($table!='hardware' && !empty($linkfield)) {
               $from_sql .= " LEFT JOIN `$table` ON (`$table`.`$linkfield` = `hardware`.`ID`)";
            }
         }

         if ($select_sql != "") {
            //Build the all request
            $sql = "SELECT $select_sql
                    $from_sql
                    WHERE `hardware`.`ID` = '$ocsid'";

            PluginOcsinventoryngOcsServer::checkOCSconnection($ocsservers_id);
            $result    = $PluginOcsinventoryngDBocs->query($sql);
            $ocs_data  = array();
            $fields    = plugin_ocsinventoryng_getFieldsForQuery(1);

            //May have more than one line : for example in case of multiple network cards
            if ($PluginOcsinventoryngDBocs->numrows($result) > 0) {
               while ($datas = $PluginOcsinventoryngDBocs->fetch_assoc($result)) {
                  foreach ($fields as $field) {
                     if ($field != "OCS_SERVER" && isset($datas[$field])) {
                        $ocs_data[$field][] = $datas[$field];
                     }
                  }
               }
            }

            //This cas should never happend but...
            //Sometimes OCS can't find network ports but fill the right ip in hardware table...
            //So let's use the ip to proceed rules (if IP is a criteria of course)
            if (in_array("IPADDRESS",$fields) && !isset($ocs_data['IPADDRESS'])) {
               $ocs_data['IPADDRESS']
                  = PluginOcsinventoryngOcsServer::getGeneralIpAddress($ocsservers_id, $ocsid);
            }
            return array_merge($rule_parameters, $ocs_data);
         }
         return $rule_parameters;
   }
   return array();
}

/**
 *
 * Actions for rules
 * @since 0.84
 * @param $params           input data
 * @return an array of actions
 */
function plugin_ocsinventoryng_executeActions($params) {

   $action = $params['action'];
   $output = $params['output'];
   switch ($params['params']['rule_itemtype']) {
      case 'RuleImportComputer':
         if ($action->fields['field'] == '_fusion') {
            if ($action->fields["value"] == RuleImportComputer::RULE_ACTION_LINK_OR_IMPORT) {
               if (isset($params['params']['criterias_results']['found_computers'])) {
                  $output['found_computers'] = $params['params']['criterias_results']['found_computers'];
                  $output['action']          = PluginOcsinventoryngOcsServer::LINK_RESULT_LINK;
               } else {
                  $output['action'] = PluginOcsinventoryngOcsServer::LINK_RESULT_IMPORT;
               }

            } else if ($action->fields["value"] == RuleImportComputer::RULE_ACTION_LINK_OR_NO_IMPORT) {
               if (isset($params['params']['criterias_results']['found_computers'])) {
                  $output['found_computers'] = $params['params']['criterias_results']['found_computers'];;
                  $output['action']          = PluginOcsinventoryngOcsServer::LINK_RESULT_LINK;
               } else {
                  $output['action'] = PluginOcsinventoryngOcsServer::LINK_RESULT_NO_IMPORT;
               }
            }
         } else {
            $output['action'] = PluginOcsinventoryngOcsServer::LINK_RESULT_NO_IMPORT;
         }
         break;
   }
   return $output;
}

/**
 *
 * Preview for test a Rule
 * @since 0.84
 * @param $params           input data
 * @return $output array
 */
function plugin_ocsinventoryng_preProcessRulePreviewResults($params){
   $output = $params['output'];

   switch ($params['params']['rule_itemtype']) {
      case 'RuleImportComputer':

         //If ticket is assign to an object, display this information first
         if (isset($output["action"])){
            echo "<tr class='tab_bg_2'>";
            echo "<td>".__('Action type')."</td>";
            echo "<td>";

            switch ($output["action"]){
               case PluginOcsinventoryngOcsServer::LINK_RESULT_LINK:
                  _e('Link possible');
                  break;

               case PluginOcsinventoryngOcsServer::LINK_RESULT_NO_IMPORT:
                  _e('Import refused');
                  break;

               case PluginOcsinventoryngOcsServer::LINK_RESULT_IMPORT:
                  _e('New computer created in GLPI');
                  break;
            }

            echo "</td>";
            echo "</tr>";
            if ($output["action"] != PluginOcsinventoryngOcsServer::LINK_RESULT_NO_IMPORT
               && isset($output["found_computers"])){
               echo "<tr class='tab_bg_2'>";
               $item = new Computer;
               if ($item->getFromDB($output["found_computers"][0])){
                  echo "<td>".__('Link with computer')."</td>";
                  echo "<td>".$item->getLink(array('comments' => true))."</td>";
               }
               echo "</tr>";
            }
         }
      break;
   }
   return $output;
}

/**
 *
 * Preview for test a RuleCoolection
 * @since 0.84
 * @param $params           input data
 * @return $output array
 */
function plugin_ocsinventoryng_preProcessRuleCollectionPreviewResults($params){
   return plugin_ocsinventoryng_preProcessRulePreviewResults($params);
}

/**
 * Get the list of all tables to include in the query
 *
 * @return an array of table names
 **/
function plugin_ocsinventoryng_getTablesForQuery() {

   $tables = array();
   $crits = plugin_ocsinventoryng_getRuleCriteria(array('rule_itemtype' => 'RuleImportEntity'));
   foreach ($crits as $criteria) {
      if ((!isset($criteria['virtual'])
         || !$criteria['virtual'])
            && $criteria['table'] != ''
               && !isset($tables[$criteria["table"]])) {

         $tables[$criteria['table']] = $criteria['linkfield'];
      }
   }
   return $tables;
}


/**
 *  * Get fields needed to process criterias
 *
 * @param $withouttable fields without tablename ? (default 0)
 *
 * @return an array of needed fields
 **/
function plugin_ocsinventoryng_getFieldsForQuery($withouttable=0) {

   $fields = array();
   foreach (plugin_ocsinventoryng_getRuleCriteria(array('rule_itemtype' => 'RuleImportEntity')) as $key => $criteria) {
      if ($withouttable) {
         if (strcasecmp($key,$criteria['field']) != 0) {
            $fields[] = $key;
         } else {
            $fields[] = $criteria['field'];
         }

      } else {
         //If the field is different from the key
         if (strcasecmp($key,$criteria['field']) != 0) {
            $as = " AS ".$key;
         } else {
            $as = "";
         }

         //If the field name is not null AND a table name is provided
         if (($criteria['field'] != ''
         && (!isset($criteria['virtual']) || !$criteria['virtual']))) {
            if ( $criteria['table'] != '') {
               $fields[] = $criteria['table'].".".$criteria['field'].$as;
            } else {
               $fields[] = $criteria['field'].$as;
            }
         } else {
            $fields[] = $criteria['id'];
         }
      }
   }
   return $fields;
}


/**
 * Get foreign fields needed to process criterias
 *
 * @return an array of needed fields
 **/
function plugin_ocsinventoryng_getFKFieldsForQuery() {

   $fields = array();
   foreach (plugin_ocsinventoryng_getRuleCriteria(array('rule_itemtype'
                                                      => 'RuleImportEntity')) as $criteria) {
      //If the field name is not null AND a table name is provided
      if ((!isset($criteria['virtual']) || !$criteria['virtual'])
      && $criteria['linkfield'] != '') {
         $fields[] = $criteria['table'].".".$criteria['linkfield'];
      }
   }
   return $fields;
}

/**
 *
 * Add global criteria for ruleImportComputer rules engine
 * @since 1.0
 * @param $global_criteria an array of global criteria for this rule engine
 * @return the array including plugin's criteria
 */
function plugin_ocsinventoryng_ruleImportComputer_addGlobalCriteria($global_criteria) {
   return array_merge($global_criteria, array('IPADDRESS', 'IPSUBNET', 'MACADDRESS'));
}

/**
 *
 * Get SQL restriction for ruleImportComputer
 * @param params necessary parameters to build SQL restrict requests
 * @return an array with SQL restrict resquests
 * @since 1.0
 */
function plugin_ocsinventoryng_ruleImportComputer_getSqlRestriction($params = array()) {
   // Search computer, in entity, not already linked
   $params['sql_where'] .= " AND `glpi_plugin_ocsinventoryng_ocslinks`.`computers_id` IS NULL
                             AND `glpi_computers`.`entities_id` IN (".$params['where_entity'].")
                             AND `glpi_computers`.`is_template` = '0' ";

   $params['sql_from']  = "`glpi_computers`
                           LEFT JOIN `glpi_plugin_ocsinventoryng_ocslinks`
                              ON (`glpi_computers`.`id` = `glpi_plugin_ocsinventoryng_ocslinks`.`computers_id`)";

   $needport = false;
   $needip   = false;
   foreach ($params['criteria'] as $criteria) {
      switch ($criteria->fields['criteria']) {
         case 'IPADDRESS' :
            if (count($params['input']["IPADDRESS"])) {
               $needport   = true;
               $needip     = true;
               $params['sql_where'] .= " AND `glpi_ipaddresses`.`name` IN ('";
               $params['sql_where'] .= implode("','", $params['input']["IPADDRESS"]);
               $params['sql_where'] .= "')";
            } else {
               $params['sql_where'] =  " AND 0 ";
            }
            break;

         case 'MACADDRESS' :
            if (count($params['input']["MACADDRESS"])) {
               $needport   = true;
               $params['sql_where'] .= " AND `glpi_networkports`.`mac` IN ('";
               $params['sql_where'] .= implode("','",$params['input']['MACADDRESS']);
               $params['sql_where'] .= "')";
            } else {
               $params['sql_where'] =  " AND 0 ";
            }
            break;
      }
   }

   if ($needport) {
      $params['sql_from'] .= " LEFT JOIN `glpi_networkports`
                               ON (`glpi_computers`.`id` = `glpi_networkports`.`items_id`
                                  AND `glpi_networkports`.`itemtype` = 'Computer') ";
   }
   if ($needip) {
      $params['sql_from'] .= " LEFT JOIN `glpi_networknames`
                               ON (`glpi_networkports`.`id` =  `glpi_networknames`.`items_id`
                                  AND `glpi_networknames`.`itemtype`='NetworkPort')
                               LEFT JOIN `glpi_ipaddresses`
                                  ON (`glpi_ipaddresses`.`items_id` = `glpi_networknames`.`id`)";
   }
   return $params;
}
/**
 *
 * Display plugin's entries in unlock fields form
 * @since 1.0
 * @param $params an array which contains the item and the header boolean
 * @return an array
 */
function plugin_ocsinventoryng_showLocksForItem($params = array()) {
   global $DB;

   $comp   = $params['item'];
   $header = $params['header'];
   $ID     = $comp->getID();

   if (!Session::haveRight("computer", "w")) {
      return $params;
   }
   //First of all let's look it the computer is managed by OCS Inventory
   $query = "SELECT *
   FROM `glpi_plugin_ocsinventoryng_ocslinks`
   WHERE `computers_id` = '$ID'";

   $result = $DB->query($query);
   if ($DB->numrows($result) == 1) {
      $data = $DB->fetch_assoc($result);

      // Print lock fields for OCSNG
      $lockable_fields = PluginOcsinventoryngOcsServer::getLockableFields();
      $locked          = importArrayFromDB($data["computer_update"]);

      if (!in_array(PluginOcsinventoryngOcsServer::IMPORT_TAG_078, $locked)) {
         $locked = PluginOcsinventoryngOcsServer::migrateComputerUpdates($ID, $locked);
      }

      if (count($locked) > 0) {
         foreach ($locked as $key => $val) {
            if (!isset($lockable_fields[$val])) {
               unset($locked[$key]);
            }
         }
      }

      if (count($locked)) {
         $header = true;
         echo "<tr><th colspan='2'>". _n('Locked field', 'Locked fields', 2, 'ocsinventoryng').
         "</th></tr>\n";

         foreach ($locked as $key => $val) {
            echo "<tr class='tab_bg_1'>";

            echo "<td class='center' width='10'>";
            echo "<input type='checkbox' name='lockfield[" . $key . "]'></td>";
            echo "<td class='left' width='95%'>" . $lockable_fields[$val] . "</td>";
            echo "</tr>\n";
         }
      }
   }
   $params['header'] = $header;
   return $params;
}

/**
 *
 * Unlock fields managed by the plugin
 * @since 1.0
 * @param $_POST array
 */
function plugin_ocsinventoryng_unlockFields($params = array()) {
   $computer = new Computer();
   $computer->check($_POST['id'], 'w');
   if (isset($_POST["lockfield"]) && count($_POST["lockfield"])) {
      foreach ($_POST["lockfield"] as $key => $val) {
         PluginOcsinventoryngOcsServer::deleteInOcsArray($_POST["id"], $key, "computer_update");
      }
   }
}

/**
 * Update plugin with new computers_id and new entities_id
 *
 * @param $options    array of possible options
 * - itemtype
 * - ID            old ID
 * - newID         new ID
 * - entities_id   new entities_id
**/
function plugin_ocsinventoryng_item_transfer($options=array()) {
   global $DB;

   if ($options['type'] == 'Computer') {

      $query = "UPDATE glpi_plugin_ocsinventoryng_ocslinks
                SET `computers_id` = '".$options['newID']."',
                    `entities_id` = '".$options['entities_id']."'
                WHERE `computers_id` = '".$options['id']."'";

      $DB->query($query);

      Session::addMessageAfterRedirect("Transfer Computer Hook ". $options['type']." " .
                                       $options['id']."->".$options['newID']);

      return false;

   }
}


//------------------- Locks migration -------------------

/**
 * Move locks from ocslink.import_* to is_dynamic in related tables
 *
 * @param $migration
**/
function plugin_ocsinventoryng_migrateComputerLocks(Migration $migration) {
   global $DB;

   $import = array('import_printer'    => 'Printer',
                   'import_monitor'    => 'Monitor',
                   'import_peripheral' => 'Peripheral');

   foreach ($import as $field => $itemtype) {
      foreach ($DB->request('ocs_glpi_ocslinks', '', array('computers_id', $field)) as $data) {
         if (FieldExists('ocs_glpi_ocslinks', $field)) {
            $import_field = importArrayFromDB($data[$field]);

            //If array is not empty
            if (!empty($import_field)) {
               $query_update = "UPDATE `glpi_computers_items`
                                SET `is_dynamic`='1'
                                WHERE `id` IN (".implode(',',array_keys($import_field)).")
                                      AND `itemtype`='$itemtype'";
               $DB->query($query_update);
            }
         }
      }
      $migration->dropField('ocs_glpi_ocslinks', $field);
   }
   //Migration disks and vms
   $import = array('import_disk'     => 'glpi_computerdisks',
                   'import_vm'       => 'glpi_computervirtualmachines',
                   'import_software' => 'glpi_computers_softwareversions',
                   'import_ip'       => 'glpi_networkports');

   foreach ($import as $field => $table) {
      if (FieldExists('ocs_glpi_ocslinks', $field)) {
         foreach ($DB->request('ocs_glpi_ocslinks', '', array('computers_id', $field)) as $data) {
            $import_field = importArrayFromDB($data[$field]);

            //If array is not empty
            if (!empty($import_field)) {
               $in_where = "(".implode(',',array_keys($import_field)).")";
               $query_update = "UPDATE `$table`
                                SET `is_dynamic`='1'
                                WHERE `id` IN $in_where";
               $DB->query($query_update);
               if ($table == 'glpi_networkports') {
                  $query_update = "UPDATE `glpi_networkports` AS PORT,
                                          `glpi_networknames` AS NAME
                                   SET NAME.`is_dynamic` = 1
                                   WHERE PORT.`id` IN $in_where
                                     AND NAME.`itemtype` = 'NetworkPort'
                                     AND NAME.`items_id` = PORT.`id`";
                  $DB->query($query_update);
                  $query_update = "UPDATE `glpi_networkports` AS PORT,
                                          `glpi_networknames` AS NAME,
                                          `glpi_ipaddresses` AS ADDR
                                   SET ADDR.`is_dynamic` = 1
                                   WHERE PORT.`id` IN $in_where
                                     AND NAME.`itemtype` = 'NetworkPort'
                                     AND NAME.`items_id` = PORT.`id`
                                     AND ADDR.`itemtype` = 'NetworkName'
                                     AND ADDR.`items_id` = NAME.`id`";
                  $DB->query($query_update);
               }
            }
         }
         $migration->dropField('ocs_glpi_ocslinks', $field);
      }
   }

   if (FieldExists('ocs_glpi_ocslinks', 'import_device')) {
      foreach ($DB->request('ocs_glpi_ocslinks', '', array('computers_id', 'import_device'))
               as $data) {
         $import_device = importArrayFromDB($data['import_device']);
         if (!in_array('_version_078_', $import_device)) {
            $import_device = plugin_ocsinventoryng_migrateImportDevice($import_device);
         }

         $devices = array();
         $types   = Item_Devices::getDeviceTypes();
         foreach ($import_device as $key => $val) {
            if (!$key) { // OcsServer::IMPORT_TAG_078
               continue;
            }
            list($type, $nomdev) = explode('$$$$$', $val);
            list($type, $iddev)  = explode('$$$$$', $key);
            if (!isset($types[$type])) { // should never happen
               continue;
            }
            $devices[$types[$type]][] = $iddev;
         }
         foreach ($devices as $type => $data) {
            //If array is not empty
            $query_update = "UPDATE `".getTableForItemType($type)."`
                             SET `is_dynamic`='1'
                             WHERE `id` IN (".implode(',',$data).")";
           $DB->query($query_update);
         }
      }
      $migration->dropField('ocs_glpi_ocslinks', 'import_device');
   }
   $migration->migrationOneTable('ocs_glpi_ocslinks');
}


/**
 * Migration import_device field if GLPI version is not 0.78
 *
 * @param $import_device     array
 *
 * @return import_device array migrated in post 0.78 scheme
**/
function plugin_ocsinventoryng_migrateImportDevice($import_device=array()) {

   $new_import_device = array('_version_078_');
   if (count($import_device)) {
      foreach ($import_device as $key=>$val) {
         $tmp = explode('$$$$$', $val);

         if (isset($tmp[1])) { // Except for old IMPORT_TAG
            $tmp2                     = explode('$$$$$', $key);
            // Index Could be 1330395 (from glpi 0.72)
            // Index Could be 5$$$$$5$$$$$5$$$$$5$$$$$5$$$$$1330395 (glpi 0.78 bug)
            // So take the last part of the index
            $key2                     = $tmp[0].'$$$$$'.array_pop($tmp2);
            $new_import_device[$key2] = $val;
         }

      }
   }
   return $new_import_device;
}

?>