<?php
/*
 * @version $Id: searchoption.php 21179 2013-06-25 07:08:30Z moyo $
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

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"searchoption.php")) {
   include ('../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

   $addmeta = "";

// Non define case
if (isset($_POST["itemtype"])
    && isset($_POST["field"]) ) {

   if (isset($_POST['meta']) && $_POST['meta']) {
      $addmeta = '2';
   } else {
      $_POST['meta'] = 0;
   }

   $actions = Search::getActionsFor($_POST["itemtype"], $_POST["field"]);

   // is it a valid action for type ?
   if (count($actions)
       && (empty($_POST['searchtype']) || !isset($actions[$_POST['searchtype']]))) {
      $tmp                 = $actions;
      unset($tmp['searchopt']);
      $_POST['searchtype'] = key($tmp);
      unset($tmp);
   }

   $randsearch   = -1;
   $dropdownname = "searchtype$addmeta".$_POST["itemtype"].$_POST["num"];
   $searchopt    = array();

   echo "<table><tr><td>";
   if (count($actions)>0) {

      // get already get search options
      if (isset($actions['searchopt'])) {
         $searchopt = $actions['searchopt'];
         // No name for clean array whith quotes
         unset($searchopt['name']);
         unset($actions['searchopt']);
      }
      $randsearch = Dropdown::showFromArray("searchtype".$addmeta."[".$_POST["num"]."]",
                                            $actions,
                                            array('value' => $_POST["searchtype"]));
   }
   echo "</td><td>";
   echo "<span id='span$dropdownname'>\n";

   $_POST['value']      = stripslashes($_POST['value']);
   $_POST['searchopt']  = $searchopt;

   include(GLPI_ROOT."/ajax/searchoptionvalue.php");
   echo "</span>\n";
   echo "</td></tr></table>";

   $paramsaction = array('searchtype' => '__VALUE__',
                         'field'      => $_POST["field"],
                         'itemtype'   => $_POST["itemtype"],
                         'num'        => $_POST["num"],
                         'value'      => rawurlencode($_POST['value']),
                         'searchopt'  => $searchopt,
                         'meta'       => $_POST['meta']);

   Ajax::updateItemOnSelectEvent("dropdown_searchtype".$addmeta."[".$_POST["num"]."]$randsearch",
                                 "span$dropdownname",
                                 $CFG_GLPI["root_doc"]."/ajax/searchoptionvalue.php",
                                 $paramsaction);
}
?>
