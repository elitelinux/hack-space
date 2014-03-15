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

class PluginRacksConfig extends CommonDBTM {

	static function getTypeName($nb=0) {

      return __('Configuration of units', 'racks');
   }

   function showForm($target) {

		$this->GetfromDB(1);
		echo "<div align='center'><form method='post'  action=\"$target\">";
      echo "<table class='tab_cadre' cellpadding='5'><tr ><th colspan='2'>";
      echo self::getTypeName(0)."</th></tr>";
      echo "<tr class='tab_bg_1'><td>";
      echo "<select name=\"unit\" size=\"1\"> ";
      echo "<option ";
      if ($this->fields["unit"]=='1') echo "selected ";
      echo "value=\"1\">".__('metric', 'racks')."</option>";
      echo "<option ";
      if ($this->fields["unit"]=='2') echo "selected ";
      echo "value=\"2\">".__('English', 'racks')."</option>";
      echo "</select> ";
      echo "</td>";
      echo "<td>";
      echo "<div align='center'><input type='hidden' name='id' value='1'>";
      echo "<input type='submit' name='update' value=\""._sx('button', 'Post')."\" class='submit'></div></td></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";

   }
   function getUnit($field) {

      $this->GetfromDB(1);
      switch ($field) {
         case "weight" :
            if ($this->fields["unit"]=='1')
               _e('kg', 'racks');
            else
               _e('lbs', 'racks');
            break;
         case "dissipation" :
            if ($this->fields["unit"]=='1')
               _e('btu/h', 'racks');
            else
               _e('watts', 'racks');
            break;
         case "rate" :
            if ($this->fields["unit"]=='1')
               _e('m3/h', 'racks');
            else
               _e('CFM', 'racks');
            break;
         case "size" :
            if ($this->fields["unit"]=='1')
               _e('mm', 'racks');
            else
               _e('pouces', 'racks');
            break;
      }
   }
}

?>
