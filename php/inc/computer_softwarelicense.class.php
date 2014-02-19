<?php
/*
 * @version $Id: computer_softwarelicense.class.php 21364 2013-07-19 15:38:35Z yllen $
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

class Computer_SoftwareLicense extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1 = 'Computer';
   static public $items_id_1 = 'computers_id';

   static public $itemtype_2 = 'SoftwareLicense';
   static public $items_id_2 = 'softwarelicenses_id';



   /**
    * Get search function for the class
    *
    * @since version 0.84
    *
    * @return array of search option
   **/
   function getSearchOptions() {

      $tab                       = array();
      $tab['common']             = __('Characteristics');

      $tab[2]['table']           = $this->getTable();
      $tab[2]['field']           = 'id';
      $tab[2]['name']            = __('ID');
      $tab[2]['massiveaction']   = false;
      $tab[2]['datatype']        = 'number';

      $tab[4]['table']           = 'glpi_softwarelicenses';
      $tab[4]['field']           = 'name';
      $tab[4]['name']            = _n('License', 'Licenses', 1);
      $tab[4]['datatype']        = 'dropdown';
      $tab[4]['massiveaction']   = false;

      $tab[5]['table']           = 'glpi_computers';
      $tab[5]['field']           = 'name';
      $tab[5]['name']            = _n('Computer', 'Computers', 1);
      $tab[5]['massiveaction']   = false;
      $tab[5]['datatype']        = 'dropdown';

      return $tab;
   }


   /**
    * @since version 0.84
    *
    * @see CommonDBTM::showSpecificMassiveActionsParameters()
   **/
   function showSpecificMassiveActionsParameters($input=array()) {

      switch ($input['action']) {
         case "move_license" :
            if (isset($input['options'])) {
               $input['options'] = Toolbox::decodeArrayFromInput($input['options']);
               if (isset($input['options']['move'])) {
                  SoftwareLicense::dropdown(array('condition'
                                                   => "`glpi_softwarelicenses`.`softwares_id`
                                                        = '".$input['options']['move']['softwares_id']."'",
                                                  'used'
                                                   => $input['options']['move']['used']));
                     echo "<br><br><input type='submit' name='massiveaction' value=\"".
                                    _sx('button','Move')."\" class='submit'>&nbsp;";
                  return true;
               }
            }
            return false;

         default :
            return parent::showSpecificMassiveActionsParameters($input);

      }
      return false;
   }


   /**
    * @since version 0.84
    *
    * @see CommonDBTM::doSpecificMassiveActions()
   **/
   function doSpecificMassiveActions($input=array()) {

      $res = array('ok'      => 0,
                   'ko'      => 0,
                   'noright' => 0);

      switch ($input['action']) {
         case "move_license" :
            if (isset($input['softwarelicenses_id'])) {
               foreach ($input["item"] as $key => $val) {
                  if ($val == 1) {
                     //Get software name and manufacturer
                     if ($this->can($key,'w')) {
                        //Process rules
                        if ($this->update(array('id'  => $key,
                                                'softwarelicenses_id'
                                                      => $input['softwarelicenses_id']))) {
                           $res['ok']++;
                        } else {
                           $res['ko']++;
                        }
                     } else {
                        $res['noright']++;
                     }
                  }
               }
            } else {
               $res['ko']++;
            }
            break;

         case "install" :
            $csl = new self();
            $csv = new Computer_SoftwareVersion();
            foreach ($input["item"] as $key => $val) {
               if ($val == 1) {
                  if ($csl->getFromDB($key)) {
                     $sl = new SoftwareLicense();

                     if ($sl->getFromDB($csl->fields["softwarelicenses_id"])) {
                        $version = 0;
                        if ($sl->fields["softwareversions_id_use"]>0) {
                           $version = $sl->fields["softwareversions_id_use"];
                        } else {
                           $version = $sl->fields["softwareversions_id_buy"];
                        }

                        if ($version > 0) {
                           $params = array('computers_id'       => $csl->fields['computers_id'],
                                          'softwareversions_id' => $version);
                           //Get software name and manufacturer
                           if ($csv->can(-1,'w', $params)) {
                              //Process rules
                              if ($csv->add($params)) {
                                 $res['ok']++;
                              } else {
                                 $res['ko']++;
                              }
                           } else {
                              $res['noright']++;
                           }
                        } else {
                           $res['ko']++;
                        }
                     } else {
                        $res['ko']++;
                     }
                  } else {
                     $res['ko']++;
                  }
               }
            }
            break;
         default :
            return parent::doSpecificMassiveActions($input);
      }
      return $res;
   }


   /**
    * Get number of installed licenses of a license
    *
    * @param $softwarelicenses_id   license ID
    * @param $entity                to search for computer in (default = all active entities)
    *                               (default '')
    *
    * @return number of installations
   **/
   static function countForLicense($softwarelicenses_id, $entity='') {
      global $DB;

      $query = "SELECT COUNT(`glpi_computers_softwarelicenses`.`id`)
                FROM `glpi_computers_softwarelicenses`
                INNER JOIN `glpi_computers`
                      ON (`glpi_computers_softwarelicenses`.`computers_id` = `glpi_computers`.`id`)
                WHERE `glpi_computers_softwarelicenses`.`softwarelicenses_id` = '$softwarelicenses_id'
                      AND `glpi_computers`.`is_deleted` = '0'
                      AND `glpi_computers`.`is_template` = '0'
                      AND `glpi_computers_softwarelicenses`.`is_deleted` = '0'" .
                      getEntitiesRestrictRequest('AND', 'glpi_computers','',$entity);

      $result = $DB->query($query);

      if ($DB->numrows($result) != 0) {
         return $DB->result($result, 0, 0);
      }
      return 0;
   }


   /**
    * Get number of installed licenses of a software
    *
    * @param $softwares_id software ID
    *
    * @return number of installations
   **/
   static function countForSoftware($softwares_id) {
      global $DB;

      $query = "SELECT COUNT(`glpi_computers_softwarelicenses`.`id`)
                FROM `glpi_softwarelicenses`
                INNER JOIN `glpi_computers_softwarelicenses`
                      ON (`glpi_softwarelicenses`.`id`
                          = `glpi_computers_softwarelicenses`.`softwarelicenses_id`)
                INNER JOIN `glpi_computers`
                      ON (`glpi_computers_softwarelicenses`.`computers_id` = `glpi_computers`.`id`)
                WHERE `glpi_softwarelicenses`.`softwares_id` = '$softwares_id'
                      AND `glpi_computers`.`is_deleted` = '0'
                      AND `glpi_computers`.`is_template` = '0'
                      AND `glpi_computers_softwarelicenses`.`is_deleted` = '0'" .
                      getEntitiesRestrictRequest('AND', 'glpi_computers');

      $result = $DB->query($query);

      if ($DB->numrows($result) != 0) {
         return $DB->result($result, 0, 0);
      }
      return 0;
   }


   /**
    * Show number of installation per entity
    *
    * @param $license SoftwareLicense object
    *
    * @return nothing
   **/
   static function showForLicenseByEntity(SoftwareLicense $license) {
      global $DB, $CFG_GLPI;

      $softwarelicense_id = $license->getField('id');

      if (!Session::haveRight("software", "r") || !$softwarelicense_id) {
         return false;
      }

      echo "<div class='center'>";
      echo "<table class='tab_cadre'><tr>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Number of affected computers')."</th>";
      echo "</tr>\n";

      $tot = 0;

      $sql = "SELECT `id`, `completename`
              FROM `glpi_entities` " .
              getEntitiesRestrictRequest('WHERE', 'glpi_entities') ."
              ORDER BY `completename`";

      foreach ($DB->request($sql) as $ID => $data) {
         $nb = self::countForLicense($softwarelicense_id,$ID);
         if ($nb > 0) {
            echo "<tr class='tab_bg_2'><td>" . $data["completename"] . "</td>";
            echo "<td class='numeric'>".$nb."</td></tr>\n";
            $tot += $nb;
         }
      }

      if ($tot > 0) {
         echo "<tr class='tab_bg_1'><td class='center b'>".__('Total')."</td>";
         echo "<td class='numeric b '>".$tot."</td></tr>\n";
      } else {
         echo "<tr class='tab_bg_1'><td colspan='2 b'>" . __('No item found') . "</td></tr>\n";
      }
      echo "</table></div>";
   }


   /**
    * Show computers linked to a License
    *
    * @param $license SoftwareLicense object
    *
    * @return nothing
   **/
   static function showForLicense(SoftwareLicense $license) {
      global $DB, $CFG_GLPI;

      $searchID = $license->getField('id');

      if (!Session::haveRight("software", "r") || !$searchID) {
         return false;
      }

      $canedit         = Session::haveRight("software", "w");
      $canshowcomputer = Session::haveRight("computer", "r");


      if (isset($_POST["start"])) {
         $start = $_POST["start"];
      } else {
         $start = 0;
      }

      if (isset($_POST["order"]) && ($_POST["order"] == "DESC")) {
         $order = "DESC";
      } else {
         $order = "ASC";
      }

      if (isset($_POST["sort"]) && !empty($_POST["sort"])) {
         // manage several param like location,compname : order first
         $tmp  = explode(",",$_POST["sort"]);
         $sort = "`".implode("` $order,`",$tmp)."`";
      } else {
         $sort = "`entity` $order, `compname`";
      }

      //SoftwareLicense ID
      $query_number = "SELECT COUNT(*) AS cpt
                       FROM `glpi_computers_softwarelicenses`
                       INNER JOIN `glpi_computers`
                           ON (`glpi_computers_softwarelicenses`.`computers_id`
                                 = `glpi_computers`.`id`)
                       WHERE `glpi_computers_softwarelicenses`.`softwarelicenses_id` = '$searchID'".
                             getEntitiesRestrictRequest(' AND', 'glpi_computers') ."
                             AND `glpi_computers`.`is_deleted` = '0'
                             AND `glpi_computers`.`is_template` = '0'
                             AND `glpi_computers_softwarelicenses`.`is_deleted` = '0'";

      $number = 0;
      if ($result = $DB->query($query_number)) {
         $number = $DB->result($result, 0, 0);
      }

      echo "<div class='center'>";

      if ($canedit) {
         echo "<form method='post' action='".
                $CFG_GLPI["root_doc"]."/front/computer_softwarelicense.form.php'>";
         echo "<input type='hidden' name='softwarelicenses_id' value='$searchID'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2 center'>";
         echo "<td>";
         Computer::dropdown(array('entity'      => $license->fields['entities_id'],
                                  'entity_sons' => $license->fields['is_recursive']));

         echo "</td>";
         echo "<td><input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>";

         echo "</table>";
         Html::closeForm();
      }

      if ($number < 1) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>".__('No item found')."</th></tr>";
         echo "</table></div>\n";
         return;
      }

      // Display the pager
      Html::printAjaxPager(__('Affected computers'), $start, $number);

      $query = "SELECT `glpi_computers_softwarelicenses`.*,
                       `glpi_computers`.`name` AS compname,
                       `glpi_computers`.`id` AS cID,
                       `glpi_computers`.`serial`,
                       `glpi_computers`.`otherserial`,
                       `glpi_users`.`name` AS username,
                       `glpi_users`.`id` AS userid,
                       `glpi_users`.`realname` AS userrealname,
                       `glpi_users`.`firstname` AS userfirstname,
                       `glpi_softwarelicenses`.`name` AS license,
                       `glpi_softwarelicenses`.`id` AS vID,
                       `glpi_softwarelicenses`.`name` AS vername,
                       `glpi_entities`.`completename` AS entity,
                       `glpi_locations`.`completename` AS location,
                       `glpi_states`.`name` AS state,
                       `glpi_groups`.`name` AS groupe,
                       `glpi_softwarelicenses`.`name` AS lname,
                       `glpi_softwarelicenses`.`id` AS lID
                FROM `glpi_computers_softwarelicenses`
                INNER JOIN `glpi_softwarelicenses`
                     ON (`glpi_computers_softwarelicenses`.`softwarelicenses_id`
                          = `glpi_softwarelicenses`.`id`)
                INNER JOIN `glpi_computers`
                     ON (`glpi_computers_softwarelicenses`.`computers_id` = `glpi_computers`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_computers`.`entities_id` = `glpi_entities`.`id`)
                LEFT JOIN `glpi_locations`
                     ON (`glpi_computers`.`locations_id` = `glpi_locations`.`id`)
                LEFT JOIN `glpi_states` ON (`glpi_computers`.`states_id` = `glpi_states`.`id`)
                LEFT JOIN `glpi_groups` ON (`glpi_computers`.`groups_id` = `glpi_groups`.`id`)
                LEFT JOIN `glpi_users` ON (`glpi_computers`.`users_id` = `glpi_users`.`id`)
                WHERE (`glpi_softwarelicenses`.`id` = '$searchID') " .
                       getEntitiesRestrictRequest(' AND', 'glpi_computers') ."
                       AND `glpi_computers`.`is_deleted` = '0'
                       AND `glpi_computers`.`is_template` = '0'
                       AND `glpi_computers_softwarelicenses`.`is_deleted` = '0'
                ORDER BY $sort $order
                LIMIT ".intval($start)."," . intval($_SESSION['glpilist_limit']);

      $rand = mt_rand();

      if ($result = $DB->query($query)) {
         if ($data = $DB->fetch_assoc($result)) {

            if ($canedit) {
               $rand = mt_rand();
               Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
               $paramsma = array('num_displayed'    => $_SESSION['glpilist_limit'],
                                 'specific_actions' => array('move_license'
                                                                      => _x('button', 'Move'),
                                                             'delete' => _x('button',
                                                                           'Delete permanently')));
               // Options to update license
               $paramsma['extraparams']['options']['move']['used'] = array($searchID);
               $paramsma['extraparams']['options']['move']['softwares_id']
                                                                   = $license->fields['softwares_id'];

               Html::showMassiveActions(__CLASS__, $paramsma);
            }

            $soft = new Software();
            $soft->getFromDB($license->fields['softwares_id']);
            $showEntity = ($license->isRecursive());
            $linkUser   = Session::haveRight('user', 'r');

            $text = sprintf(__('%1$s = %2$s'), Software::getTypeName(1), $soft->fields["name"]);
            $text = sprintf(__('%1$s - %2$s'), $text, $data["vername"]);

            Session::initNavigateListItems('Computer', $text);

            $sort_img = "<img src='" . $CFG_GLPI["root_doc"] . "/pics/" .
                          ($order == "DESC" ? "puce-down.png" : "puce-up.png") . "' alt='' title=''>";
            echo "<table class='tab_cadre_fixehov'><tr>";
            if ($canedit) {
               echo "<th width='10'>";
               Html::checkAllAsCheckbox('mass'.__CLASS__.$rand);
               echo "</th>";
            }
            echo "<th>".($sort=="`compname`"?$sort_img:"").
                 "<a href='javascript:reloadTab(\"sort=compname&amp;order=".
                   ($order=="ASC"?"DESC":"ASC")."&amp;start=0\");'>".__('Name')."</a></th>";

            if ($showEntity) {
               echo "<th>".(strstr($sort,"entity")?$sort_img:"").
                    "<a href='javascript:reloadTab(\"sort=entity,compname&amp;order=".
                      ($order=="ASC"?"DESC":"ASC")."&amp;start=0\");'>".__('Entity')."</a></th>";
            }

            echo "<th>".($sort=="`serial`"?$sort_img:"").
                 "<a href='javascript:reloadTab(\"sort=serial&amp;order=".
                   ($order=="ASC"?"DESC":"ASC")."&amp;start=0\");'>".__('Serial number')."</a></th>";
            echo "<th>".($sort=="`otherserial`"?$sort_img:"").
                 "<a href='javascript:reloadTab(\"sort=otherserial&amp;order=".
                   ($order=="ASC"?"DESC":"ASC")."&amp;start=0\");'>".__('Inventory number').
                 "</a></th>";
            echo "<th>".(strstr($sort,"`location`")?$sort_img:"").
                 "<a href='javascript:reloadTab(\"sort=location,compname&amp;order=".
                   ($order=="ASC"?"DESC":"ASC")."&amp;start=0\");'>".__('Location')."</a></th>";
            echo "<th>".(strstr($sort,"state")?$sort_img:"").
                 "<a href='javascript:reloadTab(\"sort=state,compname&amp;order=".
                   ($order=="ASC"?"DESC":"ASC")."&amp;start=0\");'>".__('Status')."</a></th>";
            echo "<th>".(strstr($sort,"groupe")?$sort_img:"").
                 "<a href='javascript:reloadTab(\"sort=groupe,compname&amp;order=".
                   ($order=="ASC"?"DESC":"ASC")."&amp;start=0\");'>".__('Group')."</a></th>";
            echo "<th>".(strstr($sort,"username")?$sort_img:"").
                 "<a href='javascript:reloadTab(\"sort=username,compname&amp;order=".
                   ($order=="ASC"?"DESC":"ASC")."&amp;start=0\");'>".__('User')."</a></th>";
            echo "</tr>\n";

            do {
               Session::addToNavigateListItems('Computer',$data["cID"]);

               echo "<tr class='tab_bg_2'>";
               if ($canedit) {
                  echo "<td>".Html::getMassiveActionCheckBox(__CLASS__, $data["id"])."</td>";
               }

               $compname = $data['compname'];
               if (empty($compname) || $_SESSION['glpiis_ids_visible']) {
                  $compname = sprintf(__('%1$s (%2$s)'), $compname, $data['cID']);
               }

               if ($canshowcomputer) {
                  echo "<td><a href='computer.form.php?id=".$data['cID']."'>$compname</a></td>";
               } else {
                  echo "<td>".$compname."</td>";
               }

               if ($showEntity) {
                  echo "<td>".$data['entity']."</td>";
               }
               echo "<td>".$data['serial']."</td>";
               echo "<td>".$data['otherserial']."</td>";
               echo "<td>".$data['location']."</td>";
               echo "<td>".$data['state']."</td>";
               echo "<td>".$data['groupe']."</td>";
               echo "<td>".formatUserName($data['userid'], $data['username'], $data['userrealname'],
                                          $data['userfirstname'], $linkUser)."</td>";
               echo "</tr>\n";

            } while ($data=$DB->fetch_assoc($result));

            echo "</table>\n";
            if ($canedit) {
               $paramsma['ontop'] = false;
               Html::showMassiveActions(__CLASS__, $paramsma);
               Html::closeForm();
            }

         } else { // Not found
            _e('No item found');
         }
      } // Query
      Html::printAjaxPager(__('Affected computers'), $start, $number);

      echo "</div>\n";

   }


   /**
    * Update license associated on a computer
    *
    * @param $licID                 ID of the install software lienk
    * @param $softwarelicenses_id   ID of the new license
    *
    * @return nothing
   **/
   function upgrade($licID, $softwarelicenses_id) {
      global $DB;

      if ($this->getFromDB($licID)) {
         $computers_id = $this->fields['computers_id'];
         $this->delete(array('id' => $licID));
         $this->add(array('computers_id'        => $computers_id,
                          'softwarelicenses_id' => $softwarelicenses_id));
      }
   }


   /**
    * Get licenses list corresponding to an installation
    *
    * @param $computers_id          ID of the computer
    * @param $softwareversions_id   ID of the version
    *
    * @return nothing
   **/
   static function GetLicenseForInstallation($computers_id, $softwareversions_id) {
      global $DB;

      $lic = array();
      $sql = "SELECT `glpi_softwarelicenses`.*,
                     `glpi_softwarelicensetypes`.`name` AS type
              FROM `glpi_softwarelicenses`
              INNER JOIN `glpi_computers_softwarelicenses`
                  ON (`glpi_softwarelicenses`.`id`
                           = `glpi_computers_softwarelicenses`.`softwarelicenses_id`
                      AND `glpi_computers_softwarelicenses`.`computers_id` = '$computers_id')
              LEFT JOIN `glpi_softwarelicensetypes`
                  ON (`glpi_softwarelicenses`.`softwarelicensetypes_id`
                           =`glpi_softwarelicensetypes`.`id`)
              WHERE `glpi_softwarelicenses`.`softwareversions_id_use` = '$softwareversions_id'
                    OR `glpi_softwarelicenses`.`softwareversions_id_buy` = '$softwareversions_id'";

      foreach ($DB->request($sql) as $ID => $data) {
         $lic[$data['id']] = $data;
      }
      return $lic;
   }


   /**
    * Duplicate all software licenses from a computer template to its clone
    *
    * @param $oldid ID of the computer to clone
    * @param $newid ID of the computer cloned
   **/
   static function cloneComputer($oldid, $newid) {
      global $DB;

      $query = "SELECT *
                FROM `glpi_computers_softwarelicenses`
                WHERE `computers_id` = '$oldid'";

      foreach ($DB->request($query) as $data) {
         $csl                  = new self();
         unset($data['id']);
         $data['computers_id'] = $newid;
         $data['_no_history']  = true;

         $csl->add($data);
      }
   }


   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
  function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      switch ($item->getType()) {
         case 'SoftwareLicense' :
            if (!$withtemplate) {
               $nb = 0;
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb = self::countForLicense($item->getID());
               }
               return array(1 => SoftwareLicense::getTypeName(1),
                            2 => self::createTabEntry(Computer::getTypeName(2), $nb));
            }
            break;
      }
      return '';
   }


   /**
    * @param $item         CommonGLPI object
    * @param $tabnum       (default 1)
    * @param $withtemplate (default 0)
   **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'SoftwareLicense') {
         switch ($tabnum) {
            case 1 :
               self::showForLicenseByEntity($item);
               break;

            case 2 :
               self::showForLicense($item);
               break;
         }
      }
      return true;
   }
}
?>