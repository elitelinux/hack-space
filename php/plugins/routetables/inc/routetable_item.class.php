<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Routetables plugin for GLPI
  Copyright (C) 2003-2011 by the Routetables Development Team.

  https://forge.indepnet.net/projects/routetables
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Routetables.

  Routetables is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Routetables is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Routetables. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginRoutetablesRoutetable_Item extends CommonDBTM {

   // From CommonDBRelation
   static public $itemtype_1 = "PluginRoutetablesRoutetable";
   static public $items_id_1 = 'plugin_routetables_routetables_id';
   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';

   public static function canCreate() {
      return plugin_routetables_haveRight('routetables', 'w');
   }

   public static function canView() {
      return plugin_routetables_haveRight('routetables', 'r');
   }

   static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
              array('itemtype' => $item->getType(),
                  'items_id' => $item->getField('id'))
      );
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!$withtemplate) {
         if ($item->getType() == 'PluginRoutetablesRoutetable'
                 && count(PluginRoutetablesRoutetable::getTypes(false))) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(__('Associated item'), self::countForRoutetable($item));
            }
            return __('Associated item');
         } else if (in_array($item->getType(), PluginRoutetablesRoutetable::getTypes(true))
                 && plugin_routetables_haveRight('routetables', 'r')) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginRoutetablesRoutetable::getTypeName(2), self::countForItem($item));
            }
            return PluginRoutetablesRoutetable::getTypeName(2);
         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'PluginRoutetablesRoutetable') {

         self::showForRoutetables($item);
      } else if (in_array($item->getType(), PluginRoutetablesRoutetable::getTypes(true))) {

         self::showForItem($item);
      }
      return true;
   }

   static function countForRoutetable(PluginRoutetablesRoutetable $item) {

      $types = implode("','", $item->getTypes());
      if (empty($types)) {
         return 0;
      }
      return countElementsInTable('glpi_plugin_routetables_routetables_items', "`itemtype` IN ('$types')
                                   AND `plugin_routetables_routetables_id` = '".$item->getID()."'");
   }

   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_routetables_routetables_items', "`itemtype`='".$item->getType()."'
                                   AND `items_id` = '".$item->getID()."'");
   }

   function getFromDBbyRoutetablesAndItem($plugin_routetables_routetables_id, $items_id, $itemtype) {
      global $DB;

      $query = "SELECT * FROM `".$this->getTable()."` ".
              "WHERE `plugin_routetables_routetables_id` = '".$plugin_routetables_routetables_id."' 
			AND `itemtype` = '".$itemtype."'
			AND `items_id` = '".$items_id."'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   function addItem($values) {

      $this->add(array('plugin_routetables_routetables_id' => $values["plugin_routetables_routetables_id"],
          'items_id' => $values["items_id"],
          'itemtype' => $values["itemtype"]));
   }

   function deleteItemByRoutetablesAndItem($plugin_routetables_routetables_id, $items_id, $itemtype) {

      if ($this->getFromDBbyRoutetablesAndItem($plugin_routetables_routetables_id, $items_id, $itemtype)) {
         $this->delete(array('id' => $this->fields["id"]));
      }
   }

   /**
    * @since version 0.84
    * */
   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }

   /**
    * Show items links to a routetables
    *
    * @since version 0.84
    *
    * @param $routetable PluginRoutetablesRoutetable object
    *
    * @return nothing (HTML display)
    * */
   public static function showForRoutetables(PluginRoutetablesRoutetable $routetable) {
      global $DB;

      $instID = $routetable->fields['id'];
      if (!$routetable->can($instID, 'r'))
         return false;

      $rand = mt_rand();

      $canedit = $routetable->can($instID, 'w');

      $query = "SELECT DISTINCT `itemtype`
             FROM `glpi_plugin_routetables_routetables_items`
             WHERE `plugin_routetables_routetables_id` = '$instID'
             ORDER BY `itemtype`
             LIMIT ".count(PluginRoutetablesRoutetable::getTypes(true));

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form method='post' name='routetable_form$rand' id='routetable_form$rand'
         action='".Toolbox::getItemTypeFormURL("PluginRoutetablesRoutetable")."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='".($canedit ? (5 + $colsup) : (4 + $colsup))."'>".
         __('Add an item')."</th></tr>";

         echo "<tr class='tab_bg_1'><td colspan='".(3 + $colsup)."' class='center'>";
         echo "<input type='hidden' name='plugin_routetables_routetables_id' value='$instID'>";
         Dropdown::showAllItems("items_id", 0, 0, -1, PluginRoutetablesRoutetable::getTypes());
         echo "</td>";
         echo "<td colspan='2' class='tab_bg_2'>";
         echo "<input type='submit' name='additem' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array();
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";

      if ($canedit && $number) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }

      echo "<th>".__('Type')."</th>";
      echo "<th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode())
         echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Serial number')."</th>";
      echo "<th>".__('Inventory number')."</th>";
      echo "</tr>";

      for ($i = 0; $i < $number; $i++) {
         $itemType = $DB->result($result, $i, "itemtype");

         if (!($item = getItemForItemtype($itemType))) {
            continue;
         }

         if ($item->canView()) {
            $column = "name";
            $itemTable = getTableForItemType($itemType);

            $query = "SELECT `".$itemTable."`.*,
                             `glpi_plugin_routetables_routetables_items`.`id` AS items_id,
                             `glpi_entities`.`id` AS entity "
                    ." FROM `glpi_plugin_routetables_routetables_items`, `".$itemTable
                    ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$itemTable."`.`entities_id`) "
                    ." WHERE `".$itemTable."`.`id` = `glpi_plugin_routetables_routetables_items`.`items_id`
                AND `glpi_plugin_routetables_routetables_items`.`itemtype` = '$itemType'
                AND `glpi_plugin_routetables_routetables_items`.`plugin_routetables_routetables_id` = '$instID' "
                    .getEntitiesRestrictRequest(" AND ", $itemTable, '', '', $item->maybeRecursive());

            if ($item->maybeTemplate()) {
               $query.=" AND `".$itemTable."`.`is_template` = '0'";
            }
            $query.=" ORDER BY `glpi_entities`.`completename`, `".$itemTable."`.`$column`";

            if ($result_linked = $DB->query($query)) {
               if ($DB->numrows($result_linked)) {

                  Session::initNavigateListItems($itemType, PluginRoutetablesRoutetable::getTypeName(2)." = ".$routetable->fields['name']);

                  while ($data = $DB->fetch_assoc($result_linked)) {

                     $item->getFromDB($data["id"]);

                     Session::addToNavigateListItems($itemType, $data["id"]);

                     $ID = "";

                     if ($_SESSION["glpiis_ids_visible"] || empty($data["name"]))
                        $ID = " (".$data["id"].")";

                     $link = Toolbox::getItemTypeFormURL($itemType);
                     $name = "<a href=\"".$link."?id=".$data["id"]."\">"
                             .$data["name"]."$ID</a>";

                     echo "<tr class='tab_bg_1'>";

                     if ($canedit) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(__CLASS__, $data["items_id"]);
                        echo "</td>";
                     }
                     echo "<td class='center'>".$item::getTypeName(1)."</td>";

                     echo "<td class='center' ".(isset($data['is_deleted']) && $data['is_deleted'] ? "class='tab_bg_2_2'" : "").
                     ">".$name."</td>";

                     if (Session::isMultiEntitiesMode())
                        echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data['entity'])."</td>";

                     echo "<td class='center'>".(isset($data["serial"]) ? "".$data["serial"]."" : "-")."</td>";
                     echo "<td class='center'>".(isset($data["otherserial"]) ? "".$data["otherserial"]."" : "-")."</td>";

                     echo "</tr>";
                  }
               }
            }
         }
      }
      echo "</table>";

      if ($canedit && $number) {
         $paramsma['ontop'] = false;
         Html::showMassiveActions(__CLASS__, $paramsma);
         Html::closeForm();
      }
      echo "</div>";
   }

   /**
    * Show routetables associated to an item
    *
    * @since version 0.84
    *
    * @param $item            CommonDBTM object for which associated routetables must be displayed
    * @param $withtemplate    (default '')
    * */
   static function showForItem(CommonDBTM $item, $withtemplate = '') {
      global $DB, $CFG_GLPI;

      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!plugin_routetables_haveRight('routetables', 'r')) {
         return false;
      }

      if (!$item->can($item->fields['id'], 'r')) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }

      $canedit = $item->canadditem('PluginRoutetablesRoutetable');
      $rand = mt_rand();
      $is_recursive = $item->isRecursive();

      $query = "SELECT `glpi_plugin_routetables_routetables_items`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity,
                       `glpi_plugin_routetables_routetables`.`name` AS assocName,
                       `glpi_plugin_routetables_routetables`.*
                FROM `glpi_plugin_routetables_routetables_items`
                LEFT JOIN `glpi_plugin_routetables_routetables`
                 ON (`glpi_plugin_routetables_routetables_items`.`plugin_routetables_routetables_id`=`glpi_plugin_routetables_routetables`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_plugin_routetables_routetables`.`entities_id`=`glpi_entities`.`id`)
                WHERE `glpi_plugin_routetables_routetables_items`.`items_id` = '$ID'
                      AND `glpi_plugin_routetables_routetables_items`.`itemtype` = '".$item->getType()."' ";

      $query .= getEntitiesRestrictRequest(" AND","glpi_plugin_routetables_routetables",'','',false);

      $query .= " ORDER BY `assocName`";
      
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i = 0;

      $routetables   = array();
      $routetable    = new PluginRoutetablesRoutetable();
      $used          = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $routetables[$data['id']] = $data;
            $used[$data['id']] = $data['id'];
         }
      }

      if ($canedit && $withtemplate < 2) {
         // Restrict entity for knowbase
         $entities = "";
         $entity = $_SESSION["glpiactive_entity"];

         if ($item->isEntityAssign()) {
            /// Case of personal items : entity = -1 : create on active entity (Reminder case))
            if ($item->getEntityID() >= 0) {
               $entity = $item->getEntityID();
            }

            if ($item->isRecursive()) {
               $entities = getSonsOf('glpi_entities', $entity);
            } else {
               $entities = $entity;
            }
         }
         $limit = getEntitiesRestrictRequest(" AND ", "glpi_plugin_routetables_routetables", '', $entities, false);
         $q = "SELECT COUNT(*)
               FROM `glpi_plugin_routetables_routetables`
               WHERE `is_deleted` = '0'
               $limit";

         $result = $DB->query($q);
         $nb = $DB->result($result, 0, 0);

         echo "<div class='firstbloc'>";

         if (plugin_routetables_haveRight('routetables', 'r')
                 && ($nb > count($used))) {
            echo "<form name='routetable_form$rand' id='routetable_form$rand' method='post'
                   action='".Toolbox::getItemTypeFormURL('PluginRoutetablesRoutetable')."'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' class='center'>";
            echo "<input type='hidden' name='entities_id' value='$entity'>";
            echo "<input type='hidden' name='is_recursive' value='$is_recursive'>";
            echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
            echo "<input type='hidden' name='items_id' value='$ID'>";
            if ($item->getType() == 'Ticket') {
               echo "<input type='hidden' name='tickets_id' value='$ID'>";
            }

            $routetable->dropdownRouteTables("plugin_routetables_routetables_id", $entities, $used);
            echo "</td><td class='center' width='20%'>";
            echo "<input type='submit' name='additem' value=\"".
            _sx('button', 'Associate a routing table', 'routetables')."\" class='submit'>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            Html::closeForm();
         }

         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number && ($withtemplate < 2)) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('num_displayed' => $number);
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      if ($canedit && $number && ($withtemplate < 2)) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }

      echo "<th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>".__('Entity')."</th>";
      }
      echo "<th>".__('Network')."</th>";
      echo "<th>".__('Subnet mask')."</th>";
      echo "<th>".__('Gateway')."</th>";
      echo "<th>".__('Metric', 'routetables')."</th>";
      echo "<th>".__('Interface')."</th>";
      echo "<th>".__('Persistance', 'routetables')."</th>";
      echo "<th>".__('Comments')."</th>";
      echo "</tr>";
      $used = array();

      if ($number) {

         Session::initNavigateListItems('PluginRoutetablesRoutetable',
                 //TRANS : %1$s is the itemtype name,
                 //        %2$s is the name of the item (used for headings of a list)
                 sprintf(__('%1$s = %2$s'), $item->getTypeName(1), $item->getName()));

         foreach ($routetables as $data) {
            
            $routetableID = $data["id"];
            $link         = NOT_AVAILABLE;

            if ($routetable->getFromDB($routetableID)) {
               $link         = $routetable->getLink();
            }

            Session::addToNavigateListItems('PluginRoutetablesRoutetable', $routetable);
            
            $used[$routetableID] = $routetableID;
            $assocID      = $data["assocID"];
            
            echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
            if ($canedit && ($withtemplate < 2)) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["assocID"]);
               echo "</td>";
            }
            echo "<td class='center'>$link</td>";
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data['entities_id']).
                    "</td>";
            }
            echo "<td class='center'>" . $data["destination"] . "</td>";
            echo "<td class='center'>" . $data["netmask"] . "</td>";
            echo "<td class='center'>" . $data["gateway"] . "</td>";
            echo "<td class='center'>" . $data["metric"] . "</td>";
            echo "<td class='center'>" . $data["interface"] . "</td>";
            echo "<td class='center'>" . Dropdown::getYesNo($data["persistence"]) . "</td>";
            echo "<td class='center'>" . $data["comment"] . "</td>";
            echo "</tr>";
            $i++;
         }
      }

      echo "</table>";
      if ($canedit && $number && ($withtemplate < 2)) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }
}

?>