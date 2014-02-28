<?php
/*
 * @version $Id: ruledictionnarysoftware.class.php 22657 2014-02-12 16:17:54Z moyo $
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

/**
* Rule class store all information about a GLPI rule :
*   - description
*   - criterias
*   - actions
**/
class RuleDictionnarySoftware extends Rule {

   var $additional_fields_for_dictionnary = array('manufacturer');

   // From Rule
   static public $right    = 'rule_dictionnary_software';
   public $can_sort        = true;


   /**
    * @see Rule::getTitle()
   **/
   function getTitle() {
      //TRANS: plural for software
      return __('Dictionnary of software');
   }


   /**
    * @see Rule::maxActionsCount()
   **/
   function maxActionsCount() {
      return 4;
   }


   /**
    * @see Rule::getCriterias()
   **/
   function getCriterias() {

      static $criterias = array();

      if (count($criterias)) {
         return $criterias;
      }

      $criterias['name']['field']         = 'name';
      $criterias['name']['name']          = _n('Software', 'Software', 1);
      $criterias['name']['table']         = 'glpi_softwares';

      $criterias['manufacturer']['field'] = 'name';
      $criterias['manufacturer']['name']  = __('Publisher');
      $criterias['manufacturer']['table'] = 'glpi_manufacturers';

      $criterias['entities_id']['field']  = 'completename';
      $criterias['entities_id']['name']   = __('Entity');
      $criterias['entities_id']['table']  = 'glpi_entities';
      $criterias['entities_id']['type']   = 'dropdown';

      return $criterias;
   }


   /**
    * @see Rule::getActions()
   **/
   function getActions() {

      $actions                                  = array();

      $actions['name']['name']                  = _n('Software', 'Software', 1);
      $actions['name']['force_actions']         = array('assign', 'regex_result');

      $actions['_ignore_import']['name']        = __('To be unaware of import');
      $actions['_ignore_import']['type']        = 'yesonly';

      $actions['version']['name']               = _n('Version', 'Versions',1);
      $actions['version']['force_actions']      = array('assign','regex_result',
                                                        'append_regex_result');

      $actions['manufacturer']['name']          = __('Publisher');
      $actions['manufacturer']['table']         = 'glpi_manufacturers';
      $actions['manufacturer']['force_actions'] = array('append_regex_result', 'assign','regex_result');

      $actions['is_helpdesk_visible']['name']   = __('Associable to a ticket');
      $actions['is_helpdesk_visible']['table']  = 'glpi_softwares';
      $actions['is_helpdesk_visible']['type']   = 'yesno';

      $actions['new_entities_id']['name']       = __('Entity');
      $actions['new_entities_id']['table']      = 'glpi_entities';
      $actions['new_entities_id']['type']       = 'dropdown';

      return $actions;
   }


   /**
    * @see Rule::addSpecificParamsForPreview()
   **/
   function addSpecificParamsForPreview($params) {

      if (isset($_POST["version"])) {
         $params["version"] = $_POST["version"];
      }
      return $params;
   }


   /**
    * @see Rule::showSpecificCriteriasForPreview()
   **/
   function showSpecificCriteriasForPreview($fields) {

      if (isset($this->fields['id'])) {
         $this->getRuleWithCriteriasAndActions($this->fields['id'],0,1);
      }

      //if there's a least one action with type == append_regex_result, then need to display
      //this field as a criteria
      foreach ($this->actions as $action) {
         if ($action->fields["action_type"] == "append_regex_result") {
            $value = (isset($fields[$action->fields['field']])?$fields[$action->fields['field']]:'');
            //Get actions for this type of rule
            $actions = $this->getActions();

            //display the additionnal field
            echo "<tr class='tab_bg_1'>";
            echo "<td>".$this->fields['match']."</td>";
            echo "<td>".$actions[$action->fields['field']]['name']."</td>";
            echo "<td><input type='text' name='version' value='$value'></td></tr>";
         }
      }
   }


}
?>
