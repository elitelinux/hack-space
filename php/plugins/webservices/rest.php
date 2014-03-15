<?php
/*
 * @version $Id: rest.php 349 2013-05-21 15:16:15Z yllen $
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

if (!function_exists("json_encode")) {
   header("HTTP/1.0 500 Extension json not loaded");
   die("Extension json not loaded");
}

define('DO_NOT_CHECK_HTTP_REFERER', 1);
define('GLPI_ROOT', '../..');

// define session_id before any other think
if (isset($_GET['session'])) {
   include_once ("inc/methodcommon.class.php");
   include_once ("inc/methodsession.class.php");
   $session = new PluginWebservicesMethodSession();
   $session->setSession($_GET['session']);
}

include (GLPI_ROOT . "/inc/includes.php");

Plugin::load('webservices', true);

Plugin::doHook("webservices");
plugin_webservices_registerMethods();

error_reporting(E_ALL);

$method  = (isset($_GET['method'])?$_GET['method']:'');
$session = new PluginWebservicesMethodSession();
$resp    = $session->execute($method, $_GET, WEBSERVICE_PROTOCOL_REST);

// Send UTF8 Headers
header("Content-Type: text/html; charset=UTF-8");
echo json_encode($resp);
?>