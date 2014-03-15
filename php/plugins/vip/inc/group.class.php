<?php
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginVipGroup extends CommonDBTM {

	static function canCreate() {
	      return plugin_vip_haveRight('config', 'w');
	}
	
	static function canView() {
	      return plugin_vip_haveRight('config', 'r');
	}
	
	/**
	 * Configuration form
	**/
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
        echo "<tr><th colspan='2' class='center b'>".sprintf(_('%1$s %2$s'), ('gestion Vip :'),
                                                             Dropdown::getDropdownName("glpi_groups",
                                                                                       $this->fields["id"]));
        echo "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>Groupe Vip</td><td>";
        Dropdown::showYesNo("isvip", $this->fields["isvip"]);
        echo "</td></tr>";

        if ($canedit) {
           echo "<tr class='tab_bg_2'>";
           echo "<td class='center' colspan='2'>";
           echo "<input type='hidden' name='id' value=$id>";
           echo "<input type='submit' name='update_vip_group' value='Mettre à jour' class='submit'>";
           echo "</td></tr>";
        }
        echo "</table>";
	
	   	Html::closeForm();
	}

	function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
        
        if ($item->getType() == 'Group' && $_SESSION['glpi_plugin_vip_profile']['show_vip_tab']) {
            return "Vip";
        }
        return '';
    }

	static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
        
       if ($item->getType() == 'Group') {
          $grp = new self();
          $ID = $item->getField('id');
          if (!$grp->GetfromDB($ID)) {
             $grp->createVip($ID);
          }
     	  $grp->showForm($ID);
       }
       return true;
    }

	static function changeGroup() {

       $grp = new self();
       if ($grp->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
          $_SESSION["glpi_plugin_vip_profile"] = $grp->fields;
       } else {
          unset($_SESSION["glpi_plugin_vip_profile"]);
       }
    }

	function createVip($ID) {

		$this->add(array('id' => $ID));
	}

}
?>
