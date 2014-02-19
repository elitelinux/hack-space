<?php
/*
 * @version $Id: resaperiod.php 20129 2013-02-04 16:53:59Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
* @since version 0.84
*/

$AJAX_INCLUDE = 1;
include ('../inc/includes.php');

// Send UTF8 Headers
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_POST['type']) && isset($_POST['end'])) {

   echo "<table>";
   switch ($_POST['type']) {
      case 'day' :
         echo "<tr><td>".__('End date').'</td><td>';
         Html::showDateFormItem('periodicity[end]', $_POST['end']);
         echo "</td></tr>";
         break;

      case 'week' :
         echo "<tr><td>".__('End date').'</td><td>';
         Html::showDateFormItem('periodicity[end]', $_POST['end']);
         echo "</td></tr></table>";
         echo "<table class='tab_glpi'>";
         echo "<tr class='center'><td>&nbsp;</td>";
         $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
         foreach ($days as $day) {
            echo "<th>".__($day)."</th>";
         }
         echo "</tr><tr class='center'><td>".__('By day').'</td>';

         foreach ($days as $day) {
            echo "<td><input type='checkbox' name='periodicity[days][$day]'></td>";
         }
         echo "</tr>";
         break;

      case 'month' :
         echo "<tr><td colspan='2'>";
         echo "<select name='periodicity[subtype]'>";
         echo "<option value='date'>".__('Each month, same date')."</option>\n";
         echo "<option value='day'>".__('Each month, same day of week')."</option>\n";
         echo "</select>";
         echo "</td></tr>";
         echo "<tr><td>".__('End date').'</td><td>';
         Html::showDateFormItem('periodicity[end]', $_POST['end']);
         echo "</td></tr>";

   }
   echo '</table>';
}
?>