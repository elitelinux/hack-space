<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Additionalalerts plugin for GLPI
 Copyright (C) 2003-2011 by the Additionalalerts Development Team.

 https://forge.indepnet.net/projects/additionalalerts
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Additionalalerts.

 Additionalalerts is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Additionalalerts is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with additionalalerts. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginAdditionalalertsProfile extends CommonDBTM {
   
   static function getTypeName($nb=0) {

      return __('Rights management', 'additionalalerts');
   }
   
   static function canCreate() {
      return Session::haveRight('profile', 'w');
   }

   static function canView() {
      return Session::haveRight('profile', 'r');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Profile' 
         && $item->getField('interface')!='helpdesk') {
            return PluginAdditionalalertsAdditionalalert::getTypeName(2);
      }
      return '';
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
                           $CFG_GLPI["root_doc"]."/plugins/additionalalerts/front/profile.form.php",
                                                      'colspan' => '1'));
      }
      return true;
   }
   
	//if profile deleted
	static function purgeProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }
   
	function cleanProfiles($ID) {
      global $DB;
      
		$query = "DELETE 
				FROM `".$this->getTable()."`
				WHERE `profiles_id` = '$ID' ";
		
		$DB->query($query);
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
            'additionalalerts' => 'w'));
            
      }
   }

   function createAccess($ID) {

      $this->add(array(
      'profiles_id' => $ID));
   }
  
   static function changeProfile() {
      
      $prof = new self();
      if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id']))
         $_SESSION["glpi_plugin_additionalalerts_profile"]=$prof->fields;
      else
         unset($_SESSION["glpi_plugin_additionalalerts_profile"]);
   }
  
	//profiles modification
	function showForm ($ID, $options=array()) {

		if (!Session::haveRight("profile","r")) return false;

		$prof = new Profile();
		if ($ID) {
			$this->getFromDBByProfile($ID);
			$prof->getFromDB($ID);
		}

      $this->showFormHeader($options);
      
		echo "<tr class='tab_bg_2'>";
		
		echo "<th colspan='2'>".self::getTypeName(2)." ".$prof->fields["name"]."</th>";

      echo "</tr>";
      echo "<tr class='tab_bg_2'>";
		
		echo "<td>".__('Setup alerts', 'additionalalerts')."</td><td>";
		Profile::dropdownNoneReadWrite("additionalalerts",$this->fields["additionalalerts"],1,1,1);
		echo "</td>";
		echo "</tr>";
      
      echo "<input type='hidden' name='id' value=".$this->fields["id"].">";
      
		$options['candel'] = false;
      $this->showFormButtons($options);
	}
}

?>