<?php
/*
 * @version $Id: timer.class.php 20129 2013-02-04 16:53:59Z moyo $
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
 *  Timer class for debug
 */
class Timer {
   //! Timer value
   var $timer=0;


   //! Start the Timer
   function start () {

      $this->timer = microtime(true);
      return true;
   }


   /**
    * Get the current time of the timer
    *
    * @param $decimals number of decimal of the result (default 3)
    *
    * @return time past from start
   **/
   function getTime ($decimals=3) {

      // $decimals will set the number of decimals you want for your milliseconds.
      return number_format(microtime(true) - $this->timer, $decimals, '.', ' ');
   }

}
?>
