<?php

include ("../../../inc/includes.php");

Session::checkRight("profile","r");

$prof = new PluginTicketmailProfile();

if (isset ($_POST['update_user_profile'])) {
	$prof->update($_POST);
	Html::back();
}

?>
