<?php
/*
 * @version $Id: fieldblacklist.class.php 20129 2013-02-04 16:53:59Z moyo $
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

/// Class Fieldblacklist
class Fieldblacklist extends CommonDropdown {

   static function getTypeName($nb=0) {
      return _n('Ignored value for the unicity', 'Ignored values for the unicity', $nb);
   }


   static function canCreate() {
      return Session::haveRight('config', 'w');
   }


   static function canView() {
      return Session::haveRight('config', 'r');
   }


   function getAdditionalFields() {

      return array(array('name'  => 'itemtype',
                         'label' => __('Type'),
                         'type'  => 'blacklist_itemtype'),
                   array('name'  => 'field',
                         'label' => _n('Field', 'Fields', 1),
                         'type'  => 'blacklist_field'),
                   array('name'  => 'value',
                         'label' => __('Value'),
                         'type'  => 'blacklist_value'));
   }


   /**
    * Get search function for the class
    *
    * @return array of search option
   **/
   function getSearchOptions() {

      $tab                        = parent::getSearchOptions();

      $tab[4]['table']            = $this->getTable();
      $tab[4]['field']            = 'itemtype';
      $tab[4]['name']             = __('Type');
      $tab[4]['massiveaction']    = false;
      $tab[4]['datatype']         = 'itemtypename';
      $tab[4]['forcegroupby']     = true;

      $tab[6]['table']            = $this->getTable();
      $tab[6]['field']            = 'field';
      $tab[6]['name']             = _n('Field', 'Fields', 1);
      $tab[6]['massiveaction']    = false;
      $tab[6]['datatype']         = 'specific';
      $tab[6]['additionalfields'] = array('itemtype');

      $tab[7]['table']            = $this->getTable();
      $tab[7]['field']            = 'value';
      $tab[7]['name']             = __('Value'); // Is also specific
      $tab[7]['datatype']         = 'specific';
      $tab[7]['additionalfields'] = array('itemtype', 'field');
      $tab[7]['massiveaction']    = false;

      return $tab;
   }


   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'field':
            if (isset($values['itemtype']) && !empty($values['itemtype'])) {
               $target       = getItemForItemtype($values['itemtype']);
               $searchOption = $target->getSearchOptionByField('field', $values[$field]);
//                if (empty($searchOption)) {
//                   if ($table = getTableNameForForeignKeyField($values[$field])) {
//                      $searchOption = $target->getSearchOptionByField('field', 'name', $table);
//                   }
//                   echo $table.'--';
//                }
               return $searchOption['name'];
            }
            break;

         case  'value' :
            if (isset($values['itemtype']) && !empty($values['itemtype'])) {
               $target = getItemForItemtype($values['itemtype']);
               if (isset($values['field']) && !empty($values['field'])) {
                  $searchOption = $target->getSearchOptionByField('field', $values['field']);
                  // MoYo : do not know why this part ?
//                   if ($table = getTableNameForForeignKeyField($values['field'])) {
//                      $searchOption = $target->getSearchOptionByField('field', 'name', $table);
//                   }
                  return $target->getValueToDisplay($searchOption, $values[$field]);
               }
            }
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * @since version 0.84
    *
    * @param $field
    * @param $name               (default '')
    * @param $values             (default '')
    * @param $options      array
   **/
   static function getSpecificValueToSelect($field, $name='', $values='', array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      $options['display'] = false;
      switch ($field) {
         case 'field' :
            if (isset($values['itemtype'])
                && !empty($values['itemtype'])) {
               $options['value'] = $values[$field];
               $options['name']  = $name;
               return self::dropdownField($values['itemtype'], $options);
            }
            break;

         case 'value' :
            if (isset($values['itemtype'])
                && !empty($values['itemtype'])) {
               if ($item = getItemForItemtype($values['itemtype'])) {
                  if (isset($values['field']) && !empty($values['field'])) {
                     $searchOption = $item->getSearchOptionByField('field', $values['field']);
                     return $item->getValueToSelect($searchOption, $name, $values[$field], $options);
                  }
               }
            }
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }


   /**
    * @see CommonDBTM::prepareInputForAdd()
   **/
   function prepareInputForAdd($input) {

      $input = parent::prepareInputForAdd($input);
      return $input;
   }


   /**
    * @see CommonDBTM::prepareInputForUpdate()
   **/
   function prepareInputForUpdate($input) {

      $input = parent::prepareInputForUpdate($input);
      return $input;
   }


   /**
    * Display specific fields for FieldUnicity
    *
    * @param $ID
    * @param $field array
   **/
   function displaySpecificTypeField($ID, $field=array()) {

      switch ($field['type']) {
         case 'blacklist_itemtype' :
            $this->showItemtype();
            break;

         case 'blacklist_field' :
            $this->selectCriterias();
            break;

         case 'blacklist_value' :
            $this->selectValues();
            break;
      }
   }


   /**
    * Display a dropdown which contains all the available itemtypes
    *
    * @return nothing
   **/
   function showItemtype() {
      global $CFG_GLPI;

      if ($this->fields['id'] > 0) {
         if ($item = getItemForItemtype($this->fields['itemtype'])) {
            echo $item->getTypeName(1);
         }
         echo "<input type='hidden' name='itemtype' value='".$this->fields['itemtype']."'>";

      } else {
         //Add criteria : display dropdown
         $options[0] = Dropdown::EMPTY_VALUE;
         foreach ($CFG_GLPI['unicity_types'] as $itemtype) {
            if ($item = getItemForItemtype($itemtype)) {
               if ($item->can(-1,'r')) {
                  $options[$itemtype] = $item->getTypeName(1);
               }
            }
         }
         asort($options);
         $rand = Dropdown::showFromArray('itemtype', $options,
                                         array('value' => $this->fields['value']));

         $params = array('itemtype' => '__VALUE__',
                         'id'       => $this->fields['id']);
         Ajax::updateItemOnSelectEvent("dropdown_itemtype$rand", "span_fields",
                                       $CFG_GLPI["root_doc"]."/ajax/dropdownFieldsBlacklist.php",
                                       $params);
      }
   }


   function selectCriterias() {
      global $CFG_GLPI;

      echo "<span id='span_fields' name='span_fields'>";

      if (!isset($this->fields['itemtype']) || !$this->fields['itemtype']) {
         echo "</span>";
         return;
      }

      if (!isset($this->fields['entities_id'])) {
         $this->fields['entities_id'] = $_SESSION['glpiactive_entity'];
      }

      if ($rand = self::dropdownField($this->fields['itemtype'],
                                      array('value' => $this->fields['field']))) {
         $params = array('itemtype' => $this->fields['itemtype'],
                         'id_field' => '__VALUE__',
                         'id'       => $this->fields['id']);
         Ajax::updateItemOnSelectEvent("dropdown_field$rand", "span_values",
                                       $CFG_GLPI["root_doc"]."/ajax/dropdownValuesBlacklist.php",
                                       $params);
      }
      echo "</span>";
   }


   /** Dropdown fields for a specific itemtype
    *
    * @since version 0.84
    *
    * @param $itemtype          itemtype
    * @param $options    array    of options
   **/
   static function dropdownField($itemtype, $options=array()) {
      global $DB;

      $p['name']    = 'field';
      $p['display'] = true;
      $p['value']   = '';

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      if ($target = getItemForItemtype($itemtype)) {
         $criteria = array();
         foreach ($DB->list_fields($target->getTable()) as $field) {
            $searchOption = $target->getSearchOptionByField('field', $field['Field']);

         // MoYo : do not know why  this part ?
//             if (empty($searchOption)) {
//                if ($table = getTableNameForForeignKeyField($field['Field'])) {
//                   $searchOption = $target->getSearchOptionByField('field', 'name', $table);
//                }
//             }

            if (!empty($searchOption)
                && !in_array($field['Type'], $target->getUnallowedFieldsForUnicity())
                && !in_array($field['Field'], $target->getUnallowedFieldsForUnicity())) {
               $criteria[$field['Field']] = $searchOption['name'];
            }
         }
         return Dropdown::showFromArray($p['name'], $criteria, $p);
      }
      return false;
   }


   /**
    * @param $field  (default '')
   **/
   function selectValues($field='') {
      global $DB, $CFG_GLPI;

      if ($field == '') {
         $field = $this->fields['field'];
      }
      echo "<span id='span_values' name='span_values'>";
      if ($this->fields['itemtype'] != '') {
         if ($item = getItemForItemtype($this->fields['itemtype'])) {
            $searchOption = $item->getSearchOptionByField('field', $field);
            $options      = array();
            if (isset($this->fields['entity'])) {
               $options['entity']      = $this->fields['entity'];
               $options['entity_sons'] = $this->fields['is_recursive'];
            }
            echo $item->getValueToSelect($searchOption, 'value', $this->fields['value'], $options);
//             if (isset($searchOption['linkfield'])) {
//                $linkfield = $searchOption['linkfield'];
//             } else {
//                $linkfield = $searchOption['field'];
//             }
//
//             if ($linkfield == $this->fields['field']) {
//                $value = $this->fields['value'];
//             } else {
//                $value = '';
//             }
         }

//          //If field is a foreign key on another table or not
//          $table = getTableNameForForeignKeyField($linkfield);
//          if ($table == '') {
//             if (isset($searchOption['datatype'])) {
//                $datatype = $searchOption['datatype'];
//             } else {
//                $datatype = 'text';
//             }
//
//             switch ($datatype) {
//                case 'text' :
//                case 'string' :
//                default :
//                   Html::autocompletionTextField($this, 'value', array('value' => $value));
//                   break;
//
//                case 'bool':
//                   Dropdown::showYesNo('value',$value);
//                   break;
//             }
//
//          } else {
//             $itemtype = getItemTypeForTable($table);
//             Dropdown::show($itemtype, array('name'  => 'value',
//                                             'value' => $value));
//          }
      }
      echo "</span>";
   }


   /**
    * Check if a field & value are blacklisted or not
    *
    * @param itemtype      itemtype of the blacklisted field
    * @param entities_id   the entity in which the field must be saved
    * @param field         the field to check
    * @param value         the field's value
    *
    * @return true is value if blacklisted, false otherwise
   **/
   static function isFieldBlacklisted($itemtype, $entities_id, $field, $value) {
      global $DB;

      $query = "SELECT COUNT(*) AS cpt
                FROM `glpi_fieldblacklists`
                WHERE `itemtype` = '$itemtype'
                      AND `field` = '$field'
                      AND `value` = '$value'".
                      getEntitiesRestrictRequest(" AND", "glpi_fieldblacklists", "entities_id",
                                                 $entities_id, true);
      return ($DB->result($DB->query($query), 0, 'cpt') ?true :false);
   }

}
?>