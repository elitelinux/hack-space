<?php

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/config/config.php");


function conta($asset) {

global $DB;

$query = "
SELECT count(id) AS id
FROM glpi_".$asset."
WHERE is_deleted = 0
";

$result = $DB->query($query);
$total = $DB->result($result,0,'id');

if($total != "") {
	return $total;
}

else {
	return "0";
}
	
}	


//cartridges and consumables

function conta1($asset) {

global $DB;

$query = "
SELECT count(id) AS id
FROM glpi_".$asset."
";

$result = $DB->query($query);
$total = $DB->result($result,0,'id');

if($total != "") {
	return $total;
}

else {
	return "0";
}
	
}	


//all assets

$arr_assets =  array('computers', 'monitors', 'printers', 'networkequipments', 'phones', 'peripherals');
$global = 0;

foreach($arr_assets as $asset) {

$query = "
SELECT count(id) AS id
FROM glpi_".$asset."
WHERE is_deleted = 0
";

$result = $DB->query($query);
$total = $DB->result($result,0,'id');

$global+=$total;
}

/*
              <td style='width:100px; text-align:center;'><div id='asset_img' style='background: url(../img/server.jpg) repeat-x; 
      			background-size: 70px 70px; background-position: center; background-color: ffffff;'></div> ".__('Server')."<br>0</td>
*/
?>        

<html> 
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <title> GLPI - <?php echo __('Assets'); ?> </title>
    <!-- <base href= "<?php $_SERVER['SERVER_NAME'] ?>" > -->
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
    <meta http-equiv="content-language" content="en-us">
    
    <link href="../css/styles.css" rel="stylesheet" type="text/css">
    <link href="../css/bootstrap.css" rel="stylesheet" type="text/css">
    <link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css">
    <script src="../js/jquery.js" type="text/javascript"></script>

<script src="../js/highcharts.js"></script>
<script src="../js/modules/exporting.js"></script>
<script src="../js/themes/grid.js"></script>

<script src="../js/media/js/jquery.dataTables.js"></script>  
<style type="text/css" title="currentStyle">	
	@import "../js/media/css/jquery.dataTables_themeroller.css";
	@import "../js/smoothness/jquery-ui-1.9.2.custom.css";
</style>
</head>

<body>

<div id='content' >
<div id='container-fluid' style="margin: 0px 12% 0px 12%;"> 
	
	                                                            
      <div id="tabela_assets" class="row-fluid " >


<div id="head" class="row-fluid span12" style="margin-bottom: 35px; margin-top:20px;;">
	<div id="titulo" style="margin-top: 5px; margin-bottom: 20px;"> <?php echo __('Assets'); ?> </div>  
