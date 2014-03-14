<?php

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/config/config.php");

global $DB;
   
    switch (date("m")) {
    case "01": $mes = __('January','dashboard'); break;
    case "02": $mes = __('February','dashboard'); break;
    case "03": $mes = __('March','dashboard'); break;
    case "04": $mes = __('April','dashboard'); break;
    case "05": $mes = __('May','dashboard'); break;
    case "06": $mes = __('June','dashboard'); break;
    case "07": $mes = __('July','dashboard'); break;
    case "08": $mes = __('August','dashboard'); break;
    case "09": $mes = __('September','dashboard'); break;
    case "10": $mes = __('October','dashboard'); break;
    case "11": $mes = __('November','dashboard'); break;
    case "12": $mes = __('December','dashboard'); break;
    }

?>

<html> 
<head>
<title>GLPI - <?php echo __('Tickets','dashboard'). " " .__('by Group','dashboard'); ?></title>
<!-- <base href= "<?php $_SERVER['SERVER_NAME'] ?>" > -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta http-equiv="content-language" content="en-us" />
 
  <link href="../css/styles.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
  <link href="../css/lib/font-awesome.css" type="text/css" rel="stylesheet" />

<script type="text/javascript" src="../js/jquery.min.js"></script> 
<link href="../inc/calendar/calendar.css" rel="stylesheet" type="text/css">
<script language="javascript" src="../inc/calendar/calendar.js"></script>

</head>
<body>

<script src="../js/highcharts.js"></script>
<script src="../js/modules/exporting.js"></script>
<script src="../js/themes/grid.js"></script>

<?php

require_once('../inc/calendar/classes/tc_calendar.php');

if(!empty($_POST['submit']))
{	
	$data_ini =  $_POST['date1'];
	
	$data_fin = $_POST['date2'];
}

else {
	$data_ini = date("Y-m-01");
	$data_fin = date("Y-m-d");
} 

$ano = date("Y");
$month = date("Y-m");
$datahoje = date("Y-m-d");

?>

<div id='content' >
<div id='container-fluid' style="margin: 0px 12% 0px 12%;"> 

<div id="pad-wrapper" >

<div id="charts" class="row-fluid chart"> 
<div id="head" class="row-fluid">

	<a href="../index.php"><i class="icon-home" style="font-size:14pt; margin-left:25px;"></i><span></span></a>

	<div id="titulo_graf" style="margin-bottom:15px;">

	  <?php echo __('Tickets','dashboard') ." ". __('by Date','dashboard'); //." - ". $mes ." ".$ano.":" ; ?> 
	<span style="color:#8b1a1a; font-size:35pt; font-weight:bold;"> <?php //echo "&nbsp; ".$total_mes['total'] ; ?> </span> </div>

<div id="datas" class="span12 row-fluid" style="margin-top: -10px; margin-bottom:10px;"> 
 
<form id="form1" name="form1" class="form1" method="post" action="?date1=<?php echo $data_ini ?>&date2=<?php echo $data_fin ?>&con=1" onsubmit="datai();dataf();"> 
<table border="0" cellspacing="0" cellpadding="2" bgcolor="#efefef">
<tr>

<td>

<?php
include_once("../inc/datas.php");
?>

</td>

<script language="Javascript">
function datai()
{
document.getElementById("data1").value = document.form1.date3.value;
}
function dataf()
{
document.getElementById("data2").value = document.form1.date4.value;
}
</script>

<td> <input type="hidden" id="data1" name="date1"> </input></td>
<td> <input type="hidden" id="data2" name="date2"> </input></td>

<td><button class="btn btn-primary btn-small" type="submit" name="submit" value="Atualizar" ><i class="icon-white icon-refresh"></i>&nbsp; <?php echo __('Consult','dashboard'); ?> </button></td>
<td><button class="btn btn-primary btn-small" type="button" name="Limpar" value="Limpar" onclick="location.href='<?php echo $url2 ?>'" ><i class="icon-white icon-trash"></i>&nbsp; <?php echo __('Clean','dashboard'); ?> </button></td>
</tr>
</table>
<p>
</p>
<?php Html::closeForm(); ?>
<!-- </form> -->
</div>

</div>
<!-- DIV's -->

<div id="graf_linhas" class="span12" style="height: 450px; margin-top: 25px; margin-left: -5px;">
<?php include ("./inc/graflinhas_cham_mes.inc.php"); ?>
</div>

<div id="graf2" class="span6" >
<?php include ("./inc/grafstat_geral_mes.inc.php"); ?>
</div>

<div id="graf4" class="span6" >
<?php  include ("./inc/grafpie_origem_mes.inc.php");  ?>
</div>

<div>
<?php include ("./inc/grafcat_geral_mes.inc.php"); ?>
</div>

<div>
<?php include ("./inc/grafent_geral_mes.inc.php"); ?>
</div>

<div>
<?php  include ("./inc/grafbar_grupo_geral_mes.inc.php"); ?>
</div>


</div>
</div>
</div>
</body> </html>
