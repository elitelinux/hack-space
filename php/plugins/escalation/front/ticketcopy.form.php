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
   @since     2013
 
   ------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');

include (GLPI_ROOT . "/inc/includes.php");


Html::header("escalation",$_SERVER["PHP_SELF"], "plugins", 
             "escalation", "ticketcopy");

if (isset($_POST['add'])) {
   $a_saved = array();

   $_SESSION['plugin_escalation_ticketcopy'] = array();
   
   foreach ($_POST['checked'] as $name) {
      $a_saved[$name] = $_POST[$name];
   }
   //* Manage ticket link
   if ($_POST['link'] == 1) {
      $a_saved['_link']['tickets_id_1'] = 0;
      $a_saved['_link']['link'] = '';
      $a_saved['_link']['tickets_id_2'] = $_POST['tickets_id'];
   }

   // * Manage requester
   if (isset($_POST['_users_id_requester'])) {
      
   }
      
   if (isset($_POST['_groups_id_requester'])) {
      $a_saved['_groups_id_requester'] = $_POST['_groups_id_requester'];
   }

   foreach ($a_saved as $name=>$value) {
      if (strstr($name, "followup-")) {
         $_SESSION['plugin_escalation_ticketcopy']['followup'][$value] = $value;
      }
      if (strstr($name, "task-")) {
         $_SESSION['plugin_escalation_ticketcopy']['task'][$value] = $value;
      }
   }
   
   // * Manage assign
   
   $_SESSION['helpdeskSaved'] = $a_saved;
   
   Html::redirect($CFG_GLPI['root_doc'].'/front/ticket.form.php');
}
Html::back();

Html::footer();
?>