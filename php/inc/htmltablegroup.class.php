<?php
/*
 * @version $Id: htmltablegroup.class.php 20513 2013-03-26 14:32:49Z webmyster $
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


/**
 * @since v ersion 0.84
**/
class HTMLTableGroup extends HTMLTableBase {

   private $name;
   private $content;
   private $new_headers = array();
   private $table;
   private $rows = array();


   /**
    * @param $table     HTMLTableMain object
    * @param $name
    * @param $content
   **/
   function __construct(HTMLTableMain $table, $name, $content) {

      parent::__construct(false);
      $this->table      = $table;
      $this->name       = $name;
      $this->content    = $content;
   }


   function getName() {
      return $this->name;
   }


   function getTable() {
      return $this->table;
   }


   /**
    * @param $header    HTMLTableHeader object
   **/
   function haveHeader(HTMLTableHeader $header) {

    //TODO $header_name AND $subHeader_name  not initialized
      $header->getHeaderAndSubHeaderName($header_name, $subheader_name);
      try {
         $subheaders = $this->getHeaders($header_name);
      } catch (HTMLTableUnknownHeaders $e) {
         try {
            $subheaders = $this->table->getHeaders($header_name);
         } catch (HTMLTableUnknownHeaders $e) {
            return false;
         }
      }
      return isset($subheaders[$subheader_name]);
   }


   function tryAddHeader() {

      if (isset($this->ordered_headers)) {
         throw new Exception('Implementation error: must define all headers before any row');
      }
    }


   private function completeHeaders() {

      if (!isset($this->ordered_headers)) {
         $this->ordered_headers = array();

         foreach ($this->table->getHeaderOrder() as $header_name) {
            $header        = $this->table->getSuperHeaderByName($header_name);
            $header_names  = $this->getHeaderOrder($header_name);
            if (!$header_names) {
               $this->ordered_headers[] = $header;
            } else {
               foreach ($header_names as $sub_header_name) {
                  $this->ordered_headers[] = $this->getHeaderByName($header_name, $sub_header_name);
               }
            }
         }
      }
   }


   function createRow() {

      //$this->completeHeaders();
      $new_row      = new HTMLTableRow($this);
      $this->rows[] = $new_row;
      return $new_row;
   }


   function prepareDisplay() {

      foreach ($this->table->getHeaderOrder() as $super_header_name) {
         $super_header = $this->table->getSuperHeaderByName($super_header_name);

         try {

            $sub_header_names = $this->getHeaderOrder($super_header_name);
            $count            = 0;

            foreach ($sub_header_names as $sub_header_name) {
               $sub_header = $this->getHeaderByName($super_header_name, $sub_header_name);
               if ($sub_header->hasToDisplay()) {
                  $count ++;
               }
            }

            if ($count == 0) {
               $this->ordered_headers[] = $super_header;
            } else {
               $super_header->updateNumberOfSubHeader($count);
               foreach ($sub_header_names as $sub_header_name) {
                  $sub_header = $this->getHeaderByName($super_header_name, $sub_header_name);
                  if ($sub_header->hasToDisplay()) {
                     $this->ordered_headers[]        = $sub_header;
                     $sub_header->numberOfSubHeaders = $count;
                  }
               }
            }

         } catch (HTMLTableUnknownHeadersOrder $e) {
            $this->ordered_headers[] = $super_header;
         }
      }

      foreach ($this->rows as $row) {
         $row->prepareDisplay();
      }
   }


   /**
    * Display the current group (with headers and rows)
    *
    * @param $totalNumberOfColumn         Total number of columns : to span correctly the title
    * @param $params                array of possible options:
    *     'display_super_for_each_group' display the super header (ie.: big header of the table)
    *                                    before the group specific headers
    *     'display_title_for_each_group' display the title of the header before the group
    *                                    specific headers
    *
    * @return nothing (display only)
   **/
   function displayGroup($totalNumberOfColumn, array $params) {

      $p['display_header_for_each_group'] = true;
      $p['display_super_for_each_group']  = true;
      $p['display_title_for_each_group']  = true;

      foreach ($params as $key => $val) {
         $p[$key] = $val;
      }

      if ($this->getNumberOfRows() > 0) {

         if ($p['display_title_for_each_group']
             && !empty($this->content)) {
            echo "\t<tbody><tr><th colspan='$totalNumberOfColumn'>" . $this->content .
                 "</th></tr></tbody>\n";
         }

         if ($p['display_super_for_each_group']) {
            echo "\t<tbody>\n";
            $this->table->displaySuperHeader();
            echo "\t</tbody>\n";
         }

         echo "\t<tbody><tr>\n";
         foreach ($this->ordered_headers as $header) {
            if ($header instanceof HTMLTableSubHeader) {
               $header->updateColSpan($header->numberOfSubHeaders);
               $with_content = true;
            } else {
               $with_content = false;
            }
            if ($p['display_header_for_each_group']) {
               echo "\t\t";
               $header->displayTableHeader($with_content);
               echo "\n";
            }
         }
         echo "\t</tr></tbody>\n";

         $previousNumberOfSubRows = 0;
         foreach ($this->rows as $row) {
            if (!$row->notEmpty()) {
               continue;
            }
            $currentNumberOfSubRow = $row->getNumberOfSubRows();
            if (($previousNumberOfSubRows * $currentNumberOfSubRow) > 1) {
               echo "\t<tbody><tr class='tab_bg_1'><td colspan='$totalNumberOfColumn'><hr></td></tr>".
                    "</tbody>\n";
            }
            $row->displayRow($this->ordered_headers);
            $previousNumberOfSubRows = $currentNumberOfSubRow;
         }
      }
   }


   function getNumberOfRows() {

      $numberOfRows = 0;
      foreach ($this->rows as $row) {
         if ($row->notEmpty()) {
            $numberOfRows ++;
         }
      }
      return $numberOfRows;
   }


   function getSuperHeaderByName($name) {

      try {
         return $this->getHeaderByName($name, '');
      } catch (HTMLTableUnknownHeader $e) {
         return $this->table->getSuperHeaderByName($name);
      }
   }
}

?>
