<?php

function plugin_init_formcreator() {
   global $PLUGIN_HOOKS,$CFG_GLPI;
   
   $PLUGIN_HOOKS['csrf_compliant']['formcreator'] = true;
   
   Plugin::registerClass('PluginFormcreatorForm');
    
   if (Session::haveRight("config", "w")) {
      $PLUGIN_HOOKS['config_page']['formcreator'] = 'front/form.php';
      $PLUGIN_HOOKS['submenu_entry']['formcreator']['options']['form']['links']['add'] 
                                                   = '/plugins/formcreator/front/form.form.php';
      $PLUGIN_HOOKS['submenu_entry']['formcreator']['options']['form']['links']['config']
                                                   = '/plugins/formcreator/front/form.php';
   }
   
   $PLUGIN_HOOKS['menu_entry']['formcreator'] = 'front/formlist.php';
   $PLUGIN_HOOKS["helpdesk_menu_entry"]['formcreator'] = '/front/formlist.php';

   $PLUGIN_HOOKS['add_javascript']['formcreator'] = 'js/script.php';
   $PLUGIN_HOOKS['add_css']['formcreator']        = 'style.css';
   
}

function plugin_version_formcreator() {

   return array('name'           => 'Form Creator',
                'version'        => '1.8.1',
                'author'         => 'Goneri Le Bouder, Nicolas Manceau, Dimitri Mouillard',
                'homepage'       => 'https://forge.indepnet.net/projects/formcreator',
                'minGlpiVersion' => '0.84',
                'license'        => 'GPLv2');
}

function plugin_formcreator_check_prerequisites() {

   if (GLPI_VERSION >= 0.84) {
      return true;
   } else {
      echo "GLPI version not compatible, need 0.84";
   }
}

function plugin_formcreator_check_config($verbose=false) {

   if (true) { // Your configuration check
      return true;
   }
   if ($verbose) {
      echo __('Installed / not configured');
   }
   return false;
}

?>