<?php

function plugin_init_dashboard() {
  
   global $PLUGIN_HOOKS, $LANG ;
   
   $menuentry = 'front/index.php'; 
       
   $PLUGIN_HOOKS['csrf_compliant']['dashboard'] = true;
   $PLUGIN_HOOKS['menu_entry']['dashboard']     = $menuentry;
   //$PLUGIN_HOOKS['helpdesk_menu_entry']['dashboard']     = $menuentry;
   
   $PLUGIN_HOOKS['config_page']['dashboard'] = 'front/index.php';
                
}


function plugin_version_dashboard(){
	global $DB, $LANG;

	return array('name'			=> __('Dashboard','dashboard'),
					'version' 			=> '0.3.7',
					'author'			   => '<a href="mailto:stevenesdonato@gmail.com"> Stevenes Donato </b> </a>',
					'license'		 	=> 'GPLv2+',
					'homepage'			=> 'https://sourceforge.net/projects/glpidashboard/',
					'minGlpiVersion'	=> '0.84');
}

function plugin_dashboard_check_prerequisites(){
        if (GLPI_VERSION>=0.84){
                return true;
        } else {
                echo "GLPI version not compatible need 0.83";
        }
}


function plugin_dashboard_check_config($verbose=false){
	if ($verbose) {
		echo 'Installed / not configured';
	}
	return true;
}


?>
