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

// Init the hooks of the plugins -Needed
function plugin_init_projet() {
   global $PLUGIN_HOOKS,$CFG_GLPI;
   
   $PLUGIN_HOOKS['csrf_compliant']['projet'] = true;
   $PLUGIN_HOOKS['change_profile']['projet'] = array('PluginProjetProfile','changeProfile');
   $PLUGIN_HOOKS['assign_to_ticket']['projet'] = true;
   
   if (Session::getLoginUserID()) {

      Plugin::registerClass('PluginProjetProjet', array(
         'linkgroup_types'             => true,
         'linkuser_types'              => true,
         'doc_types'                   => true,
         'contract_types'              => true,
         'helpdesk_types'              => true,
         'helpdesk_visible_types'      => true,
         'notificationtemplates_types' => true
      ));
   
      Plugin::registerClass('PluginProjetTaskPlanning', array(
         'planning_types' => true
      ));
      
      Plugin::registerClass('PluginProjetProfile',
                         array('addtabon' => 'Profile'));
   
      // Display a menu entry ?
      if (plugin_projet_haveRight("projet","r")) {
         $PLUGIN_HOOKS['menu_entry']['projet'] = 'front/projet.php';
         $PLUGIN_HOOKS['submenu_entry']['projet']['search'] = 'front/projet.php';
         $PLUGIN_HOOKS['redirect_page']['projet'] = "front/projet.form.php";	
      }

      if (plugin_projet_haveRight("projet","w")) {
         $PLUGIN_HOOKS['submenu_entry']['projet']['add'] = 'front/setup.templates.php?add=1';
         $PLUGIN_HOOKS['submenu_entry']['projet']['template'] = 'front/setup.templates.php?add=0';
      }
      
      if (plugin_projet_haveRight("task","r")) {
         $PLUGIN_HOOKS['submenu_entry']['projet']["<img  src='".
         $CFG_GLPI["root_doc"]."/pics/menu_showall.png' title='".
         __('Tasks list', 'projet')."' alt='".__('Tasks list', 'projet')."'>"] = 'front/task.php';
      
      }
   
      if (class_exists('PluginProjetProjet_Item')) { // only if plugin activated
         $PLUGIN_HOOKS['pre_item_purge']['projet'] 
                        = array('Profile'=>array('PluginProjetProfile', 'purgeProfiles'));
      }
   
      // Add specific files to add to the header : javascript or css
      //$PLUGIN_HOOKS['add_javascript']['example']="example.js";
      $PLUGIN_HOOKS['add_css']['projet']="projet.css";
      
      //Planning hook
      $PLUGIN_HOOKS['planning_populate']['projet'] = 
                                             array('PluginProjetTaskPlanning','populatePlanning');
      $PLUGIN_HOOKS['display_planning']['projet'] = 
                                          array('PluginProjetTaskPlanning','displayPlanningItem');
      
      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['projet'] = 'plugin_projet_postinit';
      
      if (class_exists('PluginPdfCommon')) {
         $PLUGIN_HOOKS['plugin_pdf']['PluginProjetProjet']='PluginProjetProjetPDF';
      }
   }
   
   //Planning hook
   $PLUGIN_HOOKS['display_planning']['projet'] = 
                                          array('PluginProjetTaskPlanning','displayPlanningItem');
      
   // End init, when all types are registered
   $PLUGIN_HOOKS['post_init']['projet'] = 'plugin_projet_postinit';

}
// Get the name and the version of the plugin - Needed
function plugin_version_projet() {

   return array (
      'name' => _n('Project', 'Projects', 2, 'projet'),
      'version' => '1.4.1',
      'license' => 'GPLv2+',
      'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a> & <a href='mailto:d.durieux@siprossii.com'>David DURIEUX</a>",
      'homepage'=>'https://forge.indepnet.net/projects/show/projet',
      'minGlpiVersion' => '0.84',// For compatibility / no install in version < 0.80
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_projet_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      _e('This plugin requires GLPI >= 0.84', 'projet');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_projet_check_config(){
   return true;
}

//////////////////////////////// Define rights for the plugin types

function plugin_projet_haveRight($module,$right) {
   $matches=array(
         ""  => array("","r","w"), // ne doit pas arriver normalement
         "r" => array("r","w"),
         "w" => array("w"),
         "1" => array("1"),
         "0" => array("0","1"), // ne doit pas arriver non plus
            );
   if (isset($_SESSION["glpi_plugin_projet_profile"][$module])
         &&in_array($_SESSION["glpi_plugin_projet_profile"][$module],$matches[$right]))
      return true;
   else return false;
}

?>