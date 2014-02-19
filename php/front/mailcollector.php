<?php
/*
 * @version $Id: mailcollector.php 20129 2013-02-04 16:53:59Z moyo $
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

Session::checkRight("config", "w");

Html::header(MailCollector::getTypeName(2), $_SERVER['PHP_SELF'], "config","mailcollector");

if (!Toolbox::canUseImapPop()) {
   echo "<div class='center'>";
   echo "<table class='tab_cadre_fixe'>";
   echo "<tr><th colspan='2'>" . _n('Receiver', 'Receivers', 2)."</th></tr>";
   echo "<tr class='tab_bg_2'>";
   echo "<td class='center red'>" . __('Your PHP parser was compiled without the IMAP functions');
   echo "</td></tr></table>";
   echo "</div>";
   Html::footer();
   exit();

} else {
   $mailcollector = new MailCollector();
   $mailcollector->title();
   Search::show('MailCollector');
   Html::footer();
}
?>