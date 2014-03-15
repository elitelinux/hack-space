function notify(titulo,texto, icone) {
  'use strict';

  if (!("Notification" in window)) {
    //alert("Your browser doesn't support Notifications, but I brought this ooold alert for you :)")
  } else if (Notification.permission === 'granted') {
    var notification = new Notification(titulo, {
         icon: icone,      
     		body: texto
    });
  } else {
    Notification.requestPermission(function(permission) {
      if (!('permission' in Notification)) {
        Notification.permission = permission;
      }
      if (Notification.permission === 'granted') {
        var notification = new Notification(titulo, {
            icon: icone,     		
     			body: texto
        })
      }
    })
  }
};

Notification.onshow = function () { 
  setTimeout(Notification.close, 500000); 
}