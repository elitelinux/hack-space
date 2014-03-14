<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
-------------------------------------------------------------------------
Accounts plugin for GLPI
Copyright (C) 2003-2011 by the accounts Development Team.

https://forge.indepnet.net/projects/accounts
-------------------------------------------------------------------------

LICENSE

This file is part of accounts.

accounts is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

accounts is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with accounts. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
*/

// Init the hooks of the plugins -Needed
function plugin_init_accounts() {
   global $PLUGIN_HOOKS, $CFG_GLPI;
    
   $PLUGIN_HOOKS['csrf_compliant']['accounts'] = true;
   $PLUGIN_HOOKS['change_profile']['accounts'] = array('PluginAccountsProfile','changeProfile');
   $PLUGIN_HOOKS['assign_to_ticket']['accounts'] = true;
    
   if (Session::getLoginUserID()) {

      // Params : plugin name - string type - number - attributes
      Plugin::registerClass('PluginAccountsAccount', array(
      'linkgroup_types' => true,
      'linkuser_types' => true,
      'linkgroup_tech_types' => true,
      'linkuser_tech_types' => true,
      'document_types' => true,
      'ticket_types'         => true,
      'helpdesk_visible_types' => true,
      'notificationtemplates_types' => true,
      'header_types' => true,
      ));

      Plugin::registerClass('PluginAccountsConfig',
      array('addtabon' => 'CronTask'));
       
      Plugin::registerClass('PluginAccountsProfile',
      array('addtabon' => 'Profile'));

      if (isset($_SESSION["glpi_plugin_environment_installed"])
               && $_SESSION["glpi_plugin_environment_installed"]==1) {

         $_SESSION["glpi_plugin_environment_accounts"] = 1;

         if (plugin_accounts_haveRight("accounts","r")) {
            $PLUGIN_HOOKS['helpdesk_menu_entry']['accounts'] = '/front/account.php';
            $PLUGIN_HOOKS['submenu_entry']['environment']['options']['accounts']['title'] = PluginAccountsAccount::getTypeName(2);
            $PLUGIN_HOOKS['submenu_entry']['environment']['options']['accounts']['page'] = '/plugins/accounts/front/account.php';
            $PLUGIN_HOOKS['submenu_entry']['environment']['options']['accounts']['links']['search'] = '/plugins/accounts/front/account.php';
            $PLUGIN_HOOKS['redirect_page']['accounts'] = "front/account.form.php";
         }

         if (plugin_accounts_haveRight("accounts","w")) {
            $PLUGIN_HOOKS['submenu_entry']['environment']['options']['accounts']['links']['add'] = '/plugins/accounts/front/account.form.php';
            $PLUGIN_HOOKS['use_massive_action']['accounts'] = 1;
            if (Session::haveRight("config","w") && plugin_accounts_haveRight("accounts","w")) {
               //TODO check it
               $PLUGIN_HOOKS['submenu_entry']['environment']['options']['accounts']['links']["<img src='".
                        $CFG_GLPI["root_doc"]."/plugins/accounts/pics/cadenas.png' title='".
                        _n('Encryption key', 'Encryption keys', 2)."' alt='"._n('Encryption key', 'Encryption keys', 2, 'accounts')."'>"] = '/plugins/accounts/front/hash.php';
               $PLUGIN_HOOKS['submenu_entry']['environment']['options']['hash']['page'] = '/plugins/accounts/front/hash.php';
               $PLUGIN_HOOKS['submenu_entry']['environment']['options']['hash']['title'] = _n('Encryption key', 'Encryption keys', 2, 'accounts');
               $PLUGIN_HOOKS['submenu_entry']['environment']['options']['hash']['links']['add'] = '/plugins/accounts/front/hash.form.php';
               $PLUGIN_HOOKS['submenu_entry']['environment']['options']['hash']['links']['search'] = '/plugins/accounts/front/hash.php';
            }
         }
      } else {

         // Display a menu entry ?
         if (plugin_accounts_haveRight("accounts","r")) {
            $PLUGIN_HOOKS['menu_entry']['accounts'] = 'front/account.php';
            $PLUGIN_HOOKS['helpdesk_menu_entry']['accounts'] = '/front/account.php';
            $PLUGIN_HOOKS['submenu_entry']['accounts']['search'] = 'front/account.php';
            $PLUGIN_HOOKS['redirect_page']['accounts'] = "front/account.form.php";
         }

         if (plugin_accounts_haveRight("accounts","w")) {
            $PLUGIN_HOOKS['submenu_entry']['accounts']['add'] = 'front/account.form.php';
            $PLUGIN_HOOKS['header_entry']['accounts'] = array(__('New account') =>'/plugins/accounts/front/account.form.php');
            $PLUGIN_HOOKS['header_action']['accounts'] = 'plugin_accounts_header_action';
            $PLUGIN_HOOKS['use_massive_action']['accounts'] = 1;
            if (Session::haveRight("config","w") && plugin_accounts_haveRight("accounts","w")) {
               $PLUGIN_HOOKS['submenu_entry']['accounts']["<img src='".
                        $CFG_GLPI["root_doc"]."/plugins/accounts/pics/cadenas.png' title='".
                        _n('Encryption key', 'Encryption keys', 2)."' alt='"._n('Encryption key', 'Encryption keys', 2, 'accounts')."'>"] = 'front/hash.php';
               $PLUGIN_HOOKS['submenu_entry']['accounts']['options']['hash']['page'] = '/plugins/accounts/front/hash.php';
               $PLUGIN_HOOKS['submenu_entry']['accounts']['options']['hash']['title'] = _n('Encryption key', 'Encryption keys', 2, 'accounts');
               $PLUGIN_HOOKS['submenu_entry']['accounts']['options']['hash']['links']['add'] = '/plugins/accounts/front/hash.form.php';
               $PLUGIN_HOOKS['submenu_entry']['accounts']['options']['hash']['links']['search'] = '/plugins/accounts/front/hash.php';
            }
         }

      }

      //Clean Plugin on Profile delete
      if (class_exists('PluginAccountsAccount_Item')) { // only if plugin activated
         $PLUGIN_HOOKS['pre_item_purge']['accounts']
         = array('Profile'=>array('PluginAccountsProfile', 'purgeProfiles'));
         $PLUGIN_HOOKS['plugin_datainjection_populate']['accounts']
         = 'plugin_datainjection_populate_accounts';
      }
       
      // Add specific files to add to the header : javascript or css
      //$PLUGIN_HOOKS['add_javascript']['example']="example.js";
      $PLUGIN_HOOKS['add_javascript']['accounts']="lightcrypt.js";

      $PLUGIN_HOOKS['migratetypes']['accounts'] = 'plugin_datainjection_migratetypes_accounts';

      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['accounts'] = 'plugin_accounts_postinit';

   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_accounts() {

   return array (
            'name' => _n('Account', 'Accounts', 2, 'accounts'),
            'version' => '1.9.1',
            'oldname' => 'compte',
            'license' => 'GPLv2+',
            'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>, Franck Waechter",
            'homepage'=>'https://forge.indepnet.net/projects/show/accounts',
            'minGlpiVersion' => '0.84',// For compatibility / no install in version < 0.80
   );

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_accounts_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      _e('This plugin requires GLPI >= 0.84', 'accounts');
      return false;
   } else {
      if (TableExists("glpi_comptes")) {//1.0
         if (countElementsInTable("glpi_comptes")>0 && function_exists("mcrypt_encrypt")) {
            return true;
         } else {
            _e('phpX-mcrypt must be installed', 'accounts');
         }
      } else if (TableExists("glpi_plugin_comptes")) {//1.1
         if (countElementsInTable("glpi_plugin_comptes")>0 && function_exists("mcrypt_encrypt")) {
            return true;
         } else {
            _e('phpX-mcrypt must be installed', 'accounts');
         }
      } else if (!TableExists("glpi_plugin_compte_mailing")
               && TableExists("glpi_plugin_comptes")) {//1.3
         if (countElementsInTable("glpi_plugin_comptes")>0 && function_exists("mcrypt_encrypt")) {
            return true;
         } else {
            _e('phpX-mcrypt must be installed', 'accounts');
         }
      } else if (TableExists("glpi_plugin_compte")
               && FieldExists("glpi_plugin_compte_profiles","interface")) {//1.4
         if (countElementsInTable("glpi_plugin_compte")>0 && function_exists("mcrypt_encrypt")) {
            return true;
         } else {
            _e('phpX-mcrypt must be installed', 'accounts');
         }
      } else {
         return true;
      }
   }
}

// Uninstall process for plugin : need to return true if succeeded
//may display messages or add to message after redirect
function plugin_accounts_check_config() {
   return true;
}

function plugin_accounts_haveRight($module,$right) {
   $matches=array(
            ""  => array("","r","w"), // ne doit pas arriver normalement
            "r" => array("r","w"),
            "w" => array("w"),
            "1" => array("1"),
            "0" => array("0","1"), // ne doit pas arriver non plus
   );
   if (isset($_SESSION["glpi_plugin_accounts_profile"][$module])
            &&in_array($_SESSION["glpi_plugin_accounts_profile"][$module],$matches[$right]))
      return true;
   else return false;
}

function plugin_datainjection_migratetypes_accounts($types) {
   $types[1900] = 'PluginAccountsAccount';
   return $types;
}

?>