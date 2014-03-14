<?php
/*
 * @version $Id: archires.class.php 181 2013-03-12 10:36:28Z yllen $
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

class PluginArchiresArchires extends CommonDBTM {


   static function getTypeName($nb=0) {

      return _n('Network Architecture', 'Network Architectures', $nb, 'archires');
   }

   static function canCreate() {
      return plugin_archires_haveRight('archires', 'w');
   }


   static function canView() {
      return plugin_archires_haveRight('archires', 'r');
   }


   static function showSummary() {

      echo "<div class='center'><table class='tab_cadre' cellpadding='5' width='50%'>";
      echo "<tr><th>".__('Summary')."</th></tr>";


      if (countElementsInTable('glpi_plugin_archires_views',
                               "`entities_id`='".$_SESSION["glpiactive_entity"]."'") > 0) {

         echo "<tr class='tab_bg_1'><td>";
         echo "<a href='view.php'>".PluginArchiresView::getTypeName(2)."</a>";
         echo "</td></tr>";

         echo "<tr class='tab_bg_1'><td>";
         echo "<a href='locationquery.php'>".
                sprintf(__('%1$s - %2$s'), self::getTypeName(1),
                        PluginArchiresLocationQuery::getTypeName(1))."</a>";
         echo "</td></tr>";

         echo "<tr class='tab_bg_1'><td>";
         echo "<a href='networkequipmentquery.php'>".
                sprintf(__('%1$s - %2$s'), self::getTypeName(1),
                        PluginArchiresNetworkEquipmentQuery::getTypeName(1))."</a>";
         echo "</td></tr>";

         $plugin = new Plugin();
         if ($plugin->isActivated("appliances")) {
            echo "<tr class='tab_bg_1'><td>";
            echo "<a href='appliancequery.php'>".
                   sprintf(__('%1$s - %2$s'), self::getTypeName(1),
                           PluginAppliancesAppliance::getTypeName(1))."</a>";
            echo "</td></tr>";
         }
      } else {
         echo "<tr class='tab_bg_1'><td>";
         echo "<a href='view.form.php?new=1'>".__('Add view', 'archires')."</a>";
         echo "</td></tr>";
      }
      echo "</table></div>";
   }


   function showAllItems($myname, $value_type=0, $value=0, $entity_restrict=-1) {
      global $DB,$CFG_GLPI;

      $types = array('Computer','NetworkEquipment','Peripheral','Phone','Printer');
      $rand  = mt_rand();

      echo "<table border='0'><tr><td>\n";

      echo "<select name='type' id='item_type$rand'>\n";
      echo "<option value='0;0'>".Dropdown::EMPTY_VALUE."</option>\n";

      foreach ($types as $type => $label) {
         $item = new $label();
         echo "<option value='".$label.";".getTableForItemType($label."Type")."'>".
               $item->getTypeName()."</option>\n";
      }

      echo "</select>";

      $params = array('typetable'       => '__VALUE__',
                      'value'           => $value,
                      'myname'          => $myname,
                      'entity_restrict' => $entity_restrict);

      Ajax::updateItemOnSelectEvent("item_type$rand", "show_$myname$rand",
                                    $CFG_GLPI["root_doc"]."/plugins/archires/ajax/dropdownAllItems.php",
                                    $params);

      echo "</td><td>\n"	;
      echo "<span id='show_$myname$rand'>&nbsp;</span>\n";
      echo "</td></tr></table>\n";

      if ($value > 0) {
         echo "<script type='text/javascript' >\n";
         echo "document.getElementById('item_type$rand').value='".$value_type."';";
         echo "</script>\n";

         $params["typetable"]=$value_type;
         Ajax::updateItem("show_$myname$rand",
                          $CFG_GLPI["root_doc"]."/plugins/archires/ajax/dropdownAllItems.php",
                          $params);
      }
      return $rand;
   }
}
?>