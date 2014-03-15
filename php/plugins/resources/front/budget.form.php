<?php
/*
 * @version $Id: budget.form.php 480 2012-11-09 tynet $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_GET["id"])) $_GET["id"] = "";

$budget = new PluginResourcesBudget();

if (isset($_POST["add"])) {
   $budget->check(-1, 'w');
   $newID = $budget->add($_POST);

   Html::back();

} else if (isset($_POST["update"])) {
   $budget->check($_POST["id"], 'w');
   $budget->update($_POST);

   Html::back();

} else if (isset($_POST["delete"])) {
   $budget->check($_POST["id"], 'w');
   $budget->delete($_POST);

   $budget->redirectToList();

} else if (isset($_POST["purge"])) {
   $budget->check($_POST['id'],'w');
   $budget->delete($_POST,1);

   $budget->redirectToList();

} else if (isset($_POST["restore"])) {
   $budget->check($_POST["id"],'w');
   $budget->restore($_POST);

   $budget->redirectToList();

}else {
   $budget->checkGlobal("r");
   Html::header(PluginResourcesResource::getTypeName(2),'', "plugins", "resources","budget");

   $budget->showForm($_GET["id"]);
   Html::footer();
}
?>