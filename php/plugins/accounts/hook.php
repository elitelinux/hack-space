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

function plugin_accounts_install() {
   global $DB,$CFG_GLPI;
    
   include_once (GLPI_ROOT."/plugins/accounts/inc/profile.class.php");
    
   $install=false;
   $update78=false;
   $update80=false;
   $update171=false;
   if (!TableExists("glpi_plugin_compte")
            && !TableExists("glpi_plugin_comptes")
            && !TableExists("glpi_comptes")
            && !TableExists("glpi_plugin_accounts_accounts")) {

      $install=true;
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/empty-1.9.0.sql");


   } else if (TableExists("glpi_comptes")
            && !FieldExists("glpi_comptes","notes")) {

      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.3.sql");
      plugin_accounts_updatev14();
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.3.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.1.sql");
      $_SESSION['plugin_acounts_upgrading'] = 1;

   } else if (TableExists("glpi_plugin_comptes")
            && !FieldExists("glpi_plugin_comptes","all_users")) {

      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.3.sql");
      plugin_accounts_updatev14();
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.3.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.1.sql");
      $_SESSION['plugin_acounts_upgrading'] = 1;

   } else if (TableExists("glpi_plugin_compte_profiles")
            && !FieldExists("glpi_plugin_compte_profiles","my_groups")) {

      $update78=true;
      $update80=true;
      plugin_accounts_updatev14();
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.3.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.1.sql");
      $_SESSION['plugin_acounts_upgrading'] = 1;

   } else if (TableExists("glpi_plugin_compte_profiles")
            && FieldExists("glpi_plugin_compte_profiles","interface")) {

      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.3.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.1.sql");
      $_SESSION['plugin_acounts_upgrading'] = 1;

   } else if (TableExists("glpi_plugin_compte")
            && !FieldExists("glpi_plugin_compte","date_mod")) {

      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.3.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.1.sql");

   } else if (TableExists("glpi_plugin_compte")
            && !TableExists("glpi_plugin_compte_aeskey")) {

      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.5.3.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.1.sql");

   } else if (TableExists("glpi_plugin_compte")
            && !TableExists("glpi_plugin_accounts_accounts")) {

      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.1.sql");

   } else if (TableExists("glpi_plugin_accounts_accounts")
            && !FieldExists("glpi_plugin_accounts_accounts","locations_id")) {
       
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.1.sql");

   } else if (TableExists("glpi_plugin_accounts_hashes")
            && !FieldExists("glpi_plugin_accounts_hashes","entities_id")) {
       
      $update171=true;
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.7.1.sql");

   }

   //from 1.6 version
   if (TableExists("glpi_plugin_accounts_accounts")
            && !FieldExists("glpi_plugin_accounts_accounts","users_id_tech")) {
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.8.0.sql");
   }
   
   //from 1.9 version
   if (TableExists("glpi_plugin_accounts_accounttypes")
            && !FieldExists("glpi_plugin_accounts_accounttypes","is_recursive")) {
      $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.9.0.sql");
   }
    
   if ($install || $update78) {

      //Do One time on 0.78
      $query_id = "SELECT `id` FROM `glpi_notificationtemplates`
               WHERE `itemtype`='PluginAccountsAccount'
               AND `name` = 'New Accounts'";
      $result = $DB->query($query_id) or die ($DB->error());
      $itemtype = $DB->result($result,0,'id');

      $query="INSERT INTO `glpi_notificationtemplatetranslations`
               VALUES(NULL, ".$itemtype.", '','##lang.account.title##',
                        '##lang.account.url## : ##account.url##\r\n\r\n
                        ##lang.account.entity## : ##account.entity##\r\n
                        ##IFaccount.name####lang.account.name## : ##account.name##\r\n##ENDIFaccount.name##
                        ##IFaccount.type####lang.account.type## : ##account.type##\r\n##ENDIFaccount.type##
                        ##IFaccount.state####lang.account.state## : ##account.state##\r\n##ENDIFaccount.state##
                        ##IFaccount.login####lang.account.login## : ##account.login##\r\n##ENDIFaccount.login##
                        ##IFaccount.users_id####lang.account.users_id## : ##account.users_id##\r\n##ENDIFaccount.users_id##
                        ##IFaccount.groups_id####lang.account.groups_id## : ##account.groups_id##\r\n##ENDIFaccount.groups_id##
                        ##IFaccount.others####lang.account.others## : ##account.others##\r\n##ENDIFaccount.others##
                        ##IFaccount.datecreation####lang.account.datecreation## : ##account.datecreation##\r\n##ENDIFaccount.datecreation##
                        ##IFaccount.dateexpiration####lang.account.dateexpiration## : ##account.dateexpiration##\r\n##ENDIFaccount.dateexpiration##
                        ##IFaccount.comment####lang.account.comment## : ##account.comment##\r\n##ENDIFaccount.comment##',
                        '&lt;p&gt;&lt;strong&gt;##lang.account.url##&lt;/strong&gt; : &lt;a href=\"##account.url##\"&gt;##account.url##&lt;/a&gt;&lt;/p&gt;
                        &lt;p&gt;&lt;strong&gt;##lang.account.entity##&lt;/strong&gt; : ##account.entity##&lt;br /&gt; ##IFaccount.name##&lt;strong&gt;##lang.account.name##&lt;/strong&gt; : ##account.name##&lt;br /&gt;##ENDIFaccount.name##  ##IFaccount.type##&lt;strong&gt;##lang.account.type##&lt;/strong&gt; : ##account.type##&lt;br /&gt;##ENDIFaccount.type##  ##IFaccount.state##&lt;strong&gt;##lang.account.state##&lt;/strong&gt; : ##account.state##&lt;br /&gt;##ENDIFaccount.state##  ##IFaccount.login##&lt;strong&gt;##lang.account.login##&lt;/strong&gt; : ##account.login##&lt;br /&gt;##ENDIFaccount.login##  ##IFaccount.users##&lt;strong&gt;##lang.account.users##&lt;/strong&gt; : ##account.users##&lt;br /&gt;##ENDIFaccount.users##  ##IFaccount.groups##&lt;strong&gt;##lang.account.groups##&lt;/strong&gt; : ##account.groups##&lt;br /&gt;##ENDIFaccount.groups##  ##IFaccount.others##&lt;strong&gt;##lang.account.others##&lt;/strong&gt; : ##account.others##&lt;br /&gt;##ENDIFaccount.others##  ##IFaccount.datecreation##&lt;strong&gt;##lang.account.datecreation##&lt;/strong&gt; : ##account.datecreation##&lt;br /&gt;##ENDIFaccount.datecreation##  ##IFaccount.dateexpiration##&lt;strong&gt;##lang.account.dateexpiration##&lt;/strong&gt; : ##account.dateexpiration##&lt;br /&gt;##ENDIFaccount.dateexpiration##  ##IFaccount.comment##&lt;strong&gt;##lang.account.comment##&lt;/strong&gt; : ##account.comment####ENDIFaccount.comment##&lt;/p&gt;');";
      $result=$DB->query($query);

      $query = "INSERT INTO `glpi_notifications`
               VALUES (NULL, 'New Accounts', 0, 'PluginAccountsAccount', 'new',
               'mail',".$itemtype.",
                        '', 1, 1, '2010-02-17 22:36:46');";
      $result=$DB->query($query);

      $query_id = "SELECT `id` FROM `glpi_notificationtemplates`
               WHERE `itemtype`='PluginAccountsAccount'
               AND `name` = 'Alert Accounts'";
      $result = $DB->query($query_id) or die ($DB->error());
      $itemtype = $DB->result($result,0,'id');

      $query="INSERT INTO `glpi_notificationtemplatetranslations`
               VALUES(NULL, ".$itemtype.", '','##account.action## : ##account.entity##',
                        '##lang.account.entity## :##account.entity##
                        ##FOREACHaccounts##
                        ##lang.account.name## : ##account.name## - ##lang.account.dateexpiration## : ##account.dateexpiration##
                        ##ENDFOREACHaccounts##',
                        '&lt;p&gt;##lang.account.entity## :##account.entity##&lt;br /&gt; &lt;br /&gt;
                        ##FOREACHaccounts##&lt;br /&gt;
                        ##lang.account.name##  : ##account.name## - ##lang.account.dateexpiration## :  ##account.dateexpiration##&lt;br /&gt;
                        ##ENDFOREACHaccounts##&lt;/p&gt;');";
      $result=$DB->query($query);

      $query = "INSERT INTO `glpi_notifications`
               VALUES (NULL, 'Alert Expired Accounts', 0, 'PluginAccountsAccount', 'ExpiredAccounts',
               'mail',".$itemtype.",
                        '', 1, 1, '2010-02-17 22:36:46');";
      $result=$DB->query($query);
       
      $query = "INSERT INTO `glpi_notifications`
               VALUES (NULL, 'Alert Accounts Which Expire', 0, 'PluginAccountsAccount', 'AccountsWhichExpire',
               'mail',".$itemtype.",
                        '', 1, 1, '2010-02-17 22:36:46');";

      $result=$DB->query($query);
   }
   if ($update78) {
      //Do One time on 0.78
      $query_="SELECT *
               FROM `glpi_plugin_accounts_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_accounts_profiles`
                     SET `profiles_id` = '".$data["id"]."'
                              WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }

      $query="ALTER TABLE `glpi_plugin_accounts_profiles`
               DROP `name` ;";
      $result=$DB->query($query);

      Plugin::migrateItemType(
      array(1900=>'PluginAccountsAccount',
      1901=>'PluginAccountsHelpdesk',
      1902=>'PluginAccountsGroup'),
      array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
      "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
      array("glpi_plugin_accounts_accounts_items"));

      Plugin::migrateItemType(
      array(1200 => "PluginAppliancesAppliance",
      1300 => "PluginWebapplicationsWebapplication",
      1700 => "PluginCertificatesCertificate",
      4400 => "PluginDomainsDomain",
      2400 => "PluginDatabasesDatabase"),
      array("glpi_plugin_accounts_accounts_items"));

   }

   if ($update171) {

      $query="UPDATE `glpi_plugin_accounts_hashes`
               SET `is_recursive` = '1'
               WHERE `id` = '1';";
      $result=$DB->query($query);

      $query="UPDATE `glpi_plugin_accounts_aeskeys`
               SET `plugin_accounts_hashes_id` = '1'
               WHERE `id` = '1';";
      $result=$DB->query($query);

   }

   if (isset($_SESSION['plugin_acounts_upgrading'])) {
      $msg = __('After plugin installation, you must do upgrade of your passwords from here : ', 'accounts');
      $msg .= "<a href='".$CFG_GLPI["root_doc"]."/plugins/accounts/front/upgrade.form.php'>".__('Upgrading page', 'accounts')."</a>";
      Session::addMessageAfterRedirect($msg,ERROR);
   }

   CronTask::Register('PluginAccountsAccount', 'AccountsAlert', DAY_TIMESTAMP);
   PluginAccountsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_accounts_updatev14() {
   global $DB;

   $DB->runFile(GLPI_ROOT ."/plugins/accounts/sql/update-1.4.sql");

   // crypt passwords
   $query_="SELECT *
            FROM `glpi_plugin_compte` ";
   $result_=$DB->query($query_);
   if ($DB->numrows($result_)>0) {

      while ($data=$DB->fetch_array($result_)) {
         $password=plugin_accounts_crypte($data["mdp"],1);
         $query="UPDATE `glpi_plugin_compte`
                  SET `mdp` = '".$password."'
                           WHERE `ID` = '".$data["ID"]."';";
         $result=$DB->query($query);

      }
   }
}

function plugin_accounts_crypte($Texte,$action) {
   //$algo = "blowfish"; // ou la constante php MCRYPT_BLOWFISH
   //$mode = "nofb"; // ou la constante php MCRYPT_MODE_NOFB
   $key_size = mcrypt_module_get_algo_key_size(MCRYPT_RIJNDAEL_256);
   //$iv_size = mcrypt_get_iv_size($algo, $mode);
   //$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

   $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
   $key = 'Ceci est une clé secrète';
   if ($action==1) {

      $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $Texte, MCRYPT_MODE_ECB, $iv);
      $crypttext   = base64_encode($crypttext);
   } else if ($action==2) {
      $crypttext = "<script language='javascript'>document.write(AESEncryptCtr($Texte,SHA256($key),256));</script>";
   } else if ($action==3) {
      $Texte   = base64_decode($Texte);
      $crypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $Texte, MCRYPT_MODE_ECB, $iv);
   }
  	return trim($crypttext);
}

