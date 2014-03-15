<?php
/*
 * @version $Id: checklist.class.php 480 2012-11-09 tsmr $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginResourcesChecklist extends CommonDBTM {
	
	const RESOURCES_CHECKLIST_IN = 1;
   const RESOURCES_CHECKLIST_OUT = 2;
   
   static function getTypeName($nb=0) {

      return _n('Checklist', 'Checklists', $nb, 'resources');
   }
   
	static function canCreate() {
      return plugin_resources_haveRight('checklist', 'w');
   }

   static function canView() {
      return plugin_resources_haveRight('checklist', 'r');
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
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      
      if (!$withtemplate) {
         if ($item->getID() && $this->canView()) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(self::getTypeName(2), self::countForItem($item));
            }
            return self::getTypeName(2);
         }
      }
      return '';
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      $ID = $item->getField('id');
      if (self::checkifChecklistExist($ID)) {
         self::showFromResources($ID,self::RESOURCES_CHECKLIST_IN,$withtemplate);
         self::showFromResources($ID,self::RESOURCES_CHECKLIST_OUT,$withtemplate);
      } else {
         self::showAddForm($ID);
      }
      return true;
   }
   
   static function countForItem($item) {
      
      if ($item->getField('is_leaving') == 1) {
         $checklist_type=self::RESOURCES_CHECKLIST_OUT;
      } else {
         $checklist_type=self::RESOURCES_CHECKLIST_IN;
      }
      $restrict = "`plugin_resources_resources_id` = '".$item->getField('id')."' 
                     AND `checklist_type` = '".$checklist_type."'
                     AND is_checked != 1 ";
      $nb = countElementsInTable(array('glpi_plugin_resources_checklists'), $restrict);

      return $nb ;
   }
 	
 	static function checkifChecklistExist($ID) {
    
      $restrict = "`plugin_resources_resources_id` = '".$ID."'";

      $checklists = getAllDatasFromTable("glpi_plugin_resources_checklists",$restrict);

      if (!empty($checklists)) {
         foreach ($checklists as $checklist) {
            return $checklist["id"];
         }
      } else {
         return false;
      }
   }
   
   static function checkifChecklistFinished($input) {
    
      $restrict = "`plugin_resources_resources_id` = '".$input['plugin_resources_resources_id']."'
                  AND `checklist_type` = '".$input['checklist_type']."'";

      $checklists = getAllDatasFromTable("glpi_plugin_resources_checklists",$restrict);
      
      $nok = 0;
      if (!empty($checklists)) {
         foreach ($checklists as $checklist) {
            if ($checklist["is_checked"] < 1) {
               $nok += 1;
            }
         }
         if ($nok > 0) {
            return false;
         } else {
            return true;
         }
      } else {
         return false;
      } 
   }
   
   function openFinishedChecklist($input) {
    
      $restrict = "`plugin_resources_resources_id` = '".$input['plugin_resources_resources_id']."'
                  AND `checklist_type` = '".$input['checklist_type']."'";

      $checklists = getAllDatasFromTable("glpi_plugin_resources_checklists",$restrict);

      if (!empty($checklists)) {
         foreach ($checklists as $checklist) {
            $this->update(array("id"=>$checklist["id"],
                                 "is_checked"=>0));
         }
      } else {
         return false;
      } 
   }
   
   static function createTicket($data) {

      $result = false;
      $tt = new TicketTemplate();

      // Create ticket based on ticket template and entity informations of ticketrecurrent
      if ($tt->getFromDB($data['tickettemplates_id'])) {
         // Get default values for ticket
         $input = Ticket::getDefaultValues($data['entities_id']);
         // Apply tickettemplates predefined values
         $ttp        = new TicketTemplatePredefinedField();
         $predefined = $ttp->getPredefinedFields($data['tickettemplates_id'], true);

         if (count($predefined)) {
            foreach ($predefined as $predeffield => $predefvalue) {
               $input[$predeffield] = $predefvalue;
            }
         }
         // Set date to creation date
         $createtime    = date('Y-m-d H:i:s');
         $input['date'] = $createtime;
         // Compute due_date if predefined based on create date
         if (isset($predefined['due_date'])) {
            $input['due_date'] = Html::computeGenericDateTimeSearch($predefined['due_date'], false,
                                                                    $createtime);
         }
         // Set entity
         $input['entities_id'] = $data['entities_id'];
         $input['actiontime'] = $data['actiontime'];
         $res = new PluginResourcesResource();
         if ($res->getFromDB($data['plugin_resources_resources_id'])) {
            
            $input['users_id_recipient'] = $res->fields['users_id_recipient'];
            $input['_users_id_requester'] = $res->fields['users_id_recipient'];
            
            if(isset($res->fields['users_id'])) {
               $input['_users_id_observer'] = $res->fields['users_id'];
            }
            $input['_users_id_assign'] = Session::getLoginUserID();

            $input["itemtype"]= "PluginResourcesResource";
            $input["items_id"]= $data['plugin_resources_resources_id'];
            $input["name"].= addslashes(" ".PluginResourcesResource::getResourceName($data['plugin_resources_resources_id']));
         }
         
         //TODO : ADD checklist lists or add config into plugin ?
         $input["content"].= addslashes("\n\n");
         $input['status'] = Ticket::CLOSED;
         $ticket = new Ticket();
         $input  = Toolbox::addslashes_deep($input);
         if ($tid=$ticket->add($input)) {
            $msg = __('Create a end treatment ticket', 'resources')." OK - ($tid)"; // Success
            $result = true;
         } else {
            $msg = __('Failed operation'); // Failure
         }
      } else {
         $msg = __('No selected element or badly defined operation'); // Not defined
      }
      if ($tid) {
         $changes[0] = 0;
         $changes[1] = '';
         $changes[2] = addslashes($msg);
         Log::history($data['plugin_resources_resources_id'], "PluginResourcesResource", $changes, '', Log::HISTORY_LOG_SIMPLE_MESSAGE);
      }
      return $result;
   }
   
   function dropdownChecklistType($name, $value = 0) {
      
      $checklists = array(self::RESOURCES_CHECKLIST_IN => __('At the arriving of a resource', 'resources'),
                          self::RESOURCES_CHECKLIST_OUT => __('At the leaving of a resource', 'resources'));
      
      if (!empty($checklists)) {

         return Dropdown::showFromArray($name, $checklists, array('value'  => $value));
      } else {
         return false;
      }
   }
   
   static function getChecklistType($value) {
      
      switch ($value) {
         case self::RESOURCES_CHECKLIST_IN :
            return __('At the arriving of a resource', 'resources');
         case self::RESOURCES_CHECKLIST_OUT :
            return __('At the leaving of a resource', 'resources');
         default :
            return "";
      }
   }
   
   function prepareInputForAdd($input) {
		global $DB;
		
		$query="SELECT MAX(`rank`) 
				FROM `".$this->getTable()."` 
				WHERE `checklist_type` = '".$input['checklist_type']."' 
				AND `plugin_resources_contracttypes_id` = '".$input['plugin_resources_contracttypes_id']."' 
				AND `plugin_resources_resources_id` = '".$input['plugin_resources_resources_id']."' 
				AND `entities_id` = '".$input['entities_id']."' ";
		$result=$DB->query($query);
		$input["rank"]=$DB->result($result,0,0)+1;

		return $input;
	}
   
   static function showAddForm($ID) {
      
      echo "<div align='center'>";
      echo "<form action='".Toolbox::getItemTypeFormURL('PluginResourcesResource')."' method='post'>";
      echo "<table class='tab_cadre' width='50%'>";
      echo "<tr>";
      echo "<th colspan='2'>";
      _e('Create checklists', 'resources');
      echo "</th></tr>";
      echo "<tr class='tab_bg_2 center'>";
      echo "<td colspan='2'>";
      echo "<input type='submit' name='add_checklist_resources' value='"._sx('button', 'Post')."' class='submit' />";
      echo "<input type='hidden' name='id' value='".$ID."'>";
      echo "</td></tr></table>";
      Html::closeForm();
      echo "</div>";
   }
   
   /**
    * Modify checklist's ranking and automatically reorder all checklists
    *
    * @param $ID the checklist ID whose ranking must be modified
    * @param $checklist_type IN or OUT
    * @param $plugin_resources_resources_id the resources ID
    * @param $action up or down
   **/
   function changeRank($input) {
      global $DB;

      $sql = "SELECT `rank`
              FROM `".$this->getTable()."`
              WHERE `id` ='".$input['id']."'";
      
      if ($result = $DB->query($sql)) {
         if ($DB->numrows($result)==1) {
            $current_rank=$DB->result($result,0,0);
            // Search rules to switch
            $sql2 = "SELECT `ID`,`rank` 
				FROM `".$this->getTable()."` 
				WHERE `checklist_type` = '".$input['checklist_type']."' 
				AND `plugin_resources_resources_id` = '".$input['plugin_resources_resources_id']."' ";

            switch ($input['action']) {
               case "up" :
                  $sql2 .= " AND `rank` < '$current_rank'
                           ORDER BY `rank` DESC
                           LIMIT 1";
                  break;

               case "down" :
                  $sql2 .= " AND `rank` > '$current_rank'
                           ORDER BY `rank` ASC
                           LIMIT 1";
                  break;

               default :
                  return false;
            }
            
            if ($result2 = $DB->query($sql2)) {
               if ($DB->numrows($result2)==1) {
                  
                  list($other_ID,$new_rank) = $DB->fetch_array($result2);

                  return ($this->update(array('id'      => $input['id'],
                                              'rank' => $new_rank))
                          && $this->update(array('id'      => $other_ID,
                                                 'rank' => $current_rank)));
               }
            }
         }
         return false;
      }
   }
	
	function showForm ($ID, $options=array()) {
		
		if (!$this->canView()) return false;
      
      $plugin_resources_contracttypes_id = -1;
      if (isset($options['plugin_resources_contracttypes_id'])) {
         $plugin_resources_contracttypes_id = $options['plugin_resources_contracttypes_id'];
      }
      
      $checklist_type = -1;
      if (isset($options['checklist_type'])) {
         $checklist_type = $options['checklist_type'];
      }
      
      $plugin_resources_resources_id = -1;

      if (isset($options['plugin_resources_resources_id'])) {
         $plugin_resources_resources_id = $options['plugin_resources_resources_id'];
         $item = new PluginResourcesResource();
         if ($item->getFromDB($plugin_resources_resources_id))
            $options["entities_id"] = $item->fields["entities_id"];
      }

		if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w',$input);
      }

      if (!strpos($_SERVER['PHP_SELF'],"viewchecklisttask"))
         $this->showTabs($options);
      $this->showFormHeader($options);
      
      echo "<input type='hidden' name='plugin_resources_resources_id' value='".$plugin_resources_resources_id."'>";

      if ($ID>0) {
         echo "<input type='hidden' name='plugin_resources_contracttypes_id' value='".$this->fields["plugin_resources_contracttypes_id"]."'>";
         echo "<input type='hidden' name='checklist_type' value='".$this->fields["checklist_type"]."'>";
      } else {
         echo "<input type='hidden' name='plugin_resources_contracttypes_id' value='$plugin_resources_contracttypes_id'>";
         echo "<input type='hidden' name='checklist_type' value='$checklist_type'>";
      }
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td >".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name",array('size' => "40"));	
      echo "</td>";
      
      echo "<td>";
      _e('Important', 'resources');
      echo "</td><td>";
      Dropdown::showYesNo("tag",$this->fields["tag"]);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td >".__('Link', 'resources')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"address",array('size' => "75"));
      echo "</td>";
      
      echo "<td></td>";
      echo "<td></td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td class='left' colspan = '4'>";
      echo __('Description')."<br>";
      echo "<textarea cols='150' rows='6' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td>";
      
      echo "</tr>";

      $options['candel'] = false;
      $this->showFormButtons($options);
      return true;

	}
	
	//show from resources
	static function showFromResources($plugin_resources_resources_id,$checklist_type,$withtemplate='') {
		global $CFG_GLPI;
		
		if (!plugin_resources_haveRight('checklist', 'r')) return false;
		
		$target = "./resource.form.php";
		$targetchecklist = "./checklist.form.php";
		$targettask = "./task.form.php";
      $resource=new PluginResourcesResource();
      $resource->getFromDB($plugin_resources_resources_id);
      $canedit = $resource->can($plugin_resources_resources_id, 'w');
      
      $entities_id=$resource->fields["entities_id"];
      $plugin_resources_contracttypes_id=$resource->fields["plugin_resources_contracttypes_id"];

		$rand=mt_rand();
		
		$restrict = "`entities_id` = '".$entities_id."' 
                  AND `plugin_resources_resources_id` = '$plugin_resources_resources_id' 
                  AND `checklist_type` = '$checklist_type' 
                  ORDER BY `rank`";

      $checklists = getAllDatasFromTable("glpi_plugin_resources_checklists",$restrict);
		$numrows = countElementsInTable("glpi_plugin_resources_checklists",$restrict);
		
		if (!empty($checklists)) {
         
         $values = array();
         $values["checklist_type"] = $checklist_type;
         $values["plugin_resources_resources_id"] = $plugin_resources_resources_id;
            
         $isfinished = self::checkifChecklistFinished($values);
         
         if (!$isfinished) {
            echo "<ul><li><div align='left' id='menu_navigate'>";
            if ($checklist_type==self::RESOURCES_CHECKLIST_IN) {
               echo "<a href=\"javascript:showHideDiv('checklist_view_in_mode',
                  'checklistimg$rand','".$CFG_GLPI["root_doc"]."/pics/deplier_down.png',
                  '".$CFG_GLPI["root_doc"]."/pics/deplier_up.png');\">";
            } else {
               echo "<a href=\"javascript:showHideDiv('checklist_view_out_mode',
                  'checklistimg$rand','".$CFG_GLPI["root_doc"]."/pics/deplier_down.png',
                  '".$CFG_GLPI["root_doc"]."/pics/deplier_up.png');\">";
            }	
            echo "<img name='checklistimg$rand' src=\"".$CFG_GLPI["root_doc"]."/pics/deplier_up.png\">";
            echo "</a>";	
            echo "</li></ul></div>";
               
            echo "<div align='center' ";
            if ($checklist_type==self::RESOURCES_CHECKLIST_IN) {
               echo "id='checklist_view_in_mode'>";
            } else if ($checklist_type==self::RESOURCES_CHECKLIST_OUT) {
               echo "id='checklist_view_out_mode'>";
            }
         }
         echo "<form method='post' name='massiveaction_form$checklist_type.$rand' id='massiveaction_form$checklist_type.$rand' action='".$target."'>";

         echo "<table width='950' class='tab_cadre_fixe' cellpadding='2'>";
         echo "<tr><th colspan='7'>".self::getChecklistType($checklist_type)."</th></tr>";

         if (!$isfinished) {
         
            echo "<tr>";
            echo "<th>&nbsp;</th>";
            echo "<th>".__('Name')."</th>";
            echo "<th>".__('Important', 'resources')."</th>";
            if (plugin_resources_haveRight("task","w") && $canedit) {
               echo "<th>".__('Linked task', 'resources')."</th>";
            }
            echo "<th>".__('State')."</th>";
            echo "<th>&nbsp;</th>";
            echo "<th>&nbsp;</th>";
            echo "</tr>";
            
            Session::initNavigateListItems("PluginResourcesChecklist",
                        PluginResourcesResource::getTypeName(1)." = ".$resource->fields['name']);
            
            $i=0;
            
            foreach ($checklists as $checklist) {
               
               $ID=$checklist["id"];

               Session::addToNavigateListItems("PluginResourcesChecklist",$ID);
               
               echo "<tr class='tab_bg_1'>";
               echo "<td width='10'>";
               echo "<input type='checkbox' name='item[$ID]' ";
               $is_finished=0;
               $PluginResourcesTask = new PluginResourcesTask();
               if($PluginResourcesTask->GetfromDB($checklist["plugin_resources_tasks_id"]))
                  if ($PluginResourcesTask->fields["is_finished"]==1)
                     $is_finished=1;

               if($checklist["plugin_resources_tasks_id"] && $is_finished==0)
                  echo " disabled='true' ";
               echo " value='1'>";
               echo "</td>";
               
               echo "<td width='30%'>";
               echo "<a href='".$targetchecklist."?id=".$ID."&amp;plugin_resources_resources_id=".
               $plugin_resources_resources_id."&amp;plugin_resources_contracttypes_id=".
               $plugin_resources_contracttypes_id."&amp;checklist_type=".$checklist_type."' >";	
               echo $checklist["name"];
               echo "</a>&nbsp;";
               
               if (!empty($checklist["address"])) {
                  echo "&nbsp;";
                  $link = str_replace("&","&amp;",$checklist["address"]);
                  Html::showToolTip($checklist["address"],array('link'=>$link,'linktarget'=>'_blank'));
               }
               echo "</td>";
               
               echo "<td>";
               if ($checklist["tag"]) {
                  echo "<span class='plugin_resources_date_over_color'>";
               }
               echo nl2br($checklist["comment"]);
               if ($checklist["tag"]) {
                  echo "</span>";
               }
               echo "</td>";
               
               if (plugin_resources_haveRight("task","w") && $canedit) {
                  echo "<td class='center'>";
                  if(!empty($checklist["plugin_resources_tasks_id"]))
                     echo "<a href='".$targettask."?id=".$checklist["plugin_resources_tasks_id"].
                     "&amp;plugin_resources_resources_id=".$plugin_resources_resources_id."&amp;central=1'>";
                  echo Dropdown::getYesNo($checklist["plugin_resources_tasks_id"]);
                  if(!empty($checklist["plugin_resources_tasks_id"]))
                     echo "</a>";
                  echo "</td>";
               }
               echo "<td class='center'>";
               
               echo "<input type='checkbox' disabled='true' name='is_checked' ";
               if ($checklist["is_checked"]) echo "checked";
               echo " >";
               if ($checklist["is_checked"]>0)
                  echo "<input type='hidden' value='0' name='is_checked$ID'>";
               else
                  echo "<input type='hidden' value='1' name='is_checked$ID'>";
               echo "</td>";
               
               if ($i!=0 && plugin_resources_haveRight('checklist', 'w') && $canedit) {
               
                  echo "<td>";
                  Html::showSimpleForm($target,
                                       'move',
                                       __('Bring up'),
                                       array('action' => 'up',
                                             'id' => $ID,
                                             'plugin_resources_resources_id' => $plugin_resources_resources_id,
                                             'checklist_type' => $checklist_type),
                                        $CFG_GLPI["root_doc"]."/pics/deplier_up.png");
                                       
                     
                  echo "</td>";
               } else echo "<td>&nbsp;</td>";
               
               if ($i!=$numrows-1 && plugin_resources_haveRight('checklist', 'w') && $canedit) {
                  
                  echo "<td>";
                  Html::showSimpleForm($target,
                                       'move',
                                        __('Bring down'),
                                       array('action' => 'down',
                                             'id' => $ID,
                                             'plugin_resources_resources_id' => $plugin_resources_resources_id,
                                             'checklist_type' => $checklist_type),
                                        $CFG_GLPI["root_doc"]."/pics/deplier_down.png");
                                        
                  echo "</td>";

               } else echo "<td>&nbsp;</td>";
                  
               
               echo "<input type='hidden' name='plugin_resources_resources_id' value='$plugin_resources_resources_id'>";
               echo "<input type='hidden' name='checklist_type' value='$checklist_type'>";
               echo "<input type='hidden' name='plugin_resources_contracttypes_id' value='$plugin_resources_contracttypes_id'>";
               echo "<input type='hidden' value='".$checklist["comment"]."' name='comment'>";
                     
               echo "</tr>";
               
               $i++;
            }
         }
         if (plugin_resources_haveRight('checklist', 'w') && $canedit) {
            
            $values = array();
            $values["checklist_type"] = $checklist_type;
            $values["plugin_resources_resources_id"] = $plugin_resources_resources_id;
            $values["plugin_resources_contracttypes_id"] = $plugin_resources_contracttypes_id;
            $values["entities_id"] = $entities_id;
            $isfinished = self::checkifChecklistFinished($values);
      
            echo "<tr class='tab_bg_2'>";
            echo "<td colspan='7' class='left'>";
            if (!$isfinished) {
               echo "<a onclick= \"if ( plugin_resources_markCheckboxes('massiveaction_form$checklist_type.$rand') ) return false;\" href='#'>".__('Select all')."</a>";
               echo " - <a onclick= \"if ( plugin_resources_unMarkCheckboxes('massiveaction_form$checklist_type.$rand') ) return false;\" href='#'>".__('Deselect all')."</a> ";
            }
            self::dropdownChecklistActions($values);
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";
         Html::closeForm();
		}
		
		if (plugin_resources_haveRight('checklist', 'w') && $canedit) {
         $rand = mt_rand();

         echo "<div id='viewchecklisttask". "$rand'></div>\n";
         echo "<script type='text/javascript' >\n";
         echo "function viewAddChecklistTask". "$rand(){\n";
         $params = array ('type'       => __CLASS__,
                           'target' => $targetchecklist,
                           'plugin_resources_contracttypes_id' => $plugin_resources_contracttypes_id,
                           'plugin_resources_resources_id' => $plugin_resources_resources_id,
                           'checklist_type' => $checklist_type,
                           'id'         => -1);
         Ajax::updateItemJsCode("viewchecklisttask". "$rand",
                              $CFG_GLPI["root_doc"]."/plugins/resources/ajax/viewchecklisttask.php", $params, false);
         echo "};";
         echo "</script>\n";
         echo "<p align='center' ><a href='javascript:viewAddChecklistTask". "$rand();'>";
         if ($checklist_type==self::RESOURCES_CHECKLIST_IN) {
				_e('Add a task at the arriving checklist', 'resources');
			} else if ($checklist_type==self::RESOURCES_CHECKLIST_OUT) {
				_e('Add a task at the leaving checklist', 'resources');
         }
         echo "</a></p><br>\n";
      }
		
		echo "</div>";
	}
	
	static function dropdownChecklistActions($values) {
      global $CFG_GLPI;
      
      $rand = mt_rand();

      echo "<select name='checklistActions$rand' id='checklistActions$rand'>";
      echo "<option value='0' selected>".Dropdown::EMPTY_VALUE."</option>";
      $isfinished = self::checkifChecklistFinished($values);
      if (!$isfinished) {
         echo "<option value='update_checklist'>".__('Modify state', 'resources')."</option>";
         echo "<option value='delete_checklist'>".__('Delete permanently')."</option>";
         
         if (plugin_resources_haveRight("task","w")) {
            echo "<option value='add_task'>".__('Link a task', 'resources')."</option>";
         }
         if (Session::haveRight("show_all_ticket","1")) {
            echo "<option value='add_ticket'>".__('Add ticket', 'resources')."</option>";
         }
      }
      if ($isfinished) {
         echo "<option value='close_checklist'>".__('Create a end treatment ticket', 'resources')."</option>";
         echo "<option value='open_checklist'>".__('Reset the checklist', 'resources')."</option>";
      }
      echo "</select>";
      $params = array (
         'action' => '__VALUE__',
         'checklist_type' => $values["checklist_type"],
         'plugin_resources_resources_id'=>$values["plugin_resources_resources_id"],
         'plugin_resources_contracttypes_id'=>$values["plugin_resources_contracttypes_id"],
         'entities_id' => $values["entities_id"],
      );
      Ajax::updateItemOnSelectEvent("checklistActions$rand", "show_checklistActions$rand", $CFG_GLPI["root_doc"] . "/plugins/resources/ajax/checklistactions.php", $params);
      echo "<span id='show_checklistActions$rand'>&nbsp;</span>";
   }

   function showOnCentral($is_leaving) {
      global $DB,$CFG_GLPI;
      
      if ($this->canView()) {
      
         if (Session::isMultiEntitiesMode()) {
            $colsup=1;
         } else {
            $colsup=0;
         }
         
         if ($is_leaving)
            $query =self::queryChecklists(true,1);
         else
            $query =self::queryChecklists(true);
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         
         if ($number > 0) {
            
            echo "<div align='center'><table class='tab_cadre' width='100%'>";
            if ($is_leaving) {
               $title=__('Leaving resource - checklist needs to verificated', 'resources');
            } else {
               $title=__('New resource - checklist needs to verificated', 'resources');
            }
            echo "<tr><th colspan='".(5+$colsup)."'>".$title." </th></tr>";
            echo "<tr><th>".PluginResourcesResource::getTypeName(1)."</th>";
            if ($is_leaving) {
               echo "<th>".__('Departure date', 'resources')."</th>";
            } else {
               echo "<th>".__('Arrival date', 'resources')."</th>";
            }
            if (Session::isMultiEntitiesMode())
               echo "<th>".__('Entity')."</th>";
            echo "<th>".__('Location')."</th>";
            echo "<th>".PluginResourcesContractType::getTypeName(1)."</th>";
            echo "<th>".__('Checklist needs to verificated', 'resources')."</th></tr>";
         
            while ($data=$DB->fetch_array($result)) {
               
               echo "<tr class='tab_bg_1'>";
               
               echo "<td class='center'>";
               echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php?id=".$data["plugin_resources_resources_id"]."'>";
               echo $data["resource_name"]." ".$data["resource_firstname"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["plugin_resources_resources_id"].")";
               echo "</a></td>";
               
               echo "<td class='center'>";
               if ($is_leaving) {
                   if ($data["date_end"] <= date('Y-m-d') && !empty($data["date_end"])) {
                     echo "<div class='deleted'>".Html::convDate($data["date_end"])."</div>";
                  } else {
                     echo "<div class='plugin_resources_date_day_color'>";
                     echo Html::convDate($data["date_end"]);
                     echo "</div>";
                  }
               } else {
                  if ($data["date_begin"] <= date('Y-m-d') && !empty($data["date_begin"])) {
                     echo "<div class='deleted'>".Html::convDate($data["date_begin"])."</div>";
                  } else {
                     echo "<div class='plugin_resources_date_day_color'>";
                     echo Html::convDate($data["date_begin"]);
                     echo "</div>";
                  }
               }
               echo "</td>";
               
               if (Session::isMultiEntitiesMode()) {
                  echo "<td class='center'>";
                  echo Dropdown::getDropdownName("glpi_entities",$data['entities_id']);
                  echo "</td>";
               }
               echo "<td class='center'>";
               echo Dropdown::getDropdownName("glpi_locations",$data['locations_id']);
               echo "</td>";
               
               echo "<td class='center'>";
               echo Dropdown::getDropdownName("glpi_plugin_resources_contracttypes",
                                             $data['plugin_resources_contracttypes_id']);
               echo "</td>";
               
               echo "<td width='40%'>";
               if ($is_leaving)
                  $query_checklists =  self::queryListChecklists($data["plugin_resources_resources_id"],self::RESOURCES_CHECKLIST_OUT);
               else
                  $query_checklists = self::queryListChecklists($data["plugin_resources_resources_id"],self::RESOURCES_CHECKLIST_IN);
               $result_checklists = $DB->query($query_checklists);
               
               echo "<table class='tab_cadre' width='100%'>";
               while ($data_checklists=$DB->fetch_array($result_checklists)) {
                  echo "<tr class='tab_bg_1'><td>";
                  if ($data_checklists["tag"]) {
                     echo "<span class='plugin_resources_date_over_color'>";
                  }
                  echo $data_checklists["name"];
                  if ($_SESSION["glpiis_ids_visible"]) echo " (".$data_checklists["id"].")";
                  if ($data_checklists["tag"]) {
                     echo "</span>";
                  }
                  echo "</td>";	
                  echo "</tr>";
               }
               echo "</table>";
               echo "</td></tr>";
            }
            echo "</table></div>";
         }
      }
   }
   
   // Cron action
   static function cronInfo($name) {
       
      switch ($name) {
         case 'ResourcesChecklist':
            return array (
               'description' => __('Checklists Verification', 'resources'));   // Optional
            break;
      }
      return array();
   }
   
   static function queryChecklists($entity_restrict,$is_leaving=0) {
      
      $resource = new PluginResourcesResource();
      
      if ($is_leaving > 0) {
         $field = "date_end";
         $checklist_type = self::RESOURCES_CHECKLIST_OUT;
      } else {
         $field = "date_begin";
         $checklist_type = self::RESOURCES_CHECKLIST_IN;
      }
      $query = "SELECT `glpi_plugin_resources_checklists`.*,
                     `glpi_plugin_resources_resources`.`id` AS plugin_resources_resources_id,
                      `glpi_plugin_resources_resources`.`name` AS resource_name,
                       `glpi_plugin_resources_resources`.`firstname` AS resource_firstname,
                        `glpi_plugin_resources_resources`.`entities_id`,
                         `glpi_plugin_resources_resources`.`date_begin`,
                        `glpi_plugin_resources_resources`.`locations_id`,
                        `glpi_plugin_resources_resources`.`plugin_resources_departments_id`,
                        `glpi_plugin_resources_resources`.`plugin_resources_resourcestates_id`,
                        `glpi_plugin_resources_resources`.`users_id`,
                        `glpi_plugin_resources_resources`.`users_id_recipient`,
                        `glpi_plugin_resources_resources`.`date_declaration`,
                        `glpi_plugin_resources_resources`.`date_begin`,
                        `glpi_plugin_resources_resources`.`date_end`,
                        `glpi_plugin_resources_resources`.`users_id_recipient_leaving`,
                        `glpi_plugin_resources_resources`.`is_leaving`,
                        `glpi_plugin_resources_resources`.`is_helpdesk_visible`,
                        `glpi_plugin_resources_resources`.`plugin_resources_contracttypes_id` ";
      $query.= " FROM `glpi_plugin_resources_checklists`,`glpi_plugin_resources_resources` ";
      $query.= " WHERE `glpi_plugin_resources_resources`.`is_template` = '0' 
                  AND `glpi_plugin_resources_resources`.`is_leaving` = '".$is_leaving."' 
                  AND `glpi_plugin_resources_resources`.`is_deleted` = '0' 
                  AND `glpi_plugin_resources_checklists`.`checklist_type` = '".$checklist_type."' 
                  AND `glpi_plugin_resources_checklists`.`is_checked` = '0' 
                  AND `glpi_plugin_resources_checklists`.`plugin_resources_resources_id` = `glpi_plugin_resources_resources`.`id` ";
      
      if ($entity_restrict && $resource->isEntityAssign()) {
         $LINK= " AND " ;
         $query.=getEntitiesRestrictRequest($LINK,"glpi_plugin_resources_resources");
      }
         
      $query .= " GROUP BY `glpi_plugin_resources_resources`.`id`  ORDER BY `glpi_plugin_resources_resources`.`".$field."`";
      
      return $query;
   }
   
   static function queryListChecklists($ID,$checklist_type) {

      $query = "SELECT `glpi_plugin_resources_checklists`.*  ";
      $query.= " FROM `glpi_plugin_resources_checklists`,`glpi_plugin_resources_resources` ";
      $query.= " WHERE `glpi_plugin_resources_resources`.`id` = '".$ID."' 
                        AND `glpi_plugin_resources_checklists`.`checklist_type` = '".$checklist_type."' 
                        AND `glpi_plugin_resources_checklists`.`is_checked` = '0' 
                        AND `glpi_plugin_resources_checklists`.`plugin_resources_resources_id` = `glpi_plugin_resources_resources`.`id` ";	
      $query .= "  ORDER BY `glpi_plugin_resources_checklists`.`rank` ASC;";
      
      return $query;
   }
   /**
    * Cron action on checklists
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronResourcesChecklist($task=NULL) {
      global $DB,$CFG_GLPI;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message=array();
      $cron_status = 0;
      $query_arrival = self::queryChecklists(false);
      $query_leaving = self::queryChecklists(false,1);
      
      $querys = array(Alert::NOTICE=>$query_arrival, Alert::END=>$query_leaving);
      
      $checklist_infos = array();
      $checklist_messages = array();

      foreach ($querys as $type => $query) {
         $checklist_infos[$type] = array();
         foreach ($DB->request($query) as $data) {
            $entity = $data['entities_id'];
            $message = "checklists".": ".$data["resource_name"]." ".$data["resource_firstname"]."<br>\n";
            $checklist_infos[$type][$entity][] = $data;

            if (!isset($checklists_infos[$type][$entity])) {
               $checklist_messages[$type][$entity] = __('Checklists Verification', 'resources')."<br />";
            }
            $checklist_messages[$type][$entity] .= $message;
         }
      }
      
      foreach ($querys as $type => $query) {
      
         foreach ($checklist_infos[$type] as $entity => $checklists) {
            Plugin::loadLang('resources');

            if (NotificationEvent::raiseEvent(($type==Alert::NOTICE?"AlertArrivalChecklists":"AlertLeavingChecklists"),
                                              new PluginResourcesResource(),
                                              array('entities_id'=>$entity,
                                                    'checklists'=>$checklists,'tasklists'=>$checklists))) {
               $message = $checklist_messages[$type][$entity];
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
                             ":  Send checklists resources alert failed\n");
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                          ":  Send checklists resources alert failed",false,ERROR);
               }
            }
         }
      }
      
      return $cron_status;
   }
   
   
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType()=='PluginResourcesResource') {
         self::pdfForResource($pdf, $item, self::RESOURCES_CHECKLIST_IN);
         self::pdfForResource($pdf, $item, self::RESOURCES_CHECKLIST_OUT);
      } else {
         return false;
      }
      return true;
   }
   
   /**
    * Show for PDF an resources : checklists informations
    * 
    * @param $pdf object for the output
    * @param $ID of the resources
    */
   static function pdfForResource(PluginPdfSimplePDF $pdf, PluginResourcesResource $appli, $checklist_type) {
      global $DB;
      
      $ID = $appli->fields['id'];
      
      if (!$appli->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_resources_haveRight("resources","r")) {
         return false;
      }
      
      $query = "SELECT * 
               FROM `glpi_plugin_resources_checklists` 
               WHERE `plugin_resources_resources_id` = '$ID' 
               AND `checklist_type` = '$checklist_type' 
               ORDER BY `rank` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      $i=$j=0;
      
      $pdf->setColumnsSize(100);
      if($number>0) {
         $pdf->displayTitle('<b>'.self::getChecklistType($checklist_type).'</b>');
         $pdf->setColumnsSize(85,10,5);
         $pdf->displayTitle('<b><i>'.
            __('Name'),
            __('Linked task', 'resources'),
            __('Checked', 'resources').'</i></b>'
            );
      
         $i++;
         
         while ($j < $number) {

            $checkedID=$DB->result($result, $j, "is_checked");
            $name=$DB->result($result, $j, "name");
            $task_id=$DB->result($result, $j, "plugin_resources_tasks_id");

         if ($checkedID==1)
            $checked=__('Yes');
         else
            $checked=__('No');
            $pdf->displayLine(
               $name,
               Dropdown::getYesNo($task_id),
               $checked
            );			
            $j++;
         }
      } else {
         $pdf->displayLine(__('No checklist found', 'resources'));
      }	
      
      $pdf->displaySpace();
   }
}

?>