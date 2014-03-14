Helpdeskrating - README
********************************************************************************

ABOUT:

 Helpdeskrating is a Plugin for GLPI extending the helpdesk tickets with a new 
 tab. Here the work of the technician can be rated in 4 different 
 categories (overall, sollution, technician, time) with points from 1 to 6, 
 where 1 is best and 6 is worst.
 
 In turn the technician can also rate the requester's communication, input 
 and collaboration, also with points from 1 to 6.
 
 Additional to that the requester and the technician have the possibillity 
 to add a comment to their rating, to explain the given points, if needed.
 
 The rating is possible as soon as the ticket is solved.
 
 More about Helpdeskrating can be found under:
  http://sourceforge.net/projects/helpdeskrating/

********************************************************************************

FEATURES:

 - Rating of Helpdesk tickets by the requester and the technician
 - Rating is optional and always possible as soon the ticket is solved
 - Rating a Ticket automatically accepts the solution
 - Rating is easy to set up and to do
 - Different statistics for the technician
 
********************************************************************************

PREREQUISITES:

 GLPI >= 0.84

********************************************************************************

INSTALLATION:

 Exctract the ZIP archive and copy the folder "helpdeskrating" and all its
 content into the plugin-folder of your GLPI installation. 
 
 Afterwards it's ready to be activated inside the GLPI Plugins menu.

********************************************************************************

USAGE:
 
 To rate a ticket, it has to be solved or closed. A user can first see the
 rating form, if he is either the requester or an assigned technician.
 The requester can see the users' rating form and the assigned technician can
 see the technicians' rating form.
 The users' rating will trigger an event to send an email to the technician, if
 mailing is activated in GLPI.
 The technicians' rating won't trigger an mailing event unless the technician
 allows the user to see the technicians' rating.
 It is not possible to change the ratings.
 
 The Helpdeskrating statistics can be accessed through the navigation header, 
 under the navigation menu "Plugins". The statistics page shows the ratings
 the users gave the currently logged in technician.
 
********************************************************************************

UPGRADE from 0.0.10 and prior Versions:

 TCPDF is not needed anymore. 
 - You can delete the file statistic_tmp.php in the folder front and the 
   whole folder tcpdf inside the inc folder
 or
 - Delete all files inside the folder "helpdeskrating" and copy the new ones
   into this folder.
 
 If the new files are in the correct place, go into the GLPI Plugins menu
 update it and activate it again.

********************************************************************************

AUTHOR: Christian Deinert
URLs: http://sourceforge.net/projects/helpdeskrating/

********************************************************************************

Copyright (C) 2010-2013: Christian Deinert

LICENSE:

 The GLPI Plugin Helpdeskrating is free software: you can redistribute it and/or modify
 it under the terms of the GNU Lesser Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 The GLPI Plugin Helpdeskrating is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Lesser Public License for more details.
 
 You should have received a copy of the GNU Lesser Public License
 along with the GLPI Plugin Helpdeskrating.  If not, see <http://www.gnu.org/licenses/>.