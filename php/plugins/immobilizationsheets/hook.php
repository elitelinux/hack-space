<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Immobilizationsheets plugin for GLPI
  Copyright (C) 2003-2011 by the Immobilizationsheets Development Team.

  https://forge.indepnet.net/projects/immobilizationsheets
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Immobilizationsheets.

  Immobilizationsheets is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Immobilizationsheets is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Immobilizationsheets. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

function plugin_immobilizationsheets_install() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/immobilizationsheets/inc/profile.class.php");

   $update = false;
   if (!TableExists("glpi_plugin_immo_profiles") && !TableExists("glpi_plugin_immobilizationsheets_profiles")) {

      $DB->runFile(GLPI_ROOT."/plugins/immobilizationsheets/sql/empty-1.3.0.sql");
   } else if (TableExists("glpi_plugin_immo_profiles") && FieldExists("glpi_plugin_immo_profiles", "interface")) {

      $update = true;
      $DB->runFile(GLPI_ROOT."/plugins/immobilizationsheets/sql/update-1.2.0.sql");
      $DB->runFile(GLPI_ROOT."/plugins/immobilizationsheets/sql/update-1.3.0.sql");
   } else if (!TableExists("glpi_plugin_immobilizationsheets_profiles")) {

      $update = true;
      $DB->runFile(GLPI_ROOT."/plugins/immobilizationsheets/sql/update-1.3.0.sql");
   }

   if ($update) {

      //Do One time on 0.78
      $query_ = "SELECT *
            FROM `glpi_plugin_immobilizationsheets_profiles` ";
      $result_ = $DB->query($query_);
      if ($DB->numrows($result_) > 0) {

         while ($data = $DB->fetch_array($result_)) {
            $query = "UPDATE `glpi_plugin_immobilizationsheets_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result = $DB->query($query);
         }
      }

      $query = "ALTER TABLE `glpi_plugin_immobilizationsheets_profiles`
               DROP `name` ;";
      $result = $DB->query($query);
   }

   PluginImmobilizationsheetsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_immobilizationsheets_uninstall() {
   global $DB;

   $query = "DROP TABLE IF EXISTS `glpi_plugin_immobilizationsheets_profiles`;";
   $DB->query($query);

   $query = "DROP TABLE IF EXISTS `glpi_plugin_immobilizationsheets_configs`;";
   $DB->query($query);

   //old versions	
   $tables = array("glpi_plugin_immo_profiles",
       "glpi_plugin_immo_config");

   foreach ($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   return true;
}

function plugin_immobilizationsheets_postinit() {

   foreach (PluginImmobilizationsheetsItem::getTypes(true) as $type) {

      CommonGLPI::registerStandardTab($type, 'PluginImmobilizationsheetsItem');
   }
}

// Define database relations
function plugin_immobilizationsheets_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("immobilizationsheets"))
      return array(
          "glpi_documentcategories" => array(
              "glpi_plugin_immobilizationsheets_configs" => "documentcategories_id"
          ),
          "glpi_profiles" => array("glpi_plugin_immobilizationsheets_profiles" => "profiles_id")
      );
   else
      return array();
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_immobilizationsheets_MassiveActions($type) {

   $immo = new PluginImmobilizationsheetsConfig();

   if (in_array($type, PluginImmobilizationsheetsItem::getTypes())) {
      if ($immo->getFromDB(1))
         if ($immo->fields["use_backup"] == 1)
            return array("plugin_immobilizationsheets_generate" => __('Generate the immobilization sheet','immobilizationsheets'));
   }
   return array();
}

function plugin_immobilizationsheets_MassiveActionsDisplay($options = array()) {

   if (in_array($options['itemtype'], PluginImmobilizationsheetsItem::getTypes())) {
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".__('Post')."\" >";
   }
   return "";
}

function plugin_immobilizationsheets_MassiveActionsProcess($data) {
   
   $res = array('ok' => 0,
            'ko' => 0,
            'noright' => 0);
   
   $_SESSION["plugin_immobilizationsheets"]["nb_items"] = 0;
   switch ($data['action']) {

      case "plugin_immobilizationsheets_generate":
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               // Items exists ?
               $item = new $data["itemtype"]();
               if ($item->getFromDB($key)) {
                  // Entity security
                  $tab_id[] = $key;

                  $_SESSION["plugin_immobilizationsheets"]["itemtype"] = $data["itemtype"];
                  $_SESSION["plugin_immobilizationsheets"]["tab_id"] = serialize($tab_id);
                  $_SESSION["plugin_immobilizationsheets"]["nb_items"] = $_SESSION["plugin_immobilizationsheets"]["nb_items"] + 1;
                  
                  
                  echo "<script type='text/javascript'>location.href='../plugins/immobilizationsheets/front/export.massive.php'</script>";
                  $res['ok']++;
               } else {
                  $res['ko']++;
               }
            }
         }
         break;
   }
   return $res;
}

?>