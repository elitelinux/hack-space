<?php
/*
 * @version $Id: soap.php 349 2013-05-21 15:16:15Z yllen $
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

if (!extension_loaded("soap")) {
   header("HTTP/1.0 500 Extension soap not loaded");
   die("Extension soap not loaded");
}

ini_set("soap.wsdl_cache_enabled", "0");

define('DO_NOT_CHECK_HTTP_REFERER', 1);
include ("../../inc/includes.php");

Plugin::load('webservices', true);

Plugin::doHook("webservices");
plugin_webservices_registerMethods();

error_reporting(E_ALL);

try {
   $server = new SoapServer(null, array('uri' => ''));
   $server->setclass('PluginWebservicesSoap');

} catch (Exception $e) {
   echo $e;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $server->handle();
}
?>