<?php

$AJAX_INCLUDE = 1;
define('GLPI_ROOT','../../../');
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

$item   = new $_POST['type']();
$value = $_POST['value'];

if($value == 5) {
   $item->getInputOtherType();
}

Html::ajaxFooter();
?>