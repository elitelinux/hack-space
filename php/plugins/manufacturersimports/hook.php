<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manufacturersimports plugin for GLPI
 Copyright (C) 2003-2011 by the Manufacturersimports Development Team.

 https://forge.indepnet.net/projects/manufacturersimports
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Manufacturersimports.

 Manufacturersimports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manufacturersimports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manufacturersimports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_manufacturersimports_install() {
   global $DB;

	include_once (GLPI_ROOT."/plugins/manufacturersimports/inc/profile.class.php");
   
   $update=false;
	if (!TableExists("glpi_plugin_suppliertag_profiles") && !TableExists("glpi_plugin_manufacturersimports_profiles")) {
      
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/empty-1.5.0.sql");

	} else if (TableExists("glpi_plugin_suppliertag_config") && !FieldExists("glpi_plugin_suppliertag_config","FK_entities")) {
      
      $update=true;
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.1.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.2.0.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.3.0.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.4.1.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.5.0.sql");

	} else if (TableExists("glpi_plugin_suppliertag_profiles") && FieldExists("glpi_plugin_suppliertag_profiles","interface")) {
      
      $update=true;
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.2.0.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.3.0.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.4.1.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.5.0.sql");

	} else if (!TableExists("glpi_plugin_manufacturersimports_profiles")) {
      
      $update=true;
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.3.0.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.4.1.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/manufacturersimports/sql/update-1.5.0.sql");

	}
   
   $query="UPDATE `glpi_plugin_manufacturersimports_configs` 
            SET `Supplier_url` = 'http://www.dell.com/support/troubleshooting/us/en/04/Index?c=us&l=en&s=bsd&cs=04&t=system&ServiceTag=' 
            WHERE `name` ='Dell'";
   $DB->query($query);
   
   if ($update) {
      
      $query_="SELECT *
            FROM `glpi_plugin_manufacturersimports_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_manufacturersimports_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }
      
      $query="ALTER TABLE `glpi_plugin_manufacturersimports_profiles`
               DROP `name` ;";
      $result=$DB->query($query);
      
      Plugin::migrateItemType(
         array(2150=>'PluginManufacturersimportsModel',2151=>'PluginManufacturersimportsConfig'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
         array("glpi_plugin_manufacturersimports_models","glpi_plugin_manufacturersimports_logs"));
	}
	
	PluginManufacturersimportsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
	return true;
}

function plugin_manufacturersimports_uninstall() {
	global $DB;

	$tables = array("glpi_plugin_manufacturersimports_profiles",
					"glpi_plugin_manufacturersimports_configs",
					"glpi_plugin_manufacturersimports_models",
					"glpi_plugin_manufacturersimports_logs");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
   //old versions	
   $tables = array("glpi_plugin_suppliertag_config",
					"glpi_plugin_suppliertag_profiles",
					"glpi_plugin_suppliertag_models",
					"glpi_plugin_suppliertag_imported");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
		
	return true;
}

function plugin_manufacturersimports_postinit() {

   foreach (PluginManufacturersimportsConfig::getTypes(true) as $type) {

      CommonGLPI::registerStandardTab($type, 'PluginManufacturersimportsConfig');
   }
}

// Define dropdown relations
function plugin_manufacturersimports_getDatabaseRelations() {

	$plugin = new Plugin();
	if ($plugin->isActivated("manufacturersimports"))
		return array (
			"glpi_entities" => array (
				"glpi_plugin_manufacturersimports_configs" => "entities_id"
			),
			"glpi_manufacturers" => array (
				"glpi_plugin_manufacturersimports_configs" => "manufacturers_id"
			),
			"glpi_suppliers" => array (
				"glpi_plugin_manufacturersimports_configs" => "suppliers_id"
			),
			"glpi_documentcategories" => array (
				"glpi_plugin_manufacturersimports_configs" => "documentcategories_id"
			),
			"glpi_documents" => array (
				"glpi_plugin_manufacturersimports_logs" => "documents_id"
			),
			"glpi_profiles" => array ("glpi_plugin_manufacturersimports_profiles" => "profiles_id")
		);
	else
		return array ();

}

////// SEARCH FUNCTIONS ///////() {

function plugin_manufacturersimports_getAddSearchOptions($itemtype) {

    $sopt=array();

    if (in_array($itemtype, PluginManufacturersimportsConfig::getTypes())) {
      if (plugin_manufacturersimports_haveRight("manufacturersimports","r")) {
        $sopt[2150]['table']='glpi_plugin_manufacturersimports_models';
        $sopt[2150]['field']='model_name';
        $sopt[2150]['linkfield']='';
        $sopt[2150]['name']=_n('Suppliers import', 'Suppliers imports', 2 , 'manufacturersimports')." - ".__('Model number', 'manufacturersimports');
        $sopt[2150]['forcegroupby']=true;
        $sopt[2150]['joinparams']    = array('jointype'  => 'itemtype_item');
        $sopt[2150]['massiveaction']=false;
      }
	}
	return $sopt;
}

//force groupby for multible links to items
function plugin_manufacturersimports_forceGroupBy($type) {

	return true;
	switch ($type) {
		case 'PluginManufacturersimportsModel':
			return true;
			break;

	}
	return false;
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////


function plugin_manufacturersimports_MassiveActions($type) {
	
	if (in_array($type,PluginManufacturersimportsConfig::getTypes(true))) {
		return array("plugin_manufacturersimports_add_model"=>__('Add new material brand number', 'manufacturersimports'));
   }

	return array();
	
}

function plugin_manufacturersimports_MassiveActionsDisplay($options=array()) {
	
	if (in_array($options['itemtype'], PluginManufacturersimportsConfig::getTypes(true))) {
      echo "<input type=\"text\" name=\"model_name\">&nbsp;";
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='". __s('Post')."' >";
   }

	return "";
}

function plugin_manufacturersimports_MassiveActionsProcess($data) {
  
   $model=new PluginManufacturersimportsModel();
   
   $res = array('ok' => 0,
      'ko' => 0,
      'noright' => 0);
      
	switch ($data['action']) {
      
      case "plugin_manufacturersimports_add_model":
         foreach ($data["item"] as $key => $val) {
            if ($val==1) {
               $input = array('model_name' => $data['model_name'],
                              'items_id'      => $key,
                              'itemtype'      => $data['itemtype']);
               if ($model->addModel($input)) {
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