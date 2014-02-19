<?php
/*
 * @version $Id: rssfeed.form.php 20129 2013-02-04 16:53:59Z moyo $
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
* @since version 0.84
*/

include ('../inc/includes.php');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
$rssfeed = new RSSFeed();
Session::checkLoginUser();

if (isset($_POST["add"])) {
   $rssfeed->check(-1,'w',$_POST);

   $newID = $rssfeed->add($_POST);
   Event::log($newID, "rssfeed", 4, "tools",
              sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"],
                      $rssfeed->fields["name"]));
   Html::redirect($CFG_GLPI["root_doc"]."/front/rssfeed.form.php?id=".$newID);

} else if (isset($_POST["delete"])) {
   $rssfeed->check($_POST["id"],'d');

   $rssfeed->delete($_POST);
   Event::log($_POST["id"], "rssfeed", 4, "tools",
              //TRANS: %s is the user login
              sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   $rssfeed->redirectToList();

} else if (isset($_POST["update"])) {
   $rssfeed->check($_POST["id"],'w');   // Right to update the rssfeed

   $rssfeed->update($_POST);
   Event::log($_POST["id"], "rssfeed", 4, "tools",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

}  else if (isset($_POST["addvisibility"])) {
   if (isset($_POST["_type"]) && !empty($_POST["_type"])
       && isset($_POST["rssfeeds_id"]) && $_POST["rssfeeds_id"]) {
      $item = NULL;
      switch ($_POST["_type"]) {
         case 'User' :
            if (isset($_POST['users_id']) && $_POST['users_id']) {
               $item = new RSSFeed_User();
            }
            break;

         case 'Group' :
            if (isset($_POST['groups_id']) && $_POST['groups_id']) {
               $item = new Group_RSSFeed();
            }
            break;

         case 'Profile' :
            if (isset($_POST['profiles_id']) && $_POST['profiles_id']) {
               $item = new Profile_RSSFeed();
            }
            break;

         case 'Entity' :
            $item = new Entity_RSSFeed();
            break;
      }
      if (!is_null($item)) {
         $item->add($_POST);
         Event::log($_POST["rssfeeds_id"], "rssfeed", 4, "tools",
                    //TRANS: %s is the user login
                    sprintf(__('%s adds a target'), $_SESSION["glpiname"]));
      }
   }
   Html::back();

}  else {
   if ($_SESSION["glpiactiveprofile"]["interface"] == "helpdesk") {
      Html::helpHeader(RSSFeed::getTypeName(2),'',$_SESSION["glpiname"]);
   } else {
      Html::header(RSSFeed::getTypeName(2),'',"utils","rssfeed");
   }

   $rssfeed->showForm($_GET["id"]);

   if ($_SESSION["glpiactiveprofile"]["interface"] == "helpdesk") {
      Html::helpFooter();
   } else {
      Html::footer();
   }
}
?>