<?php
/*
 -------------------------------------------------------------------------
 Typology plugin for GLPI
 Copyright (C) 2006-2012 by the Typology Development Team.

 https://forge.indepnet.net/projects/typology
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Typology.

 Typology is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Typology is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Typology. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

include ('../../../inc/includes.php');

$typo = new PluginTypologyTypology();
$criteria = new PluginTypologyTypologyCriteria();

if (isset($_POST["update"])) {
   $criteria->check($_POST["id"], 'w');

   $criteria->update($_POST);

   Html::back();

} else if (isset($_POST["add"])) {
   $criteria->check(-1, 'w', $_POST);

   $newID = $criteria->add($_POST);

   Html::redirect($CFG_GLPI["root_doc"]."/plugins/typology/front/typologycriteria.form.php?id=$newID");

} else if (isset($_POST["delete"])) {

   /*if (isset($_POST["item"]) && count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($val == 1) {
            if ($criteria->can($key, 'w')) {
               $criteria->delete(array('id' => $key));
            }
         }
      }

   } else if (isset($_POST['id'])) {*/
      $criteria->check($_POST['id'], 'w');
      $criteria->delete($_POST);

      $criteria->redirectToList();
//   }

   Html::back();

} else if (isset($_POST["add_action"])) {
   $criteria->check($_POST['plugin_typology_typologycriterias_id'], 'w');

   $definition = new PluginTypologyTypologyCriteriaDefinition();
   $definition->add($_POST);

   // Mise à jour de l'heure de modification pour le critère
   $criteria->update(array('id'       => $_POST['plugin_typology_typologycriterias_id'],
                       'date_mod' => $_SESSION['glpi_currenttime']));
   Html::back();

} else if (isset($_POST["delete_action"])) {

   $definition = new PluginTypologyTypologyCriteriaDefinition();

   if (isset($_POST["item"]) && count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($val == 1) {
            if ($definition->can($key, 'w')) {
               $definition->delete(array('id' => $key));
            }
         }
      }
   } else if (isset($_POST['id'])) {
      $definition->check($_POST['id'], 'w');
      $definition->delete($_POST);
   }

   $criteria->check($_POST['plugin_typology_typologycriterias_id'], 'w');

   // Can't do this in RuleAction, so do it here
   $criteria->update(array('id'       => $_POST['plugin_typology_typologycriterias_id'],
                           'date_mod' => $_SESSION['glpi_currenttime']));
   Html::back();

 } else {
   $typo->checkGlobal("r");
   Html::header(PluginTypologyTypology::getTypeName(2), '',"plugins","typology");

   $criteria->showForm($_GET["id"]);
   Html::footer();
}
?>