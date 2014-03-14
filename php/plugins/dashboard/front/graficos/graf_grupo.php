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
<title>GLPI - <?php echo __('Charts','dashboard'). " " . __('by Group','dashboard'); ?></title>
<!-- <base href= "<?php $_SERVER['SERVER_NAME'] ?>" > -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta http-equiv="content-language" content="en-us" />
<!--  <meta http-equiv="refresh" content= "120"/> -->
  <link href="../css/styles.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
  <link href="../css/lib/font-awesome.css" type="text/css" rel="stylesheet" />

  <script type="text/javascript" src="../js/jquery.min.js"></script> 
  <link href="../inc/calendar/calendar.css" rel="stylesheet" type="text/css">
  <script language="javascript" src="../inc/calendar/calendar.js"></script>

  <script language="javascript" src="../js/jquery.min.js"></script>  
  <link href="../inc/chosen/chosen.css" rel="stylesheet" type="text/css">
  <script src="../inc/chosen/chosen.jquery.js" type="text/javascript" language="javascript"></script>

</head>
<body>

<script src="../js/highcharts.js"></script>
<script src="../js/modules/exporting.js"></script>
<script src="../js/themes/grid.js"></script>

<?php

require_once('../inc/calendar/classes/tc_calendar.php');

global $DB;

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

//seleciona entidade

$sql_grp = "
SELECT id, name
FROM `glpi_groups`
ORDER BY `name` ASC
";

$result_grp = $DB->query($sql_grp);
$grp = $DB->fetch_assoc($result_grp);


// lista de grupos

function dropdown( $name, array $options, $selected=null )
{
    /*** begin the select ***/
    $dropdown = '<select class="chosen-select" tabindex="-1" style="width: 300px; height: 27px;" autofocus onChange="javascript: document.form1.submit.focus()" name="'.$name.'" id="'.$name.'">'."\n";

    $selected = $selected;
    /*** loop over the options ***/
    foreach( $options as $key=>$option )
    {
        /*** assign a selected value ***/
        $select = $selected==$key ? ' selected' : null;

        /*** add each option to the dropdown ***/
        $dropdown .= '<option value="'.$key.'"'.$select.'>'.$option.'</option>'."\n";
    }

    /*** close the select ***/
    $dropdown .= '</select>'."\n";

    /*** and return the completed dropdown ***/
    return $dropdown;
}


$res_grp = $DB->query($sql_grp);
$arr_grp = array();
$arr_grp[0] = "-- ". __('Select Group','dashboard') . " --" ;

$DB->data_seek($result_grp, 0);

while ($row_result = $DB->fetch_assoc($result_grp))		
	{ 
	$v_row_result = $row_result['id'];
	$arr_grp[$v_row_result] = $row_result['name'] ;			
	} 	 

$name = 'sel_grp';
$options = $arr_grp;
$selected = "0";

?>

<div id='content' >
<div id='container-fluid' style="margin: 0px 12% 0px 12%;"> 

<div id="pad-wrapper" >

<div id="charts" class="row-fluid chart"> 
<div id="head" class="row-fluid">

	<a href="../index.php"><i class="icon-home" style="font-size:14pt; margin-left:25px;"></i><span></span></a>
	
<div id="titulo_graf">

	  <?php echo __('Tickets','dashboard') ." ". __('by Group','dashboard'); ?> 
	<span style="color:#8b1a1a; font-size:35pt; font-weight:bold;"> <?php //echo "&nbsp; ".$total_mes['total'] ; ?> </span> </div>

<div id="datas-tec" class="span12 row-fluid" > 
<form id="form1" name="form1" class="form2" method="post" action="?date1=<?php echo $data_ini ?>&date2=<?php echo $data_fin ?>&con=1" onsubmit="datai();dataf();"> 
<table border="0" cellspacing="0" cellpadding="2" bgcolor="#efefef">

<tr>
<td>

<?php
include_once("../inc/datas.php");
?>

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
</td>

<td>
<?php

echo dropdown( $name, $options, $selected );
//Dropdown::showFromArray( $name, $options, $selected );

?>
</td>
</tr>
<tr><td height="15px"></td></tr>
<tr>
<td colspan="2" align="center"> 
<input type="hidden" id="data1" name="date1"> </input>
<input type="hidden" id="data2" name="date2"> </input>

<button class="btn btn-primary btn-small" type="submit" name="submit" value="Atualizar" ><i class="icon-white icon-refresh"></i>&nbsp; <?php echo __('Consult','dashboard'); ?> </button>
<button class="btn btn-primary btn-small" type="button" name="Limpar" value="Limpar" onclick="location.href='<?php echo $url2 ?>'" ><i class="icon-white icon-trash"></i>&nbsp; <?php echo __('Clean','dashboard'); ?> </button>
</td>
</tr>
</table>
<p>
</p>
<?php Html::closeForm(); ?>
<!-- </form> -->
</div>

</div>

<!-- DIV's -->

<script type="text/javascript" >
$('.chosen-select').chosen();
</script>

<?php

$con = $_GET['con'];

if($con == "1") {

if(!isset($_POST['date1']))
{	
	$data_ini2 = $_GET['date1'];	
	$data_fin2 = $_GET['date2'];
}

else {	
	$data_ini2 = $_POST['date1'];	
	$data_fin2 = $_POST['date2'];	
}  

if(!isset($_POST["sel_grp"])) {

$id_grp = $_GET["grp"];	
}

else {
$id_grp = $_POST["sel_grp"];
}

if($id_grp == "0") {
echo '<script language="javascript"> alert(" ' . __('Select Group','dashboard') . ' "); </script>';
echo '<script language="javascript"> location.href="graf_grupo.php"; </script>';
}


if($data_ini == $data_fin) {
$datas = "LIKE '".$data_ini."%'";	
}	

else {
$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";	
}

// nome do grupo

$sql_nm = "
SELECT id, name
FROM `glpi_groups`
WHERE id = ".$id_grp."
";

$result_nm = $DB->query($sql_nm);
$grp_name = $DB->fetch_assoc($result_nm);

//quant de chamados

$query_quant = "
SELECT count(*) AS total
FROM `glpi_groups_tickets` , glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = ".$id_grp."
AND glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.is_deleted = 0
AND glpi_tickets.date ".$datas."
";

$result_quant = $DB->query($query_quant);
$total = $DB->fetch_assoc($result_quant);

echo '<div id="entidade" class="span12 row-fluid" style="margin-top:25px;">';
echo $grp_name['name']." - <span style = 'color:#000;'> ".$total['total']." ".__('Tickets','dashboard')."</span>";
echo "</div>";
 ?>


<div id="graf_linhas" class="span12" style="height: 450px; margin-top: 25px; margin-left: -5px;">
<?php include ("./inc/graflinhas_grupo.inc.php"); ?>
</div>


<div id="graf2" class="span6" >
<?php  include ("./inc/grafstat_grupo.inc.php"); ?>
</div>


<div id="graf4" class="span6" >
<?php include ("./inc/grafcat_grupo.inc.php"); ?>
</div>

<div id="graf_user" class="span12" style="height: 450px; margin-top:30px; margin-bottom:30px; margin-left: -5px;">
<?php  include ("./inc/grafbar_user_grupo.inc.php"); ?>
</div>

<?php 

}
?>

</div>

</div>

</div>
</div>
</div>
</body> </html>
