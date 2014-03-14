
<?php

//chamados mensais

$querym = "
SELECT DISTINCT   DATE_FORMAT(date, '%b%y') as month_l,  COUNT(id) as nb, DATE_FORMAT(date, '%y%m') as month
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
GROUP BY month
ORDER BY month
 ";

$resultm = $DB->query($querym) or die('erro');

$arr_grfm = array();
while ($row_result = $DB->fetch_assoc($resultm))		
	{ 
	$v_row_result = $row_result['month_l'];
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

//chamados abertos mensais

$querya = "
SELECT DISTINCT   DATE_FORMAT(date, '%b%y') as month_l,  COUNT(id) as nb, DATE_FORMAT(date, '%y%m') as month
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.status IN ". $status ."
GROUP BY month
ORDER BY month
 ";

$resulta = $DB->query($querya) or die('erro');

$arr_grfa = array();
while ($row_result = $DB->fetch_assoc($resulta))		
	{ 
	$v_row_result = $row_result['month_l'];
	$arr_grfa[$v_row_result] = $row_result['nb'];			
	} 
	
$grfa = array_keys($arr_grfa) ;
$quanta = array_values($arr_grfa) ;

$grfa2 = implode("','",$grfa);
$grfa3 = "'$grfa2'";
$quanta2 = implode(',',$quanta);

// fechados mensais

$queryf = "
SELECT DISTINCT   DATE_FORMAT(date, '%b%y') as month_l,  COUNT(id) as nb, DATE_FORMAT(date, '%y%m') as month
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.status NOT IN ". $status ."
GROUP BY month
ORDER BY month
 ";

$resultf = $DB->query($queryf) or die('erro');

$arr_grff = array();
while ($row_result = $DB->fetch_assoc($resultf))		
	{ 
	$v_row_result = $row_result['month_l'];
	$arr_grff[$v_row_result] = $row_result['nb'];			
	} 
	
$grff = array_keys($arr_grff) ;
$quantf = array_values($arr_grff) ;

$grff2 = implode("','",$grff);
$grff3 = "'$grff2'";
$quantf2 = implode(',',$quantf);

echo "
<script type='text/javascript'>
$(function () {
	
Highcharts.setOptions({
    colors: [
   '#4572A7', '#000099', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92']
    });	
	
        $('#graf_linhas').highcharts({
            chart: {
                type: 'areaspline'
            },
            title: {
                text: '".$LANG['plugin_dashboard']['1']."'
            },
            legend: {
                layout: 'horizontal',
                align: 'left',
                verticalAlign: 'top',
                x: 70,
                y: 20,
                floating: true,
                borderWidth: 1,
                backgroundColor: '#FFFFFF'
            },
            xAxis: {
                categories: [$grfm3],
            
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
                name: '".$LANG['plugin_dashboard']['form']['14']."',
                dataLabels: {
                    enabled: true,                    
                    color: '#000099'
                    },
                                     
                data: [$quantm2] },                             
                
                {
                name: '".$LANG['plugin_dashboard']['form']['15']."',  
                color: '#89A54E',              
                data: [$quantf2]
                },
            
               {
                name: '".$LANG['plugin_dashboard']['30']."',
                color: '#800000',
                data: [$quanta2] 
                }]
        });
    });
  </script>  
";
	 
		?>
