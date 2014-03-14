<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Ideabox plugin for GLPI
 Copyright (C) 2003-2011 by the Ideabox Development Team.

 https://forge.indepnet.net/projects/ideabox
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Ideabox.

 Ideabox is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Ideabox is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Ideabox. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
	Html::header(PluginIdeaboxIdeabox::getTypeName(2),'',"plugins","ideabox");
} else {
	Html::helpHeader(PluginIdeaboxIdeabox::getTypeName(2));
}

$idea=new PluginIdeaboxIdeabox();
if ($idea->canView() || Session::haveRight("config","w")) {

	if ($_SESSION['glpiactiveprofile']['interface'] != 'central') {
	
      if ($idea->canCreate()) {
         echo "<div align='center'><table class='tab_cadre_fixe' cellpadding='5'>";
         echo "<tr><th>".__('Menu', 'ideabox')."</th></tr>";
		
			echo "<tr class='tab_bg_1'><td class='center'>";
			echo "<a href=\"./ideabox.form.php\">";
			_e('Submit an idea', 'ideabox');
			echo "</a>";
			echo "</td></tr>";
         echo " </table></div>";	
		}
		
      Search::manageGetValues("PluginIdeaboxIdeabox");
	
      $_GET["field"] = array(0=>"2");
      $_GET["contains"] = array(0=>$_SESSION["glpiname"]);

      Search::showList("PluginIdeaboxIdeabox",$_GET);
      
	} else {
   
      Search::show("PluginIdeaboxIdeabox");
	
	}
} else {
	Html::displayRightError();
}

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
	Html::footer();
} else {
	Html::helpFooter();
}

?>