function plugin_accounts_configure15() {
   global $DB;

   // uncrypt passwords for update
   $query_="SELECT *
            FROM `glpi_plugin_accounts_accounts` ";
   $result_=$DB->query($query_);
   if ($DB->numrows($result_)>0) {

      while ($data=$DB->fetch_array($result_)) {

         $password=addslashes(plugin_accounts_crypte($data['encrypted_password'],3));

         $PluginAccountsAccount=new PluginAccountsAccount();
         $PluginAccountsAccount->update(array(
                  'id'=>$data["id"],
                  'encrypted_password'=>$password));

      }
   }
}

function plugin_accounts_uninstall() {
   global $DB;

   $tables = array("glpi_plugin_accounts_accounts",
            "glpi_plugin_accounts_accounts_items",
            "glpi_plugin_accounts_accounttypes",
            "glpi_plugin_accounts_accountstates",
            "glpi_plugin_accounts_profiles",
            "glpi_plugin_accounts_configs",
            "glpi_plugin_accounts_hashs",
            "glpi_plugin_accounts_hashes",
            "glpi_plugin_accounts_aeskeys",
            "glpi_plugin_accounts_notificationstates");

   foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");
    
   //old versions
   $tables = array("glpi_plugin_comptes",
            "glpi_plugin_compte_device",
            "glpi_dropdown_plugin_compte_type",
            "glpi_dropdown_plugin_compte_status",
            "glpi_plugin_compte_profiles",
            "glpi_plugin_compte_config",
            "glpi_plugin_compte_default",
            "glpi_plugin_compte_mailing",
            "glpi_plugin_compte",
            "glpi_plugin_compte_hash",
            "glpi_plugin_compte_aeskey");

   foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");
    
   $notif = new Notification();
    
   $options = array('itemtype' => 'PluginAccountsAccount',
            'event'    => 'new',
            'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginAccountsAccount',
            'event'    => 'ExpiredAccounts',
            'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginAccountsAccount',
            'event'    => 'AccountsWhichExpire',
            'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
    
   //templates
   $template = new NotificationTemplate();
   $translation = new NotificationTemplateTranslation();
   $options = array('itemtype' => 'PluginAccountsAccount',
            'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
      $options_template = array('notificationtemplates_id' => $data['id'],
               'FIELDS'   => 'id');
       
      foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template)
               as $data_template) {
         $translation->delete($data_template);
      }
      $template->delete($data);
   }
    
   $tables_glpi = array("glpi_displaypreferences",
            "glpi_documents_items",
            "glpi_bookmarks",
            "glpi_logs",
            "glpi_tickets");

   foreach($tables_glpi as $table_glpi)
      $DB->query("DELETE FROM `$table_glpi`
               WHERE `itemtype` = 'PluginAccountsAccount'
               OR `itemtype` = 'PluginAccountsHelpdesk'
               OR `itemtype` = 'PluginAccountsGroup' ;");
    
   if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype'=>'PluginAccountsAccount'));
   }
    
   return true;
}

