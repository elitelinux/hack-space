<?php
/*
 * @version $Id: crontask.form.php 22657 2014-02-12 16:17:54Z moyo $
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
* @brief Form to edit Cron Task
*/

include ('../inc/includes.php');

Session::checkRight("config", "w");

$crontask = new CronTask();

if (isset($_POST['execute'])) {
   if (is_numeric($_POST['execute'])) {
      // Execute button from list.
      $name = CronTask::launch(CronTask::MODE_INTERNAL, intval($_POST['execute']));
   } else {
      // Execute button from Task form (force)
      $name = CronTask::launch(-CronTask::MODE_INTERNAL, 1, $_POST['execute']);
   }
   if ($name) {
      //TRANS: %s is a task name
      Session::addMessageAfterRedirect(sprintf(__('Task %s executed'), $name));
   }
   Html::back();
} else if (isset($_POST["update"])) {
   Session::checkRight('config', 'w');
   $crontask->update($_POST);
   Html::back();

} else if (isset($_POST['resetdate'])
           && isset($_POST["id"])) {
   Session::checkRight('config', 'w');
   if ($crontask->getFromDB($_POST["id"])) {
       $crontask->resetDate();
   }
   Html::back();

} else if (isset($_POST['resetstate'])
           && isset($_POST["id"])) {
   Session::checkRight('config', 'w');
   if ($crontask->getFromDB($_POST["id"])) {
       $crontask->resetState();
   }
   Html::back();

}else {
   if (!isset($_GET["id"]) || empty($_GET["id"])) {
      exit();
   }
   Html::header(Crontask::getTypeName(2), $_SERVER['PHP_SELF'], 'config', 'crontask');
   $crontask->showForm($_GET["id"]);
   Html::footer();
}
?>