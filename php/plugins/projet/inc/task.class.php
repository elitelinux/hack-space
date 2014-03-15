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
 
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginProjetTask extends CommonDBTM {
	
	public $itemtype = 'PluginProjetProjet';
   public $items_id = 'plugin_projet_projets_id';
	public $dohistory=true;
   
   static function getTypeName($nb = 0) {

      return _n('Task', 'Tasks', $nb);
   }
   
   static function canCreate() {
      return plugin_projet_haveRight('task', 'w');
   }

   static function canView() {
      return plugin_projet_haveRight('task', 'r');
   }
   
   function getSearchOptions() {

      $tab = array();
    
      $tab['common']             = PluginProjetProjet::getTypeName(2)." - ".self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();
      
      $tab[2]['table']           = 'glpi_users';
      $tab[2]['field']           = 'name';
      $tab[2]['name']            = __('User');
      $tab[2]['datatype']        = 'dropdown';
      $tab[2]['massiveaction']   = false;
      
      $tab[3]['table']           = 'glpi_groups';
      $tab[3]['field']           = 'completename';
      $tab[3]['name']            = _n('Group', 'Groups', 1);
      $tab[3]['condition']       = '`is_assign`';
      $tab[3]['massiveaction']   = false;
      $tab[2]['datatype']        = 'dropdown';
      
      $tab[4]['table']           = 'glpi_contacts';
      $tab[4]['field']           = 'name';
      $tab[4]['name']            =  _n('Supplier', 'Suppliers', 1);
      $tab[4]['massiveaction']   = false;
      $tab[4]['datatype']        = 'dropdown';
      
      $tab[5]['table']           = 'glpi_plugin_projet_tasktypes';
      $tab[5]['field']           = 'name';
      $tab[5]['name']            = PluginProjetTaskType::getTypeName(1);
      $tab[5]['datatype']        = 'dropdown';
      
      $tab[6]['table']           = 'glpi_plugin_projet_taskstates';
      $tab[6]['field']           = 'name';
      $tab[6]['name']            = PluginProjetTaskState::getTypeName(1);
      $tab[6]['massiveaction']   = false;
      $tab[6]['datatype']        = 'dropdown';
      
      $tab[7]['table']           = 'glpi_plugin_projet_taskplannings';
      $tab[7]['field']           = 'id';
      $tab[7]['name']            = __('Planning');
      $tab[7]['massiveaction']   = false;
      $tab[7]['datatype']        = 'number';
      
      $tab[9]['table']           = $this->getTable();
      $tab[9]['field']           = 'advance';
      $tab[9]['name']            = __('Progress');
      $tab[9]['datatype']        = 'integer';
      
      $tab[10]['table']          = $this->getTable();
      $tab[10]['field']          = "priority";
      $tab[10]['name']           = __('Priority');
      $tab[10]['datatype']       = 'dropdown';
      
      $tab[11]['table']          =  $this->getTable();
      $tab[11]['field']          = 'comment';
      $tab[11]['name']           = __('Comments');
      $tab[11]['datatype']       = 'text';
      
      $tab[12]['table']          = $this->getTable();
      $tab[12]['field']          = 'sub';
      $tab[12]['name']           = __('Results');
      $tab[12]['datatype']       = 'text';
      
      $tab[13]['table']          = $this->getTable();
      $tab[13]['field']          = 'others';
      $tab[13]['name']           = __('Others participants', 'projet');
      $tab[13]['datatype']       = 'text';
      
      $tab[14]['table']          = $this->getTable();
      $tab[14]['field']          = 'affect';
      $tab[14]['name']           = __('Affected people', 'projet');
      $tab[14]['datatype']       = 'text';
      
      $tab[15]['table']          = 'glpi_plugin_projet_tasks_tasks';
      $tab[15]['field']          = 'plugin_projet_tasks_id_1';
      $tab[15]['name']           = __('Parent task', 'projet');
      $tab[15]['massiveaction']  = false;
      $tab[15]['searchtype']     = 'equals';
      $tab[15]['joinparams']     = array('jointype'  => 'item_item');
      $tab[15]['forcegroupby']   =  true;
      $tab[15]['datatype']       = 'number';
      
      $tab[16]['table']          = $this->getTable();
      $tab[16]['field']          = 'show_gantt';
      $tab[16]['name']           = __('Display on the Gantt', 'projet');
      $tab[16]['datatype']       = 'bool';
      
      $tab[18]['table']          = $this->getTable();
      $tab[18]['field']          = 'depends';
      $tab[18]['name']           = __('Dependent', 'projet');
      $tab[18]['datatype']       = 'bool';
      
      $tab[19]['table']          = $this->getTable();
      $tab[19]['field']          = 'actiontime';
      $tab[19]['name']           = __('Effective duration', 'projet');
      $tab[19]['datatype']       = 'timestamp';
      $tab[19]['massiveaction']  = false;
      $tab[19]['nosearch']       = true;
      
      $tab[21]['table']          = 'glpi_plugin_projet_tasks_items';
      $tab[21]['field']          = 'items_id';
      $tab[21]['name']           = _n('Associated item', 'Associated items', 2);
      $tab[21]['massiveaction']  = false;
      $tab[21]['forcegroupby']   = true;
      $tab[21]['joinparams']     = array('jointype' => 'child');
      
      $tab[22]['table']          = 'glpi_locations';
      $tab[22]['field']          = 'completename';
      $tab[22]['name']           = _n('Location', 'Locations', 1);
      $tab[22]['datatype']       = 'dropdown';
      
      $tab[23]['table']          = 'glpi_plugin_projet_projets';
      $tab[23]['field']          = 'id';
      $tab[23]['name']           = PluginProjetProjet::getTypeName(1)." ".__('ID');
      $tab[23]['massiveaction']  = false;
      $tab[23]['datatype']       = 'number';
      
      $tab[24]['table']          = 'glpi_plugin_projet_projets';
      $tab[24]['field']          = 'name';
      $tab[24]['name']           = PluginProjetProjet::getTypeName(2);
      $tab[24]['massiveaction']  = false;
      
      $tab[25]['table']          = $this->getTable();
      $tab[25]['field']          = 'date_mod';
      $tab[25]['name']           = __('Last update');
      $tab[25]['datatype']       = 'datetime';
      $tab[25]['massiveaction']  = false;
      
      $tab[30]['table']          = $this->getTable();
      $tab[30]['field']          = 'id';
      $tab[30]['name']           = __('ID');
      $tab[30]['massiveaction']  = false;
      $tab[30]['datatype']       = 'number';
      
      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['datatype']       = 'dropdown';
      
      return $tab;
   }
	
   /**
   * Clean object veryfing criteria (when a relation is is_deleted)
   *
   * @param $crit array of criteria (should be an index)
   */
   public function clean ($crit) {
      global $DB;
      
      foreach ($DB->request($this->getTable(), $crit) as $data) {
         $this->delete($data);
      }
   }
   
	function cleanDBonPurge() {
		
		$temp = new PluginProjetTask_Item();
		$temp->deleteByCriteria(array('plugin_projet_tasks_id' => $this->fields['id']));
		
		$temp = new PluginProjetTaskPlanning();
		$temp->deleteByCriteria(array('plugin_projet_tasks_id' => $this->fields['id']));
		
		$temp = new PluginProjetTask_Task();
		$temp->deleteByCriteria(array('plugin_projet_tasks_id_1' => $this->fields['id'],
                                    'plugin_projet_tasks_id_2' => $this->fields['id']));
	}
	
   
   function post_getEmpty() {

      $this->fields['show_gantt'] = 1;
      $this->fields['priority'] = 3;
   }
   
	function prepareInputForAdd($input) {

      Toolbox::manageBeginAndEndPlanDates($input['plan']);
      
      if (isset($input['plan'])) {
         $input['_plan'] = $input['plan'];
         unset($input['plan']);
      }
      
      if (isset($input["hour"]) && isset($input["minute"])) {
         $input["actiontime"] = $input["hour"]*HOUR_TIMESTAMP+$input["minute"]*MINUTE_TIMESTAMP;
         $input["_hour"]      = $input["hour"];
         $input["_minute"]    = $input["minute"];
         unset($input["hour"]);
         unset($input["minute"]);
      }
      
      unset($input["minute"]);
      unset($input["hour"]);
      
      if (!isset($input['plugin_projet_projets_id']) 
            || $input['plugin_projet_projets_id'] <= 0) {
         return false;
      }
      
      if (isset($input['plugin_projet_taskstates_id']) 
            && !empty($input['plugin_projet_taskstates_id'])) {
         
         //not show archived projects
         $archived = " `for_dependency` = '1' ";
         $states = getAllDatasFromTable("glpi_plugin_projet_taskstates",$archived);
         $tab = array();
         if (!empty($states)) {
            foreach ($states as $state) {
               $tab[]= $state['id'];
            }
         }

         if (!empty($tab) 
               && in_array($input['plugin_projet_taskstates_id'],$tab)) {
           
            $input['advance']='100';
         }  
      }
      
		return $input;
	}
	
	function post_addItem() {
      global $CFG_GLPI;
      
      if (isset($this->input["_plan"])) {
         $this->input["_plan"]['plugin_projet_tasks_id'] = $this->fields['id'];
         $pt = new PluginProjetTaskPlanning();

         if (!$pt->add($this->input["_plan"])) {
            return false;
         }
      }
      
      $task_task = new PluginProjetTask_Task();

      // From interface
      if (isset($this->input['_link'])) {
         $this->input['_link']['plugin_projet_tasks_id_1'] = $this->fields['id'];
         // message if task projet doesn't exist
         if (!empty($this->input['_link']['plugin_projet_tasks_id_2'])) {
            if ($task_task->can(-1, 'w', $this->input['_link'])) {
               $task_task->add($this->input['_link']);
            } else {
               Session::addMessageAfterRedirect(__('Unknown project task', 'projet'), false, ERROR);
            }
         }
      }
      
      $PluginProjetProjet = new PluginProjetProjet();
      if ($CFG_GLPI["use_mailing"]) {
         $options = array('tasks_id' => $this->fields["id"]);
         if ($PluginProjetProjet->getFromDB($this->fields["plugin_projet_projets_id"])
            && isset($this->input['send_notification']) 
            && $this->input['send_notification']==1) {
            NotificationEvent::raiseEvent("newtask",$PluginProjetProjet,$options);  
         }
      }
   }
	
	function prepareInputForUpdate($input) {
		global $CFG_GLPI;
      
      Toolbox::manageBeginAndEndPlanDates($input['plan']);
      if (isset($input["hour"]) 
            && isset($input["minute"])) {
         $input["actiontime"] = $input["hour"]*HOUR_TIMESTAMP+$input["minute"]*MINUTE_TIMESTAMP;
         unset($input["hour"]);
         unset($input["minute"]);
      }
      
      if (isset($input["plan"])) {
         $input["_plan"] = $input["plan"];
         unset($input["plan"]);
      }
      
      if (isset($input['plugin_projet_taskstates_id']) 
            && !empty($input['plugin_projet_taskstates_id'])) {
         
         $archived = " `for_dependency` = '1' ";
         $states = getAllDatasFromTable("glpi_plugin_projet_taskstates",$archived);
         $tab = array();
         if (!empty($states)) {
            foreach ($states as $state) {
               $tab[]= $state['id'];
            }
         }

         if (!empty($tab) && in_array($input['plugin_projet_taskstates_id'],$tab)) {
           
            $input['advance']='100';
         }  
      }
      
      if (isset($input['_link'])) {
         $task_task = new PluginProjetTask_Task();
         if (!empty($input['_link']['plugin_projet_tasks_id_2'])) {
            if ($task_task->can(-1, 'w', $input['_link'])) {
               $task_task->add($input['_link']);
            } else {
               Session::addMessageAfterRedirect(__('Unknown project task', 'projet'), false, ERROR);
            }
         }
      }
	
		$this->getFromDB($input["id"]);
		$input["_old_name"]=$this->fields["name"];
		$input["_old_users_id"]=$this->fields["users_id"];
		$input["_old_groups_id"]=$this->fields["groups_id"];
		$input["_old_contacts_id"]=$this->fields["contacts_id"];
		$input["_old_plugin_projet_tasktypes_id"]=$this->fields["plugin_projet_tasktypes_id"];
		$input["_old_plugin_projet_taskstates_id"]=$this->fields["plugin_projet_taskstates_id"];
		$input["_old_actiontime"]=$this->fields["actiontime"];
		$input["_old_advance"]=$this->fields["advance"];
		$input["_old_priority"]=$this->fields["priority"];
		$input["_old_comment"]=$this->fields["comment"];
		$input["_old_sub"]=$this->fields["sub"];
		$input["_old_others"]=$this->fields["others"];
		$input["_old_affect"]=$this->fields["affect"];
		$input["_old_plugin_projet_projets_id"]=$this->fields["plugin_projet_projets_id"];
		$input["_old_depends"]=$this->fields["depends"];
		$input["_old_show_gantt"]=$this->fields["show_gantt"];
		$input["_old_locations_id"]=$this->fields["locations_id"];
      
		return $input;
	}
	
	function post_updateItem($history=1) {
		global $CFG_GLPI;
		
		if (isset($this->input["_plan"])) {
         $pt = new PluginProjetTaskPlanning();
         // Update case
         if (isset($this->input["_plan"]["id"])) {
            $this->input["_plan"]['plugin_projet_tasks_id'] = $this->input["id"];

            if (!$pt->update($this->input["_plan"])) {
               return false;
            }
            unset($this->input["_plan"]);
         // Add case
         } else {
            $this->input["_plan"]['plugin_projet_tasks_id'] = $this->input["id"];
            if (!$pt->add($this->input["_plan"])) {
               return false;
            }
            unset($this->input["_plan"]);
         }

      }
		if (!isset($this->input["withtemplate"]) 
            || (isset($this->input["withtemplate"]) 
               && $this->input["withtemplate"]!=1)) {
			if ($CFG_GLPI["use_mailing"]) {
            $options = array('tasks_id' => $this->fields["id"]);
            $PluginProjetProjet = new PluginProjetProjet();
            if ($PluginProjetProjet->getFromDB($this->fields["plugin_projet_projets_id"])
               && isset($this->input['send_notification']) 
               && $this->input['send_notification']==1) {
               NotificationEvent::raiseEvent("updatetask",$PluginProjetProjet,$options);  
            }
         }
      }
	}
	
	function pre_deleteItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["use_mailing"] 
            && isset($this->input['delete'])) {
         $PluginProjetProjet = new PluginProjetProjet();
         $options = array('tasks_id' => $this->fields["id"]);
         if ($PluginProjetProjet->getFromDB($this->fields["plugin_projet_projets_id"])
            && isset($this->input['send_notification']) 
            && $this->input['send_notification']==1) {
            NotificationEvent::raiseEvent("deletetask",$PluginProjetProjet,$options);  
         }
      }
      return true;
   }
	
	
	function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()==__CLASS__  && $this->canView()) {
      
         $ong[1] = __('Hierarchy', 'projet');
         return $ong;
         
      } else if ($item->getType()=='PluginProjetProjet' && $this->canView()) {
      
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2), self::countForItem($item));
         }
         return self::getTypeName(2);
         
      } else if ($item->getType()=='Central' && $this->canView()) {
      
         return PluginProjetProjet::getTypeName(2);
         
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      $self = new self();
      if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 1 :
               PluginProjetTask_Task::showHierarchy($item->getID(),1);
               PluginProjetTask_Task::showHierarchy($item->getID());
               break;

         }
      } else if ($item->getType()=='PluginProjetProjet') {
         if (plugin_projet_haveRight('task', 'r')) {
               self::addNewTasks($item, $withtemplate);
               self::showMinimalList(array('id' => $item->getID(),
                                            'withtemplate' => $withtemplate));
               self::taskLegend();
         }
      } else if ($item->getType()=='Central') {
         $self->showCentral(Session::getLoginUserID());
      }
      return true;
   }
   
   static function countForItem(CommonDBTM $item) {

      $restrict = "`plugin_projet_projets_id` = '".$item->getField('id')."'
                  AND is_deleted = 0";
      //TODO
      //$restrict = "AND is_finished != 1";
      $nb = countElementsInTable(array('glpi_plugin_projet_tasks'), $restrict);

      return $nb ;
   }
	
	function defineTabs($options=array()) {
		
		$ong = array();
		
		$this->addStandardTab('PluginProjetTask', $ong,$options);
      $this->addStandardTab('PluginProjetTask_Item', $ong,$options);
      $this->addStandardTab('Log',$ong,$options);
      
      return $ong;
	}
	
	/**
    * Duplicate task of projects from an item template to its clone
    *
    * @since version 0.84
    *
    * @param $itemtype     itemtype of the item
    * @param $oldid        ID of the item to clone
    * @param $newid        ID of the item cloned
    * @param $newitemtype  itemtype of the new item (= $itemtype if empty) (default '')
   **/
   static function cloneItem($oldid, $newid) {

      $task_item = new PluginProjetTask_Item();
      $task_task = new PluginProjetTask_Task();
      
      $restrict = "`plugin_projet_projets_id` = '".$oldid."'
                  AND is_deleted != 1";
      $ptasks = getAllDatasFromTable("glpi_plugin_projet_tasks",$restrict);
      if (!empty($ptasks)) {
         foreach ($ptasks as $ptask) {
            
            $task = new self();
            $values=$ptask;
            $taskid = $values["id"];
            unset($values["id"]);
            $values["plugin_projet_projets_id"]=$newid;
            $values["name"] = addslashes($ptask["name"]);
            $values["comment"] = addslashes($ptask["comment"]);
            $values["sub"] = addslashes($ptask["sub"]);
            $values["others"] = addslashes($ptask["others"]);
            $values["affect"] = addslashes($ptask["affect"]);
            
            $newtid = $task->add($values);
            
            $restrictitems = "`plugin_projet_tasks_id` = '".$taskid."'";
            $tasksitems = getAllDatasFromTable("glpi_plugin_projet_tasks_items",$restrictitems);
            if (!empty($tasksitems)) {
               foreach ($tasksitems as $tasksitem) {
                  $task_item->add(array('plugin_projet_tasks_id' => $newtid,
                     'itemtype' => $tasksitem["itemtype"],
                     'items_id' => $tasksitem["items_id"]));
               }
            }
            
            /*$restrictlnk = "`plugin_projet_tasks_id_1` = '".$taskid."'";
            $links = getAllDatasFromTable("glpi_plugin_projet_tasks_tasks",$restrictlnk);
            if (!empty($links)) {
               foreach ($links as $link) {
                  $task_task->add(array('plugin_projet_tasks_id_1' => $newtid,
                                          'plugin_projet_tasks_id_2' => $link["plugin_projet_tasks_id_2"],
                                          'link' => PluginProjetTask_Task::LINK_TO));
               }
            }
            
            $restrictlnk2 = "`plugin_projet_tasks_id_2` = '".$taskid."'";
            $links2 = getAllDatasFromTable("glpi_plugin_projet_tasks_tasks",$restrictlnk2);
            if (!empty($links2)) {
               foreach ($links2 as $link2) {
                  $task_task->add(array('plugin_projet_tasks_id_1' => $link2["plugin_projet_tasks_id_1"],
                                          'plugin_projet_tasks_id_2' => $newtid,
                                          'link' => PluginProjetTask_Task::LINK_TO));
               }
            }*/
         }
      }
   }
   
   static function addNewTasks(CommonDBTM $item, $withtemplate='') {
      global $CFG_GLPI;
      
      $rand=mt_rand();
      
      $ID = $item->getField('id');
      $entities_id = $item->getField('entities_id');
      $canedit = $item->can($ID, 'w');
      if (plugin_projet_haveRight('task', 'w') 
            && $canedit 
               && $withtemplate<2) {
      
         echo "<div align='center'>";
         echo "<a href='".
         $CFG_GLPI["root_doc"]."/plugins/projet/front/task.form.php?plugin_projet_projets_id=".$ID
         ."&entities_id=".$entities_id."' >".__('Add a new task')."</a></div>";
         echo "</div>";
      }
   }
	
   function showForm ($ID, $options=array()) {
      global $CFG_GLPI;

      if (!$this->canView()) return false;
      
      $plugin_projet_projets_id = -1;
      if (isset($options['plugin_projet_projets_id'])) {
         $plugin_projet_projets_id = $options['plugin_projet_projets_id'];
      }
      
      $item = new PluginProjetProjet();
      if ($item->getFromDB($plugin_projet_projets_id)){
         $entities_id = $item->fields["entities_id"];
      }

      if ($ID > 0) {
         $this->check($ID,'r');
         $plugin_projet_projets_id=$this->fields["plugin_projet_projets_id"];
      } else {
         // Create item
         $input=array('plugin_projet_projets_id'=>$plugin_projet_projets_id,
                        'entities_id' => $entities_id);
         $this->check(-1,'w',$input);
      }
      $options["colspan"] = 4;

      $this->showTabs($options);
      $this->showFormHeader($options);
      
      echo "<input type='hidden' name='plugin_projet_projets_id' value='$plugin_projet_projets_id'>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='2'>".PluginProjetProjet::getTypeName(2)."</td><td colspan='2'>";
      
      $link = NOT_AVAILABLE;
      if ($item->getFromDB($plugin_projet_projets_id)){
         $link=$item->getLink();
      }
      echo $link;
      echo "</td>";
      echo "<td>";
      echo __('Parent task', 'projet')."</td><td>";

      PluginProjetTask_Task::displayLinkedProjetTasksTo($ID);
      
      if ($this->canCreate()) {
         
         $rand_linked_projettask = mt_rand();
         
          echo "&nbsp;";
          if (!PluginProjetTask_Task::getParentProjetTasksTo($ID)) {
				echo "<img onClick=\"Ext.get('linkedprojettask$rand_linked_projettask').setDisplayed('block')\"
                       title=\"".__('Add')."\" alt=\"".__('Add')."\"
                       class='pointer' src='".$CFG_GLPI["root_doc"]."/pics/add_dropdown.png'>";
         }
         echo "<div style='display:none' id='linkedprojettask$rand_linked_projettask'>";
         PluginProjetTask_Task::dropdownLinks('_link[link]',
                                      (isset($values["_link"])?$values["_link"]['link']:''));
         echo "&nbsp;";
         PluginProjetTask_Task::dropdownParent("_link[plugin_projet_tasks_id_2]", 
                           (isset($values["_link"])?$values["_link"]['plugin_projet_tasks_id_2']:''),
                           array('id' => $this->fields["id"],
                                 'entities_id' => $this->fields["entities_id"],
                                 'plugin_projet_projets_id' => $plugin_projet_projets_id));
         echo "<input type='hidden' name='_link[plugin_projet_tasks_id_1]' value='$ID'>\n";
         
         echo "&nbsp;";
         echo "</div>";

         if (isset($values["_link"]) && !empty($values["_link"]['plugin_projet_tasks_id_2'])) {
            echo "<script language='javascript'>Ext.get('linkedprojettask$rand_linked_projettask').
                   setDisplayed('block');</script>";
         }
      }
      echo "</td>";
      echo "<td>";
      echo __('Dependent', 'projet')."</td><td>";
      Dropdown::showYesNo("depends",$this->fields["depends"]);
      echo "&nbsp;";
      echo " <img alt='' src='".$CFG_GLPI["root_doc"]."/pics/aide.png' onmouseout=\"cleanhide('commentsup')\" onmouseover=\"cleandisplay('commentsup')\">";
      echo "<span class='over_link' id='commentsup'>".nl2br(__('Depends on the tasks children', 'projet'))."</span>";
      
      echo "</td>";
      
      echo "</tr>";
   
      $width_left=$width_right="50%";
      $cols=60;
      $rows=4;

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2'>";
      echo __('Name')."</td><td  colspan='2'>";
      Html::autocompletionTextField($this,"name",array('size' => "30"));
      echo "<td >";
      echo _n('Type' , 'Types' , 1)."</td><td>";
      Dropdown::show('PluginProjetTaskType',
                  array('value'  => $this->fields["plugin_projet_tasktypes_id"]));
      echo "</td>";
      echo "<td>";
      echo __('State')."</td><td>";
      if ($ID > 0) {
         $this->dropdownState("plugin_projet_taskstates_id",$this->fields["plugin_projet_taskstates_id"],
                           array('depends' => $this->fields["depends"],
                                 'id' => $this->fields["id"],
                                 'plugin_projet_projets_id' => $plugin_projet_projets_id));
      } else {
         Dropdown::show('PluginProjetTaskState',
                  array('value'  => $this->fields["plugin_projet_taskstates_id"]));
      }
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td colspan='2'>";
      echo _n('Location' , 'Locations' , 1)."</td><td  colspan='2'>";
      Dropdown::show('Location',
                  array('value'  => $this->fields["locations_id"]));
      echo "</td>";

      echo "<td>";
      echo __('Priority')."</td><td>";
      Ticket::dropdownPriority(array('value' => $this->fields["priority"],
                                                      'withmajor' => true));
      echo "</td>";
      echo "<td>".__('Progress')."</td><td>";
      $advance=floor($this->fields["advance"]);	
      echo "<select name='advance'>";
      if (empty($ID) || $this->fields["depends"]==0) {
         for ($i=0;$i<101;$i+=5) {
            echo "<option value='$i' ";
            if ($advance==$i) echo "selected";
               echo " >$i</option>";
         }
      } else if ($this->fields["depends"]!=0) {
            for ($i=0;$i<100;$i+=5) {
               echo "<option value='$i' ";
               if ($advance==$i) echo "selected";
                  echo " >$i</option>";
            }
      }
      
      echo "</select> %";
      echo "</td>";			
      echo "</tr>";
      
      
      echo "<tr class='tab_bg_3'>";
      echo "<td colspan='4'>".__('Assigned to')."</td>";
      echo "<td colspan='4'>".__('Planification', 'projet')."</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='4' width='$width_left'>";
      echo "<table width='100%'>";
      echo "<tr>";
      echo "<td>"._n('User' , 'Users' , 1)."</td><td>";
      $this->dropdownItems($this->fields["plugin_projet_projets_id"],
                           "users_id",
                           array(),
                           $this->fields["users_id"],
                           'User');
      echo "</td></tr>";
      echo "<tr><td>"._n('Group' , 'Groups' , 1)."</td><td>";
      $this->dropdownItems($this->fields["plugin_projet_projets_id"],
                           "groups_id",
                           array(),
                           $this->fields["groups_id"],
                           'Group');
      echo "</td>";
      echo "</tr>";
      echo "<tr>";
      echo "<td>"._n('Supplier' , 'Suppliers' , 1)."</td><td>";
      $this->dropdownItems($this->fields["plugin_projet_projets_id"],
                           "contacts_id",
                           array(),
                           $this->fields["contacts_id"],
                           'Supplier');
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      echo "</td>";	
      
      echo "<td colspan='4' width='$width_right' valign='top'>";	
      echo "<table width='100%'>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Effective duration', 'projet')."</td><td>";
      
      $toadd = array();
      for ($i=9;$i<=100;$i++) {
         $toadd[] = $i*HOUR_TIMESTAMP;
      }

      Dropdown::showTimeStamp("actiontime",array('min'             => 0,
                                                 'max'             => 8*HOUR_TIMESTAMP,
                                                 'value'           => $this->fields["actiontime"],
                                                 'addfirstminutes' => true,
                                                 'inhours'          => true,
                                                 'toadd'           => $toadd));
                                                 
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Planning')."</td>";
      echo "<td>";
      $plan = new PluginProjetTaskPlanning();
      $plan->showFormForTask($plugin_projet_projets_id, $this);
      echo "</td></tr>";
      
      echo "</table>";
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_3'>";
      $colspan = '8';
      if (!empty($ID))
         $colspan = '4';
      echo "<td colspan='".$colspan."'>".__('Description')."</td>";
      if (!empty($ID))
         echo "<td colspan='4'>".__('Results')."</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='".$colspan."' width='$width_left'>";
      echo "<table width='100%'>";
      echo "<tr>";
      echo "<td>";
      echo "<textarea name='comment' cols='$cols' rows='$rows'>".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";
      echo "</table>";
      if (!empty($ID)) {
         echo "</td>";
         echo "<td colspan='4' width='$width_left'>";
         echo "<table width='100%'>";
         echo "<tr>";
         echo "<td>";
         echo "<textarea name='sub' cols='$cols' rows='$rows'>".$this->fields["sub"]."</textarea>";
         echo "</td>";
         echo "</tr>";
         echo "</table>";
         echo "</td>";
      }
      echo "</tr>";
      
      echo "<tr class='tab_bg_3'>";
      echo "<td colspan='4'>".__('Others participants', 'projet')."</td>";
      echo "<td colspan='4'>".__('Affected people', 'projet')."</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='4' width='$width_left'>";
      echo "<table width='100%'>";
      echo "<tr>";
      echo "<td>";
      echo "<textarea name='others' cols='$cols' rows='2'>".$this->fields["others"]."</textarea>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      echo "</td>";
      echo "<td colspan='4' width='$width_right' valign='top'>";
      echo "<table width='100%'>";
      echo "<tr>";
      echo "<td>";
      echo "<textarea name='affect' cols='$cols' rows='2'>".$this->fields["affect"]."</textarea>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td  colspan='2'>".__('Send email', 'projet')."</td><td colspan='2'>";
      echo "<input type='checkbox' name='send_notification'";
      echo " value='1'>";
      echo "</td>";
      echo "<td colspan='4' align='center'>".__('Display on the Gantt', 'projet')." ";
      Dropdown::showYesNo("show_gantt",$this->fields["show_gantt"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='8' align='center'>";
      $datestring = __('Last update').": ";
      $date = Html::convDateTime($this->fields["date_mod"]);
      echo $datestring.$date."</td>";
      echo "</tr>";

		$this->showFormButtons($options);
      $this->addDivForTabs();
      
      return true;	
	}
	
	function dropdownItems($ID,$name,$used=array(),$value=0,$item=false) {
      global $DB,$CFG_GLPI;

      $restrict = "`plugin_projet_projets_id` = '$ID'";
      if ($item)
         $restrict.= " AND `itemtype` = '$item'";
      $projets = getAllDatasFromTable("glpi_plugin_projet_projets_items",$restrict);

      echo "<select name='$name'>";
      echo "<option value='0' selected>".Dropdown::EMPTY_VALUE."</option>";

      if (!empty($projets)) {

        foreach ($projets as $projet) {
            
            $table = getTableForItemType($projet["itemtype"]);
            
            if ($projet["itemtype"]=='Supplier') {
               $table = getTableForItemType('Contact');
               $class = new Contact();
               $query = "SELECT `".$table."`.* "
               ." FROM `glpi_plugin_projet_projets_items`, `".$table
               ."` LEFT JOIN `glpi_contacts_suppliers` ON (`glpi_contacts_suppliers`.`contacts_id` = `".$table."`.`id`) "
               ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table."`.`entities_id`) "
               ." WHERE `glpi_contacts_suppliers`.`suppliers_id` = `glpi_plugin_projet_projets_items`.`items_id` 
               AND `glpi_plugin_projet_projets_items`.`itemtype` = '".$projet["itemtype"]."' 
               AND `glpi_plugin_projet_projets_items`.`items_id` = '".$projet["items_id"]."' "
               . getEntitiesRestrictRequest(" AND ",$table,'','',$class->maybeRecursive()); 
            } else { 
               $query = "SELECT `".$table."`.*
                        FROM `glpi_plugin_projet_projets_items`
                        INNER JOIN `".$table."` ON (`".$table."`.`id` = `glpi_plugin_projet_projets_items`.`items_id`)
                        WHERE `glpi_plugin_projet_projets_items`.`itemtype` = '".$projet["itemtype"]."'
                        AND `glpi_plugin_projet_projets_items`.`items_id` = '".$projet["items_id"]."' ";
            }
            if (count($used)) {
               $query .= " AND `".$table."`.`id` NOT IN (0";
               foreach ($used as $ID)
                  $query .= ",$ID";
               $query .= ")";
            }
            $query .= " GROUP BY `".$table."`.`name`";
            $query .= " ORDER BY `".$table."`.`name`";
            $result_linked=$DB->query($query);

            if ($DB->numrows($result_linked)) {
               
               while ($data=$DB->fetch_assoc($result_linked)) {
                  $name=$data["name"];
                  if ($projet["itemtype"]=='User')
                     $name=getUserName($data["id"]);
                  if ($item=='Supplier' || $projet["itemtype"]=='Supplier') {
                     $temp=$data["name"];
                     $firstname=$data["firstname"];
                     if (strlen($firstname)>0) {
                        if ($CFG_GLPI["names_format"]==FIRSTNAME_BEFORE) {
                           $temp=$firstname." ".$temp;
                        } else {
                           $temp.=" ".$firstname;
                        }
                     }
                     $name=$temp;
                  }
                  if ($item)
                     echo "<option value='".$data["id"]."' ".($value=="".$data["id"].""?" selected ":"").">".$name;
                  else
                     echo "<option value='".$data["id"].",".$projet["itemtype"]."'>".$name;
                  if (empty($data["name"]) || $_SESSION["glpiis_ids_visible"] == 1 ) {
                     echo " (";
                     echo $data["id"].")";
                     }
                  echo "</option>";
               }
            }
         }
      }
      echo "</select>";
   }
   
   /*const PROJET_TASK_STATUS_PROGRESS = 1;
	const PROJET_TASK_STATUS_PLANNED = 2;
	const PROJET_TASK_STATUS_WAITING = 3;
	const PROJET_TASK_STATUS_FINISH = 4;
	const PROJET_TASK_STATUS_ABORT = 5;*/
   
   /**
   * Dropdown of task state
   *
   * @param $name select name
   * @param $value default value
   *
   * @return string id of the select
   */
   function dropdownState($name, $value=0, $options=array()) {
      global $DB;
      
      $id = "select_$name".mt_rand();
      echo "<select id='$id' name='$name'>";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";
      
      $condition= " 1 = 1 ORDER BY `name` ASC";
      $option = getAllDatasFromTable("glpi_plugin_projet_taskstates",$condition);
         
      if ($options["id"]!=0 && $options["depends"]!=0) {
         $restrict = "`id` != '".$options["id"]."' AND `plugin_projet_projets_id` = '".$options["plugin_projet_projets_id"]."'";
         
         $finished = " `for_dependency` = '1' ";
         $states = getAllDatasFromTable("glpi_plugin_projet_taskstates",$finished);
         $tab = array();
         if (!empty($states)) {
            foreach ($states as $state) {
               $tab[]= $state['id'];
            }
         }
         if (!empty($tab)) {
            $restrict.= "AND `plugin_projet_taskstates_id` NOT IN (".implode(',',$tab).")";
         }
         
         $restrict.= " AND `id` IN (";
         
         $cond = " `plugin_projet_tasks_id_2` = '".$options["id"]."' ";
         $tasks_tasks = getAllDatasFromTable("glpi_plugin_projet_tasks_tasks",$cond);
         $childs = array($options["id"]);
         if (!empty($tasks_tasks)) {
            foreach ($tasks_tasks as $tasks_task) {
               $childs[]= $tasks_task['plugin_projet_tasks_id_1'];
            }
         }
         if (!empty($childs)) {
            $restrict.= implode(',',$childs);
         }
         $restrict.= ") ";
         $restrict.= "ORDER BY `name` ASC ";
         $tasks = getAllDatasFromTable($this->getTable(),$restrict);
         
         if (!empty($tasks) && !empty($tab)) {
            
            foreach($tab as $t=>$v) {
               unset($option[$v]);
            }
         }
      }
      
      if (!empty($option)) {
         foreach($option as $opt)
         echo "<option value='".$opt["id"]."' ".($value==$opt["id"]?" selected ":"").">".$opt["name"]."</option>";
      }
      echo "</select>";

      return $id;
   }
   
   static function taskLegend() {
      
      echo "<div align='center'><table><tr>";

      $states = getAllDatasFromTable("glpi_plugin_projet_taskstates");
      if (!empty($states)) {
            foreach ($states as $state) {
         echo "<td bgcolor=\"".PluginProjetTaskState::getStatusColor($state["id"])."\">".$state["name"]."</td>";
         }
      }
      echo "</tr></table></div>";

   }
   
   function showCentral($who) {
      global $DB,$CFG_GLPI;

      echo "<table class='tab_cadre_central'><tr><td>";
      
      if ($this->canView()) {
         $who=Session::getLoginUserID();
         
         if (Session::isMultiEntitiesMode()) {
            $colsup=1;
         } else {
            $colsup=0;
         }
            
         $ASSIGN="";
         if ($who>0) {
            $ASSIGN=" AND ((`".$this->getTable()."`.`users_id` = '$who')";
         }
         //if ($who_group>0) {
         $ASSIGN.=" OR (`".$this->getTable()."`.`groups_id` IN (SELECT `groups_id` 
                                                      FROM `glpi_groups_users` 
                                                      WHERE `users_id` = '$who') )";
         //}
         $query = "SELECT `".$this->getTable()."`.`id` AS plugin_projet_tasks_id,
                        `".$this->getTable()."`.`name` AS name_task, 
                        `".$this->getTable()."`.`plugin_projet_tasktypes_id`,
                        `".$this->getTable()."`.`is_deleted`, ";
         $query.= "`".$this->getTable()."`.`users_id` AS users_id_task, 
                  `glpi_plugin_projet_projets`.`id`, 
                  `glpi_plugin_projet_projets`.`name`, 
                  `glpi_plugin_projet_projets`.`entities_id`, 
                  `glpi_plugin_projet_projets`.`plugin_projet_projetstates_id`, 
                  `glpi_plugin_projet_projets`.`users_id` ";
         $query.= " FROM `".$this->getTable()."`,`glpi_plugin_projet_projets` ";
         $query.= " WHERE 
            `".$this->getTable()."`.`plugin_projet_projets_id` = `glpi_plugin_projet_projets`.`id` ";
         //not show finished tasks
         $finished = " `for_dependency` = '1' ";
         $states = getAllDatasFromTable("glpi_plugin_projet_taskstates",$finished);
         $tab = array();
         if (!empty($states)) {
            foreach ($states as $state) {
               $tab[]= $state['id'];
            }
         }
         if (!empty($tab)) {
            $query.= "AND `plugin_projet_taskstates_id` NOT IN (".implode(',',$tab).")";
         }
         $query.= " $ASSIGN ) 
               AND `glpi_plugin_projet_projets`.`is_template` = '0' 
               AND `".$this->getTable()."`.`is_deleted` = '0' 
               AND `glpi_plugin_projet_projets`.`is_deleted` = '0'";
         $PluginProjetProjet = new PluginProjetProjet();
         $itemtable = "glpi_plugin_projet_projets";
         if ($PluginProjetProjet->isEntityAssign()) {
            $LINK= " AND " ;
            $query.=getEntitiesRestrictRequest($LINK,$itemtable);
         }

         $query .= "  ORDER BY `glpi_plugin_projet_projets`.`name` DESC LIMIT 10;";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         
         echo "<table class='tab_cadre_central'><tr><td>";
         
         if ($number > 0) {
            
            Session::initNavigateListItems($this->getType());
                                  
            echo "<div align='center'>";

            echo "<table class='tab_cadre' style='text-align:center' width='100%'>";
            echo "<tr><th colspan='".(7+$colsup)."'>".PluginProjetProjet::getTypeName(2).
            ": ".__('Tasks in progress', 'projet').
            " <a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/task.php'>".
            __('All')."</a></th></tr>";
            
            echo "<tr><th>".self::getTypeName(2)."</th>";
            if (Session::isMultiEntitiesMode()) {
               echo "<th>".__('Entity')."</th>";
            }
            echo "<th>".__('Type' , 'Types', 1)."</th>";
            echo "<th>".__('Planning')."</th>";
            echo "<th>".PluginProjetProjet::getTypeName(1)."</th>";
            echo "<th>".__('State' , 'States', 1)."</th>";
            echo "<th>"._n('Responsible' , 'Responsibles', 2, 'projet')."</th>";
            echo "<th>".__('User' , 'Users', 1)."</th>";
            
            echo "</tr>";

            while ($data=$DB->fetch_array($result)) {
               
               Session::addToNavigateListItems($this->getType(),$data['plugin_projet_tasks_id']);
               
               echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";
               echo "<td align='center'><a href='".$CFG_GLPI["root_doc"].
               "/plugins/projet/front/task.form.php?id=".
               $data["plugin_projet_tasks_id"]."'>".$data["name_task"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["plugin_projet_tasks_id"].")";
               echo "</a></td>";
               
               if (Session::isMultiEntitiesMode()) {
                  echo "<td class='center'>";
                  echo Dropdown::getDropdownName("glpi_entities",$data['entities_id'])."</td>";
               }	
               echo "<td align='center'>";
               echo Dropdown::getDropdownName("glpi_plugin_projet_tasktypes",
                                                $data["plugin_projet_tasktypes_id"]);
               echo "</td>";
               echo "<td align='center'>";
               $restrict = " `plugin_projet_tasks_id` = '".$data['plugin_projet_tasks_id']."' ";
               $plans = getAllDatasFromTable("glpi_plugin_projet_taskplannings",$restrict);
               
               if (!empty($plans)) {
                  foreach ($plans as $plan) {
                     echo Html::convDateTime($plan["begin"]) . "&nbsp;->&nbsp;" .
                     Html::convDateTime($plan["end"]);
                  }
               } else {
                  _e('None');
               }
               echo "</td>";
               echo "<td align='center'>";
               echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php?id=".$data["id"]."'>";
               echo $data["name"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
               echo "</a></td>";
               echo "<td align='center'>";
               echo Dropdown::getDropdownName("glpi_plugin_projet_projetstates",
                                                $data['plugin_projet_projetstates_id'])."</td>";
               
               echo "<td align='center'>";
               echo getUserName($data["users_id"])."</td>";
               
               echo "<td align='center'>";
               echo getUserName($data["users_id_task"])."</td>";

               echo "</tr>";

            }
            echo "</table>";
            echo "</div>";
         }
         
         echo "</td></tr></table>";
      }
      
      echo "</td></tr></table>";
   }
   
   /* Show only parents */
	static function showTaskTreeGantt($options=array()) {
      global $DB;
      
      $query= "SELECT `glpi_plugin_projet_tasks`.* 
               FROM `glpi_plugin_projet_tasks`
               LEFT JOIN `glpi_plugin_projet_taskplannings` 
               ON (`glpi_plugin_projet_taskplannings`.`plugin_projet_tasks_id` = `glpi_plugin_projet_tasks`.`id`)
               WHERE `plugin_projet_projets_id` = '".$options["plugin_projet_projets_id"]."' 
               AND `is_template` = '0' 
               AND `is_deleted` = '0'
               AND `show_gantt` = '1' 
               AND (`glpi_plugin_projet_tasks`.`id` NOT IN (SELECT `plugin_projet_tasks_id_1` FROM `glpi_plugin_projet_tasks_tasks`)
               AND `glpi_plugin_projet_tasks`.`id` NOT IN (SELECT `plugin_projet_tasks_id_2` FROM `glpi_plugin_projet_tasks_tasks`))
               OR (`glpi_plugin_projet_tasks`.`id` IN (SELECT `plugin_projet_tasks_id_2` FROM `glpi_plugin_projet_tasks_tasks`)
               AND `glpi_plugin_projet_tasks`.`id` NOT IN (SELECT `plugin_projet_tasks_id_1` FROM `glpi_plugin_projet_tasks_tasks`))
               ORDER BY `glpi_plugin_projet_taskplannings`.`begin`,`glpi_plugin_projet_tasks`.`id` ASC";

      foreach ($DB->request($query) as $data) {
         $params=array('plugin_projet_projets_id'=>$options["plugin_projet_projets_id"],
                        'plugin_projet_tasks_id'=>$data["id"],
                        'prefix'=>'');
         self::showTaskGantt($params);
      }
   }

   static function showTaskGantt($options=array()) {
      global $gdata;
      
      
      $restrict = " `plugin_projet_projets_id` = '".$options["plugin_projet_projets_id"]."' ";
      if ($options["plugin_projet_tasks_id"])
         $restrict.= " AND `id` = '".$options["plugin_projet_tasks_id"]."' ";
      $restrict.= " AND `is_deleted` = '0'";
      $restrict.= " AND `is_template` = '0'";
      $restrict.= " AND `show_gantt` = '1'";
      
      $tasks = getAllDatasFromTable("glpi_plugin_projet_tasks",$restrict);
      
      $prefix = $options["prefix"];
      
      if (!empty($tasks)) {
         foreach ($tasks as $task) {
         
            $prefix.= "-";

            //nom
            $gantt_t_name= $prefix." ".$task["name"];
            //color
            $int = hexdec(PluginProjetTaskState::getStatusColor($task["plugin_projet_taskstates_id"]));
            $gantt_t_bgcolor = array(0xFF & ($int >> 0x10), 0xFF & ($int >> 0x8), 0xFF & $int);
            
            $projet = new PluginProjetProjet();
            if ($projet->getFromDB($options["plugin_projet_projets_id"])) {
               $gantt_t_date_begin=$projet->fields["date_begin"];
               $gantt_t_date_end=$projet->fields["date_end"];
            } else {
               $gantt_t_date_begin=date("Y-m-d");
               $gantt_t_date_end=date("Y-m-d");
            }
            $plan = new PluginProjetTaskPlanning();
            $plan->getFromDBbyTask($task["id"]);
            
            if (!empty($plan->fields["begin"])) {
               $gantt_t_date_begin=$plan->fields["begin"];
            }
            if (!empty($plan->fields["end"])) {
               $gantt_t_date_end=$plan->fields["end"];
            }
            
            $gdata[]=array("type"=>'phase',
                           "task"=>$task["id"],
                           "projet"=>$options["plugin_projet_projets_id"],
                           "name"=>$gantt_t_name,
                           "begin"=>$gantt_t_date_begin,
                           "end"=>$gantt_t_date_end,
                           "advance"=>$task["advance"],
                           "bg_color"=>$gantt_t_bgcolor,
                           );
                           
           if ($task["depends"]==1) {
               $gdata[]=array("type"=>'dependency',
                                       "projet"=>$options["plugin_projet_projets_id"],
                                       "name"=>$gantt_t_name,
                                       "date_begin"=>$gantt_t_date_begin);
            }
            
            $restrictchild= " `plugin_projet_projets_id` = '".$options["plugin_projet_projets_id"]."'";
            
            $condition = " `plugin_projet_tasks_id_2` = '".$task["id"]."' ";
            $tasks_tasks = getAllDatasFromTable("glpi_plugin_projet_tasks_tasks",$condition);
            $tab = array();
            if (!empty($tasks_tasks)) {
               foreach ($tasks_tasks as $tasks_task) {
                  $tab[]= $tasks_task['plugin_projet_tasks_id_1'];
               }
            }
            if (!empty($tab)) {
               $restrictchild.= " AND `id` IN (".implode(',',$tab).")";
            }
            
            
            $restrictchild.= " AND `is_deleted` = '0'";
            $restrictchild.= " AND `is_template` = '0'";
            $restrictchild.= " AND `show_gantt` = '1'";
            $restrictchild.= " ORDER BY `plugin_projet_taskstates_id` DESC";

            $childs = getAllDatasFromTable("glpi_plugin_projet_tasks",$restrictchild);
            
            if (!empty($childs) && !empty($tab)) {
               foreach ($childs as $child) {
                  $params=array('plugin_projet_projets_id'=>$options["plugin_projet_projets_id"],
                                 'plugin_projet_tasks_id'=>$child["id"],
                                 'parent'=>1,
                                 'prefix'=>$prefix);
                  self::showTaskGantt($params);
               }
            }
         }
      }        
   }
   
   static function showMinimalList($params = array()) {
      global $DB,$CFG_GLPI;
      
      $item = new self();
      $itemtype = $item->getType();
      $itemtable = $item->getTable();
      
      // Default values of parameters
      $p['link']           = array();//
      $p['field']          = array();//
      $p['contains']       = array();//
      $p['searchtype']     = array();//
      $p['sort']           = '1'; //
      $p['order']          = 'ASC';//
      $p['start']          = 0;//
      $p['is_deleted']     = 0;
      $p['id']             = 0;
      $p['export_all']     = 0;
      $p['link2']          = '';//
      $p['contains2']      = '';//
      $p['field2']         = '';//
      $p['itemtype2']      = '';
      $p['searchtype2']    = '';
      $p['withtemplate']   = 0;
      
      foreach ($params as $key => $val) {
            $p[$key]=$val;
      }

      if (isset($_POST["start"])) {
         $p['start'] = $_POST["start"];
      } else {
         $p['start'] = 0;
      }
      
      if (isset($_POST["sort"])) {
         $p['sort'] = $_POST["sort"];
      } else {
         $p['sort'] = 1;
      }
      
      if (isset($_POST["order"]) && ($_POST["order"] == "DESC")) {
         $p['order'] = "DESC";
      } else {
         $p['order'] = "ASC";
      }
      
      $PluginProjetProjet = new PluginProjetProjet();
      $PluginProjetProjet->getFromDB($p['id']);
      $canedit = $PluginProjetProjet->can($p['id'], 'w');
      
      // Manage defautll seachtype value : for bookmark compatibility
      if (count($p['contains'])) {
         foreach ($p['contains'] as $key => $val) {
            if (!isset($p['searchtype'][$key])) {
               $p['searchtype'][$key]='contains';
            }
         }
      }
      if (is_array($p['contains2']) && count($p['contains2'])) {
         foreach ($p['contains2'] as $key => $val) {
            if (!isset($p['searchtype2'][$key])) {
               $p['searchtype2'][$key]='contains';
            }
         }
      }

      $target= Toolbox::getItemTypeSearchURL($itemtype);

      $limitsearchopt=Search::getCleanedOptions($itemtype);
      
      $LIST_LIMIT=$_SESSION['glpilist_limit'];
      
      // Set display type for export if define
      $output_type=Search::HTML_OUTPUT;
      if (isset($_GET['display_type'])) {
         $output_type=$_GET['display_type'];
         // Limit to 10 element
         if ($_GET['display_type']==Search::GLOBAL_SEARCH) {
            $LIST_LIMIT=Search::GLOBAL_DISPLAY_COUNT;
         }
      }
      
      $entity_restrict = $item->isEntityAssign();
      
      // Get the items to display
      $toview=Search::addDefaultToView($itemtype);
      
      // Add items to display depending of personal prefs
      $displaypref=DisplayPreference::getForTypeUser($itemtype,Session::getLoginUserID());
      if (count($displaypref)) {
         foreach ($displaypref as $val) {
            array_push($toview,$val);
         }
      }
      
      // Add searched items
      if (count($p['field'])>0) {
         foreach($p['field'] as $key => $val) {
            if (!in_array($val,$toview) && $val!='all' && $val!='view') {
               array_push($toview,$val);
            }
         }
      }

      // Add order item
      if (!in_array($p['sort'],$toview)) {
         array_push($toview,$p['sort']);
      }
      
      // Clean toview array
      $toview=array_unique($toview);
      foreach ($toview as $key => $val) {
         if (!isset($limitsearchopt[$val])) {
            unset($toview[$key]);
         }
      }

      $toview_count=count($toview);
      
      //// 1 - SELECT
      $query = "SELECT ".Search::addDefaultSelect($itemtype);

      // Add select for all toview item
      foreach ($toview as $key => $val) {
         $query.= Search::addSelect($itemtype,$val,$key,0);
      }
      
      $query .= "`".$itemtable."`.`id` AS id ";
      
      //// 2 - FROM AND LEFT JOIN
      // Set reference table
      $query.= " FROM `".$itemtable."`";

      // Init already linked tables array in order not to link a table several times
      $already_link_tables=array();
      // Put reference table
      array_push($already_link_tables,$itemtable);

      // Add default join
      $COMMONLEFTJOIN = Search::addDefaultJoin($itemtype,$itemtable,$already_link_tables);
      $query .= $COMMONLEFTJOIN;

      $searchopt=array();
      $searchopt[$itemtype]=&Search::getOptions($itemtype);
      // Add all table for toview items
      foreach ($toview as $key => $val) {
         $query .= Search::addLeftJoin($itemtype, $itemtable, $already_link_tables,
                                    $searchopt[$itemtype][$val]["table"],
                                    $searchopt[$itemtype][$val]["linkfield"], 0, 0,
                                    $searchopt[$itemtype][$val]["joinparams"]);
      }

      // Search all case :
      if (in_array("all",$p['field'])) {
         foreach ($searchopt[$itemtype] as $key => $val) {
            // Do not search on Group Name
            if (is_array($val)) {
               $query .= Search::addLeftJoin($itemtype, $itemtable, $already_link_tables,
                                          $searchopt[$itemtype][$key]["table"],
                                          $searchopt[$itemtype][$key]["linkfield"], 0, 0,
                                          $searchopt[$itemtype][$key]["joinparams"]);
            }
         }
      }
      
      $query.= " WHERE `".$itemtable."`.`plugin_projet_projets_id` = '".$p['id']."'";
      $query.= " AND `".$itemtable."`.`is_deleted` = '".$p['is_deleted']."' ";
      
      //// 7 - Manage GROUP BY
      $GROUPBY = "";
      // Meta Search / Search All / Count tickets
      if (in_array('all',$p['field'])) {
         $GROUPBY = " GROUP BY `".$itemtable."`.`id`";
      }

      if (empty($GROUPBY)) {
         foreach ($toview as $key2 => $val2) {
            if (!empty($GROUPBY)) {
               break;
            }
            if (isset($searchopt[$itemtype][$val2]["forcegroupby"])) {
               $GROUPBY = " GROUP BY `".$itemtable."`.`id`";
            }
         }
      }
      $query.=$GROUPBY;
      //// 4 - ORDER
      $ORDER=" ORDER BY `id` ";
      foreach($toview as $key => $val) {
         if ($p['sort']==$val) {
            $ORDER= Search::addOrderBy($itemtype,$p['sort'],$p['order'],$key);
         }
      }
      $query.=$ORDER;

      // Get it from database	
      
      if ($result = $DB->query($query)) {
         $numrows =  $DB->numrows($result);
         
         $globallinkto = Search::getArrayUrlLink("field",$p['field']).
                        Search::getArrayUrlLink("link",$p['link']).
                        Search::getArrayUrlLink("contains",$p['contains']).
                        Search::getArrayUrlLink("field2",$p['field2']).
                        Search::getArrayUrlLink("contains2",$p['contains2']).
                        Search::getArrayUrlLink("itemtype2",$p['itemtype2']).
                        Search::getArrayUrlLink("link2",$p['link2']);

         $parameters = "sort=".$p['sort']."&amp;order=".$p['order'].$globallinkto;
         
         if ($output_type==Search::GLOBAL_SEARCH) {
            if (class_exists($itemtype)) {
               echo "<div class='center'><h2>".$item::getTypeName();
               // More items
               if ($numrows>$p['start']+Search::GLOBAL_DISPLAY_COUNT) {
                  echo " <a href='$target?$parameters'>".__('All')."</a>";
               }
               echo "</h2></div>\n";
            } else {
               return false;
            }
         }
           
         if ($p['start']<$numrows) {
            
            if ($output_type==Search::HTML_OUTPUT && !$p['withtemplate']) {
               echo "<div align='center'>";
               echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/task.php?contains%5B0%5D=".
               $p['id']."&field%5B0%5D=23&sort=1&is_deleted=0&start=0'>"._x('button','Search')."</a><br>";
               echo "</div>";
            }
           
            // Pager
            
            if ($output_type==Search::HTML_OUTPUT) { // HTML display - massive modif
               $search_config="";
               if ($item->canCreate() && $canedit) {
                  $tmp = " class='pointer' onClick=\"var w = window.open('".$CFG_GLPI["root_doc"].
                        "/front/popup.php?popup=search_config&amp;itemtype=".$itemtype."' ,'glpipopup', ".
                        "'height=400, width=1000, top=100, left=100, scrollbars=yes' ); w.focus();\"";

                  $search_config = "<img alt='".__('Select default items to show')."' title='".__('Select default items to show').
                                    "' src='".$CFG_GLPI["root_doc"]."/pics/options_search.png' ";
                  $search_config .= $tmp.">";
               }
               //echo Search::showHeaderItem($output_type,$search_config,$header_num,"",0,$p['order']);
            }
            
            if ($output_type==Search::HTML_OUTPUT) {
               Html::printAjaxPager(self::getTypeName(2),$p['start'],$numrows,$search_config);
               echo "<br>";
            }
            
            // Define begin and end var for loop
            // Search case
            $begin_display=$p['start'];
            $end_display=$p['start']+$LIST_LIMIT;

            // Export All case
            if ($p['export_all']) {
               $begin_display=0;
               $end_display=$numrows;
            }
            
            //massive action
            $sel="";
            if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";

            if ($item->canCreate() && $canedit && $output_type==Search::HTML_OUTPUT && $p['withtemplate']!=2) {
               Html::openMassiveActionsForm('massform'.$itemtype);
               $massiveactionparams = array('num_displayed' => $end_display-$begin_display,
                                               'fixed'         => true,
                                               'is_deleted'    => $p['is_deleted']);
               Html::showMassiveActions($itemtype, $massiveactionparams);
            }
            // Add toview elements
            $nbcols=$toview_count;
            
            if ($output_type==Search::HTML_OUTPUT) { // HTML display - massive modif
               $nbcols++;
            }

            // Display List Header
            echo Search::showHeader($output_type,$end_display-$begin_display+1,$nbcols,1);
            
            $header_num=1;
            // Display column Headers for toview items
            $headers_line        = '';
            $headers_line_top    = '';
            $headers_line_bottom = '';
            echo Search::showNewLine($output_type);

            
            if (($output_type == Search::HTML_OUTPUT)
                && $item->canCreate() && $canedit) { // HTML display - massive modif
               
               $headers_line_top .= Search::showHeaderItem($output_type,
                                         Html::getCheckAllAsCheckbox('massform'.$itemtype),
                                         $header_num, "", 0, $p['order']);
               $headers_line_bottom .= Search::showHeaderItem($output_type,
                                         Html::getCheckAllAsCheckbox('massform'.$itemtype),
                                         $header_num, "", 0, $p['order']);
            }
           
            // Display column Headers for toview items
            foreach ($toview as $key => $val) {
               $linkto='';
               if (!isset($searchopt[$itemtype][$val]['nosort'])
                     || !$searchopt[$itemtype][$val]['nosort']) {
                  $linkto = "javascript:reloadTab('sort=".$val."&amp;order=".($p['order']=="ASC"?"DESC":"ASC").
                           "&amp;start=".$p['start'].$globallinkto."')";
               }
               $headers_line .= Search::showHeaderItem($output_type,$searchopt[$itemtype][$val]["name"],
                                          $header_num,$linkto,($p['sort'] == $val),$p['order']);
            }
            
            // End Line for column headers
            $headers_line .= Search::showEndLine($output_type);
            
            $headers_line_top    .= $headers_line;
            $headers_line_bottom .= $headers_line;

            echo $headers_line_top;
            
            $DB->data_seek($result,$p['start']);
           
            // Define begin and end var for loop
            // Search case
            $i=$begin_display;

            // Init list of items displayed
            if ($output_type==Search::HTML_OUTPUT) {
               Session::initNavigateListItems($itemtype, PluginProjetProjet::getTypeName(2)." = ".
                (empty($PluginProjetProjet->fields['name']) ? "(".$p['id'].")" : $PluginProjetProjet->fields['name']));
            }

            // Num of the row (1=header_line)
            $row_num=1;
            // Display Loop
            while ($i < $numrows && $i<($end_display)) {
               
               $item_num=1;
               $data=$DB->fetch_array($result);
               $i++;
               $row_num++;
               
               echo Search::showNewLine($output_type,($i%2));
               
               Session::addToNavigateListItems($itemtype,$data['id']);
               
               $tmpcheck="";
               if ($item->canCreate() && $canedit && $output_type==Search::HTML_OUTPUT && $p['withtemplate']!=2) {
                  $sel="";
                  $tmpcheck="<input type='checkbox' name='item[".$data["id"]."]' value='1' $sel>";
                  
               }
               echo Search::showItem($output_type,$tmpcheck,$item_num,$row_num,"width='10'");
               
               foreach ($toview as $key => $val) {
                  echo Search::showItem($output_type,Search::giveItem($itemtype,$val,$data,$key),$item_num,
                                       $row_num,
                           Search::displayConfigItem($itemtype,$val,$data,$key));
               }
           
               echo Search::showEndLine($output_type);
            }
            // Close Table
            $title="";
            // Create title
            if ($output_type==Search::PDF_OUTPUT_PORTRAIT 
               || $output_type==Search::PDF_OUTPUT_LANDSCAPE) {
               $title.=__('Tasks list', 'projet');
            }
           
            // Display footer
            echo Search::showFooter($output_type,$title);
           
            //massive action
            if ($item->canCreate() && $canedit && $output_type==Search::HTML_OUTPUT && $p['withtemplate']!=2) {

               $massiveactionparams['ontop'] = false;
               Html::showMassiveActions($itemtype, $massiveactionparams);
               // End form for delete item
               Html::closeForm();
            } else {
               echo "</table></div>";
            }

            // Pager
            if ($output_type==Search::HTML_OUTPUT) {
               echo "<br>";
               Html::printAjaxPager(self::getTypeName(2), $p['start'], $numrows);
            }
         } else {
            echo Search::showError($output_type);
         }
      }
   }
   
   
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType()=='PluginProjetProjet') {
         self::pdfForProjet($pdf, $item);

      } else {
         return false;
      }
      return true;
   }
   
   /**
    * Show for PDF an resources : tasks informations
    * 
    * @param $pdf object for the output
    * @param $ID of the resources
    */
   static function pdfForProjet(PluginPdfSimplePDF $pdf, PluginProjetProjet $appli) {
      global $DB;
      
      $ID = $appli->fields['id'];

      if (!$appli->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_projet_haveRight("projet","r")) {
         return false;
      }

      $query = "SELECT * 
               FROM `glpi_plugin_projet_tasks` 
               WHERE `plugin_projet_projets_id` = '$ID'
               AND `is_deleted` ='0'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      $i=$j=0;
      
      $pdf->setColumnsSize(100);

      if($number>0) {
         
         $pdf->displayTitle('<b>'._n('Associated task' , 'Associated tasks', 2, 'projet').'</b>');

         $pdf->setColumnsSize(14,14,14,14,16,14,14);
         $pdf->displayTitle('<b><i>'.
            __('Name'),
            _n('Type' , 'Types', 1),
            __('Description'),
            __('Duration'),
            __('Planning'),
            _n('User' , 'Users', 1),
            _n('Group' , 'Groups', 1).'</i></b>'
            );
      
         $i++;
         
         while ($j < $number) {
            
            $tID=$DB->result($result, $j, "id");
            $actiontime_ID=$DB->result($result, $j, "actiontime");
            
            $actiontime='';
            $units=Toolbox::getTimestampTimeUnits($actiontime_ID);

            $hour = $units['hour'];
            $minute = $units['minute'];
            if ($hour) $actiontime = sprintf(__('%1$d hours'), $hour);

            if ($minute || !$hour)
               $actiontime.= sprintf(__('%1$d minutes'), $minute);
            
            $restrict = " `plugin_projet_tasks_id` = '".$tID."' ";
            $plans = getAllDatasFromTable("glpi_plugin_projet_taskplannings",$restrict);
            
            if (!empty($plans)) {
               foreach ($plans as $plan) {
                  $planification = Html::convDateTime($plan["begin"]) . "&nbsp;->&nbsp;" .
                  Html::convDateTime($plan["end"]);
               }
            } else {
               $planification = __('None');
            }
            
            $users_id=$DB->result($result, $j, "users_id");
            
            $managers=Html::clean(getUserName($users_id));
            $name=$DB->result($result, $j, "name");
            $task_type=$DB->result($result, $j, "plugin_projet_tasktypes_id");
            $comment=$DB->result($result, $j, "comment");
            $groups_id=$DB->result($result, $j, "groups_id");
            
            $pdf->displayLine(
               Html::clean($name),
               Html::clean(Dropdown::getDropdownName("glpi_plugin_projet_tasktypes",$task_type)),
               $comment,
               $actiontime,
               Html::clean($planification),
               $managers,
               Html::clean(Dropdown::getDropdownName("glpi_groups",$groups_id))
               );
            $j++;
         }
      } else {
         $pdf->displayLine(__('No item found'));
      }	
      
      $pdf->displaySpace();
   }

   
   // Cron action
   static function cronInfo($name) {
       
      switch ($name) {
         case 'ProjetTask':
            return array (
               'description' => __('Outdated tasks', 'projet'));   // Optional
            break;
      }
      return array();
   }

   /**
    * Cron action on tasks : ExpiredTasks
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronProjetTask($task=NULL) {
      global $DB,$CFG_GLPI;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message=array();
      $cron_status = 0;
      
      $projettask = new self();
      $query_expired = $projettask->queryAlert();
      
      $querys = array(Alert::END=>$query_expired);
      
      $task_infos = array();
      $task_messages = array();

      foreach ($querys as $type => $query) {
         $task_infos[$type] = array();
         foreach ($DB->request($query) as $data) {
            $entity = $data['entities_id'];
            $message = $data["name"]."<br>\n";
            $task_infos[$type][$entity][] = $data;

            if (!isset($tasks_infos[$type][$entity])) {
               $task_messages[$type][$entity] = __('Outdated tasks', 'projet')."<br />";
            }
            $task_messages[$type][$entity] .= $message;
         }
      }
      
      foreach ($querys as $type => $query) {
      
         foreach ($task_infos[$type] as $entity => $tasks) {
            Plugin::loadLang('projet');

            if (NotificationEvent::raiseEvent("AlertExpiredTasks",
                                              new PluginProjetProjet(),
                                              array('entities_id'=>$entity,
                                                    'tasks'=>$tasks))) {
               $message = $task_messages[$type][$entity];
               $cron_status = 1;
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",
                                                       $entity).":  $message\n");
                  $task->addVolume(1);
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",
                                                                    $entity).":  $message");
               }

            } else {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",$entity).
                             ":  Send tasks alert failed\n");
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                          ":  Send tasks alert failed",false,ERROR);
               }
            }
         }
      }
      
      return $cron_status;
   }
   
   function queryAlert() {

      $date=date("Y-m-d");
      $query = "SELECT `".$this->getTable()."`.*, `glpi_plugin_projet_projets`.`entities_id`
            FROM `".$this->getTable()."`
            LEFT JOIN `glpi_plugin_projet_taskplannings` 
            ON (`glpi_plugin_projet_taskplannings`.`plugin_projet_tasks_id` = `".$this->getTable()."`.`id`)
            LEFT JOIN `glpi_plugin_projet_projets` 
            ON (`glpi_plugin_projet_projets`.`id` = `".$this->getTable()."`.`plugin_projet_projets_id`)
            WHERE `glpi_plugin_projet_taskplannings`.`end` IS NOT NULL 
            AND `glpi_plugin_projet_taskplannings`.`end` <= '".$date."' 
            AND `glpi_plugin_projet_projets`.`is_template` = '0' 
            AND `glpi_plugin_projet_projets`.`is_deleted` = '0' 
            AND `".$this->getTable()."`.`is_deleted` = '0'";
            //select finished tasks
            $finished = " `for_dependency` = '1' ";
            $states = getAllDatasFromTable("glpi_plugin_projet_taskstates",$finished);
            $tab = array();
            if (!empty($states)) {
               foreach ($states as $state) {
                  $tab[]= $state['id'];
               }
            }
            if (!empty($tab)) {
               $query.= "AND `plugin_projet_taskstates_id` NOT IN (".implode(',',$tab).")";
            }

      return $query;
   }
   
   //Massive action
   function getSpecificMassiveActions($checkitem = NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($isadmin) {
         $actions['Duplicate'] = __('Duplicate', 'projet');
         if (Session::haveRight('transfer', 'r')
            && Session::isMultiEntitiesMode()) {
            $actions['Transfert'] = __('Transfer');
         }
      }
      return $actions;
   }

   function showSpecificMassiveActionsParameters($input = array()) {

      switch ($input['action']) {
         case "Duplicate" :
            Dropdown::show('Entity');
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='" . _sx('button', 'Post') . "'>";
            return true;
            break;
         case "Transfert" :
            Dropdown::show('Entity');
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='" . _sx('button', 'Post') . "'>";
            return true;
            break;

         default :
            return parent::showSpecificMassiveActionsParameters($input);
            break;
      }
      return false;
   }

   function doSpecificMassiveActions($input = array()) {

      $res = array('ok' => 0,
         'ko' => 0,
         'noright' => 0);

      $projet_item = new PluginProjetProjet_Item();

      switch ($input['action']) {
         case "Transfert" :
            if ($input['itemtype'] == 'PluginProjetTask') {
               foreach ($input["item"] as $key => $val) {
                  if ($val == 1) {
                     $this->getFromDB($key);
                     $tasktype = PluginProjetTaskType::transfer($this->fields["plugin_projet_tasktypes_id"],
                                                                     $input['entities_id']);
                     if ($tasktype > 0) {
                        $values["id"] = $key;
                        $values["plugin_projet_tasktypes_id"] = $tasktype;
                        $this->update($values);
                     }

                     unset($values);
                     $values["id"] = $key;
                     $values["entities_id"] = $input['entities_id'];

                     if ($this->update($values)) {
                        $res['ok']++;
                     } else {
                        $res['ko']++;
                     }
                  }
               }
            }
            break;
         case "Duplicate" :
            if ($input['itemtype']=='PluginProjetTask') {
               foreach ($input["item"] as $key => $val) {
                  if ($val==1) {
                     $this->getFromDB($key);
                     unset($this->fields["id"]);
                     $this->fields["name"]=addslashes($this->fields["name"]);
                     $this->fields["comment"]=addslashes($this->fields["comment"]);
                     $this->fields["sub"]=addslashes($this->fields["sub"]);
                     $this->fields["others"]=addslashes($this->fields["others"]);
                     $this->fields["affect"]=addslashes($this->fields["affect"]);
                     if ($this->add($this->fields)) {
                        $res['ok']++;
                     } else {
                        $res['ko']++;
                     }
                  }
               }
            }
            break;
         default :
            return parent::doSpecificMassiveActions($input);
            break;
      }
      return $res;
   }
}

?>