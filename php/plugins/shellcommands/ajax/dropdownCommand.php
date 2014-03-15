<?php
///*
// -------------------------------------------------------------------------
// Shellcommands plugin for GLPI
// Copyright (C) 2006-2012 by the Shellcommands Development Team.
//
// https://forge.indepnet.net/projects/shellcommands
// -------------------------------------------------------------------------
//
// LICENSE
//
// This file is part of Shellcommands.
//
// Shellcommands is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// Shellcommands is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Shellcommands. If not, see <http://www.gnu.org/licenses/>.
// --------------------------------------------------------------------------
//*/
//
//
if (strpos($_SERVER['PHP_SELF'],"dropdownCommand.php")) {
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

// Make a select box
if ($_POST["idtable"]) {
   
   $link = "dropdownCommandValue.php"; // Link to user for search only > normal users
   $use_ajax = false;
   if(isset($_POST["countItem"])){// Display an search input if too items in select
      $item = getItemForItemtype($_POST['idtable']);
      $item->getFromDB($_POST['itemID']); 
      $tabValue = explode('-',$_POST['value']);
      if(sizeof($tabValue) == 2){
         if ((stristr($tabValue[0] ,'mac') || stristr($tabValue[0] ,'ip'))){
            if($CFG_GLPI["use_ajax"] && PluginShellcommandsShellcommand_Item::countForItem($item, array('type' => $tabValue[0],'itemId' => $tabValue[1])) > $CFG_GLPI["ajax_limit_count"]) {
               $use_ajax = true;
            }
            echo '</br>'.__('Network port').' ';
         }
      }
   }
   
   $rand     = mt_rand();
   $paramsallitems = array('searchText'          => '__VALUE__',
                           'itemtype'            => $_POST["itemtype"],
                           'itemID'              => $_POST["itemID"],
                           'value'               => $_POST["value"],
                           'rand'                => $rand,
                           'myname'              => $_POST["myname"],
                           'displaywith'         => array('otherserial', 'serial'),
                           'display_emptychoice' => false);

   if (isset($_POST['idtable'])) {
      $paramsallitems['idtable'] = $_POST['idtable'];
   }
   if (isset($_POST['entity_restrict'])) {
      $paramsallitems['entity_restrict'] = $_POST['entity_restrict'];
   }
   if (isset($_POST['condition'])) {
      $paramsallitems['condition'] = stripslashes($_POST['condition']);
   }

   $default = "<select name='".$_POST["myname"]."'><option value='0'>".Dropdown::EMPTY_VALUE.
              "</option></select>";

   Ajax::dropdown($use_ajax, "/plugins/shellcommands/ajax/$link", $paramsallitems, $default, $rand);
   
}
?>