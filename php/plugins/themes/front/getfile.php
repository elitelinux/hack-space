<?php
/*
 *
 -------------------------------------------------------------------------
 Themes
 Copyright (C) 2012 by iizno.

 https://forge.indepnet.net/projects/themes
 -------------------------------------------------------------------------

 LICENSE

 This file is part of themes.

 themes is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 themes is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with themes. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// Original Author of file: Jérôme Ansel <jerome@ansel.im>
// ----------------------------------------------------------------------
 
include ("../../../inc/includes.php");

if(!isset($_GET['theme_id']))
   die();

$t = new PluginThemesTheme();
$t->getFromDB($_GET['theme_id']);

if(!isset($_GET['type']))
   $_GET['type'] = 'css';

if(!isset($_GET['file']))
   die();

if (PluginThemesTheme::canView()) {
   $t->getFileContent($_GET['type'], $_GET['file']);
} else {
   die();
}