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
    <script src="js/jquery.js" type="text/javascript" ></script> 
	 <script src="js/jquery.min.js"></script>
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
  
	<link href="css/style-dash.css" rel="stylesheet" type="text/css" />
	<link href="css/dashboard.css" rel="stylesheet" type="text/css" />
	

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

<script type="text/javascript" >

function resol() {
	
    lar = screen.width;
    alt = screen.height;
     
    if ((lar <= 100)) {
    window.open('index2.php',"_self")
    }
}
     
</script>  
    
</head>
<body onload="resol();" >
     <!-- navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">           
            <a class="brand" style="font-size:17pt; width:100px; align:center; margin-left:37px;" href="<?php echo 'http://'.$_SERVER['SERVER_ADDR'].'/glpi/front/ticket.php';?>" target="_blank">GLPI</a>
            <ul class="nav">                 
                <li style="font-size:12pt;">
                    <a class="dropdown-toggle hidden-phone" >
                        <?php echo __('Tickets Statistics','dashboard'); ?>                      
                    </a>
                 </li>
            </ul>  
 
            
<!-- <div id="datetime" style="width:320px; margin-left:71%; margin-top:13px; color:#d6d6d6; font-size:11pt; "> -->

<div id="datetime" class="row-fluid" style="margin-top:13px; color:#d6d6d6; font-size:11pt; float:right; width:320px; ">
 
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

<div id="clock" class="row-fluid" ></div>

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
                <a class="dropdown-toggle" href="#">
                    <i class="icon-edit"></i>
                    <span><?php echo __('Tickets','dashboard'); ?></span>
                    <i class="icon-chevron-down" style="width:12px; heigth:12px;"></i>
                </a>
                <ul class="submenu">
						  <li><a href="./tickets/chamados.php" target="_blank"><?php echo __('Overall','dashboard'); ?></a></li>
						  <li><a href="./tickets/select_ent.php" target="_blank"><?php echo __('by Entity','dashboard'); ?></a></li>						                                                                              
						  <li><a href="./tickets/select_grupo.php" target="_blank"><?php echo __('by Group','dashboard'); ?></a></li>
                </ul>
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
                <a href="assets/assets.php" target="_blank">
                    <i class="icon-print"></i>
                    <span><?php echo __('Assets'); ?></span>
                </a>
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
					
					//print_r($headers);

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
                    
                    <div class="span3 stat">
                         <div id="odometer1" class="odometer" style="color: #32a0ee; font-size: 25px; margin-right: 15px;">
                            <span class="number"><?php //echo $total_hoje['total'];  ?></span>                            
                        </div>
                        <span class="chamado"><?php echo __('Tickets','dashboard'); ?></span>
                        <span class="date" style="font-size: 15pt; margin-top:30px;"><b><?php echo __('Today','dashboard'); ?></b></span>
                    </div>
                    <div class="span3 stat">
                        <div id="odometer2" class="odometer" style="color: #32a0ee; font-size: 25px; margin-right: 15px;">
                            <span class="number"><?php //echo $total_mes['total'];  ?></span>                            
                        </div>
                        <span class="chamado"><?php echo __('Tickets','dashboard'); ?></span>
                        <span class="date" style="font-size: 15pt; margin-top:30px;"><b><?php echo $mes ?></b></span>
                    </div>
                    <div class="span3 stat">
                        <div id="odometer3" class="odometer" style="color: #32a0ee; font-size: 25px; margin-right: 15px;">
                            <span class="number"><?php //echo $total_ano['total'];  ?></span>
                        </div>
                        <span class="chamado"><?php echo __('Tickets','dashboard'); ?></span>
                        <span class="date" style="font-size: 15pt; margin-top:30px;"><b><?php echo __('Total','dashboard'); ?></b></span>
                    </div>
                    <div class="span3 stat">
                        <div id="odometer4" class="odometer" style="color: #32a0ee; font-size: 25px; margin-right: 15px;">
                            <span class="number"><?php //echo $total_users['total'];  ?></span>
                        </div>
                        <span class="chamado"><?php echo __('users','dashboard'); ?></span>
                        <span class="date" style="font-size: 15pt; margin-top:30px;"><b> <?php //echo __('users'); ?></b></span>
                    </div>
                    
                </div>
            </div>
            <!-- end upper main stats -->
  
  <script type="text/javascript" >
