<?php

/*
   ------------------------------------------------------------------------
   Plugin Escalation for GLPI
   Copyright (C) 2012-2012 by the Plugin Escalation for GLPI Development Team.

   https://forge.indepnet.net/projects/escalation/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Escalation project.

   Plugin Escalation for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Escalation for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Escalation. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Escalation for GLPI
   @author    David Durieux
   @co-author 
   @comment   
   @copyright Copyright (c) 2011-2012 Plugin Escalation for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/escalation/
   @since     2012
 
   ------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$user = new User();
echo '<table>';
echo "<tr>";
echo "<td>";
echo __('Technician')."&nbsp: ";
echo "</td>";
echo "<td>";
$elements = array('0' => Dropdown::EMPTY_VALUE);
$query = "SELECT * FROM `glpi_groups_users`
   WHERE `groups_id`='".$_POST['groups_id']."'";
$result = $DB->query($query);
while ($data = $DB->fetch_assoc($result)) {
   $user->getFromDB($data['users_id']);
   $elements[$data['users_id']] = $user->getName();
}
Dropdown::showFromArray("_users_id_assign", $elements);
echo "</td>";
echo "</tr>";
echo "</table>";
?>