</div>
        
      <div id="pad-wrapper" >
		<div id="charts" class="row-fluid chart">
             
        <table id="assets" class="assets" border="0" cellpadding="3" >
          <tbody>
         
            <tr>
            <?php echo "
              <td style='width:100px; text-align:center;'><div id='asset_img' style='background: url(../img/computer.jpg) repeat-x; 
      			background-size: 70px 70px; background-position: center; background-color: ffffff;'></div> </td>
       			
              <td style='width:100px; text-align:center;'><div id='asset_img' style='background: url(../img/monitor.jpg) no-repeat; 
      			background-size: 70px 70px; background-position: center; background-color: ffffff;'></div></td>
      			
              <td style='width:100px; text-align:center;'><div id='asset_img' style='background: url(../img/printer.jpg) repeat-x; 
      			background-size: 70px 70px; background-position: center; background-color: ffffff;'></div></td>      		             
      			
              <td style='width:100px; text-align:center;'><div id='asset_img' style='background: url(../img/network.jpg) repeat-x; 
      			background-size: 70px 70px; background-position: center; background-color: ffffff;'></div> </td>
      			
              <td style='width:100px; text-align:center;'><div id='asset_img' style='background: url(../img/phone.jpg) repeat-x; 
      			background-size: 70px 70px; background-position: center; background-color: ffffff;'></div></td>
              
              <td style='width:100px; text-align:center;'><div id='asset_img' style='background: url(../img/device.jpg) no-repeat; 
      			background-size: 70px 70px; background-position: center; background-color: ffffff;'></div></td>

 					<td style='width:100px; text-align:center;'><div id='asset_img' style='background: url(../img/software.jpg) repeat-x; 
      			background-size: 70px 70px; background-position: center; background-color: ffffff;'></div> </td>

					<td style='width:100px; text-align:center;'><div id='asset_img' style='background: url(../img/cartridges.jpg) repeat-x; 
      			background-size: 70px 70px; background-position: center; background-color: ffffff;'></div> </td>
      			
      			<td style='width:100px; text-align:center;'><div id='asset_img' style='background: url(../img/consumables.jpg) repeat-x; 
      			background-size: 70px 70px; background-position: center; background-color: ffffff;'></div> </td>
              
              <td style='width:100px; text-align:center;'><div id='asset_img' style='background: url(../img/global.jpg) repeat-x; 
      			background-size: 70px 70px; background-position: center; background-color: ffffff;'></div> </td>              
               ";                                 
             ?> 
            </tr>  
            <tr>
            <?php echo '
            	<td> <a href="assets.php#" onclick=showDiv(\'computers\')>
            	'._n('Computer','Computers',2).'<br>'. conta(computers) .'</a></td>
            	
            	<td> <a href="assets.php#" onclick=showDivM(\'monitors\')>
            	'._n('Monitor','Monitors',2).'<br>'. conta(monitors) .'</a></td>
            	
            	<td> <a href="assets.php#" onclick=showDivP(\'printers\')>
            	'._n('Printer','Printers',2).'<br>'. conta(printers) .'</a></td>
            	
            	<td> <a href="assets.php#" onclick=showDivN(\'net\')>
            	'._n('Network','Networks',2).'<br>'. conta(networkequipments) .'</a></td>
            	
            	<td> <a href="assets.php#" onclick=showDivT(\'phone\')>
            	'._n('Phone','Phones',2).'<br>'. conta(phones) .' </a></td>
            	
            	<td> <a href="assets.php#" onclick=showDivD(\'peripheral\')>
            	'._n('Device','Devices',2).'<br>'. conta(peripherals) .' </a></td>
            	
            	<td> <a href="assets.php#" onclick=showDivS(\'soft\')>
            	'._n('Software','Softwares',2).'<br>'. conta(softwares) .'</a></td>
            	
            	<td> <a href="assets.php#" onclick=showDivC(\'cart\')>
            	'._n('Cartridge','Cartridges',2).'<br>'. conta1(cartridges) .'</a></td>
            	
            	<td> '._n('Consumable','Consumables',2).'<br>'. conta1(consumables) .' </td>
            	
            	<td> <a href="assets.php#" onclick=showDivG(\'global\')>
            	'.__('Global').'<br>'. $global .' </a></td> ';
            ?>	
            </tr>                                 
          </tbody>
        </table>
        </div>

<script type="text/javascript">
function showDiv(computers){
	
if (document.getElementById(computers).style.display == 'block') 
  { 		
	document.getElementById(computers).style.display = 'none';
	//document.getElementById(btn_comp).value = 'hide';
	}
else {
	
	document.getElementById(computers).style.display = 'block';
	//document.getElementById(btn_comp).value = 'show';
	}

}
</script>

<!-- <button type="button" onclick="showDiv('computers')" id="btn_comp" value="show" style="margin-top: 20px;">Click Me!</button> --> 

			<div id="computers" style="display:none;" class="row-fluid span12"> 
			
				<div id="graf2" class="span6 row-fluid" style="max-width: 470px; margin-left: -1%;">
					<?php  include('./comp_os.php'); ?>		
				</div>
			
				<div id="graf4" class="span6 row-fluid" style="max-width: 470px;">
					<?php  include('./comp_cat.php'); ?>		
				</div>
				
				<div id="graf_manufac" class="span6 row-fluid" style="max-width: 470px; margin-top:30px; margin-left: -5px; margin-bottom: 30px;">
					<?php  include('./comp_manufac.php'); ?>		
				</div>
								
				<div id="graf_ticket" class="span6 row-fluid" style="max-width: 470px; margin-top:30px; margin-left: 25px; margin-bottom: 30px;">
					<?php  include('./comp_ticket.php'); ?>		
				</div>
				
				<div id="graf_cpu" class="row-fluid" style="margin-left: -5px;">
					<?php  include('./comp_cpu.php'); ?>		
				</div>
						
			</div>
			
			
<script type="text/javascript">
function showDivM(monitors){
	
if (document.getElementById(monitors).style.display == 'block') 
  { 		
	document.getElementById(monitors).style.display = 'none';
	//document.getElementById(btn_comp).value = 'hide';
	}
else {
	
	document.getElementById(monitors).style.display = 'block';
	//document.getElementById(btn_comp).value = 'show';
	}

}
</script>

			<div id="monitors" style="display:none;" class="row-fluid span12"> 
			
				<div id="graf_mon1" class="span6 row-fluid graf2" style="max-width: 470px; margin-left: -1%;">
					<?php  include('./mon_manuf.php'); ?>		
				</div>
				<div id="graf_mon2" class="span6 row-fluid graf4" style="max-width: 470px;">
					<?php  include('./mon_model.php'); ?>		
				</div>
						
			</div>


<script type="text/javascript">
function showDivP(printers){
	
if (document.getElementById(printers).style.display == 'block') 
  { 		
	document.getElementById(printers).style.display = 'none';
	}
else {	
	document.getElementById(printers).style.display = 'block';
	}

}
</script>

			<div id="printers" style="display:none;" class="row-fluid span12"> 
			
				<div id="graf_printer1" class="span6 row-fluid graf2" style="max-width: 470px; margin-left: -1%;">
					<?php  include('./printer_manuf.php'); ?>		
				</div>
				<div id="graf_printer2" class="span6 row-fluid graf4" style="max-width: 470px;">
					<?php  include('./printer_model.php'); ?>		
				</div>
						
			</div>


<script type="text/javascript">
function showDivN(net){
	
if (document.getElementById(net).style.display == 'block') 
  { 		
	document.getElementById(net).style.display = 'none';
	}
else {	
	document.getElementById(net).style.display = 'block';
	}

}
</script>

			<div id="net" style="display:none;" class="row-fluid span12"> 
			
				<div id="graf_net1" class="span6 row-fluid graf2" style="max-width: 470px; margin-left: -1%;">
					<?php  include('./net_manuf.php'); ?>		
				</div>
				<div id="graf_net2" class="span6 row-fluid graf4" style="max-width: 470px;">
					<?php  include('./net_model.php'); ?>		
				</div>
						
			</div>
			
			
<script type="text/javascript">
function showDivT(phone){
	
if (document.getElementById(phone).style.display == 'block') 
  { 		
	document.getElementById(phone).style.display = 'none';
	}
else {	
	document.getElementById(phone).style.display = 'block';
	}

}
</script>

			<div id="phone" style="display:none;" class="row-fluid span12"> 
			
				<div id="graf_phone1" class="span6 row-fluid graf2" style="max-width: 470px; margin-left: -1%;">
					<?php  include('./phone_manuf.php'); ?>		
				</div>
				<div id="graf_phone2" class="span6 row-fluid graf4" style="max-width: 470px;">
					<?php  include('./phone_model.php'); ?>		
				</div>
						
			</div>
			
			
<script type="text/javascript">
function showDivD(peripheral){
	
if (document.getElementById(peripheral).style.display == 'block') 
  { 		
	document.getElementById(peripheral).style.display = 'none';
	}
else {	
	document.getElementById(peripheral).style.display = 'block';
	}

}
</script>

			<div id="peripheral" style="display:none;" class="row-fluid span12"> 
			
				<div id="graf_perip1" class="span6 row-fluid graf2" style="max-width: 470px; margin-left: -1%;">
					<?php  include('./perip_manuf.php'); ?>		
				</div>
				<div id="graf_perip2" class="span6 row-fluid graf4" style="max-width: 470px;">
					<?php  include('./perip_model.php'); ?>		
				</div>
						
			</div>
			
			
<script type="text/javascript">
function showDivS(soft){
	
if (document.getElementById(soft).style.display == 'block') 
  { 		
	document.getElementById(soft).style.display = 'none';
	}
else {	
	document.getElementById(soft).style.display = 'block';
	}

}
</script>

			<div id="soft" style="display:none;" class="row-fluid span12"> 
			
				<div id="graf_soft1" class="span6 row-fluid graf2" style="max-width: 470px; margin-left: -1%;">
					<?php  include('./soft_manuf.php'); ?>		
				</div>
				<div id="graf_soft2" class="span6 row-fluid graf4" style="max-width: 470px;">
					<?php  include('./soft_install.php'); ?>		
				</div>
						
			</div>			
			


<script type="text/javascript">
function showDivC(cart){
	
if (document.getElementById(cart).style.display == 'block') 
  { 		
	document.getElementById(cart).style.display = 'none';
	}
else {	
	document.getElementById(cart).style.display = 'block';
	}

}
</script>

			<div id="cart" style="display:none;" class="row-fluid span12"> 
			
				<div id="graf_cart1" class="span12 row-fluid graf2" style="max-width: 950px; margin-left: -1%;">
					<?php  include('./cart_manuf.php'); ?>	

				</div>
				<div id="graf_cart2" class="span12 row-fluid graf4" style="max-width: 950px; margin-top 6px; margin-left: -1%;">
					<?php  include('./cart_quant.php'); ?>		
				</div>
						
			</div>



<script type="text/javascript">
function showDivG(global){
	
if (document.getElementById(global).style.display == 'block') 
  { 		
	document.getElementById(global).style.display = 'none';
	}
else {	
	document.getElementById(global).style.display = 'block';
	}

}
</script>

			<div id="global" style="display:none;" class="row-fluid span12"> 
			
				<div id="graf_global1" class="span12 row-fluid graf2" style="max-width: 950px; margin-left: -1%;">
					<?php  include('./global_assets.php'); ?>	

				</div>
				<div id="asset_tickets" class="span12 row-fluid graf4" style="margin-top 6px; margin-left: -1%;">
					<?php  include('./global_tickets.php'); ?>		
				</div>
						
			</div>

		 
</div>
</div>

</div>
</div>
</body> </html>
