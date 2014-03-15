<?php
class PluginVipProfile extends CommonDBTM {

	static function canCreate() {

		if (isset($_SESSION["glpi_plugin_vip_profile"])) {
			return ($_SESSION["glpi_plugin_vip_profile"]['vip'] == 'w');
		}
    	return false;
	}

	static function canView() {

		if (isset($_SESSION["glpi_plugin_vip_profile"])) {
        	return ($_SESSION["glpi_plugin_vip_profile"]['vip'] == 'w'
            || $_SESSION["glpi_plugin_vip_profile"]['vip'] == 'r');
      	}
	return false;
	}

	static function createAdminAccess($ID) {

		$myProf = new self();
		if (!$myProf->getFromDB($ID)) {
			$myProf->add(array('id'		=> $ID,
                               'show_vip_tab'  => '1'));
		}
	}

	function showForm($id, $options=array()) {
	
		$target = $this->getFormURL();
		if (isset($options['target'])) {
		  $target = $options['target'];
		}
		
		if (!Session::haveRight("profile","r")) {
		   return false;
		}
		$canedit = Session::haveRight("profile", "w");
		$prof = new Profile();
		if ($id){
		   $this->getFromDB($id);
		   $prof->getFromDB($id);
		}
		
		echo "<form action='".$target."' method='post'>";
		echo "<table class='tab_cadre_fixe'>";
		echo "<tr><th colspan='2' class='center b'>".sprintf(_('%1$s %2$s'), ('gestion des droits :'),
		                                                     Dropdown::getDropdownName("glpi_profiles",
		                                                                               $this->fields["id"]));
		echo "</th></tr>";
		
		echo "<tr class='tab_bg_1'>";
		echo "<td>Utiliser Vip</td><td>";
		Dropdown::showYesNo("show_vip_tab", $this->fields["show_vip_tab"]);
		echo "</td></tr>";
		
		if ($canedit) {
		   echo "<tr class='tab_bg_2'>";
		   echo "<td class='center' colspan='2'>";
		   echo "<input type='hidden' name='id' value=$id>";
		   echo "<input type='submit' name='update_user_profile' value='Mettre à jour' class='submit'>";
		   echo "</td></tr>";
		}
		echo "</table>";
		Html::closeForm();
	}
	
	function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
	
	   	if ($item->getType() == 'Profile') {
			if (plugin_vip_haveRight())
		    	return "Vip";
	   	}
	   	return '';
	}
	
	static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
	
	   if ($item->getType() == 'Profile') {
	      $prof = new self();
	      $ID = $item->getField('id');
	      if (!$prof->GetfromDB($ID)) {
	         $prof->createAccess($ID);
	      }
	      $prof->showForm($ID);
	   }
	   return true;
	}
	
	function createAccess($ID) {
	   $this->add(array('id' => $ID));
	}
	
	static function changeProfile() {
	
	   $prof = new self();
	   if ($prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
	      $_SESSION["glpi_plugin_vip_profile"] = $prof->fields;
	   } else {
	      unset($_SESSION["glpi_plugin_vip_profile"]);
	   }
	}
}

function plugin_vip_haveRight() {
	if ($_SESSION['glpi_plugin_vip_profile']['show_vip_tab'] == "1") {
		return true;
	}
	else {
		return false;
	}
}

?>