window.odometerOptions = {
  format: '( ddd).dd'
};

setTimeout(function(){
    odometer1.innerHTML = <?php echo $total_hoje['total']; ?>;
    odometer2.innerHTML = <?php echo $total_mes['total']; ?>;
    odometer3.innerHTML = <?php echo $total_ano['total']; ?>;
    odometer4.innerHTML = <?php echo $total_users['total']; ?>;
}, 1000);

</script> 

<div id='content2a' class="container-fluid" style=' display:block; margin-top: 0px; '> 

 <div class="container-fluid">

 <div id="pad-wrapper">

	<div class="row-fluid chart"  >
      <h4> <?php echo __('Tickets Evolution','dashboard'); ?> </h4>
      <p id="choices" style="float:right; width:300px; margin-right: 0px; margin-top: 5px; text-align:right;"></p>	 
 	
		<div class="demo-container">
			<div id="graflinhas1" class="demo-placeholder" style="float:left;"></div>
		
		</div>
		</div>

	   <?php 
			include ("graficos/inc/index/graflinhas_index_sel.inc.php");
		?>					
	
	<div class="row-fluid section">
	
	<div class="knobs">
	
		<div class="span6 knob-wrapper">
			<div id="pie1" style="height:300px;"> 			
				<?php
				include ("graficos/inc/index/grafpie_index1.inc.php");
				?> 	 						            
			</div> 
		</div>

		<div class="span6 knob-wrapper">
			<div id="pie2" style="height:300px;"> 
				<?php
				include ("graficos/inc/index/grafstat_geral.inc.php");
				?> 	 				              
			</div> 
  		</div>
  
  </div>
  </div>

	<div class="row-fluid section">
	
	<div id="last_tickets" class="span6 showcase" style="height:300px;  "> 
 	 				              
		      <div class="widget widget-table action-table">
            <div class="widget-header"> <i class="icon-list-alt"></i>

              <h3><a href="../../../front/ticket.php" target="_blank" style="color: #525252;"><?php echo __('Last Tickets','dashboard'); ?></a></h3>
             <!-- <div id="refresh-users" onClick="javascript:get_users();" class="btn btn-mini">
                <i class="icon-refresh"></i> &nbsp;&nbsp;Refresh
              </div>
              -->
            </div>
            <!-- /widget-header -->
            <div class="widget-content" style="height:318px;">
            <?php

$version = substr($CFG_GLPI["version"],0,5);

if($version == "0.83") {
	$status = "('assign','new','plan','waiting')";	
}	

