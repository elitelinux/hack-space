<?php
/*
 * @version $Id: prototype.class.php 190 2013-08-03 14:36:00Z tsmr $
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

class PluginArchiresPrototype extends CommonDBTM {


   private static function dotIt($engine, $graph, $format) {

      $out         = '';
      $Path        = realpath(GLPI_PLUGIN_DOC_DIR."/archires");
      $graph_name  = tempnam($Path, "txt");
      $out_name    = tempnam($Path, $format);

      if (file_put_contents($graph_name, $graph)) {
         $command = "$engine -T$format -o\"$out_name\" \"$graph_name\" ";
         $out     = shell_exec($command);
         $out     = file_get_contents($out_name);
         unlink($graph_name);
         unlink($out_name);
         //Toolbox::logDebug("command:", $command, "in:", $graph_name, "out:", $out_name, "Res:", strlen($out));
      }
      return $out;
   }


   function testGraphviz() {

      $graph = "graph G {
                  a;
                  b;
                  c -- d;
                  a -- c;}";

      return self::dotIt('dot', $graph, 'png');
   }


   function CleanField($string) {

      $string = str_replace(">", " - ", $string);
      $string = str_replace("&", " - ", $string);
      return $string;
   }


   static function displayTypeAndIP($PluginArchiresView,$itemtype,$device,$generation) {

      $graph                  = "";
      $PluginArchiresArchires = new PluginArchiresArchires();

      if (($PluginArchiresView->fields["display_ip"] != 0)
          && isset($device["ip"])) {

         if (($PluginArchiresView->fields["display_type"] != 0)
             && !empty($device["type"])) {
            $class     = $itemtype."Type";
            $typeclass = new $class();
            $typeclass->getFromDB($device["type"]);

            if (!$generation) {
               $graph = $typeclass->fields["name"] . " " .$device["ip"];
            } else {
               $graph = " - ".$typeclass->fields["name"].
                        "</td></tr><tr><td>".$device["ip"]."</td></tr>";
            }

         } else {
            if (!$generation) {
               $graph = $device["ip"];
            } else {
               $graph ="</td></tr><tr><td>".$device["ip"]."</td></tr>";
            }
         }

      } else {
         if (($PluginArchiresView->fields["display_type"] != 0)
             && !empty($device["type"])) {
            $class     = $itemtype."Type";
            $typeclass = new $class();
            $typeclass->getFromDB($device["type"]);

            if (!$generation) {
               $graph =$typeclass->fields["name"];
            } else {
               $graph ="</td></tr><tr><td>".
                        $typeclass->fields["name"]."</td></tr>";
            }

         } else {
            if (!$generation) {
               echo "";
            } else {
               $graph ="</td></tr>";
            }
         }
      }
      return $graph;
  }


   static function displayUsers($url,$device,$generation) {

      $graph ="";
      if ($device["users_id"]) {
         if ($generation) {
            $graph = "URL=\"".$url."\" tooltip=\"".getUserName($device["users_id"])."\"";
         } else {
            $graph = "<a href='".$url."'>".getUserName($device["users_id"])."</a>";
         }

      } else if (!$device["users_id"] && $device["groups_id"]) {
         if ($generation) {
            $graph = "URL=\"".$url."\" tooltip=\"".Dropdown::getDropdownName("glpi_groups",
                                                                             $device["groups_id"])."\"";
         } else {
            $graph = "<a href='".$url."'>".Dropdown::getDropdownName("glpi_groups",
                                                                     $device["groups_id"])."</a>";
         }

      } else if (!$device["users_id"]
                 && !$device["groups_id"]
                 && $device["contact"]) {
         if ($generation) {
            $graph = "URL=\"".$url."\" tooltip=\"".$device["contact"]."\"";
         } else {
            $graph = "<a href='".$url."'>".$device["contact"]."</a>";
         }

      } else {
         if ($generation) {
            $graph = "URL=\"".$url."\" tooltip=\"".$device["name"]."\"";
         } else {
            $graph = "<a href='".$url."'>".$device["name"]."</a>";
         }
      }
      return $graph;
   }


   static function test($item) {
      global $DB,$CFG_GLPI;

      $ID                       = $item->getID();
      $type                     = $item->getType();
      $plugin_archires_views_id = $item->fields["plugin_archires_views_id"];

      if (!$plugin_archires_views_id) {
         return false;
      }
      $plugin             = new Plugin();
      $PluginArchiresView = new PluginArchiresView();

      if ($plugin->isActivated("appliances")) {
         $PluginArchiresApplianceQuery = new PluginArchiresApplianceQuery();
      }
      $PluginArchiresLocationQuery         = new PluginArchiresLocationQuery();
      $PluginArchiresNetworkEquipmentQuery = new PluginArchiresNetworkEquipmentQuery();
      $PluginArchiresStateColor            = new PluginArchiresStateColor();
      $PluginArchiresImageItem             = new PluginArchiresImageItem();

      $PluginArchiresView->getFromDB($plugin_archires_views_id);

      $devices = array();
      $ports   = array();

      echo "<br><div class='center'>";
      echo "<table class='tab_cadre_fixe'cellpadding='2' width='75%'>";
      echo "<tr><th colspan='6'>".__('Item')."</th></tr>";
      echo "<tr><th>".__('Graphviz name', 'archires')."</th>";
      echo "<th>".__('Associated picture', 'archires')."</th>";
      echo "<th>".__('Name of item', 'archires')."</th>";
      echo "<th>".sprintf(__('%1$s / %2$s'), __('Type'), __('IP'))."</th>";
      echo "<th>".__('Status')."</th>";
      echo "<th>".sprintf(__('%1$s / %2$s'), __('User'),
                          sprintf(__('%1$s / %2$s'), __('Group'), __('Contact')))."</th></tr>";

      if ($type == 'PluginArchiresLocationQuery') {
         $devices = $PluginArchiresLocationQuery->Query($ID, $PluginArchiresView, true);
         $ports   = $PluginArchiresLocationQuery->Query($ID, $PluginArchiresView, false);

      } else if ($type == 'PluginArchiresNetworkEquipmentQuery') {
         $devices = $PluginArchiresNetworkEquipmentQuery->Query($ID, $PluginArchiresView, true);
         $ports   = $PluginArchiresNetworkEquipmentQuery->Query($ID, $PluginArchiresView, false);

      } else if ($type == 'PluginArchiresApplianceQuery') {
         $devices = $PluginArchiresApplianceQuery->Query($ID, $PluginArchiresView, true);
         $ports   = $PluginArchiresApplianceQuery->Query($ID, $PluginArchiresView, false);
      }

      foreach ($devices as $itemtype => $typed_devices) {
         foreach ($typed_devices as $device_id => $device) {
            $device_unique_name  = $itemtype . "_" . $device_id . "_";
            $device_unique_name .= $device["name"];

            $image_name = $PluginArchiresImageItem->displayItemImage($device["type"], $itemtype,
                                                                     true);
            $link       = Toolbox::getItemTypeFormURL($itemtype);
            $url        = $link."?id=".$device_id;

            echo "<tr class='tab_bg_1'>";
            echo "<td>$device_unique_name</td>";
            echo "<td class='center'><img src='$image_name' alt='$image_name'></td>";
            echo "<td>" . $device["name"]."</td>";

            echo "<td>";
            echo self::displayTypeAndIP($PluginArchiresView, $itemtype, $device, false);
            echo  "</td>";

            echo  "<td>";
            if ($PluginArchiresView->fields["display_state"]!=0 && isset($device["states_id"])) {
               echo $PluginArchiresStateColor->displayColorState($device);
            }
            echo  "</td>";

            echo  "<td>";
            echo self::displayUsers($url, $device, false);
            echo  "</td>";
            echo  "</tr>";
         }
      }
      echo "</table>";

      echo "<br><table class='tab_cadre_fixe' cellpadding='2' width='75%'>";
      echo "<tr><th colspan='6'>"._n('Link', 'Links', 2)."</th></tr>";
      echo "<tr><th>".__('Graphviz links', 'archires')."</th>";
      echo "<th>".__('IP item 1', 'archires')."</th>";
      echo "<th>".__('Socket item 1', 'archires')."</th>";
      echo "<th>".__('Associated picture', 'archires')."</th>";
      echo "<th>".__('Socket item 2', 'archires')."</th>";
      echo "<th>".__('IP item 2', 'archires')."</th></tr>";

      $wires = array();

      $query = "SELECT `id`, `networkports_id_1`, `networkports_id_2`
                FROM `glpi_networkports_networkports`";

      if ($result = $DB->query($query)) {
         while ($data = $DB->fetch_array($result)) {
            $wires[$data["id"]]["networkports_id_1"] = $data["networkports_id_1"];
            $wires[$data["id"]]["networkports_id_2"] = $data["networkports_id_2"];
         }
      }

      foreach ($wires as $wire) {
         if (isset($ports[$wire["networkports_id_1"]])
             && !empty($ports[$wire["networkports_id_1"]])
             && isset($ports[$wire["networkports_id_2"]])
             && !empty($ports[$wire["networkports_id_2"]])) {

            $items_id1              = $ports[$wire["networkports_id_1"]]["items_id"];
            $itemtype1              = $ports[$wire["networkports_id_1"]]["itemtype"];
            $logical_number1        = $ports[$wire["networkports_id_1"]]["logical_number"];
            $name1                  = $ports[$wire["networkports_id_1"]]["namep"];
            $ID1                    = $ports[$wire["networkports_id_1"]]["idp"];
            $networkinterfaces_id1  = $ports[$wire["networkports_id_1"]]["instantiation_type"];
            $ip1                    = $ports[$wire["networkports_id_1"]]["ip"];
            $device_unique_name1    = $itemtype1 . "_" . $items_id1 . "_";
            $device_unique_name1   .= $devices[$itemtype1][$items_id1]["name"];

            $items_id2              = $ports[$wire["networkports_id_2"]]["items_id"];
            $itemtype2              = $ports[$wire["networkports_id_2"]]["itemtype"];
            $logical_number2        = $ports[$wire["networkports_id_2"]]["logical_number"];
            $name2                  = $ports[$wire["networkports_id_2"]]["namep"];
            $ID2                    = $ports[$wire["networkports_id_2"]]["idp"];
            $networkinterfaces_id2  = $ports[$wire["networkports_id_2"]]["instantiation_type"];
            $ip2                    = $ports[$wire["networkports_id_2"]]["ip"];
            $device_unique_name2    = $itemtype2 . "_" . $items_id2 . "_";
            $device_unique_name2   .= $devices[$itemtype2][$items_id2]["name"];

            echo "<tr class='tab_bg_1'>";

            if ($PluginArchiresView->fields["display_ports"]!=0
                && $PluginArchiresView->fields["engine"]!=1) {

               $url_ports = $CFG_GLPI["root_doc"] . "/front/networkport.form.php?id=";
               echo  "<td>".$device_unique_name1;
               echo  " -- " . $device_unique_name2 ."</td>";

               if ($PluginArchiresView->fields["display_ip"]!=0) {
                  echo  "<td>".$ip1."</td>";
               } else {
                  echo  "<td></td>";
               }
               echo "<td><a href='".$url_ports.$ID1."'>".$name1."</a> - ".
                           __('Socket', 'archires')." ".$logical_number1."</td>";
               echo "<td class='center'><img src= \"../pics/socket.png\" alt='../pics/socket.png' />";
               echo "</td><td><a href='".$url_ports.$ID2."'>".$name2."</a> - ".
                               __('Socket', 'archires')." ".$logical_number2."</td>";
               if ($PluginArchiresView->fields["display_ip"]!=0) {
                  echo  "<td>".$ip2."</td>";
               } else {
                  echo  "<td></td>";
               }
            } else {

               echo  "<td>".$device_unique_name1." -- ".$device_unique_name2 ."</td>";
               echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
            }
         }
      }
      echo "</tr></table>";

      echo "<br><table class='tab_cadre' cellpadding='2'>";
      echo "<tr><th>".__('Test Graphviz', 'archires')."</th></tr>";
      echo "<tr class='tab_bg_1'><td>";
      echo "<img src='./archires.test.php' alt=''>";
      echo "</td></tr>";
      echo "</table>";

      echo "</div>";
   }


   function graphItems($device,$device_id,$itemtype,$format,$image_name,$url,$PluginArchiresView) {
      global $DB;

      $PluginArchiresStateColor = new PluginArchiresStateColor();

      $device_unique_name  = $itemtype . "_" . $device_id . "_";
      $device_unique_name .= $device["name"];

      $graph  = "\"".$device_unique_name."\"[shape=plaintext, label=";
      //label
      $graph .= "<<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";

      //img
      $graph .= "<tr><td><img src=\"".realpath(GLPI_ROOT)."/plugins/archires/".$image_name. "\"/>".
                "</td></tr>";

      $graph .= "<tr><td> </td></tr><tr><td>".$device["name"];
      //ip / type
      $graph .= self::displayTypeAndIP($PluginArchiresView, $itemtype, $device, true);
      //entity
      if ($PluginArchiresView->fields["display_entity"]!=0 && isset($device["entity"])) {
         $graph .= "<tr><td>".$this->CleanField(Dropdown::getDropdownName("glpi_entities",
                                                                         $device["entity"])).
                   "</td></tr>";
      }
      //location
      if ($PluginArchiresView->fields["display_location"]!=0 && isset($device["locations_id"])) {
         $graph .= "<tr><td>".$this->CleanField(Dropdown::getDropdownName("glpi_locations",
                                                                         $device["locations_id"])).
                   "</td></tr>";
      }

      //state
      if ($PluginArchiresView->fields["display_state"]!=0 && isset($device["states_id"])) {
         $graph .="<tr><td>".$PluginArchiresStateColor->displayColorState($device)."</td></tr>";
      }

      $graph .= "</table>>";
      //end label

      //link - users
      $graph .=self::displayUsers($url, $device, true);

      $graph .="];\n";

      return $graph;
   }


   function graphPorts($devices, $ports, $wire, $format, $PluginArchiresView) {
      global $DB, $CFG_GLPI;

      $PluginArchiresNetworkInterfaceColor = new PluginArchiresNetworkInterfaceColor();
      $PluginArchiresVlanColor             = new PluginArchiresVlanColor();

      $items_id1              = $ports[$wire["networkports_id_1"]]["items_id"];
      $itemtype1              = $ports[$wire["networkports_id_1"]]["itemtype"];
      $logical_number1        = $ports[$wire["networkports_id_1"]]["logical_number"];
      $name1                  = $ports[$wire["networkports_id_1"]]["namep"];
      $ID1                    = $ports[$wire["networkports_id_1"]]["idp"];
      $networkinterfaces_id1  = $ports[$wire["networkports_id_1"]]["instantiation_type"];
      $ip1                    = $ports[$wire["networkports_id_1"]]["ip"];
      $netmask1               = $ports[$wire["networkports_id_2"]]["netmask"];
      $device_unique_name1    = $itemtype1 . "_" . $items_id1 . "_";
      $device_unique_name1   .= $devices[$itemtype1][$items_id1]["name"];

      $items_id2              = $ports[$wire["networkports_id_2"]]["items_id"];
      $itemtype2              = $ports[$wire["networkports_id_2"]]["itemtype"];
      $logical_number2        = $ports[$wire["networkports_id_2"]]["logical_number"];
      $name2                  = $ports[$wire["networkports_id_2"]]["namep"];
      $ID2                    = $ports[$wire["networkports_id_2"]]["idp"];
      $networkinterfaces_id2  = $ports[$wire["networkports_id_2"]]["instantiation_type"];
      $ip2                    = $ports[$wire["networkports_id_2"]]["ip"];
      $netmask2               = $ports[$wire["networkports_id_2"]]["netmask"];
      $device_unique_name2    = $itemtype2 . "_" . $items_id2 . "_";
      $device_unique_name2   .= $devices[$itemtype2][$items_id2]["name"];

      $graph = "";

      if ($PluginArchiresView->fields["color"] == PluginArchiresView::PLUGIN_ARCHIRES_NETWORK_COLOR ) {
         if (empty($networkinterfaces_id1) && empty($networkinterfaces_id2)) {
            $graph .= "edge [color=black,arrowsize=1, fontname=\"Verdana\", fontsize=\"5\"];\n";

         } else if (!empty($networkinterfaces_id1)) {
            if ($PluginArchiresNetworkInterfaceColor->getFromDBbyNetworkInterface($networkinterfaces_id1)) {
               $graph .= "edge [color=".$PluginArchiresNetworkInterfaceColor->fields["color"].",
                                fontname=\"Verdana\", fontsize=\"5\"];\n";
            } else {
               $graph .= "edge [color=black,arrowsize=1, fontname=\"Verdana\", fontsize=\"5\"];\n";
            }

         } else {
            if ($PluginArchiresNetworkInterfaceColor->getFromDBbyNetworkInterface($networkinterfaces_id2)) {
               $graph .= "edge [color=".$PluginArchiresNetworkInterfaceColor->fields["color"].",
                                fontname=\"Verdana\", fontsize=\"5\"];\n";
            } else {
               $graph .= "edge [color=black,arrowsize=1, fontname=\"Verdana\", fontsize=\"5\"];\n";
            }
         }
      } else if ($PluginArchiresView->fields["color"] == PluginArchiresView::PLUGIN_ARCHIRES_VLAN_COLOR) {
         $vlan1 = $PluginArchiresVlanColor->getVlanbyNetworkPort($ID1);
         $vlan2 = $PluginArchiresVlanColor->getVlanbyNetworkPort($ID2);

         if (empty($vlan1) && empty($vlan2)) {
            $graph .= "edge [color=black,arrowsize=1, fontname=\"Verdana\", fontsize=\"5\"];\n";

         } else if (!empty($vlan1)) {
            if ($PluginArchiresVlanColor->getFromDBbyVlan($vlan1)) {
               $graph .= "edge [color=".$PluginArchiresVlanColor->fields["color"].",
                                fontname=\"Verdana\", fontsize=\"5\"];\n";
            } else {
               $graph .= "edge [color=black,arrowsize=1, fontname=\"Verdana\", fontsize=\"5\"];\n";
            }

         } else {
            if ($PluginArchiresVlanColor->getFromDBbyVlan($vlan2)) {
               $graph .= "edge [color=".$PluginArchiresVlanColor->fields["color"].",
                                fontname=\"Verdana\", fontsize=\"5\"];\n";
            } else {
               $graph .= "edge [color=black,arrowsize=1, fontname=\"Verdana\", fontsize=\"5\"];\n";
            }
         }
      }
      //Display Ports
      if (($PluginArchiresView->fields["display_ports"] != 0)
          && ($PluginArchiresView->fields["engine"] != 1)) {
         $url_ports = $CFG_GLPI["root_doc"] . "/front/networkport.form.php?id=";
         $graph .= "\"".$device_unique_name1."\"";
         $graph .= " -- \"".$device_unique_name2."\"[label=";
         $graph .= "<<table border=\"0\" cellpadding=\"2\" cellspacing=\"2\">";
         //display ip ports
         if ($PluginArchiresView->fields["display_ip"] != 0) {
            if (!empty($ip1)) {
               $graph .= "<tr><td>".$ip1;
               if (!empty($netmask1)) {
                  $graph = sprintf(__('%1$s / %2$s'), $graph, $netmask1);
               }
               $graph .= "</td></tr>";
            }
         }
         $graph .= "<tr><td HREF=\"".$url_ports.$ID1."\" tooltip=\"".$name1;
         if ($_SESSION["glpiis_ids_visible"] || empty($name1)) {
            $graph.= "_".$ID1."_";
         }
         $graph .= "\">";

         if ($PluginArchiresView->fields["display_ports"]==1) {
            $graph .= __('Socket', 'archires')." ".$logical_number1;
         } else if ($PluginArchiresView->fields["display_ports"]==2) {
            $graph .= $name1;
            if ($_SESSION["glpiis_ids_visible"] || empty($name1)) {
                $graph = sprintf(__('%1$s (%2$s)'), $graph, $ID1);
            }
         }
         $graph .= "</td></tr>";

         if ($format!='svg') {
            $graph .= "<tr><td><img src= 'pics/socket.png' /></td></tr>";
         } else {
            $graph .= "<tr><td><img src=\"".realpath(GLPI_ROOT)."/plugins/archires/pics/socket.png\"/>".
                      "</td></tr>";
         }
         $graph .= "<tr><td HREF=\"".$url_ports.$ID2."\" tooltip=\"".$name2;
         if ($_SESSION["glpiis_ids_visible"] || empty($name2)) {
            $graph.= "_".$ID2."_";
         }
         $graph .= "\">";

         if ($PluginArchiresView->fields["display_ports"]==1) {
            $graph .= __('Socket', 'archires')." ".$logical_number2;
         } else if ($PluginArchiresView->fields["display_ports"]==2) {
            $graph .= $name2;
            if ($_SESSION["glpiis_ids_visible"] || empty($name2)) {
               $graph = sprintf(__('%1$s (%2$s)'), $graph, $ID2);
            }
         }
         $graph .= "</td></tr>";

         //display ip ports
         if ($PluginArchiresView->fields["display_ip"] != 0) {
            if (!empty($ip2)) {
               $graph .= "<tr><td>".$ip2;
               if (!empty($netmask2)) {
                  $graph = sprintf(__('%1$s / %2$s'), $graph, $netmask2);
               }
               $graph .= "</td></tr>";
            }
         }
         $graph .= "</table>>];\n";

      } else {
         $graph .= "\"".$device_unique_name1."\"";
         $graph .= " -- \"".$device_unique_name2."\";\n";
      }

      return $graph;
   }


   static function displayGraph($item, $plugin_archires_views_id, $select=0) {
      global $DB,$CFG_GLPI;

      $querytype   = $item->getType();
      $ID          = $item->getID();
      $object_view = $item->fields["plugin_archires_views_id"];
      $graph       = new self();

      if (!isset($plugin_archires_views_id)) {
        $plugin_archires_views_id = $object_view;
      }
      if (!$object_view && !$plugin_archires_views_id) {
        return false;
      }

      $PluginArchiresView                  = new PluginArchiresView();
      $PluginArchiresView->getFromDB($plugin_archires_views_id);
      $PluginArchiresNetworkInterfaceColor = new PluginArchiresNetworkInterfaceColor();
      $PluginArchiresVlanColor             = new PluginArchiresVlanColor();
      $PluginArchiresStateColor            = new PluginArchiresStateColor();

      echo "<div class='center'>";
      $PluginArchiresView->viewSelect($item, $plugin_archires_views_id, $select);
      echo "</div>";

      if (isset($ID) && !empty($ID)) {
         echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/archires/front/archires.map.php?id=".$ID.
               "&amp;querytype=".$querytype."&amp;plugin_archires_views_id=".$plugin_archires_views_id.
               "' alt='' usemap='#G'>";
         echo $graph->createGraph("cmapx", $item, $plugin_archires_views_id);
      }
      //legend
      if (isset($ID) && !empty($ID)) {
         echo "<div align='center'>";
         echo "<table cellpadding='5'>";
         echo "<tr><td class='top'>";
         if ( $PluginArchiresView->fields["color"] == 0 ) {
            $PluginArchiresNetworkInterfaceColor->showConfigForm();
         } else if ($PluginArchiresView->fields["color"] == 1 ) {
            $PluginArchiresVlanColor->showConfigForm();
         }
         echo "</td><td>".$PluginArchiresStateColor->showConfigForm()."</td>";
         echo "</tr>";
         echo "</table>";
         echo "</div>";

      } else {
         echo "<div class='center'><br><br><img src=\"".$CFG_GLPI["root_doc"].
               "/pics/warning.png\" alt='warning'><br><br>";
         echo "<b>".__('No item found')."</b></div>";
      }
   }


   function createGraph($format,$obj,$plugin_archires_views_id) {
      global $DB,$CFG_GLPI;

      $type        = get_class($obj);
      $ID          = $obj->fields["id"];
      $object_view = $obj->fields["plugin_archires_views_id"];

      if (!isset($plugin_archires_views_id)) {
        $plugin_archires_views_id = $object_view;
      }
      $PluginArchiresView = new PluginArchiresView();

      $plugin = new Plugin();
      if ($plugin->isActivated("appliances")) {
         $PluginArchiresApplianceQuery = new PluginArchiresApplianceQuery();
      }
      $PluginArchiresLocationQuery         = new PluginArchiresLocationQuery();
      $PluginArchiresNetworkEquipmentQuery = new PluginArchiresNetworkEquipmentQuery();
      $PluginArchiresImageItem             = new PluginArchiresImageItem();

      $PluginArchiresView->getFromDB($plugin_archires_views_id);

      $devices = array();
      $ports   = array();

      if ($type == 'PluginArchiresLocationQuery') {
         $devices = $PluginArchiresLocationQuery->Query($ID, $PluginArchiresView, true);
         $ports   = $PluginArchiresLocationQuery->Query($ID, $PluginArchiresView, false);

      } else if ($type == 'PluginArchiresNetworkEquipmentQuery') {
         $devices = $PluginArchiresNetworkEquipmentQuery->Query($ID, $PluginArchiresView, true);
         $ports   = $PluginArchiresNetworkEquipmentQuery->Query($ID, $PluginArchiresView, false);

      } else if ($type == 'PluginArchiresApplianceQuery') {
         $devices = $PluginArchiresApplianceQuery->Query($ID, $PluginArchiresView, true);
         $ports   = $PluginArchiresApplianceQuery->Query($ID, $PluginArchiresView, false);
      }
      $wires = array();

      $query = "SELECT `id`, `networkports_id_1`, `networkports_id_2`
                FROM `glpi_networkports_networkports`";

      if ($result = $DB->query($query)) {
         while ($data = $DB->fetch_array($result)) {
            $wires[$data["id"]]["networkports_id_1"] = $data["networkports_id_1"];
            $wires[$data["id"]]["networkports_id_2"] = $data["networkports_id_2"];
         }
      }

      $graph  = "graph G {\n";
      $graph .= "overlap=false;\n";

      $graph .= "bgcolor=white;\n";

      //items
      $graph .= "node [shape=polygon, sides=6, fontname=\"Verdana\", fontsize=\"5\"];\n";

      foreach ($devices as $itemtype => $typed_devices) {
         foreach ($typed_devices as $device_id => $device) {
            $image_name = $PluginArchiresImageItem->displayItemImage($device["type"], $itemtype,
                                                                     false);
            $link = Toolbox::getItemTypeFormURL($itemtype);
            $url = $link."?id=".$device_id;

            $graph .= $this->graphItems($device, $device_id, $itemtype, $format, $image_name, $url,
                                        $PluginArchiresView);
         }
      }

      foreach ($wires as $wire) {
         if (!empty($ports[$wire["networkports_id_1"]])
             && !empty($ports[$wire["networkports_id_2"]])
             && isset($ports[$wire["networkports_id_1"]])
             && isset($ports[$wire["networkports_id_2"]]) ) {

            $graph .= $this->graphPorts($devices,$ports,$wire,$format,$PluginArchiresView);
         }
      }

      $graph .= "}\n";

      return $this->generateGraphviz($graph,$format,$PluginArchiresView);
   }


   function generateGraphviz($graph, $format, $PluginArchiresView) {

      if ($PluginArchiresView->fields["engine"] != 0) {
         $engine_archires = "neato";
      } else {
         $engine_archires = "dot";
      }

      return self::dotIt($engine_archires, $graph, $format);
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'PluginArchiresApplianceQuery' :
         case 'PluginArchiresLocationQuery' :
         case 'PluginArchiresNetworkEquipmentQuery' :
            switch ($tabnum) {
               case 1 :
                  self::test($item);
                  break;

               case 2 :
                  $plugin_archires_views_id = $item->fields["plugin_archires_views_id"];
                  self::displayGraph($item, $plugin_archires_views_id);
                  PluginArchiresView::linkToAllViews($item);
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
               return array('1' => __('Test'),
                            '2' => __('Generation', 'archires'));
         }
      }
      return '';
   }

}
?>