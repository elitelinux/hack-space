<?php
/*
 * @version $Id: install.php 22918 2014-04-16 13:37:20Z moyo $
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


define('GLPI_ROOT', realpath('..'));

include_once (GLPI_ROOT . "/inc/autoload.function.php");
include_once (GLPI_ROOT . "/inc/db.function.php");
Config::detectRootDoc();

//Print a correct  Html header for application
function header_html($etape) {

   // Send UTF8 Headers
   header("Content-Type: text/html; charset=UTF-8");

   echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'
          'http://www.w3.org/TR/html4/loose.dtd'>";
   echo "<html>";
   echo "<head>";
   echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
   echo "<meta http-equiv='Content-Script-Type' content='text/javascript'> ";
   echo "<meta http-equiv='Content-Style-Type' content='text/css'> ";
   echo "<meta http-equiv='Content-Language' content='fr'> ";
   echo "<meta name='generator' content=''>";
   echo "<meta name='DC.Language' content='fr' scheme='RFC1766'>";
   echo "<title>Setup GLPI</title>";

   // CSS
   echo "<link rel='stylesheet' href='../css/style_install.css' type='text/css' media='screen'>";
   echo "</head>";
   echo "<body>";
   echo "<div id='principal'>";
   echo "<div id='bloc'>";
   echo "<div id='logo_bloc'></div>";
   echo "<h2>GLPI SETUP</h2>";
   echo "<br><h3>". $etape ."</h3>";
}


//Display a great footer.
function footer_html() {
   echo "</div></div></body></html>";
}


// choose language
function choose_language() {

   echo "<form action='install.php' method='post'>";
   echo "<p class='center'>";

   Dropdown::showLanguages("language", array('value' => "en_GB"));
   echo "</p>";
   echo "";
   echo "<p class='submit'><input type='hidden' name='install' value='lang_select'>";
   echo "<input type='submit' name='submit' class='submit' value='OK'></p>";
   Html::closeForm();
}




function acceptLicense() {

   echo "<div class='center'>";
   echo "<textarea id='license' cols='85' rows='10' readonly='readonly'>";
   readfile("../COPYING.txt");
   echo "</textarea>";

   echo "<br><a target='_blank' href='http://www.gnu.org/licenses/old-licenses/gpl-2.0-translations.html'>".
         __('Unofficial translations are also available')."</a>";

   echo "<form action='install.php' method='post'>";
   echo "<p>";
   echo "<input type='radio' name='install' id='agree' value='License'><label for= agree >";
   echo __('I have read and ACCEPT the terms of the license written above.')." </label></p>";

   echo "<br>";
   echo "<input type='radio' name='install' value='lang_select' id='disagree' checked='checked'>";
   echo " <label for='disagree'>";
   echo __('I have read and DO NOT ACCEPT the terms of the license written above')." </label>";
   echo "<p><input type='submit' name='submit' class='submit' value=\"".__s('Continue')."\"></p>";
   Html::closeForm();
   echo "</div>";
}


//confirm install form
function step0() {

   echo "<h3>".__('Installation or update of GLPI')."</h3>";
   echo "<p>".__s("Choose 'Install' for a completely new installation of GLPI.")."</p>";
   echo "<p> ".__s("Select 'Upgrade' to update your version of GLPI from an earlier version")."</p>";
   echo "<form action='install.php' method='post'>";
   echo "<input type='hidden' name='update' value='no'>";
   echo "<p class='submit'><input type='hidden' name='install' value='Etape_0'>";
   echo "<input type='submit' name='submit' class='submit' value=\"".__('Install')."\"></p>";
   Html::closeForm();

   echo "<form action='install.php' method='post'>";
   echo "<input type='hidden' name='update' value='yes'>";
   echo "<p class='submit'><input type='hidden' name='install' value='Etape_0'>";
   echo "<input type='submit' name='submit' class='submit' value=\"".__('Upgrade')."\"></p>";
   Html::closeForm();
}


//Step 1 checking some compatibilty issue and some write tests.
function step1($update) {
   global $CFG_GLPI;

   $error = 0;
   echo "<h3>".__s('Checking of the compatibility of your environment with the execution of GLPI').
        "</h3>";
   echo "<table class='tab_check'>";

   $error = Toolbox::commonCheckForUseGLPI();

   echo "</table>";
   switch ($error) {
      case 0 :
         echo "<form action='install.php' method='post'>";
         echo "<input type='hidden' name='update' value='". $update."'>";
         echo "<input type='hidden' name='language' value='". $_SESSION['glpilanguage']."'>";
         echo "<p class='submit'><input type='hidden' name='install' value='Etape_1'>";
         echo "<input type='submit' name='submit' class='submit' value=\"".__('Continue')."\">";
         echo "</p>";
         Html::closeForm();
         break;

      case 1 :
         echo "<h3>".__('Do you want to continue?')."</h3>";
         echo "<div class='submit'><form action='install.php' method='post' class='inline'>";
         echo "<input type='hidden' name='install' value='Etape_1'>";
         echo "<input type='hidden' name='update' value='". $update."'>";
         echo "<input type='hidden' name='language' value='". $_SESSION['glpilanguage']."'>";
         echo "<input type='submit' name='submit' class='submit' value=\"".__('Continue')."\">";
         Html::closeForm();
         echo "&nbsp;&nbsp;";

         echo "<form action='install.php' method='post' class='inline'>";
         echo "<input type='hidden' name='update' value='". $update."'>";
         echo "<input type='hidden' name='language' value='". $_SESSION['glpilanguage']."'>";
         echo "<input type='hidden' name='install' value='Etape_0'>";
         echo "<input type='submit' name='submit' class='submit' value=\"".__('Try again')."\">";
         Html::closeForm();
         echo "</div>";
         break;

      case 2 :
         echo "<h3>".__('Do you want to continue?')."</h3>";
         echo "<form action='install.php' method='post'>";
         echo "<input type='hidden' name='update' value='".$update."'>";
         echo "<p class='submit'><input type='hidden' name='install' value='Etape_0'>";
         echo "<input type='submit' name='submit' class='submit' value=\"".__('Try again')."\">";
         echo "</p>";
         Html::closeForm();
         break;
   }

}


//step 2 import mysql settings.
function step2($update) {

   echo "<h3>".__('Database connection setup')."</h3>";
   echo "<form action='install.php' method='post'>";
   echo "<input type='hidden' name='update' value='".$update."'>";
   echo "<fieldset><legend>".__('Database connection parameters')."</legend>";
   echo "<p><label class='block'>".__('Mysql server') ." </label>";
   echo "<input type='text' name='db_host'><p>";
   echo "<p><label class='block'>".__('Mysql user') ." </label>";
   echo "<input type='text' name='db_user'></p>";
   echo "<p><label class='block'>".__('Mysql password')." </label>";
   echo "<input type='password' name='db_pass'></p></fieldset>";
   echo "<input type='hidden' name='install' value='Etape_2'>";
   echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
         __('Continue')."'></p>";
   Html::closeForm();
}


//step 3 test mysql settings and select database.
function step3($host, $user, $password, $update) {

   error_reporting(16);
   echo "<h3>".__('Test of the connection at the database')."</h3>";
   $link = new mysqli($host, $user, $password);

   if (($link->connect_error) || empty($host) || empty($user)) {
      echo "<p>".__("Can't connect to the database")."\n <br>".
           sprintf(__('The server answered: %s'), $link->connect_error)."</p>";

      if (empty($host) || empty($user)) {
         echo "<p>".__('The server or/and user field is empty')."</p>";
      }

      echo "<form action='install.php' method='post'>";
      echo "<input type='hidden' name='update' value='".$update."'>";
      echo "<input type='hidden' name='install' value='Etape_1'>";
      echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
            __s('Back')."'></p>";
      Html::closeForm();

   } else {
      $_SESSION['db_access'] = array('host'     => $host,
                                     'user'     => $user,
                                     'password' => $password);
      echo  "<h3>".__('Database connection successful')."</h3>";

      if ($update == "no") {
         echo "<p>".__('Please select a database:')."</p>";
         echo "<form action='install.php' method='post'>";

         $DB_list = $link->query("SHOW DATABASES");
         while ($row = $DB_list->fetch_array()) {
            if (!in_array($row['Database'],array("information_schema",
                                               "mysql",
                                               "performance_schema") )) {
               echo "<p><input type='radio' name='databasename' value='". $row['Database']."'>";
               echo $row['Database'].".</p>";
            }
         }

         echo "<p><input type='radio' name='databasename' value='0'>";
         _e('Create a new database or use an existing one:');
         echo "&nbsp;<input type='text' name='newdatabasename'></p>";
         /*
         echo "<input type='hidden' name='db_host' value='". $host ."'>";
         echo "<input type='hidden' name='db_user' value='". $user ."'>";
         echo "<input type='hidden' name='db_pass' value='". rawurlencode($password) ."'>";
         */
         echo "<input type='hidden' name='install' value='Etape_3'>";
         echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
               __('Continue')."'></p>";
         $link->close();
         Html::closeForm();

      } else if ($update == "yes") {
         echo "<p>".__('Please select the database to update:')."</p>";
         echo "<form action='install.php' method='post'>";

         $DB_list = $link->query("SHOW DATABASES");
         while ($row = $DB_list->fetch_array()) {
            echo "<p><input type='radio' name='databasename' value='". $row['Database']."'>";
            echo $row['Database'].".</p>";
         }

         /*
         echo "<input type='hidden' name='db_host' value='". $host ."'>";
         echo "<input type='hidden' name='db_user' value='". $user ."'>";
         echo "<input type='hidden' name='db_pass' value='". rawurlencode($password) ."'>";
         */
         echo "<input type='hidden' name='install' value='update_1'>";
         echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
                __('Continue')."'></p>";
         $link->close();
         Html::closeForm();
      }

   }
}


