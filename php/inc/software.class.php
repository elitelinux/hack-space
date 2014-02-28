<?php
/*
 * @version $Id: software.class.php 22666 2014-02-14 21:43:39Z ddurieux $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

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

/// Software class
class Software extends CommonDBTM {


   // From CommonDBTM
   public $dohistory                   = true;

   static protected $forward_entity_to = array('Infocom', 'ReservationItem', 'SoftwareVersion');


   static function getTypeName($nb=0) {
      return _n('Software', 'Software', $nb);
   }


   static function canCreate() {
      return Session::haveRight('software', 'w');
   }


   static function canView() {
      return Session::haveRight('software', 'r');
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case __CLASS__ :
               if ($item->isRecursive()
                   && $item->can($item->fields['id'],'w')) {
                  return __('Merging');
               }
               break;
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         $item->showMergeCandidates();
      }
      return true;
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab('SoftwareVersion', $ong, $options);
      $this->addStandardTab('SoftwareLicense', $ong, $options);
      $this->addStandardTab('Computer_SoftwareVersion', $ong, $options);
      $this->addStandardTab('Infocom', $ong, $options);
      $this->addStandardTab('Contract_Item', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Link', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      $this->addStandardTab('Reservation', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      $this->addStandardTab(__CLASS__, $ong, $options);

      return $ong;
   }


   function prepareInputForUpdate($input) {

      if (isset($input['is_update']) && !$input['is_update']) {
         $input['softwares_id'] = 0;
      }
      return $input;
   }


   function prepareInputForAdd($input) {

      if (isset($input['is_update']) && !$input['is_update']) {
         $input['softwares_id'] = 0;
      }

      if (isset($input["id"]) && ($input["id"] > 0)) {
         $input["_oldID"] = $input["id"];
      }
      unset($input['id']);
      unset($input['withtemplate']);

      //If category was not set by user (when manually adding a user)
      if (!isset($input["softwarecategories_id"]) || !$input["softwarecategories_id"]) {
         $softcatrule = new RuleSoftwareCategoryCollection();
         $result      = $softcatrule->processAllRules(null,null,Toolbox::stripslashes_deep($input));

         if (!empty($result) && isset($result["softwarecategories_id"])) {
            $input["softwarecategories_id"] = $result["softwarecategories_id"];
         } else {
            $input["softwarecategories_id"] = 0;
         }
      }
      return $input;
   }


   function post_addItem() {
      global $DB, $CFG_GLPI;

      // Manage add from template
      if (isset($this->input["_oldID"])) {
         // ADD Infocoms
         Infocom::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);

         // ADD Contract
         Contract_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);

         // ADD Documents
         Document_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
      }
   }


   function cleanDBonPurge() {
      global $DB;

      // Delete all licenses
      $query2 = "SELECT `id`
                 FROM `glpi_softwarelicenses`
                 WHERE `softwares_id` = '".$this->fields['id']."'";

      if ($result2 = $DB->query($query2)) {
         if ($DB->numrows($result2)) {
            $lic = new SoftwareLicense();
            while ($data = $DB->fetch_assoc($result2)) {
               $lic->delete(array("id" => $data["id"]));
            }
         }
      }

      $version = new SoftwareVersion();
      $version->cleanDBonItemDelete(__CLASS__, $this->fields['id']);

      $ip = new Item_Problem();
      $ip->cleanDBonItemDelete(__CLASS__, $this->fields['id']);      
   }


   /**
    * Print the Software form
    *
    * @param $ID        integer  ID of the item
    * @param $options   array    of possible options:
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    *@return boolean item found
   **/
   function showForm($ID, $options=array()) {

      $this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      $canedit = $this->can($ID,'w');

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "<td>" . __('Publisher')."</td><td>";
      Manufacturer::dropdown(array('value' => $this->fields["manufacturers_id"]));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Location') . "</td><td>";
      Location::dropdown(array('value'  => $this->fields["locations_id"],
                               'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "<td>" . __('Category') . "</td><td>";
      SoftwareCategory::dropdown(array('value' => $this->fields["softwarecategories_id"]));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Technician in charge of the hardware') . "</td><td>";
      User::dropdown(array('name'   => 'users_id_tech',
                           'value'  => $this->fields["users_id_tech"],
                           'right'  => 'own_ticket',
                           'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "<td>" . __('Associable to a ticket') . "</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible', $this->fields['is_helpdesk_visible']);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group in charge of the hardware')."</td>";
      echo "<td>";
      Group::dropdown(array('name'      => 'groups_id_tech',
                            'value'     => $this->fields['groups_id_tech'],
                            'entity'    => $this->fields['entities_id'],
                            'condition' => '`is_assign`'));
      echo "</td>";
      echo "<td rowspan='5' class='middle'>".__('Comments') . "</td>";
      echo "<td class='center middle' rowspan='5'>";
      echo "<textarea cols='45' rows='8' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td >" . __('User') . "</td>";
      echo "<td >";
      User::dropdown(array('value'  => $this->fields["users_id"],
                           'entity' => $this->fields["entities_id"],
                           'right'  => 'all'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Group') . "</td><td>";
      Group::dropdown(array('value'     => $this->fields["groups_id"],
                            'entity'    => $this->fields["entities_id"],
                            'condition' => '`is_itemgroup`'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      if ((!isset($options['withtemplate']) || ($options['withtemplate'] == 0))
          && !empty($this->fields['template_name'])) {
         echo "<span class='small_space'>";
         printf(__('Created from the template %s'), $this->fields['template_name']);
         echo "</span>";
      } else {
         echo "&nbsp;";
      }
      echo "</td><td>";
      if (isset($options['withtemplate']) && $options['withtemplate']) {
         //TRANS: %s is the datetime of insertion
         printf(__('Created on %s'), Html::convDateTime($_SESSION["glpi_currenttime"]));
      } else {
         //TRANS: %s is the datetime of insertion
         printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      }
      echo "</td></tr>\n";

      // UPDATE
      echo "<tr class='tab_bg_1'>";
      //TRANS: a noun, (ex : this software is an upgrade of..)
      echo "<td>" . __('Upgrade') . "</td><td>";
      Dropdown::showYesNo("is_update", $this->fields['is_update']);
      echo "&nbsp;" . __('from') . "&nbsp;";
      Software::dropdown(array('value' => $this->fields["softwares_id"]));
      echo "</td></tr>\n";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }


   function getEmpty() {
      global $CFG_GLPI;
      parent::getEmpty();

      $this->fields["is_helpdesk_visible"] = $CFG_GLPI["default_software_helpdesk_visible"];
   }


   /**
    * @see CommonDBTM::getSpecificMassiveActions()
   **/
   function getSpecificMassiveActions($checkitem=NULL) {

      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);
      if ($isadmin
          && (countElementsInTable("glpi_rules", "sub_type='RuleSoftwareCategory'") > 0)) {
         $actions['compute_software_category'] = __('Recalculate the category');
      }

      if (Session::haveRight("rule_dictionnary_software","w")
           && (countElementsInTable("glpi_rules", "sub_type='RuleDictionnarySoftware'") > 0)) {
         $actions['replay_dictionnary'] = __('Replay the dictionary rules');
      }

      if (Session::haveRight('transfer','r')
          && Session::isMultiEntitiesMode()
          && $isadmin) {
         $actions['add_transfer_list'] = _x('button', 'Add to transfer list');
      }
      return $actions;
   }


   /**
    * @see CommonDBTM::doSpecificMassiveActions()
   **/
   function doSpecificMassiveActions($input=array()) {

      $res = array('ok'      => 0,
                   'ko'      => 0,
                   'noright' => 0);

      switch ($input['action']) {
         case "mergesoftware":
            if (isset($input["id"])
                && isset($input["item"]) && is_array($input["item"]) && count($input["item"])) {

               if ($this->can($_POST["id"],'w')) {
                  if ($this->merge($_POST["item"])) {
                     $res['ok']++;
                  } else {
                     $res['ko']++;
                  }
               } else {
                  $res['noright']++;
               }
            } else {
               $res['ko']++;
            }
            break;

         case "compute_software_category" :
            $softcatrule = new RuleSoftwareCategoryCollection();
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  $params = array();
                  //Get software name and manufacturer
                  if ($this->can($key,'w')) {
                     $params["name"]             = $this->fields["name"];
                     $params["manufacturers_id"] = $this->fields["manufacturers_id"];
                     $params["comment"]          = $this->fields["comment"];
                     $output = array();
                     $output = $softcatrule->processAllRules(null, $output, $params);
                     //Process rules
                     if (isset($output['softwarecategories_id'])
                         && $this->update(array('id'       => $key,
                                                'softwarecategories_id'
                                                           => $output['softwarecategories_id']))) {
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

         case "replay_dictionnary" :
            $softdictionnayrule = new RuleDictionnarySoftwareCollection();
            $ids                = array();
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  if ($this->can($key,'w')) {
                     $ids[] = $key;
                  } else {
                     $res['noright']++;
                  }
               }
            }
            if ($softdictionnayrule->replayRulesOnExistingDB(0, 0, $ids)>0) {
               $res['ok'] += count($ids);
            } else {
               $res['ko'] += count($ids);
            }

            break;

         default :
            return parent::doSpecificMassiveActions($input);
      }
      return $res;
   }


   function getSearchOptions() {

      // Only use for History (not by search Engine)
      $tab                       = array();
      
      $tab['common']             = __('Characteristics');
      
      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['massiveaction']   = false;

      $tab[2]['table']           = $this->getTable();
      $tab[2]['field']           = 'id';
      $tab[2]['name']            = __('ID');
      $tab[2]['massiveaction']   = false;
      $tab[2]['datatype']        = 'number';

      $tab+=Location::getSearchOptionsToAdd();

      $tab[16]['table']          = $this->getTable();
      $tab[16]['field']          = 'comment';
      $tab[16]['name']           = __('Comments');
      $tab[16]['datatype']       = 'text';

      $tab[90]['table']          = $this->getTable();
      $tab[90]['field']          = 'notepad';
      $tab[90]['name']           = __('Notes');
      $tab[90]['massiveaction']  = false;
      $tab[90]['datatype']       = 'text';

      $tab[62]['table']          = 'glpi_softwarecategories';
      $tab[62]['field']          = 'name';
      $tab[62]['name']           = __('Category');
      $tab[62]['datatype']       = 'dropdown';

      $tab[19]['table']          = $this->getTable();
      $tab[19]['field']          = 'date_mod';
      $tab[19]['name']           = __('Last update');
      $tab[19]['datatype']       = 'datetime';
      $tab[19]['massiveaction']  = false;

      $tab[23]['table']          = 'glpi_manufacturers';
      $tab[23]['field']          = 'name';
      $tab[23]['name']           = __('Publisher');
      $tab[23]['datatype']       = 'dropdown';

      $tab[24]['table']          = 'glpi_users';
      $tab[24]['field']          = 'name';
      $tab[24]['linkfield']      = 'users_id_tech';
      $tab[24]['name']           = __('Technician in charge of the hardware');
      $tab[24]['datatype']       = 'dropdown';
      $tab[24]['right']          = 'own_ticket';

      $tab[49]['table']          = 'glpi_groups';
      $tab[49]['field']          = 'completename';
      $tab[49]['linkfield']      = 'groups_id_tech';
      $tab[49]['name']           = __('Group in charge of the hardware');
      $tab[49]['condition']      = '`is_assign`';
      $tab[49]['datatype']       = 'dropdown';

      $tab[70]['table']          = 'glpi_users';
      $tab[70]['field']          = 'name';
      $tab[70]['name']           = __('User');
      $tab[70]['datatype']       = 'dropdown';
      $tab[70]['right']          = 'all';

      $tab[71]['table']          = 'glpi_groups';
      $tab[71]['field']          = 'completename';
      $tab[71]['name']           = __('Group');
      $tab[71]['condition']      = '`is_itemgroup`';
      $tab[71]['datatype']       = 'dropdown';

      $tab[61]['table']          = $this->getTable();
      $tab[61]['field']          = 'is_helpdesk_visible';
      $tab[61]['name']           = __('Associable to a ticket');
      $tab[61]['datatype']       = 'bool';

      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['massiveaction']  = false;
      $tab[80]['datatype']       = 'dropdown';

      $tab[72]['table']          = 'glpi_computers_softwareversions';
      $tab[72]['field']          = 'count';
      $tab[72]['name']           = __('Number of installations');
      $tab[72]['forcegroupby']   = true;
      $tab[72]['usehaving']      = true;
      $tab[72]['datatype']       = 'number';
      $tab[72]['nometa']         = true;
      $tab[72]['massiveaction']  = false;
      if (Session::getLoginUserID()) {
         $tab[72]['joinparams']  = array('jointype'   => 'child',
                                         'condition'  => "AND NEWTABLE.`is_deleted_computer` = '0'
                                                          AND NEWTABLE.`is_deleted` = '0'
                                                          AND NEWTABLE.`is_template_computer` = '0'
                                                          ".getEntitiesRestrictRequest('AND', 'NEWTABLE'),
                                         'beforejoin' => array('table' => 'glpi_softwareversions',
                                                               'joinparams'
                                                                       => array('jointype'
                                                                                 => 'child')));
      }

      $tab[86]['table']          = $this->getTable();
      $tab[86]['field']          = 'is_recursive';
      $tab[86]['name']           = __('Child entities');
      $tab[86]['datatype']       = 'bool';
      $tab[86]['massiveaction']  = false;

      $tab['versions']           = _n('Version', 'Versions',2);

      $tab[5]['table']           = 'glpi_softwareversions';
      $tab[5]['field']           = 'name';
      $tab[5]['name']            = __('Version name');
      $tab[5]['forcegroupby']    = true;
      $tab[5]['massiveaction']   = false;
      $tab[5]['joinparams']      = array('jointype' => 'child');
      $tab[5]['datatype']       = 'dropdown';

      $tab[31]['table']          = 'glpi_states';
      $tab[31]['field']          = 'completename';
      $tab[31]['name']           = __('Status');
      $tab[31]['datatype']       = 'dropdown';
      $tab[31]['forcegroupby']   = true;
      $tab[31]['massiveaction']  = false;
      $tab[31]['joinparams']     = array('beforejoin'
                                          => array('table'      => 'glpi_softwareversions',
                                                   'joinparams' => array('jointype' => 'child')));

      $tab[170]['table']         = 'glpi_softwareversions';
      $tab[170]['field']         = 'comment';
      $tab[170]['name']          = __('Version comments');
      $tab[170]['forcegroupby']  = true;
      $tab[170]['datatype']      = 'text';
      $tab[170]['massiveaction'] = false;
      $tab[170]['joinparams']    = array('jointype' => 'child');

      $tab[4]['table']           = 'glpi_operatingsystems';
      $tab[4]['field']           = 'name';
      $tab[4]['datatype']        = 'dropdown';
      $tab[4]['name']            = __('Operating system');
      $tab[4]['forcegroupby']    = true;
      $tab[4]['joinparams']      = array('beforejoin'
                                          => array('table'      => 'glpi_softwareversions',
                                                   'joinparams' => array('jointype' => 'child')));

      $tab['license']            = _n('License', 'Licenses', 2);

      $licjoin       = array();
      $licjoinexpire = array();

      if (!Session::isCron()) { // no filter for cron
         $licjoin       = array('jointype'  => 'child',
                                'condition' => getEntitiesRestrictRequest(' AND', "NEWTABLE",
                                                                          '', '', true));

         $licjoinexpire = array('jointype'  => 'child',
                                'condition' => getEntitiesRestrictRequest(' AND', "NEWTABLE",
                                                                          '', '', true).
                                                " AND (NEWTABLE.`expire` IS NULL
                                                   OR NEWTABLE.`expire` > NOW())");
      }
      
      $tab[160]['table']         = 'glpi_softwarelicenses';
      $tab[160]['field']         = 'name';
      $tab[160]['name']          = __('License name');
      $tab[160]['datatype']      = 'dropdown';
      $tab[160]['forcegroupby']  = true;
      $tab[160]['massiveaction'] = false;
      $tab[160]['joinparams']    = $licjoinexpire;

      $tab[161]['table']         = 'glpi_softwarelicenses';
      $tab[161]['field']         = 'serial';
      $tab[161]['datatype']      = 'string';
      $tab[161]['name']          = __('License serial number');
      $tab[161]['forcegroupby']  = true;
      $tab[161]['massiveaction'] = false;
      $tab[161]['joinparams']    = $licjoinexpire;

      $tab[162]['table']         = 'glpi_softwarelicenses';
      $tab[162]['field']         = 'otherserial';
      $tab[162]['datatype']      = 'string';
      $tab[162]['name']          = __('License inventory number');
      $tab[162]['forcegroupby']  = true;
      $tab[162]['massiveaction'] = false;
      $tab[162]['joinparams']    = $licjoinexpire;

      $tab[163]['table']         = 'glpi_softwarelicenses';
      $tab[163]['field']         = 'number';
      $tab[163]['name']          = __('Number of licenses');
      $tab[163]['forcegroupby']  = true;
      $tab[163]['usehaving']     = true;
      $tab[163]['datatype']      = 'number';
      $tab[163]['massiveaction'] = false;
      $tab[163]['joinparams']    = $licjoinexpire;

      $tab[164]['table']         = 'glpi_softwarelicensetypes';
      $tab[164]['field']         = 'name';
      $tab[164]['datatype']      = 'dropdown';
      $tab[164]['name']          = __('License types');
      $tab[164]['forcegroupby']  = true;
      $tab[164]['massiveaction'] = false;
      $tab[164]['joinparams']    = array('beforejoin'
                                           => array('table'      => 'glpi_softwarelicenses',
                                                    'joinparams' => $licjoinexpire));

      $tab[165]['table']         = 'glpi_softwarelicenses';
      $tab[165]['field']         = 'comment';
      $tab[165]['name']          = __('License comments');
      $tab[165]['forcegroupby']  = true;
      $tab[165]['datatype']      = 'text';
      $tab[165]['massiveaction'] = false;
      $tab[165]['joinparams']    = $licjoinexpire;

      $tab[166]['table']         = 'glpi_softwarelicenses';
      $tab[166]['field']         =  'expire';
      $tab[166]['name']          = __('Expiration');
      $tab[166]['forcegroupby']  = true;
      $tab[166]['datatype']      = 'date';
      $tab[166]['massiveaction'] = false;
      $tab[166]['joinparams']    = $licjoinexpire;

      return $tab;
   }


   /**
    * Make a select box for  software to install
    *
    * @param $myname          select name
    * @param $entity_restrict restrict to a defined entity
    * @param $massiveaction   is it a massiveaction select ? (default 0)
    *
    * @return nothing (print out an HTML select box)
   **/
   static function dropdownSoftwareToInstall($myname, $entity_restrict, $massiveaction=0) {
      global $CFG_GLPI;

      $rand     = mt_rand();
      $use_ajax = false;

      if ($CFG_GLPI["use_ajax"]) {
         if (countElementsInTableForEntity("glpi_softwares", $entity_restrict)
               > $CFG_GLPI["ajax_limit_count"]) {
            $use_ajax = true;
         }
      }

      $params = array('searchText'      => '__VALUE__',
                      'myname'          => $myname,
                      'entity_restrict' => $entity_restrict);

      $default = "<select name='$myname'><option value='0'>".Dropdown::EMPTY_VALUE." </option>
                  </select>";
      Ajax::dropdown($use_ajax, "/ajax/dropdownSelectSoftware.php", $params, $default, $rand);

      return $rand;
   }


   /**
    * Make a select box for license software to associate
    *
    * @param $myname          select name
    * @param $entity_restrict restrict to a defined entity
    * @param $massiveaction   is it a massiveaction select ? (default 0)
    *
    * @return nothing (print out an HTML select box)
   **/
   static function dropdownLicenseToInstall($myname, $entity_restrict, $massiveaction=0) {
      global $CFG_GLPI;

      $rand     = mt_rand();
      $use_ajax = false;

      if ($CFG_GLPI["use_ajax"]) {
         if (countElementsInTableForEntity("glpi_softwarelicenses", $entity_restrict)
               > $CFG_GLPI["ajax_limit_count"]) {
            $use_ajax = true;
         }
      }

      $params = array('searchText'      => '__VALUE__',
                      'myname'          => $myname,
                      'entity_restrict' => $entity_restrict);

      $default = "<select name='$myname'><option value='0'>".Dropdown::EMPTY_VALUE." </option>
                  </select>";
      Ajax::dropdown($use_ajax, "/ajax/dropdownSelectSoftwareLicense.php", $params, $default,
                     $rand);

      return $rand;
   }


   /**
    * Create a new software
    *
    * @param name                          the software's name (need to be addslashes)
    * @param manufacturer_id               id of the software's manufacturer
    * @param entity                        the entity in which the software must be added
    * @param comment                       (default '')
    * @param is_recursive         boolean  must the software be recursive (false by default)
    * @param is_helpdesk_visible           show in helpdesk, default : from config (false by default)
    *
    * @return the software's ID
   **/
   function addSoftware($name, $manufacturer_id, $entity, $comment='',
                        $is_recursive=false, $is_helpdesk_visible=NULL) {
      global $DB, $CFG_GLPI;

      $input["name"]                = $name;
      $input["manufacturers_id"]    = $manufacturer_id;
      $input["entities_id"]         = $entity;
      $input["is_recursive"]        = ($is_recursive ? 1 : 0);
      // No comment
      if (is_null($is_helpdesk_visible)) {
         $input["is_helpdesk_visible"] = $CFG_GLPI["default_software_helpdesk_visible"];
      } else {
         $input["is_helpdesk_visible"] = $is_helpdesk_visible;
      }

      //Process software's category rules
      $softcatrule = new RuleSoftwareCategoryCollection();
      $result      = $softcatrule->processAllRules(null, null, Toolbox::stripslashes_deep($input));

      if (!empty($result) && isset($result["softwarecategories_id"])) {
         $input["softwarecategories_id"] = $result["softwarecategories_id"];
      } else {
         $input["softwarecategories_id"] = 0;
      }

      return $this->add($input);
   }


   /**
    * Add a software. If already exist in dustbin restore it
    *
    * @param name                            the software's name
    * @param manufacturer                    the software's manufacturer
    * @param entity                          the entity in which the software must be added
    * @param comment                         comment (default '')
    * @param is_recursive           boolean  must the software be recursive (false by default)
    * @param is_helpdesk_visible             show in helpdesk, default = config value (false by default)
   */
   function addOrRestoreFromTrash($name, $manufacturer, $entity, $comment='',
                                  $is_recursive=false, $is_helpdesk_visible=NULL) {
      global $DB;

      //Look for the software by his name in GLPI for a specific entity
      $manufacturer_id = 0;
      if ($manufacturer != '') {
         $manufacturer_id = Dropdown::import('Manufacturer', array('name' => $manufacturer));
      }

      $query_search = "SELECT `glpi_softwares`.`id`, `glpi_softwares`.`is_deleted`
                       FROM `glpi_softwares`
                       WHERE `name` = '$name'
                             AND `manufacturers_id` = '$manufacturer_id'
                             AND `is_template` = '0' ".
                             getEntitiesRestrictRequest('AND', 'glpi_softwares',
                                                        'entities_id', $entity,true);

      $result_search = $DB->query($query_search);

      if ($DB->numrows($result_search) > 0) {
         //Software already exists for this entity, get his ID
         $data = $DB->fetch_assoc($result_search);
         $ID   = $data["id"];

         // restore software
         if ($data['is_deleted']) {
            $this->removeFromTrash($ID);
         }

      } else {
         $ID = 0;
      }

      if (!$ID) {
         $ID = $this->addSoftware($name, $manufacturer_id, $entity, $comment, $is_recursive,
                                  $is_helpdesk_visible);
      }
      return $ID;
   }


   /**
    * Put software in dustbin because it's been removed by GLPI software dictionnary
    *
    * @param $ID        the ID of the software to put in dustbin
    * @param $comment   the comment to add to the already existing software's comment (default '')
    *
    * @return boolean (success)
   **/
   function putInTrash($ID, $comment='') {
      global $CFG_GLPI;

      $this->getFromDB($ID);
      $input["id"]         = $ID;
      $input["is_deleted"] = 1;

      //change category of the software on deletion (if defined in glpi_configs)
      if (isset($CFG_GLPI["softwarecategories_id_ondelete"])
          && ($CFG_GLPI["softwarecategories_id_ondelete"] != 0)) {

         $input["softwarecategories_id"] = $CFG_GLPI["softwarecategories_id_ondelete"];
      }

      //Add dictionnary comment to the current comment
      $input["comment"] = (($this->fields["comment"] != '') ? "\n" : '') . $comment;

      return $this->update($input);
   }


   /**
    * Restore a software from dustbin
    *
    * @param $ID  the ID of the software to put in dustbin
    *
    * @return boolean (success)
   **/
   function removeFromTrash($ID) {

      $res         = $this->restore(array("id" => $ID));
      $softcatrule = new RuleSoftwareCategoryCollection();
      $result      = $softcatrule->processAllRules(null, null, $this->fields);

      if (!empty($result)
          && isset($result['softwarecategories_id'])
          && ($result['softwarecategories_id'] != $this->fields['softwarecategories_id'])) {

         $this->update(array('id'                    => $ID,
                             'softwarecategories_id' => $result['softwarecategories_id']));
      }

      return $res;
   }


   /**
    * Show softwares candidates to be merged with the current
    *
    * @return nothing
   **/
   function showMergeCandidates() {
      global $DB, $CFG_GLPI;

      $ID   = $this->getField('id');
      $this->check($ID,"w");
      $rand = mt_rand();

      echo "<div class='center'>";
      $sql = "SELECT `glpi_softwares`.`id`,
                     `glpi_softwares`.`name`,
                     `glpi_entities`.`completename` AS entity
              FROM `glpi_softwares`
              LEFT JOIN `glpi_entities` ON (`glpi_softwares`.`entities_id` = `glpi_entities`.`id`)
              WHERE (`glpi_softwares`.`id` != '$ID'
                     AND `glpi_softwares`.`name` = '".addslashes($this->fields["name"])."'
                     AND `glpi_softwares`.`is_deleted` = '0'
                     AND `glpi_softwares`.`is_template` = '0' " .
                         getEntitiesRestrictRequest('AND', 'glpi_softwares','entities_id',
                                                    getSonsOf("glpi_entities",
                                                              $this->fields["entities_id"]),
                                                              false).")
              ORDER BY `entity`";
      $req = $DB->request($sql);

      if ($nb = $req->numrows()) {
         $link = Toolbox::getItemTypeFormURL('Software');
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $paramsma = array('num_displayed'    => $nb,
                           'specific_actions' => array('mergesoftware' => __('Merge')) );
         Html::showMassiveActions(__CLASS__, $paramsma);

         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr><th width='10'>";
         echo Html::checkAllAsCheckbox('mass'.__CLASS__.$rand);
         echo "</th>";
         echo "<th>".__('Name')."</th>";
         echo "<th>".__('Entity')."</th>";
         echo "<th>"._n('Installation', 'Installations', 2)."</th>";
         echo "<th>"._n('License', 'Licenses', 2)."</th></tr>";

         foreach ($req as $data) {
            echo "<tr class='tab_bg_2'>";
            echo "<td>".Html::getMassiveActionCheckBox(__CLASS__, $data["id"])."</td>";
            echo "<td><a href='".$link."?id=".$data["id"]."'>".$data["name"]."</a></td>";
            echo "<td>".$data["entity"]."</td>";
            echo "<td class='right'>".Computer_SoftwareVersion::countForSoftware($data["id"])."</td>";
            echo "<td class='right'>".SoftwareLicense::countForSoftware($data["id"])."</td></tr>\n";
         }
         echo "</table>\n";
         echo "<input type='hidden' name='id' value='$ID'>";
         $paramsma['ontop'] =false;
         Html::showMassiveActions(__CLASS__, $paramsma);
         Html::closeForm();

      } else {
         _e('No item found');
      }

      echo "</div>";
   }


   /**
    * Merge softwares with current
    *
    * @param $item array of software ID to be merged
    *
    * @return boolean about success
   **/
   function merge($item) {
      global $DB;

      $ID = $this->getField('id');

      echo "<div class='center'>";
      echo "<table class='tab_cadrehov'><tr><th>".__('Merging')."</th></tr>";
      echo "<tr class='tab_bg_2'><td>";
      Html::createProgressBar(__('Work in progress...'));
      echo "</td></tr></table></div>\n";

      $item = array_keys($item);

      // Search for software version
      $req = $DB->request("glpi_softwareversions", array("softwares_id" => $item));
      $i   = 0;

      if ($nb = $req->numrows()) {
         foreach ($req as $from) {
            $found = false;

            foreach ($DB->request("glpi_softwareversions",
                                  array("softwares_id" => $ID,
                                        "name"         => $from["name"])) as $dest) {
               // Update version ID on License
               $sql = "UPDATE `glpi_softwarelicenses`
                       SET `softwareversions_id_buy` = '".$dest["id"]."'
                       WHERE `softwareversions_id_buy` = '".$from["id"]."'";
               $DB->query($sql);

               $sql = "UPDATE `glpi_softwarelicenses`
                       SET `softwareversions_id_use` = '".$dest["id"]."'
                       WHERE `softwareversions_id_use` = '".$from["id"]."'";
               $DB->query($sql);

               // Move installation to existing version in destination software
               $sql = "UPDATE `glpi_computers_softwareversions`
                       SET `softwareversions_id` = '".$dest["id"]."'
                       WHERE `softwareversions_id` = '".$from["id"]."'";
               $found = $DB->query($sql);
            }

            if ($found) {
               // Installation has be moved, delete the source version
               $sql = "DELETE
                       FROM `glpi_softwareversions`
                       WHERE `id` = '".$from["id"]."'";

            } else {
               // Move version to destination software
               $sql = "UPDATE `glpi_softwareversions`
                       SET `softwares_id` = '$ID',
                           `entities_id` = '".$this->getField('entities_id')."'
                       WHERE `id` = '".$from["id"]."'";
            }

            if ($DB->query($sql)) {
               $i++;
            }
            Html::changeProgressBarPosition($i, $nb+1);
         }
      }

      // Move software license
      $sql = "UPDATE `glpi_softwarelicenses`
              SET `softwares_id` = '$ID'
              WHERE `softwares_id` IN ('".implode("','",$item)."')";

      if ($DB->query($sql)) {
         $i++;
      }

      if ($i == ($nb+1)) {
         //error_log ("All merge operations ok.");
         $soft = new self();
         foreach ($item as $old) {
            $soft->putInTrash($old, __('Software deleted after merging'));
         }
      }
      Html::changeProgressBarPosition($i, $nb+1, __('Task completed.'));
      return $i == ($nb+1);
   }


}
?>
