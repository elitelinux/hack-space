
<?php

if($data_ini == $data_fin) {
$datas = "LIKE '".$data_ini."%'";	
}	

else {
$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";	
}


$query2 = "
SELECT glpi_requesttypes.name AS request, count( glpi_tickets.id ) AS total
FROM `glpi_tickets` , glpi_requesttypes
WHERE glpi_tickets.is_deleted =0
AND glpi_tickets.date ".$datas."
AND glpi_tickets.`requesttypes_id` = glpi_requesttypes.id
GROUP BY request
ORDER BY total DESC";

		
$result2 = $DB->query($query2) or die('erro');

$arr_grf2 = array();
while ($row_result = $DB->fetch_assoc($result2))		
	{ 
	$v_row_result = $row_result['request'];
	$arr_grf2[$v_row_result] = $row_result['total'];			
	} 
	
$grf2 = array_keys($arr_grf2);
$quant2 = array_values($arr_grf2);

$conta = count($arr_grf2);

if($conta > 1) {

echo "
<script type='text/javascript'>

$(function () {		
    	   		
		// Build the chart
        $('#graf4').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '".__('Tickets by Source','dashboard')."'
            },
            tooltip: {
        	    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    size: '85%',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        formatter: function() {
                            return ''+ this.point.y ;
                        }
                    },
                showInLegend: true
                }
            },
            series: [{
                type: 'pie',
                name: '".__('Tickets','dashboard')."',
                data: [
                    {
                        name: '" .$grf2[0]."',
                        y: $quant2[0],
                        sliced: true,
                        selected: true
                    },";
                    
for($i = 1; $i < $conta; $i++) {    
     echo '[ "'.$grf2[$i].'", '.$quant2[$i].'],';
        }                    
                                                         
echo "                ]
            }]
        });
    });

		</script>"; 

	}	
	
		?>
