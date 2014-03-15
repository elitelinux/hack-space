<?php
/*
 * @version $Id: HEADER 15930 2012-03-08 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Projet plugin for GLPI
 Copyright (C) 2003-2012 by the Projet Development Team.

 https://forge.indepnet.net/projects/projet
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Projet.

 Projet is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Projet is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Projet. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_projet_install() {
   global $DB;
   
   foreach (glob(GLPI_ROOT . '/plugins/projet/inc/*.php') as $file) {
      if(!preg_match('/projetpdf/', $file)){
         include_once ($file);
      }
   }
     
   if (!TableExists("glpi_plugin_projet_projets") 
         && !TableExists("glpi_plugin_projet") 
            && !TableExists("glpi_plugin_project") 
               && !TableExists("glpi_project")) {
      
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/empty-1.4.1.sql");
      include_once(GLPI_ROOT."/plugins/projet/install/install_141.php");
      install141();

   } else if (TableExists("glpi_project") 
                  && !FieldExists("glpi_plugin_project_profiles","task")) {
      
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.6.sql");
      plugin_projet_updatev62();
      plugin_projet_updatev7();
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.7.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.8.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.9.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-1.0.0.sql");
      include_once(GLPI_ROOT."/plugins/projet/install/update_101_110.php");
      update101to110();
      include_once(GLPI_ROOT."/plugins/projet/install/update_110_120.php");
      update110to120();
      include_once(GLPI_ROOT."/plugins/projet/install/update_120_130.php");
      update120to130();
      include_once(GLPI_ROOT."/plugins/projet/install/update_130_133.php");
      update130to133();
      include_once(GLPI_ROOT."/plugins/projet/install/update_140_141.php");
      update140to141();
      
   } else if (TableExists("glpi_plugin_project") 
                  && !FieldExists("glpi_plugin_project_profiles","task")) {
      
      plugin_projet_updatev62();
      plugin_projet_updatev7();
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.7.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.8.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.9.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-1.0.0.sql");
      include_once(GLPI_ROOT."/plugins/projet/install/update_101_110.php");
      update101to110();
      include_once(GLPI_ROOT."/plugins/projet/install/update_110_120.php");
      update110to120();
      include_once(GLPI_ROOT."/plugins/projet/install/update_120_130.php");
      update120to130();
      include_once(GLPI_ROOT."/plugins/projet/install/update_130_133.php");
      update130to133();
      include_once(GLPI_ROOT."/plugins/projet/install/update_140_141.php");
      update140to141();

   } else if (TableExists("glpi_plugin_project") 
               && !TableExists("glpi_dropdown_plugin_project_status")) {
      
      plugin_projet_updatev7();
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.7.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.8.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.9.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-1.0.0.sql");
      include_once(GLPI_ROOT."/plugins/projet/install/update_101_110.php");
      update101to110();
      include_once(GLPI_ROOT."/plugins/projet/install/update_110_120.php");
      update110to120();
      include_once(GLPI_ROOT."/plugins/projet/install/update_120_130.php");
      update120to130();
      include_once(GLPI_ROOT."/plugins/projet/install/update_130_133.php");
      update130to133();
      include_once(GLPI_ROOT."/plugins/projet/install/update_140_141.php");
      update140to141();

   } else if (TableExists("glpi_plugin_project") 
               && !TableExists("glpi_plugin_projet")) {
      
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.8.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.9.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-1.0.0.sql");
      include_once(GLPI_ROOT."/plugins/projet/install/update_101_110.php");
      update101to110();
      include_once(GLPI_ROOT."/plugins/projet/install/update_110_120.php");
      update110to120();
      include_once(GLPI_ROOT."/plugins/projet/install/update_120_130.php");
      update120to130();
      include_once(GLPI_ROOT."/plugins/projet/install/update_130_133.php");
      update130to133();
      include_once(GLPI_ROOT."/plugins/projet/install/update_140_141.php");
      update140to141();

   } else if (TableExists("glpi_plugin_projet") 
               && !FieldExists("glpi_plugin_projet_tasks","location")) {
      
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-0.9.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-1.0.0.sql");
      include_once(GLPI_ROOT."/plugins/projet/install/update_101_110.php");
      update101to110();
      include_once(GLPI_ROOT."/plugins/projet/install/update_110_120.php");
      update110to120();
      include_once(GLPI_ROOT."/plugins/projet/install/update_120_130.php");
      update120to130();
      include_once(GLPI_ROOT."/plugins/projet/install/update_130_133.php");
      update130to133();
      include_once(GLPI_ROOT."/plugins/projet/install/update_140_141.php");
      update140to141();
      
   } else if (TableExists("glpi_plugin_projet_profiles") 
                  && FieldExists("glpi_plugin_projet_profiles","interface")) {
      
      $DB->runFile(GLPI_ROOT ."/plugins/projet/sql/update-1.0.0.sql");
      include_once(GLPI_ROOT."/plugins/projet/install/update_101_110.php");
      update101to110();
      include_once(GLPI_ROOT."/plugins/projet/install/update_110_120.php");
      update110to120();
      include_once(GLPI_ROOT."/plugins/projet/install/update_120_130.php");
      update120to130();
      include_once(GLPI_ROOT."/plugins/projet/install/update_130_133.php");
      update130to133();
      include_once(GLPI_ROOT."/plugins/projet/install/update_140_141.php");
      update140to141();
   
   } else if (!TableExists("glpi_plugin_projet_tasktypes")) {
      
      include_once(GLPI_ROOT."/plugins/projet/install/update_101_110.php");
      update101to110();
      include_once(GLPI_ROOT."/plugins/projet/install/update_110_120.php");
      update110to120();
      include_once(GLPI_ROOT."/plugins/projet/install/update_120_130.php");
      update120to130();
      include_once(GLPI_ROOT."/plugins/projet/install/update_130_133.php");
      update130to133();
      include_once(GLPI_ROOT."/plugins/projet/install/update_140_141.php");
      update140to141();
      
   
   } else if (!TableExists("glpi_plugin_projet_taskplannings")) {
      
      include_once(GLPI_ROOT."/plugins/projet/install/update_110_120.php");
      update110to120();
      include_once(GLPI_ROOT."/plugins/projet/install/update_120_130.php");
      update120to130();
      include_once(GLPI_ROOT."/plugins/projet/install/update_130_133.php");
      update130to133();
      include_once(GLPI_ROOT."/plugins/projet/install/update_140_141.php");
      update140to141();
   
   } else if (!TableExists("glpi_plugin_projet_projets_projets")) {
      
      include_once(GLPI_ROOT."/plugins/projet/install/update_120_130.php");
      update120to130();
      include_once(GLPI_ROOT."/plugins/projet/install/update_130_133.php");
      update130to133();
      include_once(GLPI_ROOT."/plugins/projet/install/update_140_141.php");
      update140to141();
      
   } else if (TableExists("glpi_plugin_projet_projets") 
               && !FieldExists("glpi_plugin_projet_projets","estimatedtime")) {
      
      include_once(GLPI_ROOT."/plugins/projet/install/update_130_133.php");
      update130to133();
      include_once(GLPI_ROOT."/plugins/projet/install/update_140_141.php");
      update140to141();
      
   }  else if (!TableExists("glpi_plugin_projet_followups")) {
      
      include_once(GLPI_ROOT."/plugins/projet/install/update_130_133.php");
      update130to133();
      include_once(GLPI_ROOT."/plugins/projet/install/update_140_141.php");
      update140to141();
      
   }
   
   $rep_files_projet = GLPI_PLUGIN_DOC_DIR."/projet";
   if (!is_dir($rep_files_projet))
      mkdir($rep_files_projet);
   
   PluginProjetProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   
   return true;
}

function plugin_projet_uninstall() {
   global $DB;
   
   $tables = array("glpi_plugin_projet_projets",
               "glpi_plugin_projet_projetstates",
               "glpi_plugin_projet_projets_items",
               "glpi_plugin_projet_projets_projets",
               "glpi_plugin_projet_tasks",
               "glpi_plugin_projet_tasks_items",
               "glpi_plugin_projet_taskstates",
               "glpi_plugin_projet_tasktypes",
               "glpi_plugin_projet_taskplannings",
               "glpi_plugin_projet_tasks_tasks",
               "glpi_plugin_projet_profiles",
               "glpi_plugin_projet_followups");
               
   foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   
   $oldtables = array("glpi_plugin_projet",
               "glpi_plugin_projet_items",
               "glpi_plugin_projet_tasks",
               "glpi_plugin_projet_tasks_items",
               "glpi_dropdown_plugin_projet_tasks_type",
               "glpi_plugin_projet_mailing",
               "glpi_dropdown_plugin_projet_status",
               "glpi_dropdown_plugin_projet_task_status",
               "glpi_plugin_project",
               "glpi_plugin_project_items",
               "glpi_plugin_project_tasks",
               "glpi_plugin_project_tasks_items",
               "glpi_dropdown_plugin_project_status",
               "glpi_dropdown_plugin_project_tasks_type",
               "glpi_dropdown_plugin_project_task_status",
               "glpi_plugin_project_mailing",
               "glpi_plugin_project_profiles",
               "glpi_plugin_project_users",
               "glpi_plugin_project_setup",
               "glpi_plugin_project_groups",
               "glpi_plugin_project_items",
               "glpi_plugin_project_enterprises",
               "glpi_plugin_project_contracts",
               "glpi_plugin_project_documents",
               "glpi_dropdown_project_tasks_type",
               "glpi_project",
               "glpi_project_tasks",
               "glpi_project_user",
               "glpi_project_items",
               "glpi_plugin_projet_projetitems",
               "glpi_plugin_projet_mailings",
               "glpi_plugin_projet_taskitems");
               
   foreach($oldtables as $oldtable)
      $DB->query("DROP TABLE IF EXISTS `$oldtable`;");
      
   $rep_files_projet = GLPI_PLUGIN_DOC_DIR."/projet";

   Toolbox::deleteDir($rep_files_projet);
   
   $in = "IN (" . implode(',', array (
      "'PluginProjetProjet'",
      "'PluginProjetTask'"
   )) . ")";

   $tables = array (
      "glpi_displaypreferences",
      "glpi_documents_items",
      "glpi_contracts_items",
      "glpi_bookmarks",
      "glpi_logs",
      "glpi_tickets"
   );

   foreach ($tables as $table) {
      $query = "DELETE FROM `$table` WHERE (`itemtype` " . $in." ) ";
      $DB->query($query);
   }
   
   $notif = new Notification();
   
   $options = array('itemtype' => 'PluginProjetProjet',
                    'event'    => 'new',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginProjetProjet',
                    'event'    => 'update',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginProjetProjet',
                    'event'    => 'delete',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginProjetProjet',
                    'event'    => 'newtask',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginProjetProjet',
                    'event'    => 'updatetask',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginProjetProjet',
                    'event'    => 'deletetask',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   
   //templates
   $template = new NotificationTemplate();
   $translation = new NotificationTemplateTranslation();
   $options = array('itemtype' => 'PluginProjetProjet',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
      $options_template = array('notificationtemplates_id' => $data['id'],
                    'FIELDS'   => 'id');
   
         foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
            $translation->delete($data_template);
         }
      $template->delete($data);
   }

   return true;
}


function plugin_projet_postinit() {
   global $CFG_GLPI, $PLUGIN_HOOKS;
   
   $PLUGIN_HOOKS['item_purge']['projet'] = array();

   foreach (PluginProjetProjet::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['projet'][$type]
         = array('PluginProjetProjet','cleanItems');
        
      CommonGLPI::registerStandardTab($type, 'PluginProjetProjet_Item');
   }
   
   CommonGLPI::registerStandardTab("Central", 'PluginProjetTask');
}

function plugin_projet_AssignToTicket($types) {
   
   if (plugin_projet_haveRight("open_ticket","1")) {
      $types['PluginProjetProjet']=PluginProjetProjet::getTypeName(2);
   }
   return $types;
}

// Define dropdown relations
function plugin_projet_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("projet"))
      return array(
      "glpi_users"=>array("glpi_plugin_projet_projets"=>array('users_id'),
                           "glpi_plugin_projet_tasks"=>"users_id"),
      "glpi_groups"=>array("glpi_plugin_projet_projets"=>"groups_id",
                             "glpi_plugin_projet_tasks"=>"groups_id"),
      "glpi_contacts"=>array("glpi_plugin_projet_tasks"=>"contacts_id"),
      "glpi_locations"=>array("glpi_plugin_projet_tasks"=>"locations_id"),
      "glpi_plugin_projet_projets"=>array("glpi_plugin_projet_projets_items"=>"plugin_projet_projets_id",
                                                "glpi_plugin_projet_tasks"=>"plugin_projet_projets_id",
                                                'glpi_plugin_projet_projets_projets' => array('plugin_projet_projets_id_1',
                                                                                          'plugin_projet_projets_id_2')),
      "glpi_plugin_projet_tasktypes"=>array("glpi_plugin_projet_tasks"=>"plugin_projet_tasktypes_id"),
      "glpi_plugin_projet_projetstates"=>array("glpi_plugin_projet_projets"=>"plugin_projet_projetstates_id"),
      "glpi_plugin_projet_taskstates"=>array("glpi_plugin_projet_tasks"=>"plugin_projet_taskstates_id"),
      "glpi_plugin_projet_tasks"=>array("glpi_plugin_projet_tasks_items"=>"plugin_projet_tasks_id",
                                          "glpi_plugin_projet_taskplannings"=>"plugin_projet_tasks_id",
                                          'glpi_plugin_projet_tasks_tasks' => array('plugin_projet_tasks_id_1',
                                                                                          'plugin_projet_tasks_id_2')),
      "glpi_profiles" => array ("glpi_plugin_projet_profiles" => "profiles_id"),
      "glpi_entities"=>array("glpi_plugin_projet_projets"=>"entities_id",
                              "glpi_plugin_projet_tasks"=>"entities_id",
                              "glpi_plugin_projet_tasktypes"=>"entities_id"));
   else
      return array();

}

/*
 * Define dropdown tables to be manage in GLPI
 * 
 */
