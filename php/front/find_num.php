<?php
/*
 * @version $Id: find_num.php 20129 2013-02-04 16:53:59Z moyo $
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

if (!$CFG_GLPI["use_anonymous_helpdesk"]) {
   exit();
}

// Send UTF8 Headers
header("Content-Type: text/html; charset=UTF-8");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>GLPI</title>

<?php
// Appel CSS
echo "<link rel='stylesheet' href='".$CFG_GLPI["root_doc"]."/css/styles.css' type='text/css' ".
      "media='screen' >";
// Appel javascript
echo "<script type='text/javascript' src='".$CFG_GLPI["root_doc"]."/script.js'></script>";

?>

</head>

<body>
<script language="javascript" type="text/javascript">
function fillidfield(Type,Id) {

   window.opener.document.forms["helpdeskform"].elements["items_id"].value = Id;
   window.opener.document.forms["helpdeskform"].elements["itemtype"].value = Type;
   window.close();
}
</script>

<?php

echo "<div class='center'>";
echo "<p class='b'>".__('Search the ID of your hardware')."</p>";
echo " <form name='form1' method='post' action='".$_SERVER['PHP_SELF']."'>";

echo "<table class='tab_cadre_fixe'>";
echo "<tr><th height='29'>".__('Enter the first letters (user, item name, serial or asset number)').
     "</th></tr>";
echo "<tr><td class='tab_bg_1 center'>";
echo "<input name='NomContact' type='text' id='NomContact' >";
echo "<input type='hidden' name='send' value='1'>"; // bug IE ! La validation par enter ne fonctionne pas sans cette ligne  incroyable mais vrai !
echo "<input type='submit' name='send' value='". _sx('button', 'Search')."'>";
echo "</td></tr></table>";
Html::closeForm();
echo "</div>";

if (isset($_POST["send"])) {
   echo "<table class='tab_cadre_fixe'>";
   echo " <tr class='tab_bg3'>";
   echo " <td class='center b' width='30%'>".__('Alternate username')."</td>";
   echo " <td class='center b' width='20%'>".__('Hardware type')."</td>";
   echo " <td class='center b' width='30%'>".__('Associated element')."</td>";
   echo " <td class='center b' width='5%'>".__('ID')."</td>";
   echo " <td class='center b' width='10%'>".__('Serial number')."</td>";
   echo " <td class='center b' width='10%'>".__('Inventory number')."</td>";
   echo " </tr>";

   $types = array('Computer'         => __('Computer'),
                  'NetworkEquipment' => __('Network device'),
                  'Printer'          => __('Printer'),
                  'Monitor'          => __('Monitor'),
                  'Peripheral'       => __('Device'));
   foreach ($types as $type => $label) {
      $query = "SELECT `name`, `id`, `contact`, `serial`, `otherserial`
                FROM `".getTableForItemType($type)."`
                WHERE `is_template` = '0'
                      AND `is_deleted` = '0'
                      AND (`contact` LIKE '%".$_POST["NomContact"]."%'
                           OR `name` LIKE '%".$_POST["NomContact"]."%'
                           OR `serial` LIKE '%".$_POST["NomContact"]."%'
                           OR `otherserial` LIKE '%".$_POST["NomContact"]."%')
                ORDER BY `name`";
      $result = $DB->query($query);

      while ($ligne = $DB->fetch_assoc($result)) {
         $Comp_num = $ligne['id'];
         $Contact  = $ligne['contact'];
         $Computer = $ligne['name'];
         $s1       = $ligne['serial'];
         $s2       = $ligne['otherserial'];
         echo " <tr class='tab_find' onClick=\"fillidfield(".$type.",".$Comp_num.")\">";
         echo "<td class='center'>&nbsp;$Contact&nbsp;</td>";
         echo "<td class='center'>&nbsp;$label&nbsp;</td>";
         echo "<td class='center b'>&nbsp;$Computer&nbsp;</td>";
         echo "<td class='center'>&nbsp;$Comp_num&nbsp;</td>";
         echo "<td class='center'>&nbsp;$s1&nbsp;</td>";
         echo "<td class='center'>&nbsp;$s2&nbsp;</td>";
         echo "<td class='center'>";
         echo "</td></tr>";
      }
   }

   $query = "SELECT `name`, `id`
             FROM `glpi_softwares`
             WHERE `is_template` = '0'
                   AND `is_deleted` = '0'
                   AND (`name` LIKE '%".$_POST["NomContact"]."%' )
             ORDER BY `name`";
   $result = $DB->query($query);

   while ($ligne = $DB->fetch_assoc($result)) {
      $Comp_num = $ligne['id'];
      $Computer = $ligne['name'];
      echo " <tr class='tab_find' onClick=\"fillidfield('Software',".$Comp_num.")\">";
      echo "<td class='center'>&nbsp;</td>";
      echo "<td class='center'>&nbsp;"._n('Software', 'Software', 1)."&nbsp;</td>";
      echo "<td class='center b'>&nbsp;$Computer&nbsp;</td>";
      echo "<td class='center'>&nbsp;$Comp_num&nbsp;</td>";
      echo "<td class='center'>&nbsp;</td></tr>";
   }

   echo "</table>";
}
echo '</body></html>';
?>
