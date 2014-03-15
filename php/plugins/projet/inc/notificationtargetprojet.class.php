<?php
/*
 * @version $Id: HEADER 15930 2012-03-08 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Projet plugin for GLPI
 Copyright (C) 2003-2012 by the Projet Development Team.

 https://forge.indepnet.net/projects/projet
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Projet.

 Projet is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Projet is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Projet. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginProjetNotificationTargetProjet extends NotificationTarget {

   const PROJET_MANAGER = 2300;
   const PROJET_GROUP = 2301;
   const PROJET_TASK_TECHNICIAN = 2302;
   const PROJET_TASK_GROUP = 2303;
   const PROJET_TASK_CONTACT = 2304;
   
   function getEvents() {

      return array ('new' => __('A project has been added', 'projet'),
                     'update' => __('A project has been modified', 'projet'),
                     'delete' => __('A project has been deleted', 'projet'),
                     'newtask' => __('A task has been added', 'projet'),
                     'updatetask' => __('A task has been modified', 'projet'),
                     'deletetask' => __('A task has been deleted', 'projet'),
                     'add_followup' => __('A followup has been added', 'projet'),
                     'update_followup' => __('A followup has been modified', 'projet'),
                     'delete_followup' => __('A followup has been deleted', 'projet'),
                     'AlertExpiredTasks' => __('Outdated tasks', 'projet'),
                     );
   }

   /**
    * Get additionnals targets for Tickets
    */
   function getAdditionalTargets($event='') {
      
      if ($event != 'AlertExpiredTasks') {
         $this->addTarget(self::PROJET_MANAGER,
                           __('User')." ".__('responsible of the project', 'projet'));
         $this->addTarget(self::PROJET_GROUP,
                           __('Group')." ".__('responsible of the project', 'projet'));
         if ($event == 'newtask' || $event == 'updatetask' || $event == 'deletetask') {
            $this->addTarget(self::PROJET_TASK_TECHNICIAN,
                           __('User')." ".__('responsible of the task', 'projet'));
            $this->addTarget(self::PROJET_TASK_GROUP,
                           __('Group')." ".__('responsible of the task', 'projet'));
            $this->addTarget(self::PROJET_TASK_CONTACT,
                           __('Supplier')." ".__('responsible of the task', 'projet'));
         }
      }
   }

   function getSpecificTargets($data,$options) {

      //Look for all targets whose type is Notification::ITEM_USER
      switch ($data['items_id']) {

         case self::PROJET_MANAGER :
            $this->getManagerAddress();
            break;
         case self::PROJET_GROUP :
            $this->getGroupAddress();
            break;
         case self::PROJET_TASK_TECHNICIAN :
            $this->getTaskTechAddress($options);
            break;
         case self::PROJET_TASK_GROUP :
            $this->getTaskGroupAddress($options);
            break;
         case self::PROJET_TASK_CONTACT :
            $this->getTaskContactAddress($options);
            break;
      }
   }

   //Get recipient
   function getManagerAddress() {
      return $this->getUserByField ("users_id");
   }
   
   function getGroupAddress () {
      global $DB;

      $group_field = "groups_id";

      if (isset($this->obj->fields[$group_field])
                && $this->obj->fields[$group_field]>0) {

         $query = $this->getDistinctUserSql().
                   " FROM `glpi_users`
                    LEFT JOIN `glpi_groups_users` 
                    ON (`glpi_groups_users`.`users_id` = `glpi_users`.`id`)
                    WHERE `glpi_groups_users`.`groups_id` = '".$this->obj->fields[$group_field]."'";

         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }
   
   function getTaskTechAddress($options=array()) {
      global $DB;

      if (isset($options['tasks_id'])) {
         $query = "SELECT DISTINCT `glpi_users`.`id` AS id,
                          `glpi_users`.`language` AS language
                   FROM `glpi_plugin_projet_tasks`
                   LEFT JOIN `glpi_users` ON (`glpi_users`.`id` = `glpi_plugin_projet_tasks`.`users_id`)
                   WHERE `glpi_plugin_projet_tasks`.`id` = '".$options['tasks_id']."'";

         foreach ($DB->request($query) as $data) {
            $data['email'] = UserEmail::getDefaultForUser($data['id']);
            $this->addToAddressesList($data);
         }
      }
   }
   
   
   function getTaskGroupAddress ($options=array()) {
      global $DB;

      if (isset($options['groups_id'])
                && $options['groups_id']>0
                && isset($options['tasks_id'])) {

         $query = $this->getDistinctUserSql().
                   " FROM `glpi_users`
                    LEFT JOIN `glpi_groups_users` 
                    ON (`glpi_groups_users`.`users_id` = `glpi_users`.`id`) 
                    LEFT JOIN `glpi_plugin_projet_tasks` 
                    ON (`glpi_plugin_projet_tasks`.`groups_id` = `glpi_groups_users`.`groups_id`)
                    WHERE `glpi_plugin_projet_tasks`.`id` = '".$options['tasks_id']."'";
         
         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }
   
   function getTaskContactAddress() {
      global $DB;

      if (isset($this->obj->fields["contacts_id"])
          && $this->obj->fields["contacts_id"]>0
                && isset($options['tasks_id'])) {

         $query = "SELECT DISTINCT `glpi_contacts`.`email` AS email
                   FROM `glpi_contacts`
                   WHERE `glpi_contacts`.`id` = '".$this->obj->fields["contacts_id"]."'";

         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }

   function getDatasForTemplate($event,$options=array()) {
      global $CFG_GLPI, $DB;
      
      if ($event == 'AlertExpiredTasks') {
         
         $this->datas['##projet.entity##'] =
                           Dropdown::getDropdownName('glpi_entities',
                                                     $options['entities_id']);
         $this->datas['##lang.projet.entity##'] =__('Entity');
         $this->datas['##projet.action##'] = __('Outdated tasks', 'projet');

         $this->datas['##lang.task.name##'] = __('Name');
         $this->datas['##lang.task.type##'] = __('Type');
         $this->datas['##lang.task.users##'] = __('User');
         $this->datas['##lang.task.groups##'] = __('Group');
         $this->datas['##lang.task.datebegin##'] = __('Start date');
         $this->datas['##lang.task.dateend##'] = __('End date');
         $this->datas['##lang.task.planned##'] = __('Used for planning', 'projet');
         $this->datas['##lang.task.realtime##'] = __('Effective duration', 'projet');
         $this->datas['##lang.task.comment##'] =  __('Comments');
         $this->datas['##lang.task.projet##'] =  PluginProjetProjet::getTypeName(1);
         
         foreach($options['tasks'] as $id => $task) {
            $tmp = array();

            $tmp['##task.name##'] = $task['name'];
            $tmp['##task.type##'] = Dropdown::getDropdownName('glpi_plugin_projet_tasktypes',
                                                       $task['plugin_projet_tasktypes_id']);
            $tmp['##task.users##'] = Html::clean(getUserName($task['users_id']));
            $tmp['##task.groups##'] = Dropdown::getDropdownName('glpi_groups',
                                                       $task['groups_id']);
            $restrict = " `plugin_projet_tasks_id` = '".$task['id']."' ";
            $plans = getAllDatasFromTable("glpi_plugin_projet_taskplannings",$restrict);
            
            if (!empty($plans)) {
               foreach ($plans as $plan) {
                  $tmp['##task.datebegin##'] = Html::convDateTime($plan["begin"]);
                  $tmp['##task.dateend##'] = Html::convDateTime($plan["end"]);
               }
            } else {
               $tmp['##task.datebegin##'] = '';
               $tmp['##task.dateend##'] = '';
            }
            
            $tmp['##task.planned##'] = '';
            $tmp['##task.realtime##'] = Ticket::getActionTime($task["actiontime"]);
            $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['comment']));
            $tmp['##task.comment##'] = Html::clean($comment);
            $tmp['##task.projet##'] = Dropdown::getDropdownName('glpi_plugin_projet_projets',
                                                       $task['plugin_projet_projets_id']);
                                                       
            $this->datas['tasks'][] = $tmp;
         }
      } else {
      
         $events = $this->getAllEvents();

         $this->datas['##lang.projet.title##'] = $events[$event];

         $this->datas['##lang.projet.entity##'] = __('Entity');
         $this->datas['##projet.entity##'] =
                              Dropdown::getDropdownName('glpi_entities',
                                                        $this->obj->getField('entities_id'));
         $this->datas['##projet.id##'] = $this->obj->getField("id");

         $this->datas['##lang.projet.name##'] = __('Name');
         $this->datas['##projet.name##'] = $this->obj->getField("name");
         
         $this->datas['##lang.projet.datebegin##'] = __('Start date');
         $this->datas['##projet.datebegin##'] = Html::convDate($this->obj->getField('date_begin'));
         
         $this->datas['##lang.projet.dateend##'] = __('End date');
         $this->datas['##projet.dateend##'] = Html::convDate($this->obj->getField('date_end'));
         
         $this->datas['##lang.projet.users##'] = __('User');
         $this->datas['##projet.users##'] =  Html::clean(getUserName($this->obj->getField("users_id")));
         
         $this->datas['##lang.projet.groups##'] = __('Group');
         $this->datas['##projet.groups##'] =  Dropdown::getDropdownName('glpi_groups',
                                                       $this->obj->getField('groups_id'));
         
         $this->datas['##lang.projet.status##'] = __('State');
         $this->datas['##projet.status##'] =  Dropdown::getDropdownName('glpi_plugin_projet_projetstates',
                                                       $this->obj->getField('plugin_projet_projetstates_id'));
         
         $this->datas['##lang.projet.parent##'] = __('Parent project', 'projet');
         $this->datas['##projet.parent##'] =  PluginProjetProjet_Projet::displayLinkedProjetsTo($this->obj->getField('id'), true);
         
         $this->datas['##lang.projet.advance##'] = __('Progress');
         $this->datas['##projet.advance##'] =  PluginProjetProjet::displayProgressBar('100',$this->obj->getField('advance'),array('simple'=>true));
         
         $this->datas['##lang.projet.gantt##'] = __('Display on the global Gantt', 'projet');;
         $this->datas['##projet.gantt##'] =  Dropdown::getYesNo($this->obj->getField('show_gantt'));
         
         $this->datas['##lang.projet.comment##'] = __('Comments');
         $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->obj->getField("comment")));
         $this->datas['##projet.comment##'] = Html::clean($comment);
         
         $this->datas['##lang.projet.description##'] = __('Description');
         $description = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->obj->getField("description")));
         $this->datas['##projet.description##'] = Html::clean($description);
         
         $this->datas['##lang.projet.helpdesk##'] = __('Associable to a ticket');
         $this->datas['##projet.helpdesk##'] =  Dropdown::getYesNo($this->obj->getField('is_helpdesk_visible'));

         $this->datas['##lang.projet.url##'] = __('URL');
         $this->datas['##projet.url##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=plugin_projet_".
                                    $this->obj->getField("id"));

          
         //old values infos
         if (isset($this->target_object->oldvalues) && !empty($this->target_object->oldvalues) && $event=='update') {
            
            $this->datas['##lang.update.title##'] = __('Modifications', 'projet');
            
            $tmp = array();
               
            if (isset($this->target_object->oldvalues['name'])) {
               if (empty($this->target_object->oldvalues['name']))
                  $tmp['##update.name##'] = "---";
               else  
                  $tmp['##update.name##'] = $this->target_object->oldvalues['name'];
            }
            
            if (isset($this->target_object->oldvalues['date_begin'])) {
               if (empty($this->target_object->oldvalues['date_begin']))
                  $tmp['##update.datebegin##'] = "---";
               else
                  $tmp['##update.datebegin##'] = Html::convDate($this->target_object->oldvalues['date_begin']);
            }
            
            if (isset($this->target_object->oldvalues['date_end'])) {
               if (empty($this->target_object->oldvalues['date_end']))
                  $tmp['##update.dateend##'] = "---";
               else
                  $tmp['##update.dateend##'] = Html::convDate($this->target_object->oldvalues['date_end']);
            }
            
            if (isset($this->target_object->oldvalues['users_id'])) {
               if (empty($this->target_object->oldvalues['users_id']))
                  $tmp['##update.users##'] = "---";
               else
                  $tmp['##update.users##'] = Html::clean(getUserName($this->target_object->oldvalues['users_id']));
            }
            
            if (isset($this->target_object->oldvalues['groups_id'])) {
               if (empty($this->target_object->oldvalues['groups_id']))
                  $tmp['##update.groups##'] = "---";
               else
                  $tmp['##update.groups##'] = Dropdown::getDropdownName('glpi_groups',
                                                       $this->target_object->oldvalues['groups_id']);
            }        
            
            if (isset($this->target_object->oldvalues['plugin_projet_projetstates_id'])) {
               if (empty($this->target_object->oldvalues['plugin_projet_projetstates_id']))
                  $tmp['##update.status##'] = "---";
               else
                  $tmp['##update.status##'] = Dropdown::getDropdownName('glpi_plugin_projet_projetstates',
                                                       $this->target_object->oldvalues['plugin_projet_projetstates_id']);
            }
            
            if (isset($this->target_object->oldvalues['plugin_projet_projets_id'])) {
               if (empty($this->target_object->oldvalues['plugin_projet_projets_id']))
                  $tmp['##update.plugin_projet_projets_id##'] = "---";
               else
                  $tmp['##update.plugin_projet_projets_id##'] = Dropdown::getDropdownName('glpi_plugin_projet_projets',
                                                       $this->target_object->oldvalues['plugin_projet_projets_id']);
            }
            
            if (isset($this->target_object->oldvalues['advance'])) {
               if (empty($this->target_object->oldvalues['advance']))
                  $tmp['##update.advance##'] = "---";
               else
                  $tmp['##update.advance##'] = PluginProjetProjet::displayProgressBar('100',$this->target_object->oldvalues['advance'],array('simple'=>true));
            }
            
            if (isset($this->target_object->oldvalues['comment'])) {
               if (empty($this->target_object->oldvalues['comment'])) {
                  $tmp['##update.comment##'] = "---";
               } else {
                  $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->target_object->oldvalues['comment']));
                  $tmp['##update.comment##'] = Html::clean($comment);
               }
            }
            
            if (isset($this->target_object->oldvalues['description'])) {
               if (empty($this->target_object->oldvalues['description'])) {
                  $tmp['##update.description##'] = "---";
               } else {
                  $description = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->target_object->oldvalues['description']));
                  $tmp['##update.description##'] = Html::clean($description);
               }
            }
            
            if (isset($this->target_object->oldvalues['show_gantt'])) {
               if (empty($this->target_object->oldvalues['show_gantt']))
                  $tmp['##update.gantt##'] = "---";
               else
                  $tmp['##update.gantt##'] = Dropdown::getYesNo($this->target_object->oldvalues['show_gantt']);
            }
            
            if (isset($this->target_object->oldvalues['is_helpdesk_visible'])) {
               if (empty($this->target_object->oldvalues['is_helpdesk_visible']))
                  $tmp['##update.helpdesk##'] = "---";
               else
                  $tmp['##update.helpdesk##'] = Dropdown::getYesNo($this->target_object->oldvalues['is_helpdesk_visible']);
            }

            $this->datas['updates'][] = $tmp;
         }
         
         // Projet followup
         $restrict = "`plugin_projet_projets_id`='".$this->obj->getField('id')."'";
         
         if (isset($options['followups_id']) && $options['followups_id']) {
            $restrict .= " AND `glpi_plugin_projet_followups`.`id` = '".$options['followups_id']."'";
         }
         $restrict .= " ORDER BY `date` DESC";
         $followups = getAllDatasFromTable('glpi_plugin_projet_followups',$restrict);
         
         $this->datas['##lang.followup.description##'] = __('Content');
         $this->datas['##lang.followup.date##'] = __('Date');
         $this->datas['##lang.followup.recipient##'] = __('Writer');
            
          if (!empty($followups)) {
               
            $this->datas['##lang.followup.title##'] = _n('Associated followup' , 'Associated followups', 2, 'projet');

            foreach ($followups as $followup) {
               $tmp = array();

               $tmp['##followup.recipient##'] = Html::clean(getUserName($followup['users_id']));
               $tmp['##followup.date##'] = Html::convDate($followup['date']);
               $content = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $followup['content']));
               $tmp['##followup.description##'] = Html::clean($content);
               $this->datas['followups'][] = $tmp;
            }
         }
         
         //task infos
         $restrict = "`plugin_projet_projets_id`='".$this->obj->getField('id')."'";
         
         if (isset($options['tasks_id']) && $options['tasks_id']) {
            $restrict .= " AND `glpi_plugin_projet_tasks`.`id` = '".$options['tasks_id']."'";
         }
         $restrict .= " ORDER BY `name` DESC";
         $tasks = getAllDatasFromTable('glpi_plugin_projet_tasks',$restrict);
         
         $this->datas['##lang.task.title##'] = _n('Associated task' , 'Associated tasks', 2, 'projet');

         $this->datas['##lang.task.name##'] = __('Name');
         $this->datas['##lang.task.users##'] = __('User');
         $this->datas['##lang.task.groups##'] = __('Group');
         $this->datas['##lang.task.contacts##'] = __('Supplier');
         $this->datas['##lang.task.type##'] = __('Type');
         $this->datas['##lang.task.status##'] = __('State');
         $this->datas['##lang.task.advance##'] = __('Progress');
         $this->datas['##lang.task.priority##'] = __('Priority');
         $this->datas['##lang.task.comment##'] = __('Comments');
         $this->datas['##lang.task.sub##'] = __('Results');
         $this->datas['##lang.task.others##'] = __('Others participants', 'projet');
         $this->datas['##lang.task.affect##'] = __('Affected people', 'projet');
         $this->datas['##lang.task.parenttask##'] = __('Parent task', 'projet');
         $this->datas['##lang.task.gantt##'] = __('Display on the Gantt', 'projet');
         $this->datas['##lang.task.depends##'] = __('Dependent', 'projet');
         $this->datas['##lang.task.realtime##'] = __('Effective duration', 'projet');
         $this->datas['##lang.task.location##'] = __('Location');
         $this->datas['##lang.task.projet##'] = _n('Project', 'Projects', 2, 'projet');
         
         if (!empty($tasks)) {
               $this->datas['##task.title##'] = _n('Associated task' , 'Associated tasks', 2, 'projet');
            foreach ($tasks as $task) {
               $tmp = array();

               $tmp['##task.name##'] = $task['name'];
               $tmp['##task.users##'] = Html::clean(getUserName($task['users_id']));
               $tmp['##task.groups##'] = Dropdown::getDropdownName('glpi_groups',
                                                          $task['groups_id']);
               $tmp['##task.contacts##'] = Dropdown::getDropdownName('glpi_contacts',
                                                          $task['contacts_id']);
               $tmp['##task.type##'] = Dropdown::getDropdownName('glpi_plugin_projet_tasktypes',
                                                          $task['plugin_projet_tasktypes_id']);
               $tmp['##task.status##'] = Dropdown::getDropdownName('glpi_plugin_projet_taskstates',
                                                       $task['plugin_projet_taskstates_id']);
               $tmp['##task.advance##'] = PluginProjetProjet::displayProgressBar('100',$task['advance'],array('simple'=>true));
               $tmp['##task.priority##'] = Ticket::getPriorityName($task['priority']);
               $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['comment']));
               $tmp['##task.comment##'] = Html::clean($comment);
               $sub = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['sub']));
               $tmp['##task.sub##'] = Html::clean($sub);
               $others = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['others']));
               $tmp['##task.others##'] = Html::clean($others);
               $affect = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['affect']));
               $tmp['##task.affect##'] = Html::clean($affect);
               $tmp['##task.parenttask##'] = PluginProjetTask_Task::displayLinkedProjetTasksTo($task['id'],true);
               $tmp['##task.gantt##'] = Dropdown::getYesNo($task['show_gantt']);
               $tmp['##task.depends##'] = Dropdown::getYesNo($task['depends']);
               $tmp['##task.realtime##'] = Ticket::getActionTime($task["actiontime"]);
               $tmp['##task.location##'] = Dropdown::getDropdownName('glpi_locations',
                                                          $task['locations_id']);
               $tmp['##task.projet##'] = Dropdown::getDropdownName('glpi_plugin_projet_projets',
                                                          $task['plugin_projet_projets_id']);
                                                          
               $this->datas['tasks'][] = $tmp;
            }
         }
      }
   }
   
   function getTags() {

      $tags = array('projet.id'           => __('ID'),
                     'projet.name'        => __('Name'),
                     'projet.users'       => __('User'),
                     'projet.groups'      => __('Group'),
                     'projet.datebegin'   => __('Start date'),
                     'projet.dateend'     => __('End date'),
                     'projet.status'      => __('Status'),
                     'projet.parent'      => __('Parent project', 'projet'),
                     'projet.advance'     => __('Progress'),
                     'projet.gantt'       => __('Display on the global Gantt', 'projet'),
                     'projet.comment'     => __('Comments'),
                     'projet.description' => __('Description'),
                     'projet.helpdesk'    => __('Associable to a ticket'),
                     'update.name'        => __('Name'),
                     'update.users'       => __('User'),
                     'update.groups'      => __('Group'),
                     'update.datebegin'   => __('Start date'),
                     'update.dateend'     => __('End date'),
                     'update.status'      => __('State'),
                     'update.parent'      => __('Parent project', 'projet'),
                     'update.advance'     => __('Progress'),
                     'update.gantt'       => __('Display on the global Gantt', 'projet'),
                     'update.comment'     => __('Comments'),
                     'update.description' => __('Description'),
                     'update.helpdesk'    => __('Associable to a ticket'),
                     'task.name'          => __('Name'),
                     'task.type'          => __('Type'),
                     'task.status'        => __('State'),
                     'task.users'         => __('User'),
                     'task.groups'        => __('Group'),
                     'task.contacts'      => __('Supplier'),
                     'task.advance'       => __('Progress'),
                     'task.priority'      => __('Priority'),
                     'task.parenttask'    => __('Parent task', 'projet'),
                     'task.gantt'         => __('Display on the Gantt', 'projet'),
                     'task.realtime'      => __('Effective duration', 'projet'),
                     'task.depends'       => __('Dependent', 'projet'),
                     'task.comment'       => __('Comments'),
                     'task.sub'           => __('Results'),
                     'task.others'        => __('Others participants', 'projet'),
                     'task.affect'        => __('Affected people', 'projet'));
      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag,'label'=>$label,
                                   'value'=>true));
      }
      
      $this->addTagToList(array('tag'=>'projet',
                                'label'=>__('With creation, modification, deletion of a project', 'projet'),
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('new','update','delete')));
      $this->addTagToList(array('tag'=>'updates',
                                'label'=>__('Modifications', 'projet'),
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('update')));
      $this->addTagToList(array('tag'=>'tasks',
                                'label'=>__('With creation, modification, deletion, expiration of a task', 'projet'),
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('newtask','updatetask','deletetask')));
      
      $this->addTagToList(array('tag'=>'followups',
                                'label'=>__('With creation, modification, deletion, expiration of a followup', 'projet'),
                                'value'=>false,
                                'foreach'=>false,
                                'events'=>array('add_followup','update_followup','delete_followup')));
      asort($this->tag_descriptions);
   }
}

?>