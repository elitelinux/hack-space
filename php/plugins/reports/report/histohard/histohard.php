<?php
/*
 * @version $Id: histohard.php 246 2013-05-02 13:03:33Z yllen $
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

// Original Author of file: Benoit Machiavello
// ----------------------------------------------------------------------

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 1; // Really a big SQL request

include ("../../../../inc/includes.php");

includeLocales("histohard");

plugin_reports_checkRight('reports', "histohard","r");
$computer = new Computer();
$computer->checkGlobal('r');

//TRANS: The name of the report = History of last hardware's installations
Html::header(__("histohard_report_title", 'reports'), $_SERVER['PHP_SELF'], "utils","report");

Report::title();

echo "<div class='center'>";
echo "<table class='tab_cadrehov'>\n";
echo "<tr class='tab_bg_1 center'>".
     "<th colspan='5'>". __("History of last hardware's installations", 'reports')."</th></tr>\n";

echo "<tr><th>".__('Date of inventory', 'reports'). "</th>" .
      "<th>". __('User') . "</th>".
      "<th>". __('Network device') . "</th>".
      "<th>". __(-'Field') . "</th>".
      "<th>". __('Modification', 'reports') . "</th></tr>\n";

$sql = "SELECT `glpi_logs`.`date_mod` AS dat, `linked_action`, `itemtype`, `itemtype_link`,
               `old_value`, `new_value`, `glpi_computers`.`id` AS cid, `name`, `user_name`
        FROM `glpi_logs`
        LEFT JOIN `glpi_computers` ON (`glpi_logs`.`items_id` = `glpi_computers`.`id`)
        WHERE `glpi_logs`.`date_mod` > DATE_SUB(Now(), INTERVAL 21 DAY)
              AND `itemtype` = 'Computer'
              AND `linked_action` IN (".Log::HISTORY_CONNECT_DEVICE.",
                                      ".Log::HISTORY_DISCONNECT_DEVICE.",
                                      ".Log::HISTORY_DELETE_DEVICE.",
                                      ".Log::HISTORY_UPDATE_DEVICE.",
                                      ".Log::HISTORY_ADD_DEVICE.")
              AND `glpi_computers`.`entities_id` = '" . $_SESSION["glpiactive_entity"] ."'
        ORDER BY `glpi_logs`.`id` DESC
        LIMIT 0,100";
$result = $DB->query($sql);

$prev  = "";
$class = "tab_bg_2";
while ($data = $DB->fetch_array($result)) {
   if ($prev == $data["dat"].$data["name"]) {
      echo "</td></tr><tr class='" . $prevclass ." top'><td></td><td></td><td></td><td>";
   } else {
      if (!empty($prev)) {
         echo "</td></tr>\n";
      }
      $prev = $data["dat"].$data["name"];
      echo "<tr class='" . $class . " top'><td>". Html::convDateTime($data["dat"]) . "</td>" .
            "<td>". $data["user_name"] . "&nbsp;</td>".
            "<td><a href='". Toolbox::getItemTypeFormURL('Computer')."?id=" . $data["cid"] . "'>" .
            $data["name"] . "</a></td><td>";
      $prevclass = $class;
      $class = ($class=="tab_bg_2" ? "tab_bg_1" : "tab_bg_2");
   }
   $field = "";
   if ($data["linked_action"]) {
   // Yes it is an internal device
      switch ($data["linked_action"]) {
         case Log::HISTORY_ADD_DEVICE :
            $field = NOT_AVAILABLE;
            if ($item = getItemForItemtype($data["itemtype_link"])) {
               $field = $item->getTypeName();
            }
            $change = sprintf(__('%1$s: %2$s'), __('Add the component'), $data[ "new_value"]);
            break;

         case Log::HISTORY_UPDATE_DEVICE :
            $field = NOT_AVAILABLE;
            $change = '';
            if ($item = getItemForItemtype($data["itemtype_link"])) {
               $field  = $item->getTypeName();
               $change = sprintf(__('%1$s: %2$s'), $item->getSpecifityLabel(), "");
            }
            $change .= $data[ "old_value"]."'&nbsp;<strong>--></strong>&nbsp;'".$data[ "new_value"]."'";
            break;

         case Log::HISTORY_DELETE_DEVICE :
            $field = NOT_AVAILABLE;
            if ($item = getItemForItemtype($data["itemtype_link"])) {
               $field = $item->getTypeName();
            }
            $change = sprintf(__('%1$s: %1$s'), __('Delete the component'), $data["old_value"]);
            break;

         case Log::HISTORY_DISCONNECT_DEVICE :
            if (!($item = getItemForItemtype($data["itemtype_link"]))) {
               continue;
            }
            $field  = $item->getTypeName();
            $change = sprintf(__('%1$s: %2$s'), __('Logout'), $data["old_value"]);
            break;

         case Log::HISTORY_CONNECT_DEVICE :
            if (!($item = getItemForItemtype($data["itemtype_link"]))) {
               continue;
            }
            $field  = $item->getTypeName();
            $change = sprintf(__('%1$s: %2$s'), __('Logout'), $data["new_value"]);
            break;
      }//fin du switch
   }
   echo $field . "<td>" . $change;
}

if (!empty($prev)) {
   echo "</td></tr>\n";
}
echo "</table><p>".__('The list is limited to 100 items and 21 days', 'reports')."</p></div>\n";

Html::footer();
?>