function plugin_accounts_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['accounts'] = array();

   foreach (PluginAccountsAccount::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['accounts'][$type]
      = array('PluginAccountsAccount_Item','cleanForItem');

      CommonGLPI::registerStandardTab($type, 'PluginAccountsAccount_Item');
   }
}

function plugin_accounts_AssignToTicket($types) {

   if (plugin_accounts_haveRight("open_ticket","1"))
      $types['PluginAccountsAccount']= PluginAccountsAccount::getTypeName(2);

   return $types;
}

// Define dropdown relations
function plugin_accounts_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("accounts"))
      return array (
               "glpi_plugin_accounts_accounttypes" => array (
                        "glpi_plugin_accounts_accounts" => "plugin_accounts_accounttypes_id"
               ),
               "glpi_plugin_accounts_accountstates" => array (
                        "glpi_plugin_accounts_accounts" => "plugin_accounts_accountstates_id",
                        "glpi_plugin_accounts_mailingstates" => "plugin_accounts_accountstates_id"
               ),
               "glpi_plugin_accounts_accounts" => array (
                        "glpi_plugin_accounts_accounts_items" => "plugin_accounts_accounts_id"
               ),
               "glpi_entities" => array (
                        "glpi_plugin_accounts_accounts" => "entities_id",
                        "glpi_plugin_accounts_accounttypes" => "entities_id"
               ),
               "glpi_users" => array (
                        "glpi_plugin_accounts_accounts" => "users_id",
                        "glpi_plugin_accounts_accounts" => "users_id_tech"
               ),
               "glpi_groups" => array (
                        "glpi_plugin_accounts_accounts" => "groups_id",
                        "glpi_plugin_accounts_accounts" => "groups_id_tech"
               ),
               "glpi_locations" => array (
                        "glpi_plugin_accounts_accounts" => "locations_id"
               ),
               "glpi_profiles" => array (
                        "glpi_plugin_accounts_profiles" => "profiles_id"
               )
      );
   else
      return array ();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_accounts_getDropdown() {

   $plugin = new Plugin();
   if ($plugin->isActivated("accounts"))
      return array (
               "PluginAccountsAccountType" => PluginAccountsAccountType::getTypeName(2),
               "PluginAccountsAccountState" => PluginAccountsAccountState::getTypeName(2)
      );
   else
      return array ();
}

