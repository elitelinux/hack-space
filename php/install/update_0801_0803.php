<?php

/*
 * @version $Id: update_0801_0803.php 20129 2013-02-04 16:53:59Z moyo $
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

/**
 * Update from 0.80.1 to 0.80.3
 *
 * @return bool for success (will die for most error)
**/
function update0801to0803() {
   global $DB, $migration;

   $updateresult     = true;
   $ADDTODISPLAYPREF = array();

   //TRANS: %s is the number of new version
   $migration->displayTitle(sprintf(__('Update to %s'), '0.80.3'));
   $migration->setVersion('0.80.3');

   $migration->changeField("glpi_fieldunicities", 'fields', 'fields', "text");

   $migration->dropKey('glpi_ocslinks', 'unicity');
   $migration->migrationOneTable('glpi_ocslinks');
   $migration->addKey("glpi_ocslinks", array('ocsid', 'ocsservers_id'),
                        "unicity", "UNIQUE");

   // must always be at the end
   $migration->executeMigration();

   return $updateresult;
}
?>
