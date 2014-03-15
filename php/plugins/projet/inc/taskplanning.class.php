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

class PluginProjetTaskPlanning extends CommonDBTM {
   
   static function canCreate() {
      return (plugin_projet_haveRight('task', 'w') 
                  && Session::haveRight('show_planning', 1));
   }

   static function canView() {
      return plugin_projet_haveRight('task', 'r');
   }
   
   static function getTypeName($nb = 0) {

      return sprintf(__('%1$s - %2$s'), _n('Project', 'Projects', $nb, 'projet'),
                                __('Tasks list', 'projet'));
   }
   
	function prepareInputForAdd($input) {

      if (!isset($input["begin"]) || !isset($input["end"]) ){
         return false;
      }

      $this->fields["begin"] = $input["begin"];
      $this->fields["end"] = $input["end"];

      if (!$this->test_valid_date()) {
         self::displayError("date");
         return false;
      }
      
      $fup=new PluginProjetTask();
      $fup->getFromDB($input["plugin_projet_tasks_id"]);
      
      if(isset($fup->fields["users_id"])) {
         Planning::checkAlreadyPlanned($fup->fields["users_id"], $input["begin"], $input["end"]);
      }

		return $input;
	}
	
	function post_addItem() {
      global $CFG_GLPI;
      
      // Auto update actiontime
      $fup=new PluginProjetTask();
      $fup->getFromDB($this->input["plugin_projet_tasks_id"]);
      if ($fup->fields["actiontime"]==0) {
         $timestart  = strtotime($this->input["begin"]);
         $timeend    = strtotime($this->input["end"]);
         $updates2[] = "actiontime";
         $fup->fields["actiontime"] = $timeend-$timestart;

         $updates2[]="plugin_projet_taskstates_id";
         $fup->fields["plugin_projet_taskstates_id"]=PluginProjetTaskState::getStatusForPlanning();
         $fup->updateInDB($updates2);
         
      }
   }
	
	function prepareInputForUpdate($input) {
		global $CFG_GLPI;
      
      $this->getFromDB($input["id"]);
      // Save fields
      $oldfields=$this->fields;
      $this->fields["begin"] = $input["begin"];
      $this->fields["end"] = $input["end"];

      if (!$this->test_valid_date()) {
         $this->displayError("date");
         return false;
      }
      
      if(isset($fup->fields["users_id"])) {
         Planning::checkAlreadyPlanned($fup->fields["users_id"], $input["begin"], $input["end"],
                                    array('PluginProjetTask' => array($input["id"])));
      } 
      // Restore fields
      $this->fields=$oldfields;
      
		return $input;
	}
	
	function post_updateItem($history=1) {
		global $CFG_GLPI;
		
      $fup=new PluginProjetTask();
      $fup->getFromDB($this->input["plugin_projet_tasks_id"]);
      $timestart  = strtotime($this->input["begin"]);
      $timeend    = strtotime($this->input["end"]);
      $updates2[] = "actiontime";
      $fup->fields["actiontime"] = $timeend-$timestart;
      $updates2[]="plugin_projet_taskstates_id";
      $fup->fields["plugin_projet_taskstates_id"]=PluginProjetTaskState::getStatusForPlanning();
      $fup->updateInDB($updates2);
	}
   
