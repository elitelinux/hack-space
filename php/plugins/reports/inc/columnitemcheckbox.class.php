<?php
/*
 * @version $Id: columnitemcheckbox.class.php 238 2013-04-09 13:43:24Z yllen $
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

/**
 * class PluginReportsColumn to manage output
 */
class PluginReportsColumnItemCheckbox extends PluginReportsColumn {

   private $obj          = NULL;
   private $with_comment = 0;


   function __construct($name, $itemtype, $options=array()) {

      parent::__construct($name, '&nbsp;', $options);

      $this->obj = getItemForItemtype($itemtype);
   }


   function displayValue($output_type, $row) {

      if (!isset($row[$this->name]) || !$row[$this->name]) {
         return '';
      }

      if ($this->obj
          && ($output_type == Search::HTML_OUTPUT)
          && $this->obj->can($row[$this->name], 'w')) {
         return "<input type='checkbox' name='item[".$row[$this->name]."]' value='1'>";
      }

      return '';
   }
}
?>