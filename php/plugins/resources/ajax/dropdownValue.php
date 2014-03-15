<?php
/*
 * @version $Id: dropdownValue.php 480 2012-11-09 tsmr $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"dropdownValue.php")) {
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

// Security
if (!($item = getItemForItemtype($_POST['itemtype']))) {
   exit();
}

$table = $item->getTable();

$displaywith = false;

if (isset($_POST['displaywith'])
    && is_array($_POST['displaywith'])
    && count($_POST['displaywith'])) {

   $displaywith = true;
}

// No define value
if (!isset($_POST['value'])) {
   $_POST['value'] = '';
}

// No define rand
if (!isset($_POST['rand'])) {
   $_POST['rand'] = mt_rand();
}

if (isset($_POST['condition']) && !empty($_POST['condition'])) {
   $_POST['condition'] = rawurldecode(stripslashes($_POST['condition']));
}

if (!isset($_POST['emptylabel']) || $_POST['emptylabel'] == '') {
   $_POST['emptylabel'] = Dropdown::EMPTY_VALUE;
}

if (!isset($_POST['display_rootentity'])) {
   $_POST['display_rootentity'] = false;
}

if (isset($_POST["entity_restrict"])
    && !is_numeric($_POST["entity_restrict"])
    && !is_array($_POST["entity_restrict"])) {

   $_POST["entity_restrict"] = Toolbox::decodeArrayFromInput($_POST["entity_restrict"]);
}

// Make a select box with preselected values
if (!isset($_POST["limit"])) {
   $_POST["limit"] = $_SESSION["glpidropdown_chars_limit"];
}

$where = "WHERE 1 ";

if ($item->maybeDeleted()) {
   $where .= " AND `is_deleted` = '0' ";
}
if ($item->maybeTemplate()) {
   $where .= " AND `is_template` = '0' ";
}

$NBMAX = $CFG_GLPI["dropdown_max"];
$LIMIT = "LIMIT 0,$NBMAX";

if ($_POST['searchText']==$CFG_GLPI["ajax_wildcard"]) {
   $LIMIT = "";
}

$where .=" AND `$table`.`id` NOT IN ('".$_POST['value']."'";

if (isset($_POST['used'])) {

   if (is_array($_POST['used'])) {
      $used = $_POST['used'];
   } else {
      $used = Toolbox::decodeArrayFromInput($_POST['used']);
   }

   if (count($used)) {
      $where .= ",'".implode("','",$used)."'";
   }
}

if (isset($_POST['toadd'])) {
   if (is_array($_POST['toadd'])) {
      $toadd = $_POST['toadd'];
   } else {
      $toadd = Toolbox::decodeArrayFromInput($_POST['toadd']);
   }
} else {
   $toadd = array();
}

$where .= ") ";

if (isset($_POST['condition']) && $_POST['condition'] != '') {
   $where .= " AND ".$_POST['condition']." ";
}

if ($item instanceof CommonTreeDropdown) {

   if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"]) {
      $where .= " AND `completename` ".Search::makeTextSearch($_POST['searchText']);
   }
   $multi = false;

   // Manage multiple Entities dropdowns
   $add_order = "";

   if ($item->isEntityAssign()) {
      $recur = $item->maybeRecursive();

       // Entities are not really recursive : do not display parents
      if ($_POST['itemtype'] == 'Entity') {
         $recur = false;
      }

      if (isset($_POST["entity_restrict"]) && !($_POST["entity_restrict"]<0)) {
         $where .= getEntitiesRestrictRequest(" AND ", $table, '', $_POST["entity_restrict"],
                                              $recur);

         if (is_array($_POST["entity_restrict"]) && count($_POST["entity_restrict"])>1) {
            $multi = true;
         }

      } else {
         $where .= getEntitiesRestrictRequest(" AND ", $table, '', '', $recur);

         if (count($_SESSION['glpiactiveentities'])>1) {
            $multi = true;
         }
      }

      // Force recursive items to multi entity view
      if ($recur) {
         $multi = true;
      }

      // no multi view for entitites
      if ($_POST['itemtype']=="Entity") {
         $multi = false;
      }

      if ($multi) {
         $add_order = '`entities_id`, ';
      }

   }

   $query = "SELECT *
             FROM `$table`
             $where
             ORDER BY $add_order `completename`
             $LIMIT";

   if ($result = $DB->query($query)) {
      echo "<select id='dropdown_".$_POST["myname"].$_POST["rand"]."' name='".$_POST['myname']."'
             size='1'";

      if (isset($_POST["on_change"]) && !empty($_POST["on_change"])) {
         echo " onChange='".$_POST["on_change"]."'";
      }
      echo ">";

      if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"] && $DB->numrows($result)==$NBMAX) {
         echo "<option class='tree' value='0'>--".__('Limited view')."--</option>";
      }

      if (count($toadd)) {
         foreach ($toadd as $key => $val) {
            echo "<option class='tree' ".($_POST['value']==$key?'selected':'').
                 " value='$key' title=\"".Html::cleanInputText($val)."\">".
                  Toolbox::substr($val, 0, $_POST["limit"])."</option>";
         }
      }

      $display_selected = true;

      switch ($table) {
         case "glpi_entities" :
            // If entity=0 allowed
            if (isset($_POST["entity_restrict"])
                && (($_POST["entity_restrict"]<=0 && in_array(0, $_SESSION['glpiactiveentities']))
                    || (is_array($_POST["entity_restrict"])
                        && in_array(0, $_POST["entity_restrict"])))) {

               echo "<option class='tree' value='0'>".__('Root entity')."</option>";

               // Entity=0 already add above
               if ($_POST['value']==0 && !$_POST['display_rootentity']) {
                  $display_selected = false;
               }
            }
            break;

         default :
            if ($_POST['display_emptychoice']) {
               echo "<option class='tree' value='0'>".$_POST['emptylabel']."</option>";
            }
      }

      if ($display_selected) {
         $outputval = Dropdown::getDropdownName($table, $_POST['value']);

         if (Toolbox::strlen($outputval)!=0 && $outputval!="&nbsp;") {

            if (Toolbox::strlen($outputval)>$_POST["limit"]) {
               // Completename for tree dropdown : keep right
               $outputval = "&hellip;".Toolbox::substr($outputval, -$_POST["limit"]);
            }
            if ($_SESSION["glpiis_ids_visible"] || Toolbox::strlen($outputval)==0) {
               $outputval .= " (".$_POST['value'].")";
            }
            echo "<option class='tree' selected value='".$_POST['value']."'>".$outputval."</option>";
         }
      }

      $last_level_displayed = array();

      if ($DB->numrows($result)) {
         $prev = -1;

         while ($data =$DB->fetch_array($result)) {
            $ID     = $data['id'];
            $level  = $data['level'];
            $output = $data['name'];

            if ($displaywith) {
               foreach ($_POST['displaywith'] as $key) {
                  if (isset($data[$key]) && strlen($data[$key])!=0) {
                     $output .= " - ".$data[$key];
                  }
               }
            }

            if ($multi && $data["entities_id"]!=$prev) {
               if ($prev>=0) {
                  echo "</optgroup>";
               }
               $prev = $data["entities_id"];
               echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
               // Reset last level displayed :
               $last_level_displayed = array();
            }

            $class = " class='tree' ";
            $raquo = "&raquo;";

            if ($level==1) {
               $class = " class='treeroot'";
               $raquo = "";
            }

            if ($_SESSION['glpiuse_flat_dropdowntree']) {
               $output = $data['completename'];
               if ($level>1) {
                  $class = "";
                  $raquo = "";
                  $level = 0;
               }

            } else { // Need to check if parent is the good one
               if ($level>1) {
                  // Last parent is not the good one need to display arbo
                  if (!isset($last_level_displayed[$level-1])
                      || $last_level_displayed[$level-1] != $data[$item->getForeignKeyField()]) {

                     $work_level    = $level-1;
                     $work_parentID = $data[$item->getForeignKeyField()];
                     $to_display    = '';

                     do {
                        // Get parent
                        if ($item->getFromDB($work_parentID)) {
                           $addcomment = "";

                           if (isset($item->fields["comment"])) {
                              $addcomment = " - ".$item->fields["comment"];
                           }
                           $output2 = $item->getName();
                           if (Toolbox::strlen($output2)>$_POST["limit"]) {
                              $output2 = Toolbox::substr($output2, 0 ,$_POST["limit"])."&hellip;";
                           }

                           $class2 = " class='tree' ";
                           $raquo2 = "&raquo;";

                           if ($work_level==1) {
                              $class2 = " class='treeroot'";
                              $raquo2 = "";
                           }

                           $to_display = "<option disabled value='$work_parentID' $class2
                                           title=\"".Html::cleanInputText($item->fields['completename'].
                                             $addcomment)."\">".
                                         str_repeat("&nbsp;&nbsp;&nbsp;", $work_level).
                                         $raquo2.$output2."</option>".$to_display;

                           $last_level_displayed[$work_level] = $item->fields['id'];
                           $work_level--;
                           $work_parentID = $item->fields[$item->getForeignKeyField()];

                        } else { // Error getting item : stop
                           $work_level = -1;
                        }

                     } while ($work_level > 1
                              && (!isset($last_level_displayed[$work_level])
                                  || $last_level_displayed[$work_level] != $work_parentID));

                     echo $to_display;
                  }
               }
               $last_level_displayed[$level] = $data['id'];
            }

            if (Toolbox::strlen($output)>$_POST["limit"]) {

               if ($_SESSION['glpiuse_flat_dropdowntree']) {
                  $output = "&hellip;".Toolbox::substr($output, -$_POST["limit"]);
               } else {
                  $output = Toolbox::substr($output, 0, $_POST["limit"])."&hellip;";
               }
            }

            if ($_SESSION["glpiis_ids_visible"] || Toolbox::strlen($output)==0) {
               $output .= " ($ID)";
            }
            $addcomment = "";

            if (isset($data["comment"])) {
               $addcomment = " - ".$data["comment"];
            }
            echo "<option value='$ID' $class title=\"".Html::cleanInputText($data['completename'].
                   $addcomment)."\">".str_repeat("&nbsp;&nbsp;&nbsp;", $level).$raquo.$output.
                 "</option>";
         }
         if ($multi) {
            echo "</optgroup>";
         }
      }
      echo "</select>";
   }

} else { // Not a dropdowntree
   $multi = false;

   if ($item->isEntityAssign()) {
      $multi = $item->maybeRecursive();

      if (isset($_POST["entity_restrict"]) && !($_POST["entity_restrict"]<0)) {
         $where .= getEntitiesRestrictRequest("AND", $table, "entities_id",
                                              $_POST["entity_restrict"], $multi);

         if (is_array($_POST["entity_restrict"]) && count($_POST["entity_restrict"])>1) {
            $multi = true;
         }

      } else {
         $where .= getEntitiesRestrictRequest("AND", $table, '', '', $multi);

         if (count($_SESSION['glpiactiveentities'])>1) {
            $multi = true;
         }
      }
   }

   $field = "name";

   if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"]) {
      $search = Search::makeTextSearch($_POST['searchText']);
      $where .=" AND  (`$table`.`$field` ".$search;
      $where .= ')';
   }

   switch ($_POST['itemtype']) {

      default :
         $query = "SELECT *
                   FROM `$table`
                   $where";
   }

   if ($multi) {
      $query .= " ORDER BY `entities_id`, $field
                 $LIMIT";
   } else {
      $query .= " ORDER BY $field
                 $LIMIT";
   }

   if ($result = $DB->query($query)) {
      echo "<select id='dropdown_".$_POST["myname"].$_POST["rand"]."' name='".$_POST['myname']."'
             size='1'";

      if (isset($_POST["on_change"]) && !empty($_POST["on_change"])) {
         echo " onChange='".$_POST["on_change"]."'";
      }

      echo ">";

      if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"] && $DB->numrows($result)==$NBMAX) {
         echo "<option value='0'>--".__('Limited view')."--</option>";

      } else if (!isset($_POST['display_emptychoice']) || $_POST['display_emptychoice']) {
         echo "<option value='0'>".$_POST["emptylabel"]."</option>";
      }

      if (count($toadd)) {
         foreach ($toadd as $key => $val) {
            echo "<option title=\"".Html::cleanInputText($val)."\" value='$key' ".
                  ($_POST['value']==$key?'selected':'').">".
                  Toolbox::substr($val, 0, $_POST["limit"])."</option>";
         }
      }

      $output = Dropdown::getDropdownName($table,$_POST['value']);

      if (strlen($output)!=0 && $output!="&nbsp;") {
         if ($_SESSION["glpiis_ids_visible"]) {
            $output .= " (".$_POST['value'].")";
         }
         echo "<option selected value='".$_POST['value']."'>".$output."</option>";
      }

      if ($DB->numrows($result)) {
         $prev = -1;

         while ($data =$DB->fetch_array($result)) {
            $output = $data[$field];

            if ($displaywith) {
               foreach ($_POST['displaywith'] as $key) {
                  if (isset($data[$key]) && strlen($data[$key])!=0) {
                     $output .= " - ".$data[$key];
                  }
               }
            }
            $ID = $data['id'];
            $addcomment = "";

            if (isset($data["comment"])) {
               $addcomment = " - ".$data["comment"];
            }
            if ($_SESSION["glpiis_ids_visible"] || strlen($output)==0) {
               $output .= " ($ID)";
            }

            if ($multi && $data["entities_id"]!=$prev) {
               if ($prev>=0) {
                  echo "</optgroup>";
               }
               $prev = $data["entities_id"];
               echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
            }

            echo "<option value='$ID' title=\"".Html::cleanInputText($output.$addcomment)."\">".
                  Toolbox::substr($output, 0, $_POST["limit"])."</option>";
         }

         if ($multi) {
            echo "</optgroup>";
         }
      }
      echo "</select>";
   }
}

if (isset($_POST["comment"]) && $_POST["comment"]) {
   $paramscomment = array('value' => '__VALUE__',
                          'table' => $table);

   Ajax::updateItemOnSelectEvent("dropdown_".$_POST["myname"].$_POST["rand"],
                                 "comment_".$_POST["myname"].$_POST["rand"],
                                 $CFG_GLPI["root_doc"]."/ajax/comments.php", $paramscomment);
}

if (isset($_POST["action"]) && $_POST["action"]) {

   
   $sort = false;
   if(isset($_POST['sort']) && !empty($_POST['sort'])){
      $sort = $_POST['sort'];
   }
      
   $params=array($_POST['myname'] => '__VALUE__',
                       'entity_restrict' => $_POST['entity_restrict'],
                       'rand' => $_POST['rand'],
                       'sort' => $sort);
                       
   Ajax::updateItemOnSelectEvent("dropdown_".$_POST["myname"].$_POST["rand"], $_POST['span'],
                                     $_POST['action'],
                                     $params);

}

Ajax::commonDropdownUpdateItem($_POST);
?>