//Step 4 Create and fill database.
function step4 ($databasename, $newdatabasename) {

   $host     = $_SESSION['db_access']['host'];
   $user     = $_SESSION['db_access']['user'];
   $password = $_SESSION['db_access']['password'];

   //display the form to return to the previous step.
   echo "<h3>".__('Initialization of the database')."</h3>";


   function prev_form($host, $user, $password) {

      echo "<br><form action='install.php' method='post'>";
      echo "<input type='hidden' name='db_host' value='". $host ."'>";
      echo "<input type='hidden' name='db_user' value='". $user ."'>";
      echo " <input type='hidden' name='db_pass' value='". rawurlencode($password) ."'>";
      echo "<input type='hidden' name='update' value='no'>";
      echo "<input type='hidden' name='install' value='Etape_2'>";
      echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
            __s('Back')."'></p>";
      Html::closeForm();
   }


   //Display the form to go to the next page
   function next_form() {

      echo "<br><form action='install.php' method='post'>";
      echo "<input type='hidden' name='install' value='Etape_4'>";
      echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
             __('Continue')."'></p>";
      Html::closeForm();
   }


   //Fill the database
   function fill_db() {
      global $CFG_GLPI;

      //include_once (GLPI_ROOT . "/inc/dbmysql.class.php");
      include_once (GLPI_CONFIG_DIR . "/config_db.php");

      $DB = new DB();
      if (!$DB->runFile(GLPI_ROOT ."/install/mysql/glpi-0.84.6-empty.sql")) {
         echo "Errors occurred inserting default database";
      }

      // update default language
      $query = "UPDATE `glpi_configs`
                SET `language` = '".$_SESSION["glpilanguage"]."'";
      $DB->queryOrDie($query, "4203");

      $query = "UPDATE `glpi_users`
                SET `language` = NULL";
      $DB->queryOrDie($query, "4203");
   }

   $link = new mysqli($host, $user, $password);

   $databasename = $link->real_escape_string($databasename);
   $newdatabasename = $link->real_escape_string($newdatabasename);
   
   if (!empty($databasename)) { // use db already created
      $DB_selected = $link->select_db($databasename);

      if (!$DB_selected) {
         _e('Impossible to use the database:');
         echo "<br>".sprintf(__('The server answered: %s'), $link->error);
         prev_form($host, $user, $password);

      } else {
         if (create_conn_file($host,$user,$password,$databasename)) {
            fill_db();
            echo "<p>".__('OK - database was initialized')."</p>";
            next_form();

         } else { // can't create config_db file
            echo "<p>".__('Impossible to write the database setup file')."</p>";
            prev_form($host, $user, $password);
         }
      }

   } else if (!empty($newdatabasename)) { // create new db
      // Try to connect
      if ($link->select_db($newdatabasename)) {
         echo "<p>".__('Database created')."</p>";

         if (create_conn_file($host,$user,$password,$newdatabasename)) {
            fill_db();
            echo "<p>".__('OK - database was initialized')."</p>";
            next_form();

         } else { // can't create config_db file
            echo "<p>".__('Impossible to write the database setup file')."</p>";
            prev_form($host, $user, $password);
         }

      } else { // try to create the DB
         if ($link->query("CREATE DATABASE IF NOT EXISTS `".$newdatabasename."`")) {
            echo "<p>".__('Database created')."</p>";

            if ($link->select_db($newdatabasename)
                && create_conn_file($host,$user,$password,$newdatabasename)) {

               fill_db();
               echo "<p>".__('OK - database was initialized')."</p>";
               next_form();

            } else { // can't create config_db file
               echo "<p>".__('Impossible to write the database setup file')."</p>";
               prev_form($host, $user, $password);
            }

         } else { // can't create database
            echo __('Error in creating database!');
            echo "<br>".sprintf(__('The server answered: %s'), $link->error);
            prev_form($host, $user, $password);
         }
      }

   } else { // no db selected
      echo "<p>".__("You didn't select a database!"). "</p>";
      //prev_form();
      prev_form($host, $user, $password);
   }

   $link->close();

}


