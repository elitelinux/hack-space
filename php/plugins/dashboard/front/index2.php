<!DOCTYPE html>
<html>

<?php
define('GLPI_ROOT', '../../..');
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

   switch (date("w")) {
    case "0": $dia = __('Sunday','dashboard'); break;    
    case "1": $dia = __('Monday','dashboard'); break;
    case "2": $dia = __('Tuesday','dashboard'); break;
    case "3": $dia = __('Wednesday','dashboard'); break;
    case "4": $dia = __('Thursday','dashboard'); break;
    case "5": $dia = __('Friday','dashboard'); break;
    case "6": $dia = __('Saturday','dashboard'); break;  
    }

?>

<head>
    <title>GLPI - Dashboard - Home</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content= "120"/>
	
    <!-- bootstrap -->
    <link href="css/bootstrap.css" rel="stylesheet" />
    <link href="css/bootstrap-responsive.css" rel="stylesheet" />
    <link href="css/bootstrap-overrides.css" type="text/css" rel="stylesheet" />

    <!-- libraries -->
    <link href="css/lib/jquery-ui-1.10.2.custom.css" rel="stylesheet" type="text/css" />
    <link href="css/lib/font-awesome.css" type="text/css" rel="stylesheet" />

    <!-- global styles -->
    <link rel="stylesheet" type="text/css" href="css/layout.css">
    <link rel="stylesheet" type="text/css" href="css/elements.css">
    <link rel="stylesheet" type="text/css" href="css/icons.css">

    <!-- this page specific styles -->
    <link rel="stylesheet" href="css/compiled/index.css" type="text/css" media="screen" />    

    <!-- open sans font -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>

    <!-- lato font -->
    <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700,900,300italic,400italic,700italic,900italic' rel='stylesheet' type='text/css'>

    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <link href="css/styles.css" rel="stylesheet" type="text/css" />
    <script src="js/jquery.min.js" type="text/javascript" ></script> 
    <script src="js/highcharts.js" type="text/javascript" ></script>
    <script src="js/modules/exporting.js" type="text/javascript" ></script>

	<!-- scripts -->
  <!--  <script src="http://code.jquery.com/jquery-latest.js"></script> -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-ui-1.10.2.custom.min.js"></script>
    <!-- knob -->
    <script src="js/jquery.knob.js"></script>
    <!-- flot charts -->
    <script src="js/jquery.flot.js"></script>
    <script src="js/jquery.flot.stack.js"></script>
    <script src="js/jquery.flot.resize.js"></script>
    <script src="js/jquery.flot.pie.min.js"></script>
    <script src="js/jquery.flot.valuelabels.js"></script>
    <script src="js/theme.js"></script>     
    
    <script src="js/jquery.jclock.js"></script>
    
    <!-- odometer -->
    <link href="css/odometer.css" rel="stylesheet">
    <script src="js/odometer.js"></script>
    

<script type="text/javascript">
$(function($) {
var options = {
timeNotation: '24h',
am_pm: false,
fontFamily: 'Open Sans',
fontSize: '11pt',
foreground: '#d6d6d6'
}
$('#clock').jclock(options);
});
</script>   

   
</head>
<body>
     <!-- navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">           
            <a class="brand" style="font-size:17pt; width:85px; align:center; margin-left:52px;" href="<?php echo 'http://'.$_SERVER['SERVER_ADDR'].'/glpi/front/ticket.php';?>" target="_blank">GLPI</a>
            <ul class="nav">                 
                <li style="font-size:12pt;">
                    <a class="dropdown-toggle hidden-phone" >
                        <?php echo __('Tickets Statistics','dashboard'); ?>                      
                    </a>
                 </li>
            </ul>  
 
            
<div id="datetime" style="width:320px; margin-left:70%; margin-top:13px; color:#d6d6d6; font-size:11pt; "> 
 
<table>
<tr><td>     
<div id="date" >
<script type="text/javascript">

var d_names = <?php echo '"'.$dia.'"' ; ?>;

var m_names = <?php echo '"'.$mes.'"' ; ?>;

var d = new Date();
var curr_day = d.getDay();
var curr_date = d.getDate();
var curr_month = d.getMonth();
var curr_year = d.getFullYear();

document.write(d_names + ", " + curr_date + " " + m_names + " " + curr_year);

</script> 
</div>

</td> 
<td>&nbsp;</td>
<td> 

<div id="clock" ></div>

