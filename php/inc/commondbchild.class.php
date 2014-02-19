<?php
/*
 * @version $Id: commondbchild.class.php 20600 2013-03-31 13:34:51Z webmyster $
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

/// Common DataBase Relation Table Manager Class
abstract class CommonDBChild extends CommonDBConnexity {

   // Mapping between DB fields
   // * definition
   static public $itemtype; // Class name or field name (start with itemtype) for link to Parent
   static public $items_id; // Field name
   // * rights
   static public $checkParentRights  = self::HAVE_SAME_RIGHT_ON_ITEM;
   static public $mustBeAttached     = true;
   // * log
   static public $logs_for_parent    = true;
   static public $log_history_add    = Log::HISTORY_ADD_SUBITEM;
   static public $log_history_update = Log::HISTORY_UPDATE_SUBITEM;
   static public $log_history_delete = Log::HISTORY_DELETE_SUBITEM;
   static public $log_history_lock   = Log::HISTORY_LOCK_SUBITEM;
   static public $log_history_unlock = Log::HISTORY_UNLOCK_SUBITEM;

   /**
    * @since version 0.84
    *
    * @param $itemtype
    * @param $items_id
    *
    * @return string
   **/
   static function getSQLRequestToSearchForItem($itemtype, $items_id) {

      $conditions = array();
      $fields     = array('`'.static::getIndexName().'`');

      // Check item 1 type
      $condition_id = "`".static::$items_id."` = '$items_id'";
      $fields[]     = "`".static::$items_id."` as items_id";
      if (preg_match('/^itemtype/', static::$itemtype)) {
         $fields[]  = "`".static::$itemtype."` AS itemtype";
         $condition = "($condition_id AND `".static::$itemtype."` = '$itemtype')";
      } else {
         $fields[] = "'".static::$itemtype."' AS itemtype";
         if (($itemtype ==  static::$itemtype)
             || is_subclass_of($itemtype,  static::$itemtype)) {
            $condition = $condition_id;
         }
      }
      if (isset($condition)) {
         return "SELECT ".implode(', ', $fields)."
                 FROM `".static::getTable()."`
                 WHERE $condition";
      }
      return '';
   }


   /**
    * @since version 0.84
   **/
   static function canCreate() {
      return static::canChild('canUpdate');
   }


   /**
    * @since version 0.84
   **/
   static function canView() {
      return static::canChild('canView');
   }


   /**
    * @since version 0.84
   **/
   static function canUpdate() {
      return static::canChild('canUpdate');
   }


   /**
    * @since version 0.84
   **/
   static function canDelete() {
      return static::canChild('canUpdate');
   }


   /**
    * @since version 0.84
   **/
   function canCreateItem() {
      return $this->canChildItem('canUpdateItem', 'canUpdate');
   }


   /**
    * @since version 0.84
   **/
   function canViewItem() {
      return $this->canChildItem('canViewItem', 'canView');
   }


   /**
    * @since version 0.84
   **/
   function canUpdateItem() {
      return $this->canChildItem('canUpdateItem', 'canUpdate');
   }


   /**
    * @since version 0.84
   **/
   function canDeleteItem() {
      return $this->canChildItem('canUpdateItem', 'canUpdate');
   }


   /**
    * @since version 0.84
    *
    * @param $method
   **/
   static function canChild($method) {

      return static::canConnexity($method, static::$checkParentRights, static::$itemtype,
                                  static::$items_id);
   }


   /**
    * @since version 0.84
    *
    * @param $methodItem
    * @param $methodNotItem
    *
    * @return boolean
   **/
   function canChildItem($methodItem, $methodNotItem) {

      try {
         return static::canConnexityItem($methodItem, $methodNotItem, static::$checkParentRights,
                                         static::$itemtype, static::$items_id, $item);
      } catch (CommonDBConnexityItemNotFound $e) {
         return !static::$mustBeAttached;
      }
   }


   /**
    * Get the item associated with the current object. Rely on CommonDBConnexity::getItemFromArray()
    *
    * @since version 0.84
    *
    * @param $getFromDB   (true by default)
    * @param $getEmpty    (true by default)
    *
    * @return object of the concerned item or false on error
   **/
   function getItem($getFromDB=true, $getEmpty=true) {

      return $this->getConnexityItem(static::$itemtype, static::$items_id,
                                     $getFromDB, $getEmpty);
   }


   /**
    * \brief recursively display the items of this
    *
    * @param $recursiveItems     array of the items of the current elements (see recursivelyGetItems())
    * @param $elementToDisplay         what to display : 'Type', 'Name', 'Link'
   **/
   static function displayRecursiveItems(array $recursiveItems, $elementToDisplay) {

      if ((!is_array($recursiveItems)) || (count($recursiveItems) == 0)) {
         _e('Item not linked to an object');
         return;
      }

      switch ($elementToDisplay) {
      case 'Type' :
         $masterItem = $recursiveItems[count($recursiveItems) - 1];
         echo $masterItem->getTypeName(1);
         break;

      case 'Name' :
      case 'Link' :
         $items_elements  = array();
         foreach ($recursiveItems as $item) {
            if ($elementToDisplay == 'Name') {
               $items_elements[] = $item->getName();
            } else {
               $items_elements[] = $item->getLink();
            }
         }
         echo implode(' &lt; ', $items_elements);
         break;
      }

   }


   /**
    * Get all the items associated with the current object by recursive requests
    *
    * @since version 0.84
    *
    * @return an array containing all the items
   **/
   function recursivelyGetItems() {

      $item = $this->getItem();
      if ($item !== false) {
         if ($item instanceof CommonDBChild) {
            return array_merge(array($item), $item->recursivelyGetItems());
         }
         return array($item);
      }
      return array();
   }


   /**
    * Get the ID of entity assigned to the object
    *
    * @return ID of the entity
   **/
   function getEntityID () {

      // Case of Duplicate Entity info to child
      if (parent::isEntityAssign()) {
         return parent::getEntityID();
      }

      $item = $this->getItem();
      if (($item !== false) && ($item->isEntityAssign())) {

         return $item->getEntityID();

      }
      return -1;
   }


   function isEntityAssign() {

      // Case of Duplicate Entity info to child
      if (parent::isEntityAssign()) {
         return true;
      }

      $item = $this->getItem(false);

      if ($item !== false) {
         return $item->isEntityAssign();
      }

      return false;
   }


   /**
    * Is the object may be recursive
    *
    * @return boolean
   **/
   function maybeRecursive() {

      // Case of Duplicate Entity info to child
      if (parent::maybeRecursive()) {
         return true;
      }

      $item = $this->getItem(false);

      if ($item !== false) {
         return $item->maybeRecursive();
      }

      return false;
   }


   /**
    * Is the object recursive
    *
    * @return booleanDONT_CHECK_ITEM_RIGHTS
   **/
   function isRecursive () {

      // Case of Duplicate Entity info to child
      if (parent::maybeRecursive()) {
          return parent::isRecursive();
      }

      $item = $this->getItem();

      if ($item !== false) {
         return $item->isRecursive();
      }

      return false;
   }


   /**
    * @since version 0.84
   **/
   function addNeededInfoToInput($input) {

      // is entity missing and forwarding on ?
      if ($this->tryEntityForwarding() && !isset($input['entities_id'])) {
         // Merge both arrays to ensure all the fields are defined for the following checks
         $completeinput = array_merge($this->fields, $input);
         // Set the item to allow parent::prepareinputforadd to get the right item ...
         if ($itemToGetEntity = static::getItemFromArray(static::$itemtype, static::$items_id,
                                                         $completeinput)) {
            if (($itemToGetEntity instanceof CommonDBTM)
                && $itemToGetEntity->isEntityForwardTo(get_called_class())) {

               $input['entities_id']  = $itemToGetEntity->getEntityID();
               $input['is_recursive'] = intval($itemToGetEntity->isRecursive());

            } else {
               // No entity link : set default values
               $input['entities_id']  = 0;
               $input['is_recursive'] = 0;
            }
         }
      }
      return $input;
   }


   /**
    * @since version 0.84
   **/
   function prepareInputForAdd($input) {

      if (!is_array($input)) {
         return false;
      }
      return $this->addNeededInfoToInput($input);
   }


   /**
    * @since version 0.84
   **/
   function prepareInputForUpdate($input) {

      if (!is_array($input)) {
         return false;
      }

      // True if item changed
      if (!parent::checkAttachedItemChangesAllowed($input, array(static::$itemtype,
                                                                 static::$items_id))) {
         return false;
      }

      return parent::addNeededInfoToInput($input);
   }


   /**
    * Get the history name of item
    *
    * @param $item the other item
    * @param $case : can be overwrite by object
    *              - 'add' when this CommonDBChild is added (to and item)
    *              - 'update item previous' transfert : this is removed from the old item
    *              - 'update item next' transfert : this is added to the new item
    *              - 'delete' when this CommonDBChild is remove (from an item)
    *
    * @return (string) the name of the entry for the database (ie. : correctly slashed)
   **/
   function getHistoryNameForItem(CommonDBTM $item, $case) {

      return $this->getNameID(array('forceid'    => true,
                                    'additional' => true));
   }


   /**
    * Actions done after the ADD of the item in the database
    *
    * @return nothing
   **/
   function post_addItem() {

      if ((isset($this->input['_no_history']) && $this->input['_no_history'])
          || !static::$logs_for_parent) {
         return;
      }

      $item = $this->getItem();

      if (($item !== false)
          && $item->dohistory) {
         $changes[0] = '0';
         $changes[1] = "";
         $changes[2] = $this->getHistoryNameForItem($item, 'add');
         Log::history($item->getID(), $item->getType(), $changes, $this->getType(),
                      static::$log_history_add);
      }
   }


   /**
    * Actions done after the UPDATE of the item in the database
    *
    * @since version 0.84
    *
    * @param $history store changes history ? (default 1)
    *
    * @return nothing
   **/
   function post_updateItem($history=1) {

      if ((isset($this->input['_no_history']) && $this->input['_no_history'])
          || !static::$logs_for_parent) {
         return;
      }

      $items_for_log = $this->getItemsForLog(static::$itemtype, static::$items_id);


      // Whatever case : we log the changes
      $oldvalues = $this->oldvalues;
      unset($oldvalues[static::$itemtype]);
      unset($oldvalues[static::$items_id]);
      $item      = $items_for_log['new'];
      if (($item !== false)
          && $item->dohistory) {
         foreach ($oldvalues as $field => $value) {
            $changes = $this->getHistoryChangeWhenUpdateField($field);
            if ((!is_array($changes)) || (count($changes) != 3)) {
               continue;
            }
            Log::history($item->getID(), $item->getType(), $changes, $this->getType(),
                         static::$log_history_update);
         }
      }

      if (isset($items_for_log['previous'])) {
         // Have updated the connexity relation

         $prevItem = $items_for_log['previous'];
         $newItem  = $items_for_log['new'];

         if (($prevItem !== false)
             && $prevItem->dohistory) {
            $changes[0] = '0';
            $changes[1] = addslashes($this->getHistoryNameForItem($prevItem, 'update item previous'));
            $changes[2] = '';
            Log::history($prevItem->getID(), $prevItem->getType(), $changes, $this->getType(),
                         static::$log_history_delete);
         }

         if (($newItem !== false)
             && $newItem->dohistory) {
            $changes[0] = '0';
            $changes[1] = '';
            $changes[2] = addslashes($this->getHistoryNameForItem($newItem, 'update item next'));
            Log::history($newItem->getID(), $newItem->getType(), $changes, $this->getType(),
                         static::$log_history_add);
         }
      }
  }

   /**
    * Actions done after the DELETE of the item in the database
    *
    *@return nothing
   **/
   function post_deleteFromDB() {

      if ((isset($this->input['_no_history']) && $this->input['_no_history'])
          || !static::$logs_for_parent) {
         return;
      }

      $item = $this->getItem();

      if (($item !== false)
          && $item->dohistory) {
         $changes[0] = '0';

         if (static::$log_history_delete == Log::HISTORY_LOG_SIMPLE_MESSAGE) {
            $changes[1] = '';
            $changes[2] = addslashes($this->getHistoryNameForItem($item, 'delete'));
         } else {
            $changes[1] = addslashes($this->getHistoryNameForItem($item, 'delete'));
            $changes[2] = '';
         }
         Log::history($item->getID(), $item->getType(), $changes, $this->getType(),
                      static::$log_history_delete);
      }
   }


   /**
    *  Actions done when item flag deleted is set to an item
    *
    * @since version 0.84
    *
    * @return nothing
   **/
   function cleanDBonMarkDeleted() {

      if ((isset($this->input['_no_history']) && $this->input['_no_history'])
          || !static::$logs_for_parent) {
         return;
      }

      if ($this->useDeletedToLockIfDynamic()
          && $this->isDynamic()) {
         $item = $this->getItem();


         if (($item !== false)
             && $item->dohistory) {
            $changes[0] = '0';
            $changes[1] = addslashes($this->getHistoryNameForItem($item, 'lock'));
            $changes[2] = '';
            Log::history($item->getID(), $item->getType(), $changes, $this->getType(),
                         static::$log_history_lock);
         }
      }
   }


   /**
    * Actions done after the restore of the item
    *
    * @since version 0.84
    *
    * @return nothing
   **/

   function post_restoreItem() {
      if ((isset($this->input['_no_history']) && $this->input['_no_history'])
          || !static::$logs_for_parent) {
         return;
      }

      if ($this->useDeletedToLockIfDynamic()
          && $this->isDynamic()) {
         $item = $this->getItem();


         if (($item !== false)
             && $item->dohistory) {
            $changes[0] = '0';
            $changes[1] = '';
            $changes[2] = addslashes($this->getHistoryNameForItem($item, 'unlock'));
            Log::history($item->getID(), $item->getType(), $changes, $this->getType(),
                         static::$log_history_unlock);
         }
      }
   }


   /**
    * get the Javascript "code" to add to the form when clicking on "+"
    *
    * @since version 0.84
    *
    * @see showAddChildButtonForItemForm()
    *
    * @param $field_name         the name of the HTML field inside Item's form
    * @param $child_count_js_var the name of the javascript variable containing current child number
    *                            of items
    *
    * @return (string) the code
   **/
   static function getJSCodeToAddForItemChild($field_name, $child_count_js_var) {
      return "<input type=\'text\' size=\'40\' ". "name=\'" . $field_name .
             "[-'+$child_count_js_var+']\'>";
   }


   /**
    * display the field of a given child
    *
    * @since version 0.84
    *
    * @see showChildsForItemForm()
    *
    * @param $canedit     true if we can edit the child
    * @param $field_name  the name of the HTML field inside Item's form
    *
    * @return nothing (display only)
   **/
   function showChildForItemForm($canedit, $field_name) {

      if ($this->isNewID($this->getID())) {
         $value = '';
      } else {
         $value = $this->getName();
      }
      if ($canedit) {
         echo "<input type='text' size='40' name='$field_name' value='$value'>";
      } else {
         echo "<input type='hidden' name='$field_name' value='$value'>$value" ;
      }
   }


   /**
    * We can add several single CommonDBChild to a given Item. In such case, we display a "+"
    * button and the fields already entered
    * This method display the "+" button
    *
    * @since version 0.84
    *
    * @todo study if we cannot use these methods for the user emails
    * @see showChildsForItemForm(CommonDBTM $item, $field_name)
    *
    * @param $item         CommonDBTM object: the item on which to add the current CommenDBChild
    * @param $field_name   the name of the HTML field inside Item's form
    * @param $canedit      (default NULL) NULL to use default behaviour
    *
    * @return nothing (display only)
   **/
   static function showAddChildButtonForItemForm(CommonDBTM $item, $field_name, $canedit=NULL) {
      global $CFG_GLPI;

      $items_id = $item->getID();

      if (is_null($canedit)) {
         if ($item->isNewItem()) {
            if (!$item->canCreate()) {
               return false;
            }
            $canedit = $item->canUpdate();
         } else {
            if (!$item->can($items_id,'r')) {
               return false;
            }

            $canedit = $item->can($items_id,"w");
         }
      }

      if ($canedit) {
         $lower_name         = strtolower(get_called_class());
         $child_count_js_var = 'nb'.$lower_name.'s';
         $div_id             = "add_".$lower_name."_to_".$item->getType()."_".$items_id;

         echo "&nbsp;<script type='text/javascript'>var $child_count_js_var=1; </script>";
         echo "<span id='add".$lower_name."button'>".
              "<img title=\"".__s('Add')."\" alt=\"". __s('Add').
                "\" onClick=\"var row = Ext.get('$div_id');
                             row.createChild('<br>" .
               static::getJSCodeToAddForItemChild($field_name, $child_count_js_var)."');
                            $child_count_js_var++;\"
               class='pointer' src='".$CFG_GLPI["root_doc"]."/pics/add_dropdown.png'></span>";
      }
   }


   /**
    * We can add several single CommonDBChild to a given Item. In such case, we display a "+"
    * button and the fields already entered.
    * This method display the fields
    *
    * @since version 0.84
    *
    * @todo study if we cannot use these methods for the user emails
    * @see showAddChildButtonForItemForm()
    *
    * @param $item         CommonDBTM object the item on which to add the current CommenDBChild
    * @param $field_name   the name of the HTML field inside Item's form
    * @param $canedit      (default NULL) NULL to use default behaviour
    *
    * @return nothing (display only)
   **/
   static function showChildsForItemForm(CommonDBTM $item, $field_name, $canedit=NULL) {
      global $DB, $CFG_GLPI;

      $items_id = $item->getID();

      if (is_null($canedit)) {
         if ($item->isNewItem()) {
            if (!$item->canCreate()) {
               return false;
            }
            $canedit = $item->canUpdate();
         } else {
            if (!$item->can($items_id,'r')) {
               return false;
            }

            $canedit = $item->can($items_id,"w");
         }
      }

      $lower_name = strtolower(get_called_class());
      $div_id     = "add_".$lower_name."_to_".$item->getType()."_".$items_id;

     // To be sure not to load bad datas from this table
      if ($items_id == 0) {
         $items_id = -99;
      }

      $query = "SELECT *
                FROM `" . static::getTable() . "`
                WHERE `".static::$items_id."` = '".$item->getID()."'";

      if (preg_match('/^itemtype/', static::$itemtype)) {
         $query .= " AND `itemtype` = '".$item->getType()."'";
      }

      $current_item = new static();

      if ($current_item->maybeDeleted()) {
         $query .= " AND `is_deleted` = '0'";
      }

      $count = 0;
      foreach ($DB->request($query) as $data) {

         $current_item->fields = $data;

         if ($count) {
            echo '<br>';
         }
         $count++;

         $current_item->showChildForItemForm($canedit, $field_name . "[" .
                                             $current_item->getID() . "]");

      }

      if ($canedit) {
         echo "<div id='$div_id'>";
         // No Child display field
         if ($count == 0) {
            $current_item->getEmpty();
            $current_item->showChildForItemForm($canedit, $field_name . "[-100]");
         }
         echo "</div>";
      }
   }

}
?>
