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

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$account=new PluginAccountsAccount();
$account_item=new PluginAccountsAccount_Item();

if (isset($_POST["add"])) {

   $account->check(-1,'w',$_POST);
   $newID=$account->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {

   $account->check($_POST['id'],'w');
   $account->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {

   $account->check($_POST['id'],'w');
   $account->delete($_POST);
   $account->redirectToList();

} else if (isset($_POST["restore"])) {

   $account->check($_POST['id'],'w');
   $account->restore($_POST);
   $account->redirectToList();

} else if (isset($_POST["purge"])) {

   $account->check($_POST['id'],'w');
   $account->delete($_POST,1);
   $account->redirectToList();

} else if (isset($_POST['idcrypt'])) {
   //History log
   $changes[0]=15;
   $changes[1]= $_POST['nameP'];
   $changes[2]= __('Uncrypted', 'accounts');
   Log::history($_POST['idcrypt'], "PluginAccountsAccount", $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);
    
} else if (isset($_POST["additem"])) {

   if (!empty($_POST['itemtype'])) {
      $account_item->check(-1,'w',$_POST);
      $account_item->addItem($_POST);
   }
   Html::back();

} else if (isset($_POST["deleteitem"])) {

   foreach ($_POST["item"] as $key => $val) {
      $input = array('id' => $key);
      if ($val==1) {
         $account_item->check($key,'w');
         $account_item->delete($input);
      }
   }

   Html::back();

   //from items ?
} else if (isset($_POST["deleteaccounts"])) {

   $input = array('id' => $_POST["id"]);
   $account_item->check($_POST["id"],'w');
   $account_item->delete($input);
   Html::back();

} else {

   $account->checkGlobal("r");

   if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
      $plugin = new Plugin();
      if ($plugin->isActivated("environment"))
         Html::header(PluginAccountsAccount::getTypeName(2),'',"plugins","environment","accounts");
      else
         Html::header(PluginAccountsAccount::getTypeName(2),'',"plugins","accounts");
   } else {
      Html::helpHeader(PluginAccountsAccount::getTypeName(2));
   }
    
   $account->showForm($_GET["id"]);

   if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
      Html::footer();
   } else {
      Html::helpFooter();
   }
}

?>