
<?php


$sql_user = "
SELECT count( glpi_tickets.id ) AS conta, glpi_tickets_users.`users_id` AS id
FROM `glpi_tickets_users`, glpi_tickets 
WHERE glpi_tickets.id = glpi_tickets_users.`tickets_id`

AND glpi_tickets_users.type = 1
AND glpi_tickets_users.`users_id` NOT IN (SELECT DISTINCT users_id FROM glpi_tickets_users WHERE glpi_tickets_users.type=2)
AND glpi_tickets.is_deleted = 0
GROUP BY `users_id`
ORDER BY conta DESC
LIMIT 40
";

$query_user = $DB->query($sql_user);

echo "
<script type='text/javascript'>

$(function () {
        $('#graf_user').highcharts({
            chart: {
                type: 'bar',
                height: 1000
            },
            title: {
                text: ''
            },
            subtitle: {
                text: ''
            },
            xAxis: { 
            categories: [ ";

while ($user = $DB->fetch_assoc($query_user)) {

$sqlu = "SELECT glpi_users.firstname AS name, glpi_users.realname AS sname
FROM glpi_tickets_users, glpi_users
WHERE glpi_tickets_users.users_id = glpi_users.id
AND glpi_tickets_users.users_id = ".$user['id']."
GROUP BY glpi_users.firstname
";

	$queryu = $DB->query($sqlu);
	$chamadou = $DB->fetch_assoc($queryu);

echo "'". $chamadou['name']." ". $chamadou['sname']."',";
//echo "'". utf8_encode($chamado['name'])." ". utf8_encode($chamado['sname'])."',";	 
}   

//zerar rows para segundo while
$DB->data_seek($query_user, 0) ;               

echo "    ],
                title: {
                    text: null
                },
                labels: {
                	style: {
                        fontSize: '12px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: '',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                valueSuffix: ' chamados'
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true                                                
                    },
                     borderWidth: 1,
                	borderColor: 'white',
                	shadow:true,           
                	showInLegend: false
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -40,
                y: 100,
                floating: true,
                borderWidth: 1,
                backgroundColor: '#FFFFFF',
                shadow: true,
                enabled: false
            },
            credits: {
                enabled: false
            },
            series: [{
            	 dataLabels: {
            	 	color: '#000099'
            	 	},
                name: '". $LANG['plugin_dashboard']['1']."',
                data: [  
";
             
while ($user = $DB->fetch_assoc($query_user)) 

{
echo $user['conta'].",";
}    

echo "]
            }]
        });
    });

</script>
";
		
		?>
