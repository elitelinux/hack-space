<?php

function plugin_version_vip() {

   	return array('name'           => "VIP",
                 'version'        => '1.0.1',
                 'author'         => 'Probesys',
                 'license'        => 'GPLv2+',
                 'homepage'       => 'http://www.probesys.com',
                 'minGlpiVersion' => '0.84');// For compatibility / no install in version < 0.84
}

function plugin_vip_check_prerequisites() {

   	if (version_compare(GLPI_VERSION,'0.84','lt')) {
      	echo "This plugin requires GLPI >= 0.84";
    	return false;
   	}
   	return true;
}

function plugin_vip_check_config($verbose=false) {

   	if ($verbose) {
     	echo 'Installed / not configured';
   	}
   	return true;
}

function plugin_init_vip() {

   	global $PLUGIN_HOOKS;

	$PLUGIN_HOOKS['csrf_compliant']['vip'] = true;

	Plugin::registerClass('PluginVipProfile', array('addtabon' => array('Profile')));
	$PLUGIN_HOOKS['change_profile']['vip'] = array('PluginVipProfile','changeProfile');

	Plugin::registerClass('PluginVipGroup', array('addtabon' => array('Group')));
	$PLUGIN_HOOKS['change_group']['vip'] = array('PluginVipGroup','changeGroup');

	Plugin::registerClass('PluginVipTicket');
	$PLUGIN_HOOKS['item_add']['vip'] 	= array('Ticket' 	  => array('PluginVipTicket',
																  	   'plugin_vip_item_add'));
	$PLUGIN_HOOKS['item_add']['vip']    = array('Ticket_User' => array('PluginVipTicket',
                                                                       'plugin_vip_item_update_user'));
	$PLUGIN_HOOKS['item_update']['vip'] = array('Ticket' 	  => array('PluginVipTicket',
																  	   'plugin_vip_item_update'));
	$PLUGIN_HOOKS['item_delete']['vip'] = array('Ticket' 	  => array('PluginVipTicket',
																  	   'plugin_vip_item_delete'));
	$PLUGIN_HOOKS['item_purge']['vip']	= array('Ticket_User' => array('PluginVipTicket',
                                                                       'plugin_vip_item_update_user'));

}

?>
