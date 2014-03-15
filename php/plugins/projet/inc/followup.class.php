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
 
if (! defined ( 'GLPI_ROOT' )) {
   die ( "Sorry. You can't access directly to this file" );
}
class PluginProjetFollowup extends CommonDBTM {

   public $dohistory = true;
   
   static function getTypeName($nb = 0) {

      return _n('Followup', 'Followups', $nb);
   }
   
   
   static function canCreate() {
      return plugin_projet_haveRight ( 'projet', 'w' );
   }
   
   
   static function canView() {
      return plugin_projet_haveRight ( 'projet', 'r' );
   }
   
   /**
    * Display followup's tab for each users
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return array string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
   
      if (! $withtemplate) {
         if ($item->getType () == 'PluginProjetProjet') {
            if ($_SESSION ['glpishow_count_on_tabs']) {
               return self::createTabEntry (self::getTypeName(2), countElementsInTable ($this->getTable(), "`plugin_projet_projets_id` = '" . $item->getID () . "'" ) );
            }
            return self::getTypeName(2);
         }
      }
      return '';
   }
   
   /**
    * Display tab's content for each users
    * @static
    * @param CommonGLPI $item 
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      $fup = new PluginProjetFollowup();
      $fup->showSummary($item);
      return true;
   }
   
   
   /**
    * Show the current ticketfollowup summary
    *
    * @param $item Projet
    * object
    */
   function showSummary($item) {
      global $DB, $CFG_GLPI;
      
      $tID = $item->fields['id'];

      $tmp = array ('plugin_projet_projets_id' => $tID);
      $canadd = PluginProjetProjet_Item::isProjetParticipant($tID) 
                        && plugin_projet_haveRight('projet', 'w');

      $query = "SELECT `id`, `date`
      FROM `glpi_plugin_projet_followups`
      WHERE `plugin_projet_projets_id` = '$tID'
      ORDER BY `date` DESC";
      $result = $DB->query ($query);
      
      $rand = mt_rand ();
      
      
      echo "<h1>".__('Project followup', 'projet')."</h1><br/>";
      
      if ($canadd) {
         echo "<div id='viewfollowup" . $tID . "$rand'></div>\n";
      }
      if ($canadd) {
         echo "<script type='text/javascript' >\n";
         echo "function viewAddFollowup" . $item->fields ['id'] . "$rand() {\n";
         $params = array (
               'type' => __CLASS__,
               'parenttype' => 'PluginProjetProjet',
               'plugin_projet_projets_id' => $item->fields ['id'],
               'id' => - 1 
         );
         Ajax::updateItemJsCode ( "viewfollowup" . $item->fields ['id'] . "$rand", "../ajax/viewsubitem.php", $params );
         echo "};";
         echo "</script>\n";
         echo "<div class='center'>" . "<a href='javascript:viewAddFollowup" . $item->fields ['id'] . "$rand();'>";
         echo __('Add a new followup') . "</a></div><br>\n";
      }
      
      if ($DB->numrows ( $result ) == 0) {
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'>";
         echo "<th class='b'>" . __('Add a new followup') . "</th></tr></table>";
      } else {
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr>";
         echo "<th>" . __('Content') . "</th>";
         echo "<th>" . __('Date') . "</th>";
         echo "<th>" . __('Writer') . "</th>";
         
         while ( $data = $DB->fetch_array ( $result ) ) {
            if ($this->getFromDB ( $data ['id'] )) {
               $this->showInFollowupSummary ( $item, $rand);
            }
         }
         echo "</table>";
      }
   }
   
   
   /**
    * Permet d'afficher une ligne de suivi dans la liste de suivi
    */
   function showInFollowupSummary(PluginProjetProjet $projet, $rand) {
      global $DB, $CFG_GLPI;
      
      $canedit = PluginProjetProjet_Item::isProjetParticipant($projet->fields ["id"]) 
                        && plugin_projet_haveRight('projet', 'w');
      
      
      echo "<tr class='tab_bg_2' ".($canedit ? "style='cursor:pointer' onClick=\"viewEditFollowup" . $this->fields ['plugin_projet_projets_id'] . $this->fields ['id'] . "$rand();\"" : '') 
               . " id='viewfollowup".$this->fields ['plugin_projet_projets_id'] . $this->fields ["id"] . "$rand'>";
      
      echo "<td class='left'>".nl2br($this->fields ["content"]) ."</td>";
      
      echo "<td>";
      if ($canedit) {
         echo "\n<script type='text/javascript' >\n";
         echo "function viewEditFollowup" . $this->fields ['plugin_projet_projets_id'] . $this->fields ["id"] . "$rand() {\n";
         $params = array (
               'type' => __CLASS__,
               'parenttype' => 'PluginProjetProjet',
               'plugin_projet_projets_id' => $this->fields ["plugin_projet_projets_id"],
               'id' => $this->fields ["id"] 
         );
         Ajax::updateItemJsCode ( "viewfollowup" . $this->fields ['plugin_projet_projets_id'] . "$rand", "../ajax/viewsubitem.php", $params );
         echo "};";
         echo "</script>\n";
      }
      echo Html::convDateTime ( $this->fields ["date"] ) . "</td>";
      echo "<td>" . getUserName ( $this->fields ["users_id"] ) . "</td>";
      echo "</tr>\n";
   }
   
   
   
   
   function prepareInputForUpdate($input) {
      
      if (! isset ( $input ["users_id"] )) {
         $input ["users_id"] = Session::getLoginUserID ();
      }
      
      $input ["date"] = $_SESSION ["glpi_currenttime"];
      
      return $input; 
   }
   
   
   function prepareInputForAdd($input) {
      
      if (! isset ( $input ["users_id"] )) {
         $input ["users_id"] = Session::getLoginUserID ();
      }
      
      $input ["date"] = $_SESSION ["glpi_currenttime"];
      
      return $input;
   }
   
