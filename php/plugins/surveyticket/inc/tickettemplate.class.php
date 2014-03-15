<?php

/*
   ------------------------------------------------------------------------
   Surveyticket
   Copyright (C) 2012-2013 by the Surveyticket plugin Development Team.

   https://forge.indepnet.net/projects/surveyticket
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Surveyticket plugin project.

   Surveyticket plugin is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Surveyticket plugin is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Surveyticket plugin. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Surveyticket plugin
   @author    David Durieux
   @copyright Copyright (c) 2012-2013 Surveyticket plugin team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/surveyticket
   @since     2012

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginSurveyticketTicketTemplate extends CommonDBTM {
   
   static function getTypeName($nb = 0) {
      return __('Template');
   }



   static function canCreate() {
      return PluginSurveyticketProfile::haveRight("config", 'w');
   }


   static function canView() {
      return PluginSurveyticketProfile::haveRight("config", 'r');
   }
   
   
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->getType()=='PluginSurveyticketSurvey') {
         return _n('Ticket template', 'Ticket templates', 1);
      }
      return '';
   }



   /**
    * Display content of tab
    *
    * @param CommonGLPI $item
    * @param integer $tabnum
    * @param interger $withtemplate
    *
    * @return boolean TRUE
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if ($item->getType() == 'PluginSurveyticketSurvey') {
         $psTicketTemplate = new self();
         $psTicketTemplate->showTicketTemplate($item->getID());
      }
      return TRUE;
   }
   
   
   
   function showTicketTemplate($items_id) {
      global $CFG_GLPI;
      
      $ticketTemplate = new TicketTemplate();
      
      echo "<form method='post' name='form_addquestion' action='".$CFG_GLPI['root_doc'].
             "/plugins/surveyticket/front/tickettemplate.form.php'>";

      echo "<table class='tab_cadre' width='700'>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Ticket template')."&nbsp;:</td>";
      echo "<td>";
      $a_used = array();

      
      Dropdown::show("TicketTemplate", 
                     array("name" => "tickettemplates_id",
                           "used" => $a_used)
                    );
      echo "</td>";
      echo "<td>".__('Type')."&nbsp;:</td>";
      echo "<td>";
      Ticket::dropdownType("type");
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Simplified interface')."&nbsp;:</td>";
      echo "<td>";
      Dropdown::showYesNo("is_helpdesk");
      echo "</td>";
      
      echo "<td>".__('Standard interface')."&nbsp;:</td>";
      echo "<td>";
      Dropdown::showYesNo("is_central");
      echo "</td>";
      echo "</tr>";
      
      echo "<tr>";
      echo "<td class='tab_bg_2 top' colspan='4'>";
      echo "<input type='hidden' name='plugin_surveyticket_surveys_id' value='".$items_id."'>";
      echo "<div class='center'>";
      echo "<input type='submit' name='add' value=\"".__('Add')."\" class='submit'>";
      echo "</div></td></tr>";
         
      echo "</table>";
      Html::closeForm();
      
      
      // list templates
      
      
      echo "<table class='tab_cadre_fixe'>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<th>";
      echo __('Ticket template');
      echo "</th>";
      echo "<th>";
      echo __('Type');
      echo "</th>";
      echo "<th>";
      echo __('Simplified interface');
      echo "</th>";
      echo "<th>";
      echo __('Standard interface');
      echo "</th>";
      echo "<th>";
      echo "</th>";
      echo "</tr>";    
      
      $_tickettempaltes = $this->find("`plugin_surveyticket_surveys_id`='".$items_id."'");
      foreach ($_tickettempaltes as $data) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>";
         $ticketTemplate->getFromDB($data['tickettemplates_id']);
         echo $ticketTemplate->getLink(1);
         echo "</td>";
         echo "<td>";
         echo Ticket::getTicketTypeName($data['type']);
         echo "</td>";
         echo "<td>";
         echo Dropdown::getYesNo($data['is_helpdesk']);
         echo "</td>";   
         echo "<td>";
         echo Dropdown::getYesNo($data['is_central']);
         echo "</td>";
         
         echo "<td align='center'>";
         echo "<form method='post' name='form_delettickettemplate' action='".$CFG_GLPI['root_doc'].
             "/plugins/surveyticket/front/tickettemplate.form.php'>";
         echo "<input type='hidden' name='id' value='".$data['id']."'>";
         echo "<input type='submit' name='delete' value=\""._sx('button', 'Delete permanently')."\" class='submit'>";
         Html::closeForm();
         echo "</td>";
         echo "</tr>";    
      }
      echo "</table>";
   }
}

?>