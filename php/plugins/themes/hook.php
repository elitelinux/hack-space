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

function plugin_themes_install() {
   $types = array('theme', 'profile');
   return plugin_themes_install_types($types, "install");
}

function plugin_themes_uninstall() {
   $types = array('theme', 'profile');
   return plugin_themes_install_types($types, "uninstall");
}

function plugin_themes_install_types($types = array(), $action = "install") {
   foreach ($types as $type) {
      require_once("inc/$type.class.php");

      $classname = "PluginThemes".ucfirst($type);
      if (method_exists($classname, $action)) {
         $result = call_user_func(array($classname,$action));

         if (!$result['success']) {
            Session::addMessageAfterRedirect($result['msg']);
            return false;
         }
      }
   }

   return true;
}

function plugin_themes_postinit() {
}