else {
	$status = "('2','1','3','4')"	;	
}            
                        
            $query_wid = "
            SELECT glpi_tickets.id AS id, glpi_tickets.name AS name
				FROM glpi_tickets
				WHERE glpi_tickets.is_deleted = 0
				AND glpi_tickets.status IN $status
				ORDER BY id DESC
				LIMIT 10          
            ";
            
            $result_wid = $DB->query($query_wid);			            
            
            ?>    
              <table id="last_tickets" class="table table-hover table-bordered table-condensed" >
              <th style="text-align: center;"><?php echo __('Tickets','dashboard'); ?></th><th style="text-align: center;" ><?php echo __('Title','dashboard'); ?></th>
              
				<?php
					while($row_c = $DB->fetch_assoc($result_wid)) 
					{					
						echo "<tr><td style='text-align: center;'><a href=../../../front/ticket.form.php?id=".$row_c['id']." target=_blank style='color: #526273;'>".$row_c['id']."</a>
						</td><td>". substr($row_c['name'],0,60)."</td></tr>";											
					}				
				?>                                       
              </table>
              
            </div>
            <!-- /widget-content --> 
          </div>
	</div> 


	<div id="open_tickets" class="span6 showcase" style="height:300px;"> 
	
	<div class="widget widget-table action-table">
            <div class="widget-header"> <i class="icon-list-alt"></i>

            <h3><a href="../../../front/ticket.php" target="_blank" style="color: #525252;"><?php echo __('Open Tickets','dashboard'). " " .__('by Technician','dashboard') ?></a></h3>
           
            </div>
            <!-- /widget-header -->
            <div class="widget-content" style="height:318px;">
            <?php
                                
            $query_op = "
            SELECT DISTINCT glpi_users.id AS id, glpi_users.`firstname` AS name, glpi_users.`realname` AS sname, count(glpi_tickets_users.tickets_id) AS tick
				FROM `glpi_users` , glpi_tickets_users, glpi_tickets
				WHERE glpi_tickets_users.users_id = glpi_users.id
				AND glpi_tickets_users.type = 2
				AND glpi_tickets.is_deleted = 0
				AND glpi_tickets.id = glpi_tickets_users.tickets_id
				AND glpi_tickets.status IN ".$status."
				GROUP BY `glpi_users`.`firstname` ASC
				ORDER BY tick DESC
				LIMIT 10         
            ";
            
            $result_op = $DB->query($query_op);			            
            
            ?>    
              <table id="open_tickets" class="table table-hover table-bordered table-condensed" >
              <th style="text-align: center;"><?php echo __('Technician','dashboard'); ?></th><th style="text-align: center;">
              <?php echo __('Open Tickets','dashboard'); ?></th>
              
				<?php
					while($row_o = $DB->fetch_assoc($result_op)) 
					{					
						echo "<tr><td><a href=./rel_tecnico.php?con=1&tec=".$row_o['id']."&stat=open target=_blank style='color: #526273;'>
						".$row_o['name']." ".$row_o['sname']."</a></td><td style='text-align: center;' >".$row_o['tick']."</td></tr>";											
					}				
				?>                                       
              </table>
              
            </div>
            <!-- /widget-content --> 
          </div>
       </div>   

</div>


<div class="row-fluid section">

		<div id="logged_users" class="span6 showcase" style="height:300px;"> 
 	 				              
		      <div class="widget widget-table action-table">
            <div class="widget-header"> <i class="icon-group"></i>
 
            <?php


//logados

 $path = "../../../files/_sessions/";
 $diretorio = dir($path);        

$arr_arq = array();    
       
   while($arquivo = $diretorio -> read()){   
      
     $arr_arq[] = $path.$arquivo;           
   }
   $diretorio -> close();


foreach ($arr_arq as $listar) {
// retira "./" e "../" para que retorne apenas pastas e arquivos
  
   if ($listar!="." && $listar!=".."){ 
			$arquivos[]=$listar;
   }
}

$conta = count($arquivos);

for($i=0;$i<$conta;$i++) {

$file = $arquivos[$i];

$string = file_get_contents( $file ); 
// poderia ser um string ao invés de file_get_contents().

$list = preg_match( '/glpiID\|s:[0-9]:"(.+)/', $string, $matches );

$posicao = strpos($matches['0'], 'glpiID|s:');

$string2 = substr($matches['0'], $posicao, 25);
$string3 = explode("\"", $string2); 

$arr_ids[] = $string3['1'];

}

//print_r($arr_ids);

$ids = array_values($arr_ids) ;
$ids2 = implode("','",$ids);

$query_name = 
"SELECT firstname AS name, realname AS sname, id AS uid, name AS glpiname 
FROM glpi_users
WHERE id IN ('".$ids2."')
ORDER BY name
"; 

$result_name = $DB->query($query_name); 

$num_users = $DB->numrows($result_name);          
            
            ?>    
            
                  <h3><?php echo __('Logged Users','dashboard')."  :  " .$num_users; ?></h3>

            </div>
            <!-- /widget-header -->
<?php
	          if($num_users <= 10) {
	          	echo '<div class="widget-content" style="height:318px;">'; }
	          else {
	          	echo '   <div class="widget-content"">'; }	          	
