
<?php

$query_unk = "SELECT count(*) AS total
FROM `glpi_cartridgeitems`
WHERE `is_deleted` = 0
AND `manufacturers_id` = 0";

$result = $DB->query($query_unk) or die('erro');
$unk = $DB->result($result,0,'total');


$query_os = "
SELECT glpi_manufacturers.name AS name, count( glpi_cartridgeitems.id ) AS conta
FROM glpi_manufacturers, glpi_cartridgeitems
WHERE glpi_cartridgeitems.is_deleted = 0
AND glpi_manufacturers.id = glpi_cartridgeitems.manufacturers_id
GROUP BY glpi_manufacturers.name
ORDER BY count( glpi_cartridgeitems.id ) DESC ";

		
$result_os = $DB->query($query_os) or die('erro');

$arr_grf_os = array();

if($unk != 0) {
$arr_grf_os[__('Unknow','dashboard')] = $unk;
}

while ($row_result = $DB->fetch_assoc($result_os))		
	{ 
	$v_row_result = $row_result['name'];
	$arr_grf_os[$v_row_result] = $row_result['conta'];			
	} 
	
$grf_os2 = array_keys($arr_grf_os);
$quant_os2 = array_values($arr_grf_os);

$conta_os = count($arr_grf_os);


echo "
<script type='text/javascript'>

$(function () {		
    	   		
		// Build the chart
        $('#graf_cart1').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '".__('Cartridges by Manufacturer','dashboard')."'
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
                name: '',
                data: [
                    {
                        name: '" . $grf_os2[0] . "',
                        y: $quant_os2[0],
                        sliced: true,
                        selected: true
                    },";
                    
for($i = 1; $i < $conta_os; $i++) {    
     echo '[ "' . $grf_os2[$i] . '", '.$quant_os2[$i].'],';
        }                    
                                                         
echo "                ]
            }]
        });
    });

		</script>"; 
		?>
