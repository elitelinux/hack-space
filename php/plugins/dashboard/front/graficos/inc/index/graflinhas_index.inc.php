
<?php

//chamados mensais

$querym = "
SELECT DISTINCT DATE_FORMAT(date, '%b-%y') as month_l,  COUNT(id) as nb, DATE_FORMAT(date, '%y-%m') as month
FROM glpi_tickets
WHERE glpi_tickets.is_deleted = '0'
GROUP BY month
ORDER BY month
 ";

$resultm = $DB->query($querym);
$contador = $DB->numrows($resultm);
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

echo "

 <script type=\"text/javascript\">
        $(function () {          

            // jQuery Flot Chart 
            
            var visits = [";
            
for($i = 0; $i <= $contador; $i++) {	
$j=$i+1;
echo " [$j,$quantm[$i]],
";
}

echo " ]
            var plot = $.plot($(\"#graflinhas\"),
                [ { data: visits, label: \" ". $LANG['plugin_dashboard']['1'] . " \"}], {
                    series: {
                        lines: { show: true,
                                lineWidth: 1,
                                fill: true, 
                                fillColor: { colors: [ { opacity: 0.08 }, { opacity: 0.27 } ] },
                                label: {show:true}
                             },
//data labels                             
//  valueLabels: {
//   show: true
//  },
                        points: { show: true, 
                                 lineWidth: 2,
                                 radius: 3
                             },
                        shadowSize: 0,
                        stack: true
                    },
                    grid: { hoverable: true, 
                           clickable: true, 
                           tickColor: \"#f9f9f9\",
                           borderWidth: 0
                        },
                    legend: {
                            show: true,
                            labelBoxBorderColor: \"#fff\"
                        },  
                    colors: [\"#30a0eb\",\"#a7b5c5\"],
                    xaxis: {
                        ticks: [ ";
 
for($i = 0; $i <= $contador; $i++) {	
$j=$i+1;
echo " [$j,\"$grfm[$i]\"],

";
}                        

echo " ], 
                        font: {
                            size: 12,
                            family: \"Open Sans, Arial\",
                            variant: \"small-caps\",
                            color: \"#697695\"
                        }
                    },
                    yaxis: {
                        ticks:3, 
                        tickDecimals: 0,                        
                        font: {size:12, color: \"#9da3a9\"}
                    }
                 });

            function showTooltip(x, y, contents) {
                $('<div id=\"tooltip\">' + contents + '</div>').css( {
                    position: 'absolute',
                    display: 'none',
                    top: y - 30,
                    left: x - 50,
                    color: \"#fff\",
                    padding: '2px 5px',
                    'border-radius': '6px',
                    'background-color': '#000',
                    opacity: 0.80
                }).appendTo(\"body\").fadeIn(200);
            }

            var previousPoint = null;
            $(\"#graflinhas\").bind(\"plothover\", function (event, pos, item) {
                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;

                        $(\"#tooltip\").remove();
                        var x = item.datapoint[0].toFixed(0),
                            y = item.datapoint[1].toFixed(0);

                        var month = item.series.xaxis.ticks[item.dataIndex].label;

                        showTooltip(item.pageX, item.pageY,
                                    item.series.label + \" de \" + month + \": \" + y);
                    }
                }
                else {
                    $(\"#tooltip\").remove();
                    previousPoint = null;
                }
            });
        });
    </script>
";
		 
?>
