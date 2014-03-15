<?php

function plugin_notification_install(){
	
	global $DB, $LANG;
	
    if (! TableExists("glpi_plugin_notification_count")) {
        $query = "CREATE TABLE `glpi_plugin_notification_count` (`users_id` INTEGER NOT NULL,
        `quant` INTEGER, 
        PRIMARY KEY (`users_id`))
		  ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
        $DB->query($query) or die("error creating glpi_plugin_notification_count " . $DB->error());
        
        $insert = "INSERT INTO glpi_plugin_notification_count (users_id, quant) VALUES ('1','1')";
        $DB->query($insert);
    } 	
  	
	return true;
}

function plugin_notification_uninstall(){

	global $DB;
	
$drop = "DROP TABLE glpi_plugin_notification_count";
$DB->query($drop); 	

	
	return true;

}

?>
