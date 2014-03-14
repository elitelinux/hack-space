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

Session::checkRight("config", "w");

$config=new PluginAccountsConfig();
$notif=new PluginAccountsNotificationState();

if (isset($_POST["add"])) {

   $notif->addNotificationState($_POST['plugin_accounts_accountstates_id']);
   Html::back();

} else if (isset($_POST["delete"])) {

   foreach ($_POST["item"] as $key => $val) {
      if ($val==1) {
         $notif->delete(array('id'=>$key));
      }
   }
   Html::back();

} else if (isset($_POST["update"])) {

   $config->update($_POST);
   Html::back();

}

?>