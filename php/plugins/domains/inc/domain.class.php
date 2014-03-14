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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginDomainsDomain extends CommonDBTM {
	
   public $dohistory=true;
   
   static $types = array('Computer','Monitor','NetworkEquipment','Peripheral',
         'Phone','Printer','Software');
         
   static function getTypeName($nb=0) {

      return _n('Domain', 'Domains', $nb, 'domains');
   }
   
   static function canCreate() {
      return plugin_domains_haveRight('domains', 'w');
   }

   static function canView() {
      return plugin_domains_haveRight('domains', 'r');
   }
	
	function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Supplier') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2), self::countForItem($item));
         }
         return self::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Supplier') {
         self::showForSupplier($item);
      }
      return true;
   }
   
   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_domains_domains',
                                  "`suppliers_id` = '".$item->getID()."'");
   }
   
	function cleanDBonPurge() {

		$temp = new PluginDomainsDomain_Item();
		$temp->deleteByCriteria(array('plugin_domains_domains_id' => $this->fields['id']));

	}
	
	function getSearchOptions() {

      $tab                       = array();
    
      $tab['common']             = self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();
      
      $tab[2]['table']           = 'glpi_plugin_domains_domaintypes';
      $tab[2]['field']           = 'name';
      $tab[2]['name']            = __('Type');
      $tab[2]['datatype']        = 'dropdown';
      
      $tab[3]['table']           = 'glpi_users';
      $tab[3]['field']           = 'name';
      $tab[3]['linkfield']       = 'users_id_tech';
      $tab[3]['name']            = __('Technician in charge of the hardware');
      $tab[3]['datatype']       = 'dropdown';
      
      $tab[4]['table']           = 'glpi_suppliers';
      $tab[4]['field']           = 'name';
      $tab[4]['name']            = __('Supplier');
      $tab[4]['datatype']        = 'itemlink';
      $tab[4]['itemlink_type']   = 'Supplier';
      $tab[4]['forcegroupby']    = true;
      $tab[4]['datatype']        = 'dropdown';
      
      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'date_creation';
      $tab[5]['name']            = __('Creation date');
      $tab[5]['datatype']        = 'date';
      
      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'date_expiration';
      $tab[6]['name']            = __('Expiration date');
      $tab[6]['datatype']        = 'date';
      
      $tab[7]['table']           = $this->getTable();
      $tab[7]['field']           = 'comment';
      $tab[7]['name']            = __('Comments');
      $tab[7]['datatype']        = 'text';
      
      $tab[8]['table']           = 'glpi_plugin_domains_domains_items';
      $tab[8]['field']           = 'items_id';
      $tab[8]['nosearch']        = true;
      $tab[8]['massiveaction']   = false;
      $tab[8]['name']            = _n('Associated item' , 'Associated items', 2);
      $tab[8]['forcegroupby']    = true;
      $tab[8]['joinparams']      = array('jointype' => 'child');
      
      $tab[9]['table']           = $this->getTable();
      $tab[9]['field']           = 'others';
      $tab[9]['name']            = __('Others');
      
      $tab[10]['table']          = 'glpi_groups';
      $tab[10]['field']          = 'name';
      $tab[10]['linkfield']      = 'groups_id_tech';
      $tab[10]['name']           = __('Group in charge of the hardware');
      $tab[10]['condition']      = '`is_assign`';
      $tab[10]['datatype']       = 'dropdown';
      
      $tab[11]['table']          = $this->getTable();
      $tab[11]['field']          = 'is_helpdesk_visible';
      $tab[11]['name']           = __('Associable to a ticket');
      $tab[11]['datatype']       = 'bool';
      
      $tab[12]['table']          = $this->getTable();
      $tab[12]['field']          = 'date_mod';
      $tab[12]['massiveaction']  = false;
      $tab[12]['name']           = __('Last update');
      $tab[12]['datatype']       = 'datetime';
      
      $tab[18]['table']          = $this->getTable();
      $tab[18]['field']          = 'is_recursive';
      $tab[18]['name']           = __('Child entities');
      $tab[18]['datatype']       = 'bool';
      
      $tab[30]['table']          = $this->getTable();
      $tab[30]['field']          = 'id';
      $tab[30]['name']           = __('ID');
      $tab[30]['datatype']       = 'number';


      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['datatype']       = 'dropdown';
		
		return $tab;
   }
	
	
	function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab('PluginDomainsDomain_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Contract_Item', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }
   
	function prepareInputForAdd($input) {
		
		if (isset($input['date_creation']) 
            && empty($input['date_creation'])) 
         $input['date_creation']='NULL';
		if (isset($input['date_expiration']) 
            && empty($input['date_expiration'])) 
         $input['date_expiration']='NULL';
		
		return $input;
	}
	
	function prepareInputForUpdate($input) {
		
		if (isset($input['date_creation']) 
            && empty($input['date_creation'])) 
         $input['date_creation']='NULL';
		if (isset($input['date_expiration']) 
            && empty($input['date_expiration'])) 
         $input['date_expiration']='NULL';
		
		return $input;
	}
	
	/*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */
   function getSelectLinkedItem () {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_domains_domains_items`
              WHERE `plugin_domains_domains_id`='" . $this->fields['id']."'";
   }
   
	function showForm ($ID, $options=array()) {
      global $CFG_GLPI;
		
		$this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";
      
      echo "<td>".__('Others')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"others");	
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".__('Supplier')."</td>";
      echo "<td>";
      Dropdown::show('Supplier', array('name' => "suppliers_id",
                                       'value' => $this->fields["suppliers_id"], 
                                       'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td>".__('Creation date')."</td>";
      echo "<td>";
      Html::showDateFormItem("date_creation",$this->fields["date_creation"],true,true);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".__('Type')."</td><td>";
      Dropdown::show('PluginDomainsDomainType', array('name' => "plugin_domains_domaintypes_id",
                                                      'value' => $this->fields["plugin_domains_domaintypes_id"],
                                                      'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td>".__('Expiration date');
      echo "&nbsp;";
      Html::showToolTip(nl2br(__('Empty for infinite', 'domains')));
      echo "</td>";
      echo "<td>";
      Html::showDateFormItem("date_expiration",$this->fields["date_expiration"],true,true);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".__('Technician in charge of the hardware')."</td><td>";
      User::dropdown(array('name' => "users_id_tech",
                           'value' => $this->fields["users_id_tech"],
                           'entity' => $this->fields["entities_id"],
                           'right' => 'interface'));
      echo "</td>";
      
      echo "<td>" . __('Associable to a ticket') . "</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".__('Group in charge of the hardware')."</td>";
      echo "<td>";
      Dropdown::show('Group', array('name' => "groups_id_tech",
                                    'value' => $this->fields["groups_id_tech"], 
                                    'entity' => $this->fields["entities_id"],
                                    'condition' => '`is_assign`'));
      echo "</td>";
      
      echo "<td class='center' colspan='2'>";
      printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>";
      echo __('Comments')."</td>";
      echo "<td colspan = '3' class='center'>";
      echo "<textarea cols='115' rows='5' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td>";
      
      echo "</tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

		return true;
	}
	
	function dropdownDomains($myname,$entity_restrict='',$used=array()) {
      global $DB,$CFG_GLPI;

      $rand=mt_rand();

      $where=" WHERE `".$this->getTable()."`.`is_deleted` = '0' ";
      $where.=getEntitiesRestrictRequest("AND",$this->getTable(),'',$entity_restrict,true);

      if (count($used)) {
         $where .= " AND `id` NOT IN (0";
         foreach ($used as $ID)
            $where .= ",$ID";
         $where .= ")";
      }

      $query="SELECT *
        FROM `glpi_plugin_domains_domaintypes`
        WHERE `id` IN (
          SELECT DISTINCT `plugin_domains_domaintypes_id`
          FROM `".$this->getTable()."`
          $where)
        GROUP BY `name`
        ORDER BY `name`";
      $result=$DB->query($query);

      echo "<select name='_type' id='plugin_domains_domaintypes_id'>\n";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>\n";
      while ($data=$DB->fetch_assoc($result)) {
         echo "<option value='".$data['id']."'>".$data['name']."</option>\n";
      }
      echo "</select>\n";

      $params=array('plugin_domains_domaintypes_id'=>'__VALUE__',
        'entity_restrict'=>$entity_restrict,
        'rand'=>$rand,
        'myname'=>$myname,
        'used'=>$used
        );

      Ajax::updateItemOnSelectEvent("plugin_domains_domaintypes_id","show_$myname$rand",$CFG_GLPI["root_doc"]."/plugins/domains/ajax/dropdownTypeDomains.php",$params);

      echo "<span id='show_$myname$rand'>";
      $_POST["entity_restrict"]=$entity_restrict;
      $_POST["plugin_domains_domaintypes_id"]=0;
      $_POST["myname"]=$myname;
      $_POST["rand"]=$rand;
      $_POST["used"]=$used;
      include (GLPI_ROOT."/plugins/domains/ajax/dropdownTypeDomains.php");
      echo "</span>\n";

      return $rand;
   }
  
  // Cron action
   static function cronInfo($name) {
       
      switch ($name) {
         case 'DomainsAlert':
            return array (
               'description' => __('Expired or expiring domains', 'domains'));   // Optional
            break;
      }
      return array();
   }

   static function queryExpiredDomains() {
      global $DB;
      
      $config=new PluginDomainsConfig();
      $config->getFromDB('1');
      $delay=$config->fields["delay_expired"];

      $query = "SELECT * 
         FROM `glpi_plugin_domains_domains`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > $delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) > 0 ";

      return $query;
   }
   
   static function queryDomainsWhichExpire() {
      global $DB;
      
      $config=new PluginDomainsConfig();
      $config->getFromDB('1');
      $delay=$config->fields["delay_whichexpire"];
      
      $query = "SELECT *
         FROM `glpi_plugin_domains_domains`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > -$delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) < 0 ";

      return $query;
   }
   /**
    * Cron action on domains : ExpiredDomains or DomainsWhichExpire
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronDomainsAlert($task=NULL) {
      global $DB,$CFG_GLPI;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message=array();
      $cron_status = 0;
      
      $query_expired = self::queryExpiredDomains();
      $query_whichexpire = self::queryDomainsWhichExpire();
      
      $querys = array(Alert::NOTICE=>$query_whichexpire, Alert::END=>$query_expired);
      
      $domain_infos = array();
      $domain_messages = array();

      foreach ($querys as $type => $query) {
         $domain_infos[$type] = array();
         foreach ($DB->request($query) as $data) {
            $entity = $data['entities_id'];
            $message = $data["name"].": ".
                        Html::convdate($data["date_expiration"])."<br>\n";
            $domain_infos[$type][$entity][] = $data;

            if (!isset($domains_infos[$type][$entity])) {
               $domain_messages[$type][$entity] = __('Domains expired since more', 'domains')."<br />";
            }
            $domain_messages[$type][$entity] .= $message;
         }
      }
      
      foreach ($querys as $type => $query) {
      
         foreach ($domain_infos[$type] as $entity => $domains) {
            Plugin::loadLang('domains');

            if (NotificationEvent::raiseEvent(($type==Alert::NOTICE?"DomainsWhichExpire":"ExpiredDomains"),
                                              new PluginDomainsDomain(),
                                              array('entities_id'=>$entity,
                                                    'domains'=>$domains))) {
               $message = $domain_messages[$type][$entity];
               $cron_status = 1;
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",
                                                       $entity).":  $message\n");
                  $task->addVolume(1);
               } else {
                  addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",
                                                                    $entity).":  $message");
               }

            } else {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",$entity).
                             ":  Send domains alert failed\n");
               } else {
                  addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                          ":  Send domains alert failed",false,ERROR);
               }
            }
         }
      }
      
      return $cron_status;
   }
   
   static function configCron($target) {

      $config=new PluginDomainsConfig();
      $config->showForm($target,1);
    
   }
   
   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }


   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
   
   //Massive action
   function getSpecificMassiveActions($checkitem = NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($isadmin) {
         $actions['Install'] = __('Associate');
         $actions['Desinstall'] = __('Dissociate');

         if (Session::haveRight('transfer', 'r')
            && Session::isMultiEntitiesMode()) {
            $actions['Transfert'] = __('Transfer');
         }
      }
      return $actions;
   }

   function showSpecificMassiveActionsParameters($input = array()) {

      switch ($input['action']) {
         case "Install" :
            Dropdown::showAllItems("item_item", 0, 0, -1, self::getTypes(true),false,false,'typeitem');
            echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='" . __s('Post') . "'>";
            return true;
            break;
         case "Desinstall" :
            Dropdown::showAllItems("item_item", 0, 0, -1, self::getTypes(true),false,false,'typeitem');
            echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='" . __s('Post') . "'>";
            return true;
            break;
         case "Transfert" :
            Dropdown::show('Entity');
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='" . __s('Post') . "'>";
            return true;
            break;

         default :
            return parent::showSpecificMassiveActionsParameters($input);
            break;
      }
      return false;
   }

   function doSpecificMassiveActions($input = array()) {

      $res = array('ok' => 0,
         'ko' => 0,
         'noright' => 0);

      $domain_item = new PluginDomainsDomain_Item();

      switch ($input['action']) {
         case "Transfert" :
            if ($input['itemtype'] == 'PluginDomainsDomain') {
               foreach ($input["item"] as $key => $val) {
                  if ($val == 1) {
                     $this->getFromDB($key);
                     $type = PluginDomainsDomainType::transfer($this->fields["plugin_domains_domaintypes_id"], $input['entities_id']);
                     if ($type > 0) {
                        $values["id"] = $key;
                        $values["plugin_domains_domaintypes_id"] = $type;

                        if ($this->update($values)) {
                           $res['ok']++;
                        } else {
                           $res['ko']++;
                        }
                     }

                     unset($values);
                     $values["id"] = $key;
                     $values["entities_id"] = $input['entities_id'];

                     if ($this->update($values)) {
                        $res['ok']++;
                     } else {
                        $res['ko']++;
                     }
                  }
               }
            }
            break;
         case "Install" :
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  $values = array('plugin_domains_domains_id' => $key,
                     'items_id'      => $input["item_item"],
                     'itemtype'      => $input['typeitem']);
                  if ($domain_item->add($values)) {
                     $res['ok']++;
                  } else {
                     $res['ko']++;
                  }
               }
            }
            break;
         case "Desinstall" :
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  if ($domain_item->deleteItemByDomainsAndItem($key,$input['item_item'],$input['typeitem'])) {
                     $res['ok']++;
                  } else {
                     $res['ko']++;
                  }
               }
            }
            break;
         default :
            return parent::doSpecificMassiveActions($input);
            break;
      }
      return $res;
   }
   
   /**
    * Show domains associated to a supplier
    *
    * @since version 0.84
    *
    * @param $item            CommonDBTM object for which associated domains must be displayed
    * @param $withtemplate    (default '')
   **/
   static function showForSupplier(CommonDBTM $item, $withtemplate='') {
      global $DB, $CFG_GLPI;

      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!plugin_domains_haveRight('domains', 'r')) {
         return false;
      }

      if (!$item->can($item->fields['id'],'r')) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }

      $rand          = mt_rand();
      $is_recursive  = $item->isRecursive();
      
      $query = "SELECT `glpi_plugin_domains_domains`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity,
                       `glpi_plugin_domains_domains`.`name` AS assocName,
                       `glpi_plugin_domains_domains`.* "
        ."FROM `glpi_plugin_domains_domains` "
        ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_domains_domains`.`entities_id`) "
        ." WHERE `suppliers_id` = '$ID' "
        . getEntitiesRestrictRequest(" AND ","glpi_plugin_domains_domains",'','',true);
      $query.= " ORDER BY `assocName` ";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;

      $domains       = array();
      $domain        = new PluginDomainsDomain();
      $used          = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $domains[$data['assocID']] = $data;
            $used[$data['id']] = $data['id'];
         }
      }

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>".__('Entity')."</th>";
      }
      echo "<th>".__('Group in charge of the hardware')."</th>";
      echo "<th>".__('Supplier')."</th>";
      echo "<th>".__('Technician in charge of the hardware')."</th>";
      echo "<th>".__('Type')."</th>";
      echo "<th>".__('Creation date')."</th>";
      echo "<th>".__('Expiration date')."</th>";
      echo "</tr>";
      $used = array();

      if ($number) {

         Session::initNavigateListItems('PluginDomainsDomain',
                           //TRANS : %1$s is the itemtype name,
                           //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                $item->getTypeName(1), $item->getName()));

         
         foreach  ($domains as $data) {
            $domainID   = $data["id"];
            $link       = NOT_AVAILABLE;

            if ($domain->getFromDB($domainID)) {
               $link    = $domain->getLink();
            }

            Session::addToNavigateListItems('PluginDomainsDomain', $domainID);
            
            $used[$domainID] = $domainID;
            $assocID      = $data["assocID"];

            echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
            echo "<td class='center'>$link</td>";
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data['entities_id']).
                    "</td>";
            }
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_groups",$data["groups_id_tech"])."</td>";
            echo "<td>";
            echo "<a href=\"".$CFG_GLPI["root_doc"]."/front/enterprise.form.php?ID=".$data["suppliers_id"]."\">";
            echo Dropdown::getDropdownName("glpi_suppliers",$data["suppliers_id"]);
            if ($_SESSION["glpiis_ids_visible"] == 1 )
               echo " (".$data["suppliers_id"].")";
            echo "</a></td>";
            echo "<td class='center'>".getUsername($data["users_id_tech"])."</td>";
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_plugin_domains_domaintypes",$data["plugin_domains_domaintypes_id"])."</td>";
            echo "<td class='center'>".Html::convdate($data["date_creation"])."</td>";
            if ($data["date_expiration"] <= date('Y-m-d') 
                  && !empty($data["date_expiration"])) {
               echo "<td class='center'><div class='deleted'>".convdate($data["date_expiration"])."</div></td>";
            } else if (empty($data["date_expiration"])) {
               echo "<td class='center'>".__('Does not expire', 'domains')."</td>";
            } else {
               echo "<td class='center'>".Html::convdate($data["date_expiration"])."</td>";
            }
            echo "</tr>";
            $i++;
         }
      }


      echo "</table>";
      echo "</div>";
   }

}

?>