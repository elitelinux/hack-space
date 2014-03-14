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

class PluginIdeaboxIdeabox extends CommonDBTM {
   
   static function getTypeName($nb = 0) {

      return _n('Idea box', 'Ideas box', $nb, 'ideabox');
   }
   
   static function canCreate() {
      return plugin_ideabox_haveRight('ideabox', 'w');
   }

   static function canView() {
      return plugin_ideabox_haveRight('ideabox', 'r');
   }
   
	//clean if ideabox are deleted
	function cleanDBonPurge() {

		$temp = new PluginIdeaboxComment();
      $temp->deleteByCriteria(array('plugin_ideabox_ideaboxes_id' => $this->fields['id']));
	}
	
	//define header form
	function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab('PluginIdeaboxComment', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }
	
	function getSearchOptions() {

      $tab = array();
    
      $tab['common']             = self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();
      
      $tab[2]['table']           = 'glpi_users';
      $tab[2]['field']           = 'name';
      $tab[2]['name']            = __('Author');
      $tab[2]['datatype']        = 'dropdown';
      $tab[2]['massiveaction']   = false;
      
      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'comment';
      $tab[3]['name']            = __('Description');
      $tab[3]['datatype']        = 'text';
      
      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'date_idea';
      $tab[4]['name']            = __('Date of submission', 'ideabox');
      $tab[4]['datatype']        = 'datetime';
      $tab[4]['massiveaction']   = false;
      
      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'is_helpdesk_visible';
      $tab[5]['name']            = __('Associable to a ticket');
      $tab[5]['datatype']        = 'bool';
      
      $tab[30]['table']          = $this->getTable();
      $tab[30]['field']          = 'id';
      $tab[30]['name']           = __('ID');
      $tab[30]['datatype']       = 'number';

      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['datatype']       = 'dropdown';
    
		return $tab;
   }
	
	function post_addItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["use_mailing"]) {
         NotificationEvent::raiseEvent("new",$this);
      }
   }
   
	function prepareInputForUpdate($input) {

		if (isset($input['users_id'])) unset($input['users_id']);
		
		return $input;
	}
	
	function post_updateItem($history=1) {
      global $CFG_GLPI;
      
      if (count($this->updates)) {
         if ($CFG_GLPI["use_mailing"]) {
            NotificationEvent::raiseEvent('update',$this);
         }
      }
   }
   
   function pre_deleteItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["use_mailing"]) {
         NotificationEvent::raiseEvent("delete",$this);
      }
      
      return true;
   }
	
	function showForm ($ID, $options=array()) {
      global $CFG_GLPI;

		if (!$this->canView()) return false;

		if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $this->showTabs($options);
      $options['colspan'] = 1;
      $this->showFormHeader($options);
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Associable to a ticket') . "</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Author')."</td><td>";
      if ($ID > 0) {
         echo getusername($this->fields["users_id"]);
      } else {
         echo getusername(Session::getLoginUserID());
      }
      if (!empty($this->fields["date_idea"]) && !empty($ID)) {
         echo " - ".__('Date of submission', 'ideabox').": ".Html::convDateTime($this->fields["date_idea"]);
      }
      echo "<input type='hidden' name='users_id' value='".Session::getLoginUserID()."'>";
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td colspan = '2'>";
      echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
      echo __('Description')."</td></tr>";
      echo "<tr><td class='center'>";
      echo "<textarea cols='125' rows='14' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td></tr></table>";
      echo "</td>";
      
      echo "</tr>";

      if (empty($this->fields["date_idea"])) {
         echo "<input type='hidden' name='date_idea' value=\"".$_SESSION["glpi_currenttime"]."\">";
      }
      
      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
	}
	
	//Massive action
   function getSpecificMassiveActions($checkitem = NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         if ($isadmin) {
            if (Session::haveRight('transfer', 'r')
               && Session::isMultiEntitiesMode()
            ) {
               $actions['Transfert'] = __('Transfer');
            }
         }
      }
      return $actions;
   }

   function showSpecificMassiveActionsParameters($input = array()) {

      switch ($input['action']) {
         case "Transfert" :
            Dropdown::show('Entity');
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='" . __s('Post') . "'>";
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
         
      switch ($input['action']) {
         case "Transfert" :
            if ($input['itemtype'] == 'PluginIdeaboxIdeabox') {
               foreach ($input["item"] as $key => $val) {
                  if ($val == 1) {

                     $this->getFromDB($key);

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
      }
      return $res;
   }
}

?>