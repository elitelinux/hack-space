<?php
/*
 * @version $Id: event.class.php 21049 2013-05-30 08:06:56Z moyo $
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

// Event class
class Event extends CommonDBTM {


   static function getTypeName($nb=0) {
      return _n('Log', 'Logs', $nb);
   }


   function prepareInputForAdd($input) {
      global $CFG_GLPI;

      if (isset($input['level']) && ($input['level'] <= $CFG_GLPI["event_loglevel"])) {
         return $input;
      }
      return false;
   }


   /**
    * Log an event.
    *
    * Log the event $event on the glpi_event table with all the others args, if
    * $level is above or equal to setting from configuration.
    *
    * @param $items_id
    * @param $type
    * @param $level
    * @param $service
    * @param $event
   **/
   static function log($items_id, $type, $level, $service, $event) {

      $input = array('items_id' => intval($items_id),
                     'type'     => addslashes($type),
                     'date'     => $_SESSION["glpi_currenttime"],
                     'service'  => addslashes($service),
                     'level'    => intval($level),
                     'message'  => addslashes($event));
      $tmp = new self();
      return $tmp->add($input);
   }


   /**
    * Clean old event - Call by cron
    *
    * @param $day integer
    *
    * @return integer number of events deleted
   **/
   static function cleanOld($day) {
      global $DB;

      $secs = $day * DAY_TIMESTAMP;

      $query_exp = "DELETE
                    FROM `glpi_events`
                    WHERE UNIX_TIMESTAMP(date) < UNIX_TIMESTAMP()-$secs";
      $DB->query($query_exp);

      return $DB->affected_rows();
   }


   /**
    * Return arrays for function showEvent et lastEvent
   **/
   static function logArray() {

      static $logItemtype = array();
      static $logService  = array();

      if (count($logItemtype)) {
         return array($logItemtype, $logService);
      }

      $logItemtype = array('system'      => __('System'),
                           'devices'     => _n('Component', 'Components', 2),
                           'planning'    => __('Planning'),
                           'reservation' => _n('Reservation', 'Reservations', 2),
                           'dropdown'    => _n('Dropdown', 'Dropdowns', 2),
                           'rules'       => _n('Rule', 'Rules', 2));

      $logService = array('inventory'    => __('Assets'),
                          'tracking'     => _n('Ticket', 'Tickets', 2),
                          'maintain'     => __('Assistance'),
                          'planning'     => __('Planning'),
                          'tools'        => __('Tools'),
                          'financial'    => __('Management'),
                          'login'        => __('Connection'),
                          'setup'        => __('Setup'),
                          'security'     => __('Security'),
                          'reservation'  => _n('Reservation', 'Reservations', 2),
                          'cron'         => _n('Automatic action', 'Automatic actions', 2),
                          'document'     => _n('Document', 'Documents', 2),
                          'notification' => _n('Notification', 'Notifications',2),
                          'plugin'       => __('Plugins'));

      return array($logItemtype, $logService);
   }


   /**
    * @param $type
    * @param $items_id
   **/
   static function displayItemLogID($type, $items_id) {
      global $CFG_GLPI;

      if (($items_id == "-1") || ($items_id == "0")) {
         echo "&nbsp;";//$item;
      } else {
         switch ($type) {
            case "rules" :
               echo "<a href=\"".$CFG_GLPI["root_doc"]."/front/rule.generic.form.php?id=".
                     $items_id."\">".$items_id."</a>";
               break;

            case "infocom" :
               echo "<a href='#' onClick=\"window.open('".$CFG_GLPI["root_doc"].
                     "/front/infocom.form.php?id=".$items_id."','infocoms','location=infocoms,width=".
                     "1000,height=400,scrollbars=no')\">".$items_id."</a>";
               break;

            case "devices" :
               echo $items_id;
               break;

            case "reservationitem" :
               echo "<a href=\"".$CFG_GLPI["root_doc"]."/front/reservation.php?reservationitems_id=".
                     $items_id."\">".$items_id."</a>";
               break;

            default :
               $type = getSingular($type);
               $url  = '';
               if ($item = getItemForItemtype($type)) {
                  $url  =  $item->getFormURL();
               }
               if (!empty($url)) {
                  echo "<a href=\"".$url."?id=".$items_id."\">".$items_id."</a>";
               } else {
                  echo $items_id;
               }
               break;
         }
      }
   }


   /**
    * Print a nice tab for last event from inventory section
    *
    * Print a great tab to present lasts events occured on glpi
    *
    * @param $user   string  name user to search on message (default '')
    **/
   static function showForUser($user="") {
      global $DB, $CFG_GLPI;

      // Show events from $result in table form
      list($logItemtype, $logService) = self::logArray();

      // define default sorting
      $usersearch = "";
      if (!empty($user)) {
         $usersearch = $user." ";
      }

      // Query Database
      $query = "SELECT *
                FROM `glpi_events`
                WHERE `message` LIKE '".$usersearch."%'
                ORDER BY `date` DESC
                LIMIT 0,".intval($_SESSION['glpilist_limit']);

      // Get results
      $result = $DB->query($query);

      // Number of results
      $number = $DB->numrows($result);

      // No Events in database
      if ($number < 1) {
         echo "<br><div class='spaced'><table class='tab_cadrehov'>";
         echo "<tr><th>".__('No Event')."</th></tr>";
         echo "</table></div>";
         return;
      }

      // Output events
      $i = 0;

      echo "<br><div class='spaced'><table class='tab_cadrehov'>";
      echo "<tr><th colspan='5'>";
      //TRANS: %d is the number of item to display
      echo "<a href=\"".$CFG_GLPI["root_doc"]."/front/event.php\">".
             sprintf(__('Last %d events'), $_SESSION['glpilist_limit'])."</a>";
      echo "</th></tr>";

      echo "<tr><th colspan='2'>".__('Source')."</th>";
      echo "<th>".__('Date')."</th>";
      echo "<th width='8%'>".__('Service')."</th>";
      echo "<th width='60%'>".__('Message')."</th></tr>";

      while ($i < $number) {
         $ID       = $DB->result($result, $i, "id");
         $items_id = $DB->result($result, $i, "items_id");
         $type     = $DB->result($result, $i, "type");
         $date     = $DB->result($result, $i, "date");
         $service  = $DB->result($result, $i, "service");
         $message  = $DB->result($result, $i, "message");

         $itemtype = "&nbsp;";
         if (isset($logItemtype[$type])) {
            $itemtype = $logItemtype[$type];
         } else {
            $type = getSingular($type);
            if ($item = getItemForItemtype($type)) {
               $itemtype = $item->getTypeName(1);
            }
         }

         echo "<tr class='tab_bg_2'><td>".$itemtype."</td>";
         echo "<td class='center'>";
         self::displayItemLogID($type, $items_id);
         echo "</td><td class='center'>".Html::convDateTime($date)."</td>";
         echo "<td class='center'>".$logService[$service]."</td><td>".$message."</td></tr>";

         $i++;
      }

      echo "</table></div>";
   }


   /**
    * Print a nice tab for last event
    *
    * Print a great tab to present lasts events occured on glpi
    *
    * @param $target    where to go when complete
    * @param $order     order by clause occurences (eg: ) (default 'DESC')
    * @param $sort      order by clause occurences (eg: date) (defaut 'date')
    * @param $start     (default 0)
   **/
   static function showList($target, $order='DESC', $sort='date', $start=0) {
      global $DB, $CFG_GLPI;

      // Show events from $result in table form
      list($logItemtype, $logService) = self::logArray();

      // Columns of the Table
      $items = array("items_id" => array(__('Source'), "colspan='2'"),
                     "date"     => array(__('Date'), ""),
                     "service"  => array(__('Service'), "width='8%'"),
                     "level"    => array(__('Level'), "width='8%'"),
                     "message"  => array(__('Message'), "width='50%'"));

      // define default sorting
      if (!isset($items[$sort])) {
         $sort = "date";
      }
      if ($order != "ASC") {
         $order = "DESC";
      }

      // Query Database
      $query_limit = "SELECT *
                      FROM `glpi_events`
                      ORDER BY `$sort` $order
                      LIMIT ".intval($start).",".intval($_SESSION['glpilist_limit']);

      // Number of results
      $numrows = countElementsInTable("glpi_events");
      // Get results
      $result = $DB->query($query_limit);
      $number = $DB->numrows($result);

      // No Events in database
      if ($number < 1) {
         echo "<div class='center b'>".__('No Event')."</div>";
         return;
      }

      // Output events
      $i = 0;

      echo "<div class='center'>";
      $parameters = "sort=$sort&amp;order=$order";
      Html::printPager($start, $numrows, $target, $parameters);

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";

      foreach ($items as $field => $args) {
         echo "<th ".$args[1].">";
         if ($sort == $field) {
            if ($order == "DESC") {
               echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/puce-down.png\" alt='' title=''>";
            } else {
               echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/puce-up.png\" alt='' title=''>";
            }
         }
         echo "<a href='$target?sort=$field&amp;order=".(($order=="ASC")?"DESC":"ASC")."'>".$args[0].
              "</a></th>";
      }
      echo "</tr>";

      while ($i < $number) {
         $ID       = $DB->result($result, $i, "id");
         $items_id = $DB->result($result, $i, "items_id");
         $type     = $DB->result($result, $i, "type");
         $date     = $DB->result($result, $i, "date");
         $service  = $DB->result($result, $i, "service");
         $level    = $DB->result($result, $i, "level");
         $message  = $DB->result($result, $i, "message");

         $itemtype = "&nbsp;";
         if (isset($logItemtype[$type])) {
            $itemtype = $logItemtype[$type];
         } else {
            $type = getSingular($type);
            if ($item = getItemForItemtype($type)) {
               $itemtype = $item->getTypeName(1);
            }
         }

         echo "<tr class='tab_bg_2'>";
         echo "<td>$itemtype</td>";
         echo "<td class='center b'>";
         self::displayItemLogID($type, $items_id);
         echo "</td><td>".Html::convDateTime($date)."</td>";
         echo "<td class='center'>".(isset($logService[$service])?$logService[$service]:$service);
         echo "</td><td class='center'>".$level."</td><td>".$message."</td></tr>";

         $i++;
      }
      echo "</table></div><br>";
   }


    /** Display how many logins since
     *
     * @return  nothing
    **/
    static function getCountLogin() {
       global $DB;

       $query = "SELECT COUNT(*)
                 FROM `glpi_events`
                 WHERE `message` LIKE '%logged in%'";

       $query2 = "SELECT `date`
                  FROM `glpi_events`
                  ORDER BY `date` ASC
                  LIMIT 1";

       $result   = $DB->query($query);
       $result2  = $DB->query($query2);
       $nb_login = $DB->result($result, 0, 0);
       $date     = $DB->result($result2, 0, 0);
       // Only for DEMO mode (not need to be translated)
       printf(_n('%1$s login since %2$s', '%1$s logins since %2$s', $nb_login),
              '<span class="b">'.$nb_login.'</span>', $date);
    }

}
?>
