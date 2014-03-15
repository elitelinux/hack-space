<?php
/*
 * @version $Id: client.form.php 351 2013-05-22 14:41:59Z yllen $
 -------------------------------------------------------------------------
 webservices - WebServices plugin for GLPI
 Copyright (C) 2003-2013 by the webservices Development Team.

 https://forge.indepnet.net/projects/webservices
 -------------------------------------------------------------------------

 LICENSE

 This file is part of webservices.

 webservices is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 webservices is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with webservices. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

Plugin::load('webservices', true);

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
$webservices = new PluginWebservicesClient();

if (isset($_POST["add"])) {
   $webservices->check(-1,'w',$_POST);
   $webservices->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {
   $webservices->check($_POST["id"],'w');
   $webservices->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $webservices->check($_POST["id"],'w');
   $webservices->delete($_POST);
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/webservices/front/client.php");

} else {
   Html::header(__('Web Services', 'webservices'), $_SERVER['PHP_SELF'], "plugins", "webservices");
   $webservices->showForm($_GET["id"]);
   Html::footer();
}
?>