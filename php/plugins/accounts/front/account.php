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

include ('../../../inc/includes.php');

$plugin=new plugin;

if ($_SESSION['glpiactiveprofile']['interface'] == 'central'){
   if ($plugin->isActivated("environment"))
      Html::header(PluginAccountsAccount::getTypeName(2),'',"plugins","environment","accounts");
   else
      Html::header(PluginAccountsAccount::getTypeName(2),'',"plugins","accounts");
} else {
   Html::helpHeader(PluginAccountsAccount::getTypeName(2));
}

$account=new PluginAccountsAccount();
$account->checkGlobal("r");

if ($account->canView()) {

   if (plugin_accounts_haveRight("all_users","r")) {

      echo "<div align='center'><script type='text/javascript'>";
      echo "cleanhide('modal_account_content');";
      echo "var account_window=new Ext.Window({
               layout:'fit',
               width:800,
               height:400,
               closeAction:'hide',
               modal: true,
               autoScroll: true,
               title: \"".__('Type view', 'accounts')."\",
                        autoLoad: '".$CFG_GLPI['root_doc']."/plugins/accounts/ajax/accounttree.php'
   });";
      echo "</script>";

      echo "<a onclick='account_window.show();' href='#modal_account_content' title='".
               __s('Type view')."'>".__('Type view', 'accounts')."</a>";
      echo "</div>";

   }

   Search::show("PluginAccountsAccount");

} else {
   Html::displayRightError();
}

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}

?>