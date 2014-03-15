<?php
/*
 * @version $Id: projet_projet.class.php 17152 2012-01-24 11:22:16Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

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

// ----------------------------------------------------------------------
// Original Author of file: Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// Class Projet links
class PluginProjetProjet_Projet extends CommonDBRelation {


   // From CommonDBRelation
   static public $itemtype_1 = 'PluginProjetProjet';
   static public $items_id_1 = 'plugin_projet_projets_id_1';
   static public $itemtype_2 = 'PluginProjetProjet';
   static public $items_id_2 = 'plugin_projet_projets_id_2';

   public $check_entities = false;

   // Ticket links
   const LINK_TO        = 1;


   function canCreateItem() {

      $projet = new PluginProjetProjet();
      return $projet->can($this->fields['plugin_projet_projets_id_1'], 'w')
             || $projet->can($this->fields['plugin_projet_projets_id_2'], 'w');
   }

   static function canView() {
      return plugin_projet_haveRight('projet', 'r');
   }


   /**
    * Get linked projets to a projet
    *
    * @param $ID ID of the projet id
    *
    * @return array of linked projets  array(id=>linktype)
   **/
   static function getParentProjetsTo ($ID) {
      global $DB;

      // Make new database object and fill variables
      if (empty($ID)) {
         return false;
      }

      $sql = "SELECT *
              FROM `glpi_plugin_projet_projets_projets`
              WHERE `plugin_projet_projets_id_1` = '$ID'";

      $projets = array();

      foreach ($DB->request($sql) as $data) {
         if ($data['plugin_projet_projets_id_1']!=$ID) {
            $projets[$data['id']] = array('link'       => $data['link'],
                                          'plugin_projet_projets_id' => $data['plugin_projet_projets_id_1']);
         } else {
            $projets[$data['id']] = array('link'       => $data['link'],
                                          'plugin_projet_projets_id' => $data['plugin_projet_projets_id_2']);
         }
      }

      ksort($projets);
      return $projets;
   }


   /**
    * Display linked projets to a projet
    *
    * @param $ID ID of the projet id
    *
    * @return nothing display
   **/
   static function displayLinkedProjetsTo ($ID, $withtemplate='', $notif = false) {
      global $DB, $CFG_GLPI;

      $projets   = self::getParentProjetsTo($ID);
      $canupdate = plugin_projet_haveRight('projet', 'w');

      $projet = new PluginProjetProjet();
      if (is_array($projets) && count($projets)) {
         foreach ($projets as $linkID => $data) {
            if ($notif) {
               return Dropdown::getDropdownName("glpi_plugin_projet_projets", $data['plugin_projet_projets_id']);
            } else {
               echo self::getLinkName($data['link'])."&nbsp;";
               if (!$_SESSION['glpiis_ids_visible']) {
                  echo __('ID')."&nbsp;".$data['plugin_projet_projets_id']."&nbsp;:&nbsp;";
               }

               if ($projet->getFromDB($data['plugin_projet_projets_id'])) {
                  echo $projet->getLink();
                  if ($canupdate && $withtemplate < 2) {
                     echo "&nbsp;";
                     
                     Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/projet/front/projet.form.php',
                                    'delete_link',
                                    _x('button','Delete permanently'),
                                    array('delete_link' => 'delete_link',
                                          'id' => $linkID,
                                          'plugin_projet_projets_id' =>$ID
                                          ),
                                     $CFG_GLPI["root_doc"]."/pics/delete.png");
                     
                  }
               }
            }
         }
      }
   }


   /**
    * Dropdown for links between projets
    *
    * @param $myname select name
    * @param $value default value
   **/
   static function dropdownLinks($myname, $value=self::LINK_TO) {

      $tmp[self::LINK_TO]        = __('Parent project', 'projet');
      Dropdown::showFromArray($myname, $tmp, array('value' => $value));
   }


   /**
    * Get Link Name
    *
    * @param $value default value
   **/
   static function getLinkName($value) {

      $tmp[self::LINK_TO]        = __('Parent project', 'projet');

      if (isset($tmp[$value])) {
         return $tmp[$value];
      }
      return NOT_AVAILABLE;
   }


   function prepareInputForAdd($input) {

      $projet = new PluginProjetProjet();
      if (!isset($input['plugin_projet_projets_id_1'])
          || !isset($input['plugin_projet_projets_id_2'])
          || $input['plugin_projet_projets_id_2'] == $input['plugin_projet_projets_id_1']
          || !$projet->getFromDB($input['plugin_projet_projets_id_1'])
          || !$projet->getFromDB($input['plugin_projet_projets_id_2'])) {
         return false;
      }

      if (!isset($input['link'])) {
         $input['link'] = self::LINK_TO;
      }

      return $input;
   }
   
   	
	/**
    * Duplicate projects from an item template to its clone
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
      
      $query  = "SELECT `plugin_projet_projets_id_2`
                 FROM `glpi_plugin_projet_projets_projets`
                 WHERE `plugin_projet_projets_id_1` = '$oldid';";

      foreach ($DB->request($query) as $data) {
         $projet_projet = new self();
         $projet_projet->add(array('plugin_projet_projets_id_2' => $data["plugin_projet_projets_id_2"],
                             'plugin_projet_projets_id_1'         => $newid,
                             'link'                               => self::LINK_TO));
      }
   }
   
   static function findChilds($DB,$options) {
    
      $queryBranch='';
      // Recherche les enfants
      $queryChilds= "SELECT `glpi_plugin_projet_projets_projets`.`plugin_projet_projets_id_1` 
               FROM `glpi_plugin_projet_projets` 
               LEFT JOIN `glpi_plugin_projet_projets_projets` 
               ON (`glpi_plugin_projet_projets_projets`.`plugin_projet_projets_id_2` = `glpi_plugin_projet_projets`.`id`)
               WHERE `glpi_plugin_projet_projets_projets`.`plugin_projet_projets_id_2` = '".$options["id"]."' 
               AND `is_template` = '0' 
               AND `is_deleted` = '0' "
               . getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_projets",'',$options["entities_id"],true);
               
      if ($resultChilds = $DB->query($queryChilds)) {
         while ($dataChilds = $DB->fetch_array($resultChilds)) {
            $child=$dataChilds["plugin_projet_projets_id_1"];
            $queryBranch .= ",'$child'";
            // Recherche les petits enfants recursivement
            $values["entities_id"] = $options["entities_id"];
            $values["id"] = $child;
            $queryBranch .= self::findChilds($DB,$values);
         }
      }
      return $queryBranch;
   }
   
   static function dropdownParent($name,$value=0, $options=array()) {
      global $DB;
      
      echo "<select name='$name'>";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";
      
      $restrict = " `is_template` = '0' AND `is_deleted` = '0'";
      $restrict.= getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_projets",'',
                                                                     $options["entities_id"],true);
      if (!empty($options["id"])) {
         $restrict.= " AND `id` != '".$options["id"]."' ";
      }
      
      $restrict.= " AND `id` NOT IN ('".$options["id"]."'";
      $restrict.= self::findChilds($DB,$options);
      $restrict.= ") ";

      $restrict.= "ORDER BY `name` ASC ";
      
      $projets = getAllDatasFromTable("glpi_plugin_projet_projets",$restrict);

      if (!empty($projets)) {
         $prev=-1;
         foreach ($projets as $projet) {
            if ($projet["entities_id"]!=$prev) {
               if ($prev>=0) {
                  echo "</optgroup>";
               }
               $prev=$projet["entities_id"];
               echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
            }
            $output = $projet["name"];
            echo "<option value='".$projet["id"]."' ".($value=="".$projet["id"].""?" selected ":"").
            " title=\"$output\">".substr($output,0,$_SESSION["glpidropdown_chars_limit"])."</option>";
         }
         if ($prev>=0) {
            echo "</optgroup>";
         }
      }
      echo "</select>";	
   }
   
   
	//$parents=0 -> childs
   //$parents=1 -> parents
   static function showHierarchy($ID,$parents=0) {
      global $DB,$CFG_GLPI;
      
      $first=false;
      $projet = new PluginProjetProjet();
      
      $query = "SELECT `glpi_plugin_projet_projets`.*  ";
      if ($parents!=0) {
         $parent = "plugin_projet_projets_id_1";
         $child = "plugin_projet_projets_id_2";
      } else {
         $parent = "plugin_projet_projets_id_2";
         $child = "plugin_projet_projets_id_1";
      }
      $query.= " FROM `glpi_plugin_projet_projets`";
      $query.= " LEFT JOIN `glpi_plugin_projet_projets_projets` 
                  ON (`glpi_plugin_projet_projets_projets`.`$child` = `glpi_plugin_projet_projets`.`id`)";
      $query.= " WHERE `glpi_plugin_projet_projets_projets`.`$parent` = '$ID' ";

      if ($projet->maybeTemplate()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`glpi_plugin_projet_projets`.`is_template` = '0' ";
      }
      // Add is_deleted if item have it
      if ($projet->maybeDeleted()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`glpi_plugin_projet_projets`.`is_deleted` = '0' ";
      }   
      $LINK= " AND " ;
      
      $query.=getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_projets",'','',$projet->maybeRecursive());
            
      $query.= " ORDER BY `glpi_plugin_projet_projets`.`name`";
      
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i = 0;
      
      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }
         
      if ($number !="0") {

         echo "<div align='center'><table class='tab_cadre_fixe'>";
         
         $title = _n('Child project', 'Child projects', 2, 'projet');
         if ($parents!=0)
            $title = __('Parent project', 'projet');
         
         echo "<tr><th colspan='".(7+$colsup)."'>".$title."</th></tr>";
         
         echo "<tr><th>".__('Name')."</th>";
         if (Session::isMultiEntitiesMode()) {
            echo "<th>".__('Entity')."</th>";
         }
         echo "<th>".__('Progress')."</th>";
         echo "<th>"._n('User', 'Users', 1)."</th>";
         echo "<th>"._n('Group', 'Groups', 1)."</th>";
         echo "<th>".__('State')."</th>";
         echo "<th>".__('Start date')."</th>";
         echo "<th>".__('End date')."</th>";
         echo "</tr>";

         while ($data=$DB->fetch_array($result)) {
            $start = 0;
            $output_type=Search::HTML_OUTPUT;
            $del=false;
            if($data["is_deleted"]=='0')
               echo "<tr class='tab_bg_1'>";
            else
               echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";

            echo Search::showItem($output_type,"<a href=\"./projet.form.php?id=".$data["id"]."\">".
            $data["name"].($_SESSION["glpiis_ids_visible"]||empty($data["name"])?' ('.$data["id"].') ':'')."</a>",$item_num,$i-$start+1,'');
            
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entities_id'])."</td>";
            }
            echo Search::showItem($output_type,
               PluginProjetProjet::displayProgressBar('100',$data["advance"],array("simple"=>true)),
               $item_num,$i-$start+1,"align='center'");
            echo Search::showItem($output_type,getUserName($data['users_id']),$item_num,$i-$start+1,'');
            echo Search::showItem($output_type,
            Dropdown::getDropdownName("glpi_groups",$data['groups_id']),$item_num,$i-$start+1,'');
            echo Search::showItem($output_type,
            Dropdown::getDropdownName("glpi_plugin_projet_projetstates",$data['plugin_projet_projetstates_id']),
            $item_num,$i-$start+1,
            "bgcolor='".PluginProjetProjetState::getStatusColor($data['plugin_projet_projetstates_id'])."' align='center'");
            echo Search::showItem($output_type,
            Html::convdate($data["date_begin"]),$item_num,$i-$start+1,"align='center'");
            $display = Html::convdate($data["date_end"]);
            if ($data["date_end"] <= date('Y-m-d'))
               $display = "<span class='plugin_projet_date_end'>".Html::convdate($data["date_end"])."</span>";

            echo Search::showItem($output_type,$display,$item_num,$i-$start+1,"align='center'");
               
            echo "</tr>";
         }
         echo "</table></div>";
      }
   }
   
   /**
    * Show for PDF an projet - Hierarchy
    * 
    * @param $pdf object for the output
    * @param $ID of the projet
    */
   static function pdfHierarchyForProjet(PluginPdfSimplePDF $pdf, PluginProjetProjet $appli, $parents=0) {
      global $DB,$CFG_GLPI;
      
      $ID = $appli->fields['id'];

      if (!$appli->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_projet_haveRight("projet","r")) {
         return false;
      }

      $pdf->setColumnsSize(100);
      if ($parents) {
         $pdf->displayTitle('<b>'.__('Parent project', 'projet').'</b>');
      } else {
         $pdf->displayTitle('<b>'._n('Child project', 'Child projects', 2, 'projet').'</b>');
      }

      $first=false;
      $query = "SELECT `".$appli->gettable()."`.*  ";
      if ($parents!=0) {
         $parent = "plugin_projet_projets_id_1";
         $child = "plugin_projet_projets_id_2";
      } else {
         $parent = "plugin_projet_projets_id_2";
         $child = "plugin_projet_projets_id_1";
      }
      $query.= " FROM `glpi_plugin_projet_projets`";
      $query.= " LEFT JOIN `glpi_plugin_projet_projets_projets` 
                  ON (`glpi_plugin_projet_projets_projets`.`$child` = `glpi_plugin_projet_projets`.`id`)";
      $query.= " WHERE `glpi_plugin_projet_projets_projets`.`$parent` = '$ID' ";
      
      if ($appli->maybeTemplate()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`".$appli->getTable()."`.`is_template` = '0' ";
      }
      // Add is_deleted if item have it
      if ($appli->maybeDeleted()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`".$appli->getTable()."`.`is_deleted` = '0' ";
      }   
      $LINK= " AND " ;    
      $query.=getEntitiesRestrictRequest(" AND ",$appli->gettable(),'','',$appli->maybeRecursive());
            
      $query.= " ORDER BY `".$appli->gettable()."`.`name`";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      if (!$number) {
         $pdf->displayLine(__('No item found'));						
      } else {
         if (Session::isMultiEntitiesMode()) {
            $pdf->setColumnsSize(17,17,17,17,16,16);
            $pdf->displayTitle(
               '<b><i>'.__('Name'),
               __('Entity'),
               __('Progress'),
               _n('State', 'States', 1),
               __('Start date'),
               __('End date').'</i></b>'
               );
         } else {
            $pdf->setColumnsSize(20,17,17,17,17);
            $pdf->displayTitle(
               '<b><i>'.__('Name'),
               __('Progress'),
               _n('State', 'States', 1),
               __('Start date'),
               __('End date').'</i></b>'
               );
         }
         
         while ($data=$DB->fetch_array($result)) {
         
            $items_id_display="";
            if ($_SESSION["glpiis_ids_visible"]||empty($data["name"])) $items_id_display= " (".$data["id"].")";
            $name=$data["name"].$items_id_display;
            
            $entity=Html::clean(Dropdown::getDropdownName("glpi_entities",$data['entities_id']));
               
            if (Session::isMultiEntitiesMode()) {
               $pdf->setColumnsSize(17,17,17,17,16,16);
               $pdf->displayLine(
                  $name,
                  $entity,
                  PluginProjetProjet::displayProgressBar('100',$data["advance"],array("simple"=>true)),
                  Html::clean(Dropdown::getDropdownName("glpi_plugin_projet_projetstates",$data['plugin_projet_projetstates_id'])),
                  Html::convdate($data["date_begin"]),
                  Html::convdate($data["date_end"])
                  );
            } else {
               $pdf->setColumnsSize(20,17,17,17,17);
               $pdf->displayLine(
                  $name,
                  PluginProjetProjet::displayProgressBar('100',$data["advance"],array("simple"=>true)),
                  Html::clean(Dropdown::getDropdownName("glpi_plugin_projet_projetstates",$data['plugin_projet_projetstates_id'])),
                  Html::convdate($data["date_begin"]),
                  Html::convdate($data["date_end"])
                  );
            }
         }
      }
      $pdf->displaySpace();
   }
}

?>