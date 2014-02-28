<?php
/*
 * @version $Id: change_item.class.php 22657 2014-02-12 16:17:54Z moyo $
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

// Relation between Changes and Items
class Change_Item extends CommonDBRelation{


   // From CommonDBRelation
   static public $itemtype_1          = 'Change';
   static public $items_id_1          = 'changes_id';

   static public $itemtype_2          = 'itemtype';
   static public $items_id_2          = 'items_id';
   static public $checkItem_2_Rights  = self::DONT_CHECK_ITEM_RIGHTS;



   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }


   function prepareInputForAdd($input) {

      // Well, if I remember my PHP: empty(0) == true ...
      if (empty($input['changes_id']) || ($input['changes_id'] == 0)) {
         return false;
      }

      // Avoid duplicate entry
      $restrict = "`changes_id` = '".$input['changes_id']."'
                   AND `itemtype` = '".$input['itemtype']."'
                   AND `items_id` = '".$input['items_id']."'";
      if (countElementsInTable($this->getTable(),$restrict)>0) {
         return false;
      }
      return parent::prepareInputForAdd($input);
   }


   static function countForItem(CommonDBTM $item) {

      $restrict = "`glpi_changes_items`.`changes_id` = `glpi_changes`.`id`
                   AND `glpi_changes_items`.`items_id` = '".$item->getField('id')."'
                   AND `glpi_changes_items`.`itemtype` = '".$item->getType()."'".
                   getEntitiesRestrictRequest(" AND ", "glpi_changes", '', '', true);

      $nb = countElementsInTable(array('glpi_changes_items', 'glpi_changes'), $restrict);

      return $nb ;
   }


   /**
    * Print the HTML array for Items linked to a change
    *
    * @param $change Change object
    *
    * @return Nothing (display)
   **/
   static function showForChange(Change $change) {
      global $DB, $CFG_GLPI;

      $instID = $change->fields['id'];

      if (!$change->can($instID,'r')) {
         return false;
      }
      $canedit = $change->can($instID,'w');
      $rand    = mt_rand();

      $query = "SELECT DISTINCT `itemtype`
                FROM `glpi_changes_items`
                WHERE `glpi_changes_items`.`changes_id` = '$instID'
                ORDER BY `itemtype`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);


      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='changeitem_form$rand' id='changeitem_form$rand' method='post'
                action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Add an item')."</th></tr>";

         echo "<tr class='tab_bg_1'><td class='right'>";
         $types = array();
         foreach ($change->getAllTypesForHelpdesk() as $key => $val) {
            $types[] = $key;
         }
         Dropdown::showAllItems("items_id", 0, 0,
                                ($change->fields['is_recursive']?-1:$change->fields['entities_id']),
                                $types);
         echo "</td><td class='center'>";
            echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "<input type='hidden' name='changes_id' value='$instID'>";
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
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Name')."</th>";
      echo "<th>".__('Serial number')."</th>";
      echo "<th>".__('Inventory number')."</th></tr>";

      $totalnb = 0;
      for ($i=0 ; $i<$number ; $i++) {
         $itemtype = $DB->result($result, $i, "itemtype");
         if (!($item = getItemForItemtype($itemtype))) {
            continue;
         }
         if ($item->canView()) {
            $itemtable = getTableForItemType($itemtype);
            $query = "SELECT `$itemtable`.*,
                             `glpi_changes_items`.`id` AS IDD,
                             `glpi_entities`.`id` AS entity
                      FROM `glpi_changes_items`,
                           `$itemtable`";

            if ($itemtype != 'Entity') {
               $query .= " LEFT JOIN `glpi_entities`
                                 ON (`$itemtable`.`entities_id`=`glpi_entities`.`id`) ";
            }

            $query .= " WHERE `$itemtable`.`id` = `glpi_changes_items`.`items_id`
                              AND `glpi_changes_items`.`itemtype` = '$itemtype'
                              AND `glpi_changes_items`.`changes_id` = '$instID'";

            if ($item->maybeTemplate()) {
               $query .= " AND `$itemtable`.`is_template` = '0'";
            }

            $query .= getEntitiesRestrictRequest(" AND", $itemtable, '', '',
                                                 $item->maybeRecursive())."
                      ORDER BY `glpi_entities`.`completename`, `$itemtable`.`name`";

            $result_linked = $DB->query($query);
            $nb            = $DB->numrows($result_linked);

            for ($prem=true ; $data=$DB->fetch_assoc($result_linked) ; $prem=false) {
               $link     = Toolbox::getItemTypeFormURL($itemtype);
               $linkname = $data["name"];
               if ($_SESSION["glpiis_ids_visible"]
                   || empty($data["name"])) {
                  $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $data["id"]);
               }
               $name = "<a href=\"".$link."?id=".$data["id"]."\">".$linkname."</a>";

               echo "<tr class='tab_bg_1'>";
               if ($canedit) {
                  echo "<td width='10'>";
                  Html::showMassiveActionCheckBox(__CLASS__, $data["IDD"]);
                  echo "</td>";
               }
               if ($prem) {
                  $itemname = $item->getTypeName($nb);
                  echo "<td class='center top' rowspan='$nb'>".
                         ($nb>1 ? sprintf(__('%1$s: %2$s'), $itemname, $nb) : $itemname)."</td>";
               }
               echo "<td class='center'>";
               echo Dropdown::getDropdownName("glpi_entities", $data['entity'])."</td>";
               echo "<td class='center".
                      (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
               echo ">".$name."</td>";
               echo "<td class='center'>".
                      (isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
               echo "<td class='center'>".
                      (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
               echo "</tr>";
            }
            $totalnb += $nb;
         }
      }
      echo "<tr class='tab_bg_2'>";
      echo "<td class='center' colspan='2'>".
             (($totalnb > 0) ? sprintf(__('%1$s = %2$s'), __('Total'), $totalnb) : "&nbsp;");
      echo "</td><td colspan='4'>&nbsp;</td></tr> ";

      echo "</table>";
      if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'Change' :
               return _n('Item', 'Items', 2);
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Change') {
         self::showForChange($item);
      }
      return true;
   }

}
?>