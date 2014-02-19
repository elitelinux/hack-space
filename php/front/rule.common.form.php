<?php
/*
 * @version $Id: rule.common.form.php 20288 2013-02-26 10:30:49Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief 
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

$rule = $rulecollection->getRuleClass();
$rulecollection->checkGlobal('r');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
$rulecriteria = new RuleCriteria(get_class($rule));
$ruleaction   = new RuleAction(get_class($rule));

if (isset($_POST["add_criteria"])) {
   $rulecollection->checkGlobal('w');
   $rulecriteria->add($_POST);

   Html::back();

} else if (isset($_POST["add_action"])) {
   $rulecollection->checkGlobal('w');
   $ruleaction->add($_POST);

   Html::back();

} else if (isset($_POST["update"])) {
   $rulecollection->checkGlobal('w');
   $rule->update($_POST);

   Event::log($_POST['id'], "rules", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["add"])) {
   $rulecollection->checkGlobal('w');

   $newID = $rule->add($_POST);
   Event::log($newID, "rules", 4, "setup",
              sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $newID));
   Html::redirect($_SERVER['HTTP_REFERER']."?id=$newID");

} else if (isset($_POST["delete"])) {
   $rulecollection->checkGlobal('w');
   $rulecollection->deleteRuleOrder($_POST["ranking"]);
   $rule->delete($_POST);

   Event::log($_POST["id"], "rules", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   $rule->redirectToList();
}

Html::header(Rule::getTypeName(2), $_SERVER['PHP_SELF'], 'admin',
             $rulecollection->menu_type, $rulecollection->menu_option);

$rule->showForm($_GET["id"]);
Html::footer();
?>