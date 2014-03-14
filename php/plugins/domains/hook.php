<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Domains plugin for GLPI
 Copyright (C) 2003-2011 by the Domains Development Team.

 https://forge.indepnet.net/projects/domains
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Domains.

 Domains is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Domains is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Domains. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_domains_install() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/domains/inc/profile.class.php");
   
   $install=false;
   $update78=false;
   $update80=false;
   
   if (!TableExists("glpi_plugin_domain") && !TableExists("glpi_plugin_domains_domains")) {
      
      $install=true;
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/empty-1.4.0.sql");

   } else if (TableExists("glpi_plugin_domain") && !FieldExists("glpi_plugin_domain","recursive")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/update-1.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/update-1.2.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/update-1.2.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/update-1.3.0.sql");

   } else if (TableExists("glpi_plugin_domain_profiles") && FieldExists("glpi_plugin_domain_profiles","interface")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/update-1.2.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/update-1.2.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/update-1.3.0.sql");

   } else if (TableExists("glpi_plugin_domain") && !FieldExists("glpi_plugin_domain","helpdesk_visible")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/update-1.2.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/update-1.3.0.sql");
      
   } else if (!TableExists("glpi_plugin_domains_domains")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/update-1.3.0.sql");
   }
   
   //from 1.3 version
   if (TableExists("glpi_plugin_domains_domains") 
      && !FieldExists("glpi_plugin_domains_domains","users_id_tech")) {
      $DB->runFile(GLPI_ROOT ."/plugins/domains/sql/update-1.5.0.sql");
   }
   
   if ($install || $update78) {

      //Do One time on 0.78
      $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginDomainsDomain' AND `name` = 'Alert Domains'";
      $result = $DB->query($query_id) or die ($DB->error());
      $itemtype = $DB->result($result,0,'id');
      
      $query="INSERT INTO `glpi_notificationtemplatetranslations`
                                 VALUES(NULL, ".$itemtype.", '','##domain.action## : ##domain.entity##',
                        '##lang.domain.entity## :##domain.entity##
   ##FOREACHdomains##
   ##lang.domain.name## : ##domain.name## - ##lang.domain.dateexpiration## : ##domain.dateexpiration##
   ##ENDFOREACHdomains##',
                        '&lt;p&gt;##lang.domain.entity## :##domain.entity##&lt;br /&gt; &lt;br /&gt;
                        ##FOREACHdomains##&lt;br /&gt;
                        ##lang.domain.name##  : ##domain.name## - ##lang.domain.dateexpiration## :  ##domain.dateexpiration##&lt;br /&gt; 
                        ##ENDFOREACHdomains##&lt;/p&gt;');";
      $result=$DB->query($query);
      
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'Alert Expired Domains', 0, 'PluginDomainsDomain', 'ExpiredDomains',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";
      
      $result=$DB->query($query);
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'Alert Domains Which Expire', 0, 'PluginDomainsDomain', 'DomainsWhichExpire',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";

      $result=$DB->query($query);
   }
   if ($update78) {
      $query_="SELECT *
            FROM `glpi_plugin_domains_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_domains_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }
      
      $query="ALTER TABLE `glpi_plugin_domains_profiles`
               DROP `name` ;";
      $result=$DB->query($query);
   
      Plugin::migrateItemType(
         array(4400=>'PluginDomainsDomain'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
         array("glpi_plugin_domains_domains_items"));
      
      Plugin::migrateItemType(
         array(1200 => "PluginAppliancesAppliance",1300 => "PluginWebapplicationsWebapplication"),
         array("glpi_plugin_domains_domains_items"));
	}
	
	CronTask::Register('PluginDomainsDomain', 'DomainsAlert', DAY_TIMESTAMP);
      
   PluginDomainsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_domains_uninstall() {
	global $DB;

	$tables = array("glpi_plugin_domains_domains",
					"glpi_plugin_domains_domains_items",
					"glpi_plugin_domains_domaintypes",
					"glpi_plugin_domains_profiles",
					"glpi_plugin_domains_configs",
					"glpi_plugin_domains_notificationstates");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
   //old versions	
   $tables = array("glpi_plugin_domain",
					"glpi_plugin_domain_device",
					"glpi_dropdown_plugin_domain_type",
					"glpi_plugin_domain_profiles",
					"glpi_plugin_domain_mailing");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");

	$notif = new Notification();
   $options = array('itemtype' => 'PluginDomainsDomain',
                    'event'    => 'ExpiredDomains',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginDomainsDomain',
                    'event'    => 'DomainsWhichExpire',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   
   //templates
   $template = new NotificationTemplate();
   $translation = new NotificationTemplateTranslation();
   $options = array('itemtype' => 'PluginDomainsDomain',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
      $options_template = array('notificationtemplates_id' => $data['id'],
                    'FIELDS'   => 'id');
   
         foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
            $translation->delete($data_template);
         }
      $template->delete($data);
   }
   	
   $tables_glpi = array("glpi_displaypreferences",
					"glpi_documents_items",
					"glpi_bookmarks",
					"glpi_logs",
					"glpi_tickets",
					"glpi_contracts_items");

	foreach($tables_glpi as $table_glpi)
		$DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginDomainsDomain';");

	if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype'=>'PluginDomainsDomain'));
   }

	return true;
}

function plugin_domains_postinit() {
   global $CFG_GLPI, $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['domains'] = array();

   foreach (PluginDomainsDomain::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['domains'][$type]
         = array('PluginDomainsDomain_Item','cleanForItem');

      CommonGLPI::registerStandardTab($type, 'PluginDomainsDomain_Item');
   }
}

function plugin_domains_AssignToTicket($types) {

	if (plugin_domains_haveRight("open_ticket","1"))
		$types['PluginDomainsDomain']=PluginDomainsDomain::getTypeName(2);
	return $types;
}


// Define dropdown relations
function plugin_domains_getDatabaseRelations() {

	$plugin = new Plugin();

	if ($plugin->isActivated("domains"))
		return array("glpi_plugin_domains_domaintypes"=>array("glpi_plugin_domains_domains"=>"plugin_domains_domaintypes_id"),
                     "glpi_users"=>array("glpi_plugin_domains_domains"=>"users_id_tech"),
                     "glpi_groups"=>array("glpi_plugin_domains_domains"=>"groups_id_tech"),
                     "glpi_suppliers"=>array("glpi_plugin_domains_domains"=>"glpi_suppliers"),
                     "glpi_plugin_domains_domains"=>array("glpi_plugin_domains_domains_items"=>"plugin_domains_domains_id"),
                     "glpi_profiles" => array ("glpi_plugin_domains_profiles" => "profiles_id"),
                     "glpi_entities"=>array("glpi_plugin_domains_domains"=>"entities_id",
												"glpi_plugin_domains_domaintypes"=>"entities_id"));
	else
		return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_domains_getDropdown() {
	
	$plugin = new Plugin();

	if ($plugin->isActivated("domains"))
		return array('PluginDomainsDomainType'=>PluginDomainsDomainType::getTypeName(2));
	else
		return array();
}

////// SEARCH FUNCTIONS ///////() {

function plugin_domains_getAddSearchOptions($itemtype) {
    
   $sopt=array();

   if (in_array($itemtype, PluginDomainsDomain::getTypes(true))) {
      if (plugin_domains_haveRight("domains","r")) {
         $sopt[4410]['table']='glpi_plugin_domains_domains';
         $sopt[4410]['field']='name';
         $sopt[4410]['name']=PluginDomainsDomain::getTypeName(2)." - ".
                                      __('Name');
         $sopt[4410]['forcegroupby']=true;
         $sopt[4410]['datatype']='itemlink';
         $sopt[4410]['itemlink_type']='PluginDomainsDomain';
         $sopt[4410]['massiveaction']  = false;
         $sopt[4410]['joinparams']     = array('beforejoin'
                                                => array('table'      => 'glpi_plugin_domains_domains_items',
                                                         'joinparams' => array('jointype' => 'itemtype_item')));
                                                         
         $sopt[4411]['table']='glpi_plugin_domains_domaintypes';
         $sopt[4411]['field']='name';
         $sopt[4411]['name']=PluginDomainsDomain::getTypeName(2)." - ".
                                      PluginDomainsDomainType::getTypeName(1);
         $sopt[4411]['forcegroupby']=true;
         $sopt[4411]['datatype']       = 'dropdown';
         $sopt[4411]['massiveaction']  = false;
         $sopt[4411]['joinparams']     = array('beforejoin' => array(
                                                   array('table'      => 'glpi_plugin_domains_domains',
                                                         'joinparams' => $sopt[4410]['joinparams'])));
      }
	}
	return $sopt;
}

function plugin_domains_displayConfigItem($type,$ID,$data,$num) {

	$searchopt=&Search::getOptions($type);
	$table=$searchopt[$ID]["table"];
	$field=$searchopt[$ID]["field"];
	
	switch ($table.'.'.$field) {
      case "glpi_plugin_domains_domains.date_expiration" :
         if ($data["ITEM_$num"] <= date('Y-m-d') && !empty($data["ITEM_$num"]))
            return " class=\"deleted\" ";
         break;
	}
	return "";
}

function plugin_domains_giveItem($type,$ID,$data,$num) {
	global $CFG_GLPI, $DB;

	$searchopt=&Search::getOptions($type);
	$table=$searchopt[$ID]["table"];
	$field=$searchopt[$ID]["field"];

	switch ($table.'.'.$field) {
		case "glpi_plugin_domains_domains.date_expiration" :
			if (empty($data["ITEM_$num"]))
				$out=__('Does not expire', 'domains');
			else
				$out= Html::convdate($data["ITEM_$num"]);
         return $out;
         break;
		case "glpi_plugin_domains_domains_items.items_id" :
			$query_device = "SELECT DISTINCT `itemtype`
							FROM `glpi_plugin_domains_domains_items`
							WHERE `plugin_domains_domains_id` = '".$data['id']."'
							ORDER BY `itemtype`";
			$result_device = $DB->query($query_device);
			$number_device = $DB->numrows($result_device);
			$out='';
			$domains=$data['id'];
			if ($number_device>0) {
				for ($i=0 ; $i < $number_device ; $i++) {
					$column = "name";
					$itemtype = $DB->result($result_device, $i, "itemtype");
					
					if (!class_exists($itemtype)) {
                  continue;
               }
					$item = new $itemtype();
					if ($item->canView()) {
                  $table_item = getTableForItemType($itemtype);
						$query = "SELECT `".$table_item."`.*, `glpi_entities`.`ID` AS entity "
						." FROM `glpi_plugin_domains_domains_items`, `".$table_item
						."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table_item."`.`entities_id`) "
						." WHERE `".$table_item."`.`id` = `glpi_plugin_domains_domains_items`.`items_id`
						AND `glpi_plugin_domains_domains_items`.`itemtype` = '$itemtype'
						AND `glpi_plugin_domains_domains_items`.`plugin_domains_domains_id` = '".$domains."' "
						. getEntitiesRestrictRequest(" AND ",$table_item,'','',$item->maybeRecursive());

						if ($item->maybeTemplate()) {
							$query.=" AND `".$table_item."`.`is_template` = '0'";
						}
						$query.=" ORDER BY `glpi_entities`.`completename`, `".$table_item."`.`$column`";

						if ($result_linked=$DB->query($query))
							if ($DB->numrows($result_linked)) {
								$item = new $itemtype();
								while ($data = $DB->fetch_assoc($result_linked)) {
                           if ($item->getFromDB($data['id'])) {
                              $out .= $item->getTypeName()." - ".$item->getLink()."<br>";
                           }
								}
							} else
								$out.= ' ';
						} else
							$out.=' ';
				}
			}
         return $out;
         break;
	}
	return "";
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_domains_MassiveActions($type) {

	if (in_array($type,PluginDomainsDomain::getTypes(true))) {
		return array("plugin_domains_add_item"=>__('Associate a domain', 'domains'));
   }
	return array();
}

function plugin_domains_MassiveActionsDisplay($options=array()) {
	
	$domain=new PluginDomainsDomain();
	
	if (in_array($options['itemtype'],PluginDomainsDomain::getTypes(true))) {
		$domain->dropdownDomains("plugin_domains_domains_id");
		echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".__s('Post')."\" >";
	}
	return "";
}

function plugin_domains_MassiveActionsProcess($data) {
   
   $res = array('ok' => 0,
            'ko' => 0,
            'noright' => 0);
            
   $domain_item = new PluginDomainsDomain_Item();
			
	switch ($data['action']) {

      case "plugin_domains_add_item":
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('plugin_domains_domains_id' => $data['plugin_domains_domains_id'],
                              'items_id'      => $key,
                              'itemtype'      => $data['itemtype']);
               if ($domain_item->can(-1,'w',$input)) {
                  if ($domain_item->can(-1,'w',$input)) {
                     $domain_item->add($input);
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

function plugin_datainjection_populate_domains() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginDomainsDomainInjection'] = 'domains';
}

?>