// finish installation
function step7() {
   global $CFG_GLPI;

   require_once (GLPI_ROOT . "/inc/dbmysql.class.php");
   require_once (GLPI_CONFIG_DIR . "/config_db.php");
   $DB = new DB();

   $query = "UPDATE `glpi_configs`
             SET `url_base` = '".$DB->escape(str_replace("/install/install.php", "", $_SERVER['HTTP_REFERER']))."'
             WHERE `id` = '1'";
   $DB->query($query);

   echo "<h2>".__('The installation is finished')."</h2>";
   echo "<p>".__('Default logins / passwords are:')."</p>";
   echo "<p><ul><li> ".__('glpi/glpi for the administrator account')."</li>";
   echo "<li>".__('tech/tech for the technician account')."</li>";
   echo "<li>".__('normal/normal for the normal account')."</li>";
   echo "<li>".__('post-only/postonly for the postonly account')."</li></ul></p>";
   echo "<p>".__('You can delete or modify these accounts as well as the initial data.')."</p>";
   echo "<p class='center'><a class='vsubmit' href='../index.php'>".__('Use GLPI');
   echo "</a></p>";
}


//Create the file config_db.php
// an fill it with user connections info.
function create_conn_file($host, $user, $password, $DBname) {
   global $CFG_GLPI;

   $DB_str = "<?php\n class DB extends DBmysql {
                \n var \$dbhost = '". $host ."';
                \n var \$dbuser 	= '". $user ."';
                \n var \$dbpassword= '". rawurlencode($password) ."';
                \n var \$dbdefault	= '". $DBname ."';
                \n } \n?>";

   return Toolbox::writeConfig('config_db.php', $DB_str);
}


