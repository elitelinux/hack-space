<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Racks plugin for GLPI
 Copyright (C) 2003-2011 by the Racks Development Team.

 https://forge.indepnet.net/projects/racks
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Racks.

 Racks is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Racks is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Racks. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginRacksReport extends CommonDBTM {

   public function execQueryGetOnlyRacks(){
      global $DB;

      $pRack = new PluginRacksRack();

      $query = "SELECT DISTINCT `".$pRack->getTable()."`.*
              FROM `".$pRack->getTable()."`
              ORDER BY `".$pRack->getTable()."`.`name` ASC" ;

      $ret = array(
         "query" => $query,
         "query_result" => $DB->query($query)
      );
      return $ret;
   }


   public function execQuery($post){
      global $DB;

      $pRackItem = new PluginRacksRack_Item();
      $pRack = new PluginRacksRack();
      $query = "";

      $face = -1;
      if (isset($post['select_front_rear']) && $post['select_front_rear'] != 0){
         $face = $post['select_front_rear'];
      }

      if (isset($post['plugin_racks_racks_id']) && $post['plugin_racks_racks_id'] != 0){
         $restrictRackId = "   AND `".$pRack->getTable()."`.`id` = '".$post['plugin_racks_racks_id']."'";
         $restrictRackId .= "   AND `".$pRack->getTable()."`.`id` = `".$pRackItem->getTable()."`.`plugin_racks_racks_id`";
         $leftjoin=", `glpi_plugin_racks_racks_items` WHERE (1) ".$restrictRackId;


      }else{
         $restrictRackId="";
         $leftjoin = "LEFT JOIN `glpi_plugin_racks_racks_items` ON (`glpi_plugin_racks_racks_items`.`plugin_racks_racks_id` = `glpi_plugin_racks_racks`.`id`)";

         $restrictRackId = "AND `glpi_plugin_racks_racks_items`.`plugin_racks_racks_id` = `glpi_plugin_racks_racks`.`id`";
      }


      switch ($face) {
         case PluginRacksRack::FRONT_FACE:
            $query = "SELECT `".$pRackItem->getTable()."`.* , `".$pRack->getTable()."`.*
              FROM `".$pRackItem->getTable()."`,`glpi_plugin_racks_itemspecifications` , `".$pRack->getTable()."`
              WHERE `".$pRackItem->getTable()."`.`plugin_racks_itemspecifications_id` = `glpi_plugin_racks_itemspecifications`.`id` ".$restrictRackId." 
              AND (`".$pRackItem->getTable()."`.`faces_id` = '".PluginRacksRack::FRONT_FACE."' )
              ORDER BY `".$pRack->getTable()."`.`name` ASC, `".$pRackItem->getTable()."`.`faces_id` ASC, `".$pRackItem->getTable()."`.`position` DESC" ;
            break;

         case PluginRacksRack::BACK_FACE:
            $query = "SELECT `".$pRackItem->getTable()."`.* , `".$pRack->getTable()."`.*
              FROM `".$pRackItem->getTable()."`,`glpi_plugin_racks_itemspecifications` , `".$pRack->getTable()."`
              WHERE `".$pRackItem->getTable()."`.`plugin_racks_itemspecifications_id` = `glpi_plugin_racks_itemspecifications`.`id` ".$restrictRackId." 
              AND (`".$pRackItem->getTable()."`.`faces_id` = '".PluginRacksRack::BACK_FACE."' )
              ORDER BY `".$pRack->getTable()."`.`name` ASC, `".$pRackItem->getTable()."`.`faces_id` ASC, `".$pRackItem->getTable()."`.`position` DESC" ;
            break;
         default:
            $query = "SELECT `".$pRackItem->getTable()."`.* , `".$pRack->getTable()."`.*
              FROM  `".$pRack->getTable()."`
              $leftjoin
              ORDER BY `".$pRack->getTable()."`.`name` ASC, `".$pRackItem->getTable()."`.`faces_id` ASC, `".$pRackItem->getTable()."`.`position` DESC" ;

              break;
      }

      $ret = array(
         "query" => $query,
         "query_result" => $DB->query($query)
      );
      return $ret;
   }


   public function showResult($output_type,$limit=0){
      global $DB;

      $arrayRet = $this->execQuery($_POST);

      $result = $arrayRet['query_result'];
      $query = $arrayRet['query'];

      $nbtot = ($result ? $DB->numrows($result) : 0);

      if ($limit) {
         $start = (isset($_GET["start"]) ? $_GET["start"] : 0);
         if ($start >= $nbtot) {
            $start = 0;
         }
         if ($start > 0 || $start + $limit < $nbtot) {
            $result = $DB->query($query." LIMIT $start,$limit");
         }
      } else {
         $start = 0;
      }

      $nbCols = $DB->num_fields($result);
      $nbrows = $DB->numrows($result);


      $groupByRackName = true;
      if (isset($_POST['groupByRackName']) && $_POST['groupByRackName'] == "on"){
         $groupByRackName = false;
      }

      $title = date("d/m/Y H:i");
      if ($nbtot == 0) {
         echo "<div class='center'><font class='red b'>".__("No item found")."</font></div>";
         Html::footer();
      } else if ($output_type == Search::PDF_OUTPUT_LANDSCAPE || $output_type == Search::PDF_OUTPUT_PORTRAIT) {
         include (GLPI_ROOT."/lib/ezpdf/class.ezpdf.php");
      } else if ($output_type == Search::HTML_OUTPUT) {

         echo "<div class='center'><table class='tab_cadre_fixe'>";
         echo "<tr  class='tab_bg_1'><th>$title</th></tr>\n";
         echo "<tr class='tab_bg_2 center'><td class='center'>";
         echo "<form method='POST' action='".$_SERVER["PHP_SELF"]."?start=$start' target='_blank'>\n";

         $param = "";
         foreach ($_POST as $key => $val) {
            if (is_array($val)) {
               foreach ($val as $k => $v) {
                  echo "<input type='hidden' name='".$key."[$k]' value='$v' >";
                  if (!empty($param)) {
                     $param .= "&";
                  }
                  $param .= $key."[".$k."]=".urlencode($v);
               }
            } else {
               echo "<input type='hidden' name='$key' value='$val' >";
               if (!empty($param)) {
                  $param .= "&";
               }
               $param .= "$key=".urlencode($val);
            }
         }

         echo "<input type='hidden' name='result_search_reports' value='searchdone' >";
         $param .= "&result_search_reports=searchdone&target=_blank";

         Dropdown::showOutputFormat();
         Html::closeForm();
         echo "</td></tr>";
         echo "</table></div>";

         Html::printPager($start, $nbtot, $_SERVER['PHP_SELF'], $param);
      }

      if ($nbtot > 0) {

         if ($output_type == Search::HTML_OUTPUT)
         echo "<form method='POST' action='".$_SERVER["PHP_SELF"]."?start=$start'>\n";

         echo Search::showHeader($output_type, $nbrows, $nbCols, true);

         $showAllFieds = true;
         $listFields = array();
         $cptField = 0;

         $showAllFieds =
            (!isset($_POST['cb_object_name'])      || $_POST['cb_object_name'] != "on")
         && (!isset($_POST['cb_object_location'])  || $_POST['cb_object_location'] != "on")
         && (!isset($_POST['cb_group'])            || $_POST['cb_group'] != "on")
         && (!isset($_POST['cb_manufacturer'])     || $_POST['cb_manufacturer'] != "on")
         && (!isset($_POST['cb_model'])            || $_POST['cb_model'] != "on")
         && (!isset($_POST['cb_serial_number'])    || $_POST['cb_serial_number'] != "on");


         $num = 1;
         $cptRow = 1;

         if  (!$showAllFieds){

            $this->showTitle($output_type, $num, __("Bay name","racks"), 'name', false);
            $cptField++;

            $this->showTitle($output_type, $num, _n("Place","Places",1,"racks"), 'location', false);
            $cptField++;

            $this->showTitle($output_type, $num,  _n("Location","Locations",1,"racks"), 'roomlocation', false);
            $cptField++;
            
            $this->showTitle($output_type, $num,__("U","racks"), 'u', false);
            $cptField++;
            
            $this->showTitle($output_type, $num,__("Front","racks")." / ".__("Back","racks"), 'front_rear', false);
            $cptField++;


            if (isset($_POST['cb_object_name']) && $_POST['cb_object_name'] == "on") {
               $listFields['object_name'] = $_POST['cb_object_name'];
               $this->showTitle($output_type, $num,__("Object name","racks"), 'object_name', false);
               $cptField++;
            }

            // Lieu
            if (isset($_POST['cb_object_location']) && $_POST['cb_object_location'] == "on") {
               $listFields['object_location'] = $_POST['cb_object_location'];
               $this->showTitle($output_type, $num, __("Object location","racks"), 'object_location', false);
               $cptField++;
            }

            // Groupe
            if (isset($_POST['cb_group']) && $_POST['cb_group'] == "on") {
               $listFields['group'] = $_POST['cb_group'];
               $this->showTitle($output_type, $num, __("Group"), 'roomlocation', false);
               $cptField++;
            }

            // Fabricant
            if (isset($_POST['cb_manufacturer']) && $_POST['cb_manufacturer'] == "on") {
               $listFields['manufacturer'] = $_POST['cb_manufacturer'];
               $this->showTitle($output_type, $num, __("Manufacturer"), 'manufacturer', false);
               $cptField++;
            }


            // Modèle
            if (isset($_POST['cb_model']) && $_POST['cb_model'] == "on") {
               $listFields['model'] = $_POST['cb_model'];
               $this->showTitle($output_type, $num, __("Model"), 'model', false);
               $cptField++;
            }

            // Numéro de série
            if (isset($_POST['cb_serial_number']) && $_POST['cb_serial_number'] == "on") {
               $listFields['serial_number'] = $_POST['cb_serial_number'];
               $this->showTitle($output_type, $num, __("Serial number","racks"), 'group', false);
               $cptField++;
            }
         } else {
            $this->showTitle($output_type, $num, __("Bay name","racks"), 'rack_name', false);
            $listFields['rack_name'] = true;

            $this->showTitle($output_type, $num, __("Place","racks"), 'location', false);
            $listFields['location'] = true;

            $this->showTitle($output_type, $num, __("Location","racks"), 'roomlocation', false);
            $listFields['roomlocation'] = true;

            $this->showTitle($output_type, $num, __("U","racks"), 'u', false);
            $listFields['u'] = true;

            $this->showTitle($output_type, $num, __("Front","racks")." / ".__("Back","racks"), 'front_rear', false);
            $listFields['front_rear'] = true;

            $this->showTitle($output_type, $num, __("Object name","racks"), 'object_name', false);
            $listFields['object_name'] = true;

            $this->showTitle($output_type, $num, __("Object location","racks"), 'object_location', false);
            $listFields['object_location'] = true;

            $this->showTitle($output_type, $num, __("Group"), false);
            $listFields['group'] = true;

            $this->showTitle($output_type, $num, __("Type"), 'type', false);
            $listFields['type'] = true;

            $this->showTitle($output_type, $num, __("Manufacturer"), 'manufacturer', false);
            $listFields['manufacturer'] = true;

            $this->showTitle($output_type, $num, __("Model"), 'model', false);
            $listFields['model'] = true;

            $this->showTitle($output_type, $num, __("Serial number","racks"), 'serial_number', false);
            $listFields['serial_number'] = true;

            $this->showTitle($output_type, $num, __("Inventory number"), 'other_serial', false);
            $listFields['other_serial'] = true;

            $cptField = 13;
         }

         echo Search::showEndLine($output_type);

         $num=1;

         $currentRack = -1;


         while ($row = $DB->fetch_array($result)) {

            // itemtype
            $itemtype = $row['itemtype'];

            $num = 1;
            $cptRow++;
            echo Search::showNewLine($output_type);

            if (isset($row['itemtype']) && $row['itemtype'] != "" ){
               $class = substr($itemtype, 0, -5);
               $item = new $class();
               $table = getTableForItemType($class);
               $r = $DB->query("SELECT * FROM `".$table."` WHERE `id` = '".$row["items_id"]."' ");
               $device = $DB->fetch_array($r);
            }

            // nom
            $link = Toolbox::getItemTypeFormURL("PluginRacksRack");
            if ($groupByRackName || $currentRack != $row['id']) {
               if($output_type == Search::HTML_OUTPUT){
                  echo Search::showItem($output_type, "<a href=\"".$link."?id=".$row["id"]."\">".$row["name"]."</a>", $num, $cptRow);
               }else{
                  echo Search::showItem($output_type, $row["name"], $num, $cptRow);
               }
            } else {
               echo Search::showItem($output_type, "&nbsp;", $num, $cptRow);
            }

            // lieu
            if ($groupByRackName || $currentRack != $row['id']) {
               $tmpId = $row['locations_id'];
               $tmpObj = new Location();
               $tmpObj->getFromDB($tmpId);
               if (isset($tmpObj->fields['name'])) {
                  echo Search::showItem($output_type, $tmpObj->fields['name'], $num, $cptRow);
               } else {
                  echo Search::showItem($output_type, "&nbsp;", $num, $cptRow);
               }
            } else {
               echo Search::showItem($output_type, "&nbsp;", $num, $cptRow);
            }

            // Emplacement
            if ($groupByRackName || $currentRack != $row['id']) {
               $tmpId = $row['plugin_racks_roomlocations_id'];
               $tmpObj = new PluginRacksRoomLocation();
               $tmpObj->getFromDB($tmpId);
               if (isset($tmpObj->fields['name'])) {
                  echo Search::showItem($output_type, $tmpObj->fields['name'], $num, $cptRow);
               } else {
                  echo Search::showItem($output_type, '&nbsp;', $num, $cptRow);
               }
            } else {
               echo Search::showItem($output_type, "&nbsp;", $num, $cptRow);
            }

            if (isset($row['itemtype']) && $row['itemtype'] != "" ){
               // U
               if (isset($row['position']) && $row['position'] != "") {
                  echo Search::showItem($output_type, $row['position'], $num, $cptRow);
               }else{
                  echo Search::showItem($output_type, "&nbsp;", $num, $cptRow);
               }

               // avant / arrière
               if ($row['faces_id'] == 1) {
                  echo Search::showItem($output_type, __("Front","racks"), $num, $cptRow);
               } else {
                  echo Search::showItem($output_type, __("Back","racks"), $num, $cptRow);
               }

               // Nom de l'objet
               if (array_key_exists("object_name", $listFields)) {
                  $link = Toolbox::getItemTypeFormURL(substr($itemtype, 0, -5));
                  if ($itemtype != 'PluginRacksOtherModel') {
                     if($output_type == Search::HTML_OUTPUT){
                        echo Search::showItem($output_type, "<a href=\"".$link."?id=".$row["items_id"]."\">".$device["name"]."</a>", $num, $cptRow);
                     }else{
                        echo Search::showItem($output_type, $device["name"], $num, $cptRow);
                     }
                  } else {
                     echo Search::showItem($output_type, $device["name"], $num, $cptRow);
                  }
               }
                
               // Lieu de l'objet
               if (array_key_exists("object_location", $listFields)) {
                  if ($itemtype != 'PluginRacksOtherModel') {
                     echo Search::showItem($output_type, Dropdown::getDropdownName("glpi_locations", $device["locations_id"]), $num, $cptRow);
                  } else {
                     echo Search::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow);
                  }
               }
                
               // Groupe
               if (array_key_exists("group", $listFields)) {
                  // Groupe
                  if ($itemtype != 'PluginRacksOtherModel') {
                     echo Search::showItem($output_type, Dropdown::getDropdownName("glpi_groups", $device["groups_id_tech"]), $num, $cptRow);
                  } else {
                     echo Search::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow);
                  }
               }

               // type
               if (array_key_exists("type", $listFields)) {
                  echo Search::showItem($output_type, $item->getTypeName(), $num, $cptRow);

               }

               // fabricant
               if (array_key_exists("manufacturer", $listFields)) {

                  if ($itemtype != 'PluginRacksOtherModel') {
                     echo Search::showItem($output_type, Dropdown::getDropdownName("glpi_manufacturers", $device["manufacturers_id"]), $num, $cptRow);
                  } else {
                     echo Search::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow);
                  }
               }

               // modèle //TODO = model du rack => model des objets
               if (array_key_exists("model", $listFields)) {
                   
                  if ($itemtype != 'PluginRacksOtherModel') {
                      
                     $model_table = getTableForItemType($itemtype);
                     $modelfield = getForeignKeyFieldForTable(getTableForItemType($itemtype));
                     echo Search::showItem($output_type, Dropdown::getDropdownName($model_table, $device[$modelfield]), $num, $cptRow);
                      
                  } else {

                     echo Search::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow);
                  }
               }

               // numéro de série
               if (array_key_exists("serial_number", $listFields)) {
                  if ($itemtype != 'PluginRacksOtherModel') {
                     echo Search::showItem($output_type, $device['serial'], $num, $cptRow);
                  } else {
                     echo Search::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow);
                  }
               }

               // numéro d'inventaire
               if (array_key_exists("other_serial", $listFields)) {
                  if ($itemtype != 'PluginRacksOtherModel') {
                     echo Search::showItem($output_type, $device['otherserial'], $num, $cptRow);
                  } else {
                     echo Search::showItem($output_type, Dropdown::EMPTY_VALUE, $num, $cptRow);
                  }
               }

               $currentRack = $row['id'];
            }else{
               for ($k=0;$k<$cptField-3;$k++){
                  echo Search::showItem($output_type, "&nbsp;", $num, $cptRow, "");
               }
            }
            echo Search::showEndLine($output_type);
         }

         if ($output_type == Search::HTML_OUTPUT) {
            Html::closeForm();
         }

         echo Search::showFooter($output_type, $title);
      }
   }

   public function showForm($post){

      echo "<form name='form' method='post' action='../front/report.php'>";

      echo "<table class='tab_cadre_fixe' >";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>".__("Search criteria","racks") ."</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Bay name","racks")." :</td>";
      echo "<td>";

      $arrayValue = array();
      if (isset($post['plugin_racks_racks_id'])){
         $arrayValue = array("value" => $post['plugin_racks_racks_id']);
      }
      $idSelectRankName = Dropdown::show( 'PluginRacksRack', $arrayValue );
      echo "<input type='hidden' name='id_select_rank_name' id='id_select_rank_name' val  ue='".$idSelectRankName."' />";
      echo "</td>";
      echo "<td>".__("Front","racks")." / " . __("Back","racks") ;
      echo "<input type='hidden' name='id_select_front_rear' id='id_select_front_rear' value='".$idSelectRankName."' />";
      echo "</td>";

      echo "<td>";

      $arrayValue = array();
      if (isset($post['select_front_rear'])){
         $arrayValue = array("value" => $post['select_front_rear']);
      }
      $idSelectFrontRear = Dropdown::showFromArray("select_front_rear", array("0" => Dropdown::EMPTY_VALUE,"1" => __("Front","racks"),"2" => __("Back","racks")),$arrayValue);
      echo "</td>";
      echo "</tr>";
      echo "<tr  class='tab_bg_1'>";
      echo "<td class='top'>".__("Field to export","racks")."</td>";
      echo "<td>";

      echo "<label for='cb_object_name'>   <input type='checkbox' name='cb_object_name' id='cb_object_name' ";
      if (isset($post['cb_object_name'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Object name","racks")."<br/></label>";

      echo "<label for='cb_object_location'>      <input type='checkbox' name='cb_object_location' id='cb_object_location' ";
      if (isset($post['cb_object_location'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Object location","racks")."<br/></label>";

      echo "<label for='cb_group'>      <input type='checkbox' name='cb_group' id='cb_group' ";
      if (isset($post['cb_group'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Group")."<br/></label>";

      echo "</td>";
      echo "<td colspan='2'>";
      echo "<label for='cb_manufacturer'>   <input type='checkbox' name='cb_manufacturer' id='cb_manufacturer' ";
      if (isset($post['cb_manufacturer'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Manufacturer")."<br/></label>";

      echo "<label for='cb_model'>      <input type='checkbox' name='cb_model' id='cb_model' ";
      if (isset($post['cb_model'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Model")."<br/></label>";

      echo "<label for='cb_serial_number'><input type='checkbox' name='cb_serial_number' id='cb_serial_number' ";
      if (isset($post['cb_serial_number'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Serial number","racks")."<br/></label>";

      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";

      echo "<input type='hidden' name='result_search_reports' id='result_search_reports' value='searchdone' />";
      echo "<input type='submit' value='"._sx("button", "Search")."' class='submit' />";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__("Display options","racks")."</td>\n";
      echo "<td colspan='3'><label for='groupByRackName'><input type='checkbox' name='groupByRackName' id='groupByRackName' ";

      if (!isset($_POST['result_search_reports']) ){
         echo " checked ";
      }else if (isset($post['groupByRackName'])){
         echo " checked ";
      }
      echo "/>&nbsp;".__("Group by bay name","racks")."</label></td>";
      echo "</tr>";
      echo "</table>";

      Html::closeForm();
   }

   /**
    * Display the column title and allow the sort
    *
    * @param $output_type
    * @param $num
    * @param $title
    * @param $columnname
    * @param bool $sort
    * @return mixed
    */
   function showTitle($output_type, &$num, $title, $columnname, $sort=false) {
      if ($output_type != Search::HTML_OUTPUT ||$sort==false) {
         echo Search::showHeaderItem($output_type, $title, $num);
         return;
      }
      $order = 'ASC';
      $issort = false;
      if (isset($_REQUEST['sort']) && $_REQUEST['sort']==$columnname) {
         $issort = true;
         if (isset($_REQUEST['order']) && $_REQUEST['order']=='ASC') {
            $order = 'DESC';
         }
      }
      $link  = $_SERVER['PHP_SELF'];
      $first = true;
      foreach ($_REQUEST as $name => $value) {
         if (!in_array($name,array('sort','order','PHPSESSID'))) {
            $link .= ($first ? '?' : '&amp;');
            $link .= $name .'='.urlencode($value);
            $first = false;
         }
      }
      $link .= ($first ? '?' : '&amp;').'sort='.urlencode($columnname);
      $link .= '&amp;order='.$order;
      echo Search::showHeaderItem($output_type, $title, $num,
      $link, $issort, ($order=='ASC'?'DESC':'ASC'));
   }

}