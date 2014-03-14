<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Immobilizationsheets plugin for GLPI
  Copyright (C) 2003-2011 by the Immobilizationsheets Development Team.

  https://forge.indepnet.net/projects/immobilizationsheets
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Immobilizationsheets.

  Immobilizationsheets is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Immobilizationsheets is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Immobilizationsheets. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginImmobilizationsheetsConfig extends CommonDBTM {

   public static function getTypeName($nb = 0) {

      return _n('Immobilization sheet', 'Immobilization sheets', $nb, 'immobilizationsheets');
   }

   public static function canView() {
      return plugin_immobilizationsheets_haveRight("immobilizationsheets", "r");
   }

   public static function canCreate() {
      return plugin_immobilizationsheets_haveRight("immobilizationsheets", "r");
   }

   function showForm($ID) {
      global $CFG_GLPI;

      echo "<form name='form' method='post' action='".$CFG_GLPI["root_doc"]."/plugins/immobilizationsheets/front/immobilizationsheet.php'>";
      echo "<div align=\"center\">";
      echo "<table class=\"tab_cadre\"  cellspacing=\"2\" cellpadding=\"2\">";
      echo "<tr><th>".__('Generation of immobilization sheet', 'immobilizationsheets')."</th></tr>";
      echo "<tr>";
      echo "<td class='tab_bg_2 center'>";
      $immo_item = new PluginImmobilizationsheetsItem();
      Dropdown::showAllItems("item_item", 0, 0, -1, $immo_item->getTypes());
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".__s('Post')."\" >";
      echo "</td>";
      echo "</tr>";
      echo "</table></div>";
      Html::closeForm();
   }

   function showconfigForm() {
      global $CFG_GLPI;

      echo "<form name='form' method='post' action='".$CFG_GLPI["root_doc"]."/plugins/immobilizationsheets/front/config.form.php'>";
      echo "<div align=\"center\">";
      echo "<table class=\"tab_cadre_fixe\"  cellspacing=\"2\" cellpadding=\"2\">";
      echo "<tr><th colspan=\"2\">".__('Options', 'immobilizationsheets')."</th></tr>";
      echo "<tr class='tab_bg_1 top'>";
      echo "<td>".__('Save sheets in GLPI', 'immobilizationsheets').": </td>";
      echo "<td>";
      Dropdown::showYesNo("use_backup", $this->fields["use_backup"]);
      echo "</td>";
      echo "<tr class='tab_bg_1 top'><td>";
      echo __('Default Heading for sheets', 'immobilizationsheets').": </td>";
      echo "<td>";
      Dropdown::show('DocumentCategory', array('name' => "documentcategories_id",
          'value' => $this->fields["documentcategories_id"]));
      echo "</td>";
      echo "<tr><th colspan='2'>";
      echo "<input type='hidden' name='id' value='1'>";
      echo "<input type=\"submit\" name=\"update_config\" class=\"submit\" value=\"".__s('Post')."\" >";
      echo "</th></tr>";
      echo "</table></div>";
      Html::closeForm();
      echo "<br>";
   }

}

?>