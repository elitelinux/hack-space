<?php
/*
 * @version $Id: task.class.php 480 2012-11-09 tsmr $
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

class PluginResourcesTask extends CommonDBTM {
   
   public $itemtype = 'PluginResourcesResource';
   public $items_id = 'plugin_resources_resources_id';
   public $dohistory=true;
   
   static function getTypeName($nb = 0) {

      return _n('Task', 'Tasks', $nb);
   }
   
   static function canCreate() {
      return plugin_resources_haveRight('task', 'w');
   }

   static function canView() {
      return plugin_resources_haveRight('task', 'r');
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
   
   function cleanDBonPurge() {
      
      $temp = new PluginResourcesTask_Item();
      $temp->deleteByCriteria(array('plugin_resources_tasks_id' => $this->fields['id']));
      
      $temp = new PluginResourcesTaskPlanning();
      $temp->deleteByCriteria(array('plugin_resources_tasks_id' => $this->fields['id']));
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
      
      if (!isset($input['plugin_resources_resources_id']) 
            || $input['plugin_resources_resources_id'] <= 0) {
         return false;
      }

      return $input;
   }
   
   function post_addItem() {
      global $CFG_GLPI;
      
      if (isset($this->input["_plan"])) {
         $this->input["_plan"]['plugin_resources_tasks_id'] = $this->fields['id'];
         $pt = new PluginResourcesTaskPlanning();

         if (!$pt->add($this->input["_plan"])) {
            return false;
         }
      }
      
      $PluginResourcesResource = new PluginResourcesResource();
      if ($CFG_GLPI["use_mailing"]) {
         $options = array('tasks_id' => $this->fields["id"]);
         if ($PluginResourcesResource->getFromDB($this->fields["plugin_resources_resources_id"])) {
            NotificationEvent::raiseEvent("newtask",$PluginResourcesResource,$options);  
         }
      }
   }
   
   function prepareInputForUpdate($input) {
      
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
      
      $this->getFromDB($input["id"]);
      $input["_old_name"]=$this->fields["name"];
      $input["_old_plugin_resources_tasktypes_id"]=$this->fields["plugin_resources_tasktypes_id"];
      $input["_old_users_id"]=$this->fields["users_id"];
      $input["_old_groups_id"]=$this->fields["groups_id"];
      $input["_old_actiontime"]=$this->fields["actiontime"];
      $input["_old_is_finished"]=$this->fields["is_finished"];
      $input["_old_comment"]=$this->fields["comment"];

      return $input;
   }
   
   function post_updateItem($history=1) {
      global $CFG_GLPI;
      
      if (isset($this->input["_plan"])) {
         $pt = new PluginResourcesTaskPlanning();
         // Update case
         if (isset($this->input["_plan"]["id"])) {
            $this->input["_plan"]['plugin_resources_tasks_id'] = $this->input["id"];

            if (!$pt->update($this->input["_plan"])) {
               return false;
            }
            unset($this->input["_plan"]);
         // Add case
         } else {
            $this->input["_plan"]['plugin_resources_tasks_id'] = $this->input["id"];
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
            $PluginResourcesResource = new PluginResourcesResource();
            if ($PluginResourcesResource->getFromDB($this->fields["plugin_resources_resources_id"])) {
               NotificationEvent::raiseEvent("updatetask",$PluginResourcesResource,$options);  
            }
         }
      }
   }
   
   function pre_deleteItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["use_mailing"] 
            && isset($this->input['delete'])) {
         $PluginResourcesResource = new PluginResourcesResource();
         $options = array('tasks_id' => $this->fields["id"]);
         if ($PluginResourcesResource->getFromDB($this->fields["plugin_resources_resources_id"])) {
            NotificationEvent::raiseEvent("deletetask",$PluginResourcesResource,$options);  
         }
      }
      return true;
   }
   
   function cleanDBonMarkDeleted() {
      global $DB;
      
      $query = "UPDATE `glpi_plugin_resources_checklists` 
            SET `plugin_resources_tasks_id` = 0 
            WHERE `plugin_resources_tasks_id` = '".$this->fields["id"]."' ";
      $result = $DB->query($query);

   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='PluginResourcesResource' 
            && $this->canView()) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2), self::countForItem($item));
         }
         return self::getTypeName(2);
      } else if ($item->getType()=='Central' 
                     && $this->canView()) {
         return PluginResourcesResource::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      $self = new self();
      if ($item->getType()=='PluginResourcesResource') {
         if (plugin_resources_haveRight('task', 'r')) {
               self::addNewTasks($item, $withtemplate);
               self::showMinimalList(array('id' => $item->getID(),
                                            'withtemplate' => $withtemplate));
         }
      } else if ($item->getType()=='Central') {
         $self->showCentral(Session::getLoginUserID());
      }
      return true;
   }
   
   static function countForItem(CommonDBTM $item) {

      $restrict = "`plugin_resources_resources_id` = '".$item->getField('id')."'
                  AND is_finished != 1 AND is_deleted = 0";
      $nb = countElementsInTable(array('glpi_plugin_resources_tasks'), $restrict);

      return $nb ;
   }
   
   function getSearchOptions() {

      $tab = array();
    
      $tab['common']             = PluginResourcesResource::getTypeName(2)." - ".self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();

      $tab[2]['table']           = 'glpi_users';
      $tab[2]['field']           = 'name';
      $tab[2]['name']            = __('Technician');
      $tab[2]['datatype']        = 'dropdown';
      $tab[2]['massiveaction']   = false;

      $tab[3]['table']           = 'glpi_groups';
      $tab[3]['field']           = 'completename';
      $tab[3]['name']            = __('Group');
      $tab[3]['condition']       = '`is_assign`';
      $tab[3]['massiveaction']   = false;
      $tab[2]['datatype']        = 'dropdown';
      
      $tab[4]['table']           ='glpi_plugin_resources_taskplannings';
      $tab[4]['field']           = 'id';
      $tab[4]['name']            = __('Planning');
      $tab[4]['massiveaction']   = false;
      $tab[4]['datatype']        = 'number';
      
      $tab[7]['table']           = $this->getTable();
      $tab[7]['field']           = 'actiontime';
      $tab[7]['name']            = __('Effective duration', 'resources');
      $tab[7]['datatype']        = 'timestamp';
      $tab[7]['massiveaction']   = false;
      $tab[7]['nosearch']        = true;
      
      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'comment';
      $tab[8]['name']            = __('Comments');
      $tab[8]['datatype']        = 'text';

      $tab[9]['table']           = $this->getTable();
      $tab[9]['field']           = 'is_finished';
      $tab[9]['name']            = __('Carried out task', 'resources');
      $tab[9]['datatype']        = 'bool';
      
      $tab[10]['table']          = 'glpi_plugin_resources_tasks_items';
      $tab[10]['field']          = 'items_id';
      $tab[10]['name']           = _n('Associated item', 'Associated items', 2);
      $tab[10]['massiveaction']  = false;
      $tab[10]['forcegroupby']   = true;
      $tab[10]['joinparams']     = array('jointype' => 'child');
      
      $tab[11]['table']          = 'glpi_plugin_resources_tasktypes';
      $tab[11]['field']          = 'name';
      $tab[11]['name']           = PluginResourcesTaskType::getTypeName(1);
      $tab[11]['datatype']       = 'dropdown';
      
      $tab[13]['table']          = 'glpi_plugin_resources_resources';
      $tab[13]['field']          = 'id';
      $tab[13]['name']           = PluginResourcesResource::getTypeName(1)." ".__('ID');
      $tab[13]['massiveaction']  = false;
      $tab[13]['datatype']       = 'number';
      
      $tab[12]['table']          = 'glpi_plugin_resources_resources';
      $tab[12]['field']          = 'name';
      $tab[12]['name']           = PluginResourcesResource::getTypeName(2);
      $tab[12]['massiveaction']  = false;
      
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
   
   
   function defineTabs($options=array()) {
      
      $ong = array();
      
      $this->addStandardTab('PluginResourcesTask_Item', $ong,$options);
      $this->addStandardTab('Log',$ong,$options);
      
      return $ong;
   }
   
   /**
    * Duplicate task of resources from an item template to its clone
    *
    * @since version 0.84
    *
    * @param $itemtype     itemtype of the item
    * @param $oldid        ID of the item to clone
    * @param $newid        ID of the item cloned
    * @param $newitemtype  itemtype of the new item (= $itemtype if empty) (default '')
   **/
   static function cloneItem($oldid, $newid) {

      $task_item = new PluginResourcesTask_Item();
      
      $restrict = "`plugin_resources_resources_id` = '".$oldid."'
                  AND is_deleted != 1";
      $ptasks = getAllDatasFromTable("glpi_plugin_resources_tasks",$restrict);
      if (!empty($ptasks)) {
         foreach ($ptasks as $ptask) {
            $item = new self();
            $values=$ptask;
            $taskid = $values["id"];
            unset($values["id"]);
            $values["plugin_resources_resources_id"]=$newid;
            $values["name"] = addslashes($ptask["name"]);
            $values["comment"] = addslashes($ptask["comment"]);
            
            $newtid = $item->add($values);
            
            $restrictitems = "`plugin_resources_tasks_id` = '".$taskid."'";
            $tasksitems = getAllDatasFromTable("glpi_plugin_resources_tasks_items",$restrictitems);
            if (!empty($tasksitems)) {
               foreach ($tasksitems as $tasksitem) {
                  $task_item->add(array('plugin_resources_tasks_id' => $newtid,
                     'itemtype' => $tasksitem["itemtype"],
                     'items_id' => $tasksitem["items_id"]));
               }
            }
         }
      }
   }
   
   static function addNewTasks(CommonDBTM $item, $withtemplate='') {
      global $CFG_GLPI;
      
      $rand=mt_rand();
      
      $ID = $item->getField('id');
      $entities_id = $item->getField('entities_id');
      $canedit = $item->can($ID, 'w');
      if (plugin_resources_haveRight('task', 'w') 
            && $canedit 
               && $withtemplate<2) {
         
         echo "<div align='center'>";
         echo "<a href='".
         $CFG_GLPI["root_doc"]."/plugins/resources/front/task.form.php?plugin_resources_resources_id=".$ID
         ."&entities_id=".$entities_id."' >".__('Add a new task')."</a></div>";
         echo "</div>";
      }
   }
   
   function showForm ($ID, $options=array()) {

      if (!$this->canView()) return false;
      
      $plugin_resources_resources_id = -1;
      if (isset($options['plugin_resources_resources_id'])) {
         $plugin_resources_resources_id = $options['plugin_resources_resources_id'];
      }
      
      $item = new PluginResourcesResource();
      if ($item->getFromDB($plugin_resources_resources_id)){
         $entities_id = $item->fields["entities_id"];
      }
      
      if ($ID > 0) {
         $this->check($ID,'r');
         $plugin_resources_resources_id=$this->fields["plugin_resources_resources_id"];
      } else {
         // Create item
         $input=array('plugin_resources_resources_id'=>$plugin_resources_resources_id,
                        'entities_id' => $entities_id);
         $this->check(-1,'w',$input);
      }
      
      $this->showTabs($options);
      $this->showFormHeader($options);
      
      echo "<input type='hidden' name='plugin_resources_resources_id' value='$plugin_resources_resources_id'>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".PluginResourcesResource::getTypeName(2)."&nbsp;</td><td>";

      $user = PluginResourcesResource::getResourceName($plugin_resources_resources_id,2);
      $out= "<a href='".$user['link']."'>";
      $out.= $user["name"];
      if ($_SESSION["glpiis_ids_visible"]) $out.= " (".$plugin_resources_resources_id.")";
      $out.= "</a>";
      echo $out;
      echo "</td>";
      echo "<td colspan='2'>";
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name",array('size' => "50"));
      echo "</td>";
      echo "<td>".PluginResourcesTaskType::getTypeName(1)."</td><td>";
      Dropdown::show('PluginResourcesTaskType',
                     array('value'  => $this->fields["plugin_resources_tasktypes_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Technician')."</td><td>";
      User::dropdown(array('name' => "users_id",
                           'value' => $this->fields["users_id"],
                           'right' => 'interface'));
      echo "</td>";
      echo "<td>".__('Planning')."</td>";
      echo "<td>";
      $plan = new PluginResourcesTaskPlanning();
      $plan->showFormForTask($plugin_resources_resources_id, $this);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group')."</td><td>";
      Dropdown::show('Group',
                     array('value'  => $this->fields["groups_id"]));
      echo "</td>";
      echo "<td>".__('Carried out task', 'resources')."</td><td>";
      Dropdown::showYesNo("is_finished",$this->fields["is_finished"]);
      echo "</td>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Effective duration', 'resources')."</td><td>";
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

      echo "</td><td colspan='2'></td></tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='4'>".__('Comments')."</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'><td colspan='4'>";			
      echo "<textarea cols='130' rows='4' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "<input type='hidden' name='withtemplate' value=\"".$options['withtemplate']."\" >";
      echo "</td></tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();
      
      return true;
   }
   
   /**
    * get the task status list
    *
    * @param $withmetaforsearch boolean
    * @return an array
    */
   static function getAllStatusArray() {

      $tab = array('1'      => __('Yes'),
                   '0'   => __('No'));

      return $tab;
   }
   
   /**
    * Get task status Name
    *
    * @param $value status ID
    */
   static function getStatus($value) {

      $tab = self::getAllStatusArray();
      return (isset($tab[$value]) ? $tab[$value] : '');
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
      $p['id']  = 0;
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
      
      $PluginResourcesResource = new PluginResourcesResource();
      $PluginResourcesResource->getFromDB($p['id']);
      $canedit = $PluginResourcesResource->can($p['id'], 'w');
      
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
      
      $query.= " WHERE `".$itemtable."`.`plugin_resources_resources_id` = '".$p['id']."'";
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
               echo "<div class='center'><h2>".$item->getTypeName();
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
               echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/resources/front/task.php?contains%5B0%5D=".
               $p['id']."&field%5B0%5D=13&sort=1&is_deleted=0&start=0'>"._x('button','Search')."</a><br>";
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
            
            // Pager
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
                                          $header_num,$linkto,$p['sort']==$val,$p['order']);
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
               
               Session::initNavigateListItems($itemtype, PluginResourcesResource::getTypeName(2)." = ".
                  (empty($PluginResourcesResource->fields['name']) ? "(".$p['id'].")" : $PluginResourcesResource->fields['name']));
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
               $title.=__('Tasks list', 'resources');
            }
           
            // Display footer
            echo Search::showFooter($output_type,$title);
           
            //massive action
            if ($item->canCreate() && $canedit && $output_type==Search::HTML_OUTPUT && $p['withtemplate']!=2) {
               $massiveactionparams['ontop'] = false;
               Html::showMassiveActions($itemtype, $massiveactionparams);
               Html::closeForm();
            } else {
               echo "</table></div>";
            }

            // Pager
            if ($output_type==Search::HTML_OUTPUT) {
               echo "<br>";
               Html::printAjaxPager(self::getTypeName(2),$p['start'],$numrows);
            }
         } else {
            echo Search::showError($output_type);
         }
      }
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
         
         $query = "SELECT `".$this->getTable()."`.`id` AS plugin_resources_tasks_id, `".$this->getTable()."`.`name` AS name_task, `".$this->getTable()."`.`plugin_resources_tasktypes_id` AS plugin_resources_tasktypes_id,`".$this->getTable()."`.`is_deleted` AS is_deleted, ";
         $query.= "`".$this->getTable()."`.`users_id` AS users_id_task, `glpi_plugin_resources_resources`.`id` as id, `glpi_plugin_resources_resources`.`name` AS name, `glpi_plugin_resources_resources`.`firstname` AS firstname, `glpi_plugin_resources_resources`.`entities_id`, `glpi_plugin_resources_resources`.`users_id` as users_id ";
         $query.= " FROM `".$this->getTable()."`,`glpi_plugin_resources_resources` ";
         $query.= " WHERE `glpi_plugin_resources_resources`.`is_template` = '0' 
                  AND `glpi_plugin_resources_resources`.`is_deleted` = '0' 
                  AND `".$this->getTable()."`.`is_deleted` = '0' 
                  AND `".$this->getTable()."`.`is_finished` = '0' 
                  AND `".$this->getTable()."`.`plugin_resources_resources_id` = `glpi_plugin_resources_resources`.`id` 
                  $ASSIGN ) ";

         // Add Restrict to current entities
         $PluginResourcesResource = new PluginResourcesResource();
         $itemtable = "glpi_plugin_resources_resources";
         if ($PluginResourcesResource->isEntityAssign()) {
            $LINK= " AND " ;
            $query.=getEntitiesRestrictRequest($LINK,$itemtable);
         }
         
         $query .= " ORDER BY `glpi_plugin_resources_resources`.`name` DESC LIMIT 10;";
         
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         
         if ($number > 0) {
            
            echo "<div align='center'><table class='tab_cadre' width='100%'>";
            echo "<tr><th colspan='".(7+$colsup)."'>".PluginResourcesResource::getTypeName(2).
            ": ".__('Tasks in progress', 'resources')." <a href='".$CFG_GLPI["root_doc"]."/plugins/resources/front/task.php?contains%5B0%5D=0&field%5B0%5D=9&sort=1&is_deleted=0&start=0'>".__('All')."</a></th></tr>";
            echo "<tr><th>".__('Name')."</th>";
            if (Session::isMultiEntitiesMode())
               echo "<th>".__('Entity')."</th>";
            echo "<th>".PluginResourcesTaskType::getTypeName(2)."</th>";
            echo "<th>".__('Planning')."</th>";
            echo "<th>".PluginResourcesResource::getTypeName(1)."</th>";
            echo "<th>".__('Resource manager', 'resources')."</th>";
            echo "<th>".__('User')."</th>";
            echo "</tr>";
         
            while ($data=$DB->fetch_array($result)) {
               
               echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";
               echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/resources/front/task.form.php?id=".$data["plugin_resources_tasks_id"]."'>".$data["name_task"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["plugin_resources_tasks_id"].")";
               echo "</a></td>";
               if (Session::isMultiEntitiesMode())
                  echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entities_id'])."</td>";		
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_plugin_resources_tasktypes",$data["plugin_resources_tasktypes_id"])."</td>";
               echo "<td align='center'>";
               $restrict = " `plugin_resources_tasks_id` = '".$data['plugin_resources_tasks_id']."' ";
               $plans = getAllDatasFromTable("glpi_plugin_resources_taskplannings",$restrict);
               
               if (!empty($plans)) {
                  foreach ($plans as $plan) {
                     echo Html::convDateTime($plan["begin"]) . "&nbsp;->&nbsp;" .
                     Html::convDateTime($plan["end"]);
                  }
               } else {
                  _e('None');
               }
               echo "</td>";
               
               echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php?id=".$data["id"]."'>".$data["name"]." ".$data["firstname"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
               echo "</a></td>";
               
               echo "<td class='center'>".getUserName($data["users_id"])."</td>";
               
               echo "<td class='center'>".getUserName($data["users_id_task"])."</td>";
                        
               echo "</tr>";
            }
            
            echo "</table></div><br>";
            
         }
      }
      
      $PluginResourcesChecklist = new PluginResourcesChecklist();
      $PluginResourcesChecklist->showOnCentral(false);
      echo "<br>";
      $PluginResourcesChecklist->showOnCentral(true);

      echo "</td></tr></table>";
   }
   
   // Cron action
   static function cronInfo($name) {
       
      switch ($name) {
         case 'ResourcesTask':
            return array (
               'description' => __('Not finished tasks', 'resources'));   // Optional
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
   static function cronResourcesTask($task=NULL) {
      global $DB,$CFG_GLPI;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message=array();
      $cron_status = 0;
      
      $resourcetask = new self();
      $query_expired = $resourcetask->queryAlert();
      
      $querys = array(Alert::END=>$query_expired);
      
      $task_infos = array();
      $task_messages = array();

      foreach ($querys as $type => $query) {
         $task_infos[$type] = array();
         foreach ($DB->request($query) as $data) {
            $entity = $data['entities_id'];
            $message = $data["name"].": ".
                        Html::convDate($data["date_end"])."<br>\n";
            $task_infos[$type][$entity][] = $data;

            if (!isset($tasks_infos[$type][$entity])) {
               $task_messages[$type][$entity] = __('Not finished tasks', 'resources')."<br />";
            }
            $task_messages[$type][$entity] .= $message;
         }
      }
      
      foreach ($querys as $type => $query) {
      
         foreach ($task_infos[$type] as $entity => $tasks) {
            Plugin::loadLang('resources');

            if (NotificationEvent::raiseEvent("AlertExpiredTasks",
                                              new PluginResourcesResource(),
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
      $query = "SELECT `".$this->getTable()."`.*, `glpi_plugin_resources_resources`.`entities_id`,
                        `glpi_plugin_resources_taskplannings`.`end` AS date_end
            FROM `".$this->getTable()."`
            LEFT JOIN `glpi_plugin_resources_taskplannings` ON (`glpi_plugin_resources_taskplannings`.`plugin_resources_tasks_id` = `".$this->getTable()."`.`id`)
            LEFT JOIN `glpi_plugin_resources_resources` ON (`glpi_plugin_resources_resources`.`id` = `".$this->getTable()."`.`plugin_resources_resources_id`)
            WHERE `glpi_plugin_resources_taskplannings`.`end` IS NOT NULL 
            AND `glpi_plugin_resources_taskplannings`.`end` <= '".$date."' 
            AND `glpi_plugin_resources_resources`.`is_template` = '0' 
            AND `glpi_plugin_resources_resources`.`is_deleted` = '0' 
            AND `".$this->getTable()."`.`is_deleted` = '0' 
            AND `".$this->getTable()."`.`is_finished` = '0'";
      
      return $query;
   }
   
   //Massive action
   function getSpecificMassiveActions($checkitem = NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($isadmin) {
         $actions['Install'] = __('Associate');
         $actions['Desinstall'] = __('Dissociate');
         $actions['Duplicate'] = __('Duplicate', 'resources');
         if (Session::haveRight('transfer', 'r')
            && Session::isMultiEntitiesMode()) {
            $actions['Transfert'] = __('Transfer');
         }
      }
      return $actions;
   }

   function showSpecificMassiveActionsParameters($input = array()) {

      switch ($input['action']) {
         case "Install" :
            Dropdown::showAllItems("item_item",0,0,-1,self::getTypes());
            echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='" . _sx('button', 'Post') . "'>";
            return true;
            break;
            
         case "Desinstall" :
            Dropdown::showAllItems("item_item",0,0,-1,self::getTypes());
            echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='" . _sx('button', 'Post') . "'>";
            return true;
            break;
            
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

      $task_item = new PluginResourcesTask_Item();

      switch ($input['action']) {
         case "Transfert" :
            if ($input['itemtype'] == 'PluginResourcesTask') {
               foreach ($input["item"] as $key => $val) {
                  if ($val == 1) {
                     $this->getFromDB($key);
                     $tasktype = PluginResourcesTaskType::transfer($PluginResourcesTask->fields["plugin_resources_tasktypes_id"],
                                                                  $input['entities_id']);
                     if ($tasktype > 0) {
                        $values["id"] = $key;
                        $values["plugin_resources_tasktypes_id"] = $tasktype;
                        $PluginResourcesTask->update($values);
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
            if ($input['itemtype']=='PluginResourcesTask') {
               foreach ($input["item"] as $key => $val) {
                  if ($val==1) {
                     $this->getFromDB($key);
                     unset($this->fields["id"]);
                     $this->fields["name"]=addslashes($this->fields["name"]);
                     $this->fields["comment"]=addslashes($this->fields["comment"]);
                     if ($this->add($this->fields)) {
                        $res['ok']++;
                     } else {
                        $res['ko']++;
                     }
                  }
               }
            }
            break;
         case "Install" :
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  $values = array('plugin_resources_tasks_id' => $key,
                     'items_id'      => $input["item_item"],
                     'itemtype'      => $input['typeitem']);
                  if ($task_item->add($values)) {
                     $res['ok']++;
                  } else {
                     $res['ko']++;
                  }
               }
            }
            break;
         case "Desinstall" :
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  if ($task_item->deleteItemByTaskAndItem($key,$input['item_item'],$input['typeitem'])) {
                     $res['ok']++;
                  } else {
                     $res['ko']++;
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
   
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType()=='PluginResourcesResource') {
         self::pdfForResource($pdf, $item);

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
   static function pdfForResource(PluginPdfSimplePDF $pdf, PluginResourcesResource $appli) {
      global $DB;
      
      $ID = $appli->fields['id'];

      if (!$appli->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_resources_haveRight("resources","r")) {
         return false;
      }

      $query = "SELECT * 
               FROM `glpi_plugin_resources_tasks` 
               WHERE `plugin_resources_resources_id` = '$ID'
               AND `is_deleted` ='0'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      $i=$j=0;
      
      $pdf->setColumnsSize(100);

      if($number>0) {
         
         $pdf->displayTitle('<b>'.self::getTypeName(2).'</b>');

         $pdf->setColumnsSize(14,14,14,14,16,14,14);
         $pdf->displayTitle('<b><i>'.
            __('Name'),
            __('Type'),
            __('Comments'),
            __('Duration'),
            __('Planning'),
            __('Resource manager', 'resources'),
            __('Group').'</i></b>'
            );
      
         $i++;
         
         while ($j < $number) {
            
            $tID=$DB->result($result, $j, "id");
            $actiontime_ID=$DB->result($result, $j, "actiontime");
            
            $actiontime='';
            $units=Toolbox::getTimestampTimeUnits($actiontime_ID);

            $hour = $units['hour'];
            $minute = $units['minute'];
            if ($hour) $actiontime = $hour .__('Hour', 'Hours', 2);
            if ($minute||!$hour)
               $actiontime.= $minute .__('Minute', 'Minutes', 2);
            
            $restrict = " `plugin_resources_tasks_id` = '".$tID."' ";
            $plans = getAllDatasFromTable("glpi_plugin_resources_taskplannings",$restrict);
            
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
            $task_type=$DB->result($result, $j, "plugin_resources_tasktypes_id");
            $comment=$DB->result($result, $j, "comment");
            $groups_id=$DB->result($result, $j, "groups_id");
            
            $pdf->displayLine(
               Html::clean($name),
               Html::clean(Dropdown::getDropdownName("glpi_plugin_resources_tasktypes",$task_type)),
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
}

?>