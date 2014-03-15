<?php

/*
 * @version $Id: dropdownCommandValue.php 19878 2012-12-21 08:44:57Z remi $
  -------------------------------------------------------------------------
  GLPI - Gestionnaire Libre de Parc Informatique
  Copyright (C) 2003-2012 by the INDEPNET Development Team.

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

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------
// Direct access to file
if (strpos($_SERVER['PHP_SELF'], "dropdownCommandValue.php")) {
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) die("Can not acces directly to this file");

Session::checkLoginUser();

$tabValue = explode('-',$_POST['value']);

// Security
if (!($item = getItemForItemtype($_POST['itemtype'])) || sizeof($tabValue) < 2) exit();

$link = $tabValue[0];
$shellId = $tabValue[1];

$item->getFromDB($_POST['itemID']); 
$shell_item = getItemForItemtype('PluginShellcommandsShellcommand');
$shell_item->getFromDB($shellId);

$displaywith = false;

if (isset($_POST['displaywith'])
        && is_array($_POST['displaywith'])
        && count($_POST['displaywith'])) {

   $displaywith = true;
}

// No define value
if (!isset($_POST['value'])) $_POST['value'] = '';

// No define rand
if (!isset($_POST['rand'])) $_POST['rand'] = mt_rand();

if (isset($_POST['condition']) && !empty($_POST['condition'])) 
   $_POST['condition'] = rawurldecode(stripslashes($_POST['condition']));

if (!isset($_POST['emptylabel']) || $_POST['emptylabel'] == '') 
   $_POST['emptylabel'] = Dropdown::EMPTY_VALUE;

switch ($_POST['myname']) {
   case "command_name":
      if (strstr($link, '[NAME]') || strstr($link, '[ID]')) {
         // NAME or ID
         $tLink = str_replace("[NAME]", $item->getField('name'), $link);
         $shellExecute = "onClick=\"window.open('".$CFG_GLPI["root_doc"]."/plugins/shellcommands/front/shellcommand.exec.php?plugin_shellcommands_shellcommands_id=".$shell_item->getName()."&amp;value=".$tLink."' ,'mywindow', 'height=300, width=700, top=100, left=100, scrollbars=yes' )\"";
      } else if (strstr($link, '[DOMAIN]')) {
         // DOMAIN
         if (isset($item->fields['domains_id'])) 
            $shellExecute = "onClick=\"window.open('".$CFG_GLPI["root_doc"]."/plugins/shellcommands/front/shellcommand.exec.php?plugin_shellcommands_shellcommands_id=".$shell_item->getName()."&amp;value=".Dropdown::getDropdownName("glpi_domains", $item->getField('domains_id'))."' ,'mywindow', 'height=300, width=700, top=100, left=100, scrollbars=yes' )\"";
      } else if (strstr($link, '[IP]') || strstr($link, '[MAC]')) {
         // IP or MAC
         $ip = array();
         $mac = array();
         $resultSelectCommand[0] = Dropdown::EMPTY_VALUE;
         $ipCount = 0;
         $macCount = 0;
         
         if ($_POST['searchText'] != $CFG_GLPI["ajax_wildcard"]) {// if search text is called (ajax dropdown)
            $where = " AND `glpi_networkports`.`name` ".Search::makeTextSearch($_POST['searchText']);
         } else $where = '';
         
         // We search all ip and mac addresses
         $query2 = "SELECT `glpi_networkports`.*, `glpi_ipaddresses`.`name` as ip
                     FROM `glpi_networkports`
                     LEFT JOIN `glpi_networknames`
                        ON (`glpi_networknames`.`items_id`=`glpi_networkports`.`id`)                            
                     LEFT JOIN `glpi_ipaddresses`
                        ON (`glpi_networknames`.`id`=`glpi_ipaddresses`.`items_id`) 
                     WHERE `glpi_networkports`.`items_id` = '".$_POST['itemID']."' 
                     $where 
                     AND `glpi_networkports`.`itemtype` = '".$item->getType()."' 
                     ORDER BY `glpi_networkports`.`logical_number`";

         $result2 = $DB->query($query2);
         
         if ($DB->numrows($result2) > 0)
            while ($data2 = $DB->fetch_array($result2)) {
               if((!empty($data2["ip"]) && $data2["ip"] != '0.0.0.0')){
                  if(!empty($data2["name"])) $ip[$ipCount]['name'] = $data2["name"];
                  else $ip[$ipCount]['name'] = '('.__('Network port').' '.$data2["id"].')';
                  $ip[$ipCount]['ip'] = $data2["ip"];
                  $ipCount++;        
               }
               if(!empty($data2["mac"])){
                  if(!empty($data2["name"])) $mac[$macCount]['name'] = $data2["name"];
                  else $mac[$macCount]['name'] = '('.__('Network port').' '.$data2["id"].')';
                  $mac[$macCount]['mac'] = $data2["mac"];
                  $macCount++;
               }
            }
         if (strstr($link, '[IP]')) {
            // Add IP internal switch
            if ($item->getType() == 'NetworkEquipment') 
               $shellExecute = "onClick=\"window.open('".$CFG_GLPI["root_doc"]."/plugins/shellcommands/front/shellcommand.exec.php?plugin_shellcommands_shellcommands_id=".$shell_item->getName()."&amp;value=".$item->getField('ip')."' ,'mywindow', 'height=300, width=700, top=100, left=100, scrollbars=yes' )\"";
            
            if ($ipCount > 0) 
               foreach ($ip as $key => $val) $resultSelectCommand['IP-'.$shell_item->getId().'-'.$val['ip'].'-'.$key] = $val['ip'].' - '.$val['name'];
         }
         if (strstr($link, '[MAC]')) {
            // Add MAC internal switch
            if ($item->getType() == 'NetworkEquipment') 
               $shellExecute = "onClick=\"window.open('".$CFG_GLPI["root_doc"]."/plugins/shellcommands/front/shellcommand.exec.php?plugin_shellcommands_shellcommands_id=".$shell_item->getName()."&amp;value=".$item->getField('mac')."' ,'mywindow', 'height=300, width=700, top=100, left=100, scrollbars=yes' )\"";
         
            if ($macCount > 0) 
               foreach ($mac as $key => $val) $resultSelectCommand['MAC-'.$shell_item->getId().'-'.$val['mac'].'-'.$key] = $val['mac'].' - '.$val['name'];
         }
      }   

      if(isset($resultSelectCommand) && sizeof($resultSelectCommand) > 0){
         $randSelect = Dropdown::showFromArray("ip", $resultSelectCommand);
         Ajax::updateItemOnSelectEvent("dropdown_ip$randSelect", "command_ip$randSelect", $CFG_GLPI["root_doc"]."/plugins/shellcommands/ajax/dropdownCommand.php", array('idtable' => 'NetworkPort',
                'value' => '__VALUE__',
                'itemID' => $_POST['itemID'],
                'itemtype' => $item->getType(),
                'myname' => "command_ip"));

         echo "<span id='command_ip$randSelect'></span>";      
      } elseif(isset($shellExecute))
         echo '<input type="submit" name="update" value="'.__('Execute').'" class="submit" '.$shellExecute.'>';
      
      break;
   case "command_ip":  
      $name = $shell_item->getField('name');
      $ipmac = $tabValue[2];
      
      $shellExecute = "onClick=\"window.open('".$CFG_GLPI["root_doc"]."/plugins/shellcommands/front/shellcommand.exec.php?plugin_shellcommands_shellcommands_id=$name&amp;value=".$ipmac."' ,'mywindow', 'height=300, width=700, top=100, left=100, scrollbars=yes' )\"";
      echo '<input type="submit" name="update" value="'.__('Execute').'" class="submit" '.$shellExecute.'>';
      break;
}

  
if (isset($_POST["comment"]) && $_POST["comment"]) {
   $paramscomment = array('value' => '__VALUE__', 'table' => $table);
   Ajax::updateItemOnSelectEvent("dropdown_".$_POST["myname"].$_POST["rand"], 
                                 "comment_".$_POST["myname"].$_POST["rand"], 
                                 $CFG_GLPI["root_doc"]."/ajax/comments.php", $paramscomment);
}

Ajax::commonDropdownUpdateItem($_POST);
?>
