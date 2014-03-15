DROP TABLE IF EXISTS `glpi_plugin_escalation_groups_groups`;

CREATE TABLE `glpi_plugin_escalation_groups_groups` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `groups_id_source` int(11) NOT NULL DEFAULT '0',
   `groups_id_destination` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



DROP TABLE IF EXISTS `glpi_plugin_escalation_configs`;

CREATE TABLE `glpi_plugin_escalation_configs` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `entities_id` int(11) NOT NULL DEFAULT '0',
   `unique_assigned` varchar(255) DEFAULT NULL,
   `workflow`  varchar(255) DEFAULT NULL,
   `limitgroup`  varchar(255) DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



DROP TABLE IF EXISTS `glpi_plugin_escalation_profiles`;

CREATE TABLE `glpi_plugin_escalation_profiles` (
  `profiles_id` int(11) NOT NULL DEFAULT '0',
  `bypassworkflow` char(1) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



INSERT INTO `glpi_plugin_escalation_configs`
   (`id` ,`entities_id` ,`unique_assigned` ,`workflow`)
VALUES (NULL , '0', '0', '0');
