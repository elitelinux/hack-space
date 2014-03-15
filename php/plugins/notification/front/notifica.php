<?php

//define('GLPI_ROOT', '../..');
//include ('http://'.$_SERVER['SERVER_ADDR'].'/glpi/inc/includes.php');
//include (GLPI_ROOT . "/config/config.php");


echo "<script type='text/javascript' src='http://".$_SERVER['SERVER_ADDR']."/glpi/plugins/notification/front/js/notify.js'></script>";

global $DB;

$sql = "
SELECT COUNT(gt.id) AS total
FROM glpi_tickets_users gtu, glpi_tickets gt
WHERE gtu.users_id = ". $_SESSION['glpiID'] ."
AND gtu.type = 2
AND gt.is_deleted = 0
AND gt.id = gtu.tickets_id" ;

$resulta = $DB->query($sql);
$data = $DB->result($resulta,0,'total');

$abertos = $data; 

$init = $data - 1;

$query_u = "
INSERT IGNORE INTO glpi_plugin_notification_count (users_id, quant) 
VALUES ('". $_SESSION['glpiID'] ."', '" . $init ."')  ";

$result_u = $DB->query($query_u);


$query = "SELECT users_id, quant 
FROM glpi_plugin_notification_count
WHERE users_id = ". $_SESSION['glpiID'] ."
" ;

$result = $DB->query($query);

$user = $DB->result($result,0,'users_id');
$atual = $DB->result($result,0,'quant');


//update tickets count

$query_up = "UPDATE glpi_plugin_notification_count 
SET quant=". $data ."
WHERE users_id = ". $_SESSION['glpiID'] ." ";

$result_up = $DB->query($query_up);

if($abertos > $atual) {
	
	$dif = $abertos - $atual;
	
	if($dif >= 5) { $dif = 5; }


$queryc = 
"SELECT gt.id AS id, gt.name AS name 
FROM glpi_tickets_users gtu, glpi_tickets gt
WHERE gtu.users_id = ". $_SESSION['glpiID'] ."
AND gtu.type = 2
AND gt.is_deleted = 0
AND gt.id = gtu.tickets_id
ORDER BY id DESC
LIMIT ".$dif." ";

$res = $DB->query($queryc);

while($row = $DB->fetch_assoc($res)) {

$icon = "../plugins/notification/front/img/icon.png";
$titulo = __('New ticket');
$text = __('New ticket').": ".$row['id']." - ".$row['name'];

//$text = __('New ticket').": <a href=../../../front/ticket.form.php?id=".$id.">".$row['id']."</a> - ".$row['name'];

echo"<script>notify('".$titulo."','".$text."','".$icon."');</script>"; 

	}

}
 ?>	
