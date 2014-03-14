<?php

define('GLPI_ROOT', '../../..');
include ("../../../inc/includes.php");
//include ("inc/dbFacile.php");
include (GLPI_ROOT . "/config/config.php");

?>

<html> 
<head>
<title>GLPI - Usuários Logados</title>
<!-- <base href= "<?php $_SERVER['SERVER_NAME'] ?>" > -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <meta http-equiv="content-language" content="en-us" />
  <meta http-equiv="refresh" content= "300"/>
  <link href="./css/styles.css" rel="stylesheet" type="text/css" />
  <link href="./css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="./css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
  <link href="./css/lib/font-awesome.css" type="text/css" rel="stylesheet" />
<link href="./inc/calendar/calendar.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../js/jquery.min.js"></script> 
<script language="javascript" src="../inc/calendar/calendar.js"></script>
<script src="./js/highcharts.js"></script>
<script src="./js/themes/grid.js"></script>
<script src="./js/modules/exporting.js"></script>

</head>

<body>

<script src="./js/highcharts.js"></script>
<script src="./js/modules/exporting.js"></script>
<script src="./js/modules/no-data-to-display.js"></script>
<script src="./js/themes/grid.js"></script>

<?php

$datahoje = date("Y-m-d");
//$datahoje = date("Y-m");  

/*
$query = "SELECT DISTINCT `message`, date
FROM `glpi_events`
WHERE `items_id` = '-1'
AND service = 'login'
AND date LIKE '".$datahoje."%'
GROUP BY message
ORDER BY date DESC
";

$result = $DB->query($query);

$arr_id = array();

$DB->data_seek($result,0);

// pegar ids

while($row = $DB->fetch_assoc($result)) {

$login = $row['message'];

$log_user = explode(" ", $login);

	$v_row_result = $log_user['0'];
	$arr_id[$v_row_result] = $row['date'];				
}

//array apenas com ids

$id = array_keys($arr_id) ;
$id2 = implode("','",$id);
$id3 = "'$id2'";

$dates = array_values($arr_id) ;
$dates2 = implode(",",$dates);


$query_user = "
SELECT firstname AS name, realname AS sname, id 
FROM glpi_users
WHERE name IN ('".$id2."')
ORDER BY name
";

$result_user = $DB->query($query_user);

echo '<div class="lead" align="left">';
echo "Usuários Logados: </p>";

while($row_user = $DB->fetch_assoc($result_user)) 
{
	echo $row_user['name']." ".$row_user['sname']." (".$row_user['id'].")<br>";	 	
}	
echo '</div>';

*/
//$file = "../../../files/_sessions/sess_artnnql9160gkonevi93obvi85";

///logados

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

$posicao = strpos($matches[0], 'glpiID|s:');

$string2 = substr($matches[0], $posicao, 25);
$string3 = explode("\"", $string2); 

$arr_ids[] = $string3[1];

}

$ids = array_values($arr_ids) ;
$ids2 = implode("','",$ids);
//$ids3 = "'$ids2'";

$query_name = 
"SELECT firstname AS name, realname AS sname, id AS uid, name AS glpiname 
FROM glpi_users
WHERE id IN ('".$ids2."')
ORDER BY name
"; 

$result_name = $DB->query($query_name);

echo '<div class="lead" align="left">';
echo "Usuários Logados: </p>";

while($row_name = $DB->fetch_assoc($result_name)) 


{
	//echo "<a href=http://10.20.12.107/glpi/plugins/chatajax/index.php?nick=".$row_name['glpiname']." target=_blank >".$row_name['name']." ".$row_name['sname']." (".$row_name['uid'].")</a><br>";	 	
$nick2 = urlencode($row_name['name']." ".$row_name['sname']);
	echo "<a href=http://10.20.12.107/glpi/plugins/chatajax/index.php?nick=".$nick2." target=_blank >".$row_name['name']." ".$row_name['sname']." (".$row_name['uid'].")</a><br>";
	
	//echo "<a href=http://10.20.12.107/glpi/plugins/chatajax/principal.php target=_blank >".$row_name['name']." ".$row_name['sname']." (".$row_name['uid'].")</a><br>";
}	
echo '</div>';

//update alert
 /*
					//update version

					//http://a.fsdn.com/con/app/proj/glpidashboard/screenshots/0.2.7.png
					//version check	
								
					$ver = explode(" ",implode(" ",plugin_version_dashboard())); 									
					
					$versao = $ver[1];
										
					$filename = 'http://a.fsdn.com/con/app/proj/glpidashboard/screenshots/'.$versao.'.png';

if(fopen("http://a.fsdn.com/con/app/proj/glpidashboard/screenshots/".$versao.".png","r"))

//echo $filename."<br>";
//if(fopen("http://www.uol.com.br","r+"))

{   
   print "O arquivo existe";
} else {
   print "O arquivo não existe";
}											   
   */           
   
   
   
//echo date('Y-m-d H:i:s',$timestamp);
/*

echo "<br>";

$date_to_unixtime = strtotime('2014-02-03 14:12:34');

echo $date_to_unixtime;

echo "<br>";

echo time();



echo "<div id='graf1' style='width:800px;'></div>";

//include("./graficos/inc/grafcol_last.inc.php");
*/

/*
$queryc = 
"SELECT count(*) AS conta
FROM glpi_tickets_users
WHERE users_id = 1161
AND type = 2
";
*/

$queryc = 
"SELECT gt.id AS id, gt.name AS name
FROM glpi_tickets_users gtu, glpi_tickets gt
WHERE gtu.users_id = 1161
AND gtu.type = 2
AND gt.is_deleted = 0
AND gt.id = gtu.tickets_id
ORDER BY id DESC
LIMIT 1
";


$res = $DB->query($queryc);
$id = $DB->result($res,0,'id');
$tit = $DB->result($res,0,'name');



$titulo = __('New ticket');

//$text = "Novo Chamado: <a href=../../../front/ticket.form.php?id=".$id.">".$id."</a> <br> ".$tit;

$text = "Novo Chamado: ".$id." ".$tit;


/* 
function chamaphp(){ 
$url = "index2.php"; 
echo"<script>AbreUrl('$url');</script>"; 
} 
*/

   ?>        

<link rel="stylesheet" type="text/css" href="../../gritter/css/jquery.gritter.css" />

<script type="text/javascript" src="../../gritter/js/jquery.gritter.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="./notify.js"></script>



	<script src="./notification.js"></script>
	
	<script src="./notify2.js"></script>


	<button class="notify">NOTIFY!</button>	
		
	<!-- <button onClick="notify2('Novo Chamado','Chamado 1232432');">NOTIFY2!</button> -->	
	
<?php 

if($id != '') {

echo"<script>notify2('".$titulo."','".$text."');</script>"; 
}

echo "<button class=add-sticky onClick=\"notify1('".$titulo."','".$text."');\">Gritter!</button>";

echo "<button onClick=\"notify2('".$titulo."','".$text."');\">NOTIFY2!</button>";
 ?>	
			
<script type="text/javascript" >
//var n = new Notification("Hi!");

//n.onshow = function () { 
//  setTimeout(n.close, 5000); 
//}
</script>			
			
			
	<!-- <button class="add-sticky" onClick="notify1('Título','Conteúdo')">Gritter!</button> -->
		
   <a href="#" id="add-sticky1">Add sticky notification</a>
	

</body>
</html>

