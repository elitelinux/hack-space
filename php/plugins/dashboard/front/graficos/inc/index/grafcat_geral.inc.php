
<?php

$query4 = "
SELECT glpi_itilcategories.name as cat_name, COUNT(glpi_tickets.id) as cat_tick
FROM glpi_tickets,  glpi_itilcategories
WHERE glpi_tickets.is_deleted = '0'
AND glpi_itilcategories.id = glpi_tickets.itilcategories_id
GROUP BY glpi_itilcategories.id
ORDER BY `cat_tick` DESC
LIMIT 5
";

$result4 = $DB->query($query4) or die('erro');

$arr_grf4 = array();
while ($row_result = $DB->fetch_assoc($result4))		
	{ 
	$v_row_result = $row_result['cat_name'];
	$arr_grf4[$v_row_result] = $row_result['cat_tick'];			
	} 
	
$grf4 = array_keys($arr_grf4) ;
$quant4 = array_values($arr_grf4) ;
$soma4 = array_sum($arr_grf4);

$grf_2a = implode("','",$grf4);
$grf_3a = "'$grf_2a'";
$quant_2a = implode(',',$quant4);


echo "
<script type='text/javascript'>

$(function () {
        $('#graf4').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: 'Top 5 - ".$LANG['plugin_dashboard']['17']."'
            },
           
            xAxis: {
                categories: [".$grf_3a."],
                labels: {
                    rotation: -55,
                    align: 'right',
                    style: {
                        fontSize: '11px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: ''
                }
            },
            tooltip: {
                headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
                pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +
                    '<td style=\"padding:0\"><b>{point.y:.1f} </b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0,
                    borderWidth: 2,
                borderColor: 'white',
                shadow:true,           
                showInLegend: false,
                }
            },
            series: [{
                name: '".$LANG['plugin_dashboard']['1']."',
                data: [".$quant_2a."],
                dataLabels: {
                    enabled: true,                    
                    color: '#000099',
                    align: 'center',
                    x: 1,
                    y: 1,
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    },
                    formatter: function () {
                    return Highcharts.numberFormat(this.y, 0, '', ''); // Remove the thousands sep?
                }
                }    
            }]
        });
    });

		</script>"; ?>
