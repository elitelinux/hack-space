
<?php

$query2 = "
SELECT glpi_manufacturers.name AS name, count( glpi_computers.id ) AS conta
FROM glpi_manufacturers, glpi_computers
WHERE glpi_computers.is_deleted = 0
AND glpi_manufacturers.id = glpi_computers.manufacturers_id
GROUP BY glpi_manufacturers.name
ORDER BY count( glpi_computers.id ) DESC ";
		
$result2 = $DB->query($query2) or die('erro');
	
echo ' 
<table cellpadding="0" cellspacing="0" border="0" class="display" id="manufac">
	<thead>
		<tr>
		<th>'. __('Manufacturer').'</th>
		<th>'. __('Quantity','dashboard').'</th>
		</tr>
	</thead>
	<tbody>'; 		

while ($row = $DB->fetch_assoc($result2))		
{		
	echo '<tr>
			<td><a href=../../../../front/computer.php?is_deleted=0&field[0]=view&searchtype[0]=contains&contains[0]='. urlencode($row['name']) .'&itemtype=Computer&start=0
				 target="_blank"  style="color:#555555;" >'. $row['name'].'</td>
			<td>'. $row['conta'].'</td>
			</tr>';		
}

echo '		
	</tbody>
</table>';

?>

<script type="text/javascript" >
$(document).ready(function() {
    oTable = $('#manufac').dataTable({
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "bFilter":false,
        "aaSorting": [[1,'desc'], [0,'asc']],
        "aoColumnDefs": [{ "sWidth": "60%", "aTargets": [1] }],
//        "sDom": 'lf<"fixed_height"t>ip'
        //"sScrollY": "270px",
        //"bPaginate": false,
        //"bScrollCollapse": false
    });
} );
		
</script>  
