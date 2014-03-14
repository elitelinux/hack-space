
<?php

$sql_grp = "
SELECT count(glpi_groups_tickets.id) AS conta, glpi_groups.name AS name, glpi_groups.id AS gid
FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
WHERE glpi_groups_tickets.`groups_id` = glpi_groups.id
AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
AND glpi_tickets.is_deleted = 0
GROUP BY name
ORDER BY conta DESC
LIMIT 5
";

$query_grp = $DB->query($sql_grp);

echo "
<script type='text/javascript'>
$(function () {
    var chart;
    $(document).ready(function() {
    
        var colors = Highcharts.getOptions().colors,
            categories = [ ";

while ($grupo = $DB->fetch_assoc($query_grp)) 
{
	echo "'". $grupo['name']."',";
}              

echo " ],
            name = 'Grupos',
            data = [";
            
$DB->data_seek($query_grp, 0) ; 
while ($grupo = $DB->fetch_assoc($query_grp)) 
{

//usuarios do grupo	
$query_user = "
SELECT `glpi_groups_users`.`users_id` AS uid, count(glpi_tickets_users.id) AS conta 
FROM `glpi_groups_users`, glpi_tickets_users
WHERE `glpi_groups_users`.`groups_id` = ".$grupo['gid']."
AND glpi_tickets_users.users_id = glpi_groups_users.users_id
GROUP BY uid
ORDER BY conta DESC
";

$result_user = $DB->query($query_user);

 				echo " { y: ".$grupo['conta'].",
						  color: colors[0],
                    drilldown: {
                    name: '".$grupo['name']."', 
                    categories: [";

while ($user = $DB->fetch_assoc($result_user)) 
{
		        
$query_tic = "
SELECT `glpi_users`.`firstname` AS name ,`glpi_users`.`realname` AS sname, count(`glpi_tickets`.id) AS conta
FROM `glpi_users` , glpi_tickets_users, glpi_groups_tickets, glpi_tickets
WHERE `glpi_users`.`id` = ".$user['uid']."

AND glpi_tickets_users.users_id = glpi_users.id
AND glpi_tickets_users.tickets_id = glpi_groups_tickets.tickets_id
AND glpi_groups_tickets.groups_id = ".$grupo['gid']."
AND glpi_tickets.id = glpi_groups_tickets.tickets_id
AND `glpi_tickets`.`is_deleted` = 0

ORDER BY conta DESC		        
";

$result_tic = $DB->query($query_tic);
$tickets = $DB->fetch_assoc($result_tic);
		            
  echo "'". $tickets['name']." ". $tickets['sname']."',";
                
}

//}                
echo "],
		data: [";

$DB->data_seek($result_user, 0) ;
 
while ($user = $DB->fetch_assoc($result_user)) 
{

$query_cont = "
SELECT `glpi_users`.`firstname` AS name ,`glpi_users`.`realname` AS sname, count(`glpi_tickets`.id) AS conta
FROM `glpi_users` , glpi_tickets_users, glpi_groups_tickets, glpi_tickets
WHERE `glpi_users`.`id` = ".$user['uid']."

AND glpi_tickets_users.users_id = glpi_users.id
AND glpi_tickets_users.tickets_id = glpi_groups_tickets.tickets_id
AND glpi_groups_tickets.groups_id = ".$grupo['gid']."
AND glpi_tickets.id = glpi_groups_tickets.tickets_id
AND `glpi_tickets`.`is_deleted` = 0

ORDER BY conta DESC		 
";		   
		   

$result_cont = $DB->query($query_cont);
$conta = $DB->fetch_assoc($result_cont);

echo "".$conta['conta'].",";
}
echo "],
 color: colors[0]
                 } },
";
}

echo "                                
		 ];   
        function setChart(options) {
            chart.series[0].remove(false);
            chart.addSeries({
                type: options.type,
                //height: 900,
                name: options.name,
                data: options.data,
                color: options.color
            }, false);
            chart.xAxis[0].setCategories(options.categories, false);
            chart.redraw();
        }
    
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'graf_grupo'
            },
            title: {
                text: 'Chamados por Grupos'
            },
            subtitle: {
                text: 'Click the bars to view users. Click again to view groups.'
            },
            xAxis: {
                categories: categories
            },
            yAxis: {
                title: {
                    text: 'Chamados'
                }
            },
            plotOptions: {
                series: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function() {
                                var drilldown = this.drilldown;
                                var options;
                                if (drilldown) { // drill down
                                    options = {
                                        'name': drilldown.name,
                                        'categories': drilldown.categories,
                                        'data': drilldown.data,
                                        'color': drilldown.color,
                                        'type': 'column'
                                    };
                                } else { // restore
                                    options = {
                                        'name': name,
                                        'categories': categories,
                                        'data': data,
                                        'type': 'bar'
                                    };
                                }
                                setChart(options);
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        color: colors[0],
                        style: {
                            fontWeight: 'bold'
                        },
                        formatter: function() {
                            return this.y ;
                        }
                    }
                }
            },
            tooltip: {
                formatter: function() {
                    var point = this.point,
                        s = this.x +':<b>'+ this.y +' chamados</b><br/>';
                    if (point.drilldown) {
                        s += 'Click to view '+ point.category +' group';
                    } else {
                        s += 'Click to return to browser brands';
                    }
                    return s;
                }
            },
            series: [{
                type: 'bar',
                name: name,
                data: data,
                color: 'white'
            }],
            exporting: {
                enabled: false
            }
        });
    });
    
});
</script>
";
		
		?>
