<?php
/*
 * @version $Id: comments.php 22657 2014-02-12 16:17:54Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

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

$AJAX_INCLUDE = 1;
include ('../inc/includes.php');

// Send UTF8 Headers
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_POST["table"])
    && isset($_POST["value"])) {
   // Security
   if (!TableExists($_POST['table']) ) {
      exit();
   }

   switch ($_POST["table"]) {
      case "glpi_users" :
         if ($_POST['value'] == 0) {
            $tmpname['link']    = $CFG_GLPI['root_doc']."/front/user.php";
            $tmpname['comment'] = "";
         } else {
            $tmpname = getUserName($_POST["value"],2);
         }
         echo $tmpname["comment"];

         if (isset($_POST['withlink'])) {
            echo "<script type='text/javascript' >\n";
            echo "Ext.get('".$_POST['withlink']."').dom.href='".$tmpname['link']."';";
            echo "</script>\n";
         }
         break;

      default :
         if ($_POST["value"] > 0) {
            $tmpname = Dropdown::getDropdownName($_POST["table"], $_POST["value"], 1);
            echo $tmpname["comment"];
            if (isset($_POST['withlink'])) {
               echo "<script type='text/javascript' >\n";
               echo "Ext.get('".$_POST['withlink']."').dom.href='".Toolbox::getItemTypeFormURL(getItemTypeForTable($_POST["table"]))."?id=".$_POST["value"]."';";
               echo "</script>\n";
            }
         }
   }
}
?>