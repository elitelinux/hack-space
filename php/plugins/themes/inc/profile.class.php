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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginThemesProfile extends CommonDBTM {
   static function getTypeName($nb=0) {
      return __('Themes manager', 'themes');
   }

   static function canCreate() {
      return Session::haveRight('profile', 'w');
   }

   static function canView() {
      return Session::haveRight('profile', 'r');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->getType()=='Profile') {
            return __('Themes manager', 'themes');
      }
      return '';
   }


   static function install() {
      global $DB;

      /*** install table ***/
      $sql = file_get_contents("../plugins/themes/sql/profiles.sql");
      if(!$DB->query($sql)){
         return array('success' => false,'msg' => "Error : Profile.sql");
      }
      
      self::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

      return array('success' => true);
   }
   
   
   static function uninstall() {
      global $DB;
      if(!$DB->query("DROP TABLE IF EXISTS glpi_plugin_themes_profiles")){
         return array('success' => false,'msg' => "Error : uninstall.sql");
      }

      return array('success' => true);
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile') {
         $ID = $item->getField('id');
         $prof = new self();
         
         if (!$prof->getFromDBByProfile($item->getField('id'))) {
            $prof->createAccess($item->getField('id'));
         }
         $prof->showForm($item->getField('id'), array('target' => 
                           $CFG_GLPI["root_doc"]."/plugins/themes/front/profiles.form.php"));
      }
      return true;
   }

   //if profile deleted
   static function purgeProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }

   function getFromDBByProfile($profiles_id) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."`
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

         $myProf->add(array(
            'profiles_id' => $ID,
            'themes' => 'w'
            ));

      }
   }

   function createAccess($ID) {

      $this->add(array(
      'profiles_id' => $ID));
   }

   static function changeProfile() {
      
      $prof = new self();
      if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_themes_profile"] = $prof->fields;
      } else {
         unset($_SESSION["glpi_plugin_themes_profile"]);
      }
   }

   //profiles modification
   function showForm($ID, $options=array()) {
      $target = $this->getFormURL();
      if (isset($options['target'])) {
        $target = $options['target'];
      }

      if (!Session::haveRight("profile","r")) {
         return false;
      }
      $prof = new Profile();
      if ($ID) {
         $this->getFromDBByProfile($ID);
         $prof->getFromDB($ID);
      }
      
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_2'>";

      echo "<th colspan='3'>".__('Themes manager', 'themes')."</th></tr>
            <tr class='tab_bg_2'><td colspan='2'>".__('Choose the default theme', 'themes')." : </td><td>";
      
      Profile::dropdownNoneReadWrite("themes",$this->fields["themes"],1,1,1);
      
      echo "</td></tr>";

      echo "<input type='hidden' name='id' value=".$this->fields["id"].">";

      $options['candel'] = false;
      $this->showFormButtons($options);
   }
}

?>