function plugin_projet_getDropdown() {

   $plugin = new Plugin();
   if ($plugin->isActivated("projet"))
      return array (
         "PluginProjetProjetState" => PluginProjetProjetState::getTypeName(),
         "PluginProjetTaskType"    => PluginProjetTasktype::getTypeName(),
         "PluginProjetTaskState"    => PluginProjetTaskState::getTypeName()
      );
   else
      return array ();
}

////// SEARCH FUNCTIONS ///////(){

function plugin_projet_getAddSearchOptions($itemtype) {

   $sopt = array();
   if (plugin_projet_haveRight("projet","r")) {
      if (in_array($itemtype, PluginProjetProjet::getTypes(true))) {
         $sopt[2310]['table']          = 'glpi_plugin_projet_projets';
         $sopt[2310]['field']          = 'name';
         $sopt[2310]['massiveaction']  = false;
         $sopt[2310]['name']           = PluginProjetProjet::getTypeName(2)." - ".
                                          __('Name');
         $sopt[2310]['forcegroupby']   = true;
         $sopt[2310]['datatype']       = 'itemlink';
         $sopt[2310]['itemlink_type']  = 'PluginProjetProjet';
         $sopt[2310]['joinparams']     = array('beforejoin'
                                                => array('table'      => 'glpi_plugin_projet_projets_items',
                                                         'joinparams' => array('jointype' => 'itemtype_item')));

         $sopt[2311]['table']         = 'glpi_plugin_projet_projetstates';
         $sopt[2311]['field']         = 'name';
         $sopt[2311]['massiveaction'] = false;
         $sopt[2311]['datatype']       = 'dropdown';
         $sopt[2311]['name']          = PluginProjetProjet::getTypeName(2)." - ".
                                          _n('Project state' , 'Project states', 1, 'projet');
         $sopt[2311]['forcegroupby']  =  true;
         $sopt[2311]['joinparams']     = array('beforejoin' => array(
                                                   array('table'      => 'glpi_plugin_projet_projets',
                                                         'joinparams' => $sopt[2310]['joinparams'])));
      }
   }
   return $sopt;
}

