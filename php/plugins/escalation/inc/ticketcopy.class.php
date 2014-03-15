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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginEscalationTicketCopy extends CommonDBRelation {

   /**
    * Display tab
    *
    * @param CommonGLPI $item
    * @param integer $withtemplate
    *
    * @return varchar name of the tab(s) to display
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Ticket'
              && $item->getID() > 0
              && PluginEscalationProfile::haveRight("copyticket", 1)) {
         return "Copie de ticket";
      }
      return '';
   }



   /**
    * Display content of tab
    *
    * @param CommonGLPI $item
    * @param integer $tabnum
    * @param interger $withtemplate
    *
    * @return boolean TRUE
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Ticket') {
         $peTicketCopy = new self();
         $peTicketCopy->showForm($item);
      }
      return TRUE;
   }

    
   
   function showForm(Ticket $ticket) {
      global $CFG_GLPI;

      echo "<form method='post' name='' id=''  action=\"".$CFG_GLPI['root_doc'] . 
         "/plugins/escalation/front/ticketcopy.form.php\">";
      echo "<table width='950' class='tab_cadre_fixe'>";
      
      echo "<tr>";
      echo "<th colspan='3'>";
      echo "Copie de ticket (Liste des champs à copier)";
      echo "</th>";
      echo "</tr>";
      
      echo "<tr>";
      echo "<td colspan='3' align='center'>";
      echo "<a href=\"javascript:showHideDiv('listfields','imgcat0','../../pics/folder.png',".
              "'../../pics/folder-open.png');\">";
      echo "Voir tous les champs</a>";
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>";
      echo "</td>";
      echo "<td>";
      echo "Lier au ticket";
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('link');
      echo "</td>";
      echo "</tr>";
  
      $this->displayField(__('Title'), "name", $ticket->fields['name'], '', 'checked');
      
      $this->displayField(__('Description'), "content", $ticket->fields['content'], '', 
                          'checked');      
    
      echo "</table>";

      
      
      echo "<div id='listfields' style='display:none;'>";
      echo "<table width='950' class='tab_cadre_fixe'>";
      
      $this->displayField(__('Status'), "status", 
         Ticket::getStatus($ticket->fields['status']), $ticket->fields['status']);
      
      $this->displayField(__('Type'), "type", 
         Ticket::getTicketTypeName($ticket->fields['type']), $ticket->fields['type']);
      
      $this->displayField(__('Urgency'), "urgency", 
         Ticket::getUrgencyName($ticket->fields['urgency']), $ticket->fields['urgency']); 
      
      $this->displayField(__('Impact'), "impact", 
         Ticket::getImpactName($ticket->fields['impact']), $ticket->fields['impact']);
      
      $this->displayField(__('Priority'), "priority", 
         Ticket::getPriorityName($ticket->fields['priority']), $ticket->fields['priority']);
      
      $this->displayField(__('Due date'), "due_date", 
         Html::convDateTime($ticket->fields['due_date']), $ticket->fields['due_date']);
      
      $this->displayField(__('Category'), "itilcategories_id", 
         Dropdown::getDropdownName('glpi_itilcategories', $ticket->fields['itilcategories_id']), 
         $ticket->fields['itilcategories_id']);
      
      if ($ticket->fields['items_id'] > 0) {
         $this->displayField(__('Associated element').' - '.__('Type'), "itemtype", 
            call_user_func(array($ticket->fields['itemtype'], 'getTypeName')), 
            $ticket->fields['itemtype']);
         
         $this->displayField(__('Associated element'), "items_id", 
         Dropdown::getDropdownName(getTableForItemType($ticket->fields['itemtype']), 
                                   $ticket->fields['items_id']), 
         $ticket->fields['items_id']);
      }
      
      $this->displayField(__('Request source'), "requesttypes_id", 
         Dropdown::getDropdownName('glpi_requesttypes', $ticket->fields['requesttypes_id']),
         $ticket->fields['requesttypes_id']);
            
      $this->displayField(__('SLA'), "slas_id", 
         Dropdown::getDropdownName('glpi_slas', $ticket->fields['slas_id']),
         $ticket->fields['slas_id']);
      
      $ticket_User = new Ticket_User();
      
      $a_ticket_users = $ticket_User->find("`tickets_id`='".$ticket->getID()."'
         AND `type`='1'");
      foreach ($a_ticket_users as $data) {
         $name = '';
         if ($data['users_id'] == 0) {
            $name = $data['alternative_email'];
         } else {
            $name = Dropdown::getDropdownName('glpi_users', $data['users_id']);
         }
         $this->displayField(__('Requester'), "_users_id_requester", 
            $name, $data['id']);
      }
      
      $group_Ticket = new Group_Ticket();
      
      $a_group_tickets = $group_Ticket->find("`tickets_id`='".$ticket->getID()."'
         AND `type`='1'");
      foreach ($a_group_tickets as $data) {
         $this->displayField(__('Requester group'), "_groups_id_requester", 
            Dropdown::getDropdownName('glpi_groups', $data['groups_id']), $data['groups_id']);
      }

      // Techs
      $peConfig = new PluginEscalationConfig();
      if ($peConfig->getValue('workflow', $ticket->fields['entities_id']) == '0') {
         $a_ticket_uers = $ticket_User->find("`tickets_id`='".$ticket->getID()."'
            AND `type`='2'");
         foreach ($a_ticket_uers as $data) {
            $name = '';
            if ($data['users_id'] == 0) {
               $name = $data['alternative_email'];
            } else {
               $name = Dropdown::getDropdownName('glpi_users', $data['users_id']);
            }
            $this->displayField(__('Technician'), "_users_id_assign", 
               $name, $data['id']);
         }

         $a_group_tickets = $group_Ticket->find("`tickets_id`='".$ticket->getID()."'
            AND `type`='2'");
         foreach ($a_group_tickets as $data) {
            $this->displayField(__('Group in charge of the ticket'), "_groups_id_assign", 
               Dropdown::getDropdownName('glpi_groups', $data['groups_id']), $data['groups_id']);
         }
      }
      
      $ticketFollowup= new TicketFollowup();
      $followups = $ticketFollowup->find("`tickets_id`='".$ticket->getID()."'");
      foreach ($followups as $data) {
         $this->displayField(__('Follow-up'), "followup-".$data['id'], 
            $data['content'], $data['id']);
      }
      
      $ticketTask= new TicketTask();
      $tasks = $ticketTask->find("`tickets_id`='".$ticket->getID()."'");
      foreach ($tasks as $data) {
         $this->displayField(__('Task'), "task-".$data['id'], 
            $data['content'], $data['id']);
      }
      
      // Documents
//      $document_Item = new Document_Item();
//      $docs = $document_Item->find("`items_id`='".$ticket->getID()."'
//         AND `itemtype`='Ticket'");
//      foreach ($docs as $data) {
//         $this->displayField($LANG['document'][18], "filename", 
//            Dropdown::getDropdownName("glpi_documents", $data['documents_id']),
//            $data['documents_id']);
//      }
//      // filename[]
      
      
      
      echo "</table>";
      echo "</div>";
      
            
      echo "<table width='950' class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_3'>";
      echo "<td class='center'>";
      echo "<input type='submit' name='add' value=\"Créer\" class='submit'>";
      echo "<input type='hidden' name='tickets_id' value='".$ticket->fields['id']."'>";
      echo "</td>";
      echo "</tr>";  
      
      Html::closeForm();
      echo "</table>";
      
      
      echo "<br/>";
      echo "<br/>";
   }
   
   
   
   function displayField($name, $fieldname, $valuedisplay, $value='', $checked='') {
      if ($value == '') {
         $value = $valuedisplay;
      }
      $type = 'checkbox';
      $inputname = 'checked[]';
      if (strstr($fieldname, 'requester')
              || strstr($fieldname, 'assign')) {
         $type = 'radio';
         $inputname = $fieldname;
         $fieldname = $value;
      }
      
      echo "<tr class='tab_bg_3'>";
      echo "<td>";
      echo "<input type='".$type."' name='".$inputname."' value='".
              $fieldname."' ".$checked." />";
      echo "</td>";
      echo "<td>";
      echo $name;
      echo "</td>";
      echo "<td>";
      echo $valuedisplay;
      if ($type == 'checkbox') {
         $value = Html::cleanInputText(Toolbox::clean_cross_side_scripting_deep(stripslashes($value)));
         echo '<input type="hidden" name="'.$fieldname.
                 '" value="'.$value.'" />';
      }
      echo "</td>";
      echo "</tr>";      
   }
   
   
   
   /**
    * 
    * @param type $items_id id of the ticket
    */
   static function createSubTicket($items_id) {
      global $CFG_GLPI;
      
      if ($_POST['slas_id'] == 0
              || $_POST['groupsubticket'] == 0) {
//         return;
      }
      
      $ticket = new Ticket();
      $ticketFollowup = new TicketFollowup();
      $ticketTask = new TicketTask();
      $document_Item = new Document_Item();
      $ticket_User = new Ticket_User();
      $group_Ticket = new Group_Ticket();
      
      // Disable send notification
      $use_mailing = $CFG_GLPI["use_mailing"];
      $CFG_GLPI["use_mailing"] = false;
      
      $ticket->getFromDB($items_id);
      unset($ticket->fields['id']);
      $ticket->fields['_link']['link'] = 1;
      $ticket->fields['_link']['tickets_id_1'] = 0;
      $ticket->fields['_link']['tickets_id_2'] = $items_id;
      $ticket->fields['bypassgrouponadd'] = true;
      $ticket->fields['slas_id'] = $_POST['slas_id'];
      $ticket->fields['date'] = date("Y-m-d H:i:s");
      $ticket->fields = Toolbox::addslashes_deep($ticket->fields);
      foreach($ticket->fields as $key=>$value) {
         if ($value == '') {
            unset($ticket->fields[$key]);
         }
      }
      $new_tickets_id = $ticket->add($ticket->fields);
      
      $a_followups = $ticketFollowup->find("`tickets_id`='".$items_id."'", "`id`");
      foreach ($a_followups as $data) {
         unset($data['id']);
         $data = Toolbox::addslashes_deep($data);
         $data['tickets_id'] = $new_tickets_id;
         $ticketFollowup->add($data);
      }
            
      $a_tasks = $ticketTask->find("`tickets_id`='".$items_id."'", "`id`");
      foreach ($a_tasks as $data) {
         unset($data['id']);
         $data = Toolbox::addslashes_deep($data);
         $data['tickets_id'] = $new_tickets_id;
         foreach($data as $key=>$value) {
            if ($value == '') {
               unset($data[$key]);
            }
         }
         $ticketTask->add($data);
      }
            
      $a_documents = $document_Item->find("`items_id`='".$items_id."'
         AND `itemtype`='Ticket'", "`id`");
      foreach ($a_documents as $data) {
         unset($data['id']);
         $data = Toolbox::addslashes_deep($data);
         $data['items_id'] = $new_tickets_id;
         $document_Item->add($data);
      }
            
      $a_ticketusers = $ticket_User->find("`tickets_id`='".$items_id."'
         AND `type`='1'", "`id`");
      foreach ($a_ticketusers as $data) {
         unset($data['id']);
         $data = Toolbox::addslashes_deep($data);
         $data['tickets_id'] = $new_tickets_id;
         $ticket_User->add($data);
      }
            
      $a_ticketgroups = $group_Ticket->find("`tickets_id`='".$items_id."'
         AND `type`='1'", "`id`");
      foreach ($a_ticketgroups as $data) {
         unset($data['id']);
         $data = Toolbox::addslashes_deep($data);
         $data['tickets_id'] = $new_tickets_id;
         $group_Ticket->add($data);
      }
      
      $CFG_GLPI["use_mailing"] = $use_mailing;
    
      $input = array();
      $input['tickets_id'] = $new_tickets_id;
      $input['groups_id'] = $_POST['groupsubticket'];
      $input['type'] = 2;
      $group_Ticket->add($input);
      
   }
   
   
   
   static function finishAdd($item) {
      
      if (isset($_SESSION['plugin_escalation_ticketcopy'])
              && count($_SESSION['plugin_escalation_ticketcopy']) > 0) {
         
         if (isset($_SESSION['plugin_escalation_ticketcopy']['followup'])) {
            $ticketFollowup = new TicketFollowup();
            foreach ($_SESSION['plugin_escalation_ticketcopy']['followup'] as $follows_id) {
               $a_followups = $ticketFollowup->find("`id`='".$follows_id."'");
               foreach ($a_followups as $data) {
                  unset($data['id']);
                  $data = Toolbox::addslashes_deep($data);
                  $data['tickets_id'] = $item->getID();
                  $ticketFollowup->add($data);
               }               
            }
         }
         
         if (isset($_SESSION['plugin_escalation_ticketcopy']['task'])) {
            $ticketTask = new TicketTask();
            foreach ($_SESSION['plugin_escalation_ticketcopy']['task'] as $tasks_id) {
               $a_tasks = $ticketTask->find("`id`='".$tasks_id."'", "`id`");
               foreach ($a_tasks as $data) {
                  unset($data['id']);
                  $data = Toolbox::addslashes_deep($data);
                  $data['tickets_id'] = $item->getID();
                  foreach($data as $key=>$value) {
                     if ($value == '') {
                        unset($data[$key]);
                     }
                  }
                  $ticketTask->add($data);
               }             
            }
         }
         unset($_SESSION['plugin_escalation_ticketcopy']);
      }
   }
}

?>