?>        
              <table id="logged_users" class="table table-hover table-bordered table-condensed" >
        <!--      <th style="text-align: center;"><?php echo __('','dashboard'); ?></th> -->              
				<?php
								
				while($row_name = $DB->fetch_assoc($result_name)) 

					{
						echo "<tr><td style='text-align: left;'><a href=../../../front/user.form.php?id=".$row_name['uid']." target=_blank style='color: #526273;'>
						".$row_name['name']." ".$row_name['sname']." (".$row_name['uid'].")</a>	</td></tr>";												
					}	

				?>                                       
              </table>
              
            </div>
            <!-- /widget-content --> 
          </div>
	</div>          
          


			<div id="events" class="span6 showcase" style="height:300px;"> 
 	 				              
		      <div class="widget widget-table action-table">
            <div class="widget-header"> <i class="icon-list-alt"></i>

              <h3><a href="../../../front/event.php" target="_blank" style="color: #525252;"><?php echo __('Last Events','dashboard'); ?></a></h3>

            </div>
            <!-- /widget-header -->
            <div class="widget-content">
   
         <?php

	$query_evt = "
	SELECT *
       FROM `glpi_events`
	ORDER BY `glpi_events`.id DESC
	LIMIT 10
	";   
	
	$result_evt = $DB->query($query_evt);
	$number = $DB->numrows($result_evt);

function tipo($type) {

    switch ($type) {
    case "system": $type 	  = __('System'); break;
    case "ticket": $type 	  = __('Ticket'); break;
    case "devices": $type 	  = _n('Component', 'Components', 2); break;
    case "planning": $type 	  = __('Planning'); break;
    case "reservation": $type = _n('Reservation', 'Reservations', 2); break;
    case "dropdown": $type 	  = _n('Dropdown', 'Dropdowns', 2); break;
    case "rules": $type 	  = _n('Rule', 'Rules', 2); break;
   };
	return $type;
	}


function servico($service) {

    switch ($service) {
    case "inventory": $service 	  = __('Assets'); break;
    case "tracking": $service 	  = __('Ticket'); break;
    case "maintain": $service 	  = __('Assistance'); break;
    case "planning": $service  	  = __('Planning'); break;
    case "tools": $service 	  	  = __('Tools'); break;
    case "financial": $service 	  = __('Management'); break;
    case "login": $service 	         = __('Connection'); break;
    case "setup": $service 	  	  = __('Setup'); break;
    case "security": $service 	  = __('Security'); break;
    case "reservation": $service     = _n('Reservation', 'Reservations', 2); break;
    case "cron": $service 	  	  = _n('Automatic action', 'Automatic actions', 2); break;
    case "document": $service 	  = _n('Document', 'Documents', 2); break;
    case "notification": $service    = _n('Notification', 'Notifications', 2); break;
    case "plugin": $service 	  = __('Plugin'); break;
   }

	return $service;
	}
	
	          ?>    
            <table id="events" class="table table-hover table-bordered table-condensed" >
            <th style="text-align: center;"><?php echo __('Type'); ?></th>
				<th style="text-align: center;"><?php echo __('Date'); ?></th>
				<!-- <th style="text-align: center;"><?php echo __('Service'); ?></th>  -->
				<th style="text-align: center;"><?php echo __('Message'); ?></th>                 
				<?php
			   $i = 0;	
			   while ($i < $number) {
			   
				  $type     = $DB->result($result_evt, $i, "type");
       			  $date     = date_create($DB->result($result_evt, $i, "date"));
			        // $service  = $DB->result($result_evt, $i, "service");         
			        
			         $message  = $DB->result($result_evt, $i, "message");
				
				echo "<tr><td style='text-align: left;'>". tipo($type) ."</td>
						<td style='text-align: left;'>" . date_format($date, 'Y-m-d H:i:s') . "</td>					
						<td style='text-align: left;'>". substr($message,0,50) ."</td></tr>
				";
				++$i;													
				}
								
		?>                                       
              </table>  
              
            </div>
            <!-- /widget-content --> 
          </div>
	</div>
          <!-- content row-fluid 2 --> 
	</div>          
          
   </div>
   </div>  
    
	</div>
	</div>

</div> 
</body>
</html>
