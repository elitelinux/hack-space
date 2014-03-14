<?php
include ("../../../inc/includes.php");

//change mimetype
header("Content-type: application/javascript");

$locale_assignme = __("Assign me this ticket", "escalade");
$locale_assignto = __("Assigned to");

//not executed in self-service interface & right verification
if (isset($_SESSION['glpiactiveprofile'])
   && $_SESSION['glpiactiveprofile']['interface'] == "central"
   && $_SESSION['glpiactiveprofile']['create_ticket'] == true
   && $_SESSION['glpiactiveprofile']['update_ticket'] == true
   ) {

   $JS = <<<JAVASCRIPT
   Ext.onReady(function() {

      // only in ticket form
      if (location.pathname.indexOf('ticket.form.php') > 0) {

                  // separating the GET parameters from the current URL
         var getParams = document.URL.split("?");
         // transforming the GET parameters into a dictionnary
         var url_params = Ext.urlDecode(getParams[getParams.length - 1]);
         // get tickets_id
         var tickets_id = url_params['id'];

         //only in edit form
         if(tickets_id == undefined) return;

         var assign_me_html = "&nbsp;<img src='../plugins/escalade/pics/assign_me.png' "+
         "alt='$locale_assignme' width='20'"+
         "title='$locale_assignme' class='pointer' id='assign_me_ticket'>";
         Ext.select('th:contains($locale_assignto) > img').insertHtml('afterEnd', assign_me_html);

         //onclick event on new buttons
         Ext.get('assign_me_ticket').on('click', function() {
            window.location.href= '../plugins/escalade/front/assign_me.php?tickets_id='+tickets_id;
         });
      }
   });
JAVASCRIPT;
   echo $JS;
}