function plugin_accounts_getAddSearchOptions($itemtype) {

   $sopt=array();

   if (in_array($itemtype, PluginAccountsAccount::getTypes(true))) {
      if (plugin_accounts_haveRight("accounts","r")) {
         $sopt[1900]['table']          = 'glpi_plugin_accounts_accounts';
         $sopt[1900]['field']          = 'name';
         $sopt[1900]['name']           = PluginAccountsAccount::getTypeName(2)." - ".__('Name');
         $sopt[1900]['forcegroupby']   = true;
         $sopt[1900]['datatype']       = 'itemlink';
         $sopt[1900]['massiveaction']  = false;
         $sopt[1900]['itemlink_type']  = 'PluginAccountsAccount';
         if ($itemtype != 'User') {
            $sopt[1900]['joinparams']  = array('beforejoin' => array('table'      => 'glpi_plugin_accounts_accounts_items',
                     'joinparams' => array('jointype' => 'itemtype_item')));
         }
         $sopt[1901]['table']          = 'glpi_plugin_accounts_accounttypes';
         $sopt[1901]['field']          = 'name';
         $sopt[1901]['name']           = PluginAccountsAccount::getTypeName(2)." - ".__('Type');
         $sopt[1901]['forcegroupby']   = true;
         $sopt[1901]['joinparams']     = array('beforejoin' => array( array('table'      => 'glpi_plugin_accounts_accounts',
                  'joinparams' => $sopt[1900]['joinparams'])));
         $sopt[1901]['datatype']       = 'dropdown';
         $sopt[1901]['massiveaction']  = false;
      }
   }
   return $sopt;
}

