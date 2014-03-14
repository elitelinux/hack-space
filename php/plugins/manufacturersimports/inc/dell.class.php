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

ini_set("max_execution_time", "0");

class PluginManufacturersimportsDell extends PluginManufacturersimportsManufacturer {
   
   function showCheckbox($ID,$sel,$otherSerial=false) {
   
      return "<input type='checkbox' name='item[".$ID."]' value='1' $sel>";
      
   }
   
   function showItem($output_type,$otherSerial=false,$item_num,$row_num) {
   
      return false;
      
   }
   
   function showItemTitle($output_type,$header_num) {

      return false;
      
   }
   
   function showDocTitle($output_type,$header_num) {
      
      return false;
      
   }
   
   function showDocItem($output_type,$item_num,$row_num, $doc=null) {

      return Search::showEndLine($output_type);
      
   }
   
   function getSupplierInfo($compSerial=null, $otherserial=null) {
   
      $info["name"]="Dell";
      $info["supplier_url"] = "http://www.dell.com/support/troubleshooting/us/en/04/Index?c=us&l=en&s=bsd&cs=04&t=system&ServiceTag=";
      $info["url"] = $info["supplier_url"].$compSerial;
      return $info;
   }
   
   function getSearchField() {
      
      return false;
   }

}

?>