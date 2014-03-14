<?php

function plugin_version_minixmpp() {

   return array('name'           => "XMPP Chat",
                'version'        => '1.0.0',
                'author'         => 'Adrien Beudin',
                'license'        => 'GPLv2+',
                'homepage'       => 'https://forge.indepnet.net/repositories/show/minixmpp',
                'minGlpiVersion' => '0.84');// For compatibility / no install in version < 0.80

}

function plugin_minixmpp_check_prerequisites() {

   if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','gt')) {
      echo "This plugin requires GLPI >= 0.84 and GLPI < 0.85";
      return false;
   }
   return true;
}

function plugin_minixmpp_check_config($verbose=false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
     echo 'Installed / not configured';
   }
   return false;
}

function plugin_init_minixmpp() {
   global $PLUGIN_HOOKS;

   $plugin = new Plugin();
   if ($plugin->isActivated("minixmpp")) {
      $PluginMinixmppConfig = new PluginMinixmppConfig();
      $PluginMinixmppConfig->init();
   }

   $PLUGIN_HOOKS['csrf_compliant']['minixmpp'] = true;

   if (Session::haveRight("config", "w")) {
      $PLUGIN_HOOKS['config_page']['minixmpp'] = 'front/config.form.php';
   }
 
   $PLUGIN_HOOKS['add_javascript']['minixmpp'] = array('js/jquery.min.js', 'js/minijappix.js');
}


?>
