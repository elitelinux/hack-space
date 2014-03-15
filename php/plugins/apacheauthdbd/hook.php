<?php 

function plugin_apacheauthdbd_install() {
	global $DB;


	// Création de la table uniquement lors de la première installation
	if (!TableExists("glpi_plugin_apacheauthdbd_users")) {

		// requete de création de la table    
		$query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_apacheauthdbd_users` (
          `users_id` int(11) NOT NULL,
          `auth` tinyint(1) DEFAULT NULL,
          `password` varchar(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;" ; 

		$DB->query($query) or die("Erreur lors de la création de la table <strong>users</strong> pour ApacheAuthDBD Dixinfor : ". $DB->error());
	}
    $query_select_users = "SELECT `id`, `password` FROM `glpi_users`" ;
    
    foreach ($DB->request($query_select_users) as $users) {  
        $DB->query(
            "INSERT INTO `glpi_plugin_apacheauthdbd_users`
                (`users_id`, `password`) 
                VALUES 
                ('".$users["id"]."'
                ,'{SHA}".base64_encode(pack("H*", $users["password"]))."'
                )");
    } 

    return true;
}

function plugin_apacheauthdbd_uninstall() {
    global $DB;

    $tables = array("glpi_plugin_apacheauthdbd_users");

    foreach($tables as $table) {
        $DB->query("DROP TABLE IF EXISTS `$table`;");
    }

    return true;
}


?>