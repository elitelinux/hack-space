<?php 

class PluginApacheauthdbdUser extends CommonDBTM {

    function showForm($id, $options=array()) {
        if (!defined('GLPI_ROOT')) {
           define('GLPI_ROOT', '../../..');
        }
        include_once (GLPI_ROOT . "/inc/includes.php");
        $target = $this->getFormURL();
        if (isset($options['target'])) {
        $target = $options['target'];
        }
        global $DB;
        echo "<form action=\"$target\" method='post'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='2' class='center b'Autoriser l'authentification via le module Apache : ";
        echo "</th></tr>";
        echo "<tr class='tab_bg_2'>";
        echo "<td>Authentification autorisée</td><td>";
        $result = $data = array();
        $query = "SELECT auth FROM glpi_plugin_apacheauthdbd_users WHERE users_id = ".$id;
        $result = $DB->query($query) or die("Erreur lors de la lecture de la table <strong>profiles</strong> pour Dixinfor : ". $DB->error());
        $data = $DB->fetch_assoc($result);
        echo "<select name='auth' id='auth'>";
        echo "<option value='0'";
        if($data['auth']=='0') echo ' selected="selected"';
        echo ">Refusé</option>";
        echo "<option value='1'";
        if($data['auth']=='1') echo ' selected="selected"';
        echo ">Autorisé</option>";
        echo "</select>"; 
        echo "</td></tr>";
        echo "<tr class='tab_bg_1'>";	
        echo "<td class='center' colspan='2'>";
        echo "<input type='hidden' name='id' value=$id>";
        echo "<input type='submit' value='Envoyer' class='submit' name='à jour' >";
        echo "</td></tr>";

        echo "</table>";

        Html::closeForm();
   }
   
      function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'User') {
         return "Apache Auth DBD";
      }
      return '';
     }
      static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'User') {
         $me = new self();
         $ID = $item->getField('id');
         $me->showForm($ID);
      }
      return true;
	}
	


	
	
  }
  
?>