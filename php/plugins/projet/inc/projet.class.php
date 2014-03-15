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

class PluginProjetProjet extends CommonDBTM {
   
   static $types = array('Computer','Monitor','NetworkEquipment','Peripheral',
         'Phone', 'Printer', 'Software', 'Group','User','Supplier', 'Ticket', 'Problem');
         
   public $dohistory=true;
   
   static function getTypeName($nb = 0) {

      return _n('Project', 'Projects', $nb, 'projet');
   }
   
   static function canCreate() {
      return plugin_projet_haveRight('projet', 'w');
   }

   static function canView() {
      return plugin_projet_haveRight('projet', 'r');
   }
   
   /**
    * For other plugins, add a type to the linkable types
    *
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }
   //TODO Appliances

   /**
    * Type than could be linked to a Store
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
   
   //clean if projet are deleted
   function cleanDBonPurge() {

      $temp = new PluginProjetProjet_Item();
      $temp->deleteByCriteria(array('plugin_projet_projets_id' => $this->fields['id']));
      
      $temp = new PluginProjetTask();
      $temp->deleteByCriteria(array('plugin_projet_projets_id' => $this->fields['id']),1);
      
      $temp = new PluginProjetFollowup();
      $temp->deleteByCriteria(array('plugin_projet_projets_id' => $this->fields['id']));
      
      $temp = new PluginProjetProjet_Projet();
      $temp->deleteByCriteria(array('plugin_projet_projets_id_1' => $this->fields['id'],
                                    'plugin_projet_projets_id_2' => $this->fields['id']));
      
      
   }
   
   /**
    * Hook called After an item is purge
    */
   static function cleanForItem(CommonDBTM $item) {

      $type = get_class($item);
      $temp = new PluginProjetProjet_Item();
      $temp->deleteByCriteria(array('itemtype' => $type,
                                       'items_id' => $item->getField('id')));
      
      $task = new PluginProjetTask_Item();
      $task->deleteByCriteria(array('itemtype' => $type,
                                       'items_id' => $item->getField('id')));
   }
   
   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */
   function getSelectLinkedItem () {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_projet_projets_items`
              WHERE `plugin_projet_projets_id`='" . $this->fields['id']."'";
   }
   
   function getSearchOptions() {

      $tab = array();
    
      $tab['common']             = self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();
      
      $tab[2]['table']           = $this->getTable();
      $tab[2]['field']           = 'date_begin';
      $tab[2]['name']            = __('Start date');
      $tab[2]['datatype']        = 'date';
      
      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'date_end';
      $tab[3]['name']            = __('End date');
      $tab[3]['datatype']        = 'date';
      
      $tab[4]['table']           = 'glpi_users';
      $tab[4]['field']           = 'name';
      $tab[4]['name']            = __('User');
      $tab[4]['datatype']        = 'dropdown';
      $tab[4]['right']           = 'all';
      
      $tab[5]['table']           = 'glpi_groups';
      $tab[5]['field']           = 'completename';
      $tab[5]['name']            = __('Group');
      $tab[5]['condition']       = '`is_itemgroup`';
      $tab[5]['datatype']        = 'dropdown';
      
      $tab[6]['table']           = 'glpi_plugin_projet_projetstates';
      $tab[6]['field']           = 'name';
      $tab[6]['name']            = PluginProjetProjetState::getTypeName(1);
      $tab[6]['datatype']        = 'dropdown';
      
      $tab[7]['table']           = 'glpi_plugin_projet_projets_projets';
      $tab[7]['field']           = 'plugin_projet_projets_id_1';
      $tab[7]['name']            = __('Parent project', 'projet');
      $tab[7]['massiveaction']   = false;
      $tab[7]['searchtype']      = 'equals';
      $tab[7]['joinparams']      = array('jointype'  => 'item_item');
      $tab[7]['forcegroupby']    =  true;

      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'advance';
      $tab[8]['name']            = __('Progress');
      $tab[8]['datatype']        = 'integer';
      
      $tab[9]['table']           = $this->getTable();
      $tab[9]['field']           = 'show_gantt';
      $tab[9]['name']            = __('Display on the global Gantt', 'projet');
      $tab[9]['datatype']        = 'bool';
      
      $tab[10]['table']          = $this->getTable();
      $tab[10]['field']          = 'estimatedtime';
      $tab[10]['name']           = __('Estimated duration', 'projet');
      $tab[10]['datatype']       = 'timestamp';
      
      $tab[11]['table']          = $this->getTable();
      $tab[11]['field']          = 'comment';
      $tab[11]['name']           = __('Comments');
      $tab[11]['datatype']       = 'text';
      
      $tab[12]['table']          = $this->getTable();
      $tab[12]['field']          = 'description';
      $tab[12]['name']           = __('Description');
      $tab[12]['datatype']       = 'text';
      
      $tab[13]['table']          = $this->getTable();
      $tab[13]['field']          = 'is_recursive';
      $tab[13]['name']           = __('Child entities');
      $tab[13]['datatype']       = 'bool';
      $tab[13]['massiveaction']  = false;
      
      $tab[14]['table']          = $this->getTable();
      $tab[14]['field']          = 'date_mod';
      $tab[14]['name']           = __('Last update');
      $tab[14]['datatype']       = 'datetime';
      $tab[14]['massiveaction']  = false;
      
      $tab[15]['table']          = $this->getTable();
      $tab[15]['field']          = 'is_helpdesk_visible';
      $tab[15]['name']           = __('Associable to a ticket');
      $tab[15]['datatype']       = 'bool';
      
      $tab[16]['table']          = 'glpi_plugin_projet_projets_items';
      $tab[16]['field']          = 'items_id';
      $tab[16]['name']           = _n('Associated item' , 'Associated items', 2);
      $tab[16]['massiveaction']  = false;
      $tab[16]['forcegroupby']   = true;
      $tab[16]['joinparams']     = array('jointype' => 'child');
      
      $tab[31]['table']          = $this->getTable();
      $tab[31]['field']          = 'id';
      $tab[31]['name']           = __('ID');
      $tab[31]['massiveaction']  = false;
      $tab[31]['datatype']       = 'number';
      
      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['datatype']       = 'dropdown';
      
      return $tab;
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()==__CLASS__) {
         
         if (!isset($withtemplate) || empty($withtemplate)) {
            $ong[1] = __('Hierarchy', 'projet');
            $ong[2]= __('Gantt', 'projet');
         }
         if ($_SESSION['glpishow_count_on_tabs']) {
            $ong[3]= self::createTabEntry(_n('Associated participant' , 'Associated participants', 2, 'projet'), PluginProjetProjet_Item::countForProjet($item));
         } else {
            $ong[3] = _n('Associated participant' , 'Associated participants', 2, 'projet');
         }
         return $ong;
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 1 :
               PluginProjetProjet_Projet::showHierarchy($item->getID(),1);
               PluginProjetProjet_Projet::showHierarchy($item->getID());
               break;

            case 2 :
               self::showProjetTreeGantt(array('plugin_projet_projets_id'=>$item->getID(),
                                                'prefixp'=>'','parent'=>0));
               PluginProjetTask::taskLegend();
               break;
               
            case 3 :
               PluginProjetProjet_Item::showForProjet($item, $withtemplate);
               break;

         }
      }
      return true;
   }
   
   function defineTabs($options=array()) {
      
      $ong = array();

      $this->addStandardTab('PluginProjetProjet', $ong,$options);
      $this->addStandardTab('PluginProjetProjet_Item', $ong,$options);
      $this->addStandardTab('Document_Item',$ong,$options);
      $this->addStandardTab('Contract_Item',$ong,$options);
      $this->addStandardTab('PluginProjetFollowup',$ong,$options);
      $this->addStandardTab('PluginProjetTask', $ong,$options);
      if (!isset($options['withtemplate']) || empty($options['withtemplate'])) {
         $this->addStandardTab('Ticket',$ong,$options);
         $this->addStandardTab('Item_Problem', $ong, $options);
      }
      
      $this->addStandardTab('Note',$ong,$options);
      $this->addStandardTab('Log',$ong,$options);

      return $ong;
   }
   
   function post_getEmpty() {

      $this->fields['show_gantt'] = 1;
      $this->fields['is_helpdesk_visible'] = 1;
   }
   
   function prepareInputForAdd($input) {

      if (isset($input['date_begin']) 
            && empty($input['date_begin'])) $input['date_begin']='NULL';
      if (isset($input['date_end']) 
            && empty($input['date_end'])) $input['date_end']='NULL';
      
      if (isset($input["id"]) && $input["id"]>0) {
         $input["_oldID"]=$input["id"];
      }
      
      if (isset($input['plugin_projet_projetstates_id']) 
            && !empty($input['plugin_projet_projetstates_id'])) {
         
         $archived = " `type` = '1' ";
         $states = getAllDatasFromTable("glpi_plugin_projet_projetstates",$archived);
         $tab = array();
         if (!empty($states)) {
            foreach ($states as $state) {
               $tab[]= $state['id'];
            }
         }

         if (!empty($tab) && in_array($input['plugin_projet_projetstates_id'],$tab)) {
           
            $input['advance']='100';
         }  
      }

      unset($input['id']);
      //unset($input['withtemplate']);

      return $input;
   }

   function post_addItem() {
      global $CFG_GLPI;
      
      
      $projet_projet = new PluginProjetProjet_Projet();

      // From interface
      if (isset($this->input['_link'])) {
         $this->input['_link']['plugin_projet_projets_id_1'] = $this->fields['id'];
         // message if projet doesn't exist
         if (!empty($this->input['_link']['plugin_projet_projets_id_2'])) {
            if ($projet_projet->can(-1, 'w', $this->input['_link'])) {
               $projet_projet->add($this->input['_link']);
            } else {
               Session::addMessageAfterRedirect(__('Unknown project', 'projet'), false, ERROR);
            }
         }
      }
      
      // Manage add from template
      if (isset($this->input["_oldID"])) {
         
         //add parent
         PluginProjetProjet_Projet::cloneItem($this->input["_oldID"], $this->fields['id']);
      
         // ADD Documents
         Document_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
         
         // ADD Contracts
         Contract_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
         
         // ADD items
         PluginProjetProjet_Item::cloneItem($this->input["_oldID"], $this->fields['id']);
         
         // ADD tasks
         PluginProjetTask::cloneItem($this->input["_oldID"], $this->fields['id']);

      }

      if (isset($this->input['withtemplate']) 
          && $this->input["withtemplate"]!=1
          && isset($this->input['send_notification']) 
          && $this->input['send_notification']==1) {
         if ($CFG_GLPI["use_mailing"]) {
            NotificationEvent::raiseEvent("new",$this);
         }
      }
   }
   
   function prepareInputForUpdate($input) {
      global $CFG_GLPI;
      
      if (isset($input['date_begin']) 
            && empty($input['date_begin'])) $input['date_begin']='NULL';
      if (isset($input['date_end']) 
            && empty($input['date_end'])) $input['date_end']='NULL';
      
      if (isset($input['plugin_projet_projetstates_id']) 
            && !empty($input['plugin_projet_projetstates_id'])) {
         
         $archived = " `type` = '1' ";
         $states = getAllDatasFromTable("glpi_plugin_projet_projetstates",$archived);
         $tab = array();
         if (!empty($states)) {
            foreach ($states as $state) {
               $tab[]= $state['id'];
            }
         }
         if (!empty($tab) && in_array($input['plugin_projet_projetstates_id'],$tab)) { 
            $input['advance']='100';
         }  
      }
            
      if (isset($input['_link'])) {
         $projet_projet = new PluginProjetProjet_Projet();
         if (!empty($input['_link']['plugin_projet_projets_id_2'])) {
            if ($projet_projet->can(-1, 'w', $input['_link'])) {
               $projet_projet->add($input['_link']);
            } else {
               Session::addMessageAfterRedirect(__('Unknown project', 'projet'), false, ERROR);
            }
         }
      }
      
      $this->getFromDB($input["id"]);
      
      $input["_old_name"]=$this->fields["name"];
      $input["_old_date_begin"]=$this->fields["date_begin"];
      $input["_old_date_end"]=$this->fields["date_end"];
      $input["_old_users_id"]=$this->fields["users_id"];
      $input["_old_groups_id"]=$this->fields["groups_id"];
      $input["_old_plugin_projet_projetstates_id"]=$this->fields["plugin_projet_projetstates_id"];
      $input["_old_advance"]=$this->fields["advance"];
      $input["_old_estimatedtime"]=$this->fields["estimatedtime"];
      $input["_old_show_gantt"]=$this->fields["show_gantt"];
      $input["_old_comment"]=$this->fields["comment"];
      $input["_old_description"]=$this->fields["description"];

      return $input;
   }
   
   function post_updateItem($history=1) {
      global $CFG_GLPI;
      
      if (count($this->updates) 
         && isset($this->input["withtemplate"]) 
         && $this->input["withtemplate"]!=1) {

         if ($CFG_GLPI["use_mailing"] 
            && isset($this->input['send_notification']) 
            && $this->input['send_notification']==1) {
            NotificationEvent::raiseEvent("update",$this);
         }
      }
   }
   
   function pre_deleteItem() {
      global $CFG_GLPI;
      
      if ($CFG_GLPI["use_mailing"] 
         && $this->fields["is_template"]!=1 
         && isset($this->input['delete'])  
         && isset($this->input['send_notification']) 
         && $this->input['send_notification']==1) {
         NotificationEvent::raiseEvent("delete",$this);
      }
      
      return true;
   }
   
   function showForm($ID, $options=array()) {
      global $CFG_GLPI;
      
      $this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'><td>".__('Name')."</td>";
      echo "<td>";
      $opt = array();
      if (isset($options['helpdesk_id']) && !empty($options['helpdesk_id'])) {
         $help = new $options['helpdesk_itemtype']();
         if ($help->getFromDB($options['helpdesk_id'])) {
            $opt['value'] = $help->fields["name"];
            echo "<input type='hidden' name='helpdesk_id' value='".$options['helpdesk_id']."'>";
            echo "<input type='hidden' name='helpdesk_itemtype' value='".$options['helpdesk_itemtype']."'>";
         }
      }
      Html::autocompletionTextField($this,"name",$opt);
      echo "</td>";

      //Projet parent
      echo "<td>".__('Parent project', 'projet')."</td>";
      echo "<td>";
      
      PluginProjetProjet_Projet::displayLinkedProjetsTo($ID, $options['withtemplate']);
      
      if ($this->canCreate() && $options['withtemplate'] < 2) {
         
         $rand_linked_projet = mt_rand();
         
         echo "&nbsp;";
         if (!PluginProjetProjet_Projet::getParentProjetsTo($ID)) {
            echo "<img onClick=\"Ext.get('linkedprojet$rand_linked_projet').setDisplayed('block')\"
                       title=\""._x('button','Add')."\" alt=\""._x('button','Add')."\"
                       class='pointer' src='".$CFG_GLPI["root_doc"]."/pics/add_dropdown.png'>";
         }
         echo "<div style='display:none' id='linkedprojet$rand_linked_projet'>";
         PluginProjetProjet_Projet::dropdownLinks('_link[link]',
                                      (isset($values["_link"])?$values["_link"]['link']:''));
         echo "&nbsp;";
         PluginProjetProjet_Projet::dropdownParent("_link[plugin_projet_projets_id_2]", 
                           (isset($values["_link"])?$values["_link"]['plugin_projet_projets_id_2']:''),
                           array('id' => $this->fields["id"],
                                 'entities_id' => $this->fields["entities_id"]));
         echo "<input type='hidden' name='_link[plugin_projet_projets_id_1]' value='$ID'>\n";
         
         echo "&nbsp;";
         echo "</div>";

         if (isset($values["_link"]) && !empty($values["_link"]['plugin_projet_projets_id_2'])) {
            echo "<script language='javascript'>Ext.get('linkedprojet$rand_linked_projet').
                   setDisplayed('block');</script>";
         }
      }
      
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_2'><td colspan='2'>"._n('Responsible' , 'Responsibles', 2, 'projet')."</td>";
      echo "<td colspan='2'>".__('Planification', 'projet')."</td></tr>";
      
      echo "<tr class='tab_bg_1'><td>".__('User')."</td><td>";
      User::dropdown(array('value' => $this->fields["users_id"],
                           'entity' => $this->fields["entities_id"],
                           'right' => 'all'));
      echo "</td>";
      echo "<td>".__('Start date')."</td><td>";
      Html::showDateFormItem("date_begin",$this->fields["date_begin"],true,true);
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1'><td>".__('Group')."</td><td>";
      Dropdown::show('Group', array('value' => $this->fields["groups_id"],
                                    'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "<td>".__('End date')."</td><td>";
      Html::showDateFormItem("date_end",$this->fields["date_end"],true,true);
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_2'><td>" . __('Estimated duration', 'projet');
      echo "</td>";
      echo "<td>";
      
      $toadd = array();
      for ($i=9;$i<=100;$i++) {
         $toadd[] = $i*DAY_TIMESTAMP;
      }

      Dropdown::showTimeStamp("estimatedtime",array('min'             => 0,
                                                 'max'             => 8*DAY_TIMESTAMP,
                                                 'value'           => $this->fields["estimatedtime"],
                                                 'addfirstminutes' => false,
                                                 'inhours'          => false,
                                                 'toadd'           => $toadd));
      
      
                                           
      echo "</td>\n";
      
      
      echo "<td>" . __('Effective duration', 'projet')."&nbsp;";
      Html::showToolTip(nl2br(__('Total of effective duration of project tasks', 'projet')));
      echo "</td>";
      echo "<td>". self::getProjectForecast($ID) . "</td></tr>";
      
      
      echo "<tr class='tab_bg_2'><td>";
      echo __('Estimated duration', 'projet')."&nbsp;".__('in hours', 'projet');
      echo "</td><td>";
      $time = floor($this->fields["estimatedtime"]);
      $out = Html::formatNumber($time/HOUR_TIMESTAMP, 2);
      echo sprintf(_n('%s hour', '%s hours', $out), $out);
      echo "</td><td>" . __('Linked tickets duration', 'projet')."&nbsp;";
      Html::showToolTip(nl2br(__('Total of duration of linked tickets for project', 'projet')));
      echo "</td>";
      echo "<td>". self::getProjectDuration($ID) . "</td></tr>";
      
      
      //status
      echo "<tr class='tab_bg_1'><td>".__('State')."</td><td>";
      Dropdown::show('PluginProjetProjetState',
                  array('value'  => $this->fields["plugin_projet_projetstates_id"]));
      echo "</td>";
      echo "<td>".__('Display on the global Gantt', 'projet')."</td><td>";
      Dropdown::showYesNo("show_gantt",$this->fields["show_gantt"]);
      echo "</td></tr>";
      
      //advance
      echo "<tr class='tab_bg_1'><td>".__('Progress')."</td><td>";
      $advance=floor($this->fields["advance"]);
      echo "<select name='advance'>";
      for ($i=0;$i<101;$i+=5) {
         echo "<option value='$i' ";
         if ($advance==$i) echo "selected";
            echo " >$i</option>";
      }
      echo "</select> %";	
      echo "<td>".__('Associable to a ticket')."</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1'><td colspan='4'>".__('Comments')."</td></tr>";

      echo "<tr class='tab_bg_1'><td colspan='4'>";
      $value = $this->fields["comment"];
      if (isset($options['helpdesk_id']) && !empty($options['helpdesk_id'])) {
         $help = new $options['helpdesk_itemtype']();
         if ($help->getFromDB($options['helpdesk_id'])) {
            $value = $help->fields["content"];
         }
      }
      echo "<textarea cols='130' rows='4' name='comment' >".$value."</textarea>";

      echo "<input type='hidden' name='withtemplate' value='".$options['withtemplate']."'>";
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1'><td colspan='4'>".__('Description')."</td></tr>";

      echo "<tr class='tab_bg_1'><td colspan='4'>";
      echo "<textarea cols='130' rows='4' name='description' >".$this->fields["description"]."</textarea>";
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1 center'>";
      echo "<td>".__('Send email', 'projet')."</td><td>";
      echo "<input type='checkbox' name='send_notification' checked = true";
      echo " value='1'>";
      echo "</td>";
      
      echo "<td colspan='2'></td>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      if ((!isset($options['withtemplate']) || ($options['withtemplate'] == 0))
          && !empty($this->fields['template_name'])) {
         echo "<span class='small_space'>";
         printf(__('Created from the template %s'), $this->fields['template_name']);
         echo "</span>";
      } else {
         echo "&nbsp;";
      }
      echo "</td><td colspan='4'>";
      if (isset($options['withtemplate']) && $options['withtemplate']) {
         //TRANS: %s is the datetime of insertion
         printf(__('Created on %s'), Html::convDateTime($_SESSION["glpi_currenttime"]));
      } else {
         //TRANS: %s is the datetime of update
         printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      }
      echo "</td></tr>\n";

      echo "</tr>";
      
      $this->showFormButtons($options);
      $this->addDivForTabs();
      return true;
      
   }
   
   function listOfTemplates($target,$add=0) {
      
      $restrict = "`is_template` = '1'";
      $restrict.=getEntitiesRestrictRequest(" AND ",$this->getTable(),'','',$this->maybeRecursive());
      $restrict.=" ORDER BY `name`";
      $templates = getAllDatasFromTable($this->getTable(),$restrict);
      
      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }
         
      echo "<div align='center'><table class='tab_cadre_fixe'>";
      if ($add) {
         echo "<tr><th colspan='".(2+$colsup)."'>".__('Choose a template')." - ".self::getTypeName(2)."</th>";
      } else {
         echo "<tr><th colspan='".(2+$colsup)."'>".__('Templates')." - ".self::getTypeName(2)."</th>";
      }
      
      echo "</tr>";
      if ($add) {

         echo "<tr>";
         echo "<td colspan='".(2+$colsup)."' class='center tab_bg_1'>";
         echo "<a href=\"$target?id=-1&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;" . __('Blank Template') . "&nbsp;&nbsp;&nbsp;</a></td>";
         echo "</tr>";
      }
      
      foreach ($templates as $template) {

         $templname = $template["template_name"];
         if ($_SESSION["glpiis_ids_visible"]||empty($template["template_name"]))
         $templname.= "(".$template["id"].")";

         echo "<tr>";
         echo "<td class='center tab_bg_1'>";
         if (!$add) {
            echo "<a href=\"$target?id=".$template["id"]."&amp;withtemplate=1\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";
            
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center tab_bg_2'>";
               echo Dropdown::getDropdownName("glpi_entities",$template['entities_id']);
               echo "</td>";
            }
            echo "<td class='center tab_bg_2'>";
            Html::showSimpleForm($target,
                                    'purge',
                                    _x('button', 'Delete permanently'),
                                    array('id' => $template["id"],'withtemplate'=>1));

            echo "</td>";
            
         } else {
            echo "<a href=\"$target?id=".$template["id"]."&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";
            
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center tab_bg_2'>";
               echo Dropdown::getDropdownName("glpi_entities",$template['entities_id']);
               echo "</td>";
            }
         }
         echo "</tr>";
      }
      if (!$add) {
         echo "<tr>";
         echo "<td colspan='".(2+$colsup)."' class='tab_bg_2 center'>";
         echo "<b><a href=\"$target?withtemplate=1\">".__('Add a template...')."</a></b>";
         echo "</td>";
         echo "</tr>";
      }
      echo "</table></div>";
   }
   
   /**
    * Display a simple progress bar
    * @param $width Width of the progress bar
    * @param $percent Percent of the progress bar
    * @param $options array options :
    *            - title : string title to display (default Progesssion)
    *            - simple : display a simple progress bar (no title / only percent)
    *            - forcepadding : boolean force str_pad to force refresh (default true)
    * @return nothing
    *
    *
    **/
   static function displayProgressBar($width,$percent,$options=array()) {
      global  $CFG_GLPI;
      
      $param['simple']=false;
      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $param[$key]=$val;
         }
      }
   
      $percentwidth=floor($percent*$width/100);
      
      if ($param['simple']) {
         $output=$percent."%";
      } else {
         $output="<div class='center'><table class='tab_cadre' width='".($width+20)."px'>";

         $output.="<tr><td>
                <table><tr><td class='center' style='background:url(".$CFG_GLPI["root_doc"].
                "/pics/loader.png) repeat-x; padding: 0px;font-size: 10px;' width='".$percentwidth."px' height='12px'>";

         $output.=$percent."%";

         $output.="</td></tr></table></td>";
         $output.="</tr></table>";
         $output.="</div>";
      }
      return $output;
   }
   
   function dropdownProjet($name,$entity_restrict=-1,$used=array()) {
      global $DB,$CFG_GLPI;

      $where=" WHERE `".$this->gettable()."`.`is_deleted` = '0' 
            AND `".$this->gettable()."`.`is_template` = '0'";
      
      if (isset($entity_restrict)&&$entity_restrict>=0) {
         $where.=getEntitiesRestrictRequest("AND",$this->gettable(),'',$entity_restrict,true);
      } else {
         $where.=getEntitiesRestrictRequest("AND",$this->gettable(),'','',true);
      }
      
      if (isset($used)) {
      $where .=" AND `".$this->gettable()."`.`id` NOT IN (0";

      foreach($used as $val)
         $where .= ",$val";
      $where .= ") ";
      }

      $query = "SELECT * 
            FROM `".$this->gettable()."` 
            $where 
            ORDER BY `entities_id`,`name`";
      $result=$DB->query($query);
      $number = $DB->numrows($result);
      $i = 0;
      
      echo "<select name=\"".$name."\">";

      echo "<option value=\"0\">".Dropdown::EMPTY_VALUE."</option>";
      
      if ($DB->numrows($result)) {
         $prev=-1;
         while ($data=$DB->fetch_array($result)) {
            if ($data["entities_id"]!=$prev) {
               if ($prev>=0) {
                  echo "</optgroup>";
               }
               $prev=$data["entities_id"];
               echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
            }
            $output = $data["name"];
            echo "<option value=\"".$data["id"]."\" title=\"$output\">".substr($output,0,$_SESSION["glpidropdown_chars_limit"])."</option>";
         }
         if ($prev>=0) {
            echo "</optgroup>";
         }
      }
      echo "</select>";
      
   }
   
   static function showUsers(CommonDBTM $item) {
      global $DB,$CFG_GLPI;
      
      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!plugin_projet_haveRight('projet', 'r')) {
         return false;
      }

      if (!$item->can($item->fields['id'],'r')) {
         return false;
      }
      
      $canread = $item->can($ID,'r');
      
      $query = "SELECT `glpi_plugin_projet_projets`.* FROM `glpi_plugin_projet_projets` "
      ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_projet_projets`.`entities_id`) ";
      if ($item->getType() == 'User') {
         $query.= " WHERE `glpi_plugin_projet_projets`.`users_id` = '".$ID."' ";
      } else {
         $query.= " WHERE `glpi_plugin_projet_projets`.`groups_id` = '".$ID."' ";
      }
      $query.= "AND `glpi_plugin_projet_projets`.`is_template` = 0 "
      . getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_projets",'','',$item->maybeRecursive());
      
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }
      
      if ($number>0){
         echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php\">";
         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='".(4+$colsup)."'>".__('Responsible of projects', 'projet').":</th></tr>";
         echo "<tr><th>".__('Name')."</th>";
         if (Session::isMultiEntitiesMode()) {
            echo "<th>".__('Entity')."</th>";
         }
         echo "<th>".__('Description')."</th>";
         echo "<th>".__('Progress')."</th>";
         echo "</tr>";

         while ($data=$DB->fetch_array($result)) {

            echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";

            if ($canread && (in_array($data['entities_id'],$_SESSION['glpiactiveentities']) || $data["recursive"])) {
               echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php?id=".$data["id"]."'>".$data["name"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
               echo "</a></td>";
            } else {
               echo "<td class='center'>".$data["name"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
               echo "</td>";
            }
            if (Session::isMultiEntitiesMode()) 
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entities_id'])."</td>";
            echo "<td align='center'>".Html::resume_text($data["description"], 250)."</td>";
            echo "<td align='center'>".$data["advance"]." %</td>";
            echo "</tr>";
         }
         echo "</table></div>";
         Html::closeForm();
         echo "<br>";
      } else {
         echo "<div align='center'><table class='tab_cadre_fixe' style='text-align:center'>";
         echo "<tr><th>".__('Responsible of projects', 'projet')." - ".__('No item found');
         echo "</th></tr></table></div><br>";
      }
   }
   
   //// START PROVECTIO
   /**
   * Compute project real duration
   *
   *@param $ID ID of the project
   *@return text duration of project
   **/
   static function getProjectDuration($ID) {
      global $DB;
      
      $query = "SELECT SUM(`actiontime`) 
            FROM `glpi_tickets` 
            WHERE `items_id`  = '$ID' 
               AND `itemtype` = 'PluginProjetProjet';";
      
      if ($result = $DB->query($query)) {
         $sum=$DB->result($result,0,0);
         if (is_null($sum)) return '--';
         
         return Ticket::getActionTime($sum);
      } else {
         return '--';
      }
   }
   
   /**
   * Compute forecast duration
   *
   *@param $ID ID of the project
   *@return text duration of project
   */
   static function getProjectForecast ($ID) {
      global $DB;
      
      $query = "SELECT SUM(`actiontime`) 
            FROM `glpi_plugin_projet_tasks` 
            WHERE `plugin_projet_projets_id` = '$ID' ";
      
      if ($result = $DB->query($query)) {
         $sum=$DB->result($result,0,0);
      if (is_null($sum)) return '--';
         return Ticket::getActionTime($sum);
      } else {
         return '--';
      }
   }
   

   static function showGlobalGantt() {
      global $CFG_GLPI,$gtitle,$gdata,$nbgdata,$gconst,$gdate_begin,$gdate_end;    
      
      //not show archived projects
      $archived = " `type` = '1' ";
      $states = getAllDatasFromTable("glpi_plugin_projet_projetstates",$archived);
      
      $restrict= " `is_deleted` = '0'";
      
      $tab = array();
      if (!empty($states)) {
         foreach ($states as $state) {
            $tab[]= $state['id'];
         }
      }
      if (!empty($tab)) {
         $restrict.= "AND `plugin_projet_projetstates_id` NOT IN (".implode(',',$tab).")";
      }
      
      $restrict.= " AND `is_template` = '0'";
      $restrict.= " AND `show_gantt` = '1'";
      $restrict.=getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_projets",'','',true);
      $restrict.= " ORDER BY `date_begin` ASC";
      
      $projets = getAllDatasFromTable("glpi_plugin_projet_projets",$restrict);
      
      if (!empty($projets)) {
         echo "<div align='center'><table border='0' class='tab_cadre'>";
         echo "<tr><th align='center' >".__('Gantt', 'projet');
         echo "</th></tr>";
         
         $gdata=array();
         foreach ($projets as $projet) {
            
            if (Session::isMultiEntitiesMode())
               $gantt_p_name=Dropdown::getDropdownName("glpi_entities",$projet['entities_id'])." - ".$projet["name"];
            else
               $gantt_p_name=$projet["name"];
               
            $int = hexdec(PluginProjetProjetState::getStatusColor($projet["plugin_projet_projetstates_id"]));
            $gantt_p_bgcolor = array(0xFF & ($int >> 0x10), 0xFF & ($int >> 0x8), 0xFF & $int);
            
            $gantt_p_date_begin=date("Y-m-d");
            $gantt_p_date_end=date("Y-m-d");
            if (!empty($projet["date_begin"])) {
               $gantt_p_date_begin=$projet["date_begin"];
            }
            if (!empty($projet["date_end"])) {
               $gantt_p_date_end=$projet["date_end"];
            }
            
            $dateDepartTimestamp = strtotime($gantt_p_date_end);
            $gantt_p_date_end = date("Y-m-d", strtotime("+ 1 day", $dateDepartTimestamp));
            
            $gdata[]=array("type"=>'group',
                           "projet"=>$projet["id"],
                           "name"=>$gantt_p_name,
                           "date_begin"=>$gantt_p_date_begin,
                           "date_end"=>$gantt_p_date_end,
                           "advance"=>$projet["advance"],
                           "bg_color"=>$gantt_p_bgcolor);
         }

         echo "<tr><td width='100%'>";
         echo "<div align='center'>";
         if (!empty($gdate_begin) && !empty($gdate_end)) {
            $gtitle=$gtitle."<DateBeg> / <DateEnd>";
            $gdate_begin=date("Y",$gdate_begin)."-".date("m",$gdate_begin)."-".date("d",$gdate_begin);
            $gdate_end=date("Y",$gdate_end)."-".date("m",$gdate_end)."-".date("d",$gdate_end);
         }
         $ImgName=self::writeGantt($gtitle,$gdata,$gconst,$gdate_begin,$gdate_end);
         echo "<img src='".$CFG_GLPI["root_doc"]."/front/pluginimage.send.php?plugin=projet&amp;name=".$ImgName."&amp;clean=1' alt='Gantt'/>";//afficher graphique
         echo "</div>";
         echo "</td></tr></table></div>";
      }
   }
   
   static function showProjetTreeGantt($options=array()) {
      global $CFG_GLPI,$gtitle,$gdata,$gconst,$gdate_begin,$gdate_end;
      
      self::showProjetGantt($options);
      
      echo "<div align='center'><table border='0' class='tab_cadre'>";
      echo "<tr><th align='center' >".__('Gantt', 'projet');
      echo "</th></tr>";
      echo "<tr><td width='100%'>";
      echo"<div align='center'>";
      if (!empty($gdate_begin) && !empty($gdate_end)) {
         $gtitle=$gtitle."<DateBeg> / <DateEnd>";
         $gdate_begin=date("Y",$gdate_begin)."-".date("m",$gdate_begin)."-".date("d",$gdate_begin);
         $gdate_end=date("Y",$gdate_end)."-".date("m",$gdate_end)."-".date("d",$gdate_end);
      }
      $ImgName=self::writeGantt($gtitle,$gdata,$gconst,$gdate_begin,$gdate_end);
      echo "<img src='".$CFG_GLPI["root_doc"]."/front/pluginimage.send.php?plugin=projet&amp;name=".$ImgName."&amp;clean=1' alt='Gantt' />";//afficher graphique

      echo"</div>";
      echo "</td></tr></table></div>";
            
   }
   
   static function showProjetGantt($options=array()) {
      global $gdata;
      
      $restrict = " `id` = '".$options["plugin_projet_projets_id"]."' ";
      $restrict.= " AND `is_deleted` = '0'";
      $restrict.= " AND `is_template` = '0'";
      
      $projets = getAllDatasFromTable("glpi_plugin_projet_projets",$restrict);
      
      $prefixp = $options["prefixp"];
      
      if (!empty($projets)) {
         
         foreach ($projets as $projet) {
            
            if ($options["parent"] > 0)
               $prefixp.= "-";
            //nom
            if ($options["parent"] > 0)
               $gantt_p_name= $prefixp." ".$projet["name"];
            else
               $gantt_p_name= $projet["name"];

            
            $int = hexdec(PluginProjetProjetState::getStatusColor($projet["plugin_projet_projetstates_id"]));
            $gantt_p_bgcolor = array(0xFF & ($int >> 0x10), 0xFF & ($int >> 0x8), 0xFF & $int);
            
            $gantt_p_date_begin=date("Y-m-d");
            $gantt_p_date_end=date("Y-m-d");
            if (!empty($projet["date_begin"])) {
               $gantt_p_date_begin=$projet["date_begin"];
            }
            if (!empty($projet["date_end"])) {
               $gantt_p_date_end=$projet["date_end"];
            }
            
            $dateDepartTimestamp = strtotime($gantt_p_date_end);
            $gantt_p_date_end = date("Y-m-d", strtotime("+ 1 day", $dateDepartTimestamp));
            
            $gdata[]=array("type"=>'group',
                           "projet"=>$options["plugin_projet_projets_id"],
                           "name"=>$gantt_p_name,
                           "date_begin"=>$gantt_p_date_begin,
                           "date_end"=>$gantt_p_date_end,
                           "advance"=>$projet["advance"],
                           "bg_color"=>$gantt_p_bgcolor);

            PluginProjetTask::showTaskTreeGantt(array('plugin_projet_projets_id'=>$projet["id"]));
            
            $condition = " `plugin_projet_projets_id_2` = '".$projet["id"]."' ";
            $projets_projets = getAllDatasFromTable("glpi_plugin_projet_projets_projets",$condition);
            
            $restrictchild= " `is_deleted` = '0'";
            $restrictchild.= " AND `is_template` = '0'";
            $tab = array();
            if (!empty($projets_projets)) {
               foreach ($projets_projets as $projets_projet) {
                  $tab[]= $projets_projet['plugin_projet_projets_id_1'];
               }
            }
            if (!empty($tab)) {
               $restrictchild.= " AND `id` IN (".implode(',',$tab).")";
            }

            $restrictchild.= " ORDER BY `plugin_projet_projetstates_id`,`date_begin` DESC";

            $childs = getAllDatasFromTable("glpi_plugin_projet_projets",$restrictchild);

            if (!empty($childs) && !empty($tab)) {
               foreach ($childs as $child) {
                  $params=array('plugin_projet_projets_id'=>$child["id"],
                                 'parent'=>1,
                                 'prefixp'=>$prefixp);
                  self::showProjetGantt($params);
                  
               }
            }
         }
      }        
   }
   
   static function dropAccent($chaine) {

      $chaine=utf8_decode($chaine);
      $chaine=strtr( $chaine, 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ', 'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn' );
      return $chaine;

   }

   static function writeGantt($title,$gdata,$gconst,$gantt_date_begin,$gantt_date_end) {
      global $CFG_GLPI;
      
      include_once (GLPI_ROOT."/plugins/projet/inc/gantt.class.php");
      
      if (isset($gantt_date_begin)) $definitions['limit']['start'] = mktime(0,0,0,substr($gantt_date_begin, 5, 2),substr($gantt_date_begin, 8, 2),substr($gantt_date_begin, 0, 4));

      if (isset($gantt_date_end))   $definitions['limit']['end']   = mktime(0,0,0,substr($gantt_date_end, 5, 2),substr($gantt_date_end, 8, 2),substr($gantt_date_end, 0, 4));

      $definitions['locale'] = substr($CFG_GLPI["language"],0,2);
      $definitions['today']['data']= time();        
      $definitions['title_string'] = self::dropAccent((strlen($title)>60) ? substr($title,0,58)."..." : $title);

      for ($i=0 ; $i<count($gdata) ; $i++) {            

         if ($gdata[$i]["type"]=='group') { // Groupe 
            $definitions['groups']['group'][$gdata[$i]["projet"]]['name'] = self::dropAccent((strlen($gdata[$i]["name"])>60) ? substr($gdata[$i]["name"],0,58)."..." : $gdata[$i]["name"]);
            $definitions['groups']['group'][$gdata[$i]["projet"]]['bg_color'] = $gdata[$i]["bg_color"];
            $definitions['groups']['group'][$gdata[$i]["projet"]]['start'] = mktime(0,0,0,substr($gdata[$i]["date_begin"], 5, 2),substr($gdata[$i]["date_begin"], 8, 2),substr($gdata[$i]["date_begin"], 0, 4));
            $definitions['groups']['group'][$gdata[$i]["projet"]]['end'] = mktime(0,0,0,substr($gdata[$i]["date_end"], 5, 2),substr($gdata[$i]["date_end"], 8, 2),substr($gdata[$i]["date_end"], 0, 4));
            if (isset($gdata[$i]["advance"])) 
               $definitions['groups']['group'][$gdata[$i]["projet"]]['progress'] = $gdata[$i]["advance"];
            
         } else if ($gdata[$i]["type"]=='phase') { // Tache
            $definitions['groups']['group'][$gdata[$i]["projet"]]['phase'][$gdata[$i]["task"]] = $gdata[$i]["task"];
            $definitions['planned']['phase'][$gdata[$i]["task"]]['name'] = self::dropAccent((strlen($gdata[$i]["name"])>60) ? substr($gdata[$i]["name"],0,58)."..." : $gdata[$i]["name"]);

            $definitions['planned']['phase'][$gdata[$i]["task"]]['start'] = mktime(0,0,0,substr($gdata[$i]["begin"], 5, 2),substr($gdata[$i]["begin"], 8, 2),substr($gdata[$i]["begin"], 0, 4));
            $definitions['planned']['phase'][$gdata[$i]["task"]]['end'] = mktime(0,0,0,substr($gdata[$i]["end"], 5, 2),substr($gdata[$i]["end"], 8, 2),substr($gdata[$i]["end"], 0, 4));
            $definitions['planned']['phase'][$gdata[$i]["task"]]['bg_color']=$gdata[$i]["bg_color"];
            /*if ($gdata[$i]["planned"]!='1') {
               $definitions['planned_adjusted']['phase'][$gdata[$i]["task"]]['start'] = mktime(0,0,0,substr($gdata[$i]["date_begin"], 5, 2),substr($gdata[$i]["date_begin"], 8, 2),substr($gdata[$i]["date_begin"], 0, 4));
               $definitions['planned_adjusted']['phase'][$gdata[$i]["task"]]['end'] = mktime(0,0,0,substr($gdata[$i]["date_end"], 5, 2),substr($gdata[$i]["date_end"], 8, 2),substr($gdata[$i]["date_end"], 0, 4));
               $definitions['planned_adjusted']['phase'][$gdata[$i]["task"]]['color']=$gdata[$i]["bg_color"];
            }*/
            //if (isset($gdata[$i]["realstart"])) $definitions['real']['phase'][$gdata[$i]["projet"]]['start'] = mktime(0,0,0,substr($gdata[$i][9], 5, 2),substr($gdata[$i][9], 8, 2),substr($gdata[$i][9], 0, 4));
            //if (isset($gdata[$i]["realend"])) $definitions['real']['phase'][$gdata[$i]["projet"]]['end'] = mktime(0,0,0,substr($gdata[$i][10], 5, 2),substr($gdata[$i][10], 8, 2),substr($gdata[$i][10], 0, 4));
            if (isset($gdata[$i]["advance"])) 
                  $definitions['progress']['phase'][$gdata[$i]["task"]]['progress']=$gdata[$i]["advance"];
                  
         } else if ($gdata[$i]["type"]=='milestone') { // Point Important
            $definitions['groups']['group'][$gdata[$i]["projet"]]['phase'][$gdata[$i]["task"]]=$gdata[$i]["task"];
            $definitions['milestone']['phase'][$gdata[$i]["task"]]['title']=self::dropAccent((strlen($gdata[$i]["name"])>27) ? substr($gdata[$i]["name"],0,24)."..." : $gdata[$i]["name"]);
            $definitions['milestone']['phase'][$gdata[$i]["task"]]['data']= mktime(0,0,0,substr($gdata[$i]["date_begin"], 5, 2),substr($gdata[$i]["date_begin"], 8, 2),substr($gdata[$i]["date_begin"], 0, 4));
         } else if ($gdata[$i]["type"]=='dependency') { // Dependance
            $definitions['dependency'][$gdata[$i]["projet"]]['type']= 1;
            $definitions['dependency'][$gdata[$i]["projet"]]['phase_from']=$gdata[$i]["date_begin"];
            $definitions['dependency'][$gdata[$i]["projet"]]['phase_to']=$gdata[$i]["name"];
         }
      }

      $ImgName = sprintf("gantt-%08x.png", rand());

      $definitions['image']['type']= 'png'; 
      $definitions['image']['filename'] = GLPI_PLUGIN_DOC_DIR."/projet/".$ImgName;

      new gantt($definitions);

      return $ImgName;

   }
   
   
   //Massive action
   function getSpecificMassiveActions($checkitem = NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($isadmin) {
         $actions['Install'] = __('Associate');
         $actions['Desinstall'] = __('Dissociate');

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
            if ($input['itemtype'] == 'PluginProjetProjet') {
               foreach ($input["item"] as $key => $val) {
                  if ($val == 1) {
                     
                     $PluginProjetTask = new PluginProjetTask();
                     $restrict = "`plugin_projet_projets_id` = '".$key."'";
                     $tasks = getAllDatasFromTable("glpi_plugin_projet_tasks");
                     if (!empty($tasks)) {
                        foreach ($tasks as $task) {

                           $PluginProjetTask->getFromDB($task["id"]);
                           $tasktype = PluginProjetTaskType::transfer($PluginProjetTask->fields["plugin_projet_tasktypes_id"],
                                                                           $input['entities_id']);
                           if ($tasktype > 0) {
                              $values["id"] = $task["id"];
                              $values["plugin_projet_tasktypes_id"] = $tasktype;
                              $PluginProjetTask->update($values);
                           }
                           $values["id"] = $task["id"];
                           $values["entities_id"] = $input['entities_id'];
                           $PluginProjetTask->update($values);
                        }
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
         case "Install" :
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  $values = array('plugin_projet_projets_id' => $key,
                     'items_id'      => $input["item_item"],
                     'itemtype'      => $input['typeitem']);
                  if ($projet_item->add($values)) {
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
                  if ($projet_item->deleteItemByProjetAndItem($key,$input['item_item'],$input['typeitem'])) {
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
   
   /**
    * Show for PDF an projet
    * 
    * @param $pdf object for the output
    * @param $ID of the projet
    */
   function show_PDF ($pdf) {
      
      $pdf->setColumnsSize(100);
      $col1 = '<b>'.__('ID').' '.$this->fields['id'].'</b>';
      $pdf->displayTitle($col1);
      
      $pdf->displayLine(
         '<b><i>'.__('Name').' :</i></b> '.$this->fields['name']);
      $pdf->setColumnsSize(50,50);
      $pdf->displayLine(
         '<b><i>'.__('User').' :</i></b> '.Html::clean(getUserName($this->fields["users_id"])),
         '<b><i>'.__('Group').' :</i></b> '.Html::clean(Dropdown::getDropdownName('glpi_groups',$this->fields["groups_id"])));
      
      $pdf->displayLine(
         '<b><i>'.__('Start date').' :</i></b> '.Html::convDate($this->fields["date_begin"]),
         '<b><i>'.__('End date').' :</i></b> '.Html::convDate($this->fields["date_end"]));
      
      $pdf->displayLine(
         '<b><i>'.__('State').' :</i></b> '.Html::clean(Dropdown::getDropdownName("glpi_plugin_projet_projetstates",$this->fields['plugin_projet_projetstates_id'])),
         '<b><i>'.__('Progress').' :</i></b> '.PluginProjetProjet::displayProgressBar('100',$this->fields["advance"],array("simple"=>true)));

      $pdf->setColumnsSize(100);

      $pdf->displayText('<b><i>'.__('Comments').' :</i></b>', $this->fields['comment']);
      
      $pdf->setColumnsSize(100);

      $pdf->displayText('<b><i>'.__('Description').' :</i></b>', $this->fields['description']);
      
      $pdf->displaySpace();
   }
   
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {
      
      if ($item->getType()=='PluginProjetProjet') {
         if ($tab == 1) {
            
            PluginProjetProjet_Projet::pdfHierarchyForProjet($pdf, $item);
            PluginProjetProjet_Projet::pdfHierarchyForProjet($pdf, $item, 1);
            
         } else if ($tab == 2) {
            
            self::pdfGanttForProjet($pdf, $item);
            
         } else if ($tab == 3) {
            
            PluginProjetProjet_Item::pdfForProjet($pdf, $item);
         }
         
      } else {
         return false;
      }
      return true;
   }
   
   /**
    * Show for PDF an projet
    * 
    * @param $pdf object for the output
    * @param $ID of the projet
    */
   static function pdfGanttForProjet(PluginPdfSimplePDF $pdf, PluginProjetProjet $appli) {
       global $CFG_GLPI,$gtitle,$gdata,$gconst,$gdate_begin,$gdate_end;
      
      $ID = $appli->fields['id'];

      if (!$appli->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_projet_haveRight("projet","r")) {
         return false;
      }
      
      //nom
      $gantt_p_name= $appli->fields["name"];
      //type de gantt    
      $int = hexdec(PluginProjetProjetState::getStatusColor($appli->fields["plugin_projet_projetstates_id"]));
      $gantt_p_bgcolor = array(0xFF & ($int >> 0x10), 0xFF & ($int >> 0x8), 0xFF & $int);
            
      $gantt_p_date_begin=date("Y-m-d");
      $gantt_p_date_end=date("Y-m-d");
      if (!empty($appli->fields["date_begin"])) {
         $gantt_p_date_begin=$appli->fields["date_begin"];
      }
      if (!empty($appli->fields["date_end"])) {
         $gantt_p_date_end=$appli->fields["date_end"];
      }
      
      $gdata[]=array("type"=>'group',
                     "projet"=>$ID,
                     "name"=>$gantt_p_name,
                     "date_begin"=>$gantt_p_date_begin,
                     "date_end"=>$gantt_p_date_end,
                     "advance"=>$appli->fields["advance"],
                     "bg_color"=>$gantt_p_bgcolor);

      PluginProjetTask::showTaskTreeGantt(array('plugin_projet_projets_id'=>$ID));
      
      if (!empty($gdate_begin) && !empty($gdate_end)) {
         $gtitle=$gtitle."<DateBeg> / <DateEnd>";
         $gdate_begin=date("Y",$gdate_begin)."-".date("m",$gdate_begin)."-".date("d",$gdate_begin);
         $gdate_end=date("Y",$gdate_end)."-".date("m",$gdate_end)."-".date("d",$gdate_end);
      }
      $ImgName=self::writeGantt($gtitle,$gdata,$gconst,$gdate_begin,$gdate_end);

      $image = GLPI_PLUGIN_DOC_DIR."/projet/".$ImgName;

      $pdf->newPage();

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.__('Gantt', 'projet').'</b>');
      $size = GetImageSize($image);
      $src_w = $size[0];
      $src_h = $size[1];
      $pdf->addPngFromFile($image,$src_w/2,$src_h/2);
      $pdf->displaySpace();
      unlink($image);

   }
   
   static function commonListHeader($output_type=HTML_OUTPUT, $canedit) {

      // New Line for Header Items Line
      echo Search::showNewLine($output_type);
      // $show_sort if
      $header_num = 1;

      $items = array();

      $items[__('Name')] = "glpi_plugin_projet_projets.name";
      if (Session::isMultiEntitiesMode()) {
         $items[__('Entity')] = "glpi_entities.completename";
      }
      $items[__('Description')] = "glpi_plugin_projet_projets.description";
      $items[__('Progress')]   = "glpi_plugin_projet_projets.advance";
      $items[__('Start date')]       = "glpi_plugin_projet_projets.date_begin";
      $items[__('End date')]   = "glpi_plugin_projet_projets.date_end";
      
      foreach ($items as $key => $val) {
         $issort = 0;
         $link = "";
         echo Search::showHeaderItem($output_type,$key,$header_num,$link);
      }
      if ($canedit) {
         echo "<th>&nbsp;</th>";
      }
      // End Line for column headers
      echo Search::showEndLine($output_type);
   }
}

?>