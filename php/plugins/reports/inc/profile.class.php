<?php
/*
 * @version $Id: profile.class.php 240 2013-04-17 14:12:48Z yllen $
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2013 by the reports Development Team.

 https://forge.indepnet.net/projects/reports
 -------------------------------------------------------------------------

 LICENSE

 This file is part of reports.

 reports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with reports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

class PluginReportsProfile extends CommonDBTM {


   /**
    * if profile deleted
    *
    * @param $prof   Profile  object
   **/
   static function cleanProfile(Profile $prof) {

      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }


   /**
    * if profile cloned
    *
    * @param $prof   Profile  object
   **/
   static function cloneProfile(Profile $prof) {
      global $DB;

      $plugprof = new self();
      $crit     = array('profiles_id' => $prof->input['_old_id']);
      foreach ($DB->request($plugprof->getTable(), $crit) as $data) {
         $input = ToolBox::addslashes_deep($data);
         unset($input['id']);
         $input['profiles_id'] = $prof->getID();
         $plugprof->add($input);
      }
   }


   static function canCreate() {
      return Session::haveRight('profile', 'w');
   }


   static function canView() {
      return Session::haveRight('profile', 'r');
   }


   /**
    * @param $prof   Profile object
   **/
   static function showForProfile(Profile $prof){
      global $DB, $LANG;

      $target = Toolbox::getItemTypeFormURL(__CLASS__);

      $profiles_id = $prof->getField('id');
      $prof->check($profiles_id, 'r');
      $canedit = $prof->can($profiles_id, 'w');

      $prof = new Profile();
      if ($profiles_id){
         $prof->getFromDB($profiles_id);
      }

      $rights = self::getAllRights(array('profiles_id' => $profiles_id));
      if ($canedit) {
         echo "<form action='".$target."' method='post'>";
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4' class='center b'>";
      printf(__('%1$s: %2$s'), __('Rights management by profil', 'reports'), $prof->fields["name"]);
      echo "</th></tr>";

      $plugname = array();
      foreach(searchReport() as $key => $plug) {
         $mod = (($plug == 'reports') ? $key : "${plug}_${key}");
         echo "<tr class='tab_bg_1'>";
         if (!isset($plugname[$plug])) {
            // Retrieve the plugin name
            $function         = "plugin_version_$plug";
            $tmp              = $function();
            $plugname[$plug]  = $tmp['name'];
         }
         echo "<td>".$plugname[$plug]."</td>";
         if (strpos($key,'stat') === false) {
            echo "<td>"._n('Report', 'Reports', 2)."</td>";
         } else {
            echo "<td>".__('Statistics')."</td>";
         }
         echo "<td>".$LANG["plugin_$plug"][$key]."</td><td>";
         if ((isStat($key) && ( $prof->getField('statistic')== 1))
             || (!isStat($key) && ($prof->getField('reports') == 'r'))) {
            Profile::dropdownNoneReadWrite($mod, (isset($rights[$mod])?$rights[$mod]:''), 1, 1, 0);
         } else {
            // Can't access because missing right from GLPI core
            // Profile::dropdownNoneReadWrite($mod,'',1,0,0);
            echo "<input type='hidden' name='$mod' value='NULL'>".__('No Access');
            echo (isStat($key) ? " **" : " *");
         }
         echo "</td></tr>";
      }

      if (($prof->getField('statistic')!= 1)
          || ($prof->getField('reports') != 'r')) {
         echo "<tr class='b tab_bg_4'><td colspan='4'>";
         if ($prof->getField('reports')!='r') {
            echo '*  '.__('No right on Tools / Reports', 'reports').'.<br>';
         }
         if ($prof->getField('statistic')!=1) {
            echo '** '.__('No right on Assistance / Statistics', 'reports').'.';
         }
         echo "</td></tr>\n";
      }
      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center' colspan='4'>";
         echo "<input type='hidden' name='profiles_id' value=$profiles_id>";
         echo "<input type='submit' name='update_user_profile' value='"._sx('button', 'Update')."'
                class='submit'>";
         echo "</td></tr>\n";
         echo "</table>";
         Html::closeForm();
      } else {
         echo "</table>";
      }
   }


   /**
    * @param $report
   **/
   static function showForReport($report) {
      global $DB;

      if (empty($report) || !Session::haveRight('profile', 'r')) {
         return false;
      }
      $current = self::getAllRights(array('report' => $report), true);
      $canedit = Session::haveRight('profile', 'w');

      if ($canedit) {
         echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>\n";
      }

      echo "<table class='tab_cadre'>\n";
      echo "<tr><th colspan='2'>".__('Profils rights', 'reports')."</th></tr>\n";


      $query = "SELECT `id`, `name`, `statistic`, `reports`
                FROM `glpi_profiles`
                ORDER BY `name`";

      foreach ($DB->request($query) as $data) {
         echo "<tr class='tab_bg_1'><td>" . $data['name'] . "&nbsp: </td><td>";
         if ((isStat($report) && ($data['statistic'] == 1))
             || (!isStat($report) && ($data['reports'] == 'r'))) {
            Profile::dropdownNoneReadWrite($data['id'], (isset($current[$data['id']])?'r':''),
                                           1, 1, 0);
         } else {
            // Can't access because missing right from GLPI core
            // Profile::dropdownNoneReadWrite($mod,'',1,0,0);
            echo "<input type='hidden' name='".$data['id']."' value='NULL'>".__('No access')." *";
         }
         echo "</td></tr>\n";
      }
      echo "<tr class='tab_bg_4'><td colspan='2'>* ";
      if (isStat($report)) {
         _e('No right on Assistance / Statistics', 'reports');
      } else {
         _e('No right on Tools / Reports', 'reports');
      }
      echo "</tr>";

      if ($canedit) {
         echo "<tr class='tab_bg_1'><td colspan='2' class='center'>";
         echo "<input type='hidden' name='report' value='$report'>";
         echo "<input type='submit' name='update' value='"._sx('button', 'Update')."' ".
                "class='submit'>&nbsp;&nbsp;&nbsp;";
         echo "<input type='submit' name='delete' value='"._sx('button', 'Delete permanently')."'
                class='submit'>";
         echo "</td></tr>\n";
         echo "</table>\n";
         Html::closeForm();
      } else {
         echo "</table>\n";
      }
   }


   /**
    * @param $input
   **/
   static function updateForProfile($input) {

      $prof = new self();
      $current = self::getAllRights(array('profiles_id' => $input['profiles_id']),
                                          true);

      foreach(searchReport() as $key => $plug) {
         $mod = ($plug=='reports' ? $key : "${plug}_${key}");

         if ($input[$mod] == 'r') {
            if (isset($current[$mod])) {
               unset($current[$mod]);
            } else {
               // Give right
               $prof->add(array('profiles_id' => $input['profiles_id'],
                                'report'      => $mod,
                                'access'      => 'r'));
            }
         }
      }
      foreach ($current as $mod => $data) {
         $prof->delete($data);
      }
   }


   /**
    * @param $input
   **/
   static function updateForReport($input) {

      $prof    = new self();
      $report  = $input['report'];
      $current = self::getAllRights(array('report' => $report), true);

      foreach($input as $key => $right) {
         if (is_numeric($key) && $right=='r') {
            if (isset($current[$key])) {
               unset($current [$key]);
            } else {
               // Give right
               $prof->add(array('profiles_id' => $key,
                                'report'      => $report,
                                'access'      => 'r'));
            }
         } else {
            unset($input [$key]);
         }
      }
      foreach ($current as $key => $data) {
         $prof->delete($data);
      }
   }


   /**
    * @param $reports
   **/
   function updateRights($reports) {
      global $DB;

      $rights = array();
      foreach ($reports as $report => $plug) {
         if ($plug == 'reports') {
            $rights[$report] = 1;
         } else {
            $rights["${plug}_${report}"] = 1;
         }
      }

      $current_rights = array();
      $query = "SELECT DISTINCT `report`
                FROM `glpi_plugin_reports_profiles`";
      foreach ($DB->request($query) as $data) {
         $current_rights[$data['report']] = 1;
      }

      // Removed report
      foreach($current_rights as $right => $value) {
         if (!isset($rights[$right])) {
            // Delete the lines for old reports
            $this->deleteByCriteria(array('report' => $right));
         } else {
            unset($rights[$right]);
         }
      }

      // Added report
      foreach ($rights as $right => $val) {
         $DB->query("INSERT INTO `".$this->getTable()."`
                            (`profiles_id`, `report`, `access`)
                     VALUES (4, '$right', 'r')");

         // For immediate availability
         if ($_SESSION['glpiactiveprofile']['id'] == 4) {
            $_SESSION['glpi_plugin_reports_profile'][$right] = 'r';
         }
      }
   }


   /**
    * @param $crit
    * @param $full   (false by default)
   **/
   static function getAllRights($crit, $full=false) {
      global $DB;

      $tab = array();

      foreach ($DB->request('glpi_plugin_reports_profiles', $crit) as $data) {
         if (isset($crit['report'])) {
            $tab[$data['profiles_id']] = ($full ? $data : $data['access']);
         } else {
            $tab[$data['report']] = ($full ? $data : $data['access']);
         }
      }
      return $tab;
   }


   static function changeprofile() {

      $crit = array('profiles_id' => $_SESSION['glpiactiveprofile']['id']);
      $_SESSION['glpi_plugin_reports_profile'] = self::getAllRights($crit);
   }


   /**
    * Look for all the plugins, and update rights if necessary
    */
   function updatePluginRights() {

      $tab = searchReport();
      $this->updateRights($tab);

      return $tab;
   }


   static function install() {
      global $DB;

      $create = "CREATE TABLE IF NOT EXISTS `glpi_plugin_reports_profiles` (
                    `id` int(11) NOT NULL auto_increment,
                    `profiles_id` int(11) NOT NULL DEFAULT '0',
                    `report` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `access` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `report` (`report`),
                  KEY `profiles_id` (`profiles_id`))
                  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      if (TableExists('glpi_plugin_reports_profiles')) { //1.1 ou 1.2

         if (FieldExists('glpi_plugin_reports_profiles','ID')) { // version installee < 1.4.0
            $query = "ALTER TABLE `glpi_plugin_reports_profiles`
                      CHANGE `ID` `id` int(11) NOT NULL auto_increment";
            $DB->query($query) or die("CHANGE ID: ".$DB->error());
         }

         if (!FieldExists('glpi_plugin_reports_profiles','profiles_id')) { // version < 1.5.0
            $query = "RENAME TABLE `glpi_plugin_reports_profiles`
                                TO `glpi_plugin_reports_oldprofiles`";
            $DB->query($query) or die("SAVE TABLE profiles: ".$DB->error());
            $DB->query($create) or die("CREATE TABLE profiles: ".$DB->error());

            $fields = $DB->list_fields('glpi_plugin_reports_oldprofiles');
            unset($fields['id']);
            unset($fields['profile']);
            foreach($fields as $field => $descr) {
               $query = "INSERT INTO `glpi_plugin_reports_profiles`
                                (`profiles_id`, `report`, `access`)
                          SELECT `id`, '$field', `$field`
                          FROM `glpi_plugin_reports_oldprofiles`
                          WHERE `$field` IS NOT NULL";
               $DB->query($query) or die("LOAD TABLE profiles: ".$DB->error());
            }

            $query = "DROP TABLE `glpi_plugin_reports_oldprofiles`";
            $DB->query($query) or die("DROP TABLE oldprofiles: ".$DB->error());
         }
      } else {
         $DB->query($create) or die("CREATE TABLE profiles: ".$DB->error());
      }

      return true;
   }


   static function uninstall() {
      global $DB;

      $tables = array('glpi_plugin_reports_profiles',
                      'glpi_plugin_reports_oldprofiles',
                      'glpi_plugin_reports_doublons_backlist',
                      'glpi_plugin_reports_doublons_backlists');

      foreach ($tables as $table) {
         $query = "DROP TABLE IF EXISTS `$table`";
         $DB->query($query) or die($DB->error());
      }

      return true;
   }


   /**
    * @see inc/CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         if ($item->fields['interface'] != 'helpdesk') {
            return array(1 => _n('Report', 'Reports', 2));
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         if ($item->getField('interface') == 'central') {
            $prof = new self();
            $prof->updatePluginRights();
            self::showForProfile($item);
         }
      }
      return true;
   }

}
?>