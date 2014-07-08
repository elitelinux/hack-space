<?php
/*
 * @version $Id: toolbox.class.php 22953 2014-04-25 15:08:33Z yllen $
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


// class Toolbox
class Toolbox {

   /**
    * Wrapper for get_magic_quotes_runtime
    *
    * @since version 0.83
    *
    * @return boolean
   **/
   static function get_magic_quotes_runtime() {

      // Deprecated function(8192): Function get_magic_quotes_runtime() is deprecated
      if (PHP_VERSION_ID < 50400) {
         return get_magic_quotes_runtime();
      }
      return 0;
   }


   /**
    * Wrapper for get_magic_quotes_gpc
    *
    * @since version 0.83
    *
    * @return boolean
   **/
   static function get_magic_quotes_gpc() {

      // Deprecated function(8192): Function get_magic_quotes_gpc() is deprecated
      if (PHP_VERSION_ID < 50400) {
         return get_magic_quotes_gpc();
      }
      return 0;
   }

   /**
    * Wrapper for max_input_vars
    *
    * @since version 0.84
    *
    * @return integer
   **/
   static function get_max_input_vars() {
      $max        = ini_get('max_input_vars');  // Security limit since PHP 5.3.9
      if (!$max) {
         $max = ini_get('suhosin.post.max_vars');  // Security limit from Suhosin
      }
      return $max;
   }

   /**
    * Convert first caracter in upper
    *
    * @since version 0.83
    *
    * @param $str string to change
    *
    * @return string changed
   **/
   static function ucfirst($str) {

      if ($str{0} >= "\xc3") {
         return (($str{1} >= "\xa0") ? ($str{0}.chr(ord($str{1})-32))
                                     : ($str{0}.$str{1})).substr($str,2);
      }
      return ucfirst($str);
    }


   /**
    * to underline shortcut letter
    *
    * @since version 0.83
    *
    * @param $str       string   from dico
    * @param $shortcut           letter of shortcut
    *
    * @return string
   **/

   static function shortcut($str, $shortcut) {

      $pos = self::strpos(self::strtolower($str), $shortcut);
      if ($pos !== false) {
         return self::substr($str, 0, $pos).
                "<u>". self::substr($str, $pos,1)."</u>".
                self::substr($str, $pos+1);
      }
      return $str;
   }


   /**
    * substr function for utf8 string
    *
    * @param $str       string   string
    * @param $tofound   string   string to found
    * @param $offset    integer  The search offset. If it is not specified, 0 is used.
    *                            (default 0)
    *
    * @return substring
   **/
   static function strpos($str, $tofound, $offset=0) {
      return mb_strpos($str, $tofound, $offset, "UTF-8");
   }



   /**
    *  Replace str_pad()
    *  who bug with utf8
    *
    * @param $input        string   input string
    * @param $pad_length   integer  padding length
    * @param $pad_string   string   padding string (default '')
    * @param $pad_type     integer  padding type (default STR_PAD_RIGHT)
    *
    * @return string
   **/
   static function str_pad($input, $pad_length, $pad_string=" ", $pad_type=STR_PAD_RIGHT) {

       $diff = (strlen($input) - self::strlen($input));
       return str_pad($input, $pad_length+$diff, $pad_string, $pad_type);
   }


   /**
    * strlen function for utf8 string
    *
    * @param $str string
    *
    * @return length of the string
   **/
   static function strlen($str) {
      return mb_strlen($str, "UTF-8");
   }


   /**
    * substr function for utf8 string
    *
    * @param $str       string
    * @param $start     integer  start of the result substring
    * @param $length    integer  The maximum length of the returned string if > 0 (default -1)
    *
    * @return substring
   **/
   static function substr($str, $start, $length=-1) {

      if ($length == -1) {
         $length = self::strlen($str)-$start;
      }
      return mb_substr($str, $start, $length, "UTF-8");
   }


   /**
    * strtolower function for utf8 string
    *
    * @param $str string
    *
    * @return lower case string
   **/
   static function strtolower($str) {
      return mb_strtolower($str, "UTF-8");
   }


   /**
    * strtoupper function for utf8 string
    *
    * @param $str string
    *
    * @return upper case string
   **/
   static function strtoupper($str) {
      return mb_strtoupper($str, "UTF-8");
   }


   /**
    * Is a string seems to be UTF-8 one ?
    *
    * @param $str string   string to analyze
    *
    * @return  boolean
   **/
   static function seems_utf8($str) {
      return mb_check_encoding($str, "UTF-8");
   }


   /**
    * Encode string to UTF-8
    *
    * @param $string       string   string to convert
    * @param $from_charset string   original charset (if 'auto' try to autodetect)
    *                               (default "ISO-8859-1")
    *
    * @return utf8 string
   **/
   static function encodeInUtf8($string, $from_charset="ISO-8859-1") {

      if (strcmp($from_charset,"auto") == 0) {
         $from_charset = mb_detect_encoding($string);
      }
      return mb_convert_encoding($string, "UTF-8", $from_charset);
   }


   /**
    * Decode string from UTF-8 to specified charset
    *
    * @param $string       string   string to convert
    * @param $to_charset   string   destination charset (default "ISO-8859-1")
    *
    * @return converted string
   **/
   static function decodeFromUtf8($string, $to_charset="ISO-8859-1") {
      return mb_convert_encoding($string, $to_charset, "UTF-8");
   }


   /**
    * Encrypt a string
    *
    * @param $string    string to encrypt
    * @param $key       string key used to encrypt
    *
    * @return encrypted string
   **/
   static function encrypt($string, $key) {

     $result = '';
     for($i=0 ; $i<strlen($string) ; $i++) {
       $char    = substr($string, $i, 1);
       $keychar = substr($key, ($i % strlen($key))-1, 1);
       $char    = chr(ord($char)+ord($keychar));
       $result .= $char;
     }

     return base64_encode($result);
   }


   /**
    * Decrypt a string
    *
    * @param $string    string to decrypt
    * @param $key       string key used to decrypt
    *
    * @return decrypted string
   **/
   static function decrypt($string, $key) {

     $result = '';
     $string = base64_decode($string);

     for($i=0 ; $i<strlen($string) ; $i++) {
       $char    = substr($string, $i, 1);
       $keychar = substr($key, ($i % strlen($key))-1, 1);
       $char    = chr(ord($char)-ord($keychar));
       $result .= $char;
     }

     return Toolbox::unclean_cross_side_scripting_deep($result);
   }


   /**
    * Prevent from XSS
    * Clean code
    *
    * @param $value array or string: item to prevent (array or string)
    *
    * @return clean item
    *
    * @see unclean_cross_side_scripting_deep*
   **/
   static function clean_cross_side_scripting_deep($value) {

      $in  = array('<', '>');
      $out = array('&lt;', '&gt;');

      $value = is_array($value) ? array_map(array(__CLASS__, 'clean_cross_side_scripting_deep'),
                                            $value)
                                : (is_null($value)
                                   ? NULL : (is_resource($value)
                                             ? $value : str_replace($in,$out,$value)));

      return $value;
   }


   /**
    *  Invert fonction from clean_cross_side_scripting_deep
    *
    * @param $value  array or string   item to unclean from clean_cross_side_scripting_deep
    *
    * @return unclean item
    *
    * @see clean_cross_side_scripting_deep
   **/
   static function unclean_cross_side_scripting_deep($value) {

      $in  = array('<', '>');
      $out = array('&lt;', '&gt;');

      $value = is_array($value) ? array_map(array(__CLASS__, 'unclean_cross_side_scripting_deep'),
                                            $value)
                                : (is_null($value)
                                   ? NULL : (is_resource($value)
                                             ? $value : str_replace($out,$in,$value)));

      return $value;
   }


   /**
    *  Invert fonction from clean_cross_side_scripting_deep to display HTML striping XSS code
    *
    * @since version 0.83.3
    *
    * @param $value array or string: item to unclean from clean_cross_side_scripting_deep
    *
    * @return unclean item
    *
    * @see clean_cross_side_scripting_deep
   **/
   static function unclean_html_cross_side_scripting_deep($value) {

      $in  = array('<', '>');
      $out = array('&lt;', '&gt;');

      $value = is_array($value) ? array_map(array(__CLASS__, 'unclean_html_cross_side_scripting_deep'),
                                            $value)
                                : (is_null($value)
                                   ? NULL : (is_resource($value)
                                             ? $value : str_replace($out,$in,$value)));

      include_once(GLPI_HTMLAWED);

      // revert unclean inside <pre>
      $count = preg_match_all('/(<pre[^>]*>)(.*?)(<\/pre>)/is', $value, $matches);
      for ($i = 0; $i < $count; ++$i) {
         $complete       = $matches[0][$i];
         $cleaned        = self::clean_cross_side_scripting_deep($matches[2][$i]);
         $cleancomplete  = $matches[1][$i].$cleaned.$matches[3][$i];;
         $value          = str_replace($complete, $cleancomplete, $value);
      }

      $config             = array('safe'=>1);
      $config["elements"] = "*+iframe";
      $config["direct_list_nest"] = 1;
      $value              = htmLawed($value, $config);

      return $value;
   }


   /**
    * Log in 'php-errors' all args
   **/
   static function logDebug() {
      static $tps = 0;

      $msg = "";
      foreach (func_get_args() as $arg) {
         if (is_array($arg) || is_object($arg)) {
            $msg .= ' ' . print_r($arg, true);
         } else if (is_null($arg)) {
            $msg .= ' NULL';
         } else if (is_bool($arg)) {
            $msg .= ' '.($arg ? 'true' : 'false');
         } else {
            $msg .= ' ' . $arg;
         }
      }

      if ($tps && function_exists('memory_get_usage')) {
         $msg .= ' ('.number_format(microtime(true)-$tps,3).'", '.
                 number_format(memory_get_usage()/1024/1024,2).'Mio)';
      }

      $tps = microtime(true);
      self::logInFile('php-errors', $msg."\n",true);
   }


   /**
    * Log a message in log file
    *
    * @param $name   string   name of the log file
    * @param $text   string   text to log
    * @param $force  boolean  force log in file not seeing use_log_in_files config (false by default)
   **/
   static function logInFile($name, $text, $force=false) {
      global $CFG_GLPI;

      $user = '';
      if (function_exists('Session::getLoginUserID')) {
         $user = " [".Session::getLoginUserID().'@'.php_uname('n')."]";
      }

      $ok = true;
      if ((isset($CFG_GLPI["use_log_in_files"]) && $CFG_GLPI["use_log_in_files"])
          || $force) {
         $ok = error_log(date("Y-m-d H:i:s")."$user\n".$text, 3, GLPI_LOG_DIR."/".$name.".log");
      }

      if (isset($_SESSION['glpi_use_mode'])
          && ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE)
          && isCommandLine()) {
         fwrite(STDERR, $text);
      }
      return $ok;
   }


   /**
    * Specific error handler in Normal mode
    *
    * @param $errno     integer  level of the error raised.
    * @param $errmsg    string   error message.
    * @param $filename  string   filename that the error was raised in.
    * @param $linenum   integer  line number the error was raised at.
    * @param $vars      array    that points to the active symbol table at the point the error occurred.
   **/
   static function userErrorHandlerNormal($errno, $errmsg, $filename, $linenum, $vars) {

      // Date et heure de l'erreur
      $errortype = array(E_ERROR           => 'Error',
                         E_WARNING         => 'Warning',
                         E_PARSE           => 'Parsing Error',
                         E_NOTICE          => 'Notice',
                         E_CORE_ERROR      => 'Core Error',
                         E_CORE_WARNING    => 'Core Warning',
                         E_COMPILE_ERROR   => 'Compile Error',
                         E_COMPILE_WARNING => 'Compile Warning',
                         E_USER_ERROR      => 'User Error',
                         E_USER_WARNING    => 'User Warning',
                         E_USER_NOTICE     => 'User Notice',
                         E_STRICT          => 'Runtime Notice',
                         // Need php 5.2.0
                         4096 /*E_RECOVERABLE_ERROR*/  => 'Catchable Fatal Error',
                         // Need php 5.3.0
                         8192 /* E_DEPRECATED */       => 'Deprecated function',
                         16384 /* E_USER_DEPRECATED */ => 'User deprecated function');
      // Les niveaux qui seront enregistrés
      $user_errors = array(E_USER_ERROR, E_USER_NOTICE, E_USER_WARNING);

      $err = $errortype[$errno] . "($errno): $errmsg\n";
      if (in_array($errno, $user_errors)) {
         $err .= "Variables:".wddx_serialize_value($vars, "Variables")."\n";
      }

      if (function_exists("debug_backtrace")) {
         $err   .= "Backtrace :\n";
         $traces = debug_backtrace();
         foreach ($traces as $trace) {
            if (isset($trace["file"]) && isset($trace["line"])) {
               $err .= $trace["file"] . ":" . $trace["line"] . "\t\t"
                       . (isset($trace["class"]) ? $trace["class"] : "")
                       . (isset($trace["type"]) ? $trace["type"] : "")
                       . (isset($trace["function"]) ? $trace["function"]."()" : ""). "\n";
            }
         }

      } else {
         $err .= "Script: $filename, Line: $linenum\n" ;
      }

      // sauvegarde de l'erreur, et mail si c'est critique
      self::logInFile("php-errors", $err."\n");

      return $errortype[$errno];
   }


   /**
    * Specific error handler in Debug mode
    *
    * @param $errno     integer  level of the error raised.
    * @param $errmsg    string   error message.
    * @param $filename  string   filename that the error was raised in.
    * @param $linenum   integer  line number the error was raised at.
    * @param $vars      array    that points to the active symbol table at the point the error occurred.
   **/
   static function userErrorHandlerDebug($errno, $errmsg, $filename, $linenum, $vars) {

      // For file record
      $type = self::userErrorHandlerNormal($errno, $errmsg, $filename, $linenum, $vars);

      // Display
      if (!isCommandLine()) {
         echo '<div style="position:fload-left; background-color:red; z-index:10000">'.
              '<span class="b">PHP '.$type.': </span>';
         echo $errmsg.' in '.$filename.' at line '.$linenum.'</div>';
      } else {
         echo 'PHP '.$type.': '.$errmsg.' in '.$filename.' at line '.$linenum."\n";
      }
   }


   /**
    * Switch error mode for GLPI
    *
    * @param $mode         Integer  from Session::*_MODE (default NULL)
    * @param $debug_sql    Boolean  (default NULL)
    * @param $debug_vars   Boolean  (default NULL)
    * @param $log_in_files Boolean  (default NULL)
    *
    * @since version 0.84
   **/
   static function setDebugMode($mode=NULL, $debug_sql=NULL, $debug_vars=NULL, $log_in_files=NULL) {
      global $CFG_GLPI;

      if (isset($mode)) {
         $_SESSION['glpi_use_mode'] = $mode;
      }
      if (isset($debug_sql)) {
         $CFG_GLPI['debug_sql'] = $debug_sql;
      }
      if (isset($debug_vars)) {
         $CFG_GLPI['debug_vars'] = $debug_vars;
      }
      if (isset($log_in_files)) {
         $CFG_GLPI['use_log_in_files'] = $log_in_files;
      }

      // If debug mode activated : display some information
      if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
         // display_errors only need for for E_ERROR, E_PARSE, ... which cannot be catched
         // Recommended development settings
         ini_set('display_errors', 'On');
         error_reporting(E_ALL | E_STRICT);
         set_error_handler(array('Toolbox','userErrorHandlerDebug'));

      } else {
         // Recommended production settings
         ini_set('display_errors', 'Off');
         error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
         set_error_handler(array('Toolbox', 'userErrorHandlerNormal'));
      }
   }


   /**
    * Send a file (not a document) to the navigator
    * See Document->send();
    *
    * @param $file      string: storage filename
    * @param $filename  string: file title
    *
    * @return nothing
   **/
   static function sendFile($file, $filename) {

      // Test securite : document in DOC_DIR
      $tmpfile = str_replace(GLPI_DOC_DIR, "", $file);

      if (strstr($tmpfile,"../") || strstr($tmpfile,"..\\")) {
         Event::log($file, "sendFile", 1, "security",
                    $_SESSION["glpiname"]." try to get a non standard file.");
         die("Security attack!!!");
      }

      if (!file_exists($file)) {
         die("Error file $file does not exist");
      }

      $splitter = explode("/", $file);
      $mime     = "application/octetstream";

      if (preg_match('/\.(...)$/', $file, $regs)) {
         switch ($regs[1]) {
            case "sql" :
               $mime = "text/x-sql";
               break;

            case "xml" :
               $mime = "text/xml";
               break;

            case "csv" :
               $mime = "text/csv";
               break;

            case "svg" :
               $mime = "image/svg+xml";
               break;

            case "png" :
               $mime = "image/png";
               break;
         }
      }

      // Now send the file with header() magic
      header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
      header('Pragma: private'); /// IE BUG + SSL
      header('Cache-control: private, must-revalidate'); /// IE BUG + SSL
      header("Content-disposition: filename=\"$filename\"");
      header("Content-type: ".$mime);

      readfile($file) or die ("Error opening file $file");
   }


   /**
    *  Add slash for variable & array
    *
    * @param $value array or string: value to add slashes (array or string)
    *
    * @return addslashes value
   **/
   static function addslashes_deep($value) {
      global $DB;

      $value = is_array($value) ? array_map(array(__CLASS__, 'addslashes_deep'), $value)
                                : (is_null($value)
                                   ? NULL : (is_resource($value)
                                             ? $value : $DB->escape($value)));

      return $value;
   }


   /**
    * Strip slash  for variable & array
    *
    * @param $value     array or string: item to stripslashes (array or string)
    *
    * @return stripslashes item
   **/
   static function stripslashes_deep($value) {

      $value = is_array($value) ? array_map(array(__CLASS__, 'stripslashes_deep'), $value)
                                : (is_null($value)
                                   ? NULL : (is_resource($value)
                                             ? $value :stripslashes($value)));

      return $value;
   }


   /** Converts an array of parameters into a query string to be appended to a URL.
    *
    * @param $array     array parameters to append to the query string.
    * @param $separator        separator may be defined as &amp; to display purpose
    *                         (default '&')
    * @param $parent          This should be left blank (it is used internally by the function).
    *                         (default '')
    *
    * @return string  : Query string to append to a URL.
   **/
   static function append_params($array, $separator='&', $parent='') {

      $params = array();
      foreach ($array as $k => $v) {

         if (is_array($v)) {
            $params[] = self::append_params($v, $separator,
                                            (empty($parent) ? rawurlencode($k)
                                                            : $parent .'['.rawurlencode($k).']'));
         } else {
            $params[] = (!empty($parent) ? $parent . '[' . rawurlencode($k) . ']'
                                         : rawurlencode($k)) . '=' . rawurlencode($v);
         }
      }
      return implode($separator, $params);
   }


   /**
    * Compute PHP memory_limit
    *
    * @return memory limit
   **/
   static function getMemoryLimit() {

      $mem = ini_get("memory_limit");
      preg_match("/([-0-9]+)([KMG]*)/", $mem, $matches);
      $mem = "";

      // no K M or G
      if (isset($matches[1])) {
         $mem = $matches[1];
         if (isset($matches[2])) {
            switch ($matches[2]) {
               case "G" :
                  $mem *= 1024;
                  // nobreak;

               case "M" :
                  $mem *= 1024;
                  // nobreak;

               case "K" :
                  $mem *= 1024;
                  // nobreak;
            }
         }
      }

      return $mem;
   }


   /**
    * Check is current memory_limit is enough for GLPI
    *
    * @since version 0.83
    *
    * @return 0 if PHP not compiled with memory_limit support
    *         1 no memory limit (memory_limit = -1)
    *         2 insufficient memory for GLPI
    *         3 enough memory for GLPI
   **/
   static function checkMemoryLimit() {

      $mem = self::getMemoryLimit();
      if ($mem == "") {
         return 0;
      }
      if ($mem == "-1") {
         return 1;
      }
      if ($mem < (64*1024*1024)) {
         return 2;
      }
      return 3;
   }


   /**
    * Common Checks needed to use GLPI
    *
    * @return 2 : creation error 1 : delete error 0: OK
   **/
   static function commonCheckForUseGLPI() {
      global $CFG_GLPI;

      $error = 0;

      // Title
      echo "<tr><th>".__('Test done')."</th><th >".__('Results')."</th></tr>";

      // Parser test
      echo "<tr class='tab_bg_1'><td class='b left'>".__('Testing PHP Parser')."</td>";

      // PHP Version  - exclude PHP3, PHP 4 and zend.ze1 compatibility
      if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
         // PHP > 5.3 ok, now check PHP zend.ze1_compatibility_mode
         if (ini_get("zend.ze1_compatibility_mode") == 1) {
            $error = 2;
            echo "<td class='red'>
                  <img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'>".
                  __('GLPI is not compatible with the option zend.ze1_compatibility_mode = On.').
                 "</td>";
         } else {
            echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt=\"".
                       __s('PHP version is at least 5.3.0 - Perfect!')."\"
                       title=\"".__s('PHP version is at least 5.3.0 - Perfect!')."\"></td>";
         }

      } else { // PHP <5
         $error = 2;
         echo "<td class='red'>
               <img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'>".
                __('You must install at least PHP 5.3.0.')."</td>";
      }
      echo "</tr>";

      // Check for mysql extension ni php
      echo "<tr class='tab_bg_1'><td class='left b'>".__('MySQL Improved extension test')."</td>";
      if (class_exists("mysqli")) {
         echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png'
                    alt=\"". __s('Ok - the MySQLi class exist - Perfect!')."\"
                    title=\"". __s('Ok - the MySQLi class exist - Perfect!')."\"></td>";
      } else {
         echo "<td class='red'>";
         echo "<img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'>".
               __('You must install the MySQL Improved extension for PHP.')."</td>";
         $error = 2;
      }
      echo "</tr>";

      // session test
      echo "<tr class='tab_bg_1'><td class='b left'>".__('Sessions test')."</td>";

      // check whether session are enabled at all!!
      if (!extension_loaded('session')) {
         $error = 2;
         echo "<td class='red b'>".__('Your parser PHP is not installed with sessions support!').
              "</td>";

      } else if ((isset($_SESSION["Test_session_GLPI"]) && ($_SESSION["Test_session_GLPI"] == 1)) // From install
                 || isset($_SESSION["glpi_currenttime"])) { // From Update
         echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt=\"".
                    __s('Sessions support is available - Perfect!').
                    "\" title=\"".__s('Sessions support is available - Perfect!')."\"></td>";

      } else if ($error != 2) {
         echo "<td class='red'>";
         echo "<img src='".$CFG_GLPI['root_doc']."/pics/orangebutton.png'>".
                __('Make sure that sessions support has been activated in your php.ini')."</td>";
         $error = 1;
      }
      echo "</tr>";

      //Test for session auto_start
      if (ini_get('session.auto_start')==1) {
         echo "<tr class='tab_bg_1'><td class='b'>".__('Test session auto start')."</td>";
         echo "<td class='red'>";
         echo "<img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'>".
               __('session.auto_start is activated. See .htaccess file in the GLPI root for more information.').
               "</td></tr>";
         $error = 2;
      }

      //Test for option session use trans_id loaded or not.
      echo "<tr class='tab_bg_1'><td class='left b'>".__('Test if Session_use_trans_sid is used')."</td>";

      if (isset($_POST[session_name()]) || isset($_GET[session_name()])) {
         echo "<td class='red'>";
         echo "<img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'>".
               __('You must desactivate the Session_use_trans_id option in your php.ini')."</td>";
         $error = 2;

      } else {
         echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt=\"".
               __s('Ok - the sessions works (no problem with trans_id) - Perfect!').
               "\" title=\"". __s('Ok - the sessions works (no problem with trans_id) - Perfect!').
               "\"></td>";
      }
      echo "</tr>";

      //Test for sybase extension loaded or not.
      echo "<tr class='tab_bg_1'><td class='left b'>".__('magic_quotes_sybase extension test')."</td>";

      if (ini_get('magic_quotes_sybase')) {
         echo "<td class='red'>";
         echo "<img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'>".
               __('GLPI does not work with the magic_quotes_sybase option. Please turn it off and retry').
               "</td>";
         $error = 2;

      } else {
         echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt=\"".
              __s("The magic_quotes_sybase option isn't active on your server - Perfect!").
              "\" title=\"".
              __s("The magic_quotes_sybase option isn't active on your server - Perfect!").
              "\"></td>";
      }
      echo "</tr>";

      //Test for ctype extension loaded or not (forhtmlawed)
      echo "<tr class='tab_bg_1'><td class='left b'>".__('Test ctype functions')."</td>";

      if (!function_exists('ctype_digit')) {
         echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'>".
                    __("GLPI can't work correctly without the ctype functions")."></td>";
         $error = 2;

      } else {
         echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt=\"".
                    __s('The functionality is found - Perfect!')."\" title=\"".
                    __s('The functionality is found - Perfect!')."\"></td>";
      }
      echo "</tr>";

      //Test for json_encode function.
      echo "<tr class='tab_bg_1'><td class='left b'>".__('Test json functions')."</td>";

      if (!function_exists('json_encode') || !function_exists('json_decode')) {
         echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'>".
                    __("GLPI can't work correctly without the json_encode and json_decode functions").
               "></td>";
         $error = 2;

      } else {
         echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt=\"".
               __s('The functionality is found - Perfect!'). "\" title=\"".
               __s('The functionality is found - Perfect!').
               "\"></td>";
      }
      echo "</tr>";

      //Test for mbstring extension.
      echo "<tr class='tab_bg_1'><td class='left b'>".__('Mbstring extension test')."</td>";

      if (!extension_loaded('mbstring')) {
         echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'>".
               __('Mbstring extension of your parser PHP is not installed')."></td>";
         $error = 2;

      } else {
         echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt=\"".
               __s('The functionality is found - Perfect!'). "\" title=\"".
               __s('The functionality is found - Perfect!').
               "\"></td>";
      }
      echo "</tr>";

      // memory test
      echo "<tr class='tab_bg_1'><td class='left b'>".__('Allocated memory test')."</td>";

      //Get memory limit
      $mem = self::getMemoryLimit();
      switch (self::checkMemoryLimit()) {
         case 0 : // memory_limit not compiled -> no memory limit
         case 1 : // memory_limit compiled and unlimited
            echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt=\"".
                  __s('Unlimited memory - Perfect!')."\" title=\"".
                  __s('Unlimited memory - Perfect!')."\"></td>";
            break;

         case 2: //Insufficient memory
            $showmem = $mem/1048576;
            echo "<td class='red'><img src='".$CFG_GLPI['root_doc']."/pics/redbutton.png'><span class='b'>".
                  sprintf(__('%1$s: %2$s'), __('Allocated memory'),
                          sprintf(__('%1$s %2$s'), $showmem, __('Mio'))).
                  "</span>".
                  "<br>".__('A minimum of 64MB is commonly required for GLPI.').
                  "<br>".__('Try increasing the memory_limit parameter in the php.ini file.').
                  "</td>";
            $error = 2;
            break;

         case 3: //Got enough memory, going to the next step
            echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt=\"".
                  __s('Allocated memory > 64Mio - Perfect!')."\" title=\"".
                  __s('Allocated memory > 64Mio - Perfect!')."\"></td>";
            break;
      }
      echo "</tr>";

      $suberr = Config::checkWriteAccessToDirs();
      if ($suberr > $error) {
         $error = $suberr;
      }

      $suberr = self::checkSELinux();
      if ($suberr > $error) {
         $error = $suberr;
      }

      return $error;
   }


   /**
    * Check SELinux configuration
    *
    * @since version 0.84
    *
    *  @return integer 0: OK, 1:Warning, 2:Error
   **/
   static function checkSELinux() {
      global $CFG_GLPI;

      if ((DIRECTORY_SEPARATOR != '/')
          || !file_exists('/usr/sbin/getenforce')) {
         // This is not a SELinux system
         return 0;
      }

      $mode = exec("/usr/sbin/getenforce");
      //TRANS: %s is mode name (Permissive, Enforcing of Disabled)
      $msg  = sprintf(__('SELinux mode is %s'), $mode);
      echo "<tr class='tab_bg_1'><td class='left b'>$msg</td>";
      // All modes should be ok
      echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt='$mode' title='$mode'></td></tr>";
      if (!strcasecmp($mode, 'Disabled')) {
         // Other test are not useful
         return 0;
      }

      $err = 0;

      // No need to check file context as checkWriteAccessToDirs will show issues

      // Enforcing mode will block some feature (notif, ...)
      // Permissive mode will write lot of stuff in audit.log

      $bools = array('httpd_can_network_connect', 'httpd_can_network_connect_db',
                     'httpd_can_sendmail');
      foreach ($bools as $bool) {
         $state = exec('/usr/sbin/getsebool '.$bool);
         //TRANS: %s is an option name
         $msg = sprintf(__('SELinux boolean configuration for %s'), $state);
         echo "<tr class='tab_bg_1'><td class='left b'>$msg</td>";
         if (substr($state, -2) == 'on') {
            echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/greenbutton.png' alt='$state' title='$state'>".
                 "</td>";
         } else {
            echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/orangebutton.png' alt='$state' title='$state'>".
                 "</td>";
            $err = 1;
         }
         echo "</tr>";
      }

      return $err;
   }


   /**
    * Get the filesize of a complete directory (from php.net)
    *
    * @param $path string: directory or file to get size
    *
    * @return size of the $path
   **/
   static function filesizeDirectory($path) {

      if (!is_dir($path)) {
         return filesize($path);
      }

      if ($handle = opendir($path)) {
         $size = 0;

         while (false !== ($file = readdir($handle))) {
            if (($file != '.') && ($file != '..')) {
               $size += filesize($path.'/'.$file);
               $size += self::filesizeDirectory($path.'/'.$file);
            }
         }

         closedir($handle);
         return $size;
      }
   }


   /** Format a size passing a size in octet
    *
    * @param   $size integer: Size in octet
    *
    * @return  formatted size
   **/
   static function getSize($size) {

      //TRANS: list of unit (o for octet)
      $bytes = array(__('o'), __('Kio'), __('Mio'), __('Gio'), __('Tio'));
      foreach ($bytes as $val) {
         if ($size > 1024) {
            $size = $size / 1024;
         } else {
            break;
         }
      }
      //TRANS: %1$s is a number maybe float or string and %2$s the unit
      return sprintf(__('%1$s %2$s'), round($size, 2), $val);
   }


   /**
    * Delete a directory and file contains in it
    *
    * @param $dir string: directory to delete
   **/
   static function deleteDir($dir) {

      if (file_exists($dir)) {
         chmod($dir, 0777);

         if (is_dir($dir)) {
            $id_dir = opendir($dir);
            while (($element = readdir($id_dir)) !== false) {
               if (($element != ".") && ($element != "..")) {

                  if (is_dir($dir."/".$element)) {
                     self::deleteDir($dir."/".$element);
                  } else {
                     unlink($dir."/".$element);
                  }

               }
            }
            closedir($id_dir);
            rmdir($dir);

         } else { // Delete file
            unlink($dir);
         }
      }
   }


   /**
    * Check if new version is available
    *
    * @param $auto                  boolean: check done autically ? (if not display result)
    *                                        (true by default)
    * @param $messageafterredirect  boolean: use message after redirect instead of display
    *                                        (false by default)
    *
    * @return string explaining the result
   **/
   static function checkNewVersionAvailable($auto=true, $messageafterredirect=false) {
      global $CFG_GLPI;

      if (!$auto && !Session::haveRight("check_update","r")) {
         return false;
      }

      if (!$auto && !$messageafterredirect) {
         echo "<br>";
      }

      $error          = "";
      $latest_version = self::getURLContent("http://glpi-project.org/latest_version", $error);

      if (strlen(trim($latest_version)) == 0) {
         if (!$auto) {
            if ($messageafterredirect) {
               Session::addMessageAfterRedirect($error, true, ERROR);
            } else {
               echo "<div class='center'>$error</div>";
            }
         } else {
            return $error;
         }

      } else {
         $splitted = explode(".", trim($CFG_GLPI["version"]));

         if ($splitted[0] < 10) {
            $splitted[0] .= "0";
         }

         if ($splitted[1] < 10) {
            $splitted[1] .= "0";
         }

         $cur_version = ($splitted[0]*10000) + ($splitted[1]*100);

         if (isset($splitted[2])) {
            if ($splitted[2] < 10) {
               $splitted[2] .= "0";
            }
            $cur_version += $splitted[2];
         }

         $splitted = explode(".", trim($latest_version));

         if ($splitted[0] < 10) {
            $splitted[0] .= "0";
         }

         if ($splitted[1] < 10) {
            $splitted[1] .= "0";
         }

         $lat_version = ($splitted[0]*10000) + ($splitted[1]*100);

         if (isset($splitted[2])) {
            if ($splitted[2] < 10) {
               $splitted[2] .= "0";
            }
            $lat_version += $splitted[2];
         }

         if ($cur_version < $lat_version) {
            $config_object                = new Config();
            $input["id"]                  = 1;
            $input["founded_new_version"] = $latest_version;
            $config_object->update($input);

            if (!$auto) {
               if ($messageafterredirect) {
                  Session::addMessageAfterRedirect(sprintf(__('A new version is available: %s.'),
                                                           $latest_version));
                  Session::addMessageAfterRedirect(__('You will find it on the GLPI-PROJECT.org site.'));
               } else {
                  echo "<div class='center'>".sprintf(__('A new version is available: %s.'),
                                                      $latest_version)."</div>";
                  echo "<div class='center'>".__('You will find it on the GLPI-PROJECT.org site.').
                       "</div>";
               }

            } else {
               if ($messageafterredirect) {
                  Session::addMessageAfterRedirect(sprintf(__('A new version is available: %s.'),
                                                           $latest_version));
               } else {
                  return sprintf(__('A new version is available: %s.'), $latest_version);
               }
            }

         } else {
            if (!$auto) {
               if ($messageafterredirect) {
                  Session::addMessageAfterRedirect(__('You have the latest available version'));
               } else {
                  echo "<div class='center'>".__('You have the latest available version')."</div>";
               }

            } else {
               if ($messageafterredirect) {
                  Session::addMessageAfterRedirect(__('You have the latest available version'));
               } else {
                  return __('You have the latest available version');
               }
            }
         }
      }
      return 1;
   }


   /**
    * Determine if Imap/Pop is usable checking extension existence
    *
    * @return boolean
   **/
   static function canUseImapPop() {
      return extension_loaded('imap');
   }


   /**
    * Determine if Ldap is usable checking ldap extension existence
    *
    * @return boolean
   **/
   static function canUseLdap() {
      return extension_loaded('ldap');
   }


   /**
    * Check Write Access to a directory
    *
    * @param $dir string: directory to check
    *
    * @return 2 : creation error 1 : delete error 0: OK
   **/
   static function testWriteAccessToDirectory($dir) {

      $rand = rand();

      // Check directory creation which can be denied by SElinux
      $sdir = sprintf("%s/test_glpi_%08x", $dir, $rand);

      if (!mkdir($sdir)) {
         return 4;
      }

      if (!rmdir($sdir)) {
         return 3;
      }

      // Check file creation
      $path = sprintf("%s/test_glpi_%08x.txt", $dir, $rand);
      $fp   = fopen($path, 'w');

      if (empty($fp)) {
         return 2;
      }

      $fw = fwrite($fp, "This file was created for testing reasons. ");
      fclose($fp);
      $delete = unlink($path);

      if (!$delete) {
         return 1;
      }

      return 0;
}


   /**
    * Get form URL for itemtype
    *
    * @param $itemtype  string   item type
    * @param $full               path or relative one (true by default)
    *
    * return string itemtype Form URL
   **/
   static function getItemTypeFormURL($itemtype, $full=true) {
      global $CFG_GLPI;

      $dir = ($full ? $CFG_GLPI['root_doc'] : '');

      if ($plug = isPluginItemType($itemtype)) {
         $dir .= "/plugins/".strtolower($plug['plugin']);
         $item = strtolower($plug['class']);

      } else { // Standard case
         $item = strtolower($itemtype);
      }

      return "$dir/front/$item.form.php";
   }


   /**
    * Get search URL for itemtype
    *
    * @param $itemtype  string   item type
    * @param $full               path or relative one (true by default)
    *
    * return string itemtype search URL
   **/
   static function getItemTypeSearchURL($itemtype, $full=true) {
      global $CFG_GLPI;

      $dir = ($full ? $CFG_GLPI['root_doc'] : '');

      if ($plug = isPluginItemType($itemtype)) {
         $dir .=  "/plugins/".strtolower($plug['plugin']);
         $item = strtolower($plug['class']);

      } else { // Standard case
         if ($itemtype == 'Cartridge') {
            $itemtype = 'CartridgeItem';
         }
         if ($itemtype == 'Consumable') {
            $itemtype = 'ConsumableItem';
         }
         $item = strtolower($itemtype);
      }

      return "$dir/front/$item.php";
   }


   /**
    * Get ajax tabs url for itemtype
    *
    * @param $itemtype  string   item type
    * @param $full               path or relative one (true by default)
    *
    * return string itemtype tabs URL
   **/
   static function getItemTypeTabsURL($itemtype, $full=true) {
      global $CFG_GLPI;


      $filename = "/ajax/common.tabs.php";

      /// To keep for plugins
      /// TODO drop also for plugins.
      /// MoYo : test to drop it : plugin dev ?
//       if ($plug = isPluginItemType($itemtype)) {
//          $dir      = "/plugins/".strtolower($plug['plugin']);
//          $item     = strtolower($plug['class']);
//          $tempname = $dir."/ajax/$item.tabs.php";
//          if (file_exists(GLPI_ROOT.$tempname)) {
//             $filename = $tempname;
//          }
//       }

      return ($full ? $CFG_GLPI['root_doc'] : '').$filename;
   }


   /**
    * Get a random string
    *
    * @param $length integer: length of the random string
    *
    * @return random string
   **/
   static function getRandomString($length) {

      $alphabet  = "1234567890abcdefghijklmnopqrstuvwxyz";
      $rndstring = "";

      for ($a=0 ; $a<=$length ; $a++) {
         $b          = rand(0, strlen($alphabet) - 1);
         $rndstring .= $alphabet[$b];
      }
      return $rndstring;
   }


   /**
    * Split timestamp in time units
    *
    * @param $time integer: timestamp
    *
    * @return string
   **/
   static function getTimestampTimeUnits($time) {

      $time          = round(abs($time));
      $out['second'] = 0;
      $out['minute'] = 0;
      $out['hour']   = 0;
      $out['day']    = 0;

      $out['second'] = $time%MINUTE_TIMESTAMP;
      $time         -= $out['second'];

      if ($time > 0) {
         $out['minute'] = ($time%HOUR_TIMESTAMP)/MINUTE_TIMESTAMP;
         $time         -= $out['minute']*MINUTE_TIMESTAMP;

         if ($time > 0) {
            $out['hour'] = ($time%DAY_TIMESTAMP)/HOUR_TIMESTAMP;
            $time       -= $out['hour']*HOUR_TIMESTAMP;

            if ($time > 0) {
               $out['day'] = $time/DAY_TIMESTAMP;
            }
         }
      }
      return $out;
   }


   /**
    * Get a web page. Use proxy if configured
    *
    * @param $url    string   to retrieve
    * @param $msgerr string   set if problem encountered (default NULL)
    * @param $rec    integer  internal use only Must be 0 (default 0)
    *
    * @return content of the page (or empty)
   **/
   static function getURLContent ($url, &$msgerr=NULL, $rec=0) {
      global $CFG_GLPI;

      $content = "";
      $taburl  = parse_url($url);

      // Connection directe
      if (empty($CFG_GLPI["proxy_name"])) {
         $hostscheme  = '';
         $defaultport = 80;
         // Manage standard HTTPS port : scheme detection or port 443
         if ((isset($taburl["scheme"]) && $taburl["scheme"]=='https')
            || (isset($taburl["port"]) && $taburl["port"]=='443')) {
            $hostscheme  = 'ssl://';
            $defaultport = 443;
         }
         if ($fp = @fsockopen($hostscheme.$taburl["host"],
                              (isset($taburl["port"]) ? $taburl["port"] : $defaultport),
                              $errno, $errstr, 1)) {

            if (isset($taburl["path"]) && ($taburl["path"] != '/')) {
               $toget = $taburl["path"];
               if (isset($taburl["query"])) {
                  $toget .= '?'.$taburl["query"];
               }
               // retrieve path + args
               $request = "GET $toget HTTP/1.1\r\n";
            } else {
               $request = "GET / HTTP/1.1\r\n";
            }

            $request .= "Host: ".$taburl["host"]."\r\n";
         } else {
            if (isset($msgerr)) {
               //TRANS: %s is the error string
               $msgerr = sprintf(__('Connection failed. If you use a proxy, please configure it. (%s)'),
                                 $errstr);
            }
            return "";
         }

      } else { // Connection using proxy
         $fp = fsockopen($CFG_GLPI["proxy_name"], $CFG_GLPI["proxy_port"], $errno, $errstr, 1);

         if ($fp) {
            $request  = "GET $url HTTP/1.1\r\n";
            $request .= "Host: ".$taburl["host"]."\r\n";
            if (!empty($CFG_GLPI["proxy_user"])) {
               $request .= "Proxy-Authorization: Basic " . base64_encode ($CFG_GLPI["proxy_user"].":".
                           self::decrypt($CFG_GLPI["proxy_passwd"], GLPIKEY)) . "\r\n";
            }

         } else {
            if (isset($msgerr)) {
               //TRANS: %s is the error string
               $msgerr = sprintf(__('Failed to connect to the proxy server (%s)'), $errstr);
            }
            return "";
         }
      }

      $request .= "User-Agent: GLPI/".trim($CFG_GLPI["version"])."\r\n";
      $request .= "Connection: Close\r\n\r\n";

      fwrite($fp, $request);

      $header = true ;
      $redir  = false;
      $errstr = "";
      while (!feof($fp)) {
         if ($buf = fgets($fp, 1024)) {
            if ($header) {

               if (strlen(trim($buf)) == 0) {
                  // Empty line = end of header
                  $header = false;

               } else if ($redir && preg_match("/^Location: (.*)$/", $buf, $rep)) {
                  if ($rec < 9) {
                     $desturl = trim($rep[1]);
                     $taburl2 = parse_url($desturl);

                     if (isset($taburl2['host'])) {
                        // Redirect to another host
                        return (self::getURLContent($desturl, $errstr, $rec+1));
                     }
                     // redirect to same host
                     return (self::getURLContent((isset($taburl['scheme'])?$taburl['scheme']:'http').
                                                 "://".$taburl['host'].
                                                 (isset($taburl['port'])?':'.$taburl['port']:'').
                                                  $desturl, $errstr, $rec+1));
                  }

                  $errstr = "Too deep";
                  break;

               } else if (preg_match("/^HTTP.*200.*OK/", $buf)) {
                  // HTTP 200 = OK

               } else if (preg_match("/^HTTP.*302/", $buf)) {
                  // HTTP 302 = Moved Temporarily
                  $redir = true;

               } else if (preg_match("/^HTTP/", $buf)) {
                  // Other HTTP status = error
                  $errstr = trim($buf);
                  break;
               }

            } else {
               // Body
               $content .= $buf;
            }
         }
      } // eof

      fclose($fp);

      if (empty($content) && isset($msgerr)) {
         if (empty($errstr)) {
            $msgerr = __('No data available on the web site');
         } else {
            //TRANS: %s is the error string
            $msgerr = sprintf(__('Impossible to connect to site (%s)'),$errstr);
         }
      }
      return $content;
   }


   /**
    * @param $need
    * @param $tab
   **/
   static function key_exists_deep($need, $tab) {

      foreach ($tab as $key => $value) {

         if ($need == $key) {
            return true;
         }

         if (is_array($value)
             && self::key_exists_deep($need, $value)) {
            return true;
         }

      }
      return false;
   }


   /**
    * Manage planning posted datas (must have begin + duration or end)
    * Compute end if duration is set
    *
    * @param $data array data to process
    *
    * @return processed datas
   **/
   static function manageBeginAndEndPlanDates(&$data) {

      if (!isset($data['end'])) {
         if (isset($data['begin'])
             && isset($data['_duration'])) {
            $begin_timestamp = strtotime($data['begin']);
            $data['end']     = date("Y-m-d H:i:s", $begin_timestamp+$data['_duration']);
            unset($data['_duration']);
         }
      }
   }


   /**
    * Manage login redirection
    *
    * @param $where string: where to redirect ?
   **/
   static function manageRedirect($where) {
      global $CFG_GLPI, $PLUGIN_HOOKS;

      if (!empty($where)) {
         $data = explode("_", $where);

         if ((count($data) >= 2)
             && isset($_SESSION["glpiactiveprofile"]["interface"])
             && !empty($_SESSION["glpiactiveprofile"]["interface"])) {

            $forcetab = '';
            if (isset($data[2])) {
               $forcetab = 'forcetab='.$data[2];
            }
            // Plugin tab
            if (isset($data[3])) {
               $forcetab .= '_'.$data[3];
            }

            switch ($_SESSION["glpiactiveprofile"]["interface"]) {
               case "helpdesk" :
                  switch ($data[0]) {
                     case "plugin" :
                        $plugin = $data[1];
                        $valid  = false;
                        if (isset($PLUGIN_HOOKS['redirect_page'][$plugin])
                            && !empty($PLUGIN_HOOKS['redirect_page'][$plugin])) {
                           // Simple redirect
                           if (!is_array($PLUGIN_HOOKS['redirect_page'][$plugin])) {
                              if (isset($data[2]) && ($data[2] > 0)) {
                                 $valid = true;
                                 $id    = $data[2];
                                 $page  = $PLUGIN_HOOKS['redirect_page'][$plugin];
                              }
                              $forcetabnum = 3 ;
                           } else { // Complex redirect
                              if (isset($data[2]) && !empty($data[2])
                                  && isset($data[3]) && ($data[3] > 0)
                                  && isset($PLUGIN_HOOKS['redirect_page'][$plugin][$data[2]])
                                  && !empty($PLUGIN_HOOKS['redirect_page'][$plugin][$data[2]])) {
                                 $valid = true;
                                 $id    = $data[3];
                                 $page  = $PLUGIN_HOOKS['redirect_page'][$plugin][$data[2]];
                              }
                              $forcetabnum = 4 ;
                           }
                        }

                        if (isset($data[$forcetabnum])) {
                           $forcetab = 'forcetab='.$data[$forcetabnum];
                        }

                        if ($valid) {
                           Html::redirect($CFG_GLPI["root_doc"].
                                          "/plugins/$plugin/$page?id=$id&$forcetab");
                        } else {
                           Html::redirect($CFG_GLPI["root_doc"].
                                          "/front/helpdesk.public.php?$forcetab");
                        }
                        break;

                     // Use for compatibility with old name
                     case "tracking" :
                     case "ticket" :
                        // Check entity
                        if (($item = getItemForItemtype($data[0]))
                            && $item->isEntityAssign()) {
                           if ($item->getFromDB($data[1])) {
                              if (!Session::haveAccessToEntity($item->getEntityID())) {
                                 Session::changeActiveEntities($item->getEntityID(),1);
                              }
                           }
                        }
                        Html::redirect($CFG_GLPI["root_doc"]."/front/ticket.form.php?id=".$data[1].
                                       "&$forcetab");
                        break;

                     case "preference" :
                        Html::redirect($CFG_GLPI["root_doc"]."/front/preference.php?$forcetab");
                        break;

                     default :
                        Html::redirect($CFG_GLPI["root_doc"]."/front/helpdesk.public.php?$forcetab");
                        break;
                  }
                  break;

               case "central" :
                  switch ($data[0]) {
                     case "plugin" :
                        $plugin = $data[1];
                        $valid  = false;
                        if (isset($PLUGIN_HOOKS['redirect_page'][$plugin])
                            && !empty($PLUGIN_HOOKS['redirect_page'][$plugin])) {
                           // Simple redirect
                           if (!is_array($PLUGIN_HOOKS['redirect_page'][$plugin])) {
                              if (isset($data[2]) && ($data[2] > 0)) {
                                 $valid = true;
                                 $id    = $data[2];
                                 $page  = $PLUGIN_HOOKS['redirect_page'][$plugin];
                              }
                              $forcetabnum = 3 ;
                           } else { // Complex redirect
                              if (isset($data[2]) && !empty($data[2])
                                  && isset($data[3]) && ($data[3] > 0)
                                  && isset($PLUGIN_HOOKS['redirect_page'][$plugin][$data[2]])
                                  && !empty($PLUGIN_HOOKS['redirect_page'][$plugin][$data[2]])) {
                                 $valid = true;
                                 $id    = $data[3];
                                 $page  = $PLUGIN_HOOKS['redirect_page'][$plugin][$data[2]];
                              }
                              $forcetabnum = 4 ;
                           }
                        }

                        if (isset($data[$forcetabnum])) {
                           $forcetab = 'forcetab='.$data[$forcetabnum];
                        }

                        if ($valid) {
                           Html::redirect($CFG_GLPI["root_doc"].
                                          "/plugins/$plugin/$page?id=$id&$forcetab");
                        } else {
                           Html::redirect($CFG_GLPI["root_doc"]."/front/central.php?$forcetab");
                        }
                        break;

                     case "preference" :
                        Html::redirect($CFG_GLPI["root_doc"]."/front/preference.php?$forcetab");
                        break;

                     // Use for compatibility with old name
                     // no break
                     case "tracking" :
                        $data[0] = "ticket";

                     default :
                        if (!empty($data[0] )&& ($data[1] > 0)) {
                           // Check entity
                           if (($item = getItemForItemtype($data[0]))
                               && $item->isEntityAssign()) {
                              if ($item->getFromDB($data[1])) {
                                 if (!Session::haveAccessToEntity($item->getEntityID())) {
                                    Session::changeActiveEntities($item->getEntityID(),1);
                                 }
                              }
                           }

                           Html::redirect($CFG_GLPI["root_doc"]."/front/".$data[0].".form.php?id=".
                                        $data[1]."&$forcetab");
                        } else {
                           Html::redirect($CFG_GLPI["root_doc"]."/front/central.php?$forcetab");
                        }
                        break;
                  }
                  break;
            }
         }
      }
   }


   /**
    * Convert a value in byte, kbyte, megabyte etc...
    *
    * @param $val string: config value (like 10k, 5M)
    *
    * @return $val
   **/
   static function return_bytes_from_ini_vars($val) {

      $val  = trim($val);
      $last = self::strtolower($val{strlen($val)-1});

      switch($last) {
         // Le modifieur 'G' est disponible depuis PHP 5.1.0
         case 'g' :
            $val *= 1024;
            // no break;

         case 'm' :
            $val *= 1024;
            // no break;

         case 'k' :
            $val *= 1024;
            // no break;
      }

      return $val;
   }

   /**
    * Parse imap open connect string
    *
    * @since version 0.84
    *
    * @param $value string: connect string
    * @param $forceport boolean: force compute port if not set (false by default)
    *
    * @return array of parsed arguments (address, port, mailbox, type, ssl, tls, validate-cert
    *         norsh, secure and debug) : options are empty if not set
    *                                    and options have boolean values if set
   **/
   static function parseMailServerConnectString($value, $forceport=false) {

      $tab = array();
      if (strstr($value,":")) {
         $tab['address'] = str_replace("{", "", preg_replace("/:.*/", "", $value));
         $tab['port']    = preg_replace("/.*:/", "", preg_replace("/\/.*/", "", $value));

      } else {
         if (strstr($value,"/")) {
            $tab['address'] = str_replace("{", "", preg_replace("/\/.*/", "", $value));
         } else {
            $tab['address'] = str_replace("{", "", preg_replace("/}.*/", "", $value));
         }
         $tab['port'] = "";
      }
      $tab['mailbox'] = preg_replace("/.*}/", "", $value);

      $tab['type']    = '';
      if (strstr($value,"/imap")) {
         $tab['type'] = 'imap';
      } else if (strstr($value,"/pop")) {
         $tab['type'] = 'pop';
      }
      $tab['ssl'] = false;
      if (strstr($value,"/ssl")) {
         $tab['ssl'] = true;
      }

      if ($forceport && empty($tab['port'])) {
         if ($tab['type'] == 'pop') {
            if ($tab['ssl']) {
               $tab['port'] = 110;
            } else {
               $tab['port'] = 995;
            }
         }
         if ($tab['type'] = 'imap') {
            if ($tab['ssl']) {
               $tab['port'] = 993;
            } else {
               $tab['port'] = 143;
            }
         }
      }
      $tab['tls'] = '';
      if (strstr($value,"/tls")) {
         $tab['tls'] = true;
      }
      if (strstr($value,"/notls")) {
         $tab['tls'] = false;
      }
      $tab['validate-cert'] = '';
      if (strstr($value,"/validate-cert")) {
         $tab['validate-cert'] = true;
      }
      if (strstr($value,"/novalidate-cert")) {
         $tab['validate-cert'] = false;
      }
      $tab['norsh'] = '';
      if (strstr($value,"/norsh")) {
         $tab['norsh'] = true;
      }
      $tab['secure'] = '';
      if (strstr($value,"/secure")) {
         $tab['secure'] = true;
      }
      $tab['debug'] = '';
      if (strstr($value,"/debug")) {
         $tab['debug'] = true;
      }

      return $tab;
   }


   /**
    * Display a mail server configuration form
    *
    * @param $value String host connect string ex
    *                      {localhost:993/imap/ssl}INBOX
    *
    * @return String type of the server (imap/pop)
   **/
   static function showMailServerConfig($value) {

      if (!Session::haveRight("config", "w")) {
         return false;
      }

      $tab = Toolbox::parseMailServerConnectString($value);

      echo "<tr class='tab_bg_1'><td>" . __('Server') . "</td>";
      echo "<td><input size='30' type='text' name='mail_server' value=\"" .$tab['address']. "\">";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'><td>" . __('Connection options') . "</td><td>";
      echo "<select name='server_type'>";
      echo "<option value=''>&nbsp;</option>\n";
      //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
      echo "<option value='/imap' ".(($tab['type'] == 'imap') ?" selected ":"").">".__('IMAP').
           "</option>\n";
      //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
      echo "<option value='/pop' ".(($tab['type'] == 'pop') ? " selected " : "").">".__('POP').
           "</option>\n";
      echo "</select>&nbsp;";

      echo "<select name='server_ssl'>";
      echo "<option value=''>&nbsp;</option>\n";
      //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
      echo "<option value='/ssl' " .(($tab['ssl'] === true) ? " selected " : "").">".__('SSL').
           "</option>\n";
      echo "</select>&nbsp;";

      echo "<select name='server_tls'>";
      echo "<option value=''>&nbsp;</option>\n";
      echo "<option value='/tls' ".(($tab['tls'] === true) ? " selected " : "").">";
      //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
      echo __('TLS')."</option>\n";
      echo "<option value='/notls' ".(($tab['tls'] === false) ?" selected ":"").">";
      //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
      echo __('NO-TLS')."</option>\n";
      echo "</select>&nbsp;";

      echo "<select name='server_cert'>";
      echo "<option value=''>&nbsp;</option>\n";
      echo "<option value='/novalidate-cert' ".(($tab['validate-cert'] === false) ?" selected ":"");
      //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
      echo ">".__('NO-VALIDATE-CERT')."</option>\n";
      echo "<option value='/validate-cert' " .(($tab['validate-cert'] === true) ?" selected ":"");
      //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
      echo ">".__('VALIDATE-CERT')."</option>\n";
      echo "</select>\n";

      echo "<select name='server_rsh'>";
      echo "<option value=''>&nbsp;</option>\n";
      echo "<option value='/norsh' ".(($tab['norsh'] === true) ?" selected ":"");
      //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
      echo ">".__('NORSH')."</option>\n";
      echo "</select>\n";

      echo "<select name='server_secure'>";
      echo "<option value=''>&nbsp;</option>\n";
      echo "<option value='/secure' ".(($tab['secure'] === true) ?" selected ":"");
      //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
      echo ">".__('SECURE')."</option>\n";
      echo "</select>\n";

      echo "<select name='server_debug'>";
      echo "<option value=''>&nbsp;</option>\n";
      echo "<option value='/debug' ".(($tab['debug'] === true) ?" selected ":"");
      //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
      echo ">".__('DEBUG')."</option>\n";
      echo "</select>\n";

      echo "<input type=hidden name=imap_string value='".$value."'>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'><td>". __('Incoming mail folder (optional, often INBOX)')."</td>";
      echo "<td><input size='30' type='text' name='server_mailbox' value=\"" . $tab['mailbox'] . "\" >";
      echo "</td></tr>\n";

      //TRANS: for mail connection system
      echo "<tr class='tab_bg_1'><td>" . __('Port (optional)') . "</td>";
      echo "<td><input size='10' type='text' name='server_port' value='".$tab['port']."'></td></tr>\n";
      if (empty($value)) {
         $value = "&nbsp;";
      }
      //TRANS: for mail connection system
      echo "<tr class='tab_bg_1'><td>" . __('Connection string') . "</td>";
      echo "<td class='b'>$value</td></tr>\n";

      return $tab['type'];
   }


   /**
    * @param $input
   **/
   static function constructMailServerConfig($input) {

      $out = "";
      if (isset($input['mail_server']) && !empty($input['mail_server'])) {
         $out .= "{" . $input['mail_server'];
      } else {
         return $out;
      }
      if (isset($input['server_port']) && !empty($input['server_port'])) {
         $out .= ":" . $input['server_port'];
      }
      if (isset($input['server_type'])) {
         $out .= $input['server_type'];
      }
      if (isset($input['server_ssl'])) {
         $out .= $input['server_ssl'];
      }
      if (isset($input['server_cert'])
          && (!empty($input['server_ssl']) || !empty($input['server_tls']))) {
         $out .= $input['server_cert'];
      }
      if (isset($input['server_tls'])) {
         $out .= $input['server_tls'];
      }

      if (isset($input['server_rsh'])) {
         $out .= $input['server_rsh'];
      }
      if (isset($input['server_secure'])) {
         $out .= $input['server_secure'];
      }
      if (isset($input['server_debug'])) {
         $out .= $input['server_debug'];
      }
      $out .= "}";
      if (isset($input['server_mailbox'])) {
         $out .= $input['server_mailbox'];
      }

      return $out;
   }


   static function getDaysOfWeekArray() {

      $tab[0] = __("Sunday");
      $tab[1] = __("Monday");
      $tab[2] = __("Tuesday");
      $tab[3] = __("Wednesday");
      $tab[4] = __("Thursday");
      $tab[5] = __("Friday");
      $tab[6] = __("Saturday");

      return $tab;
   }


   static function getMonthsOfYearArray() {

      $tab[1]  = __("January");
      $tab[2]  = __("February");
      $tab[3]  = __("March");
      $tab[4]  = __("April");
      $tab[5]  = __("May");
      $tab[6]  = __("June");
      $tab[7]  = __("July");
      $tab[8]  = __("August");
      $tab[9]  = __("September");
      $tab[10] = __("October");
      $tab[11] = __("November");
      $tab[12] = __("December");

      return $tab;
   }


   /**
    * Do a in_array search comparing string using strcasecmp
    *
    * @since version 0.84
    *
    * @param $string    string   to search
    * @param $datas     array    to search to search
    *
    * @return boolean : string founded ?
   **/
   static function inArrayCaseCompare($string, $datas=array()) {

      if (count($datas)) {
         foreach ($datas as $tocheck) {
            if (strcasecmp($string, $tocheck) == 0) {
               return true;
            }
         }
      }
      return false;
   }


   /**
    * Clean integer value (strip all chars not - and spaces )
    *
    * @since versin 0.83.5
    *
    * @param $integer string   integer string
    *
    * @return clean integer
   **/
   static function cleanInteger($integer) {
      return preg_replace("/[^0-9-]/", "", $integer);
   }


   /**
    * Clean decimal value (strip all chars not - and spaces )
    *
    * @since versin 0.83.5
    *
    * @param $decimal string    float string
    *
    * @return clean integer
   **/
   static function cleanDecimal($decimal) {
      return preg_replace("/[^0-9\.-]/", "", $decimal);
   }


   /**
    * Save a configuration file
    *
    * @since version 0.84
    *
    * @param $name      string   config file name
    * @param $content   string   config file content
    *
    * @return boolean
   **/
   function writeConfig($name, $content) {

      $name = GLPI_CONFIG_DIR . '/'.$name;
      $fp   = fopen($name, 'wt');
      if ($fp) {
         $fw = fwrite($fp, $content);
         fclose($fp);
         if (function_exists('opcache_invalidate')) {
            /* Invalidate Zend OPcache to ensure saved version used */
            opcache_invalidate($name, true);
         }
         return ($fw>0);
      }
      return false;
   }

   /**
    * Prepare array passed on an input form
    *
    * @param $value array: passed array
    * @since version 0.83.91
    * @return string encoded array
   **/
   static function prepareArrayForInput($value) {
      return base64_encode(json_encode($value));
   }

   /**
    * Decode array passed on an input form
    *
    * @param $value string: encoded value
    * @since version 0.83.91
    * @return string decoded array
   **/
   static function decodeArrayFromInput($value) {
      if ($dec = base64_decode($value)) {
         if ($ret = json_decode($dec,true)) {
            return $ret;
         }
      }
      return array();
   }

   /**
    * Check valid referer accessing GLPI
    *
    * @since version 0.84.2
    *
    * @return nothing : display error if not permit
   **/
   static function checkValidReferer() {
      global $CFG_GLPI;

      if (!isset($_SERVER['HTTP_REFERER'])
         || !is_array($url = parse_url($_SERVER['HTTP_REFERER']))
         || !isset($url['host'])
         || (($url['host'] != $_SERVER['SERVER_NAME'])
            && (!isset($_SERVER['HTTP_X_FORWARDED_SERVER'])
                  || ($url['host'] != $_SERVER['HTTP_X_FORWARDED_SERVER'])))
         || !isset($url['path'])
         || (!empty($CFG_GLPI['root_doc'])
            && strpos($url['path'], $CFG_GLPI['root_doc']) !== 0)) {
         Html::displayErrorAndDie(__("The action you have requested is not allowed. Reload previous page before doing action again."), true);
      }
   }
}
?>