</tr></td>    
</table>
</div>          
              
        </div>
    </div>
    <!-- end navbar -->

    <!-- sidebar -->
    <div id="sidebar-nav" style="position: fixed; margin-top:20px;">
        <ul id="dashboard-menu">
            <li class="active">
                <div class="pointer">
                    <div class="arrow"></div>
                    <div class="arrow_border"></div>
                </div>
                <a href="index.php">
                    <i class="icon-home"></i>
                    <span><?php echo __('Home','dashboard'); ?></span>
                </a>
            </li>

           <li>
                <div class="pointer">
                </div>
                <a href="chamados.php" target="_blank">
                    <i class="icon-edit"></i>
                    <span><?php echo __('Tickets','dashboard'); ?></span>
                </a>
            </li>            				         
                                            
				<li>
                <a class="dropdown-toggle" href="#">
                    <i class="icon-bar-chart"></i>
                    <span><?php echo __('Charts','dashboard'); ?></span>
                    <i class="icon-chevron-down"></i>
                </a>
                <ul class="submenu">

                    <li><a href="./graficos/geral.php" target="_blank"><?php echo __('Overall','dashboard'); ?></a></li>
                    <li><a href="./graficos/tecnicos.php" target="_blank"><?php echo __('Technician','dashboard')."s"; ?></a></li>
                    <li><a href="./graficos/usuarios.php" target="_blank"><?php echo __('Requester','dashboard')."s"; ?></a></li>                                                            
						  <li><a href="./graficos/grupos.php" target="_blank"><?php echo __('Group','dashboard')."s"; ?></a></li>					                    
						  <li><a href="./graficos/entidades.php" target="_blank"><?php echo __('Entity','dashboard')."s"; ?></a></li>
						  <li><a href="./graficos/satisfacao.php" target="_blank"><?php echo __('Satisfaction','dashboard'); ?></a></li>                       
                    <li><a href="./graficos/graf_tecnico.php" target="_blank"><?php echo __('by Technician','dashboard'); ?></a></li>
                    <li><a href="./graficos/graf_usuario.php" target="_blank"><?php echo __('by Requester','dashboard'); ?></a></li>
						  <li><a href="./graficos/graf_grupo.php" target="_blank"><?php echo __('by Group','dashboard'); ?></a></li> 						  
 						  <li><a href="./graficos/graf_entidade.php" target="_blank"><?php echo __('by Entity','dashboard'); ?></a></li>                    
                    <li><a href="./graficos/geral_mes.php" target="_blank"><?php echo __('by Date','dashboard'); ?></a></li>
                     
                </ul>
            </li>                 
                 

 					<li>
                <a class="dropdown-toggle" href="#">
                    <i class="icon-list-alt"></i>
                    <span><?php echo __('Reports','dashboard'); ?></span>
                    <i class="icon-chevron-down" style="width:12px; heigth:12px;"></i>
                </a>
                <ul class="submenu">
					     <li><a href="./rel_tecnico.php" target="_blank"><?php echo __('by Technician','dashboard'); ?></a></li>
						  <li><a href="./rel_usuario.php" target="_blank"><?php echo __('by Requester','dashboard'); ?></a></li>
						  <li><a href="./rel_grupo.php" target="_blank"><?php echo __('by Group','dashboard'); ?></a></li>
						  <li><a href="./rel_entidade.php" target="_blank"><?php echo __('by Entity','dashboard'); ?></a></li>
						  <li><a href="./rel_data.php" target="_blank"><?php echo __('by Date','dashboard'); ?></a></li>                                         
                                      
                </ul>
            	</li>                       
                 
                 
            <li>
                <a href="info.php" target="_blank">
                    <i class="icon-info-sign"></i>
                    <span>Info</span>
                </a>
            </li>
            
               <li>
              <?php

              //version check	
              								
					$ver = explode(" ",implode(" ",plugin_version_dashboard())); 																																										
									
					$urlv = "http://a.fsdn.com/con/app/proj/glpidashboard/screenshots/".$ver[1].".png";

					$headers = get_headers($urlv, 1);

					if($headers[0] != '') {

					//if ($headers[0] == 'HTTP/1.1 200 OK') { }

					if ($headers[0] == 'HTTP/1.0 404 Not Found') {
						echo '<a href="https://sourceforge.net/projects/glpidashboard/files/?source=navbar" target="_blank">
					 	<i class="icon-refresh"></i>                   
                    <span>'. __('New version','dashboard').'</span>
                     <span>'.__('avaliable','dashboard').'</span></a>';			
							}
						}
				  ?>
            </li> 
            
        </ul>
    </div>
    <!-- end sidebar -->
    
<?php     

//plugin version             	
//$get_ver = implode(" ",plugin_version_dashboard());
   
//$ver = explode(" ",implode(" ",plugin_version_dashboard()));
//echo $ver['1'];              						                         	


$ano = date("Y");
$month = date("Y-m");
$hoje = date("Y-m-d");

//chamados ano

$sql_ano =	"SELECT COUNT(glpi_tickets.id) as total        
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.is_deleted = '0'
      ";

$result_ano = $DB->query($sql_ano);
$total_ano = $DB->fetch_assoc($result_ano);
      
//chamados mes

$sql_mes =	"SELECT COUNT(glpi_tickets.id) as total        
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.date LIKE '$month%'      
      AND glpi_tickets.is_deleted = '0'
      ";

$result_mes = $DB->query($sql_mes);
$total_mes = $DB->fetch_assoc($result_mes);

//chamados dia

$sql_hoje =	"SELECT COUNT(glpi_tickets.id) as total        
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.date like '$hoje%'      
      AND glpi_tickets.is_deleted = '0'
      ";

