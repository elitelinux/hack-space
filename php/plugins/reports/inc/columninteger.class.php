<?php
/*
 * @version $Id: columninteger.class.php 238 2013-04-09 13:43:24Z yllen $
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
class PluginReportsColumnInteger extends PluginReportsColumn {

   private $total;
   private $with_zero = 1;


   function __construct($name, $title, $options=array()) {

      if (!isset($options['extrafine'])) {
         $options['extrafine'] =  "class='right'";
      }

      if (!isset($options['extrabold'])) {
         $options['extrabold'] =  "class='b right'";
      }

      if (isset($options['with_zero'])) {
         $this->with_zero = $options['with_zero'];
      }

      parent::__construct($name, $title, $options);

      $this->total = 0;
   }


   function displayValue($output_type, $row) {

      if (isset($row[$this->name])) {
         $this->total += intval($row[$this->name]);

         if ($row[$this->name] || $this->with_zero) {
            return Html::formatNumber($row[$this->name], false, 0);
         }
      }
      return '';
   }


   function displayTotal($output_type) {
      return Html::formatNumber($this->total, false, 0);
   }
}
?>
