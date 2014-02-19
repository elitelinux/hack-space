<?php
/*
 * @version $Id: commondropdown.class.php 20696 2013-04-09 14:39:41Z moyo $
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

/// CommonDropdown class - generic dropdown
abstract class CommonDropdown extends CommonDBTM {

   // For delete operation (entity will overload this value)
   public $must_be_replace = false;

   //Indicates if only the dropdown or the whole page is refreshed when a new dropdown value
   //is added using popup window
   public $refresh_page = false;

   //Menu & navigation
   public $first_level_menu  = "config";
   public $second_level_menu = "dropdowns";
   public $third_level_menu  = "";

   public $display_dropdowntitle  = true;


   /**
    * Return Additional Fields for this type
   **/
   function getAdditionalFields() {
      return array();
   }


   function defineTabs($options=array()) {

      $ong = array();
      if ($this->dohistory) {
         $this->addStandardTab('Log',$ong, $options);
      }

      return $ong;
   }


   /**
    * Have I the right to "create" the Object
    *
    * MUST be overloaded for entity_dropdown
    *
    * @return booleen
   **/
   static function canCreate() {
      return Session::haveRight('dropdown', 'w');
   }


   /**
    * Have I the right to "view" the Object
    *
    * MUST be overloaded for entity_dropdown
    *
    * @return booleen
   **/
   static function canView() {
      return Session::haveRight('dropdown', 'r');
   }


   /**
    * Display title above search engine
    *
    * @return nothing (HTML display if needed)
   **/
   function title() {

      if ($this->display_dropdowntitle) {
         Dropdown::showItemTypeMenu(_n('Dropdown', 'Dropdowns', 2),
                                    Dropdown::getStandardDropdownItemTypes(),
                                    $this->getSearchURL());
      }
   }


   function displayHeader() {

      if (empty($this->third_level_menu)) {
        $this->third_level_menu = $this->getType();
      }
      Html::header($this->getTypeName(2), '', $this->first_level_menu, $this->second_level_menu,
                   $this->third_level_menu);
   }


   /**
    * @since version 0.83.3
    *
    * @see CommonDBTM::prepareInputForAdd()
   **/
   function prepareInputForAdd($input) {

      if (isset($input['name'])) {
         // leading/ending space will break findID/import
         $input['name'] = trim($input['name']);
      }
      return $input;
   }


   /**
    * @since version 0.83.3
    *
    * @see CommonDBTM::prepareInputForUpdate()
   **/
   function prepareInputForUpdate($input) {
      return self::prepareInputForAdd($input);
   }


   function showForm($ID, $options=array()) {
      global $CFG_GLPI;

      if (!$this->isNewID($ID)) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }
      $this->showTabs($options);
      $this->showFormHeader($options);

      $fields = $this->getAdditionalFields();
      $nb     = count($fields);

      echo "<tr class='tab_bg_1'><td>".__('Name')."</td>";
      echo "<td>";
