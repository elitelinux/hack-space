<?php
/*
 * @version $Id: config.class.php 22657 2014-02-12 16:17:54Z moyo $
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
 *  Config class
**/
class Config extends CommonDBTM {

   // From CommonGLPI
   protected $displaylist         = false;

   // From CommonDBTM
   public $auto_message_on_action = false;
   public $showdebug              = true;


   static function getTypeName($nb=0) {
      return __('Setup');
   }


   static function canCreate() {
      return false;
   }


   static function canUpdate() {
      return Session::haveRight('config', 'w');
   }


   static function canView() {
      return Session::haveRight('config', 'r');
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab(__CLASS__, $ong, $options);

      return $ong;
   }


   function showForm($ID, $options=array()) {

      $this->check(1, 'r');
      $this->showTabs($options);
      $this->addDivForTabs();
   }


   /**
    * Prepare input datas for updating the item
    *
    * @see CommonDBTM::prepareInputForUpdate()
    *
    * @param $input array of datas used to update the item
    *
    * @return the modified $input array
   **/
   function prepareInputForUpdate($input) {

      if (isset($input['allow_search_view']) && !$input['allow_search_view']) {
         // Global search need "view"
         $input['allow_search_global'] = 0;
      }
      if (isset($input["smtp_passwd"])) {
         if (empty($input["smtp_passwd"])) {
            unset($input["smtp_passwd"]);
         } else {
            $input["smtp_passwd"] = Toolbox::encrypt(stripslashes($input["smtp_passwd"]), GLPIKEY);
         }
      }

      if (isset($input["_blank_smtp_passwd"]) && $input["_blank_smtp_passwd"]) {
         $input['smtp_passwd'] = '';
      }

      if (isset($input["proxy_passwd"])) {
         if (empty($input["proxy_passwd"])) {
            unset($input["proxy_passwd"]);
         } else {
            $input["proxy_passwd"] = Toolbox::encrypt(stripslashes($input["proxy_passwd"]),
                                                      GLPIKEY);
         }
      }

      if (isset($input["_blank_proxy_passwd"]) && $input["_blank_proxy_passwd"]) {
         $input['proxy_passwd'] = '';
      }

      // Manage DB Slave process
      if (isset($input['_dbslave_status'])) {
         $already_active = DBConnection::isDBSlaveActive();

         if ($input['_dbslave_status']) {
            DBConnection::changeCronTaskStatus(true);

            if (!$already_active) {
               // Activate Slave from the "system" tab
               DBConnection::createDBSlaveConfig();

            } else if (isset($input["_dbreplicate_dbhost"])) {
               // Change parameter from the "replicate" tab
               DBConnection::saveDBSlaveConf($input["_dbreplicate_dbhost"],
                                             $input["_dbreplicate_dbuser"],
                                             $input["_dbreplicate_dbpassword"],
                                             $input["_dbreplicate_dbdefault"]);
            }
         }

         if (!$input['_dbslave_status'] && $already_active) {
            DBConnection::deleteDBSlaveConfig();
            DBConnection::changeCronTaskStatus(false);
         }
      }

      // Matrix for Impact / Urgence / Priority
      if (isset($input['_matrix'])) {
         $tab = array();

         for ($urgency=1 ; $urgency<=5 ; $urgency++) {
            for ($impact=1 ; $impact<=5 ; $impact++) {
               $priority               = $input["_matrix_${urgency}_${impact}"];
               $tab[$urgency][$impact] = $priority;
            }
         }

         $input['priority_matrix'] = exportArrayToDB($tab);
         $input['urgency_mask']    = 0;
         $input['impact_mask']     = 0;

         for ($i=1 ; $i<=5 ; $i++) {
            if ($input["_urgency_${i}"]) {
               $input['urgency_mask'] += (1<<$i);
            }

            if ($input["_impact_${i}"]) {
               $input['impact_mask'] += (1<<$i);
            }
         }
      }
      return $input;
   }


