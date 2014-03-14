<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Environment plugin for GLPI
 Copyright (C) 2003-2011 by the Environment Development Team.

 https://forge.indepnet.net/projects/environment
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Environment.

 Environment is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Environment is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Environment. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginEnvironmentDisplay extends CommonDBTM {
   
   static function getTypeName($nb = 0) {

      return __('Environment', 'environment');
   }
   
	function showAppliances($appliances) {
      global $CFG_GLPI,$DB;

      if(plugin_environment_haveRight("appliances","r") && $appliances!=0) {
         echo "<table class='tab_cadrehov' width='750px'>";
         echo "<tr>";
         echo "<th class='center top' colspan='2'>";
         echo "<a href=\"".$CFG_GLPI["root_doc"]."/plugins/appliances/front/appliance.php\">";
         _e('Appliances', 'environment');
         echo "</th></tr>";
         
         $query="SELECT COUNT(`glpi_plugin_appliances_appliances`.`id`) AS total,
                              `glpi_plugin_appliances_appliancetypes`.`name` AS TYPE,
                              `glpi_plugin_appliances_appliances`.`entities_id` 
                  FROM `glpi_plugin_appliances_appliances` ";
         $query.=" LEFT JOIN `glpi_plugin_appliances_appliancetypes` ON (`glpi_plugin_appliances_appliances`.`plugin_appliances_appliancetypes_id` = `glpi_plugin_appliances_appliancetypes`.`id`) ";
         $query.=" LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_appliances_appliances`.`entities_id`) ";
         $query.="WHERE `glpi_plugin_appliances_appliances`.`is_deleted` = '0' "
         . getEntitiesRestrictRequest(" AND ","glpi_plugin_appliances_appliances",'','',true);
         $query.="GROUP BY `glpi_plugin_appliances_appliances`.`entities_id`,`TYPE`
               ORDER BY `glpi_entities`.`completename`, `glpi_plugin_appliances_appliancetypes`.`name`";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            echo "<tr><th colspan='2'>".__('Appliances', 'environment')." : </th></tr>";
            while ($data=$DB->fetch_array($result)) {
               echo "<tr class='tab_bg_1'>";
               $link="";
               if (Session::isMultiEntitiesMode()) {
                  echo "<td class='left top'>".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."</td>";
                  if($data["entities_id"]==0) {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=NULL&field[1]=80";
                  } else {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."&field[1]=80";
                  }
               }
               if (empty($data["TYPE"]))
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/appliances/front/appliance.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=NULL&field[0]=2$link&is_deleted=0&itemtype=PluginAppliancesAppliance&start=0'>".$data["total"]." ".__('Without type', 'environment')."</a></td>";
               else
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/appliances/front/appliance.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=".rawurlencode($data["TYPE"])."&field[0]=2$link&is_deleted=0&itemtype=PluginAppliancesAppliance&start=0'>".$data["total"]." ".$data["TYPE"]."</a></td>";
               echo "</tr>";
            }
         } else
            echo "<tr><th colspan='2'>".__('Appliances', 'environment')." : 0</th></tr>";

         echo "</table><br>";
      }
   }

   function showWebapplications($webapplications) {
      global $CFG_GLPI,$DB;

      if(plugin_environment_haveRight("webapplications","r") && $webapplications!=0) {
         echo "<table class='tab_cadrehov' width='750px'>";
         echo "<tr>";
         echo "<th class='center top' colspan='2'>";
         echo "<a href=\"".$CFG_GLPI["root_doc"]."/plugins/webapplications/front/webapplication.php\">";
         _e('Web applications', 'environment');
         echo "</th></tr>";

         $query="SELECT COUNT(`glpi_plugin_webapplications_webapplications`.`id`) AS total,
                        `glpi_plugin_webapplications_webapplicationtypes`.`name` AS TYPE,
                        `glpi_plugin_webapplications_webapplications`.`entities_id` 
                  FROM `glpi_plugin_webapplications_webapplications` ";
         $query.=" LEFT JOIN `glpi_plugin_webapplications_webapplicationtypes` ON (`glpi_plugin_webapplications_webapplications`.`plugin_webapplications_webapplicationtypes_id` = `glpi_plugin_webapplications_webapplicationtypes`.`id`) ";
         $query.=" LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id`=`glpi_plugin_webapplications_webapplications`.`entities_id`) ";
         $query.="WHERE `glpi_plugin_webapplications_webapplications`.`is_deleted` = '0' "
         . getEntitiesRestrictRequest(" AND ","glpi_plugin_webapplications_webapplications",'','',true);
         $query.="GROUP BY `glpi_plugin_webapplications_webapplications`.`entities_id`,`TYPE`
               ORDER BY `glpi_entities`.`completename`, `glpi_plugin_webapplications_webapplicationtypes`.`name` ";
         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            echo "<tr><th colspan='2'>".__('Web applications', 'environment')." : </th></tr>";
            while ($data=$DB->fetch_array($result)) {
               echo "<tr class='tab_bg_1'>";
               $link="";
               if (Session::isMultiEntitiesMode()) {
                  echo "<td class='left top'>".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."</td>";
                  if($data["entities_id"]==0) {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=NULL&field[1]=80";
                  } else {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."&field[1]=80";
                  }
               }
               if (empty($data["TYPE"]))
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/webapplications/front/webapplication.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=NULL&field[0]=2$link&is_deleted=0&itemtype=PluginWebapplicationsWebapplication&start=0'>".$data["total"]." ".__('Without type', 'environment')."</a></td>";
               else
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/webapplications/front/webapplication.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=".rawurlencode($data["TYPE"])."&field[0]=2$link&is_deleted=0&itemtype=PluginWebapplicationsWebapplication&start=0'>".$data["total"]." ".$data["TYPE"]."</a></td>";
               echo "</tr>";
            }
         } else
            echo "<tr><th colspan='2'>".__('Web applications', 'environment')." : 0</th></tr>";

         echo "</table><br>";
      }
   }

   function showCertificates($certificates) {
      global $CFG_GLPI,$DB;

      if(plugin_environment_haveRight("certificates","r") && $certificates!=0) {
         echo "<table class='tab_cadrehov' width='750px'>";
         echo "<tr>";
         echo "<th class='center top' colspan='2'>";
         echo "<a href=\"".$CFG_GLPI["root_doc"]."/plugins/certificates/front/certificate.php\">";
         _e('Certificates', 'environment');
         echo "</th></tr>";

         $query="SELECT COUNT(`glpi_plugin_certificates_certificates`.`id`) AS total,
                        `glpi_plugin_certificates_certificatetypes`.`name` AS TYPE,
                        `glpi_plugin_certificates_certificates`.`entities_id` 
                  FROM `glpi_plugin_certificates_certificates` ";
         $query.=" LEFT JOIN `glpi_plugin_certificates_certificatetypes` ON (`glpi_plugin_certificates_certificates`.`plugin_certificates_certificatetypes_id` = `glpi_plugin_certificates_certificatetypes`.`id`) ";
         $query.=" LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_certificates_certificates`.`entities_id`) ";
         $query.="WHERE `glpi_plugin_certificates_certificates`.`is_deleted` = '0' "
         . getEntitiesRestrictRequest(" AND ","glpi_plugin_certificates_certificates",'','',true);
         $query.="GROUP BY `glpi_plugin_certificates_certificates`.`entities_id`,`TYPE`
               ORDER BY `glpi_entities`.`completename`, `glpi_plugin_certificates_certificatetypes`.`name` ";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            echo "<tr><th colspan='2'>".__('Certificates', 'environment')." : </th></tr>";
            while ($data=$DB->fetch_array($result)) {
               echo "<tr class='tab_bg_1'>";
               $link="";
               if (Session::isMultiEntitiesMode()) {
                  echo "<td class='left top'>".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."</td>";
                  if($data["entities_id"]==0) {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=NULL&field[1]=80";
                  } else {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."&field[1]=80";
                  }
               }
               if (empty($data["TYPE"]))
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/certificates/front/certificate.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=NULL&field[0]=2$link&is_deleted=0&itemtype=PluginCertificatesCertificate&start=0'>".$data["total"]." ".__('Without type', 'environment')."</a></td>";
               else
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/certificates/front/certificate.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=".rawurlencode($data["TYPE"])."&field[0]=2$link&is_deleted=0&itemtype=PluginCertificatesCertificate&start=0'>".$data["total"]." ".$data["TYPE"]."</a></td>";
               echo "</tr>";
            }
         } else
            echo "<tr><th colspan='2'>".__('Certificates', 'environment')." : 0</th></tr>";

         echo "</table><br>";
      }
   }

   function showAccounts($accounts) {
      global $CFG_GLPI,$DB;

      if(plugin_environment_haveRight("accounts","r") && $accounts!=0) {
         echo "<table class='tab_cadrehov' width='750px'>";
         echo "<tr>";
         echo "<th class='center top' colspan='2'>";
         echo "<a href=\"".$CFG_GLPI["root_doc"]."/plugins/accounts/front/account.php\">";
         _e('Accounts', 'environment');
         echo "</th></tr>";
         
         $who=Session::getLoginUserID();

         if (count($_SESSION["glpigroups"])){
            $first_groups=true;
            $groups="";
            foreach ($_SESSION['glpigroups'] as $val){
               if (!$first_groups) $groups.=",";
               else $first_groups=false;
               $groups.=$val;
            }			
            $ASSIGN=" (`glpi_plugin_accounts_accounts`.`groups_id` IN (SELECT DISTINCT `groups_id` 
                                                                        FROM `glpi_groups_users` 
                                                                        WHERE `groups_id` IN ($groups)) OR";
            $ASSIGN.=" `glpi_plugin_accounts_accounts`.`users_id` = '$who') AND  ";
         } else { // Only personal ones
            $ASSIGN=" `glpi_plugin_accounts_accounts`.`users_id` = '$who' AND  ";
         }
		
         $query="SELECT COUNT(`glpi_plugin_accounts_accounts`.`id`) AS total,
                              `glpi_plugin_accounts_accounttypes`.`name` AS TYPE, 
                              `glpi_plugin_accounts_accounts`.`entities_id` 
                  FROM `glpi_plugin_accounts_accounts` ";
         $query.=" LEFT JOIN `glpi_plugin_accounts_accounttypes` ON (`glpi_plugin_accounts_accounts`.`plugin_accounts_accounttypes_id` = `glpi_plugin_accounts_accounttypes`.`id`) ";
         $query.=" LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_accounts_accounts`.`entities_id`) ";
         $query.= "WHERE ";
         if (!plugin_accounts_haveRight("all_users","r")) {
            if (!plugin_accounts_haveRight("my_groups","r")){
               $query.= " `glpi_plugin_accounts_accounts`.`users_id` = ".$who." AND  ";
            }else{
               $query.= " $ASSIGN ";
            }
         }
         $query.=" `glpi_plugin_accounts_accounts`.`is_deleted` = '0' "
         . getEntitiesRestrictRequest(" AND ","glpi_plugin_accounts_accounts",'','',true);
         $query.="GROUP BY `glpi_plugin_accounts_accounts`.`entities_id`,`TYPE`
               ORDER BY `glpi_entities`.`completename`, `glpi_plugin_accounts_accounttypes`.`name`";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            echo "<tr><th colspan='2'>".__('Accounts', 'environment')." : </th></tr>";
            while ($data=$DB->fetch_array($result)) {
               echo "<tr class='tab_bg_1'>";
               $link="";
               if (Session::isMultiEntitiesMode()) {
                  echo "<td class='left top'>".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."</td>";
                  if($data["entities_id"]==0) {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=NULL&field[1]=80";
                  } else {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."&field[1]=80";
                  }
               }
               
               if (empty($data["TYPE"])) {
                  if (!plugin_accounts_haveRight("all_users","r")) {
                     if (plugin_accounts_haveRight("my_groups","r")) {
                        if($data["entities_id"]==0) {
                           $linkgroup="&link1[1]=AND&contains1[1]=NULL&field1[1]=80";
                        }else{
                           $linkgroup="&link1[1]=AND&contains1[1]=".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."&field1[1]=80";
                        }
                        echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/accounts/front/account.php?contains1[0]=NULL&field1[0]=2$linkgroup&is_deleted=0&itemtype=PluginAcountsAccount&start=0'>".$data["total"]." ".__('Without type', 'environment')."</a></td>";
                     }else {
                        echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/accounts/front/account.php?searchtype[0]=contains&contains[0]=NULL&field[0]=2$link&is_deleted=0&itemtype=PluginAcountsAccount&start=0'>".$data["total"]." ".__('Without type', 'environment')."</a></td>";
                     }
                  } else {
                     echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/accounts/front/account.php?searchtype[0]=contains&contains[0]=NULL&field[0]=2$link&is_deleted=0&itemtype=PluginAcountsAccount&start=0'>".$data["total"]." ".__('Without type', 'environment')."</a></td>";
                  }
               } else {
                  
                  if (!plugin_accounts_haveRight("all_users","r")) {
                     if (plugin_accounts_haveRight("my_groups","r")) {
                        if($data["entities_id"]==0) {
                           $linkgroup="&link1[1]=AND&contains1[1]=NULL&field1[1]=80";
                        }else{
                           $linkgroup="&link1[1]=AND&contains1[1]=".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."&field1[1]=80";
                        }
                        echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/accounts/front/account.php?contains1[0]=".rawurlencode($data["TYPE"])."&field1[0]=2$linkgroup&is_deleted=0&itemtype=PluginAcountsAccount&start=0'>".$data["total"]." ".$data["TYPE"]."</a></td>";
                     }else {
                        echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/accounts/front/account.php?searchtype[0]=contains&contains[0]=".rawurlencode($data["TYPE"])."&field[0]=2$link&is_deleted=0&itemtype=PluginAcountsAccount&start=0'>".$data["total"]." ".$data["TYPE"]."</a></td>";
                     }
                  } else {
                     echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/accounts/front/account.php?searchtype[0]=contains&contains[0]=".rawurlencode($data["TYPE"])."&field[0]=2$link&is_deleted=0&itemtype=PluginAcountsAccount&start=0'>".$data["total"]." ".$data["TYPE"]."</a></td>";
                  }
               }
            }
         } else
            echo "<tr><th colspan='2'>".__('Accounts', 'environment')." : 0</th></tr>";

         echo "</table><br>";
      }
   }

   function showDomains($domains) {
      global $CFG_GLPI,$DB;

      if(plugin_environment_haveRight("domains","r") && $domains!=0) {
         echo "<table class='tab_cadrehov' width='750px'>";
         echo "<tr>";
         echo "<th class='center top' colspan='2'>";
         echo "<a href=\"".$CFG_GLPI["root_doc"]."/plugins/domains/front/domain.php\">";
         _e('Domains', 'environment');
         echo "</th></tr>";

         $query="SELECT COUNT(`glpi_plugin_domains_domains`.`id`) AS total,
                              `glpi_plugin_domains_domaintypes`.`name` AS TYPE,
                              `glpi_plugin_domains_domains`.`entities_id` 
                  FROM `glpi_plugin_domains_domains` ";
         $query.=" LEFT JOIN `glpi_plugin_domains_domaintypes` ON (`glpi_plugin_domains_domains`.`plugin_domains_domaintypes_id` = `glpi_plugin_domains_domaintypes`.`id`) ";
         $query.=" LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_domains_domains`.`entities_id`) ";
         $query.="WHERE `glpi_plugin_domains_domains`.`is_deleted` = '0' "
         . getEntitiesRestrictRequest(" AND ","glpi_plugin_domains_domains",'','',true);
         $query.="GROUP BY `glpi_plugin_domains_domains`.`entities_id`,`TYPE`
               ORDER BY `glpi_entities`.`completename`, `glpi_plugin_domains_domaintypes`.`name` ";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            echo "<tr><th colspan='2'>".__('Domains', 'environment')." : </th></tr>";
            while ($data=$DB->fetch_array($result)) {
               echo "<tr class='tab_bg_1'>";
               $link="";
               if (Session::isMultiEntitiesMode()) {
                  echo "<td class='left top'>".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."</td>";
                  if($data["entities_id"]==0) {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=NULL&field[1]=80";
                  } else {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."&field[1]=80";
                  }
               }
               if (empty($data["TYPE"]))
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/domains/front/domain.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=NULL&field[0]=2$link&is_deleted=0&itemtype=PluginDomainsDomain&start=0'>".$data["total"]." ".__('Without type', 'environment')."</a></td>";
               else
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/domains/front/domain.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=".rawurlencode($data["TYPE"])."&field[0]=2$link&is_deleted=0&itemtype=PluginDomainsDomain&start=0'>".$data["total"]." ".$data["TYPE"]."</a></td>";
               echo "</tr>";
            }
         } else
            echo "<tr><th colspan='2'>".__('Domains', 'environment')." : 0</th></tr>";

         echo "</table><br>";
      }
   }

   function showDatabases($databases) {
      global $CFG_GLPI,$DB;

      if(plugin_environment_haveRight("databases","r") && $databases!=0) {
         echo "<table class='tab_cadrehov' width='750px'>";
         echo "<tr>";
         echo "<th class='center top' colspan='2'>";
         echo "<a href=\"".$CFG_GLPI["root_doc"]."/plugins/databases/front/database.php\">";
         _e('Databases', 'environment');
         echo "</th></tr>";

         $query="SELECT COUNT(`glpi_plugin_databases_databases`.`id`) AS total,
                              `glpi_plugin_databases_databasetypes`.`name` AS TYPE,
                              `glpi_plugin_databases_databases`.`entities_id` 
                  FROM `glpi_plugin_databases_databases` ";
         $query.=" LEFT JOIN `glpi_plugin_databases_databasetypes` ON (`glpi_plugin_databases_databases`.`plugin_databases_databasetypes_id` = `glpi_plugin_databases_databasetypes`.`id`) ";
         $query.=" LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_databases_databases`.`entities_id`) ";
         $query.="WHERE `glpi_plugin_databases_databases`.`is_deleted` = '0' "
         . getEntitiesRestrictRequest(" AND ","glpi_plugin_databases_databases",'','',true);
         $query.="GROUP BY `glpi_plugin_databases_databases`.`entities_id`,`TYPE`
               ORDER BY `glpi_entities`.`completename`, `glpi_plugin_databases_databasetypes`.`name`";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            echo "<tr><th colspan='2'>".__('Databases', 'environment')." : </th></tr>";
            while ($data=$DB->fetch_array($result)) {
               echo "<tr class='tab_bg_1'>";
               $link="";
               if (Session::isMultiEntitiesMode()) {
                  echo "<td class='left top'>".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."</td>";
                  if($data["entities_id"]==0) {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=NULL&field[1]=80";
                  } else {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."&field[1]=80";
                  }
               }
               if (empty($data["TYPE"]))
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/databases/front/database.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=NULL&field[0]=10$link&is_deleted=0&itemtype=PluginDatabasesDatabase&start=0'>".$data["total"]." ".__('Without type', 'environment')."</a></td>";
               else
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/databases/front/database.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=".rawurlencode($data["TYPE"])."&field[0]=10$link&is_deleted=0&itemtype=PluginDatabasesDatabase&start=0'>".$data["total"]." ".$data["TYPE"]."</a></td>";
               echo "</tr>";
            }
         } else
            echo "<tr><th colspan='2'>".__('Databases', 'environment')." : 0</th></tr>";

         echo "</table><br>";
      }
   }

   function showBadges($badges) {
      global $CFG_GLPI,$DB;

      if(plugin_environment_haveRight("badges","r") && $badges!=0) {
         echo "<table class='tab_cadrehov' width='750px'>";
         echo "<tr>";
         echo "<th class='center top' colspan='2'>";
         echo "<a href=\"".$CFG_GLPI["root_doc"]."/plugins/badges/front/badge.php\">";
         _e('Badges', 'environment');
         echo "</th></tr>";

         $query="SELECT COUNT(`glpi_plugin_badges_badges`.`id`) AS total,
                           `glpi_plugin_badges_badgetypes`.`name` AS TYPE,
                           `glpi_plugin_badges_badges`.`entities_id` 
                  FROM `glpi_plugin_badges_badges` ";
         $query.=" LEFT JOIN `glpi_plugin_badges_badgetypes` ON (`glpi_plugin_badges_badges`.`plugin_badges_badgetypes_id` = `glpi_plugin_badges_badgetypes`.`id`) ";
         $query.=" LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_badges_badges`.`entities_id`) ";
         $query.="WHERE `glpi_plugin_badges_badges`.`is_deleted` = '0' "
         . getEntitiesRestrictRequest(" AND ","glpi_plugin_badges_badges",'','',false);
         $query.="GROUP BY `glpi_plugin_badges_badges`.`entities_id`,`TYPE`
               ORDER BY `glpi_entities`.`completename`, `glpi_plugin_badges_badgetypes`.`name` ";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            echo "<tr><th colspan='2'>".__('Badges', 'environment')." : </th></tr>";
            while ($data=$DB->fetch_array($result)) {
               echo "<tr class='tab_bg_1'>";
               $link="";
               if (Session::isMultiEntitiesMode()) {
                  echo "<td class='left top'>".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."</td>";
                  if($data["entities_id"]==0) {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=NULL&field[1]=80";
                  } else {
                     $link="&link[1]=AND&searchtype[1]=contains&contains[1]=".Dropdown::getDropdownName("glpi_entities",$data["entities_id"])."&field[1]=80";
                  }
               }
               if (empty($data["TYPE"]))
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/badges/front/badge.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=NULL&field[0]=2$link&is_deleted=0&itemtype=PluginBadgesBadge&start=0'>".$data["total"]." ".__('Without type', 'environment')."</a></td>";
               else
                  echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/badges/front/badge.php?glpisearchcount=2&searchtype[0]=contains&contains[0]=".rawurlencode($data["TYPE"])."&field[0]=2$link&is_deleted=0&itemtype=PluginBadgesBadge&start=0'>".$data["total"]." ".$data["TYPE"]."</a></td>";
               echo "</tr>";
            }
         } else
            echo "<tr><th colspan='2'>".__('Badges', 'environment')." : 0</th></tr>";

         echo "</table><br>";
      }
   }
}

?>