function update1($DBname) {

   $host     = $_SESSION['db_access']['host'];
   $user     = $_SESSION['db_access']['user'];
   $password = $_SESSION['db_access']['password'];

   if (create_conn_file($host,$user,$password,$DBname) && !empty($DBname)) {
      $from_install = true;
      include(GLPI_ROOT ."/install/update.php");

   } else { // can't create config_db file
      _e("Can't create the database connection file, please verify file permissions.");
      echo "<h3>".__('Do you want to continue?')."</h3>";
      echo "<form action='install.php' method='post'>";
      echo "<input type='hidden' name='update' value='yes'>";
      echo "<p class='submit'><input type='hidden' name='install' value='Etape_0'>";
      echo "<input type='submit' name='submit' class='submit' value=\"".__('Continue')."\">";
      echo "</p>";
      Html::closeForm();
   }
}



//------------Start of install script---------------------------


// Use default session dir if not writable
if (is_writable(GLPI_SESSION_DIR)) {
   Session::setPath();
}

Session::start();
error_reporting(0); // we want to check system before affraid the user.

if (isset($_POST["language"])) {
   $_SESSION["glpilanguage"] = $_POST["language"];
}

Session::loadLanguage();

function checkConfigFile() {
   if (file_exists(GLPI_CONFIG_DIR . "/config_db.php")) {
      Html::redirect($CFG_GLPI['root_doc'] ."/index.php");
      die();
   }
}

