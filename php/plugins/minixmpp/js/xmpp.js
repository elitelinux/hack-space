jQuery.ajaxSetup({cache: true});
jQuery.getScript("https://static.jappix.com/php/get.php?l=en&t=js&g=mini.xml", function() {
   MINI_GROUPCHATS = ["<?php echo $nom ?>@<?php echo $conference ?>"];
   MINI_NICKNAME = "'.$_SESSION['glpiname'].'";
launchMini(false, true, "'.$anoserver.'");
});
