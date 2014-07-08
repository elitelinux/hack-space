<?php
/*
 * @version $Id: document_item.class.php 22875 2014-04-09 07:07:17Z moyo $
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

// Relation between Documents and Items
class Document_Item extends CommonDBRelation{


   // From CommonDBRelation
   static public $itemtype_1    = 'Document';
   static public $items_id_1    = 'documents_id';
   static public $take_entity_1 = true ;

   static public $itemtype_2    = 'itemtype';
   static public $items_id_2    = 'items_id';
   static public $take_entity_2 = false ;


   /**
    * @since version 0.84
   **/
   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }


   function prepareInputForAdd($input) {

      if ((empty($input['items_id']) || ($input['items_id'] == 0))
          && ($input['itemtype'] != 'Entity')) {
         return false;
      }

      // Do not insert circular link for document
      if (($input['itemtype'] == 'Document')
          && ($input['items_id'] == $input['documents_id'])) {
         return false;
      }

      // Avoid duplicate entry
      $restrict = "`documents_id` = '".$input['documents_id']."'
                   AND `itemtype` = '".$input['itemtype']."'
                   AND `items_id` = '".$input['items_id']."'";
      if (countElementsInTable($this->getTable(),$restrict) > 0) {
         return false;
      }
      return parent::prepareInputForAdd($input);
   }


   function post_addItem() {

      if ($this->fields['itemtype'] == 'Ticket') {
         $ticket = new Ticket();
         $input  = array('id'            => $this->fields['items_id'],
                         'date_mod'      => $_SESSION["glpi_currenttime"],
                         '_donotadddocs' => true);

         if (!isset($this->input['_do_notif']) || $this->input['_do_notif']) {
            $input['_forcenotif'] = true;
         }
         $ticket->update($input);
      }
      parent::post_addItem();
   }

   function post_purgeItem() {

      if ($this->fields['itemtype'] == 'Ticket') {
         $ticket = new Ticket();
         $input = array('id'            => $this->fields['items_id'],
                        'date_mod'      => $_SESSION["glpi_currenttime"],
                        '_donotadddocs' => true);

         if (!isset($this->input['_do_notif']) || $this->input['_do_notif']) {
            $input['_forcenotif'] = true;
         }
         $ticket->update($input);
      }
      parent::post_purgeItem();
   }

   /**
    * @param $item   CommonDBTM object
   **/
   static function countForItem(CommonDBTM $item) {

      $restrict = "`glpi_documents_items`.`items_id` = '".$item->getField('id')."'
                   AND `glpi_documents_items`.`itemtype` = '".$item->getType()."'";

      if (Session::getLoginUserID()) {
         $restrict .= getEntitiesRestrictRequest(" AND ", "glpi_documents_items", '', '', true);
      } else {
         // Anonymous access from FAQ
         $restrict .= " AND `glpi_documents_items`.`entities_id` = '0' ";
      }

      $nb = countElementsInTable(array('glpi_documents_items'), $restrict);

      // Document case : search in both
      if ($item->getType() == 'Document') {
         $restrict = "`glpi_documents_items`.`documents_id` = '".$item->getField('id')."'
                      AND `glpi_documents_items`.`itemtype` = '".$item->getType()."'";

         if (Session::getLoginUserID()) {
            $restrict .= getEntitiesRestrictRequest(" AND ", "glpi_documents_items", '', '', true);
         } else {
            // Anonymous access from FAQ
            $restrict .= " AND `glpi_documents_items`.`entities_id` = '0' ";
         }
         $nb += countElementsInTable(array('glpi_documents_items'), $restrict);
      }
      return $nb ;
   }


   /**
    * @param $item   Document object
   **/
   static function countForDocument(Document $item) {

      $restrict = "`glpi_documents_items`.`documents_id` = '".$item->getField('id')."'
                   AND `glpi_documents_items`.`itemtype` != '".$item->getType()."'";

      return countElementsInTable(array('glpi_documents_items'), $restrict);
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      switch ($item->getType()) {
         case 'Document' :
            $ong = array();
            if ($_SESSION['glpishow_count_on_tabs']) {
               $ong[1] = self::createTabEntry(_n('Associated item', 'Associated items',
                                              self::countForDocument($item)));
            }
            $ong[1] = _n('Associated item', 'Associated items', 2);

            if ($_SESSION['glpishow_count_on_tabs']) {
               $ong[2] = Document::createTabEntry(Document::getTypeName(2),
                                                  self::countForItem($item));
            }
            $ong[2] = Document::getTypeName(2);
            return $ong;

         default :
            // Can exist for template
            if (Session::haveRight("document","r")
                || ($item->getType() == 'Ticket')
                || ($item->getType() == 'Reminder')
                || ($item->getType() == 'KnowbaseItem')) {

               if ($_SESSION['glpishow_count_on_tabs']) {
                  return Document::createTabEntry(Document::getTypeName(2),
                                                  self::countForItem($item));
               }
               return Document::getTypeName(2);
            }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'Document' :
            switch ($tabnum) {
               case 1 :
                  self::showForDocument($item);
                  break;

               case 2 :
                  self::showForItem($item, $withtemplate);
                  break;
            }
            return true;

         default :
            self::showForitem($item, $withtemplate);
      }
   }


   /**
    * Duplicate documents from an item template to its clone
    *
    * @since version 0.84
    *
    * @param $itemtype     itemtype of the item
    * @param $oldid        ID of the item to clone
    * @param $newid        ID of the item cloned
    * @param $newitemtype  itemtype of the new item (= $itemtype if empty) (default '')
   **/
   static function cloneItem($itemtype, $oldid, $newid, $newitemtype='') {
      global $DB;

      if (empty($newitemtype)) {
         $newitemtype = $itemtype;
      }

      $query  = "SELECT `documents_id`
                 FROM `glpi_documents_items`
                 WHERE `items_id` = '$oldid'
                        AND `itemtype` = '$itemtype';";

      foreach ($DB->request($query) as $data) {
         $docitem = new self();
         $docitem->add(array('documents_id' => $data["documents_id"],
                             'itemtype'     => $newitemtype,
                             'items_id'     => $newid));
      }
   }


   /**
    * Show items links to a document
    *
    * @since version 0.84
    *
    * @param $doc Document object
    *
    * @return nothing (HTML display)
   **/
   static function showForDocument(Document $doc) {
      global $DB, $CFG_GLPI;

      $instID = $doc->fields['id'];
      if (!$doc->can($instID,"r")) {
         return false;
      }
      $canedit = $doc->can($instID,'w');

      // for a document,
      // don't show here others documents associated to this one,
      // it's done for both directions in self::showAssociated
      $query = "SELECT DISTINCT `itemtype`
                FROM `glpi_documents_items`
                WHERE `glpi_documents_items`.`documents_id` = '$instID'
                      AND `glpi_documents_items`.`itemtype` != 'Document'
                ORDER BY `itemtype`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $rand   = mt_rand();

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='documentitem_form$rand' id='documentitem_form$rand' method='post'
               action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Add an item')."</th></tr>";

         echo "<tr class='tab_bg_1'><td class='right'>";
         Dropdown::showAllItems("items_id", 0, 0,
                                ($doc->fields['is_recursive']?-1:$doc->fields['entities_id']),
                                $CFG_GLPI["document_types"], false, true);
         echo "</td><td class='center'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "<input type='hidden' name='documents_id' value='$instID'>";
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
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Serial number')."</th>";
      echo "<th>".__('Inventory number')."</th>";
      echo "</tr>";

      for ($i=0 ; $i < $number ; $i++) {
         $itemtype=$DB->result($result, $i, "itemtype");
         if (!($item = getItemForItemtype($itemtype))) {
            continue;
         }

         if ($item->canView()) {
            $column = "name";
            if ($itemtype == 'Ticket') {
               $column = "id";
            }

            $itemtable = getTableForItemType($itemtype);
            $query     = "SELECT `$itemtable`.*,
                                 `glpi_documents_items`.`id` AS IDD, ";

            if ($itemtype == 'KnowbaseItem') {
               $query .= "-1 AS entity
                          FROM `glpi_documents_items`, `$itemtable`
                          ".KnowbaseItem::addVisibilityJoins()."
                          WHERE `$itemtable`.`id` = `glpi_documents_items`.`items_id`
                                AND ";
            } else {
               $query .= "`glpi_entities`.`id` AS entity
                          FROM `glpi_documents_items`, `$itemtable`
                          LEFT JOIN `glpi_entities`
                              ON (`glpi_entities`.`id` = `$itemtable`.`entities_id`)
                          WHERE `$itemtable`.`id` = `glpi_documents_items`.`items_id`
                                AND ";
            }
            $query .= "`glpi_documents_items`.`itemtype` = '$itemtype'
                       AND `glpi_documents_items`.`documents_id` = '$instID' ";

            if ($itemtype =='KnowbaseItem') {
               if (Session::getLoginUserID()) {
                 $where = "AND ".KnowbaseItem::addVisibilityRestrict();
               } else {
                  // Anonymous access
                  if (Session::isMultiEntitiesMode()) {
                     $where = " AND (`glpi_entities_knowbaseitems`.`entities_id` = '0'
                                     AND `glpi_entities_knowbaseitems`.`is_recursive` = '1')";
                  }
               }
            } else {
               $query .= getEntitiesRestrictRequest(" AND ", $itemtable, '', '',
                                                   $item->maybeRecursive());
            }

            if ($item->maybeTemplate()) {
               $query .= " AND `$itemtable`.`is_template` = '0'";
            }

            if ($itemtype == 'KnowbaseItem') {
               $query .= " ORDER BY `$itemtable`.`$column`";
            } else {
               $query .= " ORDER BY `glpi_entities`.`completename`, `$itemtable`.`$column`";
            }

            if ($itemtype == 'SoftwareLicense') {
               $soft = new Software();
            }

            if ($result_linked = $DB->query($query)) {
               if ($DB->numrows($result_linked)) {

                  while ($data = $DB->fetch_assoc($result_linked)) {

                     if ($itemtype == 'Ticket') {
                        $data["name"] = sprintf(__('%1$s: %2$s'), __('Ticket'), $data["id"]);
                     }

                     if ($itemtype == 'SoftwareLicense') {
                        $soft->getFromDB($data['softwares_id']);
                        $data["name"] = sprintf(__('%1$s - %2$s'), $data["name"],
                                                $soft->fields['name']);
                     }
                     $linkname = $data["name"];
                     if ($_SESSION["glpiis_ids_visible"]
                         || empty($data["name"])) {
                        $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $data["id"]);
                     }

                     $link = Toolbox::getItemTypeFormURL($itemtype);
                     $name = "<a href=\"".$link."?id=".$data["id"]."\">".$linkname."</a>";

                     echo "<tr class='tab_bg_1'>";

                     if ($canedit) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(__CLASS__, $data["IDD"]);
                        echo "</td>";
                     }
                     echo "<td class='center'>".$item->getTypeName(1)."</td>";
                     echo "<td ".
                           (isset($data['is_deleted']) && $data['is_deleted']?"class='tab_bg_2_2'":"").
                          ">".$name."</td>";
                     echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",
                                                                          $data['entity']);
                     echo "</td>";
                     echo "<td class='center'>".
                            (isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
                     echo "<td class='center'>".
                            (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
                     echo "</tr>";
                  }
               }
            }
         }
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
    * Show documents associated to an item
    *
    * @since version 0.84
    *
    * @param $item            CommonDBTM object for which associated documents must be displayed
    * @param $withtemplate    (default '')
   **/
   static function showForItem(CommonDBTM $item, $withtemplate='') {
      global $DB, $CFG_GLPI;

      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (($item->getType() != 'Ticket')
          && ($item->getType() != 'KnowbaseItem')
          && ($item->getType() != 'Reminder')
          && !Session::haveRight('document','r')) {
         return false;
      }

      if (!$item->can($item->fields['id'],'r')) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }
      $linkparam = '';

      if (get_class($item) == 'Ticket') {
         $linkparam = "&amp;tickets_id=".$item->fields['id'];
      }

      $canedit       =  $item->canAddItem('Document') && Document::canView();
      $rand          = mt_rand();
      $is_recursive  = $item->isRecursive();


      if (isset($_POST["order"]) && ($_POST["order"] == "ASC")) {
         $order = "ASC";
      } else {
         $order = "DESC";
      }

      if (isset($_POST["sort"]) && !empty($_POST["sort"])) {
         $sort = "`".$_POST["sort"]."`";
      } else {
         $sort = "`assocdate`";
      }

      $query = "SELECT `glpi_documents_items`.`id` AS assocID,
                       `glpi_documents_items`.`date_mod` AS assocdate,
                       `glpi_entities`.`id` AS entityID,
                       `glpi_entities`.`completename` AS entity,
                       `glpi_documentcategories`.`completename` AS headings,
                       `glpi_documents`.*
                FROM `glpi_documents_items`
                LEFT JOIN `glpi_documents`
                          ON (`glpi_documents_items`.`documents_id`=`glpi_documents`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_documents`.`entities_id`=`glpi_entities`.`id`)
                LEFT JOIN `glpi_documentcategories`
                        ON (`glpi_documents`.`documentcategories_id`=`glpi_documentcategories`.`id`)
                WHERE `glpi_documents_items`.`items_id` = '$ID'
                      AND `glpi_documents_items`.`itemtype` = '".$item->getType()."' ";

      if (Session::getLoginUserID()) {
         $query .= getEntitiesRestrictRequest(" AND","glpi_documents",'','',true);
      } else {
         // Anonymous access from FAQ
         $query .= " AND `glpi_documents`.`entities_id`= '0' ";
      }

      // Document : search links in both order using union
      if ($item->getType() == 'Document') {
         $query .= "UNION
                    SELECT `glpi_documents_items`.`id` AS assocID,
                           `glpi_documents_items`.`date_mod` AS assocdate,
                           `glpi_entities`.`id` AS entityID,
                           `glpi_entities`.`completename` AS entity,
                           `glpi_documentcategories`.`completename` AS headings,
                           `glpi_documents`.*
                    FROM `glpi_documents_items`
                    LEFT JOIN `glpi_documents`
                              ON (`glpi_documents_items`.`items_id`=`glpi_documents`.`id`)
                    LEFT JOIN `glpi_entities`
                              ON (`glpi_documents`.`entities_id`=`glpi_entities`.`id`)
                    LEFT JOIN `glpi_documentcategories`
                              ON (`glpi_documents`.`documentcategories_id`=`glpi_documentcategories`.`id`)
                    WHERE `glpi_documents_items`.`documents_id` = '$ID'
                          AND `glpi_documents_items`.`itemtype` = '".$item->getType()."' ";

         if (Session::getLoginUserID()) {
            $query .= getEntitiesRestrictRequest(" AND","glpi_documents",'','',true);
         } else {
            // Anonymous access from FAQ
            $query .= " AND `glpi_documents`.`entities_id`='0' ";
         }
      }
      $query .= " ORDER BY $sort $order ";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;

      $documents = array();
      $used      = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $documents[$data['assocID']] = $data;
            $used[$data['id']]           = $data['id'];
         }
      }

      if ($item->canAddItem('Document') && $withtemplate < 2) {
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
         $limit = getEntitiesRestrictRequest(" AND ","glpi_documents",'',$entities,true);
         $q = "SELECT COUNT(*)
               FROM `glpi_documents`
               WHERE `is_deleted` = '0'
               $limit";

         $result = $DB->query($q);
         $nb     = $DB->result($result,0,0);


         if ($item->getType() == 'Document') {
            $used[$ID] = $ID;
         }

         echo "<div class='firstbloc'>";
         echo "<form name='documentitem_form$rand' id='documentitem_form$rand' method='post'
                action='".Toolbox::getItemTypeFormURL('Document')."'  enctype=\"multipart/form-data\">";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='5'>".__('Add a document')."</th></tr>";
         echo "<tr class='tab_bg_1'>";

         echo "<td class='center'>";
         _e('Heading');
         echo '</td><td>';
         DocumentCategory::dropdown(array('entity' => $entities));
         echo "</td>";
         echo "<td class='right'>";
         echo "<input type='hidden' name='entities_id' value='$entity'>";
         echo "<input type='hidden' name='is_recursive' value='$is_recursive'>";

         echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
         echo "<input type='hidden' name='items_id' value='$ID'>";
         if ($item->getType() == 'Ticket') {
            echo "<input type='hidden' name='tickets_id' value='$ID'>";
         }
         echo "<input type='file' name='filename' size='25'>";
         echo "</td><td class='left'>";
         echo "(".Document::getMaxUploadSize().")&nbsp;";
         echo "</td>";
         echo "<td class='center' width='20%'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add a new file')."\"
                class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();

         if (Session::haveRight('document','r')
             && ($nb > count($used))) {
            echo "<form name='document_form$rand' id='document_form$rand' method='post'
                   action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' class='center'>";
            echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
            echo "<input type='hidden' name='items_id' value='$ID'>";
            if ($item->getType() == 'Ticket') {
               echo "<input type='hidden' name='tickets_id' value='$ID'>";
               echo "<input type='hidden' name='documentcategories_id' value='".
                      $CFG_GLPI["documentcategories_id_forticket"]."'>";
            }

            Document::dropdown(array('entity' => $entities ,
                                     'used'   => $used));
            echo "</td><td class='center' width='20%'>";
            echo "<input type='submit' name='add' value=\"".
                     _sx('button', 'Associate an existing document')."\" class='submit'>";
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

      $sort_img = "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/" .
            (($order == "DESC") ? "puce-down.png" : "puce-up.png") ."\" alt='' title=''>";

      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      if ($canedit && $number && ($withtemplate < 2)) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }

      $columns = array('name' => __('Name'),
                       'entity' => __('Entity'),
                       'filename'   => __('File'),
                       'link'=> __('Web link'),
                       'headings'   => __('Heading'),
                       'mime'   => __('MIME type'),
                       'assocdate'   => __('Date'));

      foreach ($columns as $key => $val) {
         echo "<th>".(($sort == "`$key`") ?$sort_img:"").
               "<a href='javascript:reloadTab(\"sort=$key&amp;order=".
                  (($order == "ASC") ?"DESC":"ASC")."&amp;start=0\");'>$val</a></th>";
      }
      echo "</tr>";
      $used = array();

      if ($number) {

         // Don't use this for document associated to document
         // To not loose navigation list for current document
         if ($item->getType() != 'Document') {
            Session::initNavigateListItems('Document',
                              //TRANS : %1$s is the itemtype name,
                              //        %2$s is the name of the item (used for headings of a list)
                                           sprintf(__('%1$s = %2$s'),
                                                   $item->getTypeName(1), $item->getName()));
         }

         $document = new Document();
         foreach  ($documents as $data) {
            $docID        = $data["id"];
            $link         = NOT_AVAILABLE;
            $downloadlink = NOT_AVAILABLE;

            if ($document->getFromDB($docID)) {
               $link         = $document->getLink();
               $downloadlink = $document->getDownloadLink($linkparam);
            }

            if ($item->getType() != 'Document') {
               Session::addToNavigateListItems('Document', $docID);
            }
            $used[$docID] = $docID;
            $assocID      = $data["assocID"];

            echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
            if ($canedit && ($withtemplate < 2)) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["assocID"]);
               echo "</td>";
            }
            echo "<td class='center'>$link</td>";
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data['entityID']);
            echo "</td>";
            echo "<td class='center'>$downloadlink</td>";
            echo "<td class='center'>";
            if (!empty($data["link"])) {
               echo "<a target=_blank href='".formatOutputWebLink($data["link"])."'>".$data["link"];
               echo "</a>";
            } else {;
               echo "&nbsp;";
            }
            echo "</td>";
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_documentcategories",
                                                                 $data["documentcategories_id"]);
            echo "</td>";
            echo "<td class='center'>".$data["mime"]."</td>";
            echo "<td class='center'>".Html::convDateTime($data["assocdate"])."</td>";
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