function plugin_projet_addSelect($type,$ID,$num) {
  
   $searchopt=&Search::getOptions($type);
   $table=$searchopt[$ID]["table"];
   $field=$searchopt[$ID]["field"];
   $addtable    = "";
   $NAME        = "ITEM";
   $complexjoin = '';

   if (isset($searchopt[$ID]['joinparams'])) {
      $complexjoin = Search::computeComplexJoinID($searchopt[$ID]['joinparams']);
   }

   if (($table != getTableForItemType($type) || !empty($complexjoin))
       && $searchopt[$ID]["linkfield"] != getForeignKeyFieldForTable($table)) {
      $addtable .= "_".$searchopt[$ID]["linkfield"];
   }

   if (!empty($complexjoin)) {
      $addtable .= "_".$complexjoin;
   }

   // Example of standard Select clause but use it ONLY for specific Select
   // No need of the function if you do not have specific cases
   switch ($type){
      
      case 'PluginProjetProjet':
         switch ($table.".".$field) {
            case "glpi_plugin_projet_projetstates.name" :
               return "`".$table."`.`".$field."` AS ITEM_$num, `".$table."`.`id` AS ITEM_".$num."_2, ";
               break;
            case "glpi_plugin_projet_projets_projets.plugin_projet_projets_id_1" :
               return " GROUP_CONCAT(`$table$addtable`.`plugin_projet_projets_id_1` SEPARATOR '$$$$')
                           AS ".$NAME."_$num,
                     GROUP_CONCAT(`$table$addtable`.`plugin_projet_projets_id_2` SEPARATOR '$$$$')
                           AS ".$NAME."_".$num."_2, ";
              break;
         }
         return "";
         break;
      
      case 'PluginProjetProjetState':
         switch ($table.".".$field) {
            case "glpi_plugin_projet_projetstates.color" :
               return "`".$table."`.`".$field."` AS ITEM_$num, `".$table."`.`id` AS ITEM_".$num."_2, ";
               break;
         }
         return "";
         break;
           
      case 'PluginProjetTask':
         switch ($table.".".$field) {
            case "glpi_plugin_projet_tasks_tasks.plugin_projet_tasks_id_1" :
               return " GROUP_CONCAT(`$table$addtable`.`plugin_projet_tasks_id_1` SEPARATOR '$$$$')
                           AS ".$NAME."_$num,
                     GROUP_CONCAT(`$table$addtable`.`plugin_projet_tasks_id_2` SEPARATOR '$$$$')
                           AS ".$NAME."_".$num."_2, ";
               break;
               
            case "glpi_contacts.name":
               return "`".$table."`.`id` AS ITEM_$num, `".$table."`.`name` AS contacts_name,
                        `".$table."`.`firstname` AS contacts_firstname, ";
               break;
               
            case "glpi_plugin_projet_taskstates.name" :
               return "`".$table."`.`".$field."` AS ITEM_$num, `".$table."`.`id` AS ITEM_".$num."_2, ";
               break;
         }
         return "";
         break;
      
      case 'PluginProjetTaskState':
         switch ($table.".".$field) {
            case "glpi_plugin_projet_taskstates.color" :
               return "`".$table."`.`".$field."` AS ITEM_$num, `".$table."`.`id` AS ITEM_".$num."_2, ";
               break;
         }
         return "";
         break;
   }
   return "";
}

