<?php
/*
 * @version $Id: preference.class.php 340 2013-07-24 14:18:27Z yllen $
 -------------------------------------------------------------------------
 pdf - Export to PDF plugin for GLPI
 Copyright (C) 2003-2013 by the pdf Development Team.

 https://forge.indepnet.net/projects/pdf
 -------------------------------------------------------------------------

 LICENSE

 This file is part of pdf.

 pdf is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 pdf is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with pdf. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// Original Author of file: BALPE Dévi
// ----------------------------------------------------------------------

class PluginPdfPreference extends CommonDBTM {


   static function showPreferences() {
      global $DB, $CFG_GLPI, $PLUGIN_HOOKS;

      $target = Toolbox::getItemTypeFormURL(__CLASS__);
      $pref   = new self();

      echo "<div class='center' id='pdf_type'>";
      foreach ($PLUGIN_HOOKS['plugin_pdf'] as $type => $plug) {
         if (!($item = getItemForItemtype($type))) {
            continue;
         }
         if ($item->canView()) {
            $pref->menu($item, $target);
         }
      }
      echo "</div>";
   }


   /**
    * @param $num
    * @param $label
    * @param $checked   (false by default)
   **/
   function checkbox($num,$label,$checked=false) {

       echo "<td width='20%'><input type='checkbox' ".($checked==true?"checked='checked'":'').
             " name='item[$num]' value='1'>&nbsp;".$label."</td>";
    }


    /**
     * @param $item
     * @param $action
    **/
   function menu($item, $action) {
      global $DB, $PLUGIN_HOOKS;

      $type = $item->getType();

      // $ID set if current object, not set from preference
      if (isset($item->fields['id'])) {
         $ID = $item->fields['id'];
      } else {
         $ID = 0;
         $item->getEmpty();
      }

      if (!isset($PLUGIN_HOOKS['plugin_pdf'][$type])
          || !class_exists($PLUGIN_HOOKS['plugin_pdf'][$type])) {
         return;
      }
      $itempdf = new $PLUGIN_HOOKS['plugin_pdf'][$type]($item);
      $options = $itempdf->defineAllTabs();

      $formid="plugin_pdf_${type}_".mt_rand();
      echo "<form name='".$formid."' id='".$formid."' action='$action' method='post' ".
             ($ID ? "target='_blank'" : "")."><table class='tab_cadre_fixe'>";

      $landscape = false;
      $values    = array();

      $sql = "SELECT `tabref`
              FROM `".$this->getTable()."`
              WHERE `users_ID` = '" . $_SESSION['glpiID'] . "'
                    AND `itemtype` = '$type'";

      foreach ($DB->request($sql) AS $data) {
         if ($data["tabref"]=='landscape') {
            $landscape = true;
         } else {
            $values[$data["tabref"]] = $data["tabref"];
         }
      }
      // Always export, at least, main part.
      if (!count($values) && isset($options['_main_'])) {
         $values['_main_'] = 1;
      }
      echo "<tr><th colspan='6'>".sprintf(__('%1$s: %2$s'),
                                          __('Choose the tables to print in pdf', 'pdf'),
                                          $item->getTypeName());
      echo "</th></tr>";

      $i = 0;
      foreach ($options as $num => $title) {
         if (!$i) {
            echo "<tr class='tab_bg_1'>";
         }
         if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
            $title = "$title ($num)";
         }
         $this->checkbox($num, $title, (isset($values[$num]) ? true : false));
         if ($i == 4) {
            echo "</tr>";
            $i = 0;
         } else {
            $i++;
         }
      }
      if ($i) {
         while ($i <= 4) {
            echo "<td width='20%'>&nbsp;</td>";
            $i++;
         }
         echo "</tr>";
      }

      echo "<tr class='tab_bg_2'><td colspan='2' class='left'>";
      echo "<a onclick=\"if (markCheckboxes('".$formid."') ) return false;\" href='".
           $_SERVER['PHP_SELF']."?select=all'>".__('Check all')."</a> / ";
      echo "<a onclick=\"if (unMarkCheckboxes('".$formid."') ) return false;\" href='".
           $_SERVER['PHP_SELF']."?select=none'>".__('Uncheck all')."</a></td>";

      echo "<td colspan='4' class='center'>";
      echo "<input type='hidden' name='plugin_pdf_inventory_type' value='".$type."'>";
      echo "<input type='hidden' name='indice' value='".count($options)."'>";

      if ($ID) {
         printf(__('Display %d items', 'pdf'), Dropdown::showListLimit());
      }
      echo "<select name='page'>\n";
      echo "<option value='0'>".__('Portrait', 'pdf')."</option>\n"; // Portrait
      echo "<option value='1'".($landscape?"selected='selected'":'').">".__('Landscape', 'pdf').
           "</option>\n"; // Paysage
      echo "</select>&nbsp;&nbsp;&nbsp;&nbsp;\n";

      if ($ID) {
         echo "<input type='hidden' name='itemID' value='".$ID."'>";
         echo "<input type='submit' value='". _sx('button','Print', 'pdf') .
              "' name='generate' class='submit'></td></tr>";
      } else {
         echo "<input type='submit' value='" . _sx('button', 'Save') .
              "' name='plugin_pdf_user_preferences_save' class='submit'></td></tr>";
      }
      echo "</table>";
      Html::closeForm();
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (($item->getType() == 'Preference')
          && isset($_SESSION['glpi_plugin_pdf_profile'])
          && $_SESSION['glpi_plugin_pdf_profile']['use']) {
         return __('Print to pdf', 'pdf');
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Preference') {
         self::showPreferences();
      }
      return true;
   }
}
?>