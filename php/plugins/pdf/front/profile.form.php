<?php
/*
 * @version $Id: profile.form.php 338 2013-07-22 14:50:38Z yllen $
 -------------------------------------------------------------------------
 pdf - Export to PDF plugin for GLPI
 Copyright (C) 2003-2013 by the pdf Development Team.

 https://forge.indepnet.net/projects/pdf
 -------------------------------------------------------------------------

 LICENSE

 This file is part of pdf.

 pdf is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 pdf is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with pdf. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include_once ("../../../inc/includes.php");
Session::checkRight("profile","r");

// Mainly usefull if not actived
Plugin::load('pdf',true);

$prof = new PluginPdfProfile();

if (isset($_POST["update_user_profile"])) {
   Session::checkRight("profile","w");
   $prof->update($_POST);
   Html::back();
}
?>