function plugin_projet_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables) {

   switch ($type){
         
      case 'PluginProjetTask':
         switch ($new_table){

            case "glpi_plugin_projet_projets" : // From items
                  $out= " LEFT JOIN `glpi_plugin_projet_projets` 
                        ON (`$ref_table`.`plugin_projet_projets_id` = `glpi_plugin_projet_projets`.`id`) ";
               return $out;
               break;
            case "glpi_contacts" :
               return " LEFT JOIN `glpi_contacts` ON (`glpi_contacts`.`id` = `$ref_table`.`contacts_id`) ";
               break;
            case "glpi_plugin_projet_taskplannings" :
               return " LEFT JOIN `glpi_plugin_projet_taskplannings` 
                        ON (`glpi_plugin_projet_taskplannings`.`plugin_projet_tasks_id` = `$ref_table`.`id`) ";
               break;
         }
      
         return "";
         break;
   }
   
   return "";
}

function plugin_projet_addWhere($link,$nott,$type,$ID,$val) {

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   $SEARCH = Search::makeTextSearch($val,$nott);

   switch ($table.".".$field) {
      case "glpi_plugin_projet_projets_projets.plugin_projet_projets_id_1" :
            return $link." ((`$table`.`plugin_projet_projets_id_2` = '$val')
                            AND `glpi_plugin_projet_projets`.`id` <> '$val')";
         
         break;
         
      case "glpi_plugin_projet_tasks_tasks.plugin_projet_tasks_id_1" :
            return $link." ((`$table`.`plugin_projet_tasks_id_2` = '$val')
                            AND `glpi_plugin_projet_tasks`.`id` <> '$val')";
         
         break;
   }
   return "";
}

