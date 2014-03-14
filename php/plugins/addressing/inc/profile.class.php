<?php
/*
 * @version $Id: profile.class.php 150 2012-12-17 14:34:55Z tsmr $
 -------------------------------------------------------------------------
 Addressing plugin for GLPI
 Copyright (C) 2003-2011 by the addressing Development Team.

 https://forge.indepnet.net/projects/addressing
 -------------------------------------------------------------------------

 LICENSE

 This file is part of addressing.

 Addressing is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Addressing is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Addressing. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginAddressingProfile extends CommonDBTM {

   static function getTypeName($nb=0) {
      return __('Rights management', 'addressing');
   }


   static function canCreate() {
      return Session::haveRight('profile', 'w');
   }


   static function canView() {
      return Session::haveRight('profile', 'r');
   }


   //if profile deleted
   static function purgeProfiles(Profile $prof) {

      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }


   function getFromDBByProfile($profiles_id) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `profiles_id` = '" . $profiles_id . "' ";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }


   static function createFirstAccess($ID) {

      $myProf = new self();
      if (!$myProf->getFromDBByProfile($ID)) {
         $myProf->add(array('profiles_id'           => $ID,
                            'addressing'            => 'w',
                            'use_ping_in_equipment' => 1));
      }
   }


   function createAccess($profile) {
      return $this->add(array('profiles_id' => $profile->getField('id')));
   }


   static function changeProfile() {

      $prof = new self();
      if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_addressing_profile"] = $prof->fields;
      } else {
         unset($_SESSION["glpi_plugin_addressing_profile"]);
      }
   }


   //profiles modification
   function showForm ($ID, $options=array()) {

      if (!Session::haveRight("profile","r")) {
         return false;
      }

//      $target = $this->getFormURL();
//      if (isset($options['target'])) {
//        $target = $options['target'];
//      }

      $prof = new Profile();
      if ($ID) {
         $this->getFromDBByProfile($ID);
         $prof->getFromDB($ID);
      }

      $this->showFormHeader($options);

//      echo "<form action='".$target."' method='post'>";
//      echo "<table class='tab_cadre_fixe'>";

      echo "<tr class='tab_bg_2'>";
      echo "<th colspan='4'>".sprintf(__('%1$s - %2$s'), __('Rights management', 'addressing'),
         $prof->fields["name"])."</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";

      echo "<td>".__('Generate reports', 'addressing')."</td><td>";
      Profile::dropdownNoneReadWrite("addressing",$this->fields["addressing"],1,1,1);
      echo "</td>";


      echo "<td>".__('Use ping on equipment form', 'addressing')."</td><td>";
      Dropdown::showYesNo("use_ping_in_equipment", $this->fields["use_ping_in_equipment"]);
      echo "</td>";

      echo "</tr>";

      echo "<input type='hidden' name='id' value=".$this->fields["id"].">";

      $options['candel'] = false;
      $this->showFormButtons($options);
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         if ($item->getField('id') && $item->getField('interface')!='helpdesk') {
            return array(1 => PluginAddressingAddressing::getTypeName(2));
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         $prof = new self();
         $ID = $item->getField('id');
         if (!$prof->getFromDBByProfile($ID)) {
            $prof->createAccess($item);
         }
         $prof->showForm($ID);
      }
      return true;
   }
}
?>
