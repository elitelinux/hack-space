<?php
/*
 * @version $Id: imageitem.class.php 185 2013-03-13 12:57:43Z yllen $
 -------------------------------------------------------------------------
 Archires plugin for GLPI
 Copyright (C) 2003-2013 by the archires Development Team.

 https://forge.indepnet.net/projects/archires
 -------------------------------------------------------------------------

 LICENSE

 This file is part of archires.

 Archires is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Archires is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Archires. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginArchiresImageItem extends CommonDBTM {


   static function canCreate() {
      return plugin_archires_haveRight('archires', 'w');
   }


   static function canView() {
      return plugin_archires_haveRight('archires', 'r');
   }


   function getFromDBbyType($itemtype, $type) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `itemtype` = '$itemtype'
                      AND `type` = '$type'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
      }
      return false;
   }


   function addItemImage($type,$itemtype,$img) {
      global $DB;

      if ($type != '-1') {
         if ($this->GetfromDBbyType($itemtype,$type)) {
            $this->update(array('id'  => $this->fields['id'],
                                'img' => $img));
         } else {
            $this->add(array('itemtype' => $itemtype,
                             'type'     => $type,
                             'img'      => $img));
         }
      } else {
         $query  = "SELECT *
                    FROM `".getTableForItemType($itemtype."Type")."` ";

         $result = $DB->query($query);
         $number = $DB->numrows($result);
         $i      = 0;
         while ($i < $number) {
            $type_table = $DB->result($result, $i, "id");
            if ($this->GetfromDBbyType($itemtype,$type_table)) {
            $this->update(array('id'  => $this->fields['id'],
                                'img' => $img));
           } else {
             $this->add(array('itemtype' => $itemtype,
                              'type'     => $type_table,
                              'img'      => $img));
            }
            $i++;
         }
      }
   }


   function showConfigForm() {
      global $DB, $CFG_GLPI;

      echo "<form method='post' action='./config.form.php'>";
      echo "<table class='tab_cadre' cellpadding='5'>";
      echo "<tr><th colspan='4'>".__('Associate pictures with item types', 'archires')."</th></tr>";
      echo "<tr class='tab_bg_1'><td>";
      $PluginArchiresArchires = new PluginArchiresArchires();
      $PluginArchiresArchires->showAllItems("type",0,0,$_SESSION["glpiactive_entity"]);
      echo "</td><td>";
      //file
      $rep = "../pics/";
      $dir = opendir($rep);
      echo "<select name=\"img\">";
      while ($f = readdir($dir)) {
         if (is_file($rep.$f)) {
            echo "<option value='".$f."'>".$f."</option>";
         }
      }
      echo "</select>&nbsp;";
      closedir($dir);
      Html::showToolTip(nl2br(__('Some types of items must be created so that the association can exist',
                                 'archires')));
      echo "<td>";
      echo "<div class='center'><input type='submit' name='add' value=\""._sx('button', 'Add').
            "\" class='submit'></div></td></tr>";
      echo "</table>";
      Html::closeForm();

      $query = "SELECT *
                FROM `".$this->getTable()."`
                ORDER BY `itemtype`,`type` ASC;";

      $i = 0;
      if ($result = $DB->query($query)) {
         $number = $DB->numrows($result);
         if ($number != 0) {
            echo "<form method='post' name='massiveaction_form' id='massiveaction_form' action='".
                  "./config.form.php'>";
            echo "<div id='liste'>";
            echo "<table class='tab_cadre' cellpadding='5'>";
            echo "<tr>";
            echo "<th class='left'>".__('Item')."</th>";
            echo "<th class='left'>".__('Item type')."</th>";
            echo "<th class='left'>".__('Picture', 'archires')."</th><th></th>";
            if ($number > 1) {
               echo "<th class='left'>".__('Item')."</th>";
               echo "<th class='left'>".__('Item type')."</th>";
               echo "<th class='left'>".__('Picture', 'archires')."</th><th></th>";
            }
            echo "</tr>";

            while($ligne = $DB->fetch_assoc($result)) {
               $ID = $ligne["id"];
               if (($i % 2 == 0)
                   && ($number > 1)) {
                  echo "<tr class='tab_bg_1'>";
               }
               if ($number == 1) {
                  echo "<tr class='tab_bg_1'>";
               }
               $PluginArchiresArchires = new PluginArchiresArchires();
               $item                   = new $ligne["itemtype"]();
               echo "<td>".$item->getTypeName()."</td>";
               $class     = $ligne["itemtype"]."Type";
               $typeclass = new $class();
               $typeclass->getFromDB($ligne["type"]);
               echo "<td>".$typeclass->fields["name"]."</td>";
               echo "<td><img src=\"".$CFG_GLPI["root_doc"]."/plugins/archires/pics/".$ligne["img"].
                           "\" alt=\"".$ligne["img"]."\" title=\"".$ligne["img"]."\"></td>";
               echo "<td>";
               echo "<input type='hidden' name='id' value='$ID'>";
               echo "<input type='checkbox' name='item[$ID]' value='1'>";
               echo "</td>";

               $i++;
               if (($i == $number) && (($number % 2) !=0) && $number > 1) {
                  echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
               }
            }

            echo "<tr class='tab_bg_1'>";

            if ($number > 1) {
               echo "<td colspan='8' class='center'>";
            } else {
               echo "<td colspan='4' class='center'>";
            }
            echo "<a onclick= \"if (markCheckboxes ('massiveaction_form')) return false;\"
                  href='#'>".__('Select all')."</a>";
            echo " - <a onclick= \"if ( unMarkCheckboxes ('massiveaction_form') ) return false;\"
                  href='#'>".__('Deselect all')."</a> ";
            Html::closeArrowMassives(array('delete' => _sx('button', 'Delete permanently')));
            echo "</div>";
            Html::closeForm();
         }
      }
   }


   function displayItemImage($type,$itemtype,$test) {
      global $DB;

      $path = "";
      if ($test)
         $path="../";

      $image_name = $path."pics/nothing.png";
      $query = "SELECT *
                FROM `glpi_plugin_archires_imageitems`
                WHERE `itemtype` = '$itemtype'";

      if ($result = $DB->query($query)) {
         while ($ligne= $DB->fetch_array($result)) {
            $config_img = $ligne["img"];
            if ($type == $ligne["type"]) {
               $image_name = $path."pics/$config_img";
            }
         }
      }
      return $image_name;
   }

}
?>