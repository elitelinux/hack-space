<?php
/*
 * @version $Id: resource_item.class.php 480 2012-11-09 tsmr $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginResourcesResource_Item extends CommonDBTM {
   
   // From CommonDBRelation
   static public $itemtype_1 = "PluginResourcesResource";
   static public $items_id_1 = 'plugin_resources_resources_id';

   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';
   
   static function canCreate() {
      return plugin_resources_haveRight('resources', 'w');
   }

   static function canView() {
      return plugin_resources_haveRight('resources', 'r');
   }
   
   static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         array('itemtype' => $item->getType(),
               'items_id' => $item->getField('id'))
      );
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='PluginResourcesResource'
          && count(PluginResourcesResource::getTypes(false))) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(_n('Associated item', 'Associated items', 2), self::countForResource($item));
         }
         return _n('Associated item', 'Associated items', 2);

      } else if (in_array($item->getType(), PluginResourcesResource::getTypes(true))
                 && $this->canView() && !$withtemplate) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(PluginResourcesResource::getTypeName(2), self::countForItem($item));
         }
         return PluginResourcesResource::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      if ($item->getType()=='PluginResourcesResource') {
         self::showForResource($item, $withtemplate);

      } else if (in_array($item->getType(), PluginResourcesResource::getTypes(true))) {
         self::showForItem($item);
      }
      return true;
   }
   
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType()=='PluginResourcesResource') {
         self::pdfForResource($pdf, $item);

      } else if (in_array($item->getType(), PluginResourcesResource::getTypes(true))) {
         self::PdfFromItems($pdf, $item);

      } else {
         return false;
      }
      return true;
   }
   
   static function countForResource(PluginResourcesResource $item) {

      $types = implode("','", PluginResourcesResource::getTypes());
      if (empty($types)) {
         return 0;
      }
      return countElementsInTable('glpi_plugin_resources_resources_items',
                                  "`itemtype` IN ('$types')
                                   AND `plugin_resources_resources_id` = '".$item->getID()."'");
   }


   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_resources_resources_items',
                                  "`itemtype`='".$item->getType()."'
                                   AND `items_id` = '".$item->getID()."'");
   }

   function getFromDBbyResourcesAndItem($plugin_resources_resources_id,$items_id,$itemtype) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."` " .
         "WHERE `plugin_resources_resources_id` = '" . $plugin_resources_resources_id . "' 
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
      
      if (!isset($options["plugin_resources_resources_id"]) 
            || $options["plugin_resources_resources_id"] <= 0) {
         return false;
      } else {
         $this->add(array('plugin_resources_resources_id'=>$options["plugin_resources_resources_id"],
                           'items_id'=>$options["items_id"],
                           'itemtype'=>$options["itemtype"]));

         if ($options["itemtype"] == 'User') {
         
            $values["id"] = $options["items_id"];
            $item = new PluginResourcesResource();
            $item->getFromDB($options["plugin_resources_resources_id"]);

            if (isset($item->fields["locations_id"])) {
               $values["locations_id"] = $item->fields["locations_id"];
            } else {
               $values["locations_id"] = 0;
            }
            $this->updateLocation($values,$options["itemtype"]);
            
         }
      }
   }

   function updateItem($ID,$comment) {
    
      if ($ID>0) {
         $values["id"] = $ID;
         $values["comment"] = $comment;
         $this->update($values);
      }
   }

   function deleteItem($ID) {

      $this->delete(array('id'=>$ID));
   }

   function deleteItemByResourcesAndItem($plugin_resources_resources_id,$items_id,$itemtype) {
    
      if ($this->getFromDBbyResourcesAndItem($plugin_resources_resources_id,$items_id,$itemtype)) {
         $this->delete(array('id'=>$this->fields["id"]));
      }
   }
   
   /**
    * Duplicate item resources from an item template to its clone
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
                 FROM `glpi_plugin_resources_resources_items`
                 WHERE `plugin_resources_resources_id` = '$oldid';";

      foreach ($DB->request($query) as $data) {
         $item = new self();
         $item->add(array('plugin_resources_resources_id'   => $newid,
                             'itemtype'                        => $data["itemtype"],
                             'items_id'                        => $data["items_id"],
                              'comment'                        => $data["comment"]));
      }
   }
  
   function updateLocation($values,$itemtype) {
      global $DB;
      
      $id = 0;
      if ($itemtype == "PluginResourcesResource") {
         $restrict = "`itemtype` = 'User' 
                     AND `plugin_resources_resources_id` = '".$values["id"]."'";
         $resources = getAllDatasFromTable($this->getTable(),$restrict);

         if (!empty($resources)) {
            foreach ($resources as $resource) {
              $id = $resource["items_id"];
            }
         }
      } else if ($itemtype == "User") {
         $id = $values["id"];
      }
      if (isset($id) && $id > 0 && isset($values["locations_id"]) && $values["locations_id"] > 0) {
         
         $item = new User();
         $update["id"] = $id;
         $update["locations_id"] = $values["locations_id"];
         if ($itemtype == "PluginResourcesResource")
            $update["_UpdateFromResource_"] = 1;
         if ($item->update($update))
            Session::addMessageAfterRedirect(__("Modification of the associated user's location", "resources"),true);
      }
   }
  
   function searchAssociatedBadge($ID) {
    
      $plugin = new Plugin();
      $PluginResourcesResource = new PluginResourcesResource();
      
      if ($plugin->isActivated("badges")) {

         //search is the user have a linked badge
         $restrict = "`itemtype` = 'User' 
                     AND `plugin_resources_resources_id` = '".$ID."'";
         $resources = getAllDatasFromTable($this->getTable(),$restrict);

         if (!empty($resources)) {
            foreach ($resources as $resource) {
               $restrictbadge = "`users_id` = '".$resource["items_id"]."'";
               $badges = getAllDatasFromTable("glpi_plugin_badges_badges",$restrictbadge);
               //if the user have a linked badge, send email for his badge
               if (!empty($badges)) {
                  foreach ($badges as $badge)
                     return $badge["id"];
               } else {
                  return 0;
               }
            }
         }
      }
   }
  
   function dropdownItems($ID,$used=array()) {
      global $DB,$CFG_GLPI;

      $restrict = "`plugin_resources_resources_id` = '$ID'";
      $resources = getAllDatasFromTable($this->getTable(),$restrict);

      echo "<select name='item_item'>";
      echo "<option value='0' selected>".Dropdown::EMPTY_VALUE."</option>";

      if (!empty($resources)) {

        foreach ($resources as $resource) {
            
            $table = getTableForItemType($resource["itemtype"]);
             
            $query = "SELECT `".$table."`.*
                     FROM `".$this->getTable()."`
                     INNER JOIN `".$table."` ON (`".$table."`.`id` = `".$this->getTable()."`.`items_id`)
                     WHERE `".$this->getTable()."`.`itemtype` = '".$resource["itemtype"]."'
                     AND `".$this->getTable()."`.`items_id` = '".$resource["items_id"]."' ";
            if (count($used)) {
               $query .= " AND `".$table."`.`id` NOT IN (0";
               foreach ($used as $ID)
                  $query .= ",$ID";
               $query .= ")";
            }
            $query .= " ORDER BY `".$table."`.`name`";
            $result_linked=$DB->query($query);

            if ($DB->numrows($result_linked)) {

               if ($data=$DB->fetch_assoc($result_linked)) {
                  $name=$data["name"];
                  if ($resource["itemtype"]=='User')
                     $name=getUserName($data["id"]);
                  echo "<option value='".$data["id"].",".$resource["itemtype"]."'>".$name;
                  if (empty($data["name"]) || $_SESSION["glpiis_ids_visible"] == 1 ) {
                     echo " (";
                     echo $data["id"].")";
                     }
                  echo "</option>";
               }
            }
         }
      }
      echo "</select>";
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
    * Show items links to a resource
    *
    * @since version 0.84
    *
    * @param $resource PluginResourcesResource object
    *
    * @return nothing (HTML display)
    **/
   public static function showForResource(PluginResourcesResource $resource, $withtemplate='') {
      global $DB,$CFG_GLPI;
      
      $instID = $resource->fields['id'];
      if (!$resource->can($instID,'r'))   return false;
      
      $rand=mt_rand();

      $canedit=$resource->can($instID,'w');
      if (empty($withtemplate)) {
         $withtemplate = 0;
      }
      $types=PluginResourcesResource::getTypes();
         $plugin = new Plugin();
         if ($plugin->isActivated("badges"))
               $types[]='PluginBadgesBadge';	
               
      $query = "SELECT DISTINCT `itemtype` 
          FROM `glpi_plugin_resources_resources_items` 
          WHERE `plugin_resources_resources_id` = '$instID' 
          ORDER BY `itemtype` 
          LIMIT ".count($types);
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }
      
      if ($canedit && $withtemplate < 2 && $number < 1) {
         echo "<div class='firstbloc'>";
         echo "<form method='post' name='resource_form$rand' id='resource_form$rand'
         action='".Toolbox::getItemTypeFormURL("PluginResourcesResource")."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".
         __('Add a user')."</th></tr>";
         echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";
         echo "<input type='hidden' name='plugin_resources_resources_id' value='$instID'>";
         echo "<input type='hidden' name='itemtype' value='User'>";
         //Dropdown::showAllItems("items_id",0,0,($resource->fields['is_recursive']?-1:$resource->fields['entities_id']),$types);
         User::dropdown(array('name'        => 'items_id',
                                 'entity'      => $resource->fields["entities_id"],
                                 'right' => 'all',
                                 'ldap_import' => true));
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
      echo "<th>".__('Serial Number')."</th>";
      echo "<th>".__('Inventory number')."</th>";
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
                             `glpi_plugin_resources_resources_items`.`id` AS items_id,
                             `glpi_plugin_resources_resources_items`.`comment` AS comment,
                             `glpi_entities`.`id` AS entity "
               ." FROM `glpi_plugin_resources_resources_items`, `".$itemTable
               ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$itemTable."`.`entities_id`) "
               ." WHERE `".$itemTable."`.`id` = `glpi_plugin_resources_resources_items`.`items_id`
                AND `glpi_plugin_resources_resources_items`.`itemtype` = '$itemType'
                AND `glpi_plugin_resources_resources_items`.`plugin_resources_resources_id` = '$instID' ";
            if ($itemType != 'User') {
               $query.=getEntitiesRestrictRequest(" AND ",$itemTable,'','',$item->maybeRecursive());
            }
            if ($item->maybeTemplate()) {
               $query.=" AND `".$itemTable."`.`is_template` = '0'";
            }
            $query.=" ORDER BY `glpi_entities`.`completename`, `".$itemTable."`.`$column`";

            if ($result_linked=$DB->query($query)) {
               if ($DB->numrows($result_linked)) {

                  Session::initNavigateListItems($itemType,PluginResourcesResource::getTypeName(2)." = ".$resource->fields['name']);

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
                     $items_id=$data["items_id"];
                     if ($canedit && $withtemplate < 2) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(__CLASS__, $data["items_id"]);
                        /*TODO resolve IT or drop IT ?
                        echo "<img src='".$CFG_GLPI["root_doc"]."/pics/expand.gif' onclick=\"plugin_resources_show_item('comment$items_id$rand',this,'".$CFG_GLPI["root_doc"]."/pics/collapse.gif');\">";*/
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
                     echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
                     echo "<td class='center'>".(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
                     echo "</tr>";
                     /*TODO resolve IT or drop IT ?
                     echo "<tr class='tab_bg_1'>";
                     
                     $class = "class='plugin_resources_show'";
                     
                     if (!isset($data["comment"]) || empty($data["comment"])) {
                        $data["comment"]='';
                        $class = "class='plugin_resources_hide'";
                     }
                     echo "<td colspan='6' id='comment$items_id$rand' $class >";
                     
                     echo "<form method='post' name='updatecomment$items_id$rand' id='updatecomment$items_id$rand' action='".Toolbox::getItemTypeFormURL("PluginResourcesResource")."'>";
                     echo "<table><tr><td>";
                     _e('Comments');
                     echo "<br><textarea cols='150' rows='5' name='comment$items_id' >";
                     echo $data["comment"];
                     echo "</textarea><br><br>";
                     echo "<input type='hidden' name='items_id' value='".$data["items_id"]."'>";
                     if($canedit && $withtemplate<2) {
                        if (!isset($data["comment"]) || empty($data["comment"])) {
                           
                           echo "<input type='submit' name='updatecomment[".$items_id."]' value=\""._sx('button','Add')."\" class='submit'>";
                        } else {
                           echo "<input type='submit' name='updatecomment[".$items_id."]' value=\""._sx('button','Update')."\" class='submit'>";
                        }			
                     }
                     echo "</td>";
                     echo "</tr>";
                     echo "</table>";
                     Html::closeForm();
                     
                     echo "</td>";
                     echo "</tr>";*/
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
   * Show resource associated to an item
   *
   * @since version 0.84
   *
   * @param $item            CommonDBTM object for which associated resource must be displayed
   * @param $withtemplate    (default '')
   **/
   static function showForItem(CommonDBTM $item, $withtemplate='') {
      global $DB,$CFG_GLPI;
      
      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!plugin_resources_haveRight('resources', 'r')) {
         return false;
      }

      if (!$item->can($item->fields['id'],'r')) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }

      $canedit       = $item->canadditem('PluginResourcesResource');
      $rand          = mt_rand();
      $is_recursive  = $item->isRecursive();
      
      $query = "SELECT `glpi_plugin_resources_resources_items`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity,
                       `glpi_plugin_resources_resources`.`name` AS assocName,
                       `glpi_plugin_resources_resources`.*
                FROM `glpi_plugin_resources_resources_items`
                LEFT JOIN `glpi_plugin_resources_resources`
                 ON (`glpi_plugin_resources_resources_items`.`plugin_resources_resources_id`=`glpi_plugin_resources_resources`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_plugin_resources_resources`.`entities_id`=`glpi_entities`.`id`)
                WHERE `glpi_plugin_resources_resources_items`.`items_id` = '$ID'
                      AND `glpi_plugin_resources_resources_items`.`itemtype` = '".$item->getType()."' ";

      $query .= getEntitiesRestrictRequest(" AND","glpi_plugin_resources_resources",'','',true);

      $query .= " ORDER BY `assocName`";
    
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;
      
      $resources    = array();
      $used       = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $resources[$data['assocID']] = $data;
            $used[$data['id']] = $data['id'];
         }
      }
      $resource = new PluginResourcesResource();
      
      $more = true;
      if ($item->getType() == "User" && $number != 0) {
         $more = false;
      }
      if ($canedit && $withtemplate < 2 && $more) {
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
         $limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_resources_resources",'',$entities,true);
         $q = "SELECT COUNT(*)
               FROM `glpi_plugin_resources_resources`
               WHERE `is_deleted` = '0'
               AND `is_template` = '0' ";
         if ($item->getType() != 'User') {
            $q.=" $limit";
         }
         $result = $DB->query($q);
         $nb     = $DB->result($result,0,0);

         echo "<div class='firstbloc'>";       
         
         
         if (plugin_resources_haveRight('resources', 'r')
             && ($nb > count($used))) {
            echo "<form name='resource_form$rand' id='resource_form$rand' method='post'
                   action='".Toolbox::getItemTypeFormURL('PluginResourcesResource')."'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' class='center'>";
            echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
            echo "<input type='hidden' name='items_id' value='$ID'>";
            if ($item->getType() == 'Ticket') {
               echo "<input type='hidden' name='tickets_id' value='$ID'>";
            }

            PluginResourcesResource::dropdown(array('entity' => $entities,
                                                         'used'   => $used));

            echo "</td><td class='center' width='20%'>";
            echo "<input type='submit' name='additem' value=\"".
                     __s('Associate a resource', 'resources')."\" class='submit'>";
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

      echo "<tr>";
      if ($canedit && $number && ($withtemplate < 2)) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }
      echo "<th>".__('Name')."</th>";
      echo "<th>".__('First name')."</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>".__('Entity')."</th>";
      }
      echo "<th>".__('Location')."</th>";
      echo "<th>".PluginResourcesContractType::getTypeName(1)."</th>";
      echo "<th>".PluginResourcesDepartment::getTypeName(1)."</th>";
      echo "<th>".__('Arrival date', 'resources')."</th>";
      echo "<th>".__('Departure date', 'resources')."</th>";
      echo "</tr>";
      
      $used = array();
      $resourceID = 0;
      if ($number) {

         Session::initNavigateListItems('PluginResourcesResource',
                           //TRANS : %1$s is the itemtype name,
                           //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                $item->getTypeName(1), $item->getName()));

         
         foreach  ($resources as $data) {
            $resourceID     = $data["id"];
            $link         = NOT_AVAILABLE;

            if ($resource->getFromDB($resourceID)) {
               $link         = $resource->getLink();
            }

            Session::addToNavigateListItems('PluginResourcesResource', $resourceID);
            
            $used[$resourceID]  = $resourceID;
            $assocID          = $data["assocID"];

            echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
            if ($canedit && ($withtemplate < 2)) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["assocID"]);
               echo "</td>";
            }
            echo "<td class='center'>$link</td>";
            echo "<td class='center'>".$data['firstname']."</td>";
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data['entities_id']).
                    "</td>";
            }

            echo "<td class='center'>";
            echo Dropdown::getDropdownName("glpi_locations",$data["locations_id"]);
            echo "</td>";
            
            echo "<td class='center'>";
            echo Dropdown::getDropdownName("glpi_plugin_resources_contracttypes"
                                                      ,$data["plugin_resources_contracttypes_id"]);
            echo "</td>";
            echo "<td class='center'>";
            echo Dropdown::getDropdownName("glpi_plugin_resources_departments"
                                                         ,$data["plugin_resources_departments_id"]);
            echo "</td>";

            echo "<td class='center'>".Html::convDate($data["date_begin"])."</td>";
            if ($data["date_end"] <= date('Y-m-d') && !empty($data["date_end"])) {
               echo "<td class='center'>";
               echo "<span class='plugin_resources_date_color'>";
               echo Html::convDate($data["date_end"]);
               echo "</span>";
               echo "</td>";
            } else if (empty($data["date_end"])) {
               echo "<td class='center'>".__('Not defined', 'resources')."</td>";
            } else {
               echo "<td class='center'>".Html::convDate($data["date_end"])."</td>";
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
      
      if ($item->getType() == "User") {
         $PluginResourcesEmployee=new PluginResourcesEmployee();
         $PluginResourcesEmployee->showForm($resourceID,$ID,0);
      }
   }
   
   
   /**
    * Show for PDF an resources - asociated devices
    * 
    * @param $pdf object for the output
    * @param $ID of the resources
    */
   static function pdfForResource(PluginPdfSimplePDF $pdf, PluginResourcesResource $appli) {
      global $DB,$CFG_GLPI;
      
      $ID = $appli->fields['id'];

      if (!$appli->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_resources_haveRight("resources","r")) {
         return false;
      }

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'._n('Associated item', 'Associated items', 2).'</b>');
         
      $query = "SELECT DISTINCT `itemtype` 
               FROM `glpi_plugin_resources_resources_items` 
               WHERE `plugin_resources_resources_id` = '$ID' 
               ORDER BY `itemtype` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $pdf->setColumnsSize(12,27,25,18,18);
         $pdf->displayTitle(
            '<b><i>'.__('Type'),
            __('Name'),
            __('Entity'),
            __('Serial Number'),
            __('Inventory number').'</i></b>'
            );
      } else {
         $pdf->setColumnsSize(25,31,22,22);
         $pdf->displayTitle(
            '<b><i>'.__('Type'),
            __('Name'),
            __('Serial Number'),
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
               ." FROM `glpi_plugin_resources_resources_items`, `".$table
               ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table."`.`entities_id`) "
               ." WHERE `".$table."`.`id` = `glpi_plugin_resources_resources_items`.`items_id` 
                  AND `glpi_plugin_resources_resources_items`.`itemtype` = '$type' 
                  AND `glpi_plugin_resources_resources_items`.`plugin_resources_resources_id` = '$ID' ";
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
      } // numrows type
   }
   
   /** 
    * show for PDF the resources associated with a device
    * 
    * @param $ID of the device
    * @param $itemtype : type of the device
    * 
    */
   static function PdfFromItems($pdf, $item) {
      global $DB,$CFG_GLPI;
      
      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.__('Associated Human Resource', 'resources').'</b>');
      
      $ID = $item->getField('id');
      $itemtype = get_Class($item);
      $canread = $item->can($ID,'r');
      $canedit = $item->can($ID,'w');
      
      $PluginResourcesResource=new PluginResourcesResource(); 
      
      $query = "SELECT `glpi_plugin_resources_resources`.* "
      ." FROM `glpi_plugin_resources_resources_items`,`glpi_plugin_resources_resources` "
      ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_resources_resources`.`entities_id`) "
      ." WHERE `glpi_plugin_resources_resources_items`.`items_id` = '".$ID."' 
         AND `glpi_plugin_resources_resources_items`.`itemtype` = '".$itemtype."' 
         AND `glpi_plugin_resources_resources_items`.`plugin_resources_resources_id` = `glpi_plugin_resources_resources`.`id` "
      . getEntitiesRestrictRequest(" AND ","glpi_plugin_resources_resources",'','',$PluginResourcesResource->maybeRecursive());
      
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
               __('Location'),
               PluginResourcesContractType::getTypeName(1),
               PluginResourcesDepartment::getTypeName(1),
               __('Arrival date', 'resources'),
               __('Departure date', 'resources').'</i></b>'
               );
         } else {
            $pdf->setColumnsSize(17,17,17,17,17,17);
            $pdf->displayTitle(
               '<b><i>'.__('Name'),
               __('Location'),
               PluginResourcesContractType::getTypeName(1),
               PluginResourcesDepartment::getTypeName(1),
               __('Arrival date', 'resources'),
               __('Departure date', 'resources').'</i></b>'
               );
         }
         while ($data=$DB->fetch_array($result)) {
            $resourcesID=$data["id"];
      
            if (Session::isMultiEntitiesMode()) {
               $pdf->setColumnsSize(14,14,14,14,14,14,16);
               $pdf->displayLine(
                  $data["name"],
                  Html::clean(Dropdown::getDropdownName("glpi_entities",$data['entities_id'])),
                  Html::clean(Dropdown::getDropdownName("glpi_locations",$data["locations_id"])),
                  Html::clean(Dropdown::getDropdownName("glpi_plugin_resources_contracttypes",$data["plugin_resources_contracttypes_id"])),
                  Html::clean(Dropdown::getDropdownName("glpi_plugin_resources_departments",$data["plugin_resources_departments_id"])),
                  Html::convDate($data["date_begin"]),
                  Html::convDate($data["date_end"])
                  );
            } else {
               $pdf->setColumnsSize(17,17,17,17,17,17);
               $pdf->displayLine(
                  $data["name"],
                  Html::clean(Dropdown::getDropdownName("glpi_locations",$data["locations_id"])),
                  Html::clean(Dropdown::getDropdownName("glpi_plugin_resources_contracttypes",$data["plugin_resources_contracttypes_id"])),
                  Html::clean(Dropdown::getDropdownName("glpi_plugin_resources_departments",$data["plugin_resources_departments_id"])),
                  Html::convDate($data["date_begin"]),
                  Html::convDate($data["date_end"])
                  );
            }
         }		
      }
   }
}

?>