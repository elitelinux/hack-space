<?php

function plugin_init_notification() {
  
   global $PLUGIN_HOOKS, $LANG ;
   
   //$menuentry = 'front/config.php'; 
       
   $PLUGIN_HOOKS['csrf_compliant']['notification'] = true;
   //$PLUGIN_HOOKS['menu_entry']['notification']     = $menuentry;
      
   $PLUGIN_HOOKS['config_page']['notification'] = 'front/config.php';
                
}


function plugin_version_notification(){
	global $DB, $LANG;

	return array('name'			=> _n('Notification','Notifications',2),
					'version' 			=> '0.0.1',
					'author'			   => '<a href="mailto:stevenesdonato@gmail.com"> Stevenes Donato </b> </a>',
					'license'		 	=> 'GPLv2+',
					'homepage'			=> 'https://sourceforge.net/projects/glpinotification/',
					'minGlpiVersion'	=> '0.82');
}

function plugin_notification_check_prerequisites(){
        if (GLPI_VERSION>=0.82){
                return true;
        } else {
                echo "GLPI version not compatible need 0.83";
        }
}


function plugin_notification_check_config($verbose=false){
	if ($verbose) {
		echo 'Installed / not configured';
	}
	return true;
}


?>
