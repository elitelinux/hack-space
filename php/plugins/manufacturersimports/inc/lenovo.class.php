<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manufacturersimports plugin for GLPI
 Copyright (C) 2003-2011 by the Manufacturersimports Development Team.

 https://forge.indepnet.net/projects/manufacturersimports
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manufacturersimports.

 Manufacturersimports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manufacturersimports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manufacturersimports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginManufacturersimportsLenovo extends PluginManufacturersimportsManufacturer {

   function showCheckbox($ID,$sel,$otherSerial=false) {

      return "<input type='checkbox' name='item[".$ID."]' value='1' $sel>";

   }

   function showItemTitle($output_type,$header_num) {

      return Search::showHeaderItem($output_type,__('Model number', 'manufacturersimports'),$header_num);

   }

   function showDocTitle($output_type,$header_num) {

      return Search::showHeaderItem($output_type,_n('Associated document', 'Associated documents', 2),$header_num);

   }

   function showItem($output_type,$otherSerial,$item_num,$row_num) {

      return Search::showItem($output_type,$otherSerial,$item_num,$row_num);
   }

   function getSearchField() {

      $field = "Start Date:";

      return $field;
   }

   function getSupplierInfo($compSerial=null, $otherserial=null) {

      $info["name"]="Lenovo";
      $info["supplier_url"] = "http://support.lenovo.com/templatedata/Web%20Content/JSP/warrantyLookup.jsp?";
      $info["url"] = $info["supplier_url"]."sysSerial=".$compSerial."&sysMachType=".$otherserial."&btnSubmit";

      return $info;
   }
   
   static function strallpos($haystack,$needle,$offset = 0){
       $result = array();
       for($i = $offset; $i<strlen($haystack); $i++){
           $pos = strpos($haystack,$needle,$i);
           if($pos !== FALSE){
               $offset =  $pos;
               if($offset >= $i){
                   $i = $offset;
                   $result[] = $offset;
               }
           }
       }
       return $result;
   }
   
   function getBuyDate($contents) {
      
      $field = "Start Date:";

      $searchbegin = self::strallpos($contents, $field);
      
      $dates = array();
      
      if($searchbegin) {
         foreach($searchbegin as $pos) {
             
            $date = substr($contents, $pos+21, 12);
            $dates[]=$date;

         }
      }
      sort($dates);
      $output = array_shift($dates);

      $output = PluginManufacturersimportsPostImport::checkDate($output);

      return $output;
   }

   function getExpirationDate($contents) {

      $field = "End Date:";

      $searchend = self::strallpos($contents, $field);

      $dates = array();
      
      if($searchend) {
         foreach($searchend as $pos) {
            $date = substr($contents, $pos+19,10);
            $date = trim($date);
            $dates[]=$date;
         }
      }
      sort($dates);
      
      $values = array_values($dates);
      $output = end($values);
      
      $output = PluginManufacturersimportsPostImport::checkDate($output);
      
      return $output;
   }
}

?>