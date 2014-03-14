<?php

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/config/config.php");

require_once('./inc/calendar/classes/tc_calendar.php');

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

if(!isset($_POST["sel_ent"])) {

$id_ent = $_GET["ent"];	
}

else {
$id_ent = $_POST["sel_ent"];
}

//paginacao 

$num_por_pagina = 20; 

if(!isset($_GET['pagina'])) {
$primeiro_registro = 0;
$pagina = 1;

}
else {
	$pagina = $_GET['pagina'];
	$primeiro_registro = ($pagina*$num_por_pagina) - $num_por_pagina;
}

function conv_data($data) {
	if($data != "") {
		$source = $data;
		$date = new DateTime($source);	
		return $date->format('d-m-Y');}
	else {
		return "";	
	}
}

function conv_data_hora($data) {
	if($data != "") {
		$source = $data;
		$date = new DateTime($source);	
		return $date->format('d-m-Y H:i:s');}
	else {
		return "";	
	}
}

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


?>

<html> 
<head>
<title> GLPI - <?php echo __('Tickets', 'dashboard') .'  '. __('by Entity', 'dashboard') ?> </title>
<!-- <base href= "<?php $_SERVER['SERVER_NAME'] ?>" > -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta http-equiv="content-language" content="en-us" />
  <link href="css/styles.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
  <link href="inc/calendar/calendar.css" rel="stylesheet" type="text/css">
  <script language="javascript" src="inc/calendar/calendar.js"></script>
  <link href="css/lib/font-awesome.css" type="text/css" rel="stylesheet" />
  
  <script language="javascript" src="js/jquery.min.js"></script>  
  <link href="inc/chosen/chosen.css" rel="stylesheet" type="text/css">
  <script src="inc/chosen/chosen.jquery.js" type="text/javascript" language="javascript"></script>

</head>

<body style="background-color: #e5e5e5;">

<?php

$sql_ent = "
SELECT id AS id , name AS name
FROM `glpi_entities`
ORDER BY `name` ASC
";

$result_ent = $DB->query($sql_ent);
$ent = $DB->fetch_assoc($result_ent);

?>

<div id='content' >
<div id='container-fluid' style="margin: 0px 12% 0px 12%;"> 

<div id="charts" class="row-fluid chart"> 

<div id="pad-wrapper" >

<div id="head" class="row-fluid">

<style type="text/css">
a:link, a:visited, a:active {
	text-decoration: none
	}
a:hover {
	color: #000099;
	}
</style>

<a href="index.php"><i class="icon-home" style="font-size:14pt; margin-left:25px;"></i><span></span></a>

	<div id="titulo_graf"> <?php echo __('Tickets', 'dashboard') .'  '. __('by Entity', 'dashboard') ?> </div>
	
		<div id="datas-tec" class="span12 row-fluid" >
 
	<form id="form1" name="form1" class="form2" method="post" action="rel_entidade.php?con=1" onsubmit="datai();dataf();"> 
	<table border="0" cellspacing="0" cellpadding="3" bgcolor="#efefef" >
	<tr>
<td style="width: 230px;">
<?php
$url = $_SERVER['REQUEST_URI']; 
$arr_url = explode("?", $url);
$url2 = $arr_url[0];
    
     $date3_default = $data_ini;
     $date4_default = $data_fin;
	  $myCalendar = new tc_calendar("date3", true, false);
	  $myCalendar->setIcon("./inc/calendar/images/iconCalendar.gif");
	  $myCalendar->setDate(date('d', strtotime($date3_default))
            , date('m', strtotime($date3_default))
            , date('Y', strtotime($date3_default)));
	  $myCalendar->setPath("./inc/calendar/");
	  $myCalendar->setYearInterval(2010, 2025);
	  $myCalendar->setAlignment('left', 'bottom');
	  $myCalendar->setDatePair('date3', 'date4', $date4_default);
	  $myCalendar->writeScript();	  
	  
	  $myCalendar = new tc_calendar("date4", true, false);
	  $myCalendar->setIcon("./inc/calendar/images/iconCalendar.gif");
	  $myCalendar->setDate(date('d', strtotime($date4_default))
           , date('m', strtotime($date4_default))
           , date('Y', strtotime($date4_default)));
	  $myCalendar->setPath("./inc/calendar/");
	  $myCalendar->setYearInterval(2010, 2025);
	  $myCalendar->setAlignment('left', 'bottom');
	  $myCalendar->setDatePair('date3', 'date4', $date3_default);
	  $myCalendar->writeScript();

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

// lista de entidades

$res_ent = $DB->query($sql_ent);
$arr_ent = array();
$arr_ent[0] = "-- ". __('Select Entity', 'dashboard') . " --" ;