function plugin_projet_forceGroupBy($type) {

   return true;
   switch ($type) {
      case 'PluginProjetProjet':
      case 'PluginProjetTask':
         return true;
         break;

   }
   return false;
}

function plugin_projet_displayConfigItem($type,$ID,$data,$num) {
   global $CFG_GLPI, $DB;

   $searchopt=&Search::getOptions($type);
   $table=$searchopt[$ID]["table"];
   $field=$searchopt[$ID]["field"];
   
   switch ($type) {
      
      case 'PluginProjetProjet':
         
         switch ($table.'.'.$field) {
            case "glpi_plugin_projet_projetstates.name" :
               return " style=\"background-color:".PluginProjetProjetState::getStatusColor($data["ITEM_".$num."_2"]).";\" ";
               break;
         }
         break;
      
      case 'PluginProjetTask':
         
         switch ($table.'.'.$field) {
            case "glpi_plugin_projet_tasks.priority" :
               return " style=\"background-color:".$_SESSION["glpipriority_".$data["ITEM_$num"]].";\" ";
               break;
            case "glpi_plugin_projet_taskstates.name" :
               return " style=\"background-color:".PluginProjetTaskState::getStatusColor($data["ITEM_".$num."_2"]).";\" ";
               break;
         }
         break;
      
      case 'PluginProjetProjetState':
         
         switch ($table.'.'.$field) {

            case "glpi_plugin_projet_projetstates.color" :
               return " style=\"background-color:".PluginProjetProjetState::getStatusColor($data["ITEM_".$num."_2"]).";\" ";
               break;
         }
         break;
      
      case 'PluginProjetTaskState':
         
         switch ($table.'.'.$field) {

            case "glpi_plugin_projet_taskstates.color" :
               return " style=\"background-color:".PluginProjetTaskState::getStatusColor($data["ITEM_".$num."_2"]).";\" ";
               break;
         }
         break;
      
   }
   return "";
}

