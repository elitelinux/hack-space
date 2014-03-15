<?php

include ('../../../inc/includes.php');

$plugin = new Plugin();
	if ($plugin->isActivated("notification")) {

      Html::header('Notification', "", "plugins", "notification");	   
      
      echo "<div id='config' class='center'>
      		<br><p>
            <h1>"._n('Notification','Notifications',2)."</h1> <br><p>
				<table border='0' width='200px' style='margin-left: auto; margin-right: auto; margin-bottom: 25px; margin-top:30px;'>
				<tr>            
      		<td><a class='vsubmit' type='submit' onclick=\"window.location.href = 'config.php?opt=ativar';\"> "._x('button','Enable')." </a></td>
      		<td><a class='vsubmit' type='submit' onclick=\"window.location.href = 'config.php?opt=desativar';\"> ".__('Disable')." </a></td>
				</tr>
				</table>
				
				</div>      
      		";

      // choose config server or config synchro
      //PluginOcsinventoryngConfig::showMenu();

   } else {
      Html::header(__('Setup'),'',"config","plugins");
      echo "<div class='center'><br><br>";
      echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt='".__s('Warning')."'><br><br>";
      echo "<b>".__('Please activate the plugin', 'notification')."</b></div>";
   }



if(isset($_REQUEST['opt'])) {

$action = $_REQUEST['opt'];

if($action == 'ativar') {

$search = "// Print foot for every page";
$replace = "include('../plugins/notification/front/notifica.php');";
file_put_contents('../../../inc/html.class.php', str_replace($search, $replace, file_get_contents('../../../inc/html.class.php')));

echo "<div id='config' class='center'>";
echo "Plugin  "._x('plugin', 'Enabled')." <br><br><p>
 		</div>";

}


if($action == 'desativar') {
	
$search = "include('../plugins/notification/front/notifica.php');";	
$replace = "// Print foot for every page";
file_put_contents('../../../inc/html.class.php', str_replace($search, $replace, file_get_contents('../../../inc/html.class.php')));

echo "<div id='config' class='center'>";
echo "Plugin  ".__('Disabled')."  <br><br><p>
		</div>";

}

}

echo "<div id='config' class='center'>
		<a class='vsubmit' type='submit' onclick=\"window.location.href = '". $CFG_GLPI['root_doc'] ."/front/plugin.php';\" >  ".__('Back')." </a> 
		</div>";




Html::footer();
?>