<?php
/*
 * @version $Id: histoinst.php 246 2013-05-02 13:03:33Z yllen $
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2013 by the reports Development Team.

 https://forge.indepnet.net/projects/reports
 -------------------------------------------------------------------------

 LICENSE

 This file is part of reports.

 reports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with reports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Original Author of file: Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------

$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 1; // Really a big SQL request

include ("../../../../inc/includes.php");

includeLocales("histoinst");

plugin_reports_checkRight('reports', "histoinst","r");
$computer = new Computer();
$computer->checkGlobal('r');
$software = new Software();
$software->checkGlobal('r');

//TRANS: The name of the report = History of last software's installations
Html::header(__('histoinst_report_title', 'reports'), $_SERVER['PHP_SELF'], "utils", "report");

Report::title();

echo "<div class='center'>";
echo "<table class='tab_cadrehov' cellpadding='5'>\n";
echo "<tr class='tab_bg_1 center'>".
      "<th colspan='4'>" . __("History of last software's installations", "reports") .
      "</th></tr>\n";

echo "<tr class='tab_bg_2'><th>". __('Date of inventory', 'reports') . "</th>" .
      "<th>". __('User') . "</th>".
      "<th>". __("Computer's name") . "</th>".
      "<th>". sprintf(__('%1$s (%2$s)'), _n('Software', 'Software', 1), __('version'))."</th></tr>\n";

$sql = "SELECT a.`date_mod` AS dat, a.`new_value`, `glpi_computers`.`id` AS cid, `name`,
               a.`user_name`
        FROM (SELECT `date_mod`, `new_value`, `user_name`, `items_id`, `id`
              FROM `glpi_logs`
              WHERE `glpi_logs`.`date_mod` > DATE_SUB(Now(), INTERVAL 21 DAY)
                    AND `linked_action` = '" .Log::HISTORY_INSTALL_SOFTWARE ."'
                    AND `itemtype` = 'Computer') a
        LEFT JOIN `glpi_computers` ON (a.`items_id` = `glpi_computers`.`id`)
        WHERE `glpi_computers`.`entities_id` = '" . $_SESSION["glpiactive_entity"] ."'
        ORDER BY a.`id` DESC
        LIMIT 0,200";
$result = $DB->query($sql);

$prev = "";
$class = "tab_bg_2";
while ($data = $DB->fetch_array($result)) {
   if ($prev == $data["dat"].$data["name"]) {
      echo "<br />";
   } else {
      if (!empty($prev)) {
         echo "</td></tr>\n";
      }
      $prev = $data["dat"].$data["name"];
      echo "<tr class='" . $class . " top'>".
            "<td class='center'>". Html::convDateTime($data["dat"]) . "</td>" .
            "<td>". $data["user_name"] . "&nbsp;</td>".
            "<td><a href='". Toolbox::getItemTypeFormURL('Computer') . "?id=" . $data["cid"]."'>" .
                  $data["name"] . "</a></td>".
            "<td>";
      $class = ($class=="tab_bg_2" ? "tab_bg_1" : "tab_bg_2");
   }
   echo $data["new_value"];
}

if (!empty($prev)) {
   echo "</td></tr>\n";
}
echo "</table><p>". __('The list is limited to 200 items and 21 days', 'reports')."</p></div>\n";

Html::footer();
?>