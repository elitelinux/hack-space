<?php
/*
 * @version $Id: networkalias.class.php 22657 2014-02-12 16:17:54Z moyo $
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

/// Class NetworkAlias
/// since version 0.84
class NetworkAlias extends FQDNLabel {

   var $refresh_page                 = true;

   // From CommonDBChild
   static public $itemtype           = 'NetworkName';
   static public $items_id           = 'networknames_id';
   public $dohistory                 = true;

   static public $checkParentRights = CommonDBConnexity::HAVE_SAME_RIGHT_ON_ITEM;


   static function getTypeName($nb=0) {
      return _n('Network alias', 'Network aliases', $nb);
   }


   /**
    * Get the full name (internet name) of a NetworkName
    *
    * @param $ID ID of the NetworkName
    *
    * @return its internet name, or empty string if invalid NetworkName
   **/
   static function getInternetNameFromID($ID) {

      $networkAlias = new self();
      if ($networkalias->can($ID, 'r'))
         return FQDNLabel::getInternetNameFromLabelAndDomainID($this->fields["name"],
                                                               $this->fields["fqdns_id"]);
      return "";
   }


   /**
    * Print the network alias form
    *
    * @param $ID        integer ID of the item
    * @param $options   array
    *     - target for the Form
    *     - withtemplate template or basic computer
    *
    * @return Nothing (display)
   **/
   function showForm ($ID, $options=array()) {

      // Show only simple form to add / edit
      $showsimple = false;
      if (isset($options['parent'])) {
         $showsimple                 = true;
         $options['networknames_id'] = $options['parent']->getID();
      }

      $this->initForm($ID, $options);

      $recursiveItems = $this->recursivelyGetItems();
      if (count($recursiveItems) == 0) {
         return false;
      }

      $lastItem = $recursiveItems[count($recursiveItems) - 1];
      if (!$showsimple) {
         $this->showTabs();
      }

      $options['entities_id'] = $lastItem->getField('entities_id');
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'><td>";
      $this->displayRecursiveItems($recursiveItems, 'Type');
      echo "&nbsp;:</td>\n<td>";

      if (!($ID > 0)) {
         echo "<input type='hidden' name='networknames_id' value='".
               $this->fields["networknames_id"]."'>\n";
      }
      $this->displayRecursiveItems($recursiveItems, (isset($options['popup']) ? "Name" : "Link"));
      echo "</td><td>" . __('Name') . "</td><td>\n";
      Html::autocompletionTextField($this, "name");
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".FQDN::getTypeName()."</td><td>";
      Dropdown::show(getItemTypeForTable(getTableNameForForeignKeyField("fqdns_id")),
                     array('value'        => $this->fields["fqdns_id"],
                           'name'         => 'fqdns_id',
                           'entity'       => $this->getEntityID(),
                           'displaywith'  => array('view')));
      echo "</td>";
      echo "<td>".__('Comments')."</td>";
      echo "<td><textarea cols='45' rows='4' name='comment' >".$this->fields["comment"];
      echo "</textarea></td>\n";
      echo "</tr>\n";

      $this->showFormButtons($options);
      if (!$showsimple) {
         $this->addDivForTabs();
      }
      return true;
   }


   /**
    * @since version 0.84
    *
    * @param $itemtype
    * @param $base                  HTMLTableBase object
    * @param $super                 HTMLTableSuperHeader object (default NULL)
    * @param $father                HTMLTableHeader object (default NULL)
    * @param $options      array
   **/
   static function getHTMLTableHeader($itemtype, HTMLTableBase $base,
                                      HTMLTableSuperHeader $super=NULL,
                                      HTMLTableHeader $father=NULL, array $options=array()) {

      $column_name = __CLASS__;
      if (isset($options['dont_display'][$column_name])) {
         return;
      }

      if ($itemtype != 'NetworkName') {
         return;
      }

      $content = self::getTypeName();
      if (isset($options['column_links'][$column_name])) {
         $content = "<a href='".$options['column_links'][$column_name]."'>$content</a>";
      }
      $this_header = $base->addHeader($column_name, $content, $super, $father);
      $this_header->setItemType('NetworkAlias');
   }


   /**
    * @since version 0.84
    *
    * @param $row                HTMLTableRow object (default NULL)
    * @param $item               CommonDBTM object (default NULL)
    * @param $father             HTMLTableCell object (default NULL)
    * @param $options   array
   **/
   static function getHTMLTableCellsForItem(HTMLTableRow $row=NULL, CommonDBTM $item=NULL,
                                            HTMLTableCell $father=NULL, array $options=array()) {
      global $DB, $CFG_GLPI;

      if (empty($item)) {
         if (empty($father)) {
            return;
         }
         $item = $father->getItem();
      }

      if ($item->getType() != 'NetworkName') {
         return;
      }

      $column_name = __CLASS__;
      if (isset($options['dont_display'][$column_name])) {
         return;
      }

      $header = $row->getGroup()->getHeaderByName('Internet', $column_name);
      if (!$header) {
         return;
      }

      $canedit              = (isset($options['canedit']) && $options['canedit']);
      $createRow            = (isset($options['createRow']) && $options['createRow']);
      $options['createRow'] = false;

      $query                = "SELECT `id`
                               FROM `glpi_networkaliases`
                               WHERE `networknames_id` = '".$item->getID()."'";

      $alias                = new self();

      foreach ($DB->request($query) as $line) {
         if ($alias->getFromDB($line["id"])) {

            if ($createRow) {
               $row = $row->createRow();
            }

            $content = "<a href='" . $alias->getLinkURL(). "'>".$alias->getInternetName()."</a>";
            $row->addCell($header, $content, $father, $alias);

         }
      }
   }


   /**
    * \brief Show aliases for an item from its form
    * Beware that the rendering can be different if readden from direct item form (ie : add new
    * NetworkAlias, remove, ...) or if readden from item of the item (for instance from the computer
    * form through NetworkPort::ShowForItem and NetworkName::ShowForItem).
    *
    * @param $item                     NetworkName object
    * @param $withtemplate   integer   withtemplate param (default 0)
   **/
   static function showForNetworkName(NetworkName $item, $withtemplate=0) {
      global $DB, $CFG_GLPI;

      $ID = $item->getID();
      if (!$item->can($ID, 'r')) {
         return false;
      }

      $canedit = $item->can($ID, 'w');
      $rand    = mt_rand();

      $query = "SELECT *
                FROM `glpi_networkaliases`
                WHERE `networknames_id` = '$ID'";

      $result  = $DB->query($query);
      $aliases = array();
      if ($number = $DB->numrows($result)) {
         while ($line = $DB->fetch_assoc($result)) {
            $aliases[$line["id"]] = $line;
         }
      }

      if ($canedit) {
         echo "\n<div class='firstbloc'>";
         echo "<script type='text/javascript' >\n";
         echo "function viewAddAlias$rand() {\n";
         $params = array('type'            => __CLASS__,
                         'parenttype'      => 'NetworkName',
                         'networknames_id' => $ID,
                         'id'              => -1);
         Ajax::updateItemJsCode("viewnetworkalias$rand",
                                $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
         echo "};";
         echo "</script>";
         echo "<a class='vsubmit' href='javascript:viewAddAlias$rand();'>";
         echo __('Add a network alias')."</a>\n";
         echo "</div>\n";
      }
      echo "<div id='viewnetworkalias$rand'></div>";

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('num_displayed' => $number);
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
      }
      echo "<table class='tab_cadre_fixehov'>";

      echo "<tr>";
      if ($canedit && $number) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }
      echo "<th>".__('Name')."</th>";
      echo "<th>"._n('Internet domain', 'Internet domains', 1)."</th>";
      echo "<th>".__('Entity')."</th>";
      echo "</tr>";

      $used = array();
      foreach ($aliases as $data) {
         $showviewjs = ($canedit
                        ? "style='cursor:pointer' onClick=\"viewEditAlias".$data['id']."$rand();\""
                        : '');
         echo "<tr class='tab_bg_1'>";
         if ($canedit) {
            echo "<td>";
            Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
            echo "</td>";
         }
         $name = $data["name"];
         if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
            $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
         }
         echo "<td class='center b' $showviewjs>";
         if ($canedit) {
            echo "\n<script type='text/javascript' >\n";
            echo "function viewEditAlias". $data["id"]."$rand() {\n";
            $params = array('type'             => __CLASS__,
                            'parenttype'       => 'NetworkName',
                            'networknames_id'  => $ID,
                            'id'               => $data["id"]);
            Ajax::updateItemJsCode("viewnetworkalias$rand",
                                   $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
            echo "};";
            echo "</script>\n";
         }
         echo "<a href='".static::getFormURL()."?id=".$data["id"]."'>".$name."</a>";
         echo "</td>";
         echo "<td class='center' $showviewjs>".Dropdown::getDropdownName("glpi_fqdns",
                                                                          $data["fqdns_id"]);
         echo "<td class='center' $showviewjs>".Dropdown::getDropdownName("glpi_entities",
                                                                          $data["entities_id"]);
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


   /**
    * Show the aliases contained by the alias
    *
    * @param $item                     the FQDN owning the aliases
    * @param $withtemplate  integer    withtemplate param
   **/
   static function showForFQDN(CommonGLPI $item, $withtemplate) {
      global $DB;

      $alias   = new self();
      $address = new NetworkName();
      $item->check($item->getID(), 'r');
      $canedit = $item->can($item->getID(), 'w');

      if (isset($_POST["start"])) {
         $start = $_POST["start"];
      } else {
         $start = 0;
      }
      if (!empty($_POST["order"])) {
         $order = $_POST["order"];
      } else {
         $order = "alias";
      }

      $number = countElementsInTable($alias->getTable(), "`fqdns_id`='".$item->getID()."'");

      echo "<br><div class='center'>";

      if ($number < 1) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>".self::getTypeName(1)."</th><th>".__('No item found')."</th></tr>";
         echo "</table>\n";
      } else {
         Html::printAjaxPager(self::getTypeName($number), $start, $number);

         echo "<table class='tab_cadre_fixe'><tr>";

         echo "<th><a href='javascript:reloadTab(\"order=alias\");'>".self::getTypeName(1).
              "</a></th>"; // Alias
         echo "<th><a href='javascript:reloadTab(\"order=realname\");'>".__("Computer's name").
              "</a></th>";
         echo "<th>".__('Comments')."</th>";
         echo "</tr>\n";

         Session::initNavigateListItems($item->getType(),
         //TRANS : %1$s is the itemtype name, %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                self::getTypeName(1), $item->fields['name']));

         $query = "SELECT `glpi_networkaliases`.`id` AS alias_id,
                          `glpi_networkaliases`.`name` AS alias,
                          `glpi_networknames`.`id` AS address_id,
                          `glpi_networkaliases`.`comment` AS comment
                   FROM `glpi_networkaliases`, `glpi_networknames`
                   WHERE `glpi_networkaliases`.`fqdns_id` = '".$item->getID()."'
                         AND  `glpi_networknames`.`id` = `glpi_networkaliases`.`networknames_id`
                   ORDER BY `$order`
                   LIMIT ".$_SESSION['glpilist_limit']."
                   OFFSET $start";

         foreach ($DB->request($query) as $data) {
            Session::addToNavigateListItems($alias->getType(),$data["alias_id"]);
            if ($address->getFromDB($data["address_id"])) {
               echo "<tr class='tab_bg_1'>";
               echo "<td><a href='".$alias->getFormURL().'?id='.$data['alias_id']."'>" .
                          $data['alias']. "</a></td>";
               echo "<td><a href='".$address->getLinkURL()."'>".$address->getInternetName().
                    "</a></td>";
               echo "<td>".$data['comment']."</td>";
               echo "</tr>\n";
            }
         }

         echo "</table>\n";
         Html::printAjaxPager(self::getTypeName($number), $start, $number);

      }
      echo "</div>\n";
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'NetworkName' :
            self::showForNetworkName($item, $withtemplate);
            break;

         case 'FQDN' :
            self::showForFQDN($item, $withtemplate);
            break;
      }
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getID()
          && $item->can($item->getField('id'),'r')) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            switch ($item->getType()) {
               case 'NetworkName' :
                  $numberElements = countElementsInTable($this->getTable(),
                                                         "networknames_id='".$item->getID()."'");
                  break;

               case 'FQDN' :
                  $numberElements = countElementsInTable($this->getTable(),
                                                         "fqdns_id='".$item->getID()."'");
            }
            return self::createTabEntry(self::getTypeName(2), $numberElements);
         }
         return self::getTypeName(2);
      }
      return '';
   }


   function getSearchOptions() {

      $tab                      = parent::getSearchOptions();

      $tab[12]['table']         = 'glpi_fqdns';
      $tab[12]['field']         = 'fqdn';
      $tab[12]['name']          = FQDN::getTypeName(1);
      $tab[12]['datatype']      = 'string';

      $tab[20]['table']         = 'glpi_networknames';
      $tab[20]['field']         = 'name';
      $tab[20]['name']          = NetworkName::getTypeName(1);
      $tab[20]['massiveaction'] = false;
      $tab[20]['datatype']      = 'dropdown';

      return $tab;
   }
}
?>
