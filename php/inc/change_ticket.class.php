<?php
/*
 * @version $Id: change_ticket.class.php 22657 2014-02-12 16:17:54Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

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

/** @file
* @brief
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class Change_Ticket extends CommonDBRelation{

   // From CommonDBRelation
   static public $itemtype_1   = 'Change';
   static public $items_id_1   = 'changes_id';

   static public $itemtype_2   = 'Ticket';
   static public $items_id_2   = 'tickets_id';



   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }


   static function getTypeName($nb=0) {
      return _n('Link Ticket/Change','Links Ticket/Change',$nb);
   }


   /**
    * Get search function for the class
    *
    * @return array of search option
   **/
   function getSearchOptions() {
      return parent::getSearchOptions();
   }


   /**
    * Show tickets for a change
    *
    * @param $change Change object
   **/
   static function showForChange(Change $change) {
      global $DB, $CFG_GLPI;

      $ID = $change->getField('id');
      if (!$change->can($ID,'r')) {
         return false;
      }

      $canedit = $change->can($ID,'w');
      $rand    = mt_rand();
      echo Toolbox::getItemTypeFormURL(__CLASS__)."'>";

      $query = "SELECT DISTINCT `glpi_changes_tickets`.`id` AS linkID,
                                `glpi_tickets`.*
                FROM `glpi_changes_tickets`
                LEFT JOIN `glpi_tickets`
                     ON (`glpi_changes_tickets`.`tickets_id` = `glpi_tickets`.`id`)
                WHERE `glpi_changes_tickets`.`changes_id` = '$ID'
                ORDER BY `glpi_tickets`.`name`";
      $result = $DB->query($query);

      $tickets = array();
      $used    = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $tickets[$data['id']] = $data;
            $used[$data['id']]    = $data['id'];
         }
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='changeticket_form$rand' id='changeticket_form$rand' method='post'
                action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='3'>".__('Add a ticket')."</th></tr>";

         echo "<tr class='tab_bg_2'><td class='right'>";
         echo "<input type='hidden' name='changes_id' value='$ID'>";
         Ticket::dropdown(array('used'        => $used,
                                'entity'      => $change->getEntityID(),
                                'entity_sons' => $change->isRecursive()));
         echo "</td><td class='center'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>";

         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }


      echo "<div class='spaced'>";

      if ($canedit && $numrows) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('num_displayed'  => $numrows);
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
      }

      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr>";
      if ($canedit && $numrows) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }
      echo "<th>".__('Title')."</th>";
      if ($change->isRecursive()) {
         echo "<th>".__('Entity')."</th>";
      }
      echo "</tr>";

      $used = array();
      if ($numrows) {
         Session::initNavigateListItems('Ticket',
                                 //TRANS : %1$s is the itemtype name,
                                 //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'), Change::getTypeName(1),
                                                $change->fields["name"]));

         foreach ($tickets as $data) {
            Session::addToNavigateListItems('Ticket', $data["id"]);
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["linkID"]);
               echo "</td>";
            }
            echo "<td><a href='".Toolbox::getItemTypeFormURL('Ticket')."?id=".$data['id']."'>".
                      $data["name"]."</a></td>";
            if ($change->isRecursive()) {
               echo "<td>".Dropdown::getDropdownName('glpi_entities',$data["entities_id"])."</td>";
            }
            echo "</tr>";
         }
      }


      echo "</table>";

      if ($canedit && $numrows) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }


   /**
    * Show changes for a ticket
    *
    * @param $ticket Ticket object
   **/
   static function showForTicket(Ticket $ticket) {
      global $DB, $CFG_GLPI;

      $ID = $ticket->getField('id');
      if (!$ticket->can($ID,'r')) {
         return false;
      }

      $canedit = $ticket->can($ID,'w');
      $rand    = mt_rand();
      echo Toolbox::getItemTypeFormURL(__CLASS__)."'>";

      $query = "SELECT DISTINCT `glpi_changes_tickets`.`id` AS linkID,
                                `glpi_changes`.*
                FROM `glpi_changes_tickets`
                LEFT JOIN `glpi_changes`
                     ON (`glpi_changes_tickets`.`changes_id` = `glpi_changes`.`id`)
                WHERE `glpi_changes_tickets`.`tickets_id` = '$ID'
                ORDER BY `glpi_changes`.`name`";
      $result = $DB->query($query);

      $changes = array();
      $used = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $changes[$data['id']] = $data;
            $used[$data['id']] = $data['id'];
         }
      }
      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='changeticket_form$rand' id='changeticket_form$rand' method='post'
               action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='3'>".__('Add a change')."</th></tr>";
         echo "<tr class='tab_bg_2'><td>";
         echo "<input type='hidden' name='tickets_id' value='$ID'>";
         Change::dropdown(array('used'        => $used,
                                'entity'      => $ticket->getEntityID()));
         echo "</td><td class='center'>";
         echo "<input type='submit' name='add' value=\""._sx('button','Add')."\" class='submit'>";
         echo "</td><td>";
         echo "<a href='".Toolbox::getItemTypeFormURL('Change')."?tickets_id=$ID'>";
         _e('Create a change from this ticket');
         echo "</a>";

         echo "</td></tr></table>";
         Html::closeForm();
         echo "</div>";
      }


      echo "<div class='spaced'>";
      if ($canedit && $numrows) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('num_displayed'  => $numrows);
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
      }
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr>";
      if ($canedit && $numrows) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }
      echo "<th>".__('Title')."</th>";
      echo "</tr>";



      $used = array();
      if ($numrows) {
         Session::initNavigateListItems('Change',
         //TRANS : %1$s is the itemtype name, %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'), Ticket::getTypeName(1),
                                                $ticket->fields["name"]));

         foreach ($changes as $data) {
            $used[$data['id']] = $data['id'];
            Session::addToNavigateListItems('Change', $data["id"]);
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["linkID"]);
               echo "</td>";
            }
            echo "<td><a href='".Toolbox::getItemTypeFormURL('Change')."?id=".$data['id']."'>".
                      $data["name"]."</a></td>";
            echo "</tr>";
         }
      }

      echo "</table>";
      if ($canedit && $numrows) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";

   }


}
?>
