<?php
/*
 * @version $Id: contract_supplier.class.php 22657 2014-02-12 16:17:54Z moyo $
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

// Relation between Contracts and Suppliers
class Contract_Supplier extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1 = 'Contract';
   static public $items_id_1 = 'contracts_id';

   static public $itemtype_2 = 'Supplier';
   static public $items_id_2 = 'suppliers_id';



   /**
    * @since version 0.84
   **/
   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }


   /**
    * @param $item   string   Supplier object
   **/
   static function countForSupplier(Supplier $item) {

      $restrict = "`glpi_contracts_suppliers`.`suppliers_id` = '".$item->getField('id') ."'
                    AND `glpi_contracts_suppliers`.`contracts_id` = `glpi_contracts`.`id` ".
                    getEntitiesRestrictRequest(" AND ", "glpi_contracts", '',
                                               $_SESSION['glpiactiveentities']);

      return countElementsInTable(array('glpi_contracts_suppliers', 'glpi_contracts'), $restrict);
   }


   /**
    * @param $item   string   Contract object
   **/
   static function countForContract(Contract $item) {

      $restrict = "`glpi_contracts_suppliers`.`contracts_id` = '".$item->getField('id') ."'
                    AND `glpi_contracts_suppliers`.`suppliers_id` = `glpi_suppliers`.`id` ".
                    getEntitiesRestrictRequest(" AND ", "glpi_suppliers", '',
                                               $_SESSION['glpiactiveentities'], true);

      return countElementsInTable(array('glpi_contracts_suppliers', 'glpi_suppliers'), $restrict);
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'Supplier' :
               if (Session::haveRight("contract","r")) {
                  if ($_SESSION['glpishow_count_on_tabs']) {
                     return self::createTabEntry(Contract::getTypeName(2),
                                                 self::countForSupplier($item));
                  }
                  return Contract::getTypeName(2);
               }
               break;

            case 'Contract' :
               if (Session::haveRight("contact_enterprise","r")) {
                  if ($_SESSION['glpishow_count_on_tabs']) {
                     return self::createTabEntry(Supplier::getTypeName(2),
                                                 self::countForContract($item));
                  }
                  return Supplier::getTypeName(2);
               }
               break;
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'Supplier' :
            self::showForSupplier($item);
            break;

         case 'Contract' :
            self::showForContract($item);
            break;
      }
      return true;
   }


   /**
    * Print an HTML array with contracts associated to the enterprise
    *
    * @since version 0.84
    *
    * @param $supplier   Supplier object
    *
    * @return Nothing (display)
   **/
   static function showForSupplier(Supplier $supplier) {
      global $DB, $CFG_GLPI;

      $ID = $supplier->fields['id'];
      if (!Session::haveRight("contract","r")
          || !$supplier->can($ID,'r')) {
         return false;
      }
      $canedit = $supplier->can($ID,'w');
      $rand    = mt_rand();

      $query = "SELECT `glpi_contracts`.*,
                       `glpi_contracts_suppliers`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity
                FROM `glpi_contracts_suppliers`, `glpi_contracts`
                LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id`=`glpi_contracts`.`entities_id`)
                WHERE `glpi_contracts_suppliers`.`suppliers_id` = '$ID'
                      AND `glpi_contracts_suppliers`.`contracts_id`=`glpi_contracts`.`id`".
                      getEntitiesRestrictRequest(" AND", "glpi_contracts", '', '', true)."
                ORDER BY `glpi_entities`.`completename`,
                         `glpi_contracts`.`name`";

      $result    = $DB->query($query);
      $contracts = array();
      $used      = array();
      if ($number = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $contracts[$data['assocID']] = $data;
            $used[$data['id']]           = $data['id'];
         }
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='contractsupplier_form$rand' id='contractsupplier_form$rand' method='post'
                action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
         echo "<input type='hidden' name='suppliers_id' value='$ID'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Add a contract')."</th></tr>";

         echo "<tr class='tab_bg_1'><td class='right'>";
         Contract::dropdown(array('used'         => $used,
                                  'entity'       => $supplier->fields["entities_id"],
                                  'entity_sons'  => $supplier->fields["is_recursive"],
                                  'nochecklimit' => true));

         echo "</td><td class='center'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('num_displayed' => $number);
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      if ($canedit && $number) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }
      echo "<th>".__('Name')."</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>"._x('phone', 'Number')."</th>";
      echo "<th>".__('Contract type')."</th>";
      echo "<th>".__('Start date')."</th>";
      echo "<th>".__('Initial contract period')."</th>";
      echo "</tr>";

      $used = array();
      foreach ($contracts as $data) {
         $cID        = $data["id"];
         $used[$cID] = $cID;
         $assocID    = $data["assocID"];

         echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
         if ($canedit) {
            echo "<td>";
            Html::showMassiveActionCheckBox(__CLASS__, $data["assocID"]);
            echo "</td>";
         }
         $name = $data["name"];
         if ($_SESSION["glpiis_ids_visible"]
             || empty($data["name"])) {
            $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
         }
         echo "<td class='center b'>
               <a href='".$CFG_GLPI["root_doc"]."/front/contract.form.php?id=$cID'>".$name."</a>";
         echo "</td>";
         echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data["entity"]);
         echo "</td><td class='center'>".$data["num"]."</td>";
         echo "<td class='center'>".
                Dropdown::getDropdownName("glpi_contracttypes",$data["contracttypes_id"])."</td>";
         echo "<td class='center'>".Html::convDate($data["begin_date"])."</td>";
         echo "<td class='center'>";
         sprintf(_n('%d month', '%d months', $data["duration"]), $data["duration"]);

         if (($data["begin_date"] != '') && !empty($data["begin_date"])) {
            echo " -> ".Infocom::getWarrantyExpir($data["begin_date"], $data["duration"], 0, true);
         }
         echo "</td>";
         echo "</tr>";
      }

      echo "</table>";
      if ($canedit && $number) {
         $massiveactionparams['ontop'] =false;
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }


   /**
    * Print the HTML array of suppliers for this contract
    *
    * @since version 0.84
    *
    * @param $contract Contract object
    *
    * @return Nothing (HTML display)
    **/
   static function showForContract(Contract $contract) {
      global $DB, $CFG_GLPI;

      $instID = $contract->fields['id'];

      if (!$contract->can($instID,'r')
          || !Session::haveRight("contact_enterprise","r")) {
         return false;
      }
      $canedit = $contract->can($instID,'w');
      $rand    = mt_rand();

      $query = "SELECT `glpi_contracts_suppliers`.`id`,
                       `glpi_suppliers`.`id` AS entID,
                       `glpi_suppliers`.`name` AS name,
                       `glpi_suppliers`.`website` AS website,
                       `glpi_suppliers`.`phonenumber` AS phone,
                       `glpi_suppliers`.`suppliertypes_id` AS type,
                       `glpi_entities`.`id` AS entity
                FROM `glpi_contracts_suppliers`,
                     `glpi_suppliers`
                LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id`=`glpi_suppliers`.`entities_id`)
                WHERE `glpi_contracts_suppliers`.`contracts_id` = '$instID'
                      AND `glpi_contracts_suppliers`.`suppliers_id`=`glpi_suppliers`.`id`".
                      getEntitiesRestrictRequest(" AND","glpi_suppliers",'','',true). "
                ORDER BY `glpi_entities`.`completename`, `name`";

      $result    = $DB->query($query);
      $suppliers = array();
      $used      = array();
      if ($number = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $suppliers[$data['id']] = $data;
            $used[$data['entID']]   = $data['entID'];
         }
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='contractsupplier_form$rand' id='contractsupplier_form$rand' method='post'
                action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
         echo "<input type='hidden' name='contracts_id' value='$instID'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Add a supplier')."</th></tr>";

         echo "<tr class='tab_bg_1'><td class='right'>";

         Supplier::dropdown(array('used'         => $used,
                                  'entity'       => $contract->fields["entities_id"],
                                  'entity_sons'  => $contract->fields["is_recursive"]));
         echo "</td><td class='center'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('num_displayed' => $number);
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      if ($canedit && $number) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }
      echo "<th>".__('Supplier')."</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Third party type')."</th>";
      echo "<th>".__('Phone')."</th>";
      echo "<th>".__('Website')."</th>";
      echo "</tr>";

      $used = array();
      foreach ($suppliers as $data) {
         $ID      = $data['id'];
         $website = $data['website'];
         if (!empty($website)) {
            if (!preg_match("?https*://?",$website)) {
               $website = "http://".$website;
            }
            $website = "<a target=_blank href='$website'>".$data['website']."</a>";
         }
         $entID         = $data['entID'];
         $entity        = $data['entity'];
         $used[$entID]  = $entID;
         $entname       = Dropdown::getDropdownName("glpi_suppliers", $entID);
         echo "<tr class='tab_bg_1'>";
         if ($canedit) {
            echo "<td>";
            Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
            echo "</td>";
         }
         echo "<td class='center'>";
         if ($_SESSION["glpiis_ids_visible"]
             || empty($entname)) {
            $entname = sprintf(__('%1$s (%2$s)'), $entname, $entID);
         }
         echo "<a href='".$CFG_GLPI["root_doc"]."/front/supplier.form.php?id=$entID'>".$entname;
         echo "</a></td>";
         echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$entity)."</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName("glpi_suppliertypes", $data['type'])."</td>";
         echo "<td class='center'>".$data['phone']."</td>";
         echo "<td class='center'>".$website."</td>";
         echo "</tr>";
      }
      echo "</table>";
      if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }

}
?>
