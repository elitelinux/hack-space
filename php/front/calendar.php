<?php
/*
 * @version $Id: calendar.php 20129 2013-02-04 16:53:59Z moyo $
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


include ('../inc/includes.php');

 $dropdown = new Calendar();

/* $dropdown->getFromDB(1);
 $begin='2010-12-27 10:00:00';
 $end='2010-12-25 09:33:00';
 $delay=15*MINUTE_TIMESTAMP;*/
//  $timestamp=   $dropdown->getActiveTimeBetween($begin,$end);
//  echo 'timestamp='.$timestamp.'<br>';
//
//  echo '--'.Html::timestampToString($timestamp);
//  echo '<br>';

// echo 'END = '.$dropdown->computeEndDate($begin,$delay);

include (GLPI_ROOT . "/front/dropdown.common.php");
?>
