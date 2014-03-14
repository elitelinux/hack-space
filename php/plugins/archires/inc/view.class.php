<?php
/*
 * @version $Id: view.class.php 188 2013-08-03 13:09:35Z tsmr $
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

class PluginArchiresView extends CommonDBTM {


   const PLUGIN_ARCHIRES_NETWORK_COLOR = 0;
   const PLUGIN_ARCHIRES_VLAN_COLOR    = 1;

   const PLUGIN_ARCHIRES_JPEG_FORMAT = 0;
   const PLUGIN_ARCHIRES_PNG_FORMAT  = 1;
   const PLUGIN_ARCHIRES_GIF_FORMAT  = 2;
   const PLUGIN_ARCHIRES_SVG_FORMAT  = 3;


   static function getTypeName($nb=0) {
      return _n('View', 'Views', $nb);
   }


   static function canCreate() {
      return plugin_archires_haveRight('archires', 'w');
   }


   static function canView() {
      return plugin_archires_haveRight('archires', 'r');
   }


   function getSearchOptions() {

      $tab = array();

      $tab['common']             = self::getTypeName();

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();

      $tab[2]['table']           = $this->getTable();
      $tab[2]['field']           = 'computer';
      $tab[2]['name']            = _n('Computer', 'Computers', 2);
      $tab[2]['datatype']        = 'bool';

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'networking';
      $tab[3]['name']            = _n('Network equipment', 'Network equipments', 2, 'archires');
      $tab[3]['datatype']        = 'bool';

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'printer';
      $tab[4]['name']            = _n('Printer', 'Printers', 2);
      $tab[4]['datatype']        = 'bool';

      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'peripheral';
      $tab[5]['name']            = _n('Device', 'Devices', 2);
      $tab[5]['datatype']        = 'bool';

      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'phone';
      $tab[6]['name']            = _n('Phone', 'Phones', 2);
      $tab[6]['datatype']        = 'bool';

      $tab[7]['table']           = $this->getTable();
      $tab[7]['field']           = 'display_ports';
      $tab[7]['name']            = __('Display sockets', 'archires');
      $tab[7]['datatype']        = 'text';

      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'display_ip';
      $tab[8]['name']            = __('Display IP/Mask', 'archires');
      $tab[8]['datatype']        = 'bool';

      $tab[9]['table']           = $this->getTable();
      $tab[9]['field']           = 'display_type';
      $tab[9]['name']            = __('Display item types', 'archires');
      $tab[9]['datatype']        = 'bool';

      $tab[10]['table']          = $this->getTable();
      $tab[10]['field']          = 'display_state';
      $tab[10]['name']           = __('Display item statuses', 'archires');
      $tab[10]['datatype']       = 'bool';

      $tab[11]['table']          = $this->getTable();
      $tab[11]['field']          = 'display_location';
      $tab[11]['name']           = __('Display item locations', 'archires');
      $tab[11]['datatype']       = 'bool';

      $tab[12]['table']          = $this->getTable();
      $tab[12]['field']          = 'display_entity';
      $tab[12]['name']           = __('Display item entities', 'archires');
      $tab[12]['datatype']       = 'bool';

      $tab[13]['table']          = $this->getTable();
      $tab[13]['field']          = 'engine';
      $tab[13]['name']           = __('Rendering engine', 'archires');

      $tab[14]['table']          = $this->getTable();
      $tab[14]['field']          = 'format';
      $tab[14]['name']           = __('Image format', 'archires');

      $tab[15]['table']          = $this->getTable();
      $tab[15]['field']          = 'color';
      $tab[15]['name']           = __('Color', 'archires');

      return $tab;
   }


   function dropdownObject($obj) {
      global $DB;

      $ID = $obj->fields["id"];

      $query = "SELECT `id`, `name`
                FROM `".$obj->getTable()."`
                WHERE `is_deleted` = '0' ";
      // Add Restrict to current entities
      if ($obj->isEntityAssign()) {
         $query .= getEntitiesRestrictRequest(" AND",$obj->getTable());
      }
      $query.=" ORDER BY `name` ASC";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) > 0) {
            echo "<select name='plugin_archires_queries_id' size='1'> ";
            while ($ligne = $DB->fetch_array($result)) {
               echo "<option value='".$ligne["id"]."' ".(($ligne["id"] == "".$ID."")?" selected ":"").">".
                     $ligne["name"]."</option>";
            }
            echo "</select>";
         }
      }
   }


   function dropdownView($obj,$default) {
      global $DB;

      if (isset($obj->fields["id"])) {
         $default = $obj->fields["plugin_archires_views_id"];
      }
      $query = "SELECT `id`, `name`
                FROM `".$this->getTable()."`
                WHERE `is_deleted` = '0'
                      AND `entities_id` = '" . $_SESSION["glpiactive_entity"] . "'
                ORDER BY `name` ASC";

      echo "<select name='plugin_archires_views_id' size='1'> ";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>\n";
      if ($result = $DB->query($query)) {
         while ($ligne= $DB->fetch_array($result)) {
            $view_name = $ligne["name"];
            $view_id   = $ligne["id"];
            echo "<option value='".$view_id."' ".($view_id=="".$default.""?" selected ":"").">".
                  $view_name."</option>";
         }
      }
      echo "</select>";
   }


   static function linkToAllViews($item) {

      echo "<div class='center'>";
      echo "<a href=\"./archires.graph.php?id=".$item->getID()."&querytype=".$item->getType()."\">".
             __('See all views', 'archires');
      echo "</a></div>";
   }


   function viewSelect($obj,$plugin_archires_views_id,$select=0) {
      global $CFG_GLPI,$DB;

      $querytype   = get_class($obj);
      $ID          = $obj->fields["id"];
      $object_view = $obj->fields["plugin_archires_views_id"];
      if (!isset($plugin_archires_views_id)) {
         $plugin_archires_views_id = $object_view;
      }
      if ($select) {
         // display only
         echo "<form method='get' name='selecting' action='".$CFG_GLPI["root_doc"].
               "/plugins/archires/front/archires.graph.php'>";
         echo "<table class='tab_cadre' cellpadding='5'>";
         echo "<tr class='tab_bg_1'>";

         echo "<td class='center'>".__('Display', 'archires');
         $this->dropdownObject($obj);
         echo "</td>";

         echo "<td class='center'>".self::getTypeName(2);
         $this->dropdownView(-1, $plugin_archires_views_id);
         echo "</td>";

         echo "<td>";
         echo "<input type='hidden' name='querytype' value=\"".$querytype."\"> ";
         echo "<input type='submit' class='submit'  name='displayview' value=\"".
                _sx('button', 'Post')."\"> ";
         echo "</td>";
         echo "</tr>";
         echo "</table>";
         Html::closeForm();
      }
      echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/archires/front/archires.map.php?format=".
            self::PLUGIN_ARCHIRES_SVG_FORMAT."&amp;id=".$ID."&amp;querytype=".$querytype.
            "&amp;plugin_archires_views_id=".
            $plugin_archires_views_id."'>[".__('SVG')."]</a>";
   }


   function showForm ($ID, $options=array()) {

      $this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='1'>".__('Name')."</td>";
      echo "<td colspan='3'>";
      Html::autocompletionTextField($this, "name", array('size' => 20));
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><th colspan='4'>".__('Display of items', 'archires')."</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>"._n('Computer', 'Computers', 2)."</td>";
      echo "<td>";
      Dropdown::showYesNo("computer",$this->fields["computer"]);
      echo "</td>";
      echo "<td>"._n('Network equipment', 'Network equipments', 2, 'archires')."</td>";
      echo "<td>";
      Dropdown::showYesNo("networking",$this->fields["networking"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>"._n('Printer', 'Printers', 2)."</td>";
      echo "<td>";
      Dropdown::showYesNo("printer",$this->fields["printer"]);
      echo "</td>";
      echo "<td>"._n('Device', 'Devices', 2)."</td>";
      echo "<td>";
      Dropdown::showYesNo("peripheral",$this->fields["peripheral"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>"._n('Phone', 'Phones', 2)."</td>";
      echo "<td colspan='3'>";
      Dropdown::showYesNo("phone",$this->fields["phone"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><th colspan='4'>".__('Display description', 'archires')."</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Display sockets', 'archires')."</td>";
      echo "<td><select name='display_ports'> ";
      echo "<option ";
      if ($this->fields["display_ports"] == '0') {
         echo "selected ";
      }
      echo "value='0'>".__('No')."</option>";
      echo "<option ";
      if ($this->fields["display_ports"] == '1') {
         echo "selected ";
      }
      echo "value='1'>".__('See numbers', 'archires')."</option>";
      echo "<option ";
      if ($this->fields["display_ports"] == '2') {
         echo "selected ";
      }
      echo "value='2'>".__('See names', 'archires')."</option>";
      echo "</select></td>";
      echo "<td>".__('Display IP/Mask', 'archires')."</td>";
      echo "<td>";
      Dropdown::showYesNo("display_ip",$this->fields["display_ip"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Display item types', 'archires')."</td>";
      echo "<td>";
      Dropdown::showYesNo("display_type",$this->fields["display_type"]);
      echo "</td>";
      echo "<td>".__('Display item statuses', 'archires')."</td>";
      echo "<td>";
      Dropdown::showYesNo("display_state",$this->fields["display_state"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Display item locations', 'archires')."</td>";
      echo "<td>";
      Dropdown::showYesNo("display_location",$this->fields["display_location"]);
      echo "</td>";
      echo "<td>".__('Display item entities', 'archires')."</td>";
      echo "<td>";
      Dropdown::showYesNo("display_entity",$this->fields["display_entity"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><th colspan='4'>".__('Generation', 'archires')."</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Rendering engine', 'archires')."</td>";
      echo "<td><select name='engine'> ";
      echo "<option ";
      if ($this->fields["engine"] == '0') {
         echo "selected ";
      }
      echo "value='0'>Dot</option>";
      echo "<option ";
      if ($this->fields["engine"] == '1') {
         echo "selected ";
      }
      echo "value='1'>Neato</option>";
      echo "</select>&nbsp;";
      Html::showToolTip(nl2br(__('With neato, the sockets will not be displayed', 'archires')));
      echo "</td>";
      echo "<td>".__('Image format', 'archires')."</td>";
      echo "<td><select name='format'> ";
      echo "<option ";
      if ($this->fields["format"] == self::PLUGIN_ARCHIRES_JPEG_FORMAT) {
         echo "selected ";
      }
      echo "value='0'>jpeg</option>";
      echo "<option ";
      if ($this->fields["format"] == self::PLUGIN_ARCHIRES_PNG_FORMAT) {
         echo "selected ";
      }
      echo "value='1'>png</option>";
      echo "<option ";
      if ($this->fields["format"] == self::PLUGIN_ARCHIRES_GIF_FORMAT) {
         echo "selected ";
      }
      echo "value='2'>gif</option>";
      echo "</select></td>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Color', 'archires')."</td>";
      echo "<td colspan='3'><select name='color'> ";
      echo "<option ";
      if ($this->fields["color"]=='0') {
         echo "selected ";
      }
      echo "value='0'>".__('Type of network', 'archires')."</option>";
      echo "<option ";
      if ($this->fields["color"] == '1') {
         echo "selected ";
      }
      echo "value='1'>".__('VLAN')."</option>";
      echo "</select></td></tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }


   static function showView($item) {
      global $DB;

      $plugin_archires_views_id = $item->fields["plugin_archires_views_id"];

      if (!$plugin_archires_views_id) {
         return false;
      }
      $view = new self();
      $view->getFromDB($plugin_archires_views_id);

      $name_config = $view->fields["name"];

      echo "<table class='tab_cadre_fixe' cellpadding='2'width='75%'>";
      echo "<tr>";
      echo "<th colspan='3'>".sprintf(__('%1$s: %2$s'), self::getTypeName(1), $name_config);
      echo "</th></tr>";

      echo "<tr class='tab_bg_2 top'>";
      echo "<th>".__('Display of items', 'archires')."</th>";
      echo "<th>".__('Display description', 'archires')."</th>";
      echo "<th>".__('Generation', 'archires').
           "</th></tr>";

      echo "<tr class='tab_bg_1 top'><td class='center'>";
      if ($view->fields["computer"] != 0) {
         printf(__('%1$s: %2$s'), _n('Computer', 'Computers', 2), __('Yes'));
      } else {
         printf(__('%1$s: %2$s'), _n('Computer', 'Computers', 2), __('No'));
      }
      echo "<br>";

      if ($view->fields["networking"] != 0) {
         printf(__('%1$s: %2$s'), _n('Network equipment', 'Network equipments', 2, 'archires'),
                __('Yes'));
      } else {
         printf(__('%1$s: %2$s'), _n('Network equipment', 'Network equipments', 2, 'archires'),
                __('No'));
      }
      echo "<br>";

      if ($view->fields["printer"] !=0 ) {
         printf(__('%1$s: %2$s'), _n('Printer', 'Printers', 2), __('Yes'));
      } else {
         printf(__('%1$s: %2$s'), _n('Printer', 'Printers', 2), __('No'));
      }
      echo "<br>";

      if ($view->fields["peripheral"]!=0) {
          printf(__('%1$s: %2$s'), _n('Device', 'Devices', 2), __('Yes'));
      } else {
          printf(__('%1$s: %2$s'), _n('Device', 'Devices', 2), __('No'));
      }
      echo "<br>";

      if ($view->fields["phone"] != 0) {
          printf(__('%1$s: %2$s'), _n('Phone', 'Phones', 2), __('Yes'));
      } else {
          printf(__('%1$s: %2$s'), _n('Phone', 'Phones', 2), __('No'));
      }
      echo "</td>";

      echo "<td class='center'>";
      if ($view->fields["display_ports"] != 0) {
          printf(__('%1$s: %2$s'), __('Display sockets', 'archires'), __('Yes'));
      } else {
          printf(__('%1$s: %2$s'), __('Display sockets', 'archires'), __('No'));
      }
      echo "<br>";

      if ($view->fields["display_ip"] != 0) {
          printf(__('%1$s: %2$s'), __('Display IP/Mask', 'archires'), __('Yes'));
      } else {
          printf(__('%1$s: %2$s'), __('Display IP/Mask', 'archires'), __('No'));
      }
      echo "<br>";

      if ($view->fields["display_type"] != 0) {
          printf(__('%1$s: %2$s'), __('Display item types', 'archires'), __('Yes'));
      } else {
          printf(__('%1$s: %2$s'), __('Display item types', 'archires'), __('No'));
      }
      echo "<br>";

      if ($view->fields["display_state"] != 0) {
          printf(__('%1$s: %2$s'), __('Display item statuses', 'archires'), __('Yes'));
      } else {
          printf(__('%1$s: %2$s'), __('Display item statuses', 'archires'), __('No'));
      }
      echo "<br>";

      if ($view->fields["display_location"] != 0) {
          printf(__('%1$s: %2$s'), __('Display item locations', 'archires'), __('Yes'));
      } else {
          printf(__('%1$s: %2$s'), __('Display item locations', 'archires'), __('No'));
      }
      echo "<br>";

      if ($view->fields["display_entity"] != 0) {
          printf(__('%1$s: %2$s'), __('Display item entities', 'archires'), __('Yes'));
      } else {
          printf(__('%1$s: %2$s'), __('Display item entities', 'archires'), __('No'));
      }
      echo "</td>";

      $engine = '';
     if ($view->fields["engine"] != 0) {
         $engine = "Neato";
      } else {
         $engine = "Dot";
      }

      echo "<td class='center'>". sprintf(__('%1$s: %2$s'), __('All'),
                                          sprintf(__('%1$s %2$s'),
                                                  __('Rendering engine', 'archires'), $engine));
      echo "<br>";
      $format_graph = '';
      if ($view->fields["format"] == self::PLUGIN_ARCHIRES_JPEG_FORMAT) {
         $format_graph = "jpeg";
      } else if ($view->fields["format"] == self::PLUGIN_ARCHIRES_PNG_FORMAT) {
         $format_graph = "png";
      } else if ($view->fields["format"] == self::PLUGIN_ARCHIRES_GIF_FORMAT) {
         $format_graph = "gif";
      }
      printf(__('%1$s: %2$s'), __('Image format', 'archires'), $format_graph);
      echo "</td></tr>";
      echo "</table>";
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'PluginArchiresApplianceQuery' :
         case 'PluginArchiresLocationQuery' :
         case 'PluginArchiresNetworkEquipmentQuery' :
            switch ($tabnum) {
               case 1 :
                  self::showView($item);
                  break;
            }
            break;
      }
      return true;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate && plugin_archires_haveRight('archires', 'r')) {
         switch ($item->getType()) {
            case 'PluginArchiresApplianceQuery' :
            case 'PluginArchiresLocationQuery' :
            case 'PluginArchiresNetworkEquipmentQuery' :
               return self::getTypeName(1);
         }
      }
      return '';
   }

}
?>