   /**
    * Print the config form for display
    *
    * @return Nothing (display)
   **/
   function showFormDisplay() {
      global $CFG_GLPI;

      if (!Session::haveRight("config", "w")) {
         return false;
      }

      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\" method='post'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<input type='hidden' name='id' value='" . $CFG_GLPI["id"] . "'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='4'>" . __('General setup') . "</th></tr>";
      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('URL of the application') . "</td>";
      echo "<td colspan='3'><input type='text' name='url_base' size='80' value='".$CFG_GLPI["url_base"]."'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td> " . __('Text in the login box') . "</td>";
      echo "<td colspan='3'>";
      echo "<textarea cols='70' rows='4' name='text_login'>".$CFG_GLPI["text_login"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td> " . __('Allow FAQ anonymous access') . "</td><td>";
      Dropdown::showYesNo("use_public_faq", $CFG_GLPI["use_public_faq"]);
      echo "</td><td>" . __('Simplified interface help link') . "</td>";
      echo "<td><input size='22' type='text' name='helpdesk_doc_url' value='" .
                 $CFG_GLPI["helpdesk_doc_url"] . "'></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Default search results limit (page)')."</td><td>";
      Dropdown::showInteger("list_limit_max", $CFG_GLPI["list_limit_max"], 5, 200, 5);
      echo "</td><td>" . __('Standard interface help link') . "</td>";
      echo "<td><input size='22' type='text' name='central_doc_url' value='" .
                 $CFG_GLPI["central_doc_url"] . "'></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Default characters limit (summary text boxes)') . "</td><td>";
      Dropdown::showInteger('cut', $CFG_GLPI["cut"], 50, 500, 50);
      echo "</td><td>" . __('Default url length limit') . "</td><td>";
      Dropdown::showInteger('url_maxlength', $CFG_GLPI["url_maxlength"], 20, 80, 5);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'><td>" .__('Default decimals limit') . "</td><td>";
      Dropdown::showInteger("decimal_number", $CFG_GLPI["decimal_number"], 1, 4);
      echo "</td><td>" . __('Default chart format')."</td><td>";
      Dropdown::showFromArray("default_graphtype", array('png' => 'PNG',
                                                         'svg' => 'SVG'),
                              array('value' => $CFG_GLPI["default_graphtype"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td colspan='4' class='center b'>".__('Dynamic display').
           "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Use dynamic display for dropdowns and text fields'). "</td><td>";
      Dropdown::showYesNo("use_ajax", $CFG_GLPI["use_ajax"]);
      echo "</td>";
      if ($CFG_GLPI["use_ajax"]) {
         echo "<td>".__('Minimum text length for dynamic search in dropdowns')."</td><td>";
         Dropdown::showInteger('ajax_min_textsearch_load', $CFG_GLPI["ajax_min_textsearch_load"],
                               0, 10, 1);
      } else {
         echo "<td colspan='2'>&nbsp;";
      }

      echo "</td></tr>";

      if ($CFG_GLPI["use_ajax"]) {
         echo "<tr class='tab_bg_2'>";
         echo "<td>". __("Don't use dynamic display if the number of items is less than")."</td>
               <td>";
         Dropdown::showInteger('ajax_limit_count', $CFG_GLPI["ajax_limit_count"], 1, 200, 1,
                               array(0 => __('Never')));
         echo "</td><td>".__('Buffer time for dynamic search in dropdowns')."</td><td>";
         Dropdown::showNumber('ajax_buffertime_load',
                              array('value' => $CFG_GLPI["ajax_buffertime_load"],
                                    'min'   => 0,
                                    'max'   => 5000,
                                    'step'  => 100,
                                    'unit'  => 'millisecond'));
         echo "</td></tr>";

         echo "<tr class='tab_bg_2'>";
         echo "<td>" . __('Autocompletion of text fields') . "</td><td>";
         Dropdown::showYesNo("use_ajax_autocompletion", $CFG_GLPI["use_ajax_autocompletion"]);
         echo "</td><td>". __('Character to force the full display of dropdowns (wildcard)')."</td>";
         echo "<td><input type='text' size='1' name='ajax_wildcard' value='" .
                    $CFG_GLPI["ajax_wildcard"] . "'></td>";
         echo "</tr>";
         echo "<tr class='tab_bg_2'>";
         echo "<td>".
               __('Maximum number of items to display in the dropdown when wildcard is not used').
              "</td><td>";
         Dropdown::showInteger('dropdown_max', $CFG_GLPI["dropdown_max"], 0, 200);
         echo "</td>";
         echo "<td colspan='2'>&nbsp;</td></tr>";
      }
      echo "<tr class='tab_bg_1'><td colspan='4' class='center b'>".__('Search engine')."</td></tr>";
      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Items seen') . "</td><td>";
      $values = array(0 => __('No'),
                      1 => sprintf(__('%1$s (%2$s)'), __('Yes'), __('last criterion')),
                      2 => sprintf(__('%1$s (%2$s)'), __('Yes'), __('default criterion')));
      Dropdown::showFromArray('allow_search_view', $values,
                              array('value' => $CFG_GLPI['allow_search_view']));
      echo "</td><td>". __('Global search')."</td><td>";
      if ($CFG_GLPI['allow_search_view']) {
         Dropdown::showYesNo('allow_search_global', $CFG_GLPI['allow_search_global']);
      } else {
         echo Dropdown::getYesNo(0);
      }
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('All') . "</td><td>";
      $values = array(0 => __('No'),
                      1 => sprintf(__('%1$s (%2$s)'), __('Yes'), __('last criterion')));
      Dropdown::showFromArray('allow_search_all', $values,
                              array('value' => $CFG_GLPI['allow_search_all']));
      echo "</td><td colspan='2'></td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('button','Save')."\">";
      echo "</td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }


   /**
    * Print the config form for restrictions
    *
    * @return Nothing (display)
   **/
   function showFormInventory() {
      global $DB, $CFG_GLPI;

      if (!Session::haveRight("config", "w")) {
         return false;
      }

      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\" method='post'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<input type='hidden' name='id' value='" . $CFG_GLPI["id"] . "'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='4'>" . __('Assets') . "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Enable the financial and administrative information by default')."</td><td>";
      Dropdown::ShowYesNo('auto_create_infocoms', $CFG_GLPI["auto_create_infocoms"]);
      echo "</td><td> " . __('Restrict monitor management') . "</td>";
      echo "<td>";
      $this->dropdownGlobalManagement ("monitors_management_restrict",
                                       $CFG_GLPI["monitors_management_restrict"]);
      echo "</td</tr>";

      echo "<tr class='tab_bg_2'><td>" . __('Software category deleted by the dictionary rules') .
           "</td><td>";
      SoftwareCategory::dropdown(array('value' => $CFG_GLPI["softwarecategories_id_ondelete"],
                                       'name'  => "softwarecategories_id_ondelete"));
      echo "</td></td><td> " . __('Restrict device management') . "</td><td>";
      $this->dropdownGlobalManagement ("peripherals_management_restrict",
                                       $CFG_GLPI["peripherals_management_restrict"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" .__('Beginning of fiscal year') . "</td><td>";
      Html::showDateFormItem("date_tax", $CFG_GLPI["date_tax"], false, true, '', '', false);
      echo "</td><td> " . __('Restrict phone management') . "</td><td>";
      $this->dropdownGlobalManagement ("phones_management_restrict",
                                       $CFG_GLPI["phones_management_restrict"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Automatic fields (marked by *)') . "</td><td>";
      $tab = array(0 => __('Global'),
                   1 => __('By entity'));
      Dropdown::showFromArray('use_autoname_by_entity', $tab,
                              array('value' => $CFG_GLPI["use_autoname_by_entity"]));
      echo "</td><td> " . __('Restrict printer management') . "</td><td>";
      $this->dropdownGlobalManagement("printers_management_restrict",
                                      $CFG_GLPI["printers_management_restrict"]);
      echo "</td></tr>";

      echo "</table>";

      if (Session::haveRight("transfer","w") && Session::isMultiEntitiesMode()) {
         echo "<br><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='2'>" . __('Automatic transfer of computers') . "</th></tr>";
         echo "<tr class='tab_bg_2'>";
         echo "<td>" . __('Template for the automatic transfer of computers in another entity') .
              "</td><td>";
         Transfer::dropdown(array('value'      => $CFG_GLPI["transfers_id_auto"],
                                  'name'       => "transfers_id_auto",
                                  'emptylabel' => __('No automatic transfer')));
         echo "</td></td></tr>";
         echo "</table>";
      }

      echo "<br><table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th colspan='4'>".__('Automatically update of the elements related to the computers');
      echo "</th><th colspan='2'>".__('Unit management')."</th></tr>";

      echo "<tr><th>&nbsp;</th>";
      echo "<th>" . __('Alternate username') . "</th>";
      echo "<th>" . __('User') . "</th>";
      echo "<th>" . __('Group') . "</th>";
      echo "<th>" . __('Location') . "</th>";
      echo "<th>" . __('Status') . "</th>";
      echo "</tr>";

      $fields = array("contact", "group", "location", "user");
      echo "<tr class='tab_bg_2'>";
      echo "<td> " . __('When connecting or updating') . "</td>";
      $values[0] = __('Do not copy');
      $values[1] = __('Copy');

      foreach ($fields as $field) {
         echo "<td>";
         $fieldname = "is_".$field."_autoupdate";
         Dropdown::showFromArray($fieldname, $values, array('value' => $CFG_GLPI[$fieldname]));
         echo "</td>";
      }

      echo "<td>";
      State::dropdownBehaviour("state_autoupdate_mode", __('Copy computer status'),
                               $CFG_GLPI["state_autoupdate_mode"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td> " . __('When disconnecting') . "</td>";
      $values[0] = __('Do not delete');
      $values[1] = __('Clear');

      foreach ($fields as $field) {
         echo "<td>";
         $fieldname = "is_".$field."_autoclean";
         Dropdown::showFromArray($fieldname, $values, array('value' => $CFG_GLPI[$fieldname]));
         echo "</td>";
      }

      echo "<td>";
      State::dropdownBehaviour("state_autoclean_mode", __('Clear status'),
                               $CFG_GLPI["state_autoclean_mode"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='6' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Save')."\">";
      echo "</td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }


   /**
    * Print the config form for restrictions
    *
    * @return Nothing (display)
   **/
   function showFormAuthentication() {
      global $DB, $CFG_GLPI;

      if (!Session::haveRight("config", "w")) {
         return false;
      }

      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\" method='post'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<input type='hidden' name='id' value='" . $CFG_GLPI["id"] . "'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>" . __('Authentication') . "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Automatically add users from an external authentication source')."</td><td>";
      Dropdown::showYesNo("is_users_auto_add", $CFG_GLPI["is_users_auto_add"]);
      echo "</td><td>". __('Add a user without accreditation from a LDAP directory')."</td><td>";
      Dropdown::showYesNo("use_noright_users_add", $CFG_GLPI["use_noright_users_add"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td> " . __('Action when a user is deleted from the LDAP directory') . "</td><td>";
      AuthLDap::dropdownUserDeletedActions($CFG_GLPI["user_deleted_ldap"]);
      echo "</td><td> " . __('GLPI server time zone') . "</td><td>";
      Dropdown::showGMT("time_offset", $CFG_GLPI["time_offset"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update_auth' class='submit' value=\""._sx('button', 'Save').
           "\">";
      echo "</td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }


   /**
    * Print the config form for slave DB
    *
    * @return Nothing (display)
   **/
   function showFormDBSlave() {
      global $DB, $CFG_GLPI, $DBslave;

      if (!Session::haveRight("config", "w")) {
         return false;
      }

      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\" method='post'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<input type='hidden' name='id' value='" . $CFG_GLPI["id"] . "'>";
      echo "<input type='hidden' name='_dbslave_status' value='1'>";
      echo "<table class='tab_cadre_fixe'>";
      $active = DBConnection::isDBSlaveActive();

      echo "<tr class='tab_bg_2'><th colspan='4'>" . _n('Mysql replica', 'Mysql replicas',2) .
           "</th></tr>";
      $DBslave = DBConnection::getDBSlaveConf();

      if (is_array($DBslave->dbhost)) {
         $host = implode(' ', $DBslave->dbhost);
      } else {
         $host = $DBslave->dbhost;
      }
      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Mysql server') . "</td>";
      echo "<td><input type='text' name='_dbreplicate_dbhost' size='40' value='$host'></td>";
      echo "<td>" . __('Database') . "</td>";
      echo "<td><input type='text' name='_dbreplicate_dbdefault' value='".$DBslave->dbdefault."'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Mysql user') . "</td>";
      echo "<td><input type='text' name='_dbreplicate_dbuser' value='".$DBslave->dbuser."'></td>";
      echo "<td>" . __('Mysql password') . "</td>";
      echo "<td><input type='password' name='_dbreplicate_dbpassword' value='".
                 rawurldecode($DBSlave->dbpassword)."'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Use the slave for the search engine') . "</td><td>";
      $values = array(0 => __('Never'),
                      1 => __('If synced (all changes)'),
                      2 => __('If synced (current user changes)'),
                      3 => __('If synced or read-only account'),
                      4 => __('Always'));
      Dropdown::showFromArray('use_slave_for_search', $values,
                              array('value' => $CFG_GLPI["use_slave_for_search"]));
      echo "<td colspan='2'>&nbsp;</td>";
      echo "</tr>";

      if ($DBslave->connected && !$DB->isSlave()) {
         echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
         DBConnection::showAllReplicateDelay();
         echo "</td></tr>";
      }

      echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Save')."\">";
      echo "</td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }


   /**
    * Print the config form for connections
    *
    * @return Nothing (display)
   **/
   function showFormHelpdesk() {
      global $DB, $CFG_GLPI;

      if (!Session::haveRight("config", "w")) {
         return false;
      }

      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\" method='post'>";
      echo "<div class='center spaced' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='4'>" . __('Assistance') . "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Step for the hours (minutes)') . "</td><td>";
      Dropdown::showInteger('time_step', $CFG_GLPI["time_step"], 30, 60, 30, array(1  => 1,
                                                                                   5  => 5,
                                                                                   10 => 10,
                                                                                   15 => 15,
                                                                                   20 => 20));
      echo "</td><td>" .__('Limit of the schedules for planning') . "</td><td>";
      Dropdown::showHours('planning_begin', $CFG_GLPI["planning_begin"]);
      echo "&nbsp;->&nbsp;";
      Dropdown::showHours('planning_end', $CFG_GLPI["planning_end"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>".__('Default file size limit imported by the mails receiver')."</td><td>";
      MailCollector::showMaxFilesize('default_mailcollector_filesize_max',
                                     $CFG_GLPI["default_mailcollector_filesize_max"]);
      echo "</td><td>&nbsp;</td><td>&nbsp;</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Default heading when adding a document to a ticket') . "</td><td>";
      DocumentCategory::dropdown(array('value' => $CFG_GLPI["documentcategories_id_forticket"],
                                       'name'  => "documentcategories_id_forticket"));
      echo "</td>";
      echo "<td>" . __('By default, a software may be linked to a ticket') . "</td><td>";
      Dropdown::showYesNo("default_software_helpdesk_visible",
                          $CFG_GLPI["default_software_helpdesk_visible"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Keep tickets when purging hardware in the inventory') . "</td><td>";
      Dropdown::showYesNo("keep_tickets_on_delete", $CFG_GLPI["keep_tickets_on_delete"]);
      echo "</td><td>".__('Show personnal information in new ticket form (simplified interface)');
      echo "</td><td>";
      Dropdown::showYesNo('use_check_pref', $CFG_GLPI['use_check_pref']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" .__('Allow anonymous ticket creation (helpdesk.receiver)') . "</td><td>";
      Dropdown::showYesNo("use_anonymous_helpdesk", $CFG_GLPI["use_anonymous_helpdesk"]);
      echo "</td><td>" . __('Allow anonymous followups (receiver)') . "</td><td>";
      Dropdown::showYesNo("use_anonymous_followups", $CFG_GLPI["use_anonymous_followups"]);
      echo "</td></tr>";

      echo "</table>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='7'>" . __('Matrix of calculus for priority');
      echo "<input type='hidden' name='_matrix' value='1'></th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td class='b right' colspan='2'>".__('Impact')."</td>";

      for ($impact=5 ; $impact>=1 ; $impact--) {
         echo "<td>".Ticket::getImpactName($impact).'&nbsp;';

         if ($impact==3) {
            $isimpact[3] = 1;
            echo "<input type='hidden' name='_impact_3' value='1'>";

         } else {
            $isimpact[$impact] = (($CFG_GLPI['impact_mask']&(1<<$impact)) >0);
            Dropdown::showYesNo("_impact_${impact}", $isimpact[$impact]);
         }
         echo "</td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td class='b' colspan='2'>".__('Urgency')."</td>";

      for ($impact=5 ; $impact>=1 ; $impact--) {
         echo "<td>&nbsp;</td>";
      }
      echo "</tr>";

      for ($urgency=5 ; $urgency>=1 ; $urgency--) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>".Ticket::getUrgencyName($urgency)."&nbsp;</td>";
         echo "<td>";

         if ($urgency==3) {
            $isurgency[3] = 1;
            echo "<input type='hidden' name='_urgency_3' value='1'>";

         } else {
            $isurgency[$urgency] = (($CFG_GLPI['urgency_mask']&(1<<$urgency)) >0);
            Dropdown::showYesNo("_urgency_${urgency}", $isurgency[$urgency]);
         }
         echo "</td>";

         for ($impact=5 ; $impact>=1 ; $impact--) {
            $pri = round(($urgency+$impact)/2);

            if (isset($CFG_GLPI['priority_matrix'][$urgency][$impact])) {
               $pri = $CFG_GLPI['priority_matrix'][$urgency][$impact];
            }


            if ($isurgency[$urgency] && $isimpact[$impact]) {
               $bgcolor=$_SESSION["glpipriority_$pri"];
               echo "<td bgcolor='$bgcolor'>";
               Ticket::dropdownPriority(array('value' => $pri,
                                              'name'  => "_matrix_${urgency}_${impact}"));
               echo "</td>";
            } else {
               echo "<td><input type='hidden' name='_matrix_${urgency}_${impact}' value='$pri'>
                     </td>";
            }
         }
         echo "</tr>\n";
      }

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='7' class='center'>";
      echo "<input type='hidden' name='id' value='" . $CFG_GLPI["id"] . "'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('button','Save')."\">";
      echo "</td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }


   /**
    * Print the config form for default user prefs
    *
    * @param $data array containing datas
    * (CFG_GLPI for global config / glpi_users fields for user prefs)
    *
    * @return Nothing (display)
   **/
   function showFormUserPrefs($data=array()) {
      global $DB, $CFG_GLPI;

      $oncentral = ($_SESSION["glpiactiveprofile"]["interface"]=="central");
      $userpref  = false;
      $url       = Toolbox::getItemTypeFormURL(__CLASS__);

      if (array_key_exists('last_login',$data)) {
         $userpref = true;
         if ($data["id"] === Session::getLoginUserID()) {
            $url  = $CFG_GLPI['root_doc']."/front/preference.php";
         } else {
            $url  = $CFG_GLPI['root_doc']."/front/user.form.php";
         }
      }

      echo "<form name='form' action='$url' method='post'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<input type='hidden' name='id' value='" . $data["id"] . "'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='4'>" . __('Personalization') . "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . ($userpref?__('Language'):__('Default language')) . "</td>";
      echo "<td>";
      if (Session::haveRight("config","w")
          || !GLPI_DEMO_MODE) {
         Dropdown::showLanguages("language", array('value' => $data["language"]));
      } else {
         echo "&nbsp;";
      }

      echo "<td>" . __('Date format') ."</td>";
      echo "<td>";
      $date_formats = array(0 => __('YYYY-MM-DD'),
                            1 => __('DD-MM-YYYY'),
                            2 => __('MM-DD-YYYY'));
      Dropdown::showFromArray('date_format', $date_formats, array('value' => $data["date_format"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Results to display by page')."</td><td>";
      // Limit using global config
      Dropdown::showInteger('list_limit',
                            (($data['list_limit'] < $CFG_GLPI['list_limit_max'])
                             ? $data['list_limit'] : $CFG_GLPI['list_limit_max']),
                            5, $CFG_GLPI['list_limit_max'], 5);
      echo "</td>";

      echo "<td>" .__('Number format') . "</td>";
      $values = array(0 => '1 234.56',
                      1 => '1,234.56',
                      2 => '1 234,56');
      echo "<td>";
      Dropdown::showFromArray('number_format', $values, array('value' => $data["number_format"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      if ($oncentral) {
         echo "<td>" . __('Default characters limit in dropdowns') . "</td><td>";
         Dropdown::showInteger('dropdown_chars_limit', $data["dropdown_chars_limit"], 20, 100);
         echo "</td>";
       } else {
        echo "<td colspan='2'>&nbsp;</td>";
      }
      echo "<td>".__('Display order of surnames firstnames')."</td><td>";
      $values = array(User::REALNAME_BEFORE  => __('Surname, First name'),
                      User::FIRSTNAME_BEFORE => __('First name, Surname'));
      Dropdown::showFromArray('names_format', $values, array('value' => $data["names_format"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      if ($oncentral) {
         echo "<td>" . __('Display the complete name in tree dropdowns') . "</td><td>";
         Dropdown::showYesNo('use_flat_dropdowntree', $data["use_flat_dropdowntree"]);
         echo "</td>";
      } else {
        echo "<td colspan='2'>&nbsp;</td>";
      }

      if (!$userpref
          || ($CFG_GLPI['show_count_on_tabs'] != -1)) {
         echo "<td>".__('Display counts in tabs')."</td><td>";

         $values = array(0 => __('No'),
                         1 => __('Yes'));

         if (!$userpref) {
            $values[-1] = __('Never');
         }
         Dropdown::showFromArray('show_count_on_tabs', $values,
                                 array('value' => $data["show_count_on_tabs"]));
         echo "</td>";
      } else {
         echo "<td colspan='2'>&nbsp;</td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      if ($oncentral) {
         echo "<td>" . __('Show GLPI ID') . "</td><td>";
         Dropdown::showYesNo("is_ids_visible", $data["is_ids_visible"]);
         echo "</td>";
      } else {
         echo "<td colspan='2'></td>";
      }

      echo "<td>".__('CSV delimiter')."</td><td>";
      $values = array(';' => ';',
                      ',' => ',');
      Dropdown::showFromArray('csv_delimiter', $values, array('value' => $data["csv_delimiter"]));

      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Notifications for my changes') . "</td><td>";
      Dropdown::showYesNo("notification_to_myself", $data["notification_to_myself"]);
      echo "</td>";
      if ($oncentral) {
         echo "<td>".__('Results to display on home page')."</td><td>";
         Dropdown::showInteger('display_count_on_home', $data['display_count_on_home'], 0, 30);
         echo "</td>";
      } else {
         echo "<td colspan='2'>&nbsp;</td>";
      }
      echo "</tr>";

      if ($oncentral) {
         echo "<tr class='tab_bg_1'><th colspan='4'>".__('Assistance')."</th></tr>";

         echo "<tr class='tab_bg_2'>";
         echo "<td>".__('Private followups by default')."</td><td>";
         Dropdown::showYesNo("followup_private", $data["followup_private"]);
         echo "</td><td> " . __('Show new tickets on the home page') . "</td><td>";
         Dropdown::showYesNo("show_jobs_at_login", $data["show_jobs_at_login"]);
         echo " </td></tr>";

         echo "<tr class='tab_bg_2'><td>" . __('Private tasks by default') . "</td><td>";
         Dropdown::showYesNo("task_private", $data["task_private"]);
         echo "</td><td> " . __('Request sources by default') . "</td><td>";
         RequestType::dropdown(array('value' => $data["default_requesttypes_id"],
                                     'name'  => "default_requesttypes_id"));
         echo "</td></tr>";

         echo "<tr class='tab_bg_2'><td>".__('Pre-select me as a technician when creating a ticket').
              "</td><td>";
         if (!$userpref || Session::haveRight('own_ticket', 1)) {
            Dropdown::showYesNo("set_default_tech", $data["set_default_tech"]);
         } else {
            echo Dropdown::getYesNo(0);
         }
         echo "</td><td>" . __('Automatically refresh the list of tickets (minutes)') . "</td><td>";
         Dropdown::showInteger('refresh_ticket_list', $data["refresh_ticket_list"], 1, 30, 1,
                               array(0 => __('Never')));
         echo "</td>";
         echo "</tr>";
         echo "<tr class='tab_bg_2'>";
         echo "<td>" . __('Priority colors') . "</td>";
         echo "<td colspan='3'>";

         echo "<table><tr>";
         echo "<td bgcolor='" . $data["priority_1"] . "'>1&nbsp;";
         echo "<input type='text' name='priority_1' size='7' value='".$data["priority_1"]."'></td>";
         echo "<td bgcolor='" . $data["priority_2"] . "'>2&nbsp;";
         echo "<input type='text' name='priority_2' size='7' value='".$data["priority_2"]."'></td>";
         echo "<td bgcolor='" . $data["priority_3"] . "'>3&nbsp;";
         echo "<input type='text' name='priority_3' size='7' value='".$data["priority_3"]."'></td>";
         echo "<td bgcolor='" . $data["priority_4"] . "'>4&nbsp;";
         echo "<input type='text' name='priority_4' size='7' value='".$data["priority_4"]."'></td>";
         echo "<td bgcolor='" . $data["priority_5"] . "'>5&nbsp;";
         echo "<input type='text' name='priority_5' size='7' value='".$data["priority_5"]."'></td>";
         echo "<td bgcolor='" . $data["priority_6"] . "'>6&nbsp;";
         echo "<input type='text' name='priority_6' size='7' value='".$data["priority_6"]."'></td>";
         echo "</tr></table>";

         echo "</td></tr>";

         echo "<tr class='tab_bg_1'>".
              "<th colspan='4'>". _n('Software category','Software categories', 2) ."</th></tr>";

         echo "<tr class='tab_bg_2'>";
         echo "<td>" . __('Unfold the software belonging to a category')."</td><td>";
         Dropdown::showYesNo("is_categorized_soft_expanded", $data["is_categorized_soft_expanded"]);
         echo "</td><td>" . __('Unfold the software without category') . "</td><td>";
         Dropdown::showYesNo("is_not_categorized_soft_expanded",
                             $data["is_not_categorized_soft_expanded"]);
         echo "</td></tr>";
      }

      // Only for user
      if (array_key_exists('personal_token', $data)) {
         echo "<tr class='tab_bg_1'><th colspan='4'>". __('Remote access key') ."</th></tr>";

         echo "<tr class='tab_bg_1'><td>" . __('Remote access key');
         if (!empty($data["personal_token"])) {
            //TRANS: %s is the generation date
            echo "<br>".sprintf(__('generated on %s'),
                                Html::convDateTime($data["personal_token_date"]));
         }

         echo "</td><td colspan='3'>";
         echo "<input type='checkbox' name='_reset_personal_token'>&nbsp;".__('Regenerate');
         echo "</td></tr>";
      }

      echo "<tr><th colspan='4'>".__('Due date progression')."</th></tr>";

      echo "<tr class='tab_bg_1'>".
           "<td>".__('OK state color')."</td>";
      echo "<td bgcolor='".$data['duedateok_color']."'>";
      echo "<input name='duedateok_color' size='7' value='".$data['duedateok_color']."'
             type='text'>";
      echo "</td><td colspan='2'>&nbsp;</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Warning state color')."</td>";
      echo "<td bgcolor='".$data['duedatewarning_color']."'>";
      echo "<input name='duedatewarning_color' size='7' value='".$data['duedatewarning_color']."'
             type='text'>";
      echo "</td>";
      echo "<td>".__('Warning state threshold')."</td>";
      echo "<td>";
      Dropdown::showNumber("duedatewarning_less", array('value' => $data['duedatewarning_less']));
      $elements = array('%'     => '%',
                        'hours' => _n('Hour', 'Hours', 2),
                        'days'  => _n('Day', 'Days', 2));
      echo "&nbsp;";
      Dropdown::showFromArray("duedatewarning_unit", $elements,
                              array('value' => $data['duedatewarning_unit']));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>".
           "<td>".__('Critical state color')."</td>";
      echo "<td bgcolor='".$data['duedatecritical_color']."'>";
      echo "<input name='duedatecritical_color' size='7' value='".$data['duedatecritical_color']."'
             type='text'>";
      echo "</td>";
      echo "<td>".__('Critical state threshold')."</td>";
      echo "<td>";
      Dropdown::showNumber("duedatecritical_less", array('value' => $data['duedatecritical_less']));
      echo "&nbsp;";
      $elements = array('%'    => '%',
                       'hours' => _n('Hour', 'Hours', 2),
                       'days'  => _n('Day', 'Days', 2));
      Dropdown::showFromArray("duedatecritical_unit", $elements,
                              array('value' => $data['duedatecritical_unit']));
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Save')."\">";
      echo "</td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }


   /**
    * Display security checks on password
    *
    * @param $field string id of the field containing password to check (default 'password')
    *
    * @since version 0.84
   **/
   static function displayPasswordSecurityChecks($field='password') {
      global $CFG_GLPI;

      printf(__('%1$s: %2$s'), __('Password minimum length'),
                "<span id='password_min_length' class='red'>".$CFG_GLPI['password_min_length'].
                "</span>");

      echo "<script type='text/javascript' >\n";
      echo "function passwordCheck() {\n";
      echo "var pwd = document.getElementById('$field');";
      echo "if(pwd.value.length < ".$CFG_GLPI['password_min_length'].") {
            Ext.get('password_min_length').addClass('red');
            Ext.get('password_min_length').removeClass('green');
      } else {
            Ext.get('password_min_length').addClass('green');
            Ext.get('password_min_length').removeClass('red');
      }";
      $needs = array();
      if ($CFG_GLPI["password_need_number"]) {
         $needs[] = "<span id='password_need_number' class='red'>".__('Digit')."</span>";
         echo "var numberRegex = new RegExp('[0-9]', 'g');
         if(false == numberRegex.test(pwd.value)) {
               Ext.get('password_need_number').addClass('red');
               Ext.get('password_need_number').removeClass('green');
         } else {
               Ext.get('password_need_number').addClass('green');
               Ext.get('password_need_number').removeClass('red');
         }";
      }
      if ($CFG_GLPI["password_need_letter"]) {
         $needs[] = "<span id='password_need_letter' class='red'>".__('Lowercase')."</span>";
         echo "var letterRegex = new RegExp('[a-z]', 'g');
         if(false == letterRegex.test(pwd.value)) {
               Ext.get('password_need_letter').addClass('red');
               Ext.get('password_need_letter').removeClass('green');
         } else {
               Ext.get('password_need_letter').addClass('green');
               Ext.get('password_need_letter').removeClass('red');
         }";
      }
      if ($CFG_GLPI["password_need_caps"]) {
         $needs[] = "<span id='password_need_caps' class='red'>".__('Uppercase')."</span>";
         echo "var capsRegex = new RegExp('[A-Z]', 'g');
         if(false == capsRegex.test(pwd.value)) {
               Ext.get('password_need_caps').addClass('red');
               Ext.get('password_need_caps').removeClass('green');
         } else {
               Ext.get('password_need_caps').addClass('green');
               Ext.get('password_need_caps').removeClass('red');
         }";
      }
      if ($CFG_GLPI["password_need_symbol"]) {
         $needs[] = "<span id='password_need_symbol' class='red'>".__('Symbol')."</span>";
         echo "var capsRegex = new RegExp('[^a-zA-Z0-9_]', 'g');
         if(false == capsRegex.test(pwd.value)) {
               Ext.get('password_need_symbol').addClass('red');
               Ext.get('password_need_symbol').removeClass('green');
         } else {
               Ext.get('password_need_symbol').addClass('green');
               Ext.get('password_need_symbol').removeClass('red');
         }";

      }
      echo "}";
      echo '</script>';
      if (count($needs)) {
         echo "<br>";
         printf(__('%1$s: %2$s'), __('Password must contains'), implode(', ',$needs));
      }
   }


   /**
    * Validate password based on security rules
    *
    * @since version 0.84
    *
    * @param $password  string   password to validate
    * @param $display   boolean  display errors messages? (true by default)
    *
    * @return boolean is password valid?
   **/
   static function validatePassword($password, $display=true) {
      global $CFG_GLPI;

      $ok = true;
      if ($CFG_GLPI["use_password_security"]) {
         if (Toolbox::strlen($password) < $CFG_GLPI['password_min_length']) {
            $ok = false;
            if ($display) {
               Session::addMessageAfterRedirect(__('Password too short!'), false, ERROR);
            }
         }
         if ($CFG_GLPI["password_need_number"]
             && !preg_match("/[0-9]+/", $password)) {
            $ok = false;
            if ($display) {
               Session::addMessageAfterRedirect(__('Password must include at least a digit!'),
                                                false, ERROR);
            }
         }
         if ($CFG_GLPI["password_need_letter"]
             && !preg_match("/[a-z]+/", $password)) {
            $ok = false;
            if ($display) {
               Session::addMessageAfterRedirect(__('Password must include at least a lowercase letter!'),
                                                false, ERROR);
            }
         }
         if ($CFG_GLPI["password_need_caps"]
             && !preg_match("/[A-Z]+/", $password)) {
            $ok = false;
            if ($display) {
               Session::addMessageAfterRedirect(__('Password must include at least a uppercase letter!'),
                                                false, ERROR);
            }
         }
         if ($CFG_GLPI["password_need_symbol"]
             && !preg_match("/\W+/", $password)) {
            $ok = false;
            if ($display) {
               Session::addMessageAfterRedirect(__('Password must include at least a symbol!'),
                                                false, ERROR);
            }
         }

      }
      return $ok;
   }


   /**
    * Display a HTML report about systeme information / configuration
   **/
   function showSystemInformations() {
      global $DB, $CFG_GLPI;

      echo "<div class='center' id='tabsbody'>";
      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\" method='post'>";
      echo "<input type='hidden' name='id' value='" . $CFG_GLPI["id"] . "'>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>" . __('General setup') . "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Log Level') . "</td><td>";
      $values[1] = __('1- Critical (login error only)');
      $values[2] = __('2- Severe (not used)');
      $values[3] = __('3- Important (successful logins)');
      $values[4] = __('4- Notices (add, delete, tracking)');
      $values[5] = __('5- Complete (all)');

      Dropdown::showFromArray('event_loglevel', $values,
                              array('value' => $CFG_GLPI["event_loglevel"]));
      echo "</td><td>".__('Maximal number of automatic actions (run by CLI)')."</td><td>";
      Dropdown::showInteger('cron_limit', $CFG_GLPI["cron_limit"], 1, 30);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td> " . __('Logs in files (SQL, email, automatic action...)') . "</td><td>";
      Dropdown::showYesNo("use_log_in_files", $CFG_GLPI["use_log_in_files"]);
      echo "</td><td> " . _n('Mysql replica', 'Mysql replicas', 1) . "</td><td>";
      $active = DBConnection::isDBSlaveActive();
      Dropdown::showYesNo("_dbslave_status", $active);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='4' class='center b'>".__('Password security policy');
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Password security policy validation') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("use_password_security", $CFG_GLPI["use_password_security"]);
      echo "</td>";
      echo "<td>" . __('Password minimum length') . "</td>";
      echo "<td>";
      Dropdown::showInteger('password_min_length', $CFG_GLPI["password_min_length"], 4, 30);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Password need digit') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("password_need_number", $CFG_GLPI["password_need_number"]);
      echo "</td>";
      echo "<td>" . __('Password need lowercase character') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("password_need_letter", $CFG_GLPI["password_need_letter"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Password need uppercase character') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("password_need_caps", $CFG_GLPI["password_need_caps"]);
      echo "</td>";
      echo "<td>" . __('Password need symbol') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("password_need_symbol", $CFG_GLPI["password_need_symbol"]);
      echo "</td>";
      echo "</tr>";


      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='4' class='center b'>".__('Proxy configuration for upgrade check');
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Server') . "</td>";
      echo "<td><input type='text' name='proxy_name' value='".$CFG_GLPI["proxy_name"]."'></td>";
      //TRANS: Proxy port
      echo "<td>" . __('Port') . "</td>";
      echo "<td><input type='text' name='proxy_port' value='".$CFG_GLPI["proxy_port"]."'></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Login') . "</td>";
      echo "<td><input type='text' name='proxy_user' value='".$CFG_GLPI["proxy_user"]."'></td>";
      echo "<td>" . __('Password') . "</td>";
      echo "<td><input type='password' name='proxy_passwd' value='' autocomplete='off'>";
      echo "<br><input type='checkbox' name='_blank_proxy_passwd'>".__('Clear');
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Save')."\">";
      echo "</td></tr>";

      echo "</table>";
      Html::closeForm();

      $width = 128;

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th>". __('Information about system installation and configuration')."</th></tr>";

       $oldlang = $_SESSION['glpilanguage'];
       // Keep this, for some function call which still use translation (ex showAllReplicateDelay)
       Session::loadLanguage('en_GB');

      // No need to translate, this part always display in english (for copy/paste to forum)

      echo "<tr class='tab_bg_1'><td><pre>[code]\n&nbsp;\n";
      echo "GLPI ".$CFG_GLPI['version']." (".$CFG_GLPI['root_doc']." => ".
            dirname(dirname($_SERVER["SCRIPT_FILENAME"])).")\n";
      echo "\n</pre></td></tr>";


      echo "<tr><th>Server</th></tr>\n";
      echo "<tr class='tab_bg_1'><td><pre>\n&nbsp;\n";
      echo wordwrap("Operating system: ".php_uname()."\n", $width, "\n\t");
      $exts = get_loaded_extensions();
      sort($exts);
      echo wordwrap("PHP ".phpversion().' '.php_sapi_name()." (".implode(', ',$exts).")\n",
                    $width, "\n\t");
      $msg = "Setup: ";

      foreach (array('max_execution_time', 'memory_limit', 'post_max_size', 'safe_mode',
                     'session.save_handler', 'upload_max_filesize') as $key) {
         $msg .= $key.'="'.ini_get($key).'" ';
      }
      echo wordwrap($msg."\n", $width, "\n\t");

      $msg = 'Software: ';
      if (isset($_SERVER["SERVER_SOFTWARE"])) {
         $msg .= $_SERVER["SERVER_SOFTWARE"];
      }
      if (isset($_SERVER["SERVER_SIGNATURE"])) {
         $msg .= ' ('.Html::clean($_SERVER["SERVER_SIGNATURE"]).')';
      }
      echo wordwrap($msg."\n", $width, "\n\t");

      if (isset($_SERVER["HTTP_USER_AGENT"])) {
         echo "\t" . $_SERVER["HTTP_USER_AGENT"] . "\n";
      }

      echo "DBMS:\t";
      foreach ($DB->getInfo() as $key => $val) {
         echo "$key: $val\n\t";
      }
      echo "\n";

      self::checkWriteAccessToDirs(true);

      echo "\n</pre></td></tr>";

      self::showLibrariesInformation();

      foreach ($CFG_GLPI["systeminformations_types"] as $type) {
         $tmp = new $type();
         $tmp->showSystemInformations($width);
      }

      Session::loadLanguage($oldlang);

      echo "<tr class='tab_bg_1'><td>[/code]\n</td></tr>";

      echo "<tr class='tab_bg_2'><th>". __('To copy/paste in your support request')."</th></tr>\n";

      echo "</table></div>\n";
   }


   /**
    * show Libraries information in system information
    *
    * @since version 0.84
   **/
   static function showLibrariesInformation() {

      // No gettext

      echo "<tr class='tab_bg_2'><th>Libraries</th></tr>\n";
      echo "<tr class='tab_bg_1'><td><pre>\n&nbsp;\n";

      include_once(GLPI_HTMLAWED);
      echo "htmLawed version " . hl_version() . " in (" . realpath(dirname(GLPI_HTMLAWED)) . ")\n";

      include (GLPI_PHPCAS);
      echo "phpCas version " . phpCAS::getVersion() . " in (" .
            (dirname(GLPI_PHPCAS) ? realpath(dirname(GLPI_PHPCAS)) : "system") . ")\n";

      require_once(GLPI_PHPMAILER_DIR . "/class.phpmailer.php");
      $pm = new PHPMailer();
      echo "PHPMailer version " . $pm->Version . " in (" . realpath(GLPI_PHPMAILER_DIR) . ")\n";

      // EZ component
      echo "eZ Graph componnent installed :  ".(class_exists('ezcGraph')?'OK':'KO'). "\n";

      // Zend
      $zv = new Zend\Version\Version;
      echo "Zend Framework version " . $zv::VERSION . " in (" . realpath(GLPI_ZEND_PATH) . ")\n";

      // SimplePie :
      $sp = new SimplePie();
      echo "SimplePie version " . SIMPLEPIE_VERSION . " in (" . realpath(GLPI_SIMPLEPIE_PATH) . ")\n";

      echo "\n</pre></td></tr>";
   }


   /**
    * Dropdown for global management config
    *
    * @param $name   select name
    * @param $value  default value
   **/
   static function dropdownGlobalManagement($name, $value) {

      $choices[0] = __('Yes - Restrict to unit management for manual add');
      $choices[1] = __('Yes - Restrict to global management for manual add');
      $choices[2] = __('No');
      Dropdown::showFromArray($name,$choices,array('value'=>$value));
   }


   /**
    * Get language in GLPI associated with the value coming from LDAP
    * Value can be, for example : English, en_EN or en
    *
    * @param $lang : the value coming from LDAP
    *
    * @return the locale's php page in GLPI or '' is no language associated with the value
   **/
   static function getLanguage($lang) {
      global $CFG_GLPI;

      // Search in order : ID or extjs dico or tinymce dico / native lang / english name
      //                   / extjs dico / tinymce dico
      // ID  or extjs dico or tinymce dico
      foreach ($CFG_GLPI["languages"] as $ID => $language) {
         if ((strcasecmp($lang,$ID) == 0)
             || (strcasecmp($lang,$language[2]) == 0)
             || (strcasecmp($lang,$language[3]) == 0)) {
            return $ID;
         }
      }

      // native lang
      foreach ($CFG_GLPI["languages"] as $ID => $language) {
         if (strcasecmp($lang,$language[0]) == 0) {
            return $ID;
         }
      }

      // english lang name
      foreach ($CFG_GLPI["languages"] as $ID => $language) {
         if (strcasecmp($lang,$language[4]) == 0) {
            return $ID;
         }
      }

      return "";
   }


   static function detectRootDoc() {
      global $CFG_GLPI;

      if (!isset($CFG_GLPI["root_doc"])) {
         if (!isset($_SERVER['REQUEST_URI']) ) {
            $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
         }

         $currentdir = getcwd();
         chdir(GLPI_ROOT);
         $glpidir    = str_replace(str_replace('\\', '/',getcwd()), "",
                                   str_replace('\\', '/',$currentdir));
         chdir($currentdir);
         $globaldir  = Html::cleanParametersURL($_SERVER['REQUEST_URI']);
         $globaldir  = preg_replace("/\/[0-9a-zA-Z\.\-\_]+\.php/","",$globaldir);

         $CFG_GLPI["root_doc"] = str_replace($glpidir,"",$globaldir);
         $CFG_GLPI["root_doc"] = preg_replace("/\/$/","",$CFG_GLPI["root_doc"]);
         // urldecode for space redirect to encoded URL : change entity
         $CFG_GLPI["root_doc"] = urldecode($CFG_GLPI["root_doc"]);
      }
   }


   /**
    * Display debug information for dbslave
   **/
   function showDebug() {

      $options['diff'] = 0;
      $options['name'] = '';
      NotificationEvent::debugEvent(new DBConnection(), $options);
   }


   /**
    * Display field unicity criterias form
   **/
   function showFormFieldUnicity() {
      global $CFG_GLPI;

      $unicity = new FieldUnicity();
      $unicity->showForm($CFG_GLPI["id"], -1);
   }


   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      switch ($item->getType()) {
         case 'Preference' :
            return __('Personalization');

         case 'User' :
            if (Session::haveRight('user','w')
                && $item->currentUserHaveMoreRightThan($item->getID())) {
               return __('Settings');
            }
            break;

         case __CLASS__ :
            $tabs[1] = __('General setup');   // Display
            $tabs[2] = __('Default values');   // Prefs
            $tabs[3] = __('Assets');
            $tabs[4] = __('Assistance');
            $tabs[5] = __('System');

            if (DBConnection::isDBSlaveActive()) {
               $tabs[6]  = _n('Mysql replica', 'Mysql replicas', 2);  // Slave
            }
            return $tabs;
      }
      return '';
   }


   /**
    * @param $item         CommonGLPI object
    * @param $tabnum       (default 1)
    * @param $withtemplate (default 0)
   **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType() == 'Preference') {
         $config = new self();
         $user   = new User();
         if ($user->getFromDB(Session::getLoginUserID())) {
            $user->computePreferences();
            $config->showFormUserPrefs($user->fields);
         }

      } else if ($item->getType() == 'User') {
         $config = new self();
         $item->computePreferences();
         $config->showFormUserPrefs($item->fields);

      } else if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 1 :
               $item->showFormDisplay();
               break;

            case 2 :
               $item->showFormUserPrefs($CFG_GLPI);
               break;

            case 3 :
               $item->showFormInventory();
               break;

            case 4 :
               $item->showFormHelpdesk();
               break;

            case 5 :
               $item->showSystemInformations();
               break;

            case 6 :
               $item->showFormDBSlave();
               break;

         }
      }
      return true;
   }


   /**
    * Check Write Access to needed directories
    *
    * @param $fordebug boolean display for debug (no html, no gettext required) (false by default)
    *
    * @return 2 : creation error 1 : delete error 0: OK
   **/
   static function checkWriteAccessToDirs($fordebug=false) {
      global $CFG_GLPI;
      $dir_to_check = array(GLPI_CONFIG_DIR
                                    => __('Checking write permissions for setting files'),
                            GLPI_DOC_DIR
                                    => __('Checking write permissions for document files'),
                            GLPI_DUMP_DIR
                                    => __('Checking write permissions for dump files'),
                            GLPI_SESSION_DIR
                                    => __('Checking write permissions for session files'),
                            GLPI_CRON_DIR
                                    => __('Checking write permissions for automatic actions files'),
                            GLPI_CACHE_DIR
                                    => __('Checking write permissions for cache files'),
                            GLPI_GRAPH_DIR
                                    => __('Checking write permissions for graphic files'),
                            GLPI_LOCK_DIR
                                    => __('Checking write permissions for lock files'),
                            GLPI_PLUGIN_DOC_DIR
                                    => __('Checking write permissions for plugins document files'),
                            GLPI_TMP_DIR
                                    => __('Checking write permissions for temporary files'),
                            GLPI_RSS_DIR
                                    => __('Checking write permissions for rss files'),
                            GLPI_UPLOAD_DIR
                                    => __('Checking write permissions for upload files'));
      $error = 0;
      foreach ($dir_to_check as $dir => $message) {
         if (!$fordebug) {
            echo "<tr class='tab_bg_1'><td class='left b'>".$message."</td>";
         }
         $tmperror = Toolbox::testWriteAccessToDirectory($dir);

         $errors = array(4 => __('The directory could not be created.'),
                         3 => __('The directory was created but could not be removed.'),
                         2 => __('The file could not be created.'),
                         1 => __("The file was created but can't be deleted."));

         if ($tmperror > 0) {
            if ($fordebug) {
               echo "<img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'> ".
                     sprintf(__('Check permissions to the directory: %s'), $dir).
                     " ".$errors[$tmperror]."\n";
            } else {
               echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'><p class='red'>".
                    $errors[$tmperror]."</p> ".
                    sprintf(__('Check permissions to the directory: %s'), $dir).
                    "'</td></tr>";
            }
            $error = 2;
         } else {
            if ($fordebug) {
               echo "<img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png'>$dir : OK\n";
            } else {
               echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt=\"".
                          __s('A file and a directory have be created and deleted - Perfect!')."\"
                          title=\"".
                          __s('A file and a directory have be created and deleted - Perfect!')."\">".
                    "</td></tr>";
            }
         }
      }

      // Only write test for GLPI_LOG as SElinux prevent removing log file.
      if (!$fordebug) {
         echo "<tr class='tab_bg_1'><td class='b left'>".
               __('Checking write permissions for log files')."</td>";
      }

      if (error_log("Test\n", 3, GLPI_LOG_DIR."/php-errors.log")) {
         if ($fordebug) {
            echo "<img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png'>".GLPI_LOG_DIR." : OK\n";
         } else {
            echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt=\"".
                       __s('A file was created - Perfect!')."\" title=\"".
                       __s('A file was created - Perfect!')."\"></td></tr>";
         }

      } else {
         if ($fordebug) {
            echo "<img src='".$CFG_GLPI['root_doc']."/pics/orangebutton.png'>".
                  sprintf(__('Check permissions to the directory: %s'), GLPI_LOG_DIR)."\n";
         } else {
            echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/orangebutton.png'>".
                 "<p class='red'>".__('The file could not be created.')."</p>".
                 sprintf(__('Check permissions to the directory: %s'), GLPI_LOG_DIR)."</td></tr>";
         }
         $error = 1;
      }
      return $error;
   }
}
?>
