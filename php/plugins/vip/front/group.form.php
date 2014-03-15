<?php
include ("../../../inc/includes.php");

Session::checkRight("profile", "r");

$grp = new PluginVipGroup();

if (isset($_POST['update_vip_group'])) {
   $grp->update($_POST);
   PluginVipTicket::updateVipDb();
   Html::back();
}

?>
