<?php
/*
 * @version $Id: task_item.class.php 480 2012-11-09 tsmr $
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

class PluginResourcesTask_Item extends CommonDBTM {
	
	static function canCreate() {
      return plugin_resources_haveRight('task', 'w');
   }

   static function canView() {
      return plugin_resources_haveRight('task', 'r');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         if ($item->getType()=='PluginResourcesTask') {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(_n('Associated item', 'Associated items', 2),
                        self::countForResourceTask($item));
            }
            return _n('Associated item', 'Associated items', 2);

         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      $self = new self();
      if ($item->getType()=='PluginResourcesTask') {
         $self->showItemFromPlugin($item->getID(),$withtemplate);

      }
      return true;
   }
   
   static function countForResourceTask(PluginResourcesTask $item) {
      
      $types = implode("','", PluginResourcesResource::getTypes());
      if (empty($types)) {
         return 0;
      }
      return countElementsInTable('glpi_plugin_resources_tasks_items',
                                  "`itemtype` IN ('$types')
                                   AND `plugin_resources_tasks_id` = '".$item->getID()."'");
   }
   
	function getFromDBbyTaskAndItem($plugin_resources_tasks_id,$items_id,$itemtype) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->getTable()."` " .
			"WHERE `plugin_resources_tasks_id` = '" . $plugin_resources_tasks_id . "' 
			AND `itemtype` = '" . $itemtype . "'
			AND `items_id` = '" . $items_id . "'";
		if ($result = $DB->query($query)) {
			if ($DB->numrows($result) != 1) {
				return false;
			}
			$this->fields = $DB->fetch_assoc($result);
			if (is_array($this->fields) && count($this->fields)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	
	function addTaskItem($values) {
      
      $args = explode(",",$values['item_item']);
      if (isset($args[0]) &&isset($args[1])) {
         $this->add(array('plugin_resources_tasks_id'=>$values["plugin_resources_tasks_id"],
                        'items_id'=>$args[0],
                        'itemtype'=>$args[1]));
      }
   }
  
   function deleteItemByTaskAndItem($plugin_resources_tasks_id,$items_id,$itemtype) {
    
      if ($this->getFromDBbyTaskAndItem($plugin_resources_tasks_id,$items_id,$itemtype)) {
         $this->delete(array('id'=>$this->fields["id"]));
      }
   }
  
   function showItemFromPlugin($instID,$withtemplate='') {
      global $DB,$CFG_GLPI;

      if (empty($withtemplate)) $withtemplate=0;
      
      $PluginResourcesTask = new PluginResourcesTask();
      if ($PluginResourcesTask->getFromDB($instID)) {
         
         $plugin_resources_resources_id=$PluginResourcesTask->fields["plugin_resources_resources_id"];
         $PluginResourcesResource=new PluginResourcesResource();
         $PluginResourcesResource->getFromDB($plugin_resources_resources_id);
         
         $canedit=$PluginResourcesResource->can($plugin_resources_resources_id,'w');
         
         $query = "SELECT `items_id`, `itemtype` 
               FROM `".$this->getTable()."` 
               WHERE `plugin_resources_tasks_id` = '$instID' 
               ORDER BY `itemtype` ";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         
         echo "<form method='post' name='addtaskitem' action=\"./task.form.php\">";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='".($canedit?3:2)."'>"._n('Associated item', 'Associated items', 2);
         echo "</th></tr>";
         echo "<tr><th>"._n('Type', 'Types', 2)."</th>";
         echo "<th>".__('Name')."</th>";
         if($canedit && $this->canCreate() && $withtemplate<2)
            echo "<th>&nbsp;</th>";
         echo "</tr>";
         $used=array();		
         if($number !="0") {
      
            for ($i=0 ; $i < $number ; $i++) {
               $type=$DB->result($result, $i, "itemtype");
               $items_id=$DB->result($result, $i, "items_id");
               if (!class_exists($type)) {
                  continue;
               }           
               $item = new $type();
               if ($item->canView()) {
                  $table = getTableForItemType($type);
                  $query = "SELECT `".$table."`.*, `".$this->getTable()."`.`id` as items_id 
                        FROM `".$this->getTable()."` 
                        INNER JOIN `".$table."` ON (`".$table."`.`id` = `".$this->getTable()."`.`items_id`) 
                        WHERE `".$this->getTable()."`.`itemtype` = '".$type."' 
                        AND `".$this->getTable()."`.`items_id` = '".$items_id."' 
                        AND `".$this->getTable()."`.`plugin_resources_tasks_id` = '$instID' ";
                  $query.= "ORDER BY `".$table."`.`name` ";
                  $result_linked=$DB->query($query);
         
                  if ($DB->numrows($result_linked)) {

                     while ($data=$DB->fetch_assoc($result_linked)) {
                        $ID="";
                        $itemID=$data["id"];
                        $used[]=$itemID;
                        if($_SESSION["glpiis_ids_visible"]||empty($data["name"])) $ID= " (".$data["id"].")";
                        $itemname=$data["name"];
                        if ($type=='User')
                           $itemname=getUserName($itemID);
                        
                        $link=Toolbox::getItemTypeFormURL($type);
                        $name= "<a href=\"".$link."\">". $itemname."$ID</a>";
                        echo "<tr class='tab_bg_1'>";
                        echo "<td class='center'>".$item->getTypeName()."</td>";
         
                        echo "<td class='center' ".(isset($data['is_deleted'])&&$data['is_deleted']=='1'?"class='tab_bg_2_2'":"").">".$name."</td>";
                        if($canedit && $this->canCreate() && $withtemplate<2) {
                           echo "<td class='center' class='tab_bg_2'>";
                           Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/resources/front/task.form.php',
                                    'deletetaskitem',
                                    _x('button','Delete permanently'),
                                    array('id' => $data["items_id"]));
                           echo "</td>";
                        }
                        echo "</tr>";
                     }
                  }
               }
            }
         }
         if($canedit && $this->canCreate() && $withtemplate<2) {
            echo "<tr class='tab_bg_1'><td colspan='2' class='right'>";
            echo "<input type='hidden' name='plugin_resources_tasks_id' value='$instID'>";
            $PluginResourcesResource_Item = new PluginResourcesResource_Item();
            $PluginResourcesResource_Item->dropdownItems($plugin_resources_resources_id,$used);
            echo "</td>";
            echo "<td class='center' colspan='2' class='tab_bg_2'>";
            echo "<input type='submit' name='addtaskitem' value=\""._sx('button','Add')."\" class='submit'>";
            echo "</td></tr>";
            echo "</table></div>" ;

         } else {
      
            echo "</table></div>";
         }
         Html::closeForm();
         echo "<br>";
      }
   }
}

?>