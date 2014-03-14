<?php
/************************************************************************************************
 *
 * File: front/statistic.php
 *
 ************************************************************************************************
 *
 * Helpdeskrating - A Plugin for GLPI Software
 * Copyright (c) 2010-2013 Christian Deinert
 *
 * http://sourceforge.net/projects/helpdeskrating/
 *
 ************************************************************************************************
 *
 * LICENSE
 *
 *     This file is part of the GLPI Plugin Helpdeskrating.
 *
 *     The GLPI Plugin Helpdeskrating is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Lesser Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     The GLPI Plugin Helpdeskrating is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Lesser Public License for more details.
 *
 *     You should have received a copy of the GNU Lesser Public License
 *     along with the GLPI Plugin Helpdeskrating.  If not, see <http://www.gnu.org/licenses/>.
 *
 ************************************************************************************************/
define('GLPI_ROOT', '../../..');
include GLPI_ROOT . '/inc/includes.php';
include GLPI_ROOT . '/plugins/helpdeskrating/inc/statistic.class.php';

Html::header(Computer::GetTypeName(2), $_SERVER['PHP_SELF'], 'Plugin', 'helpdeskrating');

$PluginHelpdeskratingStatistic = new PluginHelpdeskratingStatistic();

$PluginHelpdeskratingStatistic->show();

Html::footer();

?>