   function post_updateItem($history=1){
      global $CFG_GLPI;
      
      if (!isset($this->input["withtemplate"]) || (isset($this->input["withtemplate"]) && $this->input["withtemplate"]!=1)) {
         if ($CFG_GLPI["use_mailing"]) {
            $options = array('followups_id' => $this->fields["id"]);
            $projet = new PluginProjetProjet();
            if ($projet->getFromDB($this->fields["plugin_projet_projets_id"])) {
               NotificationEvent::raiseEvent("update_followup",$projet,$options);  
            }
         }
      }
   }
   
   
    function post_addItem() {
      global $CFG_GLPI;
      
      if (!isset($this->input["withtemplate"]) || (isset($this->input["withtemplate"]) && $this->input["withtemplate"]!=1)) {
         if ($CFG_GLPI["use_mailing"]) {
            $projet = new PluginProjetProjet();
            $options = array('followups_id' => $this->fields["id"]);
            if ($projet->getFromDB($this->fields["plugin_projet_projets_id"])) {
               NotificationEvent::raiseEvent("add_followup",$projet,$options);  
            }
         }
      }
   }
   
   function pre_deleteItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["use_mailing"] && isset($this->input['delete'])) {
         $projet = new PluginProjetProjet();
         $options = array('followups_id' => $this->fields["id"]);
         if ($projet->getFromDB($this->fields["plugin_projet_projets_id"])) {
            NotificationEvent::raiseEvent("delete_followup",$projet,$options);  
         }
      }
      return true;
   }
   
   /**
    * Print the intervention form
    *
    * @param $ID integer
    *        	ID of the item
    * @param $options array
    *        	- target filename : where to go when done.
    *        	- withtemplate boolean : template or basic item
    *        	
    * @return Nothing (display)
    *        
    */
   function showForm($ID, $options = array()) {
      global $DB, $CFG_GLPI;
      
      if (isset ( $options ['parent'] ) && ! empty ( $options ['parent'] )) {
         $projet = $options ['parent'];
      }
      
      if ($ID > 0) {
         $this->check ( $ID, 'r' );
      } else {
         // Create item
         $input = array (
               'plugin_projet_projets_id' => $projet->getField ( 'id' ) 
         );
         $this->check ( - 1, 'w', $input );
      }
      
      $canedit = $this->canCreate();
      
      if ($canedit) {
         $options ['colspan'] = 1;
         $this->showFormHeader($options);
         
         echo "<tr class='tab_bg_1'>";
         echo "<td class='middle right'>" . __('Content'). "</td>";
         echo "<td class='left middle'><textarea name='content' cols='110' rows='10'>" . $this->fields ["content"] . "</textarea>";
         
         echo "<input type='hidden' name='plugin_projet_projets_id' value='" . $this->fields ["plugin_projet_projets_id"] . "'>";
         
         echo "</td></tr>";
         if ($this->fields ["date"]) {
            echo "<tr class='tab_bg_1'><td class='middle right'>" . __('Date') . "</td>";
            echo "<td>" . Html::convDateTime ( $this->fields ["date"] ). "</td>";
         }
         
         $this->showFormButtons ($options);
      }

      return true;
   }
   

   // If followup deleted
   static function purgeFollowup($item) {
      
      $temp = new PluginProjetFollowup();
      $temp->deleteByCriteria(array('plugin_projet_projets_id' => $item->getField('id')));
      
   }
}

?>