<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Utilitaires plugin for GLPI
 Copyright (C) 2003-2011 by the Utilitaires Development Team.

 https://forge.indepnet.net/projects/utilitaires
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Utilitaires.

 Utilitaires is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Utilitaires is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with utilitaires. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

Html::header(PluginUtilitairesUtilitaire::getTypeName(2),'',"plugins","utilitaires");

$itemtype = "";
$actionId = "";

$util=new PluginUtilitairesUtilitaire();
if ($util->canView() || Session::haveRight("config","w")) {
   
   if (isset($_POST["do_action"])) {
      if (isset($_POST['itemtype'])) $itemtype = $_POST['itemtype'];
      if (isset($_POST['actionId'])) $actionId = $_POST['actionId'];
      if (isset($_POST['entities_id'])) $entities = $_POST['entities_id'];
      $date = "NULL";
      if (isset($_POST['date'])) $date = $_POST['date'];
      
      //Do actions
      $res = PluginUtilitairesUtilitaire::processObjects($itemtype, $actionId, $entities, $date);
      if ($res) {
         
         echo "<div class='box' style='margin-bottom:20px;'>";
         echo "<div class='box-tleft'><div class='box-tright'><div class='box-tcenter'>";
         echo "</div></div></div>";
         echo "<div class='box-mleft'><div class='box-mright'><div class='box-mcenter'>";
         echo "<h3>";
         $result = __('Successful treatment', 'utilitaires');
         if (isset($res['ok']) 
            || isset($res['ko'])) {
            $ok = "";
            $ko = "";
            if ($res['ok'] > 0) {
               $ok .= __('Successful treatment', 'utilitaires');
               $ok .= " : ".$res['ok']."&nbsp;".__('records processed', 'utilitaires');
            }
            if ($res['ko'] > 0) {
               if ($res['ok'] > 0) {
                  $ko .= "<br>";
               }
               $ko .= __('Treatment failure', 'utilitaires');
               $ko .=" : ".$res['ko']."&nbsp;".__('unprocessed records', 'utilitaires');
            }
            $result = $ok;
            $result .= $ko;
         }
         echo $result;
         echo "</h3>";
         echo "</div></div></div>";
         echo "<div class='box-bleft'><div class='box-bright'><div class='box-bcenter'>";
         echo "</div></div></div>";
         echo "</div>";
         
         PluginUtilitairesUtilitaire::MenuDisplay();
      } else {
         PluginUtilitairesUtilitaire::MenuDisplay();
      }
      //if ($actionId != PluginUtilitairesUtilitaire::IMPORT_PRINTERS_ACTION) {
      //   if (PluginUtilitairesUtilitaire::processObjects($itemtype, $actionId, $start)) {
      //      echo "<div align='center'><strong>".__('Successful treatment', 'utilitaires')."<br>";
      //      PluginUtilitairesUtilitaire::MenuDisplay();
      //   } else {
      //      PluginUtilitairesUtilitaire::MenuDisplay();
      //   }
      //} else {
      //   PluginUtilitairesUtilitaire::processObjects($itemtype, $actionId, $_GET['start']);
      //}
   
   //List actions
   } else if (isset($_POST["choose"])) {
      
      if (!empty($_POST['actionId'])) {
         foreach ($_POST["choose"] as $key => $val) {
            $itemtype = $key;
         }
         $date = "NULL";
         if (isset($_POST['actionId'])) $actionId = $_POST['actionId'];
         if (isset($_POST['entities_id'])) $entities = $_POST['entities_id'];
         if (isset($_POST['date'])) $date = $_POST['date'];
         
         $nb = PluginUtilitairesUtilitaire::countObjectsToProcess($itemtype, $actionId, $entities, $date);
         if ($nb) {
            PluginUtilitairesUtilitaire::ShowActions($itemtype, $actionId, $entities, $date);
         } else {
            PluginUtilitairesUtilitaire::MenuDisplay();
         }
         //if ($actionId != PluginUtilitairesUtilitaire::IMPORT_PRINTERS_ACTION) {
         //   if ($nb) {
         //      PluginUtilitairesUtilitaire::ShowActions($itemtype, $actionId);
         //   } else {
         //      PluginUtilitairesUtilitaire::MenuDisplay();
         //   }
         //} else {
         //   PluginUtilitairesUtilitaire::MenuDisplay();
         //}
      } else {
         PluginUtilitairesUtilitaire::MenuDisplay();
      }
   } else {

      PluginUtilitairesUtilitaire::MenuDisplay();
   }
} else {
	Html::displayRightError();
}

Html::footer();

?>
