<?php

function plugin_dashboard_install(){
	
	global $DB, $LANG;
	
    if (! TableExists("glpi_plugin_dashboard_count")) {
        $query = "CREATE TABLE `glpi_plugin_dashboard_count` 
        (`type` INTEGER , `id` INTEGER, `quant` INTEGER, PRIMARY KEY (`id`))
						ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";
						
        $DB->query($query) or die("error creating glpi_plugin_dashboard_count " . $DB->error());
        
        $insert = "INSERT INTO glpi_plugin_dashboard_count (type,quant) VALUES ('1','1')";
        $DB->query($insert);
    } 	
    
else {

		//remove old table
		$drop = "DROP TABLE glpi_plugin_dashboard_count";
		$DB->query($drop); 

       $query = "CREATE TABLE `glpi_plugin_dashboard_count` 
        (`type` INTEGER , `id` INTEGER, `quant` INTEGER, PRIMARY KEY (`id`))
						ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";
						
        $DB->query($query) or die("error creating glpi_plugin_dashboard_count " . $DB->error());
        
        $insert = "INSERT INTO glpi_plugin_dashboard_count (type,quant) VALUES ('1','1')";
        $DB->query($insert);

}
	
	return true;
}

function plugin_dashboard_uninstall(){

	global $DB;
	
$drop = "DROP TABLE glpi_plugin_dashboard_count";
$DB->query($drop); 	
	
	return true;

}

?>
