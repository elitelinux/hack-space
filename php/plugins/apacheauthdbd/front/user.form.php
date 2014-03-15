<?php
if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', '../../..');
}
include_once (GLPI_ROOT . "/inc/includes.php");
   
global $DB;
if (isset ($_POST)) {
	$DB->query(
        "UPDATE `glpi_plugin_apacheauthdbd_users` 
            SET `auth` = '".$_POST['auth']."' WHERE `users_id` = '".$_POST['id']."'");
} 
Html::back();



?>