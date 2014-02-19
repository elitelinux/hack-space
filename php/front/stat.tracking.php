<?php
/*
 * @version $Id: stat.tracking.php 20129 2013-02-04 16:53:59Z moyo $
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
*/

include ('../inc/includes.php');

Html::header(__('Statistics'), '', "maintain", "stat");

Session::checkRight("statistic", "1");

if (!$item = getItemForItemtype($_GET['itemtype'])) {
   exit;
}

if (empty($_GET["type"])) {
   $_GET["type"] = "user";
}

if (empty($_GET["showgraph"])) {
   $_GET["showgraph"] = 0;
}

if (empty($_GET["value2"])) {
   $_GET["value2"] = 0;
}

if (empty($_GET["date1"]) && empty($_GET["date2"])) {
   $year              = date("Y")-1;
   $_GET["date1"] = date("Y-m-d",mktime(1,0,0,date("m"),date("d"),$year));
   $_GET["date2"] = date("Y-m-d");
}

if (!empty($_GET["date1"])
    && !empty($_GET["date2"])
    && (strcmp($_GET["date2"],$_GET["date1"]) < 0)) {

   $tmp           = $_GET["date1"];
   $_GET["date1"] = $_GET["date2"];
   $_GET["date2"] = $tmp;
}

if (!isset($_GET["start"])) {
   $_GET["start"] = 0;
}

Stat::title();

$requester = array('user'               => array('title' => __('Requester')),
                   'users_id_recipient' => array('title' => __('Writer')),
                   'group'              => array('title' => __('Group')),
                   'group_tree'         => array('title' => __('Group tree')),
                   'usertitles_id'      => array('title' => _x('person','Title')),
                   'usercategories_id'  => array('title' => __('Category')));

$caract    = array('itilcategories_id'   => array('title' => __('Category')),
                   'itilcategories_tree' => array('title' => __('Category tree')),
                   'urgency'             => array('title' => __('Urgency')),
                   'impact'              => array('title' => __('Impact')),
                   'priority'            => array('title' => __('Priority')),
                   'solutiontypes_id'    => array('title' => __('Solution type')));

if ($_GET['itemtype'] == 'Ticket') {
   $caract['type']            = array('title' => __('Type'));
   $caract['requesttypes_id'] = array('title' => __('Request source'));
   $caract['locations_id']    = array('title' => __('Location'));
   $caract['locations_tree']  = array('title' => __('Location tree'));
}


$items = array(__('Requester')       => $requester,
               __('Characteristics') => $caract,
               __('Assigned to')     => array('technicien'
                                                   => array('title' => __('Technician as assigned')),
                                              'technicien_followup'
                                                   => array('title' => __('Technician in tasks')),
                                              'groups_id_assign'
                                                   => array('title' => __('Group')),
                                              'groups_tree_assign'
                                                   => array('title' => __('Group tree')),
                                              'suppliers_id_assign'
                                                   => array('title' => __('Supplier'))));

$INSELECT = "";
foreach ($items as $label => $tab) {
   $INSELECT .= "<optgroup label=\"$label\">";
   foreach ($tab as $key => $val) {
      $INSELECT .= "<option value='$key' ".(($key == $_GET["type"])?"selected":"").">".$val['title'].
                   "</option>";
   }
   $INSELECT .= "</optgroup>";
}

echo "<div class='center'><form method='get' name='form' action='stat.tracking.php'>";
echo "<table class='tab_cadre'>";
echo "<tr class='tab_bg_2'><td rowspan='2' class='center'>";
echo "<select name='type'>".$INSELECT."</select></td>";
echo "<td class='right'>".__('Start date')."</td><td>";
Html::showDateFormItem("date1", $_GET["date1"]);
echo "</td>";
echo "<td class='right'>".__('Show graphics')."</td>";
echo "<td rowspan='2' class='center'>";
echo "<input type='hidden' name='itemtype' value=\"". $_GET["itemtype"] ."\">";
echo "<input type='submit' class='submit' name='submit' value=\"".__s('Display report')."\"></td>".
     "</tr>";