function plugin_projet_giveItem($type,$ID,$data,$num) {
   global $CFG_GLPI, $DB;

   $searchopt=&Search::getOptions($type);
   $table=$searchopt[$ID]["table"];
   $field=$searchopt[$ID]["field"];
   
   $output_type=Search::HTML_OUTPUT;
   if (isset($_GET['display_type']))
      $output_type=$_GET['display_type'];
      
   switch ($type) {
      
      case 'PluginProjetProjet':
         
         switch ($table.'.'.$field) {
            case "glpi_plugin_projet_projets_projets.plugin_projet_projets_id_1" :
               $out = " ";
               
               $split      = explode("$$$$",$data["ITEM_".$num]);
               $split2     = explode("$$$$",$data["ITEM_".$num."_2"]);
               $displayed  = array();
               for ($k=0 ; $k<count($split) ; $k++) {
                  //$linkid = $split2[$k];
                  $linkid = $split[$k]==$data['id'] ? $split2[$k] : $split2[$k];
                  if ($linkid>0 && $linkid != $data['id'] && !isset($displayed[$linkid])) {
                     $text = $linkid." - ".Dropdown::getDropdownName('glpi_plugin_projet_projets', $linkid);
                     if (count($displayed)) {
                        $out .= "<br>";
                     }
                     $displayed[$linkid] = $linkid;
                     $out               .= $text;
                  }
               }
               return $out;
               break;
            case "glpi_plugin_projet_projets.name" :
               $out= "";
               if (!empty($data["ITEM_".$num."_2"])) {
                  $link = Toolbox::getItemTypeFormURL('PluginProjetProjet');
                  
                  if ($output_type==Search::HTML_OUTPUT)
                     $out= "<a href=\"".$link."?id=".$data["ITEM_".$num."_2"]."\">";
                  $out.= $data["ITEM_$num"];
                  if ($output_type==Search::HTML_OUTPUT) {
                     if ($_SESSION["glpiis_ids_visible"]||empty($data["ITEM_$num"])) $out.= " (".$data["ITEM_".$num."_2"].")";
                     $out.= "</a>";
                  }
                  
                  if (plugin_projet_haveRight("task","r") && $output_type==Search::HTML_OUTPUT) {
                     
                     $query_tasks = "SELECT COUNT(`id`) AS nb_tasks
                                 FROM `glpi_plugin_projet_tasks`
                                 WHERE `plugin_projet_projets_id` = '".$data['id']."' ";
                     $query_tasks.= "AND `is_deleted` = '0'";
                     $result_tasks = $DB->query($query_tasks);
                     
                     $nb_tasks=$DB->result($result_tasks, 0, "nb_tasks");
                     
                     //select finished tasks
                     $query_states = "SELECT COUNT(`id`) AS nb_tasks
                                 FROM `glpi_plugin_projet_tasks`
                                 WHERE `plugin_projet_projets_id` = '".$data['id']."' ";
                     $query_states.= "AND `is_deleted` = '0'";
                     
                     
                     $finished = " `for_dependency` = '1' ";
                     $states = getAllDatasFromTable("glpi_plugin_projet_taskstates",$finished);
                     $tab = array();
                     if (!empty($states)) {
                        foreach ($states as $state) {
                           $tab[]= $state['id'];
                        }
                     }
                     if (!empty($tab)) {
                        $query_states.= "AND `plugin_projet_taskstates_id` IN (".implode(',',$tab).")";
                     }
                     $result_states = $DB->query($query_states);
                     $is_finished=$DB->result($result_states, 0, "nb_tasks");
                     
                     $out.= "&nbsp;(<a href=\"".$CFG_GLPI["root_doc"]."/plugins/projet/front/task.php?plugin_projet_projets_id=".$data["id"]."\">";
                     if (($nb_tasks-$is_finished) > 0) {
                        $out.= "<span class='red'>";
                        $out.=$nb_tasks-$is_finished."</span></a>)";
                     } else {
                        $out.= "<span class='green'>";
                        $out.=$nb_tasks."</span></a>)";
                     }
                     
                  }
               }
               return $out;
               break;
            case "glpi_plugin_projet_projets.date_end" :
               if (!empty($data["ITEM_$num"])) {
                  if ($data["ITEM_$num"] <= date('Y-m-d') && !empty($data["ITEM_$num"])) {
                     $out= "<span class='red'>".Html::convdate($data["ITEM_$num"])."</span>";
                  } else {
                     $out= "<span class='green'>".Html::convdate($data["ITEM_$num"])."</span>";
                  }
               } else {
                  $out= "--";
               }
               return $out;
               break;
            case "glpi_plugin_projet_projets.advance" :	
               $out= PluginProjetProjet::displayProgressBar('100',$data["ITEM_$num"]);
               return $out;
               break;
            case "glpi_plugin_projet_projets_items.items_id" :
               $restrict = "`plugin_projet_projets_id` = '".$data['id']."' 
                           ORDER BY `itemtype`, `items_id`";
               $items = getAllDatasFromTable("glpi_plugin_projet_projets_items",$restrict);
               $out='';
               if (!empty($items)) {
                  foreach ($items as $device) {
                     if (!class_exists($device["itemtype"])) {
                        continue;
                     }
                     $item=new $device["itemtype"]();
                     $item->getFromDB($device["items_id"]);
                     $out.=$item->getTypeName()." - ";
                        if ($device["itemtype"] == 'User') {
                           if ($output_type==Search::HTML_OUTPUT) {
                              $link = Toolbox::getItemTypeFormURL('User');
                              $out.="<a href=\"".$link."?id=".$device["items_id"]."\">";
                           }
                           $out.=getUserName($device["items_id"]);
                           if ($output_type==Search::HTML_OUTPUT)
                              $out.="</a>";
                        } else {
                           $out.=$item->getLink();
                        }
                        $out.="<br>";
                  }
               } else
                  $out=' ';
               return $out;
               break;
         }
         return "";
         break;
      case 'PluginProjetTask':
         
         switch ($table.'.'.$field) {
            
            case "glpi_plugin_projet_tasks_tasks.plugin_projet_tasks_id_1" :
               $out = " ";
               
               $split      = explode("$$$$",$data["ITEM_".$num]);
               $split2     = explode("$$$$",$data["ITEM_".$num."_2"]);
               $displayed  = array();
               for ($k=0 ; $k<count($split) ; $k++) {
                  //$linkid = $split2[$k];
                  $linkid = $split[$k]==$data['id'] ? $split2[$k] : $split2[$k];
                  if ($linkid>0 && $linkid != $data['id'] && !isset($displayed[$linkid])) {
                     $text = $linkid." - ".Dropdown::getDropdownName('glpi_plugin_projet_tasks', $linkid);
                     if (count($displayed)) {
                        $out .= "<br>";
                     }
                     $displayed[$linkid] = $linkid;
                     $out               .= $text;
                  }
               }
               return $out;
               break;
               
            case "glpi_plugin_projet_tasks.advance" :	
               $out= PluginProjetProjet::displayProgressBar('100',$data["ITEM_$num"]);
               return $out;
               break;
            case "glpi_plugin_projet_tasks.priority" :
               $out= Ticket::getPriorityName($data["ITEM_$num"]);
               return $out;
               break;
            case 'glpi_plugin_projet_tasks.depends':
               $out="";
               if ($data["ITEM_$num"]==1)
                  $out.="<span class='red'>";
               $out.= Dropdown::getYesNo($data["ITEM_$num"]);
               if ($data["ITEM_$num"]==1)
                  $out.="</span>";
               return $out;
               break;
            case "glpi_plugin_projet_tasks.plugin_projet_projets_id" :
               $out=Dropdown::getdropdownname("glpi_plugin_projet_projets",$data["ITEM_$num"]);
               $out.= " (".$data["ITEM_$num"].")";
               return $out;
               break;
            case "glpi_plugin_projet_tasks_items.items_id" :
               $restrict = "`plugin_projet_tasks_id` = '".$data['id']."' 
                           ORDER BY `itemtype`, `items_id`";
               $items = getAllDatasFromTable("glpi_plugin_projet_tasks_items",$restrict);
               $out='';
               if (!empty($items)) {
                  foreach ($items as $device) {
                     $item=new $device["itemtype"]();
                     $item->getFromDB($device["items_id"]);
                     $out.=$item->getTypeName()." - ".$item->getLink()."<br>";
                  }
               }
               return $out;
               break;
            case "glpi_contacts.name" :
               if (!empty($data["ITEM_$num"])) {
                  $link=Toolbox::getItemTypeFormURL('Contact');
                  $out= "<a href=\"".$link."?id=".$data["ITEM_$num"]."\">";
                  $temp=$data["contacts_name"];
                  $firstname=$data["contacts_firstname"];
                  if (strlen($firstname)>0) {
                     if ($CFG_GLPI["names_format"]==FIRSTNAME_BEFORE) {
                        $temp=$firstname." ".$temp;
                     } else {
                        $temp.=" ".$firstname;
                     }
                  }
                  $out.= $temp;
                  if ($_SESSION["glpiis_ids_visible"]||empty($data["ITEM_$num"])) $out.= " (".$data["ITEM_$num"].")";
                  $out.= "</a>";
               } else
                  $out= "";
               return $out;
               break;
            case "glpi_plugin_projet_taskplannings.id" :
               if (!empty($data["ITEM_$num"])) {
                  $plan = new PluginProjetTaskPlanning();
                  $plan->getFromDB($data["ITEM_$num"]);
                  $out=Html::convDateTime($plan->fields["begin"]) . "<br>&nbsp;->&nbsp;" .
                     Html::convDateTime($plan->fields["end"]);
               } else
                  $out= __('None');
               return $out;
               break;
            }
         return "";
         break;
   }
   return "";
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////


function plugin_projet_MassiveActions($type) {
   
   if (in_array($type,PluginProjetProjet::getTypes(true))) {
      return array("plugin_projet_add_item"=>__('Associate to project', 'projet'));
   }
   return array();
}

function plugin_projet_MassiveActionsDisplay($options=array()) {  
   
   $projet = new PluginProjetProjet();
   if (in_array($options['itemtype'],PluginProjetProjet::getTypes(true))) {
      $projet->dropdownProjet("plugin_projet_projets_id");
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\""._sx('button', 'Post')."\" >";
   }
   return "";
}

function plugin_projet_MassiveActionsProcess($data) {
   
   $res = array('ok' => 0,
            'ko' => 0,
            'noright' => 0);
   
   $projet_item=new PluginProjetProjet_Item();
   
   switch ($data['action']) {
      case "plugin_projet_add_item":
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('plugin_projet_projets_id' => $data['plugin_projet_projets_id'],
                              'items_id'      => $key,
                              'itemtype'      => $data['itemtype']);
               if ($projet_item->can(-1,'w',$input)) {
                  if ($projet_item->add($input)) {
                     $res['ok']++;
                  } else {
                     $res['ko']++;
                  }
               } else {
                  $res['noright']++;
               }
            }
         }
         break;
   }
}


