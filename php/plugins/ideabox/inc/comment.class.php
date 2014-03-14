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

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginIdeaboxComment extends CommonDBChild {

	static public $itemtype = 'PluginIdeaboxIdeabox';
   static public $items_id = 'plugin_ideabox_ideaboxes_id';
   
	static function getTypeName($nb = 0) {

      return _n('Comment','Comments',$nb, 'ideabox');
   }
   
   static function canCreate() {
      return plugin_ideabox_haveRight('ideabox', 'w');
   }

   static function canView() {
      return plugin_ideabox_haveRight('ideabox', 'r');
   }
   
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      
      if (!$withtemplate) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2), self::countForIdea($item));
         }
         return self::getTypeName(2);
      }
      return '';
   }
   
   static function countForIdea(PluginIdeaboxIdeabox $item) {

      return countElementsInTable('glpi_plugin_ideabox_comments',
                                  "`plugin_ideabox_ideaboxes_id` = '".$item->getID()."'");
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='PluginIdeaboxIdeabox') {
         $self = new self();
         
         $self->showComments($item);
         $self->showForm("", array('plugin_ideabox_ideaboxes_id' => $item->getField('id'), 
                                    'target' => $CFG_GLPI['root_doc']."/plugins/ideabox/front/comment.form.php"));
      }
      return true;
   }
   
   /**
    * Clean object veryfing criteria (when a relation is deleted)
    *
    * @param $crit array of criteria (should be an index)
    */
   public function clean ($crit) {
      global $DB;
      
      foreach ($DB->request($this->getTable(), $crit) as $data) {
         $this->delete($data);
      }
   }
   
   function prepareInputForAdd($input) {
      // Not attached to reference -> not added
      if (!isset($input['plugin_ideabox_ideaboxes_id']) 
            || $input['plugin_ideabox_ideaboxes_id'] <= 0) {
         return false;
      }
      return $input;
   }
   
   function post_addItem() {
      global $CFG_GLPI;
      
      $idea = new PluginIdeaboxIdeabox();
      if ($CFG_GLPI["use_mailing"]) {
         $options = array('comment_id' => $this->fields["id"]);
         if ($idea->getFromDB($this->fields["plugin_ideabox_ideaboxes_id"])) {
            NotificationEvent::raiseEvent("newcomment",$idea,$options);  
         }
      }
   }
   
   function post_updateItem($history=1) {
      global $CFG_GLPI;

      $idea = new PluginIdeaboxIdeabox();
      
      if (count($this->updates)) {
         $options = array('comment_id' => $this->fields["id"]);
         if ($idea->getFromDB($this->fields["plugin_ideabox_ideaboxes_id"])) {
            NotificationEvent::raiseEvent("updatecomment",$idea,$options);  
         }
      }
   }
   
   function pre_deleteItem() {
      global $CFG_GLPI;

      $idea = new PluginIdeaboxIdeabox();
      if ($CFG_GLPI["use_mailing"]) {
         $options = array('comment_id' => $this->fields["id"]);
         if ($idea->getFromDB($this->fields["plugin_ideabox_ideaboxes_id"])) {
            NotificationEvent::raiseEvent("deletecomment",$idea,$options);  
         }
      }
      return true;
   }
   
	function showForm ($ID, $options=array()) {
      global $CFG_GLPI;
	
      if (!$this->canView()) return false;
      
      $plugin_ideabox_ideaboxes_id = -1;
      if (isset($options['plugin_ideabox_ideaboxes_id'])) {
         $plugin_ideabox_ideaboxes_id = $options['plugin_ideabox_ideaboxes_id'];
      }
      
		if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $input=array('plugin_ideabox_ideaboxes_id'=>$plugin_ideabox_ideaboxes_id);
         $this->check(-1,'w',$input);
      }
      
      if ($ID > 0) {
         $this->showTabs($options);
      }
      $this->showFormHeader($options);

      echo "<input type='hidden' name='plugin_ideabox_ideaboxes_id' value='$plugin_ideabox_ideaboxes_id'>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Name').":	</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";

      echo "<td>".__('Author').": </td><td>";
      echo getusername(Session::getLoginUserID());
      echo "<input type='hidden' name='users_id' value='".Session::getLoginUserID()."'>";
      echo "<input type='hidden' name='date_comment' value=\"".$_SESSION["glpi_currenttime"]."\">";
      echo "</td>";
      
      echo "</tr>";
      
      if (!empty($ID)) {		

         echo "<tr class='tab_bg_1'>";
      
         echo "<td>".__('Date').": </td>";
         echo "<td>";
         echo Html::convdatetime($this->fields["date_comment"]);
         echo "</td>";
         
         echo "<td></td>";
         echo "<td></td>";
         
         echo "</tr>";
      
      }
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td colspan = '4'>";
      echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
      echo __('Content').": </td></tr>";
      echo "<tr><td class='center'>";
      echo "<textarea cols='125' rows='14' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td></tr></table>";
      echo "</td>";
      
      echo "</tr>";

      $options['candel'] = false;
      $this->showFormButtons($options);

      return true;

	}
	
	/**
    * @since version 0.84
   **/
   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }
   
   function showComments(PluginIdeaboxIdeabox $ideabox) {
      global $DB,$CFG_GLPI;

      $instID = $ideabox->fields['id'];

      if (!$ideabox->can($instID, "r")) {
         return false;
      }

      $rand=mt_rand();
      $canedit = $ideabox->can($instID,'w');

      $query = "SELECT `glpi_plugin_ideabox_comments`.`name` AS name,
                        `glpi_plugin_ideabox_comments`.`id`,
                        `glpi_plugin_ideabox_comments`.`plugin_ideabox_ideaboxes_id`,
                        `glpi_plugin_ideabox_comments`.`date_comment`,
                        `glpi_plugin_ideabox_comments`.`comment`,
                        `glpi_plugin_ideabox_comments`.`users_id` AS users_id
               FROM `glpi_plugin_ideabox_comments` ";
      $query.= " LEFT JOIN `glpi_plugin_ideabox_ideaboxes`
      ON (`glpi_plugin_ideabox_ideaboxes`.`id` = `glpi_plugin_ideabox_comments`.`plugin_ideabox_ideaboxes_id`)";
      $query.= " WHERE `glpi_plugin_ideabox_comments`.`plugin_ideabox_ideaboxes_id` = '$instID'
          ORDER BY `glpi_plugin_ideabox_comments`.`name`";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      echo "<div class='spaced'>";

      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array();
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
      }

      if($number!=0){
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";

         if ($canedit && $number) {
            echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
         }

         echo "<th>".__('Name')."</th>";
         echo "<th>".__('Author')."</th>";
         echo "<th>".__('Date')."</th>";
         echo "<th>".__('Content')."</th>";

         echo "</tr>";

         Session::initNavigateListItems($this->getType(),PluginIdeaboxIdeabox::getTypeName(2) ." = ". $ideabox->fields["name"]);
         $i = 0;
         $row_num=1;

         while ($data=$DB->fetch_array($result)) {

            Session::addToNavigateListItems($this->getType(),$data['id']);

            $i++;
            $row_num++;
            echo "<tr class='tab_bg_1 center'>";
            echo "<td width='10'>";
            if ($canedit) {
               Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
            }
            echo "</td>";

            echo "<td class='center'>";
            echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/ideabox/front/comment.form.php?id=".$data["id"]."&amp;plugin_ideabox_ideaboxes_id=".$data["plugin_ideabox_ideaboxes_id"]."'>";
            echo $data["name"];
            if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) echo " (".$data["id"].")";
            echo "</a></td>";

            echo "<td class='center'>".getusername($data["users_id"])."</td>";
            echo "<td class='center'>".Html::convdatetime($data["date_comment"])."</td>";
            echo "<td class='left'>".nl2br($data["comment"])."</td>";
            echo "</tr>";
         }
         echo "</table>";
      }

      if ($canedit && $number) {
         $paramsma['ontop'] =false;
         Html::showMassiveActions(__CLASS__, $paramsma);
         Html::closeForm();
      }
      echo "</div>";
   }
}

?>