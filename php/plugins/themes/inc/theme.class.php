<?php
/*
 *
 -------------------------------------------------------------------------
 Themes
 Copyright (C) 2012 by iizno.

 https://forge.indepnet.net/projects/themes
 -------------------------------------------------------------------------

 LICENSE

 This file is part of themes.

 themes is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 themes is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with themes. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// Original Author of file: Jérôme Ansel <jerome@ansel.im>
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginThemesTheme extends CommonDBTM {

   public $dohistory = true;

   static function canView() {
      return true;
   }

   static function canCreate() {
      return true;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->getType()=='Preference') {
         return __('Themes manager', 'themes');
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Preference') {
         $self = new self();
         $self->showPluginFromPreference($item);
      }
      return true;
   }

   function showPluginFromPreference($item) {
      global $DB, $CFG_GLPI;

      $currentUserId = Session::getLoginUserID();
      $defaultThemeId = self::getDefaultThemeId();
      $userCurrentThemeId = self::getUserTheme();

      echo "<form action='".$CFG_GLPI["root_doc"].
         "/plugins/themes/front/themes.prefs.php' method='POST'>";
      echo "<input type='hidden' name='theme_user_selection' value='1' />";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<td>".__('Choose your theme : ', 'themes');
      PluginThemesTheme::dropdown(array('name' => "plugin_themes_themes_id",
                                                'value' => $userCurrentThemeId));
//       Dropdown::show('PluginThemesTheme', array('name' => "plugin_themes_themes_id",
//                                                 'value' => $userCurrentThemeId));
      echo "</td><td>";
      echo "<input type='submit' class='submit' value='".__('Choose', 'themes')."' />";
      echo "</td>";
      echo "<td>";
      echo "</tr>";
      echo "</table>";
      Html::closeForm();
   }
   
   /**
    * Set theme for the current user.
    */
   function setAsUserTheme() {
      global $DB;
      
      $userId = Session::getLoginUserID();
      $themeId = $this->getId();
      
      $DB->query("DELETE FROM glpi_plugin_themes_per_user WHERE user_id = {$userId};");
      
      $insertSql = "INSERT INTO glpi_plugin_themes_per_user(theme_id, user_id) 
                    VALUES({$themeId}, {$userId});";
                    
      return $DB->query($insertSql);
   }

   /**
    * Get theme from the current user.
    */   
   static function getUserTheme() {
      global $DB;

      if(!Session::getLoginUserID())
         return self::getDefaultThemeId();
               
      $userId = Session::getLoginUserID();
      
      $result = $DB->query("SELECT theme_id 
                            FROM glpi_plugin_themes_per_user 
                            WHERE user_id = {$userId};");
      if ($DB->numrows($result) == 0) {
         return self::getDefaultThemeId();
      } else {      
         $return = $DB->fetch_assoc($result);
         return $return['theme_id'];
      }

   }
   
   static function getDefaultThemeId() {
      global $DB;
      
      $result = $DB->query("SELECT id FROM glpi_plugin_themes_themes WHERE active_theme = 1");
      $return = $DB->fetch_assoc($result);
      return $return['id'];
      
   }
    
   static function unzipThemes($zipPath, $zipName) {
   
      $zipExplode = explode(".",$zipName);
      $themeName = $zipExplode[0];
      $themeFolder = GLPI_PLUGIN_DOC_DIR."/themes/".$themeName;

      if (strpbrk($themeName, "\\/?%*:|\"<>") === FALSE) {
      
         $zip = new ZipArchive;
         $res = $zip->open($zipPath.$zipName);
         
         if ($res === TRUE) {

            if (!is_dir($themeFolder)) {
               mkdir($themeFolder, 0777, true);
            }

            $zip->extractTo($themeFolder);
            $zip->close();
            return true;
         } else {
            return 'invalid_zip_file';
         }   
      } else {
         return 'invalid_name';
      }
   
  
   }

   static function installThemes($zipPath, $zipName) {
      global $DB;
 
      $zipExplode = explode(".",$zipName);
      $themeName = $zipExplode[0];
 
      self::unzipThemes($zipPath,$zipName);
      
      $themeFolder = GLPI_PLUGIN_DOC_DIR."/themes/".$themeName;
      
      if (is_dir($themeFolder)) {
      
         $xmlStr = file_get_contents($themeFolder.'/theme.xml');
         if(!$xmlStr) {
            return 'invalid_xml';
         }
         $xmlObj = simplexml_load_string($xmlStr);
         $arrXml = self::objectsIntoArray($xmlObj);

         $values = array();
         
         $values['author'] = $arrXml['author'];
         $values['name'] = $arrXml['name'];
         $values['url'] = $arrXml['url'];
         $values['mail'] = ($arrXml['mail']) ? $arrXml['mail'] : '';
         $values['js'] = ($arrXml['js']) ? 1 : 0;
         $values['version'] = $arrXml['version'];
         
         $newTheme = new PluginThemesTheme();
         $newTheme->add($values);
         return true;
         
      } else {
         return 'invalid_theme_folder';
      }
      
      return false;
            
   }
   
   static function objectsIntoArray($arrObjData, $arrSkipIndices = array()) {
      $arrData = array();

      // if input is object, convert into array
      if (is_object($arrObjData)) {
         $arrObjData = get_object_vars($arrObjData);
      }

      if (is_array($arrObjData)) {
         foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
      }
      return $arrData;
   }
   
   static function install() {
      global $DB;
      
      /*** Create themes upload dir ***/
      if (!is_dir(PLUGIN_THEMES_UPLOAD_DIR)) {
         if (!@mkdir(PLUGIN_THEMES_UPLOAD_DIR, 0777, true)) {
            /*** Create default themes ***/   
            return array(
               'success' => false,
               'msg' => "Can't create folder " . PLUGIN_THEMES_UPLOAD_DIR
            );
         }
      }
      
      if (!TableExists("glpi_plugin_themes_themes")) {
         $result = $DB->runFile(GLPI_ROOT ."/plugins/themes/sql/themes.sql");
      } else {
         $result = true;
      }
      
      if (!TableExists("glpi_plugin_themes_per_user")) {
         $result = $DB->runFile(GLPI_ROOT ."/plugins/themes/sql/users.sql");
      } else {
         $result = true;
      }
      

      /*** Fake installation of the default glpi theme ***/
      $defaultGlpi = new PluginThemesTheme();
      $resultDefaultTheme = $defaultGlpi->find('name = "GLPI"');
      if(count($resultDefaultTheme) === 0) {
         $values = array(
            'name' => 'GLPI',
            'author' => 'GLPI Team',
            'mail' => '',
            'url' => 'http://www.glpi-project.org/',
            'active_theme' => '1',
            'version' => '1.0',
            'js' => '1'
         );
         $defaultGlpi->add($values);
      }
      
      /*** Installation of the default new theme ***/
      if (!is_dir(PLUGIN_THEMES_UPLOAD_DIR.'/BlackGLPI')) {
         self::installThemes(GLPI_ROOT."/plugins/themes/","BlackGLPI.zip");
      }
            
      if(!$result)
         return array('success' => false, 'msg' => 'Installation Error');
      else
         return array('success' => true);
   }
   
   static function uninstall() {
      global $DB;
      
      if (is_dir(PLUGIN_THEMES_UPLOAD_DIR)) {
         Toolbox::deleteDir(PLUGIN_THEMES_UPLOAD_DIR);
      }
      if(TableExists("glpi_plugin_themes_themes")) {
         $result = $DB->query("DROP TABLE IF EXISTS `glpi_plugin_themes_themes`;");
      } else {
         $result = true;
      }
      if(TableExists("glpi_plugin_themes_per_user")) {
         $result = $DB->query("DROP TABLE IF EXISTS `glpi_plugin_themes_per_user`;");
      } else {
         $result = true;
      }      
      if(!$result)
         return array('success' => false, 'msg' => 'Uninstallation Error');
      else
         return array('success' => true);
   }
   
   static function showAllThemes() {
      global $DB, $CFG_GLPI;
      
      $t = new PluginThemesTheme();
      $allThemes = $t->find();
      
      echo '<table class="tab_glpi" style="margin-bottom:20px;">
               <tr>
                  <td>
                     <img src="../../../pics/warning.png">
                  </td>
                  <td class="icon_consol">
                     <span class="b">'.__('Setting a theme as default will make this theme visible by all users. If you want to use a theme for your own user, please use your preferences settings.', 'themes').'</span>
                  </td>
               </tr>
            </tbody></table>';
      
      echo "<table class='tab_cadrehov' style='margin-bottom:20px;'>";
      
      echo "<tr>
               <th colspan='6'>".__('All themes available', 'themes')."</th>
            </tr>";
            
      echo "<tr>
               <th>".__('Name', 'themes')."</th>
               <th>".__('Version', 'themes')."</th>
               <th>".__('Author', 'themes')."</th>
               <th>".__('Need javascript', 'themes')."</th>
               <th>".__('Screenshot', 'themes')."</th>";
            
      if(PluginThemesTheme::canCreate()) {
         echo "<th>Action</th>";
      }
         
      echo "</tr>";
            
      foreach($allThemes as $themeDetails) {
         echo "<tr class='tab_bg_1'>";

         /*** Name and Url ***/
         echo "<td class='center'>";
         if(empty($themeDetails['url'])) {
            echo $themeDetails['name'];
         } else {
            echo "<a title='".__('Go to the theme website', 'themes').
                 "' href='".$themeDetails['url'];
            echo "'>".$themeDetails['name']."</a>";
         }
         echo "</td>";

         /*** version ***/
         echo "<td class='center'>".$themeDetails['version']."</td>";

         /*** author and mail ***/
         echo "<td class='center'>";
         if(empty($themeDetails['mail'])) {
            echo $themeDetails['author'];
         } else {
            echo "<a title='".__('Contact the author', 'themes').
                 "' href='mailto:".$themeDetails['mail'];
            echo "'>".$themeDetails['author']."</a>";
         }
         echo "</td>";

         /*** javascript ***/
         echo "<td class='center'>";
         if($themeDetails['js']) {
            echo __('Yes', 'themes');
         } else {
            echo __('No', 'themes');
         }
         echo "</td>";

         /*** preview ***/
         echo "<td class='center'>";

         if($themeDetails['name'] == "GLPI") {
            $screenshotPath = $CFG_GLPI['root_doc'].'/plugins/themes/img/defaultscreenshot.jpg';
            $screenshotFile = GLPI_ROOT.'/plugins/themes/img/defaultscreenshot.jpg';
         } else {
            $screenshotFile = PLUGIN_THEMES_UPLOAD_DIR.'/'.
               $themeDetails['name'].'/screenshot.jpg';

            $screenshotPath = $CFG_GLPI['root_doc']."/plugins/themes/front/getfile.php?theme_id=".
               $themeDetails['id']."&type=img&file=screenshot.jpg";
         }

         if(is_file($screenshotFile)) {
            echo "<a target='_blank' href='".$screenshotPath."'>
                  <img src='".$screenshotPath."' height='100' /></a>";
         } else {
            echo __('No screenshot available', 'themes');
         }
         echo "</td>";

         if(PluginThemesTheme::canCreate()) {
            /*** Make this theme the default one ***/
            echo "<td class='center'>";
            if($themeDetails['active_theme'] == 0) {
               echo "<a href='?activate=".$themeDetails['id']."'>".
                    __('Set as default theme', 'themes')."</a><br /><br />";
            }
            if($themeDetails['name'] != "GLPI") {
               echo "<a href='?delete=".$themeDetails['id']."'>".
                  __('Delete this theme', 'themes')."</a>";
            }
            echo "</td>";               
         }

         echo "</tr>";
      }

      echo "</table>";
     
      echo "<form method='post' action='' enctype=\"multipart/form-data\">";
      echo "<table class='tab_cadrehov'><tr><th>";
      echo __('Add a new theme', 'themes');
      echo "</th></tr><tr><td class='center'>";

      echo "<input type='file' name='themeFile' />&nbsp;";
      echo "<input type='submit' class='submit' name='add_theme' value='".__('Choose', 'themes')."' />";


      echo "</td></tr></table>";
      Html::closeForm();
   }

   function getFileContent($type, $file) {

      // Security check
      $tmpfile = str_replace(GLPI_DOC_DIR, "", $file);
      if (strstr($tmpfile,"../") || strstr($tmpfile,"..\\")) {
         Event::log($file, "sendFile", 1, "security", $_SESSION["glpiname"]." try to get a non standard file.");
         die("Security attack !!!");
      }

      $themePath = PLUGIN_THEMES_UPLOAD_DIR.'/'.$this->getName().'/';
      switch($type) {
         case 'img':
            header ("Content-type: image/jpeg");
            $img = imagecreatefromjpeg($themePath.$file);
            imagejpeg($img);
            break;
         case 'css':
            header ("Content-type: text/css");
            $css = file_get_contents($themePath.$file);
            echo $css;
            break;
         case 'js':
            header("Content-type: text/javascript");
            $css = file_get_contents($themePath.$file);
            echo $css;
            break;
      }
      
   }
  
   static function resetActiveTheme() {
      global $DB;
      
      $DB->query('UPDATE glpi_plugin_themes_themes SET active_theme = 0');
   }
   
}
?>