$DB->data_seek($result_ent, 0) ;

while ($row_result = $DB->fetch_assoc($result_ent))		
	{ 
	$v_row_result = $row_result['id'];
	$arr_ent[$v_row_result] = $row_result['name'] ;			
	} 
	
$name = 'sel_ent';
$options = $arr_ent;
$selected = "0";

echo dropdown( $name, $options, $selected );
//Dropdown::showFromArray( $name, $options, $selected );

?>
</td>
</tr>
<tr><td height="15px"></td></tr>
<tr>
<td colspan="2" align="center">
 <input type="hidden" id="data1" name="date1" style="width: 50px;"> </input>
 <input type="hidden" id="data2" name="date2" style="width: 50px;"> </input>

<button class="btn btn-primary btn-small" type="submit" name="submit" value="Atualizar" ><i class="icon-white icon-search"></i>&nbsp; <?php echo __('Consult', 'dashboard'); ?></button>
<button class="btn btn-primary btn-small" type="button" name="Limpar" value="Limpar" onclick="location.href='<?php echo $url2 ?>'" > <i class="icon-white icon-trash"></i>&nbsp; <?php echo __('Clean', 'dashboard'); ?> </button></td>
</td>
</tr>
	
	</table>
<?php Html::closeForm(); ?>
<!-- </form> -->
		</div>
	</div>


<script type="text/javascript" >
$('.chosen-select').chosen();
</script>

<?php 

//entidades

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

if(!isset($_POST["sel_ent"])) {

$id_ent = $_GET["ent"];	
}

else {
$id_ent = $_POST["sel_ent"];
}

if($id_ent == "") {
echo '<script language="javascript"> alert(" ' . __('Select Entity', 'dashboard') . ' "); </script>';
echo '<script language="javascript"> location.href="rel_entidade.php"; </script>';
}

if($data_ini2 == $data_fin2) {
$datas2 = "LIKE '".$data_ini2."%'";	
}	

else {
$datas2 = "BETWEEN '".$data_ini2." 00:00:00' AND '".$data_fin2." 23:59:59'";	
}

//status

$status = "";
$version = substr($CFG_GLPI["version"],0,5);

if($version == "0.83") {
	$status_open = "('assign','new','plan','waiting')";
	$status_close = "('closed','solved')";	
	$status_all = "('assign','new','plan','waiting','closed','solved')";
}	

else {
	$status_open = "('2','1','3','4')";
	$status_close = "('5','6')";	
	$status_all = "('2','1','3','4','5','6')";
}


if(isset($_GET['stat'])) {
	
	if($_GET['stat'] == "open") {		
		$status = $status_open;
	}
	elseif($_GET['stat'] == "close") {
		$status = $status_close;
	}
	else {
	$status = $status_all;	
	}
}

else {
	$status = $status_all;
	}


// Chamados

$sql_cham = 
"SELECT glpi_tickets.id AS id, glpi_tickets.name AS descr, glpi_tickets.date AS date, glpi_tickets.solvedate as solvedate, glpi_tickets.status
FROM glpi_tickets
WHERE glpi_tickets.entities_id = ".$id_ent."
AND glpi_tickets.is_deleted = 0
AND glpi_tickets.date ".$datas2."
AND glpi_tickets.status IN ".$status."
ORDER BY id DESC
LIMIT ". $primeiro_registro .", ". $num_por_pagina ."
";

$result_cham = $DB->query($sql_cham);

//fim paginacao 1

$consulta1 = 
"SELECT glpi_tickets.id AS total
FROM glpi_tickets
WHERE glpi_tickets.entities_id = ".$id_ent."
AND glpi_tickets.is_deleted = 0
AND glpi_tickets.date ".$datas2."
AND glpi_tickets.status IN ".$status."
";

$result_cons1 = $DB->query($consulta1);

$conta_cons = $DB->numrows($result_cons1);

$consulta = $conta_cons;


