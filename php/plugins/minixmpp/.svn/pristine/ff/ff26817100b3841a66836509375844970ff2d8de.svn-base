<?php

function plugin_minixmpp_install() {
   global $DB;
   // Création de la table uniquement lors de la première installation
   if (!TableExists("glpi_plugin_minixmpp_configs")) {
      // requete de création de la table pour les droits
      $query = "CREATE TABLE `glpi_plugin_minixmpp_configs` (
                  `id` int(11) NOT NULL default '0',
                  `anoserver` VARCHAR(100),
                  `conference` VARCHAR(100),
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->queryOrDie($query, "erreur lors de la création de la table des droits ".$DB->error());

      $query = "INSERT INTO `glpi_plugin_minixmpp_configs`
                       (`anoserver`,`conference`)
                VALUES ('anonymous.jappix.com', 'conference.jappix.com')";
      $DB->queryOrDie($query,
                "erreur lors de l'insertion des valeurs par défaut dans la table de configuration ".$DB->error());
   }

   return true;
}

function plugin_minixmpp_uninstall() {
    global $DB; 
    $DB->query("DROP TABLE IF EXISTS `glpi_plugin_minixmpp_configs`;");
    return true;
}

?>
