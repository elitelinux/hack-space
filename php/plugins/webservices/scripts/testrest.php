<?php
/*
 * @version $Id: testrest.php 350 2013-05-22 13:38:57Z yllen $
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

// ----------------------------------------------------------------------
// Purpose of file: Test the XML-RPC plugin from Command Line
// ----------------------------------------------------------------------

if (!function_exists("json_encode")) {
   die("Extension json_encode not loaded\n");
}
chdir(dirname($_SERVER["SCRIPT_FILENAME"]));
chdir("../../..");
$url = "/".basename(getcwd())."/plugins/webservices/xmlrpc.php";

$args=array();
if ($_SERVER['argc']>1) {
   for ($i=1 ; $i<count($_SERVER['argv']) ; $i++) {
      $it           = explode("=",$argv[$i],2);
      $it[0]        = preg_replace('/^--/','',$it[0]);
      $args[$it[0]] = (isset($it[1]) ? $it[1] : true);
   }
}

if (isset($args['help']) && !isset($args['method'])) {
   echo "\nusage : ".$_SERVER["SCRIPT_FILENAME"]." [ options] \n\n";

   echo "\thelp     : display this screen\n";
   echo "\thost     : server REST plugin URL, default : $url\n";
   echo "\tusername : User name for security check (optionnal)\n";
   echo "\tpassword : User password (optionnal)\n";
   echo "\tmethod   : REST method to call, default : glpi.test\n";

   die( "\nOther options are used for REST call.\n\n");
}

if (isset($args['url'])) {
   $url = $args['url'];
   unset($args['url']);
}

if (isset($args['host'])) {
   $host = $args['host'];
   unset($args['host']);
} else {
   $host = 'localhost';
}

if (isset($args['method'])) {
   $method = $args['method'];
   unset($args['method']);
} else {
   $method='glpi.test';
}

$header = "Content-Type: text/html";

if (isset($args['deflate'])) {
   unset($args['deflate']);
   $header .= "\nAccept-Encoding: deflate";
}

if (isset($args['base64'])) {
   $content = @file_get_contents($args['base64']);
   if (!$content) {
      die ("File not found or empty (".$args['base64'].")\n");
   }
   $args['base64'] = base64_encode($content);
}

$request = "";
foreach ($args as $key => $value) {
   $request.= "&$key=$value";
}
echo "+ Calling '$method' on http://$host/$url?method=".$method."$request\n";

$file = file_get_contents("http://$host/$url?method=".$method."$request", false);
if (!$file) {
   die("+ No response\n");
}

$response = json_decode($file, true);
if (!is_array($response)) {
   echo $file;
   die ("+ Bad response\n");
}

if (isset($response['faultCode'])) {
    echo("REST error(".$response['faultCode']."): ".$response['faultString']."\n");
} else {
   echo "+ Response: ";
   print_r($response);
}
?>
