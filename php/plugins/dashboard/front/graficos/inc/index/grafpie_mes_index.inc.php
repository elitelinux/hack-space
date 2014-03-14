<?php

global $DB;

$querym = "
SELECT COUNT(glpi_tickets.id) as tick, glpi_tickets.status as stat
FROM glpi_tickets
WHERE glpi_tickets.date LIKE '$month%'
AND glpi_tickets.is_deleted = 0         
GROUP BY glpi_tickets.status
ORDER BY stat  ASC ";

		
$resultm = $DB->query($querym) or die('erro');

$arr_grfm = array();
while ($row_result = $DB->fetch_assoc($resultm))		
	{ 
	$v_row_result = $row_result['stat'];
	$arr_grfm[$v_row_result] = $row_result['tick'];			
	} 
	
$grfm = array_keys($arr_grfm);
$quantm = array_values($arr_grfm);

$contam = count($arr_grfm);
	
echo '
<!-- Javascript -->
<script type="text/javascript">
$(function () {
    var data = [';
   
for($i = 0; $i < $contam; $i++) {    
     echo ' {label: "'.Ticket::getStatus($grfm[$i]).'", data:'.$quantm[$i].'},';
        }
 
 echo '   ];
 
    var options = {
            series: {
                pie: {
                	innerRadius: 0.5,
                	show: true }
                    }
         };
         
        for (var i=0;i<data.length;i++){
        //put the data value into the label
        data[i].label+= " - "+data[i].data
         }
 
    $.plot($("#piemes"), data, options); 
});
</script>
 
<!-- HTML -->
<div id="legendPlaceholder" style="margin-left:3px;"></div>
<div id="piemes1"></div>
';

		?>