$result_hoje = $DB->query($sql_hoje);
$total_hoje = $DB->fetch_assoc($result_hoje);

// total users

$sql_users = "SELECT COUNT(id) AS total
FROM `glpi_users`
WHERE is_deleted = 0
AND is_active = 1";

$result_users = $DB->query($sql_users);
$total_users = $DB->fetch_assoc($result_users);

?>
    <!-- main container -->
    <div class="content">

           <div class="container-fluid">

            <!-- upper main stats -->
            <div id="main-stats" style="margin-top: 50px;">
                <div class="row-fluid stats-row">
                    
                    <div class="span4 stat">
                         <div id="odometer1" class="odometer" style="color: #32a0ee; font-size: 25px; margin-right: 15px;">
                            <span class="number"><?php //echo $total_hoje['total'];  ?></span>                            
                        </div>
                        <span class="chamado"><?php echo __('Tickets','dashboard'); ?></span>
                        <span class="date" style="font-size: 15pt; margin-top:30px;"><b><?php echo __('Today','dashboard'); ?></b></span>
                    </div>
                    <div class="span4 stat">
                        <div id="odometer2" class="odometer" style="color: #32a0ee; font-size: 25px; margin-right: 15px;">
                            <span class="number"><?php //echo $total_mes['total'];  ?></span>                            
                        </div>
                        <span class="chamado"><?php echo __('Tickets','dashboard'); ?></span>
                        <span class="date" style="font-size: 15pt; margin-top:30px;"><b><?php echo $mes ?></b></span>
                    </div>
                    <div class="span4 stat">
                        <div id="odometer3" class="odometer" style="color: #32a0ee; font-size: 25px; margin-right: 15px;">
                            <span class="number"><?php //echo $total_ano['total'];  ?></span>
                        </div>
                        <span class="chamado"><?php echo __('Tickets','dashboard'); ?></span>
                        <span class="date" style="font-size: 15pt; margin-top:30px;"><b><?php echo __('Total','dashboard'); ?></b></span>
                    </div>
                   <!-- <div class="span3 stat">
                        <div id="odometer4" class="odometer" style="color: #32a0ee; font-size: 25px; margin-right: 15px;">
                            <span class="number"><?php //echo $total_users['total'];  ?></span>
                        </div>
                        <span class="chamado"><?php //echo $LANG['plugin_dashboard']['12']; ?></span>
                        <span class="date" style="font-size: 15pt; margin-top:30px;"><b> <?php //echo $LANG['plugin_dashboard']['13']; ?></b></span>
                    </div> -->
                    
                </div>
            </div>
            <!-- end upper main stats -->
  
  <script type="text/javascript" >
setTimeout(function(){
    odometer1.innerHTML = <?php echo $total_hoje['total']; ?>;
    odometer2.innerHTML = <?php echo $total_mes['total']; ?>;
    odometer3.innerHTML = <?php echo $total_ano['total']; ?>;
    odometer4.innerHTML = <?php echo $total_users['total']; ?>;
}, 1000);
window.odometerOptions = {
  format: '( ddd).dd'
};
</script>          
         

<?php 
include ("graficos/inc/index/graflinhas_index.inc.php");
?>

<div id='content2' style='min-height:950px; display:block; margin-left:3%; width:80%;'> 
<div id='container' style='max-width:95%; width:800px; height:680px; left:52%; margin:0 0 0 -400px; position:absolute; '>
 
    <div id="pad-wrapper" style="height:60px; width:260px;  ">             
                <div class="row-fluid chart" style="margin-bottom:40px;" >
                    <h4> <?php echo __('Tickets Evolution','dashboard'); ?> </h4> </div>

					<div id="graflinhas" class="span1" style="height:300px; width:760px;"></div> 
    </div>                     	

   <div id="separa" class="span1" style="width:500px; height:75px"></div>


<div id="pie_container">

<h4 style="margin-bottom:45px;"><?php echo __('Tickets by Status','dashboard'); ?></h4>
  
  		<?php
           echo $mes.'&nbsp;&nbsp;&nbsp;&nbsp;';
           include ("graficos/inc/index/grafpie_mes_index.inc.php");
      ?>


	<div id="piemes" > 
		<?php
//		echo $mes.'&nbsp;&nbsp;&nbsp;&nbsp;';
//		include ("graficos/inc/index/grafpie_mes_index.inc.php");			
		?>				

	</div> 	
		<div id="pie" > 
		<?php
		include ("graficos/inc/index/grafpie_index.inc.php");
		?>
		Total 
	</div>
	
</div>
		
 	<div id="graf31" > 
		<?php
		$sql_ent = "SELECT COUNT(id) AS conta FROM `glpi_entities` ";
		$result_ent = $DB->query($sql_ent) or die('erro');
		$num_ent = $DB->fetch_assoc($result_ent);
					

		if($num_ent['conta'] > 3) {		
			include ("graficos/inc/index/grafent_index.inc.php");
			}
		else {
			include ("graficos/inc/index/grafbar_grupo_index.inc.php");
				}

		?> 	 				              
    </div>           


</div>
</div> 

</body>
</html>
