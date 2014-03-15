<?php
/*
 * @version $Id: projet_projet.class.php 17152 2012-01-24 11:22:16Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// Class Projet links
class PluginProjetTask_Task extends CommonDBRelation {


   // From CommonDBRelation
   static public $itemtype_1 = 'PluginProjetTask';
   static public $items_id_1 = 'plugin_projet_tasks_id_1';
   static public $itemtype_2 = 'PluginProjetTask';
   static public $items_id_2 = 'plugin_projet_tasks_id_2';

   static public $check_entities = false;

   // Ticket links
   const LINK_TO        = 1;


   function canCreateItem() {

      $task = new PluginProjetTask();
      return $task->can($this->fields['plugin_projet_tasks_id_1'], 'w')
             || $task->can($this->fields['plugin_projet_tasks_id_2'], 'w');
   }

   static function canView() {
      return plugin_projet_haveRight('task', 'r');
   }


   /**
    * Get linked projets to a projet
    *
    * @param $ID ID of the projet id
    *
    * @return array of linked projets  array(id=>linktype)
   **/
   static function getParentProjetTasksTo ($ID) {
      global $DB;

      // Make new database object and fill variables
      if (empty($ID)) {
         return false;
      }

      $sql = "SELECT *
              FROM `glpi_plugin_projet_tasks_tasks`
              WHERE `plugin_projet_tasks_id_1` = '$ID'";

      $tasks = array();

      foreach ($DB->request($sql) as $data) {
         if ($data['plugin_projet_tasks_id_1']!=$ID) {
            $tasks[$data['id']] = array('link'       => $data['link'],
                                          'plugin_projet_tasks_id' => $data['plugin_projet_tasks_id_1']);
         } else {
            $tasks[$data['id']] = array('link'       => $data['link'],
                                          'plugin_projet_tasks_id' => $data['plugin_projet_tasks_id_2']);
         }
      }

      ksort($tasks);
      return $tasks;
   }


   /**
    * Display linked projets to a projet
    *
    * @param $ID ID of the projet id
    *
    * @return nothing display
   **/
   static function displayLinkedProjetTasksTo ($ID, $notif = false) {
      global $DB, $CFG_GLPI;

      $tasks   = self::getParentProjetTasksTo($ID);
      $canupdate = plugin_projet_haveRight('task', 'w');

      $task = new PluginProjetTask();
      if (is_array($tasks) && count($tasks)) {
         foreach ($tasks as $linkID => $data) {
            
            if ($notif) {
               return Dropdown::getDropdownName("glpi_plugin_projet_tasks", $data['plugin_projet_tasks_id']);
            } else {
            
               echo self::getLinkName($data['link'])."&nbsp;";
               if (!$_SESSION['glpiis_ids_visible']) {
                  echo __('ID')."&nbsp;".$data['plugin_projet_tasks_id']."&nbsp;:&nbsp;";
               }

               if ($task->getFromDB($data['plugin_projet_tasks_id'])) {
                  echo $task->getLink();
                  if ($canupdate) {
                     
                     echo "&nbsp;";
                     Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/projet/front/task.form.php',
                                    'delete_link',
                                    _x('button','Delete permanently'),
                                    array('delete_link' => 'delete_link',
                                          'id' => $linkID,
                                          'plugin_projet_tasks_id' =>$ID
                                          ),
                                     $CFG_GLPI["root_doc"]."/pics/delete.png");

                  }
               }
            }
         }
      }
   }


   /**
    * Dropdown for links between projets
    *
    * @param $myname select name
    * @param $value default value
   **/
   static function dropdownLinks($myname, $value=self::LINK_TO) {

      $tmp[self::LINK_TO]        = __('Parent task', 'projet');
      Dropdown::showFromArray($myname, $tmp, array('value' => $value));
   }


   /**
    * Get Link Name
    *
    * @param $value default value
   **/
   static function getLinkName($value) {

      $tmp[self::LINK_TO]        = __('Parent task', 'projet');

      if (isset($tmp[$value])) {
         return $tmp[$value];
      }
      return NOT_AVAILABLE;
   }

   
   function prepareInputForAdd($input) {

      $task = new PluginProjetTask();
      if (!isset($input['plugin_projet_tasks_id_1'])
          || !isset($input['plugin_projet_tasks_id_2'])
          || $input['plugin_projet_tasks_id_2'] == $input['plugin_projet_tasks_id_1']
          || !$task->getFromDB($input['plugin_projet_tasks_id_1'])
          || !$task->getFromDB($input['plugin_projet_tasks_id_2'])) {
         return false;
      }

      if (!isset($input['link'])) {
         $input['link'] = self::LINK_TO;
      }

      return $input;
   }
   
   static function findChilds($DB,$options) {
    
      $queryBranch='';
      // Recherche les enfants
      $queryChilds= "SELECT `glpi_plugin_projet_tasks_tasks`.`plugin_projet_tasks_id_1` 
               FROM `glpi_plugin_projet_tasks` 
               LEFT JOIN `glpi_plugin_projet_tasks_tasks` 
               ON (`glpi_plugin_projet_tasks_tasks`.`plugin_projet_tasks_id_2` = `glpi_plugin_projet_tasks`.`id`)
               WHERE `plugin_projet_projets_id` = '".$options["plugin_projet_projets_id"]."' 
               AND `glpi_plugin_projet_tasks_tasks`.`plugin_projet_tasks_id_2` = '".$options["id"]."' 
               AND `is_template` = '0' 
               AND `is_deleted` = '0' "
               . getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_tasks",'',$options["entities_id"],true);
               
      if ($resultChilds = $DB->query($queryChilds)) {
         while ($dataChilds = $DB->fetch_array($resultChilds)) {
            $child=$dataChilds["plugin_projet_tasks_id_1"];
            $queryBranch .= ",'$child'";
            // Recherche les petits enfants recursivement
            $values["plugin_projet_projets_id"] = $options["plugin_projet_projets_id"];
            $values["entities_id"] = $options["entities_id"];
            $values["id"] = $child;
            $queryBranch .= self::findChilds($DB,$values);
         }
      }
      return $queryBranch;
   }
   
   static function dropdownParent($name,$value=0, $options=array()) {
      global $DB;
      
      echo "<select name='$name'>";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";
      
      $restrict = " `is_template` = '0' AND `is_deleted` = '0'";
      $restrict.= " AND `plugin_projet_projets_id` = '".$options["plugin_projet_projets_id"]."'";
      $restrict.= getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_tasks",'',
                                                                     $options["entities_id"],true);
      if (!empty($options["id"])) {
         $restrict.= " AND `id` != '".$options["id"]."' "; 
      }
      
      $restrict.= " AND `id` NOT IN ('".$options["id"]."'";
      $restrict.= self::findChilds($DB,$options);
      $restrict.= ") ";

      $restrict.= "ORDER BY `name` ASC ";

      $tasks = getAllDatasFromTable("glpi_plugin_projet_tasks",$restrict);

      if (!empty($tasks)) {
         $prev=-1;
         foreach ($tasks as $task) {
            if ($task["entities_id"]!=$prev) {
               if ($prev>=0) {
                  echo "</optgroup>";
               }
               $prev=$task["entities_id"];
               echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
            }
            $output = $task["name"];
            echo "<option value='".$task["id"]."' ".($value=="".$task["id"].""?" selected ":"").
            " title=\"$output\">".substr($output,0,$_SESSION["glpidropdown_chars_limit"])."</option>";
         }
         if ($prev>=0) {
            echo "</optgroup>";
         }
      }
      echo "</select>";	
   }
   
	//$parents=0 -> childs
   //$parents=1 -> parents
   static function showHierarchy($ID,$parents=0) {
      global $DB,$CFG_GLPI;
      
      $first=false;
      $projet = new PluginProjetTask();
      
      $query = "SELECT `glpi_plugin_projet_tasks`.*  ";
      if ($parents!=0) {
         $parent = "plugin_projet_tasks_id_1";
         $child = "plugin_projet_tasks_id_2";
      } else {
         $parent = "plugin_projet_tasks_id_2";
         $child = "plugin_projet_tasks_id_1";
      }
      $query.= " FROM `glpi_plugin_projet_tasks`";
      $query.= " LEFT JOIN `glpi_plugin_projet_tasks_tasks` 
                  ON (`glpi_plugin_projet_tasks_tasks`.`$child` = `glpi_plugin_projet_tasks`.`id`)";
      $query.= " WHERE `glpi_plugin_projet_tasks_tasks`.`$parent` = '$ID' ";

      if ($projet->maybeTemplate()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`glpi_plugin_projet_tasks`.`is_template` = '0' ";
      }
      // Add is_deleted if item have it
      if ($projet->maybeDeleted()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`glpi_plugin_projet_tasks`.`is_deleted` = '0' ";
      }   
      $LINK= " AND " ;
      
      $query.=getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_tasks",'','',$projet->maybeRecursive());
            
      $query.= " ORDER BY `glpi_plugin_projet_tasks`.`name`";
      
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i = 0;
      
      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }
         
      if ($number !="0") {

         echo "<div align='center'><table class='tab_cadre_fixe'>";
         
         $title = _n('Child task', 'Child tasks', 2, 'projet');
         if ($parents!=0)
            $title = __('Parent task', 'projet');
         
         echo "<tr><th colspan='".(7+$colsup)."'>".$title."</th></tr>";
         
         echo "<tr><th>".__('Name')."</th>";
         if (Session::isMultiEntitiesMode()) {
            echo "<th>".__('Entity')."</th>";
         }
         echo "<th>".__('Progress')."</th>";
         echo "<th>"._n('User', 'Users', 1)."</th>";
         echo "<th>"._n('Group', 'Groups', 1)."</th>";
         echo "<th>".__('State')."</th>";
         echo "</tr>";

         while ($data=$DB->fetch_array($result)) {
            $start = 0;
            $output_type=Search::HTML_OUTPUT;
            $del=false;
            if($data["is_deleted"]=='0')
               echo "<tr class='tab_bg_1'>";
            else
               echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";

            echo Search::showItem($output_type,"<a href=\"./task.form.php?id=".$data["id"]."\">".
            $data["name"].($_SESSION["glpiis_ids_visible"]||empty($data["name"])?' ('.$data["id"].') ':'')."</a>",$item_num,$i-$start+1,'');
            
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entities_id'])."</td>";
            }
            echo Search::showItem($output_type,
               PluginProjetProjet::displayProgressBar('100',$data["advance"],array("simple"=>true)),
               $item_num,$i-$start+1,"align='center'");
            echo Search::showItem($output_type,getUserName($data['users_id']),$item_num,$i-$start+1,'');
            echo Search::showItem($output_type,
            Dropdown::getDropdownName("glpi_groups",$data['groups_id']),$item_num,$i-$start+1,'');
            echo Search::showItem($output_type,
            Dropdown::getDropdownName("glpi_plugin_projet_taskstates",$data['plugin_projet_taskstates_id']),
            $item_num,$i-$start+1,
            "bgcolor='".PluginProjetTaskState::getStatusColor($data['plugin_projet_taskstates_id'])."' align='center'");
               
            echo "</tr>";
         }
         echo "</table></div>";
      }
   }
}

?>