<?php
/*
 * @version $Id: ruleticket.class.php 22657 2014-02-12 16:17:54Z moyo $
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


class RuleTicket extends Rule {

   // From Rule
   static public $right    = 'entity_rule_ticket';
   public $can_sort        = true;


   function getTitle() {
      return __('Business rules for tickets');
   }


   static function canCreate() {
      return Session::haveRight('entity_rule_ticket', 'w');
   }


   static function canView() {
      return Session::haveRight('entity_rule_ticket', 'r');
   }


   function maybeRecursive() {
      return true;
   }


   function isEntityAssign() {
      return true;
   }


   function canUnrecurs() {
      return true;
   }


   function maxActionsCount() {
      return count($this->getActions());
   }

   /**
    * display title for action form
   **/
   function getTitleAction() {

      parent::getTitleAction();
      $showwarning = false;
      if (isset($this->actions)) {
         foreach ($this->actions as $key => $val) {
            if (isset($val->fields['field'])) {
               if (in_array($val->fields['field'], array('impact', 'urgency'))) {
                  $showwarning = true;
               }
            }
         }
      }
      if ($showwarning) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><td>".
               __('Urgency or impact used in actions, think to add Prioriy: recompute action if needed.').
               "</td></tr>\n";
         echo "</table><br>";
      }
   }
   
   /**
    * @param $params
   **/
   function addSpecificParamsForPreview($params) {

      if (!isset($params["entities_id"])) {
         $params["entities_id"] = $_SESSION["glpiactive_entity"];
      }
      return $params;
   }


   /**
    * Function used to display type specific criterias during rule's preview
    *
    * @param $fields fields values
   **/
   function showSpecificCriteriasForPreview($fields) {

      $entity_as_criteria = false;
      foreach ($this->criterias as $criteria) {
         if ($criteria->fields['criteria'] == 'entities_id') {
            $entity_as_criteria = true;
            break;
         }
      }
      if (!$entity_as_criteria) {
         echo "<input type='hidden' name='entities_id' value='".$_SESSION["glpiactive_entity"]."'>";
      }
   }


   /**
    * @param $output
    * @param $params
   **/
   function executeActions($output,$params) {

      if (count($this->actions)) {
         foreach ($this->actions as $action) {
            switch ($action->fields["action_type"]) {
               case "send" :
                  $ticket = new Ticket();
                  if ($ticket->getFromDB($output['id'])) {
                     NotificationEvent::raiseEvent('recall', $ticket);
                  }
                  break;

               case "add_validation" :
                  if (isset($output['_add_validation']) && !is_array($output['_add_validation'])) {
                     $output['_add_validation'] = array($output['_add_validation']);
                  }
                  switch ($action->fields['field']) {
                     case 'users_id_validate_requester_supervisor' :
                        $output['_add_validation'][] = 'requester_supervisor';
                        break;

                     case 'users_id_validate_assign_supervisor' :
                        $output['_add_validation'][] = 'assign_supervisor';
                        break;

                     default :
                        $output['_add_validation'][] = $action->fields["value"];
                        break;
                  }
                  break;

               case "assign" :
                  $output[$action->fields["field"]] = $action->fields["value"];
                  break;

               case "append" :
                  $actions = $this->getActions();
                  $value   = $action->fields["value"];
                  if (isset($actions[$action->fields["field"]]["appendtoarray"])
                      && isset($actions[$action->fields["field"]]["appendtoarrayfield"])) {
                     $value = $actions[$action->fields["field"]]["appendtoarray"];
                     $value[$actions[$action->fields["field"]]["appendtoarrayfield"]]
                            = $action->fields["value"];
                  }
                  $output[$actions[$action->fields["field"]]["appendto"]][] = $value;
                  break;

               case 'fromuser' :
                  if ($action->fields['field'] == 'locations_id') {
                     $output['locations_id'] = $output['users_locations'];
                  }
                  break;

               case 'fromitem' :
                  if ($action->fields['field'] == 'locations_id') {
                     $output['locations_id'] = $output['items_locations'];
                  }
                  break;

               case 'compute' :
                  // Value could be not set (from test)
                  $urgency = (isset($output['urgency'])?$output['urgency']:3);
                  $impact  = (isset($output['impact'])?$output['impact']:3);
                  // Apply priority_matrix from config
                  $output['priority'] = Ticket::computePriority($urgency, $impact);
                  break;

               case "affectbyip" :
               case "affectbyfqdn" :
               case "affectbymac" :
                  if (!isset($output["entities_id"])) {
                     $output["entities_id"] = $params["entities_id"];
                  }
                  if (isset($this->regex_results[0])) {
                     $regexvalue = RuleAction::getRegexResultById($action->fields["value"],
                                                                  $this->regex_results[0]);
                  } else {
                     $regexvalue = $action->fields["value"];
                  }
                  /// TODO : check, because, previous version also propose deleted and template items
                  switch ($action->fields["action_type"]) {
                     case "affectbyip" :
                        $result = IPAddress::getUniqueItemByIPAddress($regexvalue,
                                                                      $output["entities_id"]);
                        break;

                     case "affectbyfqdn" :
                        $result= FQDNLabel::getUniqueItemByFQDN($regexvalue,
                                                                $output["entities_id"]);
                        break;

                     case "affectbymac" :
                        $result = NetworkPortInstantiation::getUniqueItemByMac($regexvalue,
                                                                               $output["entities_id"]);
                        break;

                     default:
                        $result = array();
                  }
                  if (!empty($result)) {
                     $output["itemtype"] = $result["itemtype"];
                     $output["items_id"] = $result["id"];
                  }
                  break;
            }
         }
      }
      return $output;
   }


   /**
    * @param $output
   **/
   function preProcessPreviewResults($output) {

      $output = parent::preProcessPreviewResults($output);
      return Ticket::showPreviewAssignAction($output);
   }


   function getCriterias() {

      static $criterias = array();

      if (count($criterias)) {
         return $criterias;
      }

      $criterias['name']['table']                     = 'glpi_tickets';
      $criterias['name']['field']                     = 'name';
      $criterias['name']['name']                      = __('Title');
      $criterias['name']['linkfield']                 = 'name';

      $criterias['content']['table']                  = 'glpi_tickets';
      $criterias['content']['field']                  = 'content';
      $criterias['content']['name']                   = __('Description');
      $criterias['content']['linkfield']              = 'content';

      $criterias['itilcategories_id']['table']        = 'glpi_itilcategories';
      $criterias['itilcategories_id']['field']        = 'name';
      $criterias['itilcategories_id']['name']         = __('Category');
      $criterias['itilcategories_id']['linkfield']    = 'itilcategories_id';
      $criterias['itilcategories_id']['type']         = 'dropdown';

      $criterias['type']['table']                     = 'glpi_tickets';
      $criterias['type']['field']                     = 'type';
      $criterias['type']['name']                      = __('Type');
      $criterias['type']['linkfield']                 = 'type';
      $criterias['type']['type']                      = 'dropdown_tickettype';


      $criterias['_users_id_requester']['table']      = 'glpi_users';
      $criterias['_users_id_requester']['field']      = 'name';
      $criterias['_users_id_requester']['name']       = __('Requester');
      $criterias['_users_id_requester']['linkfield']  = '_users_id_requester';
      $criterias['_users_id_requester']['type']       = 'dropdown_users';

      $criterias['users_locations']['table']          = 'glpi_locations';
      $criterias['users_locations']['field']          = 'completename';
      $criterias['users_locations']['name']           = __('Requester location');
      $criterias['users_locations']['linkfield']      = 'users_locations';
      $criterias['users_locations']['type']           = 'dropdown';

      $criterias['items_locations']['table']          = 'glpi_locations';
      $criterias['items_locations']['field']          = 'completename';
      $criterias['items_locations']['name']           = __('Item location');
      $criterias['items_locations']['linkfield']      = 'items_locations';
      $criterias['items_locations']['type']           = 'dropdown';

      $criterias['locations_id']['table']             = 'glpi_locations';
      $criterias['locations_id']['field']             = 'completename';
      $criterias['locations_id']['name']              = __('Ticket location');
      $criterias['locations_id']['linkfield']         = 'locations_id';
      $criterias['locations_id']['type']              = 'dropdown';

      $criterias['_groups_id_requester']['table']     = 'glpi_groups';
      $criterias['_groups_id_requester']['field']     = 'completename';
      $criterias['_groups_id_requester']['name']      = __('Requester group');
      $criterias['_groups_id_requester']['linkfield'] = '_groups_id_requester';
      $criterias['_groups_id_requester']['type']      = 'dropdown';

      $criterias['_users_id_assign']['table']         = 'glpi_users';
      $criterias['_users_id_assign']['field']         = 'name';
      $criterias['_users_id_assign']['name']          = __('Technician');
      $criterias['_users_id_assign']['linkfield']     = '_users_id_assign';
      $criterias['_users_id_assign']['type']          = 'dropdown_users';

      $criterias['_groups_id_assign']['table']        = 'glpi_groups';
      $criterias['_groups_id_assign']['field']        = 'completename';
      $criterias['_groups_id_assign']['name']         = __('Technician group');
      $criterias['_groups_id_assign']['linkfield']    = '_groups_id_assign';
      $criterias['_groups_id_assign']['type']         = 'dropdown';
      $criterias['_groups_id_assign']['condition']    = '`is_assign`';

      $criterias['_suppliers_id_assign']['table']     = 'glpi_suppliers';
      $criterias['_suppliers_id_assign']['field']     = 'name';
      $criterias['_suppliers_id_assign']['name']      = __('Assigned to a supplier');
      $criterias['_suppliers_id_assign']['linkfield'] = '_suppliers_id_assign';
      $criterias['_suppliers_id_assign']['type']      = 'dropdown';

      $criterias['_users_id_observer']['table']       = 'glpi_users';
      $criterias['_users_id_observer']['field']       = 'name';
      $criterias['_users_id_observer']['name']        = __('Watcher');
      $criterias['_users_id_observer']['linkfield']   = '_users_id_observer';
      $criterias['_users_id_observer']['type']        = 'dropdown_users';

      $criterias['_groups_id_observer']['table']      = 'glpi_groups';
      $criterias['_groups_id_observer']['field']      = 'completename';
      $criterias['_groups_id_observer']['name']       = __('Watcher group');
      $criterias['_groups_id_observer']['linkfield']  = '_groups_id_observer';
      $criterias['_groups_id_observer']['type']       = 'dropdown';

      $criterias['requesttypes_id']['table']          = 'glpi_requesttypes';
      $criterias['requesttypes_id']['field']          = 'name';
      $criterias['requesttypes_id']['name']           = __('Request source');
      $criterias['requesttypes_id']['linkfield']      = 'requesttypes_id';
      $criterias['requesttypes_id']['type']           = 'dropdown';

      $criterias['itemtype']['table']                 = 'glpi_tickets';
      $criterias['itemtype']['field']                 = 'itemtype';
      $criterias['itemtype']['name']                  = __('Item type');
      $criterias['itemtype']['linkfield']             = 'itemtype';
      $criterias['itemtype']['type']                  = 'dropdown_tracking_itemtype';

      $criterias['entities_id']['table']              = 'glpi_entities';
      $criterias['entities_id']['field']              = 'name';
      $criterias['entities_id']['name']               = __('Entity');
      $criterias['entities_id']['linkfield']          = 'entities_id';
      $criterias['entities_id']['type']               = 'dropdown';

      $criterias['urgency']['name']                   = __('Urgency');
      $criterias['urgency']['type']                   = 'dropdown_urgency';

      $criterias['impact']['name']                    = __('Impact');
      $criterias['impact']['type']                    = 'dropdown_impact';

      $criterias['priority']['name']                  = __('Priority');
      $criterias['priority']['type']                  = 'dropdown_priority';

      $criterias['_mailgate']['table']                = 'glpi_mailcollectors';
      $criterias['_mailgate']['field']                = 'name';
      $criterias['_mailgate']['name']                 = __('Mails receiver');
      $criterias['_mailgate']['linkfield']            = '_mailgate';
      $criterias['_mailgate']['type']                 = 'dropdown';

      $criterias['_x-priority']['name']               = __('X-Priority email header');
      $criterias['_x-priority']['table']              = '';
      $criterias['_x-priority']['type']               = 'text';

      return $criterias;
   }


   function getActions() {

      $actions                                              = array();

      $actions['itilcategories_id']['name']                 = __('Category');
      $actions['itilcategories_id']['type']                 = 'dropdown';
      $actions['itilcategories_id']['table']                = 'glpi_itilcategories';

      $actions['type']['name']                              = __('Type');
      $actions['type']['table']                             = 'glpi_tickets';
      $actions['type']['type']                              = 'dropdown_tickettype';

      $actions['_users_id_requester']['name']               = __('Requester');
      $actions['_users_id_requester']['type']               = 'dropdown_users';
      $actions['_users_id_requester']['force_actions']      = array('assign', 'append');
      $actions['_users_id_requester']['permitseveral']      = array('append');
      $actions['_users_id_requester']['appendto']           = '_additional_requesters';
      $actions['_users_id_requester']['appendtoarray']      = array('use_notification' => 1);
      $actions['_users_id_requester']['appendtoarrayfield'] = 'users_id';

      $actions['_groups_id_requester']['name']              = __('Requester group');
      $actions['_groups_id_requester']['type']              = 'dropdown';
      $actions['_groups_id_requester']['table']             = 'glpi_groups';
      $actions['_groups_id_requester']['force_actions']     = array('assign', 'append');
      $actions['_groups_id_requester']['permitseveral']     = array('append');
      $actions['_groups_id_requester']['appendto']          = '_additional_groups_requesters';


      $actions['_users_id_assign']['name']                  = __('Technician');
      $actions['_users_id_assign']['type']                  = 'dropdown_assign';
      $actions['_users_id_assign']['force_actions']         = array('assign', 'append');
      $actions['_users_id_assign']['permitseveral']         = array('append');
      $actions['_users_id_assign']['appendto']              = '_additional_assigns';
      $actions['_users_id_assign']['appendtoarray']         = array('use_notification' => 1);
      $actions['_users_id_assign']['appendtoarrayfield']    = 'users_id';

      $actions['_groups_id_assign']['table']                = 'glpi_groups';
      $actions['_groups_id_assign']['name']                 = __('Technician group');
      $actions['_groups_id_assign']['type']                 = 'dropdown';
      $actions['_groups_id_assign']['condition']            = '`is_assign`';
      $actions['_groups_id_assign']['force_actions']        = array('assign', 'append');
      $actions['_groups_id_assign']['permitseveral']        = array('append');
      $actions['_groups_id_assign']['appendto']             = '_additional_groups_assigns';

      $actions['_suppliers_id_assign']['table']             = 'glpi_suppliers';
      $actions['_suppliers_id_assign']['name']              = __('Assigned to a supplier');
      $actions['_suppliers_id_assign']['type']              = 'dropdown';
      $actions['_suppliers_id_assign']['force_actions']     = array('assign', 'append');
      $actions['_suppliers_id_assign']['permitseveral']     = array('append');
      $actions['_suppliers_id_assign']['appendto']          = '_additional_suppliers_assigns';

      $actions['_users_id_observer']['name']                = __('Watcher');
      $actions['_users_id_observer']['type']                = 'dropdown_users';
      $actions['_users_id_observer']['force_actions']       = array('assign', 'append');
      $actions['_users_id_observer']['permitseveral']       = array('append');
      $actions['_users_id_observer']['appendto']            = '_additional_observers';
      $actions['_users_id_observer']['appendtoarray']       = array('use_notification' => 1);
      $actions['_users_id_observer']['appendtoarrayfield']  = 'users_id';

      $actions['_groups_id_observer']['table']              = 'glpi_groups';
      $actions['_groups_id_observer']['name']               = __('Watcher group');
      $actions['_groups_id_observer']['type']               = 'dropdown';
      $actions['_groups_id_observer']['force_actions']      = array('assign', 'append');
      $actions['_groups_id_observer']['permitseveral']      = array('append');
      $actions['_groups_id_observer']['appendto']           = '_additional_groups_observers';

      $actions['urgency']['name']                           = __('Urgency');
      $actions['urgency']['type']                           = 'dropdown_urgency';

      $actions['impact']['name']                            = __('Impact');
      $actions['impact']['type']                            = 'dropdown_impact';

      $actions['priority']['name']                          = __('Priority');
      $actions['priority']['type']                          = 'dropdown_priority';
      $actions['priority']['force_actions']                 = array('assign', 'compute');

      $actions['status']['name']                            = __('Status');
      $actions['status']['type']                            = 'dropdown_status';

      $actions['affectobject']['name']                      = __('Associated element');
      $actions['affectobject']['type']                      = 'text';
      $actions['affectobject']['force_actions']             = array('affectbyip', 'affectbyfqdn',
                                                                    'affectbymac');

      $actions['slas_id']['table']                          = 'glpi_slas';
      $actions['slas_id']['name']                           = __('SLA');
      $actions['slas_id']['type']                           = 'dropdown';

      $actions['users_id_validate']['name']                 = __('Send an approval request');
      $actions['users_id_validate']['type']                 = 'dropdown_users_validate';
      $actions['users_id_validate']['force_actions']        = array('add_validation');

      $actions['users_id_validate_requester_supervisor']['name']
                                          = __('Approval request to requester group supervisor');
      $actions['users_id_validate_requester_supervisor']['type']
                                          = 'yesno';
      $actions['users_id_validate_requester_supervisor']['force_actions']
                                          = array('add_validation');

      $actions['users_id_validate_assign_supervisor']['name']
                                          = __('Approval request to technician group supervisor');
      $actions['users_id_validate_assign_supervisor']['type']
                                          = 'yesno';
      $actions['users_id_validate_assign_supervisor']['force_actions']
                                          = array('add_validation');

      $actions['locations_id']['name']                      = __('Location');
      $actions['locations_id']['type']                      = 'dropdown';
      $actions['locations_id']['table']                     = 'glpi_locations';
      $actions['locations_id']['force_actions']             = array('assign', 'fromuser', 'fromitem');

      return $actions;
   }

}
?>