function plugin_projet_MassiveActionsFieldsDisplay($options=array()) {
   
   $table = $options['options']['table'];
   $field = $options['options']['field'];
   $linkfield = $options['options']['linkfield'];
   if ($table == getTableForItemType($options['itemtype'])) {

      // Table fields
      switch ($table.".".$field) {
         
         case "glpi_plugin_projet_projets.advance":
         case "glpi_plugin_projet_tasks.advance":
            echo "<select name='advance'>";
            for ($i=0;$i<101;$i+=5) {
               echo "<option value='$i'>$i</option>";
            }
            echo "</select> ";
            echo "<input type='hidden' name='field' value='advance'>";
            return true;
            break;
         case "glpi_plugin_projet_tasks.priority":
            Ticket::dropdownPriority($linkfield,$field,false,true);
            return true;
            break;
      }

   }
   // Need to return false on non display item
   return false;
}

function plugin_projet_updatev62() {
   global $DB;

   $query= "ALTER TABLE `glpi_plugin_project_profiles` 
         ADD `task` char(1) default NULL;";
   $DB->query($query) or die($DB->error());

   $query= "UPDATE `glpi_plugin_project_profiles` 
         SET `task` = NULL 
         WHERE `ID` = 1 ;";
   $DB->query($query) or die($DB->error());
   
   $query= "UPDATE `glpi_plugin_project_profiles` 
         SET `task` = 'r' 
         WHERE `ID` = 2 ;";
   $DB->query($query) or die($DB->error());
   
   $query= "UPDATE `glpi_plugin_project_profiles` 
         SET `task` = 'w' 
         WHERE `ID` = 3 ;";
   $DB->query($query) or die($DB->error());
   
   $query= "UPDATE `glpi_plugin_project_profiles` 
         SET `task` = 'w' 
         WHERE `ID` = 4 ;";
   $DB->query($query) or die($DB->error());

}

