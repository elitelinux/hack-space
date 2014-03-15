<?php
/*
 * @version $Id: config.form.php 254 2013-07-22 08:19:38Z tsmr $
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

include_once ("../../../inc/includes.php");
Plugin::load('reports');

Session::checkSeveralRightsOr(array("config"  => "w",
                                    "profile" => "w"));
Html::header(__('Setup'), $_SERVER['PHP_SELF'], "config", "plugins");

echo "<div class='center'>";
echo "<table class='tab_cadre'>";
echo "<tr><th>".__('Reports plugin configuration', 'reports')."</th></tr>";

if (Session::haveRight("profile","w")) {
   echo "<tr class='tab_bg_1 center'><td>";
   echo "<a href='report.form.php'>".__('Reports plugin configuration', 'reports')."</a>";
   echo "</td/></tr>\n";
}

if (Session::haveRight("config","w")) {
   foreach (searchReport() as $report => $plug) {
      $url = getReportConfigPage($plug, $report);
      $file = GLPI_ROOT.getReportConfigPage($plug, $report);
      if (is_file($file)) {
         echo "<tr class='tab_bg_1 center'><td>";
         echo "<a href='".$CFG_GLPI['root_doc'].$url."'>".sprintf(__('%1$s: %2$s'), __('Report configuration'),
                                        $LANG['plugin_reports'][$report]);
         echo "</a></td/></tr>";
      }
   }
}

echo "</table></div>";

Html::footer();
?>