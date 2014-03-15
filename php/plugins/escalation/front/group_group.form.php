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


Html::header("escalation",$_SERVER["PHP_SELF"], "plugins", 
             "escalation", "group_group");

// manage create under ticket
if (isset($_POST['update']) 
        && isset($_POST['createsubticket'])
        && $_POST['createsubticket'] == 1) {
   PluginEscalationTicketCopy::createSubTicket($_POST['tickets_id']);
}

if (isset($_POST['update']) && $_POST['group_assign'] > 0) {
   $assign_ticket_right = $_SESSION['glpiactiveprofile']['assign_ticket'];
   $_SESSION['glpiactiveprofile']['assign_ticket'] = 1;
      
   // Add group
      $ticket = new Ticket();
      $input = array();
      $input['id'] = $_POST['tickets_id'];
      $input['_itil_assign'] = array('_type'=>'group','groups_id'=>$_POST['group_assign']);
      $ticket->getFromDB($_POST['tickets_id']);
      if ($ticket->fields['status'] == 'waiting') {
         $input['status'] = 'assign';
      }
      $input['_disablenotif'] = true;
      $ticket->update($input);
      
     
      // Check if user assigned is in this new group
      $ticket_user = new Ticket_User();
      
      $a_users = $ticket_user->find("`tickets_id`='".$_POST['tickets_id']."'
         AND `type`='2'");
      foreach ($a_users as $data) {         
         $query = "SELECT * FROM `glpi_groups_users`
            WHERE `groups_id`='".$_POST['group_assign']."'
               AND `users_id`='".$data['users_id']."'
            LIMIT 1";
         $result = $DB->query($query);
         if ($DB->numrows($result) == '0') {
            $ticket_user->delete($data);
            Event::log($_POST['tickets_id'], "ticket", 4,
              "tracking", $_SESSION["glpiname"]." ".__('Deletion of an actor to the ticket'));
         }
      }
      $_SESSION['glpiactiveprofile']['assign_ticket'] = $assign_ticket_right;
      
   // delete group
      $group_ticket = new Group_Ticket();

      $a_groups = $group_ticket->find("`tickets_id`='".$_POST['tickets_id']."'
         AND `type`='2'");
      foreach ($a_groups as $data) {   
         if ($data['groups_id'] != $_POST['group_assign']) {
            $group_ticket->delete($data);
            Event::log($_POST['tickets_id'], "ticket", 4, "tracking",
                       $_SESSION["glpiname"]." ".__('Deletion of an actor to the ticket'));
         }
      }
   Html::back();
} else if (isset($_POST['update']) AND $_POST['_users_id_assign'] > 0) {
   $assign_ticket_right = $_SESSION['glpiactiveprofile']['assign_ticket'];
   $_SESSION['glpiactiveprofile']['assign_ticket'] = 1;
   // Add
   $ticket = new Ticket();
   $input = array();
   $input['id'] = $_POST['tickets_id'];
   $input['_itil_assign'] = array('_type'=>'user','users_id'=>$_POST['_users_id_assign']);
   $ticket->update($input);
   
      $ticket_user = new Ticket_User();
      $a_users = $ticket_user->find("`tickets_id`='".$_POST['tickets_id']."'
         AND `type`='2'");
      foreach ($a_users as $data) {
         if ($data['users_id'] != $_POST['_users_id_assign']) {
            $ticket_user->delete($data);
            Event::log($_POST['tickets_id'], "ticket", 4,
              "tracking", $_SESSION["glpiname"]." ".__('Deletion of an actor to the ticket'));
         }
      }
   $_SESSION['glpiactiveprofile']['assign_ticket'] = $assign_ticket_right;
   Html::back();
} else if (isset($_POST['addgroup'])) {
   $peGroup_Group = new PluginEscalationGroup_Group();
   $peGroup_Group->add($_POST);
   Html::back();
} else if (isset($_POST['deleteitem'])) {
   $peGroup_Group = new PluginEscalationGroup_Group();
   foreach ($_POST['delgroup'] as $id) {
      $peGroup_Group->delete(array('id'=>$id));
   }
   
   Html::back();
}
Html::back();

Html::footer();
?>