function plugin_projet_updatev7() {
   global $DB;
   
   $query="INSERT INTO glpi_doc_device (FK_doc,FK_device,device_type) 
      SELECT FK_documents, FK_project, '2300' 
      FROM glpi_plugin_project_documents;";

   $DB->query($query);
   
   $query="INSERT INTO glpi_plugin_project_items (FK_project,FK_device,device_type) 
      SELECT FK_project, FK_users, '".USER_TYPE."' 
      FROM glpi_plugin_project_users;";
   
   $DB->query($query);
   
   $query="INSERT INTO glpi_plugin_project_items (FK_project,FK_device,device_type) 
         SELECT FK_project, FK_groups, '".GROUP_TYPE."' 
         FROM glpi_plugin_project_FK_groups;";
   
   $DB->query($query);
   
   $query="INSERT INTO glpi_plugin_project_items (FK_project,FK_device,device_type) 
         SELECT FK_project, FK_enterprise, '".ENTERPRISE_TYPE."' 
         FROM glpi_plugin_project_enterprises;";
   
   $DB->query($query);
   
   $query="INSERT INTO glpi_plugin_project_items (FK_project,FK_device,device_type) 
         SELECT FK_project, FK_contracts, '".CONTRACT_TYPE."' 
         FROM glpi_plugin_project_contracts;";
   
   $DB->query($query);

}

?>