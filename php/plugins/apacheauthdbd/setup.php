<?php
function plugin_version_apacheauthdbd() {

   return array('name'           => 'ApacheAuthDBD',
                'version'        => '0.84+1.0',
                'author'         => 'Dixinfor',
              //  'license'        => 'GPLv2+',
                'homepage'       => 'http://www.dixinfor.com/',// OR 'https://forge.indepnet.net/repositories/show/dixinfor' if open-source,
                'minGlpiVersion' => '0.84');// For compatibility / no install in version < 0.80

}
function plugin_apacheauthdbd_check_prerequisites() {

   if (version_compare(GLPI_VERSION,'0.83','lt') || version_compare(GLPI_VERSION,'0.85','gt')) {
      echo "This plugin requires GLPI >= 0.83 and GLPI < 0.85";
      return false;
   }
   return true;
}


function plugin_apacheauthdbd_check_config() {
      return true;
}

function plugin_init_apacheauthdbd() {
   global $PLUGIN_HOOKS, $CFG_GLPI;
    $Plugin = new Plugin();
	$PLUGIN_HOOKS['item_add']['apacheauthdbd'] = $PLUGIN_HOOKS['item_restore']['apacheauthdbd'] = array('User' => 'plugin_item_add_apacheauthdbd_user');
	$PLUGIN_HOOKS['item_update']['apacheauthdbd'] = array('User' => 'plugin_item_update_apacheauthdbd_user');
	$PLUGIN_HOOKS['item_delete']['apacheauthdbd'] = $PLUGIN_HOOKS['item_purge']['apacheauthdbd'] = array('User' => 'plugin_item_delete_apacheauthdbd_user');
    $PLUGIN_HOOKS['csrf_compliant']['apacheauthdbd'] = true;
    Plugin::registerClass('PluginApacheauthdbdUser', array('addtabon' => array('User')));

}

function plugin_item_add_apacheauthdbd_user($parm) {
    global $DB ; 
    $DB->query(
    "INSERT INTO `glpi_plugin_apacheauthdbd_users`
        (`users_id`, `password`) 
        VALUES 
        ('".$parm->getField("id")."'
        ,'{SHA}".base64_encode(pack("H*", $parm->getField("password")))."'
        )");
    return true ; 
}

function plugin_item_update_apacheauthdbd_user($parm) {
    global $DB ; 
    $DB->query(
        "UPDATE `glpi_plugin_apacheauthdbd_users`
            SET `password` = '{SHA}".base64_encode(pack("H*", $parm->getField("password")))."'
            WHERE `users_id` = '".$parm->getField("id")."'") ; 
    return true ; 
}

function plugin_item_delete_apacheauthdbd_user($parm) {
    global $DB ; 
    $DB->query(
        "DELETE FROM `glpi_plugin_apacheauthdbd_users`
            WHERE `users_id` = '".$parm->getField("id")."'") ; 
    return true ; 
}

 ?>