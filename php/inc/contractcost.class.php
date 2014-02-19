<?php
/*
 * @version $Id: contractcost.class.php 20129 2013-02-04 16:53:59Z moyo $
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

/// ContractCost class
/// since version 0.84
class ContractCost extends CommonDBChild {

   // From CommonDBChild
   static public $itemtype = 'Contract';
   static public $items_id = 'contracts_id';
   public $dohistory       = true;


   static function getTypeName($nb=0) {
      return _n('Cost', 'Costs', $nb);
   }


   /**
    * @see CommonDBChild::prepareInputForAdd()
   **/
   function prepareInputForAdd($input) {

      if (empty($input['end_date'])
          || ($input['end_date'] == 'NULL')
          || ($input['end_date'] < $input['begin_date'])) {

         $input['end_date'] = $input['begin_date'];
      }

      return parent::prepareInputForAdd($input);
   }


   /**
    * @see CommonDBTM::prepareInputForUpdate()
   **/
   function prepareInputForUpdate($input) {

      if (empty($input['end_date'])
          || ($input['end_date'] == 'NULL')
          || ($input['end_date'] < $input['begin_date'])) {

         $input['end_date'] = $input['begin_date'];
      }

      return parent::prepareInputForUpdate($input);
   }


   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      // can exists for template
      if (($item->getType() == 'Contract')
          && Session::haveRight("contract","r")) {

         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2),
                                        countElementsInTable('glpi_contractcosts',
                                                             "contracts_id = '".$item->getID()."'"));
         }
         return self::getTypeName(2);
      }
      return '';
   }


   /**
    * @param $item            CommonGLPI object
    * @param $tabnum          (default 1)
    * @param $withtemplate    (default 0)
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      self::showForContract($item, $withtemplate);
      return true;
   }


   /**
    * Duplicate all costs from a contract template to its clone
    *
    * @since version 0.84
    *
    * @param $oldid
    * @param $newid
   **/
   static function cloneContract ($oldid, $newid) {
      global $DB;

      $query  = "SELECT *
                 FROM `glpi_contractcosts`
                 WHERE `contracts_id` = '$oldid'";
      foreach ($DB->request($query) as $data) {
         $cd                   = new self();
         unset($data['id']);
         $data['contracts_id'] = $newid;
         $data                 = Toolbox::addslashes_deep($data);
         $cd->add($data);
      }
   }


   /**
    * Init cost for creation based on previous cost
   **/
   function initBasedOnPrevious() {

      $ticket = new Ticket();
      if (!isset($this->fields['contracts_id'])
          || !$ticket->getFromDB($this->fields['contracts_id'])) {
         return false;
      }

      $lastdata = $this->getLastCostForContract($this->fields['contracts_id']);

      if (isset($lastdata['end_date'])) {
         $this->fields['begin_date'] = $lastdata['end_date'];
      }
      if (isset($lastdata['cost'])) {
         $this->fields['cost'] = $lastdata['cost'];
      }
      if (isset($lastdata['name'])) {
         $this->fields['name'] = $lastdata['name'];
      }
      if (isset($lastdata['budgets_id'])) {
         $this->fields['budgets_id'] = $lastdata['budgets_id'];
      }
   }

   /**
    * Get last datas for a contract
    *
    * @param $contracts_id        integer  ID of the contract
   **/
   function getLastCostForContract($contracts_id) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `contracts_id` = '$contracts_id'
                ORDER BY 'end_date' DESC, `id` DESC";

      if ($result = $DB->query($query)) {
         return $DB->fetch_assoc($result);
      }

      return array();
   }

   /**
    * Print the contract cost form
    *
    * @param $ID        integer  ID of the item
    * @param $options   array    options used
   **/
   function showForm($ID, $options=array()) {

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $options['contracts_id'] = $options['parent']->getField('id');
         $this->check(-1,'w',$options);
         $this->initBasedOnPrevious();
      }

      $this->showFormHeader($options);
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')."</td>";
      echo "<td>";
      echo "<input type='hidden' name='contracts_id' value='".$this->fields['contracts_id']."'>";
      Html::autocompletionTextField($this,'name');
      echo "</td>";
      echo "<td>".__('Cost')."</td>";
      echo "<td>";
      echo "<input type='text' name='cost' value='".Html::formatNumber($this->fields["cost"], true)."'
             size='14'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Begin date')."</td>";
      echo "<td>";
      Html::showDateFormItem("begin_date", $this->fields['begin_date']);
      echo "</td>";
      $rowspan = 3;
      echo "<td rowspan='$rowspan'>".__('Comments')."</td>";
      echo "<td rowspan='$rowspan' class='middle'>";
      echo "<textarea cols='45' rows='".($rowspan+3)."' name='comment' >".$this->fields["comment"].
           "</textarea>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'><td>".__('End date')."</td>";
      echo "<td>";
      Html::showDateFormItem("end_date", $this->fields['end_date']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Budget')."</td>";
      echo "<td>";
      Budget::dropdown(array('value' => $this->fields["budgets_id"]));
      echo "</td></tr>";

      $this->showFormButtons($options);

      return true;
   }


   /**
    * Print the contract costs
    *
    * @param $contract               Contract object
    * @param $withtemplate  boolean  Template or basic item (default '')
    *
    * @return Nothing (call to classes members)
   **/
   static function showForContract(Contract $contract, $withtemplate='') {
      global $DB, $CFG_GLPI;

      $ID = $contract->fields['id'];

      if (!$contract->getFromDB($ID)
          || !$contract->can($ID, "r")) {
         return false;
      }
      $canedit = $contract->can($ID, "w");

      echo "<div class='center'>";

      $query = "SELECT *
                FROM `glpi_contractcosts`
                WHERE `contracts_id` = '$ID'
                ORDER BY `begin_date`";

      $rand   = mt_rand();

      if ($canedit) {
         echo "<div id='viewcost".$ID."_$rand'></div>\n";
         echo "<script type='text/javascript' >\n";
         echo "function viewAddCost".$ID."_$rand() {\n";
         $params = array('type'         => __CLASS__,
                         'parenttype'   => 'Contract',
                         'contracts_id' => $ID,
                         'id'           => -1);
         Ajax::updateItemJsCode("viewcost".$ID."_$rand",
                                $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
         echo "};";
         echo "</script>\n";
         echo "<div class='center firstbloc'>".
               "<a class='vsubmit' href='javascript:viewAddCost".$ID."_$rand();'>";
         echo __('Add a new cost')."</a></div>\n";
      }

      if ($result = $DB->query($query)) {
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr><th colspan='5'>".self::getTypeName($DB->numrows($result))."</th></tr>";

         if ($DB->numrows($result)) {
            echo "<tr><th>".__('Name')."</th>";
            echo "<th>".__('Begin date')."</th>";
            echo "<th>".__('End date')."</th>";
            echo "<th>".__('Budget')."</th>";
            echo "<th>".__('Cost')."</th>";
            echo "</tr>";

         Session::initNavigateListItems(__CLASS__,
                              //TRANS : %1$s is the itemtype name,
                              //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                Contract::getTypeName(1), $contract->getName()));

            $total = 0;
            while ($data = $DB->fetch_assoc($result)) {
               echo "<tr class='tab_bg_2' ".
                     ($canedit
                      ? "style='cursor:pointer' onClick=\"viewEditCost".$data['contracts_id']."_".
                        $data['id']."_$rand();\"": '') .">";
               $name = (empty($data['name'])? sprintf(__('%1$s (%2$s)'),
                                                      $data['name'], $data['id'])
                                            : $data['name']);
               echo "<td>";
               printf(__('%1$s %2$s'), $name,
                        Html::showToolTip($data['comment'], array('display' => false)));
               if ($canedit) {
                  echo "\n<script type='text/javascript' >\n";
                  echo "function viewEditCost" .$data['contracts_id']."_". $data["id"]. "_$rand() {\n";
                  $params = array('type'         => __CLASS__,
                                  'parenttype'   => 'Contract',
                                  'contracts_id' => $data["contracts_id"],
                                  'id'           => $data["id"]);
                  Ajax::updateItemJsCode("viewcost".$ID."_$rand",
                                         $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
                  echo "};";
                  echo "</script>\n";
               }
               echo "</td>";
               echo "<td>".Html::convDate($data['begin_date'])."</td>";
               echo "<td>".Html::convDate($data['end_date'])."</td>";
               echo "<td>".Dropdown::getDropdownName('glpi_budgets', $data['budgets_id'])."</td>";
               echo "<td class='numeric'>".Html::formatNumber($data['cost'])."</td>";
               $total += $data['cost'];
               echo "</tr>";
               Session::addToNavigateListItems(__CLASS__, $data['id']);
            }
            echo "<tr class='b'><td colspan='3'>&nbsp;</td>";
            echo "<td class='right'>".__('Total cost').'</td>';
            echo "<td class='numeric'>".Html::formatNumber($total).'</td></tr>';
         } else {
            echo "<tr><th colspan='5'>".__('No item found')."</th></tr>";
         }
         echo "</table>";
      }
      echo "</div><br>";
   }

}
?>
