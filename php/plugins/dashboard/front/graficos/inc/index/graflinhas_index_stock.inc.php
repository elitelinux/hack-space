
<?php

$version = substr($CFG_GLPI["version"],0,5);

if($version == "0.83") {
	$status = "('assign','new','plan','waiting')";	
}	

else {
	$status = "('2','1','3','4')"	;	
}


$query2 = "
SELECT date AS data, count(id) AS conta
FROM `glpi_tickets` 
WHERE is_deleted = 0
GROUP BY DATE_FORMAT(date, '%Y-%m')
";
		
$result2 = $DB->query($query2) or die('erro');

$arr_grf2 = array();
while ($row_result = $DB->fetch_assoc($result2))		
	{ 
	$v_row_result = strtotime($row_result['data'])*1000;
	$arr_grf2[$v_row_result] = $row_result['conta'];			
	} 

	
$grf2 = array_keys($arr_grf2);
$quant2 = array_values($arr_grf2);

$conta = count($arr_grf2);

$grf2a = implode(",",$grf2);
$quant2a = implode(',',$quant2);



echo "
<script type='text/javascript'>

		// Create the chart
		
		    $(function() {
    		
		$('#graflinhas').highcharts('StockChart', {
			

			rangeSelector : {
				selected : 'all',
//				inputDateFormat: '%d-%m-%Y',
//            inputEditDateFormat: '%d-%m-%Y'
            
			},
			buttonTheme: {
            	width: 50
            },

			title : {
				text : '".$LANG['plugin_dashboard']['1']."'
			},

			 xAxis: {
        	type: 'datetime',
        	dateTimeLabelFormats: {
				second: '%Y-%m-%d<br/>%H:%M:%S',
				minute: '%Y-%m-%d<br/>%H:%M',
				hour: '%Y-%m-%d<br/>%H:%M',
				day: '%b-%y',
				week: '%b-%y',
				month: '%b-%y',
				year: '%Y'
			}
    	},			
			
			
			series : [{
				name : '".$LANG['plugin_dashboard']['1']."',
				type : 'area',
				threshold : null,
				//pointRange: 24 * 3600 * 1000,
				fillColor : {
					linearGradient : {
						x1: 0, 
						y1: 0, 
						x2: 0, 
						y2: 1
					},
					stops : [[0, Highcharts.getOptions().colors[0]], [1, '#F6FAFE']]
				},
				data : [";

for($i=0; $i < $conta; $i++) {
	echo "[".$grf2[$i].",".$quant2[$i]."],";	
}					
				
echo				" ],

 marker : {
					enabled : true,
					radius : 3
				},
				shadow : true,

				
	    		tooltip: {
            shared: true,
				valueDecimals : 0

        },

				
			}]
		});
	});


</script> ";


/*

	    		tooltip: {
            shared: true,
            useHTML: true,
            headerFormat: '<b>'+ Highcharts.dateFormat('%A,  %e, %Y', this.x) +'</br><table>',
            pointFormat: '<tr><td style=\"color: {series.color}\">{series.name}: </td>' +
            '<td style=\"text-align: right\"><b>{point.y}</b></td></tr>',
            footerFormat: '</table>',
            valueDecimals: 0
        },

*/

		?>
