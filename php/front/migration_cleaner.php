<?php
/*
 * @version $Id: migration_cleaner.php 20314 2013-02-27 14:30:24Z moyo $
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

Session::checkSeveralRightsOr(array("networking" => "w",
                                    "internet"   => "w"));

if (!TableExists('glpi_networkportmigrations')) {
   Session::addMessageAfterRedirect(__('You don\'t need the "migration cleaner" tool anymore...'));
   Html::redirect($CFG_GLPI["root_doc"]."/front/central.php");
}

Html::header(__('Migration cleaner'), $_SERVER['PHP_SELF'], "utils","migration");

echo "<div class='spaced' id='tabsbody'>";
echo "<table class='tab_cadre_fixe'>";

echo "<tr><th>" . __('"Migration cleaner" tool') . "</td></tr>";

if (Session::haveRight('internet', 'w')
    // Check access to all entities
    && Session::isViewAllEntities()) {
   echo "<tr class='tab_bg_1'><td class='center'>";
   Html::showSimpleForm(IPNetwork::getFormURL(), 'reinit_network',
                        __('Reinit the network topology'));
   echo "</td></tr>";
}
if (Session::haveRight('networking', 'w')) {
   echo "<tr class='tab_bg_1'><td class='center'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/front/networkportmigration.php'>".
         __('Clean the network port migration errors') . "</a>";
   echo "</td></tr>";
}

echo "</table>";
echo "</div>";


Html::footer();
?>