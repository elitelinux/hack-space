<?php
/*
 * @version $Id: user.form.php 20129 2013-02-04 16:53:59Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
*/

include ('../inc/includes.php');

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$user      = new User();
$groupuser = new Group_User();

if (empty($_GET["id"]) && isset($_GET["name"])) {

   $user->getFromDBbyName($_GET["name"]);
   Html::redirect($CFG_GLPI["root_doc"]."/front/user.form.php?id=".$user->fields['id']);
}

if (empty($_GET["name"])) {
   $_GET["name"] = "";
}

if (isset($_GET['getvcard'])) {
   if (empty($_GET["id"])) {
      Html::redirect($CFG_GLPI["root_doc"]."/front/user.php");
   }
   $user->check($_GET['id'], 'r');
   $user->generateVcard();

} else if (isset($_POST["add"])) {
   $user->check(-1, 'w', $_POST);

   // Pas de nom pas d'ajout
   if (!empty($_POST["name"]) && $newID=$user->add($_POST)) {
      Event::log($newID, "users", 4, "setup",
                 sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $user->check($_POST['id'], 'd');
   $user->delete($_POST);
   Event::log($_POST["id"], "users", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
   $user->redirectToList();

} else if (isset($_POST["restore"])) {
   $user->check($_POST['id'], 'd');
   $user->restore($_POST);
   Event::log($_POST["id"], "users", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s restores an item'), $_SESSION["glpiname"]));
   $user->redirectToList();

} else if (isset($_POST["purge"])) {
   $user->check($_POST['id'], 'd');
   $user->delete($_POST, 1);
   Event::log($_POST["id"], "users", 4, "setup",
              sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   $user->redirectToList();

} else if (isset($_POST["force_ldap_resynch"])) {
   Session::checkRight('user_authtype', 'w');

   $user->getFromDB($_POST["id"]);
   AuthLdap::ldapImportUserByServerId(array('method' => AuthLDAP::IDENTIFIER_LOGIN,
                                            'value'  => $user->fields["name"]),
                                      true, $user->fields["auths_id"], true);
   Html::back();

} else if (isset($_POST["update"])) {
   $user->check($_POST['id'], 'w');
   $user->update($_POST);
   Event::log($_POST['id'], "users", 5, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["addgroup"])) {
   $groupuser->check(-1,'w',$_POST);
   if ($groupuser->add($_POST)) {
      Event::log($_POST["users_id"], "users", 4, "setup",
                 //TRANS: %s is the user login
                 sprintf(__('%s adds a user to a group'), $_SESSION["glpiname"]));
   }
   Html::back();

} else if (isset($_POST["deletegroup"])) {
   if (count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($groupuser->can($key,'w')) {
            $groupuser->delete(array('id' => $key));
         }
      }
   }
   Event::log($_POST["users_id"], "users", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s deletes users from a group'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["change_auth_method"])) {
   Session::checkRight('user_authtype', 'w');

   if (isset($_POST["auths_id"])) {
      User::changeAuthMethod(array($_POST["id"]), $_POST["authtype"], $_POST["auths_id"]);
   }
   Html::back();

} else {



   if (isset($_GET["ext_auth"])) {
      Html::header(User::getTypeName(2), '', "admin", "user");
      User::showAddExtAuthForm();
      Html::footer();
   } else if (isset($_POST['add_ext_auth_ldap'])) {
      Session::checkRight("import_externalauth_users", "w");

      if (isset($_POST['login']) && !empty($_POST['login'])) {
         AuthLdap::importUserFromServers(array('name' => $_POST['login']));
      }
      Html::back();
   } else if (isset($_POST['add_ext_auth_simple'])) {
         if (isset($_POST['login']) && !empty($_POST['login'])) {
            Session::checkRight("import_externalauth_users", "w");
            $input = array('name'     => $_POST['login'],
                           '_extauth' => 1,
                           'add'      => 1);
            $user->check(-1, 'w', $input);
            $newID = $user->add($input);
            Event::log($newID, "users", 4, "setup",
                       sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"],
                               $_POST["login"]));
         }

         Html::back();
   } else {
      Session::checkRight("user", "r");
      Html::header(User::getTypeName(2), '', "admin", "user");
      $user->showForm($_GET["id"]);
      Html::footer();

   }
}
?>