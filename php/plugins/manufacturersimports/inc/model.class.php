<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manufacturersimports plugin for GLPI
 Copyright (C) 2003-2011 by the Manufacturersimports Development Team.

 https://forge.indepnet.net/projects/manufacturersimports
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Manufacturersimports.

 Manufacturersimports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manufacturersimports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manufacturersimports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginManufacturersimportsModel extends CommonDBTM {
   
   static function getTypeName($nb=0) {
      return _n('Suppliers import', 'Suppliers imports', $nb, 'manufacturersimports');
   }
   
   static function canCreate() {
      return plugin_manufacturersimports_haveRight('manufacturersimports', 'w');
   }

   static function canView() {
      return plugin_manufacturersimports_haveRight('manufacturersimports', 'r');
   }

	function getFromDBbyDevice($items_id,$itemtype) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->getTable()."` " .
			"WHERE `items_id` = '" . $items_id . "'
			AND `itemtype` = '" . $itemtype . "' ";
		if ($result = $DB->query($query)) {
			if ($DB->numrows($result) != 1) {
				return false;
			}
			$this->fields = $DB->fetch_assoc($result);
			if (is_array($this->fields) && count($this->fields)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	
   function checkIfModelNeeds($itemtype,$items_id) {

      if ($this->getFromDBbyDevice($items_id,$itemtype))
         return $this->fields["model_name"];
      else
         return false;
   }

  function addModel($values) {

      if ($this->getFromDBbyDevice($values['items_id'],$values['itemtype'])) {

         $this->update(array(
           'id'=>$this->fields['id'],
           'model_name'=>$values['model_name'],
           'items_id'=>$values['items_id'],
           'itemtype'=>$values['itemtype']));
      } else {

         $this->add(array(
           'model_name'=>$values['model_name'],
           'items_id'=>$values['items_id'],
           'itemtype'=>$values['itemtype']));
      }
      
      return true;
   }

   /**
   * Prints the model add form (into devices)
   *
   * @param $device the device ID
   * @param $type the device type
   * @return nothing (print out a table)
   *
   */
   static function showForm($itemtype,$items_id) {
      global $DB,$CFG_GLPI;
      
      $canedit=plugin_manufacturersimports_haveRight('manufacturersimports', 'w');

      $query = "SELECT *
          FROM `glpi_plugin_manufacturersimports_models`
          WHERE `itemtype` = '".$itemtype."'
          AND `items_id` = '".$items_id."'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/manufacturersimports/front/config.form.php\">";
      echo "<div align=\"center\"><table class=\"tab_cadre_fixe\"  cellspacing=\"2\" cellpadding=\"2\">";
      echo "<tr>";
      echo "<th>".PluginManufacturersimportsPreImport::getTypeName(2)."</th>";
      echo "<th>".__('Model Number', 'manufacturersimports')."</th>";
      echo "</tr>";

      if ($number ==1) {
         while($line=$DB->fetch_array($result)) {
            $ID=$line["id"];
            echo "<tr class='tab_bg_2'>";
            echo "<td class='left'>";
            echo "<input type='text' name='model_name' size='30' value='".$line["model_name"]."'>";
            echo "</td>";
            if ($canedit) {
               echo "<td class='center' class='tab_bg_2'>";
               Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/manufacturersimports/front/config.form.php',
                                    'delete_model',
                                    _x('button','Delete permanently'),
                                    array('id' => $ID));
               echo "</td>";
            } else {
               echo "<td>";
               echo "</td>";
            }
            echo "</tr>";
         }

      } else if ($canedit) {
         echo "<tr>";
         echo "<th colspan='2'>";
         echo "<input type='text' name='model_name' size='30'>";
         echo "<input type='hidden' name='items_id' value='".$items_id."'>";
         echo "<input type='hidden' name='itemtype' value='".$itemtype."'>";
         echo "<input type=\"submit\" name=\"update_model\" class=\"submit\" value='"._sx('button','Save')."' >";
         echo "</th></tr>";
      }

      echo "</table></div>";
      Html::closeForm();
   }
}

?>