///    TODO MoYo : Why add this field ?
//    echo "<input type='hidden' name='itemtype' value='".$this->getType()."'>";
      if ($this instanceof CommonDevice) {
         // Awfull hack for CommonDevice where name is designation
         Html::autocompletionTextField($this, "designation");
      } else {
         Html::autocompletionTextField($this, "name");
      }
      echo "</td>";

      echo "<td rowspan='".($nb+1)."'>". __('Comments')."</td>";
      echo "<td rowspan='".($nb+1)."'>
            <textarea cols='45' rows='".($nb+2)."' name='comment' >".$this->fields["comment"];
      echo "</textarea></td></tr>\n";

      foreach ($fields as $field) {
         if (($field['name'] == 'entities_id')
             && ($ID == 0)) {
            // No display for root entity
            echo "<tr class='tab_bg_1'><td colspan='2'>&nbsp;</td></tr>";
            break;
         }


         echo "<tr class='tab_bg_1'><td>".$field['label'];
         if (isset($field['comment']) && !empty($field['comment'])) {
            echo "&nbsp;";
            Html::showToolTip($field['comment']);
         }
         echo "</td><td>";
         if (!isset($field['type'])) {
            $field['type'] = '';
         }
         switch ($field['type']) {
            case 'UserDropdown' :
               $param = array('name'   => $field['name'],
                              'value'  => $this->fields[$field['name']],
                              'right'  => 'interface',
                              'entity' => $this->fields["entities_id"]);
               if (isset($field['right'])) {
                  $params['right'] = $field['right'];
               }
               User::dropdown($param);

               break;

            case 'dropdownValue' :
               $params = array('value'  => $this->fields[$field['name']],
                               'name'   => $field['name'],
                               'entity' => $this->getEntityID());
               if (isset($field['condition'])) {
                  $params['condition'] = $field['condition'];
               }
               Dropdown::show(getItemTypeForTable(getTableNameForForeignKeyField($field['name'])),
                              $params);
               break;

            case 'text' :
               Html::autocompletionTextField($this, $field['name']);
               break;

            case 'textarea' :
               echo "<textarea name='".$field['name']."' cols='40' rows='3'>".
                     $this->fields[$field['name']]."</textarea >";
               break;

            case 'integer' :
               Dropdown::showInteger($field['name'], $this->fields[$field['name']]);
               break;

            case 'timestamp' :
               $param = array('value' => $this->fields[$field['name']]);
               if (isset($field['min'])) {
                  $param['min'] = $field['min'];
               }
               if (isset($field['max'])) {
                  $param['max'] = $field['max'];
               }
               if (isset($field['step'])) {
                  $param['step'] = $field['step'];
               }
               Dropdown::showTimeStamp($field['name'], $param);
               break;

            case 'parent' :
               if ($field['name'] == 'entities_id') {
                  $restrict = -1;
               } else {
                  $restrict = $this->getEntityID();
               }
               Dropdown::show(getItemTypeForTable($this->getTable()),
                              array('value'  => $this->fields[$field['name']],
                                    'name'   => $field['name'],
                                    'entity' => $restrict,
                                    'used'   => ($ID>0 ? getSonsOf($this->getTable(), $ID)
                                                       : array())));
               break;

            case 'icon' :
               Dropdown::dropdownIcons($field['name'], $this->fields[$field['name']],
                                       GLPI_ROOT."/pics/icones");
               if (!empty($this->fields[$field['name']])) {
                  echo "&nbsp;<img style='vertical-align:middle;' alt='' src='".
                       $CFG_GLPI["typedoc_icon_dir"]."/".$this->fields[$field['name']]."'>";
               }
               break;

            case 'bool' :
               Dropdown::showYesNo($field['name'], $this->fields[$field['name']]);
               break;

            case 'date' :
               Html::showDateFormItem($field['name'], $this->fields[$field['name']]);
               break;

            case 'datetime' :
               Html::showDateTimeFormItem($field['name'], $this->fields[$field['name']]);
               break;

            case 'password':
               echo "<input type='password' name='password' value='' size='20' autocomplete='off'>";
               break;

            default:
               $this->displaySpecificTypeField($ID, $field);
               break;
         }
         if (isset($field['unit'])) {
            echo "&nbsp;".$field['unit'];
         }

         echo "</td></tr>\n";
      }

      if (isset($this->fields['is_protected']) && $this->fields['is_protected']) {
         $options['candel'] = false;
      }
      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }


   function displaySpecificTypeField($ID, $field=array()) {
   }


   function pre_deleteItem() {

      if (isset($this->fields['is_protected']) && $this->fields['is_protected']) {
         return false;
      }
      return true;
   }


   /**
    * @see CommonDBTM::getSpecificMassiveActions()
    **/
   function getSpecificMassiveActions($checkitem=NULL) {

      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($isadmin
          &&  $this->maybeRecursive()
          && (count($_SESSION['glpiactiveentities']) > 1)) {
         $actions['merge'] = __('Transfer and merge');
      }

      return $actions;
   }


   /**
    * @see CommonDBTM::showSpecificMassiveActionsParameters()
    **/
   function showSpecificMassiveActionsParameters($input=array()) {

      switch ($input['action']) {
         case 'merge' :
            echo "&nbsp;".$_SESSION['glpiactive_entity_shortname'];
            echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                           _sx('button', 'Merge')."'>\n";
            return true;

         default :
            return parent::showSpecificMassiveActionsParameters($input);
      }
      return false;
   }


   /**
    * @see CommonDBTM::doSpecificMassiveActions()
    **/
   function doSpecificMassiveActions($input=array()) {

      $res = array('ok'      => 0,
                   'ko'      => 0,
                   'noright' => 0);

      switch ($input['action']) {
         case 'merge' :
            $fk = $this->getForeignKeyField();
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  if ($this->can($key,'w')) {
                     if ($this->getEntityID() == $_SESSION['glpiactive_entity']) {
                        if ($this->update(array('id'           => $key,
                                                'is_recursive' => 1))) {
                           $res['ok']++;
                        } else {
                           $res['ko']++;
                        }
                     } else {
                        $input2 = $this->fields;

                        // Remove keys (and name, tree dropdown will use completename)
                        if ($this instanceof CommonTreeDropdown) {
                           unset($input2['id'], $input2['name'], $input2[$fk]);
                        } else {
                           unset($input2['id']);
                        }
                        // Change entity
                        $input2['entities_id']  = $_SESSION['glpiactive_entity'];
                        $input2['is_recursive'] = 1;
                        $input2 = Toolbox::addslashes_deep($input2);
                        // Import new
                        if ($newid = $this->import($input2)) {

                           // Delete old
                           if ($newid > 0) {
                              // delete with purge for dropdown with dustbin (Budget)
                              $this->delete(array('id'        => $key,
                                                '_replace_by' => $newid), 1);
                           }
                           $res['ok']++;
                        } else {
                           $res['ko']++;
                        }
                     }
                  } else {
                     $res['noright']++;
                  }
               }
            }
            break;

         default :
            return parent::doSpecificMassiveActions($input);
      }
      return $res;
   }


   /**
    * Get search function for the class
    *
    * @return array of search option
   **/
   function getSearchOptions() {

      $tab = array();
      $tab['common']               = __('Characteristics');

      $tab[1]['table']             = $this->getTable();
      $tab[1]['field']             = 'name';
      $tab[1]['name']              = __('Name');
      $tab[1]['datatype']          = 'itemlink';
      $tab[1]['massiveaction']     = false;

      $tab[2]['table']             = $this->getTable();
      $tab[2]['field']             = 'id';
      $tab[2]['name']              = __('ID');
      $tab[2]['massiveaction']     = false;
      $tab[2]['datatype']          = 'number';

      $tab[16]['table']            = $this->getTable();
      $tab[16]['field']            = 'comment';
      $tab[16]['name']             = __('Comments');
      $tab[16]['datatype']         = 'text';

      if ($this->isEntityAssign()) {
         $tab[80]['table']         = 'glpi_entities';
         $tab[80]['field']         = 'completename';
         $tab[80]['name']          = __('Entity');
         $tab[80]['massiveaction'] = false;
         $tab[80]['datatype']      = 'dropdown';
      }

      if ($this->maybeRecursive()) {
         $tab[86]['table']         = $this->getTable();
         $tab[86]['field']         = 'is_recursive';
         $tab[86]['name']          = __('Child entities');
         $tab[86]['datatype']      = 'bool';
      }

      if ($this->isField('date_mod')) {
         $tab[19]['table']         = $this->getTable();
         $tab[19]['field']         = 'date_mod';
         $tab[19]['name']          = __('Last update');
         $tab[19]['datatype']      = 'datetime';
         $tab[19]['massiveaction'] = false;
      }

      return $tab;
   }


   /** Check if the dropdown $ID is used into item tables
    *
    * @return boolean : is the value used ?
   **/
   function isUsed() {
      global $DB;

      $ID = $this->fields['id'];

      $RELATION = getDbRelations();
      if (isset($RELATION[$this->getTable()])) {
         foreach ($RELATION[$this->getTable()] as $tablename => $field) {
            if ($tablename[0] != '_') {
               if (!is_array($field)) {
                  $query = "SELECT COUNT(*) AS cpt
                            FROM `$tablename`
                            WHERE `$field` = '$ID'";
                  $result = $DB->query($query);
                  if ($DB->result($result, 0, "cpt") > 0) {
                     return true;
                  }

               } else {
                  foreach ($field as $f) {
                     $query = "SELECT COUNT(*) AS cpt
                               FROM `$tablename`
                               WHERE `$f` = '$ID'";
                     $result = $DB->query($query);
                     if ($DB->result($result, 0, "cpt") > 0) {
                        return true;
                     }
                  }
               }
            }
         }
      }
      return false;
   }


   /**
    * Report if a dropdown have Child
    * Used to (dis)allow delete action
   **/
   function haveChildren() {
      return false;
   }


   /**
    * Show a dialog to Confirm delete action
    * And propose a value to replace
    *
    * @param $target string URL
   **/
   function showDeleteConfirmForm($target) {

      if ($this->haveChildren()) {
         echo "<div class='center'><p class='red'>" .
               __("You can't delete that item, because it has sub-items") . "</p></div>";
         return false;
      }

      $ID = $this->fields['id'];

      echo "<div class='center'><p class='red'>";
      _e("Caution: you're about to remove a heading used for one or more items.");
      echo "</p>";

      if (!$this->must_be_replace) {
         // Delete form (set to 0)
         echo "<p>".__('If you confirm the deletion, all uses of this dropdown will be blanked.') .
              "</p>";
         echo "<form action='$target' method='post'>";
         echo "<table class='tab_cadre'><tr>";
         echo "<td><input type='hidden' name='id' value='$ID'>";
         echo "<input type='hidden' name='forcedelete' value='1'>";
         echo "<input class='submit' type='submit' name='delete'
                value=\""._sx('button','Confirm')."\">";
         echo "</td>";
         echo "<td><input class='submit' type='submit' name='annuler'
                    value=\""._sx('button','Cancel')."\">";
         echo "</td></tr></table>\n";
         Html::closeForm();
      }

      // Replace form (set to new value)
      echo "<p>". __('You can also replace all uses of this dropdown by another.') ."</p>";
      echo "<form action='$target' method='post'>";
      echo "<table class='tab_cadre'><tr><td>";

      if ($this instanceof CommonTreeDropdown) {
         // TreeDropdown => default replacement is parent
         $fk = $this->getForeignKeyField();
         Dropdown::show(getItemTypeForTable($this->getTable()),
                        array('name'   => '_replace_by',
                              'value'  => $this->fields[$fk],
                              'entity' => $this->getEntityID(),
                              'used'   => getSonsOf($this->getTable(), $ID)));

      } else {
         Dropdown::show(getItemTypeForTable($this->getTable()),
                        array('name'   => '_replace_by',
                              'entity' => $this->getEntityID(),
                              'used'   => array($ID)));
      }
      echo "<input type='hidden' name='id' value='$ID'/>";
      echo "</td><td>";
      echo "<input class='submit' type='submit' name='replace' value=\""._sx('button','Replace')."\">";
      echo "</td><td>";
      echo "<input class='submit' type='submit' name='annuler' value=\""._sx('button','Cancel')."\">";
      echo "</td></tr></table>\n";
      Html::closeForm();
      echo "</div>";
   }


   /**
    * check if a dropdown already exists (before import)
    *
    * @param &$input  array of value to import (name)
    *
    * @return the ID of the new (or -1 if not found)
   **/
   function findID(array &$input) {
      global $DB;

      if (!empty($input["name"])) {
         $query = "SELECT `id`
                   FROM `".$this->getTable()."`
                   WHERE `name` = '".$input["name"]."'";

         if ($this->isEntityAssign()) {
            $query .= getEntitiesRestrictRequest(' AND ', $this->getTable(), '',
                                                 $input['entities_id'], $this->maybeRecursive());
         }
         $query .= " LIMIT 1";

         // Check twin :
         if ($result_twin = $DB->query($query) ) {
            if ($DB->numrows($result_twin) > 0) {
               return $DB->result($result_twin, 0, "id");
            }
         }
      }
      return -1;
   }


   /**
    * Import a dropdown - check if already exists
    *
    * @param $input  array of value to import (name, ...)
    *
    * @return the ID of the new or existing dropdown
   **/
   function import(array $input) {

      if (!isset($input['name'])) {
         return -1;
      }
      // Clean datas
      $input['name'] = trim($input['name']);

      if (empty($input['name'])) {
         return -1;
      }

      // Check twin :
      if ($ID = $this->findID($input)) {
         if ($ID > 0) {
            return $ID;
         }
      }

      return $this->add($input);
   }


   /**
    * Import a value in a dropdown table.
    *
    * This import a new dropdown if it doesn't exist - Play dictionnary if needed
    *
    * @param $value           string   Value of the new dropdown (need to be addslashes)
    * @param $entities_id     int      entity in case of specific dropdown (default -1)
    * @param $external_params array    (manufacturer) (need to be addslashes)
    * @param $comment                  (default '') (need to be addslashes)
    * @param $add                      if true, add it if not found. if false,
    *                                  just check if exists (true by default)
    *
    * @return integer : dropdown id.
   **/
   function importExternal($value, $entities_id=-1, $external_params=array(), $comment="",
                           $add=true) {

      $value = trim($value);
      if (strlen($value) == 0) {
         return 0;
      }

      $ruleinput      = array("name" => stripslashes($value));
      $rulecollection = RuleCollection::getClassByType($this->getType(),true);

      foreach ($this->additional_fields_for_dictionnary as $field) {
         if (isset($external_params[$field])) {
            $ruleinput[$field] = $external_params[$field];
         } else {
            $ruleinput[$field] = '';
         }
      }
      /*
      switch ($this->getTable()) {
         case "glpi_computermodels" :
         case "glpi_monitormodels" :
         case "glpi_printermodels" :
         case "glpi_peripheralmodels" :
         case "glpi_phonemodels" :
         case "glpi_networkequipmentmodels" :
            $ruleinput["manufacturer"] = $external_params["manufacturer"];
            break;
      }*/

      $input["name"]        = $value;
      $input["comment"]     = $comment;
      $input["entities_id"] = $entities_id;

      if ($rulecollection) {
         $res_rule = $rulecollection->processAllRules(Toolbox::stripslashes_deep($ruleinput), array(), array());
         if (isset($res_rule["name"])) {
            $input["name"] = $res_rule["name"];
         }
      }
      return ($add ? $this->import($input) : $this->findID($input));
   }


   function refreshParentInfos() {

      if (!$this->refresh_page) {
         Ajax::refreshDropdownPopupInMainWindow();
      } else {
         Ajax::refreshPopupMainWindow();
      }
   }
}
?>