<?php
/*
 * @version $Id: central.class.php 22986 2014-05-30 10:34:27Z yllen $
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

// class Central
class Central extends CommonGLPI {


   static function getTypeName($nb=0) {

      // No plural
      return __('Standard interface');
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab(__CLASS__, $ong, $options);

      return $ong;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         $tabs[1] = __('Personal View');
         $tabs[2] = __('Group View');
         $tabs[3] = __('Global View');
         $tabs[4] = _n('RSS feed', 'RSS feeds', 2);

         return $tabs;
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 1 : // all
               $item->showMyView();
               break;

            case 2 :
               $item->showGroupView();
               break;

            case 3 :
               $item->showGlobalView();
               break;

            case 4 :
               $item->showRSSView();
               break;
         }
      }
      return true;
   }


   /**
    * Show the central global view
   **/
   static function showGlobalView() {

      $showticket  = Session::haveRight("show_all_ticket","1");
      $showproblem = Session::haveRight("show_all_problem","1");

      echo "<table class='tab_cadre_central'><tr>";
      echo "<td class='top'>";
      echo "<table class='central'>";
      echo "<tr><td>";
      if ($showticket) {
         Ticket::showCentralCount();
      }
      if ($showproblem) {
         Problem::showCentralCount();
      }
      if (Session::haveRight("contract","r")) {
         Contract::showCentral();
      }
      echo "</td></tr>";
      echo "</table></td>";

      if (Session::haveRight("logs","r")) {
         echo "<td class='top'>";

         //Show last add events
         Event::showForUser($_SESSION["glpiname"]);
         echo "</td>";
      }
      echo "</tr></table>";

      if ($_SESSION["glpishow_jobs_at_login"] && $showticket) {
         echo "<br>";
         Ticket::showCentralNewList();
      }
   }


   /**
    * Show the central personal view
   **/
   static function showMyView() {
      global $DB, $CFG_GLPI;

      $showticket = (Session::haveRight("show_all_ticket", "1")
                     || Session::haveRight("show_assign_ticket", "1"));

      $showproblem = (Session::haveRight("show_all_problem", "1")
                      || Session::haveRight("show_my_problem", "1"));

      echo "<table class='tab_cadre_central'>";

      if (Session::haveRight("config", "w")) {
         $logins = User::checkDefaultPasswords();
         $user   = new User();
         if (!empty($logins)) {
            $accouts = array();
            foreach ($logins as $login) {
               $user->getFromDBbyName($login);
               $accounts[] = $user->getLink();
            }
            $message = sprintf(__('For security reasons, please change the password for the default users: %s'),
                               implode(" ", $accounts));

            echo "<tr><th colspan='2'>";
            Html::displayTitle($CFG_GLPI['root_doc']."/pics/warning.png", $message, $message);
            echo "</th></tr>";
         }
         if (file_exists(GLPI_ROOT . "/install/install.php")) {
            echo "<tr><th colspan='2'>";
            $message = sprintf(__('For security reasons, please remove file: %s'),
                               "install/install.php");
            Html::displayTitle($CFG_GLPI['root_doc']."/pics/warning.png", $message, $message);
            echo "</th></tr>";
         }
      }

      if ($DB->isSlave()
          && !$DB->first_connection) {
         echo "<tr><th colspan='2'>";
         Html::displayTitle($CFG_GLPI['root_doc']."/pics/warning.png", __('MySQL replica: read only'),
                            __('MySQL replica: read only'));
         echo "</th></tr>";
      }
      echo "<tr><td class='top'><table class='central'>";
      echo "<tr><td>";
      if (Session::haveRight('validate_request',1)
         || Session::haveRight('validate_incident',1)) {
         Ticket::showCentralList(0,"tovalidate",false);
      }
      if (Ticket::isAllowedStatus(Ticket::SOLVED, Ticket::CLOSED)) {
         Ticket::showCentralList(0, "toapprove", false);
      }
      Ticket::showCentralList(0, "rejected", false);
      Ticket::showCentralList(0, "requestbyself", false);
      Ticket::showCentralList(0, "observed", false);
      if ($showticket) {
         Ticket::showCentralList(0, "process", false);
         Ticket::showCentralList(0, "waiting", false);
      }
      if ($showproblem) {
         Problem::showCentralList(0, "process", false);
      }
      echo "</td></tr>";
      echo "</table></td>";
      echo "<td class='top'><table class='central'>";
      echo "<tr><td>";
      Planning::showCentral(Session::getLoginUserID());
      Reminder::showListForCentral();
      if (Session::haveRight("reminder_public","r")) {
         Reminder::showListForCentral(false);
      }
      echo "</td></tr>";
      echo "</table></td></tr></table>";
   }


   /**
    * Show the central RSS view
    *
    * @since version 0.84
   **/
   static function showRSSView() {

      echo "<table class='tab_cadre_central'>";

      echo "<tr><td class='top' width='50%'>";
      RSSFeed::showListForCentral();
      echo "</td><td class='top' width='50%'>";
      if (RSSFeed::canView()) {
         RSSFeed::showListForCentral(false);
      } else {
         echo "&nbsp;";
      }
      echo "</td></tr>";
      echo "</table>";
   }


   /**
    * Show the central group view
   **/
   static function showGroupView() {

      $showticket = (Session::haveRight("show_all_ticket","1")
                     || Session::haveRight("show_assign_ticket","1"));

      $showproblem = (Session::haveRight("show_all_problem", "1")
                      || Session::haveRight("show_my_problem", "1"));

      echo "<table class='tab_cadre_central'>";
      echo "<tr><td class='top'><table class='central'>";
      echo "<tr><td>";
      if ($showticket) {
         Ticket::showCentralList(0, "process", true);
      }
      if (Session::haveRight('show_group_ticket','1')) {
         Ticket::showCentralList(0, "waiting", true);
      }
      if ($showproblem) {
         Problem::showCentralList(0, "process", true);
      }

      echo "</td></tr>";
      echo "</table></td>";
      echo "<td class='top'><table class='central'>";
      echo "<tr><td>";
      if (Session::haveRight('show_group_ticket','1')) {
         Ticket::showCentralList(0, "toapprove", true);
         Ticket::showCentralList(0, "requestbyself", true);
         Ticket::showCentralList(0, "observed", true);
      } else {
         Ticket::showCentralList(0, "waiting", true);
      }
      echo "</td></tr>";
      echo "</table></td></tr></table>";
   }

}
?>