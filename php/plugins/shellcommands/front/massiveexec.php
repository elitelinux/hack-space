<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Shellcommands plugin for GLPI
  Copyright (C) 2003-2011 by the Shellcommands Development Team.

  https://forge.indepnet.net/projects/shellcommands
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Shellcommands.

  Shellcommands is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Shellcommands is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with shellcommands. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Html::header(PluginShellcommandsShellcommand::getTypeName(2), '', "plugins", "shellcommands");

$plugin_shellcommands_shellcommands_id = $_GET['plugin_shellcommands_shellcommands_id'];

$itemtype = $_SESSION["plugin_shellcommands"]["itemtype"];
unset($_SESSION["plugin_shellcommands"]["itemtype"]);
$tab_id = unserialize($_SESSION["plugin_shellcommands"]["tab_id"]);
unset($_SESSION["plugin_shellcommands"]["tab_id"]);

$command = new PluginShellcommandsShellcommand();
$command_item = new PluginShellcommandsShellcommand_Item();

$command->getFromDBbyName($plugin_shellcommands_shellcommands_id);
$path = Dropdown::getDropdownName("glpi_plugin_shellcommands_shellcommandpaths", $command->fields["plugin_shellcommands_shellcommandpaths_id"]);
$parameters = $command->fields["parameters"];
$link = $command->fields["link"];

echo "<div align='center'>";
echo "<table class='tab_cadre_fixe' cellpadding='5'>";
echo "<tr><th>".__('Command', 'shellcommands');
echo "</th></tr>";

foreach ($tab_id as $key => $ID) {

   $item = new $itemtype();
   $item->getFromDB($ID);

   if ($plugin_shellcommands_shellcommands_id == "Wake on Lan"
           || $plugin_shellcommands_shellcommands_id == "Ping") {
      $ipmac = array();
      $i = 0;
      
    $query = "SELECT `glpi_networkports`.*, `glpi_ipaddresses`.`name` as ip, `glpi_ipnetworks`.`netmask`
               FROM `glpi_networkports`
               LEFT JOIN `glpi_networknames`
                  ON (`glpi_networknames`.`items_id`=`glpi_networkports`.`id`)                            
               LEFT JOIN `glpi_ipaddresses`
                  ON (`glpi_networknames`.`id`=`glpi_ipaddresses`.`items_id`)
               LEFT JOIN `glpi_ipaddresses_ipnetworks`
                  ON (`glpi_ipaddresses`.`id`=`glpi_ipaddresses_ipnetworks`.`ipaddresses_id`) 
               LEFT JOIN `glpi_ipnetworks`
                  ON (`glpi_ipaddresses_ipnetworks`.`ipnetworks_id`=`glpi_ipnetworks`.`id`)
               WHERE `glpi_networkports`.`items_id` = '$ID' 
               AND `glpi_networkports`.`itemtype` = '".$item->getType()."' 
               ORDER BY `glpi_networkports`.`logical_number`";
    
        
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         while ($data = $DB->fetch_array($result)) {     
            $ipmac[$i]['host'] = $item->getField('name');
            $ipmac[$i]['name'] = $data["name"];
            $ipmac[$i]['ip'] = $data["ip"];
            $ipmac[$i]['mac'] = $data["mac"];
            $ipmac[$i]['netmask'] = $data["netmask"];
            $i++; 
         }
      }
   }

   if ($plugin_shellcommands_shellcommands_id == "Tracert") {
      $host = Dropdown::getDropdownName("glpi_domains", $item->getField('domains_id'));
   } else {
      $host = str_replace("[NAME]", $item->getField('name'), $link);
   }


   echo "<tr class='tab_bg_1'><td>";
//ping windows $command="ping.exe -a -n 1 -l 10 " .$val['ip'];
//ping linux $command="ping -c 2 ".$val['ip'];;
   if ($plugin_shellcommands_shellcommands_id == "Ping") {
      $host = $item->getField('name');
      echo "<p><b>".$item->getField('name')."</b></p>";
      if ($itemtype != 'NetworkEquipement') {
         if (count($ipmac) > 0) {
            foreach ($ipmac as $key => $val) {
               if((!empty($val["ip"]) && $val["ip"] != '0.0.0.0')){
                  $command = $path." ".$parameters." ".$val['ip'];
                  $ouput[] = null;
                  echo "<p><b>$plugin_shellcommands_shellcommands_id -> ".$val['ip']."</b>";
                  exec($command, $ouput);
                  $compte = count($ouput);
                  echo "<font color=blue>";
                  for ($i = 0; $i < $compte; $i++) {
                     echo Toolbox::encodeInUtf8($ouput[$i])."<br>";
                  }
                  echo "</font>";
                  echo "</p>".$command;
                  unset($ouput);
               }
            }
         }
      } else {
         $host = $item->getField('ip');
         $command = $path." ".$parameters." ".$host;
         $ouput[] = null;
         echo "<p><b>$plugin_shellcommands_shellcommands_id -> ".$host."</b>";
         exec($command, $ouput);
         $cmd = count($ouput);
         echo " <font color=blue>";
         for ($i = 0; $i < $cmd; $i++) {
            echo Toolbox::encodeInUtf8($ouput[$i])."<br>";
         }
         echo "</font>";
         echo "</p>".$command;
      }
   } else if ($plugin_shellcommands_shellcommands_id == "Wake on Lan") {
      if ($itemtype != 'NetworkEquipement') {
         if (count($ipmac) > 0) {
            foreach ($ipmac as $key => $val) {
               if(!empty($data2["mac"])){
                  echo "<p><b>$plugin_shellcommands_shellcommands_id -> ".$val['host']." (".$val['mac'].")</b></p>";
                  $command = $command_item->sendMagicPacket($val['mac'], $val['ip'], $val['netmask']);
               }
            }
         }
      } else {
         $host = $item->getField('mac');
         echo "<p><b>$plugin_shellcommands_shellcommands_id -> ".$host."</b></p>";
         $command = $command_item->sendMagicPacket($host);
      }
   } else {
      echo "<p><b>$plugin_shellcommands_shellcommands_id -> ".$host."</b></p>";
      $command = $path." ".$parameters." ".$host;
      $ouput[] = null;
      exec($command, $ouput);
      $cmd = count($ouput);
      echo " <font color=blue>";
      for ($i = 0; $i < $cmd; $i++) {
         echo Toolbox::encodeInUtf8($ouput[$i])."<br>";
      }
      echo "</font>";
      echo "<br>".$command;
   }

   echo "</td>";
   echo "</tr>";
   unset($ouput);
}
echo "</table></div>";

Html::footer();
?>