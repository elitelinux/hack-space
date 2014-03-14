<?php

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/config/config.php");

require_once('../inc/calendar/classes/tc_calendar.php');
//define("L_LANG", "pt_BR");
// Request selected language
$hl = (isset($_POST["hl"])) ? $_POST["hl"] : false;
if(!defined("L_LANG") || L_LANG == "L_LANG")
{
	if($hl) define("L_LANG", $hl);

	// You need to tell the class which language do you use.
	// L_LANG should be defined as en_US format!!! Next line is an example, just put your own language from the provided list
	else define("L_LANG", "pt_BR"); // 
}
// IMPORTANT: Request the selected date from the calendar

$mydate = isset($_POST["date1"]) ? $_POST["date1"] : "";

?>

<html> 
<head>
<title>GLPI - <?php echo __('Tickets','dashboard') .'  '. __('by Technician','dashboard').'s'  ?></title>
<!-- <base href= "<?php $_SERVER['SERVER_NAME'] ?>" > -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta http-equiv="content-language" content="en-us" />
  <link href="../css/styles.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
  <link href="../css/lib/font-awesome.css" type="text/css" rel="stylesheet" />
<link href="../inc/calendar/calendar.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../js/jquery.min.js"></script> 
<script language="javascript" src="../inc/calendar/calendar.js"></script>
<script src="../js/highcharts.js"></script>
<script src="../js/themes/grid.js"></script>
<script src="../js/modules/exporting.js"></script>

</head>

<body>

<?php

if(!empty($_POST['submit']))
{	
	$data_ini =  $_POST['date1'];	
	$data_fin = $_POST['date2'];
}

else {
	$data_ini = date("Y-m-01");
	$data_fin = date("Y-m-d");
}    

$month = date("Y-m");
$datahoje = date("Y-m-d");  
	  
?>
<div id='content' >
<div id='container-fluid' style="margin: 0px 12% 0px 12%;"> 

 <div id="pad-wrapper" >

<div id="charts" class="row-fluid chart"> 
<div id="head" class="row-fluid">

	<a href="../index.php"><i class="icon-home" style="font-size:14pt; margin-left:25px;"></i><span></span></a>

	<div id="titulo" style="margin-bottom:45px;"> <?php echo __('Tickets','dashboard') .'  '. __('by Technician','dashboard').'s'  ?>  

<div id="datas" class="span12" > 
<form id="form1" name="form1" class="form1" method="post" action="?date1=<?php echo $data_ini ?>&date2=<?php echo $data_fin ?>" onsubmit="datai();dataf();"> 
<table border="0" cellspacing="0" cellpadding="2">
<tr>
<td>
<?php include_once("../inc/datas.php"); ?>
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

</div>

<div id="graf1">

<div id="graf1" class="row-fluid">
<?php 
include ("./inc/grafbar_tec_mes.inc.php");
?>
</div>

</div>

</div>
</div>
</div>
</body> </html>
