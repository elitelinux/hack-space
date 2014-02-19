<?php
/*
 * @version $Id: crontasklog.class.php 21333 2013-07-19 08:04:16Z moyo $
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

/**
 * CronTaskLog class
**/
class CronTaskLog extends CommonDBTM{

   // Class constant
   const STATE_START = 0;
   const STATE_RUN   = 1;
   const STATE_STOP  = 2;


   /**
    * Clean old event for a task
    *
    * @param $id     integer  ID of the CronTask
    * @param $days   integer  number of day to keep
    *
    * @return integer number of events deleted
   **/
   static function cleanOld($id, $days) {
      global $DB;

      $secs      = $days * DAY_TIMESTAMP;

      $query_exp = "DELETE
                    FROM `glpi_crontasklogs`
                    WHERE `crontasks_id` = '$id'
                          AND UNIX_TIMESTAMP(date) < UNIX_TIMESTAMP()-$secs";

      $DB->query($query_exp);
      return $DB->affected_rows();
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'CronTask' :
               $ong    = array();
               $ong[1] = __('Statistics');
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $ong[2] = self::createTabEntry(_n('Log', 'Logs', 2),
                                                 countElementsInTable($this->getTable(),
                                                                      "crontasks_id
                                                                        = '".$item->getID()."'  AND `state` = '".self::STATE_STOP."' "));
               } else {
                  $ong[2] = _n('Log', 'Logs', 2);
               }
               return $ong;
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='CronTask') {
         switch ($tabnum) {
            case 1 :
               $item->showStatistics();
               break;

            case 2 :
               $item->showHistory();
               break;
         }
      }
      return true;
   }

}
?>
