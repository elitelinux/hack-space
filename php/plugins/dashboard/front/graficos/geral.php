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
<title>GLPI - <?php echo __('Charts','dashboard')." - ".__('Overall','dashboard'); ?></title>
<!-- <base href= "<?php $_SERVER['SERVER_NAME'] ?>" > -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta http-equiv="content-language" content="en-us" />
  <!-- <meta http-equiv="refresh" content= "120"/> -->
  <link href="../css/styles.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
  <link href="../css/lib/font-awesome.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" src="../js/jquery.min.js"></script> 

</head>

<body>

<script src="../js/highcharts.js"></script>
<script src="../js/modules/exporting.js"></script>
<!-- <script src="../js/modules/no-data-to-display.js"></script>  -->
<script src="../js/themes/grid.js"></script>

<?php

$ano = date("Y");
$month = date("Y-m");
$datahoje = date("Y-m-d");

//total de chamados

$sql =	"SELECT COUNT(glpi_tickets.id) as total        
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.is_deleted = '0'
      ";

$result = $DB->query($sql) or die ("erro");
$total_mes = $DB->fetch_assoc($result);

?>
<div id='content' >
<div id='container-fluid' style="margin: 0px 12% 0px 12%;"> 

<div id="pad-wrapper" >

<div id="charts" class="row-fluid chart"> 
<div id="head" class="row-fluid" style="padding-bottom:20px;">

	<a href="../index.php"><i class="icon-home" style="font-size:14pt; margin-left:25px;"></i><span></span></a>
	
	<div id="titulo_graf" style="margin-bottom:15px;">
	
	<?php echo __('Tickets Total','dashboard'); ?>: <?php //echo $ano .":" ; ?> 
	<span style="color:#8b1a1a; font-size:35pt; font-weight:bold;"> <?php echo " ".$total_mes['total'] ; ?> </span> </div>
</div>

<!-- DIV's -->

<div id="graf_linhas" class="span12" style="height: 450px; margin-top: 25px; margin-left: -5px;">
<?php 
//include ("./inc/graflinhascham.inc.php");

include ("./inc/graflinhas_sat_geral.inc.php"); ?>
</div>

<div id="graf2" class="span6" >
<?php include ("./inc/grafstat_geral.inc.php"); ?>
</div>

<div id="graf4" class="span6" >
<?php include ("./inc/grafpie_origem.inc.php");  ?>
</div>

<div>
<?php include ("./inc/grafent_geral.inc.php");  ?>
</div>

<div>
<?php include ("./inc/grafcat_geral.inc.php"); ?>
</div>

<div>
<?php include ("./inc/grafbar_grupo_geral.inc.php");?>
</div>


</div>
</div>
</div>
</body>
</html>
