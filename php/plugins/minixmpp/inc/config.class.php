<?php
/*
 * @version $Id: config.class.php 162 2013-07-31 06:36:19Z yllen $
 -------------------------------------------------------------------------
 Xmpp plugin for GLPI
 Copyright (C) 2003-2011 by the addressing Development Team.

 https://forge.indepnet.net/projects/addressing
 -------------------------------------------------------------------------

 LICENSE

 This file is part of addressing.

 Xmpp is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Xmpp is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Xmpp. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMinixmppConfig extends CommonDBTM {

   function showForm() {

      $this->getFromDB('0');

      echo "<div class='center'>";
      echo "<form method='post' action='".$this->getFormURL()."'>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>".__('XMPP Server')."</th></tr>";
      echo "<tr>";
      echo "<td>".__('Server Anonyme', 'minixmpp')."</td>";
      echo "<td>";
      echo "<input type='text' size='40' value='".$this->fields['anoserver']."' name='anoserver'>";
      echo "</td>";
      
      echo "<td>".__('Conference', 'minixmpp')."</td>";
      echo "<td>";
      echo "<input type='text' size='40' value='".$this->fields['conference']."' name='conference'>";
      echo "</td>";

      echo "<tr><th colspan='4'>";
      echo "<input type='hidden' name='id' value='0'>";
      echo "<div class='center'>".
            "<input type='submit' name='update' value='"._sx('button','Post')."' class='submit'>".
           "</div></th></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }

   function init() {

      $this->getFromDB('0');
      $search  = array('/ /', '/_\((\w+)\)/');
      $replace = array('_', '');
      $nom = preg_replace($search, $replace, strtolower($_SESSION['glpiactive_entity_shortname']));
      $anoserver = $this->fields["anoserver"];
      $server = $this->fields["conference"];
      echo "<script type='text/javascript'>";
      echo "var user='".$_SESSION['glpiname']."';";
      echo "var anoserver='".$anoserver."';";
      echo "var conference='".$nom."@".$server."';";
      echo "</script>";
   }

}

?>
