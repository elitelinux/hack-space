<?php
/*
 * @version $Id: checklistconfig.class.php 480 2012-11-09 tsmr $
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

class PluginResourcesChecklistconfig extends CommonDBTM {
	
	static function getTypeName($nb=0) {

      return _n('Checklist setup', 'Checklists setup', $nb, 'resources');
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
   
	//define header form
	function defineTabs($options=array()) {
		
		$ong = array();
		$this->addStandardTab(__CLASS__, $ong,$options);
		
		return $ong;
	}
	
   function getSearchOptions() {
   
      $tab = array();
    
      $tab['common']             = self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      
      $tab[2]['table']           = $this->getTable();
      $tab[2]['field']           = 'address';
      $tab[2]['name']            = __('Link', 'resources');

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'comment';
      $tab[3]['name']            = __('Description');
      $tab[3]['datatype']        = 'text';

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'tag';
      $tab[4]['name']            = __('Important', 'resources');
      $tab[4]['datatype']        = 'bool';
      
      $tab[30]['table']          = $this->getTable();
      $tab[30]['field']          = 'id';
      $tab[30]['name']           = __('ID');
      $tab[30]['datatype']       = 'number';
      $tab[30]['massiveaction']  = false;
		
		$tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['datatype']       = 'dropdown';
      
      return $tab;
   }
	
	function showForm ($ID, $options=array()) {
		
		$this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td >".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name",array('size' => "40"));	
      echo "</td>";
      
      echo "<td>";
      echo __('Important', 'resources')."</td><td>";
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
      
      echo "<td colspan = '4'>";
      echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
      echo __('Description')."</td></tr>";
      echo "<tr><td class='center'>";
      echo "<textarea cols='125' rows='6' name='comment'>".$this->fields["comment"];
      echo "</textarea>";
      echo "</td></tr></table>";
      echo "</td>";
      
      echo "</tr>";

      $options['candel'] = false;
      $this->showFormButtons($options);
      return true;

	}
	
	function addResourceChecklist($resource,$checklists_id,$checklist_type) {
      
      $restrict = "`id` = '".$checklists_id."'";

      $checklists = getAllDatasFromTable("glpi_plugin_resources_checklistconfigs",$restrict);
      
      if (!empty($checklists)) {
         
         foreach ($checklists as $checklist) {
            
            if (isset($resource->fields["plugin_resources_contracttypes_id"])) { 
               unset($checklist["id"]);
               $checklist["plugin_resources_resources_id"] = $resource->fields["id"];
               $checklist["plugin_resources_contracttypes_id"] = $resource->fields["plugin_resources_contracttypes_id"];
               $checklist["checklist_type"] = $checklist_type;
               $checklist["name"] = addslashes($checklist["name"]);
               $checklist["address"] = addslashes($checklist["address"]);
               $checklist["comment"] = addslashes($checklist["comment"]);
               $checklist["entities_id"] = $resource->fields["entities_id"];
               $resource_checklist= new PluginResourcesChecklist();
               $resource_checklist->add($checklist);
            }
         }
      }
   }
   
	function addChecklistsFromRules($resource,$checklist_type) {
      
      $rulecollection = new PluginResourcesRuleChecklistCollection($resource->fields["entities_id"]);
      
      if (isset($resource->fields["plugin_resources_contracttypes_id"]) &&
            $resource->fields["plugin_resources_contracttypes_id"] > 0)
         $contract = $resource->fields["plugin_resources_contracttypes_id"];
        else
         $contract = 0;
         
      $checklists=array();
      $checklists=$rulecollection->processAllRules(array("plugin_resources_contracttypes_id"=>$contract,
                                                "checklist_type"=>$checklist_type),$checklists,array());
      
      if (!empty($checklists)) {
         
         foreach ($checklists as $key => $checklist) {
            $this->addResourceChecklist($resource,$checklist,$checklist_type);
         }
      }
	}
	
	function addRulesFromChecklists($data) {
      
      $rulecollection = new PluginResourcesRuleChecklistCollection();
      $rulecollection->checkGlobal('w');
      
      foreach ($data["item"] as $key => $val) {
         if ($val == 1) {
           
            $this->getFromDB($key);
            $rule = new PluginResourcesRuleChecklist();
            $values["name"] = addslashes($this->fields["name"]);
            $values["match"] = "AND";
            $values["is_active"] = 1;
            $values["is_recursive"] = 1;
            $values["entities_id"] = $this->fields["entities_id"];
            $values["sub_type"] = "PluginResourcesRuleChecklist";
            $newID = $rule->add($values);
            
            if (isset($data["checklist_type"]) && $data["checklist_type"] > 0) {
               $criteria = new RuleCriteria();
               $values["rules_id"] = $newID;
               $values["criteria"] = "checklist_type";
               $values["condition"] = 0;
               $values["pattern"] = $data["checklist_type"];
               $criteria->add($values);
            }
            
            if (isset($data["plugin_resources_contracttypes_id"])) {
               $criteria = new RuleCriteria();
               $values["rules_id"] = $newID;
               $values["criteria"] = "plugin_resources_contracttypes_id";
               $values["condition"] = $data["condition"];
               $values["pattern"] = $data["plugin_resources_contracttypes_id"];
               $criteria->add($values);
            }
             
            $action = new RuleAction();
            $values["rules_id"] = $newID;
            $values["action_type"] = "assign";
            $values["field"] = "checklists_id";
            $values["value"] = $key;
            $action->add($values);
            
         }
      }
	}
	
	//Massive action
   function getSpecificMassiveActions($checkitem = NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($isadmin) {
         $actions['Generate_Rule'] = __('Generate a rule', 'resources');

         if (Session::haveRight('transfer', 'r')
            && Session::isMultiEntitiesMode()) {
            $actions['Transfert'] = __('Transfer');
         }
      }
      return $actions;
   }

   function showSpecificMassiveActionsParameters($input = array()) {
      
      $PluginResourcesChecklist = new PluginResourcesChecklist();
      $PluginResourcesContractType = new PluginResourcesContractType();
	
      switch ($input['action']) {
         case "Generate_Rule" :
            $PluginResourcesChecklist->dropdownChecklistType("checklist_type", $_SESSION["glpiactive_entity"]);
            echo "&nbsp;";
            RuleCriteria::dropdownConditions("PluginResourcesRuleChecklist",array('criterion'=> 'plugin_resources_contracttypes_id',
                                                                                 'allow_conditions' => array(Rule::PATTERN_IS, Rule::PATTERN_IS_NOT)));
            echo "&nbsp;";
            $PluginResourcesContractType->dropdownContractType("plugin_resources_contracttypes_id");
            echo "&nbsp;";
            echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='" . _sx('button', 'Post') . "'>";
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
            if ($input['itemtype'] == 'PluginResourcesEmployment') {
               foreach ($input["item"] as $key => $val) {
                  if ($val == 1) {
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
         case "Generate_Rule" :
            if ($input['itemtype']=='PluginResourcesChecklistconfig') {
               $this->addRulesFromChecklists($input);
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