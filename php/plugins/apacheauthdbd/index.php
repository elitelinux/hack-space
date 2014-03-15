<?php

if(!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', '../..');
}
include (GLPI_ROOT."/inc/includes.php");

Html::header("ApacheAuthDBD",$_SERVER["PHP_SELF"], "plugins",
             "apacheauthdbd");

Html::redirect(GLPI_ROOT ."/front/central.php");
Html::footer();

?>