function plugin_accounts_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables) {

   switch ($ref_table) {

      case "glpi_users" : // From items
         $out= " LEFT JOIN `glpi_plugin_accounts_accounts`
                  ON (`glpi_plugin_accounts_accounts`.`users_id` = `glpi_users`.`id` ) ";
         return $out;
         break;
   }

   return "";
}

function plugin_accounts_addDefaultWhere($type) {

   // Example of default WHERE item to be added
   // No need of the function if you do not have specific cases
   switch ($type) {
      //       case "PluginExampleExample" :
      case "PluginAccountsAccount" :
         $who=Session::getLoginUserID();
         if (!plugin_accounts_haveRight("all_users","r")) {
            if (count($_SESSION["glpigroups"]) && plugin_accounts_haveRight("my_groups","r")) {
               $first_groups=true;
               $groups="";
               foreach ($_SESSION['glpigroups'] as $val) {
                  if (!$first_groups) $groups.=",";
                  else $first_groups=false;
                  $groups.="'".$val."'";
               }
               return " (`glpi_plugin_accounts_accounts`.`groups_id` IN (
               SELECT DISTINCT `groups_id`
               FROM `glpi_groups_users`
               WHERE `groups_id` IN ($groups)
               )
               OR `glpi_plugin_accounts_accounts`.`users_id` = '$who') ";

            } else { // Only personal ones
               return " `glpi_plugin_accounts_accounts`.`users_id` = '$who' ";
            }
         }
   }
   return "";
}

