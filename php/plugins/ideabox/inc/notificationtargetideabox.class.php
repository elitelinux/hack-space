<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Ideabox plugin for GLPI
 Copyright (C) 2003-2011 by the Ideabox Development Team.

 https://forge.indepnet.net/projects/ideabox
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Ideabox.

 Ideabox is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Ideabox is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Ideabox. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginIdeaboxNotificationTargetIdeabox extends NotificationTarget {

   const IDEABOX_USER = 4900;
   const IDEABOX_COMMENT_USER = 4901;

   function getEvents() {
      return array ('new' => __('A new idea has been submitted', 'ideabox'),
                     'update' => __('An idea has been modified', 'ideabox'),
                     'delete' => __('An idea has been deleted', 'ideabox'),
                     'newcomment' => __('A comment has been added', 'ideabox'),
                     'updatecomment' => __('A comment has been modified', 'ideabox'),
                     'deletecomment' => __('A comment has been deleted', 'ideabox'));
   }

   /**
    * Get additionnals targets for Tickets
    */
   function getAdditionalTargets($event='') {
      $this->addTarget(PluginIdeaboxNotificationTargetIdeabox::IDEABOX_USER,__('Author'));
      $this->addTarget(PluginIdeaboxNotificationTargetIdeabox::IDEABOX_COMMENT_USER,__('Comment author', 'ideabox'));
   }

   function getSpecificTargets($data,$options) {

      //Look for all targets whose type is Notification::ITEM_USER
      switch ($data['items_id']) {

         case PluginIdeaboxNotificationTargetIdeabox::IDEABOX_USER :
            $this->getUserAddress();
         break;
         case PluginIdeaboxNotificationTargetIdeabox::IDEABOX_COMMENT_USER :
            $this->getUserCommentAddress();
         break;
      }
   }

   //Get recipient
   function getUserAddress() {
      return $this->getUserByField ("users_id");
   }
   
   function getUserCommentAddress() {
      global $DB;

      $query = "SELECT DISTINCT `glpi_users`.`id` AS id
                FROM `glpi_plugin_ideabox_comments`
                LEFT JOIN `glpi_users` ON (`glpi_users`.`id` = `glpi_plugin_ideabox_comments`.`users_id`)
                WHERE `glpi_plugin_ideabox_comments`.`plugin_ideabox_ideaboxes_id` = '".$this->obj->fields["id"]."'";

      foreach ($DB->request($query) as $data) {
         $data['email'] = UserEmail::getDefaultForUser($data['id']);
         $this->addToAddressesList($data);
      }
   }

   function getDatasForTemplate($event,$options=array()) {
      global $CFG_GLPI;
      
      $events = $this->getAllEvents();

      $this->datas['##lang.ideabox.title##'] = $events[$event];

      $this->datas['##lang.ideabox.entity##'] = __('Entity');
      $this->datas['##ideabox.entity##'] =
                           Dropdown::getDropdownName('glpi_entities',
                                                     $this->obj->getField('entities_id'));
      $this->datas['##ideabox.id##'] = sprintf("%07d",$this->obj->getField("id"));

      $this->datas['##lang.ideabox.name##'] = __('Title');
      $this->datas['##ideabox.name##'] = $this->obj->getField("name");

      $this->datas['##lang.ideabox.comment##'] = __('Description');
      $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->obj->getField("comment")));
      $this->datas['##ideabox.comment##'] = nl2br($comment);

      $this->datas['##lang.ideabox.url##'] = "URL";
      $this->datas['##ideabox.url##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=plugin_ideabox_".
                                 $this->obj->getField("id"));
      
      //old values infos
      if (isset($this->target_object->oldvalues) && !empty($this->target_object->oldvalues) && $event=='update') {
         
         $this->datas['##lang.update.title##'] = __('Modified fields', 'ideabox');
         
         $tmp = array();
            
         if (isset($this->target_object->oldvalues['name']) 
            && !empty($this->target_object->oldvalues['name'])) {
            $tmp['##update.name##'] = $this->target_object->oldvalues['name'];
         }
         if (isset($this->target_object->oldvalues['comment']) 
            && !empty($this->target_object->oldvalues['comment'])) {
            $tmp['##update.comment##'] = nl2br($this->target_object->oldvalues['comment']);
         }

         $this->datas['updates'][] = $tmp;
      }

      //comment infos
      $restrict = "`plugin_ideabox_ideaboxes_id`='".$this->obj->getField('id')."'";
      
      if (isset($options['comment_id']) && $options['comment_id']) {
         $restrict .= " AND `glpi_plugin_ideabox_comments`.`id` = '".$options['comment_id']."'";
      }
      $restrict .= " ORDER BY `date_comment` DESC";
      $comments = getAllDatasFromTable('glpi_plugin_ideabox_comments',$restrict);
      
      $this->datas['##lang.comment.title##'] = _n('Associated comment' , 'Associated comments', 2, 'ideabox');
      
      $this->datas['##lang.comment.name##'] = __('Name');
      $this->datas['##lang.comment.author##'] = __('Comment author', 'ideabox');
      $this->datas['##lang.comment.datecomment##'] = __('Date');
      $this->datas['##lang.comment.comment##'] = __('Content');
      
      foreach ($comments as $comment) {
         $tmp = array();
         
         $tmp['##comment.name##'] = $comment['name'];
         $tmp['##comment.author##'] = Html::clean(getUserName($comment['users_id']));
         $tmp['##comment.datecomment##'] = Html::convDateTime($comment['date_comment']);
         $tmp['##comment.comment##'] = nl2br($comment['comment']);

         $this->datas['comments'][] = $tmp;
      } 
   }
   
   function getTags() {

      $tags = array('ideabox.name'              => __('Title'),
                     'ideabox.comment'          => __('Description'),
                     'comment.name'             => __('Name'),
                     'comment.author'           => __('Comment author', 'ideabox'),
                     'comment.datecomment'      => __('Date'),
                     'comment.comment'          => __('Content'));
      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag,'label'=>$label,
                                   'value'=>true));
      }
      
      $this->addTagToList(array('tag'=>'ideabox',
                                'label'=>__('An addition/modification/deletion of comments', 'ideabox'),
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('new','update','delete')));
      $this->addTagToList(array('tag'=>'comments',
                                'label'=>__('An addition/modification/deletion of ideas', 'ideabox'),
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('newcomment','updatecomment','deletecomment')));

      asort($this->tag_descriptions);
   }
}

?>