<?php

if($data_ini == $data_fin) {
$datas = "LIKE '".$data_ini."%'";	
}	

else {
$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";	
}

//satisfaction 	 
   		 
   		 
$query_sat = "SELECT glpi_users.firstname AS name, glpi_users.realname AS sname, COUNT( glpi_tickets.id ) AS conta, 
avg(glpi_ticketsatisfactions.satisfaction) AS media

FROM glpi_tickets, `glpi_ticketsatisfactions`, glpi_tickets_users, glpi_users
WHERE glpi_tickets.is_deleted = '0'
AND `glpi_ticketsatisfactions`.tickets_id = glpi_tickets.id
AND `glpi_ticketsatisfactions`.tickets_id = glpi_tickets_users.tickets_id
AND `glpi_users`.id = glpi_tickets_users.users_id
AND glpi_tickets_users.type = 2
AND glpi_ticketsatisfactions.date_answered ".$datas."
AND glpi_ticketsatisfactions.satisfaction <> 'NULL'
GROUP BY name
ORDER BY media DESC";          		 

$result = $DB->query($query_sat) or die('erro');

$contador = $DB->numrows($result);

//array with satisfaction average

$arr_grfsat = array();

while ($row_result = $DB->fetch_assoc($result))		
	{ 
	$v_row_result = $row_result['name']." ".$row_result['sname'];
	$arr_grfsat[$v_row_result] = round(($row_result['media']*100)/5,1);			
	} 


$grfsat = array_keys($arr_grfsat) ;
$quantsat = array_values($arr_grfsat);

$grfsat2 = implode("','",$grfsat);
$grfsat3 = "'$grfsat2'";
$quantsat2 = implode(',',$quantsat);


if($contador >= 1) {

echo "
<script type='text/javascript'>
$(function () {
	
Highcharts.setOptions({
    colors: [
   '#4572A7', '#000099', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92']
    });	
	
        $('#graf_sat_tec').highcharts({
            chart: {
            type: 'bar',	
				height: 700
                
            },
            title: {
                text: '".__('Satisfaction','dashboard')." ".__('by Technician','dashboard')."'
            },
            legend: {
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'bottom',
                x: 0,
                y: 0,
                //floating: true,
                borderWidth: 1,
                backgroundColor: '#FFFFFF',
                adjustChartSize: true
            },
            xAxis: {
                categories: [".$grfsat3."],
					labels: {
                    //rotation: -55,
                    align: 'right',
                    style: {
                        fontSize: '11px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            
            },	
         
            yAxis: {             
	 						//minPadding: 0, 
   	 					//maxPadding: 0,         
    						min: 0, 
    						//max:1,
   						showLastLabel:false,
    						//tickInterval:1,            
            
                title: { 
                    text: '',
                    style: {
                        color: '#4572A7'
                    }
                },
                labels: {
                    format: '{value} %',
                    style: {
                        color: '#4572A7'
                    }
                },                       	 	
                opposite: false
              },
            
				plotOptions: {
                bar: {
                    pointPadding: 0.2,
  		              borderWidth: 2,
      	           borderColor: 'white',
         	        shadow:true,           
                	  showInLegend: false
                },
                areaspline: {
                    fillOpacity: 0.5
                }
                }, 
            
            tooltip: {
                shared: true
            },
            credits: {
                enabled: false
            },
                          
          series: [
      		                    
					{ // satisfacao
                name: '".__('Satisfaction','dashboard')."',
                color: '#C4D9F1',
                type: 'bar',
               // yAxis: 1,          
          
          		data: [".$quantsat2."],	
          		
                tooltip: {
                    valueSuffix: ' %'
                },
                    dataLabels: {
                    enabled: true,                    
                    color: '#000099',
                    align: 'center',
                    x: 22,
                    y: 0,  
                    format: '{y} %',                                     
                    style: {
                        fontSize: '11px',
                        fontFamily: 'Verdana, sans-serif'
                    },
                    formatter: function () {
                    return Highcharts.numberFormat(this.y, 0, '',''); 
                }                	
                }
                
                }]
                
        });
    });
  </script>  
";

}	 
		?>
