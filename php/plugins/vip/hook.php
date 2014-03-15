<?php

function plugin_vip_install() {
	global $DB;
	
	$migration = new Migration(100);
	
	// Création de la table uniquement lors de la première installation
	if (!TableExists("glpi_plugin_vip_profiles")) {
	
		// Table des droits du profil
		$query = "CREATE TABLE `glpi_plugin_vip_profiles` (
	               `id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
	               `show_vip_tab` tinyint(1) collate utf8_unicode_ci default NULL,
	               PRIMARY KEY  (`id`)
	             ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
	
		$DB->query($query) or die("Error creating Vip Profiles table" . $DB->error());
	
		$migration->executeMigration();
	
		//creation du premier accès nécessaire lors de l'installation du plugin
		include_once(GLPI_ROOT."/plugins/vip/inc/profile.class.php");
		PluginVipProfile::createAdminAccess($_SESSION['glpiactiveprofile']['id']);
	}

	if (!TableExists("glpi_plugin_vip_groups")) {
      // 
      	$query = "CREATE TABLE `glpi_plugin_vip_groups` (
                  `id` int(11) NOT NULL default 0 COMMENT 'RELATION to glpi_groups(id)',
                  `isvip` tinyint(1) default '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      	$DB->query($query) or die("Erreur lors de la création de la table des groupes vip ".$DB->error());

      	$query = "INSERT INTO `glpi_plugin_vip_groups`
                  (`id`, `isvip`)
                  VALUES ('0', '0')";
      	$DB->query($query) or die("Erreur lors de l'insertion des valeurs par défaut dans la table des groupes vip ".$DB->error());
   	}
	
	if (!TableExists("glpi_plugin_vip_tickets")) {
		$query = "CREATE TABLE glpi_plugin_vip_tickets (
				  id int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_tickets (id)',
				  isvip tinyint(1) default '0',
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

		$DB->query($query) or die("Error creating Vip Tickets table");


	}
	$query = "INSERT INTO glpi_plugin_vip_tickets
				   SELECT id, '0'
					 FROM glpi_tickets
		 ON DUPLICATE KEY
				   UPDATE isvip = '0'";

	$DB->query($query) or die("Error inserting ticket in Vip Tickets table");

	$migration->executeMigration();

    return true;
}

function plugin_vip_uninstall() {
	global $DB;

	$tables = array("glpi_plugin_vip_profiles","glpi_plugin_vip_groups","glpi_plugin_vip_tickets");

	foreach($tables as $table) {
		$DB->query("DROP TABLE IF EXISTS `$table`;");
	}
	return true;
}

function plugin_vip_getPluginsDatabaseRelations() {

	$plugin = new Plugin();
	if ($plugin->isActivated("vip"))
		return array(
					 "glpi_profiles" => array ("glpi_plugin_vip_profiles" => "id"),
					 "glpi_groups"   => array ("glpi_plugin_vip_groups"   => "id"),
					 "glpi_tickets"  => array ("glpi_plugin_vip_tickets"  => "id")
					 );
	else
		return array();
}

function plugin_vip_getAddSearchOptions($itemtype) {
   	global $LANG;

   	$sopt = array();
   	if ($itemtype == 'Ticket') {
		 //Reserved Range 10100-10119
		 $rng1 = 10100;
		
         $sopt[$rng1]['table']     = 'glpi_plugin_vip_tickets';
         $sopt[$rng1]['field']     = 'isvip';
         $sopt[$rng1]['linkfield'] = 'id';
         $sopt[$rng1]['name']      = 'Vip';
		 $sopt[$rng1]['datatype']  = 'bool';

   	}   
   	return $sopt;
}

function plugin_vip_giveItem($type, $ID, $data, $num) {
	global $CFG_GLPI, $DB;
	
	$searchopt = &Search::getOptions($type);
	$table     = $searchopt[$ID]["table"];
	$field     = $searchopt[$ID]["field"];
	
	$ticketid  = $data['ITEM_0'];
	$vipdisplay = " ";
	
	$userquery  = "SELECT users_id
					 FROM glpi_tickets_users
					WHERE type = 1
					  AND tickets_id = ".$ticketid;
	$userresult	= $DB->query($userquery);

	$vipimg = "<img src=\"".$CFG_GLPI['root_doc']."/plugins/vip/pics/vip.png\" alt='vip' >";

	while ($uids = mysqli_fetch_object($userresult)) {

		foreach ($uids as $uid) { 
			$vipquery = "SELECT count(*) AS nb
						   FROM glpi_groups_users
						   JOIN glpi_plugin_vip_groups
							 ON glpi_plugin_vip_groups.id = glpi_groups_users.groups_id
						  WHERE glpi_plugin_vip_groups.isvip = 1
							AND glpi_groups_users.users_id = ".$uid;

			$vipresult = $DB->query($vipquery);
			$vip = mysqli_fetch_object($vipresult)->nb;

	
			if ($vip) {
				$vipdisplay =  $vipimg;
			}
		}
   	}
   	return $vipdisplay;
}

?>
