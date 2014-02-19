<?php
/*
 * @version $Id: rulerightcollection.class.php 20949 2013-05-14 13:37:19Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

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


/// Rule collection class for Rights management
class RuleRightCollection extends RuleCollection {

   // From RuleCollection
   public $stop_on_first_match = false;
   static public $right        = 'rule_ldap';
   public $orderby             = "name";
   public $menu_option         = 'right';

   // Specific ones
   /// Array containing results : entity + right
   var $rules_entity_rights = array();
   /// Array containing results : only entity
   var $rules_entity = array();
   /// Array containing results : only right
   var $rules_rights = array();


   function getTitle() {
      return __('Authorizations assignment rules');
   }


   /**
    * @see RuleCollection::cleanTestOutputCriterias()
   */
   function cleanTestOutputCriterias(array $output) {

      if (isset($output["_rule_process"])) {
         unset($output["_rule_process"]);
      }
      return $output;
   }


   /**
    * @see RuleCollection::showTestResults()
   */
   function showTestResults($rule, array $output, $global_result) {

      $actions = $rule->getActions();
      echo "<tr><th colspan='4'>" . __('Rule results') . "</th></tr>";
      echo "<tr class='tab_bg_2'>";
      echo "<td class='center' colspan='2'>".__('Validation')."</td><td colspan='2'>".
           "<span class='b'>".Dropdown::getYesNo($global_result)."</span></td>";

      if (isset($output["_ldap_rules"]["rules_entities"])) {
         echo "<tr class='tab_bg_2'>";
         echo "<td class='center' colspan='4'>".__('Entities assignment')."</td>";
         foreach ($output["_ldap_rules"]["rules_entities"] as $entities) {
            foreach ($entities as $entity) {
               $this->displayActionByName("entity", $entity[0]);
               if (isset($entity[1])) {
                  $this->displayActionByName("recursive", $entity[1]);
               }
            }
         }
      }

      if (isset($output["_ldap_rules"]["rules_rights"])) {
         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='4' class='center'>".__('Rights assignment')."</td>";
         foreach ($output["_ldap_rules"]["rules_rights"] as $val) {
            $this->displayActionByName("profile", $val[0]);
         }
      }

      if (isset($output["_ldap_rules"]["rules_entities_rights"])) {
         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='4' class='center'>".__('Rights and entities assignment')."</td>";
         foreach ($output["_ldap_rules"]["rules_entities_rights"] as $val) {
            if (is_array($val[0])) {
               foreach ($val[0] as $tmp) {
                  $this->displayActionByName("entity", $tmp);
               }
            } else {
               $this->displayActionByName("entity", $val[0]);
            }
            if (isset($val[1])) {
               $this->displayActionByName("profile", $val[1]);
            }
            if (isset($val[2])) {
               $this->displayActionByName("is_recursive",$val[2]);
            }
         }
      }

      if (isset($output["_ldap_rules"])) {
         unset($output["_ldap_rules"]);
      }
      foreach ($output as $criteria => $value) {
         if (isset($actions[$criteria])) { // ignore _* fields
            echo "<tr class='tab_bg_2'>";
            echo "<td class='center'>".$actions[$criteria]["name"]."</td>";
            echo "<td class='center'>".$rule->getActionValue($criteria,
                                                             $actions[$criteria]['action_type'],
                                                             $value);
            echo "</td></tr>\n";
         }
      }
      echo "</tr>";
   }


   /**
    * Display action using its name
    *
    * @param $name   action name
    * @param $value  default value
   **/
   function displayActionByName($name, $value) {

      echo "<tr class='tab_bg_2'>";
      switch ($name) {
         case "entity" :
            echo "<td class='center'>".__('Entity')." </td>\n";
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $value)."</td>";
            break;

         case "profile" :
            echo "<td class='center'>"._n('Profile', 'Profiles', 2)." </td>\n";
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_profiles", $value)."</td>";
            break;

         case "is_recursive" :
            echo "<td class='center'>".__('Recursive')." </td>\n";
            echo "<td class='center'>".Dropdown::getYesNo($value)."</td>";
            break;
      }
      echo "</tr>";
   }


   /**
    * Get all the fields needed to perform the rule
    *
    * @see RuleCollection::getFieldsToLookFor()
   **/
   function getFieldsToLookFor() {
      global $DB;

      $params = array();
      $sql = "SELECT DISTINCT `value`
              FROM `glpi_rules`,
                   `glpi_rulecriterias`,
                   `glpi_rulerightparameters`
              WHERE `glpi_rules`.`sub_type` = 'RuleRight'
                    AND `glpi_rulecriterias`.`rules_id` = `glpi_rules`.`id`
                    AND `glpi_rulecriterias`.`criteria` = `glpi_rulerightparameters`.`value`";
      $result = $DB->query($sql);

      while ($param = $DB->fetch_assoc($result)) {
         //Dn is alwsays retreived from ldap : don't need to ask for it !
         if ($param["value"] != "dn") {
            $params[] = Toolbox::strtolower($param["value"]);
         }
      }
      return $params;
   }


   /**
    * Get the attributes needed for processing the rules
    *
    * @see RuleCollection::prepareInputDataForProcess()
    *
    * @param $input  input datas
    * @param $params extra parameters given
    *
    * @return an array of attributes
   **/
   function prepareInputDataForProcess($input, $params) {

      $rule_parameters = array();
      //LDAP type method
      if ($params["type"] == "LDAP") {
         //Get all the field to retrieve to be able to process rule matching
         $rule_fields = $this->getFieldsToLookFor();

         //Get all the datas we need from ldap to process the rules
         $sz         = @ldap_read($params["connection"], $params["userdn"], "objectClass=*",
                                  $rule_fields);
         $rule_input = AuthLDAP::get_entries_clean($params["connection"], $sz);

         if (count($rule_input)) {
            if (isset($input)) {
               $groups = $input;
            } else {
               $groups = array();
            }
            $rule_input = $rule_input[0];
            //Get all the ldap fields
            $fields = $this->getFieldsForQuery();
            foreach ($fields as $field) {
               switch(Toolbox::strtoupper($field)) {
                  case "LDAP_SERVER" :
                     $rule_parameters["LDAP_SERVER"] = $params["ldap_server"];
                     break;

                  case "GROUPS" :
                     foreach ($groups as $group) {
                        $rule_parameters["GROUPS"][] = $group;
                     }
                     break;

                  default :
                     if (isset($rule_input[$field])) {
                        if (!is_array($rule_input[$field])) {
                           $rule_parameters[$field] = $rule_input[$field];
                        } else {
                           if (count($rule_input[$field])) {
                              foreach ($rule_input[$field] as $key => $val) {
                                 if ($key !== 'count') {
                                    $rule_parameters[$field][] = $val;
                                 }
                              }
                           }
                        }
                     }
               }
            }
            return $rule_parameters;
         }
         return $rule_input;
      } else if ($params["type"] == "SSO") {
         $rule_parameters["MAIL_EMAIL"]  = $params["email"];
         $rule_parameters["LOGIN"]       = $params["login"];
         return $rule_parameters;
      }
      //IMAP/POP login method
      $rule_parameters["MAIL_SERVER"] = $params["mail_server"];
      $rule_parameters["MAIL_EMAIL"]  = $params["email"];
      return $rule_parameters;
   }


   /**
    * Get the list of fields to be retreived to process rules
   **/
   function getFieldsForQuery() {

      $rule      = new RuleRight();
      $criterias = $rule->getCriterias();

      $fields = array();
      foreach ($criterias as $criteria) {
         if (!is_array($criteria)) {
            continue;
         }
         if (isset($criteria['virtual']) && $criteria['virtual']) {
            $fields[] = $criteria['id'];
         } else {
            $fields[] = $criteria['field'];
         }
      }
      return $fields;
   }

}
?>
