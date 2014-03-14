
<?php

if($data_ini == $data_fin) {
$datas = "LIKE '".$data_ini."%'";	
}	

$data1 = $data_ini;
$data2 = $data_fin;

$unix_data1 = strtotime($data1);
$unix_data2 = strtotime($data2);

$interval = ($unix_data2 - $unix_data1) / 86400;


if($interval >= "31") {

$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";

//chamados mensais

 $querym = "
SELECT DISTINCT DATE_FORMAT(date, '%b-%Y') as day_l,  COUNT(id) as nb, DATE_FORMAT(date, '%y-%m') as day
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.date ".$datas."
GROUP BY day
ORDER BY day ";
}

else {
$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";

//chamados mensais

 $querym = "
SELECT DISTINCT DATE_FORMAT(date, '%b-%d') as day_l,  COUNT(id) as nb, DATE_FORMAT(date, '%Y-%m-%d') as day
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.date ".$datas."
GROUP BY day
ORDER BY day ";
}

$resultm = $DB->query($querym) or die('erro');

$contador = $DB->numrows($resultm);

$arr_grfm = array();
while ($row_result = $DB->fetch_assoc($resultm))		
	{ 
	$v_row_result = $row_result['day_l'];
	$arr_grfm[$v_row_result] = $row_result['nb'];			
	} 
	
$grfm = array_keys($arr_grfm) ;
$quantm = array_values($arr_grfm) ;

$grfm2 = implode("','",$grfm);
$grfm3 = "'$grfm2'";

$quantm2 = implode(',',$quantm);


$version = substr($CFG_GLPI["version"],0,5);

if($version == "0.83") {
	$status = "('assign','new','plan','waiting')";	
}	

else {
	$status = "('2','1','3','4')"	;	
}



if($interval >= "31") {

$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";

// fechados mensais

$queryf = "
SELECT DISTINCT DATE_FORMAT(date, '%b-%Y') as day_l,  COUNT(id) as nb, DATE_FORMAT(date, '%y-%m') as day
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.date ".$datas."
AND glpi_tickets.status NOT IN ". $status ."
GROUP BY day
ORDER BY day
 ";
}

else {
$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";

// fechados mensais

$queryf = "
SELECT DISTINCT DATE_FORMAT(date, '%b-%d') as day_l,  COUNT(id) as nb, DATE_FORMAT(date, '%Y-%m-%d') as day
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.date ".$datas."
AND glpi_tickets.status NOT IN ". $status ."
GROUP BY day
ORDER BY day
 ";
}


$resultf = $DB->query($queryf) or die('erro');

$arr_grff = array();
while ($row_result = $DB->fetch_assoc($resultf))		
	{ 
	$v_row_result = $row_result['day_l'];
	$arr_grff[$v_row_result] = $row_result['nb'];			
	} 
	
$grff = array_keys($arr_grff) ;
$quantf = array_values($arr_grff) ;

$quantf2 = implode(',',$quantf);

if($contador >= 1) {
		
echo "
<script type='text/javascript'>
$(function () 
{
	
Highcharts.setOptions({
    colors: [
   '#4572A7', '#50B432', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92']
    });    
    
        $('#graf_linhas').highcharts({
            chart: {
                type: 'areaspline'
            },
            title: {
                text: '".__('Tickets','dashboard')."'
            },
            legend: {
                layout: 'horizontal',
                align: 'left',
                verticalAlign: 'top',
                x: 50,
                y: 20,
                floating: true,
                borderWidth: 1,
                backgroundColor: '#FFFFFF'
            },
            xAxis: {
                categories: [$grfm3],
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
                title: {
                    text: ''
                }
            },
            tooltip: {
                shared: true
            },
            credits: {
                enabled: false
            },
            plotOptions: {
                areaspline: {
                    fillOpacity: 0.5
                }
            },
          series: [{
                name: '".__('Opened','dashboard')."',
                  dataLabels: {
                    enabled: true,                    
                    color: '#000000'
                    },
                data: [$quantm2] },
                                
                {
                name: '".__('Closed','dashboard')."',
                  dataLabels: {
                    enabled: true,                    
                    color: '#000000'
                    },
                data: [$quantf2]
            }]
        });
    });
  </script>  
";
 }
		?>
