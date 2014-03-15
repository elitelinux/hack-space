<?php
/*
 * @version $Id: recap.php 480 2012-11-09 tynet $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

//show list of employment linked with a resource
if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::header(PluginResourcesResource::getTypeName(2),'',"plugins","resources");
} else {
   Html::helpHeader(PluginResourcesResource::getTypeName(2));
}

$recap = new PluginResourcesRecap();

if ($recap->canView() || Session::haveRight("config", "w")) {

   //if $_GET["employment_professions_id"] exist this show list of resource / employment
   //by employment rank and profession
   if (isset($_GET["employment_professions_id"])
      && !empty($_GET["employment_professions_id"])) {

      $_GET["field"][0] = "4373";
      $_GET["searchtype"][0] = 'equals';
      $_GET["contains"][0] = $_GET["employment_professions_id"];

      //depending on the date
      $_GET["link"][1] = 'AND';
      $_GET["field"][1] = "4367";
      $_GET["searchtype"][1] = 'lessthan';
      $_GET["contains"][1] = $_GET["date"];

      $_GET["link"][2] = 'AND';
      $_GET["field"][2] = "4368";
      $_GET["searchtype"][2] = 'contains';
      $_GET["contains"][2] = 'NULL';

      $_GET["link"][3] = 'OR';
      $_GET["field"][3] = "4368";
      $_GET["searchtype"][3] = 'morethan';
      $_GET["contains"][3] = $_GET["date"];

      if(isset($_GET["employment_ranks_id"])
         && $_GET["employment_ranks_id"]!= 0) {
         $_GET["link"][4] = 'AND';
         $_GET["field"][4] = "4372";
         $_GET["searchtype"][4] = 'equals';
         $_GET["contains"][4] = $_GET["employment_ranks_id"];
      }

//by resource rank and profession
   } else if (isset($_GET["resource_professions_id"])
      && !empty($_GET["resource_professions_id"])) {

      $_GET["field"][0] = "4375";
      $_GET["searchtype"][0] = 'equals';
      $_GET["contains"][0] = $_GET["resource_professions_id"];

      //depending on the date
      $_GET["link"][1] = 'AND';
      $_GET["field"][1] = "4367";
      $_GET["searchtype"][1] = 'lessthan';
      $_GET["contains"][1] = $_GET["date"];

      $_GET["link"][2] = 'AND';
      $_GET["field"][2] = "4368";
      $_GET["searchtype"][2] = 'contains';
      $_GET["contains"][2] = 'NULL';

      $_GET["link"][3] = 'OR';
      $_GET["field"][3] = "4368";
      $_GET["searchtype"][3] = 'morethan';
      $_GET["contains"][3] = $_GET["date"];


  /*    //depending on the date
      $_GET["link"][1] = 'AND';
      $_GET["field"][1] = "4376";
      $_GET["searchtype"][1] = 'lessthan';
      $_GET["contains"][1] = $_GET["date"];

      $_GET["link"][2] = 'AND';
      $_GET["field"][2] = "4377";
      $_GET["searchtype"][2] = 'contains';
      $_GET["contains"][2] = 'NULL';

      $_GET["link"][3] = 'OR';
      $_GET["field"][3] = "4377";
      $_GET["searchtype"][3] = 'morethan';
      $_GET["contains"][3] = $_GET["date"];*/

      if (isset($_GET["resource_ranks_id"])
         && $_GET["resource_ranks_id"] !=0){
         $_GET["link"][4] = 'AND';
         $_GET["field"][4] = "4374";
         $_GET["searchtype"][4] = 'equals';
         $_GET["contains"][4] = $_GET["resource_ranks_id"];
      }

   }
   Search::manageGetValues("PluginResourcesRecap");

   $recap->showGenericSearch($_GET);
   $recap->showMinimalList($_GET);

} else {
   Html::displayRightError();
}

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}

?>