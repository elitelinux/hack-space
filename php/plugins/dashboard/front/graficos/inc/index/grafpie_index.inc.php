<?php

global $DB;

$query2 = "
SELECT COUNT(glpi_tickets.id) as tick, glpi_tickets.status as stat
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = 0         
GROUP BY glpi_tickets.status
ORDER BY stat  ASC ";

		
$result2 = $DB->query($query2) or die('erro');

$arr_grf2 = array();
while ($row_result = $DB->fetch_assoc($result2))		
	{ 
	$v_row_result = $row_result['stat'];
	$arr_grf2[$v_row_result] = $row_result['tick'];			
	} 
	
$grf2 = array_keys($arr_grf2);
$quant2 = array_values($arr_grf2);


$conta = count($arr_grf2);
	
echo '
<!-- Javascript -->
<script type="text/javascript">
$(function () {
    var data = [';
   
for($i = 0; $i < $conta; $i++) {    
     echo ' {label: "'.Ticket::getStatus($grf2[$i]).'", data:'.$quant2[$i].'},';
        }
 
 echo '   ];
 
    var options = {
            series: {
                pie: {
                	innerRadius: 0.5,
                	show: true
                    }
             }
         };
         
        for (var i=0;i<data.length;i++){
        //put the data value into the label
        data[i].label+= " - "+data[i].data
         }
 
    $.plot($("#pie"), data, options); 
});
</script>
 
<!-- HTML -->
<div id="title"></div>
<div id="legendPlaceholder" style="margin-left:3px;"></div>
<div id="pie1"></div>
';
	
		?>