if (!isset($_POST["install"])) {
   $_SESSION = array();
   checkConfigFile();
   header_html("Select your language");
   choose_language();

} else {

   // Check valid Referer :
   Toolbox::checkValidReferer();
   // Check CSRF: ensure nobody strap first page that checks if config file exists ...
   Session::checkCSRF($_POST);

   // DB clean
   if (isset($_POST["db_pass"])) {
      $_POST["db_pass"] = stripslashes($_POST["db_pass"]);
      $_POST["db_pass"] = rawurldecode($_POST["db_pass"]);
      $_POST["db_pass"] = stripslashes($_POST["db_pass"]);
   }

   switch ($_POST["install"]) {
      case "lang_select" : // lang ok, go accept licence
         checkConfigFile();
         header_html(__('License'));
         acceptLicense();
         break;

      case "License" : // licence  ok, go choose installation or Update
         checkConfigFile();
         header_html(__('Beginning of the installation'));
         step0();
         break;

      case "Etape_0" : // choice ok , go check system
         checkConfigFile();
         //TRANS %s is step number
         header_html(sprintf(__('Step %d'), 0));
         $_SESSION["Test_session_GLPI"] = 1;
         step1($_POST["update"]);
         break;

      case "Etape_1" : // check ok, go import mysql settings.
         checkConfigFile();
         // check system ok, we can use specific parameters for debug
         Toolbox::setDebugMode(Session::DEBUG_MODE, 0, 0, 1);

         header_html(sprintf(__('Step %d'), 1));
         step2($_POST["update"]);
         break;

      case "Etape_2" : // mysql settings ok, go test mysql settings and select database.
         checkConfigFile();
         header_html(sprintf(__('Step %d'), 2));
         step3($_POST["db_host"],$_POST["db_user"],$_POST["db_pass"],$_POST["update"]);
         break;

      case "Etape_3" : // Create and fill database
         checkConfigFile();
         header_html(sprintf(__('Step %d'), 3));
         if (empty($_POST["databasename"])) {
            $_POST["databasename"] = "";
         }
         if (empty($_POST["newdatabasename"])) {
            $_POST["newdatabasename"] = "";
         }
         step4($_POST["databasename"],
               $_POST["newdatabasename"]);
         break;

      case "Etape_4" : // finish installation
         header_html(sprintf(__('Step %d'), 4));
         step7();
         break;

      case "update_1" :
         if (empty($_POST["databasename"])) {
            $_POST["databasename"] = "";
         }
         update1($_POST["databasename"]);
         break;
   }
}
footer_html();

?>