   /**
    * Read the planning information associated with a task
    *
    * @param $plugin_projet_tasks_id integer ID of the task
    *
    * @return bool, true if exists
    */
   function getFromDBbyTask($plugin_projet_tasks_id) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `plugin_projet_tasks_id` = '$plugin_projet_tasks_id'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
      }
      return false;
   }
   
   function showFormForTask($projet, PluginProjetTask $task) {
      global $CFG_GLPI;

      $PluginProjetProjet = new PluginProjetProjet();
      $PluginProjetProjet->getFromDB($projet);
      $taskid = $task->getField('id');
      if ($taskid>0 && $this->getFromDBbyTask($taskid)) {
         if ($this->canCreate()) {
            echo "<script type='text/javascript' >\n";
            echo "function showPlan".$taskid."(){\n";
            echo "Ext.get('plan').setDisplayed('none');";
            $params = array (
               'form' => 'followups',
               'id' => $this->fields["id"],
               'begin' => $this->fields["begin"],
               'end' => $this->fields["end"],
               'entity' => $PluginProjetProjet->fields["entities_id"]
            );
            Ajax::updateItemJsCode('viewplan', $CFG_GLPI["root_doc"] . "/plugins/projet/ajax/planning.php", $params);
            echo "}";
            echo "</script>\n";
            echo "<div id='plan' onClick='showPlan".$taskid."()'>\n";
            echo "<span class='showplan'>";
         }
         if ($this->fields["begin"] && $this->fields["end"]) {
            echo Html::convDateTime($this->fields["begin"]).
              "&nbsp;->&nbsp;".Html::convDateTime($this->fields["end"]);
         } else {
            _e('Plan this task');
         }
         if ($this->canCreate()) {
            echo "</span>";
            echo "</div>\n";
            echo "<div id='viewplan'></div>\n";
         }
      } else {
         if ($this->canCreate()) {
            echo "<script type='text/javascript' >\n";
            echo "function showPlanUpdate(){\n";
            echo "Ext.get('plan').setDisplayed('none');";
            $params = array('form'     => 'followups',
                            'entity'   => $_SESSION["glpiactive_entity"]);
            Ajax::UpdateItemJsCode('viewplan',$CFG_GLPI["root_doc"]."/plugins/projet/ajax/planning.php",$params);
            echo "};";
            echo "</script>";

            echo "<div id='plan'  onClick='showPlanUpdate()'>\n";
            echo "<span class='showplan'>".__('Plan this task')."</span>";
            echo "</div>\n";
            echo "<div id='viewplan'></div>\n";
         } else {
            _e('None');
         }
      }
   }
   
	// SPECIFIC FUNCTIONS
   
   /**
    * Current dates are valid ? begin before end
    *
    *@return boolean
    **/
   function test_valid_date() {
      return (!empty($this->fields["begin"]) && !empty($this->fields["end"])
              && strtotime($this->fields["begin"]) < strtotime($this->fields["end"]));
   }

   /**
    * Add error message to message after redirect
    * @param $type error type : date / is_res / other
    *@return nothing
    **/
   static function displayError($type) {

      switch ($type) {
         case "date" :
            Session::addMessageAfterRedirect(
                     __('Error in entering dates. The starting date is later than the ending date'),
                     false,ERROR);
            break;

         default :
            Session::addMessageAfterRedirect(__('Unknown error'),false,ERROR);
            break;
      }
   }
   
   static function getAlreadyPlannedInformation($val) {
      global $CFG_GLPI;
      
      $out="";

      $out .= PluginProjetProjet::getTypeName()." - ".PluginProjetTask::getTypeName().' : '.Html::convDateTime($val["begin"]).' -> '.
              Html::convDateTime($val["end"]).' : ';
      $out .= "<a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php?id=".
               $val["plugin_projet_tasks_id"]."'>";
      $out .= Html::resume_text($val["name"],80).'</a>';

      return $out;
   }
   /**
    * Populate the planning with planned projet tasks
    *
    * @param $who ID of the user (0 = undefined)
    * @param $who_group ID of the group of users (0 = undefined, mine = login user ones)
    * @param $begin Date
    * @param $end Date
    *
    * @return array of planning item
    */
   static function populatePlanning($parm) {
      global $DB, $CFG_GLPI;
      
      $output = array();
      
      if (!isset($parm['begin']) || $parm['begin'] == 'NULL'
            || !isset($parm['end']) || $parm['end'] == 'NULL') {
         return $parm;
      }

      $who       = $parm['who'];
      $who_group = $parm['who_group'];
      $begin     = $parm['begin'];
      $end       = $parm['end'];
      // Get items to print
      $ASSIGN="";

      if ($who_group==="mine") {
         if (count($_SESSION["glpigroups"])) {
            $groups=implode("','",$_SESSION['glpigroups']);
            $ASSIGN=" `glpi_plugin_projet_tasks`.`users_id` IN (SELECT DISTINCT `users_id`
                                    FROM `glpi_groups_users`
                                    WHERE `groups_id` IN ('$groups'))
                                          AND ";
         } else { // Only personal ones
            $ASSIGN="`glpi_plugin_projet_tasks`.`users_id` = '$who'
                     AND ";
         }
      } else {
         if ($who>0) {
            $ASSIGN="`glpi_plugin_projet_tasks`.`users_id` = '$who'
                     AND ";
         }
         if ($who_group>0) {
            $ASSIGN="`glpi_plugin_projet_tasks`.`users_id` IN (SELECT `users_id`
                                    FROM `glpi_groups_users`
                                    WHERE `groups_id` = '$who_group')
                                          AND ";
         }
      }
      if (empty($ASSIGN)) {
         $ASSIGN="`glpi_plugin_projet_tasks`.`users_id` IN (
                                 SELECT DISTINCT `glpi_profiles_users`.`users_id`
                                 FROM `glpi_profiles`
                                 LEFT JOIN `glpi_profiles_users`
                                    ON (`glpi_profiles`.`id` = `glpi_profiles_users`.`profiles_id`)
                                 WHERE `glpi_profiles`.`interface`='central' ";

         $ASSIGN.=getEntitiesRestrictRequest("AND","glpi_profiles_users", '',
                                             $_SESSION["glpiactive_entity"],1);
         $ASSIGN.=") AND ";
      }

      $query = "SELECT `glpi_plugin_projet_tasks`.*,
                        `glpi_plugin_projet_taskplannings`.`begin`, 
                        `glpi_plugin_projet_taskplannings`.`end`,
                        `glpi_plugin_projet_projets`.`name` as project,
                        `glpi_plugin_projet_tasktypes`.`name` as type,
                        `glpi_plugin_projet_taskstates`.`name` as state,
                        `glpi_locations`.`completename` as location
                FROM `glpi_plugin_projet_tasks`
                LEFT JOIN `glpi_plugin_projet_taskplannings` 
                ON (`glpi_plugin_projet_taskplannings`.`plugin_projet_tasks_id` = `glpi_plugin_projet_tasks`.`id`)
                LEFT JOIN `glpi_plugin_projet_projets` 
                ON (`glpi_plugin_projet_projets`.`id` = `glpi_plugin_projet_tasks`.`plugin_projet_projets_id`)
                LEFT JOIN `glpi_plugin_projet_tasktypes` 
                ON (`glpi_plugin_projet_tasktypes`.`id` = `glpi_plugin_projet_tasks`.`plugin_projet_tasktypes_id`)
                LEFT JOIN `glpi_plugin_projet_taskstates` 
                ON (`glpi_plugin_projet_taskstates`.`id` = `glpi_plugin_projet_tasks`.`plugin_projet_taskstates_id`)
                LEFT JOIN `glpi_locations` 
                ON (`glpi_locations`.`id` = `glpi_plugin_projet_tasks`.`locations_id`)
                WHERE $ASSIGN
                      '$begin' < `end` AND '$end' > `begin`";
      //not show archived projects
      $archived = " `for_dependency` = '1' ";
      $states = getAllDatasFromTable("glpi_plugin_projet_taskstates",$archived);
      $tab = array();
      if (!empty($states)) {
         foreach ($states as $state) {
            $tab[]= $state['id'];
         }
      
         $query .= " AND `glpi_plugin_projet_tasks`.`plugin_projet_taskstates_id` NOT IN (".implode($tab,",").")";
      }
      $query .= " ORDER BY `begin`";
      $result=$DB->query($query);

      if ($DB->numrows($result)>0) {
         for ($i=0 ; $data=$DB->fetch_array($result) ; $i++) {

            $key = $parm["begin"].$data["id"]."$$$"."plugin_projet";
            $output[$key]["id"]=$data["id"];
            $output[$key]["plugin_projet_projets_id"]=$data["plugin_projet_projets_id"];
            $output[$key]["users_id"]=$data["users_id"];
            $output[$key]["begin"]=$data["begin"];
            $output[$key]["end"]=$data["end"];
            $output[$key]["name"]=$data["name"];
            $output[$key]["type"]=$data["type"];
            $output[$key]["location"]=$data["location"];
            $output[$key]["project"]=$data["project"];
            $output[$key]["content"]=Html::resume_text($data["comment"],
                                                                  $CFG_GLPI["cut"]);
            $output[$key]["state"]=$data["state"];
            $output[$key]["priority"]=$data["priority"];
            $output[$key]["itemtype"]='PluginProjetTaskPlanning';
         }
      }
         
      return $output;
   }
      

   /**
    * Display a Planning Item
    *
    * @param $parm Array of the item to display
    * @return Nothing (display function)
    **/
   static function displayPlanningItem(array $val, $who, $type="", $complete=0) {
      global $CFG_GLPI;

      $rand=mt_rand(); 
		echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/task.form.php?id=".$val["id"]."'";

		echo " onmouseout=\"cleanhide('content_task_".$val["id"].$rand."')\" 
               onmouseover=\"cleandisplay('content_task_".$val["id"].$rand."')\"";
		echo ">";
		
		switch ($type) {

         case "in" :
            //TRANS: %1$s is the start time of a planned item, %2$s is the end
            $beginend = sprintf(__('From %1$s to %2$s :'), date("H:i",strtotime($val["begin"])),
                                date("H:i",strtotime($val["end"])));
            printf(__('%1$s %2$s'), $beginend, Html::resume_text($val["name"],80)) ;

            break;
         case "begin" :
            $start = sprintf(__('Start at %s'), date("H:i", strtotime($val["begin"])));
            printf(__('%1$s: %2$s'), $start, Html::resume_text($val["name"],80)) ;
            break;

         case "end" :
            $end = sprintf(__('End at %s'), date("H:i", strtotime($val["end"])));
            printf(__('%1$s: %2$s'), $end,  Html::resume_text($val["name"],80)) ;
            break;
      }
      
		if ($val["users_id"] && $who==0) {
         echo " - ".__('User')." ".getUserName($val["users_id"]);
      }
		echo "</a><br>";

		echo PluginProjetProjet::getTypeName(1).
		" : <a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php?id=".
		$val["plugin_projet_projets_id"]."'";
		echo ">".$val["project"]."</a>";

		echo "<div class='over_link' id='content_task_".$val["id"].$rand."'>";
		if ($val["end"])
         echo "<strong>".__('End date')."</strong> : ".Html::convdatetime($val["end"])."<br>";
      if ($val["type"])
         echo "<strong>".PluginProjetTaskType::getTypeName(1)."</strong> : ".
            $val["type"]."<br>";
      if ($val["state"])
         echo "<strong>".__('State')."</strong> : ".
            $val['state']."<br>";
		if ($val["location"])
         echo "<strong>"._n('Location' , 'Locations', 1)."</strong>: ".
            $val["location"]."<br>";
		if ($val["priority"])
         echo "<strong>".__('Priority')."</strong> : ".
            Ticket::getPriorityName($val["priority"])."<br>";
		if ($val["content"])
         echo "<strong>".__('Description')."</strong> : ".$val["content"];
		echo "</div>";
   }
}

?>