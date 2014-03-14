<?php
/*
 * @version $Id: view.form.php 180 2013-03-12 09:17:42Z yllen $
 -------------------------------------------------------------------------
 Archires plugin for GLPI
 Copyright (C) 2003-2013 by the archires Development Team.

 https://forge.indepnet.net/projects/archires
 -------------------------------------------------------------------------

 LICENSE

 This file is part of archires.

 Archires is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Archires is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Archires. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

if (isset($_GET["start"])) {
   $start = $_GET["start"];
} else {
   $start = 0;
}
$PluginArchiresView = new PluginArchiresView();

if (isset($_POST["add"])) {
   $PluginArchiresView->check(-1,'w',$_POST);
   $PluginArchiresView->add($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $PluginArchiresView->check($_POST['id'],'w');
   $PluginArchiresView->delete($_POST);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginArchiresView'));

} else if (isset($_POST["restore"])) {
   $PluginArchiresView->check($_POST['id'],'w');
   $PluginArchiresView->restore($_POST);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginArchiresView'));

} else if (isset($_POST["purge"])) {
   $PluginArchiresView->check($_POST['id'],'w');
   $PluginArchiresView->delete($_POST,1);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginArchiresView'));

} else if (isset($_POST["update"])) {
   $PluginArchiresView->check($_POST['id'],'w');
   $PluginArchiresView->update($_POST);
   Html::back();

} else if (isset($_POST["duplicate"])) {
   $PluginArchiresView->check($_POST['id'],'w');
   unset($_POST['id']);
   $PluginArchiresView->add($_POST);
   Html::back();

} else {
   $PluginArchiresView->checkGlobal("r");

   Html::header(PluginArchiresView::getTypeName(),'',"plugins","archires","view");

   $PluginArchiresView->showForm($_GET["id"]);

   Html::footer();
}
?>