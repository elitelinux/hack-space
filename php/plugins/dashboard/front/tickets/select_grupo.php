<?php

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
global $DB, $CFG_GLPI;

?>

<html> 
<head>
<title> GLPI - <?php echo __('Open Tickets','dashboard'); ?> </title>
<!-- <base href= "<?php $_SERVER['SERVER_NAME'] ?>" > -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta http-equiv="content-language" content="en-us" />
  <link href="../css/styles.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
  <link href="../css/lib/font-awesome.css" type="text/css" rel="stylesheet" />
  
  <script src="../js/jquery.min.js" type="text/javascript" ></script>
  <script src="../js/jquery.jclock.js"></script>
  
  <link href="../inc/chosen/chosen.css" rel="stylesheet" type="text/css">
  <script src="../inc/chosen/chosen.jquery.js" type="text/javascript" language="javascript"></script>

</head>
<body>

<?php

$status = "('2','1','3','4')"	;	

$sql = "SELECT COUNT( * ) AS total
FROM glpi_tickets
WHERE glpi_tickets.status
IN ".$status."
AND glpi_tickets.is_deleted = 0" ;

$result = $DB->query($sql);
$data = $DB->fetch_assoc($result);

$abertos = $data['total']; 

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


$sql_ent = "
SELECT id AS id , name AS name
FROM `glpi_groups`
ORDER BY `name` ASC
";

$result_ent = $DB->query($sql_ent);
$ent = $DB->fetch_assoc($result_ent);


$res_ent = $DB->query($sql_ent);
$arr_ent = array();
$arr_ent[0] = "-- ". __('Select Group', 'dashboard') . " --" ;

$DB->data_seek($result_ent, 0) ;

while ($row_result = $DB->fetch_assoc($result_ent))		
	{ 
	$v_row_result = $row_result['id'];
	$arr_ent[$v_row_result] = $row_result['name'] ;			
	} 
	
$name = 'sel_ent';
$options = $arr_ent;
$selected = "0";

//echo dropdown( $name, $options, $selected );

?>

<div id='content' >
<div id='container-fluid' style="margin: 0px 12% 0px 12%;"> 

<div id="charts" class="row-fluid chart"> 
<div id="head" class="row-fluid">

<a href="../index.php"><i class="icon-home" style="font-size:14pt; margin-left:25px;"></i><span></span></a>

	<div id="titulo_graf"> <?php echo __('Tickets', 'dashboard') .'  '. __('by Group', 'dashboard') ?> </div>
	
	<div id="datas-tec" class="span12 row-fluid" >	

<form id="form1" name="form1" class="form2" method="post" action="select_grupo.php?sel=1" >
<table border="0" class="span12" style="margin-left:15%;">
<tr>
	<td>
		<?php echo dropdown( $name, $options, $selected ); ?>
	</td>
</tr>

<tr><td>&nbsp;</td></tr>

<tr>
	<td align="center" >
		<button class="btn btn-primary btn-small" type="submit" name="submit" value="Atualizar" ><i class="icon-white icon-search"></i>&nbsp; <?php echo __('Consult', 'dashboard'); ?></button>
		<button class="btn btn-primary btn-small" type="button" name="Limpar" value="Limpar" onclick="location.href='select_ent.php'" > <i class="icon-white icon-trash"></i>&nbsp; <?php echo __('Clean', 'dashboard'); ?> </button></td>
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

$sel = $_GET['sel'];

if($sel == "1") {
 
if(!isset($_POST["sel_ent"])) {

$id_ent = $_GET["ent"];	
}

else {
$id_ent = $_POST["sel_ent"];
}

if($id_ent == "") {
echo '<script language="javascript"> alert(" ' . __('Select Group', 'dashboard') . ' "); </script>';
echo '<script language="javascript"> location.href="select_grupo.php"; </script>';
}

?>

<script type="text/javascript" >
location.href="cham_grupos.php?ent=<?php echo $id_ent; ?>";
</script>

<p></p>

</div>
</div>
</div>
</body>
</html>

<?php } ?>

