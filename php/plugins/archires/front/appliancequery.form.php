<?php
/*
 * @version $Id: appliancequery.form.php 180 2013-03-12 09:17:42Z yllen $
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

$PluginArchiresApplianceQuery = new PluginArchiresApplianceQuery();
$PluginArchiresQueryType      = new PluginArchiresQueryType();

if (isset($_POST["add"])) {
   $PluginArchiresApplianceQuery->check(-1,'w',$_POST);
   $PluginArchiresApplianceQuery->add($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $PluginArchiresApplianceQuery->check($_POST['id'],'w');
   $PluginArchiresApplianceQuery->delete($_POST);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginArchiresApplianceQuery'));

} else if (isset($_POST["restore"])) {
   $PluginArchiresApplianceQuery->check($_POST['id'],'w');
   $PluginArchiresApplianceQuery->restore($_POST);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginArchiresApplianceQuery'));

} else if (isset($_POST["purge"])) {
   $PluginArchiresApplianceQuery->check($_POST['id'],'w');
   $PluginArchiresApplianceQuery->delete($_POST,1);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginArchiresApplianceQuery'));

} else if (isset($_POST["update"])) {
   $PluginArchiresApplianceQuery->check($_POST['id'],'w');
   $PluginArchiresApplianceQuery->update($_POST);
   Html::back();

} else if (isset($_POST["duplicate"])) {
   $PluginArchiresApplianceQuery->check($_POST['id'],'w');
   unset($_POST['id']);
   $PluginArchiresApplianceQuery->add($_POST);
   Html::back();

} else if (isset($_POST["addtype"])) {
   $test = explode(";", $_POST['type']);
   if (isset($test[0]) && isset($test[1])) {
      $_POST['type']     = $test[1];
      $_POST['itemtype'] = $test[0];
      if ($PluginArchiresQueryType->canCreate()) {
         $PluginArchiresQueryType->addType('PluginArchiresApplianceQuery', $_POST['type'],
                                           $_POST['itemtype'], $_POST['query']);
      }
   }
   Html::back();

} else if (isset($_POST["deletetype"])) {
   if ($PluginArchiresQueryType->canCreate()) {
      $PluginArchiresQueryType->getFromDB($_POST["id"],-1);
      foreach ($_POST["item"] as $key => $val) {
         if ($val == 1) {
            $PluginArchiresQueryType->deleteType($key);
         }
      }
   }
   Html::back();

} else {

   $PluginArchiresApplianceQuery->checkGlobal("r");

   Html::header(PluginArchiresArchires::getTypeName()." ".PluginAppliancesAppliance::getTypeName(),
                '',"plugins","archires","appliance");

   $PluginArchiresApplianceQuery->showForm($_GET["id"]);

   Html::footer();
}
?>