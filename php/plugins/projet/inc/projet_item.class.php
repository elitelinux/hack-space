<?php
/*
 * @version $Id: HEADER 15930 2012-03-08 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Projet plugin for GLPI
 Copyright (C) 2003-2012 by the Projet Development Team.

 https://forge.indepnet.net/projects/projet
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Projet.

 Projet is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Projet is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Projet. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginProjetProjet_Item extends CommonDBTM {

   // From CommonDBRelation
   static public $itemtype_1 = "PluginProjetProjet";
   static public $items_id_1 = 'plugin_projet_projets_id';

   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';
   
   static function canCreate() {
      return plugin_projet_haveRight('projet', 'w');
   }

   static function canView() {
      return plugin_projet_haveRight('projet', 'r');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='PluginProjetProjet'
          && count(PluginProjetProjet::getTypes(false))) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(_n('Associated item', 'Associated items', 2), self::countForProjet($item, true));
         }
         return _n('Associated item', 'Associated items', 2);

      } else if (in_array($item->getType(), PluginProjetProjet::getTypes(true))
                 && $this->canView() && !$withtemplate) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(PluginProjetProjet::getTypeName(2), self::countForItem($item));
         }
         return PluginProjetProjet::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      if ($item->getType()=='PluginProjetProjet') {
         self::showForProjet($item, $withtemplate, true);
         
      } else if (in_array($item->getType(), PluginProjetProjet::getTypes(true))) {
         
         switch (get_class($item)) {
         case 'User' :
         case 'Group' :
            PluginProjetProjet::showUsers($item);
            self::showForItem($item);
            break;
         case 'Ticket' :
         case 'Problem' :
            self::showForHelpdesk($item);
            break;
         default :
            if (in_array(get_class($item), PluginProjetProjet::getTypes())) {
               self::showForItem($item);
            }
            break;
         }
      }
      return true;
   }
   
   static function countForProjet(PluginProjetProjet $item, $material = false) {

      $types = implode("','", PluginProjetProjet::getTypes());
      if (empty($types)) {
         return 0;
      }
      
      if ($material) {
            $select = "'User','Group','Supplier','Contract'";
            $in = "NOT IN";
      } else {
         $select = "'User','Group','Supplier'";
         $in = "IN";
      }

      return countElementsInTable('glpi_plugin_projet_projets_items',
                                  "`itemtype` IN ('$types')
                                   AND `plugin_projet_projets_id` = '".$item->getID()."' 
                                   AND `itemtype` $in (".$select.")");
   }


   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_projet_projets_items',
                                  "`itemtype`='".$item->getType()."'
                                   AND `items_id` = '".$item->getID()."'");
   }
   
   /**
    * Check if user is in project
    * 
    * @param unknown $id : user's id
    * @return boolean : true if is in, false otherwhise
    */
   static function isProjetParticipant($id) {
      
      $items = new self();
      $userId = Session::getLoginUserID(); 
      
      $groupUser = new Group_User();
      $groups = $groupUser->find("`users_id` = ".$userId);
      
      if ($items->getFromDBbyProjetAndItem($id, $userId, "User") ) {
         return true;
      } else {
         if (!empty($groups)){
            foreach ($groups as $grp ){
               if ($items->getFromDBbyProjetAndItem($id, $grp['groups_id'], "Group")){
                  return true;
               }
            }
         }
      }
      return false;
   }
   
   function getFromDBbyProjetAndItem($plugin_projet_projets_id,$items_id,$itemtype) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."` " .
         "WHERE `plugin_projet_projets_id` = '" . $plugin_projet_projets_id . "' 
         AND `itemtype` = '" . $itemtype . "'
         AND `items_id` = '" . $items_id . "'";
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
   
   function addItem($options) {
      
      if (!isset($options["plugin_projet_projets_id"]) 
            || $options["plugin_projet_projets_id"] <= 0) {
         return false;
      } else {
         $this->add(array('plugin_projet_projets_id'=>$options["plugin_projet_projets_id"],
                           'items_id'=>$options["items_id"],
                           'itemtype'=>$options["itemtype"]));

      }
   }
   
   function deleteItemByProjetAndItem($plugin_projet_projets_id,$items_id,$itemtype) {
    
      if ($this->getFromDBbyProjetAndItem($plugin_projet_projets_id,$items_id,$itemtype)) {
         $this->delete(array('id'=>$this->fields["id"]));
      }
   }
   
   /**
    * Duplicate item projects from an item template to its clone
    *
    * @since version 0.84
    *
    * @param $itemtype     itemtype of the item
    * @param $oldid        ID of the item to clone
    * @param $newid        ID of the item cloned
    * @param $newitemtype  itemtype of the new item (= $itemtype if empty) (default '')
   **/
   static function cloneItem($oldid, $newid) {
      global $DB;

      $query  = "SELECT `itemtype`, `items_id`
                 FROM `glpi_plugin_projet_projets_items`
                 WHERE `plugin_projet_projets_id` = '$oldid';";

      foreach ($DB->request($query) as $data) {
         $projet_item = new self();
         $projet_item->add(array('plugin_projet_projets_id'   => $newid,
                             'itemtype'                        => $data["itemtype"],
                             'items_id'                        => $data["items_id"]));
      }
   }
   
   /**
    * @since version 0.84
   **/
   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }
   
   /**
    * Show items links to a projet
    *
    * @since version 0.84
    *
    * @param $projet PluginProjetProjet object
    *
    * @return nothing (HTML display)
    **/
   public static function showForProjet(PluginProjetProjet $projet, $withtemplate='', $material=false) {
      global $DB;

      $instID = $projet->fields['id'];
      if (!$projet->can($instID,'r'))   return false;
      
      $rand=mt_rand();

      $canedit=$projet->can($instID,'w');
      if (empty($withtemplate)) {
         $withtemplate = 0;
      }
      
      if ($material) {
         $select = "'User','Group','Supplier','Contract'";
         $in = "NOT IN";
      } else {
         $select = "'User','Group','Supplier'";
         $in = "IN";
      }
      $query = "SELECT DISTINCT `itemtype` 
          FROM `glpi_plugin_projet_projets_items` 
          WHERE `plugin_projet_projets_id` = '$instID' 
          AND `itemtype` $in (".$select.")
          ORDER BY `itemtype` 
          LIMIT ".count(PluginProjetProjet::getTypes(true));
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }

      if ($canedit && $withtemplate < 2) {
         echo "<div class='firstbloc'>";
         echo "<form method='post' name='projet_form$rand' id='projet_form$rand'
         action='".Toolbox::getItemTypeFormURL("PluginProjetProjet")."'>";

         echo "<table class='tab_cadre_fixe'>";
         if ($material) {
            echo "<tr class='tab_bg_2'><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".
            __('Add an item')."</th></tr>";
         } else {
            echo "<tr class='tab_bg_2'><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".
            __('Add a participant', 'projet')."</th></tr>";
         }
         echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";
         echo "<input type='hidden' name='plugin_projet_projets_id' value='$instID'>";
         if ($material) {
            $types=PluginProjetProjet::getTypes();
            $trans= array_flip($types);
            unset($trans['User']);
            unset($trans['Group']);
            unset($trans['Supplier']);
            unset($trans['Contract']);
            $types=array_flip($trans);

         } else {
            $types[]='User';
            $types[]='Group';
            $types[]='Supplier';
         }
         Dropdown::showAllItems("items_id",0,0,($projet->fields['is_recursive']?-1:$projet->fields['entities_id']),$types);
         echo "</td>";
         echo "<td colspan='2' class='tab_bg_2'>";
         echo "<input type='submit' name='additem' value=\""._sx('button','Add')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table>" ;
         Html::closeForm();
         echo "</div>" ;
      }
      
      echo "<div class='spaced'>";
      if ($canedit && $number && $withtemplate < 2) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array();
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";

      if ($canedit && $number && $withtemplate < 2) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }

      echo "<th>".__('Type')."</th>";
      echo "<th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>".__('Entity')."</th>";
      }
      if ($material) {
         echo "<th>".__('Serial Number')."</th>";
         echo "<th>".__('Inventory number')."</th>";
      }
      echo "</tr>";

      for ($i=0 ; $i < $number ; $i++) {
         $itemType=$DB->result($result, $i, "itemtype");

         if (!($item = getItemForItemtype($itemType))) {
            continue;
         }

         if ($item->canView()) {
            $column="name";
            $itemTable = getTableForItemType($itemType);

            $query = "SELECT `".$itemTable."`.*,
                             `glpi_plugin_projet_projets_items`.`id` AS items_id,
                             `glpi_entities`.`id` AS entity "
               ." FROM `glpi_plugin_projet_projets_items`, `".$itemTable
               ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$itemTable."`.`entities_id`) "
               ." WHERE `".$itemTable."`.`id` = `glpi_plugin_projet_projets_items`.`items_id`
                AND `glpi_plugin_projet_projets_items`.`itemtype` = '$itemType'
                AND `glpi_plugin_projet_projets_items`.`plugin_projet_projets_id` = '$instID' ";
            if ($itemType != 'User') {
               $query.=getEntitiesRestrictRequest(" AND ",$itemTable,'','',$item->maybeRecursive());
            }
            if ($item->maybeTemplate()) {
               $query.=" AND `".$itemTable."`.`is_template` = '0'";
            }
            $query.=" ORDER BY `glpi_entities`.`completename`, `".$itemTable."`.`$column`";

            if ($result_linked=$DB->query($query)) {
               if ($DB->numrows($result_linked)) {

                  Session::initNavigateListItems($itemType,PluginProjetProjet::getTypeName(2)." = ".$projet->fields['name']);

                  while ($data=$DB->fetch_assoc($result_linked)) {

                     $item->getFromDB($data["id"]);

                     Session::addToNavigateListItems($itemType,$data["id"]);

                     $ID="";
                     
                     if ($itemType=='User') {
                        $format=formatUserName($data["id"],$data["name"],$data["realname"],$data["firstname"],1);
                     } else {
                        $format=$data["name"];
                     }
                     if ($_SESSION["glpiis_ids_visible"]||empty($data["name"]))
                        $ID= " (".$data["id"].")";

                     $link=Toolbox::getItemTypeFormURL($itemType);
                     $name= "<a href=\"".$link."?id=".$data["id"]."\">"
                        .$format;
                     if ($itemType!='User') {
                           $name.= "&nbsp;".$ID;
                     }
                     $name.= "</a>";

                     echo "<tr class='tab_bg_1'>";

                     if ($canedit && $withtemplate < 2) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(__CLASS__, $data["items_id"]);
                        echo "</td>";
                     }
                     echo "<td class='center'>".$item::getTypeName(1)."</td>";

                     echo "<td class='center' ".(isset($data['is_deleted'])&&$data['is_deleted']?"class='tab_bg_2_2'":"").
                        ">".$name."</td>";

                     if (Session::isMultiEntitiesMode()) {
                        if ($itemType!='User') {
                           echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entity'])."</td>";
                        } else {
                           echo "<td class='center'>-</td>";
                        }
                     }
                     if ($material) {
                        echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
                        echo "<td class='center'>".(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
                     }

                     echo "</tr>";
                  }
               }
            }
         }
      }
      echo "</table>";

      if ($canedit && $number && $withtemplate < 2) {
         $paramsma['ontop'] =false;
         Html::showMassiveActions(__CLASS__, $paramsma);
         Html::closeForm();
      }
      echo "</div>";
   }
   
   /**
   * Show projet associated to an item
   *
   * @since version 0.84
   *
   * @param $item            CommonDBTM object for which associated projet must be displayed
   * @param $withtemplate    (default '')
   **/
   static function showForItem(CommonDBTM $item, $withtemplate='') {
      global $DB, $CFG_GLPI;

      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!plugin_projet_haveRight('projet', 'r')) {
         return false;
      }

      if (!$item->can($item->fields['id'],'r')) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }

      $canedit       = $item->canadditem('PluginProjetProjet');
      $rand          = mt_rand();
      $is_recursive  = $item->isRecursive();

      $query = "SELECT `glpi_plugin_projet_projets_items`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity,
                       `glpi_plugin_projet_projets`.`name` AS assocName,
                       `glpi_plugin_projet_projets`.*
                FROM `glpi_plugin_projet_projets_items`
                LEFT JOIN `glpi_plugin_projet_projets`
                 ON (`glpi_plugin_projet_projets_items`.`plugin_projet_projets_id`=`glpi_plugin_projet_projets`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_plugin_projet_projets`.`entities_id`=`glpi_entities`.`id`)
                WHERE `glpi_plugin_projet_projets_items`.`items_id` = '$ID'
                      AND `glpi_plugin_projet_projets_items`.`itemtype` = '".$item->getType()."' ";

      $query .= getEntitiesRestrictRequest(" AND","glpi_plugin_projet_projets",'','',true);

      $query .= " ORDER BY `assocName`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;

      $projets    = array();
      $used       = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $projets[$data['assocID']] = $data;
            $used[$data['id']] = $data['id'];
         }
      }
      $projet = new PluginProjetProjet();
      if ($canedit && $withtemplate < 2) {
         // Restrict entity for knowbase
         $entities = "";
         $entity   = $_SESSION["glpiactive_entity"];

         if ($item->isEntityAssign()) {
            /// Case of personal items : entity = -1 : create on active entity (Reminder case))
            if ($item->getEntityID() >=0 ) {
               $entity = $item->getEntityID();
            }

            if ($item->isRecursive()) {
               $entities = getSonsOf('glpi_entities',$entity);
            } else {
               $entities = $entity;
            }
         }
         $limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_projets",'',$entities,true);
         $q = "SELECT COUNT(*)
               FROM `glpi_plugin_projet_projets`
               WHERE `is_deleted` = '0'
               AND `is_template` = '0' ";
         if ($item->getType() != 'User') {
            $q.=" $limit";
         }
         $result = $DB->query($q);
         $nb     = $DB->result($result,0,0);

         echo "<div class='firstbloc'>";       
         
         
         if (plugin_projet_haveRight('projet', 'r')
             && ($nb > count($used))) {
            echo "<form name='projet_form$rand' id='projet_form$rand' method='post'
                   action='".Toolbox::getItemTypeFormURL('PluginProjetProjet')."'>";
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
            if ($item->getType() !='User') {
               $projet->dropdownProjet("plugin_projet_projets_id",$entities, $used);
            } else {
               $strict_entities=Profile_User::getUserEntities($ID,true);
               if (!Session::haveAccessToOneOfEntities($strict_entities)&&!isViewAllEntities()) {
                  $canedit=false;
               }
 
               if (countElementsInTableForEntity("glpi_plugin_projet_projets",$strict_entities) > count($used)) {
                  
                  Dropdown::show('PluginProjetProjet', array('name' => "plugin_projet_projets_id",
                                                               'used' => $used,
                                                               'entity' => $strict_entities));
               }
            
            }
            echo "</td><td class='center' width='20%'>";
            echo "<input type='submit' name='additem' value=\"".
                     _sx('button', 'Associate a project', 'projet')."\" class='submit'>";
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
         $massiveactionparams = array('num_displayed'  => $number);
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }
      if ($item->getType() == "Group" || $item->getType() == "User") {
         echo "<tr><th colspan='".(7+$colsup)."'>"._n('Associated project', 'Associated projects', 2, 'projet').":</th></tr>";
      }
      echo "<tr>";
      if ($canedit && $number && ($withtemplate < 2)) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }
      echo "<th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>".__('Entity')."</th>";
      }
      echo "<th>".__('Description')."</th>";
      echo "<th>".__('Progress')."</th>";
      echo "<th>".__('Start date')."</th>";
      echo "<th>".__('End date')."</th>";
      echo "</tr>";
      $used = array();

      if ($number) {

         Session::initNavigateListItems('PluginProjetProjet',
                           //TRANS : %1$s is the itemtype name,
                           //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                $item->getTypeName(1), $item->getName()));

         
         foreach  ($projets as $data) {
            $projetID     = $data["id"];
            $link         = NOT_AVAILABLE;

            if ($projet->getFromDB($projetID)) {
               $link         = $projet->getLink();
            }

            Session::addToNavigateListItems('PluginProjetProjet', $projetID);
            
            $used[$projetID]  = $projetID;
            $assocID          = $data["assocID"];

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
            echo "<td align='center'>".Html::resume_text($data["description"], 250)."</td>";
            echo "<td align='center'>".PluginProjetProjet::displayProgressBar('100',$data["advance"])."</td>";

            echo "<td class='center'>".Html::convdate($data["date_begin"])."</td>";
            if ($data["date_end"] <= date('Y-m-d') && !empty($data["date_end"])) {
               echo "<td class='center'><span class='red'>".Html::convdate($data["date_end"])."</span></td>";
            } else {
               echo "<td class='center'><span class='green'>".Html::convdate($data["date_end"])."</span></td>";
            }
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
   
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType()=='PluginProjetProjet') {
         self::pdfForProjet($pdf, $item, true);

      } else if (in_array($item->getType(), PluginProjetProjet::getTypes(true))) {
         self::PdfFromItems($pdf, $item);

      } else {
         return false;
      }
      return true;
   }
   
   /**
    * Show for PDF an projet - associated devices
    * 
    * @param $pdf object for the output
    * @param $appli object of the projet
    */
   static function pdfForProjet(PluginPdfSimplePDF $pdf, PluginProjetProjet $appli, $material=false) {
      global $DB,$CFG_GLPI;
      
      $ID = $appli->fields['id'];

      if (!$appli->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_projet_haveRight("projet","r")) {
         return false;
      }

      $pdf->setColumnsSize(100);
      if ($material) {
         $pdf->displayTitle('<b>'._n('Associated item', 'Associated items', 2).'</b>');
      } else {
         $pdf->displayTitle('<b>'.__('Unknown project task', 'projet').'</b>');
      }
      
      if ($material) {
         $select = "'User','Group','Supplier','Contract'";
         $in = "NOT IN";
      } else {
         $select = "'User','Group','Supplier'";
         $in = "IN";
      }
      
      $query = "SELECT DISTINCT `itemtype` 
               FROM `glpi_plugin_projet_projets_items` 
               WHERE `plugin_projet_projets_id` = '$ID' 
               AND `itemtype` $in (".$select.")
               ORDER BY `itemtype` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $pdf->setColumnsSize(12,27,25,18,18);
         $pdf->displayTitle(
            '<b><i>'._n('Type', 'Types', 2),
            __('Name'),
            __('Entity'),
            __('Serial number'),
            __('Inventory number').'</i></b>'
            );
      } else {
         $pdf->setColumnsSize(25,31,22,22);
         $pdf->displayTitle(
            '<b><i>'._n('Type', 'Types', 2),
            __('Name'),
            __('Serial number'),
            __('Inventory number').'</i></b>'
            );
      }

      if (!$number) {
         $pdf->displayLine(__('No item found'));						
      } else { 
         for ($i=0 ; $i < $number ; $i++) {
            $type=$DB->result($result, $i, "itemtype");
            if (!($item = getItemForItemtype($type))) {
               continue;
            }
            if ($item->canView()) {
               $column="name";
               $table = getTableForItemType($type);
               $items = new $type();
               
               $query = "SELECT `".$table."`.*, `glpi_entities`.`id` AS entity "
               ." FROM `glpi_plugin_projet_projets_items`, `".$table
               ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table."`.`entities_id`) "
               ." WHERE `".$table."`.`id` = `glpi_plugin_projet_projets_items`.`items_id` 
                  AND `glpi_plugin_projet_projets_items`.`itemtype` = '$type' 
                  AND `glpi_plugin_projet_projets_items`.`plugin_projet_projets_id` = '$ID' ";
               if ($type!='User')
                  $query.= getEntitiesRestrictRequest(" AND ",$table,'','',$items->maybeRecursive()); 

               if ($items->maybeTemplate()) {
                  $query.=" AND `".$table."`.`is_template` = '0'";
               }
               $query.=" ORDER BY `glpi_entities`.`completename`, `".$table."`.`$column`";
               
               if ($result_linked=$DB->query($query))
                  if ($DB->numrows($result_linked)) {
                     
                     while ($data=$DB->fetch_assoc($result_linked)) {
                        if (!$items->getFromDB($data["id"])) {
                           continue;
                        }
                         $items_id_display="";

                        if ($_SESSION["glpiis_ids_visible"]||empty($data["name"])) $items_id_display= " (".$data["id"].")";
                           if ($type=='User')
                              $name=Html::clean(getUserName($data["id"])).$items_id_display;
                           else
                              $name=$data["name"].$items_id_display;
                        
                        if ($type!='User') {
                              $entity=Html::clean(Dropdown::getDropdownName("glpi_entities",$data['entity']));
                           } else {
                              $entity="-";
                           }
                           
                        if (Session::isMultiEntitiesMode()) {
                           $pdf->setColumnsSize(12,27,25,18,18);
                           $pdf->displayLine(
                              $items->getTypeName(),
                              $name,
                              $entity,
                              (isset($data["serial"])? "".$data["serial"]."" :"-"),
                              (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")
                              );
                        } else {
                           $pdf->setColumnsSize(25,31,22,22);
                           $pdf->displayTitle(
                              $items->getTypeName(),
                              $name,
                              (isset($data["serial"])? "".$data["serial"]."" :"-"),
                              (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")
                              );
                        }
                     } // Each device
                  } // numrows device
            } // type right
         } // each type
      }
      $pdf->displaySpace(); // numrows type
   }
   
   /** 
    * show for PDF the projet associated with a device
    * 
    * @param $ID of the device
    * @param $itemtype : type of the device
    * 
    */
   static function PdfFromItems($pdf, $item) {
      global $DB,$CFG_GLPI;
      
      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'._n('Associated project', 'Associated projects', 2, 'projet').'</b>');
      
      $ID = $item->getField('id');
      $itemtype = get_Class($item);
      $canread = $item->can($ID,'r');
      $canedit = $item->can($ID,'w');
      
      $PluginProjetProjet=new PluginProjetProjet(); 
      
      $query = "SELECT `glpi_plugin_projet_projets`.* "
      ." FROM `glpi_plugin_projet_projets_items`,`glpi_plugin_projet_projets` "
      ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_projet_projets`.`entities_id`) "
      ." WHERE `glpi_plugin_projet_projets_items`.`items_id` = '".$ID."' 
         AND `glpi_plugin_projet_projets_items`.`itemtype` = '".$itemtype."' 
         AND `glpi_plugin_projet_projets_items`.`plugin_projet_projets_id` = `glpi_plugin_projet_projets`.`id` "
      . getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_projets",'','',$PluginProjetProjet->maybeRecursive());
      
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (!$number) {
         $pdf->displayLine(__('No item found'));
      } else {
         if (Session::isMultiEntitiesMode()) {
            $pdf->setColumnsSize(14,14,14,14,14,14,16);
            $pdf->displayTitle(
               '<b><i>'.__('Name'),
               __('Entity'),
               __('Comments'),
               __('Description'),
               __('Progress'),
               __('Start date'),
               __('End date').'</i></b>'
               );
         } else {
            $pdf->setColumnsSize(17,17,17,17,17,17);
            $pdf->displayTitle(
               '<b><i>'.__('Name'),
               __('Comments'),
               __('Description'),
               __('Progress'),
               __('Start date'),
               __('End date').'</i></b>'
               );
         }
         while ($data=$DB->fetch_array($result)) {
      
            if (Session::isMultiEntitiesMode()) {
               $pdf->setColumnsSize(14,14,14,14,14,14,16);
               $pdf->displayLine(
                  $data["name"],
                  Html::clean(Dropdown::getDropdownName("glpi_entities",$data['entities_id'])),
                  $data["comment"],
                  $data["description"],
                  PluginProjetProjet::displayProgressBar('100',$data["advance"],array("simple"=>true)),
                  Html::convdate($data["date_begin"]),
                  Html::convdate($data["date_end"])
                  );
            } else {
               $pdf->setColumnsSize(17,17,17,17,17,17);
               $pdf->displayLine(
                  $data["name"],
                  $data["comment"],
                  $data["description"],
                  PluginProjetProjet::displayProgressBar('100',$data["advance"],array("simple"=>true)),
                  Html::convdate($data["date_begin"]),
                  Html::convdate($data["date_end"])
                  );
            }
         }		
      }
   }
   
   /**
    * Show projet for a ticket / problem
    *
    * @param $item Ticket or Problem object
   **/
   static function showForHelpdesk($item) {
      global $DB, $CFG_GLPI;

      $ID = $item->getField('id');
      if (!$item->can($ID,'r')) {
         return false;
      }

      $canedit = $item->can($ID,'w');

      $rand = mt_rand();
      echo "<form name='projetlink_form$rand' id='projetlink_form$rand' method='post'
             action='";
      echo Toolbox::getItemTypeFormURL("PluginProjetProjet")."'>";

      echo "<div class='center'><table class='tab_cadre_fixehov'>";
      echo "<tr><th colspan='7'>"._n('Project', 'Projects', 2, 'projet')."&nbsp;-&nbsp;";
      echo "<a href='".Toolbox::getItemTypeFormURL('PluginProjetProjet').
               "?helpdesk_id=$ID&helpdesk_itemtype=".$item->gettype()."'>";
      
      $nav = "";
      if ( $item->gettype() == "Ticket") {
         _e('Create a project from this ticket', 'projet');
         $nav = _n('Ticket', 'Tickets', 2);
      } else {
         _e('Create a project from this problem', 'projet');
         $nav = _n('Problem', 'Problems', 2);
      }
      echo "</a>";
      echo "</th></tr>";

      $query = "SELECT `glpi_plugin_projet_projets_items`.`id` AS items_id,
                     `glpi_plugin_projet_projets`.* "
       ." FROM `glpi_plugin_projet_projets_items`,`glpi_plugin_projet_projets` "
       ." LEFT JOIN `glpi_entities` 
         ON (`glpi_entities`.`id` = `glpi_plugin_projet_projets`.`entities_id`) "
       ." WHERE `glpi_plugin_projet_projets_items`.`items_id` = '".$ID."' 
       AND `glpi_plugin_projet_projets_items`.`itemtype` = '".$item->gettype()."' 
       AND `glpi_plugin_projet_projets_items`.`plugin_projet_projets_id` = `glpi_plugin_projet_projets`.`id`  
       AND `glpi_plugin_projet_projets`.`is_template` = '0' ";
       $query.= "ORDER BY `glpi_plugin_projet_projets`.`name`";
       
      $result = $DB->query($query);

      $used = array();

      if ($DB->numrows($result) >0) {
      
         PluginProjetProjet::commonListHeader(Search::HTML_OUTPUT, $canedit); 
         Session::initNavigateListItems('PluginProjetProjet',
                                        $nav ." = ". $item->fields["name"]);
         $i=0;
         while ($data = $DB->fetch_array($result)) {
            $used[$data['id']] = $data['id'];
            Session::addToNavigateListItems('PluginProjetProjet', $data["id"]);
            
            
            echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";

            if ($canedit 
                  && (in_array($data['entities_id'],$_SESSION['glpiactiveentities']) 
                     || $data["is_recursive"])) {
               echo "<td class='center'>";
               echo "<a href='".$CFG_GLPI["root_doc"].
                     "/plugins/projet/front/projet.form.php?id=".$data["id"]."'>".$data["name"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
               echo "</a></td>";
            } else {
               echo "<td class='center'>".$data["name"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
               echo "</td>";
            }
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>";
               echo Dropdown::getDropdownName("glpi_entities",$data['entities_id']);
               echo "</td>";
            }
            echo "<td align='center'>".$data["description"]."</td>";
            echo "<td align='center'>";
            echo PluginProjetProjet::displayProgressBar('100',$data["advance"]);
            echo "</td>";

            echo "<td class='center'>".Html::convdate($data["date_begin"])."</td>";
            if ($data["date_end"] <= date('Y-m-d') && !empty($data["date_end"])) {
               echo "<td class='center'>";
               echo "<span class='red'>".Html::convdate($data["date_end"])."</span></td>";
            } else {
               echo "<td class='center'>";
               echo "<span class='green'>".Html::convdate($data["date_end"])."</span></td>";
            }
            if ($canedit) {
               echo "<td class='center tab_bg_2'>";
               Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/projet/front/projet.form.php',
                                       'deletedevice',
                                       _x('button','Delete permanently'),
                                       array('id' => $data['items_id']));
               echo "</td>";
            }
            echo "</tr>";
         
            $i++;
         }
      }

      if ($canedit) {
         echo "<tr class='tab_bg_2'><td class='right'  colspan='6'>";
         echo "<input type='hidden' name='items_id' value='$ID'>";
         echo "<input type='hidden' name='itemtype' value='".$item->gettype()."'>";
         echo "<input type='hidden' name='helpdesk_itemtype' value='".$item->gettype()."'>";
         Dropdown::show('PluginProjetProjet', array('used'   => $used,
                                         'entity' => $item->getEntityID(),
                                         'name' => 'plugin_projet_projets_id'));
         echo "</td><td class='center'>";
         echo "<input type='submit' name='additem' value=\""._sx('button','Add')."\" class='submit'>";
         echo "</td></tr>";
      }

      echo "</table></div>";

      /*if ($canedit) {
         Html::openArrowMassives("projetlink_form$rand", true);
         Html::closeArrowMassives(array('delete' => _sx('button','Delete'));
      }*/
      Html::closeForm();
   }
}

?>