function plugin_accounts_forceGroupBy($type) {

   return true;
   switch ($type) {
      case 'PluginAccountsAccount' :
         return true;
         break;
      case 'PluginAccountsHelpdesk' :
         return true;
         break;

   }
   return false;
}

function plugin_accounts_displayConfigItem($type,$ID,$data,$num) {

   $searchopt=&Search::getOptions($type);
   $table=$searchopt[$ID]["table"];
   $field=$searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi_plugin_accounts_accounts.date_expiration" :
         if ($data["ITEM_$num"] <= date('Y-m-d') && !empty($data["ITEM_$num"]))
            return " class=\"deleted\" ";
         break;
   }
   return "";
}

function plugin_accounts_giveItem($type,$ID,$data,$num) {
   global $DB;

   $searchopt=&Search::getOptions($type);
   $table=$searchopt[$ID]["table"];
   $field=$searchopt[$ID]["field"];
    
   switch ($type) {
      case 'PluginAccountsAccount':
         switch ($table.'.'.$field) {

            case "glpi_plugin_accounts_accounts_items.items_id" :
               $query_device = "SELECT DISTINCT `itemtype`
                        FROM `glpi_plugin_accounts_accounts_items`
                        WHERE `plugin_accounts_accounts_id` = '" . $data['id'] . "'
                                 ORDER BY `itemtype`
                                 LIMIT ".count(PluginAccountsAccount::getTypes(true));
               $result_device = $DB->query($query_device);
               $number_device = $DB->numrows($result_device);
               $out = '';
               $accounts = $data['id'];
               if ($number_device > 0) {
                  for ($i=0 ; $i < $number_device ; $i++) {
                     $column = "name";
                     $itemtype = $DB->result($result_device, $i, "itemtype");
                      
                     if (!class_exists($itemtype)) {
                        continue;
                     }
                     $item = new $itemtype();
                     if ($item->canView()) {
                        $table_item = getTableForItemType($itemtype);
                        if ($itemtype!='Entity') {
                           $query = "SELECT `".$table_item."`.*,
                                    `glpi_plugin_accounts_accounts_items`.`id` AS items_id,
                                    `glpi_entities`.`id` AS entity "
                                    ." FROM `glpi_plugin_accounts_accounts_items`, `".$table_item
                                    ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table_item."`.`entities_id`) "
                                             ." WHERE `".$table_item."`.`id` = `glpi_plugin_accounts_accounts_items`.`items_id`
                                             AND `glpi_plugin_accounts_accounts_items`.`itemtype` = '$itemtype'
                                             AND `glpi_plugin_accounts_accounts_items`.`plugin_accounts_accounts_id` = '" . $accounts . "' ";
                           $query.=getEntitiesRestrictRequest(" AND ",$table_item,'','',$item->maybeRecursive());

                           if ($item->maybeTemplate()) {
                              $query.=" AND ".$table_item.".is_template='0'";
                           }
                           $query.=" ORDER BY `glpi_entities`.`completename`, `".$table_item."`.`$column` ";
                        } else {
                           $query = "SELECT `".$table_item."`.*,
                                    `glpi_plugin_accounts_accounts_items`.`id` AS items_id,
                                    `glpi_entities`.`id` AS entity "
                                    ." FROM `glpi_plugin_accounts_accounts_items`, `".$table_item
                                    ."` WHERE `".$table_item."`.`id` = `glpi_plugin_accounts_accounts_items`.`items_id`
                                    AND `glpi_plugin_accounts_accounts_items`.`itemtype` = '$itemtype'
                                    AND `glpi_plugin_accounts_accounts_items`.`plugin_accounts_accounts_id` = '" . $accounts . "' "
                                             . getEntitiesRestrictRequest(" AND ",$table_item,'','',$item->maybeRecursive());

                           if ($item->maybeTemplate()) {
                              $query.=" AND ".$table_item.".is_template='0'";
                           }
                           $query.=" ORDER BY `glpi_entities`.`completename`, `".$table_item."`.`$column` ";
                        }

                        if ($result_linked = $DB->query($query))
                           if ($DB->numrows($result_linked)) {
                           $item = new $itemtype();
                           while ($data = $DB->fetch_assoc($result_linked)) {
                              if ($item->getFromDB($data['id'])) {
                                 $out .= $item::getTypeName()." - ".$item->getLink()."<br>";
                              }
                           }
                        } else
                           $out.= ' ';
                     } else
                        $out.= ' ';
                  }
               }
               return $out;
               break;
         }
         break;
   }
   return "";
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_accounts_MassiveActions($type) {

   if (in_array($type, PluginAccountsAccount::getTypes(true))) {
      return array (
               "plugin_accounts_add_item" => __('Associate to account', 'accounts')
      );
   }
   return array ();
}

function plugin_accounts_MassiveActionsDisplay($options=array()) {

   $PluginAccountsAccount=new PluginAccountsAccount();

   if (in_array($options['itemtype'], PluginAccountsAccount::getTypes(true))) {
      $PluginAccountsAccount->dropdownAccounts("plugin_accounts_accounts_id");
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='"._sx('button', 'Post')."'>";
   }
   return "";
}

function plugin_accounts_MassiveActionsProcess($data) {

   $account_item = new PluginAccountsAccount_Item();

   $res = array('ok' => 0,
            'ko' => 0,
            'noright' => 0);
    
   switch ($data['action']) {

      case "plugin_accounts_add_item" :
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('plugin_accounts_accounts_id' => $data['plugin_accounts_accounts_id'],
                        'items_id'      => $key,
                        'itemtype'      => $data['itemtype']);
               if ($account_item->can(-1,'w',$input)) {
                  if ($account_item->add($input)){
                     $res['ok']++;
                  } else {
                     $res['ko']++;
                  }
               } else {
                  $res['noright']++;
               }
            }
         }
         break;
   }
   return $res;
}

//////////////////////////////

// Do special actions for dynamic report
/*
 function plugin_accounts_dynamicReport($parm) {

if ($parm["item_type"]=='PluginAccountsReport'
         && isset($parm["id"])
         && isset($parm["display_type"])) {

$accounts = PluginAccountsReport::queryAccountsList($parm);

PluginAccountsReport::showAccountsList($parm, $accounts);
return true;
}

// Return false if no specific display is done, then use standard display
return false;
}*/

function plugin_datainjection_populate_accounts() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginAccountsAccountInjection'] = 'accounts';
}

?>