if($consulta > 0) {

if(!isset($_GET['pagina'])) {
$primeiro_registro = 0;
$pagina = 1;

}
else {
	$pagina = $_GET['pagina'];
	$primeiro_registro = ($pagina*$num_por_pagina) - $num_por_pagina;
}

//montar barra

$sql_ab = "SELECT glpi_tickets.id AS total
FROM glpi_tickets
WHERE glpi_tickets.entities_id = ".$id_ent."
AND glpi_tickets.is_deleted = 0
AND glpi_tickets.date ".$datas2."
AND glpi_tickets.status IN ".$status_open ;

$result_ab = $DB->query($sql_ab) or die ("erro_ab");
$data_ab = $DB->numrows($result_ab);

$abertos = $data_ab; 

//barra de porcentagem

if($conta_cons > 0) {

if($status == $status_close ) {
	$barra = 100;
	$cor = "progress-success"; 
}

else {

//porcentagem

$perc = round(($abertos*100)/$conta_cons,1);
$barra = 100 - $perc;

// cor barra 

if($barra == 100) { $cor = "progress-success"; }

if($barra >= 80 and $barra < 100) { $cor = ""; } 

if($barra > 51 and $barra < 80) { $cor = "progress-warning"; }

if($barra > 0 and $barra <= 50) { $cor = "progress-danger"; }

}
}
else { $barra = 0;}


// nome da entidade

$sql_nm = "
SELECT id , name AS name
FROM `glpi_entities`
WHERE id = ".$id_ent."";

$result_nm = $DB->query($sql_nm);
$ent_name = $DB->fetch_assoc($result_nm);


//listar chamados

echo "
<div class='well info_box row-fluid span12' style='margin-top:25px; margin-left: -1px;'>

<table class='row-fluid'  style='font-size: 18px; font-weight:bold;' cellpadding = 1px>
<td  style='font-size: 16px; font-weight:bold; vertical-align:middle;'><span style='color:#000;'> ".__('Entity', 'dashboard').": </span>".$ent_name['name']." </td>
<td  style='font-size: 16px; font-weight:bold; vertical-align:middle;'><span style='color:#000;'> ".__('Tickets', 'dashboard').": </span>".$consulta." </td>
<td colspan='3' style='font-size: 16px; font-weight:bold; vertical-align:middle; width:200px;'><span style='color:#000;'>
".__('Period', 'dashboard') .": </span> " . conv_data($data_ini2) ." a ". conv_data($data_fin2)." 
</td>

<td colspan='1' style='width: 150px; vertical-align:middle; padding: 15px 0px 0px 0px;'>
    <div class='progress ". $cor ." progress-striped active' >
    <div class='bar' style='width:".$barra."%;'><div style='text-align: rigth; margin-top:2px;'>".$barra." % ".__('Closed', 'dashboard') ." </div></div>
    </div>    
</td> 
</table>

<table align='right' style='margin-bottom:10px;'>
<tr>
<td><button class='btn btn-primary btn-small' type='button' name='abertos' value='Abertos' onclick='location.href=\"rel_entidade.php?con=1&stat=open&ent=".$id_ent."&date1=".$data_ini2."&date2=".$data_fin2."\"' <i class='icon-white icon-trash'></i> ".__('Opened', 'dashboard')." </button> </td>
<td><button class='btn btn-primary btn-small' type='button' name='fechados' value='Fechados' onclick='location.href=\"rel_entidade.php?con=1&stat=close&ent=".$id_ent."&date1=".$data_ini2."&date2=".$data_fin2."\"' <i class='icon-white icon-trash'></i> ".__('Closed', 'dashboard')." </button> </td>
<td><button class='btn btn-primary btn-small' type='button' name='todos' value='Todos' onclick='location.href=\"rel_entidade.php?con=1&stat=all&ent=".$id_ent."&date1=".$data_ini2."&date2=".$data_fin2."\"' <i class='icon-white icon-trash'></i> ".__('All', 'dashboard')." </button> </td>
</tr>
</table>

<table class='table table-striped'  style='font-size: 12px; font-weight:bold;' cellpadding = 2px>
<tr>
<td style='font-size: 12px; font-weight:bold; color:#000; text-align: center;'> ".__('Tickets', 'dashboard')." </td>
<td> </td>
<td style='font-size: 12px; font-weight:bold; color:#000; text-align: center;'> ".__('Title', 'dashboard')." </td>
<td style='font-size: 12px; font-weight:bold; color:#000; '> ".__('Requester', 'dashboard')." </td>
<td style='font-size: 12px; font-weight:bold; color:#000; '> ".__('Technician', 'dashboard')." </td>
<td style='font-size: 12px; font-weight:bold; color:#000; '> ".__('Opening date', 'dashboard')."</td>
<td style='font-size: 12px; font-weight:bold; color:#000; '> ".__('Close date', 'dashboard')." </td>
</tr>
";


while($row = $DB->fetch_assoc($result_cham)){
	
	$status1 = $row['status']; 

	if($status1 == "1" ) { $status1 = "new";} 
	if($status1 == "2" ) { $status1 = "assign";} 
	if($status1 == "3" ) { $status1 = "plan";} 
	if($status1 == "4" ) { $status1 = "waiting";} 
	if($status1 == "5" ) { $status1 = "solved";}  	            
	if($status1 == "6" ) { $status1 = "closed";}	

//requerente	

	$sql_user = "SELECT glpi_tickets.id AS id, glpi_users.firstname AS name, glpi_users.realname AS sname
FROM `glpi_tickets_users` , glpi_tickets, glpi_users
WHERE glpi_tickets.id = glpi_tickets_users.`tickets_id`
AND glpi_tickets.id = ". $row['id'] ."
AND glpi_tickets_users.`users_id` = glpi_users.id
AND glpi_tickets_users.type = 1
";
$result_user = $DB->query($sql_user);
		
	$row_user = $DB->fetch_assoc($result_user);
			
//tecnico	

	$sql_tec = "SELECT glpi_tickets.id AS id, glpi_users.firstname AS name, glpi_users.realname AS sname
FROM `glpi_tickets_users` , glpi_tickets, glpi_users
WHERE glpi_tickets.id = glpi_tickets_users.`tickets_id`
AND glpi_tickets.id = ". $row['id'] ."
AND glpi_tickets_users.`users_id` = glpi_users.id
AND glpi_tickets_users.type = 2
";
$result_tec = $DB->query($sql_tec);	

	$row_tec = $DB->fetch_assoc($result_tec);
	
echo "	

<tr>
<td style='vertical-align:middle; text-align:center;'><a href=../../../front/ticket.form.php?id=". $row['id'] ." target=_blank >" . $row['id'] . "</a></td>
<td style='vertical-align:middle;'><img src=../../../pics/".$status1.".png title='".Ticket::getStatus($row['status'])."' style=' cursor: pointer; cursor: hand;'/> </td>
<td> ". substr($row['descr'],0,55) ." </td>
<td> ". $row_user['name'] ." ".$row_user['sname'] ." </td>
<td> ". $row_tec['name'] ." ".$row_tec['sname'] ." </td>
<td> ". conv_data($row['date']) ." </td>
<td> ". conv_data($row['solvedate']) ." </td>
<!-- <td> ". Ticket::getStatus($row['status']) ." </td> -->
</tr>";
}

echo "</table>	</div>"; ?>


<?php
// paginacao 2

echo '<div id=pag align=center class="paginas navigation row-fluid">';

$total_paginas = $conta_cons/$num_por_pagina;

$prev = $pagina - 1;
$next = $pagina + 1;
// se página maior que 1 (um), então temos link para a página anterior

if ($pagina > 1) {
    $prev_link = "<a href=".$url2."?con=1&stat=".$_GET['stat']."&date1=".$data_ini2."&date2=".$data_fin2."&ent=".$id_ent."&pagina=".$prev.">". __('Previous', 'dashboard') ."</a>";
  } 
  else { // senão não há link para a página anterior  
    $prev_link = "<a href='#'>".__('Previous', 'dashboard')."</a>";
  }
// se número total de páginas for maior que a página corrente, 
// então temos link para a próxima página  

if ($total_paginas > $pagina) {
    $next_link = "<a href=".$url2."?con=1&stat=".$_GET['stat']."&date1=".$data_ini2."&date2=".$data_fin2."&ent=".$id_ent."&pagina=".$next.">".__('Next', 'dashboard')."</a>";
  } else { 
// senão não há link para a próxima página
    $next_link = "<a href='#'> " .__('Next', 'dashboard')."</a>";
  }
 
$total_paginas = ceil($total_paginas);
  $painel = "";
  for ($x=1; $x<=$total_paginas; $x++) {
    if ($x==$pagina) { 
// se estivermos na página corrente, não exibir o link para visualização desta página 
      //$painel .= "$x";
      $painel .= " <a style=color:#000999; href=".$url2."?con=1&stat=".$_GET['stat']."&date1=".$data_ini2."&date2=".$data_fin2."&ent=".$id_ent."&pagina=".$x.">$x</a>";
    } else {
      $painel .= " <a href=".$url2."?con=1&stat=".$_GET['stat']."&date1=".$data_ini2."&date2=".$data_fin2."&ent=".$id_ent."&pagina=".$x.">$x</a>";
    }
  }
// exibir painel na tela
echo "$prev_link  $painel  $next_link";
echo '</div><br>';
// fim paginacao 2
}
//}

else {
	
echo "
<div class='well info_box row-fluid span12' style='margin-top:30px; margin-left: -3px;'>
<table class='table' style='font-size: 18px; font-weight:bold;' cellpadding = 1px>
<tr><td style='vertical-align:middle; text-align:center;'> <span style='color: #000;'>" . __('No ticket found', 'dashboard') . "</td></tr>
<tr></tr>
</table></div>";	

}	

}
?>
</div>
</div>

</div>
</div>

</body> 
</html>

