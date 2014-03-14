<?php
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/config/config.php");

$ver = explode(" ",implode(" ",plugin_version_dashboard()));
              						                         	            
?>

<html>
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
  <link href="css/styles.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />      
    
    <title>GLPI - Dashboard - Info</title>
  </head>
  <body style="background-color: #fff;">
  
<div class="well info_box" style="width:800px; height:350px; left:47%; margin:20px 0 0 -400px; position:absolute; text-align:center; font-size:14pt;">  
  <br>
    <br>
    <span style="font-weight: bold;">GLPI</span><p>
    <br>
    <?php echo __('Tickets Statistics','dashboard'); ?><br>
    <br>
	 <?php echo __('Version')." ". $ver['1']; ?><br>
    <br><p>
    <?php echo __('Developed by','dashboard'); ?>:
    <br><br>
    <b>Stevenes Donato
    <br>
     <a href="mailto:stevenesdonato@gmail.com"> stevenesdonato@gmail.com </b> </a>
    <br>    
    <br>
    <a href="https://sourceforge.net/projects/glpidashboard" target="_blank" >https://sourceforge.net/projects/glpidashboard</a>
    <br>
    <br>
      
    <button class="btn btn-primary btn-small" type="button" name="home" value="home" onclick="location.href='index.php'" > <i class="icon-white icon-home"></i>&nbsp; HOME </button></td>
  </body>
</div>

</html>
