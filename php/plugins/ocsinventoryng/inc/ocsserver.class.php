<?php
/*
 * @version $Id: HEADER 15930 2012-12-15 11:10:55Z tsmr $
-------------------------------------------------------------------------
Ocsinventoryng plugin for GLPI
Copyright (C) 2012-2013 by the ocsinventoryng plugin Development Team.

https://forge.indepnet.net/projects/ocsinventoryng
-------------------------------------------------------------------------

LICENSE

This file is part of ocsinventoryng.

Ocsinventoryng plugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Ocsinventoryng plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ocsinventoryng. If not, see <http://www.gnu.org/licenses/>.
---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


/// OCS config class
class PluginOcsinventoryngOcsServer extends CommonDBTM {

   static $types = array('Computer');

   // From CommonDBTM
   public $dohistory = true;

   const OCS_VERSION_LIMIT    = 4020;
   const OCS1_3_VERSION_LIMIT = 5004;
   const OCS2_VERSION_LIMIT   = 6000;

   // Class constants - import_ management
   const FIELD_SEPARATOR = '$$$$$';
   const IMPORT_TAG_070  = '_version_070_';
   const IMPORT_TAG_072  = '_version_072_';
   const IMPORT_TAG_078  = '_version_078_';

   // Class constants - OCSNG Flags on Checksum
   const HARDWARE_FL          = 0;
   const BIOS_FL              = 1;
   const MEMORIES_FL          = 2;
   // not used const SLOTS_FL       = 3;
   const REGISTRY_FL          = 4;
   // not used const CONTROLLERS_FL = 5;
   const MONITORS_FL          = 6;
   const PORTS_FL             = 7;
   const STORAGES_FL          = 8;
   const DRIVES_FL            = 9;
   const INPUTS_FL            = 10;
   const MODEMS_FL            = 11;
   const NETWORKS_FL          = 12;
   const PRINTERS_FL          = 13;
   const SOUNDS_FL            = 14;
   const VIDEOS_FL            = 15;
   const SOFTWARES_FL         = 16;
   const VIRTUALMACHINES_FL   = 17;
   const MAX_CHECKSUM         = 262143;

   // Class constants - Update result
   const COMPUTER_IMPORTED       = 0; //Computer is imported in GLPI
   const COMPUTER_SYNCHRONIZED   = 1; //Computer is synchronized
   const COMPUTER_LINKED         = 2; //Computer is linked to another computer already in GLPI
   const COMPUTER_FAILED_IMPORT  = 3; //Computer cannot be imported because it matches none of the rules
   const COMPUTER_NOTUPDATED     = 4; //Computer should not be updated, nothing to do
   const COMPUTER_NOT_UNIQUE     = 5; //Computer import is refused because it's not unique
   const COMPUTER_LINK_REFUSED   = 6; //Computer cannot be imported because a rule denies its import

   const LINK_RESULT_IMPORT    = 0;
   const LINK_RESULT_NO_IMPORT = 1;
   const LINK_RESULT_LINK      = 2;


   static function getTypeName($nb=0) {
      return _n('OCSNG server', 'OCSNG servers', $nb,'ocsinventoryng');
   }


   static function canCreate() {
      return plugin_ocsinventoryng_haveRight('ocsng', 'w');
   }


   static function canView() {
      return plugin_ocsinventoryng_haveRight('ocsng', 'r');
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case __CLASS__ :
               //If connection to the OCS DB  is ok, and all rights are ok too
               $ong[1] = self::getTypeName();
               if (self::checkOCSconnection($item->getID())
                   && self::checkConfig(1)
                   && self::checkConfig(2)
                   && self::checkConfig(8)) {
                  $ong[2] = __('Import options', 'ocsinventoryng');
                  $ong[3] = __('General information', 'ocsinventoryng');
               }
               if ($item->getField('ocs_url')) {
                  $ong[4] = __('OCSNG console', 'ocsinventoryng');
               }

               return $ong;
        }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 1 :
               $item->showDBConnectionStatus($item->getID());
               break;

            case 2 :
               $item->ocsFormImportOptions($_POST['target'], $item->getID());
               break;

            case 3 :
               $item->ocsFormConfig($_POST['target'], $item->getID());
               break;

            case 4 :
               self::showOcsReportsConsole($item->getID());
               break;

         }
      }
      return true;
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('PluginOcsinventoryngConfig', $ong, $options);
      $this->addStandardTab('PluginOcsinventoryngOcslink', $ong, $options);
      $this->addStandardTab('PluginOcsinventoryngRegistryKey', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }


   function getSearchOptions() {

      $tab                       = array();

      $tab['common']             = self::getTypeName();

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();
      $tab[1]['massiveaction']   = false;

      $tab[2]['table']           = $this->getTable();
      $tab[2]['field']           = 'id';
      $tab[2]['name']            = __('ID');
      $tab[2]['massiveaction']   = false;

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'ocs_db_host';
      $tab[3]['name']            = __('Server');

      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'is_active';
      $tab[6]['name']            = __('Active');
      $tab[6]['datatype']        = 'bool';

      $tab[19]['table']          = $this->getTable();
      $tab[19]['field']          = 'date_mod';
      $tab[19]['name']           = __('Last update');
      $tab[19]['datatype']       = 'datetime';
      $tab[19]['massiveaction']  = false;

      $tab[16]['table']          = $this->getTable();
      $tab[16]['field']          = 'comment';
      $tab[16]['name']           = __('Comments');
      $tab[16]['datatype']       = 'text';

      $tab[17]['table']          = $this->getTable();
      $tab[17]['field']          = 'use_massimport';
      $tab[17]['name']           = __('Expert sync mode', 'ocsinventoryng');
      $tab[17]['datatype']       = 'bool';

      $tab[18]['table']          = $this->getTable();
      $tab[18]['field']          = 'ocs_db_utf8';
      $tab[18]['name']           = __('Database in UTF8', 'ocsinventoryng');
      $tab[18]['datatype']       = 'bool';

      return $tab;
   }

   /**
    * Print ocs menu
    *
    * @param $plugin_ocsinventoryng_ocsservers_id Integer : Id of the ocs config
    *
    * @return Nothing (display)
   **/
   static function ocsMenu($plugin_ocsinventoryng_ocsservers_id) {
      global $CFG_GLPI, $DB;

      $name = "";

      echo "<div class='center'>";
      echo "<img src='" . $CFG_GLPI["root_doc"]."/plugins/ocsinventoryng/pics/ocsinventoryng.png' ".
             "alt='OCS Inventory NG' title='OCS Inventory NG'>";
      echo "</div>";
      $numberActiveServers = countElementsInTable('glpi_plugin_ocsinventoryng_ocsservers',
                                                 "`is_active`='1'");
      if ($numberActiveServers > 0) {
         echo "<form action=\"".$CFG_GLPI['root_doc']."/plugins/ocsinventoryng/front/ocsng.php\"
                method='post'>";
         echo "<div class='center'><table class='tab_cadre' width='40%'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Choice of an OCSNG server', 'ocsinventoryng').
              "</th></tr>\n";

         echo "<tr class='tab_bg_2'><td class='center'>" . __('Name'). "</td>";
         echo "<td class='center'>";
         Dropdown::show('PluginOcsinventoryngOcsServer',
                        array("condition" => "`is_active`='1'",
                              "value"     => $_SESSION["plugin_ocsinventoryng_ocsservers_id"],
                              "on_change" => "submit()",
                              "display_emptychoice"
                                          => false));
         echo "</td></tr></table></div>";
         Html::closeForm();
      }

      $sql = "SELECT `name`, `is_active`
              FROM `glpi_plugin_ocsinventoryng_ocsservers`
              WHERE `id` = '".$plugin_ocsinventoryng_ocsservers_id."'";
      $result = $DB->query($sql);
      $isactive = 0;
      if ($DB->numrows($result) > 0) {
         $datas = $DB->fetch_array($result);
         $name  = " : " . $datas["name"];
         $isactive = $datas["is_active"];
      }

      $usemassimport = PluginOcsinventoryngOcsServer::useMassImport();

      echo "<div class='center'><table class='tab_cadre' width='40%'>";
      echo "<tr><th colspan='".($usemassimport?4:2)."'>";
      printf(__('%1$s %2$s'), __('OCSNG server', 'ocsinventoryng'), $name);
      echo "</th></tr>";
      
      
      if (plugin_ocsinventoryng_haveRight('ocsng','w')) {
      
         //config server
         echo "<tr class='tab_bg_1'><td class='center b' colspan='2'>
               <a href='ocsserver.form.php?id=$plugin_ocsinventoryng_ocsservers_id'>
                <img src='" . $CFG_GLPI["root_doc"] . "/plugins/ocsinventoryng/pics/ocsserver.png' ".
                  "alt='".__s("Configuration of OCSNG server", 'ocsinventoryng')."' ".
                  "title=\"".__s("Configuration of OCSNG server", 'ocsinventoryng')."\">
                <br>".sprintf(__('Configuration of OCSNG server %s', 'ocsinventoryng'),
                                                   $name)."
               </a></td>";
         
         if ($isactive) {
         
            if ($usemassimport) {
               //config massimport
               echo "<td class='center b' colspan='2'>
                     <a href='config.form.php'>
                      <img src='" . $CFG_GLPI["root_doc"] . "/plugins/ocsinventoryng/pics/synchro.png' ".
                        "alt='".__s("Automatic synchronization's configuration", 'ocsinventoryng')."' ".
                        "title=\"".__s("Automatic synchronization's configuration", 'ocsinventoryng')."\">
                        <br>".__("Automatic synchronization's configuration", 'ocsinventoryng')."
                     </a></td>";
            }
            echo "</tr>\n";

            //manual import
            echo "<tr class='tab_bg_1'><td class='center b' colspan='2'>
                  <a href='ocsng.import.php'>
                   <img src='" . $CFG_GLPI["root_doc"] . "/plugins/ocsinventoryng/pics/import.png' ".
                     "alt='".__s('Import new computers', 'ocsinventoryng')."' ".
                     "title=\"".__s('Import new computers', 'ocsinventoryng')."\">
                     <br>".__('Import new computers', 'ocsinventoryng')."
                  </a></td>";
            if ($usemassimport) {
               //threads
               echo "<td class='center b' colspan='2'>
                     <a href='thread.php'>
                      <img src='" . $CFG_GLPI["root_doc"] . "/plugins/ocsinventoryng/pics/thread.png' ".
                        "alt='".__s('Scripts execution of automatic actions', 'ocsinventoryng'). "' ".
                        "title=\"" . __s('Scripts execution of automatic actions', 'ocsinventoryng') . "\">
                        <br>".__('Scripts execution of automatic actions', 'ocsinventoryng')."
                     </a></td>";
            }
            echo "</tr>\n";

            //manual synchro
            echo "<tr class='tab_bg_1'><td class='center b' colspan='2'>
                  <a href='ocsng.sync.php'>
                   <img src='" . $CFG_GLPI["root_doc"]."/plugins/ocsinventoryng/pics/synchro1.png' ".
                     "alt='" .__s('Synchronize computers already imported', 'ocsinventoryng'). "' ".
                     "title=\"" .__s('Synchronize computers already imported', 'ocsinventoryng'). "\">
                     <br>".__('Synchronize computers already imported', 'ocsinventoryng')."
                  </a></td>";
            if ($usemassimport) {
               //host imported by thread
               echo "<td class='center b' colspan='2'>
                     <a href='detail.php'>
                      <img src='" . $CFG_GLPI["root_doc"] . "/plugins/ocsinventoryng/pics/detail.png' ".
                        "alt='" .__s('Computers imported by automatic actions', 'ocsinventoryng'). "' ".
                        "title=\"" .__s('Computers imported by automatic actions', 'ocsinventoryng'). "\">
                        <br>".__('Computers imported by automatic actions', 'ocsinventoryng')."
                     </a></td>";
            }
            echo "</tr>\n";

            //link
            echo "<tr class='tab_bg_1'><td class='center b' colspan='2'>
                  <a href='ocsng.link.php'>
                   <img src='" . $CFG_GLPI["root_doc"] . "/plugins/ocsinventoryng/pics/link.png' ".
                     "alt='" .__s('Link new OCSNG computers to existing GLPI computers',
                                 'ocsinventoryng'). "' ".
                     "title=\"" .__s('Link new OCSNG computers to existing GLPI computers',
                                    'ocsinventoryng'). "\">
                     <br>".__('Link new OCSNG computers to existing GLPI computers', 'ocsinventoryng')."
                  </a></td>";
            if ($usemassimport) {
               //host not imported by thread
               echo "<td class='center b' colspan='2'>
                     <a href='notimportedcomputer.php'>
                      <img src='" . $CFG_GLPI["root_doc"]."/plugins/ocsinventoryng/pics/notimported.png' ".
                        "alt='" .__s('Computers not imported by automatic actions', 'ocsinventoryng'). "' ".
                        "title=\"" . __s('Computers not imported by automatic actions', 'ocsinventoryng'). "\" >
                        <br>".__('Computers not imported by automatic actions', 'ocsinventoryng')."
                     </a></td>";
            }
            echo "</tr>\n";
         } else {
            echo "<tr class='tab_bg_2'><td class='center red' colspan='2'>";
            _e('The selected server is not active. Import and synchronisation is not available', 'ocsinventoryng');
            echo "</td></tr>\n";
         }
      }

      if (plugin_ocsinventoryng_haveRight('clean_ocsng','r') && $isactive) {
         echo "<tr class='tab_bg_1'><td class='center b' colspan='".($usemassimport?4:2)."'>
               <a href='ocsng.clean.php'>
                <img src='" . $CFG_GLPI["root_doc"] . "/plugins/ocsinventoryng/pics/clean.png' ".
                 "alt='" .__s('Clean links between GLPI and OCSNG', 'ocsinventoryng'). "' ".
                 "title=\"" .__s('Clean links between GLPI and OCSNG', 'ocsinventoryng'). "\" >
                  <br>".__('Clean links between GLPI and OCSNG', 'ocsinventoryng')."
               </a></td><tr>";
      }

      echo "</table></div>";
   }


   /**
    * Print ocs config form
    *
    * @param $target form target
    * @param $ID Integer : Id of the ocs config
    *
    * @return Nothing (display)
   **/
   function ocsFormConfig($target, $ID) {

      if (!plugin_ocsinventoryng_haveRight("ocsng", "w")) {
         return false;
      }
      $this->getFromDB($ID);
      echo "<br><div class='center'>";
      echo "<form name='formconfig' id='formconfig' action=\"$target\" method='post'>";
      echo "<table class='tab_cadre_fixe'>\n";
      echo "<tr><th colspan ='2'>";
      _e('All');
      echo $JS = <<<JAVASCRIPT
         <script type='text/javascript'>
            function form_init_all(form, index) {
               var elem = document.getElementById('formconfig').elements;
               for(var i = 0; i < elem.length; i++) {
                  if (elem[i].type == "select-one"
                     && elem[i].name != "import_otherserial"
                        && elem[i].name != "import_location"
                           && elem[i].name != "import_group"
                              && elem[i].name != "import_contact_num"
                                 && elem[i].name != "import_network") {
                     elem[i].selectedIndex = index;
                  }
               }
            }
         </script>
JAVASCRIPT;
      Dropdown::showYesNo('init_all', 0, -1, array(
         'on_change' => "form_init_all(this.form, this.selectedIndex);"
      ));
      echo "</th><th></th></tr>";
      echo "<tr>
            <th><input type='hidden' name='id' value='$ID'>".__('General information', 'ocsinventoryng')."</th>\n";
      echo "<th>"._n('Component', 'Components', 2) ."</th>\n";
      echo "<th>" . __('OCSNG administrative information', 'ocsinventoryng') . "</th></tr>\n";

      echo "<tr class='tab_bg_2'>\n";
      echo "<td class='top'>\n";

      echo "<table width='100%'>";
      echo "<tr class='tab_bg_2'><td class='center'>" . __('Name') . "</td>\n<td>";
      Dropdown::showYesNo("import_general_name", $this->fields["import_general_name"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Operating system') . "</td>\n<td>";
      Dropdown::showYesNo("import_general_os", $this->fields["import_general_os"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'>";
      echo "<td class='center'>".__('Serial of the operating system')."</td>\n<td>";
      Dropdown::showYesNo("import_os_serial", $this->fields["import_os_serial"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Serial number') . "</td>\n<td>";
      Dropdown::showYesNo("import_general_serial", $this->fields["import_general_serial"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Model') . "</td>\n<td>";
      Dropdown::showYesNo("import_general_model", $this->fields["import_general_model"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'>";
      echo "<td class='center'>" . _n('Manufacturer', 'Manufacturers', 1) . "</td>\n<td>";
      Dropdown::showYesNo("import_general_manufacturer",
                          $this->fields["import_general_manufacturer"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Type') . "</td>\n<td>";
      Dropdown::showYesNo("import_general_type", $this->fields["import_general_type"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Domain') . "</td>\n<td>";
      Dropdown::showYesNo("import_general_domain", $this->fields["import_general_domain"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Alternate username') . "</td>\n<td>";
      Dropdown::showYesNo("import_general_contact", $this->fields["import_general_contact"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Comments') . "</td>\n<td>";
      Dropdown::showYesNo("import_general_comment", $this->fields["import_general_comment"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('IP') . "</td>\n<td>";
      Dropdown::showYesNo("import_ip", $this->fields["import_ip"]);
      echo "</td></tr>\n";
      if (self::checkOCSconnection($ID) && self::checkVersion() > self::OCS2_VERSION_LIMIT) {
         echo "<tr class='tab_bg_2'><td class='center'>" . __('UUID') . "</td>\n<td>";
         Dropdown::showYesNo("import_general_uuid", $this->fields["import_general_uuid"]);
         echo "</td></tr>\n";
      } else {
         echo "<tr class='tab_bg_2'><td class='center'>";
         echo "<input type='hidden' name='import_general_uuid' value='0'>";
         echo "</td></tr>\n";
      }

      echo "<tr><td>&nbsp;</td></tr>";
      echo "</table>";

      echo "</td>\n";

      echo "<td class='tab_bg_2 top'>\n";

      echo "<table width='100%'>";
      echo "<tr class='tab_bg_2'><td class='center'>" . __('Processor') . "</td>\n<td>";
      Dropdown::showYesNo("import_device_processor", $this->fields["import_device_processor"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Memory') . "</td>\n<td>";
      Dropdown::showYesNo("import_device_memory", $this->fields["import_device_memory"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Hard drive') . "</td>\n<td>";
      Dropdown::showYesNo("import_device_hdd", $this->fields["import_device_hdd"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Network card') . "</td>\n<td>";
      Dropdown::showYesNo("import_device_iface", $this->fields["import_device_iface"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Graphics card') . "</td>\n<td>";
      Dropdown::showYesNo("import_device_gfxcard", $this->fields["import_device_gfxcard"]);
      echo "&nbsp;&nbsp;</td></tr>";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Soundcard') . "</td>\n<td>";
      Dropdown::showYesNo("import_device_sound", $this->fields["import_device_sound"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . _n('Drive', 'Drives', 2) . "</td>\n<td>";
      Dropdown::showYesNo("import_device_drive", $this->fields["import_device_drive"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>".__('Modems') ."</td>\n<td>";
      Dropdown::showYesNo("import_device_modem", $this->fields["import_device_modem"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>"._n('Port', 'Ports', 2)."</td>\n<td>";
      Dropdown::showYesNo("import_device_port", $this->fields["import_device_port"]);
      echo "</td></tr>\n";
      echo "</table>";

      echo "</td>\n";
      echo "<td class='tab_bg_2 top'>\n";

      echo "<table width='100%'>";
      echo "<tr class='tab_bg_2'><td class='center'>" . __('Inventory number'). " </td>\n";
      echo "<td><select name='import_otherserial'>\n";
      echo "<option value=''>" .__('No import'). "</option>\n";
      $listColumnOCS = self::getColumnListFromAccountInfoTable($ID, "otherserial");
      echo $listColumnOCS;
      echo "</select>&nbsp;&nbsp;</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Location') . " </td>\n";
      echo "<td><select name='import_location'>\n";
      echo "<option value=''>" .__('No import') . "</option>\n";
      $listColumnOCS = self::getColumnListFromAccountInfoTable($ID, "locations_id");
      echo $listColumnOCS;
      echo "</select></td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Group') . " </td>\n";
      echo "<td><select name='import_group'>\n";
      echo "<option value=''>" . __('No import') . "</option>\n";
      $listColumnOCS = self::getColumnListFromAccountInfoTable($ID, "groups_id");
      echo $listColumnOCS;
      echo "</select></td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" .__('Alternate username number'). " </td>\n";
      echo "<td><select name='import_contact_num'>\n";
      echo "<option value=''>" . __('No import') . "</option>\n";
      $listColumnOCS = self::getColumnListFromAccountInfoTable($ID, "contact_num");
      echo $listColumnOCS;
      echo "</select></td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . __('Network') . " </td>\n";
      echo "<td><select name='import_network'>\n";
      echo "<option value=''>" .__('No import', 'ocsinventoryng') . "</option>\n";
      $listColumnOCS = self::getColumnListFromAccountInfoTable($ID, "networks_id");
      echo $listColumnOCS;
      echo "</select></td></tr>\n";
      echo "</table>";

      echo "</td></tr>\n";

      echo "<tr><th>". _n('Monitor', 'Monitors', 2)."</th>\n";
      echo "<th colspan='2'>&nbsp;</th></tr>\n";

      echo "<tr class='tab_bg_2'>\n";
      echo "<td class='tab_bg_2 top'>\n";

      echo "<table width='100%'>";
      echo "<tr class='tab_bg_2'><td class='center'>" . __('Comments') . " </td>\n<td>";
      Dropdown::showYesNo("import_monitor_comment", $this->fields["import_monitor_comment"]);
      echo "</td></tr>\n";
      echo "</table>";

      echo "</td>\n";
      echo "<td class='tab_bg_2' colspan='2'>&nbsp;</td>";
      echo "</table>\n";

      echo "<p class='submit'>";
      echo "<input type='submit' name='update_server' class='submit' value=\"".
            _sx('button', 'Save')."\">";
      echo "</p>";
      Html::closeForm();
      echo "</div>\n";
   }


   /**
    * @param $target
    * @param $ID
    * @param $withtemplate    (default '')
    * @param $templateid      (default '')
   **/
   function ocsFormImportOptions($target, $ID, $withtemplate='', $templateid='') {

      $this->getFromDB($ID);
      echo "<br><div class='center'>";
      echo "<form name='formconfig' action=\"$target\" method='post'>";
      echo "<table class='tab_cadre_fixe'>\n";
      echo "<tr class='tab_bg_2'><td class='center'>" .__('Web address of the OCSNG console',
                                                          'ocsinventoryng');
      echo "<input type='hidden' name='id' value='$ID'>" . " </td>\n";
      echo "<td><input type='text' size='30' name='ocs_url' value=\"".$this->fields["ocs_url"]."\">";
      echo "</td></tr>\n";

      echo "<tr><th colspan='2'>" . __('Import options'). "</th></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>".
             __('Limit the import to the following tags (separator $, nothing for all)',
                'ocsinventoryng')."</td>\n";
      echo "<td><input type='text' size='30' name='tag_limit' value='".$this->fields["tag_limit"]."'>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>".
             __('Exclude the following tags (separator $, nothing for all)', 'ocsinventoryng').
           "</td>\n";
      echo "<td><input type='text' size='30' name='tag_exclude' value='".
                 $this->fields["tag_exclude"]."'></td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>".__('Default status', 'ocsinventoryng').
           "</td>\n<td>";
      State::dropdown(array('name'   => 'states_id_default',
                            'value'  => $this->fields["states_id_default"]));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>".__('Behavior when disconnecting', 'ocsinventoryng')."</td>\n<td>";
      Dropdown::showFromArray("deconnection_behavior",
                              array(''       => __('Preserve'),
                                    "trash"  => _x('button', 'Put in dustbin'),
                                    "delete" => _x('button', 'Delete permanently')),
                              array('value' => $this->fields["deconnection_behavior"]));
      echo "</td></tr>\n";

      $import_array  = array("0" => __('No import', 'ocsinventoryng'),
                             "1" => __('Global import', 'ocsinventoryng'),
                             "2" => __('Unit import', 'ocsinventoryng'));

      $import_array2 = array("0" => __('No import', 'ocsinventoryng'),
                             "1" => __('Global import', 'ocsinventoryng'),
                             "2" => __('Unit import', 'ocsinventoryng'),
                             "3" => __('Unit import on serial number', 'ocsinventoryng'),
                             "4" => __('Unit import serial number only', 'ocsinventoryng'));

      $periph   = $this->fields["import_periph"];
      $monitor  = $this->fields["import_monitor"];
      $printer  = $this->fields["import_printer"];
      $software = $this->fields["import_software"];
      echo "<tr class='tab_bg_2'><td class='center'>" ._n('Device', 'Devices', 2). " </td>\n<td>";
      Dropdown::showFromArray("import_periph", $import_array, array('value' => $periph));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" ._n('Monitor', 'Monitors', 2). "</td>\n<td>";
      Dropdown::showFromArray("import_monitor", $import_array2, array('value' => $monitor));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" ._n('Printer', 'Printers', 2). "</td>\n<td>";
      Dropdown::showFromArray("import_printer", $import_array, array('value' => $printer));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>". _n('Software', 'Software', 2)."</td>\n<td>";
      $import_array = array("0" => __('No import', 'ocsinventoryng'),
                            "1" => __('Unit import', 'ocsinventoryng'));
      Dropdown::showFromArray("import_software", $import_array, array('value' => $software));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>" . _n('Volume', 'Volumes', 2) . "</td>\n<td>";
      Dropdown::showYesNo("import_disk", $this->fields["import_disk"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>".__('Use the OCSNG software dictionary',
                                                         'ocsinventoryng')."</td>\n<td>";
      Dropdown::showYesNo("use_soft_dict", $this->fields["use_soft_dict"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td class='center'>".__('Registry', 'ocsinventoryng')."</td>\n<td>";
      Dropdown::showYesNo("import_registry", $this->fields["import_registry"]);
      echo "</td></tr>\n";

      //check version
      if ($this->fields['ocs_version'] > self::OCS1_3_VERSION_LIMIT) {
         echo "<tr class='tab_bg_2'><td class='center'>".
                _n('Virtual machine', 'Virtual machines', 2) . "</td>\n<td>";
         Dropdown::showYesNo("import_vms", $this->fields["import_vms"]);
         echo "</td></tr>\n";
      } else {
         echo "<tr class='tab_bg_2'><td class='center'>";
         echo "<input type='hidden' name='import_vms' value='0'>";
         echo "</td></tr>\n";
      }
      echo "<tr class='tab_bg_2'><td class='center'>".
             __('Number of items to synchronize via the automatic OCSNG action',  'ocsinventoryng').
           "</td>\n<td>";
      Dropdown::showNumber('cron_sync_number', array(
                'value' => $this->fields['cron_sync_number'], 
                'min'   => 1, 
                'toadd' => array(0 => __('None'))));
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td class='center'>".
             __('Behavior to the deletion of a computer in OCSNG', 'ocsinventoryng')."</td>";
      echo "<td>";
      $actions[0] = Dropdown::EMPTY_VALUE;
      $actions[1] = __('Put in dustbin');
      foreach (getAllDatasFromTable('glpi_states') as $state) {
         $actions['STATE_'.$state['id']] = sprintf(__('Change to state %s', 'ocsinventoryng'),
                                                   $state['name']);
      }
      Dropdown::showFromArray('deleted_behavior', $actions,
                              array('value' => $this->fields['deleted_behavior']));

      echo "</table>\n";

      echo "<br>".__('No import: the plugin will not import these elements', 'ocsinventoryng');
      echo "<br>".__('Global import: everything is imported but the material is globally managed (without duplicate)',
                     'ocsinventoryng');
      echo "<br>".__("Unit import: everything is imported as it is", 'ocsinventoryng');

      echo "<p class='submit'><input type='submit' name='update_server' class='submit' value='" .
             _sx('button', 'Save') . "'></p>";
             Html::closeForm();
      echo "</div>";
   }


   // fonction jamais appeléee
   function ocsFormAutomaticLinkConfig($target, $ID, $withtemplate='', $templateid='') {

      if (!plugin_ocsinventoryng_haveRight("ocsng", "w")) {
         return false;
      }
      $this->getFromDB($ID);
      echo "<br><div class='center'>";
      echo "<form name='formconfig' action=\"$target\" method='post'>\n";
      echo "<table class='tab_cadre_fixe'>\n";
      echo "<tr><th colspan='4'>". __('Automatic connection of computers', 'ocsinventoryng');
      echo "<input type='hidden' name='id' value='$ID'></th></tr>\n";

      echo "<tr class='tab_bg_2'><td>" .__('Enable the automatic link',  'ocsinventoryng'). " </td>\n";
      echo "<td colspan='3'>";
      Dropdown::showYesNo("is_glpi_link_enabled", $this->fields["is_glpi_link_enabled"]);
      echo "</td></tr>\n";

      echo "<tr><th colspan='4'>". __('Existence criteria of a computer', 'ocsinventoryng').
           "</th></tr>\n";

      echo "<tr class='tab_bg_2'><td>" .__('IP') . " </td>\n<td>";
      Dropdown::showYesNo("use_ip_to_link", $this->fields["use_ip_to_link"]);
      echo "</td>\n";
      echo "<td>" . __('Mac address') . " </td>\n<td>";
      Dropdown::showYesNo("use_mac_to_link", $this->fields["use_mac_to_link"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td>" . __("Computer's name") . " </td>\n<td>";
      $link_array = array("0" => __('No'),
                          "1" => sprintf(__('%1$s: %2$s'), __('Yes'), __('equal')),
                          "2" => sprintf(__('%1$s: %2$s'), __('Yes'), __('empty')));
      Dropdown::showFromArray("use_name_to_link", $link_array,
                              array('value' => $this->fields["use_name_to_link"]));
      echo "</td>\n";
      echo "<td>" . __('Serial number') . " </td>\n<td>";
      Dropdown::showYesNo("use_serial_to_link", $this->fields["use_serial_to_link"]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'><td>". __('Find computers in GLPI having the status')."</td>\n";
      echo "<td colspan='3'>";
      State::dropdown(array('value' => $this->fields["states_id_linkif"],
                            'name'  => "states_id_linkif"));
      echo "</td></tr>\n";
      echo "</table><br>".__('The link automatically connects a GLPI computer with one in OCSNG.',
                             'ocsinventoryng').
           "<br>". __('This option is taken into account during manual link and by synchronization scripts."',
                      'ocsinventoryng');

      echo "<p class='submit'><input type='submit' name='update_server' class='submit' value='" .
             _sx('button', 'Post') . "'></p>";
      Html::closeForm();
      echo "</div>";
   }


   /**
    * Print simple ocs config form (database part)
    *
    * @param $ID        integer : Id of the ocs config
    * @param $options   array
    *     - target form target
    *
    * @return Nothing (display)
   **/
   function showForm($ID, $options=array()) {

      if (!plugin_ocsinventoryng_haveRight("ocsng", "w")) {
         return false;
      }

      $rowspan = 4;
      //If no ID provided, or if the server is created using an existing template
      if (empty($ID)) {
         $this->getEmpty();
         $rowspan++;
      } else {
         $this->getFromDB($ID);
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'><td class='center'>" . __('Name')."</td>\n";
      echo "<td><input type='text' name='name' value=\"" . $this->fields["name"] ."\"></td>\n";
      echo "<td class='center'>" . _n('Version', 'Versions',1)."</td>\n";
      echo "<td>".$this->fields["ocs_version"]."</td></tr>\n";

      echo "<tr class='tab_bg_1'><td class='center'>".__('Host for the database', 'ocsinventoryng').
           "</td>\n";
      echo "<td><input type='text' name='ocs_db_host' value=\"" .$this->fields["ocs_db_host"] ."\">".
           "</td>\n";
      echo "<td class='center'>" . __('Synchronisation method', 'ocsinventoryng')."</td><td>\n";
      $tabsync = array(0 => __('Standard (allow manual actions)', 'ocsinventoryng'),
                       1 => __('Expert (Fully automatic, for large configuration)', 'ocsinventoryng'));
      Dropdown::showFromArray('use_massimport', $tabsync, array('value' => $this->fields['use_massimport']));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'>".__('Database')."</td>\n";
      echo "<td><input type='text' name='ocs_db_name' value=\"".$this->fields["ocs_db_name"]."\">";
      echo "<td class='center' rowspan='$rowspan'>" . __('Comments') . "</td>\n";
      echo "<td rowspan='$rowspan'>";
      echo "<textarea cols='45' rows='6' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'>"._n('User', 'Users', 1)."</td>\n";
      echo "<td><input type='text' name='ocs_db_user' value=\"".$this->fields["ocs_db_user"]."\">";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'><td class='center'>".__('Password') . "</td>\n";
      echo "<td><input type='password' name='ocs_db_passwd' value='' autocomplete='off'>";
      if ($ID > 0) {
         echo "<br><input type='checkbox' name='_blank_passwd'>&nbsp;".__('Clear');
      }
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'><td class='center'>".__('Database in UTF8', 'ocsinventoryng')."</td>\n";
      echo "<td>";
      Dropdown::showYesNo('ocs_db_utf8',$this->fields["ocs_db_utf8"]);
      echo "</td>";
      echo "</tr>\n";

      echo "<tr class='tab_bg_1'><td class='center'>" .__('Active') . "</td>\n";
      echo "<td>";
      Dropdown::showYesNo('is_active',$this->fields["is_active"]);
      echo "</td>";

      if (!empty ($ID)) {
         echo "<td>".__('Last update')."</td>";
         echo "<td>";
         echo ($this->fields["date_mod"] ? Html::convDateTime($this->fields["date_mod"])
                                         : __('Never'));
         echo "</td>";
      }

      echo "</tr>\n";

      $this->showFormButtons($options);
      $this->addDivForTabs();
   }

   /**
    * check is one of the servers use_mass_import sync mode
    *
    * @return boolean
   **/
   static function useMassImport() {
      return countElementsInTable('glpi_plugin_ocsinventoryng_ocsservers', 'use_massimport');
   }

   /**
    * @param $ID
   **/
   function showDBConnectionStatus($ID) {

      $out="<br><div class='center'>\n";
      $out.="<table class='tab_cadre_fixe'>";
      $out.="<tr><th>" .__('Connecting to the database', 'ocsinventoryng'). "</th></tr>\n";
      $out.="<tr class='tab_bg_2'><td class='center'>";
      if ($ID != -1) {
         if (!self::checkOCSconnection($ID)) {
            $out .= __('Connection to the database failed', 'ocsinventoryng');
         } else if (!self::checkConfig(1)) {
            $out .= __('Invalid OCSNG Version: RC3 is required', 'ocsinventoryng');
         } else if (!self::checkConfig(2)) {
            $out .= __('Invalid OCSNG configuration (TRACE_DELETED must be active)', 'ocsinventoryng');
         } else if (!self::checkConfig(4)) {
            $out .= __('Access denied on database (Need write rights on hardware.CHECKSUM necessary)',
                       'ocsinventoryng');
         } else if (!self::checkConfig(8)) {
            $out .= __('Access denied on database (Delete rights in deleted_equiv table necessary)',
                       'ocsinventoryng');
         } else {
            $out .= __('Connection to database successful', 'ocsinventoryng');
            $out .= "</td></tr>\n<tr class='tab_bg_2'>".
                    "<td class='center'>".__('Valid OCSNG configuration and version', 'ocsinventoryng');
         }
      }
      $out .= "</td></tr>\n";
      $out .= "</table></div>";
      echo $out;
   }


   function prepareInputForUpdate($input) {

      $this->updateAdminInfo($input);
      if (isset($input["ocs_db_passwd"]) && !empty($input["ocs_db_passwd"])) {
         $input["ocs_db_passwd"] = rawurlencode(stripslashes($input["ocs_db_passwd"]));
      } else {
         unset($input["ocs_db_passwd"]);
      }

      if (isset($input["_blank_passwd"]) && $input["_blank_passwd"]) {
         $input['ocs_db_passwd'] = '';
      }

      return $input;
   }


   function pre_updateInDB() {

      // Update checksum
      $checksum = 0;

      if ($this->fields["import_printer"]) {
         $checksum |= pow(2,self::PRINTERS_FL);
      }
      if ($this->fields["import_software"]) {
         $checksum |= pow(2,self::SOFTWARES_FL);
      }
      if ($this->fields["import_monitor"]) {
         $checksum |= pow(2,self::MONITORS_FL);
      }
      if ($this->fields["import_periph"]) {
         $checksum |= pow(2,self::INPUTS_FL);
      }
      if ($this->fields["import_registry"]) {
         $checksum |= pow(2,self::REGISTRY_FL);
      }
      if ($this->fields["import_disk"]) {
         $checksum |= pow(2,self::DRIVES_FL);
      }
      if ($this->fields["import_ip"]) {
         $checksum |= pow(2,self::NETWORKS_FL);
      }
      if ($this->fields["import_device_port"]) {
         $checksum |= pow(2,self::PORTS_FL);
      }
      if ($this->fields["import_device_modem"]) {
         $checksum |= pow(2,self::MODEMS_FL);
      }
      if ($this->fields["import_device_drive"]) {
         $checksum |= pow(2,self::STORAGES_FL);
      }
      if ($this->fields["import_device_sound"]) {
         $checksum |= pow(2,self::SOUNDS_FL);
      }
      if ($this->fields["import_device_gfxcard"]) {
         $checksum |= pow(2,self::VIDEOS_FL);
      }
      if ($this->fields["import_device_iface"]) {
         $checksum |= pow(2,self::NETWORKS_FL);
      }
      if ($this->fields["import_device_hdd"]) {
         $checksum |= pow(2,self::STORAGES_FL);
      }
      if ($this->fields["import_device_memory"]) {
         $checksum |= pow(2,self::MEMORIES_FL);
      }

      if ($this->fields["import_device_processor"]
          || $this->fields["import_general_contact"]
          || $this->fields["import_general_comment"]
          || $this->fields["import_general_domain"]
          || $this->fields["import_general_os"]
          || $this->fields["import_general_name"]) {

         $checksum |= pow(2,self::HARDWARE_FL);
      }

      if ($this->fields["import_general_manufacturer"]
          || $this->fields["import_general_type"]
          || $this->fields["import_general_model"]
          || $this->fields["import_general_serial"]) {

         $checksum |= pow(2,self::BIOS_FL);
      }

      if ($this->fields["import_vms"]) {
         $checksum |= pow(2,self::VIRTUALMACHINES_FL);
      }

      $this->updates[] = "checksum";
      $this->fields["checksum"] = $checksum;
   }


   function prepareInputForAdd($input) {
      global $DB;

      // Check if server config does not exists
      $query = "SELECT *
                FROM `" . $this->getTable() . "`
                WHERE `name` = '".$input['name']."';";
      $result = $DB->query($query);
      if ($DB->numrows($result)>0) {
         Session::addMessageAfterRedirect(__('Unable to add. The OCSNG server already exists.',
                                             'ocsinventoryng'),
                                          false, ERROR);
         return false;
      }

      if (isset($input["ocs_db_passwd"]) && !empty($input["ocs_db_passwd"])) {
         $input["ocs_db_passwd"] = rawurlencode(stripslashes($input["ocs_db_passwd"]));
      } else {
         unset($input["ocs_db_passwd"]);
      }
      return $input;
   }


   function cleanDBonPurge() {

      $link = new PluginOcsinventoryngOcslink();
      $link->deleteByCriteria(array('plugin_ocsinventoryng_ocsservers_id' => $this->fields['id']));

      $admin = new PluginOcsinventoryngOcsAdminInfosLink();
      $admin->deleteByCriteria(array('plugin_ocsinventoryng_ocsservers_id' => $this->fields['id']));

      $server = new PluginOcsinventoryngServer();
		$server->deleteByCriteria(array('plugin_ocsinventoryng_ocsservers_id' => $this->fields['id']));

		unset($_SESSION["plugin_ocsinventoryng_ocsservers_id"]);

      // ocsservers_id for RuleImportComputer, OCS_SERVER for RuleImportEntity
      Rule::cleanForItemCriteria($this);
      Rule::cleanForItemCriteria($this, 'OCS_SERVER');

   }


   /**
    * Update Admin Info retrieve config
    *
    * @param $tab data array
   **/
   function updateAdminInfo($tab) {

      if (isset($tab["import_location"])
          || isset ($tab["import_otherserial"])
          || isset ($tab["import_group"])
          || isset ($tab["import_network"])
          || isset ($tab["import_contact_num"])) {

         $adm = new PluginOcsinventoryngOcsAdminInfosLink();
         $adm->cleanForOcsServer($tab["id"]);

         if (isset ($tab["import_location"])) {
            if ($tab["import_location"]!="") {
               $adm = new PluginOcsinventoryngOcsAdminInfosLink();
               $adm->fields["plugin_ocsinventoryng_ocsservers_id"] = $tab["id"];
               $adm->fields["glpi_column"]                         = "locations_id";
               $adm->fields["ocs_column"]                          = $tab["import_location"];
               $isNewAdm = $adm->addToDB();
            }
         }

         if (isset ($tab["import_otherserial"])) {
            if ($tab["import_otherserial"]!="") {
               $adm = new PluginOcsinventoryngOcsAdminInfosLink();
               $adm->fields["plugin_ocsinventoryng_ocsservers_id"] =  $tab["id"];
               $adm->fields["glpi_column"]                         = "otherserial";
               $adm->fields["ocs_column"]                          = $tab["import_otherserial"];
               $isNewAdm = $adm->addToDB();
            }
         }

         if (isset ($tab["import_group"])) {
            if ($tab["import_group"]!="") {
               $adm = new PluginOcsinventoryngOcsAdminInfosLink();
               $adm->fields["plugin_ocsinventoryng_ocsservers_id"] = $tab["id"];
               $adm->fields["glpi_column"]                         = "groups_id";
               $adm->fields["ocs_column"]                          = $tab["import_group"];
               $isNewAdm = $adm->addToDB();
            }
         }

         if (isset ($tab["import_network"])) {
            if ($tab["import_network"]!="") {
               $adm = new PluginOcsinventoryngOcsAdminInfosLink();
               $adm->fields["plugin_ocsinventoryng_ocsservers_id"] = $tab["id"];
               $adm->fields["glpi_column"]                         = "networks_id";
               $adm->fields["ocs_column"]                          = $tab["import_network"];
               $isNewAdm = $adm->addToDB();
            }
         }

         if (isset ($tab["import_contact_num"])) {
            if ($tab["import_contact_num"]!="") {
               $adm = new PluginOcsinventoryngOcsAdminInfosLink();
               $adm->fields["plugin_ocsinventoryng_ocsservers_id"] = $tab["id"];
               $adm->fields["glpi_column"]                         = "contact_num";
               $adm->fields["ocs_column"]                          = $tab["import_contact_num"];
               $isNewAdm = $adm->addToDB();
            }
         }
      }
   }

   /**
    *
    * Encode data coming from OCS DB in utf8 is needed
    * @since 1.0
    * @param boolean $is_ocsdb_utf8 is OCS database declared as utf8 in GLPI configuration
    * @param string $value value to encode in utf8
    * @return string value encoded in utf8
    */
   static function encodeOcsDataInUtf8($is_ocsdb_utf8, $value) {
      if (!$is_ocsdb_utf8 && !Toolbox::seems_utf8($value)) {
         return Toolbox::encodeInUtf8($value);
      } else {
         return $value;
      }
   }

   /**
    * @param $width
   **/
   function showSystemInformations($width) {

      $ocsServers = getAllDatasFromTable('glpi_plugin_ocsinventoryng_ocsservers');
      if (!empty($ocsServers)) {
         echo "\n<tr class='tab_bg_2'><th>OCS Inventory NG</th></tr>\n";
         echo "<tr class='tab_bg_1'><td><pre>\n&nbsp;\n";

         $msg = '';
         foreach ($ocsServers as $ocsServer) {
               $msg .= "Host: '".$ocsServer['ocs_db_host']."'";
               $msg .= ", Connection: ".(self::checkOCSconnection($ocsServer['id']) ? "Ok" : "KO");
               $msg .= ", Use the OCSNG software dictionary: ".
                     ($ocsServer['use_soft_dict'] ? 'Yes' : 'No');
         }
         echo wordwrap($msg."\n", $width, "\n\t\t");
         echo "\n</pre></td></tr>";
      }
   }


   /**
    * Get the ocs server id of a machine, by giving the machine id
    *
    * @param $ID the machine ID
    *
    * @return the ocs server id of the machine
   **/
   static function getByMachineID($ID) {
      global $DB;

      $sql = "SELECT `plugin_ocsinventoryng_ocsservers_id`
              FROM `glpi_plugin_ocsinventoryng_ocslinks`
              WHERE `glpi_plugin_ocsinventoryng_ocslinks`.`computers_id` = '$ID'";
      $result = $DB->query($sql);
      if ($DB->numrows($result) > 0) {
         $datas = $DB->fetch_array($result);
         return $datas["plugin_ocsinventoryng_ocsservers_id"];
      }
      return -1;
   }


   /**
    * Get an Ocs Server name, by giving his ID
    *
    * @param $ID the server ID

    * @return the ocs server name
   **/
   static function getServerNameByID($ID) {

      $plugin_ocsinventoryng_ocsservers_id = self::getByMachineID($ID);
      $conf                                = self::getConfig($plugin_ocsinventoryng_ocsservers_id);
      return $conf["name"];
   }


   /**
    * Get a random plugin_ocsinventoryng_ocsservers_id
    * use for standard sync server selection
    *
    * @return an ocs server id
   **/
   static function getRandomServerID() {
      global $DB;

      $sql = "SELECT `id`
              FROM `glpi_plugin_ocsinventoryng_ocsservers`
              WHERE `is_active` = '1'
                AND NOT `use_massimport`
              ORDER BY RAND()
              LIMIT 1";
      $result = $DB->query($sql);

      if ($DB->numrows($result) > 0) {
         $datas = $DB->fetch_array($result);
         return $datas["id"];
      }
      return -1;
   }


   /**
    * Get OCSNG mode configuration
    *
    * Get all config of the OCSNG mode
    *
    * @param $id int : ID of the OCS config (default value 1)
    *
    * @return Value of $confVar fields or false if unfound.
   **/
   static function getConfig($id) {
      global $DB;

      $query = "SELECT *
                FROM `glpi_plugin_ocsinventoryng_ocsservers`
                WHERE `id` = '$id'";
      $result = $DB->query($query);

      if ($result) {
         $data = $DB->fetch_assoc($result);
      } else {
         $data = 0;
      }

      return $data;
   }


   static function getTagLimit($cfg_ocs) {

      $WHERE = "";
      if (!empty ($cfg_ocs["tag_limit"])) {
         $splitter = explode("$", trim($cfg_ocs["tag_limit"]));
         if (count($splitter)) {
            $WHERE = " `accountinfo`.`TAG` = '" . $splitter[0] . "' ";
            for ($i = 1; $i < count($splitter); $i++) {
               $WHERE .= " OR `accountinfo`.`TAG` = '" .$splitter[$i] . "' ";
            }
         }
      }

      if (!empty ($cfg_ocs["tag_exclude"])) {
         $splitter = explode("$", $cfg_ocs["tag_exclude"]);
         if (count($splitter)) {
            if (!empty($WHERE)) {
               $WHERE .= " AND ";
            }
            $WHERE .= " `accountinfo`.`TAG` <> '" . $splitter[0] . "' ";
            for ($i=1 ; $i<count($splitter) ; $i++) {
               $WHERE .= " AND `accountinfo`.`TAG` <> '" .$splitter[$i] . "' ";
            }
         }
      }

      return $WHERE;
   }


   /**
    * Make the item link between glpi and ocs.
    *
    * This make the database link between ocs and glpi databases
    *
    * @param $ocsid integer : ocs item unique id.
    * @param $plugin_ocsinventoryng_ocsservers_id integer : ocs server id
    * @param $glpi_computers_id integer : glpi computer id
    *
    * @return integer : link id.
   **/
   static function ocsLink($ocsid, $plugin_ocsinventoryng_ocsservers_id, $glpi_computers_id) {
      global $DB, $PluginOcsinventoryngDBocs;

      // Retrieve informations from computer
      $comp = new Computer();
      $comp->getFromDB($glpi_computers_id);

      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);

      // Need to get device id due to ocs bug on duplicates
      $query_ocs = "SELECT `hardware`.*,
                           `accountinfo`.`TAG` AS TAG
                    FROM `hardware`
                    INNER JOIN `accountinfo` ON (`hardware`.`id` = `accountinfo`.`HARDWARE_ID`)
                    WHERE `ID` = '$ocsid'";
      $result_ocs = $PluginOcsinventoryngDBocs->query($query_ocs);
      $data       = $PluginOcsinventoryngDBocs->fetch_array($result_ocs);

      $query = "INSERT INTO `glpi_plugin_ocsinventoryng_ocslinks`
                       (`computers_id`, `ocsid`, `ocs_deviceid`,
                        `last_update`, `plugin_ocsinventoryng_ocsservers_id`,
                        `entities_id`, `tag`)
                VALUES ('$glpi_computers_id', '$ocsid', '".$data["DEVICEID"]."',
                        '".$_SESSION["glpi_currenttime"]."', '$plugin_ocsinventoryng_ocsservers_id',
                        '".$comp->fields['entities_id']."', '".$data["TAG"]."')";
      $result = $DB->query($query);

      if ($result) {
         return ($DB->insert_id());
      }
      return false;
   }


      /**
    * @param $ocsid
    * @param $plugin_ocsinventoryng_ocsservers_id
    * @param $computers_id
   **/
   static function linkComputer($ocsid, $plugin_ocsinventoryng_ocsservers_id, $computers_id) {
      global $DB, $PluginOcsinventoryngDBocs, $CFG_GLPI;


      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);

      $query = "SELECT *
                FROM `glpi_plugin_ocsinventoryng_ocslinks`
                WHERE `computers_id` = '$computers_id'";

      $result           = $DB->query($query);
      $ocs_id_change    = false;
      $ocs_link_exists  = false;
      $numrows          = $DB->numrows($result);

      // Already link - check if the OCS computer already exists
      if ($numrows > 0) {
         $ocs_link_exists = true;
         $data            = $DB->fetch_assoc($result);
         $query = "SELECT *
                   FROM `hardware`
                   WHERE `ID` = '" . $data["ocsid"] . "'";
         $result_ocs = $PluginOcsinventoryngDBocs->query($query);
         // Not found
         if ($PluginOcsinventoryngDBocs->numrows($result_ocs)==0) {

            $idlink = $data["id"];
            $query  = "UPDATE `glpi_plugin_ocsinventoryng_ocslinks`
                       SET `ocsid` = '$ocsid'
                       WHERE `id` = '" . $data["id"] . "'";

            if ($DB->query($query)) {
               $ocs_id_change = true;
               //Add history to indicates that the ocsid changed
               $changes[0] = '0';
               //Old ocsid
               $changes[1] = $data["ocsid"];
               //New ocsid
               $changes[2] = $ocsid;
               PluginOcsinventoryngOcslink::history($computers_id, $changes,
                                                    PluginOcsinventoryngOcslink::HISTORY_OCS_IDCHANGED);
            }
         }
      }

      // No ocs_link or ocs id change does not exists so can link
      if ($ocs_id_change || !$ocs_link_exists) {
         $ocsConfig = self::getConfig($plugin_ocsinventoryng_ocsservers_id);
         // Set OCS checksum to max value
         self::setChecksumForComputer($ocsid, self::MAX_CHECKSUM);

         if ($ocs_id_change
             || ($idlink = self::ocsLink($ocsid, $plugin_ocsinventoryng_ocsservers_id,
                                         $computers_id))) {

             // automatic transfer computer
             if (($CFG_GLPI['transfers_id_auto'] > 0)
                 && Session::isMultiEntitiesMode()) {

                // Retrieve data from glpi_plugin_ocsinventoryng_ocslinks
                $ocsLink = new PluginOcsinventoryngOcslink();
                $ocsLink->getFromDB($idlink);

                if (count($ocsLink->fields)) {
                   // Retrieve datas from OCS database
                   $query_ocs = "SELECT *
                                 FROM `hardware`
                                 WHERE `ID` = '" . $ocsLink->fields['ocsid'] . "'";
                   $result_ocs = $PluginOcsinventoryngDBocs->query($query_ocs);

                   if ($PluginOcsinventoryngDBocs->numrows($result_ocs) == 1) {
                      $data_ocs = Toolbox::addslashes_deep($PluginOcsinventoryngDBocs->fetch_array($result_ocs));
                      self::transferComputer($ocsLink->fields, $data_ocs);
                   }
                }
             }

            $comp = new Computer();
            $comp->getFromDB($computers_id);
            $input["id"]            = $computers_id;
            $input["entities_id"]   = $comp->fields['entities_id'];
            $input["is_dynamic"]    = 1;
            $input["_nolock"]       = true;

            // Not already import from OCS / mark default state
            if (!$ocs_id_change
                || (!$comp->fields['is_dynamic']
                    && ($ocsConfig["states_id_default"] > 0))) {
               $input["states_id"] = $ocsConfig["states_id_default"];
            }
            $comp->update($input);
            // Auto restore if deleted
            if ($comp->fields['is_deleted']) {
               $comp->restore(array('id' => $computers_id));
            }
            // Reset using GLPI Config
            $cfg_ocs = self::getConfig($plugin_ocsinventoryng_ocsservers_id);

            // Reset only if not in ocs id change case
            if (!$ocs_id_change) {
               if ($cfg_ocs["import_general_os"]) {
                  self::resetDropdown($computers_id, "operatingsystems_id", "glpi_operatingsystems");
               }
               if ($cfg_ocs["import_device_processor"]) {
                  self::resetDevices($computers_id, 'DeviceProcessor');
               }
               if ($cfg_ocs["import_device_iface"]) {
                  self::resetDevices($computers_id, 'DeviceNetworkCard');
               }
               if ($cfg_ocs["import_device_memory"]) {
                  self::resetDevices($computers_id, 'DeviceMemory');
               }
               if ($cfg_ocs["import_device_hdd"]) {
                  self::resetDevices($computers_id, 'DeviceHardDrive');
               }
               if ($cfg_ocs["import_device_sound"]) {
                  self::resetDevices($computers_id, 'DeviceSoundCard');
               }
               if ($cfg_ocs["import_device_gfxcard"]) {
                  self::resetDevices($computers_id, 'DeviceGraphicCard');
               }
               if ($cfg_ocs["import_device_drive"]) {
                  self::resetDevices($computers_id, 'DeviceDrive');
               }
               if ($cfg_ocs["import_device_modem"] || $cfg_ocs["import_device_port"]) {
                  self::resetDevices($computers_id, 'DevicePci');
               }
               if ($cfg_ocs["import_software"]) {
                  self::resetSoftwares($computers_id);
               }
               if ($cfg_ocs["import_disk"]) {
                  self::resetDisks($computers_id);
               }
               if ($cfg_ocs["import_periph"]) {
                  self::resetPeripherals($computers_id);
               }
               if ($cfg_ocs["import_monitor"]==1) { // Only reset monitor as global in unit management
                  self::resetMonitors($computers_id);    // try to link monitor with existing
               }
               if ($cfg_ocs["import_printer"]) {
                  self::resetPrinters($computers_id);
               }
               if ($cfg_ocs["import_registry"]) {
                  self::resetRegistry($computers_id);
               }
               $changes[0] = '0';
               $changes[1] = "";
               $changes[2] = $ocsid;
               PluginOcsinventoryngOcslink::history($computers_id, $changes,
                                                    PluginOcsinventoryngOcslink::HISTORY_OCS_LINK);
            }

            self::updateComputer($idlink, $plugin_ocsinventoryng_ocsservers_id, 0);
            return true;
         }

      } else {
        //TRANS: %s is the OCS id
         Session::addMessageAfterRedirect(sprintf(__('Unable to import, GLPI computer is already related to an element of OCSNG (%d)',
                                                     'ocsinventoryng'), $ocsid),
                                          false, ERROR);
      }
      return false;
   }


   static function processComputer($ocsid, $plugin_ocsinventoryng_ocsservers_id, $lock=0,
                                   $defaultentity=-1, $defaultlocation=-1) {
      global $DB;

      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);
      $comp = new Computer();

      //Check it machine is already present AND was imported by OCS AND still present in GLPI
      $query = "SELECT `glpi_plugin_ocsinventoryng_ocslinks`.`id`, `computers_id`, `ocsid`
                FROM `glpi_plugin_ocsinventoryng_ocslinks`
                LEFT JOIN `glpi_computers`
                     ON `glpi_computers`.`id`=`glpi_plugin_ocsinventoryng_ocslinks`.`computers_id`
                WHERE `glpi_computers`.`id` IS NOT NULL
                      AND `ocsid` = '$ocsid'
                      AND `plugin_ocsinventoryng_ocsservers_id` = '$plugin_ocsinventoryng_ocsservers_id'";
      $result_glpi_plugin_ocsinventoryng_ocslinks = $DB->query($query);

      if ($DB->numrows($result_glpi_plugin_ocsinventoryng_ocslinks)) {
         $datas = $DB->fetch_array($result_glpi_plugin_ocsinventoryng_ocslinks);
         //Return code to indicates that the machine was synchronized
         //or only last inventory date changed
         return self::updateComputer($datas["id"], $plugin_ocsinventoryng_ocsservers_id, 1, 0);
      }

      return self::importComputer($ocsid, $plugin_ocsinventoryng_ocsservers_id, $lock,
                                  $defaultentity, $defaultlocation);
   }


   static function checkVersion() {
      global $PluginOcsinventoryngDBocs;

      # Check OCS version
      $result = $PluginOcsinventoryngDBocs->query("SELECT `TVALUE`
                                                   FROM `config`
                                                   WHERE `NAME` = 'GUI_VERSION'");

      return $PluginOcsinventoryngDBocs->result($result, 0, 0);
   }


   static function checkConfig($what=1) {
      global $PluginOcsinventoryngDBocs;

      # Check OCS version
      if ($what & 1) {
         $result = $PluginOcsinventoryngDBocs->query("SELECT `TVALUE`
                                                      FROM `config`
                                                      WHERE `NAME` = 'GUI_VERSION'");

         // Update OCS version on ocsservers
         if ($result && $PluginOcsinventoryngDBocs->numrows($result)) {
            $server = new PluginOcsinventoryngOcsServer();
            $server->update(array('id'          => $PluginOcsinventoryngDBocs->ocsservers_id,
                                  'ocs_version' => $PluginOcsinventoryngDBocs->result($result,0,0)));
         }

         if (!$result || $PluginOcsinventoryngDBocs->numrows($result) != 1
             || ($PluginOcsinventoryngDBocs->result($result, 0, 0) < self::OCS_VERSION_LIMIT
                 && strpos($PluginOcsinventoryngDBocs->result($result, 0, 0),'2.0') !== 0)) { // hack for 2.0 RC
            return false;
         }
      }

      // Check TRACE_DELETED in CONFIG
      if ($what & 2) {
         $result = $PluginOcsinventoryngDBocs->query("SELECT `IVALUE`
                                                      FROM `config`
                                                      WHERE `NAME` = 'TRACE_DELETED'");

         if ($PluginOcsinventoryngDBocs->numrows($result) != 1
             || $PluginOcsinventoryngDBocs->result($result, 0, 0) != 1) {
            $query = "UPDATE `config`
                      SET `IVALUE` = '1'
                      WHERE `NAME` = 'TRACE_DELETED'";

            if (!$PluginOcsinventoryngDBocs->query($query)) {
               return false;
            }
         }
      }

      // Check write access on hardware.CHECKSUM
      if ($what & 4) {
         if (!$PluginOcsinventoryngDBocs->query("UPDATE `hardware`
                                                 SET `CHECKSUM` = CHECKSUM
                                                 LIMIT 1")) {
         return false;
         }
      }

      // Check delete access on deleted_equiv
      if ($what & 8) {
         if (!$PluginOcsinventoryngDBocs->query("DELETE
                                                 FROM `deleted_equiv`
                                                 LIMIT 0")) {
            return false;
         }
      }

      return true;
   }


   static function manageDeleted($plugin_ocsinventoryng_ocsservers_id) {
      global $DB, $PluginOcsinventoryngDBocs, $CFG_GLPI;

      if (!(self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id)
            && self::checkConfig(1))) {
         return false;
      }

      $query = "SELECT *
                FROM `deleted_equiv`
                ORDER BY `DATE`";
      $result = $PluginOcsinventoryngDBocs->query($query);

      if ($PluginOcsinventoryngDBocs->numrows($result)) {
         $deleted = array();
         while ($data = $PluginOcsinventoryngDBocs->fetch_array($result)) {
            $deleted[$data["DELETED"]] = $data["EQUIVALENT"];
         }

         if (count($deleted)) {
            foreach ($deleted as $del => $equiv) {
               if (!empty ($equiv) && !is_null($equiv)) { // New name

                  // Get hardware due to bug of duplicates management of OCS
                  if (strstr($equiv,"-")) {
                     $query_ocs = "SELECT *
                                   FROM `hardware`
                                   WHERE `DEVICEID` = '$equiv'";
                     $result_ocs = $PluginOcsinventoryngDBocs->query($query_ocs);

                     if ($data = $PluginOcsinventoryngDBocs->fetch_array($result_ocs)) {
                        $query = "UPDATE `glpi_plugin_ocsinventoryng_ocslinks`
                                  SET `ocsid` = '" . $data["ID"] . "',
                                      `ocs_deviceid` = '" . $data["DEVICEID"] . "'
                                  WHERE `ocs_deviceid` = '$del'
                                        AND `plugin_ocsinventoryng_ocsservers_id`
                                                = '$plugin_ocsinventoryng_ocsservers_id'";
                        $DB->query($query);

                        //Update hardware checksum due to a bug in OCS
                        //(when changing netbios name, software checksum is set instead of hardware checksum...)
                        $querychecksum = "UPDATE `hardware`
                                          SET `CHECKSUM` = (CHECKSUM | ".pow(2, self::HARDWARE_FL).")
                                          WHERE `ID` = '".$data["ID"]."'";
                        $PluginOcsinventoryngDBocs->query($querychecksum);
                  // } else {
                        // We're damned ! no way to find new ID
                        // TODO : delete ocslinks ?

                     }

                  } else {
                     $query_ocs = "SELECT *
                                   FROM `hardware`
                                   WHERE `ID` = '$equiv'";
                     $result_ocs = $PluginOcsinventoryngDBocs->query($query_ocs);

                     if ($data = $PluginOcsinventoryngDBocs->fetch_array($result_ocs)) {
                        $query = "UPDATE `glpi_plugin_ocsinventoryng_ocslinks`
                                  SET `ocsid` = '" . $data["ID"] . "',
                                      `ocs_deviceid` = '" . $data["DEVICEID"] . "'
                                  WHERE `ocsid` = '$del'
                                        AND `plugin_ocsinventoryng_ocsservers_id`
                                                = '$plugin_ocsinventoryng_ocsservers_id'";
                        $DB->query($query);

                        //Update hardware checksum due to a bug in OCS
                        //(when changing netbios name, software checksum is set instead of hardware checksum...)
                        $querychecksum = "UPDATE `hardware`
                                          SET `CHECKSUM` = (CHECKSUM | ".pow(2, self::HARDWARE_FL).")
                                          WHERE `ID` = '".$data["ID"]."'";
                        $PluginOcsinventoryngDBocs->query($querychecksum);
                     } else {
                        // Not found, probably because ID change twice since previous sync
                        // No way to found new DEVICEID
                        $query = "UPDATE `glpi_plugin_ocsinventoryng_ocslinks`
                                  SET `ocsid` = '$equiv'
                                  WHERE `ocsid` = '$del'
                                        AND `ocsservers_id` = '$ocsservers_id'";
                        $DB->query($query);
                        // for history, see below
                        $data = array('ID' => $equiv);
                     }
                  }

                  if ($data) {
                     $sql_id = "SELECT `computers_id`
                                FROM `glpi_plugin_ocsinventoryng_ocslinks`
                                WHERE `ocsid` = '".$data["ID"]."'
                                      AND `plugin_ocsinventoryng_ocsservers_id`
                                             = '$plugin_ocsinventoryng_ocsservers_id'";
                     if ($res_id = $DB->query($sql_id)) {
                        if ($DB->numrows($res_id)>0) {
                           //Add history to indicates that the ocsid changed
                           $changes[0] = '0';
                           //Old ocsid
                           $changes[1] = $del;
                           //New ocsid
                           $changes[2] = $data["ID"];
                           PluginOcsinventoryngOcslink::history($DB->result($res_id, 0, "computers_id"), $changes,
                                                                PluginOcsinventoryngOcslink::HISTORY_OCS_IDCHANGED);
                        }
                     }
                  }

               } else { // Deleted

                  $ocslinks_toclean = array();
                  if (strstr($del,"-")) {
                     $link = "ocs_deviceid";
                  } else {
                     $link = "ocsid";
                  }
                  $query = "SELECT *
                            FROM `glpi_plugin_ocsinventoryng_ocslinks`
                            WHERE `". $link."` = '$del'
                                  AND `plugin_ocsinventoryng_ocsservers_id`
                                             = '$plugin_ocsinventoryng_ocsservers_id'";

                  if ($result = $DB->query($query)) {
                     if ($DB->numrows($result)>0) {
                        $data                          = $DB->fetch_array($result);
                        $ocslinks_toclean[$data['id']] = $data['id'];
                     }
                  }
                  self::cleanLinksFromList($plugin_ocsinventoryng_ocsservers_id, $ocslinks_toclean);
               }

               // Delete item in DB
               $equiv_clean=" `EQUIVALENT` = '$equiv'";
               if (empty($equiv)) {
                  $equiv_clean=" (`EQUIVALENT` = '$equiv'
                                  OR `EQUIVALENT` IS NULL ) ";
               }
               $query="DELETE
                       FROM `deleted_equiv`
                       WHERE `DELETED` = '$del'
                             AND $equiv_clean";
               $PluginOcsinventoryngDBocs->query($query);
            }
         }
      }
   }


   /**
    * Return field matching between OCS and GLPI
    *
    * @return array of glpifield => ocsfield
   **/
   static function getOcsFieldsMatching() {

      // Manufacturer and Model both as text (for rules) and as id (for import)
      return array('manufacturer'                     => 'SMANUFACTURER',
                   'manufacturers_id'                 => 'SMANUFACTURER',
                   'os_license_number'                => 'WINPRODKEY',
                   'os_licenseid'                     => 'WINPRODID',
                   'operatingsystems_id'              => 'OSNAME',
                   'operatingsystemversions_id'       => 'OSVERSION',
                   'operatingsystemservicepacks_id'   => 'OSCOMMENTS',
                   'domains_id'                       => 'WORKGROUP',
                   'contact'                          => 'USERID',
                   'name'                             => 'NAME',
                   'comment'                          => 'DESCRIPTION',
                   'serial'                           => 'SSN',
                   'model'                            => 'SMODEL',
                   'computermodels_id'                => 'SMODEL',
                   'TAG'                              => 'TAG');
   }


   static function getComputerInformations($ocs_fields=array(), $cfg_ocs, $entities_id,
                                           $locations_id=0) {

      $input                  = array();
      $input["is_dynamic"] = 1;

      if ($cfg_ocs["states_id_default"]>0) {
          $input["states_id"] = $cfg_ocs["states_id_default"];
       }

      $input["entities_id"] = $entities_id;

      if ($locations_id) {
        $input["locations_id"] = $locations_id;
      }

      $input['ocsid'] = $ocs_fields['ID'];

      foreach (self::getOcsFieldsMatching() as $glpi_field => $ocs_field) {
         if (isset($ocs_fields[$ocs_field])) {
            $table     = getTableNameForForeignKeyField($glpi_field);
            $ocs_field = Toolbox::encodeInUtf8($ocs_field);

            //Field a a foreing key
            if ($table != '') {
               $itemtype         = getItemTypeForTable($table);
               $item             = new $itemtype();
               $external_params  = array();

               foreach ($item->additional_fields_for_dictionnary as $field) {
                  if (isset($ocs_fields[$field])) {
                     $external_params[$field] = $ocs_fields[$field];
                  } else {
                     $external_params[$field] = "";
                  }
               }

               $input[$glpi_field] = Dropdown::importExternal($itemtype, $ocs_fields[$ocs_field],
                                                              $entities_id, $external_params);
            } else {
               switch ($glpi_field) {
                  default :
                     $input[$glpi_field] = $ocs_fields[$ocs_field];
                     break;

                  case 'contact' :
                    if ($users_id = User::getIDByField('name', $ocs_fields[$ocs_field])) {
                       $input[$glpi_field] = $users_id;
                    }
                     break;

                  case 'comment' :
                     $input[$glpi_field] = '';
                     if (!empty ($ocs_fields["DESCRIPTION"])
                         && $ocs_fields["DESCRIPTION"] != NOT_AVAILABLE) {
                        $input[$glpi_field] .= $ocs_fields["DESCRIPTION"] . "\r\n";
                     }
                     $input[$glpi_field] .= addslashes(sprintf(__('%1$s %2$s'), $input[$glpi_field],
                                                               sprintf(__('%1$s: %2$s'),
                                                                       __('Swap', 'ocsinventoryng'),
                                                                       $ocs_fields["SWAP"])));
                     break;
               }
            }
         }
      }
      return $input;
   }


   static function setChecksumForComputer($ocsid,$checksum,$escape=false) {
      global $PluginOcsinventoryngDBocs;

      // Set OCS checksum to max value
      if (!$escape) {
         $checksum = "'" . $checksum . "'";
      }
      $query = "UPDATE `hardware`
                SET `CHECKSUM` = $checksum
                WHERE `ID` = '$ocsid'";
      $PluginOcsinventoryngDBocs->query($query);
   }


   static function importComputer($ocsid, $plugin_ocsinventoryng_ocsservers_id, $lock=0,
                                  $defaultentity=-1, $defaultlocation=-1) {
      global $PluginOcsinventoryngDBocs;

      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);
      $comp = new Computer();

      $rules_matched = array();
      self::setChecksumForComputer($ocsid, self::MAX_CHECKSUM);

      //No entity or location predefined, check rules
      if ($defaultentity == -1 || $defaultlocation == 0) {
         //Try to affect computer to an entity
         $rule = new RuleImportEntityCollection();
         $data = array();
         $data = $rule->processAllRules(array('ocsservers_id' => $plugin_ocsinventoryng_ocsservers_id,
                                              '_source'       => 'ocsinventoryng'),
                                        array(), array('ocsid' => $ocsid));
      } else {
         //An entity or a location has already been defined via the web interface
         $data['entities_id']  = $defaultentity;
         $data['locations_id'] = $defaultlocation;
      }

      //Try to match all the rules, return the first good one, or null if not rules matched
      if (isset ($data['entities_id']) && $data['entities_id']>=0) {
         if ($lock) {
            while (!$fp = self::setEntityLock($data['entities_id'])) {
               sleep(1);
            }
         }

         //Store rule that matched
         if (isset($data['_ruleid'])) {
            $rules_matched['RuleImportEntity'] = $data['_ruleid'];
         }

         //New machine to import
         $query = "SELECT `hardware`.*, `bios`.*, accountinfo.*
                   FROM `hardware`
                   LEFT JOIN `accountinfo` ON (`accountinfo`.`HARDWARE_ID`=`hardware`.`ID`)
                   LEFT JOIN `bios` ON (`bios`.`HARDWARE_ID`=`hardware`.`ID`)
                   WHERE `hardware`.`ID` = '$ocsid'";
         $result = $PluginOcsinventoryngDBocs->query($query);

         if ($result && $PluginOcsinventoryngDBocs->numrows($result) == 1) {
            $line    = $PluginOcsinventoryngDBocs->fetch_array($result);
            $line    = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line));

            $locations_id = (isset($data['locations_id'])?$data['locations_id']:0);
            $input   = self::getComputerInformations($line,
                                                     self::getConfig($plugin_ocsinventoryng_ocsservers_id),
                                                     $data['entities_id'], $locations_id);

            //Check if machine could be linked with another one already in DB
            $rulelink         = new RuleImportComputerCollection();
            $rulelink_results = array();
            $params           = array('entities_id'   => $data['entities_id'],
                                      'plugin_ocsinventoryng_ocsservers_id'
                                                      => $plugin_ocsinventoryng_ocsservers_id,
                                       'ocsid'        => $ocsid);
            $rulelink_results = $rulelink->processAllRules(Toolbox::stripslashes_deep($input),
                                                           array(), $params);

            //If at least one rule matched
            //else do import as usual
            if (isset($rulelink_results['action'])) {
               $rules_matched['RuleImportComputer'] = $rulelink_results['_ruleid'];

               switch ($rulelink_results['action']) {
                  case self::LINK_RESULT_NO_IMPORT :
                     return array('status'     => self::COMPUTER_LINK_REFUSED,
                                  'entities_id'  => $data['entities_id'],
                                  'rule_matched' => $rules_matched);

                  case self::LINK_RESULT_LINK :
                     if (is_array($rulelink_results['found_computers'])
                         && count($rulelink_results['found_computers']) > 0) {

                        foreach ($rulelink_results['found_computers'] as $tmp => $computers_id) {
                           if (self::linkComputer($ocsid, $plugin_ocsinventoryng_ocsservers_id,
                                                  $computers_id)) {
                              return array('status'       => self::COMPUTER_LINKED,
                                            'entities_id'  => $data['entities_id'],
                                            'rule_matched' => $rules_matched,
                                            'computers_id' => $computers_id);
                           }
                        }
                     break;
                  }
               }
            }

            $computers_id = $comp->add($input, array('unicity_error_message' => false));
            if ($computers_id) {
               $ocsid      = $line['ID'];
               $changes[0] = '0';
               $changes[1] = "";
               $changes[2] = $ocsid;
               PluginOcsinventoryngOcslink::history($computers_id, $changes,
                                                    PluginOcsinventoryngOcslink::HISTORY_OCS_IMPORT);

               if ($idlink = self::ocsLink($line['ID'], $plugin_ocsinventoryng_ocsservers_id,
                                           $computers_id)) {
                  self::updateComputer($idlink, $plugin_ocsinventoryng_ocsservers_id, 0);
               }

               //Return code to indicates that the machine was imported
               return array('status'       => self::COMPUTER_IMPORTED,
                            'entities_id'  => $data['entities_id'],
                            'rule_matched' => $rules_matched,
                            'computers_id' => $computers_id);
            }
            return array('status'       => self::COMPUTER_NOT_UNIQUE,
                         'entities_id'  => $data['entities_id'],
                         'rule_matched' => $rules_matched) ;
         }

         if ($lock) {
            self::removeEntityLock($data['entities_id'], $fp);
         }
      }
      //ELSE Return code to indicates that the machine was not imported because it doesn't matched rules
      return array('status'       => self::COMPUTER_FAILED_IMPORT,
                    'rule_matched' => $rules_matched);
   }


   /** Update a ocs computer
    *
    * @param $ID integer : ID of ocslinks row
    * @param $plugin_ocsinventoryng_ocsservers_id integer : ocs server ID
    * @param $dohistory bool : do history ?
    * @param $force bool : force update ?
    *
    * @return action done
   **/
   static function updateComputer($ID, $plugin_ocsinventoryng_ocsservers_id, $dohistory, $force=0) {
      global $DB, $PluginOcsinventoryngDBocs, $CFG_GLPI;

      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);
      $cfg_ocs = self::getConfig($plugin_ocsinventoryng_ocsservers_id);

      $query = "SELECT *
                FROM `glpi_plugin_ocsinventoryng_ocslinks`
                WHERE `id` = '$ID'
                      AND `plugin_ocsinventoryng_ocsservers_id`
                              = '$plugin_ocsinventoryng_ocsservers_id'";
      $result = $DB->query($query);

      if ($DB->numrows($result) == 1) {
         $line = $DB->fetch_assoc($result);
         $comp = new Computer();
         $comp->getFromDB($line["computers_id"]);

         // Get OCS ID
         $query_ocs = "SELECT *
                       FROM `hardware`
                       WHERE `ID` = '" . $line['ocsid'] . "'";
         $result_ocs = $PluginOcsinventoryngDBocs->query($query_ocs);

         // Need do history to be 2 not to lock fields
         if ($dohistory) {
            $dohistory = 2;
         }

         if ($PluginOcsinventoryngDBocs->numrows($result_ocs) == 1) {
            $data_ocs = Toolbox::addslashes_deep($PluginOcsinventoryngDBocs->fetch_array($result_ocs));

            // automatic transfer computer
            if ($CFG_GLPI['transfers_id_auto']>0 && Session::isMultiEntitiesMode()) {
               self::transferComputer($line, $data_ocs);
               $comp->getFromDB($line["computers_id"]);
            }

            // update last_update and and last_ocs_update
            $query = "UPDATE `glpi_plugin_ocsinventoryng_ocslinks`
                      SET `last_update` = '" . $_SESSION["glpi_currenttime"] . "',
                          `last_ocs_update` = '" . $data_ocs["LASTDATE"] . "',
                          `ocs_agent_version` = '".$data_ocs["USERAGENT"]." '
                      WHERE `id` = '$ID'";
            $DB->query($query);

            if ($force) {
               $ocs_checksum = self::MAX_CHECKSUM;
               self::setChecksumForComputer($line['ocsid'], $ocs_checksum);
            } else {
               $ocs_checksum = $data_ocs["CHECKSUM"];
            }

            $mixed_checksum = intval($ocs_checksum) & intval($cfg_ocs["checksum"]);

            //By default log history
            $loghistory["history"] = 1;

            // Is an update to do ?
            if ($mixed_checksum) {

               // Get updates on computers :
               $computer_updates = importArrayFromDB($line["computer_update"]);
               if (!in_array(self::IMPORT_TAG_078, $computer_updates)) {
                  $computer_updates = self::migrateComputerUpdates($line["computers_id"],
                                                                   $computer_updates);
               }
               // Update Administrative informations
               self::updateAdministrativeInfo($line['computers_id'], $line['ocsid'],
                                              $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                              $computer_updates, $comp->fields['entities_id'],
                                              $dohistory);

               if ($mixed_checksum & pow(2, self::HARDWARE_FL)) {
                  $p = array('computers_id'      => $line['computers_id'],
                             'ocs_id'            => $line['ocsid'],
                             'plugin_ocsinventoryng_ocsservers_id'
                                                 => $plugin_ocsinventoryng_ocsservers_id,
                             'cfg_ocs'           => $cfg_ocs,
                             'computers_updates' => $computer_updates,
                             'dohistory'         => $dohistory,
                             'check_history'     => true,
                             'entities_id'       => $comp->fields['entities_id']);
                  $loghistory = self::updateHardware($p);
               }

               if ($mixed_checksum & pow(2, self::BIOS_FL)) {
                  self::updateBios($line['computers_id'], $line['ocsid'],
                                   $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                   $computer_updates, $dohistory, $comp->fields['entities_id']);
               }

               // Get import devices
               $import_device = array();
               $types = Item_Devices::getDeviceTypes();
               foreach ($types as $old => $type) {
                  $associated_type  = str_replace('Item_', '', $type);
                  $associated_table = getTableForItemType($associated_type);
                  $fk               = getForeignKeyFieldForTable($associated_table);

                  $query = "SELECT `i`.`id`, `t`.`designation` as `name`
                            FROM `".getTableForItemType($type)."` as i
                            LEFT JOIN `$associated_table` as t ON (`t`.`id`=`i`.`$fk`)
                            WHERE `itemtype`='Computer'
                               AND `items_id`='".$line['computers_id']."'
                               AND `is_dynamic`";

                  $prevalue = $type. self::FIELD_SEPARATOR;
                  foreach ($DB->request($query) as $data) {

                     $import_device[$prevalue.$data['id']] = $prevalue.$data["name"];

                     // TODO voir si il ne serait pas plus simple propre
                     // en adaptant updateDevices
                     // $import_device[$type][$data['id']] = $data["name"];
                  }
               }

               if ($mixed_checksum & pow(2, self::MEMORIES_FL)) {
                  self::updateDevices("Item_DeviceMemory", $line['computers_id'], $line['ocsid'],
                                      $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                      $import_device, '', $dohistory);
               }

               if ($mixed_checksum & pow(2, self::STORAGES_FL)) {
                  self::updateDevices("Item_DeviceHardDrive", $line['computers_id'], $line['ocsid'],
                                      $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                      $import_device, '', $dohistory);
                  self::updateDevices("Item_DeviceDrive", $line['computers_id'], $line['ocsid'],
                                      $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                      $import_device, '', $dohistory);
               }

               if ($mixed_checksum & pow(2, self::HARDWARE_FL)) {
                  self::updateDevices("Item_DeviceProcessor", $line['computers_id'], $line['ocsid'],
                                      $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                      $import_device, '', $dohistory);
               }

               if ($mixed_checksum & pow(2, self::VIDEOS_FL)) {
                  self::updateDevices("Item_DeviceGraphicCard", $line['computers_id'], $line['ocsid'],
                                      $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                      $import_device, '', $dohistory);
               }

               if ($mixed_checksum & pow(2, self::SOUNDS_FL)) {
                  self::updateDevices("Item_DeviceSoundCard", $line['computers_id'], $line['ocsid'],
                                      $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                      $import_device, '', $dohistory);
               }

               if ($mixed_checksum & pow(2, self::NETWORKS_FL)) {
                  //TODO import_ip ?
                  //$import_ip = importArrayFromDB($line["import_ip"]);
                  self::updateDevices("Item_DeviceNetworkCard", $line['computers_id'], $line['ocsid'],
                                      $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                      $import_device, array(),
                                      $dohistory);
               }

               if ($mixed_checksum & pow(2, self::MODEMS_FL)
                   || $mixed_checksum & pow(2, self::PORTS_FL)) {
                  self::updateDevices("Item_DevicePci", $line['computers_id'], $line['ocsid'],
                                      $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                      $import_device, '', $dohistory);
               }

               if ($mixed_checksum & pow(2, self::MONITORS_FL)) {
                  // Get import monitors
                  self::importMonitor($cfg_ocs, $line['computers_id'],
                                        $plugin_ocsinventoryng_ocsservers_id, $line['ocsid'],
                                        $comp->fields["entities_id"], $dohistory);
                                 }

               if ($mixed_checksum & pow(2, self::PRINTERS_FL)) {
                  // Get import printers
                  self::importPrinter($cfg_ocs, $line['computers_id'],
                                        $plugin_ocsinventoryng_ocsservers_id, $line['ocsid'],
                                        $comp->fields["entities_id"], $dohistory);
               }

               if ($mixed_checksum & pow(2, self::INPUTS_FL)){
                  // Get import peripheral
                  self::importPeripheral($cfg_ocs, $line['computers_id'],
                                          $plugin_ocsinventoryng_ocsservers_id, $line['ocsid'],
                                          $comp->fields["entities_id"], $dohistory);
               }

               if ($mixed_checksum & pow(2, self::SOFTWARES_FL)){
                  // Get import software
                  self::updateSoftware($line['computers_id'], $comp->fields["entities_id"],
                                       $line['ocsid'], $plugin_ocsinventoryng_ocsservers_id,
                                       $cfg_ocs,
                                       (!$loghistory["history"]?0:$dohistory));
               }

               if ($mixed_checksum & pow(2, self::DRIVES_FL)){
                  // Get import drives
                  self::updateDisk($line['computers_id'], $line['ocsid'],
                                   $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs, $dohistory);
               }

               if ($mixed_checksum & pow(2, self::REGISTRY_FL)){
                  //import registry entries not needed
                  self::updateRegistry($line['computers_id'], $line['ocsid'],
                                       $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs);
               }

               if ($mixed_checksum & pow(2, self::VIRTUALMACHINES_FL)){
                  // Get import vm
                  self::updateVirtualMachines($line['computers_id'], $line['ocsid'],
                                              $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                              $dohistory);
               }

               //Update TAG
               self::updateTag($line, $data_ocs);
                
               // Update OCS Cheksum
               $newchecksum = "(CHECKSUM - $mixed_checksum)";
               self::setChecksumForComputer($line['ocsid'], $newchecksum, true);

               //Return code to indicate that computer was synchronized
               return array('status'       => self::COMPUTER_SYNCHRONIZED,
                            'entitites_id' => $comp->fields["entities_id"],
                            'rule_matched' => array(),
                            'computers_id' => $line['computers_id']);
            }

            // ELSE Return code to indicate only last inventory date changed
            return array('status'       => self::COMPUTER_NOTUPDATED,
                         'entities_id'  => $comp->fields["entities_id"],
                         'rule_matched' => array(),
                         'computers_id' => $line['computers_id']);
         }
      }
   }


   static function getComputerHardware($params = array()){
      global $DB, $PluginOcsinventoryngDBocs;

      $options['computers_id']                        = 0;
      $options['ocs_id']                              = 0;
      $options['plugin_ocsinventoryng_ocsservers_id'] = 0;
      $options['cfg_ocs']                             = array();
      $options['computers_update']                    = array();
      $options['check_history']                       = true;
      $options['do_history']                          = 2;

      foreach ($params as $key => $value){
         $options[$key] = $value;
      }

      $is_utf8 = $options['cfg_ocs']["ocs_db_utf8"];
      self::checkOCSconnection($options['plugin_ocsinventoryng_ocsservers_id']);

      $query = "SELECT*
                FROM `hardware`
                WHERE `ID` = '".$options['ocs_id']."'";
      $result = $PluginOcsinventoryngDBocs->query($query);

      $logHistory = 1;

      if ($PluginOcsinventoryngDBocs->numrows($result) == 1) {
         $line       = $PluginOcsinventoryngDBocs->fetch_assoc($result);
         $line       = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line));
         $compupdate = array();

         if ($options['cfg_ocs']["import_os_serial"]
             && !in_array("os_license_number", $options['computers_updates'])) {

            if (!empty ($line["WINPRODKEY"])) {
               $compupdate["os_license_number"]
                  = self::encodeOcsDataInUtf8($is_utf8, $line["WINPRODKEY"]);
            }
            if (!empty ($line["WINPRODID"])) {
               $compupdate["os_licenseid"]
                  = self::encodeOcsDataInUtf8($is_utf8, $line["WINPRODID"]);
            }
         }

         if ($options['check_history']) {
            $sql_computer = "SELECT `glpi_operatingsystems`.`name` AS os_name,
                                    `glpi_operatingsystemservicepacks`.`name` AS os_sp
                             FROM `glpi_computers`,
                                  `glpi_plugin_ocsinventoryng_ocslinks`,
                                  `glpi_operatingsystems`,
                                  `glpi_operatingsystemservicepacks`
                             WHERE `glpi_plugin_ocsinventoryng_ocslinks`.`computers_id`
                                          = `glpi_computers`.`id`
                                   AND `glpi_operatingsystems`.`id`
                                          = `glpi_computers`.`operatingsystems_id`
                                   AND `glpi_operatingsystemservicepacks`.`id`
                                          =`glpi_computers`.`operatingsystemservicepacks_id`
                                   AND `glpi_plugin_ocsinventoryng_ocslinks`.`ocsid`
                                          = '".$options['ocs_id']."'
                                   AND `glpi_plugin_ocsinventoryng_ocslinks`.`plugin_ocsinventoryng_ocsservers_id`
                                          = '".$options['plugin_ocsinventoryng_ocsservers_id']."'";

            $res_computer = $DB->query($sql_computer);

            if ($DB->numrows($res_computer) ==  1) {
               $data_computer = $DB->fetch_array($res_computer);
               $computerOS    = $data_computer["os_name"];
               $computerOSSP  = $data_computer["os_sp"];

               //Do not log software history in case of OS or Service Pack change
               if (!$options['do_history']
                   || $computerOS != $line["OSNAME"]
                   || $computerOSSP != $line["OSCOMMENTS"]) {
                  $logHistory = 0;
               }
            }
         }

         if ($options['cfg_ocs']["import_general_os"]) {
            if (!in_array("operatingsystems_id", $options['computers_updates'])) {
               $osname = self::encodeOcsDataInUtf8($is_utf8, $line['OSNAME']);
               $compupdate["operatingsystems_id"] = Dropdown::importExternal('OperatingSystem',
                                                                             $osname);
            }

            if (!in_array("operatingsystemversions_id", $options['computers_updates'])) {
               $compupdate["operatingsystemversions_id"]
                     = Dropdown::importExternal('OperatingSystemVersion',
                                                self::encodeOcsDataInUtf8($is_utf8,
                                                                          $line["OSVERSION"]));
            }

            if (!strpos($line["OSCOMMENTS"], "CEST")
                && !in_array("operatingsystemservicepacks_id", $options['computers_updates'])) {// Not linux comment

               $compupdate["operatingsystemservicepacks_id"]
                     = Dropdown::importExternal('OperatingSystemServicePack',
                                                self::encodeOcsDataInUtf8($is_utf8,
                                                                          $line["OSCOMMENTS"]));
            }
         }

         if ($options['cfg_ocs']["import_general_domain"]
             && !in_array("domains_id", $options['computers_updates'])){
            $compupdate["domains_id"] = Dropdown::importExternal('Domain',
                                                                 self::encodeOcsDataInUtf8($is_utf8,
                                                                                           $line["WORKGROUP"]));
         }

         if ($options['cfg_ocs']["import_general_contact"]
             && !in_array("contact", $options['computers_updates'])){

            $compupdate["contact"] = self::encodeOcsDataInUtf8($is_utf8, $line["USERID"]);
            $query = "SELECT `id`
                      FROM `glpi_users`
                      WHERE `name` = '" . $line["USERID"] . "';";
            $result = $DB->query($query);

            if ($DB->numrows($result) == 1 && !in_array("users_id", $options['computers_updates'])){
               $compupdate["users_id"] = $DB->result($result, 0, 0);
            }
         }

         if ($options['cfg_ocs']["import_general_name"]
             && !in_array("name", $options['computers_updates'])){
            $compupdate["name"] = self::encodeOcsDataInUtf8($is_utf8, $line["NAME"]);
         }

         if ($options['cfg_ocs']["import_general_comment"]
             && !in_array("comment", $options['computers_updates'])){

            $compupdate["comment"] = "";
            if (!empty ($line["DESCRIPTION"]) && $line["DESCRIPTION"] != NOT_AVAILABLE){
               $compupdate["comment"] .= self::encodeOcsDataInUtf8($is_utf8, $line["DESCRIPTION"])
                                        . "\r\n";
            }
            $compupdate["comment"] .= sprintf(__('%1$s: %2$s'), __('Swap', 'ocsinventoryng'),
                                              self::encodeOcsDataInUtf8($is_utf8,$line["SWAP"]));
         }

         if ($options['cfg_ocs']['ocs_version'] >= self::OCS1_3_VERSION_LIMIT
             && $options['cfg_ocs']["import_general_uuid"]
             && !in_array("uuid", $options['computers_updates'])){
            $compupdate["uuid"] = $line["UUID"];
         }

         return array('logHistory' => $logHistory, 'fields'     => $compupdate);
      }
   }


   /**
    * Update the computer hardware configuration
    *
    * @param $params array
    *
    * @return nothing.
   **/
   static function updateHardware($params=array()){
      global $DB, $PluginOcsinventoryngDBocs;

      $p = array('computers_id'                          => 0,
                 'ocs_id'                                => 0,
                 'plugin_ocsinventoryng_ocsservers_id'   => 0,
                 'cfg_ocs'                               => array(),
                 'computers_updates'                     => array(),
                 'dohistory'                             => true,
                 'check_history'                         => true,
                 'entities_id'                           => 0);
      foreach ($params as $key => $value){
         $p[$key] = $value;
      }

      self::checkOCSconnection($p['plugin_ocsinventoryng_ocsservers_id']);
      $results = self::getComputerHardware($params);

      if (count($results['fields'])){
         $results['fields']["id"]          = $p['computers_id'];
         $results['fields']["entities_id"] = $p['entities_id'];
         $results['fields']["_nolock"]     = true;
         $comp                             = new Computer();
         $comp->update($results['fields'], $p['dohistory']);
      }
      //}

      return array("history" => $results['logHistory']);
   }


   /**
    * Update the computer bios configuration
    *
    * Update the computer bios configuration
    *
    * @param $computers_id integer : ocs computer id.
    * @param $ocsid integer : glpi computer id
    * @param $plugin_ocsinventoryng_ocsservers_id integer : ocs server id
    * @param $cfg_ocs array : ocs config
    * @param $computer_updates array : already updated fields of the computer
    * @param $dohistory boolean : log changes?
    * @param entities_id the entity in which the computer is imported
    *
    * @return nothing.
   **/
   static function updateBios($computers_id, $ocsid, $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                              $computer_updates, $dohistory=2, $entities_id=0){
      global $PluginOcsinventoryngDBocs;

      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);

      $query = "SELECT*
                FROM `bios`
                WHERE `HARDWARE_ID` = '$ocsid'";
      $result = $PluginOcsinventoryngDBocs->query($query);

      $compupdate = array();
      if ($PluginOcsinventoryngDBocs->numrows($result) == 1){
         $line       = $PluginOcsinventoryngDBocs->fetch_assoc($result);
         $line       = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line));
         $compudate  = array();

         if ($cfg_ocs["import_general_serial"] && !in_array("serial", $computer_updates)){
            $compupdate["serial"] = self::encodeOcsDataInUtf8($cfg_ocs['ocs_db_utf8'],
                                                              $line["SSN"]);
         }

         if ($cfg_ocs["import_general_model"]
             && !in_array("computermodels_id", $computer_updates)) {

            $compupdate["computermodels_id"]
               = Dropdown::importExternal('ComputerModel',
                                          self::encodeOcsDataInUtf8($cfg_ocs['ocs_db_utf8'],
                                          $line["SMODEL"]),
                                          -1,
                                          (isset($line["SMANUFACTURER"])
                                                 ?array("manufacturer" => $line["SMANUFACTURER"])
                                                 :array()));
         }

         if ($cfg_ocs["import_general_manufacturer"]
             && !in_array("manufacturers_id", $computer_updates)) {

            $compupdate["manufacturers_id"]
               = Dropdown::importExternal('Manufacturer',
                                          self::encodeOcsDataInUtf8($cfg_ocs['ocs_db_utf8'],
                                                                    $line["SMANUFACTURER"]));
         }

         if ($cfg_ocs["import_general_type"]
             && !empty ($line["TYPE"])
             && !in_array("computertypes_id", $computer_updates)) {

            $compupdate["computertypes_id"]
               = Dropdown::importExternal('ComputerType',
                                          self::encodeOcsDataInUtf8($cfg_ocs['ocs_db_utf8'],
                                                                    $line["TYPE"]));
         }

         if (count($compupdate)) {
            $compupdate["id"]          = $computers_id;
            $compupdate["entities_id"] = $entities_id;
            $compupdate["_nolock"]     = true;
            $comp                      = new Computer();
            $comp->update($compupdate, $dohistory);
         }
      }
   }


   /**
    * Import a group from OCS table.
    *
    * @param $value string : Value of the new dropdown.
    * @param $entities_id int : entity in case of specific dropdown
    *
    * @return integer : dropdown id.
   **/
   static function importGroup($value, $entities_id){
      global $DB;

      if (empty ($value)){
         return 0;
      }

      $query2 = "SELECT `id`
                 FROM `glpi_groups`
                 WHERE `name` = '$value'
                       AND `entities_id` = '$entities_id'";
      $result2 = $DB->query($query2);

      if ($DB->numrows($result2) == 0){
         $group                = new Group();
         $input["name"]        = $value;
         $input["entities_id"] = $entities_id;
         return $group->add($input);
      }
      $line2 = $DB->fetch_array($result2);
      return $line2["id"];
   }


   /**
    * Displays a list of computers that can be cleaned.
    *
    * @param $plugin_ocsinventoryng_ocsservers_id int : id of ocs server in GLPI
    * @param $check string : parameter for HTML input checkbox
    * @param $start int : parameter for Html::printPager method
    *
    * @return nothing
   **/
   static function showComputersToClean($plugin_ocsinventoryng_ocsservers_id, $check, $start){
      global $DB, $PluginOcsinventoryngDBocs, $CFG_GLPI;

      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);

      if (!plugin_ocsinventoryng_haveRight("clean_ocsng", "r")){
         return false;
      }
      $canedit = plugin_ocsinventoryng_haveRight("clean_ocsng", "w");

      // Select unexisting OCS hardware
      $query_ocs  = "SELECT*
                     FROM `hardware`";
      $result_ocs = $PluginOcsinventoryngDBocs->query($query_ocs);

      $hardware   = array();
      if ($PluginOcsinventoryngDBocs->numrows($result_ocs) > 0){
         while ($data = $PluginOcsinventoryngDBocs->fetch_array($result_ocs)){
            $data = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($data));
            $hardware[$data["ID"]] = $data["DEVICEID"];
         }
      }

      $query = "SELECT `ocsid`
                FROM `glpi_plugin_ocsinventoryng_ocslinks`
                WHERE `plugin_ocsinventoryng_ocsservers_id`
                           = '$plugin_ocsinventoryng_ocsservers_id'";
      $result = $DB->query($query);

      $ocs_missing = array();
      if ($DB->numrows($result) > 0){
         while ($data = $DB->fetch_array($result)){
            $data = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($data));
            if (!isset ($hardware[$data["ocsid"]])){
               $ocs_missing[$data["ocsid"]] = $data["ocsid"];
            }
         }
      }

      $sql_ocs_missing = "";
      if (count($ocs_missing)){
         $sql_ocs_missing = " OR `ocsid` IN ('".implode("','",$ocs_missing)."')";
      }

      //Select unexisting computers
      $query_glpi = "SELECT `glpi_plugin_ocsinventoryng_ocslinks`.`entities_id` AS entities_id,
                            `glpi_plugin_ocsinventoryng_ocslinks`.`ocs_deviceid` AS ocs_deviceid,
                            `glpi_plugin_ocsinventoryng_ocslinks`.`last_update` AS last_update,
                            `glpi_plugin_ocsinventoryng_ocslinks`.`ocsid` AS ocsid,
                            `glpi_plugin_ocsinventoryng_ocslinks`.`id`,
                            `glpi_computers`.`name` AS name
                     FROM `glpi_plugin_ocsinventoryng_ocslinks`
                     LEFT JOIN `glpi_computers`
                           ON `glpi_computers`.`id` = `glpi_plugin_ocsinventoryng_ocslinks`.`computers_id`
                     WHERE ((`glpi_computers`.`id` IS NULL
                             AND `glpi_plugin_ocsinventoryng_ocslinks`.`plugin_ocsinventoryng_ocsservers_id`
                                    = '$plugin_ocsinventoryng_ocsservers_id')".
                            $sql_ocs_missing.")".
                           getEntitiesRestrictRequest(" AND", "glpi_plugin_ocsinventoryng_ocslinks");

      $result_glpi = $DB->query($query_glpi);

      // fetch all links missing between glpi and OCS
      $already_linked = array();
      if ($DB->numrows($result_glpi) > 0){
         while ($data = $DB->fetch_assoc($result_glpi)){
            $data = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($data));

            $already_linked[$data["ocsid"]]["entities_id"]  = $data["entities_id"];
            if (Toolbox::strlen($data["ocs_deviceid"])>20) { // Strip datetime tag
               $already_linked[$data["ocsid"]]["ocs_deviceid"] = substr($data["ocs_deviceid"], 0,
                                                                        -20);
            } else{
               $already_linked[$data["ocsid"]]["ocs_deviceid"] = $data["ocs_deviceid"];
            }
            $already_linked[$data["ocsid"]]["date"]         = $data["last_update"];
            $already_linked[$data["ocsid"]]["id"]           = $data["id"];
            $already_linked[$data["ocsid"]]["in_ocs"]       = isset($hardware[$data["ocsid"]]);

            if ($data["name"] == null){
               $already_linked[$data["ocsid"]]["in_glpi"] = 0;
            } else{
               $already_linked[$data["ocsid"]]["in_glpi"] = 1;
            }
         }
      }

      echo "<div class='center'>";
      echo "<h2>" . __('Clean links between GLPI and OCSNG', 'ocsinventoryng') . "</h2>";

      $target = $CFG_GLPI['root_doc'].'/plugins/ocsinventoryng/front/ocsng.clean.php';
      if (($numrows = count($already_linked)) > 0){
         $parameters = "check=$check";
         Html::printPager($start, $numrows, $target, $parameters);

         // delete end
         array_splice($already_linked, $start + $_SESSION['glpilist_limit']);

         // delete begin
         if ($start > 0){
            array_splice($already_linked, 0, $start);
         }

         echo "<form method='post' id='ocsng_form' name='ocsng_form' action='".$target."'>";
         if ($canedit){
            self::checkBox($target);
         }
         echo "<table class='tab_cadre'>";
         echo "<tr><th>".__('Item')."</th><th>".__('Import date in GLPI', 'ocsinventoryng')."</th>";
         echo "<th>" . __('Existing in GLPI', 'ocsinventoryng') . "</th>";
         echo "<th>" . __('Existing in OCSNG', 'ocsinventoryng') . "</th>";
         if (Session::isMultiEntitiesMode()){
            echo "<th>" . __('Entity'). "</th>";
         }
         if ($canedit){
            echo "<th>&nbsp;</th>";
         }
         echo "</tr>\n";

         echo "<tr class='tab_bg_1'><td colspan='6' class='center'>";
         if ($canedit){
            echo "<input class='submit' type='submit' name='clean_ok' value=\"".
                   _sx('button','Clean')."\">";
         }
         echo "</td></tr>\n";

         foreach ($already_linked as $ID => $tab){
            echo "<tr class='tab_bg_2 center'>";
            echo "<td>" . $tab["ocs_deviceid"] . "</td>\n";
            echo "<td>" . Html::convDateTime($tab["date"]) . "</td>\n";
            echo "<td>" . Dropdown::getYesNo($tab["in_glpi"]) . "</td>\n";
            echo "<td>" . Dropdown::getYesNo($tab["in_ocs"]) . "</td>\n";
            if (Session::isMultiEntitiesMode()){
               echo "<td>".Dropdown::getDropdownName('glpi_entities', $tab['entities_id'])."</td>\n";
            }
            if ($canedit){
               echo "<td><input type='checkbox' name='toclean[" . $tab["id"] . "]' ".
                          (($check == "all") ? "checked" : "") . "></td>";
            }
            echo "</tr>\n";
         }

         echo "<tr class='tab_bg_1'><td colspan='6' class='center'>";
         if ($canedit){
            echo "<input class='submit' type='submit' name='clean_ok' value=\"".
                   _sx('button','Clean')."\">";
         }
         echo "</td></tr>";
         echo "</table>\n";
         Html::closeForm();
         Html::printPager($start, $numrows, $target, $parameters);

      } else{
         echo "<div class='center b '>" . __('No item to clean', 'ocsinventoryng') . "</div>";
         Html::displayBackLink();
      }
      echo "</div>";
   }


   /**
    * Clean links between GLPI and OCS from a list.
    *
    * @param $plugin_ocsinventoryng_ocsservers_id int : id of ocs server in GLPI
    * @param $ocslinks_id array : ids of ocslinks to clean
    *
    * @return nothing
   **/
   static function cleanLinksFromList($plugin_ocsinventoryng_ocsservers_id, $ocslinks_id){
      global $DB;

      $cfg_ocs = self::getConfig($plugin_ocsinventoryng_ocsservers_id);

      foreach ($ocslinks_id as $key => $val){

         $query = "SELECT*
                   FROM `glpi_plugin_ocsinventoryng_ocslinks`
                   WHERE `id` = '$key'
                         AND `plugin_ocsinventoryng_ocsservers_id`
                                 = '$plugin_ocsinventoryng_ocsservers_id'";

         if ($result = $DB->query($query)){
            if ($DB->numrows($result)>0){
               $data = $DB->fetch_array($result);

               $comp = new Computer();
               if ($cfg_ocs['deleted_behavior']){
                  if ($cfg_ocs['deleted_behavior'] == 1){
                     $comp->delete( array("id" => $data["computers_id"]), 0);
                  } else{
                     if (preg_match('/STATE_(.*)/',$cfg_ocs['deleted_behavior'],$results)){
                        $tmp['id']          = $data["computers_id"];
                        $tmp['states_id']   = $results[1];
                        $tmp['entities_id'] = $data['entities_id'];
                        $tmp["_nolock"]     = true;
                        $comp->update($tmp);
                     }
                  }
               }

               //Add history to indicates that the machine was deleted from OCS
               $changes[0] = '0';
               $changes[1] = $data["ocsid"];
               $changes[2] = "";
               PluginOcsinventoryngOcslink::history($data["computers_id"], $changes,
                                                    PluginOcsinventoryngOcslink::HISTORY_OCS_DELETE);

               $query = "DELETE
                         FROM `glpi_plugin_ocsinventoryng_ocslinks`
                         WHERE `id` = '" . $data["id"] . "'";
               $DB->query($query);
            }
         }
      }
   }


   static function showComputersToUpdate($plugin_ocsinventoryng_ocsservers_id, $check, $start){
      global $DB, $PluginOcsinventoryngDBocs, $CFG_GLPI;

      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);
      if (!plugin_ocsinventoryng_haveRight("ocsng", "w")){
         return false;
      }

      $cfg_ocs    = self::getConfig($plugin_ocsinventoryng_ocsservers_id);
      $query_ocs  = "SELECT*
                     FROM `hardware`
                     WHERE (`CHECKSUM` & " . $cfg_ocs["checksum"] . ") > '0'
                     ORDER BY `LASTDATE`";
      $result_ocs = $PluginOcsinventoryngDBocs->query($query_ocs);

      $query_glpi = "SELECT `glpi_plugin_ocsinventoryng_ocslinks`.`last_update` AS last_update,
                            `glpi_plugin_ocsinventoryng_ocslinks`.`computers_id` AS computers_id,
                            `glpi_plugin_ocsinventoryng_ocslinks`.`ocsid` AS ocsid,
                            `glpi_computers`.`name` AS name,
                            `glpi_plugin_ocsinventoryng_ocslinks`.`use_auto_update`,
                            `glpi_plugin_ocsinventoryng_ocslinks`.`id`
                     FROM `glpi_plugin_ocsinventoryng_ocslinks`
                     LEFT JOIN `glpi_computers` ON (`glpi_computers`.`id`=computers_id)
                     WHERE `glpi_plugin_ocsinventoryng_ocslinks`.`plugin_ocsinventoryng_ocsservers_id`
                                 = '$plugin_ocsinventoryng_ocsservers_id'
                     ORDER BY `glpi_plugin_ocsinventoryng_ocslinks`.`use_auto_update` DESC,
                              last_update,
                              name";

      $result_glpi = $DB->query($query_glpi);
      if ($PluginOcsinventoryngDBocs->numrows($result_ocs) > 0){

         // Get all hardware from OCS DB
         $hardware = array();
         while ($data = $PluginOcsinventoryngDBocs->fetch_array($result_ocs)){
            $hardware[$data["ID"]]["date"] = $data["LASTDATE"];
            $hardware[$data["ID"]]["name"] = addslashes($data["NAME"]);
         }

         // Get all links between glpi and OCS
         $already_linked = array();
         if ($DB->numrows($result_glpi) > 0){
            while ($data = $DB->fetch_assoc($result_glpi)){
               $data = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($data));
               if (isset ($hardware[$data["ocsid"]])){
                  $already_linked[$data["ocsid"]]["date"]            = $data["last_update"];
                  $already_linked[$data["ocsid"]]["name"]            = $data["name"];
                  $already_linked[$data["ocsid"]]["id"]              = $data["id"];
                  $already_linked[$data["ocsid"]]["computers_id"]    = $data["computers_id"];
                  $already_linked[$data["ocsid"]]["ocsid"]           = $data["ocsid"];
                  $already_linked[$data["ocsid"]]["use_auto_update"] = $data["use_auto_update"];
               }
            }
         }
         echo "<div class='center'>";
         echo "<h2>" . __('Computers updated in OCSNG', 'ocsinventoryng') . "</h2>";

         $target = $CFG_GLPI['root_doc'].'/plugins/ocsinventoryng/front/ocsng.sync.php';
         if (($numrows = count($already_linked)) > 0){
            $parameters = "check=$check";
            Html::printPager($start, $numrows, $target, $parameters);

            // delete end
            array_splice($already_linked, $start + $_SESSION['glpilist_limit']);
            // delete begin
            if ($start > 0){
               array_splice($already_linked, 0, $start);
            }

            echo "<form method='post' id='ocsng_form' name='ocsng_form' action='".$target."'>";
            self::checkBox($target);

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'><td colspan='5' class='center'>";
            echo "<input class='submit' type='submit' name='update_ok' value=\"".
                   _sx('button','Synchronize', 'ocsinventoryng')."\">";
            echo "</td></tr>\n";

            echo "<tr><th>". __('Update computers', 'ocsinventoryng')."</th>";
            echo "<th>".__('Import date in GLPI', 'ocsinventoryng')."</th>";
            echo "<th>" . __('Last OCSNG inventory date', 'ocsinventoryng')."</th>";
            echo "<th>". __('Auto update', 'ocsinventoryng')."</th>";
            echo "<th>&nbsp;</th></tr>\n";

            foreach ($already_linked as $ID => $tab){
               echo "<tr class='tab_bg_2 center'>";
               echo "<td><a href='" . $CFG_GLPI["root_doc"] . "/front/computer.form.php?id=".
                          $tab["computers_id"] . "'>" . $tab["name"] . "</a></td>\n";
               echo "<td>" . Html::convDateTime($tab["date"]) . "</td>\n";
               echo "<td>" . Html::convDateTime($hardware[$tab["ocsid"]]["date"]) . "</td>\n";
               echo "<td>" . Dropdown::getYesNo($tab["use_auto_update"]) . "</td>\n";
               echo "<td><input type='checkbox' name='toupdate[" . $tab["id"] . "]' ".
                          (($check == "all") ? "checked" : "") . "></td></tr>\n";
            }

            echo "<tr class='tab_bg_1'><td colspan='5' class='center'>";
            echo "<input class='submit' type='submit' name='update_ok' value=\"".
                   _sx('button','Synchronize', 'ocsinventoryng')."\">";
            echo "<input type=hidden name='plugin_ocsinventoryng_ocsservers_id' ".
                   "value='$plugin_ocsinventoryng_ocsservers_id'>";
            echo "</td></tr>";

            echo "<tr class='tab_bg_1'><td colspan='5' class='center'>";
            self::checkBox($target);
            echo "</table>\n";
            Html::closeForm();
            Html::printPager($start, $numrows, $target, $parameters);

         } else{
            echo "<br><span class='b'>" . __('Update computers', 'ocsinventoryng') . "</span>";
         }
         echo "</div>";

      } else{
         echo "<div class='center b'>".__('No new computer to be updated', 'ocsinventoryng')."</div>";
      }
   }


   static function mergeOcsArray($computers_id, $tomerge, $field){
      global $DB;

      $query = "SELECT `$field`
                FROM `glpi_plugin_ocsinventoryng_ocslinks`
                WHERE `computers_id` = '$computers_id'";

      if ($result = $DB->query($query)){
         if ($DB->numrows($result)){
            $tab    = importArrayFromDB($DB->result($result, 0, 0));
            $newtab = array_merge($tomerge, $tab);
            $newtab = array_unique($newtab);

            $query = "UPDATE `glpi_plugin_ocsinventoryng_ocslinks`
                      SET `$field` = '" . addslashes(exportArrayToDB($newtab)) . "'
                      WHERE `computers_id` = '$computers_id'";
            if ($DB->query($query)){
               return true;
            }
         }
      }
      return false;
   }


   static function deleteInOcsArray($computers_id, $todel, $field, $is_value_to_del=false){
      global $DB;

      $query = "SELECT `$field`
                FROM `glpi_plugin_ocsinventoryng_ocslinks`
                WHERE `computers_id` = '$computers_id'";

      if ($result = $DB->query($query)){
         if ($DB->numrows($result)){
            $tab = importArrayFromDB($DB->result($result, 0, 0));

            if ($is_value_to_del){
               $todel = array_search($todel, $tab);
            }
            if (isset($tab[$todel])){
               unset ($tab[$todel]);
               $query = "UPDATE `glpi_plugin_ocsinventoryng_ocslinks`
                         SET `$field` = '" . addslashes(exportArrayToDB($tab)) . "'
                         WHERE `computers_id` = '$computers_id'";
               if ($DB->query($query)){
                  return true;
               }
            }
         }
      }
      return false;
   }


   static function replaceOcsArray($computers_id, $newArray, $field){
      global $DB;

      $newArray = addslashes(exportArrayToDB($newArray));

      $query = "SELECT `$field`
                FROM `glpi_plugin_ocsinventoryng_ocslinks`
                WHERE `computers_id` = '$computers_id'";

      if ($result = $DB->query($query)){
         if ($DB->numrows($result)){
            $query = "UPDATE `glpi_plugin_ocsinventoryng_ocslinks`
                      SET `$field` = '" . $newArray . "'
                      WHERE `computers_id` = '$computers_id'";
            $DB->query($query);
         }
      }
   }


   static function addToOcsArray($computers_id, $toadd, $field){
      global $DB;

      $query = "SELECT `$field`
                FROM `glpi_plugin_ocsinventoryng_ocslinks`
                WHERE `computers_id` = '$computers_id'";

      if ($result = $DB->query($query)){
         if ($DB->numrows($result)){
            $tab = importArrayFromDB($DB->result($result, 0, 0));

            // Stripslashes because importArray get clean array
            foreach ($toadd as $key => $val){
               $tab[$key] = stripslashes($val);
            }
            $query = "UPDATE `glpi_plugin_ocsinventoryng_ocslinks`
                      SET `$field` = '" . addslashes(exportArrayToDB($tab)) . "'
                      WHERE `computers_id` = '$computers_id'";
            $DB->query($query);
         }
      }
   }


   /**
    * Display a list of computers to add or to link
    *
    * @param plugin_ocsinventoryng_ocsservers_id the ID of the ocs server
    * @param advanced display detail about the computer import or not (target entity, matched rules, etc.)
    * @param check indicates if checkboxes are checked or not
    * @param start display a list of computers starting at rowX
    * @param entity a list of entities in which computers can be added or linked
    * @param tolinked false for an import, true for a link
    *
    * @return nothing
   **/
   static function showComputersToAdd($plugin_ocsinventoryng_ocsservers_id, $advanced, $check,
                                      $start, $entity=0, $tolinked=false){
      global $DB, $PluginOcsinventoryngDBocs, $CFG_GLPI;

      if (!plugin_ocsinventoryng_haveRight("ocsng", "w")){
         return false;
      }

      $target = $CFG_GLPI['root_doc'].'/plugins/ocsinventoryng/front/ocsng.import.php';
      if ($tolinked){
         $target = $CFG_GLPI['root_doc'].'/plugins/ocsinventoryng/front/ocsng.link.php';
      }

      $cfg_ocs = self::getConfig($plugin_ocsinventoryng_ocsservers_id);
      $WHERE   = self::getTagLimit($cfg_ocs);

      $query_ocs = "SELECT `hardware`.*,
                           `accountinfo`.`TAG` AS TAG,
                           `bios`.`SSN` AS SERIAL,
                           `bios`.`SMODEL`,
                           `bios`.`SMANUFACTURER`
                    FROM `hardware`
                    INNER JOIN `accountinfo` ON (`hardware`.`id` = `accountinfo`.`HARDWARE_ID`)
                    INNER JOIN `bios` ON (`hardware`.`id` = `bios`.`HARDWARE_ID`)".
                    (!empty($WHERE)?"WHERE $WHERE":"")."
                    ORDER BY `hardware`.`NAME`";
      $result_ocs = $PluginOcsinventoryngDBocs->query($query_ocs);

      // Existing OCS - GLPI link
      $query_glpi = "SELECT*
                     FROM `glpi_plugin_ocsinventoryng_ocslinks`
                     WHERE `plugin_ocsinventoryng_ocsservers_id`
                              = '$plugin_ocsinventoryng_ocsservers_id'";
      $result_glpi = $DB->query($query_glpi);

      if ($PluginOcsinventoryngDBocs->numrows($result_ocs) > 0){
         // Get all hardware from OCS DB
         $hardware = array();

         while ($data = $PluginOcsinventoryngDBocs->fetch_array($result_ocs)){
            $data = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($data));
            $hardware[$data["ID"]]["date"]         = $data["LASTDATE"];
            $hardware[$data["ID"]]["name"]         = $data["NAME"];
            $hardware[$data["ID"]]["TAG"]          = $data["TAG"];
            $hardware[$data["ID"]]["id"]           = $data["ID"];
            $hardware[$data["ID"]]["serial"]       = $data["SERIAL"];
            $hardware[$data["ID"]]["model"]        = $data["SMODEL"];
            $hardware[$data["ID"]]["manufacturer"] = $data["SMANUFACTURER"];

            $query_network = "SELECT*
                              FROM `networks`
                              WHERE `HARDWARE_ID` = '".$data["ID"]."'";

            //Get network informations for this computer
            //Ignore informations that contains "??"
            foreach ($PluginOcsinventoryngDBocs->request($query_network) as $network){
               if (isset($network['IPADDRESS']) && $network['IPADDRESS'] != '??'){
                  $hardware[$data["ID"]]['IPADDRESS'][] = $network['IPADDRESS'];
               }
               if (isset($network['IPSUBNET']) && $network['IPSUBNET'] != '??'){
                  $hardware[$data["ID"]]['IPSUBNET'][] = $network['IPSUBNET'];
               }
               if (isset($network['MACADDRESS']) && $network['MACADDR'] != '??'){
                  $hardware[$data["ID"]]['MACADDRESS'][] = $network['MACADDR'];
               }
            }
         }

         // Get all links between glpi and OCS
         $already_linked = array();
         if ($DB->numrows($result_glpi) > 0){
            while ($data = $PluginOcsinventoryngDBocs->fetch_array($result_glpi)){
               $already_linked[$data["ocsid"]] = $data["last_update"];
            }
         }

         // Clean $hardware from already linked element
         if (count($already_linked) > 0){
            foreach ($already_linked as $ID => $date){
               if (isset ($hardware[$ID]) && isset ($already_linked[$ID])){
                  unset ($hardware[$ID]);
               }
            }
         }

         if ($tolinked && count($hardware)){
            echo "<div class='center b'>".
                  __('Caution! The imported data (see your configuration) will overwrite the existing one',
                     'ocsinventoryng')."</div>";
         }
         echo "<div class='center'>";

         if (($numrows = count($hardware)) > 0){
            $parameters = "check=$check";
            Html::printPager($start, $numrows, $target, $parameters);

            // delete end
            array_splice($hardware, $start + $_SESSION['glpilist_limit']);

            // delete begin
            if ($start > 0){
               array_splice($hardware, 0, $start);
            }

            //Show preview form only in import even in multi-entity mode because computer import
            //can be refused by a rule
            if (!$tolinked){
               echo "<div class='firstbloc'>";
               echo "<form method='post' name='ocsng_import_mode' id='ocsng_import_mode'
                      action='$target'>\n";
               echo "<table class='tab_cadre_fixe'>";
               echo "<tr><th>". __('Manual import mode', 'ocsinventoryng'). "</th></tr>\n";
               echo "<tr class='tab_bg_1'><td class='center'>";
               if ($advanced){
                  Html::showSimpleForm($target, 'change_import_mode',
                                       __('Disable preview', 'ocsinventoryng'),
                                       array('id' => 'false'));
               } else{
                  Html::showSimpleForm($target, 'change_import_mode',
                                       __('Enable preview', 'ocsinventoryng'),
                                       array('id' => 'true'));
               }
               echo "</td></tr>";
               echo "<tr class='tab_bg_1'><td class='center b'>".
                     __('Check first that duplicates have been correctly managed in OCSNG',
                        'ocsinventoryng')."</td>";
               echo "</tr></table>";
               Html::closeForm();
               echo "</div>";
            }

            echo "<form method='post' name='ocsng_form' id='ocsng_form' action='$target'>";
            if (!$tolinked){
               self::checkBox($target);
            }
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr class='tab_bg_1'><td colspan='" . (($advanced || $tolinked) ? 10 : 7) . "' class='center'>";
            echo "<input class='submit' type='submit' name='import_ok' value=\"".
                   _sx('button', 'Import', 'ocsinventoryng')."\">";
            echo "</td></tr>\n";

            echo "<tr><th>".__('Name'). "</th>\n";
            echo "<th>".__('Manufacturer')."</th>\n";
            echo "<th>" .__('Model')."</th>\n";
            echo "<th>".__('Serial number')."</th>\n";
            echo "<th>" . __('Date')."</th>\n";
            echo "<th>".__('OCSNG TAG', 'ocsinventoryng')."</th>\n";
            if ($advanced && !$tolinked){
               echo "<th>" . __('Match the rule ?', 'ocsinventoryng') . "</th>\n";
               echo "<th>" . __('Destination entity') . "</th>\n";
               echo "<th>" . __('Target location', 'ocsinventoryng') . "</th>\n";
            }
            echo "<th>&nbsp;</th></tr>\n";

            $rule = new RuleImportEntityCollection();
            foreach ($hardware as $ID => $tab){
               $comp = new Computer();
               $comp->fields["id"] = $tab["id"];
               $data = array();

               if ($advanced && !$tolinked){
                  $data = $rule->processAllRules(array('ocsservers_id' => $plugin_ocsinventoryng_ocsservers_id,
                                                       '_source'       => 'ocsinventoryng'),
                                                 array(), array('ocsid' =>$tab["id"]));
               }
               echo "<tr class='tab_bg_2'><td>". $tab["name"] . "</td>\n";
               echo "<td>".$tab["manufacturer"]."</td><td>".$tab["model"]."</td>";
               echo "<td>".$tab["serial"]."</td>\n";
               echo "<td>" . Html::convDateTime($tab["date"]) . "</td>\n";
               echo "<td>" . $tab["TAG"] . "</td>\n";
               if ($advanced && !$tolinked){
                  if (!isset ($data['entities_id']) || $data['entities_id'] == -1){
                     echo "<td class='center'><img src=\"".$CFG_GLPI['root_doc']. "/pics/redbutton.png\"></td>\n";
                     $data['entities_id'] = -1;
                  } else{
                     echo "<td class='center'>";
                     $tmprule = new RuleImportEntity();
                     if ($tmprule->can($data['_ruleid'],'r')){
                        echo "<a href='". $tmprule->getLinkURL()."'>".$tmprule->getName()."</a>";
                     }  else{
                        echo $tmprule->getName();
                     }
                     echo "</td>\n";
                  }
                  echo "<td>";
                  Entity::dropdown(array('name'     => "toimport_entities[".$tab["id"]."]=".
                                                         $data['entities_id'],
                                          'value'    => $data['entities_id'],
                                         'comments' => 0));
                  echo "</td>\n";
                  echo "<td>";
                  if (!isset($data['locations_id'])){
                     $data['locations_id'] = 0;
                  }
                  Location::dropdown(array('name'     => "toimport_locations[".$tab["id"]."]=".
                                                           $data['locations_id'],
                                           'value'    => $data['locations_id'],
                                           'comments' => 0));
                  echo "</td>\n";
               }
               echo "<td>";
               if (!$tolinked){
                  echo "<input type='checkbox' name='toimport[" . $tab["id"] . "]' ".
                         ($check == "all" ? "checked" : "") . ">";
               } else{
                  $rulelink         = new RuleImportComputerCollection();
                  $rulelink_results = array();
                  $params           = array('entities_id' => $entity,
                                            'plugin_ocsinventoryng_ocsservers_id'
                                                          => $plugin_ocsinventoryng_ocsservers_id);
                  $rulelink_results = $rulelink->processAllRules(Toolbox::stripslashes_deep($tab),
                                                                 array(), $params);

                  //Look for the computer using automatic link criterias as defined in OCSNG configuration
                  $options       = array('name' => "tolink[".$tab["id"]."]");
                  $show_dropdown = true;
                  //If the computer is not explicitly refused by a rule
                  if (!isset($rulelink_results['action'])
                      || $rulelink_results['action'] != self::LINK_RESULT_NO_IMPORT){

                     if (!empty($rulelink_results['found_computers'])){
                        $options['value']  = $rulelink_results['found_computers'][0];
                        $options['entity'] = $entity;
                     }

                     Computer::dropdown($options);
                  } else{
                     echo "<img src='".$CFG_GLPI['root_doc']. "/pics/redbutton.png'>";
                  }
               }
               echo "</td></tr>\n";
            }

            echo "<tr class='tab_bg_1'><td colspan='" . (($advanced || $tolinked) ? 10 : 7) . "' class='center'>";
            echo "<input class='submit' type='submit' name='import_ok' value=\"".
                   _sx('button', 'Import', 'ocsinventoryng')."\">\n";
            echo "<input type=hidden name='plugin_ocsinventoryng_ocsservers_id' ".
                   "value='$plugin_ocsinventoryng_ocsservers_id'>";
            echo "</td></tr>";
            echo "</table>\n";
            Html::closeForm();

            if (!$tolinked){
               self::checkBox($target);
            }

            Html::printPager($start, $numrows, $target, $parameters);

         } else{
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr><th>" . __('Import new computers') . "</th></tr>\n";
            echo "<tr class='tab_bg_1'>";
            echo "<td class='center b'>".__('No new computer to be imported', 'ocsinventoryng').
                 "</td></tr>\n";
            echo "</table>";
         }
         echo "</div>";

      } else{
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>" .__('Import new computers', 'ocsinventoryng') . "</th></tr>\n";
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center b'>" .__('No new computer to be imported', 'ocsinventoryng').
              "</td></tr>\n";
         echo "</table></div>";
      }
   }


   static function migrateImportDevice($computers_id, $import_device){

      $new_import_device = array(self::IMPORT_TAG_078);
      if (count($import_device)){
         foreach ($import_device as $key=>$val){
            $tmp = explode(self::FIELD_SEPARATOR, $val);

            if (isset($tmp[1])) { // Except for old IMPORT_TAG
               $tmp2                     = explode(self::FIELD_SEPARATOR, $key);
               // Index Could be 1330395 (from glpi 0.72)
               // Index Could be 5$$$$$5$$$$$5$$$$$5$$$$$5$$$$$1330395 (glpi 0.78 bug)
               // So take the last part of the index
               $key2                     = $tmp[0].self::FIELD_SEPARATOR.array_pop($tmp2);
               $new_import_device[$key2] = $val;
            }

         }
      }
      //Add the new tag as the first occurence in the array
      //self::replaceOcsArray($computers_id, $new_import_device, "import_device");
      return $new_import_device;
   }


   static function migrateComputerUpdates($computers_id, $computer_update){

      $new_computer_update = array(self::IMPORT_TAG_078);

      $updates = array('ID'                  => 'id',
                       'FK_entities'         => 'entities_id',
                       'tech_num'            => 'users_id_tech',
                       'comments'            => 'comment',
                       'os'                  => 'operatingsystems_id',
                       'os_version'          => 'operatingsystemversions_id',
                       'os_sp'               => 'operatingsystemservicepacks_id',
                       'os_license_id'       => 'os_licenseid',
                       'auto_update'         => 'autoupdatesystems_id',
                       'location'            => 'locations_id',
                       'domain'              => 'domains_id',
                       'network'             => 'networks_id',
                       'model'               => 'computermodels_id',
                       'type'                => 'computertypes_id',
                       'tplname'             => 'template_name',
                       'FK_glpi_enterprise'  => 'manufacturers_id',
                       'deleted'             => 'is_deleted',
                       'notes'               => 'notepad',
                       'ocs_import'          => 'is_dynamic',
                       'FK_users'            => 'users_id',
                       'FK_groups'           => 'groups_id',
                       'state'               => 'states_id');

      if (count($computer_update)){
         foreach ($computer_update as $field){
            if (isset($updates[$field])){
               $new_computer_update[] = $updates[$field];
            } else{
               $new_computer_update[] = $field;
            }
         }
      }

      //Add the new tag as the first occurence in the array
      self::replaceOcsArray($computers_id, $new_computer_update, "computer_update");
      return $new_computer_update;
   }

/*
   static function unlockItems($computers_id, $field){
      global $DB;

      if (!in_array($field, array("import_disk", "import_ip", "import_monitor", "import_peripheral",
                                  "import_printer", "import_software"))){
         return false;
      }

      $query = "SELECT `$field`
                FROM `glpi_plugin_ocsinventoryng_ocslinks`
                WHERE `computers_id` = '$computers_id'";

      if ($result = $DB->query($query)){
         if ($DB->numrows($result)){
            $tab         = importArrayFromDB($DB->result($result, 0, 0));
            $update_done = false;

            foreach ($tab as $key => $val){
               if ($val != "_version_070_"){
                  switch ($field){
                     case "import_monitor":
                     case "import_printer":
                     case "import_peripheral":
                        $querySearchLocked = "SELECT `items_id`
                                              FROM `glpi_computers_items`
                                              WHERE `id` = '$key'";
                        break;

                     case "import_software":
                        $querySearchLocked = "SELECT `id`
                                              FROM `glpi_computers_softwareversions`
                                              WHERE `id` = '$key'";
                        break;

                     case "import_ip":
                        $querySearchLocked = "SELECT*
                                              FROM `glpi_networkports`
                                              LEFT JOIN `glpi_networknames`
                                              ON (`glpi_networkports`.`id` = `glpi_networknames`.`items_id`)
                                              LEFT JOIN `glpi_ipaddresses`
                                              ON (`glpi_ipaddresses`.`items_id` = `glpi_networknames`.`id`)
                                              WHERE `glpi_networkports`.`items_id` = '$computers_id'
                                                    AND `glpi_networkports`.`itemtype` = 'Computer'
                                                    AND `glpi_ipaddresses`.`name` = '$val'";
                        break;

                     case "import_disk":
                        $querySearchLocked = "SELECT `id`
                                              FROM `glpi_computerdisks`
                                              WHERE `id` = '$key'";
                        break;

                     default:
                        return;
                  }

                  $resultSearch = $DB->query($querySearchLocked);
                  if ($DB->numrows($resultSearch) == 0){
                     unset($tab[$key]);
                     $update_done = true;
                  }
               }
            }

            if ($update_done){
               $query = "UPDATE `glpi_plugin_ocsinventoryng_ocslinks`
                         SET `$field` = '" . exportArrayToDB($tab) . "'
                         WHERE `computers_id` = '$computers_id'";
               $DB->query($query);
            }
         }
      }
   }
*/

   /**
    * Import the devices for a computer
    *
    * @param $devicetype integer : device type
    * @param $computers_id integer : glpi computer id.
    * @param $ocsid integer : ocs computer id (ID).
    * @param $plugin_ocsinventoryng_ocsservers_id integer : ocs server id
    * @param $cfg_ocs array : ocs config
    * @param $import_device array : already imported devices
    * @param $import_ip array : already imported ip
    * @param $dohistory boolean : log changes?
    *
    * @return Nothing (void).
   **/
   static function updateDevices($devicetype, $computers_id, $ocsid, $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                 $import_device, $import_ip, $dohistory){
      global $PluginOcsinventoryngDBocs,$DB;

      $prevalue = $devicetype.self::FIELD_SEPARATOR;

      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);
      $do_clean   = false;

      switch ($devicetype){
         case "Item_DeviceMemory":
            $CompDevice = new $devicetype();
            //Memoire
            if ($cfg_ocs["import_device_memory"]) {
               $do_clean = true;
               $query2 = "SELECT*
                          FROM `memories`
                          WHERE `HARDWARE_ID` = '$ocsid'
                          ORDER BY `ID`";
               $result2 = $PluginOcsinventoryngDBocs->query($query2);
               if ($PluginOcsinventoryngDBocs->numrows($result2) > 0) {
                  // TODO a revoir
                  // pourquoi supprimer tous les importés ?
                  // En 0.83 cette suppression était lié à la présence du tag
                  // IMPORT_TAG_078, et donc exécuté 1 seule fois pour redressement
                  // Cela pete, je pense, tous les lock
                  //if (count($import_device)){
                  //   $dohistory = false;
                  //   foreach ($import_device as $key => $val) {
                  //      $tmp = explode(self::FIELD_SEPARATOR,$key);
                  //      if (isset($tmp[1]) && $tmp[0] == "Item_DeviceMemory") {
                  //         $CompDevice->delete(array('id'          => $tmp[1],
                  //                                   '_no_history' => true), 1);
                  //         unset($import_device[$key]);
                  //      }
                  //   }
                  //}
                  while ($line2 = $PluginOcsinventoryngDBocs->fetch_array($result2)){
                     $line2 = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line2));
                     if (isset($line2["CAPACITY"]) && $line2["CAPACITY"]!="No"){
                        $ram["designation"] = "";
                        if ($line2["TYPE"]!="Empty Slot" && $line2["TYPE"]!="Unknown"){
                           $ram["designation"] = $line2["TYPE"];
                        }
                        if ($line2["DESCRIPTION"]){
                           if (!empty($ram["designation"])){
                              $ram["designation"] .= " - ";
                           }
                           $ram["designation"] .= $line2["DESCRIPTION"];
                        }
                        if (!is_numeric($line2["CAPACITY"])){
                           $line2["CAPACITY"] = 0;
                        }
                        $ram["size_default"] = $line2["CAPACITY"];

                        if (!in_array(stripslashes($prevalue.$ram["designation"]), $import_device)){

                           $ram["frequence"]            = $line2["SPEED"];
                           $ram["devicememorytypes_id"] = Dropdown::importExternal('DeviceMemoryType',
                                                                                   $line2["TYPE"]);

                           $DeviceMemory = new DeviceMemory();
                           $ram_id = $DeviceMemory->import($ram);
                           if ($ram_id){
                              $devID = $CompDevice->add(array('items_id'           => $computers_id,
                                                              'itemtype'            => 'Computer',
                                                              'devicememories_id'   => $ram_id,
                                                              'size'                => $line2["CAPACITY"],
                                                              'is_dynamic'          => 1,
                                                              '_no_history'         => !$dohistory));
                           }
                        } else {
                           $tmp = array_search(stripslashes($prevalue . $ram["designation"]),
                                               $import_device);
                           list($type,$id) = explode(self::FIELD_SEPARATOR, $tmp);

                           $CompDevice->update(array('id'  => $id,
                                                     'size' => $line2["CAPACITY"]));
                           unset ($import_device[$tmp]);
                        }
                     }
                  }
               }
            }
            break;

         case "Item_DeviceHardDrive":
            $CompDevice = new $devicetype();
            //Disque Dur
            if ($cfg_ocs["import_device_hdd"]){
               $do_clean = true;
               $query2 = "SELECT*
                          FROM `storages`
                          WHERE `HARDWARE_ID` = '$ocsid'
                          ORDER BY `ID`";
               $result2 = $PluginOcsinventoryngDBocs->query($query2);
               if ($PluginOcsinventoryngDBocs->numrows($result2) > 0){
                  while ($line2 = $PluginOcsinventoryngDBocs->fetch_array($result2)){
                     $line2 = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line2));
                     if (!empty ($line2["DISKSIZE"]) && preg_match("/disk|spare\sdrive/i", $line2["TYPE"])){
                        if ($line2["NAME"]){
                           $dd["designation"] = $line2["NAME"];
                        } else{
                           if ($line2["MODEL"]){
                              $dd["designation"] = $line2["MODEL"];
                           } else{
                              $dd["designation"] = "Unknown";
                           }
                        }
                        if (!is_numeric($line2["DISKSIZE"])){
                           $line2["DISKSIZE"] = 0;
                        }
                        if (!in_array(stripslashes($prevalue.$dd["designation"]), $import_device)){
                           $dd["capacity_default"] = $line2["DISKSIZE"];
                           $DeviceHardDrive = new DeviceHardDrive();
                           $dd_id = $DeviceHardDrive->import($dd);
                           if ($dd_id){
                              $devID = $CompDevice->add(array('items_id'           => $computers_id,
                                                              'itemtype'            => 'Computer',
                                                              'deviceharddrives_id' => $dd_id,
                                                              'capacity'            => $line2["DISKSIZE"],
                                                              'is_dynamic'          => 1,
                                                              '_no_history'         => !$dohistory));
                           }
                        } else{
                           $tmp = array_search(stripslashes($prevalue . $dd["designation"]),
                                               $import_device);
                           list($type,$id) = explode(self::FIELD_SEPARATOR, $tmp);
                           $CompDevice->update(array('id'          => $id,
                                                     'capacity' => $line2["DISKSIZE"]));
                           unset ($import_device[$tmp]);
                        }
                     }
                  }
               }
            }
            break;

         case "Item_DeviceDrive":
            $CompDevice = new $devicetype();
            //lecteurs
            if ($cfg_ocs["import_device_drive"]){
               $do_clean = true;
               $query2 = "SELECT*
                          FROM `storages`
                          WHERE `HARDWARE_ID` = '$ocsid'
                          ORDER BY `ID`";
               $result2 = $PluginOcsinventoryngDBocs->query($query2);
               if ($PluginOcsinventoryngDBocs->numrows($result2) > 0){
                  while ($line2 = $PluginOcsinventoryngDBocs->fetch_array($result2)){
                     $line2 = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line2));
                     if (empty ($line2["DISKSIZE"]) || !preg_match("/disk/i", $line2["TYPE"])){
                        if ($line2["NAME"]){
                           $stor["designation"] = $line2["NAME"];
                        } else{
                           if ($line2["MODEL"]){
                              $stor["designation"] = $line2["MODEL"];
                           } else{
                              $stor["designation"] = "Unknown";
                           }
                        }
                        if (!in_array(stripslashes($prevalue.$stor["designation"]),
                                      $import_device)){
                           $DeviceDrive = new DeviceDrive();
                           $stor_id = $DeviceDrive->import($stor);
                           if ($stor_id){
                              $devID = $CompDevice->add(array('items_id'        => $computers_id,
                                                              'itemtype'         => 'Computer',
                                                              'devicedrives_id'  => $stor_id,
                                                              'is_dynamic'       => 1,
                                                              '_no_history'      => !$dohistory));
                           }
                        } else{
                           $tmp = array_search(stripslashes($prevalue.$stor["designation"]),
                                               $import_device);
                           unset ($import_device[$tmp]);
                        }
                     }
                  }
               }
            }
            break;

         case "Item_DevicePci":
            $CompDevice = new $devicetype();
            //Modems
            if ($cfg_ocs["import_device_modem"]) {
               $do_clean = true;
               $query2 = "SELECT*
                          FROM `modems`
                          WHERE `HARDWARE_ID` = '$ocsid'
                          ORDER BY `ID`";
               $result2 = $PluginOcsinventoryngDBocs->query($query2);
               if ($PluginOcsinventoryngDBocs->numrows($result2) > 0){
                  while ($line2 = $PluginOcsinventoryngDBocs->fetch_array($result2)){
                     $line2 = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line2));
                     $mdm["designation"] = $line2["NAME"];
                     if (!in_array(stripslashes($prevalue.$mdm["designation"]), $import_device)){
                        if (!empty ($line2["DESCRIPTION"])){
                           $mdm["comment"] = $line2["TYPE"] . "\r\n" . $line2["DESCRIPTION"];
                        }
                        $DevicePci = new DevicePci();
                        $mdm_id = $DevicePci->import($mdm);
                        if ($mdm_id){
                           $devID = $CompDevice->add(array('items_id'        => $computers_id,
                                                            'itemtype'        => 'Computer',
                                                            'devicepcis_id'   => $mdm_id,
                                                            'is_dynamic'      => 1,
                                                            '_no_history'     => !$dohistory));
                        }
                     } else{
                        $tmp = array_search(stripslashes($prevalue.$mdm["designation"]),
                                            $import_device);
                        unset ($import_device[$tmp]);
                     }
                  }
               }
            }
            //Ports
            if ($cfg_ocs["import_device_port"]){
               $query2 = "SELECT*
                          FROM `ports`
                          WHERE `HARDWARE_ID` = '$ocsid'
                          ORDER BY `ID`";
               $result2 = $PluginOcsinventoryngDBocs->query($query2);
               if ($PluginOcsinventoryngDBocs->numrows($result2) > 0){
                  while ($line2 = $PluginOcsinventoryngDBocs->fetch_array($result2)){
                     $line2 = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line2));
                     $port["designation"] = "";
                     if ($line2["TYPE"] != "Other") {
                        $port["designation"] .= $line2["TYPE"];
                     }
                     if ($line2["NAME"] != "Not Specified") {
                        $port["designation"] .= " " . $line2["NAME"];
                     } else if ($line2["CAPTION"] != "None") {
                        $port["designation"] .= " " . $line2["CAPTION"];
                     }
                     if (!empty ($port["designation"])) {
                        if (!in_array(stripslashes($prevalue.$port["designation"]),
                                      $import_device)){
                           if (!empty ($line2["DESCRIPTION"]) && $line2["DESCRIPTION"] != "None") {
                              $port["comment"] = $line2["DESCRIPTION"];
                           }
                           $DevicePci = new DevicePci();
                           $port_id   = $DevicePci->import($port);
                           if ($port_id) {
                           $devID = $CompDevice->add(array('items_id'      => $computers_id,
                                                           'itemtype'      => 'Computer',
                                                           'devicepcis_id' => $port_id,
                                                           'is_dynamic'    => 1,
                                                           '_no_history'   => !$dohistory));
                           }
                        } else {
                           $tmp = array_search(stripslashes($prevalue.$port["designation"]),
                                               $import_device);
                           unset ($import_device[$tmp]);
                        }
                     }
                  }
               }
            }
            break;

         case "Item_DeviceProcessor":
            $CompDevice = new $devicetype();
            //Processeurs:
            if ($cfg_ocs["import_device_processor"]){
               $do_clean = true;
               $query = "SELECT*
                         FROM `hardware`
                         WHERE `ID` = '$ocsid'
                         ORDER BY `ID`";
               $result = $PluginOcsinventoryngDBocs->query($query);
               if ($PluginOcsinventoryngDBocs->numrows($result) == 1){
                  $line = $PluginOcsinventoryngDBocs->fetch_array($result);
                  $line = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line));
                  for ($i=0 ; $i<$line["PROCESSORN"] ; $i++){
                     $processor = array();
                     $processor["designation"] = $line["PROCESSORT"];
                     if (!is_numeric($line["PROCESSORS"])){
                        $line["PROCESSORS"] = 0;
                     }
                     $processor["frequency_default"] = $line["PROCESSORS"];
                     $processor["frequence"] = $line["PROCESSORS"];
                     if (!in_array(stripslashes($prevalue.$processor["designation"]),
                                   $import_device)){
                        $DeviceProcessor = new DeviceProcessor();
                        $proc_id         = $DeviceProcessor->import($processor);
                        if ($proc_id){
                           $devID = $CompDevice->add(array('items_id'            => $computers_id,
                                                            'itemtype'            => 'Computer',
                                                            'deviceprocessors_id' => $proc_id,
                                                            'frequency'           => $line["PROCESSORS"],
                                                            'is_dynamic'          => 1,
                                                            '_no_history'         => !$dohistory));
                        }
                     } else {
                        $tmp = array_search(stripslashes($prevalue.$processor["designation"]),
                                            $import_device);
                        list($type,$id) = explode(self::FIELD_SEPARATOR,$tmp);
                        $CompDevice->update(array('id'          => $id,
                                                  'frequency' => $line["PROCESSORS"]));
                        unset ($import_device[$tmp]);
                     }
                  }
               }
            }
            break;

         case "Item_DeviceNetworkCard":
            //Carte reseau
            if ($cfg_ocs["import_device_iface"] || $cfg_ocs["import_ip"]){
               PluginOcsinventoryngNetworkPort::importNetwork($PluginOcsinventoryngDBocs, $cfg_ocs,
                                                              $ocsid, $computers_id, $dohistory);
            }
            break;

         case "Item_DeviceGraphicCard":
            $CompDevice = new $devicetype();
            //carte graphique
            if ($cfg_ocs["import_device_gfxcard"]){
               $do_clean = true;
               $query2 = "SELECT DISTINCT(`NAME`) AS NAME,
                                 `MEMORY`
                          FROM `videos`
                          WHERE `HARDWARE_ID` = '$ocsid'
                                AND `NAME` != ''
                          ORDER BY `ID`";
               $result2 = $PluginOcsinventoryngDBocs->query($query2);
               if ($PluginOcsinventoryngDBocs->numrows($result2) > 0){
                  while ($line2 = $PluginOcsinventoryngDBocs->fetch_array($result2)){
                     $line2 = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line2));
                     $video["designation"] = $line2["NAME"];
                     if (!is_numeric($line2["MEMORY"])){
                        $line2["MEMORY"] = 0;
                     }
                     if (!in_array(stripslashes($prevalue.$video["designation"]), $import_device)){
                        $video["memory_default"] = $line2["MEMORY"];
                        $DeviceGraphicCard = new DeviceGraphicCard();
                        $video_id = $DeviceGraphicCard->import($video);
                        if ($video_id){
                           $devID = $CompDevice->add(array('items_id'               => $computers_id,
                                                           'itemtype'               => 'Computer',
                                                           'devicegraphiccards_id'  => $video_id,
                                                           'memory'                 => $line2["MEMORY"],
                                                           'is_dynamic'             => 1,
                                                           '_no_history'            => !$dohistory));
                        }
                     } else{
                        $tmp = array_search(stripslashes($prevalue.$video["designation"]),
                                            $import_device);
                        list($type,$id) = explode(self::FIELD_SEPARATOR,$tmp);
                        $CompDevice->update(array('id'          => $id,
                                                  'memory' => $line2["MEMORY"]));
                        unset ($import_device[$tmp]);
                     }
                  }
               }
            }
            break;

         case "Item_DeviceSoundCard":
            $CompDevice = new $devicetype();
            //carte son
            if ($cfg_ocs["import_device_sound"]){
               $do_clean = true;
               $query2 = "SELECT DISTINCT(`NAME`) AS NAME,
                                 `DESCRIPTION`
                          FROM `sounds`
                          WHERE `HARDWARE_ID` = '$ocsid'
                                AND `NAME` != ''
                          ORDER BY `ID`";
               $result2 = $PluginOcsinventoryngDBocs->query($query2);
               if ($PluginOcsinventoryngDBocs->numrows($result2) > 0){
                  while ($line2 = $PluginOcsinventoryngDBocs->fetch_array($result2)){
                     $line2 = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line2));
                     if (!$cfg_ocs["ocs_db_utf8"] && !Toolbox::seems_utf8($line2["NAME"])){
                     $line2["NAME"] = Toolbox::encodeInUtf8($line2["NAME"]);
                     }
                     $snd["designation"] = $line2["NAME"];
                     if (!in_array(stripslashes($prevalue.$snd["designation"]), $import_device)){
                        if (!empty ($line2["DESCRIPTION"])){
                           $snd["comment"] = $line2["DESCRIPTION"];
                        }
                        $DeviceSoundCard = new DeviceSoundCard();
                        $snd_id          = $DeviceSoundCard->import($snd);
                        if ($snd_id){
                           $devID = $CompDevice->add(array('items_id'           => $computers_id,
                                                           'itemtype'            => 'Computer',
                                                           'devicesoundcards_id' => $snd_id,
                                                           'is_dynamic'          => 1,
                                                           '_no_history'         => !$dohistory));
                        }
                     } else{
                        $id = array_search(stripslashes($prevalue.$snd["designation"]),
                                           $import_device);
                        unset ($import_device[$id]);
                     }
                  }
               }
            }
            break;
      }

      // Delete Unexisting Items not found in OCS
      if ($do_clean && count($import_device)){
         foreach ($import_device as $key => $val){
            if (!(strpos($key, $devicetype . '$$') === false)){
               list($type,$id) = explode(self::FIELD_SEPARATOR, $key);
               $CompDevice->delete(array('id'          => $id,
                                         '_no_history' => !$dohistory, 1), true);
            }
         }
      }

      //TODO Import IP
      if ($do_clean
          && count($import_ip)
          && $devicetype == "Item_DeviceNetworkCard"){
         foreach ($import_ip as $key => $val){
            if ($key>0){
               $netport = new NetworkPort();
               $netport->delete(array('id' => $key));
            }
         }
      }
      //Alimentation
      //Carte mere
   }


   /**
    * Get a direct link to the computer in ocs console
    *
    * @param $plugin_ocsinventoryng_ocsservers_id the ID of the OCS server
    * @param $ocsid ID of the computer in OCS hardware table
    * @param $todisplay the link's label to display
    * @param $only_url
    *
    * @return the html link to the computer in ocs console
   **/
   static function getComputerLinkToOcsConsole ($plugin_ocsinventoryng_ocsservers_id, $ocsid, $todisplay, $only_url=false){

      $ocs_config = self::getConfig($plugin_ocsinventoryng_ocsservers_id);
      $url        = '';

      if ($ocs_config["ocs_url"] != ''){
         //Display direct link to the computer in ocsreports
         $url = $ocs_config["ocs_url"];
         if (!preg_match("/\/$/i",$ocs_config["ocs_url"])){
            $url .= '/';
         }
         if ($ocs_config['ocs_version'] > self::OCS2_VERSION_LIMIT){
            $url = $url."index.php?function=computer&amp;head=1&amp;systemid=$ocsid";
         } else{
            $url = $url."machine.php?systemid=$ocsid";
         }

         if ($only_url){
            return $url;
         }
         return "<a href='$url'>".$todisplay."</a>";
      }
      return $url;
   }

   /**
    * Get IP address from OCS hardware table
    *
    * @param plugin_ocsinventoryng_ocsservers_id the ID of the OCS server
    * @param computers_id ID of the computer in OCS hardware table
    *
    * @return the ip address or ''
   **/
   static function getGeneralIpAddress($plugin_ocsinventoryng_ocsservers_id, $computers_id){
      global $PluginOcsinventoryngDBocs;

      $res = $PluginOcsinventoryngDBocs->query("SELECT `IPADDR`
                            FROM `hardware`
                            WHERE `ID` = '$computers_id'");

      if ($PluginOcsinventoryngDBocs->numrows($res) == 1){
         return $PluginOcsinventoryngDBocs->result($res, 0, "IPADDR");
      }
      return '';
   }


   static function getDevicesManagementMode($ocs_config, $itemtype){

      switch ($itemtype){
         case 'Monitor':
            return $ocs_config["import_monitor"];

         case 'Printer':
            return $ocs_config["import_printer"];

         case 'Peripheral':
            return $ocs_config["import_periph"];
      }
   }


   static function setEntityLock($entity){

      $fp = fopen(GLPI_LOCK_DIR . "/lock_entity_" . $entity, "w+");
      if (flock($fp, LOCK_EX)){
         return $fp;
      }
      fclose($fp);
      return false;
   }


   static function removeEntityLock($entity, $fp){

      flock($fp, LOCK_UN);
      fclose($fp);

      //Test if the lock file still exists before removing it
      // (sometimes another thread already removed the file)
      clearstatcache();
      if (file_exists(GLPI_LOCK_DIR . "/lock_entity_" . $entity)){
         @unlink(GLPI_LOCK_DIR . "/lock_entity_" . $entity);
      }
   }


   static function getFormServerAction($ID, $templateid){

      $action = "";
      if (!isset($withtemplate) || $withtemplate == ""){
         $action = "edit_server";

      } else if (isset($withtemplate) && $withtemplate == 1){
         if ($ID == -1 && $templateid == ''){
            $action = "add_template";
         } else{
            $action = "update_template";
         }

      } else if (isset($withtemplate) && $withtemplate == 2){
         if ($templateid== ''){
            $action = "edit_server";
         } else if ($ID == -1){
            $action = "add_server_with_template";
         } else{
            $action = "update_server_with_template";
         }
      }

      return $action;
   }


   static function getColumnListFromAccountInfoTable($ID, $glpi_column){
      global $PluginOcsinventoryngDBocs, $DB;

      $listColumn = "";
      if ($ID != -1){
         self::checkOCSconnection($ID);
         if (!$PluginOcsinventoryngDBocs->error){
            $result = $PluginOcsinventoryngDBocs->query("SHOW COLUMNS
                                     FROM `accountinfo`");

            if ($PluginOcsinventoryngDBocs->numrows($result) > 0){
               while ($data = $PluginOcsinventoryngDBocs->fetch_array($result)){
                  //get the selected value in glpi if specified
                  $query = "SELECT `ocs_column`
                            FROM `glpi_plugin_ocsinventoryng_ocsadmininfoslinks`
                            WHERE `plugin_ocsinventoryng_ocsservers_id` = '$ID'
                                  AND `glpi_column` = '$glpi_column'";
                  $result_DB = $DB->query($query);
                  $selected = "";

                  if ($DB->numrows($result_DB) > 0){
                     $data_DB = $DB->fetch_array($result_DB);
                     $selected = $data_DB["ocs_column"];
                  }

                  $ocs_column = $data['Field'];
                  if (!strcmp($ocs_column, $selected)){
                     $listColumn .= "<option value='$ocs_column' selected>".$ocs_column."</option>";
                  } else{
                     $listColumn .= "<option value='$ocs_column'>" . $ocs_column . "</option>";
                  }
               }
            }
         }
      }
      return $listColumn;
   }


   /**
    * Check if OCS connection is always valid
    * If not, then establish a new connection on the good server
    *
    * @param $plugin_ocsinventoryng_ocsservers_id the ocs server id
    *
    * @return nothing.
   **/
   static function checkOCSconnection($plugin_ocsinventoryng_ocsservers_id){
      global $PluginOcsinventoryngDBocs;

      //If $PluginOcsinventoryngDBocs is not initialized, or if the connection should be on a different ocs server
      // --> reinitialize connection to OCS server
      if (!$PluginOcsinventoryngDBocs || $plugin_ocsinventoryng_ocsservers_id != $PluginOcsinventoryngDBocs->getServerID()){
         $PluginOcsinventoryngDBocs = self::getDBocs($plugin_ocsinventoryng_ocsservers_id);
      }
      return $PluginOcsinventoryngDBocs->connected;
   }


   /**
    * Get a connection to the OCS server
    *
    * @param $plugin_ocsinventoryng_ocsservers_id the ocs server id
    *
    * @return the connexion to the ocs database
   **/
   static function getDBocs($plugin_ocsinventoryng_ocsservers_id){
      return new PluginOcsinventoryngDBocs($plugin_ocsinventoryng_ocsservers_id);
   }


   /**
    * Choose an ocs server
    *
    * @return nothing.
   **/
   static function showFormServerChoice(){
      global $DB, $CFG_GLPI;

      $query = "SELECT*
                FROM `glpi_plugin_ocsinventoryng_ocsservers`
                WHERE `is_active`='1'
                ORDER BY `name` ASC";
      $result = $DB->query($query);

      if ($DB->numrows($result) > 1){
         echo "<form action=\"".$CFG_GLPI['root_doc']."/plugins/ocsinventoryng/front/ocsng.php\" method='post'>";
         echo "<div class='center'><table class='tab_cadre'>";
         echo "<tr class='tab_bg_2'>";
         echo "<th colspan='2'>".__('Choice of an OCSNG server', 'ocsinventoryng')."</th></tr>\n";

         echo "<tr class='tab_bg_2'><td class='center'>" .  __('Name'). "</td>";
         echo "<td class='center'>";
         echo "<select name='plugin_ocsinventoryng_ocsservers_id'>";
         while ($ocs = $DB->fetch_array($result)){
            echo "<option value='" . $ocs["id"] . "'>" . $ocs["name"] . "</option>";
         }
         echo "</select></td></tr>\n";

         echo "<tr class='tab_bg_2'><td class='center' colspan=2>";
         echo "<input class='submit' type='submit' name='ocs_showservers' value=\"".
                _sx('button','Post')."\"></td></tr>";
         echo "</table></div>\n";
         Html::closeForm();

      } else if ($DB->numrows($result) == 1){
         $ocs = $DB->fetch_array($result);
         Html::redirect($CFG_GLPI['root_doc']."/plugins/ocsinventoryng/front/ocsng.php?plugin_ocsinventoryng_ocsservers_id=" . $ocs["id"]);

      } else{
         echo "<div class='center'><table class='tab_cadre'>";
         echo "<tr class='tab_bg_2'>";
         echo "<th colspan='2'>".__('Choice of an OCSNG server', 'ocsinventoryng')."</th></tr>\n";

         echo "<tr class='tab_bg_2'>";
         echo "<td class='center' colspan=2>".__('No OCSNG server defined', 'ocsinventoryng').
              "</td></tr>";
         echo "</table></div>\n";
      }
   }


   /**
    * Delete old dropdown value
    *
    * Delete all old dropdown value of a computer.
    *
    * @param $glpi_computers_id integer : glpi computer id.
    * @param $field string : string of the computer table
    * @param $table string : dropdown table name
    *
    * @return nothing.
   **/
   static function resetDropdown($glpi_computers_id, $field, $table){
      global $DB;

      $query = "SELECT `$field` AS val
                FROM `glpi_computers`
                WHERE `id` = '$glpi_computers_id'";
      $result = $DB->query($query);

      if ($DB->numrows($result) == 1){
         $value = $DB->result($result, 0, "val");
         $query = "SELECT COUNT(*) AS cpt
                   FROM `glpi_computers`
                   WHERE `$field` = '$value'";
         $result = $DB->query($query);

         if ($DB->result($result, 0, "cpt") == 1){
            $query2 = "DELETE
                       FROM `$table`
                       WHERE `id` = '$value'";
            $DB->query($query2);
         }
      }
   }


   /**
    * Delete old registry entries
    *
    * @param $glpi_computers_id integer : glpi computer id.
    *
    * @return nothing.
   **/
   static function resetRegistry($glpi_computers_id){
      global $DB;

      $query = "SELECT*
                FROM `glpi_plugin_ocsinventoryng_registrykeys`
                WHERE `computers_id` = '$glpi_computers_id'";
      $result = $DB->query($query);

      if ($DB->numrows($result) > 0){
         while ($data = $DB->fetch_assoc($result)){
            $query2 = "SELECT COUNT(*)
                       FROM `glpi_plugin_ocsinventoryng_registrykeys`
                       WHERE `computers_id` = '" . $data['computers_id'] . "'";
            $result2 = $DB->query($query2);

            $registry = new PluginOcsinventoryngRegistryKey();
            if ($DB->result($result2, 0, 0) == 1){
               $registry->delete(array('id' => $data['computers_id']), 1);
            }
         }
      }
   }


   /**
    * Delete all old printers of a computer.
    *
    * @param $glpi_computers_id integer : glpi computer id.
    *
    * @return nothing.
   **/
   static function resetPrinters($glpi_computers_id){
      global $DB;

      $query = "SELECT*
                FROM `glpi_computers_items`
                WHERE `computers_id` = '$glpi_computers_id'
                      AND `itemtype` = 'Printer'";
      $result = $DB->query($query);

      if ($DB->numrows($result) > 0){
         $conn = new Computer_Item();

         while ($data = $DB->fetch_assoc($result)){
            $conn->delete(array('id' => $data['id']));

            $query2 = "SELECT COUNT(*)
                       FROM `glpi_computers_items`
                       WHERE `items_id` = '" . $data['items_id'] . "'
                             AND `itemtype` = 'Printer'";
            $result2 = $DB->query($query2);

            $printer = new Printer();
            if ($DB->result($result2, 0, 0) == 1){
               $printer->delete(array('id' => $data['items_id']), 1);
            }
         }
      }
   }


   /**
    * Delete all old monitors of a computer.
    *
    * @param $glpi_computers_id integer : glpi computer id.
    *
    * @return nothing.
   **/
   static function resetMonitors($glpi_computers_id){
      global $DB;

      $query = "SELECT*
                FROM `glpi_computers_items`
                WHERE `computers_id` = '$glpi_computers_id'
                      AND `itemtype` = 'Monitor'";
      $result = $DB->query($query);

      $mon = new Monitor();
      if ($DB->numrows($result) > 0){
         $conn = new Computer_Item();

         while ($data = $DB->fetch_assoc($result)){
            $conn->delete(array('id' => $data['id']));

            $query2 = "SELECT COUNT(*)
                       FROM `glpi_computers_items`
                       WHERE `items_id` = '" . $data['items_id'] . "'
                             AND `itemtype` = 'Monitor'";
            $result2 = $DB->query($query2);

            if ($DB->result($result2, 0, 0) == 1){
               $mon->delete(array('id' => $data['items_id']), 1);
            }
         }
      }
   }


   /**
    * Delete all old periphs for a computer.
    *
    * @param $glpi_computers_id integer : glpi computer id.
    *
    * @return nothing.
   **/
   static function resetPeripherals($glpi_computers_id){
      global $DB;

      $query = "SELECT*
                FROM `glpi_computers_items`
                WHERE `computers_id` = '$glpi_computers_id'
                      AND `itemtype` = 'Peripheral'";
      $result = $DB->query($query);

      $per = new Peripheral();
      if ($DB->numrows($result) > 0){
         $conn = new Computer_Item();
         while ($data = $DB->fetch_assoc($result)){
            $conn->delete(array('id' => $data['id']));

            $query2 = "SELECT COUNT(*)
                       FROM `glpi_computers_items`
                       WHERE `items_id` = '" . $data['items_id'] . "'
                             AND `itemtype` = 'Peripheral'";
            $result2 = $DB->query($query2);

            if ($DB->result($result2, 0, 0) == 1){
               $per->delete(array('id' => $data['items_id']), 1);
            }
         }
      }
   }


   /**
    * Delete all old softwares of a computer.
    *
    * @param $glpi_computers_id integer : glpi computer id.
    *
    * @return nothing.
   **/
   static function resetSoftwares($glpi_computers_id){
      global $DB;

      $query = "SELECT*
                FROM `glpi_computers_softwareversions`
                WHERE `computers_id` = '$glpi_computers_id'";
      $result = $DB->query($query);

      if ($DB->numrows($result) > 0){
         while ($data = $DB->fetch_assoc($result)){
            $query2 = "SELECT COUNT(*)
                       FROM `glpi_computers_softwareversions`
                       WHERE `softwareversions_id` = '" . $data['softwareversions_id'] . "'";
            $result2 = $DB->query($query2);

            if ($DB->result($result2, 0, 0) == 1){
               $vers = new SoftwareVersion();
               $vers->getFromDB($data['softwareversions_id']);
               $query3 = "SELECT COUNT(*)
                          FROM `glpi_softwareversions`
                          WHERE `softwares_id`='" . $vers->fields['softwares_id'] . "'";
               $result3 = $DB->query($query3);

               if ($DB->result($result3, 0, 0) == 1){
                  $soft = new Software();
                  $soft->delete(array('id' => $vers->fields['softwares_id']), 1);
               }
               $vers->delete(array("id" => $data['softwareversions_id']));
            }
         }

         $query = "DELETE
                   FROM `glpi_computers_softwareversions`
                   WHERE `computers_id` = '$glpi_computers_id'";
         $DB->query($query);
      }
   }


   /**
    * Delete all old disks of a computer.
    *
    * @param $glpi_computers_id integer : glpi computer id.
    *
    * @return nothing.
   **/
   static function resetDisks($glpi_computers_id){
      global $DB;

      $query = "DELETE
                FROM `glpi_computerdisks`
                WHERE `computers_id` = '$glpi_computers_id'";
      $DB->query($query);
   }


   /**
    * Import config of a new version
    *
    * This function create a new software in GLPI with some general datas.
    *
    * @param $software : id of a software.
    * @param $version : version of the software
    *
    * @return integer : inserted version id.
   **/
   static function importVersion($software, $version){
      global $DB;

      $isNewVers = 0;
      $query = "SELECT `id`
                FROM `glpi_softwareversions`
                WHERE `softwares_id` = '$software'
                      AND `name` = '$version'";
      $result = $DB->query($query);

      if ($DB->numrows($result) > 0){
         $data = $DB->fetch_array($result);
         $isNewVers = $data["id"];
      }

      if (!$isNewVers){
         $vers = new SoftwareVersion();
         // TODO : define a default state ? Need a new option in config
         // Use $cfg_ocs["states_id_default"] or create a specific one?
         $input["softwares_id"] = $software;
         $input["name"]         = $version;
         $isNewVers             = $vers->add($input);
      }

      return ($isNewVers);
   }


   /**
    *
    * Synchronize virtual machines
    *
    * @param unknown $computers_id
    * @param unknown $ocsid
    * @param unknown $ocsservers_id
    * @param unknown $cfg_ocs
    * @param unknown $dohistory
    * @return boolean
    */
   static function updateVirtualMachines($computers_id, $ocsid, $ocsservers_id,
                                           $cfg_ocs, $dohistory){
      global $PluginOcsinventoryngDBocs, $DB;

      // No VM before OCS 1.3
      if ($cfg_ocs['ocs_version'] < self::OCS1_3_VERSION_LIMIT){
         return false;
      }
      self::checkOCSconnection($ocsservers_id);
      $already_processed = array();

      //Get vms for this host
      $query = "SELECT*
                FROM `virtualmachines`
                WHERE `HARDWARE_ID` = '$ocsid'";
      $result = $PluginOcsinventoryngDBocs->query($query);

      $virtualmachine = new ComputerVirtualMachine();
      if ($PluginOcsinventoryngDBocs->numrows($result) > 0){
         while ($line = $PluginOcsinventoryngDBocs->fetch_assoc($result)){
            $line = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line));
            $vm                  = array();
            $vm['name']          = $line['NAME'];
            $vm['vcpu']          = $line['VCPU'];
            $vm['ram']           = $line['MEMORY'];
            $vm['uuid']          = $line['UUID'];
            $vm['computers_id']  = $computers_id;
            $vm['is_dynamic']    = 1;

            $vm['virtualmachinestates_id']  = Dropdown::importExternal('VirtualMachineState',
                                                                       $line['STATUS']);
            $vm['virtualmachinetypes_id']   = Dropdown::importExternal('VirtualMachineType',
                                                                       $line['VMTYPE']);
            $vm['virtualmachinesystems_id'] = Dropdown::importExternal('VirtualMachineType',
                                                                       $line['SUBSYSTEM']);

            $query = "SELECT `id`
                      FROM `glpi_computervirtualmachines`
                      WHERE `computers_id`='$computers_id'
                         AND `is_dynamic`";
            if ($line['UUID']) {
               $query .= " AND `uuid`='".$line['UUID']."'";
            } else {
               // Failback on name
               $query .= " AND `name`='".$line['NAME']."'";
            }

            $results = $DB->query($query);
            if ($DB->numrows($results) > 0){
               $id = $DB->result($results, 0, 'id');
            } else {
               $id = 0;
            }
            if (!$id){
               $virtualmachine->reset();
               if (!$dohistory){
                  $vm['_no_history'] = true;
               }
               $id_vm = $virtualmachine->add($vm);
               if ($id_vm){
                  $already_processed[] = $id_vm;
               }
            } else{
               if ($virtualmachine->getFromDB($id)){
                   $vm['id'] = $id;
                   $virtualmachine->update($vm);
               }
               $already_processed[] = $id;
            }
         }
      }

      // Delete Unexisting Items not found in OCS
      //Look for all ununsed virtual machines
      $query = "SELECT `id`
                FROM `glpi_computervirtualmachines`
                WHERE `computers_id`='$computers_id'
                   AND `is_dynamic`";
      if (!empty($already_processed)){
         $query .= "AND `id` NOT IN (".implode(',', $already_processed).")";
      }
      foreach ($DB->request($query) as $data){
       //Delete all connexions
          $virtualmachine->delete(array('id'             => $data['id'],
                                         '_ocsservers_id' => $ocsservers_id), true);
      }
   }


   /**
    * Update config of a new software
    *
    * This function create a new software in GLPI with some general datas.
    *
    * @param $computers_id integer : glpi computer id.
    * @param $ocsid integer : ocs computer id (ID).
    * @param $ocsservers_id integer : ocs server id
    * @param $cfg_ocs array : ocs config
    * @param $dohistory array:
    *
    *@return Nothing (void).
   **/
   static function updateDisk($computers_id, $ocsid, $ocsservers_id,
                                $cfg_ocs, $dohistory){
      global $PluginOcsinventoryngDBocs, $DB;

      $already_processed = array();
      self::checkOCSconnection($ocsservers_id);
      $query = "SELECT*
                FROM `drives`
                WHERE `HARDWARE_ID` = '$ocsid'";
      $result = $PluginOcsinventoryngDBocs->query($query);

      $d = new ComputerDisk();
      if ($PluginOcsinventoryngDBocs->numrows($result) > 0){
         while ($line = $PluginOcsinventoryngDBocs->fetch_array($result)){
            $line = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line));

            // Only not empty disk
            if ($line['TOTAL']>0){
               $disk                 = array();
               $disk['computers_id'] = $computers_id;
               $disk['is_dynamic']   = 1;

               // TYPE : vxfs / ufs  : VOLUMN = mount / FILESYSTEM = device
               if (in_array($line['TYPE'], array("vxfs", "ufs")) ){
                  $disk['name']           = $line['VOLUMN'];
                  $disk['mountpoint']     = $line['VOLUMN'];
                  $disk['device']         = $line['FILESYSTEM'];
                  $disk['filesystems_id'] = Dropdown::importExternal('Filesystem', $line["TYPE"]);

               } else if (in_array($line['FILESYSTEM'], array('ext2', 'ext3', 'ext4', 'ffs',
                                                              'fuseblk', 'fusefs', 'hfs', 'jfs',
                                                              'jfs2', 'Journaled HFS+', 'nfs',
                                                              'smbfs', 'reiserfs', 'vmfs', 'VxFS',
                                                              'ufs', 'xfs', 'zfs'))){
                  // Try to detect mount point : OCS database is dirty
                  $disk['mountpoint'] = $line['VOLUMN'];
                  $disk['device']     = $line['TYPE'];

                  // Found /dev in VOLUMN : invert datas
                  if (strstr($line['VOLUMN'],'/dev/')){
                     $disk['mountpoint'] = $line['TYPE'];
                     $disk['device']     = $line['VOLUMN'];
                  }

                  if ($line['FILESYSTEM'] == "vmfs"){
                     $disk['name'] = basename($line['TYPE']);
                  } else{
                     $disk['name']  = $disk['mountpoint'];
                  }
                  $disk['filesystems_id'] = Dropdown::importExternal('Filesystem',
                                                                     $line["FILESYSTEM"]);

               } else if (in_array($line['FILESYSTEM'], array('FAT', 'FAT32', 'NTFS'))){
                  if (!empty($line['VOLUMN'])){
                     $disk['name'] = $line['VOLUMN'];
                  } else{
                     $disk['name'] = $line['LETTER'];
                  }
                  $disk['mountpoint']     = $line['LETTER'];
                  $disk['filesystems_id'] = Dropdown::importExternal('Filesystem',
                                                                     $line["FILESYSTEM"]);
               }

               // Ok import disk
               if (isset($disk['name']) && !empty($disk["name"])){
                  $disk['totalsize'] = $line['TOTAL'];
                  $disk['freesize']  = $line['FREE'];

                  $query = "SELECT `id`
                            FROM `glpi_computerdisks`
                            WHERE `computers_id`='$computers_id'
                               AND `name`='".$disk['name']."'
                               AND `is_dynamic`";
                  $results = $DB->query($query);
                  if ($DB->numrows($results) == 1){
                     $id = $DB->result($results, 0, 'id');
                  } else {
                     $id = false;
                  }

                  if (!$id){
                     $d->reset();
                     if (!$dohistory){
                        $disk['_no_history'] = true;
                     }
                     $disk['is_dynamic'] = 1;
                     $id_disk = $d->add($disk);
                     $already_processed[] = $id_disk;
                  } else{
                     // Only update if needed
                     if ($d->getFromDB($id)){

                        // Update on type, total size change or variation of 5%
                        if ($d->fields['totalsize']!=$disk['totalsize']
                            || ($d->fields['filesystems_id'] != $disk['filesystems_id'])
                            || ((abs($disk['freesize']-$d->fields['freesize'])
                                 /$disk['totalsize']) > 0.05)){

                           $toupdate['id']              = $id;
                           $toupdate['totalsize']       = $disk['totalsize'];
                           $toupdate['freesize']        = $disk['freesize'];
                           $toupdate['filesystems_id']  = $disk['filesystems_id'];
                           $d->update($toupdate);
                        }
                        $already_processed[] = $id;
                     }
                  }
               }
            }
         }
      }
      // Delete Unexisting Items not found in OCS
      //Look for all ununsed disks
      $query = "SELECT `id`
                FROM `glpi_computerdisks`
                WHERE `computers_id`='$computers_id'
                   AND `is_dynamic`";
      if (!empty($already_processed)){
         $query .= "AND `id` NOT IN (".implode(',', $already_processed).")";
      }
      foreach ($DB->request($query) as $data){
       //Delete all connexions
          $d->delete(array('id'             => $data['id'],
                           '_ocsservers_id' => $ocsservers_id), true);
      }
   }


   /**
    * Install a software on a computer - check if not already installed
    *
    * @param $computers_id ID of the computer where to install a software
    * @param $softwareversions_id ID of the version to install
    * @param $dohistory Do history?
    *
    * @return nothing
   **/
   static function installSoftwareVersion($computers_id, $softwareversions_id, $dohistory=1){
      global $DB;

      if (!empty ($softwareversions_id) && $softwareversions_id > 0) {
         $query_exists = "SELECT `id`
                          FROM `glpi_computers_softwareversions`
                          WHERE (`computers_id` = '$computers_id'
                                 AND `softwareversions_id` = '$softwareversions_id')";
         $result = $DB->query($query_exists);

         if ($DB->numrows($result) > 0) {
            return $DB->result($result, 0, "id");
         }

         $tmp = new Computer_SoftwareVersion();
         return $tmp->add(array('computers_id'        => $computers_id,
                                 'softwareversions_id' => $softwareversions_id,
                                 '_no_history'         => !$dohistory,
                                 'is_dynamic'          => 1,
                                 'is_deleted'          => 0));
      }
      return 0;
   }


   /**
    * Update config of a new software
    *
    * This function create a new software in GLPI with some general data.
    *
    * @param $computers_id                         integer : glpi computer id.
    * @param $entity                               integer : entity of the computer
    * @param $ocsid                                integer : ocs computer id (ID).
    * @param $plugin_ocsinventoryng_ocsservers_id  integer : ocs server id
    * @param $cfg_ocs                              array   : ocs config
    * @param $import_software                      array   : already imported softwares
    * @param $dohistory                            boolean : log changes?
    *
    * @return Nothing (void).
   **/
   static function updateSoftware($computers_id, $entity, $ocsid,
                                  $plugin_ocsinventoryng_ocsservers_id, array $cfg_ocs, $dohistory){
      global $DB, $PluginOcsinventoryngDBocs;

      $alread_processed         = array();
      $is_utf8                  = $cfg_ocs["ocs_db_utf8"];
      $computer_softwareversion = new Computer_SoftwareVersion();

      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);
      if ($cfg_ocs["import_software"]) {

         //---- Get all the softwares for this machine from OCS -----//
         if ($cfg_ocs["use_soft_dict"]) {
            $query2 = "SELECT `dico_soft`.`FORMATTED` AS NAME,
                              `softwares`.`VERSION` AS VERSION,
                              `softwares`.`PUBLISHER` AS PUBLISHER,
                              `softwares`.`COMMENTS` AS COMMENTS
                       FROM `softwares`
                       INNER JOIN `dico_soft` ON (`softwares`.`NAME` = dico_soft.EXTRACTED)
                       WHERE `softwares`.`HARDWARE_ID` = '$ocsid'";
         } else {
            $query2 = "SELECT `softwares`.`NAME` AS NAME,
                              `softwares`.`VERSION` AS VERSION,
                              `softwares`.`PUBLISHER` AS PUBLISHER,
                              `softwares`.`COMMENTS` AS COMMENTS
                       FROM `softwares`
                       WHERE `softwares`.`HARDWARE_ID` = '$ocsid'";
         }
         $result2 = $PluginOcsinventoryngDBocs->query($query2);

         $soft                = new Software();

         // Read imported software in last sync
         $query = "SELECT `glpi_computers_softwareversions`.`id` as id,
                          `glpi_softwares`.`name` as sname,
                          `glpi_softwareversions`.`name` as vname
                   FROM `glpi_computers_softwareversions`
                   INNER JOIN `glpi_softwareversions`
                           ON `glpi_softwareversions`.`id`= `glpi_computers_softwareversions`.`softwareversions_id`
                   INNER JOIN `glpi_softwares`
                           ON `glpi_softwares`.`id`= `glpi_softwareversions`.`softwares_id`
                   WHERE `glpi_computers_softwareversions`.`computers_id`='$computers_id'
                         AND `is_dynamic`";
         $imported = array();
         foreach ($DB->request($query) as $data) {
            $imported[$data['id']] = strtolower($data['sname'].self::FIELD_SEPARATOR.$data['vname']);
         }

         if ($PluginOcsinventoryngDBocs->numrows($result2) > 0) {
            while ($data2 = $PluginOcsinventoryngDBocs->fetch_array($result2)) {
               $data2    = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($data2));

               //As we cannot be sure that data coming from OCS are in utf8, let's try to encode them
               //if possible
               foreach (array('NAME', 'PUBLISHER', 'VERSION') as $field) {
                  $data2[$field] = self::encodeOcsDataInUtf8($is_utf8, $data2[$field]);
               }

               //Replay dictionnary on manufacturer
               $manufacturer = Manufacturer::processName($data2["PUBLISHER"]);
               $version      = $data2['VERSION'];
               $name         = $data2['NAME'];

               //Software might be created in another entity, depending on the entity's configuration
               $target_entity = Entity::getUsedConfig('entities_id_software', $entity, '', true);
               //Do not change software's entity except if the dictionnary explicity changes it
               if ($target_entity < 0) {
                  $target_entity = $entity;
               }

               $modified_name       = $name;
               $modified_version    = $version;
               $is_helpdesk_visible = NULL;

               if (!$cfg_ocs["use_soft_dict"]) {
                  //Software dictionnary
                  $params = array("name" => $name, "manufacturer" => $manufacturer,
                                  "old_version"  => $version, "entities_id"  => $entity);
                  $rulecollection = new RuleDictionnarySoftwareCollection();
                  $res_rule
                     = $rulecollection->processAllRules(Toolbox::stripslashes_deep($params),
                                                        array(),
                                                        Toolbox::stripslashes_deep(array('version' => $version)));

                  if (isset($res_rule["name"]) && $res_rule["name"]) {
                     $modified_name = $res_rule["name"];
                  }

                  if (isset($res_rule["version"]) && $res_rule["version"]) {
                     $modified_version = $res_rule["version"];
                  }

                  if (isset($res_rule["is_helpdesk_visible"])
                      && strlen($res_rule["is_helpdesk_visible"])) {

                     $is_helpdesk_visible = $res_rule["is_helpdesk_visible"];
                  }

                  if (isset($res_rule['manufacturer']) && $res_rule['manufacturer']) {
                     $manufacturer = Dropdown::getDropdownName('glpi_manufacturers',
                                                               $res_rule['manufacturer']);
                     $manufacturer = Toolbox::addslashes_deep($manufacturer);
                  }

                  //If software dictionnary returns an entity, it overrides the one that may have
                  //been defined in the entity's configuration
                  if (isset($res_rule["new_entities_id"])
                        && strlen($res_rule["new_entities_id"])) {
                     $target_entity = $res_rule["new_entities_id"];
                  }
               }

               //If software must be imported
               if (!isset($res_rule["_ignore_import"]) || !$res_rule["_ignore_import"]) {
                  // Clean software object
                  $soft->reset();

                  // EXPLANATION About dictionnaries
                  // OCS dictionnary : if software name change, as we don't store INITNAME
                  //     GLPI will detect an uninstall (oldname) + install (newname)
                  // GLPI dictionnary : is rule have change
                  //     if rule have been replayed, modifiedname will be found => ok
                  //     if not, GLPI will detect an uninstall (oldname) + install (newname)

                  $id = array_search(strtolower(stripslashes($modified_name.self::FIELD_SEPARATOR.$version)),
                                     $imported);

                  if ($id) {
                     //-------------------------------------------------------------------------//
                     //---- The software exists in this version for this computer --------------//
                     //---------------------------------------------------- --------------------//
                     unset($imported[$id]);
                  } else {
                     //------------------------------------------------------------------------//
                     //---- The software doesn't exists in this version for this computer -----//
                     //------------------------------------------------------------------------//
                     $isNewSoft = $soft->addOrRestoreFromTrash($modified_name, $manufacturer,
                                                               $target_entity, '',
                                                               ($entity != $target_entity),
                                                               $is_helpdesk_visible);
                     //Import version for this software
                     $versionID = self::importVersion($isNewSoft, $modified_version);
                     //Install license for this machine
                     $instID = self::installSoftwareVersion($computers_id, $versionID, $dohistory);
                  }
               }
            }
         }

         foreach ($imported as $id => $unused) {
            $computer_softwareversion->delete(array('id' => $id, '_no_history' => !$dohistory),
                                              true);
            // delete cause a getFromDB, so fields contains values
            $verid = $computer_softwareversion->getField('softwareversions_id');

            if (countElementsInTable('glpi_computers_softwareversions',
                  "softwareversions_id = '$verid'") ==0
                  && countElementsInTable('glpi_softwarelicenses',
                        "softwareversions_id_buy = '$verid'") == 0) {

               $vers = new SoftwareVersion();
               if ($vers->getFromDB($verid)
                     && countElementsInTable('glpi_softwarelicenses',
                           "softwares_id = '".$vers->fields['softwares_id']."'") ==0
                    && countElementsInTable('glpi_softwareversions',
                           "softwares_id = '".$vers->fields['softwares_id']."'") == 1) {
                          // 1 is the current to be removed
                  $soft->putInTrash($vers->fields['softwares_id'],
                     __('Software deleted by OCSNG synchronization'));
               }
               $vers->delete(array("id" => $verid));
            }
         }
      }
   }


   /**
    * Update config of the registry
    *
    * This function erase old data and import the new ones about registry (Microsoft OS after Windows 95)
    *
    * @param $computers_id integer : glpi computer id.
    * @param $ocsid integer : ocs computer id (ID).
    * @param $plugin_ocsinventoryng_ocsservers_id integer : ocs server id
    * @param $cfg_ocs array : ocs config
    *
    * @return Nothing (void).
   **/
   static function updateRegistry($computers_id, $ocsid, $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs){
      global $DB, $PluginOcsinventoryngDBocs;

      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);
      if ($cfg_ocs["import_registry"]){
         //before update, delete all entries about $computers_id
         $query_delete = "DELETE
                          FROM `glpi_plugin_ocsinventoryng_registrykeys`
                          WHERE `computers_id` = '$computers_id'";
         $DB->query($query_delete);

         //Get data from OCS database
         $query = "SELECT `registry`.`NAME` AS name,
                          `registry`.`REGVALUE` AS regvalue,
                          `registry`.`HARDWARE_ID` AS computers_id,
                          `regconfig`.`REGTREE` AS regtree,
                          `regconfig`.`REGKEY` AS regkey
                   FROM `registry`
                   LEFT JOIN `regconfig` ON (`registry`.`NAME` = `regconfig`.`NAME`)
                   WHERE `HARDWARE_ID` = '$ocsid'";
         $result = $PluginOcsinventoryngDBocs->query($query);

         if ($PluginOcsinventoryngDBocs->numrows($result) > 0){
            $reg = new PluginOcsinventoryngRegistryKey();

            //update data
            while ($data = $PluginOcsinventoryngDBocs->fetch_array($result)){
               $data                  = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($data));
               $input                 = array();
               $input["computers_id"] = $computers_id;
               $input["hive"]         = $data["regtree"];
               $input["value"]        = $data["regvalue"];
               $input["path"]         = $data["regkey"];
               $input["ocs_name"]     = $data["name"];
               $isNewReg              = $reg->add($input, array('disable_unicity_check' => true));
               unset($reg->fields);
            }
         }
      }
      return;
   }


   /**
    * Update the administrative informations
    *
    * This function erase old data and import the new ones about administrative informations
    *
    * @param $computers_id integer : glpi computer id.
    * @param $ocsid integer : ocs computer id (ID).
    * @param $plugin_ocsinventoryng_ocsservers_id integer : ocs server id
    * @param $cfg_ocs array : configuration ocs of the server
    * @param $computer_updates array : already updated fields of the computer
    * @param $entity integer : entity of the computer
    * @param $dohistory boolean : log changes?
    *
    * @return Nothing (void).
   **/
   static function updateAdministrativeInfo($computers_id, $ocsid, $plugin_ocsinventoryng_ocsservers_id, $cfg_ocs,
                                            $computer_updates, $entity, $dohistory){
      global $DB, $PluginOcsinventoryngDBocs;

      self::checkOCSconnection($plugin_ocsinventoryng_ocsservers_id);
      //check link between ocs and glpi column
      $queryListUpdate = "SELECT*
                          FROM `glpi_plugin_ocsinventoryng_ocsadmininfoslinks`
                          WHERE `plugin_ocsinventoryng_ocsservers_id` = '$plugin_ocsinventoryng_ocsservers_id' ";
      $result = $DB->query($queryListUpdate);

      if ($DB->numrows($result) > 0){
         $queryOCS = "SELECT*
                      FROM `accountinfo`
                      WHERE `HARDWARE_ID` = '$ocsid'";
         $resultOCS = $PluginOcsinventoryngDBocs->query($queryOCS);

         if ($PluginOcsinventoryngDBocs->numrows($resultOCS) > 0){
            $data_ocs = $PluginOcsinventoryngDBocs->fetch_array($resultOCS);
            $comp = new Computer();

            //update data
            while ($links_glpi_ocs = $DB->fetch_array($result)){
               //get info from ocs
               $ocs_column  = $links_glpi_ocs['ocs_column'];
               $glpi_column = $links_glpi_ocs['glpi_column'];

               if (isset ($data_ocs[$ocs_column]) && !in_array($glpi_column, $computer_updates)){
                  $var = addslashes($data_ocs[$ocs_column]);
                  switch ($glpi_column){
                     case "groups_id":
                        $var = self::importGroup($var, $entity);
                        break;

                     case "locations_id":
                        $var = Dropdown::importExternal("Location", $var, $entity);
                        break;

                     case "networks_id":
                        $var = Dropdown::importExternal("Network", $var);
                        break;
                  }

                  $input                = array();
                  $input[$glpi_column]  = $var;
                  $input["id"]          = $computers_id;
                  $input["entities_id"] = $entity;
                  $input["_nolock"]     = true;
                  $comp->update($input, $dohistory);
               }
            }
         }
      }
   }

   static function cronInfo($name){
      // no translation for the name of the project
      return array('description' => 'OCS Inventory NG');
   }


   static function cronOcsng($task){
      global $DB, $CFG_GLPI;

      //Get a randon server id
      $plugin_ocsinventoryng_ocsservers_id = self::getRandomServerID();
      if ($plugin_ocsinventoryng_ocsservers_id > 0){
         //Initialize the server connection
         $PluginOcsinventoryngDBocs   = self::getDBocs($plugin_ocsinventoryng_ocsservers_id);
         $cfg_ocs = self::getConfig($plugin_ocsinventoryng_ocsservers_id);
         $task->log(__('Check updates from server', 'ocsinventoryng')." " . $cfg_ocs['name'] . "\n");

         if (!$cfg_ocs["cron_sync_number"]){
            return 0;
         }
         self::manageDeleted($plugin_ocsinventoryng_ocsservers_id);

         $query = "SELECT MAX(`last_ocs_update`)
                   FROM `glpi_plugin_ocsinventoryng_ocslinks`
                   WHERE `plugin_ocsinventoryng_ocsservers_id`='$plugin_ocsinventoryng_ocsservers_id'";
         $max_date="0000-00-00 00:00:00";
         if ($result=$DB->query($query)){
            if ($DB->numrows($result)>0){
               $max_date = $DB->result($result,0,0);
            }
         }

         $query_ocs = "SELECT*
                       FROM `hardware`
                       INNER JOIN `accountinfo` ON (`hardware`.`ID` = `accountinfo`.`HARDWARE_ID`)
                       WHERE ((`hardware`.`CHECKSUM` & " . $cfg_ocs["checksum"] . ") > '0'
                              OR `hardware`.`LASTDATE` > '$max_date') ";

         // workaround to avoid duplicate when synchro occurs during an inventory
         // "after" insert in ocsweb.hardware  and "before" insert in ocsweb.deleted_equiv
         $query_ocs .= " AND TIMESTAMP(`LASTDATE`) < (NOW()-180) ";

         $tag_limit = self::getTagLimit($cfg_ocs);
         if (!empty($tag_limit)){
            $query_ocs .= "AND ".$tag_limit;
         }

         $query_ocs .= " ORDER BY `hardware`.`LASTDATE` ASC
                        LIMIT ".intval($cfg_ocs["cron_sync_number"]);

         $result_ocs = $PluginOcsinventoryngDBocs->query($query_ocs);
         $nbcomp = $PluginOcsinventoryngDBocs->numrows($result_ocs);
         $task->setVolume(0);
         if ($nbcomp > 0){
            while ($data = $PluginOcsinventoryngDBocs->fetch_array($result_ocs)){
               $task->addVolume(1);
               $task->log(sprintf(__('%1$s: %2$s'), _n('Computer', 'Computer', 1),
                                  sprintf(__('%1$s (%2$s)'), $data["DEVICEID"], $data["ID"])));
               self::processComputer($data["ID"], $plugin_ocsinventoryng_ocsservers_id, 0);
            }
         } else{
            return 0;
         }
      }
      return 1;
   }


   static function analizePrinterPorts(&$printer_infos, $port=''){

      if (preg_match("/USB[0-9]*/i",$port)){
         $printer_infos['have_usb'] = 1;

      } else if (preg_match("/IP_/i",$port)){
         $printer_infos['have_ethernet'] = 1;

      } else if (preg_match("/LPT[0-9]:/i",$port)){
         $printer_infos['have_parallel'] = 1;
      }
   }


   static function getAvailableStatistics(){

      $stats = array('imported_machines_number'     => __('Computers imported', 'ocsinventoryng'),
                     'synchronized_machines_number' => __('Computers synchronized', 'ocsinventoryng'),
                     'linked_machines_number'       => __('Computers linked', 'ocsinventoryng'),
                     'notupdated_machines_number'   => __('Computers not updated', 'ocsinventoryng'),
                     'failed_rules_machines_number' => __("Computers don't check any rule",
                                                          'ocsinventoryng'),
                     'not_unique_machines_number'   => __('Duplicate computers', 'ocsinventoryng'),
                     'link_refused_machines_number' => __('Computers whose import is refused by a rule',
                                                          'ocsinventoryng'));
      return $stats;
   }


   static function manageImportStatistics(&$statistics=array(), $action= false){

      if(empty($statistics)){
         foreach (self::getAvailableStatistics() as $field => $label){
            $statistics[$field] = 0;
         }
      }

      switch ($action){
         case self::COMPUTER_SYNCHRONIZED:
            $statistics["synchronized_machines_number"]++;
            break;

         case self::COMPUTER_IMPORTED:
            $statistics["imported_machines_number"]++;
            break;

         case self::COMPUTER_FAILED_IMPORT:
            $statistics["failed_rules_machines_number"]++;
            break;

         case self::COMPUTER_LINKED:
            $statistics["linked_machines_number"]++;
            break;

         case self::COMPUTER_NOT_UNIQUE:
            $statistics["not_unique_machines_number"]++;
            break;

         case self::COMPUTER_NOTUPDATED:
            $statistics["notupdated_machines_number"]++;
            break;

         case self::COMPUTER_LINK_REFUSED:
            $statistics["link_refused_machines_number"]++;
            break;
      }
   }


   static function showStatistics($statistics=array(), $finished=false){

      echo "<div class='center b'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<th colspan='2'>".__('Statistics of the OCSNG link', 'ocsinventoryng');
      if ($finished){
         _e('Process completed');
      }
      echo "</th>";

      foreach (self::getAvailableStatistics() as $field => $label){
         echo "<tr class='tab_bg_1'><td>".$label."</td><td>".$statistics[$field]."</td></tr>";
      }
      echo "</table></div>";
   }


   /**
    * Do automatic transfer if option is enable
    *
    * @param $line_links array : data from glpi_plugin_ocsinventoryng_ocslinks table
    * @param $line_ocs array : data from ocs tables
    *
    * @return nothing
   **/
   static function transferComputer($line_links, $line_ocs){
      global $DB, $PluginOcsinventoryngDBocs, $CFG_GLPI;

      // Get all rules for the current plugin_ocsinventoryng_ocsservers_id
      $rule = new RuleImportEntityCollection();

      $data = array();
      $data = $rule->processAllRules(array('ocsservers_id'
                                                => $line_links["plugin_ocsinventoryng_ocsservers_id"],
                                            '_source'       => 'ocsinventoryng'),
                                     array(), array('ocsid' => $line_links["ocsid"]));

      // If entity is changing move items to the new entities_id
      if (isset($data['entities_id'])
          && $data['entities_id'] != $line_links['entities_id']){

         if (!isCommandLine() && !Session::haveAccessToEntity($data['entities_id'])){
            Html::displayRightError();
         }

         $transfer = new Transfer();
         $transfer->getFromDB($CFG_GLPI['transfers_id_auto']);

         $item_to_transfer = array("Computer" => array($line_links['computers_id']
                                                        =>$line_links['computers_id']));

         $transfer->moveItems($item_to_transfer, $data['entities_id'], $transfer->fields);
      }

      //If location is update by a rule
      self::updateLocation($line_links, $data);
   }


   /**
    * Update location for a computer if needed after rule processing
    *
    * @param line_links
    * @param data
    *
    * @return nothing
    */
   static function updateLocation($line_links, $data){

      //If there's a location to update
      if (isset($data['locations_id'])){
         $computer  = new Computer();
         $computer->getFromDB($line_links['computers_id']);
         $ancestors = getAncestorsOf('glpi_entities', $computer->fields['entities_id']);

         $location  = new Location();
         if ($location->getFromDB($data['locations_id'])){
            //If location is in the same entity as the computer, or if the location is
            //defined in a parent entity, but recursive
            if ($location->fields['entities_id'] == $computer->fields['entities_id']
                || (in_array($location->fields['entities_id'], $ancestors)
                    && $location->fields['is_recursive'])){

               $tmp['locations_id'] = $data['locations_id'];
               $tmp['id']           = $line_links['computers_id'];
               $computer->update($tmp);
            }
         }
      }
   }


   /**
    * Update TAG information in glpi_plugin_ocsinventoryng_ocslinks table
    *
    * @param $line_links array : data from glpi_plugin_ocsinventoryng_ocslinks table
    * @param $line_ocs array : data from ocs tables
    *
    * @return string : current tag of computer on update
   **/
   static function updateTag($line_links, $line_ocs){
      global $DB, $PluginOcsinventoryngDBocs;

      $query_ocs = "SELECT `accountinfo`.`TAG` AS TAG
                    FROM `hardware`
                    INNER JOIN `accountinfo`
                        ON (`hardware`.`ID` = `accountinfo`.`HARDWARE_ID`)
                    WHERE `hardware`.`ID` = '" . $line_links["ocsid"] . "'";

      $result_ocs = $PluginOcsinventoryngDBocs->query($query_ocs);

      if ($PluginOcsinventoryngDBocs->numrows($result_ocs) == 1){
         $data_ocs = Toolbox::addslashes_deep($PluginOcsinventoryngDBocs->fetch_array($result_ocs));

         $query = "UPDATE `glpi_plugin_ocsinventoryng_ocslinks`
                   SET `tag` = '" . $data_ocs["TAG"] . "'
                   WHERE `id` = '" . $line_links["id"] . "'";

         if ($DB->query($query)){
            $changes[0] = '0';
            $changes[1] = $line_links["tag"];
            $changes[2] = $data_ocs["TAG"];

            PluginOcsinventoryngOcslink::history($line_links["computers_id"], $changes,
                                                 PluginOcsinventoryngOcslink::HISTORY_OCS_TAGCHANGED);
            return $data_ocs["TAG"];
         }
      }
   }

   /**
    * For other plugins, add a type to the linkable types
    *
    *
    * @param $type string class name
   **/
   static function registerType($type){
      if (!in_array($type, self::$types)){
         self::$types[] = $type;
      }
   }


   /**
    * Type than could be linked to a Store
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false){

      if ($all){
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type){
         if (!($item = getItemForItemtype($type))){
            continue;
         }

         if (!$item->canView()){
            unset($types[$key]);
         }
      }
      return $types;
   }


   static function getLockableFields(){

     return array("name"                            => __('Name'),
                   "computertypes_id"               => __('Type'),
                   "manufacturers_id"               => __('Manufacturer'),
                   "computermodels_id"              => __('Model'),
                   "serial"                         => __('Serial number'),
                   "otherserial"                    => __('Inventory number'),
                   "comment"                        => __('Comments'),
                   "contact"                        => __('Alternate username'),
                   "contact_num"                    => __('Alternate username number'),
                   "domains_id"                     => __('Domain'),
                   "networks_id"                    => __('Network'),
                   "operatingsystems_id"            => __('Operating system'),
                   "operatingsystemservicepacks_id" => __('Service pack'),
                   "operatingsystemversions_id"     => __('Version of the operating system'),
                   "os_license_number"              => __('Serial of the operating system'),
                   "os_licenseid"                   => __('Product ID of the operating system'),
                   "users_id"                       => __('User'),
                   "locations_id"                   => __('Location'),
                   "groups_id"                      => __('Group'));
   }


   static function showOcsReportsConsole($id){

      $ocsconfig = PluginOcsinventoryngOcsServer::getConfig($id);

      echo "<div class='center'>";
      if ($ocsconfig["ocs_url"] != ''){
         echo "<iframe src='".$ocsconfig["ocs_url"]."/index.php?multi=4' width='95%' height='650'>";
      }
      echo "</div>";
   }


   /**
    * @since version 0.84
    *
    * @param $target
   **/
   static function checkBox($target){

      echo "<a href='".$target."?check=all' ".
             "onclick= \"if (markCheckboxes('ocsng_form')) return false;\">".__('Check all').
             "</a>&nbsp;/&nbsp;\n";
      echo "<a href='".$target."?check=none' ".
             "onclick= \"if ( unMarkCheckboxes('ocsng_form') ) return false;\">".
              __('Uncheck all') . "</a>\n";
   }


   static function getFirstServer(){
      global $DB;

      $query = "SELECT `id`
                FROM `glpi_plugin_ocsinventoryng_ocsservers`
                ORDER BY `id` ASC LIMIT 1 ";
      $results = $DB->query($query);
      if ($DB->numrows($results) > 0){
         return $DB->result($results, 0, 'id');
      }
      return -1;
   }


   /**
    * Delete old devices settings
    *
    * @param $glpi_computers_id integer : glpi computer id.
    * @param $itemtype integer : device type identifier.
    *
    * @return nothing.
    **/
   static function resetDevices($glpi_computers_id, $itemtype){
      global $DB;

      $linktable = getTableForItemType('Item_'.$itemtype);

      $query = "DELETE
                FROM `$linktable`
                WHERE `items_id` = '$glpi_computers_id'
                     AND `itemtype` = 'Computer'";
      $DB->query($query);
   }

   /**
    *
    * Import monitors from OCS
    * @since 1.0
    * @param $cfg_ocs OCSNG mode configuration
    * @param $computers_id computer's id in GLPI
    * @param $ocsservers_id OCS server id
    * @param $ocsid computer's id in OCS
    * @param entity the entity in which the monitor will be created
    * @param dohistory record in history link between monitor and computer
    */
   static function importMonitor($cfg_ocs, $computers_id, $ocsservers_id, $ocsid, $entity,
                                   $dohistory){
      global $PluginOcsinventoryngDBocs, $DB;

      self::checkOCSconnection($ocsservers_id);

      if ($cfg_ocs["import_monitor"]) {

         $already_processed = array();
         $do_clean          = true;
         $m                 = new Monitor();
         $conn              = new Computer_Item();

         $query = "SELECT DISTINCT `CAPTION`, `MANUFACTURER`, `DESCRIPTION`, `SERIAL`, `TYPE`
                   FROM `monitors`
                   WHERE `HARDWARE_ID` = '$ocsid'";
         // Config says import monitor with serial number only
         // Restrict SQL query ony for monitors with serial present
         if ($cfg_ocs["import_monitor"] == 4) {
            $query = $query." AND `SERIAL` NOT LIKE ''";
         }

         $result      = $PluginOcsinventoryngDBocs->query($query);
         $lines       = array();
         $checkserial = true;
         // First pass - check if all serial present
         if ($PluginOcsinventoryngDBocs->numrows($result) > 0) {
            while ($line = $PluginOcsinventoryngDBocs->fetch_array($result)) {
               if (empty($line["SERIAL"])) {
                  $checkserial = false;
               }
               $lines[] = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line));
            }
         }

         if (count($lines)>0
               && ($cfg_ocs["import_monitor"] <= 2 || $checkserial)) {

            foreach ($lines as $line) {
               $mon         = array();
               $mon["name"] = $line["CAPTION"];
               if (empty ($line["CAPTION"]) && !empty ($line["MANUFACTURER"])) {
                  $mon["name"] = $line["MANUFACTURER"];
               }
               if (empty ($line["CAPTION"]) && !empty ($line["TYPE"])){
                  if (!empty ($line["MANUFACTURER"])){
                     $mon["name"] .= " ";
                  }
                  $mon["name"] .= $line["TYPE"];
               }
               $mon["serial"] = $line["SERIAL"];
               //Look for a monitor with the same name (and serial if possible) already connected
               //to this computer
               $query = "SELECT `m`.`id`, `gci`.`is_deleted`
                         FROM `glpi_monitors` as `m`, `glpi_computers_items` as `gci`
                         WHERE `m`.`id` = `gci`.`items_id`
                            AND `gci`.`is_dynamic`='1'
                            AND `computers_id`='$computers_id'
                            AND `itemtype`='Monitor'
                            AND `m`.`name`='".$mon["name"]."'";
               if (!empty ($mon["serial"])) {
                  $query.= " AND `m`.`serial`='".$mon["serial"]."'";
               }
               $results = $DB->query($query);
               $id      = false;
               $lock    = false;
               if ($DB->numrows($results) == 1) {
                  $id   = $DB->result($results, 0, 'id');
                  $lock = $DB->result($results, 0, 'is_deleted');
               }

               if ($id == false) {
                  // Clean monitor object
                  $m->reset();
                  $mon["manufacturers_id"] = Dropdown::importExternal('Manufacturer',
                                                                      $line["MANUFACTURER"]);
                  if ($cfg_ocs["import_monitor_comment"]) {
                     $mon["comment"] = $line["DESCRIPTION"];
                  }
                  $id_monitor = 0;

                  if ($cfg_ocs["import_monitor"] == 1) {
                     //Config says : manage monitors as global
                     //check if monitors already exists in GLPI
                     $mon["is_global"] = 1;
                     $query = "SELECT `id`
                               FROM `glpi_monitors`
                               WHERE `name` = '" . $mon["name"] . "'
                                  AND `is_global` = '1'
                                  AND `entities_id` = '$entity'";
                     $result_search = $DB->query($query);

                     if ($DB->numrows($result_search) > 0) {
                        //Periph is already in GLPI
                        //Do not import anything just get periph ID for link
                        $id_monitor = $DB->result($result_search, 0, "id");
                     } else{
                        $input = $mon;
                        if ($cfg_ocs["states_id_default"]>0) {
                           $input["states_id"] = $cfg_ocs["states_id_default"];
                        }
                        $input["entities_id"] = $entity;
                        $id_monitor = $m->add($input);
                     }

                  } else if ($cfg_ocs["import_monitor"] >= 2) {
                     //Config says : manage monitors as single units
                     //Import all monitors as non global.
                     $mon["is_global"] = 0;

                     // Try to find a monitor with the same serial.
                     if (!empty ($mon["serial"])){
                        $query = "SELECT `id`
                                  FROM `glpi_monitors`
                                  WHERE `serial` LIKE '%" . $mon["serial"] . "%'
                                     AND `is_global` = '0'
                                     AND `entities_id` = '$entity'";
                        $result_search = $DB->query($query);
                        if ($DB->numrows($result_search) == 1) {
                           //Monitor founded
                           $id_monitor = $DB->result($result_search, 0, "id");
                        }
                     }

                     //Search by serial failed, search by name
                     if ($cfg_ocs["import_monitor"] == 2 && !$id_monitor) {
                        //Try to find a monitor with no serial, the same name and not already connected.
                        if (!empty ($mon["name"])){
                           $query = "SELECT `glpi_monitors`.`id`
                                           FROM `glpi_monitors`
                                           LEFT JOIN `glpi_computers_items`
                                                ON (`glpi_computers_items`.`itemtype`='Monitor'
                                                    AND `glpi_computers_items`.`items_id`
                                                            =`glpi_monitors`.`id`)
                                           WHERE `serial` = ''
                                                 AND `name` = '" . $mon["name"] . "'
                                                       AND `is_global` = '0'
                                                       AND `entities_id` = '$entity'
                                                       AND `glpi_computers_items`.`computers_id` IS NULL";
                           $result_search = $DB->query($query);
                           if ($DB->numrows($result_search) == 1) {
                              $id_monitor = $DB->result($result_search, 0, "id");
                           }
                        }
                     }

                     if (!$id_monitor) {
                        $input = $mon;
                        if ($cfg_ocs["states_id_default"]>0){
                           $input["states_id"] = $cfg_ocs["states_id_default"];
                        }
                        $input["entities_id"] = $entity;
                        $id_monitor = $m->add($input);
                     }
                  } // ($cfg_ocs["import_monitor"] >= 2)

                  if ($id_monitor){
                     //Import unique : Disconnect monitor on other computer done in Connect function
                     $connID = $conn->add(array('computers_id' => $computers_id,
                                                'itemtype'     => 'Monitor',
                                                'items_id'     => $id_monitor,
                                                '_no_history'  => !$dohistory,
                                                'is_dynamic'   => 1,
                                                'is_deleted'  => 0));
                     $already_processed[] = $id_monitor;

                     //Update column "is_deleted" set value to 0 and set status to default
                     $input = array();
                     $old   = new Monitor();
                     if ($old->getFromDB($id_monitor)){
                        if ($old->fields["is_deleted"]){
                           $input["is_deleted"] = 0;
                        }
                        if ($cfg_ocs["states_id_default"] >0
                              && $old->fields["states_id"] != $cfg_ocs["states_id_default"]){
                           $input["states_id"] = $cfg_ocs["states_id_default"];
                        }
                        if (empty($old->fields["name"]) && !empty($mon["name"])){
                           $input["name"] = $mon["name"];
                        }
                        if (empty($old->fields["serial"]) && !empty($mon["serial"])){
                           $input["serial"] = $mon["serial"];
                        }
                        if (count($input)){
                           $input["id"]          = $id_monitor;
                           $input['entities_id'] = $entity;
                           $m->update($input);
                        }
                     }
                  }

               } else{
                  $already_processed[] = $id;
               }
            } // end foreach

            if ($cfg_ocs["import_monitor"]<=2 || $checkserial){
               //Look for all monitors, not locked, not linked to the computer anymore
               $query = "SELECT `id`
                         FROM `glpi_computers_items`
                         WHERE `itemtype`='Monitor'
                            AND `computers_id`='$computers_id'
                            AND `is_dynamic`='1'
                            AND `is_deleted`='0'";
               if (!empty($already_processed)){
                  $query .= "AND `items_id` NOT IN (".implode(',', $already_processed).")";
               }
               foreach ($DB->request($query) as $data){
               //Delete all connexions
                  $conn->delete(array('id'             => $data['id'],
                                       '_ocsservers_id' => $ocsservers_id), true);
               }
            }
         }
      }
   }

   /**
    *
    * Import printers from OCS
    * @since 1.0
    * @param $cfg_ocs OCSNG mode configuration
    * @param $computers_id computer's id in GLPI
    * @param $ocsid computer's id in OCS
    * @param $ocsservers_id OCS server id
    * @param $entity the entity in which the printer will be created
    * @param $dohistory record in history link between printer and computer
    */
    static function importPrinter($cfg_ocs, $computers_id, $ocsservers_id, $ocsid, $entity,
                                    $dohistory){
       global $PluginOcsinventoryngDBocs, $DB;


       self::checkOCSconnection($ocsservers_id);

       if ($cfg_ocs["import_printer"]){

          $already_processed = array();
          
          $conn              = new Computer_Item();

          $query  = "SELECT*
                     FROM `printers`
                     WHERE `HARDWARE_ID` = '$ocsid'";
          $result = $PluginOcsinventoryngDBocs->query($query);
          $p      = new Printer();

          if ($PluginOcsinventoryngDBocs->numrows($result) > 0){
             while ($line = $PluginOcsinventoryngDBocs->fetch_array($result)){
                $line  = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line));
                $print = array();
                // TO TEST : PARSE NAME to have real name.
                $print['name'] = self::encodeOcsDataInutf8($cfg_ocs["ocs_db_utf8"], $line['NAME']);

                if (empty ($print["name"])){
                   $print["name"] = $line["DRIVER"];
                }

                $management_process = $cfg_ocs["import_printer"];

                //Params for the dictionnary
                $params['name']         = $print['name'];
                $params['manufacturer'] = "";
                $params['DRIVER']       = $line['DRIVER'];
                $params['PORT']         = $line['PORT'];

                if (!empty ($print["name"])){
                   $rulecollection = new RuleDictionnaryPrinterCollection();
                   $res_rule
                      = Toolbox::addslashes_deep($rulecollection->processAllRules(Toolbox::stripslashes_deep($params),
                                                 array(), array()));

                   if (!isset($res_rule["_ignore_import"])
                         || !$res_rule["_ignore_import"]){

                      foreach ($res_rule as $key => $value){
                         if ($value != '' && $value[0] != '_'){
                            $print[$key] = $value;
                         }
                      }

                      if (isset($res_rule['is_global'])){
                         if (!$res_rule['is_global']){
                            $management_process = 2;
                         } else{
                            $management_process = 1;
                         }
                      }

                     //Look for a monitor with the same name (and serial if possible) already connected
                     //to this computer
                     $query = "SELECT `p`.`id`, `gci`.`is_deleted`
                               FROM `glpi_printers` as `p`, `glpi_computers_items` as `gci`
                               WHERE `p`.`id` = `gci`.`items_id`
                                  AND `gci`.`is_dynamic`='1'
                                  AND `computers_id`='$computers_id'
                                  AND `itemtype`='Printer'
                                  AND `p`.`name`='".$print["name"]."'";
                     $results = $DB->query($query);
                     $id      = false;
                     $lock    = false;
                     if ($DB->numrows($results) > 0){
                        $id   = $DB->result($results, 0, 'id');
                        $lock = $DB->result($results, 0, 'is_deleted');
                     }

                     if (!$id){
                         // Clean printer object
                         $p->reset();
                         $print["comment"] = $line["PORT"] . "\r\n" . $line["DRIVER"];
                         self::analizePrinterPorts($print, $line["PORT"]);
                         $id_printer = 0;

                         if ($management_process == 1){
                            //Config says : manage printers as global
                            //check if printers already exists in GLPI
                            $print["is_global"] = MANAGEMENT_GLOBAL;
                            $query = "SELECT `id`
                                      FROM `glpi_printers`
                                      WHERE `name` = '" . $print["name"] . "'
                                         AND `is_global` = '1'
                                         AND `entities_id` = '$entity'";
                            $result_search = $DB->query($query);

                            if ($DB->numrows($result_search) > 0){
                               //Periph is already in GLPI
                               //Do not import anything just get periph ID for link
                               $id_printer        = $DB->result($result_search, 0, "id");
                               $already_processed[] = $id_printer;
                            } else{
                               $input = $print;

                               if ($cfg_ocs["states_id_default"]>0){
                                  $input["states_id"] = $cfg_ocs["states_id_default"];
                               }
                               $input["entities_id"] = $entity;
                               $id_printer           = $p->add($input);
                            }

                         } else if ($management_process == 2){
                            //Config says : manage printers as single units
                            //Import all printers as non global.
                            $input              = $print;
                            $input["is_global"] = MANAGEMENT_UNITARY;

                            if ($cfg_ocs["states_id_default"]>0){
                               $input["states_id"] = $cfg_ocs["states_id_default"];
                            }
                            $input["entities_id"] = $entity;
                            $input['is_dynamic']  = 1;
                            $id_printer           = $p->add($input);
                         }

                         if ($id_printer){
                            $already_processed[] = $id_printer;
                            $conn   = new Computer_Item();
                            $connID = $conn->add(array('computers_id' => $computers_id,
                                                       'itemtype'     => 'Printer',
                                                       'items_id'     => $id_printer,
                                                       '_no_history'  => !$dohistory,
                                                       'is_dynamic' => 1));
                            //Update column "is_deleted" set value to 0 and set status to default
                            $input                = array();
                            $input["id"]          = $id_printer;
                            $input["is_deleted"]  = 0;
                            $input["entities_id"] = $entity;

                            if ($cfg_ocs["states_id_default"]>0){
                               $input["states_id"] = $cfg_ocs["states_id_default"];
                            }
                            $p->update($input);
                         }

                      } else{
                         $already_processed[] = $id;
                      }
                   }
                }
             }
          }

          //Look for all monitors, not locked, not linked to the computer anymore
          $query = "SELECT `id`
                    FROM `glpi_computers_items`
                    WHERE `itemtype`='Printer'
                       AND `computers_id`='$computers_id'
                       AND `is_dynamic`='1'
                       AND `is_deleted`='0'";
          if (!empty($already_processed)){
             $query .= "AND `items_id` NOT IN (".implode(',', $already_processed).")";
          }
          foreach ($DB->request($query) as $data){
             //Delete all connexions
             $conn->delete(array('id'             => $data['id'],
                                  '_ocsservers_id' => $ocsservers_id), true);
          }
       }
   }

   /**
    *
    * Import peripherals from OCS
    * @since 1.0
    * @param $cfg_ocs OCSNG mode configuration
    * @param $computers_id computer's id in GLPI
    * @param $ocsid computer's id in OCS
    * @param $ocsservers_id OCS server id
    * @param $entity the entity in which the peripheral will be created
    * @param $dohistory record in history link between peripheral and computer
    */
   static function importPeripheral($cfg_ocs, $computers_id, $ocsservers_id, $ocsid, $entity,
                                      $dohistory){
      global $PluginOcsinventoryngDBocs, $DB;

      self::checkOCSconnection($ocsservers_id);
      if ($cfg_ocs["import_periph"]){
         $already_processed = array();
         $p                 = new Peripheral();
         $conn              = new Computer_Item();

         $query = "SELECT DISTINCT `CAPTION`, `MANUFACTURER`, `INTERFACE`, `TYPE`
                   FROM `inputs`
                   WHERE `HARDWARE_ID` = '$ocsid'
                   AND `CAPTION` <> ''";
         $result = $PluginOcsinventoryngDBocs->query($query);

         if ($PluginOcsinventoryngDBocs->numrows($result) > 0){
            while ($line = $PluginOcsinventoryngDBocs->fetch_array($result)){
               $line   = Toolbox::clean_cross_side_scripting_deep(Toolbox::addslashes_deep($line));
               $periph = array();

               $periph["name"] = self::encodeOcsDataInUtf8($cfg_ocs["ocs_db_utf8"], $line["CAPTION"]);

               //Look for a monitor with the same name (and serial if possible) already connected
               //to this computer
               $query = "SELECT `p`.`id`, `gci`.`is_deleted`
                         FROM `glpi_printers` as `p`, `glpi_computers_items` as `gci`
                         WHERE `p`.`id` = `gci`.`items_id`
                            AND `gci`.`is_dynamic`='1'
                            AND `computers_id`='$computers_id'
                            AND `itemtype`='Peripheral'
                            AND `p`.`name`='".$periph["name"]."'";
               $results = $DB->query($query);
               $id      = false;
               $lock    = false;
               if ($DB->numrows($results) > 0){
                  $id   = $DB->result($results, 0, 'id');
                  $lock = $DB->result($results, 0, 'is_deleted');
               }

               if (!$id){
               // Clean peripheral object
                  $p->reset();
                  if ($line["MANUFACTURER"] != "NULL"){
                     $periph["brand"] = self::encodeOcsDataInUtf8($cfg_ocs["ocs_db_utf8"],
                                                                  $line["MANUFACTURER"]);
                  }
                  if ($line["INTERFACE"] != "NULL"){
                     $periph["comment"] = self::encodeOcsDataInUtf8($cfg_ocs["ocs_db_utf8"],
                                                                     $line["INTERFACE"]);
                  }
                  $periph["peripheraltypes_id"] = Dropdown::importExternal('PeripheralType',
                                                                           $line["TYPE"]);
                  $id_periph = 0;
                  if ($cfg_ocs["import_periph"] == 1){
                     //Config says : manage peripherals as global
                     //check if peripherals already exists in GLPI
                     $periph["is_global"] = 1;
                     $query = "SELECT `id`
                               FROM `glpi_peripherals`
                               WHERE `name` = '" . $periph["name"] . "'
                                  AND `is_global` = '1'
                                  AND `entities_id` = '$entity'";
                     $result_search = $DB->query($query);
                     if ($DB->numrows($result_search) > 0){
                        //Periph is already in GLPI
                        //Do not import anything just get periph ID for link
                        $id_periph = $DB->result($result_search, 0, "id");
                     } else{
                        $input = $periph;
                        if ($cfg_ocs["states_id_default"]>0){
                           $input["states_id"] = $cfg_ocs["states_id_default"];
                        }
                        $input["entities_id"] = $entity;
                        $id_periph = $p->add($input);
                     }
                  } else if ($cfg_ocs["import_periph"] == 2){
                     //Config says : manage peripherals as single units
                     //Import all peripherals as non global.
                     $input = $periph;
                     $input["is_global"] = 0;
                     if ($cfg_ocs["states_id_default"]>0){
                        $input["states_id"] = $cfg_ocs["states_id_default"];
                     }
                     $input["entities_id"] = $entity;
                     $id_periph = $p->add($input);
                  }
                  if ($id_periph){
                     $already_processed[] = $id_periph;
                     $conn                = new Computer_Item();
                     if ($connID = $conn->add(array('computers_id' => $computers_id,
                                                    'itemtype'     => 'Peripheral',
                                                    'items_id'     => $id_periph,
                                                    '_no_history'  => !$dohistory,
                                                    'is_dynamic' => 1))){
                        //Update column "is_deleted" set value to 0 and set status to default
                        $input                = array();
                        $input["id"]          = $id_periph;
                        $input["is_deleted"]  = 0;
                        $input["entities_id"] = $entity;
                        if ($cfg_ocs["states_id_default"]>0){
                           $input["states_id"] = $cfg_ocs["states_id_default"];
                        }
                        $p->update($input);
                     }
                  }
               } else{
                  $already_processed[] = $id;
               }
            }
         }
         //Look for all monitors, not locked, not linked to the computer anymore
         $query = "SELECT `id`
                   FROM `glpi_computers_items`
                   WHERE `itemtype`='Peripheral'
                      AND `computers_id`='$computers_id'
                      AND `is_dynamic`='1'
                      AND `is_deleted`='0'";
         if (!empty($already_processed)){
            $query .= "AND `items_id` NOT IN (".implode(',', $already_processed).")";
         }
         foreach ($DB->request($query) as $data){
            //Delete all connexions
            $conn->delete(array('id'             => $data['id'],
                                 '_ocsservers_id' => $ocsservers_id), true);
         }
      }
   }
}
?>