<?php
/*
 * @version $Id: config.form.php 166 2013-09-05 14:32:19Z yllen $
 -------------------------------------------------------------------------
 Addressing plugin for GLPI
 Copyright (C) 2003-2013 by the addressing Development Team.

 https://forge.indepnet.net/projects/addressing
 -------------------------------------------------------------------------

 LICENSE

 This file is part of addressing.

 Addressing is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Addressing is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Addressing. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

$plugin = new Plugin();
if ($plugin->isActivated("addressing")) {
   $PluginAddressingConfig = new PluginAddressingConfig();

   Session::checkRight("config", "w");

   if (isset($_POST["update"])) {
      $PluginAddressingConfig->update($_POST);
      Html::back();

   } else {
      Html::header(PluginAddressingAddressing::getTypeName(2), '', "plugins", "addressing");
      $PluginAddressingConfig->showForm();
      Html::footer();
   }

} else {
   Html::header(__('Setup'), '', "config", "plugins");
   echo "<div class='center'><br><br>".
         "<img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt='warning'><br><br>";
   echo "<b>".__('Please activate the plugin','addressing')."</b></div>";
   Html::footer();
}
?>