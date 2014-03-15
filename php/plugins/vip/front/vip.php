<?php
include ("../../../inc/includes.php");

if($_SESSION["glpiactiveprofile"]["interface"] == "central") {
   Html::header("TITRE", $_SERVER['PHP_SELF'],"plugins","vip","optionname");
} else {
   Html::helpHeader("TITRE", $_SERVER['PHP_SELF']);
}
$pluginvip = new PluginVipTicket();

$test = $pluginvip->find();
sort($test);
Search::show('PluginVipTicket');

Html::footer();
?>