echo "<tr class='tab_bg_2'><td class='right'>".__('End date')."</td><td>";
Html::showDateFormItem("date2", $_GET["date2"]);
echo "</td><td class='center'>";
echo "<input type='hidden' name='value2' value='".$_GET["value2"]."'>";
Dropdown::showYesNo('showgraph', $_GET['showgraph']);
echo "</td></tr>";
echo "</table>";
// form using GET method : CRSF not needed
echo "</form>";
echo "</div>";

$val    = Stat::getItems($_GET["itemtype"], $_GET["date1"], $_GET["date2"], $_GET["type"],
                         $_GET["value2"]);
$params = array('type'   => $_GET["type"],
                'date1'  => $_GET["date1"],
                'date2'  => $_GET["date2"],
                'value2' => $_GET["value2"],
                'start'  => $_GET["start"]);

Html::printPager($_GET['start'], count($val), $CFG_GLPI['root_doc'].'/front/stat.tracking.php',
                 "date1=".$_GET["date1"]."&amp;date2=".$_GET["date2"]."&amp;type=".$_GET["type"].
                    "&amp;showgraph=".$_GET["showgraph"]."&amp;itemtype=".$_GET["itemtype"].
                    "&amp;value2=".$_GET['value2'],
                 'Stat', $params);

if (!$_GET['showgraph']) {
   Stat::show($_GET["itemtype"], $_GET["type"], $_GET["date1"], $_GET["date2"], $_GET['start'],
              $val, $_GET['value2']);

} else {
   $data = Stat::getDatas($_GET["itemtype"], $_GET["type"], $_GET["date1"], $_GET["date2"],
                          $_GET['start'], $val, $_GET['value2']);

   if (isset($data['opened']) && is_array($data['opened'])) {
      foreach ($data['opened'] as $key => $val) {
         $newkey             = Html::clean($key);
         $cleandata[$newkey] = $val;
      }
      Stat::showGraph(array(__('Number opened') => $cleandata),
                      array('title'     => __('Number opened'),
                            'showtotal' => 1,
                            'unit'      => $item->getTypeName(2),
                            'type'      => 'pie'));
   }

   if (isset($data['solved']) && is_array($data['solved'])) {
      foreach ($data['solved'] as $key => $val) {
         $newkey             = Html::clean($key);
         $cleandata[$newkey] = $val;
      }
      Stat::showGraph(array(__('Number solved') => $cleandata),
                      array('title'     => __('Number solved'),
                            'showtotal' => 1,
                            'unit'      => $item->getTypeName(2),
                            'type'      => 'pie'));
   }

   if (isset($data['late']) && is_array($data['late'])) {
      foreach ($data['late'] as $key => $val) {
         $newkey             = Html::clean($key);
         $cleandata[$newkey] = $val;
      }

      Stat::showGraph(array(__('Number solved late') => $cleandata),
                      array('title'     => __('Number solved late'),
                            'showtotal' => 1,
                            'unit'      => $item->getTypeName(2),
                            'type'      => 'pie'));
   }


   if (isset($data['closed']) && is_array($data['closed'])) {
      foreach ($data['closed'] as $key => $val) {
         $newkey             = Html::clean($key);
         $cleandata[$newkey] = $val;
      }
      Stat::showGraph(array(__('Number closed') => $cleandata),
                      array('title'     => __('Number closed'),
                            'showtotal' => 1,
                            'unit'      => $item->getTypeName(2),
                            'type'      => 'pie'));
   }

   if ($_GET['itemtype'] == 'Ticket') {
      if (isset($data['opensatisfaction']) && is_array($data['opensatisfaction'])) {
         foreach ($data['opensatisfaction'] as $key => $val) {
            $newkey             = Html::clean($key);
            $cleandata[$newkey] = $val;
         }
         Stat::showGraph(array(__('Satisfaction survey') => $cleandata),
                        array('title'     => __('Satisfaction survey'),
                              'showtotal' => 1,
                              'unit'      => $item->getTypeName(2),
                              'type'      => 'pie'));
      }
   }

}

Html::footer();
?>
